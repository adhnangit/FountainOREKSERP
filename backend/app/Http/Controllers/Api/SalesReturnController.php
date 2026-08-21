<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use App\Models\ProductBranchStock;
use App\Services\AccountingService;
use App\Services\BranchContextService;
use App\Services\NumberGeneratorService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesReturnController extends Controller
{
    public function __construct(
        private BranchContextService $branchContext,
        private NumberGeneratorService $numbers,
        private StockService $stockService,
        private AccountingService $accounting
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = Invoice::where('type', 'credit_note');
        $this->branchContext->applyScope($q);

        if ($request->search) {
            $q->where(fn($qq) => $qq->where('invoice_number', 'like', "%{$request->search}%")
                ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$request->search}%"))
                ->orWhereHas('originalInvoice', fn($o) => $o->where('invoice_number', 'like', "%{$request->search}%"))
            );
        }
        if ($request->from_date) $q->whereDate('invoice_date', '>=', $request->from_date);
        if ($request->to_date) $q->whereDate('invoice_date', '<=', $request->to_date);

        return response()->json(
            $q->with(['customer', 'branch', 'createdBy', 'originalInvoice', 'items.product'])
                ->latest('invoice_date')->latest('id')
                ->paginate($request->input('per_page', 20))
        );
    }

    /**
     * Items on an invoice that are still eligible for return
     * (sold quantity minus quantities on previous credit notes).
     */
    public function returnable(Invoice $invoice): JsonResponse
    {
        if ($invoice->type !== 'invoice' || !in_array($invoice->status, ['confirmed', 'partially_paid', 'paid'])) {
            return response()->json(['message' => 'Only confirmed invoices can be returned.'], 422);
        }

        $invoice->load(['items', 'customer']);
        $returned = $this->returnedQuantities($invoice->id);

        // Invoice-level discount/tax proration: value each returned unit at what
        // the customer effectively paid, not just the line price.
        $lineSum = (float) $invoice->items->sum('total');
        $factor  = $lineSum > 0 ? (float) $invoice->total / $lineSum : 1;

        $items = $invoice->items->map(fn($i) => [
            'product_id'          => $i->product_id,
            'product_name'        => $i->product_name,
            'unit'                => $i->unit,
            'unit_price'          => (float) $i->unit_price,
            'line_total'          => (float) $i->total,
            'sold_quantity'       => (float) $i->quantity,
            'returned_quantity'   => (float) ($returned[$i->product_id] ?? 0),
            'returnable_quantity' => max(0, (float) $i->quantity - (float) ($returned[$i->product_id] ?? 0)),
        ])->values();

        return response()->json([
            'invoice'  => $invoice->only(['id', 'invoice_number', 'status', 'total', 'paid_amount', 'balance_due', 'invoice_date']),
            'customer' => $invoice->customer?->only(['id', 'name', 'credit_balance']),
            'factor'   => round($factor, 6),
            'items'    => $items,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'original_invoice_id' => 'required|exists:invoices,id',
            'return_date'         => 'required|date',
            'reason'              => 'nullable|string',
            'items'               => 'required|array|min:1',
            'items.*.product_id'  => 'required|exists:products,id',
            'items.*.quantity'    => 'required|numeric|min:0.01',
        ]);

        $original = Invoice::with(['items', 'customer'])->findOrFail($data['original_invoice_id']);

        if ($original->type !== 'invoice' || !in_array($original->status, ['confirmed', 'partially_paid', 'paid'])) {
            return response()->json(['message' => 'Only confirmed invoices can be returned.'], 422);
        }

        $returned = $this->returnedQuantities($original->id);
        $lineSum  = (float) $original->items->sum('total');
        $factor   = $lineSum > 0 ? (float) $original->total / $lineSum : 1;

        // Validate quantities against what is still returnable, valuing each line
        // proportionally to the original line (price, discount and tax included).
        $lines = [];
        foreach ($data['items'] as $item) {
            $origItem = $original->items->firstWhere('product_id', $item['product_id']);
            if (!$origItem) {
                return response()->json(['message' => 'One of the products is not on the original invoice.'], 422);
            }
            $available = (float) $origItem->quantity - (float) ($returned[$item['product_id']] ?? 0);
            if ((float) $item['quantity'] > $available + 0.0001) {
                return response()->json([
                    'message' => "Return quantity for {$origItem->product_name} exceeds the remaining returnable quantity ({$available}).",
                ], 422);
            }
            $lines[] = ['orig' => $origItem, 'qty' => (float) $item['quantity']];
        }

        return DB::transaction(function () use ($data, $original, $lines, $factor, $request) {
            $subtotal = 0; $lineDiscount = 0; $lineTax = 0; $lineTotals = 0;
            foreach ($lines as $l) {
                $ratio         = $l['qty'] / (float) $l['orig']->quantity;
                $subtotal     += (float) $l['orig']->unit_price * $l['qty'];
                $lineDiscount += (float) $l['orig']->discount_amount * $ratio;
                $lineTax      += (float) $l['orig']->tax_amount * $ratio;
                $lineTotals   += (float) $l['orig']->total * $ratio;
            }
            // Amount the customer is actually owed back (invoice-level discount/tax prorated)
            $creditTotal = round($lineTotals * $factor, 2);

            $creditNote = Invoice::create([
                'branch_id'           => $original->branch_id,
                'customer_id'         => $original->customer_id,
                'created_by'          => $request->user()->id,
                'sales_rep_id'        => $original->sales_rep_id,
                'original_invoice_id' => $original->id,
                'invoice_number'      => $this->numbers->invoiceNumber($original->branch_id, 'credit_note'),
                'type'                => 'credit_note',
                'status'              => 'confirmed',
                'invoice_date'        => $data['return_date'],
                'subtotal'            => round($subtotal, 2),
                'discount_amount'     => round($lineDiscount + max(0, $lineTotals - $creditTotal), 2),
                'tax_amount'          => round($lineTax, 2),
                'total'               => $creditTotal,
                'paid_amount'         => 0,
                'balance_due'         => 0,
                'notes'               => $data['reason'] ?? null,
                'approved_by'         => $request->user()->id,
                'approved_at'         => now(),
            ]);

            $returnedCost = 0;
            foreach ($lines as $l) {
                $ratio = $l['qty'] / (float) $l['orig']->quantity;
                InvoiceItem::create([
                    'invoice_id'       => $creditNote->id,
                    'product_id'       => $l['orig']->product_id,
                    'batch_id'         => $l['orig']->batch_id,
                    'product_name'     => $l['orig']->product_name,
                    'product_code'     => $l['orig']->product_code,
                    'unit'             => $l['orig']->unit,
                    'quantity'         => $l['qty'],
                    'unit_price'       => $l['orig']->unit_price,
                    'unit_cost'        => $l['orig']->unit_cost,
                    'discount_percent' => $l['orig']->discount_percent,
                    'discount_amount'  => round((float) $l['orig']->discount_amount * $ratio, 2),
                    'tax_percent'      => $l['orig']->tax_percent,
                    'tax_amount'       => round((float) $l['orig']->tax_amount * $ratio, 2),
                    'total'            => round((float) $l['orig']->total * $ratio, 2),
                    'batch_number'     => $l['orig']->batch_number,
                ]);

                // Put the goods back into the exact batch they were sold from,
                // at that batch's own cost — not a blended average.
                $returnedCost += (float) $l['orig']->unit_cost * $l['qty'];
                $this->stockService->restockBatch(
                    $l['orig']->batch_id, $l['qty'],
                    'sale_return', 'invoice', $creditNote->id,
                    $request->user()->id, $creditNote->invoice_date->toDateString()
                );
            }

            // Apply the credit: reduce what the customer still owes on the invoice
            // first, then park any remainder on their credit account.
            $balanceReduction = round(min($creditTotal, (float) $original->balance_due), 2);
            $creditToCustomer = round($creditTotal - $balanceReduction, 2);

            $newBalance = round((float) $original->balance_due - $balanceReduction, 2);
            $original->update([
                'balance_due' => max(0, $newBalance),
                'status'      => $newBalance <= 0 ? 'paid' : $original->status,
            ]);

            if ($creditToCustomer > 0 && $original->customer) {
                $original->customer->increment('credit_balance', $creditToCustomer);
            }

            $this->postSalesReturnJournal($creditNote, $original, $request->user()->id);
            $this->accounting->postCogsReversalJournal($creditNote, $original->branch_id, $returnedCost, $request->user()->id);

            return response()->json([
                'message'            => 'Sales return recorded.',
                'credit_note'        => $creditNote->load(['items.product', 'customer', 'originalInvoice']),
                'balance_reduction'  => $balanceReduction,
                'credit_to_customer' => $creditToCustomer,
            ], 201);
        }, 5);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        if ($invoice->type !== 'credit_note') {
            return response()->json(['message' => 'Not a sales return.'], 404);
        }
        return response()->json($invoice->load(['items.product', 'customer', 'branch', 'createdBy', 'originalInvoice']));
    }

    /** Quantities already returned per product for an invoice, across all its credit notes. */
    private function returnedQuantities(int $invoiceId)
    {
        return InvoiceItem::whereIn('invoice_id', Invoice::where('type', 'credit_note')
                ->where('original_invoice_id', $invoiceId)->pluck('id'))
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');
    }

    private function postSalesReturnJournal(Invoice $creditNote, Invoice $original, int $userId): void
    {
        try {
            $branchId  = $creditNote->branch_id;
            $returnsId = $this->accounting->getAccountId($branchId, 'acc_sales_returns')
                         ?: $this->accounting->getAccountId($branchId, 'acc_sales_revenue');
            $arId      = $original->customer?->account_id
                         ?: $this->accounting->getAccountId($branchId, 'acc_trade_receivables');
            $taxId     = $this->accounting->getAccountId($branchId, 'acc_tax_payable');

            if (!$returnsId || !$arId) return;

            if (JournalEntry::where('reference_type', 'invoice')
                    ->where('reference_id', $creditNote->id)->where('type', 'sales_return')->exists()) return;

            $total = (float) $creditNote->total;
            $tax   = min((float) $creditNote->tax_amount, $total);
            $net   = $total - ($taxId ? $tax : 0);

            $lines = [
                ['account_id' => $returnsId, 'debit' => $net, 'credit' => 0, 'description' => "Sales Return {$creditNote->invoice_number}"],
            ];
            if ($taxId && $tax > 0) {
                $lines[] = ['account_id' => $taxId, 'debit' => $tax, 'credit' => 0, 'description' => "Tax reversal – {$creditNote->invoice_number}"];
            }
            $lines[] = ['account_id' => $arId, 'debit' => 0, 'credit' => $total, 'description' => "Credit note {$creditNote->invoice_number} against {$original->invoice_number}"];

            $this->accounting->createEntry(
                $branchId, 'sales_return',
                "Sales Return {$creditNote->invoice_number} against {$original->invoice_number}",
                $creditNote->invoice_date->toDateString(), $lines, $userId, 'invoice', $creditNote->id
            );
        } catch (\Throwable $e) {
            Log::error('postSalesReturnJournal failed', ['credit_note_id' => $creditNote->id, 'error' => $e->getMessage()]);
        }
    }
}
