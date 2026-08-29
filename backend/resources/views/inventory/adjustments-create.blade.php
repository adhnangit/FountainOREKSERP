@extends('layouts.app')
@section('title', 'New Stock Adjustment')
@section('page-title', 'New Stock Adjustment')
@section('page-desc', 'Record a manual stock adjustment')

@section('content')
<div x-data="adjustmentCreate()" x-init="init()" x-cloak>

  <div class="max-w-2xl mx-auto space-y-5">

    <!-- Header -->
    <div class="flex items-center gap-3">
      <a href="{{ url('/inventory/adjustments') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
      </a>
      <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100" x-text="editId ? 'Edit Stock Adjustment' : 'New Stock Adjustment'"></h2>
      <span x-show="editId" class="text-xs font-mono text-gray-400" x-text="editNumber"></span>
    </div>

    <div class="card p-5 space-y-5">

      <!-- Branch -->
      <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Branch <span class="text-red-500">*</span></label>
        <select x-model="form.branch_id" @change="loadProducts()" class="input w-full" :disabled="!!editId">
          <option value="">Select branch</option>
          <template x-for="b in branches" :key="b.id">
            <option :value="b.id" x-text="b.name"></option>
          </template>
        </select>
      </div>

      <!-- Product -->
      <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Product <span class="text-red-500">*</span></label>
        <div class="search-dd" x-data="{ open: false, q: '' }" @click.away="open = false" @keydown.escape="open = false">
          <button type="button" @click="if(!form.branch_id) return; open = !open; if(open) $nextTick(() => $refs.pAdj?.focus())"
                  class="input w-full text-left flex items-center justify-between gap-2" :disabled="!form.branch_id">
            <span class="truncate" :class="form.product_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                  x-text="form.product_id ? (products.find(p => p.id == form.product_id)?.name || '—') : 'Select product'"></span>
            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
          </button>
          <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu">
            <div class="p-2 border-b border-gray-100 dark:border-gray-700">
              <input x-ref="pAdj" x-model="q" type="text" placeholder="Search product…" class="input text-sm w-full py-1.5" @keydown.stop />
            </div>
            <div class="max-h-52 overflow-y-auto py-1">
              <template x-for="p in products.filter(p => !q || p.name.toLowerCase().includes(q.toLowerCase()))" :key="p.id">
                <button type="button" @click="form.product_id = p.id; form.batch_id = ''; setCurrentStock(); loadBatches(); open = false; q = ''"
                        class="search-dd-item" :class="form.product_id == p.id ? 'active' : ''">
                  <span class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate flex-1" x-text="p.name"></span>
                </button>
              </template>
              <div x-show="products.filter(p => !q || p.name.toLowerCase().includes(q.toLowerCase())).length === 0"
                   class="px-4 py-3 text-xs text-gray-400 text-center">No products found</div>
            </div>
          </div>
        </div>
        <div x-show="currentStock !== null && !form.batch_id" class="mt-1.5 text-xs text-gray-500">
          Current stock: <span class="font-semibold" :class="currentStock > 0 ? 'text-green-600' : 'text-red-500'" x-text="currentStock"></span>
        </div>
      </div>

      <!-- Batch (optional — targets one specific batch instead of the product total) -->
      <div x-show="batches.length > 0">
        <label class="block text-xs font-semibold text-gray-500 mb-1">Batch <span class="text-gray-400 font-normal">(optional)</span></label>
        <select x-model="form.batch_id" @change="setCurrentStock()" class="input w-full">
          <option value="">— All batches (product total) —</option>
          <template x-for="b in batches" :key="b.id">
            <option :value="b.id" x-text="b.batch_code + ' — ' + parseFloat(b.available_qty) + ' remaining'"></option>
          </template>
        </select>
        <div x-show="form.batch_id && currentStock !== null" class="mt-1.5 text-xs text-gray-500">
          Current stock (this batch): <span class="font-semibold" :class="currentStock > 0 ? 'text-green-600' : 'text-red-500'" x-text="currentStock"></span>
          <p class="mt-1 text-gray-400">Correction will apply to this batch only, not the product's overall stock.</p>
        </div>
      </div>

      <!-- Adjustment Type -->
      <div>
        <label class="block text-xs font-semibold text-gray-500 mb-2">Adjustment Type <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-2 gap-3">
          <button type="button" @click="form.adjustment_type = 'add'"
                  :class="form.adjustment_type === 'add' ? 'border-green-500 bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400'"
                  class="flex items-center justify-center gap-2 py-3 rounded-xl border-2 font-semibold text-sm transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
            Add Stock
          </button>
          <button type="button" @click="form.adjustment_type = 'remove'"
                  :class="form.adjustment_type === 'remove' ? 'border-red-500 bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400'"
                  class="flex items-center justify-center gap-2 py-3 rounded-xl border-2 font-semibold text-sm transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M20 12H4"/></svg>
            Remove Stock
          </button>
        </div>
      </div>

      <!-- Quantity -->
      <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Quantity <span class="text-red-500">*</span></label>
        <input type="number" x-model.number="form.quantity" min="0.01" step="0.01" placeholder="0"
               class="input w-full text-lg font-bold text-center"
               :class="form.adjustment_type === 'add' ? 'border-green-300 focus:border-green-500' : 'border-red-300 focus:border-red-500'" />
        <div x-show="form.quantity > 0 && currentStock !== null && form.adjustment_type" class="mt-1.5 text-xs text-gray-500">
          New stock after adjustment:
          <span class="font-bold"
                :class="newStock >= 0 ? 'text-green-600' : 'text-red-600'"
                x-text="newStock"></span>
        </div>
      </div>

      <!-- Date -->
      <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Adjustment Date</label>
        <input type="date" x-model="form.adjustment_date" class="input w-full" />
      </div>

      <!-- Reason -->
      <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Reason <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mb-2">
          <template x-for="r in reasons" :key="r">
            <button type="button" @click="form.reason = r"
                    :class="form.reason === r ? 'border-primary-500 bg-primary-50 text-primary-700 dark:bg-primary-900/20 dark:text-primary-300' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400'"
                    class="text-xs px-2 py-1.5 rounded-lg border font-medium transition-all text-center"
                    x-text="r">
            </button>
          </template>
        </div>
        <input type="text" x-model="form.reason" placeholder="Or type a custom reason…" class="input w-full text-sm" />
      </div>

      <!-- Notes -->
      <div>
        <label class="block text-xs font-semibold text-gray-500 mb-1">Notes</label>
        <textarea x-model="form.notes" rows="2" placeholder="Additional details…" class="input w-full text-sm resize-none"></textarea>
      </div>

    </div>

    <!-- Approve now vs. draft — a draft never touches stock until someone separately approves it -->
    <div class="card p-4 flex items-start gap-3 border-2" x-show="canApprove"
         :class="approveNow ? 'border-green-300 bg-green-50/60 dark:bg-green-900/10' : 'border-amber-300 bg-amber-50/60 dark:bg-amber-900/10'">
      <input type="checkbox" x-model="approveNow" id="approveNow" class="mt-0.5 w-4 h-4 accent-green-600 flex-shrink-0" />
      <label for="approveNow" class="text-sm cursor-pointer select-none">
        <span class="font-semibold" :class="approveNow ? 'text-green-700 dark:text-green-400' : 'text-amber-700 dark:text-amber-400'"
              x-text="approveNow ? 'Approve immediately' : 'Save as draft only'"></span>
        <p class="text-xs text-gray-500 mt-0.5"
           x-text="approveNow ? 'Stock updates the moment you save.' : 'Stock will NOT change until this is approved from the Adjustments list.'"></p>
      </label>
    </div>
    <div x-show="!canApprove" class="text-xs text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800 rounded-lg px-3 py-2.5">
      This saves as a draft — you don't have permission to approve adjustments. Stock won't change until someone who can approve reviews it from the Adjustments list.
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end gap-3 pb-6">
      <a href="{{ url('/inventory/adjustments') }}" class="btn-secondary">Cancel</a>
      <button type="button" @click="submit()" :disabled="submitting"
              class="btn-primary flex items-center gap-2 disabled:opacity-60">
        <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="30 70"/></svg>
        <span x-text="submitting ? 'Saving…' : saveButtonLabel"></span>
      </button>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script>
