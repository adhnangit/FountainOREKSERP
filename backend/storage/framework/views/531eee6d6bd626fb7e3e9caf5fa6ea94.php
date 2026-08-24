<?php $__env->startSection('title', 'Journal Entries'); ?>
<?php $__env->startSection('page-title', 'Journal Entries'); ?>
<?php $__env->startSection('page-desc', 'Double-entry bookkeeping ledger'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="journalPage()" x-init="init()">

  
  <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-6">
    <div class="flex gap-2 flex-wrap items-end">
      <div>
        <label class="label text-xs">From</label>
        <input type="date" x-model="filters.from" class="input text-sm" />
      </div>
      <div>
        <label class="label text-xs">To</label>
        <input type="date" x-model="filters.to" class="input text-sm" />
      </div>
      <div>
        <label class="label text-xs">Type</label>
        <select x-model="filters.type" class="input text-sm">
          <option value="">All types</option>
          <option value="manual">Manual</option>
          <option value="sales">Sales</option>
          <option value="purchase">Purchase</option>
          <option value="payment_received">Payment Received</option>
          <option value="payment_made">Payment Made</option>
          <option value="expense">Expense</option>
        </select>
      </div>
      <button @click="load()" class="btn-primary text-sm h-[38px]">Apply</button>
      <button @click="resetFilters()" class="btn-secondary text-sm h-[38px]">Reset</button>
    </div>
    <div class="flex gap-2 sm:ml-auto">
      <input x-model="search" type="text" placeholder="Search…" class="input text-sm w-52" />
      <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2 text-sm whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
        New Entry
      </button>
    </div>
  </div>

  
  <div x-show="!loading && entries.length > 0" class="grid grid-cols-3 gap-4 mb-5">
    <div class="card p-4 text-center">
      <p class="text-xs text-gray-400 mb-1">Total Entries</p>
      <p class="text-xl font-black text-gray-800 dark:text-gray-100" x-text="entries.length"></p>
    </div>
    <div class="card p-4 text-center">
      <p class="text-xs text-gray-400 mb-1">Total Debits</p>
      <p class="text-xl font-black text-green-600 tabular-nums" x-text="fmtMoney(totalDebits)"></p>
    </div>
    <div class="card p-4 text-center">
      <p class="text-xs text-gray-400 mb-1">Total Credits</p>
      <p class="text-xl font-black text-red-600 tabular-nums" x-text="fmtMoney(totalCredits)"></p>
    </div>
  </div>

  
  <div x-show="loading" class="flex justify-center py-16">
    <svg class="animate-spin w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
  </div>

  
  <div x-show="!loading" class="space-y-3">
    <template x-for="entry in filtered" :key="entry.id">
      <div class="card overflow-hidden">
        
        <div role="button" tabindex="0" @click="entry._open = !entry._open" @keydown.enter="entry._open = !entry._open"
                class="w-full px-5 py-3 flex items-center gap-4 hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors text-left cursor-pointer">
          <div class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center" :style="typeStyle(entry.type)">
            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <span class="font-bold text-sm text-gray-800 dark:text-gray-100" x-text="entry.entry_number"></span>
              <span class="text-xs px-2 py-0.5 rounded-full font-semibold capitalize" :style="typeBadge(entry.type)" x-text="entry.type.replace('_',' ')"></span>
              <span x-show="entry.is_auto_generated" class="text-xs px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-500 font-medium">Auto</span>
            </div>
            <p class="text-xs text-gray-400 mt-0.5" x-text="entry.description"></p>
          </div>
          <div class="text-right flex-shrink-0 hidden sm:block">
            <div class="text-xs text-gray-400" x-text="fmtDate(entry.entry_date)"></div>
            <div class="text-sm font-bold text-gray-700 dark:text-gray-200 tabular-nums" x-text="fmtMoney(entry.total_debit)"></div>
          </div>
          <div class="flex items-center gap-2 flex-shrink-0">
            <button @click.stop="deleteEntry(entry)"
                    class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                    title="Delete entry">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
            <svg class="w-4 h-4 text-gray-400 transition-transform" :class="entry._open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
          </div>
        </div>

        
        <div x-show="entry._open" x-transition class="border-t border-gray-100 dark:border-gray-700/40">
          <table class="min-w-full">
            <thead class="bg-gray-50 dark:bg-gray-800/40">
              <tr>
                <th class="table-hd">Account</th>
                <th class="table-hd">Description</th>
                <th class="table-hd text-right text-green-600">Debit</th>
                <th class="table-hd text-right text-red-500">Credit</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700/30">
              <template x-for="line in entry.lines" :key="line.id">
                <tr>
                  <td class="table-td">
                    <span class="font-mono text-xs text-gray-400 mr-2" x-text="line.account?.code"></span>
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-100" x-text="line.account?.name ?? '—'"></span>
                  </td>
                  <td class="table-td text-xs text-gray-400" x-text="line.description ?? '—'"></td>
                  <td class="table-td text-right tabular-nums text-sm text-green-600 font-medium" x-text="line.debit > 0 ? fmtMoney(line.debit) : '—'"></td>
                  <td class="table-td text-right tabular-nums text-sm text-red-600 font-medium" x-text="line.credit > 0 ? fmtMoney(line.credit) : '—'"></td>
                </tr>
              </template>
              <tr class="bg-gray-50 dark:bg-gray-800/30 font-semibold">
                <td class="table-td text-xs text-gray-500 uppercase tracking-wide" colspan="2">Total</td>
                <td class="table-td text-right tabular-nums text-sm text-green-600" x-text="fmtMoney(entry.total_debit)"></td>
                <td class="table-td text-right tabular-nums text-sm text-red-600" x-text="fmtMoney(entry.total_credit)"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
    <div x-show="filtered.length === 0 && !loading" class="card p-12 text-center text-gray-400">
      <svg class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      <p>No journal entries found for this period.</p>
    </div>
  </div>

  
  <div x-show="modal.open" x-transition.opacity class="fixed inset-0 z-50 flex items-start justify-center p-4 overflow-y-auto" style="background:rgba(0,0,0,0.5)">
    <div @click.away="modal.open = false" class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-7xl my-6">
      <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
        <h3 class="font-bold text-gray-800 dark:text-gray-100">New Journal Entry</h3>
        <button @click="modal.open = false" class="text-gray-400 hover:text-gray-600"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg></button>
      </div>
      <div class="px-6 py-5 space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Date <span class="text-red-500">*</span></label>
            <input type="date" x-model="modal.entry_date" class="input text-sm" />
          </div>
          <div>
            <label class="label">Branch <span class="text-red-500">*</span></label>
            <select x-model="modal.branch_id" class="input text-sm">
              <option value="">Select branch…</option>
              <template x-for="b in branches" :key="b.id">
                <option :value="b.id" x-text="b.name"></option>
              </template>
            </select>
          </div>
        </div>
        <div>
          <label class="label">Description <span class="text-red-500">*</span></label>
          <input type="text" x-model="modal.description" class="input text-sm" placeholder="e.g. Opening balance adjustment" />
        </div>

        
        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="label mb-0">Lines <span class="text-red-500">*</span></label>
            <button type="button" @click="addLine()" class="text-xs text-blue-600 hover:underline font-medium">+ Add Line</button>
          </div>
          <div class="border border-gray-200 dark:border-gray-600 rounded-xl overflow-visible">
            <table class="min-w-full">
              <thead class="bg-gray-50 dark:bg-gray-800/50 text-xs">
                <tr>
                  <th class="px-3 py-2 text-left text-gray-500 rounded-tl-xl">Account</th>
                  <th class="px-3 py-2 text-left text-gray-500">Description</th>
                  <th class="px-3 py-2 text-right text-green-600">Debit</th>
                  <th class="px-3 py-2 text-right text-red-500">Credit</th>
                  <th class="px-3 py-2 w-8 rounded-tr-xl"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                <template x-for="(line, idx) in modal.lines" :key="idx">
                  <tr>
                    <td class="px-3 py-2">
                      <div class="search-dd" x-data="{ open: false, q: '' }" @click.away="open = false" @keydown.escape="open = false">
                        <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.acctLine?.focus())"
                                class="input text-xs py-1.5 w-full text-left flex items-center justify-between gap-1">
                          <span class="truncate" :class="line.account_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                                x-text="line.account_id ? (accounts.find(a => a.id == line.account_id) ? accounts.find(a => a.id == line.account_id).code + ' – ' + accounts.find(a => a.id == line.account_id).name : '—') : 'Select account…'"></span>
                          <svg class="w-3 h-3 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu" style="right:auto;min-width:22rem;max-width:28rem">
                          <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                            <input x-ref="acctLine" x-model="q" type="text" placeholder="Search code or name…" class="input text-xs w-full py-1.5" @keydown.stop />
                          </div>
                          <div class="max-h-52 overflow-y-auto py-1">
                            <template x-for="a in accounts.filter(a => !q || a.name.toLowerCase().includes(q.toLowerCase()) || a.code.toLowerCase().includes(q.toLowerCase()))" :key="a.id">
                              <button type="button" @click="line.account_id = a.id; open = false; q = ''"
                                      class="search-dd-item" :class="line.account_id == a.id ? 'active' : ''">
                                <span class="text-xs font-medium text-gray-800 dark:text-gray-100 flex-1" style="white-space:normal;line-height:1.3" x-text="a.code + ' – ' + a.name"></span>
                              </button>
                            </template>
                            <div x-show="accounts.filter(a => !q || a.name.toLowerCase().includes(q.toLowerCase()) || a.code.toLowerCase().includes(q.toLowerCase())).length === 0"
                                 class="px-4 py-3 text-xs text-gray-400 text-center">No accounts found</div>
                          </div>
                        </div>
                      </div>
                    </td>
                    <td class="px-3 py-2">
                      <input type="text" x-model="line.description" class="input text-xs py-1.5" placeholder="Optional" />
                    </td>
                    <td class="px-3 py-2 text-right">
                      <input type="number" x-model.number="line.debit" min="0" step="0.01" @input="line.credit = line.debit > 0 ? 0 : line.credit" class="input text-xs py-1.5 text-right w-28" placeholder="0.00" />
                    </td>
                    <td class="px-3 py-2 text-right">
                      <input type="number" x-model.number="line.credit" min="0" step="0.01" @input="line.debit = line.credit > 0 ? 0 : line.debit" class="input text-xs py-1.5 text-right w-28" placeholder="0.00" />
                    </td>
                    <td class="px-3 py-2">
                      <button @click="modal.lines.splice(idx,1)" x-show="modal.lines.length > 2" class="text-gray-300 hover:text-red-500">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                      </button>
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot class="bg-gray-50 dark:bg-gray-800/30 border-t border-gray-200 dark:border-gray-600">
                <tr>
                  <td colspan="2" class="px-3 py-2 text-xs font-semibold text-gray-500 rounded-bl-xl">Totals</td>
                  <td class="px-3 py-2 text-right text-sm font-bold tabular-nums" :class="Math.abs(modalDebit-modalCredit) < 0.01 ? 'text-green-600' : 'text-green-400'" x-text="fmtMoney(modalDebit)"></td>
                  <td class="px-3 py-2 text-right text-sm font-bold tabular-nums" :class="Math.abs(modalDebit-modalCredit) < 0.01 ? 'text-red-600' : 'text-red-400'" x-text="fmtMoney(modalCredit)"></td>
                  <td class="rounded-br-xl"></td>
                </tr>
              </tfoot>
            </table>
          </div>
          <p x-show="Math.abs(modalDebit-modalCredit) > 0.01" class="text-xs text-red-500 mt-1.5">
            Entry is not balanced. Difference: <span class="font-bold" x-text="fmtMoney(Math.abs(modalDebit-modalCredit))"></span>
          </p>
        </div>
      </div>
      <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
        <button @click="modal.open = false" class="btn-secondary">Cancel</button>
        <button @click="saveEntry()" :disabled="saving || Math.abs(modalDebit-modalCredit) > 0.01" class="btn-primary flex items-center gap-2">
          <svg x-show="saving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
          Post Entry
        </button>
      </div>
    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
