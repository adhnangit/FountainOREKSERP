<?php

namespace App\Console\Commands;

use App\Models\Batch;
use App\Models\Product;
use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use App\Services\NumberGeneratorService;
use Illuminate\Console\Command;

class MigrateProductsToOpeningBatches extends Command
{
    protected $signature   = 'medri:migrate-opening-batches {--apply : Actually write the changes (default is dry-run)}';
    protected $description = 'Create one opening batch per existing product+branch stock row, using its current avg_cost/selling_price. Idempotent — skips any product+branch that already has a batch.';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $numbers = app(NumberGeneratorService::class);
        $adminId = \App\Models\User::where('email', 'admin@medri.lk')->value('id');

        $rows = ProductBranchStock::all();

        $created = 0; $skipped = 0; $backorders = 0;
        $totalQty = 0.0; $totalValue = 0.0;
        $byBranch = [];

        foreach ($rows as $stock) {
            $alreadyHasBatch = Batch::where('product_id', $stock->product_id)->where('branch_id', $stock->branch_id)->exists();
            if ($alreadyHasBatch) { $skipped++; continue; }

            $product = Product::find($stock->product_id);
            if (!$product) { $skipped++; continue; }

            $qty = (float) $stock->quantity;
            $cost = (float) $stock->avg_cost;
            $sellingPrice = (float) $product->selling_price;

            $earliestDate = StockMovement::where('product_id', $stock->product_id)
                ->where('branch_id', $stock->branch_id)
                ->orderBy('movement_date')->value('movement_date');
            $receivedDate = $earliestDate ?? now()->toDateString();

            $sourceType = $qty > 0 ? 'opening_migration' : 'backorder';
            if ($qty <= 0) $backorders++; else $created++;

            $byBranch[$stock->branch_id] = ($byBranch[$stock->branch_id] ?? 0) + 1;
            $totalQty += $qty;
            $totalValue += $qty * $cost;

            $this->line(sprintf(
                '  [%s] product #%d branch #%d: qty=%s cost=%s -> %s',
                $qty > 0 ? 'BATCH' : 'BACKORDER', $stock->product_id, $stock->branch_id,
                number_format($qty, 2), number_format($cost, 2), $sourceType
            ));

            if ($apply) {
                Batch::create([
                    'product_id' => $stock->product_id,
                    'branch_id' => $stock->branch_id,
                    'batch_code' => $numbers->batchCode($stock->branch_id),
                    'source_type' => $sourceType,
                    'cost_price' => $cost,
                    'selling_price' => $sellingPrice,
                    'quantity_received' => max(0, $qty),
                    'quantity_remaining' => $qty,
                    'received_date' => $receivedDate,
                    'created_by' => $adminId,
                    'notes' => 'Opening batch migrated from product_branch_stock on ' . now()->toDateString(),
                ]);
            }
        }

        $this->info("\nCreated (positive-stock opening batches): {$created}");
        $this->info("Created (backorder placeholder batches): {$backorders}");
        $this->info("Skipped (already had a batch): {$skipped}");
        $this->info('By branch: ' . collect($byBranch)->map(fn($n, $b) => "branch {$b}={$n}")->implode(', '));
        $this->info('Total quantity migrated: ' . number_format($totalQty, 2));
        $this->info('Total value migrated: ' . number_format($totalValue, 2));

        if (!$apply) {
            $this->warn('DRY RUN — no changes written. Re-run with --apply to commit.');
        } else {
            $this->info('APPLIED.');
        }

        return self::SUCCESS;
    }
}
