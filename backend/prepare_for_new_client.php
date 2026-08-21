<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== PREPARING DATABASE FOR NEW CLIENT (OREKS) ===\n\n";

// The single admin user to keep. Everyone else gets deleted.
$keepUserId = 1; // admin@medri.lk

// The only accounts that stay: the ones wired into system_settings
// (acc_* keys) across every branch. Everything else — per-customer/
// supplier AR/AP accounts, one-off expense accounts, branch-duplicated
// chart-of-accounts entries — is client-specific and gets removed.
$keepAccountIds = DB::table('system_settings')
    ->where('key', 'like', 'acc_%')
    ->pluck('value')
    ->map(fn ($v) => (int) $v)
    ->unique()
    ->values()
    ->all();

echo 'Keeping user id: '.$keepUserId."\n";
echo 'Keeping '.count($keepAccountIds)." accounts (system-linked, all branches): ".implode(',', $keepAccountIds)."\n\n";

DB::statement('SET FOREIGN_KEY_CHECKS=0');

// ── 1. Wipe all transactional / demo business data ──────────────────────
$truncateTables = [
    // Sales
    'invoice_items', 'invoice_payments', 'invoices',
    // Procurement
    'grn_items', 'goods_receipt_notes', 'purchase_order_items', 'purchase_orders',
    'supplier_invoices', 'supplier_payments',
    'purchase_return_items', 'purchase_returns',
    // Inventory
    'product_branch_stock', 'batches', 'stock_movements',
    'stock_adjustment_items', 'stock_adjustments',
    'inter_branch_transfers', 'transfer_items',
    'products', 'product_categories', 'services', 'service_categories',
    // Parties
    'customers', 'suppliers', 'bank_accounts',
    // Accounting transactions
    'journal_entry_lines', 'journal_entries',
    'cheque_invoice_links', 'cheques',
    'expenses', 'expense_categories', 'tax_rates',
    // HR
    'attendances', 'holidays', 'leave_balances', 'leave_requests', 'leave_types',
    'salary_components', 'payroll_runs', 'payslips',
    'job_openings', 'candidates', 'candidate_interviews', 'candidate_status_history',
    'performance_cycles', 'performance_goals', 'performance_reviews',
    'checklist_template_items', 'checklist_templates', 'employee_checklist_tasks',
    'employee_documents', 'employee_history', 'employees',
    'assets', 'asset_assignments',
    'departments', 'designations',
    // Org-wide
    'targets', 'announcements', 'announcement_reads',
    'calendar_event_attendees', 'calendar_event_completions', 'calendar_events',
    'task_comments', 'tasks',
    'office_directory', 'audit_logs', 'notifications', 'dashboard_widgets',
    'role_widget_settings',
    // Auth / sessions (force everyone to log back in fresh)
    'personal_access_tokens', 'sessions', 'user_sessions', 'password_reset_tokens',
    // Framework housekeeping
    'jobs', 'job_batches', 'failed_jobs', 'cache', 'cache_locks',
];

foreach ($truncateTables as $table) {
    if (! DB::getSchemaBuilder()->hasTable($table)) {
        echo "  (skip) $table — table not found\n";
        continue;
    }
    $n = DB::table($table)->count();
    DB::table($table)->truncate();
    echo str_pad($table, 32).": deleted $n rows\n";
}

// ── 2. Trim accounts down to the system-linked set ───────────────────────
$n = DB::table('accounts')->whereNotIn('id', $keepAccountIds)->count();
DB::table('accounts')->whereNotIn('id', $keepAccountIds)->delete();
echo "\n".str_pad('accounts (non-system)', 32).": deleted $n rows, kept ".count($keepAccountIds)."\n";

// Reset carried-over historical balances on the accounts we kept — a new
// client starts at zero, not with the old tenant's trading history.
$n = DB::table('accounts')->whereIn('id', $keepAccountIds)->where('opening_balance', '!=', 0)->count();
DB::table('accounts')->whereIn('id', $keepAccountIds)->update(['opening_balance' => 0]);
echo str_pad('accounts opening_balance', 32).": reset $n rows to 0\n";

// account_groups: kept in full (13 groups — structural, not client data)
echo str_pad('account_groups', 32).': kept all '.DB::table('account_groups')->count()." rows\n";

// ── 3. Trim users down to the one admin ───────────────────────────────────
$n = DB::table('users')->where('id', '!=', $keepUserId)->count();
DB::table('users')->where('id', '!=', $keepUserId)->delete();
echo "\n".str_pad('users', 32).": deleted $n rows, kept user #$keepUserId\n";

$n = DB::table('branch_user')->where('user_id', '!=', $keepUserId)->count();
DB::table('branch_user')->where('user_id', '!=', $keepUserId)->delete();
echo str_pad('branch_user', 32).": deleted $n rows\n";

$n = DB::table('model_has_roles')->where('model_type', 'App\\Models\\User')->where('model_id', '!=', $keepUserId)->count();
DB::table('model_has_roles')->where('model_type', 'App\\Models\\User')->where('model_id', '!=', $keepUserId)->delete();
echo str_pad('model_has_roles', 32).": deleted $n rows\n";

$n = DB::table('model_has_permissions')->where('model_type', 'App\\Models\\User')->where('model_id', '!=', $keepUserId)->count();
DB::table('model_has_permissions')->where('model_type', 'App\\Models\\User')->where('model_id', '!=', $keepUserId)->delete();
echo str_pad('model_has_permissions', 32).": deleted $n rows\n";

// roles / permissions / role_has_permissions: kept in full (RBAC config, not client data)
echo str_pad('roles / permissions', 32).': kept all (RBAC config untouched)'."\n";

// ── 4. Reset document numbering so new records start clean ───────────────
$n = DB::table('document_sequences')->count();
DB::table('document_sequences')->update(['last_number' => 0]);
echo "\n".str_pad('document_sequences', 32).": reset $n counters to 0\n";

// ── 5. Reference/lookup data left untouched: branches, financial_years,
//      system_settings, cities, districts, banks, migrations ────────────

DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "\n=== DONE ===\n";
echo "\nRemaining admin login: ";
$u = DB::table('users')->find($keepUserId);
echo ($u->email ?? 'unknown')."\n";

echo "\nAccounts kept:\n";
foreach (DB::table('accounts')->orderBy('group_id')->orderBy('code')->get(['code', 'name', 'opening_balance']) as $a) {
    echo "  {$a->code}\t{$a->name}\t(opening_balance={$a->opening_balance})\n";
}
