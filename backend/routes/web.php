<?php

use App\Models\Branch;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// Streams branch logos because the web root is the repo root, not backend/public,
// so the public storage symlink is never reachable over HTTP.
Route::get('/branch-logo/{branch}', function (Branch $branch) {
    $path = $branch->logo_path;
    abort_unless($path && Storage::disk('public')->exists($path), 404);
    $mimes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    return response(Storage::disk('public')->get($path), 200, [
        'Content-Type' => $mimes[$ext] ?? 'application/octet-stream',
        'Cache-Control' => 'public, max-age=86400',
    ]);
})->name('branch-logo');

Route::get('/login', fn() => view('auth.login'))->name('login');
Route::get('/logout', fn() => redirect('/login'));

Route::get('/', fn() => view('dashboard.index'))->name('dashboard');

Route::get('/invoices',           fn() => view('invoices.index'))->name('invoices.index');
Route::get('/invoices/create',    fn() => view('invoices.create'))->name('invoices.create');
Route::get('/invoices/{id}/edit', fn() => view('invoices.edit'))->name('invoices.edit');
Route::get('/invoices/{id}',      fn() => view('invoices.show'))->name('invoices.show');

Route::get('/sales-returns',        fn() => view('returns.index'))->name('sales-returns.index');
Route::get('/sales-returns/create', fn() => view('returns.create'))->name('sales-returns.create');

Route::get('/proforma-invoices',               fn() => view('proforma.index'))->name('proforma.index');
Route::get('/proforma-invoices/create',        fn() => view('proforma.create'))->name('proforma.create');
Route::get('/proforma-invoices/{id}/convert',  fn() => view('proforma.convert'))->name('proforma.convert');
Route::get('/proforma-invoices/{id}',          fn() => view('proforma.show'))->name('proforma.show');

Route::get('/purchase-orders',        fn() => view('purchase.orders'))->name('purchase-orders.index');
Route::get('/purchase-orders/create', fn() => view('purchase.orders-create'))->name('purchase-orders.create');
Route::get('/purchase-orders/{id}',   fn() => view('purchase.orders-show'))->name('purchase-orders.show');

Route::get('/purchase-returns',        fn() => view('purchase.returns'))->name('purchase-returns.index');
Route::get('/purchase-returns/create', fn() => view('purchase.returns-create'))->name('purchase-returns.create');

Route::get('/grns',        fn() => view('purchase.grns'))->name('grns.index');
Route::get('/grns/create', fn() => view('purchase.grns-create'))->name('grns.create');
Route::get('/grns/{id}',   fn() => view('purchase.grns-show'))->name('grns.show');

Route::get('/supplier-invoices', fn() => view('purchase.supplier-invoices'))->name('supplier-invoices.index');

Route::get('/customers',        fn() => view('customers.index'))->name('customers.index');
Route::get('/customers/create', fn() => view('customers.create'))->name('customers.create');
Route::get('/customers/{id}/bulk-payment', fn() => view('customers.bulk-payment'))->name('customers.bulk-payment');
Route::get('/customers/{id}',   fn() => view('customers.show'))->name('customers.show');

Route::get('/suppliers',            fn() => view('suppliers.index'))->name('suppliers.index');
Route::get('/suppliers/create',     fn() => view('suppliers.create'))->name('suppliers.create');
Route::get('/suppliers/{id}/edit',  fn() => view('suppliers.edit'))->name('suppliers.edit');
Route::get('/suppliers/{id}/bulk-payment', fn() => view('suppliers.bulk-payment'))->name('suppliers.bulk-payment');
Route::get('/suppliers/{id}',       fn() => view('suppliers.show'))->name('suppliers.show');

Route::get('/products',              fn() => view('products.index'))->name('products.index');
Route::get('/products/create',       fn() => view('products.create'))->name('products.create');
Route::get('/products/categories',   fn() => view('products.categories'))->name('products.categories');
Route::get('/products/{id}/edit',    fn() => view('products.edit'))->name('products.edit');
Route::get('/products/{id}',         fn() => view('products.show'))->name('products.show');

Route::get('/services',              fn() => view('services.index'))->name('services.index');
Route::get('/services/create',       fn() => view('services.create'))->name('services.create');
Route::get('/services/categories',   fn() => view('services.categories'))->name('services.categories');

Route::get('/inventory/transfers',        fn() => view('inventory.transfers'))->name('transfers.index');
Route::get('/inventory/transfers/create', fn() => view('inventory.transfers-create'))->name('transfers.create');
Route::get('/inventory/transfers/{id}',   fn($id) => view('inventory.transfers-show', ['id' => $id]))->name('transfers.show');
Route::get('/inventory/adjustments',        fn() => view('inventory.adjustments'))->name('adjustments.index');
Route::get('/inventory/adjustments/create', fn() => view('inventory.adjustments-create'))->name('adjustments.create');
Route::get('/inventory/low-stock',   fn() => view('inventory.low-stock'))->name('low-stock.index');

Route::get('/cheques',          fn() => view('cheques.index'))->name('cheques.index');
Route::get('/cheques/calendar', fn() => view('cheques.calendar'))->name('cheques.calendar');
Route::get('/cheques/history',  fn() => view('cheques.history'))->name('cheques.history');

Route::get('/expenses',           fn() => view('expenses.index'))->name('expenses.index');
Route::get('/expenses/create',    fn() => view('expenses.create'))->name('expenses.create');
Route::get('/expenses/{id}/edit', fn() => view('expenses.edit'))->name('expenses.edit');
Route::get('/expenses/{id}',      fn() => view('expenses.show'))->name('expenses.show');

Route::get('/targets', fn() => view('targets.index'))->name('targets.index');

