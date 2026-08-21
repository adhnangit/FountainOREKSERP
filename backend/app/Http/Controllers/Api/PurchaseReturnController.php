<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\ProductBranchStock;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Services\AccountingService;
use App\Services\BranchContextService;
use App\Services\NumberGeneratorService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseReturnController extends Controller
{
    public function __construct(
        private BranchContextService $branchContext,
        private NumberGeneratorService $numbers,
        private StockService $stockService,
        private AccountingService $accounting
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = PurchaseReturn::query();
        $this->branchContext->applyScope($q);

        if ($request->search) {
            $q->where(fn($qq) => $qq->where('return_number', 'like', "%{$request->search}%")
                ->orWhereHas('supplier', fn($s) => $s->where('name', 'like', "%{$request->search}%"))
                ->orWhereHas('purchaseOrder', fn($o) => $o->where('po_number', 'like', "%{$request->search}%"))
            );
        }
        if ($request->from_date) $q->whereDate('return_date', '>=', $request->from_date);
        if ($request->to_date) $q->whereDate('return_date', '<=', $request->to_date);

        return response()->json(
            $q->with(['supplier', 'branch', 'createdBy', 'purchaseOrder', 'items.product'])
                ->latest('return_date')->latest('id')
                ->paginate($request->input('per_page', 20))
        );
    }

    /**
     * Items on a purchase order that are still eligible for return: what's actually
     * been received minus what's already gone back on previous purchase returns.
     * (You can only return goods you've physically received into stock.)
     */
    public function returnable(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (!in_array($purchaseOrder->status, ['partially_received', 'received'])) {
            return response()->json(['message' => 'No goods have been received against this purchase order yet.'], 422);
        }

        $purchaseOrder->load(['items', 'supplier', 'branch']);
        $returned = $this->returnedQuantities($purchaseOrder->id);

        // Header-level discount proration, same technique as sales returns:
        // value each returned unit at what was actually recorded, not just the raw line price.
        $lineSum = (float) $purchaseOrder->items->sum('total');
        $factor  = $lineSum > 0 ? (float) $purchaseOrder->total / $lineSum : 1;

        $items = $purchaseOrder->items->map(function ($i) use ($returned, $purchaseOrder) {
            $received = (float) $i->received_quantity;
            $alreadyReturned = (float) ($returned[$i->product_id] ?? 0);
            $availableStock = $this->stockService->getAvailableStock($i->product_id, $purchaseOrder->branch_id);
            return [
                'product_id'          => $i->product_id,
                'product_name'        => $i->product_name,
                'unit'                => $i->unit,
                'unit_price'          => (float) $i->unit_price,
                'line_total'          => (float) $i->total,
                'ordered_quantity'    => (float) $i->quantity,
                'received_quantity'   => $received,
                'returned_quantity'   => $alreadyReturned,
                'available_stock'     => $availableStock,
                'returnable_quantity' => max(0, min($received - $alreadyReturned, $availableStock)),
            ];
        })->values();

        return response()->json([
            'purchase_order' => $purchaseOrder->only(['id', 'po_number', 'status', 'total', 'paid_amount', 'balance_due', 'order_date']),
            'supplier'       => $purchaseOrder->supplier?->only(['id', 'name', 'credit_balance']),
            'factor'         => round($factor, 6),
            'items'          => $items,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'purchase_order_id'  => 'required|exists:purchase_orders,id',
            'return_date'        => 'required|date',
            'reason'             => 'nullable|string',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0.01',
        ]);

        $po = PurchaseOrder::with(['items', 'supplier'])->findOrFail($data['purchase_order_id']);

        if (!in_array($po->status, ['partially_received', 'received'])) {
            return response()->json(['message' => 'No goods have been received against this purchase order yet.'], 422);
        }

        $returned = $this->returnedQuantities($po->id);
        $lineSum  = (float) $po->items->sum('total');
        $factor   = $lineSum > 0 ? (float) $po->total / $lineSum : 1;

        $lines = [];
        foreach ($data['items'] as $item) {
            $origItem = $po->items->firstWhere('product_id', $item['product_id']);
            if (!$origItem) {
                return response()->json(['message' => 'One of the products is not on this purchase order.'], 422);
            }
            $receivedLeft = (float) $origItem->received_quantity - (float) ($returned[$item['product_id']] ?? 0);
            $availableStock = $this->stockService->getAvailableStock($item['product_id'], $po->branch_id);
            $available = max(0, min($receivedLeft, $availableStock));
            if ((float) $item['quantity'] > $available + 0.0001) {
                return response()->json([
                    'message' => "Return quantity for {$origItem->product_name} exceeds what's available to return ({$available} — limited by received quantity and current stock on hand).",
                ], 422);
            }
            $lines[] = ['orig' => $origItem, 'qty' => (float) $item['quantity']];
        }

        return DB::transaction(function () use ($data, $po, $lines, $factor, $request) {
            $subtotal = 0; $lineTax = 0; $lineTotals = 0;
            foreach ($lines as $l) {
                $ratio       = $l['qty'] / (float) $l['orig']->quantity;
                $subtotal   += (float) $l['orig']->unit_price * $l['qty'];
                $lineTax    += (float) $l['orig']->tax_amount * $ratio;
                $lineTotals += (float) $l['orig']->total * $ratio;
            }
            $returnTotal = round($lineTotals * $factor, 2);

            $return = PurchaseReturn::create([
                'branch_id'         => $po->branch_id,
                'supplier_id'       => $po->supplier_id,
                'purchase_order_id' => $po->id,
                'created_by'        => $request->user()->id,
                'return_number'     => $this->numbers->purchaseReturnNumber($po->branch_id),
                'return_date'       => $data['return_date'],
                'status'            => 'confirmed',
                'subtotal'          => round($subtotal, 2),
                'tax_amount'        => round($lineTax, 2),
                'total'             => $returnTotal,
                'reason'            => $data['reason'] ?? null,
            ]);

            foreach ($lines as $l) {
                $ratio = $l['qty'] / (float) $l['orig']->quantity;
                PurchaseReturnItem::create([
                    'purchase_return_id' => $return->id,
                    'product_id'         => $l['orig']->product_id,
                    'product_name'       => $l['orig']->product_name,
                    'product_code'       => $l['orig']->product_code,
                    'unit'               => $l['orig']->unit,
                    'quantity'           => $l['qty'],
                    'unit_price'         => $l['orig']->unit_price,
                    'tax_percent'        => $l['orig']->tax_percent,
                    'tax_amount'         => round((float) $l['orig']->tax_amount * $ratio, 2),
                    'total'              => round((float) $l['orig']->total * $ratio, 2),
                ]);

                // Goods physically leave the warehouse back to the supplier
                $this->stockService->deductFifo(
                    $l['orig']->product_id, $po->branch_id, $l['qty'],
                    'purchase_return', 'purchase_return', $return->id,
                    $request->user()->id, $return->return_date->toDateString()
                );
            }

            // Reduce what we owe on the PO first, then park any remainder as supplier credit
            $balanceReduction = round(min($returnTotal, (float) $po->balance_due), 2);
            $creditToSupplier = round($returnTotal - $balanceReduction, 2);

            $newBalance = round((float) $po->balance_due - $balanceReduction, 2);
            $po->update([
                'balance_due'    => max(0, $newBalance),
                'payment_status' => $newBalance <= 0.001 ? 'paid' : $po->payment_status,
            ]);

            if ($creditToSupplier > 0 && $po->supplier) {
                $po->supplier->increment('credit_balance', $creditToSupplier);
            }

            $this->postPurchaseReturnJournal($return, $po, $request->user()->id);

            return response()->json([
                'message'            => 'Purchase return recorded.',
                'purchase_return'    => $return->load(['items.product', 'supplier', 'purchaseOrder']),
                'balance_reduction'  => $balanceReduction,
                'credit_to_supplier' => $creditToSupplier,
            ], 201);
        }, 5);
    }

    public function show(PurchaseReturn $purchaseReturn): JsonResponse
    {
        return response()->json($purchaseReturn->load(['items.product', 'supplier', 'branch', 'createdBy', 'purchaseOrder']));
    }

    /** Quantities already returned per product for a PO, across all its purchase returns. */
    private function returnedQuantities(int $purchaseOrderId)
    {
        return PurchaseReturnItem::whereIn('purchase_return_id', PurchaseReturn::where('purchase_order_id', $purchaseOrderId)->pluck('id'))
            ->selectRaw('product_id, SUM(quantity) as qty')
            ->groupBy('product_id')
            ->pluck('qty', 'product_id');
    }

    private function postPurchaseReturnJournal(PurchaseReturn $return, PurchaseOrder $po, int $userId): void
    {
        try {
            $branchId    = $return->branch_id;
            $inventoryId = $this->accounting->getAccountId($branchId, 'acc_inventory')
                           ?: $this->accounting->getAccountId($branchId, 'acc_purchases');
            $apId        = $po->supplier?->account_id
                           ?: $this->accounting->getAccountId($branchId, 'acc_trade_payables');

            if (!$inventoryId || !$apId) return;

            if (JournalEntry::where('reference_type', 'purchase_return')
                    ->where('reference_id', $return->id)->exists()) return;

            // GRN receipt values Inventory (and credits AP) at pre-tax cost only
            // (quantity_received * unit_cost — see PurchaseController::confirm()).
            // The return must reverse exactly what was recorded at receipt, so it
            // uses the same pre-tax amount rather than the tax-inclusive total —
            // otherwise every taxed return would over-credit Inventory and
            // over-debit AP by the tax portion, corrupting both balances.
            $total = (float) $return->subtotal;

            $this->accounting->createEntry(
                $branchId, 'purchase_return',
                "Purchase Return {$return->return_number} against {$po->po_number}",
                $return->return_date->toDateString(),
                [
                    ['account_id' => $apId,        'debit' => $total, 'credit' => 0,     'description' => "Purchase return {$return->return_number}"],
                    ['account_id' => $inventoryId, 'debit' => 0,       'credit' => $total, 'description' => "Inventory reversal – {$return->return_number}"],
                ],
                $userId, 'purchase_return', $return->id
            );
        } catch (\Throwable $e) {
            Log::error('postPurchaseReturnJournal failed', ['purchase_return_id' => $return->id, 'error' => $e->getMessage()]);
        }
    }
}
