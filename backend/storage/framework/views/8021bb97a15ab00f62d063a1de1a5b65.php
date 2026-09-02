<?php $__env->startSection('title', 'GRN Detail'); ?>
<?php $__env->startSection('page-title', 'Goods Received Note'); ?>
<?php $__env->startSection('page-desc', 'View receipt details and confirm stock'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="grnShowPage()" x-init="init()" class="max-w-5xl mx-auto pb-12">

  
  <div x-show="loading" class="flex items-center justify-center py-24">
    <svg class="animate-spin w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
    </svg>
  </div>

  <div x-show="!loading && grn">

    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
      <div class="flex items-center gap-3">
        <a href="<?php echo e(url('/grns')); ?>"
           class="w-8 h-8 rounded-lg flex items-center justify-center border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
          <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
          <h2 class="text-xl font-bold text-gray-900 dark:text-white" x-text="grn?.grn_number ?? ('GRN-' + grn?.id)"></h2>
          <p class="text-xs text-gray-500 mt-0.5" x-text="grn?.supplier?.name ?? ''"></p>
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full font-bold" :class="statusClass(grn?.status)" x-text="statusLabel(grn?.status)"></span>
      </div>

      <div class="flex items-center gap-2 flex-wrap">
        <template x-if="grn?.status === 'draft'">
          <button @click="confirmGRN()" :disabled="confirming"
                  class="btn-primary flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span x-text="confirming ? 'Confirming…' : 'Confirm & Update Stock'"></span>
          </button>
        </template>
        <template x-if="grn?.purchase_order_id">
          <a :href="BASE + '/purchase-orders/' + grn.purchase_order_id"
             class="btn-secondary flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            View PO
          </a>
        </template>
        <a href="<?php echo e(url('/grns')); ?>" class="btn-secondary text-sm">Back to List</a>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

      
      <div class="lg:col-span-2 space-y-5">

        
        <div class="card overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700"
               style="background:linear-gradient(135deg,#1e3a5f 0%,#0f2140 100%)">
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,0.15)">
                <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
              </div>
              <div>
                <h3 class="text-sm font-bold text-white">Items Received</h3>
                <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.6)"
                   x-text="(grn?.items?.length ?? 0) + ' line(s)'"></p>
              </div>
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="min-w-full">
              <thead>
                <tr style="background:#f8fafc;border-bottom:1px solid #e2e8f0">
                  <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wide text-gray-400">#</th>
                  <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wide text-gray-400">Product</th>
                  <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-wide text-gray-400">Qty Received</th>
                  <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-wide text-gray-400">Unit Cost</th>
                  <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-wide text-gray-400">Total</th>
                  <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wide text-gray-400">Batch / Expiry</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50 dark:divide-gray-700/40">
                <template x-for="(item, i) in (grn?.items ?? [])" :key="item.id">
                  <tr class="hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
                    <td class="px-5 py-3.5">
                      <div class="w-6 h-6 rounded-md flex items-center justify-center text-xs font-bold"
                           style="background:#e0e7ff;color:#1B3EB6" x-text="i+1"></div>
                    </td>
                    <td class="px-5 py-3.5">
                      <div class="font-semibold text-sm text-gray-800 dark:text-gray-100" x-text="item.product_name ?? item.product?.name ?? '—'"></div>
                      <div class="text-xs text-gray-400 mt-0.5" x-text="item.product?.sku ?? item.unit ?? ''"></div>
                    </td>
                    <td class="px-5 py-3.5 text-right">
                      <span class="font-black text-gray-900 dark:text-white tabular-nums" x-text="item.quantity_received"></span>
                      <span class="text-xs text-gray-400 ml-1" x-text="item.unit ?? ''"></span>
                    </td>
                    <td class="px-5 py-3.5 text-right font-medium text-gray-700 dark:text-gray-300 tabular-nums"
                        x-text="fmtMoney(item.unit_cost ?? 0)"></td>
                    <td class="px-5 py-3.5 text-right font-black text-gray-900 dark:text-white tabular-nums"
                        x-text="fmtMoney(item.total_cost ?? 0)"></td>
                    <td class="px-5 py-3.5">
                      <template x-if="item.batch_number">
                        <div class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold"
                             style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0"
                             x-text="item.batch_number"></div>
                      </template>
                      <template x-if="item.expiry_date">
                        <div class="text-xs mt-0.5"
                             :class="isExpiringSoon(item.expiry_date) ? 'text-red-500 font-bold' : 'text-gray-400'"
                             x-text="'Exp: ' + fmtDate(item.expiry_date)"></div>
                      </template>
                      <template x-if="!item.batch_number && !item.expiry_date">
                        <span class="text-gray-300 text-xs">—</span>
                      </template>
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot>
                <tr style="background:#f8fafc;border-top:2px solid #e2e8f0">
                  <td colspan="4" class="px-5 py-3 text-right text-xs font-black text-gray-500 uppercase tracking-wide">
                    Total Value
                  </td>
                  <td class="px-5 py-3 text-right font-black text-gray-900 dark:text-white tabular-nums"
                      x-text="fmtMoney((grn?.items ?? []).reduce((s,i) => s + parseFloat(i.total_cost ?? 0), 0))"></td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        
        <template x-if="grn?.notes || grn?.delivery_note_number">
          <div class="card px-5 py-4 space-y-2">
            <template x-if="grn?.delivery_note_number">
              <div class="flex items-center gap-2 text-sm">
                <span class="text-gray-400 font-semibold">Delivery Note #:</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="grn.delivery_note_number"></span>
              </div>
            </template>
            <template x-if="grn?.notes">
              <div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wide mb-1">Notes</div>
                <p class="text-sm text-gray-600 dark:text-gray-300" x-text="grn.notes"></p>
              </div>
            </template>
          </div>
        </template>

        
        <template x-if="grn?.status === 'confirmed'">
          <div class="rounded-xl px-5 py-4 flex items-center gap-3"
               style="background:#f0fdf4;border:1px solid #bbf7d0">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#dcfce7">
              <svg class="w-4 h-4" style="color:#16a34a" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <div class="text-sm font-bold text-green-800">Stock Updated</div>
              <div class="text-xs text-green-600 mt-0.5"
                   x-text="'Confirmed' + (grn.confirmed_at ? ' on ' + fmtDate(grn.confirmed_at) : '')"></div>
            </div>
          </div>
        </template>

      </div>

      
      <div class="space-y-5">

        
        <div class="card overflow-hidden">
          <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40">
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Receipt Details</h3>
          </div>
          <div class="px-5 py-4 space-y-3">

            <div class="flex items-start justify-between gap-2 text-sm">
              <span class="text-gray-400 font-medium">GRN #</span>
              <span class="font-mono font-black text-gray-800 dark:text-white"
                    x-text="grn?.grn_number ?? ('GRN-' + grn?.id)"></span>
            </div>

            <div class="flex items-start justify-between gap-2 text-sm">
              <span class="text-gray-400 font-medium">Received Date</span>
              <span class="font-semibold text-gray-800 dark:text-gray-200" x-text="fmtDate(grn?.received_date)"></span>
            </div>

            <div class="flex items-start justify-between gap-2 text-sm">
              <span class="text-gray-400 font-medium">Status</span>
              <span class="text-xs px-2.5 py-1 rounded-full font-bold" :class="statusClass(grn?.status)" x-text="statusLabel(grn?.status)"></span>
            </div>

            <div class="flex items-start justify-between gap-2 text-sm">
              <span class="text-gray-400 font-medium">Branch</span>
              <span class="font-semibold text-gray-800 dark:text-gray-200 text-right" x-text="grn?.branch?.name ?? '—'"></span>
            </div>

            <div class="flex items-start justify-between gap-2 text-sm">
              <span class="text-gray-400 font-medium">Total Items</span>
              <span class="font-bold text-gray-800 dark:text-gray-200"
                    x-text="(grn?.items ?? []).length + ' lines'"></span>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-700 pt-3 flex items-start justify-between gap-2 text-sm">
              <span class="text-gray-400 font-medium">Total Value</span>
              <span class="font-black text-gray-900 dark:text-white"
                    x-text="fmtMoney((grn?.items ?? []).reduce((s,i) => s + parseFloat(i.total_cost ?? 0), 0))"></span>
            </div>

          </div>
        </div>

        
        <div class="card overflow-hidden">
          <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40">
            <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Supplier</h3>
          </div>
          <div class="px-5 py-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-black text-white text-sm flex-shrink-0"
                 style="background:#1B3EB6"
                 x-text="(grn?.supplier?.name || '?').charAt(0).toUpperCase()"></div>
            <div>
              <div class="font-bold text-gray-800 dark:text-gray-100" x-text="grn?.supplier?.name ?? '—'"></div>
              <div class="text-xs text-gray-400 mt-0.5" x-text="grn?.supplier?.phone ?? grn?.supplier?.email ?? ''"></div>
            </div>
          </div>
        </div>

        
        <template x-if="grn?.purchase_order">
          <div class="card overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40">
              <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Purchase Order</h3>
            </div>
            <div class="px-5 py-4">
              <div class="flex items-center justify-between gap-2 mb-2">
                <span class="font-mono font-black text-indigo-600 text-sm" x-text="grn.purchase_order.po_number"></span>
                <span class="text-xs px-2 py-0.5 rounded-full font-bold bg-indigo-50 text-indigo-700"
                      x-text="grn.purchase_order.status"></span>
              </div>
              <a :href="BASE + '/purchase-orders/' + grn.purchase_order.id"
                 class="flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:underline mt-1">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                View Purchase Order
              </a>
            </div>
          </div>
        </template>

        
        <template x-if="grn?.supplier_invoice">
          <div class="card overflow-hidden">
            <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40">
              <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200">Supplier Invoice</h3>
            </div>
            <div class="px-5 py-4 space-y-1.5 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-400">Invoice #</span>
                <span class="font-mono font-bold text-gray-800 dark:text-gray-200" x-text="grn.supplier_invoice.invoice_number"></span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">Amount</span>
                <span class="font-black text-gray-900 dark:text-white" x-text="fmtMoney(grn.supplier_invoice.total_amount ?? 0)"></span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-400">Balance</span>
                <span class="font-bold" :class="(grn.supplier_invoice.balance_due > 0) ? 'text-red-600' : 'text-green-600'"
                      x-text="fmtMoney(grn.supplier_invoice.balance_due ?? 0)"></span>
              </div>
            </div>
          </div>
        </template>

      </div>

    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function grnShowPage() {
    const id = window.location.pathname.split('/').pop();
    return {
        grn:       null,
        loading:   true,
        confirming: false,

        async init() {
            try {
                const res = await apiFetch('/grns/' + id);
                const data = await res.json();
                this.grn = data.data ?? data;
            } catch (e) {
                toast('Failed to load GRN', 'error');
            } finally {
                this.loading = false;
            }
        },

        async confirmGRN() {
            if (!confirm('Confirm this GRN? Stock levels will be updated immediately.')) return;
            this.confirming = true;
            try {
                const confirmRes = await apiFetch('/grns/' + id + '/confirm', { method: 'POST' });
                if (!confirmRes.ok) { const err = await confirmRes.json(); throw new Error(err.message ?? 'Failed'); }
                toast('GRN confirmed — stock updated', 'success');
                const res = await apiFetch('/grns/' + id);
                const data = await res.json();
                this.grn = data.data ?? data;
            } catch (e) {
                toast(e.message ?? 'Failed to confirm GRN', 'error');
            } finally {
                this.confirming = false;
            }
        },

        statusLabel(s) {
            return { draft: 'Draft', confirmed: 'Confirmed', partial: 'Partial', cancelled: 'Cancelled' }[s] ?? (s || '—');
        },
        statusClass(s) {
            return {
                draft:     'bg-yellow-100 text-yellow-700',
                confirmed: 'bg-green-100 text-green-700',
                partial:   'bg-blue-100 text-blue-700',
                cancelled: 'bg-gray-100 text-gray-500',
            }[s] ?? 'bg-gray-100 text-gray-500';
        },
        isExpiringSoon(date) {
            if (!date) return false;
            const days = (new Date(date) - new Date()) / (1000 * 60 * 60 * 24);
            return days < 90;
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\purchase\grns-show.blade.php ENDPATH**/ ?>