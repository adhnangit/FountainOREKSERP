<?php $__env->startSection('title', 'Edit Expense'); ?>
<?php $__env->startSection('page-title', 'Edit Expense'); ?>
<?php $__env->startSection('page-desc', 'Update expense details and payment information'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="expenseEditPage()" x-init="init()" class="px-6 pb-12">

<template x-if="loading">
  <div class="flex items-center justify-center py-24 text-gray-400 gap-3">
    <svg class="animate-spin w-6 h-6" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    Loading expense…
  </div>
</template>

<template x-if="!loading">
<form @submit.prevent="submit()">

  
  <template x-if="wasBounced">
    <div class="mb-5 flex items-start gap-3 px-4 py-3.5 rounded-xl border border-amber-200 bg-amber-50">
      <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
      <div class="text-sm">
        <p class="font-bold text-amber-800">Cheque payment was reversed</p>
        <p class="text-amber-700 mt-0.5">The original cheque bounced. Select a new payment method to re-pay this expense.</p>
      </div>
    </div>
  </template>

  
  <div class="flex items-center justify-between mb-5">
    <a href="<?php echo e(url('/expenses')); ?>" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-800">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
      Back to Expenses
    </a>
    <span class="text-xs font-mono text-gray-400" x-text="expenseNumber"></span>
  </div>

<div class="flex flex-col lg:flex-row gap-6">

  
  <div class="w-full lg:flex-[62] min-w-0 space-y-5">

    <div class="card overflow-visible">
      <div class="flex items-center gap-3 px-6 py-4 rounded-t-xl"
           style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%)">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
             style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
          <svg style="width:18px;height:18px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-white">Expense Details</h3>
          <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">Date, account, description and amount</p>
        </div>
      </div>
      <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

        <div>
          <label class="label">Expense Date <span class="text-red-500">*</span></label>
          <input type="date" x-model="form.expense_date" class="input" required />
        </div>

        <div>
          <label class="label">Expense Account <span class="text-red-500">*</span></label>
          <div class="search-dd" x-data="{ open: false, q: '' }" @click.away="open = false" @keydown.escape="open = false">
            <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.expAcctEdit?.focus())"
                    class="input w-full text-left flex items-center justify-between gap-2">
              <span class="truncate" :class="form.account_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                    x-text="form.account_id ? (expenseAccounts.find(a => a.id == form.account_id) ? '[' + expenseAccounts.find(a => a.id == form.account_id).code + '] ' + expenseAccounts.find(a => a.id == form.account_id).name : '—') : 'Select expense account…'"></span>
              <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu">
              <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                <input x-ref="expAcctEdit" x-model="q" type="text" placeholder="Search code or name…" class="input text-sm w-full py-1.5" @keydown.stop />
              </div>
              <div class="max-h-52 overflow-y-auto py-1">
                <template x-for="a in expenseAccounts.filter(a => !q || a.name.toLowerCase().includes(q.toLowerCase()) || a.code.toLowerCase().includes(q.toLowerCase()))" :key="a.id">
                  <button type="button" @click="form.account_id = a.id; open = false; q = ''"
                          class="search-dd-item" :class="form.account_id == a.id ? 'active' : ''">
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate flex-1" x-text="'[' + a.code + '] ' + a.name"></span>
                  </button>
                </template>
                <div x-show="expenseAccounts.filter(a => !q || a.name.toLowerCase().includes(q.toLowerCase()) || a.code.toLowerCase().includes(q.toLowerCase())).length === 0"
                     class="px-4 py-3 text-xs text-gray-400 text-center">No accounts found</div>
              </div>
            </div>
          </div>
        </div>

        <div class="sm:col-span-2">
          <label class="label">Description <span class="text-red-500">*</span></label>
          <input type="text" x-model="form.description" class="input" placeholder="Brief description of expense" required />
        </div>

        <div class="sm:col-span-2">
          <label class="label">Amount (LKR) <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-medium pointer-events-none">Rs.</span>
            <input type="number" x-model.number="form.amount" class="input pl-10 text-lg font-bold tabular-nums" min="0" step="0.01" placeholder="0.00" required />
          </div>
          <template x-if="form.amount > 0">
            <p class="mt-1.5 text-xs font-medium" style="color:#1B3EB6"
               x-text="'Rs. ' + Number(form.amount).toLocaleString('en-LK', {minimumFractionDigits:2})"></p>
          </template>
        </div>

        <div class="sm:col-span-2">
          <label class="label">Notes</label>
          <textarea x-model="form.notes" rows="2" class="input resize-none" placeholder="Additional notes…"></textarea>
        </div>

      </div>
    </div>

  </div>

  
  <div class="w-full lg:flex-[38]">
  <div class="lg:sticky lg:top-6 space-y-5">

    
    <div class="card">
      <div class="flex items-center gap-3 px-5 py-4 rounded-t-xl"
           style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%)">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
             style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
          <svg style="width:16px;height:16px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-white">Payment Details</h3>
          <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">How this expense is paid</p>
        </div>
      </div>
      <div class="px-5 py-4 space-y-4">

        <div>
          <label class="label">Payment Method</label>
          <div class="grid grid-cols-2 gap-2">
            <template x-for="m in paymentMethods" :key="m.v">
              <button type="button"
                      @click="form.payment_method = m.v; onMethodChange()"
                      class="flex items-center gap-2 px-3 py-2.5 rounded-lg border text-sm font-medium transition-all"
                      :style="form.payment_method === m.v
                        ? 'background:#eef2ff;border-color:#1B3EB6;color:#1B3EB6'
                        : 'background:transparent;border-color:#e5e7eb;color:#6b7280'">
                <span x-text="m.icon" class="text-base leading-none"></span>
                <span x-text="m.l" class="truncate text-xs"></span>
              </button>
            </template>
          </div>
        </div>

        
        <template x-if="form.payment_method === 'cash'">
          <div>
            <label class="label">Cash Account <span class="text-red-500">*</span></label>
            <select x-model="form.payment_account_id" class="input" required>
              <option value="">Select cash account…</option>
              <template x-for="a in cashAccounts" :key="a.id">
                <option :value="a.id" x-text="a.name"></option>
              </template>
            </select>
          </div>
        </template>

        
        <template x-if="form.payment_method === 'bank_transfer'">
          <div class="space-y-3">
            <div>
              <label class="label">Bank Account <span class="text-red-500">*</span></label>
              <select x-model="form.payment_account_id" class="input" required>
                <option value="">Select bank account…</option>
                <template x-for="a in bankAccounts" :key="a.id">
                  <option :value="a.id" x-text="a.name"></option>
                </template>
              </select>
            </div>
            <div>
              <label class="label">Reference / Transfer Number</label>
              <input type="text" x-model="form.reference_number" class="input font-mono" placeholder="Bank ref or transfer ID" />
            </div>
          </div>
        </template>

        
        <template x-if="form.payment_method === 'cheque'">
          <div class="space-y-3">
            <div>
              <label class="label">Cheque Type</label>
              <div class="flex rounded-lg overflow-hidden border border-gray-200">
                <button type="button"
                        @click="form.cheque_type = 'party'; onChequeTypeChange()"
                        class="flex-1 py-2 text-xs font-semibold transition-all"
                        :style="form.cheque_type === 'party' ? 'background:#1B3EB6;color:#fff' : 'background:#f9fafb;color:#6b7280'">
                  Party Cheque
                </button>
                <button type="button"
                        @click="form.cheque_type = 'own'; onChequeTypeChange()"
                        class="flex-1 py-2 text-xs font-semibold transition-all border-l border-gray-200"
                        :style="form.cheque_type === 'own' ? 'background:#1B3EB6;color:#fff' : 'background:#f9fafb;color:#6b7280'">
                  Own Cheque
                </button>
              </div>
            </div>

            
            <template x-if="form.cheque_type === 'party'">
              <div class="space-y-3">
                <div>
                  <label class="label">Select Cheque <span class="text-red-500">*</span></label>
                  <select x-model="form.cheque_id" @change="onPartyChequeSelect()" class="input">
                    <option value="">Select in-hand cheque…</option>
                    <template x-for="c in partyCheques" :key="c.id">
                      <option :value="c.id"
                              x-text="c.cheque_number + ' – ' + c.bank_name + ' – Rs.' + Number(c.amount).toLocaleString()"></option>
                    </template>
                  </select>
                  <p x-show="partyCheques.length === 0 && !loadingCheques"
                     class="text-xs text-gray-400 mt-1">No in-hand received cheques available.</p>
                </div>
                <div class="rounded-lg px-3 py-2.5 text-xs"
                     :class="chequeInHandAccount ? 'bg-green-50 border border-green-200' : 'bg-amber-50 border border-amber-200'">
                  <template x-if="chequeInHandAccount">
                    <div>
                      <span class="text-gray-500">Will credit: </span>
                      <span class="font-semibold text-green-700" x-text="chequeInHandAccount.name"></span>
                    </div>
                  </template>
                  <template x-if="!chequeInHandAccount">
                    <div class="text-amber-700">
                      No "Cheque in Hand" account found. Add one in
                      <a href="<?php echo e(url('/accounting/chart-of-accounts')); ?>" class="underline font-semibold">Chart of Accounts</a>.
                    </div>
                  </template>
                </div>
              </div>
            </template>

            
            <template x-if="form.cheque_type === 'own'">
              <div class="space-y-3">
                <div>
                  <label class="label">Bank Account (Drawn On) <span class="text-red-500">*</span></label>
                  <select x-model="form.payment_account_id" class="input" required>
                    <option value="">Select bank account…</option>
                    <template x-for="a in bankAccounts" :key="a.id">
                      <option :value="a.id" x-text="a.name"></option>
                    </template>
                  </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="label">Cheque Number <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.cheque_number" class="input font-mono" placeholder="e.g. 001234" />
                  </div>
                  <div>
                    <label class="label">Cheque Date</label>
                    <input type="date" x-model="form.cheque_date" class="input" />
                  </div>
                </div>
                <div>
                  <label class="label">Bank Name</label>
                  <div x-data="{bq:'',bOpen:false}" @click.outside="bOpen=false" class="relative">
                    <input type="text" :value="form.bank_name"
                      @input="form.bank_name=$event.target.value;bq=$event.target.value;bOpen=true"
                      @focus="bq=form.bank_name||'';bOpen=true" @keydown.escape="bOpen=false"
                      class="input" placeholder="Search bank…" autocomplete="off" />
                    <ul x-show="bOpen" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-44 overflow-y-auto">
                      <template x-for="b in banks.filter(b=>b.name.toLowerCase().includes(bq.toLowerCase()))" :key="b.id">
                        <li @mousedown.prevent="form.bank_name=b.name;bq=b.name;bOpen=false"
                            :class="form.bank_name===b.name?'bg-indigo-50 text-indigo-700 font-medium':'hover:bg-gray-50 text-gray-700'"
                            class="px-3 py-2 text-sm cursor-pointer" x-text="b.name"></li>
                      </template>
                      <li x-show="!banks.filter(b=>b.name.toLowerCase().includes(bq.toLowerCase())).length"
                          class="px-3 py-2 text-sm text-gray-400 text-center">No banks found</li>
                    </ul>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </template>

        
        <template x-if="form.payment_method === 'credit_card'">
          <div class="space-y-3">
            <div>
              <label class="label">Payment Account <span class="text-red-500">*</span></label>
              <select x-model="form.payment_account_id" class="input" required>
                <option value="">Select account…</option>
                <template x-for="a in bankAccounts.concat(cashAccounts)" :key="a.id">
                  <option :value="a.id" x-text="a.name"></option>
                </template>
              </select>
            </div>
            <div>
              <label class="label">Card / Reference Number</label>
              <input type="text" x-model="form.reference_number" class="input font-mono" placeholder="Transaction ref" />
            </div>
          </div>
        </template>

        
        <div>
          <label class="label">Approved By</label>
          <input type="text" x-model="form.approved_by_name" class="input" placeholder="Manager name (optional)" />
        </div>

      </div>
    </div>

    
    <div x-show="form.amount > 0 && form.account_id && form.payment_account_id"
         class="card p-4 border border-indigo-100 bg-indigo-50/50">
      <div class="text-xs font-bold uppercase tracking-wider text-indigo-500 mb-3">Journal Preview</div>
      <div class="space-y-1.5 text-xs">
        <div class="flex justify-between">
          <span class="text-gray-500">DR</span>
          <span class="font-medium text-gray-800" x-text="expenseAccounts.find(a=>a.id==form.account_id)?.name ?? '—'"></span>
          <span class="font-bold text-indigo-700 tabular-nums"
                x-text="'Rs.' + Number(form.amount||0).toLocaleString('en-LK',{minimumFractionDigits:2})"></span>
        </div>
        <div class="flex justify-between">
          <span class="text-gray-500">CR</span>
          <span class="font-medium text-gray-800"
                x-text="allAccounts.find(a=>a.id==form.payment_account_id)?.name ?? '—'"></span>
          <span class="font-bold text-indigo-700 tabular-nums"
                x-text="'Rs.' + Number(form.amount||0).toLocaleString('en-LK',{minimumFractionDigits:2})"></span>
        </div>
      </div>
    </div>

    
    <div x-show="form.amount > 0"
         class="rounded-xl px-5 py-4 flex items-center justify-between"
         style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
      <div>
        <div class="text-xs font-semibold uppercase tracking-wider mb-0.5" style="color:rgba(255,255,255,0.7)">Expense Amount</div>
        <div class="text-xs" style="color:rgba(255,255,255,0.55)"
             x-text="expenseAccounts.find(a=>a.id==form.account_id)?.name || 'No account selected'"></div>
      </div>
      <div class="text-right">
        <div class="text-2xl font-black tabular-nums text-white"
             x-text="'Rs. ' + Number(form.amount || 0).toLocaleString()"></div>
        <div class="text-xs mt-0.5" style="color:rgba(255,255,255,0.55)"
             x-text="form.expense_date || 'No date'"></div>
      </div>
    </div>

    
    <div class="flex gap-3">
      <a :href="BASE + '/expenses/' + expenseId" class="btn-secondary flex-1 text-center">Cancel</a>
      <button type="submit" :disabled="submitting" class="btn-primary flex-1">
        <span x-text="submitting ? 'Saving…' : 'Save Changes'"></span>
      </button>
    </div>

  </div>
  </div>

