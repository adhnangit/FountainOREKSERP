@extends('layouts.app')
@section('title', 'Profit & Loss')
@section('page-title', 'Profit & Loss Statement')
@section('page-desc', 'Income and expenses for a selected period')

@section('content')
<div x-data="plPage()" x-init="init()">

  <div class="flex flex-col sm:flex-row sm:items-end gap-3 mb-6">
    <div>
      <label class="label text-xs">From</label>
      <input type="date" x-model="fromDate" class="input" />
    </div>
    <div>
      <label class="label text-xs">To</label>
      <input type="date" x-model="toDate" class="input" />
    </div>
    <button @click="load()" class="btn-primary h-[38px]">Generate</button>
    <div class="sm:ml-auto flex gap-2">
      <a href="{{ url('/accounting/trial-balance') }}"  class="btn-secondary text-sm">Trial Balance</a>
      <a href="{{ url('/accounting/balance-sheet') }}"  class="btn-secondary text-sm">Balance Sheet</a>
    </div>
  </div>

  <div x-show="loading" class="flex justify-center py-16">
    <svg class="animate-spin w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
  </div>

  <div x-show="!loading && data" class="space-y-5">

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="card p-4 border-l-4 border-green-500">
        <p class="text-xs text-gray-400 mb-1">Total Revenue</p>
        <p class="text-xl font-bold text-green-600 tabular-nums" x-text="fmtMoney(data?.total_income ?? 0)"></p>
      </div>
      <div class="card p-4 border-l-4 border-red-500">
        <p class="text-xs text-gray-400 mb-1">Total Expenses</p>
        <p class="text-xl font-bold text-red-600 tabular-nums" x-text="fmtMoney(data?.total_expenses ?? 0)"></p>
      </div>
      <div class="card p-4" :style="(data?.net_profit ?? 0) >= 0 ? 'border-left:4px solid #059669' : 'border-left:4px solid #dc2626'">
        <p class="text-xs text-gray-400 mb-1">Net Profit / Loss</p>
        <p class="text-xl font-bold tabular-nums" :class="(data?.net_profit ?? 0) >= 0 ? 'text-green-600' : 'text-red-600'"
           x-text="fmtMoney(data?.net_profit ?? 0)"></p>
      </div>
    </div>

    {{-- Period header --}}
    <div class="card p-3 flex items-center justify-between bg-gradient-to-r from-[#1B3EB6] to-[#0D2272] text-white rounded-xl">
      <span class="text-sm font-semibold">Income Statement</span>
      <span class="text-xs opacity-80" x-text="fmtDate(data?.from_date) + ' — ' + fmtDate(data?.to_date)"></span>
    </div>

    {{-- Income Section --}}
    <div class="card overflow-hidden">
      <div class="px-4 py-3 flex items-center gap-2" style="background:linear-gradient(135deg,#059669,#047857)">
        <span class="text-white font-bold text-sm uppercase tracking-wide">Revenue</span>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <template x-for="grp in groupByCategory(data?.income_accounts, 'income')" :key="grp.name">
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
              <tr class="cursor-pointer select-none bg-gray-50/80 dark:bg-gray-800/40 hover:bg-gray-100 dark:hover:bg-gray-800/60"
                  @click="toggleGroup('income-' + grp.name)">
                <td colspan="2" class="table-td">
                  <span class="inline-flex items-center gap-1.5 font-semibold text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <svg class="w-3 h-3 text-gray-400 transition-transform" :style="expandedGroups['income-' + grp.name] ? 'transform:rotate(90deg)' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M9 18l6-6-6-6"/></svg>
                    <span x-text="grp.name"></span>
                    <span class="text-gray-400 font-normal normal-case" x-text="'(' + grp.accounts.length + ')'"></span>
                  </span>
                </td>
                <td class="table-td text-right tabular-nums text-sm font-semibold w-40"
                    :class="grp.total >= 0 ? 'text-green-600' : 'text-red-600'" x-text="fmtMoney(grp.total)"></td>
              </tr>
              <template x-for="acc in grp.accounts" :key="acc.id">
                <tr x-show="expandedGroups['income-' + grp.name]" class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                  <td class="table-td font-mono text-xs text-gray-400 w-24 pl-8" x-text="acc.code"></td>
                  <td class="table-td">
                    <a :href="BASE.replace('/api','') + '/accounting/ledger?account_id=' + acc.id"
                       class="font-medium text-gray-800 dark:text-gray-100 hover:text-blue-600" x-text="acc.name"></a>
                    <span x-show="isContra(acc,'income')" class="ml-1.5 text-[10px] uppercase tracking-wide text-red-400">less</span>
                  </td>
                  <td class="table-td text-right tabular-nums text-sm font-medium w-40"
                      :class="isContra(acc,'income') ? 'text-red-600' : 'text-green-600'"
                      x-text="fmtMoney(signedBalance(acc,'income'))"></td>
                </tr>
              </template>
            </tbody>
          </template>
          <tbody>
            <template x-if="(data?.income_accounts ?? []).length === 0">
              <tr><td colspan="3" class="table-td text-center text-gray-400 text-sm">No revenue accounts with activity.</td></tr>
            </template>
          </tbody>
          <tfoot class="border-t-2 border-green-200 dark:border-green-800">
            <tr class="bg-green-50 dark:bg-green-900/20">
              <td colspan="2" class="px-4 py-3 text-sm font-bold text-green-700 dark:text-green-400">Total Revenue</td>
              <td class="px-4 py-3 text-right text-sm font-black tabular-nums text-green-700 dark:text-green-400"
                  x-text="fmtMoney(data?.total_income ?? 0)"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    {{-- Expenses Section --}}
    <div class="card overflow-hidden">
      <div class="px-4 py-3 flex items-center gap-2" style="background:linear-gradient(135deg,#dc2626,#b91c1c)">
        <span class="text-white font-bold text-sm uppercase tracking-wide">Expenses</span>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full">
          <template x-for="grp in groupByCategory(data?.expense_accounts, 'expense')" :key="grp.name">
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
              <tr class="cursor-pointer select-none bg-gray-50/80 dark:bg-gray-800/40 hover:bg-gray-100 dark:hover:bg-gray-800/60"
                  @click="toggleGroup('expense-' + grp.name)">
                <td colspan="2" class="table-td">
                  <span class="inline-flex items-center gap-1.5 font-semibold text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <svg class="w-3 h-3 text-gray-400 transition-transform" :style="expandedGroups['expense-' + grp.name] ? 'transform:rotate(90deg)' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M9 18l6-6-6-6"/></svg>
                    <span x-text="grp.name"></span>
                    <span class="text-gray-400 font-normal normal-case" x-text="'(' + grp.accounts.length + ')'"></span>
                  </span>
                </td>
                <td class="table-td text-right tabular-nums text-sm font-semibold w-40"
                    :class="grp.total >= 0 ? 'text-red-600' : 'text-green-600'" x-text="fmtMoney(grp.total)"></td>
              </tr>
              <template x-for="acc in grp.accounts" :key="acc.id">
                <tr x-show="expandedGroups['expense-' + grp.name]" class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                  <td class="table-td font-mono text-xs text-gray-400 w-24 pl-8" x-text="acc.code"></td>
                  <td class="table-td">
                    <a :href="BASE.replace('/api','') + '/accounting/ledger?account_id=' + acc.id"
                       class="font-medium text-gray-800 dark:text-gray-100 hover:text-blue-600" x-text="acc.name"></a>
                    <span x-show="isContra(acc,'expense')" class="ml-1.5 text-[10px] uppercase tracking-wide text-green-500">less</span>
                  </td>
                  <td class="table-td text-right tabular-nums text-sm font-medium w-40"
                      :class="isContra(acc,'expense') ? 'text-green-600' : 'text-red-600'"
                      x-text="fmtMoney(signedBalance(acc,'expense'))"></td>
                </tr>
              </template>
            </tbody>
          </template>
          <tbody>
            <template x-if="(data?.expense_accounts ?? []).length === 0">
              <tr><td colspan="3" class="table-td text-center text-gray-400 text-sm">No expense accounts with activity.</td></tr>
            </template>
          </tbody>
          <tfoot class="border-t-2 border-red-200 dark:border-red-800">
            <tr class="bg-red-50 dark:bg-red-900/20">
              <td colspan="2" class="px-4 py-3 text-sm font-bold text-red-700 dark:text-red-400">Total Expenses</td>
              <td class="px-4 py-3 text-right text-sm font-black tabular-nums text-red-700 dark:text-red-400"
                  x-text="fmtMoney(data?.total_expenses ?? 0)"></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    {{-- Net Profit/Loss --}}
    <div class="card p-5 flex items-center justify-between"
         :style="(data?.net_profit ?? 0) >= 0 ? 'border-left:4px solid #059669;background:linear-gradient(135deg,#f0fdf4,#dcfce7)' : 'border-left:4px solid #dc2626;background:linear-gradient(135deg,#fef2f2,#fee2e2)'">
      <div>
        <p class="text-sm font-semibold text-gray-600 dark:text-gray-300"
           x-text="(data?.net_profit ?? 0) >= 0 ? 'Net Profit' : 'Net Loss'"></p>
        <p class="text-xs text-gray-400" x-text="fmtDate(data?.from_date) + ' to ' + fmtDate(data?.to_date)"></p>
      </div>
      <p class="text-2xl font-black tabular-nums"
         :class="(data?.net_profit ?? 0) >= 0 ? 'text-green-700' : 'text-red-700'"
         x-text="fmtMoney(Math.abs(data?.net_profit ?? 0))"></p>
    </div>

  </div>

  <div x-show="!loading && !data" class="card p-12 text-center text-gray-400">
    Select a date range and click Generate.
  </div>
