<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GoodsReceiptNote;
use App\Models\GrnItem;
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
        $q = PurchaseOrder::query();
        $this->branchContext->applyScope($q);
        if ($request->status) $q->where('status', $request->status);
        if ($request->supplier_id) $q->where('supplier_id', $request->supplier_id);
        if ($request->search) {
            $q->where(fn($qq) => $qq->where('po_number', 'like', "%{$request->search}%")
                ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$request->search}%"))
            );
        }
        return response()->json($q->with(['supplier', 'branch', 'createdBy', 'grns.items.product'])->latest()->paginate($request->input('per_page', 20)));
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
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_percent'=> 'nullable|numeric|min:0',
        ]);

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
                $product = \App\Models\Product::find($item['product_id']);
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $lineTax = $lineTotal * (($item['tax_percent'] ?? 0) / 100);
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id'   => $item['product_id'],
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
                    'product_id'        => $item['product_id'],
                    'product_name'      => $product->name,
                    'unit'              => $product->unit,
                    'quantity_received' => $item['quantity'],
                    'unit_cost'         => $item['unit_price'],
                    'total_cost'        => $item['quantity'] * $item['unit_price'],
                ]);
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

        return DB::transaction(function () use ($goodsReceiptNote, $request) {
            $goodsReceiptNote->load('items');

            // Apply per-item edits submitted from the receive modal
            $itemUpdates = collect($request->input('items', []));
            $sellingPrices = []; // grn_item_id => this batch's own selling price (not persisted on grn_items)
            foreach ($goodsReceiptNote->items as $item) {
                $edit = $itemUpdates->firstWhere('grn_item_id', $item->id);
                if ($edit) {
                    $qty  = isset($edit['quantity_received']) ? (float) $edit['quantity_received'] : $item->quantity_received;
                    $cost = isset($edit['unit_cost'])         ? (float) $edit['unit_cost']         : $item->unit_cost;
                    $item->update([
                        'quantity_received' => $qty,
                        'unit_cost'         => $cost,
                        'total_cost'        => $qty * $cost,
                        'batch_number'      => $edit['batch_number'] ?? $item->batch_number,
                        'expiry_date'       => $edit['expiry_date']  ?? $item->expiry_date,
                    ]);
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

            // Update PO received quantities
            if ($goodsReceiptNote->purchase_order_id) {
                foreach ($goodsReceiptNote->items as $item) {
                    PurchaseOrderItem::where('purchase_order_id', $goodsReceiptNote->purchase_order_id)
                        ->where('product_id', $item->product_id)
                        ->increment('received_quantity', $item->quantity_received);
                }
                $po = PurchaseOrder::find($goodsReceiptNote->purchase_order_id);
                $allReceived = $po->items->every(fn($i) => $i->received_quantity >= $i->quantity);
                $po->update(['status' => $allReceived ? 'received' : 'partially_received']);
            }

            // Journal: DR Inventory, CR Accounts Payable
            try {
                $goodsReceiptNote->load('items');
                $total = $goodsReceiptNote->items->sum(fn($i) => $i->quantity_received * $i->unit_cost);
                if ($total > 0) {
                    $branchId     = $goodsReceiptNote->branch_id;
                    $inventoryId  = $this->accounting->getAccountId($branchId, 'acc_inventory')
                                 ?: $this->accounting->getAccountId($branchId, 'acc_purchases');
                    $goodsReceiptNote->load('purchaseOrder.supplier', 'supplier');
                    $supplier     = $goodsReceiptNote->purchaseOrder?->supplier ?? $goodsReceiptNote->supplier ?? null;
                    if ($supplier) $this->accounting->ensureSupplierAccount($supplier);
                    $apId         = $supplier?->account_id
                                 ?: $this->accounting->getAccountId($branchId, 'acc_trade_payables');
                    if ($inventoryId && $apId) {
                        $this->accounting->createEntry(
                            $branchId, 'grn_confirmed',
                            "Goods Received – {$goodsReceiptNote->grn_number}",
                            $goodsReceiptNote->received_date->toDateString(),
                            [
                                ['account_id' => $inventoryId, 'debit' => $total, 'credit' => 0,      'description' => "Inventory – {$goodsReceiptNote->grn_number}"],
                                ['account_id' => $apId,        'debit' => 0,      'credit' => $total, 'description' => "AP – {$goodsReceiptNote->grn_number}"],
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

    public function recordPOPayment(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $data = $request->validate([
            'amount'             => 'required|numeric|min:0.01|max:' . $purchaseOrder->balance_due,
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

            if ($data['payment_method'] === 'cheque') {
                if ($chequeType === 'received' && !empty($data['received_cheque_id'])) {
                    $heldCheque = $this->useReceivedCheque(
                        (int) $data['received_cheque_id'],
                        "Used to pay supplier: {$purchaseOrder->supplier->name} · Invoice {$purchaseOrder->po_number} · {$data['payment_date']}"
                    );
                    $chequeId = $heldCheque->id;
                    if ((float) $data['amount'] > (float) $heldCheque->amount) {
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
                        'amount'               => $data['amount'],
                        'status'               => 'in_hand',
                    ]);
                    $chequeId = $cheque->id;
                }
            }

            SupplierPayment::create([
                'purchase_order_id' => $purchaseOrder->id,
                'branch_id'         => $purchaseOrder->branch_id,
                'created_by'        => $request->user()->id,
                'amount'            => $data['amount'],
                'payment_method'    => $data['payment_method'],
                'payment_date'      => $data['payment_date'],
                'reference_number'  => $data['reference_number'] ?? null,
                'cheque_id'         => $chequeId,
                'account_id'        => $data['account_id'] ?? null,
                'cheque_type'       => $chequeType,
            ]);

            $newPaid    = (float) $purchaseOrder->paid_amount + (float) $data['amount'];
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
                            ['account_id' => $apId,   'debit' => (float) $data['amount'], 'credit' => 0,                     'description' => "AP – {$purchaseOrder->po_number}"],
                            ['account_id' => $cashId, 'debit' => 0,                        'credit' => (float) $data['amount'], 'description' => "Payment – {$purchaseOrder->po_number}"],
                        ],
                        $request->user()->id, 'purchase_order', $purchaseOrder->id
                    );
                }
            } catch (\Throwable $e) {
                Log::error('recordPOPayment journal failed', ['po_id' => $purchaseOrder->id, 'error' => $e->getMessage()]);
            }

            return response()->json(['message' => 'Payment recorded.', 'po' => $purchaseOrder->fresh(['supplier', 'branch', 'payments'])]);
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
