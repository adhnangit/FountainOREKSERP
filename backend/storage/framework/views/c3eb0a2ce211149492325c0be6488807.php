<?php $__env->startSection('title', 'Supplier Invoice'); ?>
<?php $__env->startSection('page-title', 'Supplier Invoice'); ?>
<?php $__env->startSection('page-desc', 'View supplier invoice details and payments'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="invShowPage()" x-init="init()" class="pb-16">

  
  <div x-show="loading" class="flex items-center justify-center py-32">
    <svg class="animate-spin w-9 h-9 text-emerald-600" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
    </svg>
  </div>

  <div x-show="!loading && po" x-cloak>

    
    <div class="rounded-2xl overflow-hidden mb-6 shadow-lg"
         style="background:linear-gradient(135deg,#064e3b 0%,#065f46 50%,#047857 100%)">
      <div class="px-6 py-5">
        
        <div class="flex items-center justify-between mb-5">
          <a href="<?php echo e(url('/purchase-orders')); ?>"
             class="inline-flex items-center gap-1.5 text-xs font-semibold rounded-lg px-3 py-1.5 transition-all"
             style="background:rgba(255,255,255,0.12);color:rgba(255,255,255,0.85);border:1px solid rgba(255,255,255,0.2)">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M15 19l-7-7 7-7"/></svg>
            All Invoices
          </a>
          <div class="flex items-center gap-2">
            <template x-if="hasDraftGrn()">
              <button @click="openReceive()"
                      class="inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-1.5 rounded-lg transition-all"
                      style="background:#dcfce7;color:#15803d;border:1px solid #86efac">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
                Receive Items
              </button>
            </template>
            <template x-if="parseFloat(po?.balance_due ?? 0) > 0 && po?.status !== 'cancelled'">
              <button @click="openPay()"
                      class="inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-1.5 rounded-lg transition-all"
                      style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3)">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                Record Payment
              </button>
            </template>
            <template x-if="['partially_received','received'].includes(po?.status)">
              <a :href="BASE + '/purchase-returns/create?po=' + po.id"
                 class="inline-flex items-center gap-1.5 text-xs font-bold px-3.5 py-1.5 rounded-lg transition-all"
                 style="background:rgba(239,68,68,0.15);color:#fff;border:1px solid rgba(252,165,165,0.4)">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                Purchase Return
              </a>
            </template>
          </div>
        </div>

        
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
          <div>
            <div class="flex items-center gap-2 mb-1">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                   style="background:rgba(255,255,255,0.15)">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                  <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
              </div>
              <div>
                <div class="text-2xl font-extrabold text-white tracking-tight font-mono" x-text="po?.po_number ?? '—'"></div>
                <div class="text-sm mt-0.5" style="color:rgba(255,255,255,0.7)" x-text="po?.supplier?.name ?? ''"></div>
              </div>
            </div>
            <div class="flex items-center gap-2 mt-3 flex-wrap">
              <span class="text-xs px-2.5 py-1 rounded-full font-bold"
                    :class="statusClass(po?.status)" x-text="statusLabel(po?.status)"></span>
              <span class="text-xs px-2.5 py-1 rounded-full font-bold"
                    :class="payBadge(po?.payment_status)" x-text="payLabel(po?.payment_status)"></span>
              <span class="text-xs px-2 py-1 rounded-full font-semibold"
                    style="background:rgba(255,255,255,0.12);color:rgba(255,255,255,0.8)"
                    x-text="fmtDate(po?.order_date)"></span>
              <template x-if="isOverdue()">
                <span class="text-xs px-2.5 py-1 rounded-full font-bold bg-red-500 text-white animate-pulse">Overdue</span>
              </template>
            </div>
          </div>

          
          <div class="flex items-center gap-6 text-right">
            <div>
              <div class="text-xs font-semibold mb-0.5" style="color:rgba(255,255,255,0.55)">Invoice Total</div>
              <div class="text-2xl font-extrabold text-white tabular-nums" x-text="fmtMoney(po?.total ?? 0)"></div>
            </div>
            <template x-if="parseFloat(po?.balance_due ?? 0) > 0">
              <div>
                <div class="text-xs font-semibold mb-0.5" style="color:rgba(255,255,255,0.55)">Balance Due</div>
                <div class="text-2xl font-extrabold tabular-nums" style="color:#fca5a5" x-text="fmtMoney(po?.balance_due ?? 0)"></div>
              </div>
            </template>
            <template x-if="parseFloat(po?.balance_due ?? 0) <= 0">
              <div class="flex items-center gap-2 px-3 py-2 rounded-xl"
                   style="background:rgba(255,255,255,0.12)">
                <svg class="w-5 h-5" style="color:#86efac" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                <span class="text-sm font-bold" style="color:#86efac">Fully Paid</span>
              </div>
            </template>
          </div>
        </div>
      </div>

      
      <div class="border-t px-6 py-3 flex items-center gap-0" style="background:rgba(0,0,0,0.18);border-color:rgba(255,255,255,0.1)">
        <template x-for="(step, i) in progressSteps()" :key="step.key">
          <div class="flex items-center flex-1 min-w-0">
            <div class="flex items-center gap-1.5 flex-shrink-0">
              <div class="w-6 h-6 rounded-full flex items-center justify-center flex-shrink-0 text-xs font-bold transition-all"
                   :style="step.done ? 'background:#22c55e;color:#fff' : step.active ? 'background:rgba(255,255,255,0.25);color:#fff;border:2px solid rgba(255,255,255,0.5)' : 'background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.35);border:1px solid rgba(255,255,255,0.15)'">
                <template x-if="step.done">
                  <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="!step.done">
                  <span x-text="i+1"></span>
                </template>
              </div>
              <span class="text-xs font-semibold hidden sm:block"
                    :style="step.done ? 'color:#86efac' : step.active ? 'color:rgba(255,255,255,0.9)' : 'color:rgba(255,255,255,0.3)'"
                    x-text="step.label"></span>
            </div>
            <template x-if="i < progressSteps().length - 1">
              <div class="flex-1 h-px mx-2" style="background:rgba(255,255,255,0.15)"></div>
            </template>
          </div>
        </template>
      </div>
    </div>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
      <div class="card p-4 text-center">
        <div class="text-2xl font-extrabold text-gray-800 dark:text-gray-100 tabular-nums" x-text="po?.items?.length ?? 0"></div>
        <div class="text-xs text-gray-400 mt-1 font-medium">Line Items</div>
      </div>
      <div class="card p-4 text-center">
        <div class="text-2xl font-extrabold tabular-nums" style="color:#065f46" x-text="fmtMoney(po?.subtotal ?? 0)"></div>
        <div class="text-xs text-gray-400 mt-1 font-medium">Subtotal</div>
      </div>
      <div class="card p-4 text-center">
        <div class="text-2xl font-extrabold text-green-600 tabular-nums" x-text="fmtMoney(po?.paid_amount ?? 0)"></div>
        <div class="text-xs text-gray-400 mt-1 font-medium">Paid</div>
      </div>
      <div class="card p-4 text-center">
        <div class="text-2xl font-extrabold tabular-nums"
             :class="parseFloat(po?.balance_due ?? 0) > 0 ? 'text-red-600' : 'text-gray-300 dark:text-gray-600'"
             x-text="fmtMoney(po?.balance_due ?? 0)"></div>
        <div class="text-xs text-gray-400 mt-1 font-medium">Balance Due</div>
      </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

      
      <div class="lg:col-span-2 space-y-5">

        
        <div class="card overflow-hidden">
          <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between"
               style="background:linear-gradient(90deg,#f0fdf4,#fff)">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#dcfce7">
                <svg class="w-4 h-4" style="color:#15803d" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
              </div>
              <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Invoice Items</h3>
                <p class="text-xs text-gray-400" x-text="(po?.items?.length ?? 0) + ' product(s)'"></p>
              </div>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
              <thead>
                <tr style="background:#f8fafc">
                  <th class="table-hd">Product</th>
                  <th class="table-hd text-right">Qty</th>
                  <th class="table-hd text-right">Received</th>
                  <th class="table-hd text-right">Unit Price</th>
                  <th class="table-hd text-right">Tax</th>
                  <th class="table-hd text-right">Total</th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-50 dark:divide-gray-700/40">
                <template x-for="item in (po?.items ?? [])" :key="item.id">
                  <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/20 transition-colors">
                    <td class="table-td">
                      <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center text-sm font-bold"
                             style="background:linear-gradient(135deg,#e0f2fe,#bae6fd);color:#0369a1"
                             x-text="(item.product_name || '?')[0].toUpperCase()"></div>
                        <div>
                          <div class="font-semibold text-gray-800 dark:text-gray-100 text-sm leading-tight" x-text="item.product_name"></div>
                          <div class="text-xs text-gray-400 font-mono mt-0.5" x-text="(item.product_code ?? '') + (item.unit ? ' · ' + item.unit : '')"></div>
                        </div>
                      </div>
                    </td>
                    <td class="table-td text-right">
                      <span class="font-bold text-gray-700 dark:text-gray-300 tabular-nums" x-text="parseFloat(item.quantity)"></span>
                    </td>
                    <td class="table-td text-right">
                      <span class="tabular-nums font-semibold"
                            :class="parseFloat(item.received_quantity) >= parseFloat(item.quantity) ? 'text-green-600' : parseFloat(item.received_quantity) > 0 ? 'text-amber-600' : 'text-gray-400'"
                            x-text="parseFloat(item.received_quantity ?? 0)"></span>
                      <template x-if="parseFloat(item.received_quantity) >= parseFloat(item.quantity)">
                        <svg class="w-3.5 h-3.5 text-green-500 inline ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                      </template>
                    </td>
                    <td class="table-td text-right tabular-nums text-gray-600 dark:text-gray-400" x-text="fmtMoney(item.unit_price)"></td>
                    <td class="table-td text-right">
                      <span class="text-xs tabular-nums text-gray-400" x-text="(item.tax_percent ?? 0) + '%'"></span>
                    </td>
                    <td class="table-td text-right">
                      <span class="font-bold tabular-nums text-sm" style="color:#065f46" x-text="fmtMoney(item.total)"></span>
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
          </div>
          
          <div class="border-t border-gray-100 dark:border-gray-700 px-5 py-3.5"
               style="background:linear-gradient(90deg,#f0fdf4,#f8fafc)">
            <div class="flex flex-col items-end gap-1 text-sm">
              <div class="flex justify-between w-52">
                <span class="text-gray-500">Subtotal</span>
                <span class="tabular-nums font-medium text-gray-700" x-text="fmtMoney(po?.subtotal ?? 0)"></span>
              </div>
              <div class="flex justify-between w-52" x-show="parseFloat(po?.tax_amount) > 0">
                <span class="text-gray-500">Tax</span>
                <span class="tabular-nums text-gray-600" x-text="fmtMoney(po?.tax_amount ?? 0)"></span>
              </div>
              <div class="flex justify-between w-52 pt-2 border-t border-dashed border-gray-200">
                <span class="font-bold text-gray-700">Invoice Total</span>
                <span class="font-extrabold tabular-nums text-base" style="color:#065f46" x-text="fmtMoney(po?.total ?? 0)"></span>
              </div>
            </div>
          </div>
        </div>

        
        <div class="card overflow-hidden" x-show="(po?.grns ?? []).length > 0">
          <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2"
               style="background:linear-gradient(90deg,#f0f9ff,#fff)">
            <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#dbeafe">
              <svg class="w-4 h-4" style="color:#1d4ed8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
              <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Goods Receipt Notes</h3>
              <p class="text-xs text-gray-400" x-text="(po?.grns?.length ?? 0) + ' GRN(s)'"></p>
            </div>
          </div>
          <div class="divide-y divide-gray-50 dark:divide-gray-700/40">
            <template x-for="grn in (po?.grns ?? [])" :key="grn.id">
              <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                       :style="grn.status === 'confirmed' ? 'background:#dcfce7' : 'background:#fefce8'">
                    <svg class="w-4 h-4" :style="grn.status === 'confirmed' ? 'color:#15803d' : 'color:#a16207'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                  </div>
                  <div>
                    <a :href="BASE + '/grns/' + grn.id"
                       class="text-sm font-bold font-mono hover:underline" style="color:#1d4ed8"
                       x-text="grn.grn_number"></a>
                    <div class="text-xs text-gray-400 mt-0.5" x-text="fmtDate(grn.received_date)"></div>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-xs px-2.5 py-1 rounded-full font-semibold"
                        :class="grn.status === 'confirmed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'"
                        x-text="grn.status === 'confirmed' ? 'Received' : 'Pending Receipt'"></span>
                  <template x-if="grn.status === 'draft'">
                    <button @click="openReceive()"
                            class="text-xs font-bold px-2.5 py-1 rounded-lg transition-all"
                            style="background:#dcfce7;color:#15803d;border:1px solid #86efac">
                      Receive
                    </button>
                  </template>
                </div>
              </div>
            </template>
          </div>
        </div>

        
        <div class="card overflow-hidden">
          <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between"
               style="background:linear-gradient(90deg,#eef2ff,#fff)">
            <div class="flex items-center gap-2">
              <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#e0e7ff">
                <svg class="w-4 h-4" style="color:#4338ca" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
              </div>
              <div>
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Payment History</h3>
                <p class="text-xs text-gray-400"
                   x-text="(po?.payments?.length ?? 0) + ' payment(s) · Paid: ' + fmtMoney(po?.paid_amount ?? 0)"></p>
              </div>
            </div>
            <template x-if="parseFloat(po?.balance_due ?? 0) > 0 && po?.status !== 'cancelled'">
              <button @click="openPay()"
                      class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all"
                      style="background:#e0e7ff;color:#4338ca;border:1px solid #c7d2fe">
                + Record Payment
              </button>
            </template>
          </div>

          <template x-if="(po?.payments ?? []).length === 0">
            <div class="px-5 py-10 text-center">
              <svg class="w-10 h-10 mx-auto mb-3 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
              <p class="text-sm text-gray-400">No payments recorded yet</p>
            </div>
          </template>

          <div x-show="(po?.payments ?? []).length > 0">
            <div class="divide-y divide-gray-50 dark:divide-gray-700/40">
              <template x-for="pmt in (po?.payments ?? [])" :key="pmt.id">
                <div class="px-5 py-3.5 flex items-center justify-between hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-colors">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 text-lg"
                         :style="pmtIconStyle(pmt.payment_method)">
                      <span x-text="pmtIcon(pmt.payment_method)"></span>
                    </div>
                    <div>
                      <div class="text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="pmtLabel(pmt.payment_method)"></div>
                      <div class="text-xs text-gray-400 mt-0.5"
                           x-text="fmtDate(pmt.payment_date) + (pmt.reference_number ? ' · Ref: ' + pmt.reference_number : '') + (pmt.cheque?.cheque_number ? ' · Cheque #' + pmt.cheque.cheque_number : '')"></div>
                    </div>
                  </div>
                  <div class="flex items-center gap-3">
                    <div class="text-base font-extrabold tabular-nums text-green-700" x-text="fmtMoney(pmt.amount)"></div>
                    <button x-show="isSuperAdmin && canDeletePmt(pmt)"
                            @click="deletePmt(pmt)"
                            :disabled="deletingPmtId === pmt.id"
                            title="Delete this payment (e.g. wrong amount entered)"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 dark:bg-red-900/10 dark:border-red-800 dark:text-red-400 transition-colors disabled:opacity-60 disabled:cursor-not-allowed flex-shrink-0">
                      <svg x-show="deletingPmtId !== pmt.id" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                      <svg x-show="deletingPmtId === pmt.id" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                      <span x-text="deletingPmtId === pmt.id ? 'Deleting…' : 'Delete'"></span>
                    </button>
                    <span x-show="isSuperAdmin && pmt.cheque && !canDeletePmt(pmt)" class="text-gray-300 text-xs flex-shrink-0" title="Cheque already processed — reverse its status in Manage Cheque first">—</span>
                  </div>
                </div>
              </template>
            </div>

            
            <div class="border-t border-gray-100 dark:border-gray-700 grid grid-cols-3 divide-x divide-gray-100 dark:divide-gray-700">
              <div class="py-3 px-4 text-center">
                <div class="text-xs text-gray-400 mb-1 font-medium">Invoice Total</div>
                <div class="text-sm font-bold tabular-nums text-gray-700 dark:text-gray-300" x-text="fmtMoney(po?.total ?? 0)"></div>
              </div>
              <div class="py-3 px-4 text-center">
                <div class="text-xs text-gray-400 mb-1 font-medium">Paid</div>
                <div class="text-sm font-bold tabular-nums text-green-700" x-text="fmtMoney(po?.paid_amount ?? 0)"></div>
              </div>
              <div class="py-3 px-4 text-center">
                <div class="text-xs text-gray-400 mb-1 font-medium">Balance</div>
                <div class="text-sm font-bold tabular-nums"
                     :class="parseFloat(po?.balance_due ?? 0) > 0 ? 'text-red-600' : 'text-gray-400'"
                     x-text="fmtMoney(po?.balance_due ?? 0)"></div>
              </div>
            </div>
          </div>
        </div>

        
        <div class="card px-5 py-4 space-y-3" x-show="po?.notes || po?.terms">
          <div x-show="po?.notes">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Notes</p>
            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed" x-text="po?.notes"></p>
          </div>
          <div x-show="po?.terms">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Terms & Conditions</p>
            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed" x-text="po?.terms"></p>
          </div>
        </div>

      </div>

      
      <div class="space-y-4">

        
        <div class="card overflow-hidden">
          <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2"
               style="background:#fafafa">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Invoice Details</h3>
          </div>
          <div class="px-5 py-4 space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-xs font-medium text-gray-400">Invoice #</span>
              <span class="font-mono font-bold text-gray-800 dark:text-gray-100 text-sm" x-text="po?.po_number ?? '—'"></span>
            </div>
            <template x-if="po?.supplier_invoice_ref">
              <div class="flex justify-between items-center">
                <span class="text-xs font-medium text-gray-400">Supplier Ref</span>
                <span class="font-mono text-xs bg-gray-100 dark:bg-gray-800 px-2 py-0.5 rounded text-gray-600 dark:text-gray-300" x-text="po?.supplier_invoice_ref"></span>
              </div>
            </template>
            <div class="flex justify-between items-start">
              <span class="text-xs font-medium text-gray-400">Supplier</span>
              <span class="font-semibold text-gray-800 dark:text-gray-100 text-sm text-right max-w-[60%]" x-text="po?.supplier?.name ?? '—'"></span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-xs font-medium text-gray-400">Branch</span>
              <span class="text-sm text-gray-700 dark:text-gray-300" x-text="po?.branch?.name ?? '—'"></span>
            </div>
            <div class="border-t border-dashed border-gray-100 dark:border-gray-700 pt-3 space-y-3">
              <div class="flex justify-between items-center">
                <span class="text-xs font-medium text-gray-400">Invoice Date</span>
                <span class="text-sm text-gray-700 dark:text-gray-300" x-text="fmtDate(po?.order_date)"></span>
              </div>
              <div class="flex justify-between items-center" x-show="po?.due_date">
                <span class="text-xs font-medium text-gray-400">Due Date</span>
                <span class="text-sm font-semibold"
                      :class="isOverdue() ? 'text-red-600' : 'text-gray-700 dark:text-gray-300'"
                      x-text="fmtDate(po?.due_date)"></span>
              </div>
              <div class="flex justify-between items-center" x-show="po?.expected_date">
                <span class="text-xs font-medium text-gray-400">Expected</span>
                <span class="text-sm text-gray-700 dark:text-gray-300" x-text="fmtDate(po?.expected_date)"></span>
              </div>
              <div class="flex justify-between items-center" x-show="po?.reference">
                <span class="text-xs font-medium text-gray-400">Our Ref</span>
                <span class="font-mono text-xs text-gray-600 dark:text-gray-300" x-text="po?.reference"></span>
              </div>
            </div>
          </div>
        </div>

        
        <div class="card overflow-hidden">
          <div class="px-5 py-3 border-b border-gray-100 dark:border-gray-700 flex items-center gap-2"
               style="background:#fafafa">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Payment Method</h3>
          </div>
          <div class="px-5 py-4 space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-xs font-medium text-gray-400">Method</span>
              <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                    :class="pmClass(po?.payment_method)"
                    x-text="pmLabel(po?.payment_method)"></span>
            </div>
            <div class="flex justify-between items-center" x-show="po?.payment_method === 'on_account' && po?.payment_terms_days">
              <span class="text-xs font-medium text-gray-400">Terms</span>
              <span class="text-sm text-gray-700 dark:text-gray-300" x-text="po?.payment_terms_days + ' days'"></span>
            </div>
            <div class="flex justify-between items-center" x-show="po?.account_id && (po?.payment_method === 'cash' || po?.payment_method === 'bank_transfer')">
              <span class="text-xs font-medium text-gray-400" x-text="po?.payment_method === 'cash' ? 'Cash Acct' : 'Bank Acct'"></span>
              <span class="text-xs text-gray-700 dark:text-gray-300 text-right max-w-[60%]" x-text="po?.account?.name ?? ('Account #' + po?.account_id)"></span>
            </div>
            <template x-if="po?.payment_method === 'cheque'">
              <div class="mt-1 p-3 rounded-xl space-y-2"
                   :style="po?.cheque_type === 'received' ? 'background:#ede9fe;border:1px solid #c4b5fd' : 'background:#fffbeb;border:1px solid #fde68a'">
                <p class="text-xs font-bold" :style="po?.cheque_type === 'received' ? 'color:#5b21b6' : 'color:#92400e'"
                   x-text="po?.cheque_type === 'received' ? '📨 Received Cheque' : '📄 Issued Cheque'"></p>
                <div class="space-y-1.5 text-xs">
                  <div class="flex justify-between" x-show="po?.cheque_number">
                    <span class="text-gray-500">Cheque #</span>
                    <span class="font-mono font-bold" x-text="po?.cheque_number"></span>
                  </div>
                  <div class="flex justify-between" x-show="po?.cheque_bank_name">
                    <span class="text-gray-500">Bank</span>
                    <span x-text="po?.cheque_bank_name"></span>
                  </div>
                  <div class="flex justify-between" x-show="po?.cheque_date">
                    <span class="text-gray-500">Date</span>
                    <span x-text="fmtDate(po?.cheque_date)"></span>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>

        
        <div class="card p-4 space-y-2">
          <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Quick Actions</p>
          <template x-if="hasDraftGrn()">
            <button @click="openReceive()"
                    class="w-full flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all text-left"
                    style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
              <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
              Receive Items (GRN)
            </button>
          </template>
          <template x-if="parseFloat(po?.balance_due ?? 0) > 0 && po?.status !== 'cancelled'">
            <button @click="openPay()"
                    class="w-full flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all text-left"
                    style="background:#eef2ff;color:#4338ca;border:1px solid #c7d2fe">
              <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
              Record Payment
            </button>
          </template>
          <a :href="BASE + '/purchase-orders'"
             class="w-full flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all text-left"
             style="background:#f9fafb;color:#4b5563;border:1px solid #e5e7eb">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            View All Invoices
          </a>
        </div>

      </div>
    </div>
  </div>

  
  <div x-show="!loading && !po" x-cloak class="flex flex-col items-center justify-center py-32 text-gray-400">
    <svg class="w-16 h-16 opacity-20 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
      <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
    </svg>
    <p class="font-semibold text-lg">Supplier invoice not found</p>
    <a href="<?php echo e(url('/purchase-orders')); ?>" class="mt-3 text-sm text-indigo-600 hover:underline">Back to list</a>
  </div>

  
  <template x-if="showReceive">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(15,23,42,0.6);backdrop-filter:blur(6px)"
         @click.self="showReceive = false">
      <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[90vh]">

        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between flex-shrink-0"
             style="background:linear-gradient(135deg,#065f46,#047857)">
          <div>
            <h3 class="text-base font-bold text-white">Receive Items</h3>
            <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)"
               x-text="'Invoice: ' + (po?.po_number ?? '') + ' · Review quantities, costs, and selling prices'"></p>
          </div>
          <button @click="showReceive = false" class="text-white/60 hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <div class="overflow-auto flex-1">
          <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
            <thead class="sticky top-0 z-10">
              <tr style="background:#f0fdf4">
                <th class="table-hd">Product</th>
                <th class="table-hd text-center">In Stock</th>
                <th class="table-hd text-center">Qty</th>
                <th class="table-hd text-center">Cost Price</th>
                <th class="table-hd text-center">Batch #</th>
                <th class="table-hd text-center">Expiry</th>
                <th class="table-hd text-center" style="color:#b45309;background:#fefce8">Curr. Sell</th>
                <th class="table-hd text-center" style="color:#15803d;background:#f0fdf4">New Sell</th>
                <th class="table-hd text-right">Total</th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-50 dark:divide-gray-700/40">
              <template x-for="(row, idx) in receiveFormItems" :key="idx">
                <tr class="hover:bg-gray-50/40 transition-colors">
                  <td class="table-td">
                    <div class="font-semibold text-sm text-gray-800 dark:text-gray-100" x-text="row.product_name"></div>
                    <div class="text-xs text-gray-400 font-mono" x-text="row.unit ?? ''"></div>
                  </td>
                  <td class="table-td text-center">
                    <span class="text-sm font-semibold" :class="row.stock_qty > 0 ? 'text-emerald-600' : 'text-gray-400'"
                          x-text="row.stock_qty"></span>
                  </td>
                  <td class="table-td text-center">
                    <input type="number" x-model.number="row.qty" min="0" step="0.001"
                           class="input text-center w-24 text-sm font-semibold" />
                  </td>
                  <td class="table-td text-center">
                    <input type="number" x-model.number="row.unit_cost" min="0" step="0.01"
                           class="input text-center w-28 text-sm" />
                  </td>
                  <td class="table-td text-center">
                    <input type="text" x-model="row.batch_number"
                           class="input text-center w-28 text-sm font-mono" placeholder="—" />
                  </td>
                  <td class="table-td text-center">
                    <input type="date" x-model="row.expiry_date"
                           class="input text-center w-36 text-sm" />
                  </td>
                  <td class="table-td text-center" style="background:#fffbeb">
                    <span class="text-sm font-semibold tabular-nums" style="color:#b45309"
                          x-text="row.current_sell_price > 0 ? 'Rs.' + parseFloat(row.current_sell_price).toLocaleString() : '—'"></span>
                  </td>
                  <td class="table-td text-center" style="background:#f0fdf4">
                    <input type="number" x-model.number="row.selling_price" min="0" step="0.01"
                           class="input text-center w-28 text-sm font-semibold" style="border-color:#86efac"
                           placeholder="Optional" />
                  </td>
                  <td class="table-td text-right">
                    <span class="font-bold tabular-nums text-sm" style="color:#065f46"
                          x-text="fmtMoney(row.qty * row.unit_cost)"></span>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between flex-shrink-0"
             style="background:#f8fafc">
          <div class="text-sm text-gray-500">
            Total: <span class="font-bold text-gray-800" x-text="fmtMoney(receiveFormItems.reduce((s,r)=>s+r.qty*r.unit_cost,0))"></span>
          </div>
          <div class="flex gap-3">
            <button @click="showReceive = false" class="btn-secondary">Cancel</button>
            <button @click="submitReceive()" :disabled="receiveLoading"
                    class="btn-primary flex items-center gap-2">
              <svg x-show="receiveLoading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
              </svg>
              <span x-text="receiveLoading ? 'Receiving…' : 'Confirm Receipt & Update Stock'"></span>
            </button>
          </div>
        </div>

      </div>
    </div>
  </template>

  
  <template x-if="showPay">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(15,23,42,0.55);backdrop-filter:blur(4px)"
         @click.self="showPay = false">
      <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md">

        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 rounded-t-2xl"
             style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-base font-bold text-white">Record Payment</h3>
              <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.6)"
                 x-text="(po?.po_number ?? '') + ' · Balance: ' + fmtMoney(po?.balance_due ?? 0)"></p>
            </div>
            <button @click="showPay = false" class="text-white/60 hover:text-white">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>

        <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">

          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Payment Method</label>
            <div class="grid grid-cols-3 gap-2">
              <template x-for="m in payMethods" :key="m.v">
                <button type="button"
                        @click="pf.payment_method = m.v; pf.account_id = m.v === 'cash' ? (cashAccounts[0]?.id ?? null) : (bankAccounts[0]?.id ?? null)"
                        :style="pf.payment_method === m.v ? `background:${m.bg};border:2px solid ${m.border};color:${m.color}` : 'background:#f9fafb;border:2px solid #e5e7eb;color:#6b7280'"
                        class="py-2 rounded-xl text-xs font-bold flex flex-col items-center gap-1 transition-all">
                  <span x-text="m.icon" class="text-base"></span>
                  <span x-text="m.label"></span>
                </button>
              </template>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Amount *</label>
              <input x-model="pf.amount" type="number" step="0.01" min="0.01" class="input" placeholder="0.00" />
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Payment Date *</label>
              <input x-model="pf.payment_date" type="date" class="input" />
            </div>
          </div>

          <template x-if="pf.payment_method === 'cash'">
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Cash Account *</label>
              <select x-model="pf.account_id" class="input">
                <option :value="null">— Select —</option>
                <template x-for="a in cashAccounts" :key="a.id">
                  <option :value="a.id" x-text="a.name"></option>
                </template>
              </select>
            </div>
          </template>

          <template x-if="pf.payment_method === 'bank_transfer'">
            <div>
              <label class="block text-xs font-semibold text-gray-500 mb-1">Bank Account *</label>
              <select x-model="pf.account_id" class="input">
                <option :value="null">— Select —</option>
                <template x-for="a in bankAccounts" :key="a.id">
                  <option :value="a.id" x-text="a.name"></option>
                </template>
              </select>
            </div>
          </template>

          <template x-if="pf.payment_method === 'cheque'">
            <div class="space-y-3">
              <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Cheque Type</label>
                <div class="flex gap-3">
                  <label class="flex items-center gap-2 cursor-pointer text-sm">
                    <input type="radio" x-model="pf.cheque_type" value="issued" class="accent-indigo-600" /> We Issue
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer text-sm">
                    <input type="radio" x-model="pf.cheque_type" value="received" class="accent-indigo-600" /> Use Received
                  </label>
                </div>
              </div>
              <template x-if="pf.cheque_type === 'issued'">
                <div class="space-y-3">
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Bank Account *</label>
                    <select x-model="pf.account_id" class="input">
                      <option :value="null">— Select —</option>
                      <template x-for="a in bankAccounts" :key="a.id">
                        <option :value="a.id" x-text="a.name"></option>
                      </template>
                    </select>
                  </div>
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label class="block text-xs font-semibold text-gray-500 mb-1">Cheque Number *</label>
                      <input x-model="pf.cheque_number" type="text" class="input" placeholder="e.g. 001234" />
                    </div>
                    <div>
                      <label class="block text-xs font-semibold text-gray-500 mb-1">Bank Name *</label>
                      <div x-data="{bq:'',bOpen:false}" @click.outside="bOpen=false" class="relative">
                        <input type="text" :value="pf.bank_name"
                          @input="pf.bank_name=$event.target.value;bq=$event.target.value;bOpen=true"
                          @focus="bq=pf.bank_name||'';bOpen=true" @keydown.escape="bOpen=false"
                          class="input" placeholder="Search bank…" autocomplete="off" />
                        <ul x-show="bOpen" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-44 overflow-y-auto">
                          <template x-for="b in banks.filter(b=>b.name.toLowerCase().includes(bq.toLowerCase()))" :key="b.id">
                            <li @mousedown.prevent="pf.bank_name=b.name;bq=b.name;bOpen=false"
                                :class="pf.bank_name===b.name?'bg-indigo-50 text-indigo-700 font-medium':'hover:bg-gray-50 text-gray-700'"
                                class="px-3 py-2 text-sm cursor-pointer" x-text="b.name"></li>
                          </template>
                          <li x-show="!banks.filter(b=>b.name.toLowerCase().includes(bq.toLowerCase())).length"
                              class="px-3 py-2 text-sm text-gray-400 text-center">No banks found</li>
                        </ul>
                      </div>
                    </div>
                  </div>
                  <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Cheque Date *</label>
                    <input x-model="pf.cheque_date" type="date" class="input" />
                  </div>
                </div>
              </template>
              <template x-if="pf.cheque_type === 'received'">
                <div>
                  <label class="block text-xs font-semibold text-gray-500 mb-1">Select Received Cheque *</label>
                  <select x-model="pf.received_cheque_id" class="input">
                    <option :value="null">— Select in-hand cheque —</option>
                    <template x-for="c in receivedCheques" :key="c.id">
                      <option :value="c.id" x-text="(c.cheque_number ?? c.id) + ' — Rs.' + parseFloat(c.amount||0).toLocaleString()"></option>
                    </template>
                  </select>
                </div>
              </template>
            </div>
          </template>

          <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Reference / Notes</label>
            <input x-model="pf.reference_number" type="text" class="input" placeholder="Optional reference…" />
          </div>

        </div>

        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
          <button @click="showPay = false" class="btn-secondary">Cancel</button>
          <button @click="submitPay()" :disabled="paying" class="btn-primary flex items-center gap-2">
            <svg x-show="paying" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
            </svg>
            <span x-text="paying ? 'Recording…' : 'Record Payment'"></span>
          </button>
        </div>
      </div>
    </div>
  </template>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function invShowPage() {
    const id = window.location.pathname.split('/').pop();
    return {
        po: null, loading: true, isSuperAdmin: false, deletingPmtId: null,
        showPay: false, paying: false,
        showReceive: false, receiveLoading: false,
        receiveFormItems: [],
        cashAccounts: [], bankAccounts: [], receivedCheques: [],
        payMethods: [
            { v:'cash',          label:'Cash',   icon:'💵', bg:'#f0fdf4', border:'#22c55e', color:'#15803d' },
            { v:'bank_transfer', label:'Bank',   icon:'🏦', bg:'#faf5ff', border:'#a855f7', color:'#7e22ce' },
            { v:'cheque',        label:'Cheque', icon:'📄', bg:'#fffbeb', border:'#f59e0b', color:'#b45309' },
        ],
        pf: { amount:0, payment_method:'cash', payment_date: new Date().toISOString().slice(0,10),
              reference_number:'', account_id:null, cheque_type:'issued',
              received_cheque_id:null, cheque_number:'', bank_name:'', cheque_date:'' },
        banks: [],

        async init() {
            try {
                const u = JSON.parse(localStorage.getItem('medri_user') || '{}');
                this.isSuperAdmin = !!u.is_super_admin || (u.roles ?? []).includes('super_admin');
            } catch (_) {}
            try {
                const [poR, accR, chqR] = await Promise.all([
                    apiFetch('/purchase-orders/' + id).then(r => r.json()),
                    apiFetch('/accounting/accounts').then(r => r.json()),
                    apiFetch('/cheques?direction=received&status=in_hand&per_page=100').then(r => r.json()),
                ]);
                this.po = poR.data ?? poR ?? null;
                const accounts = Array.isArray(accR) ? accR : (accR.data ?? []);
                this.cashAccounts    = accounts.filter(a => a.is_cash_account);
                this.bankAccounts    = accounts.filter(a => a.is_bank_account);
                this.receivedCheques = chqR.data ?? chqR ?? [];
                this.banks = await loadBanks();
            } catch {
                toast('Failed to load invoice', 'error');
            } finally {
                this.loading = false;
            }
        },

        hasDraftGrn() {
            return (this.po?.grns ?? []).some(g => g.status === 'draft');
        },

        progressSteps() {
            const s   = this.po?.status ?? '';
            const pay = this.po?.payment_status ?? 'unpaid';
            const steps = [
                { key:'created',  label:'Created',  done: true,  active: false },
                { key:'received', label:'Received', done: ['received','partially_received'].includes(s) || s === 'cancelled', active: ['confirmed','partially_received'].includes(s) },
                { key:'paid',     label:'Paid',     done: pay === 'paid', active: pay === 'partially_paid' },
            ];
            return steps;
        },

        async openReceive() {
            const draftGrn = (this.po?.grns ?? []).find(g => g.status === 'draft');
            if (!draftGrn) return;

            const branchId = this.po?.branch_id;
            const prodIds  = (draftGrn.items ?? []).map(i => i.product_id);

            let productsMap = {};
            try {
                const r  = await apiFetch('/products?per_page=999' + (branchId ? '&branch_id=' + branchId : '')).then(r => r.json());
                const ps = r.data ?? r ?? [];
                ps.forEach(p => { productsMap[p.id] = p; });
            } catch {}

            this.receiveFormItems = (draftGrn.items ?? []).map(item => {
                const prod = productsMap[item.product_id] ?? {};
                const stockArr = prod.branch_stocks ?? prod.branchStocks ?? [];
                const stockQty = stockArr.reduce((s, b) => s + parseFloat(b.quantity ?? 0), 0);
                return {
                    grn_item_id:        item.id,
                    product_id:         item.product_id,
                    product_name:       item.product_name ?? prod.name ?? '—',
                    unit:               item.unit ?? prod.unit ?? '',
                    qty:                parseFloat(item.quantity_received ?? 0),
                    unit_cost:          parseFloat(item.unit_cost ?? 0),
                    batch_number:       item.batch_number ?? '',
                    expiry_date:        item.expiry_date ?? '',
                    stock_qty:          stockQty,
                    current_sell_price: parseFloat(prod.selling_price ?? 0),
                    selling_price:      parseFloat(prod.selling_price ?? 0),
                };
            });

            this._receiveGrnId = draftGrn.id;
            this.showReceive   = true;
        },

        async submitReceive() {
            this.receiveLoading = true;
            try {
                const r = await apiFetch('/grns/' + this._receiveGrnId + '/confirm', {
                    method: 'POST',
                    body: JSON.stringify({
                        items: this.receiveFormItems.map(row => ({
                            grn_item_id:       row.grn_item_id,
                            quantity_received: row.qty,
                            unit_cost:         row.unit_cost,
                            batch_number:      row.batch_number || null,
                            expiry_date:       row.expiry_date  || null,
                            selling_price:     row.selling_price > 0 ? row.selling_price : null,
                        })),
                    }),
                });
                const d = await r.json();
                if (r.ok) {
                    if (this.po?.grns) {
                        const grn = this.po.grns.find(g => g.id === this._receiveGrnId);
                        if (grn) grn.status = 'confirmed';
                    }
                    this.showReceive = false;
                    toast('Items received and stock updated', 'success');
                    // Reload to reflect updated status
                    setTimeout(async () => {
                        const fresh = await apiFetch('/purchase-orders/' + id).then(r => r.json());
                        this.po = fresh.data ?? fresh ?? this.po;
                    }, 600);
                } else {
                    toast(d.message ?? 'Failed to receive items', 'error');
                }
            } catch {
                toast('Network error', 'error');
            } finally {
                this.receiveLoading = false;
            }
        },

        isOverdue() {
            if (!this.po?.due_date || this.po?.payment_status === 'paid' || this.po?.status === 'cancelled') return false;
            return new Date(this.po.due_date) < new Date();
        },

        openPay() {
            this.pf = { amount: parseFloat(this.po?.balance_due ?? 0),
                        payment_method:'cash',
                        payment_date: new Date().toISOString().slice(0,10),
                        reference_number:'', account_id: this.cashAccounts[0]?.id ?? null,
                        cheque_type:'issued', received_cheque_id:null,
                        cheque_number:'', bank_name:'', cheque_date:'' };
            this.showPay = true;
        },

        async submitPay() {
            if (!this.pf.amount || this.pf.amount <= 0) { toast('Enter a payment amount', 'error'); return; }
            if ((this.pf.payment_method === 'cash' || this.pf.payment_method === 'bank_transfer') && !this.pf.account_id) {
                toast('Please select the account', 'error'); return;
            }
            if (this.pf.payment_method === 'cheque' && this.pf.cheque_type === 'issued') {
                if (!this.pf.account_id)    { toast('Select a bank account', 'error'); return; }
                if (!this.pf.cheque_number) { toast('Enter cheque number', 'error'); return; }
                if (!this.pf.bank_name)     { toast('Enter bank name', 'error'); return; }
                if (!this.pf.cheque_date)   { toast('Enter cheque date', 'error'); return; }
            }
            if (this.pf.payment_method === 'cheque' && this.pf.cheque_type === 'received' && !this.pf.received_cheque_id) {
                toast('Select a received cheque', 'error'); return;
            }
            this.paying = true;
            try {
                const r = await apiFetch('/purchase-orders/' + id + '/payment', {
                    method: 'POST', body: JSON.stringify({
                        amount:             this.pf.amount,
                        payment_method:     this.pf.payment_method,
                        payment_date:       this.pf.payment_date,
                        reference_number:   this.pf.reference_number || null,
                        account_id:         this.pf.account_id ? parseInt(this.pf.account_id) : null,
                        cheque_type:        this.pf.cheque_type,
                        received_cheque_id: this.pf.received_cheque_id ? parseInt(this.pf.received_cheque_id) : null,
                        cheque_number:      this.pf.cheque_number || null,
                        bank_name:          this.pf.bank_name || null,
                        cheque_date:        this.pf.cheque_date || null,
                    }),
                });
                const d = await r.json();
                if (r.ok) {
                    if (d.po) this.po = { ...this.po, ...d.po };
                    this.showPay = false;
                    toast('Payment recorded and journal posted', 'success');
                } else {
                    toast(d.message ?? 'Payment failed', 'error');
                }
            } finally { this.paying = false; }
        },

        canDeletePmt(pmt) {
            if (!pmt.cheque) return true;
            const expected = pmt.cheque_type === 'received' ? 'transferred' : 'in_hand';
            return pmt.cheque.status === expected;
        },
        async deletePmt(pmt) {
            const warning = pmt.cheque
                ? `Delete this ${fmtMoney(pmt.amount)} cheque payment (#${pmt.cheque.cheque_number})? The invoice balance will be restored${pmt.cheque_type === 'received' ? " and the customer's cheque returned to In Hand" : ' and the cheque removed'}. This cannot be undone.`
                : `Delete this ${fmtMoney(pmt.amount)} payment? The invoice balance will be restored. This cannot be undone.`;
            if (!confirm(warning)) return;
            this.deletingPmtId = pmt.id;
            try {
                const r = await apiFetch('/purchase-orders/' + this.po.id + '/payments/' + pmt.id, { method: 'DELETE' });
                const d = await r.json();
                if (r.ok) { this.po = d.po ?? this.po; toast('Payment deleted', 'success'); }
                else toast(d.message ?? 'Failed to delete payment', 'error');
            } catch (e) { toast(e.message || 'Error', 'error'); }
            finally { this.deletingPmtId = null; }
        },
        pmtLabel(m)  { return { cash:'Cash', bank_transfer:'Bank Transfer', cheque:'Cheque', on_account:'On Account' }[m] ?? (m ?? '—'); },
        pmtIcon(m)   { return { cash:'💵', bank_transfer:'🏦', cheque:'📄', on_account:'📋' }[m] ?? '💳'; },
        pmtIconStyle(m) {
            const s = { cash:'#f0fdf4;border:1px solid #bbf7d0', bank_transfer:'#faf5ff;border:1px solid #e9d5ff', cheque:'#fffbeb;border:1px solid #fde68a', on_account:'#eff6ff;border:1px solid #bfdbfe' };
            return 'background:' + (s[m] ?? '#f3f4f6;border:1px solid #e5e7eb');
        },
        statusClass(s) {
            return { draft:'bg-yellow-100 text-yellow-700', confirmed:'bg-blue-100 text-blue-700', partially_received:'bg-purple-100 text-purple-700', received:'bg-green-100 text-green-700', cancelled:'bg-gray-100 text-gray-500' }[s] ?? 'bg-gray-100 text-gray-500';
        },
        statusLabel(s) {
            return { draft:'Draft', confirmed:'Confirmed', partially_received:'Partial Receipt', received:'Fully Received', cancelled:'Cancelled' }[s] ?? (s ?? '—');
        },
        payBadge(s)  { return { unpaid:'bg-red-100 text-red-700', partially_paid:'bg-yellow-100 text-yellow-700', paid:'bg-green-100 text-green-700' }[s ?? 'unpaid'] ?? 'bg-gray-100 text-gray-500'; },
        payLabel(s)  { return { unpaid:'Unpaid', partially_paid:'Partial', paid:'Paid' }[s ?? 'unpaid'] ?? 'Unpaid'; },
        pmClass(m)   { return { on_account:'bg-blue-100 text-blue-700', cash:'bg-green-100 text-green-700', bank_transfer:'bg-purple-100 text-purple-700', cheque:'bg-amber-100 text-amber-700' }[m] ?? 'bg-gray-100 text-gray-500'; },
        pmLabel(m)   { return { on_account:'On Account', cash:'Cash', bank_transfer:'Bank Transfer', cheque:'Cheque' }[m] ?? (m ?? '—'); },
        fmtMoney(v)  { return 'Rs. ' + (parseFloat(v)||0).toLocaleString('en-LK',{minimumFractionDigits:2,maximumFractionDigits:2}); },
        fmtDate(d)   { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}); },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/purchase/orders-show.blade.php ENDPATH**/ ?>