<?php $__env->startSection('title', 'New Sales Return'); ?>
<?php $__env->startSection('page-title', 'New Sales Return'); ?>
<?php $__env->startSection('page-desc', 'Issue a credit note for returned goods'); ?>
<?php $sec = 'sales'; ?>

<?php $__env->startSection('content'); ?>
<div x-data="salesReturnCreate()" x-init="init()" x-cloak>
  <div class="max-w-3xl mx-auto space-y-5">

    <div class="flex items-center gap-3">
      <a href="<?php echo e(url('/sales-returns')); ?>" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
      </a>
      <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">New Sales Return</h2>
    </div>

    
    <div class="card p-5 space-y-3">
      <label class="block text-xs font-semibold text-gray-500">Original Invoice <span class="text-red-500">*</span></label>

      <template x-if="!inv">
        <div class="relative">
          <input x-model="invSearch" @input.debounce.400ms="searchInvoices()" @focus="invOpen = invResults.length > 0"
                 type="text" placeholder="Search by invoice # or customer…" class="input w-full" autocomplete="off" />
          <div x-show="searching" class="absolute right-3 top-2.5">
            <svg class="animate-spin w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
          </div>
          <ul x-show="invOpen" @click.outside="invOpen = false"
              class="absolute z-30 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xl max-h-64 overflow-y-auto">
            <template x-for="i in invResults" :key="i.id">
              <li @click="selectInvoice(i.id)"
                  class="px-4 py-2.5 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-between gap-3">
                <div class="min-w-0">
                  <div class="text-sm font-medium text-gray-800 dark:text-gray-100" x-text="i.invoice_number"></div>
                  <div class="text-xs text-gray-400 truncate" x-text="(i.customer?.name ?? '') + ' · ' + fmtDate(i.invoice_date)"></div>
                </div>
                <div class="text-sm font-semibold tabular-nums flex-shrink-0" x-text="fmtMoney(i.total)"></div>
              </li>
            </template>
            <li x-show="!invResults.length && !searching" class="px-4 py-3 text-sm text-gray-400 text-center">No returnable invoices found</li>
          </ul>
        </div>
      </template>

      <template x-if="inv">
        <div class="flex items-center justify-between rounded-xl px-4 py-3" style="background:#f0f4ff;border:1px solid #e0e7ff">
          <div>
            <div class="text-sm font-bold" style="color:#1B3EB6" x-text="inv.invoice_number"></div>
            <div class="text-xs text-gray-500 mt-0.5">
              <span x-text="customer?.name ?? ''"></span> ·
              Total <span class="font-semibold" x-text="fmtMoney(inv.total)"></span> ·
              Balance due <span class="font-semibold" x-text="fmtMoney(inv.balance_due)"></span>
            </div>
          </div>
          <button @click="clearInvoice()" class="text-xs font-semibold text-red-500 hover:text-red-700">Change</button>
        </div>
      </template>
    </div>

    
    <div x-show="inv" class="card p-0 overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Items to Return</h3>
        <p class="text-xs text-gray-400 mt-0.5">Enter the quantity being returned for each product.</p>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="table-hd">Product</th>
              <th class="table-hd text-right">Sold</th>
              <th class="table-hd text-right">Already Returned</th>
              <th class="table-hd text-right">Returnable</th>
              <th class="table-hd text-right" style="width:130px">Return Qty</th>
              <th class="table-hd text-right">Credit</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <template x-for="(it, idx) in items" :key="it.product_id">
              <tr :class="it.returnable_quantity <= 0 ? 'opacity-40' : ''">
                <td class="table-td font-medium" x-text="it.product_name"></td>
                <td class="table-td text-right tabular-nums" x-text="it.sold_quantity"></td>
                <td class="table-td text-right tabular-nums" x-text="it.returned_quantity"></td>
                <td class="table-td text-right tabular-nums font-semibold" x-text="it.returnable_quantity"></td>
                <td class="table-td text-right">
                  <input type="number" x-model.number="it.return_qty" min="0" :max="it.returnable_quantity" step="0.01"
                         :disabled="it.returnable_quantity <= 0"
                         @input="if (it.return_qty > it.returnable_quantity) it.return_qty = it.returnable_quantity; if (it.return_qty < 0) it.return_qty = 0"
                         class="input text-right tabular-nums w-full" placeholder="0" />
                </td>
                <td class="table-td text-right tabular-nums font-semibold text-red-600" x-text="fmtMoney(lineCredit(it))"></td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    
    <div x-show="inv" class="card p-5 space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Return Date <span class="text-red-500">*</span></label>
          <input type="date" x-model="returnDate" class="input w-full" />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Reason <span class="text-red-500">*</span></label>
          <div class="flex flex-wrap gap-1.5 mb-2">
            <template x-for="r in reasons" :key="r">
              <button type="button" @click="reason = r"
                      :class="reason === r ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-gray-200 text-gray-500'"
                      class="text-xs px-2 py-1 rounded-lg border font-medium transition-all" x-text="r"></button>
            </template>
          </div>
          <input type="text" x-model="reason" placeholder="Or type a custom reason…" class="input w-full text-sm" />
        </div>
      </div>

      
      <div x-show="returnTotal > 0" class="rounded-xl p-4 space-y-2" style="background:#fef2f2;border:1px solid #fecaca">
        <div class="flex justify-between text-sm">
          <span class="text-gray-600">Total credit for returned goods</span>
          <span class="font-black text-red-600" x-text="fmtMoney(returnTotal)"></span>
        </div>
        <div class="flex justify-between text-xs text-gray-500" x-show="balanceReduction > 0">
          <span>Reduces balance due on <span x-text="inv?.invoice_number"></span></span>
          <span class="font-semibold" x-text="'− ' + fmtMoney(balanceReduction)"></span>
        </div>
        <div class="flex justify-between text-xs text-gray-500" x-show="creditToCustomer > 0">
          <span>Added to <span x-text="customer?.name"></span>'s credit account</span>
          <span class="font-semibold" x-text="'+ ' + fmtMoney(creditToCustomer)"></span>
        </div>
        <div class="text-xs text-gray-400 pt-1 border-t border-red-100">
          Returned items go back into stock at <span class="font-medium" x-text="inv?.invoice_number ? 'the branch of ' + inv.invoice_number : 'the invoice branch'"></span>.
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-1">
        <a href="<?php echo e(url('/sales-returns')); ?>" class="btn-secondary">Cancel</a>
        <button type="button" @click="submit()" :disabled="submitting || returnTotal <= 0"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-red-600 hover:bg-red-700 text-white transition-colors disabled:opacity-50">
          <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
          <span x-text="submitting ? 'Saving…' : 'Create Sales Return'"></span>
        </button>
      </div>
    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function salesReturnCreate() {
  return {
    invSearch: '',
    invResults: [],
    invOpen: false,
    searching: false,
    inv: null,
    customer: null,
    factor: 1,
    items: [],
    returnDate: new Date().toISOString().slice(0, 10),
    reason: '',
    reasons: ['Damaged', 'Wrong Item', 'Expired', 'Quality Issue', 'Customer Changed Mind'],
    submitting: false,

    get returnTotal() {
      return Math.round(this.items.reduce((s, it) => s + this.lineCredit(it), 0) * 100) / 100;
    },
    get balanceReduction() {
      return Math.min(this.returnTotal, parseFloat(this.inv?.balance_due ?? 0));
    },
    get creditToCustomer() {
      return Math.round((this.returnTotal - this.balanceReduction) * 100) / 100;
    },

    lineCredit(it) {
      const qty = parseFloat(it.return_qty) || 0;
      if (qty <= 0 || it.sold_quantity <= 0) return 0;
      return (it.line_total / it.sold_quantity) * qty * this.factor;
    },

    async init() {
      const pre = new URLSearchParams(window.location.search).get('invoice');
      if (pre) await this.selectInvoice(pre);
    },

    async searchInvoices() {
      if (!this.invSearch || this.invSearch.length < 2) { this.invResults = []; this.invOpen = false; return; }
      this.searching = true;
      try {
        const r = await apiFetch('/invoices?type=invoice&per_page=20&search=' + encodeURIComponent(this.invSearch));
        if (!r) return;
        const d = await r.json();
        this.invResults = (d.data ?? d ?? []).filter(i => ['confirmed', 'partially_paid', 'paid'].includes(i.status));
        this.invOpen = true;
      } catch (e) {
        toast('Failed to search invoices', 'error');
      } finally {
        this.searching = false;
      }
    },

    async selectInvoice(id) {
      this.invOpen = false;
      try {
        const r = await apiFetch('/invoices/' + id + '/returnable');
        if (!r) return;
        const d = await r.json();
        this.inv      = d.invoice;
        this.customer = d.customer;
        this.factor   = parseFloat(d.factor ?? 1);
        this.items    = (d.items ?? []).map(it => ({ ...it, return_qty: '' }));
        if (!this.items.some(it => it.returnable_quantity > 0)) {
          toast('Everything on this invoice has already been returned', 'warning');
        }
      } catch (e) {
        toast(e.message ?? 'This invoice cannot be returned', 'error');
      }
    },

    clearInvoice() {
      this.inv = null; this.customer = null; this.items = [];
      this.invSearch = ''; this.invResults = [];
    },

    async submit() {
      const lines = this.items
        .filter(it => (parseFloat(it.return_qty) || 0) > 0)
        .map(it => ({ product_id: it.product_id, quantity: parseFloat(it.return_qty) }));
      if (!this.inv)        { toast('Select an invoice first', 'error'); return; }
      if (!lines.length)    { toast('Enter a return quantity for at least one item', 'error'); return; }
      if (!this.reason)     { toast('Enter a reason for the return', 'error'); return; }

      this.submitting = true;
      try {
        const r = await apiFetch('/sales-returns', {
          method: 'POST',
          body: JSON.stringify({
            original_invoice_id: this.inv.id,
            return_date: this.returnDate,
            reason: this.reason,
            items: lines,
          }),
        });
        if (!r) return;
        const d = await r.json();
        let msg = 'Sales return recorded';
        if (d.credit_to_customer > 0) msg += ' — ' + fmtMoney(d.credit_to_customer) + ' credited to customer account';
        toast(msg, 'success');
        window.location.href = BASE + '/sales-returns';
      } catch (e) {
        toast(e.message ?? 'Failed to create sales return', 'error');
      } finally {
        this.submitting = false;
      }
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\returns\create.blade.php ENDPATH**/ ?>