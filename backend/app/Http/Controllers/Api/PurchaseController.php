<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceiptNote;
use App\Models\GrnItem;
use App\Models\JournalEntry;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use App\Models\Supplier;
use App\Models\Cheque;
use App\Services\AccountingService;
use App\Services\BranchContextService;
use App\Services\NumberGeneratorService;
use App\Services\StockService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    public function __construct(
        private BranchContextService $branchContext,
        private NumberGeneratorService $numbers,
        private StockService $stockService,
        private AccountingService $accounting
    ) {}

    /**
     * Mark a customer-received cheque as transferred to a supplier.
     * Locks the row and requires status=in_hand to prevent the same cheque being spent twice.
     */
    private function useReceivedCheque(int $chequeId, string $usageNote): Cheque
    {
        $cheque = Cheque::where('id', $chequeId)->where('status', 'in_hand')->lockForUpdate()->first();
        if (!$cheque) {
            abort(422, 'Selected cheque is not available — it may already be used, deposited, or does not exist.');
        }
        $cheque->update(['status' => 'transferred', 'notes' => $usageNote]);
        return $cheque;
    }

    // Purchase Orders
    public function indexPO(Request $request): JsonResponse
    {
        $statsQuery = PurchaseOrder::query();
        $this->branchContext->applyScope($statsQuery);
        $stats = [
            'total_count'  => (clone $statsQuery)->count(),
            'total_amount' => (clone $statsQuery)->sum('total'),
            'paid_amount'  => (clone $statsQuery)->sum('paid_amount'),
            'balance_due'  => (clone $statsQuery)->sum('balance_due'),
        ];

        $q = PurchaseOrder::query();
        $this->branchContext->applyScope($q);
        if ($request->status) $q->where('status', $request->status);
        if ($request->supplier_id) $q->where('supplier_id', $request->supplier_id);
        if ($request->payment_status) $q->where('payment_status', $request->payment_status);
        if ($request->search) {
            $q->where(fn($qq) => $qq->where('po_number', 'like', "%{$request->search}%")
                ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$request->search}%"))
            );
        }
        $paginated = $q->with(['supplier', 'branch', 'createdBy', 'grns.items.product'])->latest()->paginate($request->input('per_page', 20));
        return response()->json(array_merge($paginated->toArray(), ['stats' => $stats]));
    }

    public function storePO(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id'            => 'required|exists:branches,id',
            'supplier_id'          => 'required|exists:suppliers,id',
            'order_date'           => 'required|date',
            'invoice_date'         => 'nullable|date',
            'expected_date'        => 'nullable|date',
            'due_date'             => 'nullable|date',
            'reference'            => 'nullable|string|max:100',
            'supplier_invoice_ref' => 'nullable|string|max:100',
            'payment_method'       => 'nullable|in:on_account,cash,bank_transfer,cheque',
            'payment_terms_days'   => 'nullable|integer|min:0',
            'account_id'         => 'nullable|exists:accounts,id',
            'cheque_type'        => 'nullable|in:received,issued',
            'received_cheque_id' => 'nullable|exists:cheques,id',
            'cheque_number'      => 'nullable|string|max:50',
            'cheque_bank_name'   => 'nullable|string|max:100',
            'cheque_date'        => 'nullable|date',
            'notes'              => 'nullable|string',
            'terms'              => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.service_id' => 'nullable|exists:services,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_percent'=> 'nullable|numeric|min:0',
        ]);

        foreach ($data['items'] as $i => $item) {
            if (empty($item['product_id']) === empty($item['service_id'])) {
                return response()->json(['message' => "Item #".($i + 1).": choose exactly one of product or service."], 422);
            }
        }

        return DB::transaction(function () use ($data, $request) {
            $subtotal = 0;
            $taxAmount = 0;

            foreach ($data['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $lineTax = $lineTotal * (($item['tax_percent'] ?? 0) / 100);
                $subtotal += $lineTotal;
                $taxAmount += $lineTax;
            }

            $total = $subtotal + $taxAmount;
            $po = PurchaseOrder::create([
                'branch_id'            => $data['branch_id'],
                'supplier_id'          => $data['supplier_id'],
                'created_by'           => $request->user()->id,
                'po_number'            => $this->numbers->poNumber($data['branch_id']),
                'status'               => 'confirmed',
                'order_date'           => $data['order_date'],
                'invoice_date'         => $data['invoice_date'] ?? null,
                'expected_date'        => $data['expected_date'] ?? null,
                'due_date'             => $data['due_date'] ?? null,
                'subtotal'             => $subtotal,
                'tax_amount'           => $taxAmount,
                'total'                => $total,
                'paid_amount'          => 0,
                'balance_due'          => $total,
                'payment_status'       => 'unpaid',
                'notes'                => $data['notes'] ?? null,
                'terms'                => $data['terms'] ?? null,
                'reference'            => $data['reference'] ?? null,
                'supplier_invoice_ref' => $data['supplier_invoice_ref'] ?? null,
                'payment_method'       => $data['payment_method'] ?? 'on_account',
                'payment_terms_days'   => $data['payment_terms_days'] ?? 0,
                'account_id'           => $data['account_id'] ?? null,
                'cheque_type'          => $data['cheque_type'] ?? null,
                'received_cheque_id'   => $data['received_cheque_id'] ?? null,
                'cheque_number'        => $data['cheque_number'] ?? null,
                'cheque_bank_name'     => $data['cheque_bank_name'] ?? null,
                'cheque_date'          => $data['cheque_date'] ?? null,
            ]);

            $grn = GoodsReceiptNote::create([
                'branch_id'        => $po->branch_id,
                'supplier_id'      => $po->supplier_id,
                'purchase_order_id'=> $po->id,
                'created_by'       => $request->user()->id,
                'grn_number'       => $this->numbers->grnNumber($po->branch_id),
                'status'           => 'draft',
                'received_date'    => $data['order_date'],
                'notes'            => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $lineTax = $lineTotal * (($item['tax_percent'] ?? 0) / 100);

                // A service line has nothing to receive into inventory — create
                // the PO line item only, and skip the GRN item entirely below.
                if (!empty($item['service_id'])) {
                    $service = \App\Models\Service::find($item['service_id']);
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'service_id'   => $item['service_id'],
                        'item_type'    => 'service',
                        'product_name' => $service->name,
                        'product_code' => $service->code,
                        'unit'         => $service->unit ?: 'service',
                        'quantity'     => $item['quantity'],
                        'unit_price'   => $item['unit_price'],
                        'tax_percent'  => $item['tax_percent'] ?? 0,
                        'tax_amount'   => $lineTax,
                        'total'        => $lineTotal + $lineTax,
                    ]);
                    continue;
                }

                $product = \App\Models\Product::find($item['product_id']);
                $poItem = PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id'   => $item['product_id'],
                    'item_type'    => 'product',
                    'product_name' => $product->name,
                    'product_code' => $product->code,
                    'unit'         => $product->unit,
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $item['unit_price'],
                    'tax_percent'  => $item['tax_percent'] ?? 0,
                    'tax_amount'   => $lineTax,
                    'total'        => $lineTotal + $lineTax,
                ]);
                GrnItem::create([
                    'grn_id'            => $grn->id,
                    'po_item_id'        => $poItem->id,
                    'product_id'        => $item['product_id'],
                    'product_name'      => $product->name,
                    'unit'              => $product->unit,
                    'quantity_ordered'  => $item['quantity'],
                    'quantity_received' => $item['quantity'],
                    'unit_cost'         => $item['unit_price'],
                    'total_cost'        => $item['quantity'] * $item['unit_price'],
                ]);
            }

            // Journal: DR Inventory/Purchases, CR Accounts Payable — recognizes
            // the liability the moment the invoice is recorded, not whenever
            // (or whether) someone later confirms the draft GRN via "Receive
            // Items". Confirming the GRN only moves stock quantities/batches
            // from here on (see confirmGRN) — it no longer re-posts this same
            // amount, except for the delta if what's confirmed differs from
            // what was originally invoiced.
            if ($total > 0) {
                try {
                    $this->accounting->ensureSupplierAccount($po->supplier);
                    $po->supplier->refresh();
                    $apId = $po->supplier->account_id
                         ?: $this->accounting->getAccountId($po->branch_id, 'acc_trade_payables');
                    $inventoryId = $this->accounting->getAccountId($po->branch_id, 'acc_inventory')
                                 ?: $this->accounting->getAccountId($po->branch_id, 'acc_purchases');
                    if ($apId && $inventoryId) {
                        $this->accounting->createEntry(
                            $po->branch_id, 'purchase',
                            "Invoice received – {$po->po_number}",
                            $data['order_date'],
                            [
                                ['account_id' => $inventoryId, 'debit' => $total, 'credit' => 0,      'description' => "Invoice – {$po->po_number}"],
                                ['account_id' => $apId,        'debit' => 0,      'credit' => $total, 'description' => "AP – {$po->po_number}"],
                            ],
                            $request->user()->id, 'purchase_order', $po->id
                        );
                    }
                } catch (\Throwable $e) {
                    Log::error('storePO liability journal failed', ['po_id' => $po->id, 'error' => $e->getMessage()]);
                }
            }

            // Auto-record payment if method is not on_account
            $paymentMethod = $data['payment_method'] ?? 'on_account';
            if ($paymentMethod !== 'on_account' && $total > 0) {
                $chequeId   = null;
                $chequeType = $data['cheque_type'] ?? 'issued';
                $paidAmount = $total;

                if ($paymentMethod === 'cheque') {
                    if ($chequeType === 'received' && !empty($data['received_cheque_id'])) {
                        $heldCheque = $this->useReceivedCheque(
                            (int) $data['received_cheque_id'],
                            "Used to pay supplier: {$po->supplier->name} · Invoice {$po->po_number} · {$data['order_date']}"
                        );
                        $chequeId   = $heldCheque->id;
                        $paidAmount = min($total, (float) $heldCheque->amount);
                    } else {
                        $cheque = Cheque::create([
                            'branch_id'            => $po->branch_id,
                            'created_by'           => $request->user()->id,
                            'direction'            => 'issued',
                            'party_id'             => $po->supplier_id,
                            'party_type'           => 'supplier',
                            'cheque_number'        => $data['cheque_number'] ?? null,
                            'bank_name'            => $data['cheque_bank_name'] ?? null,
                            'cheque_date'          => $data['cheque_date'] ?? null,
                            'received_issued_date' => $data['order_date'],
                            'amount'               => $total,
                            'status'               => 'in_hand',
                        ]);
                        $chequeId = $cheque->id;
                    }
                }

                SupplierPayment::create([
                    'purchase_order_id' => $po->id,
                    'branch_id'         => $po->branch_id,
                    'created_by'        => $request->user()->id,
                    'amount'            => $paidAmount,
                    'payment_method'    => $paymentMethod,
                    'payment_date'      => $data['order_date'],
                    'account_id'        => $data['account_id'] ?? null,
                    'cheque_id'         => $chequeId,
                    'cheque_type'       => $chequeType,
                ]);

                $po->update([
                    'paid_amount'    => $paidAmount,
                    'balance_due'    => max(0, $total - $paidAmount),
                    'payment_status' => $paidAmount >= $total ? 'paid' : 'partially_paid',
                ]);

                // Journal: DR AP, CR Cash/Bank
                try {
                    $branchId = $po->branch_id;
                    $po->load('supplier');
                    $this->accounting->ensureSupplierAccount($po->supplier);
                    $po->supplier->refresh();
                    $apId   = $po->supplier->account_id
                           ?: $this->accounting->getAccountId($branchId, 'acc_trade_payables');
                    $cashId = !empty($data['account_id'])
                           ? (int) $data['account_id']
                           : ($this->accounting->getAccountId($branchId, 'acc_cash')
                              ?: $this->accounting->getAccountId($branchId, 'acc_bank'));
                    if ($apId && $cashId) {
                        $this->accounting->createEntry(
                            $branchId, 'payment_made',
                            "Payment – {$po->po_number}",
                            $data['order_date'],
                            [
                                ['account_id' => $apId,   'debit' => $paidAmount,  'credit' => 0,      'description' => "AP – {$po->po_number}"],
                                ['account_id' => $cashId, 'debit' => 0,       'credit' => $paidAmount, 'description' => "Payment – {$po->po_number}"],
                            ],
                            $request->user()->id, 'purchase_order', $po->id
                        );
                    }
                } catch (\Throwable $e) {
                    Log::error('storePO auto-payment journal failed', ['po_id' => $po->id, 'error' => $e->getMessage()]);
                }
            }

            return response()->json($po->fresh(['items.product', 'supplier', 'branch', 'payments']), 201);
        }, 5);
    }

    public function showPO(PurchaseOrder $purchaseOrder): JsonResponse
    {
        return response()->json($purchaseOrder->load(['items.product', 'supplier', 'branch', 'grns.items.product', 'payments.cheque']));
    }

    /**
     * Whether this invoice's line items (and therefore its delete-ability) are
     * still safe to change: no payment has been recorded against it yet, and
     * its goods receipt is still a draft (nothing has hit inventory or posted
     * an AP journal entry — see confirmGRN/receiveGRN, not here).
     */
    private function poIsEditableFreely(PurchaseOrder $purchaseOrder): bool
    {
        return (float) $purchaseOrder->paid_amount <= 0
            && !$purchaseOrder->grns()->where('status', '!=', 'draft')->exists();
    }

    public function updatePO(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $data = $request->validate([
            'supplier_id'          => 'sometimes|exists:suppliers,id',
            'order_date'           => 'sometimes|date',
            'expected_date'        => 'nullable|date',
            'due_date'             => 'nullable|date',
            'reference'            => 'nullable|string|max:100',
            'supplier_invoice_ref' => 'nullable|string|max:100',
            'payment_terms_days'   => 'nullable|integer|min:0',
            'notes'                => 'nullable|string',
            'terms'                => 'nullable|string',
            'items'                => 'sometimes|array|min:1',
            'items.*.product_id'   => 'nullable|exists:products,id',
            'items.*.service_id'   => 'nullable|exists:services,id',
            'items.*.quantity'     => 'required_with:items|numeric|min:0.01',
            'items.*.unit_price'   => 'required_with:items|numeric|min:0',
            'items.*.tax_percent'  => 'nullable|numeric|min:0',
        ]);

        if (isset($data['items'])) {
            if (!$this->poIsEditableFreely($purchaseOrder)) {
                return response()->json(['message' => "Items can't be changed once this invoice has a payment or a confirmed receipt."], 422);
            }
            foreach ($data['items'] as $i => $item) {
                if (empty($item['product_id']) === empty($item['service_id'])) {
                    return response()->json(['message' => "Item #".($i + 1).": choose exactly one of product or service."], 422);
                }
            }
        }

        return DB::transaction(function () use ($data, $purchaseOrder) {
            $purchaseOrder->update(collect($data)->only([
                'supplier_id', 'order_date', 'expected_date', 'due_date',
                'reference', 'supplier_invoice_ref', 'payment_terms_days', 'notes', 'terms',
            ])->toArray());

            if (isset($data['items'])) {
                // Replace the line items outright — safe only because
                // poIsEditableFreely() above already guarantees no payment and
                // no confirmed receipt exist yet for this invoice.
                $purchaseOrder->items()->delete();
                $grn = $purchaseOrder->grns()->where('status', 'draft')->first();
                $grn?->items()->delete();

                $subtotal = 0;
                $taxAmount = 0;

                foreach ($data['items'] as $item) {
                    $lineTotal = $item['quantity'] * $item['unit_price'];
                    $lineTax = $lineTotal * (($item['tax_percent'] ?? 0) / 100);
                    $subtotal += $lineTotal;
                    $taxAmount += $lineTax;

                    if (!empty($item['service_id'])) {
                        $service = \App\Models\Service::find($item['service_id']);
                        PurchaseOrderItem::create([
                            'purchase_order_id' => $purchaseOrder->id,
                            'service_id'   => $item['service_id'],
                            'item_type'    => 'service',
                            'product_name' => $service->name,
                            'product_code' => $service->code,
                            'unit'         => $service->unit ?: 'service',
                            'quantity'     => $item['quantity'],
                            'unit_price'   => $item['unit_price'],
                            'tax_percent'  => $item['tax_percent'] ?? 0,
                            'tax_amount'   => $lineTax,
                            'total'        => $lineTotal + $lineTax,
                        ]);
                        continue;
                    }

                    $product = \App\Models\Product::find($item['product_id']);
                    $poItem = PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id'   => $item['product_id'],
                        'item_type'    => 'product',
                        'product_name' => $product->name,
                        'product_code' => $product->code,
                        'unit'         => $product->unit,
                        'quantity'     => $item['quantity'],
                        'unit_price'   => $item['unit_price'],
                        'tax_percent'  => $item['tax_percent'] ?? 0,
                        'tax_amount'   => $lineTax,
                        'total'        => $lineTotal + $lineTax,
                    ]);

                    if ($grn) {
                        GrnItem::create([
                            'grn_id'            => $grn->id,
                            'po_item_id'        => $poItem->id,
                            'product_id'        => $item['product_id'],
                            'product_name'      => $product->name,
                            'unit'              => $product->unit,
                            'quantity_ordered'  => $item['quantity'],
                            'quantity_received' => $item['quantity'],
                            'unit_cost'         => $item['unit_price'],
                            'total_cost'        => $item['quantity'] * $item['unit_price'],
                        ]);
                    }
                }

                $total = $subtotal + $taxAmount;
                $purchaseOrder->update([
                    'subtotal'    => $subtotal,
                    'tax_amount'  => $taxAmount,
                    'total'       => $total,
                    'balance_due' => max(0, $total - $purchaseOrder->paid_amount),
                ]);
            }

            return response()->json($purchaseOrder->fresh()->load(['items', 'supplier']));
        });
    }

    public function destroyPO(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ((float) $purchaseOrder->paid_amount > 0) {
            return response()->json(['message' => "Can't delete an invoice that already has a payment recorded. Remove the payment first."], 422);
        }
        if ($purchaseOrder->grns()->where('status', '!=', 'draft')->exists()) {
            return response()->json(['message' => "Can't delete an invoice whose goods receipt has already been confirmed."], 422);
        }

        DB::transaction(function () use ($purchaseOrder) {
            foreach ($purchaseOrder->grns as $grn) {
                $grn->items()->delete();
                $grn->delete();
            }
            $purchaseOrder->items()->delete();
            $purchaseOrder->delete();
        });

        return response()->json(['message' => 'Supplier invoice deleted.']);
    }

    public function pdfPO(PurchaseOrder $purchaseOrder)
    {
        $po = $purchaseOrder->load(['items', 'supplier', 'branch', 'createdBy']);
        $pdf = Pdf::loadView('pdf.purchase-order', ['po' => $po]);
        return $pdf->download("purchase-order-{$po->po_number}.pdf");
    }

    /**
     * Delete a wrongly-recorded supplier payment (e.g. wrong cheque amount entered).
     * Reverses the PO balance/status and, for a cheque payment, undoes the cheque:
     * an 'issued' cheque (created fresh for this payment) is deleted outright; a
     * 'received' cheque (a customer's cheque that was handed over to the supplier)
     * is restored to 'in_hand' rather than deleted, since it still belongs to that
     * customer-cheque pool. Only safe while the cheque hasn't moved any further.
     * Posts an offsetting journal entry rather than editing history.
     */
    public function deletePOPayment(Request $request, PurchaseOrder $purchaseOrder, SupplierPayment $payment): JsonResponse
    {
        if ((int) $payment->purchase_order_id !== (int) $purchaseOrder->id) {
            return response()->json(['message' => 'Payment does not belong to this purchase order.'], 404);
        }
        if ($purchaseOrder->status === 'cancelled') {
            return response()->json(['message' => 'Cannot modify payments on a cancelled purchase order.'], 422);
        }

        return DB::transaction(function () use ($purchaseOrder, $payment, $request) {
            $cheque = $payment->cheque_id ? Cheque::find($payment->cheque_id) : null;

            if ($cheque) {
                $isReceived = $payment->cheque_type === 'received';
                $expected   = $isReceived ? 'transferred' : 'in_hand';
                if ($cheque->status !== $expected) {
                    return response()->json([
                        'message' => "This payment's cheque has already been {$cheque->status} — reverse the cheque status in Manage Cheque first, then delete the payment.",
                    ], 422);
                }
            }

            // 1. Reverse the purchase order
            $newPaid    = max(0, (float) $purchaseOrder->paid_amount - (float) $payment->amount);
            $newBalance = max(0, (float) $purchaseOrder->total - $newPaid);
            $purchaseOrder->update([
                'paid_amount'    => $newPaid,
                'balance_due'    => $newBalance,
                'payment_status' => $newPaid <= 0.001 ? 'unpaid' : 'partially_paid',
            ]);

            // 2. Undo the cheque
            if ($cheque) {
                if ($payment->cheque_type === 'received') {
                    $cheque->update(['status' => 'in_hand']);
                } else {
                    $stillUsed = $cheque->supplierPayments()->where('id', '!=', $payment->id)->exists()
                        || $cheque->purchaseOrders()->exists()
                        || $cheque->invoiceLinks()->exists()
                        || $cheque->expenses()->exists();
                    if (!$stillUsed) {
                        $cheque->delete();
                    }
                }
            }

            // 3. Offsetting journal entry — history stays intact, this just nets it to zero
            try {
                $branchId = $purchaseOrder->branch_id;
                $purchaseOrder->loadMissing('supplier');
                $apId   = $purchaseOrder->supplier?->account_id
                       ?: $this->accounting->getAccountId($branchId, 'acc_trade_payables');
                $cashId = $payment->account_id
                       ?: ($this->accounting->getAccountId($branchId, 'acc_cash')
                           ?: $this->accounting->getAccountId($branchId, 'acc_bank'));
                $amount = (float) $payment->amount;
                if ($apId && $cashId && $amount > 0) {
                    $this->accounting->createEntry(
                        $branchId, 'payment_reversed',
                        "Payment reversed – {$purchaseOrder->po_number}" . ($cheque ? " (cheque {$cheque->cheque_number})" : ''),
                        now()->toDateString(),
                        [
                            ['account_id' => $cashId, 'debit' => $amount, 'credit' => 0,      'description' => "Payment reversal – {$purchaseOrder->po_number}"],
                            ['account_id' => $apId,   'debit' => 0,       'credit' => $amount, 'description' => "Payment reversal – {$purchaseOrder->po_number}"],
                        ],
                        $request->user()->id, 'purchase_order', $purchaseOrder->id
                    );
                }
            } catch (\Throwable $e) {
                Log::error('deletePOPayment journal failed', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
            }

            $payment->delete();

            return response()->json([
                'message' => 'Payment deleted and reversed.',
                'po'      => $purchaseOrder->fresh(['items.product', 'supplier', 'branch', 'grns', 'payments.cheque']),
            ]);
        });
    }

    public function approvePO(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->update([
            'status' => 'confirmed',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);
        return response()->json($purchaseOrder->fresh());
    }

    // GRN
    public function indexGRN(Request $request): JsonResponse
    {
        $q = GoodsReceiptNote::query();
        $this->branchContext->applyScope($q);
        if ($request->status) $q->where('status', $request->status);
        return response()->json($q->with(['supplier', 'branch', 'purchaseOrder'])->latest()->paginate($request->input('per_page', 20)));
    }

    public function showGRN(GoodsReceiptNote $goodsReceiptNote): JsonResponse
    {
        return response()->json($goodsReceiptNote->load([
            'branch', 'supplier', 'purchaseOrder.supplier',
            'items.product', 'supplierInvoice',
        ]));
    }

    public function storeGRN(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'purchase_order_id' => 'nullable|exists:purchase_orders,id',
            'received_date' => 'required|date',
            'delivery_note_number' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'nullable|exists:purchase_order_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_received' => 'required|numeric|min:0.01',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.batch_number' => 'nullable|string',
            'items.*.expiry_date' => 'nullable|date',
        ]);

        return DB::transaction(function () use ($data, $request) {
            $grn = GoodsReceiptNote::create([
                'branch_id' => $data['branch_id'],
                'supplier_id' => $data['supplier_id'],
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'created_by' => $request->user()->id,
                'grn_number' => $this->numbers->grnNumber($data['branch_id']),
                'status' => 'draft',
                'received_date' => $data['received_date'],
                'delivery_note_number' => $data['delivery_note_number'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $product = \App\Models\Product::find($item['product_id']);
                GrnItem::create([
                    'grn_id'      => $grn->id,
                    'po_item_id'  => $item['purchase_order_item_id'] ?? null,
                    'product_id'  => $item['product_id'],
                    'product_name'=> $product->name,
                    'unit'        => $product->unit,
                    'quantity_received' => $item['quantity_received'],
                    'unit_cost'   => $item['unit_cost'],
                    'total_cost'  => $item['quantity_received'] * $item['unit_cost'],
                    'batch_number'=> $item['batch_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);
            }

            return response()->json($grn->load(['items.product', 'supplier', 'branch']), 201);
        }, 5);
    }

    public function confirmGRN(Request $request, GoodsReceiptNote $goodsReceiptNote): JsonResponse
    {
        if ($goodsReceiptNote->status !== 'draft') {
            return response()->json(['message' => 'GRN already confirmed.'], 422);
        }

        $request->validate([
            'items.*.product_id'            => 'nullable|exists:products,id',
            'new_items'                      => 'nullable|array',
            'new_items.*.product_id'        => 'required|exists:products,id',
            'new_items.*.quantity_received' => 'required|numeric|min:0.01',
            'new_items.*.unit_cost'         => 'required|numeric|min:0',
            'new_items.*.batch_number'      => 'nullable|string',
            'new_items.*.expiry_date'       => 'nullable|date',
            'new_items.*.selling_price'     => 'nullable|numeric|min:0',
            'removed_grn_item_ids'          => 'nullable|array',
            'removed_grn_item_ids.*'        => 'integer|exists:grn_items,id',
        ]);

        return DB::transaction(function () use ($goodsReceiptNote, $request) {
            $goodsReceiptNote->load('items');

            // Remove a line the user says wasn't actually delivered — the GRN
            // is still draft, nothing posted yet, so deleting it outright is
            // safe. Its po_item_id (if any) simply never gets credited below.
            $removedIds = $request->input('removed_grn_item_ids', []);
            if (!empty($removedIds)) {
                GrnItem::where('grn_id', $goodsReceiptNote->id)->whereIn('id', $removedIds)->delete();
                $goodsReceiptNote->load('items');
            }
            if ($goodsReceiptNote->items->isEmpty()) {
                return response()->json(['message' => 'A GRN needs at least one item — add one back or cancel instead.'], 422);
            }

            // Apply per-item edits submitted from the receive modal
            $itemUpdates = collect($request->input('items', []));
            $sellingPrices = []; // grn_item_id => this batch's own selling price (not persisted on grn_items)
            foreach ($goodsReceiptNote->items as $item) {
                $edit = $itemUpdates->firstWhere('grn_item_id', $item->id);
                if ($edit) {
                    $qty  = isset($edit['quantity_received']) ? (float) $edit['quantity_received'] : $item->quantity_received;
                    $cost = isset($edit['unit_cost'])         ? (float) $edit['unit_cost']         : $item->unit_cost;
                    $updateData = [
                        'quantity_received' => $qty,
                        'unit_cost'         => $cost,
                        'total_cost'        => $qty * $cost,
                        'batch_number'      => $edit['batch_number'] ?? $item->batch_number,
                        'expiry_date'       => $edit['expiry_date']  ?? $item->expiry_date,
                    ];

                    // Supplier substituted a different product for this line.
                    // po_item_id is deliberately left untouched — it still
                    // correctly links to the ORIGINAL ordered PO line even
                    // after a substitution, so PO-received-qty reconciliation
                    // below still credits what was actually ordered.
                    if (!empty($edit['product_id']) && (int) $edit['product_id'] !== (int) $item->product_id) {
                        $newProduct = \App\Models\Product::find($edit['product_id']);
                        if ($newProduct) {
                            $updateData['product_id']   = $newProduct->id;
                            $updateData['product_name'] = $newProduct->name;
                            $updateData['unit']         = $newProduct->unit;
                        }
                    }

                    $item->update($updateData);
                    $item->refresh();

                    // A blank selling price means "keep the product's current
                    // selling price" for this batch, same convention as before.
                    if (!empty($edit['selling_price'])) {
                        $sellingPrices[$item->id] = (float) $edit['selling_price'];
                        \App\Models\Product::where('id', $item->product_id)
                            ->update(['selling_price' => (float) $edit['selling_price']]);
                    }
                }
            }

            // Brand-new lines the receiver adds for goods delivered that
            // weren't on the original PO at all — not tied to any po_item_id.
            foreach ($request->input('new_items', []) as $newItem) {
                $newProduct = \App\Models\Product::find($newItem['product_id']);
                if (!$newProduct) continue;
                $qty  = (float) $newItem['quantity_received'];
                $cost = (float) $newItem['unit_cost'];
                $created = GrnItem::create([
                    'grn_id'            => $goodsReceiptNote->id,
                    'po_item_id'        => null,
                    'product_id'        => $newProduct->id,
                    'product_name'      => $newProduct->name,
                    'unit'              => $newProduct->unit,
                    'quantity_ordered'  => 0,
                    'quantity_received' => $qty,
                    'unit_cost'         => $cost,
                    'total_cost'        => $qty * $cost,
                    'batch_number'      => $newItem['batch_number'] ?? null,
                    'expiry_date'       => $newItem['expiry_date'] ?? null,
                ]);

                if (!empty($newItem['selling_price'])) {
                    $sellingPrices[$created->id] = (float) $newItem['selling_price'];
                    \App\Models\Product::where('id', $newProduct->id)
                        ->update(['selling_price' => (float) $newItem['selling_price']]);
                }
            }

            $goodsReceiptNote->refresh()->load('items');

            foreach ($goodsReceiptNote->items as $item) {
                $sellingPrice = $sellingPrices[$item->id]
                    ?? (float) (\App\Models\Product::where('id', $item->product_id)->value('selling_price') ?? 0);

                $this->stockService->receiveBatch(
                    $item->product_id,
                    $goodsReceiptNote->branch_id,
                    $item->quantity_received,
                    $item->unit_cost,
                    $sellingPrice,
                    'purchase_in',
                    'grn',
                    $goodsReceiptNote->id,
                    $request->user()->id,
                    $goodsReceiptNote->received_date->toDateString(),
                    $item->batch_number,
                    $item->expiry_date?->toDateString(),
                    'grn',
                    $item->id
                );
            }

            $goodsReceiptNote->update([
                'status' => 'confirmed',
                'confirmed_by' => $request->user()->id,
                'confirmed_at' => now(),
            ]);

            // Update PO received quantities — matched by the stable po_item_id
            // link captured when the line was created, NOT by product_id, since
            // a supplier substitution means the delivered product may no longer
            // match what was actually ordered on that line.
            if ($goodsReceiptNote->purchase_order_id) {
                foreach ($goodsReceiptNote->items as $item) {
                    if (!$item->po_item_id) continue; // extra item, not tied to any ordered line
                    PurchaseOrderItem::where('id', $item->po_item_id)->increment('received_quantity', $item->quantity_received);
                }
                $po = PurchaseOrder::find($goodsReceiptNote->purchase_order_id);
                $allReceived = $po->items->every(fn($i) => $i->received_quantity >= $i->quantity);
                $po->update(['status' => $allReceived ? 'received' : 'partially_received']);
            }

            // Journal: a PO-linked GRN already had its full liability recognized
            // at invoice time (see storePO) — only the difference between what
            // was invoiced and what's actually being confirmed here (an edited
            // quantity/cost, or a brand-new line added during receiving) still
            // needs posting. A standalone GRN with no linked PO has no prior
            // invoice entry at all, so it posts its full confirmed total exactly
            // as before.
            try {
                $goodsReceiptNote->load('items');
                $confirmedTotal = $goodsReceiptNote->items->sum(fn($i) => $i->quantity_received * $i->unit_cost);
                $linkedPo = $goodsReceiptNote->purchase_order_id ? PurchaseOrder::find($goodsReceiptNote->purchase_order_id) : null;
                $amountToPost = $linkedPo ? round($confirmedTotal - (float) $linkedPo->total, 2) : round($confirmedTotal, 2);

                if ($amountToPost != 0) {
                    $branchId     = $goodsReceiptNote->branch_id;
                    $inventoryId  = $this->accounting->getAccountId($branchId, 'acc_inventory')
                                 ?: $this->accounting->getAccountId($branchId, 'acc_purchases');
                    $goodsReceiptNote->load('purchaseOrder.supplier', 'supplier');
                    $supplier     = $goodsReceiptNote->purchaseOrder?->supplier ?? $goodsReceiptNote->supplier ?? null;
                    if ($supplier) $this->accounting->ensureSupplierAccount($supplier);
                    $apId         = $supplier?->account_id
                                 ?: $this->accounting->getAccountId($branchId, 'acc_trade_payables');
                    if ($inventoryId && $apId) {
                        $entryType   = $linkedPo ? 'adjustment' : 'grn_confirmed';
                        $description = $linkedPo
                            ? "Receiving adjustment – {$goodsReceiptNote->grn_number}"
                            : "Goods Received – {$goodsReceiptNote->grn_number}";
                        $amount         = abs($amountToPost);
                        $debitAccount   = $amountToPost > 0 ? $inventoryId : $apId;
                        $creditAccount  = $amountToPost > 0 ? $apId : $inventoryId;
                        $this->accounting->createEntry(
                            $branchId, $entryType, $description,
                            $goodsReceiptNote->received_date->toDateString(),
                            [
                                ['account_id' => $debitAccount,  'debit' => $amount, 'credit' => 0,      'description' => $description],
                                ['account_id' => $creditAccount, 'debit' => 0,       'credit' => $amount, 'description' => $description],
                            ],
                            $request->user()->id, 'grn', $goodsReceiptNote->id
                        );
                    }
                }
            } catch (\Throwable $e) {
                Log::error('confirmGRN journal failed', ['grn_id' => $goodsReceiptNote->id, 'error' => $e->getMessage()]);
            }

            return response()->json(['message' => 'GRN confirmed and stock updated.']);
        });
    }

    /**
     * One-time, idempotent correction for invoices created before storePO()
     * started recognizing the AP liability at invoice time (see storePO):
     * every non-cancelled PO whose GRN was never confirmed never got a
     * liability entry under the old code path either, so its supplier's
     * ledger balance silently disagreed with reality (payments debited AP
     * with no matching credit ever posted). Posts the missing entry, dated
     * at the invoice's own order_date. Safe to call more than once — already
     * fixed or already-GRN-confirmed invoices are skipped.
     */
    public function backfillInvoiceLiability(Request $request): JsonResponse
    {
        $fixed = [];
        $skipped = 0;

        PurchaseOrder::with(['grns', 'supplier'])
            ->where('status', '!=', 'cancelled')
            ->chunkById(50, function ($orders) use (&$fixed, &$skipped, $request) {
                foreach ($orders as $po) {
                    if ((float) $po->total <= 0) { $skipped++; continue; }
                    // Already recognized the normal way — its GRN was confirmed,
                    // which already posted (at least) the full amount back then.
                    if ($po->grns->contains(fn ($g) => $g->status !== 'draft')) { $skipped++; continue; }
                    $already = JournalEntry::where('reference_type', 'purchase_order')
                        ->where('reference_id', $po->id)
                        ->where('type', 'purchase')
                        ->exists();
                    if ($already) { $skipped++; continue; }

                    try {
                        $this->accounting->ensureSupplierAccount($po->supplier);
                        $po->supplier->refresh();
                        $apId = $po->supplier->account_id
                             ?: $this->accounting->getAccountId($po->branch_id, 'acc_trade_payables');
                        $inventoryId = $this->accounting->getAccountId($po->branch_id, 'acc_inventory')
                                     ?: $this->accounting->getAccountId($po->branch_id, 'acc_purchases');
                        if (!$apId || !$inventoryId) { $skipped++; continue; }

                        $entry = $this->accounting->createEntry(
                            $po->branch_id, 'purchase',
                            "Invoice received (backfill) – {$po->po_number}",
                            $po->order_date->toDateString(),
                            [
                                ['account_id' => $inventoryId, 'debit' => (float) $po->total, 'credit' => 0,                     'description' => "Invoice – {$po->po_number}"],
                                ['account_id' => $apId,        'debit' => 0,                     'credit' => (float) $po->total, 'description' => "AP – {$po->po_number}"],
                            ],
                            $request->user()->id, 'purchase_order', $po->id
                        );

                        if ($entry) {
                            $fixed[] = ['po_number' => $po->po_number, 'amount' => (float) $po->total];
                        } else {
                            $skipped++;
                        }
                    } catch (\Throwable $e) {
                        Log::error('backfillInvoiceLiability failed', ['po_id' => $po->id, 'error' => $e->getMessage()]);
                        $skipped++;
                    }
                }
            });

        return response()->json([
            'message'     => 'Backfilled ' . count($fixed) . ' invoice(s), skipped ' . $skipped . '.',
            'fixed'       => $fixed,
            'fixed_count' => count($fixed),
            'skipped'     => $skipped,
        ]);
    }

    public function recordPOPayment(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $data = $request->validate([
            'amount'             => 'required|numeric|min:0.01',
            'payment_method'     => 'required|in:cash,cheque,bank_transfer',
            'payment_date'       => 'required|date',
            'reference_number'   => 'nullable|string',
            'account_id'         => 'nullable|exists:accounts,id',
            'cheque_type'        => 'nullable|in:received,issued',
            'received_cheque_id' => 'nullable|exists:cheques,id',
            'cheque_number'      => 'nullable|string',
            'bank_name'          => 'nullable|string',
            'cheque_date'        => 'nullable|date',
        ]);

        return DB::transaction(function () use ($data, $purchaseOrder, $request) {
            $chequeId   = null;
            $chequeType = $data['cheque_type'] ?? 'issued';
            $amountPaid = (float) $data['amount'];

            if ($data['payment_method'] === 'cheque') {
                if ($chequeType === 'received' && !empty($data['received_cheque_id'])) {
                    $heldCheque = $this->useReceivedCheque(
                        (int) $data['received_cheque_id'],
                        "Used to pay supplier: {$purchaseOrder->supplier->name} · Invoice {$purchaseOrder->po_number} · {$data['payment_date']}"
                    );
                    $chequeId = $heldCheque->id;
                    if ($amountPaid > (float) $heldCheque->amount) {
                        abort(422, 'Payment amount exceeds the selected cheque\'s value (Rs. ' . number_format((float) $heldCheque->amount, 2) . ').');
                    }
                } else {
                    $cheque = Cheque::create([
                        'branch_id'            => $purchaseOrder->branch_id,
                        'created_by'           => $request->user()->id,
                        'direction'            => 'issued',
                        'party_id'             => $purchaseOrder->supplier_id,
                        'party_type'           => 'supplier',
                        'cheque_number'        => $data['cheque_number'],
                        'bank_name'            => $data['bank_name'],
                        'cheque_date'          => $data['cheque_date'],
                        'received_issued_date' => $data['payment_date'],
                        'amount'               => $amountPaid,
                        'status'               => 'in_hand',
                    ]);
                    $chequeId = $cheque->id;
                }
            }

            // Anything beyond this invoice's own balance becomes standing credit
            // on the supplier (same mechanism as bulkPaymentPO's overpayment
            // handling) rather than being rejected outright.
            $cashForThis = min($amountPaid, (float) $purchaseOrder->balance_due);
            $excess = round($amountPaid - $cashForThis, 2);

            if ($cashForThis > 0) {
                SupplierPayment::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'branch_id'         => $purchaseOrder->branch_id,
                    'created_by'        => $request->user()->id,
                    'amount'            => $cashForThis,
                    'payment_method'    => $data['payment_method'],
                    'payment_date'      => $data['payment_date'],
                    'reference_number'  => $data['reference_number'] ?? null,
                    'cheque_id'         => $chequeId,
                    'account_id'        => $data['account_id'] ?? null,
                    'cheque_type'       => $chequeType,
                ]);

                $newPaid    = (float) $purchaseOrder->paid_amount + $cashForThis;
                $newBalance = max(0, (float) $purchaseOrder->total - $newPaid);
                $purchaseOrder->update([
                    'paid_amount'    => $newPaid,
                    'balance_due'    => $newBalance,
                    'payment_status' => $newBalance <= 0 ? 'paid' : 'partially_paid',
                ]);

                // DR Accounts Payable, CR Cash/Bank
                try {
                    $branchId = $purchaseOrder->branch_id;
                    $purchaseOrder->load('supplier');
                    $this->accounting->ensureSupplierAccount($purchaseOrder->supplier);
                    $purchaseOrder->supplier->refresh();
                    $apId   = $purchaseOrder->supplier->account_id
                           ?: $this->accounting->getAccountId($branchId, 'acc_trade_payables');
                    $cashId = !empty($data['account_id'])
                           ? (int) $data['account_id']
                           : ($this->accounting->getAccountId($branchId, 'acc_cash')
                              ?: $this->accounting->getAccountId($branchId, 'acc_bank'));
                    if ($apId && $cashId) {
                        $this->accounting->createEntry(
                            $branchId, 'payment_made',
                            "Payment – {$purchaseOrder->po_number}",
                            $data['payment_date'],
                            [
                                ['account_id' => $apId,   'debit' => $cashForThis, 'credit' => 0,           'description' => "AP – {$purchaseOrder->po_number}"],
                                ['account_id' => $cashId, 'debit' => 0,            'credit' => $cashForThis, 'description' => "Payment – {$purchaseOrder->po_number}"],
                            ],
                            $request->user()->id, 'purchase_order', $purchaseOrder->id
                        );
                    }
                } catch (\Throwable $e) {
                    Log::error('recordPOPayment journal failed', ['po_id' => $purchaseOrder->id, 'error' => $e->getMessage()]);
                }
            }

            if ($excess > 0) {
                $purchaseOrder->supplier()->increment('credit_balance', $excess);
            }

            $message = $excess > 0
                ? 'Payment recorded. Rs. ' . number_format($excess, 2) . ' extra added as supplier credit for future invoices.'
                : 'Payment recorded.';

            return response()->json(['message' => $message, 'po' => $purchaseOrder->fresh(['supplier', 'branch', 'payments'])]);
        });
    }

    /**
     * One cash/cheque/bank payment to a supplier, applied across several of their
     * outstanding purchase orders in one go — oldest order date first. Any leftover
     * after every selected PO is fully paid becomes standing supplier credit, same
     * as a single-PO overpayment.
     */
    public function bulkPaymentPO(Request $request, Supplier $supplier): JsonResponse
    {
        $data = $request->validate([
            'amount'             => 'required|numeric|min:0',
            'payment_method'     => 'required|in:cash,cheque,bank_transfer',
            'payment_date'       => 'required|date',
            'reference_number'   => 'nullable|string',
            'account_id'         => 'nullable|exists:accounts,id',
            'use_credit'         => 'nullable|boolean',
            'cheque_type'        => 'nullable|in:received,issued',
            'received_cheque_id' => 'nullable|exists:cheques,id',
            'cheque_number'      => 'nullable|string',
            'bank_name'          => 'nullable|string',
            'cheque_date'        => 'nullable|date',
            'purchase_order_ids' => 'nullable|array',
            'purchase_order_ids.*' => 'integer|exists:purchase_orders,id',
        ]);

        if ($data['amount'] <= 0 && empty($data['use_credit'])) {
            return response()->json(['message' => 'Enter a payment amount greater than zero.'], 422);
        }

        return DB::transaction(function () use ($data, $supplier, $request) {
            $query = PurchaseOrder::where('supplier_id', $supplier->id)
                ->where('balance_due', '>', 0)
                ->whereNotIn('status', ['cancelled'])
                ->orderBy('order_date')->orderBy('id');
            if (!empty($data['purchase_order_ids'])) {
                $query->whereIn('id', $data['purchase_order_ids']);
            }
            $orders = $query->get();

            if ($orders->isEmpty()) {
                return response()->json(['message' => 'No outstanding purchase orders to pay.'], 422);
            }

            $amountPaid = (float) $data['amount'];
            $chequeId   = null;
            $chequeType = $data['cheque_type'] ?? 'issued';

            if ($data['payment_method'] === 'cheque' && $amountPaid > 0) {
                if ($chequeType === 'received' && !empty($data['received_cheque_id'])) {
                    $heldCheque = $this->useReceivedCheque(
                        (int) $data['received_cheque_id'],
                        "Used to pay supplier: {$supplier->name} · Bulk payment · {$data['payment_date']}"
                    );
                    $chequeId = $heldCheque->id;
                    if ($amountPaid > (float) $heldCheque->amount) {
                        abort(422, 'Payment amount exceeds the selected cheque\'s value (Rs. ' . number_format((float) $heldCheque->amount, 2) . ').');
                    }
                } else {
                    $cheque = Cheque::create([
                        'branch_id'            => $supplier->branch_id,
                        'created_by'           => $request->user()->id,
                        'direction'            => 'issued',
                        'party_id'             => $supplier->id,
                        'party_type'           => 'supplier',
                        'cheque_number'        => $data['cheque_number'],
                        'bank_name'            => $data['bank_name'],
                        'cheque_date'          => $data['cheque_date'],
                        'received_issued_date' => $data['payment_date'],
                        'amount'               => $amountPaid,
                        'status'               => 'in_hand',
                    ]);
                    $chequeId = $cheque->id;
                }
            }

            $remainingCash   = $amountPaid;
            $remainingCredit = !empty($data['use_credit']) ? (float) $supplier->credit_balance : 0;
            $startingCredit  = $remainingCredit;
            $applied = [];

            foreach ($orders as $po) {
                if ($remainingCash <= 0 && $remainingCredit <= 0) break;

                $balanceDue    = (float) $po->balance_due;
                $creditForThis = min($remainingCredit, $balanceDue);
                $remainingCredit -= $creditForThis;
                $balanceDue      -= $creditForThis;

                $cashForThis = min($remainingCash, $balanceDue);
                $remainingCash -= $cashForThis;

                $totalForThis = round($creditForThis + $cashForThis, 2);
                if ($totalForThis <= 0) continue;

                SupplierPayment::create([
                    'purchase_order_id' => $po->id,
                    'branch_id'         => $po->branch_id,
                    'created_by'        => $request->user()->id,
                    'amount'            => $totalForThis,
                    'payment_method'    => $data['payment_method'],
                    'payment_date'      => $data['payment_date'],
                    'reference_number'  => $data['reference_number'] ?? null,
                    'cheque_id'         => $cashForThis > 0 ? $chequeId : null,
                    'account_id'        => $data['account_id'] ?? null,
                    'cheque_type'       => $chequeType,
                ]);

                $newPaid    = (float) $po->paid_amount + $totalForThis;
                $newBalance = max(0, (float) $po->total - $newPaid);
                $po->update([
                    'paid_amount'    => $newPaid,
                    'balance_due'    => $newBalance,
                    'payment_status' => $newBalance <= 0 ? 'paid' : 'partially_paid',
                ]);

                if ($cashForThis > 0) {
                    try {
                        $branchId = $po->branch_id;
                        $this->accounting->ensureSupplierAccount($supplier);
                        $supplier->refresh();
                        $apId   = $supplier->account_id
                               ?: $this->accounting->getAccountId($branchId, 'acc_trade_payables');
                        $cashId = !empty($data['account_id'])
                               ? (int) $data['account_id']
                               : ($this->accounting->getAccountId($branchId, 'acc_cash')
                                  ?: $this->accounting->getAccountId($branchId, 'acc_bank'));
                        if ($apId && $cashId) {
                            $this->accounting->createEntry(
                                $branchId, 'payment_made',
                                "Payment – {$po->po_number}",
                                $data['payment_date'],
                                [
                                    ['account_id' => $apId,   'debit' => $cashForThis, 'credit' => 0,          'description' => "AP – {$po->po_number}"],
                                    ['account_id' => $cashId, 'debit' => 0,            'credit' => $cashForThis, 'description' => "Payment – {$po->po_number}"],
                                ],
                                $request->user()->id, 'purchase_order', $po->id
                            );
                        }
                    } catch (\Throwable $e) {
                        Log::error('bulkPaymentPO journal failed', ['po_id' => $po->id, 'error' => $e->getMessage()]);
                    }
                }

                $applied[] = [
                    'purchase_order_id' => $po->id,
                    'po_number'         => $po->po_number,
                    'applied'           => $totalForThis,
                    'remaining_balance' => $newBalance,
                ];
            }

            $creditUsed = round($startingCredit - $remainingCredit, 2);
            if ($creditUsed > 0) {
                $supplier->decrement('credit_balance', $creditUsed);
            }
            if ($remainingCash > 0) {
                $supplier->increment('credit_balance', round($remainingCash, 2));
            }

            return response()->json([
                'message'        => 'Bulk payment applied across ' . count($applied) . ' purchase order(s).',
                'applied'        => $applied,
                'credit_used'    => $creditUsed,
                'credit_added'   => round($remainingCash, 2),
                'credit_balance' => (float) $supplier->fresh()->credit_balance,
            ]);
        });
    }

    // Supplier Invoices
    public function indexSupplierInvoices(Request $request): JsonResponse
    {
        $q = SupplierInvoice::query();
        $this->branchContext->applyScope($q);
        if ($request->status) $q->where('status', $request->status);
        if ($request->supplier_id) $q->where('supplier_id', $request->supplier_id);
        return response()->json($q->with(['supplier', 'branch', 'grn.items.product'])->latest()->paginate($request->input('per_page', 20)));
    }

    public function storeSupplierInvoice(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id'               => 'required|exists:branches,id',
            'supplier_id'             => 'required|exists:suppliers,id',
            'supplier_invoice_number' => 'nullable|string',
            'invoice_date'            => 'required|date',
            'due_date'                => 'nullable|date',
            'tax_amount'              => 'nullable|numeric|min:0',
            'discount_amount'         => 'nullable|numeric|min:0',
            'notes'                   => 'nullable|string',
            'items'                   => 'required|array|min:1',
            'items.*.product_id'      => 'required|exists:products,id',
            'items.*.quantity'        => 'required|numeric|min:0.01',
            'items.*.unit_cost'       => 'required|numeric|min:0',
            'items.*.batch_number'    => 'nullable|string',
            'items.*.expiry_date'     => 'nullable|date',
        ]);

        return DB::transaction(function () use ($data, $request) {
            $grn = GoodsReceiptNote::create([
                'branch_id'     => $data['branch_id'],
                'supplier_id'   => $data['supplier_id'],
                'created_by'    => $request->user()->id,
                'grn_number'    => $this->numbers->grnNumber($data['branch_id']),
                'status'        => 'draft',
                'received_date' => $data['invoice_date'],
                'notes'         => $data['notes'] ?? null,
            ]);

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $product  = \App\Models\Product::find($item['product_id']);
                $lineCost = (float) $item['quantity'] * (float) $item['unit_cost'];
                $subtotal += $lineCost;
                GrnItem::create([
                    'grn_id'            => $grn->id,
                    'product_id'        => $item['product_id'],
                    'product_name'      => $product->name,
                    'unit'              => $product->unit,
                    'quantity_received' => $item['quantity'],
                    'unit_cost'         => $item['unit_cost'],
                    'total_cost'        => $lineCost,
                    'batch_number'      => $item['batch_number'] ?? null,
                    'expiry_date'       => $item['expiry_date'] ?? null,
                ]);
            }

            $total = $subtotal + (float) ($data['tax_amount'] ?? 0) - (float) ($data['discount_amount'] ?? 0);

            $invoice = SupplierInvoice::create([
                'branch_id'               => $data['branch_id'],
                'supplier_id'             => $data['supplier_id'],
                'grn_id'                  => $grn->id,
                'created_by'              => $request->user()->id,
                'invoice_number'          => $this->numbers->supplierInvoiceNumber($data['branch_id']),
                'supplier_invoice_number' => $data['supplier_invoice_number'] ?? null,
                'invoice_date'            => $data['invoice_date'],
                'due_date'                => $data['due_date'] ?? null,
                'subtotal'                => $subtotal,
                'tax_amount'              => $data['tax_amount'] ?? 0,
                'discount_amount'         => $data['discount_amount'] ?? 0,
                'total'                   => $total,
                'paid_amount'             => 0,
                'balance_due'             => $total,
                'notes'                   => $data['notes'] ?? null,
            ]);

            return response()->json($invoice->load(['supplier', 'branch', 'grn.items.product']), 201);
        }, 5);
    }

    public function receiveItems(Request $request, SupplierInvoice $supplierInvoice): JsonResponse
    {
        $grn = $supplierInvoice->grn;
        if (!$grn) {
            return response()->json(['message' => 'No GRN linked to this invoice.'], 422);
        }
        if ($grn->status !== 'draft') {
            return response()->json(['message' => 'Items have already been received.'], 422);
        }
        return $this->confirmGRN($request, $grn);
    }

    public function recordSupplierPayment(Request $request, SupplierInvoice $supplierInvoice): JsonResponse
    {
        $data = $request->validate([
            'amount'             => 'required|numeric|min:0.01|max:' . $supplierInvoice->balance_due,
            'payment_method'     => 'required|in:cash,cheque,bank_transfer',
            'payment_date'       => 'required|date',
            'reference_number'   => 'nullable|string',
            'account_id'         => 'nullable|exists:accounts,id',
            'cheque_type'        => 'nullable|in:received,issued',
            'received_cheque_id' => 'nullable|exists:cheques,id',
            'cheque_number'      => 'nullable|string',
            'bank_name'          => 'nullable|string',
            'cheque_date'        => 'nullable|date',
        ]);

        return DB::transaction(function () use ($data, $supplierInvoice, $request) {
            $chequeId  = null;
            $chequeType = $data['cheque_type'] ?? 'issued';

            if ($data['payment_method'] === 'cheque') {
                if ($chequeType === 'received' && !empty($data['received_cheque_id'])) {
                    // Use a received cheque (from a customer) to pay this supplier
                    $supplierInvoice->load('supplier');
                    $heldCheque = $this->useReceivedCheque(
                        (int) $data['received_cheque_id'],
                        "Used to pay supplier: {$supplierInvoice->supplier->name} · Invoice {$supplierInvoice->invoice_number} · {$data['payment_date']}"
                    );
                    $chequeId = $heldCheque->id;
                    if ((float) $data['amount'] > (float) $heldCheque->amount) {
                        abort(422, 'Payment amount exceeds the selected cheque\'s value (Rs. ' . number_format((float) $heldCheque->amount, 2) . ').');
                    }
                } else {
                    // Issue a new cheque from our own cheque book
                    $cheque = Cheque::create([
                        'branch_id'            => $supplierInvoice->branch_id,
                        'created_by'           => $request->user()->id,
                        'direction'            => 'issued',
                        'party_id'             => $supplierInvoice->supplier_id,
                        'party_type'           => 'supplier',
                        'cheque_number'        => $data['cheque_number'],
                        'bank_name'            => $data['bank_name'],
                        'cheque_date'          => $data['cheque_date'],
                        'received_issued_date' => $data['payment_date'],
                        'amount'               => $data['amount'],
                        'status'               => 'in_hand',
                    ]);
                    $chequeId = $cheque->id;
                }
            }

            SupplierPayment::create([
                'supplier_invoice_id' => $supplierInvoice->id,
                'branch_id'           => $supplierInvoice->branch_id,
                'created_by'          => $request->user()->id,
                'amount'              => $data['amount'],
                'payment_method'      => $data['payment_method'],
                'payment_date'        => $data['payment_date'],
                'reference_number'    => $data['reference_number'] ?? null,
                'cheque_id'           => $chequeId,
                'account_id'          => $data['account_id'] ?? null,
                'cheque_type'         => $chequeType,
            ]);

            $newPaid    = $supplierInvoice->paid_amount + $data['amount'];
            $newBalance = $supplierInvoice->total - $newPaid;
            $supplierInvoice->update([
                'paid_amount' => $newPaid,
                'balance_due' => max(0, $newBalance),
                'status'      => $newBalance <= 0 ? 'paid' : 'partially_paid',
            ]);

            // DR Supplier AP  CR Cash/Bank
            $branchId = $supplierInvoice->branch_id;
            $supplierInvoice->load('supplier');
            $this->accounting->ensureSupplierAccount($supplierInvoice->supplier);
            $supplierInvoice->supplier->refresh();

            $apId   = $supplierInvoice->supplier->account_id
                      ?: $this->accounting->getAccountId($branchId, 'acc_trade_payables');
            // Use explicitly selected account; fall back to CoA settings
            $cashId = !empty($data['account_id'])
                      ? (int) $data['account_id']
                      : ($this->accounting->getAccountId($branchId, 'acc_cash')
                         ?: $this->accounting->getAccountId($branchId, 'acc_bank'));

            if ($apId && $cashId) {
                try {
                    $this->accounting->createEntry(
                        $branchId, 'payment_made', "Payment – {$supplierInvoice->invoice_number}",
                        $data['payment_date'],
                        [
                            ['account_id' => $apId,   'debit' => (float) $data['amount'], 'credit' => 0,                     'description' => "AP – {$supplierInvoice->invoice_number}"],
                            ['account_id' => $cashId, 'debit' => 0,                        'credit' => (float) $data['amount'], 'description' => "Payment – {$supplierInvoice->invoice_number}"],
                        ],
                        $request->user()->id, 'supplier_invoice', $supplierInvoice->id
                    );
                } catch (\Throwable $e) {
                    Log::error('recordSupplierPayment journal failed', [
                        'invoice_id' => $supplierInvoice->id, 'error' => $e->getMessage(),
                    ]);
                }
            }

            return response()->json(['message' => 'Payment recorded.', 'invoice' => $supplierInvoice->fresh(['supplier', 'branch'])]);
        });
    }
}
