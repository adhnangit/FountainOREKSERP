<?php

namespace Database\Seeders;

use App\Models\RoleWidgetSetting;
use Illuminate\Database\Seeder;

class RoleWidgetSettingsSeeder extends Seeder
{
    // All available widget keys in default display order
    public const WIDGETS = [
        ['key' => 'kpi_overview',      'label' => 'KPI Overview (Customers, Products, Suppliers, Invoices)'],
        ['key' => 'financials',         'label' => 'Financial KPIs (Sales, Purchases, Expenses, Outstanding)'],
        ['key' => 'revenue_chart',      'label' => 'Daily Revenue & Collections Chart'],
        ['key' => 'cheque_summary',     'label' => 'Cheque Summary KPIs'],
        ['key' => 'branch_performance', 'label' => 'Branch Performance Table & Chart'],
        ['key' => 'target_progress',    'label' => 'Target Progress Tracker'],
        ['key' => 'today_sales',        'label' => 'Period Sales Report Table'],
        ['key' => 'sales_reps_aging',   'label' => 'Sales Reps & Customer Aging'],
        ['key' => 'charts',             'label' => 'Best Products & Expense Category Charts'],
        ['key' => 'due_tables',         'label' => 'Monthly Due Tables (Sales & Purchases)'],
        ['key' => 'low_stock',          'label' => 'Low Stock Alerts'],
    ];

    private const ROLE_DEFAULTS = [
        'super_admin' => [
            'kpi_overview', 'financials', 'revenue_chart', 'cheque_summary',
            'branch_performance', 'target_progress', 'today_sales',
            'sales_reps_aging', 'charts', 'due_tables', 'low_stock',
        ],
        'branch_manager' => [
            'kpi_overview', 'financials', 'revenue_chart', 'cheque_summary',
            'branch_performance', 'target_progress', 'today_sales',
            'sales_reps_aging', 'charts', 'due_tables', 'low_stock',
        ],
        'sales_person' => [
            'kpi_overview', 'today_sales', 'target_progress', 'low_stock',
        ],
        'accountant' => [
            'kpi_overview', 'financials', 'revenue_chart', 'cheque_summary',
            'cheque_details', 'due_tables', 'sales_reps_aging',
        ],
        'inventory_manager' => [
            'kpi_overview', 'low_stock', 'branch_performance',
        ],
        'purchase_officer' => [
            'kpi_overview', 'low_stock', 'today_sales', 'due_tables',
        ],
        'hr_admin' => [
            'kpi_overview', 'target_progress',
        ],
        'viewer' => [
            'kpi_overview', 'today_sales',
        ],
    ];

    public function run(): void
    {
        $allKeys = array_column(self::WIDGETS, 'key');

        foreach (self::ROLE_DEFAULTS as $role => $visibleKeys) {
            foreach ($allKeys as $idx => $key) {
                RoleWidgetSetting::updateOrInsert(
                    ['role_name' => $role, 'widget_key' => $key],
                    [
                        'is_visible'  => in_array($key, $visibleKeys),
                        'sort_order'  => $idx,
                        'updated_at'  => now(),
                        'created_at'  => now(),
                    ]
                );
            }
        }

        $this->command->info('✅  Role widget settings seeded.');
    }
}