</div>
@endsection

@push('scripts')
<script>
function plPage() {
  const today = new Date();
  const startOfYear = new Date(today.getFullYear(), 0, 1).toISOString().slice(0,10);
  return {
    fromDate: startOfYear,
    toDate: today.toISOString().slice(0,10),
    data: null, loading: false,
    expandedGroups: {},
    // Contra accounts (e.g. Sales Returns, Cancelled Sales: type=income but normal_balance=debit)
    // carry a balance that reduces their section's total rather than adding to it.
    isContra(acc, section) {
      return section === 'income' ? acc.normal_balance === 'debit' : acc.normal_balance === 'credit';
    },
    signedBalance(acc, section) {
      const v = Number(acc.balance) || 0;
      return this.isContra(acc, section) ? -v : v;
    },
    groupByCategory(accounts, section) {
      const groups = {};
      for (const acc of accounts ?? []) {
        const key = acc.group || 'Other';
        if (!groups[key]) groups[key] = { name: key, accounts: [], total: 0 };
        groups[key].accounts.push(acc);
        groups[key].total += this.signedBalance(acc, section);
      }
      return Object.values(groups);
    },
    toggleGroup(key) { this.expandedGroups[key] = !this.expandedGroups[key]; },
    async load() {
      this.loading = true;
      try {
        const r = await apiFetch(`/accounting/profit-loss?from_date=${this.fromDate}&to_date=${this.toDate}`);
        this.data = await r.json();
      } finally { this.loading = false; }
    },
    async init() { await this.load(); },
  };
}
</script>
@endpush