function adjustmentCreate() {
  return {
    branches: [],
    products: [],
    batches: [],
    currentStock: null,
    submitting: false,
    canApprove: false,
    approveNow: true,
    editId: new URLSearchParams(window.location.search).get('edit'),
    editNumber: '',
    reasons: ['Damaged', 'Expired', 'Theft/Loss', 'Correction', 'Returned', 'Recount', 'Write-off'],
    form: {
      branch_id: '',
      product_id: '',
      batch_id: '',
      adjustment_type: 'add',
      quantity: '',
      adjustment_date: new Date().toISOString().split('T')[0],
      reason: '',
      notes: '',
    },

    get newStock() {
      if (this.currentStock === null || !this.form.quantity) return this.currentStock;
      const q = parseFloat(this.form.quantity) || 0;
      return this.form.adjustment_type === 'add'
        ? parseFloat(this.currentStock) + q
        : parseFloat(this.currentStock) - q;
    },

    get saveButtonLabel() {
      if (this.canApprove && this.approveNow) return this.editId ? 'Update & Approve' : 'Save & Approve';
      return this.editId ? 'Update Draft' : 'Save as Draft';
    },

    checkCanApprove() {
      try {
        const u = JSON.parse(localStorage.getItem('medri_user') || 'null');
        if (!u) return false;
        if (u.is_super_admin) return true;
        if ((u.roles ?? []).includes('super_admin')) return true;
        return (u.permissions ?? []).includes('inventory.adjustments.approve');
      } catch (e) { return false; }
    },

    async init() {
      this.canApprove = this.checkCanApprove();
      const r = await apiFetch('/branches');
      if (!r) return;
      const d = await r.json();
      this.branches = d.data ?? d ?? [];
      if (this.editId) await this.loadDraft();
    },

    async loadDraft() {
      try {
        const r = await apiFetch('/adjustments/' + this.editId);
        if (!r) return;
        const a = await r.json();
        if (a.status !== 'draft') {
          toast('Only draft adjustments can be edited', 'error');
          window.location.href = BASE + '/inventory/adjustments';
          return;
        }
        this.editNumber = a.adjustment_number ?? '';
        this.form.branch_id = a.branch_id;
        await this.loadProducts();
        const item = (a.items ?? [])[0];
        if (item) {
          this.form.product_id = item.product_id;
          await this.loadBatches();
          this.form.batch_id = item.batch_id ?? '';
          this.setCurrentStock();
          const diff = parseFloat(item.difference ?? 0);
          this.form.adjustment_type = diff >= 0 ? 'add' : 'remove';
          this.form.quantity = Math.abs(diff);
        }
        this.form.adjustment_date = (a.adjustment_date ?? '').slice(0, 10) || this.form.adjustment_date;
        this.form.reason = a.reason ?? '';
        this.form.notes = '';
      } catch (e) {
        toast('Failed to load adjustment', 'error');
      }
    },

    async loadProducts() {
      this.products = [];
      this.batches = [];
      this.currentStock = null;
      this.form.product_id = '';
      this.form.batch_id = '';
      if (!this.form.branch_id) return;
      const r = await apiFetch('/products?per_page=500');
      if (!r) return;
      const d = await r.json();
      const list = d.data ?? d ?? [];
      this.products = list.map(p => ({
        id: p.id,
        name: p.name,
        stock: p.branch_stocks?.find(s => s.branch_id == this.form.branch_id)?.quantity ?? null,
      }));
    },

    async loadBatches() {
      this.batches = [];
      if (!this.form.product_id || !this.form.branch_id) return;
      try {
        const r = await apiFetch('/products/' + this.form.product_id + '/batches?branch_id=' + this.form.branch_id);
        if (!r) return;
        const d = await r.json();
        this.batches = Array.isArray(d) ? d : (d.data ?? []);
      } catch (e) {
        this.batches = [];
      }
    },

    setCurrentStock() {
      if (this.form.batch_id) {
        const b = this.batches.find(x => x.id == this.form.batch_id);
        this.currentStock = b ? b.available_qty : null;
        return;
      }
      const p = this.products.find(x => x.id == this.form.product_id);
      this.currentStock = p ? p.stock : null;
    },

    async submit() {
      if (!this.form.branch_id || !this.form.product_id) {
        toast('Select branch and product', 'error'); return;
      }
      if (!this.form.quantity || parseFloat(this.form.quantity) <= 0) {
        toast('Enter a valid quantity', 'error'); return;
      }
      if (!this.form.reason) {
        toast('Enter a reason for adjustment', 'error'); return;
      }
      this.submitting = true;
      try {
        const base = parseFloat(this.currentStock) || 0;
        const qty  = parseFloat(this.form.quantity) || 0;
        const physicalQty = this.form.adjustment_type === 'add' ? base + qty : Math.max(base - qty, 0);
        const url    = this.editId ? '/adjustments/' + this.editId : '/adjustments';
        const method = this.editId ? 'PUT' : 'POST';
        const r = await apiFetch(url, {
          method,
          headers: { ...authHeaders(), 'Content-Type': 'application/json' },
          body: JSON.stringify({
            branch_id: this.form.branch_id,
            adjustment_date: this.form.adjustment_date,
            reason: this.form.reason + (this.form.notes ? ' — ' + this.form.notes : ''),
            items: [{ product_id: this.form.product_id, batch_id: this.form.batch_id || null, physical_quantity: physicalQty }],
          }),
        });
        if (!r) return;
        if (!r.ok) {
          const err = await r.json();
          toast(err.message ?? 'Failed to save adjustment', 'error'); return;
        }
        const saved = await r.json();

        if (this.canApprove && this.approveNow) {
          const ar = await apiFetch('/adjustments/' + saved.id + '/approve', { method: 'POST' });
          if (ar && ar.ok) {
            toast('Adjustment approved — stock updated', 'success');
          } else {
            const aerr = ar ? await ar.json().catch(() => ({})) : {};
            toast('Saved as draft, but approval failed (' + (aerr.message ?? 'unknown error') + '). Approve it from the Adjustments list.', 'error');
          }
        } else {
          toast('Draft saved — stock not updated yet until it\'s approved', 'success');
        }
        window.location.href = BASE + '/inventory/adjustments';
      } catch(e) {
        toast('Failed to save adjustment', 'error');
      } finally {
        this.submitting = false;
      }
    },
  };
}
</script>
@endpush
