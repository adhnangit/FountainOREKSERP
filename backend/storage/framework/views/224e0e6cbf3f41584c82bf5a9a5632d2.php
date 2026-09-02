<?php $__env->startSection('title', 'Services'); ?>
<?php $__env->startSection('page-title', 'Services'); ?>
<?php $__env->startSection('page-desc', 'Billable services — equipment rental, and other non-stock charges'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="servicesPage()" x-init="init()">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex flex-col sm:flex-row gap-2">
            <input x-model="search" type="text" placeholder="Search name, code…" class="input w-full sm:w-64" />
            <select x-model="categoryFilter" class="input w-44">
                <option value="">All Categories</option>
                <template x-for="cat in categories" :key="cat.id">
                    <option :value="cat.id" x-text="cat.name"></option>
                </template>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?php echo e(url('/services/categories')); ?>" class="btn-secondary inline-flex items-center gap-2">
                Categories
            </a>
            <a href="<?php echo e(url('/services/create')); ?>" class="btn-primary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Service
            </a>
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
                        <th class="table-hd">Name</th>
                        <th class="table-hd">Code</th>
                        <th class="table-hd">Category</th>
                        <th class="table-hd">Unit</th>
                        <th class="table-hd text-right">Rate</th>
                        <th class="table-hd">Status</th>
                        <th class="table-hd">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="s in filtered" :key="s.id">
                        <tr class="hover:bg-gray-50">
                            <td class="table-td font-medium text-gray-900" x-text="s.name"></td>
                            <td class="table-td text-gray-500 font-mono text-xs" x-text="s.code"></td>
                            <td class="table-td" x-text="s.category?.name ?? '—'"></td>
                            <td class="table-td" x-text="s.unit ?? '—'"></td>
                            <td class="table-td text-right font-semibold" x-text="fmtMoney(s.rate ?? 0)"></td>
                            <td class="table-td">
                                <span :class="(s.is_active ?? true) ? 'badge-success' : 'badge-gray'"
                                    x-text="(s.is_active ?? true) ? 'Active' : 'Inactive'"></span>
                            </td>
                            <td class="table-td">
                                <a href="#" @click.prevent="openEdit(s)" class="text-indigo-600 hover:underline text-sm font-medium">Edit</a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && filtered.length === 0">
                        <td colspan="7" class="table-td text-center text-gray-400 py-10">No services found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showEdit = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Edit Service</h3>
                <button @click="showEdit = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="saveEdit()" class="p-6 space-y-4">
                <div>
                    <label class="label">Name <span class="text-red-500">*</span></label>
                    <input x-model="editForm.name" type="text" class="input w-full" required />
                </div>
                <div>
                    <label class="label">Category</label>
                    <select x-model="editForm.category_id" class="input w-full">
                        <option value="">— None —</option>
                        <template x-for="c in categories" :key="c.id">
                            <option :value="c.id" x-text="c.name"></option>
                        </template>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Unit</label>
                        <input x-model="editForm.unit" type="text" class="input w-full" placeholder="per day" />
                    </div>
                    <div>
                        <label class="label">Rate <span class="text-red-500">*</span></label>
                        <input x-model="editForm.rate" type="number" step="0.01" min="0" class="input w-full" required />
                    </div>
                </div>
                <div>
                    <label class="label">Description</label>
                    <textarea x-model="editForm.description" rows="2" class="input w-full resize-none"></textarea>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="editForm.is_active" class="rounded text-indigo-600" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                </label>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showEdit = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : 'Save Changes'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function servicesPage() {
    return {
        items: [],
        categories: [],
        loading: true,
        saving: false,
        search: '',
        categoryFilter: '',
        showEdit: false,
        editForm: {},
        get filtered() {
            let list = this.items;
            if (this.categoryFilter) list = list.filter(s => s.category_id == this.categoryFilter);
            const q = this.search.toLowerCase();
            if (!q) return list;
            return list.filter(s =>
                (s.name ?? '').toLowerCase().includes(q) ||
                (s.code ?? '').toLowerCase().includes(q)
            );
        },
        async init() {
            try {
                const [sr, cr] = await Promise.all([
                    apiFetch('/services?per_page=200').then(r => r.json()),
                    apiFetch('/services/categories').then(r => r.json()),
                ]);
                this.items = sr.data ?? sr ?? [];
                this.categories = cr.data ?? cr ?? [];
            } catch (e) {
                toast('Failed to load services', 'error');
            } finally {
                this.loading = false;
            }
        },
        openEdit(s) {
            this.editForm = {
                id: s.id,
                name: s.name,
                category_id: s.category_id ?? '',
                unit: s.unit ?? '',
                rate: s.rate,
                description: s.description ?? '',
                is_active: s.is_active ?? true,
            };
            this.showEdit = true;
        },
        async saveEdit() {
            this.saving = true;
            try {
                const r = await apiFetch('/services/' + this.editForm.id, { method: 'PUT', body: JSON.stringify(this.editForm) });
                const updated = await r.json();
                const idx = this.items.findIndex(s => s.id === updated.id);
                if (idx >= 0) this.items[idx] = updated;
                this.showEdit = false;
                toast('Service updated.', 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to update service', 'error');
            } finally {
                this.saving = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\services\index.blade.php ENDPATH**/ ?>