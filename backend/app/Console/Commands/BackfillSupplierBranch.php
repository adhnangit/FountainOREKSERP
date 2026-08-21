<?php

namespace App\Console\Commands;

use App\Models\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillSupplierBranch extends Command
{
    protected $signature   = 'medri:backfill-supplier-branch {--apply : Actually write the changes (default is dry-run)}';
    protected $description = 'Assign all existing unscoped suppliers to Head Office (branch 1)';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $suppliers = Supplier::whereNull('branch_id')->get(['id', 'name']);
        $this->info("Suppliers with no branch_id: {$suppliers->count()}");
        foreach ($suppliers as $s) {
            $this->line("  #{$s->id} {$s->name} -> branch_id=1 (HEAD OFFICE)");
        }

        if (!$apply) {
            $this->warn('DRY RUN — no changes written. Re-run with --apply to commit.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($suppliers) {
            Supplier::whereIn('id', $suppliers->pluck('id'))->update(['branch_id' => 1]);
        });
        $this->info('APPLIED.');
        return self::SUCCESS;
    }
}
