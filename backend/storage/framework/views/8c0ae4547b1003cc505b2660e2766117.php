<?php $__env->startSection('title', 'Edit Product'); ?>
<?php $__env->startSection('page-title', 'Edit Product'); ?>
<?php $__env->startSection('page-desc', 'Update product details'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="productEditPage()" x-init="init()" class="px-6 pb-12">
<form @submit.prevent="submit()">
<div class="flex flex-col lg:flex-row gap-6">

  
  <div class="w-full lg:flex-[62] min-w-0 space-y-5">

    <div class="card overflow-hidden">
      <div class="flex items-center gap-3 px-6 py-4"
           style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%)">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
             style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
          <svg style="width:18px;height:18px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-white">Product Information</h3>
          <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">Name, codes and description</p>
        </div>
      </div>
      <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

        <div class="sm:col-span-2">
          <label class="label">Product Name <span class="text-red-500">*</span></label>
          <input type="text" x-model="form.name" class="input" placeholder="Product name" required />
        </div>

        <div>
          <label class="label">SKU / Item Code</label>
          <input type="text" x-model="form.sku" class="input font-mono" placeholder="e.g. PROD-001" />
        </div>

        <div>
          <label class="label">Barcode</label>
          <input type="text" x-model="form.barcode" class="input font-mono" placeholder="Optional barcode" />
        </div>

        <div class="sm:col-span-2">
          <label class="label">Description</label>
          <textarea x-model="form.description" rows="3" class="input resize-none" placeholder="Product description, specifications, notes…"></textarea>
        </div>

      </div>
    </div>

    
    <div class="card overflow-hidden">
      <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#eef2ff">
          <svg class="w-4 h-4" style="color:#1B3EB6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Pricing &amp; Stock</h3>
          <p class="text-xs text-gray-400 mt-0.5">Cost, sale price and reorder level</p>
        </div>
      </div>
      <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

        <div>
          <label class="label">Cost Price (LKR)</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium pointer-events-none">Rs.</span>
            <input type="number" x-model.number="form.cost_price" class="input pl-9 tabular-nums" min="0" step="0.01" />
          </div>
        </div>

        <div>
          <label class="label">Sale Price (LKR) <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium pointer-events-none">Rs.</span>
            <input type="number" x-model.number="form.selling_price" class="input pl-9 tabular-nums" min="0" step="0.01" required />
          </div>
        </div>

        <div>
          <label class="label">Reorder Level</label>
          <input type="number" x-model.number="form.reorder_level" class="input tabular-nums" min="0" step="1" />
          <p class="mt-1 text-xs text-gray-400">Alert when stock falls below this</p>
        </div>

      </div>
    </div>

  </div>

  
  <div class="w-full lg:flex-[38]">
  <div class="lg:sticky lg:top-6 space-y-5">

    <div class="card overflow-hidden">
      <div class="flex items-center gap-3 px-5 py-4"
           style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%)">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
             style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
          <svg style="width:16px;height:16px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-white">Classification</h3>
          <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">Category, unit and status</p>
        </div>
      </div>
      <div class="px-5 py-4 space-y-4">

        <div>
          <label class="label">Category</label>
          <select x-model="form.category_id" class="input">
            <option value="">Select category…</option>
            <template x-for="cat in categories" :key="cat.id">
              <option :value="cat.id" x-text="cat.name"></option>
            </template>
          </select>
        </div>

        <div>
          <label class="label">Unit of Measure</label>
          <select x-model="form.unit" class="input">
            <option value="pcs">Pieces (pcs)</option>
            <option value="kg">Kilograms (kg)</option>
            <option value="g">Grams (g)</option>
            <option value="l">Litres (l)</option>
            <option value="ml">Millilitres (ml)</option>
            <option value="box">Box</option>
            <option value="pack">Pack</option>
            <option value="set">Set</option>
          </select>
        </div>

        <label class="flex items-center gap-3 cursor-pointer py-1">
          <div class="relative flex-shrink-0">
            <input type="checkbox" x-model="form.is_active" class="sr-only peer" id="is_active" />
            <div class="w-10 h-6 rounded-full transition-colors peer-checked:bg-blue-600 bg-gray-200 dark:bg-gray-700"></div>
            <div class="absolute left-1 top-1 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></div>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Product</div>
            <div class="text-xs text-gray-400">Available for sale in invoices</div>
          </div>
        </label>

      </div>
    </div>

    <div class="flex gap-3">
      <a :href="BASE + '/products/' + id" class="btn-secondary flex-1 text-center">Cancel</a>
      <button type="submit" :disabled="submitting" class="btn-primary flex-1">
        <span x-text="submitting ? 'Saving…' : 'Update Product'"></span>
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
function productEditPage() {
    return {
        categories: [],
        submitting: false,
        get id() { return window.location.pathname.split('/').filter(Boolean).at(-2); },
        form: {
            name: '', sku: '', barcode: '', description: '',
            category_id: '', unit: 'pcs',
            cost_price: 0, selling_price: 0, reorder_level: 10,
            is_active: true,
        },
        async init() {
            try {
                const [cats, prod] = await Promise.all([
                    apiFetch('/products/categories').then(r => r.json()),
                    apiFetch('/products/' + this.id).then(r => r.json()),
                ]);
                this.categories = cats.data ?? cats ?? [];
                const p = prod.data ?? prod;
                this.form = {
                    name:         p.name          ?? '',
                    sku:          p.sku           ?? '',
                    barcode:      p.barcode       ?? '',
                    description:  p.description   ?? '',
                    category_id:  p.category_id   ?? '',
                    unit:         p.unit          ?? 'pcs',
                    cost_price:   p.cost_price    ?? 0,
                    selling_price: p.selling_price ?? 0,
                    reorder_level: p.reorder_level ?? 10,
                    is_active:    p.is_active     ?? true,
                };
            } catch (e) {
                toast('Failed to load product', 'error');
            }
        },
        async submit() {
            this.submitting = true;
            try {
                await apiFetch('/products/' + this.id, { method: 'PUT', body: JSON.stringify(this.form) });
                toast('Product updated', 'success');
                window.location.href = BASE + '/products/' + this.id;
            } catch (e) {
                toast(e.message ?? 'Failed to update product', 'error');
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/products/edit.blade.php ENDPATH**/ ?>