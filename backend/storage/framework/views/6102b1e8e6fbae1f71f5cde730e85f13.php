<?php $__env->startSection('title', 'Expense Report'); ?>
<?php $__env->startSection('page-title', 'Expense Report'); ?>
<?php $__env->startSection('page-desc', 'Spending analysis by category and period'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="expenseReport()" x-init="init()">

  <div class="print-header items-center justify-between mb-4 pb-3 border-b border-gray-200">
    <div><h2 class="text-lg font-bold text-gray-800">Expense Report</h2>
      <p class="text-xs text-gray-400" x-text="'Period: ' + fmtDate(filters.from_date) + ' – ' + fmtDate(filters.to_date)"></p></div>
  </div>

  
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
        <label class="label text-xs">From</label>
        <input type="date" x-model="filters.from_date" class="input text-sm py-1.5" />
      </div>
      <div>
        <label class="label text-xs">To</label>
        <input type="date" x-model="filters.to_date" class="input text-sm py-1.5" />
      </div>
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
        <label class="label text-xs">Status</label>
        <select x-model="filters.status" class="input text-sm py-1.5">
          <option value="approved">Approved Only</option>
          <option value="all">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
      <div class="flex items-end gap-2">
        <button @click="load()" class="btn-primary text-sm py-1.5 px-4 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          Run Report
        </button>
        <button @click="resetFilters()" class="btn-secondary text-sm py-1.5 px-3">Reset</button>
      </div>
    </div>
  </div>

  
  <div x-show="loading" class="space-y-4">
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
      <template x-for="i in [1,2,3]" :key="i">
        <div class="card p-4 animate-pulse"><div class="h-3 bg-gray-200 rounded w-2/3 mb-2"></div><div class="h-6 bg-gray-200 rounded w-1/2"></div></div>
      </template>
    </div>
    <div class="card p-0 overflow-hidden animate-pulse">
      <div class="h-10 bg-gray-100 border-b border-gray-200"></div>
      <template x-for="i in [1,2,3,4,5]" :key="i">
        <div class="flex gap-4 px-4 py-3 border-b border-gray-100">
          <div class="h-3 bg-gray-200 rounded w-20"></div>
          <div class="h-3 bg-gray-200 rounded flex-1"></div>
          <div class="h-3 bg-gray-200 rounded w-16"></div>
        </div>
      </template>
    </div>
  </div>

  <div x-show="!loading">
    
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-5">
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#dc2626">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fff1f2">
          <svg style="width:18px;height:18px;color:#dc2626" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Total Expenses</div>
          <div class="text-xl font-bold text-gray-900 dark:text-white leading-tight" x-text="fmtMoney(summary.total_amount ?? 0)"></div>
        </div>
      </div>
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#7c3aed">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#f5f3ff">
          <svg style="width:18px;height:18px;color:#7c3aed" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Count</div>
          <div class="text-xl font-bold text-gray-900 dark:text-white leading-tight" x-text="(summary.count ?? 0) + ' expenses'"></div>
        </div>
      </div>
      <div class="card p-4 flex items-start gap-3 border-l-4" style="border-color:#0891b2">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#ecfeff">
          <svg style="width:18px;height:18px;color:#0891b2" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <div class="min-w-0">
          <div class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-0.5">Average</div>
          <div class="text-xl font-bold text-cyan-600 leading-tight" x-text="fmtMoney(summary.avg_amount ?? 0)"></div>
        </div>
      </div>
    </div>

    
    <div class="flex gap-1 mb-4 no-print bg-gray-100 dark:bg-gray-800 rounded-xl p-1 w-fit">
      <button @click="activeTab='list'" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all" :class="activeTab==='list'?'bg-white shadow text-indigo-600 font-semibold':'text-gray-500 hover:text-gray-700'">Expense List</button>
      <button @click="activeTab='category'" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all" :class="activeTab==='category'?'bg-white shadow text-indigo-600 font-semibold':'text-gray-500 hover:text-gray-700'">By Category</button>
      <button @click="activeTab='trend'" class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all" :class="activeTab==='trend'?'bg-white shadow text-indigo-600 font-semibold':'text-gray-500 hover:text-gray-700'">Monthly Trend</button>
    </div>

    
    <div x-show="activeTab === 'list'" class="card p-0 overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800">
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Expense List</span>
        <span class="text-xs px-2 py-0.5 rounded-full bg-red-50 text-red-600 font-semibold" x-text="expenses.length + ' records'"></span>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50"><tr>
            <th class="table-hd">Date</th>
            <th class="table-hd">Ref #</th>
            <th class="table-hd">Category</th>
            <th class="table-hd">Description</th>
            <th class="table-hd">Created By</th>
            <th class="table-hd">Method</th>
            <th class="table-hd text-right">Amount</th>
            <th class="table-hd">Status</th>
          </tr></thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <template x-for="e in expenses" :key="e.id">
              <tr class="hover:bg-gray-50">
                <td class="table-td text-sm text-gray-600" x-text="fmtDate(e.expense_date)"></td>
                <td class="table-td font-mono text-xs text-gray-400" x-text="e.expense_number ?? '—'"></td>
                <td class="table-td">
                  <span class="text-xs px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 font-medium" x-text="e.category?.name ?? '—'"></span>
                </td>
                <td class="table-td text-gray-700 max-w-xs truncate text-sm" x-text="e.description ?? '—'"></td>
                <td class="table-td text-gray-500 text-sm" x-text="e.created_by?.name ?? '—'"></td>
                <td class="table-td text-xs text-gray-400 capitalize" x-text="(e.payment_method ?? '—').replace('_',' ')"></td>
                <td class="table-td text-right font-semibold text-red-600" x-text="fmtMoney(e.amount)"></td>
                <td class="table-td">
                  <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                    :class="{'bg-green-100 text-green-700':e.status==='approved','bg-amber-100 text-amber-700':e.status==='pending','bg-red-100 text-red-600':e.status==='rejected'}"
                    x-text="e.status"></span>
                </td>
              </tr>
            </template>
            <template x-if="expenses.length===0">
              <tr><td colspan="8">
                <div class="flex flex-col items-center justify-center py-12 text-center">
                  <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                  </div>
                  <p class="text-sm font-medium text-gray-500">No expenses found</p>
                  <p class="text-xs text-gray-400 mt-0.5">Try adjusting the date range or filters</p>
                </div>
              </td></tr>
            </template>
          </tbody>
          <tfoot class="bg-gray-50 border-t-2" x-show="expenses.length>0">
            <tr>
              <td colspan="6" class="table-td font-bold text-gray-700">Total (<span x-text="expenses.length"></span>)</td>
              <td class="table-td text-right font-bold text-red-600" x-text="fmtMoney(expenses.reduce((s,e)=>s+(e.amount??0),0))"></td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    
    <div x-show="activeTab === 'category'" class="card p-0 overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800">
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Expenses by Category</span>
        <span class="text-xs px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 font-semibold" x-text="byCategory.length + ' categories'"></span>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50"><tr>
            <th class="table-hd w-8">#</th>
            <th class="table-hd">Category</th>
            <th class="table-hd text-right">Count</th>
            <th class="table-hd text-right">Total Amount</th>
            <th class="table-hd">Share</th>
          </tr></thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <template x-for="(c, i) in byCategory" :key="i">
              <tr class="hover:bg-gray-50">
                <td class="table-td">
                  <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold text-white"
                        :style="i<3?'background:#dc2626':'background:#94a3b8'"
                        x-text="i+1"></span>
                </td>
                <td class="table-td font-medium text-gray-800" x-text="c.category?.name ?? 'Uncategorized'"></td>
                <td class="table-td text-right text-gray-600" x-text="c.count"></td>
                <td class="table-td text-right font-semibold text-red-600" x-text="fmtMoney(c.total)"></td>
                <td class="table-td w-44">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 h-2 rounded-full bg-gray-200">
                      <div class="h-full rounded-full bg-red-400" :style="'width:' + Math.min(100, Math.round((c.total/(byCategory[0]?.total||1))*100)) + '%'"></div>
                    </div>
                    <span class="text-xs font-semibold text-gray-500 w-8 text-right" x-text="Math.round((c.total/(summary.total_amount||1))*100) + '%'"></span>
                  </div>
                </td>
              </tr>
            </template>
            <template x-if="byCategory.length===0">
              <tr><td colspan="5" class="py-12 text-center text-gray-400 text-sm">No category data.</td></tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    
    <div x-show="activeTab === 'trend'" class="card p-0 overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between bg-gray-50 dark:bg-gray-800">
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Monthly Trend</span>
        <span class="text-xs px-2 py-0.5 rounded-full bg-red-50 text-red-600 font-semibold" x-text="trend.length + ' months'"></span>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50"><tr>
            <th class="table-hd">Month</th>
            <th class="table-hd text-right">Count</th>
            <th class="table-hd text-right">Total Amount</th>
            <th class="table-hd">Trend</th>
          </tr></thead>
          <tbody class="bg-white divide-y divide-gray-100">
            <template x-for="(t, i) in trend" :key="t.month">
              <tr class="hover:bg-gray-50">
                <td class="table-td font-medium text-gray-800" x-text="t.month"></td>
                <td class="table-td text-right text-gray-600" x-text="t.count"></td>
                <td class="table-td text-right font-semibold text-red-600" x-text="fmtMoney(t.total)"></td>
                <td class="table-td w-52">
                  <div class="flex items-center gap-2">
                    <div class="flex-1 h-2.5 rounded-full bg-gray-200 overflow-hidden">
                      <div class="h-full rounded-full bg-red-400 transition-all"
                           :style="'width:' + Math.min(100, Math.round((t.total/(trend.reduce((s,x)=>Math.max(s,x.total),0)||1))*100)) + '%'"></div>
                    </div>
                    <span class="text-xs text-gray-400 w-8 text-right" x-text="Math.round((t.total/(trend.reduce((s,x)=>Math.max(s,x.total),0)||1))*100)+'%'"></span>
                  </div>
                </td>
              </tr>
            </template>
            <template x-if="trend.length===0">
              <tr><td colspan="4" class="py-12 text-center text-gray-400 text-sm">No trend data available.</td></tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function expenseReport() {
  const today = new Date().toISOString().slice(0,10);
  const first = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0,10);
  return {
    loading: true, categories: [],
    filters: { from_date: first, to_date: today, category_id: '', status: 'approved' },
    summary: {}, expenses: [], byCategory: [], trend: [],
    activeTab: 'list',
    async init() {
      try {
        const cr = await apiFetch('/expenses/categories').then(r => r.json());
        this.categories = cr.data ?? cr ?? [];
      } catch {}
      await this.load();
    },
    async load() {
      this.loading = true;
      try {
        const p = new URLSearchParams();
        if (this.filters.from_date)   p.set('from_date',   this.filters.from_date);
        if (this.filters.to_date)     p.set('to_date',     this.filters.to_date);
        if (this.filters.category_id) p.set('category_id', this.filters.category_id);
        if (this.filters.status)      p.set('status',      this.filters.status);
        const d = await apiFetch('/reports/expenses?' + p).then(r => r.json());
        this.summary    = d.summary    ?? {};
        this.expenses   = d.expenses   ?? [];
        this.byCategory = d.byCategory ?? [];
        this.trend      = d.trend      ?? [];
      } catch (e) { toast('Failed to load expense report', 'error'); }
      finally { this.loading = false; }
    },
    resetFilters() {
      this.filters = { from_date: new Date(new Date().getFullYear(),new Date().getMonth(),1).toISOString().slice(0,10), to_date: new Date().toISOString().slice(0,10), category_id: '', status: 'approved' };
      this.load();
    },
    doExport() {
      const headers = ['Date', 'Ref #', 'Category', 'Description', 'Created By', 'Method', 'Amount', 'Status'];
      const rows = this.expenses.map(e => [e.expense_date, e.expense_number??'', e.category?.name??'', e.description??'', e.created_by?.name??'', e.payment_method??'', e.amount, e.status]);
      exportCSV('expense_report', headers, rows);
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\reports\expenses.blade.php ENDPATH**/ ?>