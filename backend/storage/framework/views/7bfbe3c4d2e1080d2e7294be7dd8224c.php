<?php $__env->startSection('title', 'Inquiry Statuses'); ?>
<?php $__env->startSection('page-title', 'Inquiries — Statuses'); ?>
<?php $__env->startSection('page-desc', 'Manage the pipeline stages used to track an inquiry from first contact to close'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="inquiryStatusesPage()" x-init="init()" x-cloak>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex gap-2">
            <a href="<?php echo e(url('/inquiries')); ?>" class="btn-secondary text-sm">All Inquiries</a>
            <a href="<?php echo e(url('/inquiries/subjects')); ?>" class="btn-secondary text-sm">Subjects</a>
        </div>
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Status
        </button>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <!-- Statuses list -->
    <div x-show="!loading" class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/40">
                <tr>
                    <th class="table-hd">Status</th>
                    <th class="table-hd text-center">Order Priority</th>
                    <th class="table-hd text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                <template x-for="s in statuses" :key="s.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                        <td class="table-td">
                            <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full inline-flex items-center gap-2"
                                  :style="'background:' + s.color + '22; color:' + s.color">
                                <span class="w-2 h-2 rounded-full" :style="'background:' + s.color"></span>
                                <span x-text="s.name"></span>
                            </span>
                        </td>
                        <td class="table-td text-center text-gray-600 dark:text-gray-300" x-text="s.order_by"></td>
                        <td class="table-td text-right">
                            <div class="flex items-center justify-end gap-3">
                                <button @click="openEdit(s)" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</button>
                                <button @click="deleteStatus(s)" class="text-sm font-medium text-red-500 hover:text-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                </template>

                <tr x-show="!loading && statuses.length === 0">
                    <td colspan="3" class="text-center text-gray-400 py-16">No statuses yet. Add statuses to track a lead's stage (e.g. New, Contacted, Won, Lost).</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editId ? 'Edit Status' : 'Add New Status'"></h3>
                <button @click="showModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="label">Status Name <span class="text-red-500">*</span></label>
                        <input x-model="form.name" type="text" class="input w-full" placeholder="e.g. Contacted" required />
                    </div>
                    <div>
                        <label class="label">Badge Color</label>
                        <input x-model="form.color" type="color" class="input w-full p-1 h-[42px]" />
                    </div>
                </div>
                <div>
                    <label class="label">Order Priority</label>
                    <input x-model.number="form.order_by" type="number" class="input w-full" placeholder="0" />
                    <p class="text-xs text-gray-400 mt-1">Lower numbers appear first in status lists.</p>
                </div>

                <div x-show="formError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : 'Save Status'"></button>
                </div>
            </form>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function inquiryStatusesPage() {
    return {
        statuses: [],
        loading: true,
        showModal: false,
        editId: null,
        saving: false,
        formError: '',
        form: {},

        async init() {
            await this.load();
        },

        async load() {
            this.loading = true;
            try {
                this.statuses = await apiFetch('/inquiry-statuses').then(r => r.json());
            } catch (e) {
                toast(e.message ?? 'Failed to load statuses', 'error');
            } finally {
                this.loading = false;
            }
        },

        openCreate() {
            this.editId = null;
            this.form = { name: '', color: '#4f46e5', order_by: 0 };
            this.formError = '';
            this.showModal = true;
        },

        openEdit(s) {
            this.editId = s.id;
            this.form = { name: s.name, color: s.color, order_by: s.order_by };
            this.formError = '';
            this.showModal = true;
        },

        async save() {
            if (!this.form.name) { toast('Status name is required', 'error'); return; }
            this.saving = true;
            this.formError = '';
            try {
                const url = this.editId ? '/inquiry-statuses/' + this.editId : '/inquiry-statuses';
                const method = this.editId ? 'PUT' : 'POST';
                await apiFetch(url, { method, body: JSON.stringify(this.form) });
                toast(this.editId ? 'Status updated.' : 'Status created.');
                this.showModal = false;
                await this.load();
            } catch (e) {
                this.formError = e.message ?? 'Unexpected error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async deleteStatus(s) {
            if (!confirm(`Delete "${s.name}"?`)) return;
            try {
                await apiFetch('/inquiry-statuses/' + s.id, { method: 'DELETE' });
                toast('Status deleted.');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Cannot delete status.', 'error');
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views/inquiries/statuses.blade.php ENDPATH**/ ?>