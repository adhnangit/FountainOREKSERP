<?php $__env->startSection('title', 'Sales Targets Report'); ?>
<?php $__env->startSection('page-title', 'Sales Targets'); ?>
<?php $__env->startSection('page-desc', 'Target vs achievement by representative and period'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="targetsReport()" x-init="init()">

  <!-- Print header -->
  <div class="print-header items-center justify-between mb-4 pb-3 border-b border-gray-200">
    <div>
      <h2 class="text-lg font-bold text-gray-800">Sales Targets Report</h2>
      <p class="text-xs text-gray-400" x-text="'Year: ' + filters.year"></p>
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
        <label class="label text-xs">Year</label>
        <select x-model="filters.year" class="input text-sm py-1.5">
          <template x-for="y in years" :key="y">
            <option :value="y" x-text="y"></option>
          </template>
        </select>
      </div>
      <div>
        <label class="label text-xs">Target Type</label>
        <select x-model="filters.type" class="input text-sm py-1.5">
          <option value="">All Types</option>
          <option value="monthly">Monthly</option>
          <option value="quarterly">Quarterly</option>
          <option value="annual">Annual</option>
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

  <!-- Summary cards skeleton -->
  <template x-if="loading">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
      <template x-for="i in 4" :key="i">
        <div class="card p-4 animate-pulse">
          <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-xl bg-gray-100 flex-shrink-0"></div>
            <div class="flex-1">
              <div class="h-3 bg-gray-100 rounded w-20 mb-2"></div>
              <div class="h-6 bg-gray-100 rounded w-16"></div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </template>

  <!-- Summary cards -->
  <template x-if="!loading">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">

      <div class="card p-4 border-l-4" style="border-left-color:#1B3EB6">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#dbeafe">
            <svg class="w-5 h-5" style="color:#1B3EB6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Total Targets</div>
            <div class="text-2xl font-bold text-gray-900" x-text="targets.length"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#059669">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#d1fae5">
            <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">On Track (≥80%)</div>
            <div class="text-2xl font-bold text-green-600"
                 x-text="targets.filter(t => (t.achievement_percent ?? 0) >= 80).length"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#f59e0b">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#fef3c7">
            <svg class="w-5 h-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Under Alert</div>
            <div class="text-2xl font-bold text-amber-600"
                 x-text="targets.filter(t => t.is_under_alert).length"></div>
          </div>
        </div>
      </div>

      <div class="card p-4 border-l-4" style="border-left-color:#7c3aed">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#ede9fe">
            <svg class="w-5 h-5" style="color:#7c3aed" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/></svg>
          </div>
          <div>
            <div class="text-xs text-gray-500 font-medium mb-1">Avg Achievement</div>
            <div class="text-2xl font-bold" style="color:#7c3aed"
                 x-text="(targets.length ? Math.round(targets.reduce((s,t)=>s+(t.achievement_percent??0),0)/targets.length) : 0) + '%'"></div>
          </div>
        </div>
      </div>

    </div>
  </template>

  <!-- Table -->
  <div class="card overflow-hidden">
    <div class="px-5 py-3 flex items-center justify-between border-b border-gray-100" style="background:#f8fafc">
      <div class="flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
        <span class="text-sm font-semibold text-gray-700">Target Details</span>
      </div>
      <template x-if="!loading">
        <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-indigo-100 text-indigo-700"
              x-text="targets.length + ' target' + (targets.length === 1 ? '' : 's')"></span>
      </template>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-100">
        <thead style="background:#f8fafc">
          <tr>
            <th class="table-hd">Representative</th>
            <th class="table-hd">Branch</th>
            <th class="table-hd">Type</th>
            <th class="table-hd">Period</th>
            <th class="table-hd text-right">Target</th>
            <th class="table-hd text-right">Achieved</th>
            <th class="table-hd">Progress</th>
            <th class="table-hd text-right">%</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-50">

          <!-- Skeleton -->
          <template x-if="loading">
            <template x-for="i in 6" :key="i">
              <tr class="animate-pulse">
                <td class="table-td">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-gray-100"></div>
                    <div class="h-4 bg-gray-100 rounded w-28"></div>
                  </div>
                </td>
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-20"></div></td>
                <td class="table-td"><div class="h-5 bg-gray-100 rounded-full w-16"></div></td>
                <td class="table-td"><div class="h-4 bg-gray-100 rounded w-16"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-24 ml-auto"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-24 ml-auto"></div></td>
                <td class="table-td"><div class="h-2.5 bg-gray-100 rounded-full w-28"></div></td>
                <td class="table-td text-right"><div class="h-4 bg-gray-100 rounded w-10 ml-auto"></div></td>
              </tr>
            </template>
          </template>

          <!-- Data rows -->
          <template x-if="!loading">
            <template x-for="t in targets" :key="t.id">
              <tr class="hover:bg-gray-50 transition-colors">
                <td class="table-td">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                         :style="t.is_under_alert ? 'background:#f59e0b' : 'background:linear-gradient(135deg,#1B3EB6,#0D2272)'"
                         x-text="(t.user?.name || '?').charAt(0).toUpperCase()"></div>
                    <div>
                      <div class="font-medium text-gray-900 text-sm" x-text="t.user?.name ?? '—'"></div>
                      <div x-show="t.is_under_alert" class="text-xs text-amber-500 font-medium">Under alert</div>
                    </div>
                  </div>
                </td>
                <td class="table-td text-gray-500 text-sm" x-text="t.branch?.name ?? '—'"></td>
                <td class="table-td">
                  <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 font-semibold capitalize" x-text="t.type ?? '—'"></span>
                </td>
                <td class="table-td text-gray-600 text-sm font-medium" x-text="periodLabel(t)"></td>
                <td class="table-td text-right font-semibold text-gray-800" x-text="fmtMoney(t.target_value)"></td>
                <td class="table-td text-right font-semibold"
                    :class="(t.achieved_value??0) >= (t.target_value??0) ? 'text-green-600' : 'text-amber-600'"
                    x-text="fmtMoney(t.achieved_value ?? 0)"></td>
                <td class="table-td w-36">
                  <div class="h-2 rounded-full bg-gray-100 overflow-hidden">
                    <div class="h-full rounded-full transition-all"
                         :style="'width:' + Math.min(100, t.achievement_percent ?? 0) + '%;' +
                                 'background:' + ((t.achievement_percent??0)>=100 ? '#059669' : (t.achievement_percent??0)>=80 ? '#f59e0b' : '#dc2626')"></div>
                  </div>
                </td>
                <td class="table-td text-right font-bold text-sm"
                    :class="(t.achievement_percent??0)>=100 ? 'text-green-600' : (t.achievement_percent??0)>=80 ? 'text-amber-500' : 'text-red-500'"
                    x-text="Math.round(t.achievement_percent ?? 0) + '%'"></td>
              </tr>
            </template>
          </template>

          <!-- Empty state -->
          <template x-if="!loading && targets.length === 0">
            <tr>
              <td colspan="8" class="py-14 text-center">
                <div class="inline-flex flex-col items-center gap-3">
                  <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                  </div>
                  <div>
                    <div class="text-sm font-semibold text-gray-700">No targets configured</div>
                    <div class="text-xs text-gray-400 mt-0.5">Set up sales targets to track performance here</div>
                  </div>
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
function targetsReport() {
  const currentYear = new Date().getFullYear();
  return {
    loading: true,
    filters: { year: currentYear, type: '' },
    years: Array.from({length: 5}, (_, i) => currentYear - i),
    targets: [],
    async init() { await this.load(); },
    async load() {
      this.loading = true;
      try {
        const p = new URLSearchParams();
        if (this.filters.year) p.set('year', this.filters.year);
        if (this.filters.type) p.set('type', this.filters.type);
        const data = await apiFetch('/reports/targets?' + p).then(r => r.json());
        this.targets = Array.isArray(data.data) ? data.data : (Array.isArray(data) ? data : []);
      } catch (e) { toast('Failed to load targets', 'error'); }
      finally { this.loading = false; }
    },
    resetFilters() {
      this.filters = { year: new Date().getFullYear(), type: '' };
      this.load();
    },
    periodLabel(t) {
      if (t.type === 'monthly') {
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return (months[(t.period_number ?? 1) - 1] ?? '') + ' ' + t.year;
      }
      if (t.type === 'quarterly') return 'Q' + t.period_number + ' ' + t.year;
      return String(t.year);
    },
    doExport() {
      const headers = ['Representative', 'Branch', 'Type', 'Period', 'Target', 'Achieved', 'Achievement %'];
      const rows = this.targets.map(t => [
        t.user?.name ?? '', t.branch?.name ?? '', t.type,
        this.periodLabel(t), t.target_value, t.achieved_value ?? 0,
        Math.round(t.achievement_percent ?? 0) + '%'
      ]);
      exportCSV('targets_report_' + this.filters.year, headers, rows);
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\reports\targets.blade.php ENDPATH**/ ?>