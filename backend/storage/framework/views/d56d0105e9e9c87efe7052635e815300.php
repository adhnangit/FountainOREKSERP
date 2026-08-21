<?php $__env->startSection('title', 'Trial Balance'); ?>
<?php $__env->startSection('page-title', 'Trial Balance'); ?>
<?php $__env->startSection('page-desc', 'Verify that debits equal credits across all accounts'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="trialBalancePage()" x-init="init()">

  <div class="flex flex-col sm:flex-row sm:items-end gap-3 mb-6">
    <div>
      <label class="label text-xs">As of Date</label>
      <input type="date" x-model="asOf" class="input" />
    </div>
    <button @click="load()" class="btn-primary h-[38px]">Generate</button>
    <div class="sm:ml-auto flex gap-2">
      <a href="<?php echo e(url('/accounting/profit-loss')); ?>"   class="btn-secondary text-sm">P&amp;L</a>
      <a href="<?php echo e(url('/accounting/balance-sheet')); ?>" class="btn-secondary text-sm">Balance Sheet</a>
    </div>
  </div>

  <div x-show="loading" class="flex justify-center py-16">
    <svg class="animate-spin w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
  </div>

  <div x-show="!loading && data">
    
    <div class="card p-4 mb-5 flex items-center justify-between"
         :style="data?.balanced ? 'border-left:4px solid #059669' : 'border-left:4px solid #dc2626'">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-full flex items-center justify-center" :style="data?.balanced ? 'background:#d1fae5' : 'background:#fee2e2'">
          <svg class="w-4 h-4" :style="data?.balanced ? 'color:#059669' : 'color:#dc2626'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <template x-if="data?.balanced"><path d="M5 13l4 4L19 7"/></template>
            <template x-if="!data?.balanced"><path d="M6 18L18 6M6 6l12 12"/></template>
          </svg>
        </div>
        <div>
          <p class="font-semibold text-sm" x-text="data?.balanced ? 'Trial Balance is balanced' : 'Trial Balance is NOT balanced'"></p>
          <p class="text-xs text-gray-400" x-text="'As of ' + fmtDate(data?.as_of)"></p>
        </div>
      </div>
      <div class="text-right">
        <p class="text-xs text-gray-400">Difference</p>
        <p class="font-bold tabular-nums" :class="data?.balanced ? 'text-green-600' : 'text-red-600'"
           x-text="fmtMoney(Math.abs((data?.total_debit ?? 0) - (data?.total_credit ?? 0)))"></p>
      </div>
    </div>

    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wide w-24">Code</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wide">Account Name</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wide hidden sm:table-cell">Type</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide" style="color:#fca5a5">Debit</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide" style="color:#86efac">Credit</th>
            </tr>
          </thead>
          <template x-for="grp in groupByCategory(data?.accounts)" :key="grp.name">
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
              <tr class="cursor-pointer select-none bg-gray-50/80 dark:bg-gray-800/40 hover:bg-gray-100 dark:hover:bg-gray-800/60"
                  @click="toggleGroup(grp.name)">
                <td colspan="3" class="table-td">
                  <span class="inline-flex items-center gap-1.5 font-semibold text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <svg class="w-3 h-3 text-gray-400 transition-transform" :style="expandedGroups[grp.name] ? 'transform:rotate(90deg)' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M9 18l6-6-6-6"/></svg>
                    <span x-text="grp.name"></span>
                    <span class="text-gray-400 font-normal normal-case" x-text="'(' + grp.accounts.length + ')'"></span>
                  </span>
                </td>
                <td class="table-td text-right tabular-nums text-sm font-semibold text-red-600" x-text="grp.debit > 0 ? fmtMoney(grp.debit) : '—'"></td>
                <td class="table-td text-right tabular-nums text-sm font-semibold text-green-600" x-text="grp.credit > 0 ? fmtMoney(grp.credit) : '—'"></td>
              </tr>
              <template x-for="acc in grp.accounts" :key="acc.id">
                <tr x-show="expandedGroups[grp.name]" class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                  <td class="table-td font-mono text-xs text-gray-400 pl-8" x-text="acc.code"></td>
                  <td class="table-td">
                    <a :href="BASE.replace('/api','') + '/accounting/ledger?account_id=' + acc.id"
                       class="font-medium text-gray-800 dark:text-gray-100 hover:text-blue-600" x-text="acc.name"></a>
                  </td>
                  <td class="table-td text-xs text-gray-500 capitalize hidden sm:table-cell" x-text="acc.type"></td>
                  <td class="table-td text-right tabular-nums text-sm font-medium text-red-600" x-text="acc.debit_balance > 0 ? fmtMoney(acc.debit_balance) : '—'"></td>
                  <td class="table-td text-right tabular-nums text-sm font-medium text-green-600" x-text="acc.credit_balance > 0 ? fmtMoney(acc.credit_balance) : '—'"></td>
                </tr>
              </template>
            </tbody>
          </template>
          <tfoot class="border-t-2 border-gray-300 dark:border-gray-600" style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
            <tr>
              <td colspan="3" class="px-4 py-3 text-sm font-bold text-white">TOTAL</td>
              <td class="px-4 py-3 text-right text-sm font-black tabular-nums text-white" x-text="fmtMoney(data?.total_debit ?? 0)"></td>
              <td class="px-4 py-3 text-right text-sm font-black tabular-nums text-white" x-text="fmtMoney(data?.total_credit ?? 0)"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <div x-show="!loading && !data" class="card p-12 text-center text-gray-400">
    Select a date and click Generate.
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function trialBalancePage() {
  return {
    asOf: new Date().toISOString().slice(0,10),
    data: null, loading: false,
    expandedGroups: {},
    groupByCategory(accounts) {
      const groups = {};
      for (const acc of accounts ?? []) {
        const key = acc.group || 'Other';
        if (!groups[key]) groups[key] = { name: key, accounts: [], debit: 0, credit: 0 };
        groups[key].accounts.push(acc);
        groups[key].debit  += Number(acc.debit_balance) || 0;
        groups[key].credit += Number(acc.credit_balance) || 0;
      }
      return Object.values(groups);
    },
    toggleGroup(key) { this.expandedGroups[key] = !this.expandedGroups[key]; },
    async load() {
      this.loading = true;
      try {
        const r = await apiFetch('/accounting/trial-balance?as_of=' + this.asOf);
        this.data = await r.json();
      } finally { this.loading = false; }
    },
    async init() { await this.load(); },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/accounting/trial-balance.blade.php ENDPATH**/ ?>