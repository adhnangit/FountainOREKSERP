<?php $__env->startSection('title', 'Bulk Payment'); ?>
<?php $__env->startSection('page-title', 'Bulk Payment'); ?>
<?php $__env->startSection('page-desc', 'Pay several outstanding invoices for this customer in one payment'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="bulkPaymentPage()" class="px-6 pb-12">

  <div x-show="loading" class="flex items-center justify-center py-24 text-gray-400">
    <svg class="animate-spin w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    Loading…
  </div>

  <div x-show="!loading" x-cloak class="flex flex-col lg:flex-row gap-6">

    
    <div class="w-full lg:flex-[60] min-w-0">
      <div class="card overflow-hidden">
        <div class="px-6 py-4 flex items-center justify-between" style="background:linear-gradient(135deg,#0f172a,#1e3a5f,#2563eb)">
          <div>
            <h3 class="text-sm font-bold text-white">Outstanding Invoices</h3>
            <p class="text-xs" style="color:rgba(255,255,255,0.65)" x-text="customer?.name || ''"></p>
          </div>
          <span class="text-xs font-semibold text-white" x-text="selectedInvoices.length + ' selected'"></span>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700 max-h-[70vh] overflow-y-auto">
          <template x-for="inv in invoices" :key="inv.id">
            <label class="flex items-center gap-3 px-5 py-3 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/30"
                   :class="preview[inv.id] ? 'bg-blue-50/40 dark:bg-blue-900/10' : ''">
              <input type="checkbox" :value="inv.id" x-model="selectedIds" @change="calcPreview()" class="w-4 h-4 rounded accent-blue-600 flex-shrink-0" />
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="inv.invoice_number"></span>
                  <span class="text-[11px] text-gray-400" x-text="fmtDate(inv.invoice_date)"></span>
                </div>
                <div class="text-xs text-gray-400">Balance: <span class="font-semibold text-gray-600 dark:text-gray-300" x-text="fmtMoney(inv.balance_due)"></span></div>
              </div>
              <template x-if="preview[inv.id]">
                <div class="text-right flex-shrink-0">
                  <div class="text-xs font-bold" :class="preview[inv.id].fully ? 'text-green-600' : 'text-orange-500'" x-text="fmtMoney(preview[inv.id].applied) + ' applied'"></div>
                  <div class="text-[11px] text-gray-400" x-text="preview[inv.id].fully ? 'Fully paid' : ('Rs. ' + preview[inv.id].remaining.toLocaleString() + ' still due')"></div>
                </div>
              </template>
            </label>
          </template>
          <div x-show="!invoices.length" class="px-5 py-10 text-center text-sm text-gray-400">No outstanding invoices for this customer.</div>
        </div>
      </div>
    </div>

    
    <div class="w-full lg:flex-[40]">
    <div class="lg:sticky lg:top-6 space-y-5">
      <div class="card overflow-hidden">
        <div class="px-5 py-4" style="background:linear-gradient(135deg,#0f172a,#1e3a5f,#2563eb)">
          <h3 class="text-sm font-bold text-white">Payment Details</h3>
        </div>
        <div class="px-5 py-4 space-y-4">

          <template x-if="parseFloat(customer?.credit_balance ?? 0) > 0">
            <div class="rounded-xl border border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700 p-3">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold text-blue-800 dark:text-blue-200">Customer has credit</p>
                  <p class="text-xs text-blue-600 dark:text-blue-300 mt-0.5" x-text="fmtMoney(customer.credit_balance) + ' available'"></p>
                </div>
                <label class="flex items-center gap-2 cursor-pointer flex-shrink-0">
                  <span class="text-xs font-medium text-blue-700 dark:text-blue-300">Apply</span>
                  <input type="checkbox" x-model="form.use_credit" @change="calcPreview()" class="w-4 h-4 accent-blue-600" />
                </label>
              </div>
            </div>
          </template>

          <div>
            <label class="label">Amount <span class="text-red-500">*</span></label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">Rs.</span>
              <input type="number" x-model.number="form.amount" @input="calcPreview()" min="0" step="0.01" class="input pl-9 tabular-nums" placeholder="0.00" />
            </div>
            <button type="button" class="text-xs text-indigo-600 hover:underline mt-1" @click="form.amount = totalSelectedBalance(); calcPreview()">Pay full selected balance (<span x-text="fmtMoney(totalSelectedBalance())"></span>)</button>
          </div>

          <div>
            <label class="label">Payment Method</label>
            <div class="grid grid-cols-3 gap-2">
              <template x-for="m in payMethods" :key="m.v">
                <button type="button" @click="form.payment_method = m.v; form.account_id = m.v === 'cash' ? (cashAccounts[0]?.id ?? null) : (bankAccounts[0]?.id ?? null)"
                        :style="form.payment_method === m.v ? `background:${m.bg};border:2px solid ${m.border};color:${m.color}` : 'background:#f9fafb;border:2px solid #e5e7eb;color:#6b7280'"
                        class="py-2 rounded-xl text-xs font-bold flex flex-col items-center gap-1 transition-all">
                  <span x-text="m.icon" class="text-base"></span>
                  <span x-text="m.label"></span>
                </button>
              </template>
            </div>
          </div>

          <template x-if="form.payment_method === 'cash'">
            <div>
              <label class="label">Cash Account <span class="text-red-500">*</span></label>
              <select x-model="form.account_id" class="input">
                <option :value="null">— Select —</option>
                <template x-for="a in cashAccounts" :key="a.id">
                  <option :value="a.id" x-text="a.name"></option>
                </template>
              </select>
            </div>
          </template>

          <template x-if="form.payment_method === 'bank_transfer'">
            <div>
              <label class="label">Bank Account <span class="text-red-500">*</span></label>
              <select x-model="form.account_id" class="input">
                <option :value="null">— Select —</option>
                <template x-for="a in bankAccounts" :key="a.id">
                  <option :value="a.id" x-text="a.name"></option>
                </template>
              </select>
            </div>
          </template>

          <template x-if="form.payment_method === 'cheque'">
            <div class="space-y-3">
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="label">Cheque Number <span class="text-red-500">*</span></label>
                  <input x-model="form.cheque_number" type="text" class="input" placeholder="e.g. 001234" />
                </div>
                <div>
                  <label class="label">Cheque Date <span class="text-red-500">*</span></label>
                  <input x-model="form.cheque_date" type="date" class="input" />
                </div>
              </div>
              <div>
                <label class="label">Bank Name <span class="text-red-500">*</span></label>
                <div x-data="{bq:'',bOpen:false}" @click.outside="bOpen=false" class="relative">
                  <input type="text" :value="form.bank_name"
                    @input="form.bank_name=$event.target.value;bq=$event.target.value;bOpen=true"
                    @focus="bq=form.bank_name||'';bOpen=true" @keydown.escape="bOpen=false"
                    class="input" placeholder="Search bank…" autocomplete="off" />
                  <ul x-show="bOpen" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 rounded-xl shadow-xl max-h-44 overflow-y-auto">
                    <template x-for="b in banks.filter(b=>b.name.toLowerCase().includes(bq.toLowerCase()))" :key="b.id">
                      <li @mousedown.prevent="form.bank_name=b.name;bq=b.name;bOpen=false"
                          :class="form.bank_name===b.name?'bg-indigo-50 text-indigo-700 font-medium':'hover:bg-gray-50 text-gray-700'"
                          class="px-3 py-2 text-sm cursor-pointer" x-text="b.name"></li>
                    </template>
                    <li x-show="!banks.filter(b=>b.name.toLowerCase().includes(bq.toLowerCase())).length" class="px-3 py-2 text-sm text-gray-400 text-center">No banks found</li>
                  </ul>
                </div>
              </div>
            </div>
          </template>

          <div>
            <label class="label">Payment Date <span class="text-red-500">*</span></label>
            <input type="date" x-model="form.payment_date" class="input" />
          </div>

          <div>
            <label class="label">Reference</label>
            <input type="text" x-model="form.reference_number" class="input" placeholder="Optional" />
          </div>

        </div>
      </div>

      <button type="button" @click="submit()" :disabled="submitting"
              class="btn-primary w-full flex items-center justify-center gap-2 py-3">
        <span x-text="submitting ? 'Processing…' : 'Record Bulk Payment'"></span>
      </button>
      <a :href="BASE + '/customers/' + id" class="block text-center text-sm text-gray-500 hover:underline">Cancel</a>

    </div>
    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function bulkPaymentPage() {
  const id = window.location.pathname.split('/').filter(Boolean).slice(-2)[0];
  return {
    id, loading: true, submitting: false,
    customer: null,
    invoices: [],
    selectedIds: [],
    cashAccounts: [], bankAccounts: [], banks: [],
    preview: {},
    payMethods: [
      { v:'cash',          label:'Cash',   icon:'💵', bg:'#f0fdf4', border:'#22c55e', color:'#15803d' },
      { v:'bank_transfer', label:'Bank',   icon:'🏦', bg:'#faf5ff', border:'#a855f7', color:'#7e22ce' },
      { v:'cheque',        label:'Cheque', icon:'📄', bg:'#fffbeb', border:'#f59e0b', color:'#b45309' },
    ],
    form: {
      amount: 0, payment_method: 'cash', payment_date: new Date().toISOString().slice(0,10),
      reference_number: '', account_id: null, use_credit: false,
      cheque_number: '', bank_name: '', cheque_date: '',
    },

    async init() {
      try {
        const [custR, invR, accR] = await Promise.all([
          apiFetch('/customers/' + id).then(r => r.json()),
          apiFetch('/invoices?customer_id=' + id + '&per_page=999').then(r => r.json()),
          apiFetch('/accounting/accounts').then(r => r.json()),
        ]);
        this.customer = custR.data ?? custR;
        const allInv = invR.data ?? invR ?? [];
        this.invoices = allInv
          .filter(i => parseFloat(i.balance_due||0) > 0 && ['confirmed','partially_paid','overdue'].includes(i.status))
          .sort((a,b) => new Date(a.invoice_date) - new Date(b.invoice_date));
        this.selectedIds = this.invoices.map(i => i.id);
        const accounts = Array.isArray(accR) ? accR : (accR.data ?? []);
        this.cashAccounts = accounts.filter(a => a.is_cash_account);
        this.bankAccounts = accounts.filter(a => a.is_bank_account);
        this.form.account_id = this.cashAccounts[0]?.id ?? null;
        this.banks = await loadBanks();
        this.calcPreview();
      } catch (e) {
        toast('Failed to load data', 'error');
      } finally {
        this.loading = false;
      }
    },

    get selectedInvoices() {
      return this.invoices.filter(i => this.selectedIds.includes(i.id));
    },

    totalSelectedBalance() {
      return this.selectedInvoices.reduce((s, i) => s + parseFloat(i.balance_due||0), 0);
    },

    calcPreview() {
      let cash = parseFloat(this.form.amount || 0);
      let credit = this.form.use_credit ? parseFloat(this.customer?.credit_balance || 0) : 0;
      const preview = {};
      for (const inv of this.selectedInvoices) {
        if (cash <= 0 && credit <= 0) break;
        let balance = parseFloat(inv.balance_due || 0);
        const creditForThis = Math.min(credit, balance);
        credit -= creditForThis; balance -= creditForThis;
        const cashForThis = Math.min(cash, balance);
        cash -= cashForThis;
        const applied = creditForThis + cashForThis;
        if (applied <= 0) continue;
        preview[inv.id] = { applied, remaining: Math.max(0, parseFloat(inv.balance_due) - applied), fully: applied >= parseFloat(inv.balance_due) - 0.001 };
      }
      this.preview = preview;
    },

    fmtMoney(v) { return 'Rs. ' + parseFloat(v||0).toLocaleString('en-LK', { minimumFractionDigits: 2 }); },
    fmtDate(d) { return d ? new Date(d).toLocaleDateString('en-LK', { day:'2-digit', month:'short', year:'numeric' }) : ''; },

    async submit() {
      if (!this.selectedIds.length) { toast('Select at least one invoice', 'error'); return; }
      if ((!this.form.amount || this.form.amount <= 0) && !this.form.use_credit) { toast('Enter a payment amount', 'error'); return; }
      if ((this.form.payment_method === 'cash' || this.form.payment_method === 'bank_transfer') && this.form.amount > 0 && !this.form.account_id) {
        toast('Select an account', 'error'); return;
      }
      if (this.form.payment_method === 'cheque' && this.form.amount > 0) {
        if (!this.form.cheque_number) { toast('Enter cheque number', 'error'); return; }
        if (!this.form.bank_name)     { toast('Enter bank name', 'error'); return; }
        if (!this.form.cheque_date)   { toast('Enter cheque date', 'error'); return; }
      }
      this.submitting = true;
      try {
        const r = await apiFetch('/customers/' + id + '/bulk-payment', {
          method: 'POST',
          body: JSON.stringify({
            amount: this.form.amount,
            payment_method: this.form.payment_method,
            payment_date: this.form.payment_date,
            reference_number: this.form.reference_number || null,
            account_id: this.form.account_id ? parseInt(this.form.account_id) : null,
            use_credit: this.form.use_credit,
            cheque_number: this.form.cheque_number || null,
            bank_name: this.form.bank_name || null,
            cheque_date: this.form.cheque_date || null,
            invoice_ids: this.selectedIds,
          }),
        });
        const d = await r.json();
        if (r.ok) {
          toast(d.message || 'Bulk payment recorded', 'success');
          window.location.href = BASE + '/customers/' + id;
        } else {
          toast(d.message || 'Failed to record payment', 'error');
        }
      } catch (e) {
        toast('Failed to record payment', 'error');
      } finally {
        this.submitting = false;
      }
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\customers\bulk-payment.blade.php ENDPATH**/ ?>