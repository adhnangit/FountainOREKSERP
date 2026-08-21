<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Dashboard
            'dashboard.view',

            // Sales / Invoices
            'invoices.view',
            'invoices.create',
            'invoices.edit',
            'invoices.delete',
            'invoices.confirm',
            'invoices.payment',
            'invoices.cancel',

            // Proforma Invoices
            'proforma.view',
            'proforma.create',
            'proforma.convert',

            // Procurement
            'purchase_orders.view',
            'purchase_orders.create',
            'purchase_orders.approve',
            'purchase_orders.payment',
            'grns.view',
            'grns.create',
            'grns.confirm',
            'supplier_invoices.view',
            'supplier_invoices.create',
            'supplier_invoices.payment',

            // Customers
            'customers.view',
            'customers.create',
            'customers.edit',
            'customers.delete',

            // Suppliers
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',

            // Products
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            // Services
            'services.view',
            'services.create',
            'services.edit',
            'services.delete',

            // Inventory
            'inventory.view',
            'inventory.transfers.create',
            'inventory.transfers.approve',
            'inventory.adjustments.create',
            'inventory.adjustments.approve',

            // Cheques
            'cheques.view',
            'cheques.create',
            'cheques.update',
            'cheques.delete',
            'cheques.process',
            'cheques.details',
            'cheques.history',

            // Expenses
            'expenses.view',
            'expenses.create',
            'expenses.approve',
            'expenses.delete',

            // Targets
            'targets.view',
            'targets.create',
            'targets.edit',

            // Accounting
            'accounting.view',
            'accounting.journal.create',
            'accounting.reports',
            'accounting.settings',

            // Reports
            'reports.view',

            // Calendar & Tasks
            'calendar.view',
            'calendar.create',
            'tasks.view',
            'tasks.create',

            // Office Directory
            'directory.view',
            'directory.create',

            // Access Control
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.view',
            'roles.edit',
            'activity_log.view',

            // Settings & Branches
            'settings.view',
            'settings.edit',
            'branches.view',
            'branches.create',
            'branches.edit',
            'branches.delete',

            // Human Resources
            'hr.employees.view',
            'hr.employees.create',
            'hr.employees.edit',
            'hr.employees.delete',
            'hr.departments.view',
            'hr.departments.create',
            'hr.departments.edit',
            'hr.departments.delete',
            'hr.designations.view',
            'hr.designations.create',
            'hr.designations.edit',
            'hr.designations.delete',
            'hr.attendance.view',
            'hr.attendance.create',
            'hr.attendance.edit',
            'hr.attendance.delete',
            'hr.holidays.view',
            'hr.holidays.create',
            'hr.holidays.edit',
            'hr.holidays.delete',
            'hr.leave_types.view',
            'hr.leave_types.create',
            'hr.leave_types.edit',
            'hr.leave_types.delete',
            'hr.leave_balances.view',
            'hr.leave_balances.edit',
            'hr.leave_requests.view',
            'hr.leave_requests.create',
            'hr.leave_requests.approve',
            'hr.leave_requests.cancel',
            'hr.salary_components.view',
            'hr.salary_components.create',
            'hr.salary_components.edit',
            'hr.salary_components.delete',
            'hr.payroll.view',
            'hr.payroll.create',
            'hr.payroll.pay',
            'hr.payroll.delete',
            'hr.jobs.view',
            'hr.jobs.create',
            'hr.jobs.edit',
            'hr.jobs.delete',
            'hr.candidates.view',
            'hr.candidates.create',
            'hr.candidates.edit',
            'hr.candidates.delete',
            'hr.candidates.hire',
            'hr.performance.view',
            'hr.performance.create',
            'hr.performance.edit',
            'hr.performance.delete',
            'hr.checklists.view',
            'hr.checklists.create',
            'hr.checklists.edit',
            'hr.checklists.delete',
            'hr.assets.view',
            'hr.assets.create',
            'hr.assets.edit',
            'hr.assets.delete',
            'hr.assets.assign',
            'hr.announcements.manage',
            'hr.reports.view',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Default permission sets per role
        $rolePermissions = [
            'super_admin'       => $permissions, // all
            'branch_manager'    => [
                'dashboard.view',
                'invoices.view','invoices.create','invoices.edit','invoices.confirm','invoices.payment','invoices.cancel',
                'proforma.view','proforma.create','proforma.convert',
                'purchase_orders.view','purchase_orders.create','purchase_orders.approve','purchase_orders.payment',
                'grns.view','grns.create','grns.confirm',
                'supplier_invoices.view','supplier_invoices.create','supplier_invoices.payment',
                'customers.view','customers.create','customers.edit',
                'suppliers.view','suppliers.create','suppliers.edit',
                'products.view','products.create','products.edit',
                'services.view','services.create','services.edit',
                'inventory.view','inventory.transfers.create','inventory.adjustments.create',
                'cheques.view','cheques.create','cheques.update','cheques.delete','cheques.process','cheques.details','cheques.history',
                'expenses.view','expenses.create','expenses.approve',
                'targets.view','targets.create','targets.edit',
                'accounting.view','accounting.reports',
                'reports.view',
                'calendar.view','calendar.create',
                'tasks.view','tasks.create',
                'directory.view','directory.create',
                'users.view',
                'branches.view',
                'settings.view',
            ],
            'sales_person'      => [
                'dashboard.view',
                'invoices.view','invoices.create','invoices.confirm','invoices.payment',
                'proforma.view','proforma.create','proforma.convert',
                'customers.view','customers.create','customers.edit',
                'products.view',
                'services.view',
                'cheques.view','cheques.create','cheques.details','cheques.history',
                'expenses.view','expenses.create',
                'targets.view',
                'calendar.view','calendar.create',
                'tasks.view','tasks.create',
                'directory.view',
                'reports.view',
            ],
            'accountant'        => [
                'dashboard.view',
                'invoices.view','invoices.payment',
                'purchase_orders.view','purchase_orders.payment',
                'supplier_invoices.view','supplier_invoices.payment',
                'customers.view',
                'suppliers.view',
                'cheques.view','cheques.update','cheques.process','cheques.details','cheques.history',
                'expenses.view','expenses.approve',
                'accounting.view','accounting.journal.create','accounting.reports','accounting.settings',
                'reports.view',
                'calendar.view',
                'tasks.view',
                'directory.view',
            ],
            'inventory_manager' => [
                'dashboard.view',
                'products.view','products.create','products.edit',
                'services.view','services.create','services.edit',
                'inventory.view','inventory.transfers.create','inventory.transfers.approve',
                'inventory.adjustments.create','inventory.adjustments.approve',
                'grns.view','grns.create','grns.confirm',
                'purchase_orders.view',
                'reports.view',
                'calendar.view',
                'tasks.view',
                'directory.view',
            ],
            'purchase_officer'  => [
                'dashboard.view',
                'purchase_orders.view','purchase_orders.create',
                'grns.view','grns.create',
                'supplier_invoices.view','supplier_invoices.create',
                'suppliers.view','suppliers.create','suppliers.edit',
                'products.view',
                'inventory.view',
                'cheques.view','cheques.details',
                'calendar.view',
                'tasks.view',
                'directory.view',
                'reports.view',
            ],
            'hr_admin'          => [
                'dashboard.view',
                'users.view','users.create','users.edit',
                'expenses.view','expenses.create','expenses.approve',
                'targets.view','targets.create','targets.edit',
                'calendar.view','calendar.create',
                'tasks.view','tasks.create',
                'directory.view','directory.create',
                'activity_log.view',
                'hr.employees.view','hr.employees.create','hr.employees.edit','hr.employees.delete',
                'hr.departments.view','hr.departments.create','hr.departments.edit','hr.departments.delete',
                'hr.designations.view','hr.designations.create','hr.designations.edit','hr.designations.delete',
                'hr.attendance.view','hr.attendance.create','hr.attendance.edit','hr.attendance.delete',
                'hr.holidays.view','hr.holidays.create','hr.holidays.edit','hr.holidays.delete',
                'hr.leave_types.view','hr.leave_types.create','hr.leave_types.edit','hr.leave_types.delete',
                'hr.leave_balances.view','hr.leave_balances.edit',
                'hr.leave_requests.view','hr.leave_requests.create','hr.leave_requests.approve','hr.leave_requests.cancel',
                'hr.salary_components.view','hr.salary_components.create','hr.salary_components.edit','hr.salary_components.delete',
                'hr.payroll.view','hr.payroll.create','hr.payroll.pay','hr.payroll.delete',
                'hr.jobs.view','hr.jobs.create','hr.jobs.edit','hr.jobs.delete',
                'hr.candidates.view','hr.candidates.create','hr.candidates.edit','hr.candidates.delete','hr.candidates.hire',
                'hr.performance.view','hr.performance.create','hr.performance.edit','hr.performance.delete',
                'hr.checklists.view','hr.checklists.create','hr.checklists.edit','hr.checklists.delete',
                'hr.assets.view','hr.assets.create','hr.assets.edit','hr.assets.delete','hr.assets.assign',
                'hr.announcements.manage',
                'hr.reports.view',
            ],
            'viewer'            => [
                'dashboard.view',
                'invoices.view',
                'proforma.view',
                'purchase_orders.view',
                'grns.view',
                'supplier_invoices.view',
                'customers.view',
                'suppliers.view',
                'products.view',
                'services.view',
                'inventory.view',
                'cheques.view','cheques.details','cheques.history',
                'expenses.view',
                'accounting.view','accounting.reports',
                'reports.view',
                'calendar.view',
                'tasks.view',
                'directory.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->syncPermissions($perms);
            }
        }

        $this->command->info('✅  Permissions seeded and assigned to roles.');
    }
}
