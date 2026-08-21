<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\Branch;
use App\Models\SystemSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloneChartOfAccounts extends Command
{
    protected $signature   = 'medri:clone-chart-of-accounts
                                {--branch= : Target branch id to clone the structural chart of accounts into}
                                {--apply : Actually write the changes (default is dry-run)}';
    protected $description = 'Clone Head Office\'s structural (non sub-ledger) accounts into a fresh, zero-balance chart of accounts for another branch, and point that branch\'s acc_* settings at the clones';

    private const ACC_SETTING_KEYS = [
        'acc_trade_receivables', 'acc_trade_payables', 'acc_sales_revenue', 'acc_sales_returns',
        'acc_cogs', 'acc_inventory', 'acc_cash', 'acc_bank', 'acc_tax_payable',
        'acc_cheques_in_hand', 'acc_sales_cancelled', 'acc_service_income',
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $targetBranchId = (int) $this->option('branch');
        if (!$targetBranchId) {
            $this->error('Pass --branch=<id> for the target branch (e.g. 7 for MEDRI EAST, 6 for MEDFIX).');
            return self::FAILURE;
        }
        $branch = Branch::find($targetBranchId);
        if (!$branch) {
            $this->error("Branch {$targetBranchId} not found.");
            return self::FAILURE;
        }

        $custGroupId = AccountGroup::where('code', 'CUST_ACCTS')->value('id');
        $suppGroupId = AccountGroup::where('code', 'SUPP_ACCTS')->value('id');

        // acc_trade_receivables/acc_trade_payables are general CONTROL accounts
        // filed under the CUST_ACCTS/SUPP_ACCTS groups by group_id, but they're
        // structural (not owned by any single customer/supplier) — same
        // carve-out as medri:backfill-account-branch.
        $controlAccountIds = SystemSetting::whereIn('key', ['acc_trade_receivables', 'acc_trade_payables'])
            ->where(fn($q) => $q->where('branch_id', 1)->orWhereNull('branch_id'))
            ->pluck('value')
            ->map(fn($v) => (int) $v)
            ->all();

        $source = Account::where('branch_id', 1)
            ->where(fn($q) => $q->whereNotIn('group_id', [$custGroupId, $suppGroupId])->orWhereIn('id', $controlAccountIds))
            ->orderBy('code')
            ->get();

        // Guard against re-cloning by NAME collision, not a blanket count — the
        // target branch may legitimately already have unrelated accounts (its
        // own customer/supplier sub-ledger rows, or a known pre-existing
        // structural account like MEDFIX's own bank account), which don't
        // collide with the source names about to be cloned.
        $collidingNames = Account::where('branch_id', $targetBranchId)
            ->whereIn('name', $source->pluck('name'))
            ->pluck('name');
        if ($collidingNames->isNotEmpty()) {
            $this->error("Branch {$targetBranchId} already has account(s) named: " . $collidingNames->implode(', ') . " — refusing to clone again (would duplicate). Inspect manually if this needs redoing.");
            return self::FAILURE;
        }

        $this->info("Cloning {$source->count()} structural accounts from Head Office into {$branch->name} (branch {$targetBranchId}), prefix '{$branch->code}-':");
        foreach ($source as $a) {
            $this->line("  {$a->code} \"{$a->name}\"  ->  {$branch->code}-{$a->code}");
        }

        $existingSettings = SystemSetting::whereIn('key', self::ACC_SETTING_KEYS)
            ->where(fn($q) => $q->where('branch_id', 1)->orWhereNull('branch_id'))
            ->orderByRaw('CASE WHEN branch_id IS NULL THEN 1 ELSE 0 END')
            ->get()
            ->unique('key')
            ->keyBy('key');

        $this->info('acc_* settings to be created for branch ' . $targetBranchId . ':');
        foreach (self::ACC_SETTING_KEYS as $key) {
            $setting = $existingSettings->get($key);
            $sourceAccountId = $setting ? (int) $setting->value : null;
            $sourceAccount = $sourceAccountId ? $source->firstWhere('id', $sourceAccountId) : null;
            $this->line('  ' . $key . ' -> currently account #' . ($sourceAccountId ?? '?') . ' (' . ($sourceAccount->name ?? 'NOT FOUND IN STRUCTURAL SET') . ')');
            if (!$sourceAccount) {
                $this->error("    Cannot resolve a clone for {$key} — its source account isn't in the structural set (check group).");
            }
        }

        if (!$apply) {
            $this->warn('DRY RUN — no changes written. Re-run with --apply to commit.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($source, $branch, $existingSettings, $targetBranchId) {
            $idMap = [];
            foreach ($source as $a) {
                $clone = Account::create([
                    'group_id'        => $a->group_id,
                    'name'            => $a->name,
                    'code'            => "{$branch->code}-{$a->code}",
                    'type'            => $a->type,
                    'normal_balance'  => $a->normal_balance,
                    'opening_balance' => 0,
                    'is_bank_account' => $a->is_bank_account,
                    'is_cash_account' => $a->is_cash_account,
                    'branch_id'       => $targetBranchId,
                    'is_active'       => $a->is_active,
                    'description'     => $a->description,
                ]);
                $idMap[$a->id] = $clone->id;
            }

            foreach (self::ACC_SETTING_KEYS as $key) {
                $setting = $existingSettings->get($key);
                if (!$setting) continue;
                $sourceAccountId = (int) $setting->value;
                $newAccountId = $idMap[$sourceAccountId] ?? null;
                if (!$newAccountId) continue;
                SystemSetting::create([
                    'branch_id' => $targetBranchId,
                    'key'       => $key,
                    'value'     => (string) $newAccountId,
                    'group'     => $setting->group,
                ]);
            }
        });
        $this->info('APPLIED.');
        return self::SUCCESS;
    }
}
