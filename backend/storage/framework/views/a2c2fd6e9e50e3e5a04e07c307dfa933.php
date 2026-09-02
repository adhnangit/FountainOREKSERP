
<?php $__env->startSection('title', 'Low Stock Alerts'); ?>
<?php $__env->startSection('page-title', 'Low Stock Alerts'); ?>
<?php $__env->startSection('page-desc', 'Products that need restocking'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="lowStockPage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <input x-model="search" type="text" placeholder="Search product or SKU…" class="input w-64" />
        <div class="flex gap-2">
            <button @click="filter = 'all'" :class="filter === 'all' ? 'btn-primary' : 'btn-secondary'" class="text-sm">All</button>
            <button @click="filter = 'critical'" :class="filter === 'critical' ? 'btn-danger' : 'btn-secondary'" class="text-sm">Critical</button>
            <button @click="filter = 'low'" :class="filter === 'low' ? 'btn-primary' : 'btn-secondary'" class="text-sm text-orange-600">Low</button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="card p-4">
            <div class="text-xs text-gray-500 uppercase tracking-wider mb-1">Total Alerts</div>
            <div class="text-2xl font-bold text-gray-900" x-text="items.length"></div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-red-500 uppercase tracking-wider mb-1">Critical (Out of Stock)</div>
            <div class="text-2xl font-bold text-red-600" x-text="items.filter(i => (i.stock_qty ?? 0) <= 0).length"></div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-orange-500 uppercase tracking-wider mb-1">Low Stock</div>
            <div class="text-2xl font-bold text-orange-500" x-text="items.filter(i => (i.stock_qty ?? 0) > 0 && (i.stock_qty ?? 0) <= (i.reorder_level ?? 0)).length"></div>
        </div>
    </div>

    <div class="card p-0 overflow-hidden">
        <div x-show="loading" class="flex items-center justify-center py-16">
            <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
        </div>
        <div x-show="!loading" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-hd">Product</th>
                        <th class="table-hd">SKU</th>
                        <th class="table-hd">Category</th>
                        <th x-show="hasBranchCol" class="table-hd">Branch</th>
                        <th class="table-hd text-right">Current Stock</th>
                        <th class="table-hd text-right">Reorder Level</th>
                        <th class="table-hd text-right">Short By</th>
                        <th class="table-hd">Status</th>
                        <th class="table-hd">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="(p, idx) in filtered" :key="idx">
                        <tr class="hover:bg-gray-50" :class="(p.stock_qty ?? 0) <= 0 ? 'bg-red-50' : ''">
                            <td class="table-td font-medium text-gray-900" x-text="p.name"></td>
                            <td class="table-td font-mono text-xs text-gray-500" x-text="p.sku ?? p.code ?? '—'"></td>
                            <td class="table-td text-sm" x-text="p.category?.name ?? '—'"></td>
                            <td x-show="hasBranchCol" class="table-td text-sm">
                                <span x-show="p.branch_name" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700" x-text="p.branch_name"></span>
                            </td>
                            <td class="table-td text-right font-bold tabular-nums"
                                :class="(p.stock_qty ?? 0) <= 0 ? 'text-red-600' : 'text-orange-500'"
                                x-text="(p.stock_qty ?? 0) + (p.unit ? ' ' + p.unit : '')"></td>
                            <td class="table-td text-right text-gray-600 tabular-nums"
                                x-text="(p.reorder_level ?? 0) + (p.unit ? ' ' + p.unit : '')"></td>
                            <td class="table-td text-right font-semibold text-red-600 tabular-nums"
                                x-text="Math.max(0, (p.reorder_level ?? 0) - (p.stock_qty ?? 0)) + (p.unit ? ' ' + p.unit : '')"></td>
                            <td class="table-td">
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                                      :class="(p.stock_qty ?? 0) <= 0 ? 'bg-red-100 text-red-700' : 'bg-orange-100 text-orange-700'"
                                      x-text="(p.stock_qty ?? 0) <= 0 ? 'Out of Stock' : 'Low Stock'"></span>
                            </td>
                            <td class="table-td">
                                <div class="flex gap-2">
                                    <a :href="BASE + '/products/' + p.id" class="text-indigo-600 hover:underline text-sm font-medium">View</a>
                                    <a :href="BASE + '/purchase-orders/create?product_id=' + p.id" class="text-green-600 hover:underline text-sm font-medium">Order</a>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && filtered.length === 0">
                        <td :colspan="hasBranchCol ? 9 : 8" class="table-td text-center text-gray-400 py-10">
                            <svg class="w-10 h-10 mx-auto mb-2 text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            All stock levels are healthy!
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function lowStockPage() {
    return {
        items: [],
        loading: true,
        search: '',
        filter: 'all',
        get hasBranchCol() { return this.items.some(i => i.branch_name); },
        get filtered() {
            let list = this.items;
            if (this.filter === 'critical') list = list.filter(i => (i.stock_qty ?? 0) <= 0);
            else if (this.filter === 'low') list = list.filter(i => (i.stock_qty ?? 0) > 0 && (i.stock_qty ?? 0) <= (i.reorder_level ?? 0));
            const q = this.search.toLowerCase();
            if (!q) return list;
            return list.filter(i =>
                (i.name ?? '').toLowerCase().includes(q) ||
                (i.sku ?? '').toLowerCase().includes(q)
            );
        },
        async init() {
            window.addEventListener('branch-switched', () => this.init());
            try {
                const r = await apiFetch('/products/low-stock');
                if (!r) return;
                const data = await r.json();
                this.items = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load low stock data', 'error');
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\inventory\low-stock.blade.php ENDPATH**/ ?>