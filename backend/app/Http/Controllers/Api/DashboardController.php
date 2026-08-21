<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Cheque;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntryLine;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Target;
use App\Models\User;
use App\Models\ProductBranchStock;
use App\Services\BranchContextService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private BranchContextService $branchContext,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();
        $isAll    = $this->branchContext->isAllBranches();
        $today    = now()->toDateString();

        // Resolve date range from query params; default = this month
        $from = $request->query('from')
            ? Carbon::parse($request->query('from'))->startOfDay()
            : now()->startOfMonth();
        $to = $request->query('to')
            ? Carbon::parse($request->query('to'))->endOfDay()
            : now()->endOfMonth();

        $fromStr = $from->toDateString();
        $toStr   = $to->toDateString();
        $year    = $from->year;

        return response()->json([
            'kpis'               => $this->getKpis($branchId, $today, $fromStr, $toStr, $year),
            'cheque_stats'       => $this->getChequeStats($branchId),
            'sales_reps'         => $this->getSalesReps($branchId, $fromStr, $toStr, $year),
            'aging'              => $this->getAging($branchId, $today),
            'best_products'      => $this->getBestProducts($branchId, $fromStr, $toStr),
            'expense_categories' => $this->getExpenseCategories($branchId, $fromStr, $toStr),
            'daily_revenue'      => $this->getDailyRevenue($branchId, $fromStr, $toStr),
            'sales_due_today'    => $this->getSalesDueMonth($branchId),
            'purchase_due_today' => $this->getPurchaseDueMonth($branchId),
            'today_sales'        => $this->getTodaySales($branchId, $today),
            'branch_stats'       => $this->getBranchStats($today, $fromStr, $toStr, $branchId),
            'period'             => ['from' => $fromStr, 'to' => $toStr],
            'low_stock'          => $this->getLowStock($branchId, $isAll),
        ]);
    }

    public function customerMap(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();

        $rows = Customer::where('is_active', true)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->selectRaw('district, city, COUNT(*) as count')
            ->groupBy('district', 'city')
            ->orderByDesc('count')
            ->get();

        // Group by district with city breakdown
        $byDistrict = [];
        foreach ($rows as $row) {
            $district = trim($row->district);
            if (!$district) continue;
            if (!isset($byDistrict[$district])) {
                $byDistrict[$district] = ['district' => $district, 'count' => 0, 'cities' => []];
            }
            $byDistrict[$district]['count'] += $row->count;
            if ($row->city) {
                $byDistrict[$district]['cities'][] = ['city' => $row->city, 'count' => $row->count];
            }
        }

        // Customers with no district but have city
        $noDist = Customer::where('is_active', true)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where(fn($q) => $q->whereNull('district')->orWhere('district', ''))
            ->count();

        return response()->json([
            'districts'  => array_values($byDistrict),
            'total'      => Customer::where('is_active', true)->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count(),
            'mapped'     => array_sum(array_column($byDistrict, 'count')),
            'unmapped'   => $noDist,
        ]);
    }

    private function getKpis(?int $branchId, string $today, string $from, string $to, int $year): array
    {
        $qInv    = Invoice::where('type', 'invoice')->when($branchId, fn($q) => $q->where('branch_id', $branchId));
        $qPO     = PurchaseOrder::when($branchId, fn($q) => $q->where('branch_id', $branchId));

        $confirmedStatuses = ['confirmed', 'partially_paid', 'paid'];

        return [
            'total_customers'  => Customer::where('is_active', true)->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count(),
            'total_products'   => Product::where('is_active', true)->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count(),
            'total_suppliers'  => Supplier::where('is_active', true)->when($branchId, fn($q) => $q->where('branch_id', $branchId))->count(),
            'total_invoices'   => (clone $qInv)->whereIn('status', $confirmedStatuses)->whereBetween('invoice_date', [$from, $to])->count(),
            'period_sales'     => (float) (clone $qInv)->whereIn('status', $confirmedStatuses)->whereBetween('invoice_date', [$from, $to])->sum('total'),
            'period_purchases' => (float) (clone $qPO)->where('status', '!=', 'cancelled')->whereBetween('order_date', [$from, $to])->sum('total'),
            'period_expenses'  => $this->periodExpenses($branchId, $from, $to),
            'outstanding'      => $this->customerOutstanding($branchId),
            'overdue_count'    => (clone $qInv)->whereIn('status', ['confirmed', 'partially_paid'])->where('due_date', '<', $today)->count(),
            'today_sales'      => (float) (clone $qInv)->whereIn('status', $confirmedStatuses)->whereDate('invoice_date', $today)->sum('total'),
        ];
    }

    private function getSalesReps(?int $branchId, string $from, string $to, int $year): array
    {
        $monthsLeft = max(1, 12 - now()->month);

        $users = User::role('sales_person')
            ->when($branchId, fn($q) => $q->whereHas('branches', fn($b) => $b->where('branches.id', $branchId)))
            ->get();

        return $users->map(function (User $user) use ($branchId, $from, $to, $year, $monthsLeft) {
            $base = Invoice::where('created_by', $user->id)
                ->where('type', 'invoice')
                ->whereIn('status', ['confirmed', 'partially_paid', 'paid'])
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId));

            $periodSales = (float) (clone $base)->whereBetween('invoice_date', [$from, $to])->sum('total');
            $ytdSales    = (float) (clone $base)->whereYear('invoice_date', $year)->sum('total');

            $annualTarget = (float) (Target::where('user_id', $user->id)
                ->where('year', $year)->where('period', 'annual')
                ->value('target_value') ?? 0);

            $monthTarget = (float) (Target::where('user_id', $user->id)
                ->where('year', $year)->where('period', 'monthly')
                ->where('period_number', now()->month)
                ->value('target_value') ?? 0);

            $balance  = max(0, $annualTarget - $ytdSales);
            $required = $annualTarget > 0 ? round($balance / $monthsLeft, 2) : 0;

            return [
                'id'               => $user->id,
                'name'             => $user->name,
                'period_sales'     => $periodSales,
                'month_sales'      => $periodSales,
                'month_target'     => $monthTarget,
                'ytd_sales'        => $ytdSales,
                'annual_target'    => $annualTarget,
                'balance'          => $balance,
                'required_monthly' => $required,
                'achievement_pct'  => $annualTarget > 0 ? round(($ytdSales / $annualTarget) * 100, 1) : 0,
            ];
        })->values()->toArray();
    }

    private function getAging(?int $branchId, string $today): array
    {
        // A missing due_date must NOT drop the record — treat it as due today
        // (0 days over), matching ReportController::customerAging()/supplierAging().
        // Previously whereNotNull('due_date') silently excluded any invoice/PO
        // without one, which was hiding the vast majority of real dues.
        $custRows = Invoice::where('type', 'invoice')
            ->whereIn('status', ['confirmed', 'partially_paid'])
            ->where('balance_due', '>', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('balance_due, GREATEST(0, DATEDIFF(?, COALESCE(due_date, ?))) as days_over', [$today, $today])
            ->get();

        $supRows = PurchaseOrder::whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->where('balance_due', '>', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('balance_due, GREATEST(0, DATEDIFF(?, COALESCE(due_date, ?))) as days_over', [$today, $today])
            ->get();

        $bucket = function ($rows) {
            $b = ['0_30' => 0, '30_60' => 0, '60_90' => 0, '90_120' => 0, 'over_120' => 0];
            foreach ($rows as $r) {
                $d = (int) $r->days_over;
                if ($d <= 30)      $b['0_30']     += (float) $r->balance_due;
                elseif ($d <= 60)  $b['30_60']    += (float) $r->balance_due;
                elseif ($d <= 90)  $b['60_90']    += (float) $r->balance_due;
                elseif ($d <= 120) $b['90_120']   += (float) $r->balance_due;
                else               $b['over_120'] += (float) $r->balance_due;
            }
            return $b;
        };

        $custBucket = $bucket($custRows);
        $supBucket  = $bucket($supRows);

        // Fold in each party's account opening balance and standing credit_balance
        // into the oldest bucket — same adjustment as the Aging Reports, since it
        // predates all invoice/PO-level tracking and has no date of its own.
        $custBucket['over_120'] += $this->customerOpeningAdjustment($branchId);
        $supBucket['over_120']  += $this->supplierOpeningAdjustment($branchId);

        return ['customer' => $custBucket, 'supplier' => $supBucket];
    }

    /**
     * Sum of each customer's account opening_balance minus their credit_balance —
     * the adjustment every customer-facing total (Aging Report, list balance,
     * this KPI) must add on top of raw invoice balance_due, since neither of
     * those two fields is tied to any specific invoice.
     */
    private function customerOpeningAdjustment(?int $branchId): float
    {
        return (float) Customer::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with('account:id,opening_balance')->get()
            ->sum(fn($c) => (float) ($c->account->opening_balance ?? 0) - ($c->account?->openingBalancePaid() ?? 0) - (float) $c->credit_balance);
    }

    /** Same as customerOpeningAdjustment() but for suppliers. */
    private function supplierOpeningAdjustment(?int $branchId): float
    {
        return (float) Supplier::when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with('account:id,opening_balance')->get()
            ->sum(fn($s) => (float) ($s->account->opening_balance ?? 0) - (float) $s->credit_balance);
    }

    /** True total outstanding owed by customers: open invoice balances + the opening/credit adjustment. */
    private function customerOutstanding(?int $branchId): float
    {
        $invoiceBalance = (float) Invoice::where('type', 'invoice')
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['confirmed', 'partially_paid'])
            ->sum('balance_due');

        return $invoiceBalance + $this->customerOpeningAdjustment($branchId);
    }

    private function getBestProducts(?int $branchId, string $from, string $to): array
    {
        return InvoiceItem::join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
            ->where('invoices.type', 'invoice')
            ->whereIn('invoices.status', ['confirmed', 'partially_paid', 'paid'])
            ->whereBetween('invoices.invoice_date', [$from, $to])
            ->when($branchId, fn($q) => $q->where('invoices.branch_id', $branchId))
            ->groupBy('invoice_items.product_id', 'invoice_items.product_name')
            ->orderByDesc('total_sales')
            ->limit(8)
            ->selectRaw('invoice_items.product_id, invoice_items.product_name, SUM(invoice_items.total) as total_sales, SUM(invoice_items.quantity) as total_qty')
            ->get()
            ->map(fn($r) => [
                'name'  => $r->product_name,
                'total' => (float) $r->total_sales,
                'qty'   => (float) $r->total_qty,
            ])->toArray();
    }

    /**
     * True total expense for a period, from the GL (every posted journal line
     * against an expense-type account), not just the Expense table. A cost
     * booked via a manual journal entry — bypassing the Expenses module
     * entirely — still needs to count here, exactly as it already does in
     * Profit & Loss; sourcing this from the Expense table alone made it
     * silently disagree with the P&L for any such entry.
     *
     * Cost of Goods Sold (the "Purchase Accounts" group) is deliberately
     * excluded here — it's the direct cost of goods sold, not a discretionary
     * operating expense, and the automatic per-sale COGS entry would otherwise
     * dominate this figure. It's still correctly included in Profit & Loss.
     */
    private function periodExpenses(?int $branchId, string $from, string $to): float
    {
        return (float) Account::where('type', 'expense')->where('is_active', true)
            ->whereHas('group', fn($q) => $q->where('name', '!=', 'Purchase Accounts'))
            ->get()
            ->sum(function ($account) use ($branchId, $from, $to) {
                $q = JournalEntryLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function ($jq) use ($branchId, $from, $to) {
                        $jq->where('status', 'posted')->whereBetween('entry_date', [$from, $to]);
                        if ($branchId) $jq->where('branch_id', $branchId);
                    });
                $debit  = (float) (clone $q)->sum('debit');
                $credit = (float) (clone $q)->sum('credit');
                return $account->normal_balance === 'debit' ? $debit - $credit : $credit - $debit;
            });
    }

    /** Same GL-based sourcing as periodExpenses(), broken down per account. */
    private function getExpenseCategories(?int $branchId, string $from, string $to): array
    {
        return Account::where('type', 'expense')->where('is_active', true)
            ->whereHas('group', fn($q) => $q->where('name', '!=', 'Purchase Accounts'))
            ->get()
            ->map(function ($account) use ($branchId, $from, $to) {
                $q = JournalEntryLine::where('account_id', $account->id)
                    ->whereHas('journalEntry', function ($jq) use ($branchId, $from, $to) {
                        $jq->where('status', 'posted')->whereBetween('entry_date', [$from, $to]);
                        if ($branchId) $jq->where('branch_id', $branchId);
                    });
                $debit  = (float) (clone $q)->sum('debit');
                $credit = (float) (clone $q)->sum('credit');
                $total  = $account->normal_balance === 'debit' ? $debit - $credit : $credit - $debit;
                return ['name' => $account->name, 'total' => $total];
            })
            ->filter(fn($r) => abs($r['total']) > 0.001)
            ->sortByDesc('total')
            ->values()
            ->toArray();
    }

    private function getSalesDueMonth(?int $branchId): array
    {
        $start = now()->startOfMonth()->toDateString();
        $end   = now()->endOfMonth()->toDateString();
        return Invoice::where('type', 'invoice')
            ->whereIn('status', ['confirmed', 'partially_paid'])
            ->whereBetween('due_date', [$start, $end])
            ->where('balance_due', '>', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with('customer')
            ->orderBy('due_date')
            ->limit(30)
            ->get()
            ->map(fn($i) => [
                'id'             => $i->id,
                'invoice_number' => $i->invoice_number,
                'customer'       => $i->customer?->name ?? '—',
                'total'          => (float) $i->total,
                'balance_due'    => (float) $i->balance_due,
                'due_date'       => $i->due_date?->toDateString(),
            ])->toArray();
    }

    private function getPurchaseDueMonth(?int $branchId): array
    {
        $start = now()->startOfMonth()->toDateString();
        $end   = now()->endOfMonth()->toDateString();
        return PurchaseOrder::whereIn('payment_status', ['unpaid', 'partially_paid'])
            ->whereBetween('due_date', [$start, $end])
            ->where('balance_due', '>', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with('supplier')
            ->orderBy('due_date')
            ->limit(30)
            ->get()
            ->map(fn($i) => [
                'id'             => $i->id,
                'invoice_number' => $i->po_number ?? ('#PO-' . $i->id),
                'supplier'       => $i->supplier?->name ?? '—',
                'total'          => (float) $i->total,
                'balance_due'    => (float) $i->balance_due,
                'due_date'       => $i->due_date?->toDateString(),
            ])->toArray();
    }

    private function getTodaySales(?int $branchId, string $today): array
    {
        return Invoice::where('type', 'invoice')
            ->whereIn('status', ['confirmed', 'partially_paid', 'paid'])
            ->whereDate('invoice_date', $today)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->with(['customer', 'branch'])
            ->orderByDesc('created_at')
            ->limit(25)
            ->get()
            ->map(fn($i) => [
                'id'             => $i->id,
                'invoice_number' => $i->invoice_number,
                'customer'       => $i->customer?->name ?? '—',
                'branch'         => $i->branch?->name ?? '—',
                'total'          => (float) $i->total,
                'paid_amount'    => (float) $i->paid_amount,
                'balance_due'    => (float) $i->balance_due,
                'status'         => $i->status,
                'created_at'     => $i->created_at?->format('d M H:i'),
            ])->toArray();
    }

    private function getChequeStats(?int $branchId): array
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd   = now()->endOfMonth()->toDateString();

        $base = Cheque::when($branchId, fn($q) => $q->where('branch_id', $branchId));

        // Received cheques still in hand (from customers, not yet deposited)
        $recInHand = (clone $base)
            ->where('direction', 'received')
            ->where('status', 'in_hand');

        // Received cheques due/maturing this calendar month and still in hand (need to deposit)
        $recThisMonth = (clone $base)
            ->where('direction', 'received')
            ->where('status', 'in_hand')
            ->whereBetween('cheque_date', [$monthStart, $monthEnd]);

        // Issued cheques (own cheques given to suppliers) that are outstanding (not cleared/cancelled)
        $issuedActive = (clone $base)
            ->where('direction', 'issued')
            ->whereNotIn('status', ['cleared', 'cancelled', 'bounced']);

        // Issued cheques this month (by the date we issued them)
        $issuedThisMonth = (clone $base)
            ->where('direction', 'issued')
            ->whereBetween('received_issued_date', [$monthStart, $monthEnd]);

        // Party cheques: received cheques still in hand that are earmarked for a supplier (linked to a PO)
        $partyQuery = (clone $base)
            ->where('direction', 'received')
            ->where('status', 'in_hand')
            ->whereHas('purchaseOrders');

        return [
            'received_in_hand'    => [
                'count' => $recInHand->count(),
                'total' => (float) $recInHand->sum('amount'),
            ],
            'received_this_month' => [
                'count' => $recThisMonth->count(),
                'total' => (float) $recThisMonth->sum('amount'),
            ],
            'issued_active'       => [
                'count' => $issuedActive->count(),
                'total' => (float) $issuedActive->sum('amount'),
            ],
            'issued_this_month'   => [
                'count' => $issuedThisMonth->count(),
                'total' => (float) $issuedThisMonth->sum('amount'),
            ],
            'party_cheques'       => [
                'count' => $partyQuery->count(),
                'total' => (float) $partyQuery->sum('amount'),
            ],
        ];
    }

    private function getLowStock(?int $branchId, bool $isAll): array
    {
        return ProductBranchStock::query()
            ->join('products', 'product_branch_stock.product_id', '=', 'products.id')
            ->join('branches', 'product_branch_stock.branch_id', '=', 'branches.id')
            ->whereRaw('product_branch_stock.quantity <= products.reorder_level')
            ->where('products.is_active', true)
            ->when(!$isAll && $branchId, fn($q) => $q->where('product_branch_stock.branch_id', $branchId))
            ->orderBy('product_branch_stock.quantity')
            ->limit(30)
            ->select([
                'products.id as product_id',
                'products.name as product_name',
                'products.code as product_code',
                'products.reorder_level',
                'products.unit',
                'product_branch_stock.quantity',
                'product_branch_stock.branch_id',
                'branches.name as branch_name',
            ])
            ->get()
            ->map(fn($r) => [
                'product_id'    => $r->product_id,
                'product_name'  => $r->product_name,
                'product_code'  => $r->product_code,
                'reorder_level' => (float) $r->reorder_level,
                'quantity'      => (float) $r->quantity,
                'unit'          => $r->unit ?? 'pcs',
                'branch_id'     => $r->branch_id,
                'branch_name'   => $r->branch_name,
            ])->toArray();
    }

    private function getDailyRevenue(?int $branchId, string $from, string $to): array
    {
        return Invoice::where('type', 'invoice')
            ->whereIn('status', ['confirmed', 'partially_paid', 'paid'])
            ->whereBetween('invoice_date', [$from, $to])
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->selectRaw('DATE(invoice_date) as sale_date, SUM(total) as revenue, SUM(COALESCE(paid_amount,0)) as collected')
            ->groupByRaw('DATE(invoice_date)')
            ->orderByRaw('DATE(invoice_date)')
            ->get()
            ->map(fn($r) => [
                'date'      => $r->sale_date,
                'revenue'   => (float) $r->revenue,
                'collected' => (float) $r->collected,
            ])->toArray();
    }

    private function getBranchStats(string $today, string $from, string $to, ?int $branchId = null): array
    {
        return Branch::where('is_active', true)
            ->when($branchId, fn($q) => $q->where('id', $branchId))
            ->get()->map(function (Branch $b) use ($today, $from, $to) {
            $qInv = Invoice::where('branch_id', $b->id)->where('type', 'invoice');
            $confirmed = ['confirmed', 'partially_paid', 'paid'];
            return [
                'id'          => $b->id,
                'name'        => $b->name,
                'code'        => $b->code,
                'sales_today' => (float) (clone $qInv)->whereDate('invoice_date', $today)->whereIn('status', $confirmed)->sum('total'),
                'sales_month' => (float) (clone $qInv)->whereBetween('invoice_date', [$from, $to])->whereIn('status', $confirmed)->sum('total'),
                'outstanding' => $this->customerOutstanding($b->id),
                'customers'   => Customer::where('branch_id', $b->id)->where('is_active', true)->count(),
            ];
        })->values()->toArray();
    }
}
