<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Cheque;
use App\Models\ChequeInvoiceLink;
use App\Models\Customer;
use App\Services\AccountingService;
use App\Services\BranchContextService;
use App\Services\NumberGeneratorService;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function __construct(
        private BranchContextService $branchContext,
        private NumberGeneratorService $numbers,
        private StockService $stockService,
        private AccountingService $accounting
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = Invoice::where('type', $request->input('type', 'invoice'));
        $this->branchContext->applyScope($q);

        if ($request->status) $q->where('status', $request->status);
        if ($request->customer_id) $q->where('customer_id', $request->customer_id);
        if ($request->from_date) $q->whereDate('invoice_date', '>=', $request->from_date);
        if ($request->to_date) $q->whereDate('invoice_date', '<=', $request->to_date);
        if ($request->search) {
            $q->where(fn($q) => $q->where('invoice_number', 'like', "%{$request->search}%")
                ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$request->search}%"))
            );
        }

        if ($request->created_by) $q->where('created_by', $request->created_by);

        return response()->json(
            $q->with(['customer', 'createdBy', 'branch'])
                ->latest('invoice_date')
                ->paginate($request->input('per_page', 20))
        );
    }

    public function stats(Request $request): JsonResponse
    {
        $q = Invoice::where('type', 'invoice');
        $this->branchContext->applyScope($q);

        $rows = (clone $q)->selectRaw('status, COUNT(*) as cnt, SUM(`total`) as total, SUM(paid_amount) as paid')
            ->groupBy('status')->get()->keyBy('status');

        $allTotal  = (clone $q)->sum('total');
        $allPaid   = (clone $q)->sum('paid_amount');
        $allCount  = (clone $q)->count();

        return response()->json([
            'total_count'     => $allCount,
            'total_amount'    => $allTotal,
            'paid_amount'     => $allPaid,
            'outstanding'     => $allTotal - $allPaid,
            'draft_count'     => $rows->get('draft')?->cnt ?? 0,
            'pending_count'   => ($rows->get('pending')?->cnt ?? 0) + ($rows->get('partial')?->cnt ?? 0),
            'paid_count'      => $rows->get('paid')?->cnt ?? 0,
            'overdue_count'   => $rows->get('overdue')?->cnt ?? 0,
            'cancelled_count' => $rows->get('cancelled')?->cnt ?? 0,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'required|exists:customers,id',
            'type' => 'required|in:proforma,invoice,credit_note',
            'status' => 'nullable|in:draft,confirmed',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date',
            'payment_terms_days' => 'nullable|integer|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_percent' => 'nullable|numeric|min:0',
            'items.*.batch_number' => 'nullable|string|max:100',
            'items.*.batch_id' => 'nullable|integer|exists:batches,id',
            'items.*.notes' => 'nullable|string|max:1000',
            'initial_payment' => 'nullable|array',
            'initial_payment.payment_method' => 'nullable|in:cash,cheque,bank_transfer',
            'initial_payment.amount' => 'nullable|numeric|min:0',
            'initial_payment.use_credit' => 'nullable|boolean',
            'initial_payment.payment_date' => 'nullable|date',
            'initial_payment.reference_number' => 'nullable|string',
            'initial_payment.notes' => 'nullable|string',
            'initial_payment.cheque_number' => 'nullable|string',
            'initial_payment.bank_name' => 'nullable|string',
            'initial_payment.cheque_date' => 'nullable|date',
            'initial_payment.account_id' => 'nullable|exists:accounts,id',
            'sales_rep_id' => 'required|exists:users,id',
        ]);

        foreach ($data['items'] as $i => $item) {
            if (empty($item['product_id']) === empty($item['service_id'])) {
                return response()->json(['message' => "Item #" . ($i + 1) . " must have either a product or a service, not both or neither."], 422);
            }
        }

        // Check credit limit
        $customer = Customer::find($data['customer_id']);
        if ($customer->credit_limit > 0) {
            $outstanding = $customer->outstanding_balance;
            $newTotal = collect($data['items'])->sum(fn($i) => $i['quantity'] * $i['unit_price']);
            if (($outstanding + $newTotal) > $customer->credit_limit) {
                return response()->json([
                    'message' => 'Credit limit exceeded.',
                    'credit_limit' => $customer->credit_limit,
                    'outstanding' => $outstanding,
                    'new_total' => $newTotal,
                ], 422);
            }
        }

        return DB::transaction(function () use ($data, $request) {
            $totals = $this->calculateTotals($data['items'], $data['discount_percent'] ?? 0, $data['discount_amount'] ?? 0, $data['tax_percent'] ?? 0);

            $invoiceStatus = $data['type'] === 'proforma' ? 'proforma' : ($data['status'] ?? 'draft');

            $invoice = Invoice::create([
                'branch_id' => $data['branch_id'],
                'customer_id' => $data['customer_id'],
                'created_by'   => $request->user()->id,
                'sales_rep_id' => $data['sales_rep_id'] ?? null,
                'invoice_number' => $this->numbers->invoiceNumber($data['branch_id'], $data['type']),
                'type' => $data['type'],
                'status' => $invoiceStatus,
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'payment_terms_days' => $data['payment_terms_days'] ?? 30,
                'subtotal' => $totals['subtotal'],
                'discount_percent' => $data['discount_percent'] ?? 0,
                'discount_amount' => $totals['discount_amount'],
                'tax_percent' => $data['tax_percent'] ?? 0,
                'tax_amount' => $totals['tax_amount'],
                'total' => $totals['total'],
                'paid_amount' => 0,
                'balance_due' => $totals['total'],
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
                'approved_by' => $invoiceStatus === 'confirmed' ? $request->user()->id : null,
                'approved_at' => $invoiceStatus === 'confirmed' ? now() : null,
            ]);

            foreach ($data['items'] as $item) {
                $this->createInvoiceItem($invoice, $item, $data['branch_id'], $data['invoice_date']);
            }

            if ($invoiceStatus === 'confirmed') {
                $invoice->load('items');
                foreach ($invoice->items as $item) {
                    if (!$item->product_id) continue; // service lines carry no stock
                    $this->stockService->deductFromBatch(
                        $item->batch_id, $item->quantity,
                        'sale_out', 'invoice', $invoice->id,
                        $request->user()->id, $invoice->invoice_date->toDateString()
                    );
                }
                $this->postSalesJournal($invoice, $request->user()->id);
            }

            $payment = $data['initial_payment'] ?? null;
            $hasPayment = !empty($payment['payment_method'])
                && ((float)($payment['amount'] ?? 0) > 0 || !empty($payment['use_credit']));

            if ($hasPayment) {
                $customer      = $invoice->customer ?? \App\Models\Customer::find($invoice->customer_id);
                $creditApplied = 0;

                // Apply customer credit first if requested
                if (!empty($payment['use_credit']) && $customer && $customer->credit_balance > 0) {
                    $creditApplied = min((float) $customer->credit_balance, (float) $invoice->total);
                    $customer->decrement('credit_balance', $creditApplied);
                }

                $remainingAfterCredit = (float) $invoice->total - $creditApplied;
                $cashAmount           = (float) ($payment['amount'] ?? 0);
                $amountToApply        = min($cashAmount, $remainingAfterCredit);
                $excess               = round($cashAmount - $amountToApply, 2);
                $totalApplied         = $amountToApply + $creditApplied;

                $chequeId = null;
                if ($cashAmount > 0 && $payment['payment_method'] === 'cheque') {
                    $cheque = Cheque::create([
                        'branch_id'            => $invoice->branch_id,
                        'created_by'           => $request->user()->id,
                        'direction'            => 'received',
                        'party_id'             => $invoice->customer_id,
                        'party_type'           => 'customer',
                        'cheque_number'        => $payment['cheque_number'],
                        'bank_name'            => $payment['bank_name'],
                        'cheque_date'          => $payment['cheque_date'],
                        'received_issued_date' => $payment['payment_date'],
                        'amount'               => $cashAmount,
                        'status'               => 'in_hand',
                    ]);
                    $chequeId = $cheque->id;

                    ChequeInvoiceLink::create([
                        'cheque_id'  => $cheque->id,
                        'invoice_id' => $invoice->id,
                        'amount'     => $amountToApply,
                    ]);
                }

                if ($totalApplied > 0) {
                    InvoicePayment::create([
                        'invoice_id'       => $invoice->id,
                        'branch_id'        => $invoice->branch_id,
                        'created_by'       => $request->user()->id,
                        'amount'           => $totalApplied,
                        'payment_method'   => $payment['payment_method'],
                        'account_id'       => $payment['account_id'] ?? null,
                        'payment_date'     => $payment['payment_date'],
                        'reference_number' => $payment['reference_number'] ?? null,
                        'cheque_id'        => $chequeId,
                        'notes'            => $payment['notes'] ?? null,
                    ]);
                }

                $newBalance = (float) $invoice->total - $totalApplied;
                $invoice->update([
                    'paid_amount' => $totalApplied,
                    'balance_due' => max(0, $newBalance),
                    'status'      => $newBalance <= 0 ? 'paid' : 'partially_paid',
                ]);

                // Store overpayment as customer credit
                if ($excess > 0 && $customer) {
                    $customer->increment('credit_balance', $excess);
                }

                if ($cashAmount > 0) {
                    $this->postPaymentReceivedJournal($invoice, $cashAmount, $payment['payment_date'], $request->user()->id, $payment['payment_method'], $payment['account_id'] ?? null);
                }
            }

            return response()->json($invoice->load(['items.product', 'customer', 'branch']), 201);
        }, 5);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json(
            $invoice->load(['items.product', 'customer', 'branch', 'createdBy', 'payments.cheque'])
        );
    }

    /**
     * Per-item cost and profit breakdown for this invoice. Super-admin only —
     * cost price and margin are sensitive and shouldn't be visible to whoever
     * created the invoice (sales staff, branch managers, etc).
     */
    public function profit(Request $request, Invoice $invoice): JsonResponse
    {
        if (!$request->user()->hasRole('super_admin')) {
            return response()->json(['message' => 'Not authorized to view profit details.'], 403);
        }
        if ($invoice->type !== 'invoice') {
            return response()->json(['message' => 'Profit is only available for confirmed sales invoices.'], 422);
        }

        $invoice->loadMissing('items');
        $items = $invoice->items->map(function ($item) {
            $qty        = (float) $item->quantity;
            $lineRevenue = (float) $item->total; // post-discount, pre-tax line value already stored
            $unitCost   = $item->unit_cost !== null ? (float) $item->unit_cost : null;
            $lineCost   = $unitCost !== null ? round($unitCost * $qty, 2) : null;
            $lineProfit = $lineCost !== null ? round($lineRevenue - $lineCost, 2) : null;
            return [
                'product_name' => $item->product_name,
                'product_code' => $item->product_code,
                'quantity'     => $qty,
                'unit_price'   => (float) $item->unit_price,
                'unit_cost'    => $unitCost,
                'line_revenue' => $lineRevenue,
                'line_cost'    => $lineCost,
                'line_profit'  => $lineProfit,
                'margin_pct'   => ($lineProfit !== null && $lineRevenue > 0) ? round($lineProfit / $lineRevenue * 100, 1) : null,
                'cost_missing' => $unitCost === null,
            ];
        })->values();

        $hasAllCosts  = $items->every(fn($i) => !$i['cost_missing']);
        $revenueTotal = (float) $invoice->subtotal;

        // Total cost always comes from what's actually posted to the books —
        // the original COGS entry plus any later correcting 'adjustment' entry
        // (e.g. an average-cost recalculation) — since that's the single source
        // of truth and item-level unit_cost snapshots can't reflect a correction
        // applied after the sale. Item-level figures above are for display only.
        $cogsId = $this->accounting->getAccountId($invoice->branch_id, 'acc_cogs');
        $costTotal = $cogsId ? (float) \App\Models\JournalEntryLine::where('account_id', $cogsId)
            ->whereHas('journalEntry', fn($q) => $q
                ->where('reference_type', 'invoice')->where('reference_id', $invoice->id)
                ->whereIn('type', ['cogs', 'adjustment'])->where('status', 'posted'))
            ->selectRaw('SUM(debit) - SUM(credit) as net')->value('net') : 0.0;
        $costTotal = round($costTotal, 2);
        $profitTotal = round($revenueTotal - $costTotal, 2);

        $itemCostSum = $hasAllCosts ? round($items->sum('line_cost'), 2) : null;
        $hasAllCosts = $hasAllCosts && $itemCostSum !== null && abs($itemCostSum - $costTotal) < 0.5;

        return response()->json([
            'invoice_number' => $invoice->invoice_number,
            'items'          => $items,
            'revenue_total'  => $revenueTotal,
            'cost_total'     => $costTotal,
            'profit_total'   => $profitTotal,
            'margin_pct'     => $revenueTotal > 0 ? round($profitTotal / $revenueTotal * 100, 1) : null,
            'cost_estimated' => !$hasAllCosts,
        ]);
    }

    public function update(Request $request, Invoice $invoice): JsonResponse
    {
        if (!in_array($invoice->status, ['draft', 'proforma'])) {
            return response()->json(['message' => 'Cannot edit a confirmed invoice.'], 422);
        }

        $data = $request->validate([
            'invoice_date' => 'sometimes|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_percent' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'items' => 'sometimes|array|min:1',
        ]);

        return DB::transaction(function () use ($data, $invoice) {
            $touchesTotals = isset($data['items']) || array_intersect(
                ['discount_percent', 'discount_amount', 'tax_percent'],
                array_keys($data)
            );

            if (isset($data['items'])) {
                foreach ($data['items'] as $i => $item) {
                    if (empty($item['product_id']) === empty($item['service_id'])) {
                        abort(422, "Item #" . ($i + 1) . " must have either a product or a service, not both or neither.");
                    }
                }
                $invoice->items()->delete();
                foreach ($data['items'] as $item) {
                    $this->createInvoiceItem($invoice, $item, $invoice->branch_id, $invoice->invoice_date->toDateString());
                }
                unset($data['items']);
            }

            if ($touchesTotals) {
                $itemsForCalc = isset($data['items']) ? $data['items'] : $invoice->items()->get()
                    ->map(fn($i) => ['unit_price' => $i->unit_price, 'quantity' => $i->quantity, 'discount_percent' => $i->discount_percent])
                    ->toArray();
                $totals = $this->calculateTotals(
                    $itemsForCalc,
                    $data['discount_percent'] ?? $invoice->discount_percent,
                    $data['discount_amount'] ?? $invoice->discount_amount,
                    $data['tax_percent'] ?? $invoice->tax_percent
                );
                $data = array_merge($data, [
                    'subtotal' => $totals['subtotal'],
                    'discount_amount' => $totals['discount_amount'],
                    'tax_amount' => $totals['tax_amount'],
                    'total' => $totals['total'],
                    'balance_due' => $totals['total'] - $invoice->paid_amount,
                ]);
            }

            $invoice->update($data);
            return response()->json($invoice->fresh(['items.product', 'customer', 'branch']));
        });
    }

    public function confirm(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->status !== 'draft') {
            return response()->json(['message' => 'Invoice is not in draft status.'], 422);
        }

        return DB::transaction(function () use ($invoice, $request) {
            $invoice->update([
                'status'      => 'confirmed',
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);

            foreach ($invoice->items as $item) {
                if (!$item->product_id) continue; // service lines carry no stock
                $this->stockService->deductFromBatch(
                    $item->batch_id, $item->quantity,
                    'sale_out', 'invoice', $invoice->id,
                    $request->user()->id, $invoice->invoice_date->toDateString()
                );
            }

            $this->postSalesJournal($invoice, $request->user()->id);

            return response()->json($invoice->fresh());
        });
    }

    public function convertProforma(Request $request, Invoice $invoice): JsonResponse
    {
        if ($invoice->type !== 'proforma') {
            return response()->json(['message' => 'Invoice is not a proforma.'], 422);
        }

        return DB::transaction(function () use ($invoice, $request) {
            $invoice->update([
                'type' => 'invoice',
                'status' => 'confirmed',
                'invoice_number' => $this->numbers->invoiceNumber($invoice->branch_id, 'invoice'),
                'approved_by' => $request->user()->id,
                'approved_at' => now(),
            ]);

            foreach ($invoice->items as $item) {
                if (!$item->product_id) continue; // service lines carry no stock
                $this->stockService->deductFromBatch(
                    $item->batch_id,
                    $item->quantity,
                    'sale_out',
                    'invoice',
                    $invoice->id,
                    $request->user()->id,
                    $invoice->invoice_date->toDateString()
                );
            }

            return response()->json(['message' => 'Converted to invoice.', 'invoice' => $invoice->fresh()]);
        }, 5);
    }

    public function recordPayment(Request $request, Invoice $invoice): JsonResponse
    {
        $data = $request->validate([
            'amount'           => 'required|numeric|min:0',
            'payment_method'   => 'required|in:cash,cheque,bank_transfer',
            'payment_date'     => 'required|date',
            'reference_number' => 'nullable|string',
            'notes'            => 'nullable|string',
            'use_credit'       => 'nullable|boolean',
            'cheque_number'    => 'required_if:payment_method,cheque|nullable|string',
            'bank_name'        => 'required_if:payment_method,cheque|nullable|string',
            'cheque_date'      => 'required_if:payment_method,cheque|nullable|date',
            'account_id'       => 'nullable|exists:accounts,id',
        ]);

        if ($data['amount'] <= 0 && empty($data['use_credit'])) {
            return response()->json(['message' => 'Enter a payment amount greater than zero.'], 422);
        }

        return DB::transaction(function () use ($data, $invoice, $request) {
            $invoice->loadMissing('customer');
            $customer = $invoice->customer;

            $amountReceived = (float) $data['amount'];
            $balanceDue     = (float) $invoice->balance_due;

            // If the customer wants to use their credit balance, apply it first
            $creditApplied = 0;
            if (!empty($data['use_credit']) && $customer->credit_balance > 0) {
                $creditApplied = min((float) $customer->credit_balance, $balanceDue);
                $balanceDue   -= $creditApplied;
                $customer->decrement('credit_balance', $creditApplied);
            }

            // How much of the new payment applies to this invoice vs. becomes a credit
            $amountToApply = min($amountReceived, $balanceDue);
            $excess        = round($amountReceived - $amountToApply, 2);

            $chequeId = null;
            if ($data['payment_method'] === 'cheque') {
                $cheque = Cheque::create([
                    'branch_id'            => $invoice->branch_id,
                    'created_by'           => $request->user()->id,
                    'direction'            => 'received',
                    'party_id'             => $invoice->customer_id,
                    'party_type'           => 'customer',
                    'cheque_number'        => $data['cheque_number'],
                    'bank_name'            => $data['bank_name'],
                    'cheque_date'          => $data['cheque_date'],
                    'received_issued_date' => $data['payment_date'],
                    'amount'               => $amountReceived,
                    'status'               => 'in_hand',
                ]);
                $chequeId = $cheque->id;

                ChequeInvoiceLink::create([
                    'cheque_id'  => $cheque->id,
                    'invoice_id' => $invoice->id,
                    'amount'     => $amountToApply,
                ]);
            }

            // Record the payment for the amount actually applied to the invoice
            InvoicePayment::create([
                'invoice_id'       => $invoice->id,
                'branch_id'        => $invoice->branch_id,
                'created_by'       => $request->user()->id,
                'amount'           => $amountToApply + $creditApplied,
                'payment_method'   => $data['payment_method'],
                'account_id'       => $data['account_id'] ?? null,
                'payment_date'     => $data['payment_date'],
                'reference_number' => $data['reference_number'] ?? null,
                'cheque_id'        => $chequeId,
                'notes'            => $data['notes'] ?? null,
            ]);

            $totalApplied = $amountToApply + $creditApplied;
            $newPaid      = (float) $invoice->paid_amount + $totalApplied;
            $newBalance   = (float) $invoice->total - $newPaid;

            $invoice->update([
                'paid_amount' => $newPaid,
                'balance_due' => max(0, $newBalance),
                'status'      => $newBalance <= 0 ? 'paid' : 'partially_paid',
            ]);

            // Store excess as customer credit
            if ($excess > 0) {
                $customer->increment('credit_balance', $excess);
            }

            // Post journal for the full amount received (clears AR and/or creates advance).
            // Cheque receipts always land in Cheques in Hand (no account_id to pick),
            // so this only actually matters for cash/bank_transfer.
            $this->postPaymentReceivedJournal($invoice, $amountReceived, $data['payment_date'], $request->user()->id, $data['payment_method'], $data['account_id'] ?? null);

            $response = [
                'message' => 'Payment recorded.',
                'invoice' => $invoice->fresh(),
            ];

            if ($excess > 0) {
                $response['credit_added']    = $excess;
                $response['credit_balance']  = (float) $customer->fresh()->credit_balance;
                $response['message']         = "Payment recorded. Rs. " . number_format($excess, 2) . " credited to customer account.";
            }
            if ($creditApplied > 0) {
                $response['credit_used'] = $creditApplied;
            }

            return response()->json($response);
        });
    }

    /**
     * One cash/cheque/bank payment from a customer, applied across several of their
     * outstanding invoices in one go — oldest invoice date first. Any leftover after
     * every selected invoice is fully paid becomes standing customer credit, same
     * as a single-invoice overpayment.
     */
    public function bulkPayment(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'amount'           => 'required|numeric|min:0',
            'payment_method'   => 'required|in:cash,cheque,bank_transfer',
            'payment_date'     => 'required|date',
            'reference_number' => 'nullable|string',
            'notes'            => 'nullable|string',
            'use_credit'       => 'nullable|boolean',
            'cheque_number'    => 'required_if:payment_method,cheque|nullable|string',
            'bank_name'        => 'required_if:payment_method,cheque|nullable|string',
            'cheque_date'      => 'required_if:payment_method,cheque|nullable|date',
            'account_id'       => 'nullable|exists:accounts,id',
            'invoice_ids'      => 'nullable|array',
            'invoice_ids.*'    => 'integer|exists:invoices,id',
        ]);

        if ($data['amount'] <= 0 && empty($data['use_credit'])) {
            return response()->json(['message' => 'Enter a payment amount greater than zero.'], 422);
        }

        return DB::transaction(function () use ($data, $customer, $request) {
            $query = Invoice::where('customer_id', $customer->id)
                ->where('type', 'invoice')
                ->where('balance_due', '>', 0)
                ->whereIn('status', ['confirmed', 'partially_paid', 'overdue'])
                ->orderBy('invoice_date')->orderBy('id');
            if (!empty($data['invoice_ids'])) {
                $query->whereIn('id', $data['invoice_ids']);
            }
            $invoices = $query->get();

            if ($invoices->isEmpty()) {
                return response()->json(['message' => 'No outstanding invoices to pay.'], 422);
            }

            $chequeId = null;
            if ($data['payment_method'] === 'cheque' && (float) $data['amount'] > 0) {
                $cheque = Cheque::create([
                    'branch_id'            => $customer->branch_id,
                    'created_by'           => $request->user()->id,
                    'direction'            => 'received',
                    'party_id'             => $customer->id,
                    'party_type'           => 'customer',
                    'cheque_number'        => $data['cheque_number'],
                    'bank_name'            => $data['bank_name'],
                    'cheque_date'          => $data['cheque_date'],
                    'received_issued_date' => $data['payment_date'],
                    'amount'               => (float) $data['amount'],
                    'status'               => 'in_hand',
                ]);
                $chequeId = $cheque->id;
            }

            $remainingCash   = (float) $data['amount'];
            $remainingCredit = !empty($data['use_credit']) ? (float) $customer->credit_balance : 0;
            $startingCredit  = $remainingCredit;
            $applied = [];

            foreach ($invoices as $invoice) {
                if ($remainingCash <= 0 && $remainingCredit <= 0) break;

                $balanceDue    = (float) $invoice->balance_due;
                $creditForThis = min($remainingCredit, $balanceDue);
                $remainingCredit -= $creditForThis;
                $balanceDue      -= $creditForThis;

                $cashForThis = min($remainingCash, $balanceDue);
                $remainingCash -= $cashForThis;

                $totalForThis = round($creditForThis + $cashForThis, 2);
                if ($totalForThis <= 0) continue;

                if ($chequeId && $cashForThis > 0) {
                    ChequeInvoiceLink::create(['cheque_id' => $chequeId, 'invoice_id' => $invoice->id, 'amount' => $cashForThis]);
                }

                InvoicePayment::create([
                    'invoice_id'       => $invoice->id,
                    'branch_id'        => $invoice->branch_id,
                    'created_by'       => $request->user()->id,
                    'amount'           => $totalForThis,
                    'payment_method'   => $data['payment_method'],
                    'account_id'       => $data['account_id'] ?? null,
                    'payment_date'     => $data['payment_date'],
                    'reference_number' => $data['reference_number'] ?? null,
                    'cheque_id'        => $cashForThis > 0 ? $chequeId : null,
                    'notes'            => $data['notes'] ?? null,
                ]);

                $newPaid    = (float) $invoice->paid_amount + $totalForThis;
                $newBalance = max(0, (float) $invoice->total - $newPaid);
                $invoice->update([
                    'paid_amount' => $newPaid,
                    'balance_due' => $newBalance,
                    'status'      => $newBalance <= 0 ? 'paid' : 'partially_paid',
                ]);

                if ($cashForThis > 0) {
                    $this->postPaymentReceivedJournal($invoice, $cashForThis, $data['payment_date'], $request->user()->id, $data['payment_method'], $data['account_id'] ?? null);
                }

                $applied[] = [
                    'invoice_id'        => $invoice->id,
                    'invoice_number'    => $invoice->invoice_number,
                    'applied'           => $totalForThis,
                    'remaining_balance' => $newBalance,
                ];
            }

            $creditUsed = round($startingCredit - $remainingCredit, 2);
            if ($creditUsed > 0) {
                $customer->decrement('credit_balance', $creditUsed);
            }
            if ($remainingCash > 0) {
                $customer->increment('credit_balance', round($remainingCash, 2));
            }

            return response()->json([
                'message'        => 'Bulk payment applied across ' . count($applied) . ' invoice(s).',
                'applied'        => $applied,
                'credit_used'    => $creditUsed,
                'credit_added'   => round($remainingCash, 2),
                'credit_balance' => (float) $customer->fresh()->credit_balance,
            ]);
        });
    }

    /**
     * Delete a wrongly-recorded payment (e.g. cheque entered with the wrong amount).
     * Reverses the invoice balance/status, detaches and removes the linked cheque
     * (only while it's still 'in_hand' — once deposited/cleared/bounced/transferred
     * it has its own separate side-effects that this simple reversal can't safely
     * untangle), and posts an offsetting journal entry rather than editing history.
     */
    public function deletePayment(Request $request, Invoice $invoice, InvoicePayment $payment): JsonResponse
    {
        if ((int) $payment->invoice_id !== (int) $invoice->id) {
            return response()->json(['message' => 'Payment does not belong to this invoice.'], 404);
        }
        if ($invoice->status === 'cancelled') {
            return response()->json(['message' => 'Cannot modify payments on a cancelled invoice.'], 422);
        }

        return DB::transaction(function () use ($invoice, $payment, $request) {
            $invoice->loadMissing('customer');
            $customer = $invoice->customer;
            $cheque   = $payment->cheque_id ? Cheque::find($payment->cheque_id) : null;

            if ($cheque && $cheque->status !== 'in_hand') {
                return response()->json([
                    'message' => "This payment's cheque has already been {$cheque->status} — reverse the cheque status in Manage Cheque first, then delete the payment.",
                ], 422);
            }

            // What the original journal entry actually moved (the full amount received),
            // and how much of the payment's applied amount came from pre-existing customer
            // credit vs. the cheque itself — both derivable from stored data, no new columns needed.
            $journalAmount   = (float) $payment->amount;
            $creditAdjustment = 0;

            if ($cheque) {
                $journalAmount = (float) $cheque->amount;
                // payment.amount = amountToApply + creditApplied; the cheque-invoice link stores
                // amountToApply alone. So (payment.amount - cheque.amount) nets "pre-existing
                // credit returned" against "overpayment excess clawed back" in one step.
                $creditAdjustment = (float) $payment->amount - (float) $cheque->amount;
            }

            // 1. Reverse the invoice
            $newPaid    = max(0, (float) $invoice->paid_amount - (float) $payment->amount);
            $newBalance = (float) $invoice->total - $newPaid;
            $invoice->update([
                'paid_amount' => $newPaid,
                'balance_due' => max(0, $newBalance),
                'status'      => $newPaid <= 0.001 ? 'confirmed' : 'partially_paid',
            ]);

            // 2. Reverse the customer credit impact (only computable when a cheque is involved — see above)
            if ($creditAdjustment !== 0 && $customer) {
                $customer->update(['credit_balance' => max(0, (float) $customer->credit_balance + $creditAdjustment)]);
            }

            // 3. Detach and remove the cheque (only while untouched since receipt)
            if ($cheque) {
                ChequeInvoiceLink::where('cheque_id', $cheque->id)->where('invoice_id', $invoice->id)->delete();
                $stillUsed = $cheque->invoiceLinks()->exists()
                    || $cheque->supplierPayments()->exists()
                    || $cheque->purchaseOrders()->exists()
                    || $cheque->expenses()->exists();
                if (!$stillUsed) {
                    $cheque->delete();
                }
            }

            // 4. Offsetting journal entry — history stays intact, this just nets it to zero
            try {
                $branchId       = $invoice->branch_id;
                $arId           = $customer?->account_id ?: $this->accounting->getAccountId($branchId, 'acc_trade_receivables');
                // Reverse whichever account this specific payment actually used — falling
                // back to a branch-default guess only for older payments recorded before
                // account_id was captured, where there's nothing else to go on.
                $debitAccountId = $payment->account_id ?: match ($payment->payment_method) {
                    'cheque'       => $this->accounting->getAccountId($branchId, 'acc_cheques_in_hand') ?: $this->accounting->getAccountId($branchId, 'acc_cash'),
                    'bank_transfer', 'credit_card', 'online' => $this->accounting->getAccountId($branchId, 'acc_bank') ?: $this->accounting->getAccountId($branchId, 'acc_cash'),
                    default        => $this->accounting->getAccountId($branchId, 'acc_cash') ?: $this->accounting->getAccountId($branchId, 'acc_bank'),
                };
                if ($arId && $debitAccountId && $journalAmount > 0) {
                    $this->accounting->createEntry(
                        $branchId, 'payment_reversed',
                        "Payment reversed – Invoice {$invoice->invoice_number}" . ($cheque ? " (cheque {$cheque->cheque_number})" : ''),
                        now()->toDateString(),
                        [
                            ['account_id' => $arId,           'debit' => $journalAmount, 'credit' => 0,             'description' => "Payment reversal – {$invoice->invoice_number}"],
                            ['account_id' => $debitAccountId, 'debit' => 0,               'credit' => $journalAmount, 'description' => "Payment reversal – {$invoice->invoice_number}"],
                        ],
                        $request->user()->id, 'invoice', $invoice->id
                    );
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('deletePayment journal failed', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
            }

            $payment->delete();

            return response()->json([
                'message' => 'Payment deleted and reversed.',
                'invoice' => $invoice->fresh(['items.product', 'customer', 'branch', 'payments.cheque']),
            ]);
        });
    }

    public function cancel(Request $request, Invoice $invoice): JsonResponse
    {
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return response()->json(['message' => 'Cannot cancel this invoice.'], 422);
        }

        if ($invoice->type === 'credit_note') {
            // A credit note has side effects a plain cancel doesn't know how to undo:
            // it already restocked returned goods, reduced the original invoice's
            // balance_due (or credited the customer), and posted a sales_return
            // journal entry crediting AR. Cancelling it here would leave all of
            // that in place while merely flipping the status flag, silently
            // corrupting stock, the original invoice, and the customer's AR
            // balance. Reversing a credit note needs its own dedicated flow.
            return response()->json([
                'message' => 'Credit notes cannot be cancelled from here — reversing one affects stock, the original invoice, and the customer\'s account balance together. Contact support to reverse a credit note.',
            ], 422);
        }

        return DB::transaction(function () use ($invoice, $request) {
            if (in_array($invoice->status, ['confirmed', 'partially_paid'])) {
                $invoice->loadMissing('items');
                $returnedCost = 0;
                foreach ($invoice->items as $item) {
                    if (!$item->product_id) continue; // service lines carry no stock/cost to reverse
                    // Restock into the exact batch it was sold from, at that
                    // batch's own cost — not a blended average — so cancelling
                    // never distorts other batches' cost/quantity.
                    $returnedCost += (float) $item->unit_cost * (float) $item->quantity;
                    $this->stockService->restockBatch(
                        $item->batch_id, $item->quantity,
                        'sale_return', 'invoice', $invoice->id,
                        $request->user()->id, now()->toDateString()
                    );
                }

                // Reverse the revenue and cost this invoice posted when confirmed —
                // without this, a cancelled invoice's revenue stays on the books
                // forever even though the sale no longer exists.
                $this->accounting->postSalesReversalJournal($invoice, $request->user()->id);
                $this->accounting->postCogsReversalJournal($invoice, $invoice->branch_id, $returnedCost, $request->user()->id);
            }

            $invoice->update([
                'status'              => 'cancelled',
                'cancelled_at'        => now(),
                'cancellation_reason' => $request->input('reason', 'Cancelled by user'),
            ]);

            return response()->json(['message' => 'Invoice cancelled.']);
        });
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        if (in_array($invoice->status, ['partially_paid', 'paid'])) {
            return response()->json(['message' => 'Cannot delete a paid invoice.'], 422);
        }

        DB::transaction(function () use ($invoice) {
            // Remove linked journal entries
            \App\Models\JournalEntry::where('reference_type', 'invoice')
                ->where('reference_id', $invoice->id)
                ->get()
                ->each(function ($je) {
                    $je->lines()->delete();
                    $je->forceDelete();
                });
            $invoice->items()->delete();
            $invoice->payments()->delete();
            $invoice->forceDelete();
        });

        return response()->json(['message' => 'Invoice deleted.']);
    }

    public function pdf(Invoice $invoice)
    {
        $invoice->load(['items.product', 'customer', 'branch', 'createdBy', 'salesRep']);
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));
        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    public function checkStock(Request $request): JsonResponse
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        $result = [];
        foreach ($request->items as $item) {
            $available = $this->stockService->getAvailableStock($item['product_id'], $request->branch_id);
            $result[] = [
                'product_id' => $item['product_id'],
                'requested' => $item['quantity'],
                'available' => $available,
                'sufficient' => $available >= $item['quantity'],
            ];
        }

        return response()->json($result);
    }

    private function postSalesJournal(Invoice $invoice, int $userId): void
    {
        $this->accounting->postSalesJournal($invoice, $userId);
    }

    private function postPaymentReceivedJournal(Invoice $invoice, float $amount, string $date, int $userId, string $paymentMethod = 'cash', ?int $accountId = null): void
    {
        $this->accounting->postPaymentReceivedJournal($invoice, $amount, $date, $userId, $paymentMethod, $accountId);
    }

    /**
     * Resolve which batch a sale line draws from at the moment the line is
     * added (draft or confirmed) — so a later confirm()/convertProforma() just
     * reads the already-decided batch_id rather than re-resolving it. Honors
     * an explicit frontend pick, else defaults to the oldest batch with stock,
     * else opens/reuses a backorder batch (selling ahead of the first GRN is
     * a normal, accepted pattern for this business).
     */
    private function resolveBatchForItem(int $productId, int $branchId, $requestedBatchId, string $date): \App\Models\Batch
    {
        if ($requestedBatchId) {
            $batch = \App\Models\Batch::find($requestedBatchId);
            if ($batch) return $batch;
        }
        return $this->stockService->pickOldestBatch($productId, $branchId)
            ?? $this->stockService->getOrCreateBackorderBatch($productId, $branchId, $date);
    }

    /**
     * Builds one InvoiceItem line, branching on whether it's a stocked product
     * (resolves/snapshots a batch, same as always) or a service (no stock, no
     * batch — priced purely off the service's own rate).
     */
    private function createInvoiceItem(Invoice $invoice, array $item, int $branchId, string $invoiceDate): InvoiceItem
    {
        $lineDiscount = ($item['unit_price'] * $item['quantity']) * (($item['discount_percent'] ?? 0) / 100);
        $lineSubtotal = ($item['unit_price'] * $item['quantity']) - $lineDiscount;
        $lineTax = $lineSubtotal * (($item['tax_percent'] ?? 0) / 100);

        if (!empty($item['service_id'])) {
            $service = \App\Models\Service::find($item['service_id']);
            return InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $item['service_id'],
                'item_type' => 'service',
                'product_name' => $service->name,
                'product_code' => $service->code,
                'unit' => $service->unit ?: 'service',
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_percent' => $item['discount_percent'] ?? 0,
                'discount_amount' => $lineDiscount,
                'tax_percent' => $item['tax_percent'] ?? 0,
                'tax_amount' => $lineTax,
                'total' => $lineSubtotal + $lineTax,
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $product = \App\Models\Product::find($item['product_id']);
        $batch = $this->resolveBatchForItem($item['product_id'], $branchId, $item['batch_id'] ?? null, $invoiceDate);

        return InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'product_id' => $item['product_id'],
            'item_type' => 'product',
            'batch_id' => $batch->id,
            'product_name' => $product->name,
            'product_code' => $product->code,
            'unit' => $product->unit,
            'quantity' => $item['quantity'],
            'unit_price' => $item['unit_price'],
            'unit_cost' => (float) $batch->cost_price,
            'discount_percent' => $item['discount_percent'] ?? 0,
            'discount_amount' => $lineDiscount,
            'tax_percent' => $item['tax_percent'] ?? 0,
            'tax_amount' => $lineTax,
            'total' => $lineSubtotal + $lineTax,
            'serial_numbers' => $item['serial_numbers'] ?? null,
            'batch_number' => $item['batch_number'] ?? $batch->batch_number,
            'notes' => $item['notes'] ?? null,
        ]);
    }

    private function calculateTotals(array $items, float $discountPercent, float $discountAmount, float $taxPercent): array
    {
        $subtotal = collect($items)->sum(fn($i) => $i['unit_price'] * $i['quantity'] * (1 - ($i['discount_percent'] ?? 0) / 100));
        $discount = $discountAmount > 0 ? $discountAmount : ($subtotal * $discountPercent / 100);
        $taxable = $subtotal - $discount;
        $tax = $taxable * $taxPercent / 100;
        return [
            'subtotal' => round($subtotal, 2),
            'discount_amount' => round($discount, 2),
            'tax_amount' => round($tax, 2),
            'total' => round($taxable + $tax, 2),
        ];
    }
}
