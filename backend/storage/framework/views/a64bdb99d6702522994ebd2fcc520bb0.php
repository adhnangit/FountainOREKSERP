
<?php $__env->startSection('title', 'Proforma Invoices'); ?>
<?php $__env->startSection('page-title', 'Proforma Invoices'); ?>
<?php $__env->startSection('page-desc', 'Draft invoices raised by sales reps — pending admin approval'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="proformaIndex()" x-init="init()">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
    <div class="flex items-center gap-2 flex-wrap">
      <!-- Status filter -->
      <template x-for="s in statuses" :key="s.key">
        <button @click="filter = s.key; load()"
                class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all"
                :class="filter === s.key
                  ? 'bg-primary-600 text-white border-primary-600'
                  : 'bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:border-primary-300'">
          <span x-text="s.label"></span>
          <span x-show="s.count > 0" x-text="'('+s.count+')'" class="ml-1 opacity-75"></span>
        </button>
      </template>
    </div>
    <a href="<?php echo e(url('/proforma-invoices/create')); ?>" class="btn-primary whitespace-nowrap">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
      New Proforma
    </a>
  </div>

  <!-- Info banner -->
  <div class="mb-5 flex items-start gap-3 px-4 py-3 rounded-xl border"
       style="background:#fffbeb;border-color:#fde68a">
    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" style="color:#d97706" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <p class="text-xs" style="color:#92400e">
      <strong>Proforma invoices</strong> are draft sales documents. They do <strong>not</strong> affect stock levels or accounts until an admin converts them to a confirmed invoice.
    </p>
  </div>

  <!-- Table -->
  <div class="card overflow-hidden">
    <table class="w-full">
      <thead class="border-b border-gray-100 dark:border-gray-700">
        <tr>
          <th class="table-hd">Proforma #</th>
          <th class="table-hd">Customer</th>
          <th class="table-hd">Branch</th>
          <th class="table-hd">Date</th>
          <th class="table-hd">Amount</th>
          <th class="table-hd">Status</th>
          <th class="table-hd">Created by</th>
          <th class="table-hd w-28">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
        <template x-if="loading">
          <tr><td colspan="8" class="table-td text-center py-12 text-gray-400">Loading...</td></tr>
        </template>
        <template x-for="p in rows" :key="p.id">
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
            <td class="table-td">
              <a :href="BASE + '/proforma-invoices/' + p.id"
                 class="font-semibold hover:underline"
                 style="color:#1B3EB6"
                 x-text="p.proforma_number || ('#PI-' + String(p.id).padStart(4,'0'))"></a>
            </td>
            <td class="table-td">
              <div class="font-medium text-gray-800 dark:text-gray-100" x-text="p.customer?.name || '—'"></div>
              <div class="text-xs text-gray-400" x-text="p.customer?.phone || ''"></div>
            </td>
            <td class="table-td">
              <span class="badge badge-gray" x-text="p.branch?.name || '—'"></span>
            </td>
            <td class="table-td text-gray-500 dark:text-gray-400" x-text="fmtDate(p.proforma_date || p.created_at)"></td>
            <td class="table-td font-semibold" x-text="fmtMoney(p.total || p.total_amount || 0)"></td>
            <td class="table-td">
              <span class="badge"
                :class="{
                  'badge-warning': p.status === 'draft',
                  'badge-primary': p.status === 'sent',
                  'badge-success': p.status === 'converted',
                  'badge-danger':  p.status === 'cancelled',
                }" x-text="p.status"></span>
            </td>
            <td class="table-td text-gray-500 dark:text-gray-400" x-text="p.created_by?.name || '—'"></td>
            <td class="table-td">
              <div class="flex items-center gap-1.5">
                <a :href="BASE + '/proforma-invoices/' + p.id"
                   class="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 hover:bg-primary-50 transition-colors" title="View">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
                <button x-show="p.status === 'draft' || p.status === 'sent'"
                        @click="convert(p)"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-success-600 hover:bg-success-50 transition-colors" title="Convert to Invoice">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </button>
                <button x-show="p.status !== 'converted'"
                        @click="deleteProforma(p)"
                        class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Delete">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
              </div>
            </td>
          </tr>
        </template>
        <template x-if="!loading && rows.length === 0">
          <tr>
            <td colspan="8" class="py-16 text-center">
              <div class="text-gray-300 dark:text-gray-600 text-4xl mb-3">📋</div>
              <div class="text-sm font-medium text-gray-400">No proforma invoices found</div>
              <div class="text-xs text-gray-300 dark:text-gray-600 mt-1">Sales reps can create proforma invoices that won't affect accounts or stock</div>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>

</div>

<?php $__env->startPush('scripts'); ?>
<script>
function proformaIndex() {
  return {
    loading: true,
    rows: [],
    filter: 'all',
    statuses: [
      { key: 'all',       label: 'All',       count: 0 },
      { key: 'draft',     label: 'Draft',     count: 0 },
      { key: 'sent',      label: 'Sent',      count: 0 },
      { key: 'converted', label: 'Converted', count: 0 },
      { key: 'cancelled', label: 'Cancelled', count: 0 },
    ],
    async init() { await this.load(); },
    async load() {
      this.loading = true;
      try {
        const params = this.filter !== 'all' ? `?status=${this.filter}` : '';
        const r = await apiFetch('/proforma-invoices' + params);
        const d = await r.json();
        this.rows = d.data || d || [];
        /* update counts */
        const all = d.data || d || [];
        this.statuses.forEach(s => {
          s.count = s.key === 'all' ? all.length : all.filter(x => x.status === s.key).length;
        });
      } catch(e) { this.rows = []; }
      this.loading = false;
    },
    async convert(p) {
      if (!confirm(`Convert Proforma ${p.proforma_number || '#PI-' + p.id} to a confirmed invoice?\n\nThis will update stock and accounts.`)) return;
      try {
        const r = await apiFetch(`/proforma-invoices/${p.id}/convert`, { method: 'POST' });
        if (r.ok) { toast('Converted to invoice successfully', 'success'); this.load(); }
        else { const d = await r.json(); toast(d.message || 'Conversion failed', 'error'); }
      } catch { toast('Conversion failed', 'error'); }
    },
    async deleteProforma(p) {
      const label = p.proforma_number || ('#PI-' + String(p.id).padStart(4, '0'));
      if (!confirm(`Delete ${label}? This cannot be undone.`)) return;
      try {
        const r = await apiFetch('/invoices/' + p.id, { method: 'DELETE' });
        if (r.ok) { toast('Proforma deleted', 'success'); this.load(); }
        else { const d = await r.json(); toast(d.message || 'Failed to delete', 'error'); }
      } catch { toast('Delete failed', 'error'); }
    },
  };
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\proforma\index.blade.php ENDPATH**/ ?>