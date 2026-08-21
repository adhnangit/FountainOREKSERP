<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\SystemSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillAccountBranch extends Command
{
    protected $signature   = 'medri:backfill-account-branch {--apply : Actually write the changes (default is dry-run)}';
    protected $description = 'Tag existing accounts with branch_id: sub-ledger accounts inherit their owning customer/supplier\'s branch, structural accounts go to Head Office';

    // "MEDFIX HNB" (ACC56720) is a real, already-funded MEDFIX bank account
    // (opening_balance ~Rs 1,007,919.68) that happens to live in the shared
    // structural set today — it must go straight to MEDFIX (branch 6), not
    // be swept into "everything structural -> Head Office" and cloned fresh.
    private const KNOWN_BRANCH_OVERRIDES = [
        'MEDFIX HNB' => 6,
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $custGroupId = AccountGroup::where('code', 'CUST_ACCTS')->value('id');
        $suppGroupId = AccountGroup::where('code', 'SUPP_ACCTS')->value('id');

        // acc_trade_receivables / acc_trade_payables are general CONTROL
        // accounts (e.g. "Trade Receivables - General") that were filed
        // under the CUST_ACCTS/SUPP_ACCTS groups but aren't owned by any
        // single customer/supplier — they must be treated as structural,
        // not swept up as an "unresolved" per-entity sub-ledger row.
        $controlAccountIds = SystemSetting::whereIn('key', ['acc_trade_receivables', 'acc_trade_payables'])
            ->whereNull('branch_id')
            ->pluck('value')
            ->map(fn($v) => (int) $v)
            ->all();

        $overrideIds = Account::whereIn('name', array_keys(self::KNOWN_BRANCH_OVERRIDES))->pluck('id', 'name');

        $custAccounts = Account::where('group_id', $custGroupId)
            ->whereNotIn('id', $controlAccountIds)
            ->get(['id', 'name']);
        $suppAccounts = Account::where('group_id', $suppGroupId)
            ->whereNotIn('id', $controlAccountIds)
            ->get(['id', 'name']);
        $structural   = Account::whereNotIn('group_id', [$custGroupId, $suppGroupId])
            ->orWhereIn('id', $controlAccountIds)
            ->get(['id', 'code', 'name'])
            ->whereNotIn('id', $overrideIds->values())
            ->values();

        $custByAccountId = Customer::whereNotNull('account_id')->pluck('branch_id', 'account_id');
        $suppByAccountId = Supplier::whereNotNull('account_id')->pluck('branch_id', 'account_id');

        $custUpdates = []; $custUnresolved = [];
        foreach ($custAccounts as $a) {
            $b = $custByAccountId[$a->id] ?? null;
            if ($b) $custUpdates[$b][] = $a->id; else $custUnresolved[] = $a;
        }
        $suppUpdates = []; $suppUnresolved = [];
        foreach ($suppAccounts as $a) {
            $b = $suppByAccountId[$a->id] ?? null;
            if ($b) $suppUpdates[$b][] = $a->id; else $suppUnresolved[] = $a;
        }

        $this->info("Customer sub-ledger accounts: {$custAccounts->count()}");
        foreach ($custUpdates as $branchId => $ids) $this->line('  branch_id=' . $branchId . ': ' . count($ids) . ' accounts');
        if ($custUnresolved) {
            $this->warn('  Unresolved (no matching customer found), left NULL: ' . count($custUnresolved));
            foreach ($custUnresolved as $a) $this->line("    - #{$a->id} {$a->name}");
        }

        $this->info("Supplier sub-ledger accounts: {$suppAccounts->count()}");
        foreach ($suppUpdates as $branchId => $ids) $this->line('  branch_id=' . $branchId . ': ' . count($ids) . ' accounts');
        if ($suppUnresolved) {
            $this->warn('  Unresolved (no matching supplier found), left NULL: ' . count($suppUnresolved));
            foreach ($suppUnresolved as $a) $this->line("    - #{$a->id} {$a->name}");
        }

        $this->info("Structural accounts -> branch_id=1 (HEAD OFFICE): {$structural->count()}");
        foreach ($structural as $a) $this->line("  {$a->code}\t{$a->name}");

        $this->info('Known branch overrides (existing account assigned directly, not cloned): ' . $overrideIds->count());
        foreach (self::KNOWN_BRANCH_OVERRIDES as $name => $branchId) {
            $id = $overrideIds[$name] ?? null;
            $this->line('  "' . $name . '" (#' . ($id ?? '?') . ') -> branch_id=' . $branchId . ($id ? '' : '  <-- NOT FOUND, skipping'));
        }

        if (!$apply) {
            $this->warn('DRY RUN — no changes written. Re-run with --apply to commit.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($custUpdates, $suppUpdates, $structural, $overrideIds) {
            foreach ($custUpdates as $branchId => $ids) {
                Account::whereIn('id', $ids)->update(['branch_id' => $branchId]);
            }
            foreach ($suppUpdates as $branchId => $ids) {
                Account::whereIn('id', $ids)->update(['branch_id' => $branchId]);
            }
            Account::whereIn('id', $structural->pluck('id'))->update(['branch_id' => 1]);
            foreach (self::KNOWN_BRANCH_OVERRIDES as $name => $branchId) {
                if (isset($overrideIds[$name])) {
                    Account::where('id', $overrideIds[$name])->update(['branch_id' => $branchId]);
                }
            }
        });
        $this->info('APPLIED.');
        return self::SUCCESS;
    }
}
