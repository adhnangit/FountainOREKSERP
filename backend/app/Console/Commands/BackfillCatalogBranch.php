<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillCatalogBranch extends Command
{
    protected $signature   = 'medri:backfill-catalog-branch
                                {--apply : Actually write the changes (default is dry-run)}
                                {--skus= : Path to the TSV of authoritative import SKUs (col 2), for cross-check}';
    protected $description = 'Assign product categories/products to Head Office or MEDRI EAST based on the bulk PDF import';

    private const MEDRI_EAST_BRANCH_ID = 7;
    private const HEAD_OFFICE_BRANCH_ID = 1;

    private const IMPORT_CATEGORY_NAMES = [
        'Pharmacy', 'Laboratory Supplies', 'Hospital Supplies', 'Surgical Supplies', 'Baby Care',
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $skusPath = $this->option('skus');

        $importCategories = ProductCategory::whereIn('name', self::IMPORT_CATEGORY_NAMES)->get();
        if ($importCategories->count() !== count(self::IMPORT_CATEGORY_NAMES)) {
            $this->error('Expected ' . count(self::IMPORT_CATEGORY_NAMES) . ' import categories, found ' . $importCategories->count() . '. Aborting.');
            return self::FAILURE;
        }
        $importCategoryIds = $importCategories->pluck('id');

        $meProducts = Product::whereIn('category_id', $importCategoryIds)->get(['id', 'sku', 'code', 'name']);
        $otherCategories = ProductCategory::whereNotIn('id', $importCategoryIds)->get(['id', 'name']);
        $otherProducts = Product::whereNotIn('category_id', $importCategoryIds)->orWhereNull('category_id')->get(['id', 'sku', 'code', 'name']);

        $this->info('Import categories (-> MEDRI EAST, branch 7): ' . $importCategories->pluck('name')->implode(', '));
        $this->info('Products in those categories: ' . $meProducts->count());
        $this->info('All other categories (-> HEAD OFFICE, branch 1): ' . $otherCategories->count());
        $this->info('Products in those / uncategorized: ' . $otherProducts->count());

        // Cross-check against the authoritative SKU list, if provided.
        if ($skusPath) {
            if (!file_exists($skusPath)) {
                $this->error("SKU file not found: {$skusPath}");
                return self::FAILURE;
            }
            $lines = file($skusPath, FILE_IGNORE_NEW_LINES);
            array_shift($lines); // header
            $skuSet = [];
            foreach ($lines as $l) {
                if (trim($l) === '') continue;
                $cols = explode("\t", $l);
                if (count($cols) < 2) continue;
                $skuSet[strtoupper(trim($cols[1]))] = true;
            }
            $this->info('Authoritative SKU list rows: ' . count($skuSet));

            $meSkus = $meProducts->pluck('sku')->filter()->map(fn($s) => strtoupper($s))->flip();
            $missingFromDb = collect(array_keys($skuSet))->diff($meSkus->keys());
            $extraInDb = $meSkus->keys()->diff(array_keys($skuSet));

            $this->line('  SKUs in list but not found among the 432-category products: ' . $missingFromDb->count());
            foreach ($missingFromDb as $s) $this->line("    - {$s}");
            $this->line('  Products in the 432-category set with a sku not in the list: ' . $extraInDb->count());
            foreach ($extraInDb as $s) $this->line("    - {$s}");

            if ($missingFromDb->count() || $extraInDb->count()) {
                $this->warn('Cross-check found discrepancies — review before applying.');
            } else {
                $this->info('Cross-check clean: category-based set matches the SKU list exactly.');
            }
        } else {
            $this->warn('No --skus file given — skipping cross-check (category-based assignment only).');
        }

        if (!$apply) {
            $this->warn('DRY RUN — no changes written. Re-run with --apply to commit.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($importCategoryIds, $importCategories, $otherCategories, $meProducts, $otherProducts) {
            ProductCategory::whereIn('id', $importCategoryIds)->update(['branch_id' => self::MEDRI_EAST_BRANCH_ID]);
            ProductCategory::whereIn('id', $otherCategories->pluck('id'))->update(['branch_id' => self::HEAD_OFFICE_BRANCH_ID]);
            Product::whereIn('id', $meProducts->pluck('id'))->update(['branch_id' => self::MEDRI_EAST_BRANCH_ID]);
            Product::whereIn('id', $otherProducts->pluck('id'))->update(['branch_id' => self::HEAD_OFFICE_BRANCH_ID]);
        });
        $this->info('APPLIED.');
        return self::SUCCESS;
    }
}
