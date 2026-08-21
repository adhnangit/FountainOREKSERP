@extends('layouts.app')
@section('title', 'Sales Report')
@section('page-title', 'Sales Report')
@section('page-desc', 'Revenue, invoices and performance analytics')

@section('content')
<div x-data="salesReport()" x-init="init()">

  {{-- Print Header --}}
  <div class="print-header items-center justify-between mb-4 pb-3 border-b border-gray-200">
    <div><h2 class="text-lg font-bold text-gray-800">Sales Report</h2>
      <p class="text-xs text-gray-500" x-text="'Period: ' + fmtDate(filters.from_date) + ' – ' + fmtDate(filters.to_date)"></p></div>
    <div class="text-xs text-gray-400" x-text="'Generated: ' + fmtDate(new Date())"></div>
  </div>

  {{-- Filter Panel --}}
  <div class="card mb-5 no-print overflow-visible">
    <div class="px-4 py-2.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between rounded-t-xl" style="background:#f8fafc">
      <div class="flex items-center gap-2">
        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Filters</span>
      </div>
      <div class="flex items-center gap-2">
        <button @click="window.print()" class="btn-secondary text-xs py-1 px-2.5 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg> Print
        </button>
        <button @click="doExport()" class="btn-secondary text-xs py-1 px-2.5 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg> Export CSV
        </button>
      </div>
    </div>
    <div class="p-4 flex flex-wrap items-end gap-3">
      <div>
        <label class="label text-xs">From</label>
        <input type="date" x-model="filters.from_date" class="input text-sm py-1.5" />
      </div>
      <div>
        <label class="label text-xs">To</label>
        <input type="date" x-model="filters.to_date" class="input text-sm py-1.5" />
      </div>
      <div style="min-width:160px">
        <label class="label text-xs">Customer</label>
        <div class="search-dd" x-data="{ open: false, q: '' }" @click.away="open = false" @keydown.escape="open = false">
          <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.cFilt?.focus())"
                  class="input text-sm py-1.5 w-full text-left flex items-center justify-between gap-2">
            <span class="truncate" :class="filters.customer_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                  x-text="filters.customer_id ? (customers.find(c => c.id == filters.customer_id)?.name || '—') : 'All Customers'"></span>
            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu">
            <div class="p-2 border-b border-gray-100 dark:border-gray-700">
              <input x-ref="cFilt" x-model="q" type="text" placeholder="Search customer…" class="input text-sm w-full py-1.5" @keydown.stop />
            </div>
            <div class="max-h-52 overflow-y-auto py-1">
              <button type="button" @click="filters.customer_id = ''; open = false; q = ''"
                      class="search-dd-item" :class="!filters.customer_id ? 'active' : ''">
                <span class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate flex-1">All Customers</span>
              </button>
              <template x-for="c in customers.filter(c => !q || c.name.toLowerCase().includes(q.toLowerCase()))" :key="c.id">
                <button type="button" @click="filters.customer_id = c.id; open = false; q = ''"
                        class="search-dd-item" :class="filters.customer_id == c.id ? 'active' : ''">
                  <span class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate flex-1" x-text="c.name"></span>
                </button>
              </template>
            </div>
          </div>
        </div>
      </div>
      <div>
        <label class="label text-xs">Status</label>
        <select x-model="filters.status" class="input text-sm py-1.5">
          <option value="">All</option>
          <option value="confirmed">Confirmed</option>
          <option value="paid">Paid</option>
          <option value="partially_paid">Partially Paid</option>
        </select>
      </div>
      <div class="flex items-end gap-2">
        <button @click="load()" class="btn-primary text-sm py-1.5 px-4 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
          Run Report
        </button>
        <button @click="resetFilters()" class="btn-secondary text-sm py-1.5 px-3">Reset</button>
      </div>
    </div>
  </div>

  {{-- Loading skeleton --}}
  <div x-show="loading" class="space-y-4">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
      <template x-for="i in [1,2,3,4,5]" :key="i">
        <div class="card p-4 animate-pulse"><div class="h-3 bg-gray-200 rounded w-2/3 mb-2"></div><div class="h-6 bg-gray-200 rounded w-1/2"></div></div>
      </template>
    </div>
    <div class="card p-0 overflow-hidden animate-pulse">
      <div class="h-10 bg-gray-100 border-b border-gray-200"></div>
      <template x-for="i in [1,2,3,4,5,6]" :key="i">
        <div class="flex gap-4 px-4 py-3 border-b border-gray-100">
          <div class="h-3 bg-gray-200 rounded w-24"></div>
          <div class="h-3 bg-gray-200 rounded flex-1"></div>
          <div class="h-3 bg-gray-200 rounded w-16"></div>
          <div class="h-3 bg-gray-200 rounded w-20"></div>
        </div>
      </template>
    </div>
  </div>

  <div x-show="!loading">

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-5">
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#1B3EB6">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#eef2ff">
          <svg style="width:18px;height:18px;color:#1B3EB6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Total Sales</div>
          <div class="text-lg font-bold text-gray-900 dark:text-white leading-tight" x-text="fmtMoney(summary.total_sales ?? 0)"></div>
        </div>
      </div>
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#059669">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#f0fdf4">
          <svg style="width:18px;height:18px;color:#059669" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Invoices</div>
          <div class="text-lg font-bold text-gray-900 dark:text-white leading-tight" x-text="(summary.total_invoices ?? 0) + ' invoices'"></div>
        </div>
      </div>
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#22c55e">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#f0fdf4">
          <svg style="width:18px;height:18px;color:#16a34a" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Collected</div>
          <div class="text-lg font-bold text-green-600 leading-tight" x-text="fmtMoney(summary.total_collected ?? 0)"></div>
        </div>
      </div>
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#f59e0b">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fffbeb">
          <svg style="width:18px;height:18px;color:#d97706" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Outstanding</div>
          <div class="text-lg font-bold text-amber-600 leading-tight" x-text="fmtMoney(summary.total_outstanding ?? 0)"></div>
        </div>
      </div>
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#7c3aed">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#f5f3ff">
          <svg style="width:18px;height:18px;color:#7c3aed" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Avg Invoice</div>
          <div class="text-lg font-bold text-violet-600 leading-tight" x-text="fmtMoney(summary.avg_invoice ?? 0)"></div>
        </div>
      </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-4 no-print bg-gray-100 dark:bg-gray-800 rounded-xl p-1 w-fit">
      <template x-for="t in tabs" :key="t.key">
        <button @click="activeTab = t.key"
                class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all"
                :class="activeTab === t.key ? 'bg-white dark:bg-gray-700 shadow text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-700'"
                x-text="t.label"></button>
      </template>
    </div>

    {{-- TAB: Invoices --}}
    <div x-show="activeTab === 'invoices'" class="card p-0 overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800">
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Invoice List</span>
        <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 font-semibold" x-text="invoices.length + ' invoices'"></span>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="table-hd">Invoice #</th>
              <th class="table-hd">Date</th>
              <th class="table-hd">Customer</th>
              <th class="table-hd">Sales Rep</th>
              <th class="table-hd text-right">Total</th>
              <th class="table-hd text-right">Paid</th>
              <th class="table-hd text-right">Balance</th>
              <th class="table-hd">Status</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-800">
            <template x-for="inv in invoices" :key="inv.id">
              <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                <td class="table-td">
                  <a :href="BASE + '/invoices/' + inv.id" class="text-indigo-600 hover:underline font-mono text-xs font-bold" x-text="inv.invoice_number"></a>
                </td>
                <td class="table-td text-sm text-gray-500" x-text="fmtDate(inv.invoice_date)"></td>
                <td class="table-td font-medium text-gray-800 dark:text-gray-200" x-text="inv.customer?.name ?? '—'"></td>
                <td class="table-td text-gray-500 text-sm" x-text="inv.sales_rep?.name ?? '—'"></td>
                <td class="table-td text-right font-semibold text-gray-800 dark:text-gray-100" x-text="fmtMoney(inv.total)"></td>
                <td class="table-td text-right text-green-600 font-medium" x-text="fmtMoney(inv.paid_amount)"></td>
                <td class="table-td text-right font-semibold" :class="(inv.balance_due??0)>0?'text-amber-600':'text-gray-400'" x-text="fmtMoney(inv.balance_due)"></td>
                <td class="table-td">
                  <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                    :class="{
                      'bg-green-100 text-green-700': inv.status==='paid',
                      'bg-blue-100 text-blue-700': inv.status==='confirmed',
                      'bg-amber-100 text-amber-700': inv.status==='partially_paid',
                      'bg-gray-100 text-gray-500': inv.status==='draft',
                      'bg-red-100 text-red-600': inv.status==='cancelled'
                    }"
                    x-text="inv.status?.replace('_',' ')"></span>
                </td>
              </tr>
            </template>
            <template x-if="invoices.length === 0">
              <tr>
                <td colspan="8">
                  <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                      <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500">No invoices found for this period</p>
                    <p class="text-xs text-gray-400 mt-0.5">Try adjusting the date range or filters</p>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
          <tfoot class="bg-gray-50 dark:bg-gray-800 border-t-2 border-gray-200" x-show="invoices.length > 0">
            <tr>
              <td colspan="4" class="table-td font-bold text-gray-700">Total (<span x-text="invoices.length"></span> invoices)</td>
              <td class="table-td text-right font-bold text-gray-800" x-text="fmtMoney(invoices.reduce((s,r)=>s+(r.total??0),0))"></td>
              <td class="table-td text-right font-bold text-green-600" x-text="fmtMoney(invoices.reduce((s,r)=>s+(r.paid_amount??0),0))"></td>
              <td class="table-td text-right font-bold text-amber-600" x-text="fmtMoney(invoices.reduce((s,r)=>s+(r.balance_due??0),0))"></td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    {{-- TAB: By Product --}}
    <div x-show="activeTab === 'products'" class="card p-0 overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800">
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Top Products by Revenue</span>
        <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 font-semibold" x-text="byProduct.length + ' products'"></span>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50"><tr>
            <th class="table-hd w-8">#</th>
            <th class="table-hd">Product</th>
            <th class="table-hd">Code</th>
            <th class="table-hd text-right">Qty Sold</th>
            <th class="table-hd text-right">Revenue</th>
            <th class="table-hd">Share</th>
          </tr></thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <template x-for="(p, i) in byProduct" :key="i">
              <tr class="hover:bg-gray-50">
                <td class="table-td">
                  <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold text-white flex-shrink-0"
                        :style="i<3?'background:#1B3EB6':'background:#94a3b8'"
                        x-text="i+1"></span>
                </td>
                <td class="table-td font-medium text-gray-800" x-text="p.product_name"></td>
                <td class="table-td font-mono text-xs text-gray-400" x-text="p.product_code ?? '—'"></td>
                <td class="table-td text-right text-gray-700 font-medium" x-text="parseFloat(p.total_qty).toLocaleString()"></td>
                <td class="table-td text-right font-semibold text-gray-800" x-text="fmtMoney(p.total_revenue)"></td>
                <td class="table-td w-36">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 h-2 rounded-full bg-gray-200">
                      <div class="h-full rounded-full bg-indigo-500 transition-all"
                           :style="'width:' + Math.min(100, Math.round((p.total_revenue / (byProduct[0]?.total_revenue||1))*100)) + '%'"></div>
                    </div>
                    <span class="text-xs font-semibold text-gray-500 w-8 text-right" x-text="Math.round((p.total_revenue / (summary.total_sales||1))*100) + '%'"></span>
                  </div>
                </td>
              </tr>
            </template>
            <template x-if="byProduct.length===0">
              <tr><td colspan="6" class="py-12 text-center text-gray-400 text-sm">No product data available.</td></tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    {{-- TAB: By Customer --}}
    <div x-show="activeTab === 'customers'" class="card p-0 overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800">
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Top Customers by Sales</span>
        <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 font-semibold" x-text="byCustomer.length + ' customers'"></span>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50"><tr>
            <th class="table-hd w-8">#</th>
            <th class="table-hd">Customer</th>
            <th class="table-hd text-right">Invoices</th>
            <th class="table-hd text-right">Total Sales</th>
            <th class="table-hd text-right">Collected</th>
            <th class="table-hd">Share</th>
          </tr></thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <template x-for="(c, i) in byCustomer" :key="i">
              <tr class="hover:bg-gray-50">
                <td class="table-td">
                  <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold text-white"
                        :style="i<3?'background:#059669':'background:#94a3b8'"
                        x-text="i+1"></span>
                </td>
                <td class="table-td">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                         style="background:linear-gradient(135deg,#1B3EB6,#0D2272)"
                         x-text="(c.customer?.name||'?').charAt(0).toUpperCase()"></div>
                    <span class="font-medium text-gray-800" x-text="c.customer?.name ?? 'Unknown'"></span>
                  </div>
                </td>
                <td class="table-td text-right text-gray-600" x-text="c.invoice_count"></td>
                <td class="table-td text-right font-semibold text-gray-800" x-text="fmtMoney(c.total_sales)"></td>
                <td class="table-td text-right text-green-600 font-medium" x-text="fmtMoney(c.total_collected)"></td>
                <td class="table-td w-36">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 h-2 rounded-full bg-gray-200">
                      <div class="h-full rounded-full bg-emerald-500"
                           :style="'width:' + Math.min(100, Math.round((c.total_sales/(byCustomer[0]?.total_sales||1))*100)) + '%'"></div>
                    </div>
                    <span class="text-xs font-semibold text-gray-500 w-8 text-right" x-text="Math.round((c.total_sales/(summary.total_sales||1))*100) + '%'"></span>
                  </div>
                </td>
              </tr>
            </template>
            <template x-if="byCustomer.length===0">
              <tr><td colspan="6" class="py-12 text-center text-gray-400 text-sm">No customer data available.</td></tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    {{-- TAB: By Sales Rep --}}
    <div x-show="activeTab === 'reps'" class="card p-0 overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800">
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Performance by Sales Representative</span>
        <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-600 font-semibold" x-text="bySalesRep.length + ' reps'"></span>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50"><tr>
            <th class="table-hd">Sales Rep</th>
            <th class="table-hd text-right">Invoices</th>
            <th class="table-hd text-right">Total Sales</th>
            <th class="table-hd text-right">Collected</th>
            <th class="table-hd">Performance</th>
          </tr></thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <template x-for="(r, i) in bySalesRep" :key="i">
              <tr class="hover:bg-gray-50">
                <td class="table-td">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                         :style="'background:' + ['linear-gradient(135deg,#1B3EB6,#0D2272)','linear-gradient(135deg,#059669,#047857)','linear-gradient(135deg,#7c3aed,#5b21b6)','linear-gradient(135deg,#0891b2,#0e7490)'][i%4]"
                         x-text="(r.sales_rep?.name||'?').charAt(0).toUpperCase()"></div>
                    <span class="font-medium text-gray-800" x-text="r.sales_rep?.name ?? 'Unassigned'"></span>
                  </div>
                </td>
                <td class="table-td text-right text-gray-600" x-text="r.invoice_count"></td>
                <td class="table-td text-right font-semibold text-gray-800" x-text="fmtMoney(r.total_sales)"></td>
                <td class="table-td text-right text-green-600 font-medium" x-text="fmtMoney(r.total_collected)"></td>
                <td class="table-td w-48">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 h-2.5 rounded-full bg-gray-200">
                      <div class="h-full rounded-full bg-indigo-500 transition-all"
                           :style="'width:' + Math.min(100,Math.round((r.total_sales/(bySalesRep[0]?.total_sales||1))*100)) + '%'"></div>
                    </div>
                    <span class="text-xs font-bold text-indigo-600 w-8 text-right" x-text="Math.round((r.total_sales/(summary.total_sales||1))*100) + '%'"></span>
                  </div>
                </td>
              </tr>
            </template>
            <template x-if="bySalesRep.length===0">
              <tr><td colspan="5" class="py-12 text-center text-gray-400 text-sm">No sales rep data available.</td></tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
