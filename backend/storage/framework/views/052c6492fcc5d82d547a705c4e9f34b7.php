<?php $__env->startSection('title', 'Stock Movement Report'); ?>
<?php $__env->startSection('page-title', 'Stock Movement Report'); ?>
<?php $__env->startSection('page-desc', 'Product stock ins, outs and adjustments'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="stockMovementReport()" x-init="init()">

  <!-- Print header -->
  <div class="print-header items-center justify-between mb-4 pb-3 border-b border-gray-200">
    <div>
      <h2 class="text-lg font-bold text-gray-800">Stock Movement Report</h2>
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
        <label class="label text-xs">Movement Type</label>
        <select x-model="filters.type" class="input text-sm py-1.5" style="min-width:160px">
          <option value="">All Types</option>
          <option value="purchase_receipt">Purchase Receipt</option>
          <option value="sale">Sale</option>
          <option value="transfer_in">Transfer In</option>
          <option value="transfer_out">Transfer Out</option>
          <option value="adjustment_add">Adjustment Add</option>
          <option value="adjustment_subtract">Adjustment Subtract</option>
          <option value="opening_stock">Opening Stock</option>
        </select>
      </div>
      <div class="flex-1 min-w-[180px]">
        <label class="label text-xs">Search Product</label>
        <div class="relative">
          <input x-model="search" type="text" placeholder="Product name or SKU…" class="input text-sm py-1.5 pl-8 w-full" />
          <svg class="w-4 h-4 absolute left-2 top-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        </div>
      </div>
      <div class="flex items-end gap-2">
        <button @click="page=1; load()" class="btn-primary text-sm py-1.5 px-5">
          <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          Run Report
        </button>
        <button @click="resetFilters()" class="btn-secondary text-sm py-1.5 px-3">Reset</button>
      </div>
    </div>
  </div>

  <!-- Summary cards skeleton -->
  <template x-if="loading && page === 1">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
      <template x-for="i in 4" :key="i">
        <div class="card p-4 animate-pulse">
          <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-gray-100 flex-shrink-0"></div>
            <div class="flex-1">
              <div class="h-3 bg-gray-100 rounded w-20 mb-2"></div>
              <div class="h-6 bg-gray-100 rounded w-20"></div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </template>

  <!-- Summary cards -->
  <template x-if="!loading || page > 1">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">

      <div class="card p-4 border-l-4" style="border-left-color:#1B3EB6">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#dbeafe">
            <svg class="w-5 h-5" style="color:#1B3EB6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Total Movements</div>
            <div class="text-xl font-bold text-gray-900" x-text="(meta.total ?? items.length) + ' records'"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#059669">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#d1fae5">
            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M7 16V4m0 0L3 8m4-4l4 4"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Stock In</div>
            <div class="text-xl font-bold text-green-600"
                 x-text="parseFloat(items.filter(m=>(m.quantity??0)>0).reduce((s,m)=>s+(parseFloat(m.quantity)||0),0)).toFixed(2)"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#dc2626">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fee2e2">
            <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 8V20m0 0l4-4m-4 4l-4-4"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Stock Out</div>
            <div class="text-xl font-bold text-red-600"
                 x-text="Math.abs(parseFloat(items.filter(m=>(m.quantity??0)<0).reduce((s,m)=>s+(parseFloat(m.quantity)||0),0))).toFixed(2)"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#7c3aed">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#ede9fe">
            <svg class="w-5 h-5" style="color:#7c3aed" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 6V4m0 0a2 2 0 00-2 2v1M12 4a2 2 0 012 2v1m-4 3H6m12 0h-2m-4 6v2m0 0a2 2 0 002-2v-1m-2 3a2 2 0 01-2-2v-1"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Net Change</div>
            <div class="text-xl font-bold" style="color:#7c3aed"
                 x-text="parseFloat(items.reduce((s,m)=>s+(parseFloat(m.quantity)||0),0)).toFixed(2)"></div>
          </div>
        </div>
      </div>

    </div>
  </template>

  <!-- Table -->
  <div class="card overflow-hidden">
    <div class="px-5 py-3 flex items-center justify-between border-b border-gray-100" style="background:#f8fafc">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        <span class="text-sm font-semibold text-gray-700">Movement Log</span>
      </div>
      <template x-if="!loading">
        <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-indigo-100 text-indigo-700"
              x-text="filtered.length + ' of ' + (meta.total ?? items.length) + ' records'"></span>
      </template>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-100">
        <thead style="background:#f8fafc">
          <tr>
            <th class="table-hd">Date</th>
            <th class="table-hd">Product</th>
            <th class="table-hd">SKU</th>
            <th class="table-hd">Type</th>
            <th class="table-hd text-right">Qty</th>
            <th class="table-hd text-right">Unit Cost</th>
            <th class="table-hd text-right">Balance</th>
            <th class="table-hd">Batch</th>
            <th class="table-hd">Created By</th>
            <th class="table-hd">Notes</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-50">

          <!-- Skeleton -->
          <template x-if="loading">
            <template x-for="i in 8" :key="i">
              <tr class="animate-pulse">
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-20"></div></td>
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-32"></div></td>
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-16"></div></td>
                <td class="table-td"><div class="h-5 bg-gray-100 rounded-full w-24"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-12 ml-auto"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-20 ml-auto"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-16 ml-auto"></div></td>
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-16"></div></td>
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-20"></div></td>
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-24"></div></td>
              </tr>
            </template>
          </template>

          <!-- Data rows -->
          <template x-if="!loading">
            <template x-for="m in filtered" :key="m.id">
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="table-td text-sm text-gray-600" x-text="fmtDate(m.movement_date)"></td>
                <td class="table-td">
                  <div class="font-medium text-gray-800 text-sm" x-text="m.product?.name ?? '—'"></div>
                </td>
                <td class="table-td font-mono text-xs text-gray-400" x-text="m.product?.code ?? '—'"></td>
                <td class="table-td">
                  <span class="text-xs px-2 py-0.5 rounded-full font-medium"
                    :class="typeStyle(m.type)"
                    x-text="typeLabel(m.type)"></span>
                </td>
                <td class="table-td text-right font-bold"
                    :class="(m.quantity??0) > 0 ? 'text-green-600' : 'text-red-600'"
                    x-text="((m.quantity??0) > 0 ? '+' : '') + parseFloat(m.quantity ?? 0).toFixed(2)"></td>
                <td class="table-td text-right text-gray-600" x-text="fmtMoney(m.unit_cost)"></td>
                <td class="table-td text-right font-mono text-sm text-gray-700"
                    x-text="parseFloat(m.balance_quantity ?? 0).toFixed(2)"></td>
                <td class="table-td text-xs text-gray-400" x-text="m.batch_number ?? '—'"></td>
                <td class="table-td text-sm text-gray-500" x-text="m.created_by?.name ?? '—'"></td>
                <td class="table-td text-xs text-gray-400 max-w-xs truncate" x-text="m.notes ?? '—'"></td>
              </tr>
            </template>
          </template>

          <!-- Empty state -->
          <template x-if="!loading && filtered.length === 0">
            <tr>
              <td colspan="10" class="py-14 text-center">
                <div class="inline-flex flex-col items-center gap-3">
                  <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                  </div>
                  <div>
                    <div class="text-sm font-semibold text-gray-700">No movements found</div>
                    <div class="text-xs text-gray-400 mt-0.5">Try adjusting your date range, type, or search</div>
                  </div>
                </div>
              </td>
            </tr>
          </template>

        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="px-5 py-3 border-t border-gray-100 flex items-center justify-between no-print" x-show="meta.last_page > 1">
      <span class="text-xs text-gray-500"
            x-text="'Page ' + meta.current_page + ' of ' + meta.last_page + ' (' + meta.total + ' total records)'"></span>
      <div class="flex items-center gap-1.5">
        <button @click="page--; load()" :disabled="page <= 1"
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-600 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 19l-7-7 7-7"/></svg>
          Prev
        </button>
        <span class="text-xs text-gray-400 px-1" x-text="page + ' / ' + meta.last_page"></span>
        <button @click="page++; load()" :disabled="page >= meta.last_page"
                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 text-gray-600 bg-white hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
          Next
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5l7 7-7 7"/></svg>
        </button>
      </div>
    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function stockMovementReport() {
  const today = new Date().toISOString().slice(0,10);
  const first = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0,10);
  return {
    loading: true, page: 1,
    filters: { from_date: first, to_date: today, type: '' },
    search: '',
    items: [], meta: {},
    get filtered() {
      const q = this.search.toLowerCase().trim();
      if (!q) return this.items;
      return this.items.filter(m =>
        (m.product?.name ?? '').toLowerCase().includes(q) ||
        (m.product?.code ?? '').toLowerCase().includes(q)
      );
    },
    async init() { await this.load(); },
    async load() {
      this.loading = true;
      try {
        const p = new URLSearchParams({ page: this.page, per_page: 50 });
        if (this.filters.from_date) p.set('from_date', this.filters.from_date);
        if (this.filters.to_date)   p.set('to_date',   this.filters.to_date);
        if (this.filters.type)      p.set('type',      this.filters.type);
        const d = await apiFetch('/reports/stock-movements?' + p).then(r => r.json());
        this.items = Array.isArray(d.data) ? d.data : [];
        this.meta  = { current_page: d.current_page ?? 1, last_page: d.last_page ?? 1, total: d.total ?? this.items.length };
      } catch (e) { toast('Failed to load stock movements', 'error'); }
      finally { this.loading = false; }
    },
    resetFilters() {
      const now = new Date();
      this.filters = {
        from_date: new Date(now.getFullYear(), now.getMonth(), 1).toISOString().slice(0,10),
        to_date: now.toISOString().slice(0,10),
        type: ''
      };
      this.search = '';
      this.page = 1;
      this.load();
    },
    typeLabel(type) {
      const m = {
        purchase_receipt: 'Purchase Receipt',
        sale: 'Sale',
        transfer_in: 'Transfer In',
        transfer_out: 'Transfer Out',
        adjustment_add: 'Adj Add',
        adjustment_subtract: 'Adj Sub',
        opening_stock: 'Opening Stock',
      };
      return m[type] ?? (type ?? '—');
    },
    typeStyle(type) {
      const inTypes = ['purchase_receipt','transfer_in','adjustment_add','opening_stock'];
      const outTypes = ['sale','transfer_out','adjustment_subtract'];
      if (inTypes.includes(type))  return 'bg-green-100 text-green-700';
      if (outTypes.includes(type)) return 'bg-red-100 text-red-600';
      return 'bg-gray-100 text-gray-500';
    },
    doExport() {
      const headers = ['Date', 'Product', 'SKU', 'Type', 'Quantity', 'Unit Cost', 'Balance Qty', 'Batch', 'Created By', 'Notes'];
      const rows = this.filtered.map(m => [
        m.movement_date, m.product?.name??'', m.product?.code??'',
        m.type, m.quantity, m.unit_cost, m.balance_quantity,
        m.batch_number??'', m.created_by?.name??'', m.notes??''
      ]);
      exportCSV('stock_movement_' + this.filters.from_date + '_' + this.filters.to_date, headers, rows);
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\reports\stock-movement.blade.php ENDPATH**/ ?>