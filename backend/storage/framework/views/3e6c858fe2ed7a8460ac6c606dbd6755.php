
<?php $__env->startSection('title', 'Products'); ?>
<?php $__env->startSection('page-title', 'Products'); ?>
<?php $__env->startSection('page-desc', 'Manage your product catalogue'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="productsPage()" x-init="init()">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex flex-col sm:flex-row gap-2">
            <input x-model="search" type="text" placeholder="Search name, SKU…" class="input w-full sm:w-64" />
            <select x-model="categoryFilter" class="input w-44">
                <option value="">All Categories</option>
                <template x-for="cat in categories" :key="cat.id">
                    <option :value="cat.id" x-text="cat.name"></option>
                </template>
            </select>
        </div>
        <a href="<?php echo e(url('/products/create')); ?>" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Product
        </a>
    </div>

    <div class="card p-0 overflow-hidden">
        <div x-show="loading" class="flex items-center justify-center py-16">
            <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
        </div>
        <div x-show="!loading" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-hd">Name</th>
                        <th class="table-hd">SKU</th>
                        <th class="table-hd">Category</th>
                        <th class="table-hd">Unit</th>
                        <th class="table-hd text-right">Cost Price</th>
                        <th class="table-hd text-right">Sale Price</th>
                        <th class="table-hd" :class="isAllBranches ? '' : 'text-right'">Stock Qty</th>
                        <th class="table-hd text-right">Stock Value</th>
                        <th class="table-hd">Status</th>
                        <th class="table-hd">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="p in filtered" :key="p.id">
                        <tr class="hover:bg-gray-50">
                            <td class="table-td font-medium text-gray-900" x-text="p.name"></td>
                            <td class="table-td text-gray-500 font-mono text-xs" x-text="p.sku ?? '—'"></td>
                            <td class="table-td" x-text="p.category?.name ?? '—'"></td>
                            <td class="table-td" x-text="p.unit ?? '—'"></td>
                            <td class="table-td text-right text-gray-600" x-text="fmtMoney(p.cost_price ?? 0)"></td>
                            <td class="table-td text-right font-semibold" x-text="fmtMoney(p.sale_price ?? p.selling_price ?? 0)"></td>
                            <td class="table-td">
                                <template x-if="!isAllBranches">
                                    <span :class="stockQty(p) <= (p.reorder_level ?? 0) ? 'text-red-600 font-bold' : 'text-gray-800 font-semibold'"
                                          x-text="stockQty(p)"></span>
                                </template>
                                <template x-if="isAllBranches">
                                    <div class="flex flex-wrap items-center gap-1">
                                        <template x-for="s in (p.branch_stocks ?? p.branchStocks ?? [])" :key="s.branch_id">
                                            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-semibold"
                                                  :class="parseFloat(s.quantity) <= 0 ? 'bg-red-50 text-red-600' : 'bg-indigo-50 text-indigo-700'">
                                                <span x-text="s.branch?.code ?? s.branch?.name ?? 'B'+s.branch_id"></span>
                                                <span class="opacity-60">:</span>
                                                <span x-text="parseFloat(s.quantity)"></span>
                                            </span>
                                        </template>
                                        <template x-if="(p.branch_stocks ?? p.branchStocks ?? []).length > 1">
                                            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                                Total: <span x-text="stockQty(p)"></span>
                                            </span>
                                        </template>
                                        <span x-show="!(p.branch_stocks ?? p.branchStocks ?? []).length" class="text-gray-400 text-xs">—</span>
                                    </div>
                                </template>
                            </td>
                            <td class="table-td text-right font-semibold text-gray-700 dark:text-gray-200"
                                x-text="fmtMoney(stockValue(p))"></td>
                            <td class="table-td">
                                <span :class="(p.is_active ?? true) ? 'badge-success' : 'badge-gray'"
                                    x-text="(p.is_active ?? true) ? 'Active' : 'Inactive'"></span>
                            </td>
                            <td class="table-td">
                                <a :href="BASE + '/products/' + p.id" class="text-indigo-600 hover:underline text-sm font-medium">View</a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && filtered.length === 0">
                        <td colspan="10" class="table-td text-center text-gray-400 py-10">No products found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function productsPage() {
    return {
        items: [],
        categories: [],
        loading: true,
        search: '',
        categoryFilter: '',
        isAllBranches: localStorage.getItem('medri_branch') === 'all' || !localStorage.getItem('medri_branch'),
        get filtered() {
            let list = this.items;
            if (this.categoryFilter) list = list.filter(p => p.category_id == this.categoryFilter);
            const q = this.search.toLowerCase();
            if (!q) return list;
            return list.filter(p =>
                (p.name ?? '').toLowerCase().includes(q) ||
                (p.sku ?? '').toLowerCase().includes(q)
            );
        },
        stockQty(p) {
            if (p.stock_qty != null) return p.stock_qty;
            const stocks = p.branch_stocks ?? p.branchStocks ?? [];
            if (!stocks.length) return 0;
            return stocks.reduce((s, b) => s + parseFloat(b.quantity ?? 0), 0);
        },
        stockValue(p) {
            const stocks = p.branch_stocks ?? p.branchStocks ?? [];
            if (!stocks.length) return 0;
            return stocks.reduce((s, b) => s + parseFloat(b.quantity ?? 0) * parseFloat(b.avg_cost ?? 0), 0);
        },
        async init() {
            try {
                const [pr, cr] = await Promise.all([
                    apiFetch('/products?per_page=200').then(r => r.json()),
                    apiFetch('/products/categories').then(r => r.json()),
                ]);
                this.items = pr.data ?? pr ?? [];
                this.categories = cr.data ?? cr ?? [];
            } catch (e) {
                toast('Failed to load products', 'error');
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/products/index.blade.php ENDPATH**/ ?>