Route::get('/accounting/chart-of-accounts', fn() => view('accounting.chart-of-accounts'))->name('chart-of-accounts');
Route::get('/accounting/journal',           fn() => view('accounting.journal'))->name('journal');
Route::get('/accounting/trial-balance',     fn() => view('accounting.trial-balance'))->name('trial-balance');
Route::get('/accounting/profit-loss',       fn() => view('accounting.profit-loss'))->name('profit-loss');
Route::get('/accounting/balance-sheet',     fn() => view('accounting.balance-sheet'))->name('balance-sheet');
Route::get('/accounting/ledger',            fn() => view('accounting.ledger'))->name('ledger');
Route::get('/accounting/settings',          fn() => view('accounting.settings'))->name('accounting-settings');

Route::get('/reports',                 fn() => view('reports.index'))->name('reports.index');
Route::get('/reports/sales',          fn() => view('reports.sales'))->name('reports.sales');
Route::get('/reports/purchase',       fn() => view('reports.purchase'))->name('reports.purchase');
Route::get('/reports/inventory',      fn() => view('reports.inventory'))->name('reports.inventory');
Route::get('/reports/expenses',       fn() => view('reports.expenses'))->name('reports.expenses');
Route::get('/reports/customer-aging', fn() => view('reports.customer-aging'))->name('reports.customer-aging');
Route::get('/reports/supplier-aging', fn() => view('reports.supplier-aging'))->name('reports.supplier-aging');
Route::get('/reports/cheques',        fn() => view('reports.cheques'))->name('reports.cheques');
Route::get('/reports/targets',        fn() => view('reports.targets'))->name('reports.targets');
Route::get('/reports/stock-movement', fn() => view('reports.stock-movement'))->name('reports.stock-movement');

Route::get('/hr/employees',        fn() => view('hr.employees-index'))->name('hr.employees.index');
Route::get('/hr/employees/create', fn() => view('hr.employees-create'))->name('hr.employees.create');
Route::get('/hr/employees/{id}',   fn() => view('hr.employees-show'))->name('hr.employees.show');
Route::get('/hr/org-chart',        fn() => view('hr.org-chart'))->name('hr.org-chart');
Route::get('/hr/departments',      fn() => view('hr.departments'))->name('hr.departments');
Route::get('/hr/designations',     fn() => view('hr.designations'))->name('hr.designations');
Route::get('/hr/attendance',       fn() => view('hr.attendance'))->name('hr.attendance');
Route::get('/hr/holidays',         fn() => view('hr.holidays'))->name('hr.holidays');
Route::get('/hr/leave-requests',   fn() => view('hr.leave-requests'))->name('hr.leave-requests');
Route::get('/hr/leave-balances',   fn() => view('hr.leave-balances'))->name('hr.leave-balances');
Route::get('/hr/leave-types',      fn() => view('hr.leave-types'))->name('hr.leave-types');
Route::get('/hr/payroll',          fn() => view('hr.payroll'))->name('hr.payroll');
Route::get('/hr/payroll/{id}',     fn() => view('hr.payroll-show'))->name('hr.payroll.show');
Route::get('/hr/job-openings',     fn() => view('hr.job-openings'))->name('hr.job-openings');
Route::get('/hr/candidates',       fn() => view('hr.candidates-index'))->name('hr.candidates.index');
Route::get('/hr/candidates/{id}',  fn() => view('hr.candidates-show'))->name('hr.candidates.show');
Route::get('/hr/performance-cycles',  fn() => view('hr.performance-cycles'))->name('hr.performance-cycles');
Route::get('/hr/performance-reviews', fn() => view('hr.performance-reviews'))->name('hr.performance-reviews');
Route::get('/hr/checklist-templates', fn() => view('hr.checklist-templates'))->name('hr.checklist-templates');
Route::get('/hr/assets',           fn() => view('hr.assets'))->name('hr.assets');
Route::get('/hr/reports',          fn() => view('hr.reports'))->name('hr.reports');
Route::get('/announcements',       fn() => view('announcements.index'))->name('announcements');
Route::get('/my',                  fn() => view('my.dashboard'))->name('my.dashboard');
Route::get('/manager/team',        fn() => view('manager.team'))->name('manager.team');

Route::get('/calendar',  fn() => view('calendar.index'))->name('calendar');
Route::get('/directory', fn() => view('directory.index'))->name('directory');

Route::get('/task-manager',            fn() => view('task-manager.dashboard'))->name('task-manager.dashboard');
Route::get('/task-manager/board',      fn() => view('task-manager.board'))->name('task-manager.board');
Route::get('/task-manager/categories', fn() => view('task-manager.categories'))->name('task-manager.categories');

Route::get('/access-control/users',           fn() => view('access-control.users'))->name('users');
Route::get('/access-control/users/{id}/edit', fn() => view('access-control.users'))->name('users.edit');
Route::get('/access-control/roles',           fn() => view('access-control.roles'))->name('roles');
Route::get('/access-control/roles/{id}/edit', fn() => view('access-control.roles'))->name('roles.edit');
Route::get('/access-control/activity-log', fn() => view('access-control.activity-log'))->name('activity-log');

Route::get('/settings', fn() => view('settings.index'))->name('settings');
Route::get('/settings/branches', fn() => view('settings.branches'))->name('settings.branches');
Route::get('/settings/banks', fn() => view('settings.banks'))->name('settings.banks');
Route::get('/settings/districts-cities', fn() => view('settings.districts-cities'))->name('settings.districts-cities');
Route::get('/settings/dashboard-widgets', fn() => view('settings.dashboard-widgets'))->name('settings.dashboard-widgets');
