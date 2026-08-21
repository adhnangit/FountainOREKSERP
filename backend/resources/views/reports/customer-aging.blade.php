@extends('layouts.app')
@section('title', 'Customer Aging Report')
@section('page-title', 'Customer Aging Report')
@section('page-desc', 'Outstanding receivables by aging period')

@section('content')
<div x-data="customerAgingPage()" x-init="init()">

  <div class="print-header items-center justify-between mb-4 pb-3 border-b border-gray-200">
    <div><h2 class="text-lg font-bold text-gray-800">Customer Aging Report</h2>
      <p class="text-xs text-gray-400">As of: <span x-text="fmtDate(asOfDate)"></span></p></div>
  </div>

  {{-- Filter Panel --}}
  <div class="card mb-5 no-print overflow-hidden">
    <div class="px-4 py-2.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between" style="background:#f8fafc">
      <div class="flex items-center gap-2">
        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">As of Date</span>
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
        <label class="label text-xs">As of Date</label>
        <input type="date" x-model="asOfDate" @change="load()" class="input text-sm py-1.5" />
      </div>
      <div>
        <label class="label text-xs">Search</label>
        <div class="relative">
          <input x-model="search" type="text" placeholder="Search customer…" class="input text-sm py-1.5 pl-8 w-48" />
          <svg class="w-4 h-4 absolute left-2 top-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        </div>
      </div>
      <div class="flex items-end">
        <button @click="load()" class="btn-primary text-sm py-1.5 px-4 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          Refresh
        </button>
      </div>
    </div>
  </div>

  {{-- Loading skeleton --}}
  <div x-show="loading" class="space-y-4">
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
      <template x-for="i in [1,2,3,4,5]" :key="i">
        <div class="card p-4 animate-pulse"><div class="h-3 bg-gray-200 rounded w-2/3 mb-2"></div><div class="h-6 bg-gray-200 rounded w-1/2"></div></div>
      </template>
    </div>
    <div class="card p-0 overflow-hidden animate-pulse">
      <div class="h-10 bg-gray-100 border-b border-gray-200"></div>
      <template x-for="i in [1,2,3,4,5]" :key="i">
        <div class="flex gap-4 px-4 py-3 border-b border-gray-100">
          <div class="h-3 bg-gray-200 rounded flex-1"></div>
          <div class="h-3 bg-gray-200 rounded w-20"></div>
          <div class="h-3 bg-gray-200 rounded w-20"></div>
          <div class="h-3 bg-gray-200 rounded w-20"></div>
          <div class="h-3 bg-gray-200 rounded w-20"></div>
        </div>
      </template>
    </div>
  </div>

  <div x-show="!loading">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-5">
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#1B3EB6">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#eef2ff">
          <svg style="width:18px;height:18px;color:#1B3EB6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Current (0–30)</div>
          <div class="text-lg font-bold text-gray-900 dark:text-white leading-tight" x-text="fmtMoney(totals.current ?? 0)"></div>
        </div>
      </div>
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#f97316">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fff7ed">
          <svg style="width:18px;height:18px;color:#f97316" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs uppercase tracking-wider font-semibold mb-0.5" style="color:#f97316">31–60 Days</div>
          <div class="text-lg font-bold leading-tight" style="color:#f97316" x-text="fmtMoney(totals.days_31_60 ?? 0)"></div>
        </div>
      </div>
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#ea580c">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fff7ed">
          <svg style="width:18px;height:18px;color:#ea580c" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs uppercase tracking-wider font-semibold mb-0.5" style="color:#ea580c">61–90 Days</div>
          <div class="text-lg font-bold leading-tight" style="color:#ea580c" x-text="fmtMoney(totals.days_61_90 ?? 0)"></div>
        </div>
      </div>
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#dc2626">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fff1f2">
          <svg style="width:18px;height:18px;color:#dc2626" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs text-red-500 uppercase tracking-wider font-semibold mb-0.5">90+ Days</div>
          <div class="text-lg font-bold text-red-600 leading-tight" x-text="fmtMoney(totals.days_90_plus ?? 0)"></div>
        </div>
      </div>
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#7c3aed">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#f5f3ff">
          <svg style="width:18px;height:18px;color:#7c3aed" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs text-violet-500 uppercase tracking-wider font-semibold mb-0.5">Total Outstanding</div>
          <div class="text-lg font-bold text-violet-600 leading-tight" x-text="fmtMoney(totals.total ?? 0)"></div>
        </div>
      </div>
    </div>

    {{-- Table --}}
    <div class="card p-0 overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800">
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Customer Aging</span>
        <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-semibold" x-text="filtered.length + ' customers'"></span>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="table-hd">Customer</th>
              <th class="table-hd">Phone</th>
              <th class="table-hd text-right">Current (0–30)</th>
              <th class="table-hd text-right" style="color:#f97316">31–60 Days</th>
              <th class="table-hd text-right" style="color:#ea580c">61–90 Days</th>
              <th class="table-hd text-right" style="color:#dc2626">90+ Days</th>
              <th class="table-hd text-right font-bold">Total</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100">
            <template x-for="row in filtered" :key="row.customer_id ?? Math.random()">
              <tr class="hover:bg-gray-50">
                <td class="table-td">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                         :style="(row.days_90_plus??0)>0?'background:#dc2626':((row.days_61_90??0)>0?'background:#ea580c':((row.days_31_60??0)>0?'background:#f97316':'background:#1B3EB6'))"
                         x-text="(row.customer_name||'?').charAt(0).toUpperCase()"></div>
                    <span class="font-medium text-gray-900 dark:text-gray-100" x-text="row.customer_name ?? '—'"></span>
                  </div>
                </td>
                <td class="table-td text-gray-500 text-sm" x-text="row.phone ?? '—'"></td>
                <td class="table-td text-right text-gray-700" x-text="fmtMoney(row.current ?? 0)"></td>
                <td class="table-td text-right font-medium" :class="(row.days_31_60??0)>0?'text-orange-500':'text-gray-400'" x-text="fmtMoney(row.days_31_60 ?? 0)"></td>
                <td class="table-td text-right font-medium" :class="(row.days_61_90??0)>0?'text-orange-600':'text-gray-400'" x-text="fmtMoney(row.days_61_90 ?? 0)"></td>
                <td class="table-td text-right font-semibold" :class="(row.days_90_plus??0)>0?'text-red-600':'text-gray-400'" x-text="fmtMoney(row.days_90_plus ?? 0)"></td>
                <td class="table-td text-right font-bold" :class="(row.total??0)>0?'text-violet-600':'text-gray-500'" x-text="fmtMoney(row.total ?? 0)"></td>
              </tr>
            </template>
            <template x-if="filtered.length === 0">
              <tr>
                <td colspan="7">
                  <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-green-50 flex items-center justify-center mb-3">
                      <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-600">No outstanding receivables</p>
                    <p class="text-xs text-gray-400 mt-0.5">All customer accounts are clear</p>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
          <tfoot class="bg-gray-50 border-t-2 border-gray-200" x-show="filtered.length > 0">
            <tr>
              <td colspan="2" class="table-td font-bold text-gray-800">Total (<span x-text="filtered.length"></span> customers)</td>
              <td class="table-td text-right font-bold" x-text="fmtMoney(filtered.reduce((s,r)=>s+(r.current??0),0))"></td>
              <td class="table-td text-right font-bold text-orange-500" x-text="fmtMoney(filtered.reduce((s,r)=>s+(r.days_31_60??0),0))"></td>
              <td class="table-td text-right font-bold text-orange-600" x-text="fmtMoney(filtered.reduce((s,r)=>s+(r.days_61_90??0),0))"></td>
              <td class="table-td text-right font-bold text-red-600" x-text="fmtMoney(filtered.reduce((s,r)=>s+(r.days_90_plus??0),0))"></td>
              <td class="table-td text-right font-bold text-violet-600" x-text="fmtMoney(filtered.reduce((s,r)=>s+(r.total??0),0))"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function customerAgingPage() {
  return {
    items: [], loading: true, search: '',
    asOfDate: new Date().toISOString().slice(0,10),
    totals: {},
    get filtered() {
      const q = this.search.toLowerCase();
      if (!q) return this.items;
      return this.items.filter(r => (r.customer_name ?? '').toLowerCase().includes(q));
    },
    async init() { await this.load(); },
    async load() {
      this.loading = true;
      try {
        const p = new URLSearchParams({ as_of_date: this.asOfDate });
        const res  = await apiFetch('/reports/customer-aging?' + p);
        const data = await res.json();
        this.items  = data.data ?? data ?? [];
        this.totals = data.totals ?? this.calcTotals(this.items);
        if (data.as_of_date) this.asOfDate = data.as_of_date;
      } catch (e) { toast('Failed to load aging report', 'error'); }
      finally { this.loading = false; }
    },
    calcTotals(rows) {
      return {
        current:      rows.reduce((s,r) => s+(r.current??0), 0),
        days_31_60:   rows.reduce((s,r) => s+(r.days_31_60??0), 0),
        days_61_90:   rows.reduce((s,r) => s+(r.days_61_90??0), 0),
        days_90_plus: rows.reduce((s,r) => s+(r.days_90_plus??0), 0),
        total:        rows.reduce((s,r) => s+(r.total??0), 0),
      };
    },
    doExport() {
      const headers = ['Customer', 'Phone', 'Current (0-30)', '31-60 Days', '61-90 Days', '90+ Days', 'Total'];
      const rows = this.filtered.map(r => [r.customer_name, r.phone, r.current??0, r.days_31_60??0, r.days_61_90??0, r.days_90_plus??0, r.total??0]);
      exportCSV('customer_aging_' + this.asOfDate, headers, rows);
    },
  };
}
</script>
@endpush
