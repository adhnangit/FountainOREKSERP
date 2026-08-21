<?php $__env->startSection('title', 'Purchase Report'); ?>
<?php $__env->startSection('page-title', 'Purchase Report'); ?>
<?php $__env->startSection('page-desc', 'Purchase orders and supplier spending analysis'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="purchaseReport()" x-init="init()">

  <!-- Print header -->
  <div class="print-header items-center justify-between mb-4 pb-3 border-b border-gray-200">
    <div>
      <h2 class="text-lg font-bold text-gray-800">Purchase Report</h2>
      <p class="text-xs text-gray-400" x-text="'Period: ' + fmtDate(filters.from_date) + ' – ' + fmtDate(filters.to_date)"></p>
    </div>
  </div>

  <!-- Filter panel -->
  <div class="card mb-5 no-print overflow-visible">
    <div class="px-4 py-2.5 border-b border-gray-100 flex items-center justify-between rounded-t-xl" style="background:#f8fafc">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>
        </svg>
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Filters</span>
      </div>
      <div class="flex items-center gap-2">
        <button @click="window.print()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
          Print
        </button>
        <button @click="doExport()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Export CSV
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
        <label class="label text-xs">Supplier</label>
        <div class="search-dd" x-data="{ open: false, q: '' }" @click.away="open = false" @keydown.escape="open = false">
          <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.sFilt?.focus())"
                  class="input text-sm py-1.5 w-full text-left flex items-center justify-between gap-2">
            <span class="truncate" :class="filters.supplier_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                  x-text="filters.supplier_id ? (suppliers.find(s => s.id == filters.supplier_id)?.name || '—') : 'All Suppliers'"></span>
            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu">
            <div class="p-2 border-b border-gray-100 dark:border-gray-700">
              <input x-ref="sFilt" x-model="q" type="text" placeholder="Search supplier…" class="input text-sm w-full py-1.5" @keydown.stop />
            </div>
            <div class="max-h-52 overflow-y-auto py-1">
              <button type="button" @click="filters.supplier_id = ''; open = false; q = ''"
                      class="search-dd-item" :class="!filters.supplier_id ? 'active' : ''">
                <span class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate flex-1">All Suppliers</span>
              </button>
              <template x-for="s in suppliers.filter(s => !q || s.name.toLowerCase().includes(q.toLowerCase()))" :key="s.id">
                <button type="button" @click="filters.supplier_id = s.id; open = false; q = ''"
                        class="search-dd-item" :class="filters.supplier_id == s.id ? 'active' : ''">
                  <span class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate flex-1" x-text="s.name"></span>
                </button>
              </template>
            </div>
          </div>
        </div>
      </div>
      <div>
        <label class="label text-xs">Status</label>
        <select x-model="filters.status" class="input text-sm py-1.5">
          <option value="">All Statuses</option>
          <option value="draft">Draft</option>
          <option value="approved">Approved</option>
          <option value="received">Received</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="flex items-end gap-2">
        <button @click="load()" class="btn-primary text-sm py-1.5 px-5">
          <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          Run Report
        </button>
        <button @click="resetFilters()" class="btn-secondary text-sm py-1.5 px-3">Reset</button>
      </div>
    </div>
  </div>

  <!-- Summary cards — skeleton -->
  <template x-if="loading">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
      <template x-for="i in 4" :key="i">
        <div class="card p-4 animate-pulse">
          <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-gray-100 flex-shrink-0"></div>
            <div class="flex-1">
              <div class="h-3 bg-gray-100 rounded w-20 mb-2"></div>
              <div class="h-6 bg-gray-100 rounded w-28"></div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </template>

  <!-- Summary cards -->
  <template x-if="!loading">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">

      <div class="card p-4 border-l-4" style="border-left-color:#7c3aed">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#ede9fe">
            <svg class="w-5 h-5" style="color:#7c3aed" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Total Orders</div>
            <div class="text-xl font-bold text-gray-900" x-text="(summary.total_orders ?? 0) + ' POs'"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#1B3EB6">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#dbeafe">
            <svg class="w-5 h-5" style="color:#1B3EB6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Total Value</div>
            <div class="text-xl font-bold text-gray-900" x-text="fmtMoney(summary.total_value ?? 0)"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#059669">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#d1fae5">
            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Paid</div>
            <div class="text-xl font-bold text-green-600" x-text="fmtMoney(summary.total_paid ?? 0)"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#f59e0b">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fef3c7">
            <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Outstanding</div>
            <div class="text-xl font-bold text-amber-600" x-text="fmtMoney(summary.total_outstanding ?? 0)"></div>
          </div>
        </div>
      </div>

    </div>
  </template>

  <!-- Tabs -->
  <div class="flex gap-1 mb-4 no-print bg-gray-100 rounded-xl p-1 w-fit">
    <button @click="activeTab='orders'" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all"
            :class="activeTab==='orders' ? 'bg-white shadow text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-700'">
      PO List
    </button>
    <button @click="activeTab='suppliers'" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all"
            :class="activeTab==='suppliers' ? 'bg-white shadow text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-700'">
      By Supplier
    </button>
  </div>

  <!-- TAB: PO List -->
  <div x-show="activeTab === 'orders'" class="card overflow-hidden">
    <div class="px-5 py-3 flex items-center justify-between border-b border-gray-100" style="background:#f8fafc">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <span class="text-sm font-semibold text-gray-700">Purchase Orders</span>
      </div>
      <template x-if="!loading">
        <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-violet-100 text-violet-700"
              x-text="orders.length + ' order' + (orders.length === 1 ? '' : 's')"></span>
      </template>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-100">
        <thead style="background:#f8fafc">
          <tr>
            <th class="table-hd">PO #</th>
            <th class="table-hd">Date</th>
            <th class="table-hd">Supplier</th>
            <th class="table-hd text-right">Total</th>
            <th class="table-hd text-right" style="color:#059669">Paid</th>
            <th class="table-hd text-right" style="color:#f59e0b">Balance</th>
            <th class="table-hd">Status</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-50">

          <!-- Skeleton -->
          <template x-if="loading">
            <template x-for="i in 6" :key="i">
              <tr class="animate-pulse">
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-20"></div></td>
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-20"></div></td>
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-32"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-24 ml-auto"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-20 ml-auto"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-20 ml-auto"></div></td>
                <td class="table-td"><div class="h-5 bg-gray-100 rounded-full w-16"></div></td>
              </tr>
            </template>
          </template>

          <!-- Data rows -->
          <template x-if="!loading">
            <template x-for="po in orders" :key="po.id">
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="table-td">
                  <a :href="'javascript:void(0)'" class="font-mono text-xs font-semibold text-indigo-600 hover:underline" x-text="po.po_number"></a>
                </td>
                <td class="table-td text-sm text-gray-600" x-text="fmtDate(po.order_date)"></td>
                <td class="table-td">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center text-xs font-bold text-amber-700 flex-shrink-0"
                         x-text="(po.supplier?.name ?? '?')[0].toUpperCase()"></div>
                    <span class="font-medium text-gray-800" x-text="po.supplier?.name ?? '—'"></span>
                  </div>
                </td>
                <td class="table-td text-right font-semibold text-gray-800" x-text="fmtMoney(po.total)"></td>
                <td class="table-td text-right text-green-600 font-medium" x-text="fmtMoney(po.paid_amount)"></td>
                <td class="table-td text-right font-semibold" :class="(po.balance_due??0)>0?'text-amber-600':'text-gray-300'" x-text="fmtMoney(po.balance_due)"></td>
                <td class="table-td">
                  <span class="text-xs px-2 py-0.5 rounded-full font-semibold capitalize"
                    :class="{'bg-green-100 text-green-700':po.status==='received','bg-blue-100 text-blue-700':po.status==='approved','bg-gray-100 text-gray-500':po.status==='draft','bg-red-100 text-red-600':po.status==='cancelled'}"
                    x-text="po.status"></span>
                </td>
              </tr>
            </template>
          </template>

          <!-- Empty state -->
          <template x-if="!loading && orders.length === 0">
            <tr>
              <td colspan="7" class="py-14 text-center">
                <div class="inline-flex flex-col items-center gap-3">
                  <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                  </div>
                  <div>
                    <div class="text-sm font-semibold text-gray-700">No purchase orders found</div>
                    <div class="text-xs text-gray-400 mt-0.5">Try adjusting your date range or filters</div>
                  </div>
                </div>
              </td>
            </tr>
          </template>

        </tbody>

        <template x-if="!loading && orders.length > 0">
          <tfoot style="background:#f8fafc">
            <tr class="border-t-2 border-gray-200">
              <td colspan="3" class="table-td font-bold text-gray-700">Total (<span x-text="orders.length"></span>)</td>
              <td class="table-td text-right font-bold text-gray-800" x-text="fmtMoney(orders.reduce((s,r)=>s+(r.total??0),0))"></td>
              <td class="table-td text-right font-bold text-green-600" x-text="fmtMoney(orders.reduce((s,r)=>s+(r.paid_amount??0),0))"></td>
              <td class="table-td text-right font-bold text-amber-600" x-text="fmtMoney(orders.reduce((s,r)=>s+(r.balance_due??0),0))"></td>
              <td></td>
            </tr>
          </tfoot>
        </template>
      </table>
    </div>
  </div>

  <!-- TAB: By Supplier -->
  <div x-show="activeTab === 'suppliers'" class="card overflow-hidden">
    <div class="px-5 py-3 flex items-center justify-between border-b border-gray-100" style="background:#f8fafc">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
        <span class="text-sm font-semibold text-gray-700">Spending by Supplier</span>
      </div>
      <template x-if="!loading">
        <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-amber-100 text-amber-700"
              x-text="bySupplier.length + ' supplier' + (bySupplier.length === 1 ? '' : 's')"></span>
      </template>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-100">
        <thead style="background:#f8fafc">
          <tr>
            <th class="table-hd w-8">#</th>
            <th class="table-hd">Supplier</th>
            <th class="table-hd text-right">POs</th>
            <th class="table-hd text-right">Total Purchases</th>
            <th class="table-hd text-right" style="color:#059669">Paid</th>
            <th class="table-hd text-right" style="color:#f59e0b">Outstanding</th>
            <th class="table-hd">Share</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-50">

          <template x-if="loading">
            <template x-for="i in 5" :key="i">
              <tr class="animate-pulse">
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-4"></div></td>
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-32"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-8 ml-auto"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-24 ml-auto"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-20 ml-auto"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-20 ml-auto"></div></td>
                <td class="table-td"><div class="h-2 bg-gray-100 rounded w-24"></div></td>
              </tr>
            </template>
          </template>

          <template x-if="!loading">
            <template x-for="(s, i) in bySupplier" :key="i">
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="table-td">
                  <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                       :style="'background:' + (i===0?'#7c3aed':i===1?'#5b21b6':i===2?'#4c1d95':'#6b7280')"
                       x-text="i+1"></div>
                </td>
                <td class="table-td">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center text-xs font-bold text-amber-700 flex-shrink-0"
                         x-text="(s.supplier?.name ?? '?')[0].toUpperCase()"></div>
                    <span class="font-medium text-gray-800" x-text="s.supplier?.name ?? 'Unknown'"></span>
                  </div>
                </td>
                <td class="table-td text-right text-gray-600" x-text="s.po_count"></td>
                <td class="table-td text-right font-semibold text-gray-800" x-text="fmtMoney(s.total_purchases)"></td>
                <td class="table-td text-right text-green-600 font-medium" x-text="fmtMoney(s.total_paid)"></td>
                <td class="table-td text-right font-semibold" :class="(s.total_outstanding??0)>0?'text-amber-600':'text-gray-300'" x-text="fmtMoney(s.total_outstanding)"></td>
                <td class="table-td w-32">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 h-1.5 rounded-full bg-gray-100">
                      <div class="h-full rounded-full bg-violet-500 transition-all"
                           :style="'width:' + Math.min(100, Math.round((s.total_purchases / (bySupplier[0]?.total_purchases||1)) * 100)) + '%'"></div>
                    </div>
                    <span class="text-xs text-gray-400 tabular-nums"
                          x-text="Math.round((s.total_purchases / (summary.total_value||1)) * 100) + '%'"></span>
                  </div>
                </td>
              </tr>
            </template>
          </template>

          <template x-if="!loading && bySupplier.length === 0">
            <tr>
              <td colspan="7" class="py-14 text-center">
                <div class="inline-flex flex-col items-center gap-3">
                  <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                  </div>
                  <div class="text-sm font-semibold text-gray-700">No supplier data</div>
                </div>
              </td>
            </tr>
          </template>

        </tbody>
      </table>
    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function purchaseReport() {
  const today = new Date().toISOString().slice(0,10);
  const first = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0,10);
  return {
    loading: true, suppliers: [],
    filters: { from_date: first, to_date: today, supplier_id: '', status: '' },
    summary: {}, orders: [], bySupplier: [],
    activeTab: 'orders',
    async init() {
      try {
        const sr = await apiFetch('/suppliers?per_page=500').then(r => r.json());
        this.suppliers = sr.data ?? sr ?? [];
      } catch {}
      await this.load();
    },
    async load() {
      this.loading = true;
      try {
        const p = new URLSearchParams();
        if (this.filters.from_date)   p.set('from_date',   this.filters.from_date);
        if (this.filters.to_date)     p.set('to_date',     this.filters.to_date);
        if (this.filters.supplier_id) p.set('supplier_id', this.filters.supplier_id);
        if (this.filters.status)      p.set('status',      this.filters.status);
        const d = await apiFetch('/reports/purchases?' + p).then(r => r.json());
        this.summary    = d.summary    ?? {};
        this.orders     = Array.isArray(d.orders)     ? d.orders     : [];
        this.bySupplier = Array.isArray(d.bySupplier) ? d.bySupplier : [];
      } catch (e) { toast('Failed to load purchase report', 'error'); }
      finally { this.loading = false; }
    },
    resetFilters() {
      const now = new Date();
      this.filters = {
        from_date: new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0,10),
        to_date: now.toISOString().slice(0,10),
        supplier_id: '', status: ''
      };
      this.load();
    },
    doExport() {
      const headers = ['PO #', 'Date', 'Supplier', 'Total', 'Paid', 'Balance', 'Status'];
      const rows = this.orders.map(r => [r.po_number, r.order_date, r.supplier?.name??'', r.total, r.paid_amount, r.balance_due, r.status]);
      exportCSV('purchase_report_' + this.filters.from_date + '_' + this.filters.to_date, headers, rows);
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/reports/purchase.blade.php ENDPATH**/ ?>