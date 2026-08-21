<?php

use App\Http\Controllers\Api\AccountingController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BankController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\DevController;
use App\Http\Controllers\Api\ProformaController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\ChequeController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DesignationController;
use App\Http\Controllers\Api\DirectoryController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\EmployeeDocumentController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\LeaveBalanceController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\LeaveTypeController;
use App\Http\Controllers\Api\CandidateController;
use App\Http\Controllers\Api\CandidateInterviewController;
use App\Http\Controllers\Api\JobOpeningController;
use App\Http\Controllers\Api\PayrollRunController;
use App\Http\Controllers\Api\SalaryComponentController;
use App\Http\Controllers\Api\PerformanceCycleController;
use App\Http\Controllers\Api\PerformanceReviewController;
use App\Http\Controllers\Api\PerformanceGoalController;
use App\Http\Controllers\Api\ChecklistTemplateController;
use App\Http\Controllers\Api\EmployeeChecklistTaskController;
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\AnnouncementController;
use App\Http\Controllers\Api\MyEmployeeController;
use App\Http\Controllers\Api\ManagerController;
use App\Http\Controllers\Api\HrReportController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\SalesReturnController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\PurchaseReturnController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TargetController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\DashboardWidgetSettingsController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/auth/login', [AuthController::class, 'login']);

