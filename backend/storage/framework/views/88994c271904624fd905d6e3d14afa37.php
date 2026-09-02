
<?php $__env->startSection('title', 'New Product'); ?>
<?php $__env->startSection('page-title', 'New Product'); ?>
<?php $__env->startSection('page-desc', 'Add a new product to the catalogue'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="productCreatePage()" x-init="init()" class="px-6 pb-12">
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
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
             style="background:#eef2ff">
          <svg class="w-4 h-4" style="color:#1B3EB6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Pricing &amp; Stock</h3>
          <p class="text-xs text-gray-400 mt-0.5">Cost, sale price and inventory levels</p>
        </div>
      </div>
      <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

        <div>
          <label class="label">Cost Price (LKR)</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium pointer-events-none">Rs.</span>
            <input type="number" x-model.number="form.cost_price" class="input pl-9 tabular-nums" min="0" step="0.01" placeholder="0.00" />
          </div>
        </div>

        <div>
          <label class="label">Sale Price (LKR) <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium pointer-events-none">Rs.</span>
            <input type="number" x-model.number="form.selling_price" class="input pl-9 tabular-nums" min="0" step="0.01" placeholder="0.00" required />
          </div>
        </div>

        <div>
          <label class="label">Reorder Level</label>
          <input type="number" x-model.number="form.reorder_level" class="input tabular-nums" min="0" step="1" placeholder="10" />
          <p class="mt-1 text-xs text-gray-400">Alert when stock falls below this</p>
        </div>

        <div>
          <label class="label">Opening Stock</label>
          <input type="number" x-model.number="form.opening_stock" class="input tabular-nums" min="0" step="1" placeholder="0" />
          <p class="mt-1 text-xs text-gray-400">Initial quantity on hand</p>
        </div>

        <template x-if="form.cost_price > 0 && form.selling_price > 0">
          <div class="sm:col-span-2 rounded-xl p-4 flex items-center gap-4"
               style="background:#eef2ff;border:1px solid #c7d2fe">
            <div class="text-center flex-1">
              <div class="text-xs text-indigo-500 font-medium mb-1">Cost Price</div>
              <div class="text-sm font-bold tabular-nums" style="color:#1B3EB6" x-text="'Rs. ' + Number(form.cost_price).toLocaleString()"></div>
            </div>
            <div class="text-indigo-300 text-lg font-light">→</div>
            <div class="text-center flex-1">
              <div class="text-xs text-indigo-500 font-medium mb-1">Sale Price</div>
              <div class="text-sm font-bold tabular-nums" style="color:#1B3EB6" x-text="'Rs. ' + Number(form.selling_price).toLocaleString()"></div>
            </div>
            <div class="text-indigo-300 text-lg font-light">→</div>
            <div class="text-center flex-1">
              <div class="text-xs text-indigo-500 font-medium mb-1">Margin</div>
              <div class="text-sm font-bold tabular-nums" style="color:#059669"
                   x-text="form.selling_price > 0 ? (((form.selling_price - form.cost_price) / form.selling_price) * 100).toFixed(1) + '%' : '—'"></div>
            </div>
          </div>
        </template>

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
          <label class="label">Branch <span class="text-red-500">*</span></label>
          <select x-model="form.branch_id" class="input" required>
            <option value="">Select branch…</option>
            <template x-for="b in branches" :key="b.id">
              <option :value="b.id" x-text="b.name"></option>
            </template>
          </select>
        </div>

        <div>
          <div class="flex items-center justify-between">
            <label class="label">Category</label>
            <button type="button" @click="openCategoryModal()" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">+ New Category</button>
          </div>
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

    
    <div class="card overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Preview</h3>
      </div>
      <div class="px-5 py-4 space-y-3">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
               style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
          </div>
          <div class="min-w-0">
            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate"
                 x-text="form.name || 'Product Name'"></div>
            <div class="text-xs text-gray-400 truncate" x-text="form.sku ? 'SKU: ' + form.sku : 'No SKU'"></div>
          </div>
        </div>
        <div class="border-t border-gray-100 dark:border-gray-700 pt-3 grid grid-cols-2 gap-2 text-xs">
          <div>
            <div class="text-gray-400">Sale Price</div>
            <div class="font-bold mt-0.5" style="color:#1B3EB6" x-text="form.selling_price ? 'Rs. ' + Number(form.selling_price).toLocaleString() : '—'"></div>
          </div>
          <div>
            <div class="text-gray-400">Unit</div>
            <div class="font-medium text-gray-700 dark:text-gray-300 mt-0.5" x-text="form.unit || '—'"></div>
          </div>
          <div>
            <div class="text-gray-400">Opening Stock</div>
            <div class="font-medium text-gray-700 dark:text-gray-300 mt-0.5" x-text="form.opening_stock + ' ' + (form.unit || 'pcs')"></div>
          </div>
          <div>
            <div class="text-gray-400">Reorder At</div>
            <div class="font-medium text-gray-700 dark:text-gray-300 mt-0.5" x-text="form.reorder_level + ' ' + (form.unit || 'pcs')"></div>
          </div>
        </div>
      </div>
    </div>

    
    <div class="flex gap-3">
      <a href="<?php echo e(url('/products')); ?>" class="btn-secondary flex-1 text-center">Cancel</a>
      <button type="submit" :disabled="submitting" class="btn-primary flex-1">
        <span x-text="submitting ? 'Saving…' : 'Create Product'"></span>
      </button>
    </div>

  </div>
  </div>

</div>
</form>


<div x-show="showCategoryModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showCategoryModal = false">
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
      <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">New Category</h3>
      <button @click="showCategoryModal = false" class="text-gray-400 hover:text-gray-600">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="px-5 py-4 space-y-3">
      <div>
        <label class="label">Name <span class="text-red-500">*</span></label>
        <input type="text" x-model="newCategory.name" class="input" placeholder="e.g. Surgical Equipment" />
      </div>
      <div>
        <label class="label">Code <span class="text-red-500">*</span></label>
        <input type="text" x-model="newCategory.code" class="input font-mono" placeholder="e.g. SURG" maxlength="20" />
      </div>
    </div>
    <div class="flex justify-end gap-3 px-5 py-4 border-t border-gray-100 dark:border-gray-700">
      <button type="button" @click="showCategoryModal = false" class="btn-secondary">Cancel</button>
      <button type="button" @click="createCategoryQuick()" :disabled="creatingCategory" class="btn-primary">
        <span x-text="creatingCategory ? 'Saving…' : 'Create Category'"></span>
      </button>
    </div>
  </div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function productCreatePage() {
    return {
        categories: [],
        branches: [],
        submitting: false,
        showCategoryModal: false,
        creatingCategory: false,
        newCategory: { name: '', code: '' },
        form: {
            name: '',
            sku: '',
            branch_id: '',
            category_id: '',
            unit: 'pcs',
            barcode: '',
            cost_price: 0,
            selling_price: 0,
            reorder_level: 10,
            opening_stock: 0,
            description: '',
            is_active: true,
        },
        async init() {
            try {
                const data = await apiFetch('/products/categories').then(r => r.json());
                this.categories = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load categories', 'error');
            }
            try {
                const bd = await apiFetch('/branches').then(r => r.json());
                this.branches = bd.data ?? bd ?? [];
                const u = JSON.parse(localStorage.getItem('medri_user') || '{}');
                const stored = localStorage.getItem('medri_branch');
                const bid = (stored && stored !== 'all') ? stored : u.default_branch_id;
                if (bid) this.form.branch_id = bid;
            } catch (_) {}
        },
        async submit() {
            if (!this.form.branch_id) { toast('Please select a branch', 'error'); return; }
            this.submitting = true;
            try {
                await apiFetch('/products', { method: 'POST', body: JSON.stringify(this.form) });
                toast('Product created successfully', 'success');
                window.location.href = BASE + '/products';
            } catch (e) {
                toast(e.message ?? 'Failed to create product', 'error');
            } finally {
                this.submitting = false;
            }
        },
        openCategoryModal() {
            if (!this.form.branch_id) { toast('Select a branch first', 'error'); return; }
            this.newCategory = { name: '', code: '' };
            this.showCategoryModal = true;
        },
        async createCategoryQuick() {
            if (!this.newCategory.name || !this.newCategory.code) {
                toast('Enter a name and code for the category', 'error');
                return;
            }
            this.creatingCategory = true;
            try {
                const payload = { ...this.newCategory, branch_id: this.form.branch_id };
                const r = await apiFetch('/products/categories', { method: 'POST', body: JSON.stringify(payload) });
                const cat = await r.json();
                this.categories.push(cat);
                this.form.category_id = cat.id;
                this.showCategoryModal = false;
                toast('Category created', 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to create category', 'error');
            } finally {
                this.creatingCategory = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\products\create.blade.php ENDPATH**/ ?>