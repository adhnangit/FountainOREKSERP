@extends('layouts.app')
@section('title', 'Supplier Aging Report')
@section('page-title', 'Supplier Aging Report')
@section('page-desc', 'Outstanding payables by aging period')

@section('content')
<div x-data="supplierAgingPage()" x-init="init()">

  <!-- Print header -->
  <div class="print-header items-center justify-between mb-4 pb-3 border-b border-gray-200">
    <div>
      <h2 class="text-lg font-bold text-gray-800">Supplier Aging Report</h2>
      <p class="text-xs text-gray-400">As of: <span x-text="fmtDate(asOfDate)"></span></p>
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
        <label class="label text-xs">As of Date</label>
        <input type="date" x-model="asOfDate" class="input text-sm py-1.5" style="min-width:160px" />
      </div>
      <div class="flex-1 min-w-[180px]">
        <label class="label text-xs">Search Supplier</label>
        <div class="relative">
          <input x-model="search" type="text" placeholder="Supplier name…" class="input text-sm py-1.5 pl-8 w-full" />
          <svg class="w-4 h-4 absolute left-2 top-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        </div>
      </div>
      <div class="flex items-end gap-2">
        <button @click="load()" class="btn-primary text-sm py-1.5 px-5">
          <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
          Refresh
        </button>
      </div>
    </div>
  </div>

  <!-- Summary cards — skeleton -->
  <template x-if="loading">
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-5">
      <template x-for="i in 5" :key="i">
        <div class="card p-4 animate-pulse">
          <div class="h-3 bg-gray-200 rounded w-20 mb-3"></div>
          <div class="h-6 bg-gray-200 rounded w-24"></div>
        </div>
      </template>
    </div>
  </template>

  <!-- Summary cards -->
  <template x-if="!loading">
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-5">

      <div class="card p-4 border-l-4" style="border-left-color:#059669">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#d1fae5">
            <svg class="w-5 h-5" style="color:#059669" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div class="min-w-0">
            <div class="text-xs text-gray-500 font-medium mb-1">Current (0-30)</div>
            <div class="text-lg font-bold text-gray-900" x-text="fmtMoney(totals.current ?? 0)"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#f97316">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#ffedd5">
            <svg class="w-5 h-5" style="color:#f97316" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div class="min-w-0">
            <div class="text-xs font-medium mb-1" style="color:#f97316">31–60 Days</div>
            <div class="text-lg font-bold" style="color:#f97316" x-text="fmtMoney(totals.days_31_60 ?? 0)"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#ea580c">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fee2e2">
            <svg class="w-5 h-5" style="color:#ea580c" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          </div>
          <div class="min-w-0">
            <div class="text-xs font-medium mb-1" style="color:#ea580c">61–90 Days</div>
            <div class="text-lg font-bold" style="color:#ea580c" x-text="fmtMoney(totals.days_61_90 ?? 0)"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#dc2626">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fee2e2">
            <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
          </div>
          <div class="min-w-0">
            <div class="text-xs text-red-500 font-medium mb-1">90+ Days</div>
            <div class="text-lg font-bold text-red-600" x-text="fmtMoney(totals.days_90_plus ?? 0)"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#7c3aed">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#ede9fe">
            <svg class="w-5 h-5" style="color:#7c3aed" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          </div>
          <div class="min-w-0">
            <div class="text-xs font-medium mb-1" style="color:#7c3aed">Total Payable</div>
            <div class="text-lg font-bold" style="color:#7c3aed" x-text="fmtMoney(totals.total ?? 0)"></div>
          </div>
        </div>
      </div>

    </div>
  </template>

  <!-- Table -->
  <div class="card overflow-hidden">
    <!-- Table header -->
    <div class="px-5 py-3 flex items-center justify-between border-b border-gray-100" style="background:#f8fafc">
      <div class="flex items-center gap-2.5">
        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        <span class="text-sm font-semibold text-gray-700">Supplier Balances</span>
      </div>
      <template x-if="!loading">
        <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
              :class="filtered.length > 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'"
              x-text="filtered.length + ' supplier' + (filtered.length === 1 ? '' : 's')"></span>
      </template>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-100">
        <thead style="background:#f8fafc">
          <tr>
            <th class="table-hd">Supplier</th>
            <th class="table-hd">Phone</th>
            <th class="table-hd text-right" style="color:#059669">Current (0-30)</th>
            <th class="table-hd text-right" style="color:#f97316">31–60 Days</th>
            <th class="table-hd text-right" style="color:#ea580c">61–90 Days</th>
            <th class="table-hd text-right" style="color:#dc2626">90+ Days</th>
            <th class="table-hd text-right" style="color:#7c3aed">Total</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-50">

          <!-- Skeleton rows -->
          <template x-if="loading">
            <template x-for="i in 6" :key="i">
              <tr class="animate-pulse">
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-32"></div></td>
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-24"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-20 ml-auto"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-20 ml-auto"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-20 ml-auto"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-20 ml-auto"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-24 ml-auto"></div></td>
              </tr>
            </template>
          </template>

          <!-- Data rows -->
          <template x-if="!loading">
            <template x-for="row in filtered" :key="row.supplier_id ?? Math.random()">
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="table-td">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 text-white"
                         :style="'background:' + ((row.days_90_plus??0)>0 ? '#dc2626' : (row.days_61_90??0)>0 ? '#ea580c' : (row.days_31_60??0)>0 ? '#f97316' : '#6b7280')">
                      <span x-text="(row.supplier_name??'?')[0].toUpperCase()"></span>
                    </div>
                    <span class="font-medium text-gray-900" x-text="row.supplier_name ?? '—'"></span>
                  </div>
                </td>
                <td class="table-td text-gray-500 text-sm" x-text="row.phone ?? '—'"></td>
                <td class="table-td text-right text-gray-700" x-text="fmtMoney(row.current ?? 0)"></td>
                <td class="table-td text-right font-medium" :class="(row.days_31_60??0)>0 ? 'text-orange-500' : 'text-gray-300'" x-text="fmtMoney(row.days_31_60 ?? 0)"></td>
                <td class="table-td text-right font-medium" :class="(row.days_61_90??0)>0 ? 'text-orange-600' : 'text-gray-300'" x-text="fmtMoney(row.days_61_90 ?? 0)"></td>
                <td class="table-td text-right font-semibold" :class="(row.days_90_plus??0)>0 ? 'text-red-600' : 'text-gray-300'" x-text="fmtMoney(row.days_90_plus ?? 0)"></td>
                <td class="table-td text-right font-bold" :class="(row.total??0)>0 ? 'text-violet-600' : 'text-gray-400'" x-text="fmtMoney(row.total ?? 0)"></td>
              </tr>
            </template>
          </template>

          <!-- Empty state -->
          <template x-if="!loading && filtered.length === 0">
            <tr>
              <td colspan="7" class="py-14 text-center">
                <div class="inline-flex flex-col items-center gap-3">
                  <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  </div>
                  <div>
                    <div class="text-sm font-semibold text-gray-700">All accounts clear</div>
                    <div class="text-xs text-gray-400 mt-0.5">No outstanding payables for the selected date</div>
                  </div>
                </div>
              </td>
            </tr>
          </template>

        </tbody>

        <!-- Footer totals -->
        <template x-if="!loading && filtered.length > 0">
          <tfoot style="background:#f8fafc">
            <tr class="border-t-2 border-gray-200">
              <td colspan="2" class="table-td font-bold text-gray-700">
                Total (<span x-text="filtered.length"></span> suppliers)
              </td>
              <td class="table-td text-right font-bold text-gray-800" x-text="fmtMoney(filtered.reduce((s,r)=>s+(r.current??0),0))"></td>
              <td class="table-td text-right font-bold text-orange-500" x-text="fmtMoney(filtered.reduce((s,r)=>s+(r.days_31_60??0),0))"></td>
              <td class="table-td text-right font-bold text-orange-600" x-text="fmtMoney(filtered.reduce((s,r)=>s+(r.days_61_90??0),0))"></td>
              <td class="table-td text-right font-bold text-red-600" x-text="fmtMoney(filtered.reduce((s,r)=>s+(r.days_90_plus??0),0))"></td>
              <td class="table-td text-right font-bold text-violet-600" x-text="fmtMoney(filtered.reduce((s,r)=>s+(r.total??0),0))"></td>
            </tr>
          </tfoot>
        </template>

      </table>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