// Authenticated
Route::middleware(['auth:sanctum', 'branch.context'])->group(function () {
    // Auth — self-service, no extra permission needed beyond being logged in
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
    Route::post('/auth/switch-branch', [AuthController::class, 'switchBranch']);

    // Dashboard
    Route::middleware('permission:dashboard.view')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/customer-map', [DashboardController::class, 'customerMap']);
    });
    // Widget layout is personal UI preference — no extra gate beyond auth
    Route::get('/dashboard-widget-settings', [DashboardWidgetSettingsController::class, 'forCurrentUser']);
    Route::post('/dashboard-widget-settings', [DashboardWidgetSettingsController::class, 'save']);
    Route::middleware('permission:settings.view')->get('/dashboard-widget-settings/admin', [DashboardWidgetSettingsController::class, 'adminIndex']);

    // Branches
    Route::apiResource('branches', BranchController::class, ['only' => ['index', 'show']])->middleware('permission:branches.view');
    Route::middleware('permission:branches.view')->get('/branches/{branch}/stats', [BranchController::class, 'stats']);
    Route::apiResource('branches', BranchController::class, ['only' => ['store']])->middleware('permission:branches.create');
    Route::apiResource('branches', BranchController::class, ['only' => ['update']])->middleware('permission:branches.edit');
    Route::apiResource('branches', BranchController::class, ['only' => ['destroy']])->middleware('permission:branches.delete');

    // Customers
    Route::apiResource('customers', CustomerController::class, ['only' => ['index', 'show']])->middleware('permission:customers.view');
    Route::middleware('permission:customers.view')->group(function () {
        Route::get('/customers/{customer}/ledger', [CustomerController::class, 'ledger']);
        Route::get('/customers-aging', [CustomerController::class, 'aging']);
    });
    Route::apiResource('customers', CustomerController::class, ['only' => ['store']])->middleware('permission:customers.create');
    Route::apiResource('customers', CustomerController::class, ['only' => ['update']])->middleware('permission:customers.edit');
    Route::middleware('permission:customers.edit')->post('/customers/{customer}/opening-balance-payment', [CustomerController::class, 'payOpeningBalance']);
    Route::middleware('permission:customers.view')->get('/customers/{customer}/opening-balance-payments', [CustomerController::class, 'openingBalancePayments']);
    Route::middleware('permission:customers.edit')->delete('/customers/{customer}/opening-balance-payments/{journalEntry}', [CustomerController::class, 'deleteOpeningBalancePayment']);
    Route::apiResource('customers', CustomerController::class, ['only' => ['destroy']])->middleware('permission:customers.delete');

    // Suppliers
    Route::apiResource('suppliers', SupplierController::class, ['only' => ['index', 'show']])->middleware('permission:suppliers.view');
    Route::middleware('permission:suppliers.view')->get('/suppliers/{supplier}/ledger', [SupplierController::class, 'ledger']);
    Route::apiResource('suppliers', SupplierController::class, ['only' => ['store']])->middleware('permission:suppliers.create');
    Route::apiResource('suppliers', SupplierController::class, ['only' => ['update']])->middleware('permission:suppliers.edit');
    Route::apiResource('suppliers', SupplierController::class, ['only' => ['destroy']])->middleware('permission:suppliers.delete');
    Route::middleware('permission:suppliers.edit')->post('/suppliers/{supplier}/opening-balance-payment', [SupplierController::class, 'payOpeningBalance']);
    Route::middleware('permission:suppliers.view')->get('/suppliers/{supplier}/opening-balance-payments', [SupplierController::class, 'openingBalancePayments']);
    Route::middleware('permission:suppliers.edit')->delete('/suppliers/{supplier}/opening-balance-payments/{journalEntry}', [SupplierController::class, 'deleteOpeningBalancePayment']);

    // Products & Inventory
    Route::middleware('permission:products.view')->group(function () {
        Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
        Route::get('/products/categories', [ProductController::class, 'categories']);
        Route::get('/products/{product}/stock', [ProductController::class, 'stock']);
        Route::get('/products/{product}/batches', [ProductController::class, 'batches']);
        Route::get('/products/{product}/movements', [ProductController::class, 'movements']);
    });
    Route::middleware('role:super_admin')->put('/products/{product}/batches/{batch}', [ProductController::class, 'updateBatch']);
    Route::apiResource('products', ProductController::class, ['only' => ['index', 'show']])->middleware('permission:products.view');
    Route::middleware('permission:products.categories.create')->post('/products/categories', [ProductController::class, 'storeCategory']);
    Route::middleware('permission:products.edit')->put('/products/categories/{category}', [ProductController::class, 'updateCategory']);
    Route::middleware('permission:products.delete')->delete('/products/categories/{category}', [ProductController::class, 'destroyCategory']);
    Route::apiResource('products', ProductController::class, ['only' => ['store']])->middleware('permission:products.create');
    Route::apiResource('products', ProductController::class, ['only' => ['update']])->middleware('permission:products.edit');
    Route::apiResource('products', ProductController::class, ['only' => ['destroy']])->middleware('permission:products.delete');

    // Services
    Route::middleware('permission:services.view')->group(function () {
        Route::get('/services/categories', [ServiceController::class, 'categories']);
    });
    Route::apiResource('services', ServiceController::class, ['only' => ['index', 'show']])->middleware('permission:services.view');
    Route::middleware('permission:services.create')->post('/services/categories', [ServiceController::class, 'storeCategory']);
    Route::middleware('permission:services.edit')->put('/services/categories/{category}', [ServiceController::class, 'updateCategory']);
    Route::middleware('permission:services.delete')->delete('/services/categories/{category}', [ServiceController::class, 'destroyCategory']);
    Route::apiResource('services', ServiceController::class, ['only' => ['store']])->middleware('permission:services.create');
    Route::apiResource('services', ServiceController::class, ['only' => ['update']])->middleware('permission:services.edit');
    Route::apiResource('services', ServiceController::class, ['only' => ['destroy']])->middleware('permission:services.delete');

    // Proforma Invoices
    Route::middleware('permission:proforma.view')->group(function () {
        Route::get('/proforma-invoices', [ProformaController::class, 'index']);
        Route::get('/proforma-invoices/{invoice}', [ProformaController::class, 'show']);
    });
    Route::middleware('permission:proforma.create')->post('/proforma-invoices', [ProformaController::class, 'store']);
    Route::middleware('permission:proforma.convert')->post('/proforma-invoices/{invoice}/convert', [ProformaController::class, 'convert']);

    // Invoices
    Route::middleware('permission:invoices.view')->group(function () {
        Route::get('/invoices-stats', [InvoiceController::class, 'stats']);
        Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'pdf']);
    });
    // Sales Returns (credit notes) — reuse invoice permissions
    Route::middleware('permission:invoices.view')->group(function () {
        Route::get('/sales-returns', [SalesReturnController::class, 'index']);
        Route::get('/sales-returns/{invoice}', [SalesReturnController::class, 'show']);
        Route::get('/invoices/{invoice}/returnable', [SalesReturnController::class, 'returnable']);
    });
    Route::middleware('permission:invoices.create')->post('/sales-returns', [SalesReturnController::class, 'store']);

    Route::apiResource('invoices', InvoiceController::class, ['only' => ['index', 'show']])->middleware('permission:invoices.view');
    Route::middleware('permission:invoices.create')->post('/invoices/check-stock', [InvoiceController::class, 'checkStock']);
    Route::apiResource('invoices', InvoiceController::class, ['only' => ['store']])->middleware('permission:invoices.create');
    Route::apiResource('invoices', InvoiceController::class, ['only' => ['update']])->middleware('permission:invoices.edit');
    Route::apiResource('invoices', InvoiceController::class, ['only' => ['destroy']])->middleware('permission:invoices.delete');
    Route::middleware('permission:invoices.confirm')->post('/invoices/{invoice}/confirm', [InvoiceController::class, 'confirm']);
    Route::middleware('permission:proforma.convert')->post('/invoices/{invoice}/convert', [InvoiceController::class, 'convertProforma']);
    Route::middleware('permission:invoices.payment')->post('/invoices/{invoice}/payment', [InvoiceController::class, 'recordPayment']);
    Route::middleware('permission:invoices.payment')->post('/customers/{customer}/bulk-payment', [InvoiceController::class, 'bulkPayment']);
    Route::middleware('role:super_admin')->delete('/invoices/{invoice}/payments/{payment}', [InvoiceController::class, 'deletePayment']);
    Route::middleware('role:super_admin')->get('/invoices/{invoice}/profit', [InvoiceController::class, 'profit']);
    Route::middleware('permission:invoices.cancel')->post('/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel']);
    Route::middleware('permission:invoices.delete')->delete('/invoices/{invoice}', [InvoiceController::class, 'destroy']);

    // Purchase Orders & GRN
    Route::middleware('permission:purchase_orders.view')->group(function () {
        Route::get('/purchase-orders', [PurchaseController::class, 'indexPO']);
        Route::get('/purchase-orders/{purchaseOrder}', [PurchaseController::class, 'showPO']);
    });
    Route::middleware('permission:purchase_orders.create')->post('/purchase-orders', [PurchaseController::class, 'storePO']);
    Route::middleware('permission:purchase_orders.approve')->post('/purchase-orders/{purchaseOrder}/approve', [PurchaseController::class, 'approvePO']);
    Route::middleware('permission:purchase_orders.payment')->post('/purchase-orders/{purchaseOrder}/payment', [PurchaseController::class, 'recordPOPayment']);
    Route::middleware('permission:purchase_orders.payment')->post('/suppliers/{supplier}/bulk-payment', [PurchaseController::class, 'bulkPaymentPO']);
    Route::middleware('role:super_admin')->delete('/purchase-orders/{purchaseOrder}/payments/{payment}', [PurchaseController::class, 'deletePOPayment']);

    // Purchase Returns — reuse purchase order permissions
    Route::middleware('permission:purchase_orders.view')->group(function () {
        Route::get('/purchase-returns', [PurchaseReturnController::class, 'index']);
        Route::get('/purchase-returns/{purchaseReturn}', [PurchaseReturnController::class, 'show']);
        Route::get('/purchase-orders/{purchaseOrder}/returnable', [PurchaseReturnController::class, 'returnable']);
    });
    Route::middleware('permission:purchase_orders.create')->post('/purchase-returns', [PurchaseReturnController::class, 'store']);

    Route::middleware('permission:grns.view')->group(function () {
        Route::get('/grns', [PurchaseController::class, 'indexGRN']);
        Route::get('/grns/{goodsReceiptNote}', [PurchaseController::class, 'showGRN']);
    });
    Route::middleware('permission:grns.create')->post('/grns', [PurchaseController::class, 'storeGRN']);
    Route::middleware('permission:grns.confirm')->post('/grns/{goodsReceiptNote}/confirm', [PurchaseController::class, 'confirmGRN']);

    Route::middleware('permission:supplier_invoices.view')->get('/supplier-invoices', [PurchaseController::class, 'indexSupplierInvoices']);
    Route::middleware('permission:supplier_invoices.create')->post('/supplier-invoices', [PurchaseController::class, 'storeSupplierInvoice']);
    Route::middleware('permission:supplier_invoices.payment')->post('/supplier-invoices/{supplierInvoice}/payment', [PurchaseController::class, 'recordSupplierPayment']);
    Route::middleware('permission:grns.confirm')->post('/supplier-invoices/{supplierInvoice}/receive', [PurchaseController::class, 'receiveItems']);

    // Company Settings
    Route::middleware('permission:settings.view')->get('/settings', [SettingsController::class, 'index']);
    Route::middleware('permission:settings.edit')->put('/settings', [SettingsController::class, 'update']);

    // Banks — no dedicated permission exists; treat as a settings-level resource
    Route::middleware('permission:settings.view')->get('/banks', [BankController::class, 'index']);
    Route::middleware('permission:settings.edit')->group(function () {
        Route::post('/banks', [BankController::class, 'store']);
        Route::put('/banks/{bank}', [BankController::class, 'update']);
        Route::delete('/banks/{bank}', [BankController::class, 'destroy']);
    });

    // Districts & Cities — same settings-level resource treatment as Banks
    Route::middleware('permission:settings.view')->group(function () {
        Route::get('/districts', [DistrictController::class, 'index']);
        Route::get('/cities', [CityController::class, 'index']);
    });
    Route::middleware('permission:settings.edit')->group(function () {
        Route::post('/districts', [DistrictController::class, 'store']);
        Route::put('/districts/{district}', [DistrictController::class, 'update']);
        Route::delete('/districts/{district}', [DistrictController::class, 'destroy']);
        Route::post('/cities', [CityController::class, 'store']);
        Route::put('/cities/{city}', [CityController::class, 'update']);
        Route::delete('/cities/{city}', [CityController::class, 'destroy']);
    });

    // Cheques
    Route::middleware('permission:cheques.view')->group(function () {
        Route::get('/cheques/due-today', [ChequeController::class, 'dueToday']);
        Route::get('/cheques/due-week', [ChequeController::class, 'dueThisWeek']);
        Route::get('/cheques/bank-summary', [ChequeController::class, 'bankSummary']);
    });
    Route::middleware('permission:cheques.history')->get('/cheques/party-history', [ChequeController::class, 'partyHistory']);
    Route::apiResource('cheques', ChequeController::class, ['only' => ['index', 'show']])->middleware('permission:cheques.view');
    Route::apiResource('cheques', ChequeController::class, ['only' => ['update']])->middleware('permission:cheques.update');

    // Expenses
    Route::middleware('permission:expenses.view')->get('/expenses/categories', [ExpenseController::class, 'categories']);
    Route::middleware('permission:expenses.create')->post('/expenses/categories', [ExpenseController::class, 'storeCategory']);
    Route::apiResource('expenses', ExpenseController::class, ['only' => ['index', 'show']])->middleware('permission:expenses.view');
    Route::apiResource('expenses', ExpenseController::class, ['only' => ['store']])->middleware('permission:expenses.create');
    Route::apiResource('expenses', ExpenseController::class, ['only' => ['update']])->middleware('permission:expenses.create');
    Route::apiResource('expenses', ExpenseController::class, ['only' => ['destroy']])->middleware('permission:expenses.delete');
    Route::middleware('permission:expenses.approve')->group(function () {
        Route::post('/expenses/{expense}/approve', [ExpenseController::class, 'approve']);
        Route::post('/expenses/{expense}/reject', [ExpenseController::class, 'reject']);
    });

    // Targets
    Route::middleware('permission:targets.view')->group(function () {
        Route::get('/targets/progress', [TargetController::class, 'progress']);
        Route::get('/targets/branch-summary', [TargetController::class, 'branchSummary']);
    });
    Route::apiResource('targets', TargetController::class, ['only' => ['index', 'show']])->middleware('permission:targets.view');
    Route::apiResource('targets', TargetController::class, ['only' => ['store']])->middleware('permission:targets.create');
    Route::apiResource('targets', TargetController::class, ['only' => ['update']])->middleware('permission:targets.edit');
    Route::apiResource('targets', TargetController::class, ['only' => ['destroy']])->middleware('permission:targets.edit');

    // Inventory Management
    Route::middleware('permission:inventory.view')->get('/transfers', [InventoryController::class, 'indexTransfers']);
    Route::middleware('permission:inventory.transfers.create')->post('/transfers', [InventoryController::class, 'requestTransfer']);
    Route::middleware('permission:inventory.transfers.approve')->group(function () {
        Route::post('/transfers/{interBranchTransfer}/approve', [InventoryController::class, 'approveTransfer']);
        Route::post('/transfers/{interBranchTransfer}/dispatch', [InventoryController::class, 'dispatchTransfer']);
        Route::post('/transfers/{interBranchTransfer}/receive', [InventoryController::class, 'receiveTransfer']);
    });

    Route::middleware('permission:inventory.view')->get('/adjustments', [InventoryController::class, 'indexAdjustments']);
    Route::middleware('permission:inventory.view')->get('/adjustments/{stockAdjustment}', [InventoryController::class, 'showAdjustment']);
    // No dedicated edit/delete permission — "create" covers managing drafts
    Route::middleware('permission:inventory.adjustments.create')->group(function () {
        Route::post('/adjustments', [InventoryController::class, 'storeAdjustment']);
        Route::put('/adjustments/{stockAdjustment}', [InventoryController::class, 'updateAdjustment']);
        Route::delete('/adjustments/{stockAdjustment}', [InventoryController::class, 'destroyAdjustment']);
    });
    Route::middleware('permission:inventory.adjustments.approve')->post('/adjustments/{stockAdjustment}/approve', [InventoryController::class, 'approveAdjustment']);

    // Calendar — no dedicated edit/delete permission; "create" covers manage
    Route::apiResource('events', CalendarController::class, ['only' => ['index', 'show']])->middleware('permission:calendar.view');
    Route::apiResource('events', CalendarController::class, ['only' => ['store', 'update', 'destroy']])->middleware('permission:calendar.create');
    Route::middleware('permission:calendar.create')->post('/events/{calendarEvent}/complete', [CalendarController::class, 'complete']);

    // Tasks — no dedicated edit/delete permission; "create" covers manage
    // Must be registered before the {task} wildcard routes below, else "assignable-users" is matched as a task id.
    Route::middleware('permission:tasks.create')->get('/tasks/assignable-users', [TaskController::class, 'assignableUsers']);
    Route::apiResource('tasks', TaskController::class, ['only' => ['index', 'show']])->middleware('permission:tasks.view');
    Route::apiResource('tasks', TaskController::class, ['only' => ['store', 'update', 'destroy']])->middleware('permission:tasks.create');
    Route::middleware('permission:tasks.create')->post('/tasks/{task}/comments', [TaskController::class, 'addComment']);

    // Directory — no dedicated edit/delete permission; "create" covers manage
    Route::apiResource('directory', DirectoryController::class, ['only' => ['index', 'show']])->middleware('permission:directory.view');
    Route::apiResource('directory', DirectoryController::class, ['only' => ['store', 'update', 'destroy']])->middleware('permission:directory.create');

    // Users & Access Control
    Route::apiResource('users', UserController::class, ['only' => ['index', 'show']])->middleware('permission:users.view');
    Route::apiResource('users', UserController::class, ['only' => ['store']])->middleware('permission:users.create');
    Route::apiResource('users', UserController::class, ['only' => ['update']])->middleware('permission:users.edit');
    Route::apiResource('users', UserController::class, ['only' => ['destroy']])->middleware('permission:users.delete');
    Route::middleware('permission:users.edit')->post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
    Route::middleware('permission:roles.view')->group(function () {
        Route::get('/roles', [UserController::class, 'roles']);
        Route::get('/permissions', [UserController::class, 'permissions']);
    });
    Route::middleware('permission:roles.edit')->group(function () {
        Route::post('/roles', [UserController::class, 'storeRole']);
        Route::put('/roles/{role}', [UserController::class, 'updateRole']);
    });
    Route::middleware('permission:activity_log.view')->get('/activity-log', [UserController::class, 'activityLog']);

    // Accounting
    Route::prefix('accounting')->group(function () {
        // Chart of Accounts
        Route::middleware('permission:accounting.view')->get('/accounts', [AccountingController::class, 'accounts']);
        Route::middleware('permission:accounting.view')->get('/accounts/opening-balance-check', [AccountingController::class, 'openingBalanceCheck']);
        Route::middleware('permission:accounting.settings')->group(function () {
            Route::post('/accounts', [AccountingController::class, 'storeAccount']);
            Route::put('/accounts/{account}', [AccountingController::class, 'updateAccount']);
            Route::delete('/accounts/{account}', [AccountingController::class, 'destroyAccount']);
        });

        // Account Groups
        Route::middleware('permission:accounting.view')->get('/groups', [AccountingController::class, 'groups']);
        Route::middleware('permission:accounting.settings')->post('/groups', [AccountingController::class, 'storeGroup']);

        // Financial Years
        Route::middleware('permission:accounting.view')->get('/financial-years', [AccountingController::class, 'financialYears']);
        Route::middleware('permission:accounting.settings')->post('/financial-years', [AccountingController::class, 'storeFinancialYear']);

        // Journal Entries
        Route::middleware('permission:accounting.view')->group(function () {
            Route::get('/journal-entries', [AccountingController::class, 'journalEntries']);
            Route::get('/journal-entries/{journalEntry}', [AccountingController::class, 'showJournalEntry']);
        });
        Route::middleware('permission:accounting.journal.create')->group(function () {
            Route::post('/journal-entries', [AccountingController::class, 'storeJournalEntry']);
            Route::delete('/journal-entries/{journalEntry}', [AccountingController::class, 'destroyJournalEntry']);
        });

        // Reports
        Route::middleware('permission:accounting.reports')->group(function () {
            Route::get('/trial-balance', [AccountingController::class, 'trialBalance']);
            Route::get('/profit-loss', [AccountingController::class, 'profitLoss']);
            Route::get('/balance-sheet', [AccountingController::class, 'balanceSheet']);
            Route::get('/general-ledger', [AccountingController::class, 'generalLedger']);
        });

        // Settings
        Route::middleware('permission:accounting.settings')->group(function () {
            Route::get('/settings', [AccountingController::class, 'getSettings']);
            Route::post('/settings', [AccountingController::class, 'saveSettings']);
        });
    });

    // Dev / Super Admin utilities
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/dev/counts', [DevController::class, 'counts']);
        Route::post('/dev/cleanup', [DevController::class, 'cleanup']);
    });

    // Reports
    Route::middleware('permission:reports.view')->prefix('reports')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales']);
        Route::get('/purchases', [ReportController::class, 'purchases']);
        Route::get('/inventory', [ReportController::class, 'inventory']);
        Route::get('/expenses', [ReportController::class, 'expenses']);
        Route::get('/targets', [ReportController::class, 'targets']);
        Route::get('/customer-aging', [ReportController::class, 'customerAging']);
        Route::get('/supplier-aging', [ReportController::class, 'supplierAging']);
        Route::get('/cheques', [ReportController::class, 'chequeReport']);
        Route::get('/stock-movements', [ReportController::class, 'stockMovements']);
    });

    // Human Resources
    Route::prefix('hr')->group(function () {
        Route::middleware('permission:hr.departments.view')->get('/departments', [DepartmentController::class, 'index']);
        Route::middleware('permission:hr.departments.create')->post('/departments', [DepartmentController::class, 'store']);
        Route::middleware('permission:hr.departments.edit')->put('/departments/{department}', [DepartmentController::class, 'update']);
        Route::middleware('permission:hr.departments.delete')->delete('/departments/{department}', [DepartmentController::class, 'destroy']);

        Route::middleware('permission:hr.designations.view')->get('/designations', [DesignationController::class, 'index']);
        Route::middleware('permission:hr.designations.create')->post('/designations', [DesignationController::class, 'store']);
        Route::middleware('permission:hr.designations.edit')->put('/designations/{designation}', [DesignationController::class, 'update']);
        Route::middleware('permission:hr.designations.delete')->delete('/designations/{designation}', [DesignationController::class, 'destroy']);

        Route::middleware('permission:hr.employees.view')->group(function () {
            Route::get('/employees', [EmployeeController::class, 'index']);
            Route::get('/employees/org-chart', [EmployeeController::class, 'orgChart']);
            Route::get('/employees/{employee}', [EmployeeController::class, 'show']);
            Route::get('/employees/{employee}/history', [EmployeeController::class, 'history']);
            Route::get('/employees/{employee}/photo', [EmployeeController::class, 'photo']);
            Route::get('/documents/{document}/stream', [EmployeeDocumentController::class, 'stream']);
        });
        Route::middleware('permission:hr.employees.create')->group(function () {
            Route::post('/employees', [EmployeeController::class, 'store']);
            Route::post('/employees/import', [EmployeeController::class, 'bulkImport']);
        });
        // PUT, not POST — the frontend sends multipart FormData (for photo upload) as
        // POST with a spoofed _method=PUT field, which Laravel routes as a PUT request.
        Route::middleware('permission:hr.employees.edit')->put('/employees/{employee}', [EmployeeController::class, 'update']);
        Route::middleware('permission:hr.employees.delete')->delete('/employees/{employee}', [EmployeeController::class, 'destroy']);

        Route::middleware('permission:hr.employees.edit')->post('/employees/{employee}/documents', [EmployeeDocumentController::class, 'store']);
        Route::middleware('permission:hr.employees.edit')->delete('/documents/{document}', [EmployeeDocumentController::class, 'destroy']);

        Route::middleware('permission:hr.holidays.view')->get('/holidays', [HolidayController::class, 'index']);
        Route::middleware('permission:hr.holidays.create')->post('/holidays', [HolidayController::class, 'store']);
        Route::middleware('permission:hr.holidays.edit')->put('/holidays/{holiday}', [HolidayController::class, 'update']);
        Route::middleware('permission:hr.holidays.delete')->delete('/holidays/{holiday}', [HolidayController::class, 'destroy']);

        Route::middleware('permission:hr.attendance.view')->group(function () {
            Route::get('/attendance', [AttendanceController::class, 'index']);
            Route::get('/attendance/for-date', [AttendanceController::class, 'forDate']);
            Route::get('/attendance/summary', [AttendanceController::class, 'summary']);
        });
        Route::middleware('permission:hr.attendance.create')->post('/attendance/bulk-mark', [AttendanceController::class, 'bulkMark']);
        Route::middleware('permission:hr.attendance.edit')->put('/attendance/{attendance}', [AttendanceController::class, 'update']);
        Route::middleware('permission:hr.attendance.delete')->delete('/attendance/{attendance}', [AttendanceController::class, 'destroy']);

        Route::middleware('permission:hr.leave_types.view')->get('/leave-types', [LeaveTypeController::class, 'index']);
        Route::middleware('permission:hr.leave_types.create')->post('/leave-types', [LeaveTypeController::class, 'store']);
        Route::middleware('permission:hr.leave_types.edit')->put('/leave-types/{leaveType}', [LeaveTypeController::class, 'update']);
        Route::middleware('permission:hr.leave_types.delete')->delete('/leave-types/{leaveType}', [LeaveTypeController::class, 'destroy']);

        Route::middleware('permission:hr.leave_balances.view')->get('/leave-balances', [LeaveBalanceController::class, 'index']);
        Route::middleware('permission:hr.leave_balances.edit')->group(function () {
            Route::post('/leave-balances/allocate', [LeaveBalanceController::class, 'allocate']);
            Route::put('/leave-balances/{leaveBalance}', [LeaveBalanceController::class, 'update']);
        });

        Route::middleware('permission:hr.leave_requests.view')->get('/leave-requests', [LeaveRequestController::class, 'index']);
        Route::middleware('permission:hr.leave_requests.create')->post('/leave-requests', [LeaveRequestController::class, 'store']);
        Route::middleware('permission:hr.leave_requests.approve')->group(function () {
            Route::post('/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve']);
            Route::post('/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject']);
        });
        Route::middleware('permission:hr.leave_requests.cancel')->post('/leave-requests/{leaveRequest}/cancel', [LeaveRequestController::class, 'cancel']);

        Route::middleware('permission:hr.salary_components.view')->get('/employees/{employee}/salary-components', [SalaryComponentController::class, 'index']);
        Route::middleware('permission:hr.salary_components.create')->post('/employees/{employee}/salary-components', [SalaryComponentController::class, 'store']);
        Route::middleware('permission:hr.salary_components.edit')->put('/salary-components/{salaryComponent}', [SalaryComponentController::class, 'update']);
        Route::middleware('permission:hr.salary_components.delete')->delete('/salary-components/{salaryComponent}', [SalaryComponentController::class, 'destroy']);

        Route::middleware('permission:hr.payroll.view')->group(function () {
            Route::get('/payroll-runs', [PayrollRunController::class, 'index']);
            Route::get('/payroll-runs/{payrollRun}', [PayrollRunController::class, 'show']);
            Route::get('/payslips/{payslip}/pdf', [PayrollRunController::class, 'payslipPdf']);
        });
        Route::middleware('permission:hr.payroll.create')->group(function () {
            Route::post('/payroll-runs', [PayrollRunController::class, 'store']);
            Route::post('/payroll-runs/{payrollRun}/regenerate', [PayrollRunController::class, 'regenerate']);
        });
        Route::middleware('permission:hr.payroll.pay')->post('/payroll-runs/{payrollRun}/mark-paid', [PayrollRunController::class, 'markPaid']);
        Route::middleware('permission:hr.payroll.delete')->delete('/payroll-runs/{payrollRun}', [PayrollRunController::class, 'destroy']);

        Route::middleware('permission:hr.jobs.view')->get('/job-openings', [JobOpeningController::class, 'index']);
        Route::middleware('permission:hr.jobs.view')->get('/job-openings/{jobOpening}', [JobOpeningController::class, 'show']);
        Route::middleware('permission:hr.jobs.create')->post('/job-openings', [JobOpeningController::class, 'store']);
        Route::middleware('permission:hr.jobs.edit')->put('/job-openings/{jobOpening}', [JobOpeningController::class, 'update']);
        Route::middleware('permission:hr.jobs.delete')->delete('/job-openings/{jobOpening}', [JobOpeningController::class, 'destroy']);

        Route::middleware('permission:hr.candidates.view')->group(function () {
            Route::get('/candidates', [CandidateController::class, 'index']);
            Route::get('/candidates/{candidate}', [CandidateController::class, 'show']);
            Route::get('/candidates/{candidate}/resume', [CandidateController::class, 'resume']);
        });
        Route::middleware('permission:hr.candidates.create')->post('/candidates', [CandidateController::class, 'store']);
        Route::middleware('permission:hr.candidates.edit')->group(function () {
            Route::put('/candidates/{candidate}', [CandidateController::class, 'update']);
            Route::post('/candidates/{candidate}/interviews', [CandidateInterviewController::class, 'store']);
            Route::put('/candidate-interviews/{candidateInterview}', [CandidateInterviewController::class, 'update']);
            Route::delete('/candidate-interviews/{candidateInterview}', [CandidateInterviewController::class, 'destroy']);
        });
        Route::middleware('permission:hr.candidates.delete')->delete('/candidates/{candidate}', [CandidateController::class, 'destroy']);
        Route::middleware('permission:hr.candidates.hire')->post('/candidates/{candidate}/hire', [CandidateController::class, 'hire']);

        // Performance Management
        Route::middleware('permission:hr.performance.view')->group(function () {
            Route::get('/performance-cycles', [PerformanceCycleController::class, 'index']);
            Route::get('/performance-reviews', [PerformanceReviewController::class, 'index']);
            Route::get('/performance-reviews/{performanceReview}', [PerformanceReviewController::class, 'show']);
            Route::get('/employees/{employee}/goals', [PerformanceGoalController::class, 'index']);
        });
        Route::middleware('permission:hr.performance.create')->group(function () {
            Route::post('/performance-cycles', [PerformanceCycleController::class, 'store']);
            Route::post('/performance-cycles/{performanceCycle}/generate-reviews', [PerformanceCycleController::class, 'generateReviews']);
            Route::post('/employees/{employee}/goals', [PerformanceGoalController::class, 'store']);
        });
        Route::middleware('permission:hr.performance.edit')->group(function () {
            Route::put('/performance-cycles/{performanceCycle}', [PerformanceCycleController::class, 'update']);
            Route::put('/performance-reviews/{performanceReview}', [PerformanceReviewController::class, 'update']);
            Route::put('/goals/{performanceGoal}', [PerformanceGoalController::class, 'update']);
        });
        Route::middleware('permission:hr.performance.delete')->group(function () {
            Route::delete('/performance-cycles/{performanceCycle}', [PerformanceCycleController::class, 'destroy']);
            Route::delete('/goals/{performanceGoal}', [PerformanceGoalController::class, 'destroy']);
        });

        // Onboarding / Offboarding checklists
        Route::middleware('permission:hr.checklists.view')->group(function () {
            Route::get('/checklist-templates', [ChecklistTemplateController::class, 'index']);
            Route::get('/employees/{employee}/checklist-tasks', [EmployeeChecklistTaskController::class, 'index']);
        });
        Route::middleware('permission:hr.checklists.create')->group(function () {
            Route::post('/checklist-templates', [ChecklistTemplateController::class, 'store']);
            Route::post('/checklist-templates/{checklistTemplate}/items', [ChecklistTemplateController::class, 'storeItem']);
            Route::post('/employees/{employee}/checklist-tasks', [EmployeeChecklistTaskController::class, 'store']);
            Route::post('/employees/{employee}/checklist-tasks/apply-template', [EmployeeChecklistTaskController::class, 'applyTemplate']);
        });
        Route::middleware('permission:hr.checklists.edit')->group(function () {
            Route::put('/checklist-templates/{checklistTemplate}', [ChecklistTemplateController::class, 'update']);
            Route::put('/checklist-tasks/{employeeChecklistTask}', [EmployeeChecklistTaskController::class, 'update']);
        });
        Route::middleware('permission:hr.checklists.delete')->group(function () {
            Route::delete('/checklist-templates/{checklistTemplate}', [ChecklistTemplateController::class, 'destroy']);
            Route::delete('/checklist-template-items/{checklistTemplateItem}', [ChecklistTemplateController::class, 'destroyItem']);
            Route::delete('/checklist-tasks/{employeeChecklistTask}', [EmployeeChecklistTaskController::class, 'destroy']);
        });

        // Asset Management
        Route::middleware('permission:hr.assets.view')->group(function () {
            Route::get('/assets', [AssetController::class, 'index']);
            Route::get('/assets/{asset}', [AssetController::class, 'show']);
        });
        Route::middleware('permission:hr.assets.create')->post('/assets', [AssetController::class, 'store']);
        Route::middleware('permission:hr.assets.edit')->put('/assets/{asset}', [AssetController::class, 'update']);
        Route::middleware('permission:hr.assets.delete')->delete('/assets/{asset}', [AssetController::class, 'destroy']);
        Route::middleware('permission:hr.assets.assign')->group(function () {
            Route::post('/assets/{asset}/assign', [AssetController::class, 'assign']);
            Route::post('/asset-assignments/{assetAssignment}/return', [AssetController::class, 'returnAsset']);
        });

        // Announcements management (viewing is unrestricted — see /announcements below)
        Route::middleware('permission:hr.announcements.manage')->group(function () {
            Route::post('/announcements', [AnnouncementController::class, 'store']);
            Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update']);
            Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy']);
        });

        Route::middleware('permission:hr.reports.view')->get('/reports/dashboard', [HrReportController::class, 'dashboard']);
    });

    // Announcements — company bulletin, visible to every authenticated user
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::post('/announcements/{announcement}/read', [AnnouncementController::class, 'markRead']);

    // Employee Self-Service — scoped to "my own" Employee record, no hr.* permission needed
    Route::prefix('my')->group(function () {
        Route::get('/employee', [MyEmployeeController::class, 'profile']);
        Route::put('/employee', [MyEmployeeController::class, 'updateProfile']);
        Route::get('/employee/photo', [MyEmployeeController::class, 'photo']);
        Route::get('/leave-balances', [MyEmployeeController::class, 'leaveBalances']);
        Route::get('/leave-requests', [MyEmployeeController::class, 'leaveRequests']);
        Route::post('/leave-requests', [MyEmployeeController::class, 'requestLeave']);
        Route::post('/leave-requests/{leaveRequest}/cancel', [MyEmployeeController::class, 'cancelLeaveRequest']);
        Route::get('/attendance', [MyEmployeeController::class, 'attendance']);
        Route::get('/documents', [MyEmployeeController::class, 'documents']);
        Route::get('/documents/{document}/stream', [MyEmployeeController::class, 'documentStream']);
        Route::get('/checklist-tasks', [MyEmployeeController::class, 'checklistTasks']);
        Route::get('/payslips', [MyEmployeeController::class, 'payslips']);
        Route::get('/payslips/{payslip}/pdf', [MyEmployeeController::class, 'payslipPdf']);
    });

    // Manager Portal — scoped to "employees who report to me", no hr.* permission needed
    Route::prefix('manager')->group(function () {
        Route::get('/team', [ManagerController::class, 'team']);
        Route::get('/team/attendance', [ManagerController::class, 'teamAttendanceForDate']);
        Route::post('/team/attendance/bulk-mark', [ManagerController::class, 'markTeamAttendance']);
        Route::get('/team/leave-requests', [ManagerController::class, 'teamLeaveRequests']);
        Route::post('/team/leave-requests/{leaveRequest}/approve', [ManagerController::class, 'approveTeamLeave']);
        Route::post('/team/leave-requests/{leaveRequest}/reject', [ManagerController::class, 'rejectTeamLeave']);
    });
});
