@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports')
@section('page-desc', 'Business intelligence and analytics')

@section('content')
<div x-data="reportsHub()" x-init="init()">

  {{-- Header --}}
  <div class="mb-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Reports &amp; Analytics</h2>
        <p class="text-sm text-gray-500 mt-0.5">Select a report to view detailed data with filters, charts and exports</p>
      </div>
      <div class="flex items-center gap-2 text-sm text-gray-500">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        <span x-text="'Updated ' + now"></span>
      </div>
    </div>
  </div>

  {{-- Quick Stats --}}
  <div x-show="!statsLoading" class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
    <div class="card p-4 border-l-4" style="border-color:#1B3EB6">
      <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">This Month Sales</div>
      <div class="text-xl font-bold text-gray-900 dark:text-white" x-text="fmtMoney(stats.month_sales ?? 0)"></div>
      <div class="text-xs mt-1" :class="(stats.sales_change??0)>=0?'text-green-500':'text-red-400'"
           x-text="((stats.sales_change??0)>=0?'▲ ':'▼ ') + Math.abs(stats.sales_change??0) + '% vs last month'"></div>
    </div>
    <div class="card p-4 border-l-4" style="border-color:#059669">
      <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Outstanding AR</div>
      <div class="text-xl font-bold text-amber-600" x-text="fmtMoney(stats.outstanding_ar ?? 0)"></div>
      <div class="text-xs text-gray-400 mt-1" x-text="(stats.overdue_customers ?? 0) + ' customers overdue'"></div>
    </div>
    <div class="card p-4 border-l-4" style="border-color:#7c3aed">
      <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Stock Value</div>
      <div class="text-xl font-bold text-violet-600" x-text="fmtMoney(stats.stock_value ?? 0)"></div>
      <div class="text-xs text-gray-400 mt-1" x-text="(stats.low_stock_count ?? 0) + ' items low stock'"></div>
    </div>
    <div class="card p-4 border-l-4" style="border-color:#f59e0b">
      <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Pending Cheques</div>
      <div class="text-xl font-bold text-amber-600" x-text="fmtMoney(stats.pending_cheques ?? 0)"></div>
      <div class="text-xs text-gray-400 mt-1" x-text="(stats.pending_cheque_count ?? 0) + ' cheques pending'"></div>
    </div>
  </div>
  <div x-show="statsLoading" class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
    <template x-for="i in [1,2,3,4]" :key="i">
      <div class="card p-4 animate-pulse"><div class="h-4 bg-gray-200 rounded w-2/3 mb-2"></div><div class="h-6 bg-gray-200 rounded w-1/2"></div></div>
    </template>
  </div>

  {{-- Report Cards --}}
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

    {{-- Sales Report --}}
    <a href="{{ route('reports.sales') }}" class="card group hover:shadow-lg transition-all duration-200 overflow-hidden cursor-pointer">
      <div class="h-2" style="background:linear-gradient(90deg,#1B3EB6,#3B5FD4)"></div>
      <div class="p-5">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
          </div>
          <svg class="w-4 h-4 text-gray-300 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white mb-1">Sales Report</h3>
        <p class="text-xs text-gray-500 mb-3">Invoice totals, product performance, customer analysis and sales rep rankings</p>
        <div class="flex flex-wrap gap-1">
          <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600">By Product</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600">By Customer</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600">By Rep</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600">Trend</span>
        </div>
      </div>
    </a>

    {{-- Purchase Report --}}
    <a href="{{ route('reports.purchase') }}" class="card group hover:shadow-lg transition-all duration-200 overflow-hidden cursor-pointer">
      <div class="h-2" style="background:linear-gradient(90deg,#7c3aed,#9D5CF0)"></div>
      <div class="p-5">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#7c3aed,#5b21b6)">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          </div>
          <svg class="w-4 h-4 text-gray-300 group-hover:text-violet-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white mb-1">Purchase Report</h3>
        <p class="text-xs text-gray-500 mb-3">Purchase orders, supplier spending, payment status and outstanding balances</p>
        <div class="flex flex-wrap gap-1">
          <span class="text-xs px-2 py-0.5 rounded-full bg-violet-50 text-violet-600">PO List</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-violet-50 text-violet-600">By Supplier</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-violet-50 text-violet-600">Outstanding</span>
        </div>
      </div>
    </a>

    {{-- Inventory Report --}}
    <a href="{{ route('reports.inventory') }}" class="card group hover:shadow-lg transition-all duration-200 overflow-hidden cursor-pointer">
      <div class="h-2" style="background:linear-gradient(90deg,#059669,#10B981)"></div>
      <div class="p-5">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#059669,#047857)">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
          </div>
          <svg class="w-4 h-4 text-gray-300 group-hover:text-green-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white mb-1">Inventory Report</h3>
        <p class="text-xs text-gray-500 mb-3">Current stock levels, valuation, low-stock alerts and product status overview</p>
        <div class="flex flex-wrap gap-1">
          <span class="text-xs px-2 py-0.5 rounded-full bg-green-50 text-green-700">Stock Levels</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-green-50 text-green-700">Valuation</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-red-50 text-red-600">Low Stock</span>
        </div>
      </div>
    </a>

    {{-- Stock Movement --}}
    <a href="{{ route('reports.stock-movement') }}" class="card group hover:shadow-lg transition-all duration-200 overflow-hidden cursor-pointer">
      <div class="h-2" style="background:linear-gradient(90deg,#0891b2,#06B6D4)"></div>
      <div class="p-5">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#0891b2,#0e7490)">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
          </div>
          <svg class="w-4 h-4 text-gray-300 group-hover:text-cyan-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white mb-1">Stock Movement</h3>
        <p class="text-xs text-gray-500 mb-3">All stock ins, outs, transfers, adjustments and opening stock with balance trail</p>
        <div class="flex flex-wrap gap-1">
          <span class="text-xs px-2 py-0.5 rounded-full bg-cyan-50 text-cyan-700">Purchases</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-cyan-50 text-cyan-700">Sales</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-cyan-50 text-cyan-700">Adjustments</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-cyan-50 text-cyan-700">Transfers</span>
        </div>
      </div>
    </a>

    {{-- Customer Aging --}}
    <a href="{{ route('reports.customer-aging') }}" class="card group hover:shadow-lg transition-all duration-200 overflow-hidden cursor-pointer">
      <div class="h-2" style="background:linear-gradient(90deg,#f59e0b,#FBBF24)"></div>
      <div class="p-5">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#f59e0b,#d97706)">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
          <svg class="w-4 h-4 text-gray-300 group-hover:text-amber-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white mb-1">Customer Aging</h3>
        <p class="text-xs text-gray-500 mb-3">Receivables by aging bucket — current, 31–60, 61–90 and 90+ days overdue</p>
        <div class="flex flex-wrap gap-1">
          <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">AR Buckets</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-red-50 text-red-600">Overdue</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">As-of Date</span>
        </div>
      </div>
    </a>

    {{-- Supplier Aging --}}
    <a href="{{ route('reports.supplier-aging') }}" class="card group hover:shadow-lg transition-all duration-200 overflow-hidden cursor-pointer">
      <div class="h-2" style="background:linear-gradient(90deg,#ea580c,#F97316)"></div>
      <div class="p-5">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#ea580c,#c2410c)">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
          </div>
          <svg class="w-4 h-4 text-gray-300 group-hover:text-orange-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white mb-1">Supplier Aging</h3>
        <p class="text-xs text-gray-500 mb-3">Payables by aging bucket — outstanding amounts owed to each supplier</p>
        <div class="flex flex-wrap gap-1">
          <span class="text-xs px-2 py-0.5 rounded-full bg-orange-50 text-orange-700">AP Buckets</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-orange-50 text-orange-700">Overdue</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-orange-50 text-orange-700">As-of Date</span>
        </div>
      </div>
    </a>

    {{-- Expenses --}}
    <a href="{{ route('reports.expenses') }}" class="card group hover:shadow-lg transition-all duration-200 overflow-hidden cursor-pointer">
      <div class="h-2" style="background:linear-gradient(90deg,#dc2626,#EF4444)"></div>
      <div class="p-5">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#dc2626,#b91c1c)">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
          <svg class="w-4 h-4 text-gray-300 group-hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white mb-1">Expense Report</h3>
        <p class="text-xs text-gray-500 mb-3">Expense breakdown by category, approval status and monthly trend analysis</p>
        <div class="flex flex-wrap gap-1">
          <span class="text-xs px-2 py-0.5 rounded-full bg-red-50 text-red-600">By Category</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-red-50 text-red-600">Trend</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-red-50 text-red-600">Approval Status</span>
        </div>
      </div>
    </a>

    {{-- Cheques --}}
    <a href="{{ route('reports.cheques') }}" class="card group hover:shadow-lg transition-all duration-200 overflow-hidden cursor-pointer">
      <div class="h-2" style="background:linear-gradient(90deg,#0f766e,#14B8A6)"></div>
      <div class="p-5">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#0f766e,#0d5955)">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><path d="M1 10h22"/></svg>
          </div>
          <svg class="w-4 h-4 text-gray-300 group-hover:text-teal-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white mb-1">Cheque Report</h3>
        <p class="text-xs text-gray-500 mb-3">Received and issued cheques with status tracking, bank breakdown and clearing history</p>
        <div class="flex flex-wrap gap-1">
          <span class="text-xs px-2 py-0.5 rounded-full bg-teal-50 text-teal-700">By Status</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-teal-50 text-teal-700">By Bank</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-red-50 text-red-600">Bounced</span>
        </div>
      </div>
    </a>

    {{-- Sales Targets --}}
    <a href="{{ route('reports.targets') }}" class="card group hover:shadow-lg transition-all duration-200 overflow-hidden cursor-pointer">
      <div class="h-2" style="background:linear-gradient(90deg,#be185d,#EC4899)"></div>
      <div class="p-5">
        <div class="flex items-start justify-between mb-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:linear-gradient(135deg,#be185d,#9d174d)">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
          </div>
          <svg class="w-4 h-4 text-gray-300 group-hover:text-pink-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white mb-1">Sales Targets</h3>
        <p class="text-xs text-gray-500 mb-3">Target vs achievement by sales representative — monthly, quarterly and annual goals</p>
        <div class="flex flex-wrap gap-1">
          <span class="text-xs px-2 py-0.5 rounded-full bg-pink-50 text-pink-700">Achievement %</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-pink-50 text-pink-700">Progress Bars</span>
          <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">Under Alert</span>
        </div>
      </div>
    </a>

  </div>

