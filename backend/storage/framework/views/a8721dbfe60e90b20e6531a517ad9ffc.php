<?php $__env->startSection('title', 'General Ledger'); ?>
<?php $__env->startSection('page-title', 'General Ledger'); ?>
<?php $__env->startSection('page-desc', 'Account transaction history with running balance'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="ledgerPage()" x-init="init()">

  <div class="flex flex-col sm:flex-row sm:items-end gap-3 mb-6">
    <div class="flex-1 min-w-[180px]">
      <label class="label text-xs">Account</label>
      <select x-model="accountId" class="input">
        <option value="">— Select Account —</option>
        <template x-for="acc in accounts" :key="acc.id">
          <option :value="acc.id" x-text="acc.code + ' · ' + acc.name"></option>
        </template>
      </select>
    </div>
    <div>
      <label class="label text-xs">From</label>
      <input type="date" x-model="fromDate" class="input" />
    </div>
    <div>
      <label class="label text-xs">To</label>
      <input type="date" x-model="toDate" class="input" />
    </div>
    <button @click="load()" class="btn-primary h-[38px]" :disabled="!accountId">Generate</button>
    <div class="sm:ml-auto flex gap-2">
      <a href="<?php echo e(url('/accounting/trial-balance')); ?>" class="btn-secondary text-sm">Trial Balance</a>
    </div>
  </div>

  <div x-show="loading" class="flex justify-center py-16">
    <svg class="animate-spin w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
  </div>

  <div x-show="!loading && data" class="space-y-4">

    
    <div class="card p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3"
         style="border-left:4px solid #1B3EB6">
      <div>
        <p class="text-xs text-gray-400 mb-0.5">Account</p>
        <p class="font-bold text-gray-800 dark:text-gray-100" x-text="(data?.account?.code ?? '') + ' · ' + (data?.account?.name ?? '')"></p>
        <p class="text-xs text-gray-500 capitalize" x-text="data?.account?.type ?? ''"></p>
      </div>
      <div class="flex gap-6 text-right">
        <div>
          <p class="text-xs text-gray-400">Opening Balance</p>
          <p class="font-bold tabular-nums text-gray-700 dark:text-gray-200" x-text="fmtMoney(data?.opening_balance ?? 0)"></p>
        </div>
        <div>
          <p class="text-xs text-gray-400">Closing Balance</p>
          <p class="font-bold tabular-nums" :class="(data?.closing_balance ?? 0) >= 0 ? 'text-green-600' : 'text-red-600'"
             x-text="fmtMoney(data?.closing_balance ?? 0)"></p>
        </div>
      </div>
    </div>

    
    <div class="card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wide w-28">Date</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wide w-28 hidden sm:table-cell">Entry #</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wide hidden md:table-cell">Type</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-white uppercase tracking-wide">Description</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide" style="color:#fca5a5">Debit</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide" style="color:#86efac">Credit</th>
              <th class="px-4 py-3 text-right text-xs font-semibold text-white uppercase tracking-wide">Balance</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
            
            <tr class="bg-gray-50 dark:bg-gray-800/30">
              <td class="table-td text-xs text-gray-500" x-text="fmtDate(data?.from_date)"></td>
              <td class="table-td hidden sm:table-cell"></td>
              <td class="table-td hidden md:table-cell"></td>
              <td class="table-td text-xs font-semibold text-gray-500 italic">Opening Balance</td>
              <td class="table-td text-right"></td>
              <td class="table-td text-right"></td>
              <td class="table-td text-right tabular-nums text-sm font-bold text-gray-600 dark:text-gray-300"
                  x-text="fmtMoney(data?.opening_balance ?? 0)"></td>
            </tr>
            <template x-for="(entry, idx) in data?.entries ?? []" :key="idx">
              <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                <td class="table-td text-xs text-gray-500" x-text="fmtDate(entry.date)"></td>
                <td class="table-td hidden sm:table-cell">
                  <span class="font-mono text-xs text-blue-600" x-text="entry.entry_number"></span>
                </td>
                <td class="table-td hidden md:table-cell">
                  <span class="text-xs px-2 py-0.5 rounded-full font-medium capitalize"
                        :class="typeClass(entry.type)" x-text="entry.type?.replace('_', ' ')"></span>
                </td>
                <td class="table-td text-sm text-gray-700 dark:text-gray-200 max-w-xs truncate" x-text="entry.description"></td>
                <td class="table-td text-right tabular-nums text-sm font-medium text-red-600"
                    x-text="entry.debit > 0 ? fmtMoney(entry.debit) : '—'"></td>
                <td class="table-td text-right tabular-nums text-sm font-medium text-green-600"
                    x-text="entry.credit > 0 ? fmtMoney(entry.credit) : '—'"></td>
                <td class="table-td text-right tabular-nums text-sm font-bold"
                    :class="entry.balance >= 0 ? 'text-gray-800 dark:text-gray-100' : 'text-red-600'"
                    x-text="fmtMoney(entry.balance)"></td>
              </tr>
            </template>
            <template x-if="(data?.entries ?? []).length === 0">
              <tr>
                <td colspan="7" class="table-td text-center text-gray-400 text-sm py-8">No transactions in this period.</td>
              </tr>
            </template>
          </tbody>
          <tfoot class="border-t-2 border-gray-300 dark:border-gray-600" style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
            <tr>
              <td colspan="4" class="px-4 py-3 text-sm font-bold text-white">Closing Balance</td>
              <td colspan="2" class="px-4 py-3 text-right text-xs text-blue-200">
                <span x-text="fmtDate(data?.to_date)"></span>
              </td>
              <td class="px-4 py-3 text-right text-sm font-black tabular-nums text-white"
                  x-text="fmtMoney(data?.closing_balance ?? 0)"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>

  <div x-show="!loading && !data" class="card p-12 text-center text-gray-400">
    Select an account and date range, then click Generate.
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function ledgerPage() {
  const today = new Date();
  const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().slice(0,10);
  const params = new URLSearchParams(window.location.search);
  return {
    accounts: [],
    accountId: params.get('account_id') ?? '',
    fromDate: startOfMonth,
    toDate: today.toISOString().slice(0,10),
    data: null, loading: false,
    typeClass(type) {
      const map = {
        sales: 'bg-blue-100 text-blue-700', purchase: 'bg-purple-100 text-purple-700',
        payment_received: 'bg-green-100 text-green-700', payment_made: 'bg-red-100 text-red-700',
        expense: 'bg-orange-100 text-orange-700', manual: 'bg-gray-100 text-gray-700',
      };
      return map[type] ?? 'bg-gray-100 text-gray-600';
    },
    async load() {
      if (!this.accountId) return;
      this.loading = true;
      try {
        const r = await apiFetch(`/accounting/general-ledger?account_id=${this.accountId}&from_date=${this.fromDate}&to_date=${this.toDate}`);
        this.data = await r.json();
      } finally { this.loading = false; }
    },
    async init() {
      const r = await apiFetch('/accounting/accounts');
      this.accounts = await r.json();
      if (this.accountId) await this.load();
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/accounting/ledger.blade.php ENDPATH**/ ?>