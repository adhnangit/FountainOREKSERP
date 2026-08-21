@extends('layouts.app')
@section('title', 'Inventory Report')
@section('page-title', 'Inventory Report')
@section('page-desc', 'Stock levels and valuation across products')

@section('content')
<div x-data="inventoryReport()" x-init="init()">

  <div class="print-header items-center justify-between mb-4 pb-3 border-b border-gray-200">
    <div><h2 class="text-lg font-bold text-gray-800">Inventory Report</h2>
      <p class="text-xs text-gray-400" x-text="'Generated: ' + fmtDate(new Date())"></p></div>
  </div>

  {{-- Filter Panel --}}
  <div class="card mb-5 no-print overflow-hidden">
    <div class="px-4 py-2.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between" style="background:#f8fafc">
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
        <label class="label text-xs">Category</label>
        <select x-model="filters.category_id" class="input text-sm py-1.5">
          <option value="">All Categories</option>
          <template x-for="c in categories" :key="c.id">
            <option :value="c.id" x-text="c.name"></option>
          </template>
        </select>
      </div>
      <div>
        <label class="label text-xs">Stock Status</label>
        <select x-model="filters.stock_status" class="input text-sm py-1.5">
          <option value="">All</option>
          <option value="in_stock">In Stock</option>
          <option value="low">Low Stock</option>
          <option value="out">Out of Stock</option>
        </select>
      </div>
      <div>
        <label class="label text-xs">Search</label>
        <div class="relative">
          <input x-model="search" type="text" placeholder="Search product…" class="input text-sm py-1.5 pl-8 w-48" />
          <svg class="w-4 h-4 absolute left-2 top-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        </div>
      </div>
      <div class="flex items-end gap-2">
        <button @click="load()" class="btn-primary text-sm py-1.5 px-4 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
          Run Report
        </button>
        <button @click="resetFilters()" class="btn-secondary text-sm py-1.5 px-3">Reset</button>
      </div>
    </div>
  </div>

  {{-- Loading skeleton --}}
  <div x-show="loading" class="space-y-4">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <template x-for="i in [1,2,3,4]" :key="i">
        <div class="card p-4 animate-pulse"><div class="h-3 bg-gray-200 rounded w-2/3 mb-2"></div><div class="h-6 bg-gray-200 rounded w-1/2"></div></div>
      </template>
    </div>
    <div class="card p-0 overflow-hidden animate-pulse">
      <div class="h-10 bg-gray-100 border-b border-gray-200"></div>
      <template x-for="i in [1,2,3,4,5,6,7,8]" :key="i">
        <div class="flex gap-4 px-4 py-3 border-b border-gray-100">
          <div class="h-3 bg-gray-200 rounded flex-1"></div>
          <div class="h-3 bg-gray-200 rounded w-16"></div>
          <div class="h-3 bg-gray-200 rounded w-20"></div>
          <div class="h-3 bg-gray-200 rounded w-16"></div>
        </div>
      </template>
    </div>
  </div>

  <div x-show="!loading">
    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#1B3EB6">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#eef2ff">
          <svg style="width:18px;height:18px;color:#1B3EB6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Total Products</div>
          <div class="text-xl font-bold text-gray-900 dark:text-white leading-tight" x-text="summary.total_products ?? 0"></div>
          <div class="text-xs text-gray-400 mt-0.5" x-text="'showing ' + filtered.length"></div>
        </div>
      </div>
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#059669">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#f0fdf4">
          <svg style="width:18px;height:18px;color:#059669" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Stock Value</div>
          <div class="text-lg font-bold text-gray-900 dark:text-white leading-tight" x-text="fmtMoney(filteredValue)"></div>
        </div>
      </div>
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#dc2626">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fff1f2">
          <svg style="width:18px;height:18px;color:#dc2626" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Out of Stock</div>
          <div class="text-xl font-bold text-red-600 leading-tight" x-text="summary.out_of_stock ?? 0"></div>
          <div class="text-xs text-red-400 mt-0.5">Needs restocking</div>
        </div>
      </div>
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#f59e0b">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fffbeb">
          <svg style="width:18px;height:18px;color:#d97706" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Low Stock</div>
          <div class="text-xl font-bold text-amber-600 leading-tight" x-text="summary.low_stock ?? 0"></div>
          <div class="text-xs text-amber-500 mt-0.5">Below reorder level</div>
        </div>
      </div>
    </div>

    {{-- Table --}}
    <div class="card p-0 overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800">
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Product Stock</span>
        <span class="text-xs px-2 py-0.5 rounded-full bg-green-50 text-green-700 font-semibold" x-text="filtered.length + ' products'"></span>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="table-hd">Product</th>
              <th class="table-hd">SKU</th>
              <th class="table-hd">Category</th>
              <th class="table-hd text-right">Stock Qty</th>
              <th class="table-hd text-right">Cost Price</th>
              <th class="table-hd text-right">Selling Price</th>
              <th class="table-hd text-right">Stock Value</th>
              <th class="table-hd text-right">Reorder Lvl</th>
              <th class="table-hd">Status</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-800">
            <template x-for="p in filtered" :key="p.id">
              <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                <td class="table-td font-medium text-gray-800 dark:text-gray-100" x-text="p.name"></td>
                <td class="table-td font-mono text-xs text-gray-400" x-text="p.code || '—'"></td>
                <td class="table-td text-gray-500 text-sm" x-text="p.category || '—'"></td>
                <td class="table-td text-right font-bold text-base"
                    :class="(p.stock_qty??0)<=0?'text-red-600':(p.stock_qty<=p.reorder_level&&p.reorder_level>0?'text-amber-600':'text-gray-800 dark:text-gray-100')"
                    x-text="parseFloat(p.stock_qty??0).toFixed(2)"></td>
                <td class="table-td text-right text-gray-600" x-text="fmtMoney(p.cost_price)"></td>
                <td class="table-td text-right text-gray-600" x-text="fmtMoney(p.selling_price)"></td>
                <td class="table-td text-right font-semibold text-gray-800 dark:text-gray-100" x-text="fmtMoney(p.stock_value)"></td>
                <td class="table-td text-right text-gray-400 text-sm" x-text="p.reorder_level || '—'"></td>
                <td class="table-td">
                  <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                    :class="(p.stock_qty??0)<=0?'bg-red-100 text-red-600':(p.stock_qty<=p.reorder_level&&p.reorder_level>0?'bg-amber-100 text-amber-600':'bg-green-100 text-green-700')"
                    x-text="(p.stock_qty??0)<=0?'Out of Stock':(p.stock_qty<=p.reorder_level&&p.reorder_level>0?'Low Stock':'In Stock')"></span>
                </td>
              </tr>
            </template>
            <template x-if="filtered.length === 0">
              <tr>
                <td colspan="9">
                  <div class="flex flex-col items-center justify-center py-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                      <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </div>
                    <p class="text-sm font-medium text-gray-500">No products match your filters</p>
                    <p class="text-xs text-gray-400 mt-0.5">Try adjusting the category or stock status filter</p>
                  </div>
                </td>
              </tr>
            </template>
          </tbody>
          <tfoot class="bg-gray-50 border-t-2 border-gray-200" x-show="filtered.length > 0">
            <tr>
              <td colspan="6" class="table-td font-bold text-gray-700">Total (<span x-text="filtered.length"></span> products)</td>
              <td class="table-td text-right font-bold text-gray-800" x-text="fmtMoney(filteredValue)"></td>
              <td colspan="2"></td>
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
function inventoryReport() {
  return {
    loading: true, categories: [], items: [], summary: {}, search: '',
    filters: { category_id: '', stock_status: '' },
    get filtered() {
      const q = this.search.toLowerCase();
      return this.items.filter(p =>
        (!q || (p.name??'').toLowerCase().includes(q) || (p.code??'').toLowerCase().includes(q))
      );
    },
    get filteredValue() {
      return this.filtered.reduce((s, p) => s + (parseFloat(p.stock_value) || 0), 0);
    },
    async init() {
      try {
        const cr = await apiFetch('/products/categories').then(r => r.json());
        this.categories = cr.data ?? cr ?? [];
      } catch {}
      await this.load();
    },
    async load() {
      this.loading = true;
      try {
        const p = new URLSearchParams();
        if (this.filters.category_id)  p.set('category_id',  this.filters.category_id);
        if (this.filters.stock_status) p.set('stock_status', this.filters.stock_status);
        const d = await apiFetch('/reports/inventory?' + p).then(r => r.json());
        this.items   = d.data    ?? d.stock ?? [];
        this.summary = d.summary ?? {};
      } catch (e) { toast('Failed to load inventory report', 'error'); }
      finally { this.loading = false; }
    },
    resetFilters() { this.filters = { category_id: '', stock_status: '' }; this.search = ''; this.load(); },
    doExport() {
      const headers = ['Product', 'SKU', 'Category', 'Stock Qty', 'Cost Price', 'Selling Price', 'Stock Value', 'Reorder Level', 'Status'];
      const rows = this.filtered.map(p => [
        p.name, p.code ?? '', p.category ?? '',
        p.stock_qty ?? 0, p.cost_price ?? 0, p.selling_price ?? 0, p.stock_value ?? 0, p.reorder_level ?? 0,
        (p.stock_qty??0)<=0 ? 'Out of Stock' : ((p.stock_qty<=p.reorder_level&&p.reorder_level>0) ? 'Low Stock' : 'In Stock')
      ]);
      exportCSV('inventory_report', headers, rows);
    },
  };
}
</script>
@endpush