</div>
@endsection

@push('scripts')
<script>
function reportsHub() {
  return {
    statsLoading: true,
    stats: {},
    now: new Date().toLocaleString('en-GB', { day:'2-digit', month:'short', year:'numeric', hour:'2-digit', minute:'2-digit' }),
    async init() {
      try {
        const today = new Date().toISOString().slice(0,10);
        const first = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0,10);

        const [salesD, arD, invD, cheqD] = await Promise.allSettled([
          apiFetch('/reports/sales?from_date=' + first + '&to_date=' + today).then(r => r.json()),
          apiFetch('/reports/customer-aging?as_of_date=' + today).then(r => r.json()),
          apiFetch('/reports/inventory').then(r => r.json()),
          apiFetch('/reports/cheques?from_date=2000-01-01&to_date=' + today + '&status=in_hand').then(r => r.json()),
        ]);

        const sales  = salesD.status  === 'fulfilled' ? salesD.value  : {};
        const ar     = arD.status     === 'fulfilled' ? arD.value     : {};
        const inv    = invD.status    === 'fulfilled' ? invD.value    : {};
        const cheq   = cheqD.status   === 'fulfilled' ? cheqD.value   : {};

        this.stats = {
          month_sales:          sales.summary?.total_sales ?? 0,
          sales_change:         0,
          outstanding_ar:       (ar.totals?.total ?? 0),
          overdue_customers:    (ar.data ?? []).filter(r => (r.days_31_60??0)+(r.days_61_90??0)+(r.days_90_plus??0) > 0).length,
          stock_value:          inv.summary?.total_value ?? 0,
          low_stock_count:      inv.summary?.low_stock ?? 0,
          pending_cheques:      cheq.summary?.pending_amount ?? 0,
          pending_cheque_count: cheq.summary?.pending_count ?? 0,
        };
      } catch(e) {}
      finally { this.statsLoading = false; }
    },
  };
}
</script>
@endpush
