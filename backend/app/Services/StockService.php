<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockService
{
    /**
     * New stock arriving (GRN, opening stock, found-on-adjustment, transfer-in)
     * becomes its own priced batch. Before creating a fresh batch, any
     * outstanding backorder (a batch pushed negative because it was sold
     * before it was ever received) is topped up first, valued at THIS
     * receipt's cost — the batch-level equivalent of resetting the cost
     * basis when running stock had gone non-positive.
     */
    public function receiveBatch(
        int $productId,
        int $branchId,
        float $quantity,
        float $costPrice,
        float $sellingPrice,
        string $type,
        string $referenceType,
        int $referenceId,
        int $createdBy,
        string $movementDate,
        ?string $batchNumber = null,
        ?string $expiryDate = null,
        string $sourceType = 'grn',
        ?int $grnItemId = null
    ): Batch {
        return DB::transaction(function () use (
            $productId, $branchId, $quantity, $costPrice, $sellingPrice, $type,
            $referenceType, $referenceId, $createdBy, $movementDate,
            $batchNumber, $expiryDate, $sourceType, $grnItemId
        ) {
            $remaining = $quantity;
            $primaryBatch = null;

            $negativeBatch = Batch::where('product_id', $productId)->where('branch_id', $branchId)
                ->where('quantity_remaining', '<', 0)
                ->orderBy('received_date')->orderBy('id')
                ->lockForUpdate()
                ->first();

            if ($negativeBatch && $remaining > 0) {
                $topUp = min($remaining, abs((float) $negativeBatch->quantity_remaining));

                $negativeBatch->cost_price = $costPrice;
                $negativeBatch->selling_price = $sellingPrice;
                $negativeBatch->quantity_remaining += $topUp;
                $negativeBatch->quantity_received += $topUp;
                $negativeBatch->source_type = $sourceType;
                $negativeBatch->grn_item_id = $grnItemId ?? $negativeBatch->grn_item_id;
                $negativeBatch->batch_number = $batchNumber ?? $negativeBatch->batch_number;
                $negativeBatch->expiry_date = $expiryDate ?? $negativeBatch->expiry_date;
                $negativeBatch->save();

                StockMovement::create([
                    'branch_id' => $branchId, 'product_id' => $productId, 'batch_id' => $negativeBatch->id,
                    'created_by' => $createdBy, 'type' => $type,
                    'reference_type' => $referenceType, 'reference_id' => $referenceId,
                    'quantity' => $topUp, 'unit_cost' => $costPrice,
                    'balance_quantity' => $this->recomputeRollup($productId, $branchId),
                    'batch_number' => $negativeBatch->batch_number, 'expiry_date' => $negativeBatch->expiry_date,
                    'movement_date' => $movementDate,
                ]);

                $remaining -= $topUp;
                $primaryBatch = $negativeBatch;
            }

            if ($remaining > 0) {
                $batch = Batch::create([
                    'product_id' => $productId,
                    'branch_id' => $branchId,
                    'batch_code' => app(NumberGeneratorService::class)->batchCode($branchId),
                    'batch_number' => $batchNumber,
                    'grn_item_id' => $grnItemId,
                    'source_type' => $sourceType,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'cost_price' => $costPrice,
                    'selling_price' => $sellingPrice,
                    'quantity_received' => $remaining,
                    'quantity_remaining' => $remaining,
                    'received_date' => $movementDate,
                    'expiry_date' => $expiryDate,
                    'created_by' => $createdBy,
                ]);

                StockMovement::create([
                    'branch_id' => $branchId, 'product_id' => $productId, 'batch_id' => $batch->id,
                    'created_by' => $createdBy, 'type' => $type,
                    'reference_type' => $referenceType, 'reference_id' => $referenceId,
                    'quantity' => $remaining, 'unit_cost' => $costPrice,
                    'balance_quantity' => $this->recomputeRollup($productId, $branchId),
                    'batch_number' => $batchNumber, 'expiry_date' => $expiryDate,
                    'movement_date' => $movementDate,
                ]);

                $primaryBatch = $batch;
            }

            return $primaryBatch;
        });
    }

    /** Deduct from one specific batch (a sale where staff picked a batch, or any other targeted draw-down). Can go negative — a deliberate oversell/backorder is a normal, accepted state for this business. */
    public function deductFromBatch(
        int $batchId,
        float $quantity,
        string $type,
        string $referenceType,
        int $referenceId,
        int $createdBy,
        string $movementDate
    ): StockMovement {
        return DB::transaction(function () use ($batchId, $quantity, $type, $referenceType, $referenceId, $createdBy, $movementDate) {
            $batch = Batch::lockForUpdate()->findOrFail($batchId);
            $batch->quantity_remaining -= $quantity;
            $batch->save();

            return StockMovement::create([
                'branch_id' => $batch->branch_id, 'product_id' => $batch->product_id, 'batch_id' => $batch->id,
                'created_by' => $createdBy, 'type' => $type,
                'reference_type' => $referenceType, 'reference_id' => $referenceId,
                'quantity' => -$quantity, 'unit_cost' => $batch->cost_price,
                'balance_quantity' => $this->recomputeRollup($batch->product_id, $batch->branch_id),
                'batch_number' => $batch->batch_number, 'expiry_date' => $batch->expiry_date,
                'movement_date' => $movementDate,
            ]);
        });
    }

    /** Deduct oldest-batch-first across however many batches it takes (no manual batch-picker UI exists for this flow). Creates/extends a backorder batch for any shortfall. */
    public function deductFifo(
        int $productId,
        int $branchId,
        float $quantity,
        string $type,
        string $referenceType,
        int $referenceId,
        int $createdBy,
        string $movementDate
    ): array {
        return DB::transaction(function () use ($productId, $branchId, $quantity, $type, $referenceType, $referenceId, $createdBy, $movementDate) {
            $movements = [];
            $remaining = $quantity;

            $openBatches = Batch::where('product_id', $productId)->where('branch_id', $branchId)
                ->where('quantity_remaining', '>', 0)
                ->orderBy('received_date')->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($openBatches as $batch) {
                if ($remaining <= 0) break;
                $take = min($remaining, (float) $batch->quantity_remaining);
                $movements[] = $this->deductFromBatch($batch->id, $take, $type, $referenceType, $referenceId, $createdBy, $movementDate);
                $remaining -= $take;
            }

            if ($remaining > 0) {
                $backorder = $this->getOrCreateBackorderBatch($productId, $branchId, $movementDate);
                $movements[] = $this->deductFromBatch($backorder->id, $remaining, $type, $referenceType, $referenceId, $createdBy, $movementDate);
            }

            return $movements;
        });
    }

    /** Restock a SPECIFIC known batch (a cancellation/return undoing an earlier sale from it) — never creates a new batch. */
    public function restockBatch(
        int $batchId,
        float $quantity,
        string $type,
        string $referenceType,
        int $referenceId,
        int $createdBy,
        string $movementDate
    ): StockMovement {
        return DB::transaction(function () use ($batchId, $quantity, $type, $referenceType, $referenceId, $createdBy, $movementDate) {
            $batch = Batch::lockForUpdate()->findOrFail($batchId);
            $batch->quantity_remaining += $quantity;
            $batch->save();

            return StockMovement::create([
                'branch_id' => $batch->branch_id, 'product_id' => $batch->product_id, 'batch_id' => $batch->id,
                'created_by' => $createdBy, 'type' => $type,
                'reference_type' => $referenceType, 'reference_id' => $referenceId,
                'quantity' => $quantity, 'unit_cost' => $batch->cost_price,
                'balance_quantity' => $this->recomputeRollup($batch->product_id, $batch->branch_id),
                'batch_number' => $batch->batch_number, 'expiry_date' => $batch->expiry_date,
                'movement_date' => $movementDate,
            ]);
        });
    }

    /** The server-side FIFO default/fallback: oldest batch with stock left, or null if none. */
    public function pickOldestBatch(int $productId, int $branchId): ?Batch
    {
        return Batch::where('product_id', $productId)->where('branch_id', $branchId)
            ->where('quantity_remaining', '>', 0)
            ->orderBy('received_date')->orderBy('id')
            ->first();
    }

    /** Reuses an already-open backorder batch if one exists, otherwise opens a new one valued at the product's current reference price. */
    public function getOrCreateBackorderBatch(int $productId, int $branchId, string $movementDate): Batch
    {
        $existing = Batch::where('product_id', $productId)->where('branch_id', $branchId)
            ->where('quantity_remaining', '<', 0)
            ->orderBy('received_date')->orderBy('id')
            ->first();
        if ($existing) return $existing;

        $product = Product::findOrFail($productId);

        return Batch::create([
            'product_id' => $productId,
            'branch_id' => $branchId,
            'batch_code' => app(NumberGeneratorService::class)->batchCode($branchId),
            'source_type' => 'backorder',
            'cost_price' => (float) $product->cost_price,
            'selling_price' => (float) $product->selling_price,
            'quantity_received' => 0,
            'quantity_remaining' => 0,
            'received_date' => $movementDate,
        ]);
    }

    /** Recomputes the product_branch_stock rollup (quantity + informational avg_cost) from source-of-truth batches. Returns the new total quantity. */
    public function recomputeRollup(int $productId, int $branchId): float
    {
        $batches = Batch::where('product_id', $productId)->where('branch_id', $branchId)->get();
        $totalQty = (float) $batches->sum('quantity_remaining');
        $weightedCostQty = (float) $batches->sum(fn($b) => (float) $b->quantity_remaining * (float) $b->cost_price);
        $avgCost = $totalQty != 0.0 ? $weightedCostQty / $totalQty : 0.0;

        $stock = ProductBranchStock::firstOrCreate(
            ['product_id' => $productId, 'branch_id' => $branchId],
            ['quantity' => 0, 'avg_cost' => 0]
        );
        $stock->quantity = $totalQty;
        $stock->avg_cost = round($avgCost, 2);
        $stock->save();

        return $totalQty;
    }

    public function getAvailableStock(int $productId, int $branchId): float
    {
        $stock = ProductBranchStock::where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->first();
        return $stock ? (float) $stock->quantity : 0.0;
    }

    public function getLowStockProducts(int $branchId): \Illuminate\Database\Eloquent\Collection
    {
        return Product::whereHas('branchStocks', function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
                ->whereRaw('quantity <= products.reorder_level');
        })->with(['branchStocks' => fn($q) => $q->where('branch_id', $branchId)])
        ->where('is_active', true)
        ->get();
    }
}
