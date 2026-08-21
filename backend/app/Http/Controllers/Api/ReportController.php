<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cheque;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierInvoice;
use App\Models\Target;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    public function sales(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();
        $statuses  = $request->status ? [$request->status] : ['confirmed', 'partially_paid', 'paid'];

        $q = Invoice::query()
            ->where('type', 'invoice') // exclude credit notes — they share this table but aren't sales
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($request->from_date, fn($q) => $q->whereDate('invoice_date', '>=', $request->from_date))
            ->when($request->to_date,   fn($q) => $q->whereDate('invoice_date', '<=', $request->to_date))
            ->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id))
            ->when($request->sales_rep_id, fn($q) => $q->where('sales_rep_id', $request->sales_rep_id))
            ->whereIn('status', $statuses);

        $summary = [
            'total_sales'       => (clone $q)->sum('total'),
            'total_invoices'    => (clone $q)->count(),
            'total_collected'   => (clone $q)->sum('paid_amount'),
            'total_outstanding' => (clone $q)->sum('balance_due'),
            'avg_invoice'       => round((clone $q)->avg('total') ?? 0, 2),
        ];

        $byProduct = DB::table('invoice_items')
            ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.type', 'invoice')
            ->when($branchId, fn($q) => $q->where('invoices.branch_id', $branchId))
            ->when($request->from_date, fn($q) => $q->whereDate('invoices.invoice_date', '>=', $request->from_date))
            ->when($request->to_date,   fn($q) => $q->whereDate('invoices.invoice_date', '<=', $request->to_date))
            ->whereIn('invoices.status', $statuses)
            ->select(
                'invoice_items.product_id',
                'invoice_items.product_name',
                'invoice_items.product_code',
                DB::raw('SUM(invoice_items.quantity) as total_qty'),
                DB::raw('SUM(invoice_items.total) as total_revenue')
            )
            ->groupBy('invoice_items.product_id', 'invoice_items.product_name', 'invoice_items.product_code')
            ->orderByDesc('total_revenue')
            ->limit(30)
            ->get();

        $byCustomer = (clone $q)
            ->select('customer_id', DB::raw('COUNT(*) as invoice_count, SUM(total) as total_sales, SUM(paid_amount) as total_collected'))
            ->groupBy('customer_id')
            ->with('customer:id,name,phone')
            ->orderByDesc('total_sales')
            ->limit(30)
            ->get();

        $bySalesRep = (clone $q)
            ->select('sales_rep_id', DB::raw('COUNT(*) as invoice_count, SUM(total) as total_sales, SUM(paid_amount) as total_collected'))
            ->groupBy('sales_rep_id')
            ->with('salesRep:id,name,email')
            ->orderByDesc('total_sales')
            ->get();

        $trend = (clone $q)
            ->select(DB::raw('DATE(invoice_date) as date, SUM(total) as daily_sales, COUNT(*) as invoice_count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $invoices = (clone $q)
            ->with(['customer:id,name,phone', 'salesRep:id,name', 'branch:id,name'])
            ->orderByDesc('invoice_date')
            ->get();

        return response()->json(compact('summary', 'byProduct', 'byCustomer', 'bySalesRep', 'trend', 'invoices'));
    }

    public function purchases(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();

        $q = PurchaseOrder::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($request->from_date,   fn($q) => $q->whereDate('order_date', '>=', $request->from_date))
            ->when($request->to_date,     fn($q) => $q->whereDate('order_date', '<=', $request->to_date))
            ->when($request->supplier_id, fn($q) => $q->where('supplier_id', $request->supplier_id))
            ->when($request->status,      fn($q) => $q->where('status', $request->status));

        $summary = [
            'total_orders'      => (clone $q)->count(),
            'total_value'       => (clone $q)->sum('total'),
            'total_paid'        => (clone $q)->sum('paid_amount'),
            'total_outstanding' => (clone $q)->sum('balance_due'),
        ];

        $bySupplier = (clone $q)
            ->select('supplier_id', DB::raw('COUNT(*) as po_count, SUM(total) as total_purchases, SUM(paid_amount) as total_paid, SUM(balance_due) as total_outstanding'))
            ->groupBy('supplier_id')
            ->with('supplier:id,name,phone')
            ->orderByDesc('total_purchases')
            ->get();

        $trend = (clone $q)
            ->select(DB::raw('DATE(order_date) as date, SUM(total) as daily_total, COUNT(*) as order_count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $orders = (clone $q)
            ->with(['supplier:id,name,phone', 'branch:id,name'])
            ->orderByDesc('order_date')
            ->get();

        return response()->json(compact('summary', 'bySupplier', 'trend', 'orders'));
    }

    public function inventory(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();

        $rows = DB::table('product_branch_stock')
            ->join('products', 'product_branch_stock.product_id', '=', 'products.id')
            ->leftJoin('product_categories', 'products.category_id', '=', 'product_categories.id')
            ->when($branchId, fn($q) => $q->where('product_branch_stock.branch_id', $branchId))
            ->when($request->category_id, fn($q) => $q->where('products.category_id', $request->category_id))
            ->whereNull('products.deleted_at')
            ->select(
                'products.id',
                'products.name',
                'products.code',
                'product_categories.name as category',
                'products.category_id',
                DB::raw('COALESCE(product_branch_stock.quantity, 0) as stock_qty'),
                DB::raw('COALESCE(product_branch_stock.avg_cost, 0) as cost_price'),
                'products.selling_price',
                DB::raw('COALESCE(products.reorder_level, 0) as reorder_level'),
                DB::raw('COALESCE(product_branch_stock.quantity, 0) * COALESCE(product_branch_stock.avg_cost, 0) as stock_value')
            )
            ->orderBy('products.name')
            ->get();

        if ($request->stock_status === 'out') {
            $rows = $rows->filter(fn($p) => $p->stock_qty <= 0)->values();
        } elseif ($request->stock_status === 'low') {
            $rows = $rows->filter(fn($p) => $p->stock_qty > 0 && $p->stock_qty <= $p->reorder_level && $p->reorder_level > 0)->values();
        } elseif ($request->stock_status === 'in_stock') {
            $rows = $rows->filter(fn($p) => $p->stock_qty > $p->reorder_level || ($p->reorder_level == 0 && $p->stock_qty > 0))->values();
        }

        $summary = [
            'total_products' => $rows->count(),
            'total_value'    => round($rows->sum('stock_value'), 2),
            'out_of_stock'   => $rows->filter(fn($p) => $p->stock_qty <= 0)->count(),
            'low_stock'      => $rows->filter(fn($p) => $p->stock_qty > 0 && $p->stock_qty <= $p->reorder_level && $p->reorder_level > 0)->count(),
        ];

        return response()->json(['data' => $rows, 'summary' => $summary]);
    }

    public function expenses(Request $request): JsonResponse
    {
        $branchId     = $this->branchContext->getBranchId();
        $statusFilter = $request->status ?? 'approved';

        $q = Expense::query()
            ->when($statusFilter !== 'all', fn($q) => $q->where('status', $statusFilter))
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($request->from_date,   fn($q) => $q->whereDate('expense_date', '>=', $request->from_date))
            ->when($request->to_date,     fn($q) => $q->whereDate('expense_date', '<=', $request->to_date))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id));

        $summary = [
            'total_amount' => (clone $q)->sum('amount'),
            'count'        => (clone $q)->count(),
            'avg_amount'   => round((clone $q)->avg('amount') ?? 0, 2),
        ];

        $byCategory = (clone $q)
            ->select('category_id', DB::raw('SUM(amount) as total, COUNT(*) as count'))
            ->groupBy('category_id')
            ->with('category:id,name')
            ->orderByDesc('total')
            ->get();

        $trend = (clone $q)
            ->select(DB::raw('DATE_FORMAT(expense_date, "%Y-%m") as month, SUM(amount) as total, COUNT(*) as count'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $expenses = (clone $q)
            ->with(['category:id,name', 'createdBy:id,name', 'approvedBy:id,name'])
            ->orderByDesc('expense_date')
            ->get();

        return response()->json(compact('summary', 'byCategory', 'trend', 'expenses'));
    }

    public function targets(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();

        $targets = Target::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($request->year,    fn($q) => $q->where('year', $request->year))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->type,    fn($q) => $q->where('type', $request->type))
            ->with(['user:id,name,email', 'branch:id,name'])
            ->orderBy('year', 'desc')
            ->orderBy('period_number')
            ->get()
            ->map(fn($t) => array_merge($t->toArray(), [
                'achievement_percent' => $t->achievement_percent,
                'is_under_alert'      => $t->isUnderAlert(),
            ]));

        return response()->json($targets);
    }

    public function customerAging(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();
        $asOf     = $request->as_of_date ? now()->parse($request->as_of_date) : now();

        $invoices = Invoice::where('type', 'invoice') // exclude credit notes — they share this table but always carry balance_due=0
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['confirmed', 'partially_paid'])
            ->with('customer:id,name,phone')
            ->get();

        $result = [];
        foreach ($invoices as $inv) {
            $cid = $inv->customer_id;
            if (!isset($result[$cid])) {
                $result[$cid] = [
                    'customer_id'   => $cid,
                    'customer_name' => $inv->customer?->name ?? '—',
                    'phone'         => $inv->customer?->phone ?? '—',
                    'current'       => 0,
                    'days_31_60'    => 0,
                    'days_61_90'    => 0,
                    'days_90_plus'  => 0,
                    'total'         => 0,
                ];
            }
            $days   = $inv->due_date ? max(0, $asOf->diffInDays($inv->due_date, false) * -1) : 0;
            $bucket = match(true) {
                $days <= 30 => 'current',
                $days <= 60 => 'days_31_60',
                $days <= 90 => 'days_61_90',
                default     => 'days_90_plus',
            };
            $result[$cid][$bucket] += $inv->balance_due;
            $result[$cid]['total'] += $inv->balance_due;
        }

        // Fold in each customer's account opening balance (predates all
        // invoice-level tracking, so it belongs in the oldest 90+ days bucket)
        // and any standing credit_balance (a prepayment/over-return credit
        // that reduces what they owe, e.g. when a return happened after their
        // invoice was already fully paid — it never touches any invoice's
        // balance_due, only the customer's credit_balance field). Without
        // both, this report disagrees with the Chart of Accounts / Trial
        // Balance for any customer carrying either one.
        $adjustCustomers = Customer::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with('account:id,opening_balance')
            ->get()
            ->filter(fn($c) => (float) $c->credit_balance > 0.001
                || ($c->account && abs((float) $c->account->opening_balance) > 0.001));

        foreach ($adjustCustomers as $customer) {
            $adjustment = (float) ($customer->account->opening_balance ?? 0)
                - ($customer->account?->openingBalancePaid() ?? 0)
                - (float) $customer->credit_balance;
            if (abs($adjustment) < 0.001) continue;
            if (!isset($result[$customer->id])) {
                $result[$customer->id] = [
                    'customer_id'   => $customer->id,
                    'customer_name' => $customer->name,
                    'phone'         => $customer->phone ?? '—',
                    'current'       => 0,
                    'days_31_60'    => 0,
                    'days_61_90'    => 0,
                    'days_90_plus'  => 0,
                    'total'         => 0,
                ];
            }
            $result[$customer->id]['days_90_plus'] += $adjustment;
            $result[$customer->id]['total']        += $adjustment;
        }

        $rows   = array_values($result);
        $totals = [
            'current'      => array_sum(array_column($rows, 'current')),
            'days_31_60'   => array_sum(array_column($rows, 'days_31_60')),
            'days_61_90'   => array_sum(array_column($rows, 'days_61_90')),
            'days_90_plus' => array_sum(array_column($rows, 'days_90_plus')),
            'total'        => array_sum(array_column($rows, 'total')),
        ];

        return response()->json(['data' => $rows, 'totals' => $totals, 'as_of_date' => $asOf->toDateString()]);
    }

    public function supplierAging(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();
        $asOf     = $request->as_of_date ? now()->parse($request->as_of_date) : now();

        $invoices = PurchaseOrder::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->where('status', '!=', 'cancelled')
            ->with('supplier:id,name,phone')
            ->get();

        $result = [];
        foreach ($invoices as $inv) {
            $sid = $inv->supplier_id;
            if (!isset($result[$sid])) {
                $result[$sid] = [
                    'supplier_id'   => $sid,
                    'supplier_name' => $inv->supplier?->name ?? '—',
                    'phone'         => $inv->supplier?->phone ?? '—',
                    'current'       => 0,
                    'days_31_60'    => 0,
                    'days_61_90'    => 0,
                    'days_90_plus'  => 0,
                    'total'         => 0,
                ];
            }
            $days   = $inv->due_date ? max(0, $asOf->diffInDays($inv->due_date, false) * -1) : 0;
            $bucket = match(true) {
                $days <= 30 => 'current',
                $days <= 60 => 'days_31_60',
                $days <= 90 => 'days_61_90',
                default     => 'days_90_plus',
            };
            $result[$sid][$bucket] += $inv->balance_due;
            $result[$sid]['total'] += $inv->balance_due;
        }

        // Fold in each supplier's account opening balance (predates all
        // PO-level tracking, so it belongs in the oldest 90+ days bucket) and
        // any standing credit_balance (an over-return credit that reduces
        // what's owed to them, e.g. when a purchase return happened after
        // its PO was already fully paid — it never touches any PO's
        // balance_due, only the supplier's credit_balance field). See the
        // identical fix in customerAging() for why both are needed.
        $adjustSuppliers = Supplier::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with('account:id,opening_balance')
            ->get()
            ->filter(fn($s) => (float) $s->credit_balance > 0.001
                || ($s->account && abs((float) $s->account->opening_balance) > 0.001));

        foreach ($adjustSuppliers as $supplier) {
            $adjustment = (float) ($supplier->account->opening_balance ?? 0) - (float) $supplier->credit_balance;
            if (abs($adjustment) < 0.001) continue;
            if (!isset($result[$supplier->id])) {
                $result[$supplier->id] = [
                    'supplier_id'   => $supplier->id,
                    'supplier_name' => $supplier->name,
                    'phone'         => $supplier->phone ?? '—',
                    'current'       => 0,
                    'days_31_60'    => 0,
                    'days_61_90'    => 0,
                    'days_90_plus'  => 0,
                    'total'         => 0,
                ];
            }
            $result[$supplier->id]['days_90_plus'] += $adjustment;
            $result[$supplier->id]['total']        += $adjustment;
        }

        $rows   = array_values($result);
        $totals = [
            'current'      => array_sum(array_column($rows, 'current')),
            'days_31_60'   => array_sum(array_column($rows, 'days_31_60')),
            'days_61_90'   => array_sum(array_column($rows, 'days_61_90')),
            'days_90_plus' => array_sum(array_column($rows, 'days_90_plus')),
            'total'        => array_sum(array_column($rows, 'total')),
        ];

        return response()->json(['data' => $rows, 'totals' => $totals, 'as_of_date' => $asOf->toDateString()]);
    }

    public function chequeReport(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();

        $cheques = Cheque::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($request->from_date,  fn($q) => $q->whereDate('cheque_date', '>=', $request->from_date))
            ->when($request->to_date,    fn($q) => $q->whereDate('cheque_date', '<=', $request->to_date))
            ->when($request->status,     fn($q) => $q->where('status', $request->status))
            ->when($request->direction,  fn($q) => $q->where('direction', $request->direction))
            ->orderBy('cheque_date', 'desc')
            ->get();

        $cheques->each(function ($c) {
            if ($c->party_type === 'customer' && $c->party_id) {
                $c->party_name = Customer::find($c->party_id)?->name ?? '—';
            } elseif ($c->party_type === 'supplier' && $c->party_id) {
                $c->party_name = Supplier::find($c->party_id)?->name ?? '—';
            } else {
                $c->party_name = '—';
            }
        });

        $summary = [
            'total_received'  => $cheques->where('direction', 'received')->sum('amount'),
            'total_issued'    => $cheques->where('direction', 'issued')->sum('amount'),
            'pending_count'   => $cheques->where('status', 'pending')->count(),
            'pending_amount'  => $cheques->where('status', 'pending')->sum('amount'),
            'cleared_count'   => $cheques->where('status', 'cleared')->count(),
            'cleared_amount'  => $cheques->where('status', 'cleared')->sum('amount'),
            'bounced_count'   => $cheques->where('status', 'bounced')->count(),
            'bounced_amount'  => $cheques->where('status', 'bounced')->sum('amount'),
            'deposited_count' => $cheques->where('status', 'deposited')->count(),
            'total_count'     => $cheques->count(),
            'total_amount'    => $cheques->sum('amount'),
        ];

        $byStatus = $cheques->groupBy('status')->map(fn($g) => [
            'count'  => $g->count(),
            'amount' => $g->sum('amount'),
        ]);

        $byBank = $cheques->filter(fn($c) => !empty($c->bank_name))
            ->groupBy('bank_name')->map(fn($g) => [
                'count'  => $g->count(),
                'amount' => $g->sum('amount'),
            ]);

        return response()->json([
            'data'      => $cheques->values(),
            'summary'   => $summary,
            'by_status' => $byStatus,
            'by_bank'   => $byBank,
        ]);
    }

    public function stockMovements(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();

        $movements = StockMovement::with(['product:id,name,code', 'branch:id,name', 'createdBy:id,name'])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->when($request->from_date,  fn($q) => $q->whereDate('movement_date', '>=', $request->from_date))
            ->when($request->to_date,    fn($q) => $q->whereDate('movement_date', '<=', $request->to_date))
            ->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
            ->when($request->type,       fn($q) => $q->where('type', $request->type))
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->paginate($request->input('per_page', 50));

        return response()->json($movements);
    }
}
