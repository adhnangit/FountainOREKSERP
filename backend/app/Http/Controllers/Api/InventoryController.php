<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InterBranchTransfer;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\TransferItem;
use App\Services\BranchContextService;
use App\Services\NumberGeneratorService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function __construct(
        private BranchContextService $branchContext,
        private NumberGeneratorService $numbers,
        private StockService $stockService
    ) {}

    // Inter-Branch Transfers
    public function indexTransfers(Request $request): JsonResponse
    {
        $q = InterBranchTransfer::query();
        $branchId = $this->branchContext->getBranchId();
        if ($branchId) {
            $q->where(fn($q) => $q->where('from_branch_id', $branchId)->orWhere('to_branch_id', $branchId));
        }
        if ($request->status) $q->where('status', $request->status);
        return response()->json($q->with(['fromBranch', 'toBranch', 'requestedBy', 'items.product'])->latest()->paginate($request->input('per_page', 20)));
    }

    public function requestTransfer(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'request_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.requested_quantity' => 'required|numeric|min:0.01',
        ]);

        return DB::transaction(function () use ($data, $request) {
            $transfer = InterBranchTransfer::create([
                'from_branch_id' => $data['from_branch_id'],
                'to_branch_id' => $data['to_branch_id'],
                'requested_by' => $request->user()->id,
                'transfer_number' => $this->numbers->transferNumber(),
                'status' => 'requested',
                'request_date' => $data['request_date'],
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $product = \App\Models\Product::find($item['product_id']);
                TransferItem::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $product->name,
                    'requested_quantity' => $item['requested_quantity'],
                    'unit_cost' => $product->cost_price,
                ]);
            }

            return response()->json($transfer->load(['fromBranch', 'toBranch', 'items.product']), 201);
        }, 5);
    }

    public function dispatchTransfer(Request $request, InterBranchTransfer $interBranchTransfer): JsonResponse
    {
        if ($interBranchTransfer->status !== 'approved') {
            return response()->json(['message' => 'Transfer must be approved before dispatch.'], 422);
        }

        $data = $request->validate([
            'dispatch_date' => 'required|date',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:transfer_items,id',
            'items.*.dispatched_quantity' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data, $interBranchTransfer, $request) {
            foreach ($data['items'] as $itemData) {
                $item = TransferItem::find($itemData['id']);

                // Deduct from source branch, oldest batch first — this dispatch
                // may draw from several batches; capture the weighted-average
                // cost of what was actually drawn so the destination branch
                // receives it at an accurate cost.
                $movements = $this->stockService->deductFifo(
                    $item->product_id,
                    $interBranchTransfer->from_branch_id,
                    $itemData['dispatched_quantity'],
                    'transfer_out',
                    'transfer',
                    $interBranchTransfer->id,
                    $request->user()->id,
                    $data['dispatch_date']
                );
                $drawnQty = collect($movements)->sum(fn($m) => abs((float) $m->quantity));
                $drawnCost = collect($movements)->sum(fn($m) => abs((float) $m->quantity) * (float) $m->unit_cost);
                $unitCost = $drawnQty > 0 ? $drawnCost / $drawnQty : 0;

                $item->update([
                    'dispatched_quantity' => $itemData['dispatched_quantity'],
                    'unit_cost' => round($unitCost, 2),
                    'batch_id' => $movements[0]->batch_id ?? null,
                ]);
            }

            $interBranchTransfer->update([
                'status' => 'dispatched',
                'dispatch_date' => $data['dispatch_date'],
                'dispatched_by' => $request->user()->id,
            ]);

            return response()->json(['message' => 'Transfer dispatched.']);
        });
    }

    public function receiveTransfer(Request $request, InterBranchTransfer $interBranchTransfer): JsonResponse
    {
        if ($interBranchTransfer->status !== 'dispatched') {
            return response()->json(['message' => 'Transfer must be dispatched first.'], 422);
        }

        $data = $request->validate([
            'received_date' => 'required|date',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:transfer_items,id',
            'items.*.received_quantity' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data, $interBranchTransfer, $request) {
            foreach ($data['items'] as $itemData) {
                $item = TransferItem::find($itemData['id']);
                $item->update(['received_quantity' => $itemData['received_quantity']]);

                // Receive at the destination branch as its own new batch, valued
                // at the weighted-average cost of what was actually drawn from
                // the source branch's batches at dispatch; selling price defaults
                // to the product's current reference price at the destination.
                $sellingPrice = (float) (\App\Models\Product::where('id', $item->product_id)->value('selling_price') ?? 0);
                $this->stockService->receiveBatch(
                    $item->product_id,
                    $interBranchTransfer->to_branch_id,
                    $itemData['received_quantity'],
                    (float) $item->unit_cost,
                    $sellingPrice,
                    'transfer_in',
                    'transfer',
                    $interBranchTransfer->id,
                    $request->user()->id,
                    $data['received_date'],
                    $item->batch_number,
                    null,
                    'transfer_in'
                );
            }

            $interBranchTransfer->update([
                'status' => 'received',
                'received_date' => $data['received_date'],
                'received_by' => $request->user()->id,
            ]);

            return response()->json(['message' => 'Transfer received and stock updated.']);
        });
    }

    public function approveTransfer(Request $request, InterBranchTransfer $interBranchTransfer): JsonResponse
    {
        $interBranchTransfer->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
        ]);
        return response()->json(['message' => 'Transfer approved.']);
    }

    // Stock Adjustments
    public function indexAdjustments(Request $request): JsonResponse
    {
        $q = StockAdjustment::query();
        $this->branchContext->applyScope($q);
        return response()->json($q->with(['branch', 'createdBy', 'items.product'])->latest()->paginate($request->input('per_page', 20)));
    }

    public function storeAdjustment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'adjustment_date' => 'required|date',
            'reason' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.physical_quantity' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data, $request) {
            $adjustment = StockAdjustment::create([
                'branch_id' => $data['branch_id'],
                'created_by' => $request->user()->id,
                'adjustment_number' => $this->numbers->adjustmentNumber($data['branch_id']),
                'status' => 'draft',
                'adjustment_date' => $data['adjustment_date'],
                'reason' => $data['reason'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $systemQty = $this->stockService->getAvailableStock($item['product_id'], $data['branch_id']);
                $difference = $item['physical_quantity'] - $systemQty;
                StockAdjustmentItem::create([
                    'adjustment_id' => $adjustment->id,
                    'product_id' => $item['product_id'],
                    'system_quantity' => $systemQty,
                    'physical_quantity' => $item['physical_quantity'],
                    'difference' => $difference,
                    'unit_cost' => \App\Models\ProductBranchStock::where('product_id', $item['product_id'])->where('branch_id', $data['branch_id'])->value('avg_cost') ?? 0,
                ]);
            }

            return response()->json($adjustment->load(['items.product', 'branch']), 201);
        }, 5);
    }

    public function showAdjustment(StockAdjustment $stockAdjustment): JsonResponse
    {
        return response()->json($stockAdjustment->load(['items.product', 'branch', 'createdBy']));
    }

    public function updateAdjustment(Request $request, StockAdjustment $stockAdjustment): JsonResponse
    {
        if ($stockAdjustment->status !== 'draft') {
            return response()->json(['message' => 'Only draft adjustments can be edited.'], 422);
        }

        $data = $request->validate([
            'adjustment_date' => 'required|date',
            'reason' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.physical_quantity' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($stockAdjustment, $data) {
            $stockAdjustment->update([
                'adjustment_date' => $data['adjustment_date'],
                'reason' => $data['reason'] ?? null,
            ]);

            // Replace items — system qty is re-snapshotted so the difference
            // reflects current stock, not stock at original creation time.
            $stockAdjustment->items()->delete();
            foreach ($data['items'] as $item) {
                $systemQty = $this->stockService->getAvailableStock($item['product_id'], $stockAdjustment->branch_id);
                StockAdjustmentItem::create([
                    'adjustment_id' => $stockAdjustment->id,
                    'product_id' => $item['product_id'],
                    'system_quantity' => $systemQty,
                    'physical_quantity' => $item['physical_quantity'],
                    'difference' => $item['physical_quantity'] - $systemQty,
                    'unit_cost' => \App\Models\ProductBranchStock::where('product_id', $item['product_id'])->where('branch_id', $stockAdjustment->branch_id)->value('avg_cost') ?? 0,
                ]);
            }

            return response()->json($stockAdjustment->fresh()->load(['items.product', 'branch']));
        }, 5);
    }

    public function destroyAdjustment(StockAdjustment $stockAdjustment): JsonResponse
    {
        if ($stockAdjustment->status !== 'draft') {
            return response()->json(['message' => 'Only draft adjustments can be deleted.'], 422);
        }
        $stockAdjustment->items()->delete();
        $stockAdjustment->delete();
        return response()->json(['message' => 'Adjustment deleted.']);
    }

    public function approveAdjustment(Request $request, StockAdjustment $stockAdjustment): JsonResponse
    {
        if ($stockAdjustment->status !== 'draft') {
            return response()->json(['message' => 'Adjustment already processed.'], 422);
        }

        return DB::transaction(function () use ($stockAdjustment, $request) {
            foreach ($stockAdjustment->items as $item) {
                if ($item->difference > 0) {
                    $sellingPrice = (float) (\App\Models\Product::where('id', $item->product_id)->value('selling_price') ?? 0);
                    $this->stockService->receiveBatch(
                        $item->product_id, $stockAdjustment->branch_id,
                        $item->difference, (float) $item->unit_cost, $sellingPrice, 'adjustment_in',
                        'adjustment', $stockAdjustment->id,
                        $request->user()->id, $stockAdjustment->adjustment_date->toDateString(),
                        null, null, 'adjustment_in'
                    );
                } elseif ($item->difference < 0) {
                    $this->stockService->deductFifo(
                        $item->product_id, $stockAdjustment->branch_id,
                        abs($item->difference), 'adjustment_out',
                        'adjustment', $stockAdjustment->id,
                        $request->user()->id, $stockAdjustment->adjustment_date->toDateString()
                    );
                }
            }

            $stockAdjustment->update([
                'status' => 'approved',
                'approved_by' => $request->user()->id,
            ]);

            return response()->json(['message' => 'Adjustment approved and stock updated.']);
        });
    }
}