</div>
</form>
</template>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function expenseEditPage() {
    const expenseId = location.pathname.split('/').filter(Boolean).slice(-2, -1)[0];
    return {
        expenseId,
        expenseNumber: '',
        wasBounced: false,
        allAccounts: [],
        expenseAccounts: [],
        bankAccounts: [],
        cashAccounts: [],
        chequeInHandAccount: null,
        partyCheques: [],
        banks: [],
        loadingAccounts: true,
        loadingCheques: false,
        loading: true,
        submitting: false,
        paymentMethods: [
            {v:'cash',          l:'Cash',          icon:'💵'},
            {v:'bank_transfer', l:'Bank Transfer',  icon:'🏦'},
            {v:'cheque',        l:'Cheque',         icon:'📄'},
            {v:'credit_card',   l:'Card',           icon:'💳'},
        ],
        form: {
            expense_date:       '',
            account_id:         '',
            description:        '',
            amount:             0,
            notes:              '',
            payment_method:     'cash',
            payment_account_id: '',
            reference_number:   '',
            cheque_type:        'own',
            cheque_id:          '',
            cheque_number:      '',
            bank_name:          '',
            cheque_date:        '',
            approved_by_name:   '',
        },

        async init() {
            try {
                // Load accounts and expense in parallel
                const [accRes, expRes] = await Promise.all([
                    apiFetch('/accounting/accounts'),
                    apiFetch('/expenses/' + expenseId),
                ]);

                if (accRes) {
                    const accData = await accRes.json();
                    this.allAccounts        = Array.isArray(accData) ? accData : (accData.data ?? []);
                    this.expenseAccounts    = this.allAccounts.filter(a => a.type === 'expense');
                    this.bankAccounts       = this.allAccounts.filter(a => a.is_bank_account);
                    this.cashAccounts       = this.allAccounts.filter(a => a.is_cash_account);
                    this.chequeInHandAccount = this.allAccounts.find(
                        a => a.name.toLowerCase().includes('cheque')
                    ) || null;
                }

                if (expRes) {
                    const exp = await expRes.json();

                    if (!['draft', 'pending'].includes(exp.status)) {
                        toast('This expense cannot be edited', 'error');
                        window.location.href = BASE + '/expenses/' + expenseId;
                        return;
                    }

                    this.expenseNumber = exp.expense_number ?? '';
                    this.wasBounced    = (exp.notes ?? '').includes('Payment reversed: cheque');

                    const method = exp.payment_method ?? 'cash';
                    const chequeType = exp.cheque_id ? 'party' : (exp.cheque_number ? 'own' : 'own');

                    this.form = {
                        expense_date:       (exp.expense_date ?? '').slice(0, 10),
                        account_id:         exp.account_id ?? '',
                        description:        exp.description ?? '',
                        amount:             parseFloat(exp.amount) || 0,
                        notes:              exp.notes ?? '',
                        payment_method:     method,
                        payment_account_id: exp.payment_account_id ?? '',
                        reference_number:   exp.reference_number ?? '',
                        cheque_type:        chequeType,
                        cheque_id:          exp.cheque_id ?? '',
                        cheque_number:      exp.cheque_number ?? '',
                        bank_name:          exp.bank_name ?? '',
                        cheque_date:        (exp.cheque_date ?? '').slice(0, 10),
                        approved_by_name:   exp.approved_by?.name ?? '',
                    };
                }

                this.banks = await loadBanks();
            } catch (e) {
                toast('Failed to load expense', 'error');
            } finally {
                this.loadingAccounts = false;
                this.loading = false;
            }

            this.loadPartyCheques();
        },

        async loadPartyCheques() {
            this.loadingCheques = true;
            try {
                // Load in-hand cheques; also include the currently assigned one if set
                const r = await apiFetch('/cheques?direction=received&status=in_hand&per_page=100');
                if (!r) return;
                const data = await r.json();
                this.partyCheques = data.data ?? data ?? [];

                // If existing cheque_id isn't in the list (was transferred), fetch it separately
                if (this.form.cheque_id && !this.partyCheques.find(c => c.id == this.form.cheque_id)) {
                    const cr = await apiFetch('/cheques/' + this.form.cheque_id);
                    if (cr && cr.ok) {
                        const c = await cr.json();
                        this.partyCheques = [c, ...this.partyCheques];
                    }
                }
            } catch (e) {
                this.partyCheques = [];
            } finally {
                this.loadingCheques = false;
            }
        },

        onMethodChange() {
            this.form.payment_account_id = '';
            this.form.cheque_type        = 'own';
            this.form.cheque_id          = '';
            this.form.cheque_number      = '';
            this.form.bank_name          = '';
            this.form.cheque_date        = '';
            this.form.reference_number   = '';
            if (this.form.payment_method === 'cash') {
                const cash = this.cashAccounts.find(a => !a.name.toLowerCase().includes('cheque'));
                this.form.payment_account_id = (cash ?? this.cashAccounts[0])?.id ?? '';
            }
            if (this.form.payment_method === 'bank_transfer' && this.bankAccounts.length > 0) {
                this.form.payment_account_id = this.bankAccounts[0].id;
            }
        },

        onChequeTypeChange() {
            this.form.payment_account_id = '';
            this.form.cheque_id          = '';
            this.form.cheque_number      = '';
            this.form.bank_name          = '';
            this.form.cheque_date        = '';
            if (this.form.cheque_type === 'party' && this.chequeInHandAccount) {
                this.form.payment_account_id = this.chequeInHandAccount.id;
            }
            if (this.form.cheque_type === 'own' && this.bankAccounts.length > 0) {
                this.form.payment_account_id = this.bankAccounts[0].id;
            }
        },

        onPartyChequeSelect() {
            const c = this.partyCheques.find(c => c.id == this.form.cheque_id);
            if (c) {
                this.form.cheque_number = c.cheque_number;
                this.form.bank_name     = c.bank_name;
                this.form.cheque_date   = c.cheque_date ? c.cheque_date.slice(0, 10) : '';
            }
        },

        async submit() {
            if (!this.form.account_id)         { toast('Please select an expense account', 'error'); return; }
            if (!this.form.payment_account_id) { toast('Please select a payment account', 'error'); return; }

            this.submitting = true;
            try {
                const r = await apiFetch('/expenses/' + expenseId, {
                    method: 'PUT',
                    body: JSON.stringify(this.form),
                });
                if (!r) return;
                if (!r.ok) {
                    const err = await r.json();
                    toast(err.message ?? Object.values(err.errors ?? {})[0]?.[0] ?? 'Failed to save', 'error');
                    return;
                }
                toast('Expense updated successfully', 'success');
                window.location.href = BASE + '/expenses/' + expenseId;
            } catch (e) {
                toast(e.message ?? 'Failed to save expense', 'error');
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\expenses\edit.blade.php ENDPATH**/ ?>