function salesReport() {
  const today = new Date().toISOString().slice(0,10);
  const firstOfMonth = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0,10);
  return {
    loading: true,
    customers: [],
    filters: { from_date: firstOfMonth, to_date: today, customer_id: '', status: '' },
    summary: {}, invoices: [], byProduct: [], byCustomer: [], bySalesRep: [], trend: [],
    activeTab: 'invoices',
    tabs: [
      { key: 'invoices', label: 'Invoices' },
      { key: 'products', label: 'By Product' },
      { key: 'customers', label: 'By Customer' },
      { key: 'reps', label: 'By Sales Rep' },
    ],
    async init() {
      try {
        const cr = await apiFetch('/customers?per_page=500').then(r => r.json());
        this.customers = cr.data ?? cr ?? [];
      } catch {}
      await this.load();
    },
    async load() {
      this.loading = true;
      try {
        const p = new URLSearchParams();
        if (this.filters.from_date)   p.set('from_date',   this.filters.from_date);
        if (this.filters.to_date)     p.set('to_date',     this.filters.to_date);
        if (this.filters.customer_id) p.set('customer_id', this.filters.customer_id);
        if (this.filters.status)      p.set('status',      this.filters.status);
        const d = await apiFetch('/reports/sales?' + p).then(r => r.json());
        this.summary    = d.summary    ?? {};
        this.invoices   = d.invoices   ?? [];
        this.byProduct  = d.byProduct  ?? [];
        this.byCustomer = d.byCustomer ?? [];
        this.bySalesRep = d.bySalesRep ?? [];
        this.trend      = d.trend      ?? [];
      } catch (e) { toast('Failed to load report', 'error'); }
      finally { this.loading = false; }
    },
    resetFilters() {
      this.filters = { from_date: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0,10), to_date: new Date().toISOString().slice(0,10), customer_id: '', status: '' };
      this.load();
    },
    doExport() {
      const headers = ['Invoice #', 'Date', 'Customer', 'Sales Rep', 'Total', 'Paid', 'Balance', 'Status'];
      const rows = this.invoices.map(r => [r.invoice_number, r.invoice_date, r.customer?.name ?? '', r.sales_rep?.name ?? '', r.total, r.paid_amount, r.balance_due, r.status]);
      exportCSV('sales_report', headers, rows);
    },
  };
}
</script>
@endpush
