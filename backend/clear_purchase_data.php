<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CLEARING SUPPLIER INVOICE / PURCHASE / GRN DATA ===\n\n";

DB::statement('SET FOREIGN_KEY_CHECKS=0');

// GRN items
$n = DB::table('grn_items')->count();
DB::table('grn_items')->truncate();
echo "grn_items:            deleted $n rows\n";

// GRNs
$n = DB::table('goods_receipt_notes')->count();
DB::table('goods_receipt_notes')->truncate();
echo "goods_receipt_notes:  deleted $n rows\n";

// PO items
$n = DB::table('purchase_order_items')->count();
DB::table('purchase_order_items')->truncate();
echo "purchase_order_items: deleted $n rows\n";

// Supplier payments (for POs and supplier invoices)
$n = DB::table('supplier_payments')->count();
DB::table('supplier_payments')->truncate();
echo "supplier_payments:    deleted $n rows\n";

// Supplier invoices
$n = DB::table('supplier_invoices')->count();
DB::table('supplier_invoices')->truncate();
echo "supplier_invoices:    deleted $n rows\n";

// Purchase orders
$n = DB::table('purchase_orders')->count();
DB::table('purchase_orders')->truncate();
echo "purchase_orders:      deleted $n rows\n";

// Stock movements from purchases only
$n = DB::table('stock_movements')
    ->whereIn('type', ['purchase_in'])
    ->whereIn('reference_type', ['grn', 'purchase_order', 'supplier_invoice'])
    ->count();
DB::table('stock_movements')
    ->whereIn('type', ['purchase_in'])
    ->whereIn('reference_type', ['grn', 'purchase_order', 'supplier_invoice'])
    ->delete();
echo "stock_movements (purchase_in): deleted $n rows\n";

// Journal entries from GRN / purchase
$jeIds = DB::table('journal_entries')
    ->whereIn('reference_type', ['grn', 'purchase_order', 'supplier_invoice'])
    ->pluck('id');
if ($jeIds->isNotEmpty()) {
    DB::table('journal_entry_lines')->whereIn('journal_entry_id', $jeIds)->delete();
    DB::table('journal_entries')->whereIn('id', $jeIds)->delete();
    echo "journal_entries (purchase): deleted {$jeIds->count()} entries\n";
} else {
    echo "journal_entries (purchase): none found\n";
}

// Reset product_branch_stock to zero
// (recalculate from remaining stock movements — sales returns, adjustments, etc.)
$products = DB::table('stock_movements')
    ->select('product_id', 'branch_id',
        DB::raw('SUM(quantity) as total_qty'),
        DB::raw('AVG(CASE WHEN unit_cost > 0 THEN unit_cost ELSE NULL END) as avg_cost'))
    ->groupBy('product_id', 'branch_id')
    ->get();

// First zero out all
DB::table('product_branch_stock')->update(['quantity' => 0, 'avg_cost' => 0]);

// Restore from remaining movements
foreach ($products as $row) {
    DB::table('product_branch_stock')
        ->updateOrInsert(
            ['product_id' => $row->product_id, 'branch_id' => $row->branch_id],
            ['quantity' => max(0, $row->total_qty), 'avg_cost' => $row->avg_cost ?? 0]
        );
}
echo "product_branch_stock: recalculated from remaining movements\n";

DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "\n=== DONE ===\n";

// Show current stock after reset
echo "\nStock after reset:\n";
$stocks = DB::table('product_branch_stock as pbs')
    ->join('products as p', 'p.id', '=', 'pbs.product_id')
    ->join('branches as b', 'b.id', '=', 'pbs.branch_id')
    ->select('p.name', 'b.name as branch', 'pbs.quantity', 'pbs.avg_cost')
    ->where('pbs.quantity', '>', 0)
    ->orderBy('p.name')
    ->get();
foreach ($stocks as $s) {
    echo "  {$s->name} | {$s->branch} | qty={$s->quantity} | avg_cost={$s->avg_cost}\n";
}
if ($stocks->isEmpty()) echo "  (all stock cleared)\n";
