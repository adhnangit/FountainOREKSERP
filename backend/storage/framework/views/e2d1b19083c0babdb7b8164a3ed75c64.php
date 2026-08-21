<?php $__env->startSection('title', 'Cheque Report'); ?>
<?php $__env->startSection('page-title', 'Cheque Report'); ?>
<?php $__env->startSection('page-desc', 'Cheque status, bank summary and history'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="chequeReport()" x-init="init()">

  <!-- Print header -->
  <div class="print-header items-center justify-between mb-4 pb-3 border-b border-gray-200">
    <div>
      <h2 class="text-lg font-bold text-gray-800">Cheque Report</h2>
      <p class="text-xs text-gray-400" x-text="'Period: ' + fmtDate(filters.from_date) + ' – ' + fmtDate(filters.to_date)"></p>
    </div>
  </div>

  <!-- Filter panel -->
  <div class="card mb-5 no-print overflow-hidden">
    <div class="px-4 py-2.5 border-b border-gray-100 flex items-center justify-between" style="background:#f8fafc">
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
      <div>
        <label class="label text-xs">Direction</label>
        <select x-model="filters.direction" class="input text-sm py-1.5">
          <option value="">All Directions</option>
          <option value="received">Received</option>
          <option value="issued">Issued</option>
        </select>
      </div>
      <div>
        <label class="label text-xs">Status</label>
        <select x-model="filters.status" class="input text-sm py-1.5">
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="deposited">Deposited</option>
          <option value="cleared">Cleared</option>
          <option value="bounced">Bounced</option>
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
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-5">
      <template x-for="i in 6" :key="i">
        <div class="card p-4 animate-pulse">
          <div class="h-3 bg-gray-100 rounded w-16 mb-2"></div>
          <div class="h-5 bg-gray-100 rounded w-24"></div>
        </div>
      </template>
    </div>
  </template>

  <!-- Summary cards -->
  <template x-if="!loading">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-5">

      <div class="card p-4 border-l-4" style="border-left-color:#059669">
        <div class="flex items-start gap-2.5">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#d1fae5">
            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Received</div>
            <div class="text-base font-bold text-green-600" x-text="fmtMoney(summary.total_received ?? 0)"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#dc2626">
        <div class="flex items-start gap-2.5">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fee2e2">
            <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 8V4m0 0l4 4m-4-4l-4 4M7 20v-4m0 0l-4 4m4-4l4 4"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Issued</div>
            <div class="text-base font-bold text-red-500" x-text="fmtMoney(summary.total_issued ?? 0)"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#f59e0b">
        <div class="flex items-start gap-2.5">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fef3c7">
            <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Pending</div>
            <div class="text-base font-bold text-amber-600" x-text="(summary.pending_count??0) + ' / ' + fmtMoney(summary.pending_amount??0)"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#1B3EB6">
        <div class="flex items-start gap-2.5">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#dbeafe">
            <svg class="w-5 h-5" style="color:#1B3EB6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Cleared</div>
            <div class="text-base font-bold text-indigo-600" x-text="(summary.cleared_count??0) + ' / ' + fmtMoney(summary.cleared_amount??0)"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#ef4444">
        <div class="flex items-start gap-2.5">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 bg-red-50">
            <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Bounced</div>
            <div class="text-base font-bold text-red-600" x-text="(summary.bounced_count??0) + ' / ' + fmtMoney(summary.bounced_amount??0)"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#6b7280">
        <div class="flex items-start gap-2.5">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 bg-gray-100">
            <svg class="w-5 h-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Total</div>
            <div class="text-base font-bold text-gray-700" x-text="(summary.total_count ?? 0) + ' cheques'"></div>
          </div>
        </div>
      </div>

    </div>
  </template>

  <!-- Tabs -->
  <div class="flex gap-1 mb-4 no-print bg-gray-100 rounded-xl p-1 w-fit">
    <button @click="activeTab='list'" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all"
            :class="activeTab==='list' ? 'bg-white shadow text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-700'">
      Cheque List
    </button>
    <button @click="activeTab='status'" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all"
            :class="activeTab==='status' ? 'bg-white shadow text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-700'">
      By Status
    </button>
    <button @click="activeTab='bank'" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all"
            :class="activeTab==='bank' ? 'bg-white shadow text-indigo-600 font-semibold' : 'text-gray-500 hover:text-gray-700'">
      By Bank
    </button>
  </div>

  <!-- TAB: Cheque List -->
  <div x-show="activeTab === 'list'" class="card overflow-hidden">
    <div class="px-5 py-3 flex items-center justify-between border-b border-gray-100" style="background:#f8fafc">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        <span class="text-sm font-semibold text-gray-700">All Cheques</span>
      </div>
      <template x-if="!loading">
        <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-gray-100 text-gray-600"
              x-text="cheques.length + ' cheque' + (cheques.length === 1 ? '' : 's')"></span>
      </template>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-100">
        <thead style="background:#f8fafc">
          <tr>
            <th class="table-hd">Cheque #</th>
            <th class="table-hd">Date</th>
            <th class="table-hd">Party</th>
            <th class="table-hd">Bank</th>
            <th class="table-hd">Direction</th>
            <th class="table-hd text-right">Amount</th>
            <th class="table-hd">Status</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-50">

          <template x-if="loading">
            <template x-for="i in 6" :key="i">
              <tr class="animate-pulse">
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-20"></div></td>
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-20"></div></td>
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-32"></div></td>
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-24"></div></td>
                <td class="table-td"><div class="h-5 bg-gray-100 rounded-full w-16"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-24 ml-auto"></div></td>
                <td class="table-td"><div class="h-5 bg-gray-100 rounded-full w-16"></div></td>
              </tr>
            </template>
          </template>

          <template x-if="!loading">
            <template x-for="c in cheques" :key="c.id">
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="table-td font-mono text-xs font-semibold text-gray-700" x-text="c.cheque_number ?? '—'"></td>
                <td class="table-td text-sm text-gray-600" x-text="fmtDate(c.cheque_date)"></td>
                <td class="table-td font-medium text-gray-800" x-text="c.party_name ?? '—'"></td>
                <td class="table-td text-gray-500 text-sm" x-text="c.bank_name ?? '—'"></td>
                <td class="table-td">
                  <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-semibold capitalize"
                    :class="c.direction==='received' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'">
                    <svg x-show="c.direction==='received'" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M7 16V4m0 12l-4-4m4 4l4-4"/></svg>
                    <svg x-show="c.direction!=='received'" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 8V20m0-12l4 4m-4-4l-4 4"/></svg>
                    <span x-text="c.direction"></span>
                  </span>
                </td>
                <td class="table-td text-right font-semibold text-gray-800" x-text="fmtMoney(c.amount)"></td>
                <td class="table-td">
                  <span class="text-xs px-2 py-0.5 rounded-full font-semibold capitalize"
                    :class="{'bg-amber-100 text-amber-700':c.status==='pending','bg-blue-100 text-blue-700':c.status==='deposited','bg-green-100 text-green-700':c.status==='cleared','bg-red-100 text-red-600':c.status==='bounced','bg-gray-100 text-gray-500':c.status==='cancelled'}"
                    x-text="c.status"></span>
                </td>
              </tr>
            </template>
          </template>

          <template x-if="!loading && cheques.length === 0">
            <tr>
              <td colspan="7" class="py-14 text-center">
                <div class="inline-flex flex-col items-center gap-3">
                  <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                  </div>
                  <div>
                    <div class="text-sm font-semibold text-gray-700">No cheques found</div>
                    <div class="text-xs text-gray-400 mt-0.5">Try adjusting your date range or filters</div>
                  </div>
                </div>
              </td>
            </tr>
          </template>

        </tbody>

        <template x-if="!loading && cheques.length > 0">
          <tfoot style="background:#f8fafc">
            <tr class="border-t-2 border-gray-200">
              <td colspan="5" class="table-td font-bold text-gray-700">Total (<span x-text="cheques.length"></span>)</td>
              <td class="table-td text-right font-bold text-gray-800" x-text="fmtMoney(cheques.reduce((s,r)=>s+(r.amount??0),0))"></td>
              <td></td>
            </tr>
          </tfoot>
        </template>
      </table>
    </div>
  </div>

  <!-- TAB: By Status -->
  <div x-show="activeTab === 'status'" class="card overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2" style="background:#f8fafc">
      <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
      <span class="text-sm font-semibold text-gray-700">Breakdown by Status</span>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-100">
        <thead style="background:#f8fafc">
          <tr>
            <th class="table-hd">Status</th>
            <th class="table-hd text-right">Count</th>
            <th class="table-hd text-right">Total Amount</th>
            <th class="table-hd">Distribution</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-50">
          <template x-if="loading">
            <template x-for="i in 5" :key="i">
              <tr class="animate-pulse">
                <td class="table-td"><div class="h-5 bg-gray-100 rounded-full w-20"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-8 ml-auto"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-24 ml-auto"></div></td>
                <td class="table-td"><div class="h-2 bg-gray-100 rounded w-32"></div></td>
              </tr>
            </template>
          </template>
          <template x-if="!loading">
            <template x-for="[status, data] in Object.entries(byStatus)" :key="status">
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="table-td">
                  <span class="text-xs px-2.5 py-1 rounded-full font-semibold capitalize"
                    :class="{'bg-amber-100 text-amber-700':status==='pending','bg-blue-100 text-blue-700':status==='deposited','bg-green-100 text-green-700':status==='cleared','bg-red-100 text-red-600':status==='bounced','bg-gray-100 text-gray-500':status==='cancelled'}"
                    x-text="status"></span>
                </td>
                <td class="table-td text-right font-semibold text-gray-700" x-text="data.count"></td>
                <td class="table-td text-right font-semibold text-gray-800" x-text="fmtMoney(data.amount)"></td>
                <td class="table-td w-40">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 h-1.5 rounded-full bg-gray-100">
                      <div class="h-full rounded-full transition-all"
                           :class="{'bg-amber-400':status==='pending','bg-blue-400':status==='deposited','bg-green-500':status==='cleared','bg-red-500':status==='bounced','bg-gray-300':status==='cancelled'}"
                           :style="'width:' + Math.min(100, Math.round((data.count / (summary.total_count||1)) * 100)) + '%'"></div>
                    </div>
                    <span class="text-xs text-gray-400 tabular-nums"
                          x-text="Math.round((data.count / (summary.total_count||1)) * 100) + '%'"></span>
                  </div>
                </td>
              </tr>
            </template>
          </template>
          <template x-if="!loading && Object.keys(byStatus).length === 0">
            <tr><td colspan="4" class="py-10 text-center text-sm text-gray-400">No data available</td></tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>

  <!-- TAB: By Bank -->
  <div x-show="activeTab === 'bank'" class="card overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2" style="background:#f8fafc">
      <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 6l9-3 9 3M3 6v12l9 3 9-3V6"/></svg>
      <span class="text-sm font-semibold text-gray-700">Breakdown by Bank</span>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-100">
        <thead style="background:#f8fafc">
          <tr>
            <th class="table-hd">#</th>
            <th class="table-hd">Bank</th>
            <th class="table-hd text-right">Count</th>
            <th class="table-hd text-right">Total Amount</th>
            <th class="table-hd">Share</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-50">
          <template x-if="loading">
            <template x-for="i in 4" :key="i">
              <tr class="animate-pulse">
                <td class="table-td"><div class="h-6 bg-gray-100 rounded-full w-6"></div></td>
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-32"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-8 ml-auto"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-24 ml-auto"></div></td>
                <td class="table-td"><div class="h-2 bg-gray-100 rounded w-24"></div></td>
              </tr>
            </template>
          </template>
          <template x-if="!loading">
            <template x-for="([bank, data], i) in Object.entries(byBank)" :key="bank">
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="table-td">
                  <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                       :style="'background:' + (i===0?'#1B3EB6':i===1?'#2563eb':i===2?'#3b82f6':'#6b7280')"
                       x-text="i+1"></div>
                </td>
                <td class="table-td font-medium text-gray-800" x-text="bank"></td>
                <td class="table-td text-right text-gray-600" x-text="data.count"></td>
                <td class="table-td text-right font-semibold text-gray-800" x-text="fmtMoney(data.amount)"></td>
                <td class="table-td w-40">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 h-1.5 rounded-full bg-gray-100">
                      <div class="h-full rounded-full bg-indigo-500 transition-all"
                           :style="'width:' + Math.min(100, Math.round((data.count / (summary.total_count||1)) * 100)) + '%'"></div>
                    </div>
                    <span class="text-xs text-gray-400 tabular-nums"
                          x-text="Math.round((data.count / (summary.total_count||1)) * 100) + '%'"></span>
                  </div>
                </td>
              </tr>
            </template>
          </template>
          <template x-if="!loading && Object.keys(byBank).length === 0">
            <tr><td colspan="5" class="py-10 text-center text-sm text-gray-400">No data available</td></tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function chequeReport() {
  const today = new Date().toISOString().slice(0,10);
  const first = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0,10);
  return {
    loading: true,
    filters: { from_date: first, to_date: today, direction: '', status: '' },
    summary: {}, cheques: [], byStatus: {}, byBank: {},
    activeTab: 'list',
    async init() { await this.load(); },
    async load() {
      this.loading = true;
      try {
        const p = new URLSearchParams();
        if (this.filters.from_date) p.set('from_date', this.filters.from_date);
        if (this.filters.to_date)   p.set('to_date',   this.filters.to_date);
        if (this.filters.direction) p.set('direction',  this.filters.direction);
        if (this.filters.status)    p.set('status',     this.filters.status);
        const d = await apiFetch('/reports/cheques?' + p).then(r => r.json());
        this.cheques   = Array.isArray(d.data)    ? d.data    : [];
        this.summary   = d.summary  ?? {};
        this.byStatus  = d.by_status ?? {};
        this.byBank    = d.by_bank   ?? {};
      } catch (e) { toast('Failed to load cheque report', 'error'); }
      finally { this.loading = false; }
    },
    resetFilters() {
      const now = new Date();
      this.filters = {
        from_date: new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0,10),
        to_date: now.toISOString().slice(0,10),
        direction: '', status: ''
      };
      this.load();
    },
    doExport() {
      const headers = ['Cheque #', 'Date', 'Party', 'Bank', 'Direction', 'Amount', 'Status'];
      const rows = this.cheques.map(c => [c.cheque_number??'', c.cheque_date, c.party_name??'', c.bank_name??'', c.direction, c.amount, c.status]);
      exportCSV('cheque_report_' + this.filters.from_date + '_' + this.filters.to_date, headers, rows);
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/reports/cheques.blade.php ENDPATH**/ ?>