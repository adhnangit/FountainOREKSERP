<?php $__env->startSection('title', 'Invoice Detail'); ?>
<?php $__env->startSection('page-title', 'Invoice Detail'); ?>
<?php $__env->startSection('page-desc', 'View invoice information and payment history'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="invoiceShow()" x-init="init()">

  <!-- Loading -->
  <template x-if="loading">
    <div class="flex items-center justify-center py-24 text-gray-400">
      <svg class="animate-spin w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
      Loading invoice…
    </div>
  </template>

  <template x-if="!loading && inv">
    <div class="space-y-5">

      <!-- Top bar: back + actions -->
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <a href="<?php echo e(url('/invoices')); ?>"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-100 transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
          Back to Invoices
        </a>
        <div class="flex gap-2 flex-wrap">
          <button @click="printInvoice()"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m0-10V5a2 2 0 012-2h6a2 2 0 012 2v2M7 17h10v4H7v-4z"/></svg>
            Print
          </button>
          <button @click="downloadPdf()"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download PDF
          </button>
          <template x-if="isSuperAdmin && inv.type === 'invoice'">
            <button @click="openProfitModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3v-6m-3 6v-9m-2 9h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v11a2 2 0 002 2z"/></svg>
              View Profit
            </button>
          </template>
          <template x-if="inv.status === 'draft'">
            <a :href="BASE + '/invoices/' + inv.id + '/edit'"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              Edit
            </a>
          </template>
          <template x-if="inv.status === 'draft'">
            <button @click="confirmInvoice()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-primary-600 hover:bg-primary-700 text-white transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Confirm Invoice
            </button>
          </template>
          <template x-if="['confirmed','pending','partial','overdue','partially_paid'].includes(inv.status)">
            <button @click="payForm.amount=inv.balance_due||inv.total||0; payForm.use_credit=false; payForm.account_id=null; showPayModal=true"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-green-600 hover:bg-green-700 text-white transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
              Add Payment
            </button>
          </template>
          <template x-if="['confirmed','partially_paid','paid'].includes(inv.status)">
            <a :href="BASE + '/sales-returns/create?invoice=' + inv.id"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 dark:bg-transparent dark:border-red-800 dark:text-red-400 transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
              Sales Return
            </a>
          </template>
          <template x-if="inv.status !== 'paid' && inv.status !== 'cancelled'">
            <button @click="cancelInvoice()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium border border-orange-200 text-orange-600 bg-orange-50 hover:bg-orange-100 dark:bg-transparent dark:border-orange-800 dark:text-orange-400 transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
              Cancel
            </button>
          </template>
          <template x-if="inv.status === 'draft'">
            <button @click="deleteInvoice()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-red-700 hover:bg-red-800 text-white transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              Delete
            </button>
          </template>
        </div>
      </div>

      <!-- Hero card -->
      <div class="rounded-2xl overflow-hidden shadow-sm border border-gray-100 dark:border-gray-700">
        <!-- Gradient header -->
        <div class="px-6 py-5" style="background:linear-gradient(135deg,#1B3EB6 0%,#2563eb 60%,#3b82f6 100%)">
          <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
              <div class="text-xs font-semibold text-blue-200 uppercase tracking-widest mb-1">Tax Invoice</div>
              <div class="text-3xl font-bold text-white" x-text="inv.invoice_number || ('#INV-' + String(inv.id).padStart(4,'0'))"></div>
              <div class="mt-3 flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                     x-text="(inv.customer?.name || 'C').charAt(0).toUpperCase()"></div>
                <div>
                  <div class="text-white font-semibold text-sm" x-text="inv.customer?.name || '—'"></div>
                  <div class="text-blue-200 text-xs" x-text="inv.customer?.phone || ''"></div>
                </div>
              </div>
            </div>
            <div class="sm:text-right space-y-2">
              <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold tracking-wide" :class="{
                'bg-gray-200 text-gray-700':    inv.status === 'draft',
                'bg-blue-200 text-blue-800':    inv.status === 'confirmed',
                'bg-yellow-200 text-yellow-800':inv.status === 'pending' || inv.status === 'partial' || inv.status === 'partially_paid',
                'bg-green-200 text-green-800':  inv.status === 'paid',
                'bg-red-200 text-red-800':      inv.status === 'overdue' || inv.status === 'cancelled',
              }" x-text="inv.status?.replace('_',' ')?.toUpperCase()"></span>
              <div class="text-blue-100 text-sm">
                <span class="text-blue-200 text-xs">Invoice Date</span><br>
                <span class="font-medium text-white" x-text="fmtDate(inv.invoice_date)"></span>
              </div>
              <div class="text-blue-100 text-sm">
                <span class="text-blue-200 text-xs">Due Date</span><br>
                <span class="font-medium text-white" x-text="fmtDate(inv.due_date)"></span>
              </div>
            </div>
          </div>
        </div>

        <!-- Financial summary strip -->
        <div class="bg-white dark:bg-gray-800 grid grid-cols-2 sm:grid-cols-4 divide-x divide-y sm:divide-y-0 divide-gray-100 dark:divide-gray-700">
          <div class="px-5 py-4">
            <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Subtotal</div>
            <div class="text-xl font-semibold text-gray-800 dark:text-gray-100" x-text="fmtMoney(inv.subtotal || inv.sub_total || 0)"></div>
          </div>
          <div class="px-5 py-4">
            <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Tax</div>
            <div class="text-xl font-semibold text-gray-800 dark:text-gray-100" x-text="fmtMoney(inv.tax_amount || 0)"></div>
          </div>
          <div class="px-5 py-4">
            <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Total</div>
            <div class="text-xl font-bold text-blue-600 dark:text-blue-400" x-text="fmtMoney(inv.total || 0)"></div>
          </div>
          <div class="px-5 py-4">
            <div class="text-xs text-gray-400 uppercase tracking-wider mb-1">Balance Due</div>
            <div class="text-xl font-bold"
                 :class="(inv.balance_due||0) > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400'"
                 x-text="fmtMoney(inv.balance_due || 0)"></div>
          </div>
        </div>

        <!-- Payment progress bar -->
        <div class="bg-white dark:bg-gray-800 px-5 pb-4 border-t border-gray-100 dark:border-gray-700">
          <div class="flex items-center justify-between text-xs text-gray-400 mb-1.5">
            <span>Payment progress</span>
            <span x-text="inv.total > 0 ? Math.round((inv.paid_amount||0)/inv.total*100)+'% paid' : '0% paid'"></span>
          </div>
          <div class="h-2 rounded-full bg-gray-100 dark:bg-gray-700 overflow-hidden">
            <div class="h-2 rounded-full transition-all duration-500"
                 :class="(inv.balance_due||0) <= 0 ? 'bg-green-500' : 'bg-blue-500'"
                 :style="`width:${inv.total>0?Math.min(100,Math.round((inv.paid_amount||0)/inv.total*100)):0}%`"></div>
          </div>
        </div>

        <!-- Customer credit balance notice -->
        <template x-if="customerCredit > 0">
          <div class="bg-blue-50 dark:bg-blue-900/20 border-t border-blue-100 dark:border-blue-800 px-5 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2 text-sm text-blue-700 dark:text-blue-300">
              <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
              <span><strong x-text="inv.customer?.name"></strong> has a credit of <strong x-text="fmtMoney(customerCredit)"></strong> on account</span>
            </div>
            <template x-if="inv.balance_due > 0">
              <button @click="payForm.amount=Math.max(0,(inv.balance_due||0)-customerCredit); payForm.use_credit=true; payForm.account_id=null; showPayModal=true"
                      class="text-xs font-semibold px-3 py-1 rounded-lg"
                      style="background:#1B3EB6;color:#fff">
                Apply Credit
              </button>
            </template>
          </div>
        </template>
      </div>

      <!-- Line Items -->
      <div class="card overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Line Items</h3>
          <span class="ml-auto text-xs text-gray-400" x-text="(inv.items?.length||0)+' item'+(inv.items?.length===1?'':'s')"></span>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs">
              <tr>
                <th class="table-hd">Product / Description</th>
                <th class="table-hd text-right">Qty</th>
                <th class="table-hd text-right">Unit Price</th>
                <th class="table-hd text-right">Disc %</th>
                <th class="table-hd text-right">Line Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
              <template x-for="item in inv.items" :key="item.id">
                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors">
                  <td class="table-td">
                    <div class="font-medium text-gray-800 dark:text-gray-100" x-text="item.product_name || item.product?.name || '—'"></div>
                    <div class="text-xs text-gray-400 font-mono" x-text="item.product_code || item.product?.sku || ''"></div>
                  </td>
                  <td class="table-td text-right tabular-nums" x-text="item.quantity"></td>
                  <td class="table-td text-right tabular-nums" x-text="fmtMoney(item.unit_price)"></td>
                  <td class="table-td text-right text-gray-500">
                    <template x-if="(item.discount_percent||0) > 0">
                      <span class="text-orange-500 font-medium" x-text="item.discount_percent+'%'"></span>
                    </template>
                    <template x-if="!(item.discount_percent||0) > 0"><span>—</span></template>
                  </td>
                  <td class="table-td text-right font-semibold tabular-nums" x-text="fmtMoney(item.total || (item.quantity * item.unit_price))"></td>
                </tr>
              </template>
              <!-- Totals footer -->
              <template x-if="inv.items && inv.items.length > 0">
                <tr class="bg-gray-50/50 dark:bg-gray-700/20">
                  <td colspan="4" class="table-td text-right text-xs text-gray-500 font-medium">Subtotal</td>
                  <td class="table-td text-right font-semibold tabular-nums" x-text="fmtMoney(inv.subtotal||inv.sub_total||0)"></td>
                </tr>
              </template>
              <template x-if="(inv.tax_amount||0) > 0">
                <tr class="bg-gray-50/50 dark:bg-gray-700/20">
                  <td colspan="4" class="table-td text-right text-xs text-gray-500 font-medium" x-text="'Tax ('+(inv.tax_percent||0)+'%)'"></td>
                  <td class="table-td text-right font-semibold tabular-nums text-gray-600 dark:text-gray-300" x-text="fmtMoney(inv.tax_amount||0)"></td>
                </tr>
              </template>
              <template x-if="inv.items && inv.items.length > 0">
                <tr class="bg-blue-50 dark:bg-blue-900/20">
                  <td colspan="4" class="table-td text-right text-sm font-bold text-blue-700 dark:text-blue-300">Grand Total</td>
                  <td class="table-td text-right text-base font-bold text-blue-700 dark:text-blue-300 tabular-nums" x-text="fmtMoney(inv.total||0)"></td>
                </tr>
              </template>
              <template x-if="!inv.items || inv.items.length === 0">
                <tr><td colspan="5" class="table-td text-center text-gray-400 py-10">No line items</td></tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Payment History -->
      <div class="card overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2">
          <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
          <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">Payment History</h3>
          <span class="ml-auto text-xs text-gray-400" x-text="(inv.payments?.length||0)+' payment'+(inv.payments?.length===1?'':'s')"></span>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-xs">
              <tr>
                <th class="table-hd">Date</th>
                <th class="table-hd">Method</th>
                <th class="table-hd">Cheque #</th>
                <th class="table-hd">Cheque Status</th>
                <th class="table-hd">Reference</th>
                <th class="table-hd">Note</th>
                <th class="table-hd text-right">Amount</th>
                <th class="table-hd"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-700">
              <template x-for="pay in inv.payments" :key="pay.id">
                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/20 transition-colors"
                    :class="pay.cheque?.status === 'bounced' ? 'bg-red-50/50 dark:bg-red-900/10' : ''">
                  <td class="table-td text-gray-600 dark:text-gray-300" x-text="fmtDate(pay.payment_date)"></td>
                  <td class="table-td">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium" :class="{
                      'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400': pay.payment_method === 'cash',
                      'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400':    pay.payment_method === 'bank_transfer',
                      'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400': pay.payment_method === 'cheque',
                      'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300':       !['cash','bank_transfer','cheque'].includes(pay.payment_method),
                    }" x-text="(pay.payment_method||'—').replace('_',' ')"></span>
                  </td>
                  <td class="table-td">
                    <div class="font-mono text-sm text-gray-700 dark:text-gray-200" x-text="pay.cheque?.cheque_number || '—'"></div>
                    <div class="text-xs text-gray-400 mt-0.5" x-show="pay.cheque?.cheque_date" x-text="fmtDate(pay.cheque?.cheque_date)"></div>
                  </td>
                  <td class="table-td">
                    <template x-if="pay.cheque">
                      <span class="badge text-xs" :class="{
                        'badge-warning': pay.cheque.status === 'in_hand',
                        'badge-primary': pay.cheque.status === 'deposited',
                        'badge-success': pay.cheque.status === 'cleared',
                        'badge-danger':  pay.cheque.status === 'bounced',
                        'badge-gray':    pay.cheque.status === 'cancelled' || pay.cheque.status === 'returned',
                      }" x-text="pay.cheque.status?.replace('_',' ')?.toUpperCase()"></span>
                    </template>
                    <template x-if="!pay.cheque"><span class="text-gray-300">—</span></template>
                  </td>
                  <td class="table-td text-gray-500 dark:text-gray-400 text-xs" x-text="pay.reference_number || pay.reference || '—'"></td>
                  <td class="table-td text-gray-500 dark:text-gray-400 text-xs" x-text="pay.notes || '—'"></td>
                  <td class="table-td text-right font-semibold tabular-nums"
                      :class="pay.cheque?.status === 'bounced' ? 'text-red-400 line-through' : 'text-green-600 dark:text-green-400'"
                      x-text="fmtMoney(pay.amount)"></td>
                  <td class="table-td text-right">
                    <button x-show="isSuperAdmin && (!pay.cheque || pay.cheque.status === 'in_hand')"
                            @click="deletePayment(pay)"
                            :disabled="deletingPaymentId === pay.id"
                            title="Delete this payment (e.g. wrong amount entered)"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 dark:bg-red-900/10 dark:border-red-800 dark:text-red-400 transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                      <svg x-show="deletingPaymentId !== pay.id" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                      <svg x-show="deletingPaymentId === pay.id" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                      <span x-text="deletingPaymentId === pay.id ? 'Deleting…' : 'Delete'"></span>
                    </button>
                    <span x-show="isSuperAdmin && pay.cheque && pay.cheque.status !== 'in_hand'" class="text-gray-300 text-xs" title="Cheque already processed — reverse its status in Manage Cheque first">—</span>
                  </td>
                </tr>
              </template>
              <template x-if="!inv.payments || inv.payments.length === 0">
                <tr>
                  <td colspan="8" class="py-12 text-center">
                    <div class="text-gray-300 dark:text-gray-600 text-3xl mb-2">💳</div>
                    <div class="text-sm text-gray-400">No payments recorded yet</div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Notes -->
      <template x-if="inv.notes">
        <div class="card p-5 flex gap-3">
          <svg class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
          <div>
            <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Notes</div>
            <p class="text-sm text-gray-700 dark:text-gray-300" x-text="inv.notes"></p>
          </div>
        </div>
      </template>

    </div>
  </template>

  <!-- Payment Modal -->
  <div x-show="showPayModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showPayModal = false"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md z-10 flex flex-col overflow-hidden" style="max-height:90vh">
      <!-- Modal header -->
      <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between flex-shrink-0">
        <div>
          <h3 class="text-base font-semibold text-gray-900 dark:text-white">Add Payment</h3>
          <p class="text-xs text-gray-400 mt-0.5" x-text="'Balance due: ' + fmtMoney(inv?.balance_due||0)"></p>
        </div>
        <button @click="showPayModal = false" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
      <!-- Modal body -->
      <div class="px-6 py-5 space-y-4 flex-1 overflow-y-auto">

        
        <template x-if="customerCredit > 0">
          <div class="rounded-xl border border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700 p-3">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <div>
                  <p class="text-xs font-semibold text-blue-800 dark:text-blue-200">Customer has a credit balance</p>
                  <p class="text-xs text-blue-600 dark:text-blue-300 mt-0.5"
                     x-text="fmtMoney(customerCredit) + ' available on account'"></p>
                </div>
              </div>
              <label class="flex items-center gap-2 cursor-pointer flex-shrink-0">
                <span class="text-xs font-medium text-blue-700 dark:text-blue-300">Apply</span>
                <div class="relative" style="width:36px;height:20px">
                  <input type="checkbox" x-model="payForm.use_credit" @change="onUseCreditChange()" class="sr-only peer" />
                  <div class="w-full h-full rounded-full transition-colors"
                       :style="payForm.use_credit ? 'background:#1B3EB6' : 'background:#d1d5db'"></div>
                  <div class="absolute top-0.5 left-0.5 w-[16px] h-[16px] bg-white rounded-full shadow transition-transform"
                       :style="payForm.use_credit ? 'transform:translateX(16px)' : ''"></div>
                </div>
              </label>
            </div>
            <template x-if="payForm.use_credit">
              <div class="mt-2 pt-2 border-t border-blue-200 dark:border-blue-700">
                <div class="flex justify-between text-xs text-blue-700 dark:text-blue-300">
                  <span>Credit applied:</span>
                  <span class="font-semibold" x-text="fmtMoney(Math.min(customerCredit, inv?.balance_due||0))"></span>
                </div>
                <div class="flex justify-between text-xs text-blue-700 dark:text-blue-300 mt-0.5">
                  <span>Remaining to collect:</span>
                  <span class="font-semibold" x-text="fmtMoney(Math.max(0, (inv?.balance_due||0) - customerCredit))"></span>
                </div>
              </div>
            </template>
          </div>
        </template>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Payment Date</label>
            <input type="date" x-model="payForm.payment_date" class="input" />
          </div>
          <div>
            <label class="label">Amount Received</label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">Rs.</span>
              <input type="number" x-model.number="payForm.amount" @input="onAmountChange()"
                     min="0" step="0.01" class="input text-right tabular-nums" style="padding-left:2rem" placeholder="0.00" />
            </div>
          </div>
        </div>

        
        <template x-if="overpaymentAmount > 0">
          <div class="rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 p-3 flex items-start gap-2">
            <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
              <p class="text-xs font-semibold text-amber-800 dark:text-amber-200">Overpayment detected</p>
              <p class="text-xs text-amber-700 dark:text-amber-300 mt-0.5"
                 x-text="fmtMoney(overpaymentAmount) + ' will be added to ' + (inv?.customer?.name||'customer') + '\'s credit account'"></p>
            </div>
          </div>
        </template>

        <div>
          <label class="label">Payment Method</label>
          <select x-model="payForm.payment_method" @change="payForm.account_id=null" class="input">
            <option value="cash">Cash</option>
            <option value="cheque">Cheque</option>
            <option value="bank_transfer">Bank Transfer</option>
          </select>
        </div>
        <!-- Cash account picker -->
        <div x-show="payForm.payment_method === 'cash'">
          <label class="label">Cash Account <span class="text-red-500">*</span></label>
          <select x-model.number="payForm.account_id" class="input">
            <option :value="null">Select cash account…</option>
            <template x-for="a in cashAccounts" :key="a.id">
              <option :value="a.id" x-text="a.code + ' — ' + a.name"></option>
            </template>
          </select>
          <p class="text-xs text-gray-400 mt-1" x-show="!cashAccounts.length">No cash accounts in Chart of Accounts — add one first.</p>
        </div>
        <!-- Bank account picker -->
        <div x-show="payForm.payment_method === 'bank_transfer'">
          <label class="label">Bank Account <span class="text-red-500">*</span></label>
          <select x-model.number="payForm.account_id" class="input">
            <option :value="null">Select bank account…</option>
            <template x-for="a in bankAccounts" :key="a.id">
              <option :value="a.id" x-text="a.code + ' — ' + a.name"></option>
            </template>
          </select>
          <p class="text-xs text-gray-400 mt-1" x-show="!bankAccounts.length">No bank accounts in Chart of Accounts — add one first.</p>
          <p class="text-xs text-amber-600 mt-1">Required — this is the account that actually gets credited for this payment.</p>
        </div>
        <div>
          <label class="label">Reference / Transaction ID</label>
          <input type="text" x-model="payForm.reference_number" class="input" placeholder="Transaction ID, receipt #…" />
        </div>
        <!-- Cheque-specific fields -->
        <div x-show="payForm.payment_method === 'cheque'"
             class="space-y-4 p-4 rounded-xl border border-purple-100 dark:border-purple-800/50"
             style="background:#faf5ff">
          <div class="text-xs font-semibold text-purple-600 uppercase tracking-wider mb-1">Cheque Details</div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="label">Cheque Number <span class="text-red-500">*</span></label>
              <input type="text" x-model="payForm.cheque_number" class="input" placeholder="001234" />
            </div>
            <div>
              <label class="label">Cheque Date <span class="text-red-500">*</span></label>
              <input type="date" x-model="payForm.cheque_date" class="input" />
            </div>
          </div>
          <div>
            <label class="label">Bank Name <span class="text-red-500">*</span></label>
            <div x-data="{bq:'',bOpen:false}" @click.outside="bOpen=false" class="relative">
              <input type="text" :value="payForm.bank_name"
                @input="payForm.bank_name=$event.target.value;bq=$event.target.value;bOpen=true"
                @focus="bq=payForm.bank_name||'';bOpen=true" @keydown.escape="bOpen=false"
                class="input" placeholder="Search bank…" autocomplete="off" />
              <ul x-show="bOpen" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 rounded-xl shadow-xl max-h-48 overflow-y-auto">
                <template x-for="b in banks.filter(b=>b.name.toLowerCase().includes(bq.toLowerCase()))" :key="b.id">
                  <li @mousedown.prevent="payForm.bank_name=b.name;bq=b.name;bOpen=false"
                      :class="payForm.bank_name===b.name?'bg-indigo-50 text-indigo-700 font-medium':'hover:bg-gray-50 text-gray-700'"
                      class="px-3 py-2 text-sm cursor-pointer" x-text="b.name"></li>
                </template>
                <li x-show="!banks.filter(b=>b.name.toLowerCase().includes(bq.toLowerCase())).length" class="px-3 py-2 text-sm text-gray-400 text-center">No banks found</li>
              </ul>
            </div>
          </div>
        </div>
        <div>
          <label class="label">Notes</label>
          <textarea x-model="payForm.notes" rows="2" class="input"></textarea>
        </div>
      </div>
      <!-- Modal footer -->
      <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 flex-shrink-0">
        <button @click="showPayModal = false" class="btn-secondary">Cancel</button>
        <button @click="addPayment()" :disabled="submitting"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-green-600 hover:bg-green-700 text-white transition-colors disabled:opacity-50">
          <svg x-show="!submitting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
          <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
          <span x-text="submitting ? 'Saving…' : 'Record Payment'"></span>
        </button>
      </div>
    </div>
  </div>

  <!-- Profit Modal (super admin only) -->
  <div x-show="showProfitModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
       x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
    <div class="absolute inset-0" style="background:rgba(15,23,42,.65)" @click="showProfitModal=false"></div>
    <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-2xl p-6 z-10 max-h-[90vh] overflow-y-auto">
      <div class="flex items-start justify-between mb-4">
        <div>
          <h5 class="text-lg font-bold text-gray-800 dark:text-gray-100">Profit — <span x-text="inv.invoice_number"></span></h5>
          <p class="text-xs text-amber-600 dark:text-amber-400 mt-0.5 font-medium">Visible to admins only</p>
        </div>
        <button @click="showProfitModal=false" class="text-gray-400 hover:text-gray-600 ml-4">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <div x-show="loadingProfit" class="flex items-center justify-center py-10">
        <svg class="animate-spin w-6 h-6 text-emerald-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
      </div>

      <template x-if="!loadingProfit && profitData">
        <div>
          <div x-show="profitData.cost_estimated" class="text-xs text-amber-700 bg-amber-50 dark:bg-amber-900/20 dark:text-amber-400 rounded-lg p-3 mb-4">
            Per-item cost wasn't recorded for one or more lines on this older invoice — the totals below use the actual cost posted to the books, but the per-item split isn't fully available.
          </div>

          <div class="overflow-x-auto -mx-6 px-6">
            <table class="min-w-full text-sm">
              <thead>
                <tr class="text-left text-gray-400 text-xs uppercase tracking-wide border-b border-gray-100 dark:border-gray-700">
                  <th class="py-2 pr-4">Item</th>
                  <th class="py-2 pr-4 text-right">Qty</th>
                  <th class="py-2 pr-4 text-right">Unit Price</th>
                  <th class="py-2 pr-4 text-right">Unit Cost</th>
                  <th class="py-2 pr-4 text-right">Revenue</th>
                  <th class="py-2 pr-4 text-right">Cost</th>
                  <th class="py-2 pr-4 text-right">Profit</th>
                  <th class="py-2 text-right">Margin</th>
                </tr>
              </thead>
              <tbody>
                <template x-for="item in profitData.items" :key="item.product_code + item.product_name">
                  <tr class="border-b border-gray-50 dark:border-gray-800">
                    <td class="py-2 pr-4 font-medium text-gray-800 dark:text-gray-100" x-text="item.product_name"></td>
                    <td class="py-2 pr-4 text-right tabular-nums" x-text="item.quantity"></td>
                    <td class="py-2 pr-4 text-right tabular-nums" x-text="fmtMoney(item.unit_price)"></td>
                    <td class="py-2 pr-4 text-right tabular-nums" x-text="item.cost_missing ? '—' : fmtMoney(item.unit_cost)"></td>
                    <td class="py-2 pr-4 text-right tabular-nums" x-text="fmtMoney(item.line_revenue)"></td>
                    <td class="py-2 pr-4 text-right tabular-nums" x-text="item.cost_missing ? '—' : fmtMoney(item.line_cost)"></td>
                    <td class="py-2 pr-4 text-right tabular-nums font-semibold"
                        :class="item.line_profit === null ? '' : (item.line_profit >= 0 ? 'text-green-600' : 'text-red-600')"
                        x-text="item.line_profit === null ? '—' : fmtMoney(item.line_profit)"></td>
                    <td class="py-2 text-right tabular-nums" x-text="item.margin_pct === null ? '—' : item.margin_pct + '%'"></td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>

          <div class="grid grid-cols-3 gap-3 mt-5">
            <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4">
              <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Revenue</div>
              <div class="text-lg font-bold text-gray-800 dark:text-gray-100" x-text="fmtMoney(profitData.revenue_total)"></div>
            </div>
            <div class="rounded-xl border border-gray-100 dark:border-gray-700 p-4">
              <div class="text-xs text-gray-400 uppercase tracking-wide mb-1">Cost</div>
              <div class="text-lg font-bold text-gray-800 dark:text-gray-100" x-text="fmtMoney(profitData.cost_total)"></div>
            </div>
            <div class="rounded-xl border p-4"
                 :class="profitData.profit_total >= 0 ? 'border-green-200 bg-green-50 dark:bg-green-900/10 dark:border-green-800' : 'border-red-200 bg-red-50 dark:bg-red-900/10 dark:border-red-800'">
              <div class="text-xs uppercase tracking-wide mb-1" :class="profitData.profit_total >= 0 ? 'text-green-600' : 'text-red-600'">
                Profit <span x-text="profitData.margin_pct !== null ? '(' + profitData.margin_pct + '%)' : ''"></span>
              </div>
              <div class="text-lg font-bold" :class="profitData.profit_total >= 0 ? 'text-green-700' : 'text-red-700'" x-text="fmtMoney(profitData.profit_total)"></div>
            </div>
          </div>
        </div>
      </template>

      <template x-if="!loadingProfit && !profitData">
        <p class="text-sm text-gray-400 text-center py-8">Could not load profit details.</p>
      </template>
    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function invoiceShow() {
  return {
    loading: true, submitting: false,
    inv: null, showPayModal: false, banks: [], accounts: [], isSuperAdmin: false, deletingPaymentId: null,
    showProfitModal: false, loadingProfit: false, profitData: null,
    payForm: {
      payment_date: new Date().toISOString().slice(0,10),
      amount: 0, payment_method: 'cash', reference_number: '', notes: '',
      cheque_number: '', bank_name: '', cheque_date: '', account_id: null,
      use_credit: false,
    },
    get id() { return window.location.pathname.split('/').filter(Boolean).pop(); },
    get customerCredit() { return parseFloat(this.inv?.customer?.credit_balance ?? 0); },
    get cashAccounts() { return this.accounts.filter(a => a.group === 'Cash Accounts' && !/cheque/i.test(a.name) && a.branch_id == this.inv?.branch_id); },
    get bankAccounts() { return this.accounts.filter(a => a.group === 'Bank Accounts' && a.branch_id == this.inv?.branch_id); },
    get overpaymentAmount() {
      if (!this.inv) return 0;
      const effective = (parseFloat(this.inv.balance_due) || 0)
                      - (this.payForm.use_credit ? Math.min(this.customerCredit, parseFloat(this.inv.balance_due)||0) : 0);
      return Math.max(0, parseFloat(this.payForm.amount || 0) - effective);
    },
    async init() {
      this.banks = await loadBanks();
      try {
        const u = JSON.parse(localStorage.getItem('medri_user') || '{}');
        this.isSuperAdmin = !!u.is_super_admin || (u.roles ?? []).includes('super_admin');
      } catch (_) {}
      try {
        const r = await apiFetch('/invoices/' + this.id);
        const d = await r.json();
        this.inv = d.data || d;
      } catch(e) { toast('Failed to load invoice', 'error'); }
      // Scoped explicitly to this invoice's own branch — not whatever branch
      // happens to be selected in the top nav (could be "All Branches" or a
      // different branch entirely), so the payment account pickers never show
      // another branch's cash/bank accounts.
      if (this.inv?.branch_id) {
        try {
          const headers = authHeaders();
          headers['X-Branch-Id'] = this.inv.branch_id;
          const r = await apiFetch('/accounting/accounts', { headers });
          this.accounts = await r.json();
        } catch (_) {}
      }
      this.loading = false;
    },
    onUseCreditChange() {
      if (this.payForm.use_credit) {
        const remaining = Math.max(0, (parseFloat(this.inv?.balance_due)||0) - Math.min(this.customerCredit, parseFloat(this.inv?.balance_due)||0));
        this.payForm.amount = remaining;
      } else {
        this.payForm.amount = parseFloat(this.inv?.balance_due) || 0;
      }
    },
    onAmountChange() { /* reactive — overpaymentAmount computed property handles display */ },
    async downloadPdf() {
      try {
        const r = await apiFetch('/invoices/' + this.id + '/pdf');
        if (!r.ok) { toast('Failed to generate PDF', 'error'); return; }
        const blob = await r.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'invoice-' + (this.inv?.invoice_number || this.id) + '.pdf';
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
        URL.revokeObjectURL(url);
      } catch(e) { toast('PDF download failed', 'error'); }
    },
    async printInvoice() {
      try {
        const r = await apiFetch('/invoices/' + this.id + '/pdf');
        if (!r.ok) { toast('Failed to generate PDF', 'error'); return; }
        const blob = await r.blob();
        const url = URL.createObjectURL(blob);
        const iframe = document.createElement('iframe');
        iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0';
        iframe.src = url;
        document.body.appendChild(iframe);
        iframe.onload = () => {
          setTimeout(() => {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
          }, 300);
        };
        setTimeout(() => { document.body.removeChild(iframe); URL.revokeObjectURL(url); }, 60000);
      } catch(e) { toast('Print failed', 'error'); }
    },
    async confirmInvoice() {
      if (!confirm('Confirm this invoice?')) return;
      try {
        const r = await apiFetch('/invoices/' + this.id + '/confirm', { method: 'POST' });
        if (r.ok) { toast('Invoice confirmed', 'success'); await this.init(); }
        else toast('Failed to confirm', 'error');
      } catch(e) { toast('Error', 'error'); }
    },
    async cancelInvoice() {
      if (!confirm('Cancel this invoice? Stock will be reversed if already confirmed.')) return;
      try {
        const r = await apiFetch('/invoices/' + this.id + '/cancel', { method: 'POST', body: JSON.stringify({reason: 'Cancelled by user'}) });
        if (r.ok) { toast('Invoice cancelled', 'success'); await this.init(); }
        else { const e = await r.json(); toast(e.message || 'Failed to cancel', 'error'); }
      } catch(e) { toast('Error', 'error'); }
    },
    async deleteInvoice() {
      if (!confirm('Permanently delete this draft invoice? This cannot be undone.')) return;
      try {
        const r = await apiFetch('/invoices/' + this.id, { method: 'DELETE' });
        if (r.ok) { toast('Invoice deleted', 'success'); window.location.href = BASE + '/invoices'; }
        else { const e = await r.json(); toast(e.message || 'Failed to delete', 'error'); }
      } catch(e) { toast('Error', 'error'); }
    },
    async deletePayment(pay) {
      const warning = pay.cheque
        ? `Delete this ${fmtMoney(pay.amount)} cheque payment (#${pay.cheque.cheque_number})? The invoice balance will be restored and the cheque removed. This cannot be undone.`
        : `Delete this ${fmtMoney(pay.amount)} payment? The invoice balance will be restored. This cannot be undone.`;
      if (!confirm(warning)) return;
      this.deletingPaymentId = pay.id;
      try {
        const r = await apiFetch('/invoices/' + this.id + '/payments/' + pay.id, { method: 'DELETE' });
        if (r.ok) { toast('Payment deleted', 'success'); await this.init(); }
        else { const e = await r.json(); toast(e.message || 'Failed to delete payment', 'error'); }
      } catch(e) { toast(e.message || 'Error', 'error'); }
      finally { this.deletingPaymentId = null; }
    },
    async openProfitModal() {
      this.showProfitModal = true;
      this.loadingProfit = true;
      this.profitData = null;
      try {
        const r = await apiFetch('/invoices/' + this.id + '/profit');
        if (r.ok) { this.profitData = await r.json(); }
        else { const e = await r.json().catch(() => ({})); toast(e.message || 'Failed to load profit.', 'error'); }
      } catch (e) {
        toast('Failed to load profit.', 'error');
      } finally {
        this.loadingProfit = false;
      }
    },
    async addPayment() {
      if (!this.payForm.amount && !this.payForm.use_credit) {
        toast('Enter a payment amount', 'error'); return;
      }
      if (this.payForm.payment_method === 'cheque') {
        if (!this.payForm.cheque_number) { toast('Enter cheque number', 'error'); return; }
        if (!this.payForm.bank_name)     { toast('Enter bank name', 'error'); return; }
        if (!this.payForm.cheque_date)   { toast('Enter cheque date', 'error'); return; }
      }
      if (this.payForm.payment_method === 'cash' && !this.payForm.account_id) {
        toast('Select which cash account this payment went into', 'error'); return;
      }
      if (this.payForm.payment_method === 'bank_transfer' && !this.payForm.account_id) {
        toast('Select which bank account this payment went into', 'error'); return;
      }
      this.submitting = true;
      try {
        const r = await apiFetch('/invoices/' + this.id + '/payment', {
          method: 'POST', body: JSON.stringify(this.payForm)
        });
        const d = await r.json();
        if (r.ok) {
          toast(d.message || 'Payment recorded', 'success');
          this.showPayModal = false;
          this.payForm = { payment_date: new Date().toISOString().slice(0,10), amount: 0, payment_method: 'cash', reference_number: '', notes: '', cheque_number: '', bank_name: '', cheque_date: '', account_id: null, use_credit: false };
          await this.init();
        } else {
          toast(d.message || 'Failed to add payment', 'error');
        }
      } catch(e) { toast('Error', 'error'); }
      this.submitting = false;
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\invoices\show.blade.php ENDPATH**/ ?>