function supplierAgingPage() {
  return {
    items: [], loading: true, search: '',
    asOfDate: new Date().toISOString().slice(0,10),
    totals: {},
    get filtered() {
      const q = this.search.toLowerCase().trim();
      if (!q) return this.items;
      return this.items.filter(r => (r.supplier_name ?? '').toLowerCase().includes(q));
    },
    async init() { await this.load(); },
    async load() {
      this.loading = true;
      try {
        const p = new URLSearchParams({ as_of_date: this.asOfDate });
        const data = await apiFetch('/reports/supplier-aging?' + p).then(r => r.json());
        this.items  = Array.isArray(data.data) ? data.data : (Array.isArray(data) ? data : []);
        this.totals = data.totals ?? {};
        if (data.as_of_date) this.asOfDate = data.as_of_date;
      } catch (e) { toast('Failed to load supplier aging report', 'error'); }
      finally { this.loading = false; }
    },
    doExport() {
      const headers = ['Supplier', 'Phone', 'Current (0-30)', '31-60 Days', '61-90 Days', '90+ Days', 'Total'];
      const rows = this.filtered.map(r => [
        r.supplier_name ?? '', r.phone ?? '',
        r.current ?? 0, r.days_31_60 ?? 0, r.days_61_90 ?? 0, r.days_90_plus ?? 0, r.total ?? 0
      ]);
      exportCSV('supplier_aging_' + this.asOfDate, headers, rows);
    },
  };
}
</script>
@endpush