const JE_TYPE_COLORS = {
  manual:           'background:#374151',
  sales:            'background:#1B3EB6',
  purchase:         'background:#7c3aed',
  payment_received: 'background:#059669',
  payment_made:     'background:#dc2626',
  expense:          'background:#d97706',
};
const JE_BADGE = {
  manual:           'background:#f3f4f6;color:#374151',
  sales:            'background:#dbeafe;color:#1d4ed8',
  purchase:         'background:#ede9fe;color:#6d28d9',
  payment_received: 'background:#d1fae5;color:#065f46',
  payment_made:     'background:#fee2e2;color:#991b1b',
  expense:          'background:#fef3c7;color:#92400e',
};

function journalPage() {
  return {
    entries: [], accounts: [], branches: [], loading: true, saving: false, search: '',
    filters: {
      from: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0,10),
      to:   new Date().toISOString().slice(0,10),
      type: '',
    },
    modal: { open:false, entry_date: new Date().toISOString().slice(0,10), branch_id:'', description:'', lines:[] },

    get filtered() {
      const q = this.search.toLowerCase();
      return this.entries.filter(e =>
        !q || e.entry_number.toLowerCase().includes(q) || e.description.toLowerCase().includes(q)
      );
    },
    get totalDebits()  { return this.filtered.reduce((s,e) => s + parseFloat(e.total_debit  ?? 0), 0); },
    get totalCredits() { return this.filtered.reduce((s,e) => s + parseFloat(e.total_credit ?? 0), 0); },
    get modalDebit()   { return this.modal.lines.reduce((s,l) => s + (parseFloat(l.debit)  || 0), 0); },
    get modalCredit()  { return this.modal.lines.reduce((s,l) => s + (parseFloat(l.credit) || 0), 0); },

    typeStyle(t) { return JE_TYPE_COLORS[t] ?? JE_TYPE_COLORS.manual; },
    typeBadge(t) { return JE_BADGE[t] ?? JE_BADGE.manual; },

    openCreate() {
      // Default to whichever branch is currently active in the top-nav switcher
      // (same convention as invoice/PO creation) — never silently fall back to
      // "first branch in the list", which can post an entry to the wrong branch
      // without the user noticing the dropdown didn't match their active branch.
      let defaultBranchId = this.branches[0]?.id ?? '';
      try {
        const u = JSON.parse(localStorage.getItem('medri_user') || '{}');
        const stored = localStorage.getItem('medri_branch');
        const bid = (stored && stored !== 'all') ? stored : u.default_branch_id;
        if (bid) defaultBranchId = bid;
      } catch (_) {}

      this.modal = {
        open: true,
        entry_date: new Date().toISOString().slice(0,10),
        branch_id: defaultBranchId,
        description: '',
        lines: [
          { account_id:'', description:'', debit:0, credit:0 },
          { account_id:'', description:'', debit:0, credit:0 },
        ],
      };
    },

    addLine() { this.modal.lines.push({ account_id:'', description:'', debit:0, credit:0 }); },

    async saveEntry() {
      if (!this.modal.branch_id || !this.modal.description) { toast('Branch and description are required', 'error'); return; }
      this.saving = true;
      try {
        const res = await apiFetch('/accounting/journal-entries', { method:'POST', body: JSON.stringify(this.modal) });
        if (res.ok) { toast('Journal entry posted', 'success'); this.modal.open = false; await this.load(); }
        else { const e = await res.json(); toast(e.message ?? 'Failed', 'error'); }
      } finally { this.saving = false; }
    },

    async load() {
      this.loading = true;
      const p = new URLSearchParams();
      if (this.filters.from) p.set('date_from', this.filters.from);
      if (this.filters.to)   p.set('date_to',   this.filters.to);
      if (this.filters.type) p.set('type',       this.filters.type);
      p.set('per_page', '100');
      try {
        const d = await apiFetch('/accounting/journal-entries?' + p).then(r => r.json());
        this.entries = (d.data ?? d).map(e => ({ ...e, _open: false }));
      } finally { this.loading = false; }
    },

    async deleteEntry(entry) {
      const label = entry.is_auto_generated ? 'This is an auto-generated entry. Deleting it may cause accounting imbalances. Continue?' : 'Delete this journal entry permanently?';
      if (!confirm(label)) return;
      try {
        const r = await apiFetch('/accounting/journal-entries/' + entry.id, { method: 'DELETE' });
        if (r.ok) {
          this.entries = this.entries.filter(e => e.id !== entry.id);
          toast('Journal entry deleted.', 'success');
        } else {
          const err = await r.json();
          toast(err.message || 'Failed to delete.', 'error');
        }
      } catch { toast('Failed to delete.', 'error'); }
    },

    resetFilters() {
      this.filters = { from: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0,10), to: new Date().toISOString().slice(0,10), type: '' };
      this.load();
    },

    async init() {
      const [, , br] = await Promise.all([
        this.load(),
        apiFetch('/accounting/accounts').then(r => r.json()).then(d => this.accounts = d),
        apiFetch('/branches').then(r => r.json()).then(d => { this.branches = d.data ?? d; }),
      ]);
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views/accounting/journal.blade.php ENDPATH**/ ?>