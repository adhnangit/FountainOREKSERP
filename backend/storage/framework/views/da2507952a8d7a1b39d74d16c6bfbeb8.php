<?php $__env->startSection('title', 'Accounting Settings'); ?>
<?php $__env->startSection('page-title', 'Accounting Settings'); ?>
<?php $__env->startSection('page-desc', 'Configure default accounts and financial year'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="accountingSettingsPage()" x-init="init()">

  <div x-show="loading" class="flex justify-center py-16">
    <svg class="animate-spin w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
  </div>

  <div x-show="!loading" class="space-y-6">

    
    <div class="card overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/40 flex items-center justify-between"
           style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
        <div>
          <p class="font-bold text-white">Default Account Mappings</p>
          <p class="text-xs text-blue-200 mt-0.5">These accounts are used when posting automatic journal entries</p>
        </div>
        <button @click="saveSettings()" class="btn-primary text-sm" :disabled="saving">
          <span x-show="saving">Saving…</span>
          <span x-show="!saving">Save Changes</span>
        </button>
      </div>

      <div class="p-5 space-y-5">
        <div x-show="saveSuccess" class="rounded-lg p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 text-sm flex items-center gap-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
          Settings saved successfully.
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">

          
          <div>
            <label class="label text-xs">Trade Receivables (A/R)</label>
            <select x-model="form.acc_trade_receivables" class="input">
              <option value="">— Not set —</option>
              <template x-for="acc in assetAccounts" :key="acc.id">
                <option :value="acc.id" x-text="acc.code + ' · ' + acc.name"></option>
              </template>
            </select>
            <p class="text-xs text-gray-400 mt-1">Debited when a sales invoice is issued</p>
          </div>

          
          <div>
            <label class="label text-xs">Trade Payables (A/P)</label>
            <select x-model="form.acc_trade_payables" class="input">
              <option value="">— Not set —</option>
              <template x-for="acc in liabilityAccounts" :key="acc.id">
                <option :value="acc.id" x-text="acc.code + ' · ' + acc.name"></option>
              </template>
            </select>
            <p class="text-xs text-gray-400 mt-1">Credited when a supplier invoice is received</p>
          </div>

          
          <div>
            <label class="label text-xs">Sales Revenue</label>
            <select x-model="form.acc_sales_revenue" class="input">
              <option value="">— Not set —</option>
              <template x-for="acc in incomeAccounts" :key="acc.id">
                <option :value="acc.id" x-text="acc.code + ' · ' + acc.name"></option>
              </template>
            </select>
            <p class="text-xs text-gray-400 mt-1">Credited for the net amount on sales invoices</p>
          </div>

          
          <div>
            <label class="label text-xs">Service Income</label>
            <select x-model="form.acc_service_income" class="input">
              <option value="">— Not set —</option>
              <template x-for="acc in incomeAccounts" :key="acc.id">
                <option :value="acc.id" x-text="acc.code + ' · ' + acc.name"></option>
              </template>
            </select>
            <p class="text-xs text-gray-400 mt-1">Credited for the service portion of an invoice (e.g. equipment rental)</p>
          </div>

          
          <div>
            <label class="label text-xs">Sales Returns / Discounts</label>
            <select x-model="form.acc_sales_returns" class="input">
              <option value="">— Not set —</option>
              <template x-for="acc in incomeAccounts" :key="acc.id">
                <option :value="acc.id" x-text="acc.code + ' · ' + acc.name"></option>
              </template>
            </select>
            <p class="text-xs text-gray-400 mt-1">Used for credit notes and discount amounts</p>
          </div>

          
          <div>
            <label class="label text-xs">Cancelled Sales</label>
            <select x-model="form.acc_sales_cancelled" class="input">
              <option value="">— Not set —</option>
              <template x-for="acc in incomeAccounts" :key="acc.id">
                <option :value="acc.id" x-text="acc.code + ' · ' + acc.name"></option>
              </template>
            </select>
            <p class="text-xs text-gray-400 mt-1">Used when a confirmed invoice is cancelled, so the reversal shows as its own P&amp;L line instead of reducing Sales Revenue directly</p>
          </div>

          
          <div>
            <label class="label text-xs">Cost of Goods Sold (COGS)</label>
            <select x-model="form.acc_cogs" class="input">
              <option value="">— Not set —</option>
              <template x-for="acc in expenseAccounts" :key="acc.id">
                <option :value="acc.id" x-text="acc.code + ' · ' + acc.name"></option>
              </template>
            </select>
            <p class="text-xs text-gray-400 mt-1">Debited when inventory is sold</p>
          </div>

          
          <div>
            <label class="label text-xs">Inventory / Stock</label>
            <select x-model="form.acc_inventory" class="input">
              <option value="">— Not set —</option>
              <template x-for="acc in assetAccounts" :key="acc.id">
                <option :value="acc.id" x-text="acc.code + ' · ' + acc.name"></option>
              </template>
            </select>
            <p class="text-xs text-gray-400 mt-1">Debited when stock is received from supplier</p>
          </div>

          
          <div>
            <label class="label text-xs">Cash Account</label>
            <select x-model="form.acc_cash" class="input">
              <option value="">— Not set —</option>
              <template x-for="acc in cashAccounts" :key="acc.id">
                <option :value="acc.id" x-text="acc.code + ' · ' + acc.name"></option>
              </template>
            </select>
            <p class="text-xs text-gray-400 mt-1">Debited on cash payments received</p>
          </div>

          
          <div>
            <label class="label text-xs">Bank Account</label>
            <select x-model="form.acc_bank" class="input">
              <option value="">— Not set —</option>
              <template x-for="acc in bankAccounts" :key="acc.id">
                <option :value="acc.id" x-text="acc.code + ' · ' + acc.name"></option>
              </template>
            </select>
            <p class="text-xs text-gray-400 mt-1">Debited on bank/cheque payments received</p>
          </div>

          
          <div>
            <label class="label text-xs">Cheques in Hand</label>
            <select x-model="form.acc_cheques_in_hand" class="input">
              <option value="">— Not set —</option>
              <template x-for="acc in assetAccounts" :key="acc.id">
                <option :value="acc.id" x-text="acc.code + ' · ' + acc.name"></option>
              </template>
            </select>
            <p class="text-xs text-gray-400 mt-1">Holds cheques received but not yet deposited/cleared</p>
          </div>

          
          <div>
            <label class="label text-xs">Tax / VAT Payable</label>
            <select x-model="form.acc_tax_payable" class="input">
              <option value="">— Not set —</option>
              <template x-for="acc in liabilityAccounts" :key="acc.id">
                <option :value="acc.id" x-text="acc.code + ' · ' + acc.name"></option>
              </template>
            </select>
            <p class="text-xs text-gray-400 mt-1">Credited for tax collected on sales invoices</p>
          </div>

        </div>
      </div>
    </div>

    
    <div class="card overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/40 flex items-center justify-between"
           style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
        <div>
          <p class="font-bold text-white">Financial Years</p>
          <p class="text-xs text-blue-200 mt-0.5">Journal entries require an active financial year</p>
        </div>
        <button @click="showNewYearModal = true" class="btn-primary text-sm">+ New Year</button>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full">
          <thead class="bg-gray-50 dark:bg-gray-800/40">
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Name</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Start Date</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">End Date</th>
              <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
            <template x-for="fy in financialYears" :key="fy.id">
              <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                <td class="table-td font-medium text-gray-800 dark:text-gray-100" x-text="fy.name"></td>
                <td class="table-td text-sm text-gray-500" x-text="fmtDate(fy.start_date)"></td>
                <td class="table-td text-sm text-gray-500" x-text="fmtDate(fy.end_date)"></td>
                <td class="table-td">
                  <span class="text-xs px-2 py-1 rounded-full font-semibold"
                        :class="fy.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'"
                        x-text="fy.status === 'active' ? 'Active' : 'Closed'"></span>
                </td>
              </tr>
            </template>
            <template x-if="financialYears.length === 0">
              <tr><td colspan="4" class="table-td text-center text-gray-400 text-sm py-8">No financial years defined.</td></tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  
  <div x-show="showNewYearModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
       x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="absolute inset-0 bg-black/50" @click="showNewYearModal = false"></div>
    <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md p-6 z-10 max-h-[90vh] overflow-y-auto">
      <h3 class="text-lg font-bold text-gray-800 dark:text-gray-100 mb-5">New Financial Year</h3>

      <div class="space-y-4">
        <div>
          <label class="label text-xs">Year Name</label>
          <input type="text" x-model="yearForm.name" class="input" placeholder="e.g. FY 2025-2026" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label text-xs">Start Date</label>
            <input type="date" x-model="yearForm.start_date" class="input" />
          </div>
          <div>
            <label class="label text-xs">End Date</label>
            <input type="date" x-model="yearForm.end_date" class="input" />
          </div>
        </div>
        <p class="text-xs text-amber-600 bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3">
          Creating a new financial year will close the currently active year.
        </p>
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <button @click="showNewYearModal = false" class="btn-secondary">Cancel</button>
        <button @click="createFinancialYear()" class="btn-primary" :disabled="savingYear">
          <span x-show="savingYear">Creating…</span>
          <span x-show="!savingYear">Create Year</span>
        </button>
      </div>
    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function accountingSettingsPage() {
  return {
    loading: true, saving: false, savingYear: false,
    saveSuccess: false, showNewYearModal: false,
    accounts: [], financialYears: [],
    form: {
      acc_trade_receivables: '', acc_trade_payables: '', acc_sales_revenue: '',
      acc_service_income: '',
      acc_sales_returns: '', acc_sales_cancelled: '', acc_cogs: '', acc_inventory: '',
      acc_cash: '', acc_bank: '', acc_tax_payable: '', acc_cheques_in_hand: '',
    },
    yearForm: { name: '', start_date: '', end_date: '' },
    get assetAccounts()     { return this.accounts.filter(a => a.type === 'asset'); },
    get liabilityAccounts() { return this.accounts.filter(a => a.type === 'liability'); },
    get incomeAccounts()    { return this.accounts.filter(a => a.type === 'income'); },
    get expenseAccounts()   { return this.accounts.filter(a => a.type === 'expense'); },
    get cashAccounts()      { return this.accounts.filter(a => a.is_cash_account || a.is_bank_account); },
    get bankAccounts()      { return this.accounts.filter(a => a.is_bank_account); },
    async init() {
      const [accR, fyR, settR] = await Promise.all([
        apiFetch('/accounting/accounts'),
        apiFetch('/accounting/financial-years'),
        apiFetch('/accounting/settings'),
      ]);
      this.accounts        = await accR.json();
      this.financialYears  = await fyR.json();
      const settings       = await settR.json();
      Object.keys(this.form).forEach(k => {
        this.form[k] = settings[k] ? String(settings[k]) : '';
      });
      this.loading = false;
    },
    async saveSettings() {
      this.saving = true;
      this.saveSuccess = false;
      try {
        const payload = {};
        Object.keys(this.form).forEach(k => { if (this.form[k]) payload[k] = parseInt(this.form[k]); });
        const r = await apiFetch('/accounting/settings', { method: 'POST', body: JSON.stringify(payload) });
        if (r.ok) {
          this.saveSuccess = true;
          toast('Settings saved.', 'success');
          setTimeout(() => this.saveSuccess = false, 3000);
        }
      } catch (e) {
        toast(e.message ?? 'Failed to save settings.', 'error');
      } finally { this.saving = false; }
    },
    async createFinancialYear() {
      if (!this.yearForm.name || !this.yearForm.start_date || !this.yearForm.end_date) return;
      this.savingYear = true;
      try {
        const r = await apiFetch('/accounting/financial-years', { method: 'POST', body: JSON.stringify(this.yearForm) });
        if (r.ok) {
          const yr = await r.json();
          this.financialYears.unshift(yr);
          this.financialYears = this.financialYears.map(fy => ({ ...fy, status: fy.id === yr.id ? 'active' : 'closed' }));
          this.showNewYearModal = false;
          this.yearForm = { name: '', start_date: '', end_date: '' };
          toast('Financial year created.', 'success');
        }
      } catch (e) {
        toast(e.message ?? 'Failed to create financial year.', 'error');
      } finally { this.savingYear = false; }
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/accounting/settings.blade.php ENDPATH**/ ?>