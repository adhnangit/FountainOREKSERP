@extends('layouts.app')
@section('title', 'Balance Sheet')
@section('page-title', 'Balance Sheet')
@section('page-desc', 'Assets, liabilities, and equity at a point in time')

@section('content')
<style>
.bs-toolbar{background:#fff;border-radius:14px;padding:14px 18px;border:1px solid #e2e8f0;margin-bottom:20px;display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap}
.bs-field label{display:block;font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px}
.bs-field input{border:1px solid #e2e8f0;border-radius:9px;padding:7px 10px;font-size:13px;color:#1e293b;background:#f8fafc;outline:none;transition:border-color .15s,box-shadow .15s;height:36px}
.bs-field input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);background:#fff}
.bs-btn-primary{background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-radius:9px;padding:8px 16px;font-size:13px;font-weight:600;height:36px;border:none;transition:opacity .15s}
.bs-btn-primary:hover{opacity:.9}
.bs-btn-ghost{background:#f1f5f9;color:#475569;border-radius:9px;padding:8px 14px;font-size:13px;font-weight:600;height:36px;border:none;transition:background .15s}
.bs-btn-ghost:hover{background:#e2e8f0}
.bs-btn-ghost:disabled{opacity:.4;cursor:not-allowed}
.bs-link{background:#eef2ff;color:#4f46e5;border-radius:9px;padding:8px 14px;font-size:13px;font-weight:600;height:36px;display:flex;align-items:center;text-decoration:none;transition:background .15s}
.bs-link:hover{background:#e0e7ff}
.dark .bs-toolbar{background:#1e293b;border-color:#334155}
.dark .bs-field input{background:#0f172a;border-color:#334155;color:#e2e8f0}
.dark .bs-field input:focus{background:#1e293b}
.dark .bs-btn-ghost{background:#334155;color:#cbd5e1}
.dark .bs-btn-ghost:hover{background:#475569}
.dark .bs-link{background:#312e81;color:#c7d2fe}
.dark .bs-link:hover{background:#3730a3}
</style>
<div x-data="balanceSheetPage()" x-init="init()">

  <div class="bs-toolbar">
    <div class="bs-field">
      <label>As of Date</label>
      <input type="date" x-model="asOf" />
    </div>
    <button @click="load()" class="bs-btn-primary">Generate</button>
    <button @click="downloadPdf()" :disabled="!data" class="bs-btn-ghost">Download PDF</button>
    <div style="margin-left:auto" class="flex gap-2">
      <a href="{{ url('/accounting/trial-balance') }}" class="bs-link">Trial Balance</a>
      <a href="{{ url('/accounting/profit-loss') }}"   class="bs-link">P&amp;L</a>
    </div>
  </div>

  <div x-show="loading" class="flex justify-center py-16">
    <svg class="animate-spin w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
  </div>

  <div x-show="!loading && data" class="space-y-5">

    {{-- Balanced indicator --}}
    <div class="card p-3 flex items-center justify-between rounded-xl"
         :style="isBalanced ? 'border-left:4px solid #059669' : 'border-left:4px solid #dc2626'">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-full flex items-center justify-center" :style="isBalanced ? 'background:#d1fae5' : 'background:#fee2e2'">
          <svg class="w-4 h-4" :style="isBalanced ? 'color:#059669' : 'color:#dc2626'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <template x-if="isBalanced"><path d="M5 13l4 4L19 7"/></template>
            <template x-if="!isBalanced"><path d="M6 18L18 6M6 6l12 12"/></template>
          </svg>
        </div>
        <div>
          <p class="font-semibold text-sm" x-text="isBalanced ? 'Balance Sheet is balanced' : 'Balance Sheet is NOT balanced'"></p>
          <p class="text-xs text-gray-400" x-text="'As of ' + fmtDate(data?.as_of)"></p>
        </div>
      </div>
      <div class="text-right">
        <p class="text-xs text-gray-400">Total Assets</p>
        <p class="font-bold tabular-nums text-blue-600" x-text="fmtMoney(data?.total_assets ?? 0)"></p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

      {{-- Left Column: Assets --}}
      <div class="card overflow-hidden">
        <div class="px-4 py-3" style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
          <span class="text-white font-bold text-sm uppercase tracking-wide">Assets</span>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <template x-for="grp in groupByCategory(data?.asset_accounts)" :key="grp.name">
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                <tr class="cursor-pointer select-none bg-gray-50/80 dark:bg-gray-800/40 hover:bg-gray-100 dark:hover:bg-gray-800/60"
                    @click="toggleGroup('asset-' + grp.name)">
                  <td colspan="2" class="table-td">
                    <span class="inline-flex items-center gap-1.5 font-semibold text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                      <svg class="w-3 h-3 text-gray-400 transition-transform" :style="expandedGroups['asset-' + grp.name] ? 'transform:rotate(90deg)' : ''"
                           fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M9 18l6-6-6-6"/></svg>
                      <span x-text="grp.name"></span>
                      <span class="text-gray-400 font-normal normal-case" x-text="'(' + grp.accounts.length + ')'"></span>
                    </span>
                  </td>
                  <td class="table-td text-right tabular-nums text-sm font-semibold text-blue-600 w-32" x-text="fmtMoney(grp.total)"></td>
                </tr>
                <template x-for="acc in grp.accounts" :key="acc.id">
                  <tr x-show="expandedGroups['asset-' + grp.name]" class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                    <td class="table-td font-mono text-xs text-gray-400 w-20 pl-8" x-text="acc.code"></td>
                    <td class="table-td">
                      <a :href="BASE.replace('/api','') + '/accounting/ledger?account_id=' + acc.id"
                         class="font-medium text-gray-800 dark:text-gray-100 hover:text-blue-600 text-sm" x-text="acc.name"></a>
                    </td>
                    <td class="table-td text-right tabular-nums text-sm font-medium text-blue-600 w-32"
                        x-text="fmtMoney(acc.balance)"></td>
                  </tr>
                </template>
              </tbody>
            </template>
            <tbody>
              <template x-if="(data?.asset_accounts ?? []).length === 0">
                <tr><td colspan="3" class="table-td text-center text-gray-400 text-sm">No assets.</td></tr>
              </template>
            </tbody>
            <tfoot class="border-t-2 border-blue-200 dark:border-blue-800">
              <tr style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
                <td colspan="2" class="px-4 py-3 text-sm font-bold text-white">Total Assets</td>
                <td class="px-4 py-3 text-right text-sm font-black tabular-nums text-white"
                    x-text="fmtMoney(data?.total_assets ?? 0)"></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      {{-- Right Column: Liabilities + Equity --}}
      <div class="space-y-4">

        {{-- Liabilities --}}
        <div class="card overflow-hidden">
          <div class="px-4 py-3" style="background:linear-gradient(135deg,#dc2626,#b91c1c)">
            <span class="text-white font-bold text-sm uppercase tracking-wide">Liabilities</span>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <template x-for="grp in groupByCategory(data?.liability_accounts)" :key="grp.name">
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                  <tr class="cursor-pointer select-none bg-gray-50/80 dark:bg-gray-800/40 hover:bg-gray-100 dark:hover:bg-gray-800/60"
                      @click="toggleGroup('liability-' + grp.name)">
                    <td colspan="2" class="table-td">
                      <span class="inline-flex items-center gap-1.5 font-semibold text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <svg class="w-3 h-3 text-gray-400 transition-transform" :style="expandedGroups['liability-' + grp.name] ? 'transform:rotate(90deg)' : ''"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M9 18l6-6-6-6"/></svg>
                        <span x-text="grp.name"></span>
                        <span class="text-gray-400 font-normal normal-case" x-text="'(' + grp.accounts.length + ')'"></span>
                      </span>
                    </td>
                    <td class="table-td text-right tabular-nums text-sm font-semibold text-red-600 w-32" x-text="fmtMoney(grp.total)"></td>
                  </tr>
                  <template x-for="acc in grp.accounts" :key="acc.id">
                    <tr x-show="expandedGroups['liability-' + grp.name]" class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                      <td class="table-td font-mono text-xs text-gray-400 w-20 pl-8" x-text="acc.code"></td>
                      <td class="table-td">
                        <a :href="BASE.replace('/api','') + '/accounting/ledger?account_id=' + acc.id"
                           class="font-medium text-gray-800 dark:text-gray-100 hover:text-blue-600 text-sm" x-text="acc.name"></a>
                      </td>
                      <td class="table-td text-right tabular-nums text-sm font-medium text-red-600 w-32"
                          x-text="fmtMoney(acc.balance)"></td>
                    </tr>
                  </template>
                </tbody>
              </template>
              <tbody>
                <template x-if="(data?.liability_accounts ?? []).length === 0">
                  <tr><td colspan="3" class="table-td text-center text-gray-400 text-sm">No liabilities.</td></tr>
                </template>
              </tbody>
              <tfoot class="border-t-2 border-red-200 dark:border-red-800">
                <tr class="bg-red-50 dark:bg-red-900/20">
                  <td colspan="2" class="px-4 py-3 text-sm font-bold text-red-700 dark:text-red-400">Total Liabilities</td>
                  <td class="px-4 py-3 text-right text-sm font-black tabular-nums text-red-700 dark:text-red-400"
                      x-text="fmtMoney(data?.total_liabilities ?? 0)"></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        {{-- Equity --}}
        <div class="card overflow-hidden">
          <div class="px-4 py-3" style="background:linear-gradient(135deg,#7c3aed,#5b21b6)">
            <span class="text-white font-bold text-sm uppercase tracking-wide">Equity</span>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full">
              <template x-for="grp in groupByCategory(data?.equity_accounts)" :key="grp.name">
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                  <tr class="cursor-pointer select-none bg-gray-50/80 dark:bg-gray-800/40 hover:bg-gray-100 dark:hover:bg-gray-800/60"
                      @click="toggleGroup('equity-' + grp.name)">
                    <td colspan="2" class="table-td">
                      <span class="inline-flex items-center gap-1.5 font-semibold text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                        <svg class="w-3 h-3 text-gray-400 transition-transform" :style="expandedGroups['equity-' + grp.name] ? 'transform:rotate(90deg)' : ''"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M9 18l6-6-6-6"/></svg>
                        <span x-text="grp.name"></span>
                        <span class="text-gray-400 font-normal normal-case" x-text="'(' + grp.accounts.length + ')'"></span>
                      </span>
                    </td>
                    <td class="table-td text-right tabular-nums text-sm font-semibold text-purple-600 w-32" x-text="fmtMoney(grp.total)"></td>
                  </tr>
                  <template x-for="acc in grp.accounts" :key="acc.id">
                    <tr x-show="expandedGroups['equity-' + grp.name]" class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                      <td class="table-td font-mono text-xs text-gray-400 w-20 pl-8" x-text="acc.code"></td>
                      <td class="table-td">
                        <a :href="BASE.replace('/api','') + '/accounting/ledger?account_id=' + acc.id"
                           class="font-medium text-gray-800 dark:text-gray-100 hover:text-blue-600 text-sm" x-text="acc.name"></a>
                      </td>
                      <td class="table-td text-right tabular-nums text-sm font-medium text-purple-600 w-32"
                          x-text="fmtMoney(acc.balance)"></td>
                    </tr>
                  </template>
                </tbody>
              </template>
              <tbody>
                {{-- Retained Earnings row — not a real account, always visible --}}
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20 border-t border-gray-100 dark:border-gray-700/40">
                  <td class="table-td font-mono text-xs text-gray-400 w-20">—</td>
                  <td class="table-td">
                    <span class="font-medium text-gray-800 dark:text-gray-100 text-sm italic">Retained Earnings (Since Inception)</span>
                  </td>
                  <td class="table-td text-right tabular-nums text-sm font-medium w-32"
                      :class="(data?.retained_earnings ?? 0) >= 0 ? 'text-green-600' : 'text-red-600'"
                      x-text="fmtMoney(data?.retained_earnings ?? 0)"></td>
                </tr>
              </tbody>
              <tfoot class="border-t-2 border-purple-200 dark:border-purple-800">
                <tr class="bg-purple-50 dark:bg-purple-900/20">
                  <td colspan="2" class="px-4 py-3 text-sm font-bold text-purple-700 dark:text-purple-400">Total Equity + Retained Earnings</td>
                  <td class="px-4 py-3 text-right text-sm font-black tabular-nums text-purple-700 dark:text-purple-400"
                      x-text="fmtMoney((data?.total_equity ?? 0) + (data?.retained_earnings ?? 0))"></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        {{-- Total L+E --}}
        <div class="card p-4 flex items-center justify-between"
             style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
          <span class="text-white font-bold text-sm">Total Liabilities + Equity</span>
          <span class="text-white font-black tabular-nums" x-text="fmtMoney(data?.total_liabilities_equity ?? 0)"></span>
        </div>

      </div>
    </div>
  </div>

  <div x-show="!loading && !data" class="card p-12 text-center text-gray-400">
    Select a date and click Generate.
  </div>
</div>
@endsection

@push('scripts')
<script>
function balanceSheetPage() {
  return {
    asOf: new Date().toISOString().slice(0,10),
    data: null, loading: false,
    expandedGroups: {},
    get isBalanced() {
      if (!this.data) return true;
      const assets = this.data.total_assets ?? 0;
      const le     = this.data.total_liabilities_equity ?? 0;
      return Math.abs(assets - le) < 0.01;
    },
    groupByCategory(accounts) {
      const groups = {};
      for (const acc of accounts ?? []) {
        const key = acc.group || 'Other';
        if (!groups[key]) groups[key] = { name: key, accounts: [], total: 0 };
        groups[key].accounts.push(acc);
        groups[key].total += Number(acc.balance) || 0;
      }
      return Object.values(groups);
    },
    toggleGroup(key) { this.expandedGroups[key] = !this.expandedGroups[key]; },
    async load() {
      this.loading = true;
      try {
        const r = await apiFetch('/accounting/balance-sheet?as_of=' + this.asOf);
        this.data = await r.json();
      } finally { this.loading = false; }
    },
    async downloadPdf() {
      try {
        const r = await apiFetch('/accounting/balance-sheet/pdf?as_of=' + this.asOf);
        if (!r.ok) { toast('Failed to generate PDF', 'error'); return; }
        const blob = await r.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'balance-sheet-' + this.asOf + '.pdf';
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
        URL.revokeObjectURL(url);
      } catch(e) { toast('PDF download failed', 'error'); }
    },
    async init() { await this.load(); },
  };
}
</script>
@endpush
