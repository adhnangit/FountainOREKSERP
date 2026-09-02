
<?php $__env->startSection('title', 'Create GRN'); ?>
<?php $__env->startSection('page-title', 'Create Goods Received Note'); ?>
<?php $__env->startSection('page-desc', 'Record incoming goods against a purchase order'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="grnCreatePage()" x-init="init()" class="px-6 pb-12">
<form @submit.prevent="submit()">
<div class="flex flex-col lg:flex-row gap-6">

  
  <div class="w-full lg:flex-[65] min-w-0 space-y-5">

    
    <div class="card overflow-hidden">

      
      <div class="flex items-center justify-between px-6 py-4"
           style="background:linear-gradient(135deg,#1e3a5f 0%,#0f2140 100%)">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center"
               style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
            <svg style="width:18px;height:18px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-white">Items to Receive</h3>
            <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)" x-text="poItems.length ? poItems.length + ' item(s) from selected PO' : 'Select a purchase order to see items'"></p>
          </div>
        </div>
        <div x-show="loadingPO" class="flex items-center gap-2 text-xs" style="color:rgba(255,255,255,0.7)">
          <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
          Loading…
        </div>
      </div>

      
      <div x-show="!form.purchase_order_id && !loadingPO" class="py-16 text-center">
        <div class="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#eef2ff">
          <svg class="w-7 h-7" style="color:#818cf8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <p class="text-sm font-medium text-gray-400">No purchase order selected</p>
        <p class="text-xs text-gray-300 dark:text-gray-600 mt-1">Select a confirmed PO from the panel on the right</p>
      </div>

      
      <div x-show="form.purchase_order_id && poItems.length === 0 && !loadingPO" class="py-12 px-6 text-center">
        <div class="w-12 h-12 rounded-xl mx-auto mb-3 flex items-center justify-center" style="background:#fef3c7">
          <svg class="w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <p class="text-sm font-medium text-gray-500">No pending items</p>
        <p class="text-xs text-gray-400 mt-1">All items in this PO have already been received</p>
      </div>

      
      <div class="divide-y divide-gray-50 dark:divide-gray-700/40" x-show="poItems.length > 0 && !loadingPO">
        <template x-for="(row, idx) in poItems" :key="idx">
          <div class="px-5 py-4 hover:bg-blue-50/30 dark:hover:bg-blue-900/10 transition-colors">
            <div class="flex items-start gap-3">

              
              <div class="flex-shrink-0 mt-1 w-6 h-6 rounded-md flex items-center justify-center text-xs font-bold"
                   style="background:#e0e7ff;border:1px solid #c7d2fe;color:#1B3EB6"
                   x-text="idx + 1"></div>

              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-4 mb-2.5">
                  <div class="min-w-0">
                    <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate" x-text="row.product?.name ?? '—'"></div>
                    <div class="text-xs text-gray-400 mt-0.5" x-text="row.product?.sku ?? ''"></div>
                  </div>
                  <div class="flex-shrink-0 text-right">
                    <div class="text-xs text-gray-400 mb-0.5">Ordered</div>
                    <div class="text-sm font-bold tabular-nums text-gray-700 dark:text-gray-300" x-text="row.quantity"></div>
                  </div>
                </div>

                
                <div class="flex items-center gap-3">
                  <div class="flex-1">
                    <div class="flex items-center justify-between text-xs text-gray-400 mb-1">
                      <span>Previously received: <strong x-text="row.received_quantity ?? 0"></strong></span>
                      <span>Remaining: <strong x-text="row.quantity - (row.received_quantity ?? 0)"></strong></span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                      <div class="h-1.5 rounded-full transition-all" style="background:#1B3EB6"
                           :style="'width:' + Math.min(100,((row.received_qty ?? 0) / row.quantity) * 100) + '%'"></div>
                    </div>
                  </div>
                  <div class="flex items-center gap-2 flex-shrink-0">
                    <div class="text-xs text-gray-500 font-medium">Receive:</div>
                    <div class="flex items-center rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden" style="height:34px">
                      <button type="button" @click="row.receiving_qty = Math.max(0,(row.receiving_qty||0)-1)"
                              class="w-7 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors border-r border-gray-200 dark:border-gray-600 h-full text-base select-none">−</button>
                      <input type="number" x-model.number="row.receiving_qty"
                             :max="row.quantity - (row.received_quantity ?? 0)" min="0" step="1"
                             class="w-12 text-center text-sm font-semibold bg-transparent border-none outline-none h-full tabular-nums" style="box-shadow:none" />
                      <button type="button" @click="row.receiving_qty = Math.min(row.quantity-(row.received_quantity??0),(row.receiving_qty||0)+1)"
                              class="w-7 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors border-l border-gray-200 dark:border-gray-600 h-full text-base select-none">+</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>

    </div>

    
    <div class="card overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Delivery Notes</h3>
        <p class="text-xs text-gray-400 mt-0.5">Condition remarks, discrepancies or handling instructions</p>
      </div>
      <div class="px-6 py-4">
        <textarea x-model="form.notes" rows="3"
                  placeholder="e.g. 2 units arrived damaged, returned to driver."
                  class="input resize-none w-full text-sm"></textarea>
      </div>
    </div>

  </div>

  
  <div class="w-full lg:flex-[35]">
  <div class="lg:sticky lg:top-6 space-y-5">

    
    <div class="card overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700"
           style="background:linear-gradient(135deg,#1e3a5f,#0f2140)">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-white">GRN Details</h3>
            <p class="text-xs" style="color:rgba(255,255,255,0.6)">Purchase order &amp; receipt info</p>
          </div>
        </div>
      </div>
      <div class="px-5 py-4 space-y-4">

        <div>
          <label class="label">Purchase Order <span class="text-red-500">*</span></label>
          <select x-model="form.purchase_order_id" @change="loadPOItems()" class="input" required>
            <option value="">Select purchase order…</option>
            <template x-for="po in purchaseOrders" :key="po.id">
              <option :value="po.id" x-text="(po.po_number ?? 'PO-' + po.id) + ' — ' + (po.supplier?.name ?? '')"></option>
            </template>
          </select>
        </div>

        <div>
          <label class="label">Received Date <span class="text-red-500">*</span></label>
          <input type="date" x-model="form.received_date" class="input" required />
        </div>

        <div>
          <label class="label">GRN Reference</label>
          <input type="text" x-model="form.grn_number" placeholder="Auto-generated if blank" class="input font-mono" />
        </div>

        
        <div x-show="poItems.length > 0" class="rounded-xl p-4 space-y-2" style="background:#f0fdf4;border:1px solid #bbf7d0">
          <div class="text-xs font-semibold text-emerald-800 mb-2">Receiving Summary</div>
          <div class="flex justify-between text-xs text-emerald-700">
            <span>Total items in PO</span>
            <strong x-text="poItems.length"></strong>
          </div>
          <div class="flex justify-between text-xs text-emerald-700">
            <span>Total to receive now</span>
            <strong x-text="poItems.reduce((s,r) => s+(r.receiving_qty||0), 0)"></strong>
          </div>
        </div>

      </div>
    </div>

    
    <div class="flex gap-3">
      <a href="<?php echo e(url('/grns')); ?>" class="btn-secondary flex-1 text-center">Cancel</a>
      <button type="submit" :disabled="submitting || !poItems.some(r => (r.receiving_qty || 0) > 0)" class="btn-primary flex-1">
        <span x-text="submitting ? 'Saving…' : 'Create GRN'"></span>
      </button>
    </div>

  </div>
  </div>

