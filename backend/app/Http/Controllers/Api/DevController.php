<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevController extends Controller
{
    private const CLEANUP_MAP = [
        'invoices' => [
            'description' => 'Invoices',
            'tables'      => ['cheque_invoice_links', 'invoice_payments', 'invoice_items', 'invoices'],
            'conditions'  => ['invoices' => ['type', 'invoice']],
        ],
        'proforma' => [
            'description' => 'Proforma Invoices',
            'tables'      => ['invoice_items', 'invoices'],
            'conditions'  => ['invoices' => ['type', 'proforma']],
        ],
        'supplier_invoices' => [
            'description' => 'Supplier Invoices & Payments',
            'tables'      => ['supplier_payments', 'supplier_invoices'],
            'conditions'  => [],
        ],
        'purchase' => [
            'description' => 'Purchase Orders & GRNs',
            'tables'      => [
                'grn_items',
                'goods_receipt_notes',
                'purchase_order_items',
                'purchase_orders',
            ],
            'conditions'  => [],
        ],
        'cheques' => [
            'description' => 'Cheques',
            'tables'      => ['cheque_invoice_links', 'cheques'],
            'conditions'  => [],
        ],
        'expenses' => [
            'description' => 'Expenses',
            'tables'      => ['expenses'],
            'conditions'  => [],
        ],
        'ledger' => [
            'description' => 'Journal Entries & Ledger',
            'tables'      => ['journal_entry_lines', 'journal_entries'],
            'conditions'  => [],
        ],
    ];

    public function cleanup(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->is_super_admin && !$user->hasRole('super_admin')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $target   = $request->input('target', 'all');
        $branchId = $request->input('branch_id');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $cleared = [];

        if ($target === 'all') {
            foreach (self::CLEANUP_MAP as $key => $config) {
                $this->clearGroup($config, $branchId);
                $cleared[] = $config['description'];
            }
            if (!$branchId) {
                DB::table('stock_movements')->truncate();
                $cleared[] = 'Stock Movements';
                DB::table('audit_logs')->truncate();
            }
        } elseif (isset(self::CLEANUP_MAP[$target])) {
            $this->clearGroup(self::CLEANUP_MAP[$target], $branchId);
            $cleared[] = self::CLEANUP_MAP[$target]['description'];
            if (in_array($target, ['purchase', 'supplier_invoices']) && !$branchId) {
                DB::table('stock_movements')->truncate();
                $cleared[] = 'Stock Movements';
            }
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return response()->json(['message' => 'Unknown target.'], 422);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $suffix = $branchId ? ' (Branch #' . $branchId . ')' : ' (All Branches)';
        return response()->json([
            'message' => 'Cleared: ' . implode(', ', $cleared) . $suffix,
            'cleared' => $cleared,
        ]);
    }

    private function clearGroup(array $config, ?int $branchId): void
    {
        $branchable = [
            'invoices', 'expenses', 'cheques', 'supplier_invoices',
            'purchase_orders', 'goods_receipt_notes',
        ];
        $childTables = [
            'invoice_items', 'invoice_payments', 'cheque_invoice_links',
            'supplier_payments', 'grn_items', 'purchase_order_items',
            'journal_entry_lines', 'journal_entries',
        ];

        foreach ($config['tables'] as $table) {
            $hasTypeCond = isset($config['conditions'][$table]);
            $q = DB::table($table);

            if ($hasTypeCond) {
                [$col, $val] = $config['conditions'][$table];
                $q->where($col, $val);
            }

            if ($branchId && in_array($table, $branchable)) {
                $q->where('branch_id', $branchId);
            }

            if ($branchId && in_array($table, $childTables)) {
                continue;
            }

            if (!$hasTypeCond && !$branchId && !in_array($table, $branchable)) {
                $q->truncate();
            } else {
                $q->delete();
            }
        }
    }

    public function counts(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->is_super_admin && !$user->hasRole('super_admin')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $branchId = $request->query('branch_id');

        return response()->json([
            'proforma'          => DB::table('invoices')->where('type', 'proforma')
                                     ->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count(),
            'invoices'          => DB::table('invoices')->where('type', 'invoice')
                                     ->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count(),
            'supplier_invoices' => DB::table('supplier_invoices')
                                     ->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count(),
            'purchase'          => DB::table('purchase_orders')
                                     ->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count(),
            'expenses'          => DB::table('expenses')
                                     ->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count(),
            'cheques'           => DB::table('cheques')
                                     ->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count(),
            'ledger'            => DB::table('journal_entries')->count(),
        ]);
    }
}
