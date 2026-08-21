
<?php $__env->startSection('title', 'Product Detail'); ?>
<?php $__env->startSection('page-title', 'Product Detail'); ?>
<?php $__env->startSection('page-desc', 'Product information, stock levels and movement history'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="productShowPage()" x-init="init()">

    <div x-show="loading" class="flex items-center justify-center py-20">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading">
        <!-- Back -->
        <div class="flex items-center justify-between mb-6">
            <a href="<?php echo e(url('/products')); ?>" class="btn-secondary inline-flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Back
            </a>
            <a :href="BASE + '/products/' + id + '/edit'" class="btn-secondary text-sm">Edit</a>
        </div>

        <!-- Product Info -->
        <div class="card p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900" x-text="product.name ?? '—'"></h2>
                    <p class="text-sm text-gray-500 mt-1" x-text="'SKU: ' + (product.sku ?? 'N/A')"></p>
                </div>
                <span :class="(product.is_active ?? true) ? 'badge-success' : 'badge-gray'"
                    x-text="(product.is_active ?? true) ? 'Active' : 'Inactive'"></span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <dl class="space-y-2 text-sm">
                    <div class="flex gap-2"><dt class="w-28 text-gray-500">Category:</dt><dd x-text="product.category?.name ?? '—'"></dd></div>
                    <div class="flex gap-2"><dt class="w-28 text-gray-500">Unit:</dt><dd x-text="product.unit ?? '—'"></dd></div>
                    <div class="flex gap-2"><dt class="w-28 text-gray-500">Barcode:</dt><dd x-text="product.barcode ?? '—'"></dd></div>
                </dl>
                <dl class="space-y-2 text-sm">
                    <div class="flex gap-2"><dt class="w-28 text-gray-500">Cost Price:</dt><dd class="font-medium" x-text="fmtMoney(product.cost_price ?? 0)"></dd></div>
                    <div class="flex gap-2"><dt class="w-28 text-gray-500">Sale Price:</dt><dd class="font-medium text-indigo-600" x-text="fmtMoney(product.sale_price ?? product.selling_price ?? 0)"></dd></div>
                    <div class="flex gap-2"><dt class="w-28 text-gray-500">Reorder Level:</dt><dd x-text="product.reorder_level ?? 0"></dd></div>
                </dl>
                <div class="text-sm">
                    <div class="text-gray-500 mb-1">Description</div>
                    <p class="text-gray-700" x-text="product.description ?? 'No description'"></p>
                </div>
            </div>
        </div>

        <!-- Stock per Branch -->
        <div class="card p-0 overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">Stock by Branch</h3>
            </div>
            <div x-show="loadingStock" class="flex items-center justify-center py-8">
                <svg class="animate-spin w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            </div>
            <div x-show="!loadingStock" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="table-hd">Branch</th>
                            <th class="table-hd text-right">Quantity</th>
                            <th class="table-hd text-right">Stock Value</th>
                            <th class="table-hd">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="s in stockData" :key="s.branch_id ?? s.id ?? Math.random()">
                            <tr class="hover:bg-gray-50">
                                <td class="table-td font-medium" x-text="s.branch?.name ?? s.branch_name ?? '—'"></td>
                                <td class="table-td text-right font-semibold"
                                    :class="(s.quantity ?? 0) <= (product.reorder_level ?? 0) ? 'text-red-600' : 'text-gray-800'"
                                    x-text="s.quantity ?? 0"></td>
                                <td class="table-td text-right" x-text="fmtMoney((s.quantity ?? 0) * (product.cost_price ?? 0))"></td>
                                <td class="table-td">
                                    <span :class="(s.quantity ?? 0) <= 0 ? 'badge-danger' : (s.quantity ?? 0) <= (product.reorder_level ?? 0) ? 'badge-warning' : 'badge-success'"
                                        x-text="(s.quantity ?? 0) <= 0 ? 'Out of Stock' : (s.quantity ?? 0) <= (product.reorder_level ?? 0) ? 'Low Stock' : 'In Stock'"></span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="stockData.length === 0 && !loadingStock">
                            <td colspan="4" class="table-td text-center text-gray-400 py-8">No stock data available.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Batches -->
        <div class="card p-0 overflow-hidden mb-6">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">Batches</h3>
            </div>
            <div x-show="loadingBatches" class="flex items-center justify-center py-8">
                <svg class="animate-spin w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            </div>
            <div x-show="!loadingBatches" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="table-hd">Batch</th>
                            <th class="table-hd">Received</th>
                            <th class="table-hd">Expiry</th>
                            <th class="table-hd text-right">Remaining Qty</th>
                            <th class="table-hd text-right">Cost Price</th>
                            <th class="table-hd text-right">Selling Price</th>
                            <th class="table-hd" x-show="isSuperAdmin"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="b in batches" :key="b.id">
                            <tr class="hover:bg-gray-50">
                                <td class="table-td font-medium" x-text="b.batch_code + (b.batch_number ? ' (' + b.batch_number + ')' : '')"></td>
                                <td class="table-td" x-text="b.received_date ?? '—'"></td>
                                <td class="table-td" x-text="b.expiry_date ?? '—'"></td>
                                <td class="table-td text-right font-semibold" :class="(b.available_qty ?? 0) < 0 ? 'text-red-600' : 'text-gray-800'" x-text="b.available_qty ?? 0"></td>
                                <td class="table-td text-right">
                                    <template x-if="isSuperAdmin && editingBatchId === b.id">
                                        <input type="number" x-model.number="editBatchForm.cost_price" min="0" step="0.01" class="input-field w-28 text-right" />
                                    </template>
                                    <template x-if="!(isSuperAdmin && editingBatchId === b.id)">
                                        <span x-text="fmtMoney(b.unit_cost ?? 0)"></span>
                                    </template>
                                </td>
                                <td class="table-td text-right">
                                    <template x-if="isSuperAdmin && editingBatchId === b.id">
                                        <input type="number" x-model.number="editBatchForm.selling_price" min="0" step="0.01" class="input-field w-28 text-right" />
                                    </template>
                                    <template x-if="!(isSuperAdmin && editingBatchId === b.id)">
                                        <span x-text="fmtMoney(b.selling_price ?? 0)"></span>
                                    </template>
                                </td>
                                <td class="table-td" x-show="isSuperAdmin">
                                    <template x-if="editingBatchId !== b.id">
                                        <button class="btn-secondary text-xs" @click="startEditBatch(b)">Edit</button>
                                    </template>
                                    <template x-if="editingBatchId === b.id">
                                        <div class="flex gap-2">
                                            <button class="btn-primary text-xs" @click="saveBatchPrice(b)">Save</button>
                                            <button class="btn-secondary text-xs" @click="editingBatchId = null">Cancel</button>
                                        </div>
                                    </template>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="batches.length === 0 && !loadingBatches">
                            <td colspan="7" class="table-td text-center text-gray-400 py-8">No batches with stock remaining.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Stock Movements -->
        <div class="card p-0 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-700">Stock Movements</h3>
            </div>
            <div x-show="loadingMovements" class="flex items-center justify-center py-8">
                <svg class="animate-spin w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            </div>
            <div x-show="!loadingMovements" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="table-hd">Date</th>
                            <th class="table-hd">Type</th>
                            <th class="table-hd">Reference</th>
                            <th class="table-hd">Branch</th>
                            <th class="table-hd text-right">Qty In</th>
                            <th class="table-hd text-right">Qty Out</th>
                            <th class="table-hd text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="m in movements" :key="m.id ?? Math.random()">
                            <tr class="hover:bg-gray-50">
                                <td class="table-td text-sm" x-text="fmtDate(m.movement_date ?? m.created_at)"></td>
                                <td class="table-td">
                                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                                          :class="m.type?.includes('in') ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                          x-text="(m.type ?? '—').replace('_',' ')"></span>
                                </td>
                                <td class="table-td text-xs text-gray-500"
                                    x-text="m.reference_type ? m.reference_type.replace('_',' ') + ' #' + m.reference_id : (m.notes ?? '—')"></td>
                                <td class="table-td text-sm" x-text="m.branch?.name ?? '—'"></td>
                                <td class="table-td text-right font-semibold text-green-600 tabular-nums"
                                    x-text="parseFloat(m.quantity) > 0 ? '+' + parseFloat(m.quantity).toFixed(2) : '—'"></td>
                                <td class="table-td text-right font-semibold text-red-500 tabular-nums"
                                    x-text="parseFloat(m.quantity) < 0 ? parseFloat(m.quantity).toFixed(2) : '—'"></td>
                                <td class="table-td text-right font-bold tabular-nums"
                                    :class="parseFloat(m.balance_quantity ?? 0) < 0 ? 'text-red-600' : 'text-gray-700'"
                                    x-text="m.balance_quantity != null ? parseFloat(m.balance_quantity).toFixed(2) : '—'"></td>
                            </tr>
                        </template>
                        <tr x-show="movements.length === 0 && !loadingMovements">
                            <td colspan="7" class="table-td text-center text-gray-400 py-8">No movements recorded.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function productShowPage() {
    return {
        loading: true,
        loadingStock: true,
        loadingMovements: true,
        loadingBatches: true,
        product: {},
        stockData: [],
        movements: [],
        batches: [],
        isSuperAdmin: false,
        editingBatchId: null,
        editBatchForm: { cost_price: 0, selling_price: 0 },
        get id() { return window.location.pathname.split('/').filter(Boolean).pop(); },
        async init() {
            try {
                const u = JSON.parse(localStorage.getItem('medri_user') || '{}');
                this.isSuperAdmin = !!u.is_super_admin || (u.roles ?? []).includes('super_admin');
            } catch (_) {}
            try {
                const r = await apiFetch('/products/' + this.id);
                if (!r) return;
                const data = await r.json();
                this.product = data.data ?? data;
            } catch (e) {
                toast('Failed to load product', 'error');
            } finally {
                this.loading = false;
            }
            this.loadStock();
            this.loadMovements();
            this.loadBatches();
        },
        async loadBatches() {
            try {
                const branchId = this.product.branch_id;
                const r = await apiFetch('/products/' + this.id + '/batches' + (branchId ? '?branch_id=' + branchId : ''));
                if (!r) return;
                const data = await r.json();
                this.batches = Array.isArray(data) ? data : (data.data ?? []);
            } catch (e) {
                this.batches = [];
            } finally {
                this.loadingBatches = false;
            }
        },
        startEditBatch(b) {
            this.editingBatchId = b.id;
            this.editBatchForm = { cost_price: parseFloat(b.unit_cost ?? 0), selling_price: parseFloat(b.selling_price ?? 0) };
        },
        async saveBatchPrice(b) {
            try {
                await apiFetch('/products/' + this.id + '/batches/' + b.id, { method: 'PUT', body: JSON.stringify(this.editBatchForm) });
                toast('Batch price updated', 'success');
                this.editingBatchId = null;
                this.loadBatches();
            } catch (e) {
                toast('Failed to update batch price', 'error');
            }
        },
        async loadStock() {
            try {
                const r = await apiFetch('/products/' + this.id + '/stock');
                if (!r) return;
                const data = await r.json();
                this.stockData = data.stock ?? data.data ?? [];
            } catch (e) {
                this.stockData = [];
            } finally {
                this.loadingStock = false;
            }
        },
        async loadMovements() {
            try {
                const r = await apiFetch('/products/' + this.id + '/movements');
                if (!r) return;
                const data = await r.json();
                this.movements = data.data ?? data ?? [];
            } catch (e) {
                this.movements = [];
            } finally {
                this.loadingMovements = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/products/show.blade.php ENDPATH**/ ?>