</div>
</form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function grnCreatePage() {
    return {
        purchaseOrders: [],
        poItems: [],
        submitting: false,
        loadingPO: false,
        form: {
            purchase_order_id: '',
            branch_id: '',
            supplier_id: '',
            received_date: new Date().toISOString().slice(0, 10),
            grn_number: '',
            notes: '',
        },
        async init() {
            try {
                const res = await apiFetch('/purchase-orders?per_page=500');
                const data = await res.json();
                const all = data.data ?? data ?? [];
                this.purchaseOrders = all.filter(po => !['received', 'cancelled'].includes(po.status));
            } catch (e) {
                toast('Failed to load purchase orders', 'error');
            }
            // Pre-select PO from URL ?po_id=X
            const poId = new URLSearchParams(window.location.search).get('po_id');
            if (poId) {
                this.form.purchase_order_id = poId;
                await this.loadPOItems();
            }
        },
        async loadPOItems() {
            if (!this.form.purchase_order_id) { this.poItems = []; return; }
            this.loadingPO = true;
            try {
                const res = await apiFetch('/purchase-orders/' + this.form.purchase_order_id);
                const po = await res.json();
                this.form.branch_id   = (po.data ?? po).branch_id   ?? '';
                this.form.supplier_id = (po.data ?? po).supplier_id ?? '';
                const poData = po.data ?? po;
                this.poItems = (poData.items ?? []).map(item => ({
                    ...item,
                    receiving_qty: Math.max(0, parseFloat(item.quantity ?? 0) - parseFloat(item.received_quantity ?? 0)),
                }));
            } catch (e) {
                toast('Failed to load PO details', 'error');
                this.poItems = [];
            } finally {
                this.loadingPO = false;
            }
        },
        async submit() {
            this.submitting = true;
            try {
                const payload = {
                    ...this.form,
                    items: this.poItems
                        .filter(r => (r.receiving_qty || 0) > 0)
                        .map(r => ({
                            purchase_order_item_id: r.id,
                            product_id: r.product_id ?? r.product?.id,
                            quantity_received: r.receiving_qty,
                            unit_cost: parseFloat(r.unit_price ?? 0),
                        })),
                };
                const res = await apiFetch('/grns', { method: 'POST', body: JSON.stringify(payload) });
                if (!res.ok) { const err = await res.json(); throw new Error(err.message ?? JSON.stringify(err.errors ?? 'Failed to create GRN')); }
                toast('GRN created successfully', 'success');
                window.location.href = BASE + '/grns';
            } catch (e) {
                toast(e.message ?? 'Failed to create GRN', 'error');
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\purchase\grns-create.blade.php ENDPATH**/ ?>