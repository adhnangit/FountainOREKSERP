@extends('layouts.app')
@section('title', 'General Ledger')
@section('page-title', 'General Ledger')
@section('page-desc', 'Account transaction history with running balance')

@section('content')
<style>
.lg-toolbar{background:#fff;border-radius:14px;padding:14px 18px;border:1px solid #e2e8f0;margin-bottom:20px;display:flex;align-items:flex-end;gap:14px;flex-wrap:wrap}
.lg-field label{display:block;font-size:11px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.04em;margin-bottom:4px}
.lg-field input,.lg-field select{border:1px solid #e2e8f0;border-radius:9px;padding:7px 10px;font-size:13px;color:#1e293b;background:#f8fafc;outline:none;transition:border-color .15s,box-shadow .15s;height:36px}
.lg-field input:focus,.lg-field select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);background:#fff}
.lg-btn-primary{background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-radius:9px;padding:8px 16px;font-size:13px;font-weight:600;height:36px;border:none;transition:opacity .15s}
.lg-btn-primary:hover{opacity:.9}
.lg-btn-primary:disabled{opacity:.4;cursor:not-allowed}
.lg-link{background:#eef2ff;color:#4f46e5;border-radius:9px;padding:8px 14px;font-size:13px;font-weight:600;height:36px;display:flex;align-items:center;text-decoration:none;transition:background .15s}
.lg-link:hover{background:#e0e7ff}
.dark .lg-toolbar{background:#1e293b;border-color:#334155}
.dark .lg-field input,.dark .lg-field select{background:#0f172a;border-color:#334155;color:#e2e8f0}
.dark .lg-field input:focus,.dark .lg-field select:focus{background:#1e293b}
.dark .lg-link{background:#312e81;color:#c7d2fe}
.dark .lg-link:hover{background:#3730a3}
</style>
<div x-data="ledgerPage()" x-init="init()">

  <div class="lg-toolbar">
    <div class="lg-field" style="flex:1;min-width:220px">
      <label>Account</label>
      <select x-model="accountId" style="width:100%">
        <option value="">— Select Account —</option>
        <template x-for="acc in accounts" :key="acc.id">
          <option :value="acc.id" x-text="acc.code + ' · ' + acc.name"></option>
        </template>
      </select>
    </div>
    <div class="lg-field">
      <label>From</label>
      <input type="date" x-model="fromDate" />
    </div>
    <div class="lg-field">
      <label>To</label>
      <input type="date" x-model="toDate" />
    </div>
    <button @click="load()" class="lg-btn-primary" :disabled="!accountId">Generate</button>
    <div style="margin-left:auto" class="flex gap-2">
      <a href="{{ url('/accounting/trial-balance') }}" class="lg-link">Trial Balance</a>
    </div>
  </div>

  <div x-show="loading" class="flex justify-center py-16">
    <svg class="animate-spin w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
  </div>

  <div x-show="!loading && data" class="space-y-4">

    {{-- Account header --}}
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

    {{-- Transactions Table --}}
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
            {{-- Opening balance row --}}
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
@endsection

@push('scripts')
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
@endpush
