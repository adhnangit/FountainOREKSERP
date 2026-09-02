<?php $__env->startSection('title', 'New Stock Transfer'); ?>
<?php $__env->startSection('page-title', 'New Stock Transfer'); ?>
<?php $__env->startSection('page-desc', 'Transfer stock between branches'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="transferCreate()" x-init="init()" x-cloak>

  <div class="max-w-3xl mx-auto space-y-5">

    <!-- Header -->
    <div class="flex items-center gap-3">
      <a href="<?php echo e(url('/inventory/transfers')); ?>" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
      </a>
      <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">New Stock Transfer</h2>
    </div>

    <!-- Transfer Details -->
    <div class="card p-5 space-y-4">
      <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Transfer Details</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">From Branch <span class="text-red-500">*</span></label>
          <select x-model="form.from_branch_id" @change="loadStock()" class="input w-full">
            <option value="">Select branch</option>
            <template x-for="b in branches" :key="b.id">
              <option :value="b.id" x-text="b.name"></option>
            </template>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">To Branch <span class="text-red-500">*</span></label>
          <select x-model="form.to_branch_id" class="input w-full">
            <option value="">Select branch</option>
            <template x-for="b in branches.filter(b => b.id != form.from_branch_id)" :key="b.id">
              <option :value="b.id" x-text="b.name"></option>
            </template>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Transfer Date <span class="text-red-500">*</span></label>
          <input type="date" x-model="form.transfer_date" class="input w-full" />
        </div>
        <div>
          <label class="block text-xs font-semibold text-gray-500 mb-1">Reference / Notes</label>
          <input type="text" x-model="form.notes" placeholder="Optional reference" class="input w-full" />
        </div>
      </div>
    </div>

    <!-- Items -->
    <div class="card p-5 space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-bold text-gray-700 dark:text-gray-200 uppercase tracking-wider">Items to Transfer</h3>
        <button type="button" @click="addItem()" class="btn-secondary text-xs px-3 py-1.5 flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
          Add Item
        </button>
      </div>

      <div x-show="!form.from_branch_id" class="text-sm text-gray-400 py-4 text-center">Select a source branch first.</div>

      <template x-if="form.from_branch_id">
        <div class="space-y-3">
          <template x-for="(item, idx) in items" :key="idx">
            <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/30">
              <div class="flex-1">
                <label class="block text-xs text-gray-400 mb-1">Product</label>
                <div class="search-dd" x-data="{ open: false, q: '' }" @click.away="open = false" @keydown.escape="open = false">
                  <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.pTr?.focus())"
                          class="input w-full text-sm text-left flex items-center justify-between gap-2">
                    <span class="truncate" :class="item.product_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                          x-text="item.product_id ? (products.find(p => p.id == item.product_id)?.name || '—') : 'Select product'"></span>
                    <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                  </button>
                  <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu">
                    <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                      <input x-ref="pTr" x-model="q" type="text" placeholder="Search product…" class="input text-sm w-full py-1.5" @keydown.stop />
                    </div>
                    <div class="max-h-52 overflow-y-auto py-1">
                      <template x-for="p in products.filter(p => !q || p.name.toLowerCase().includes(q.toLowerCase()))" :key="p.id">
                        <button type="button" @click="item.product_id = p.id; setStock(item); open = false; q = ''"
                                class="search-dd-item" :class="item.product_id == p.id ? 'active' : ''">
                          <span class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate flex-1"
                                x-text="p.name + (p.available_qty != null ? ' (' + p.available_qty + ' available)' : '')"></span>
                        </button>
                      </template>
                      <div x-show="products.filter(p => !q || p.name.toLowerCase().includes(q.toLowerCase())).length === 0"
                           class="px-4 py-3 text-xs text-gray-400 text-center">No products found</div>
                    </div>
                  </div>
                </div>
              </div>
              <div style="width:110px">
                <label class="block text-xs text-gray-400 mb-1">Qty</label>
                <input type="number" x-model.number="item.quantity" min="0.01" step="0.01"
                       :max="item.available_qty || undefined"
                       class="input w-full text-sm text-center" />
              </div>
              <div x-show="item.available_qty != null" style="width:90px" class="text-center">
                <div class="text-xs text-gray-400 mb-1">Available</div>
                <div class="text-sm font-semibold" :class="item.available_qty > 0 ? 'text-green-600' : 'text-red-600'"
                     x-text="item.available_qty ?? '—'"></div>
              </div>
              <button type="button" @click="items.splice(idx,1)"
                      class="w-7 h-7 flex items-center justify-center rounded-lg text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors flex-shrink-0 mt-4">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
          </template>
          <template x-if="items.length === 0">
            <div class="text-center text-gray-400 text-sm py-4">No items added yet. Click "Add Item" to start.</div>
          </template>
        </div>
      </template>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end gap-3 pb-6">
      <a href="<?php echo e(url('/inventory/transfers')); ?>" class="btn-secondary">Cancel</a>
      <button type="button" @click="submit()" :disabled="submitting"
              class="btn-primary flex items-center gap-2 disabled:opacity-60">
        <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="30 70"/></svg>
        <span x-text="submitting ? 'Saving…' : 'Create Transfer'"></span>
      </button>
    </div>

  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function transferCreate() {
  return {
    branches: [],
    products: [],
    submitting: false,
    form: {
      from_branch_id: '',
      to_branch_id: '',
      transfer_date: new Date().toISOString().split('T')[0],
      notes: '',
    },
    items: [],

    async init() {
      const r = await apiFetch('/branches');
      if (!r) return;
      const d = await r.json();
      this.branches = d.data ?? d ?? [];
    },

    addItem() {
      this.items.push({ product_id: '', quantity: 1, available_qty: null });
    },

    async loadStock() {
      if (!this.form.from_branch_id) { this.products = []; return; }
      const r = await apiFetch('/products?per_page=500&branch_id=' + this.form.from_branch_id);
      if (!r) return;
      const d = await r.json();
      const list = d.data ?? d ?? [];
      this.products = list.map(p => ({
        id: p.id,
        name: p.name,
        available_qty: p.branch_stocks?.[0]?.quantity ?? null,
      }));
      // refresh stock on existing items
      this.items.forEach(item => this.setStock(item));
    },

    setStock(item) {
      const p = this.products.find(x => x.id == item.product_id);
      item.available_qty = p ? p.available_qty : null;
    },

    async submit() {
      if (!this.form.from_branch_id || !this.form.to_branch_id) {
        toast('Please select both branches', 'error'); return;
      }
      if (this.form.from_branch_id == this.form.to_branch_id) {
        toast('From and To branch cannot be the same', 'error'); return;
      }
      const validItems = this.items.filter(i => i.product_id && i.quantity > 0);
      if (!validItems.length) {
        toast('Add at least one item with a valid quantity', 'error'); return;
      }
      this.submitting = true;
      try {
        const r = await apiFetch('/transfers', {
          method: 'POST',
          headers: { ...authHeaders(), 'Content-Type': 'application/json' },
          body: JSON.stringify({
            from_branch_id: this.form.from_branch_id,
            to_branch_id: this.form.to_branch_id,
            request_date: this.form.transfer_date,
            notes: this.form.notes,
            items: validItems.map(i => ({ product_id: i.product_id, requested_quantity: i.quantity })),
          }),
        });
        if (!r) return;
        if (!r.ok) {
          const err = await r.json();
          toast(err.message ?? 'Failed to create transfer', 'error'); return;
        }
        const d = await r.json();
        toast('Transfer created successfully', 'success');
        window.location.href = BASE + '/inventory/transfers';
      } catch(e) {
        toast('Failed to create transfer', 'error');
      } finally {
        this.submitting = false;
      }
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\inventory\transfers-create.blade.php ENDPATH**/ ?>