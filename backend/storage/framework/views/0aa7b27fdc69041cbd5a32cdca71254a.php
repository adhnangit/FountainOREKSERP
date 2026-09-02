<?php $__env->startSection('title', 'Leave Types'); ?>
<?php $__env->startSection('page-title', 'Leave Types'); ?>
<?php $__env->startSection('page-desc', 'Configure the kinds of leave staff can request'); ?>
<?php $sec = 'hr'; ?>

<?php $__env->startSection('content'); ?>
<div x-data="leaveTypesPage()" x-init="init()" x-cloak>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="text-sm text-gray-500" x-text="types.length + ' leave type(s)'"></div>
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Leave Type
        </button>
    </div>

    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/40">
                <tr>
                    <th class="table-hd">Name</th>
                    <th class="table-hd">Code</th>
                    <th class="table-hd">Branch</th>
                    <th class="table-hd">Days / Year</th>
                    <th class="table-hd">Paid</th>
                    <th class="table-hd">Needs Approval</th>
                    <th class="table-hd">Status</th>
                    <th class="table-hd">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                <template x-for="t in types" :key="t.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                        <td class="table-td font-medium text-gray-900 dark:text-gray-100" x-text="t.name"></td>
                        <td class="table-td text-gray-500 font-mono text-xs" x-text="t.code"></td>
                        <td class="table-td text-gray-500" x-text="branchName(t.branch_id)"></td>
                        <td class="table-td text-gray-500" x-text="t.max_days_per_year ?? 'Unlimited'"></td>
                        <td class="table-td" x-text="t.is_paid ? 'Yes' : 'No'"></td>
                        <td class="table-td" x-text="t.requires_approval ? 'Yes' : 'No'"></td>
                        <td class="table-td">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                                  :class="t.is_active ? 'badge-success' : 'badge-danger'"
                                  x-text="t.is_active ? 'Active' : 'Inactive'"></span>
                        </td>
                        <td class="table-td">
                            <div class="flex items-center gap-3">
                                <button @click="openEdit(t)" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</button>
                                <button @click="deleteType(t)" class="text-sm font-medium text-red-500 hover:text-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && types.length === 0">
                    <td colspan="8" class="text-center text-gray-400 py-16">No leave types set up yet.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editId ? 'Edit Leave Type' : 'New Leave Type'"></h3>
                <button @click="showModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">
                <div>
                    <label class="label">Name <span class="text-red-500">*</span></label>
                    <input x-model="form.name" type="text" class="input w-full" placeholder="e.g. Annual Leave" required />
                </div>
                <div>
                    <label class="label">Code <span class="text-red-500">*</span></label>
                    <input x-model="form.code" type="text" class="input w-full font-mono" placeholder="e.g. ANNUAL" maxlength="20" required />
                </div>
                <div>
                    <label class="label">Branch</label>
                    <select x-model="form.branch_id" class="input w-full">
                        <option value="">— All / Company-wide —</option>
                        <template x-for="b in branches" :key="b.id"><option :value="b.id" x-text="b.name"></option></template>
                    </select>
                </div>
                <div>
                    <label class="label">Days per Year</label>
                    <input x-model="form.max_days_per_year" type="number" step="0.5" min="0" class="input w-full" placeholder="Leave blank for unlimited" />
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="form.is_paid" class="rounded text-indigo-600" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Paid leave</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="form.requires_approval" class="rounded text-indigo-600" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Requires approval before it's granted</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer" x-show="editId">
                    <input type="checkbox" x-model="form.is_active" class="rounded text-indigo-600" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                </label>
                <div x-show="formError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : (editId ? 'Update Leave Type' : 'Create Leave Type')"></button>
                </div>
            </form>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function leaveTypesPage() {
    return {
        types: [],
        branches: [],
        defaultBranchId: '',
        loading: true,
        showModal: false,
        editId: null,
        saving: false,
        formError: '',
        form: {},

        blank() {
            return { name: '', code: '', branch_id: this.defaultBranchId, max_days_per_year: '', is_paid: true, requires_approval: true, is_active: true };
        },

        branchName(id) {
            return this.branches.find(b => b.id == id)?.name ?? 'All Branches';
        },

        async init() {
            await this.load();
            try {
                const bd = await apiFetch('/branches').then(r => r.json());
                this.branches = bd.data ?? bd ?? [];
                const u = JSON.parse(localStorage.getItem('medri_user') || '{}');
                const stored = localStorage.getItem('medri_branch');
                this.defaultBranchId = (stored && stored !== 'all') ? stored : (u.default_branch_id ?? '');
            } catch (_) {}
        },

        async load() {
            this.loading = true;
            try {
                const r = await apiFetch('/hr/leave-types');
                this.types = await r.json();
            } catch (e) {
                toast(e.message ?? 'Failed to load leave types', 'error');
            } finally {
                this.loading = false;
            }
        },

        openCreate() {
            this.editId = null;
            this.form = this.blank();
            this.formError = '';
            this.showModal = true;
        },

        openEdit(t) {
            this.editId = t.id;
            this.form = {
                name: t.name,
                code: t.code,
                branch_id: t.branch_id ?? '',
                max_days_per_year: t.max_days_per_year ?? '',
                is_paid: t.is_paid,
                requires_approval: t.requires_approval,
                is_active: t.is_active,
            };
            this.formError = '';
            this.showModal = true;
        },

        async save() {
            this.saving = true;
            this.formError = '';
            try {
                const payload = { ...this.form, max_days_per_year: this.form.max_days_per_year === '' ? null : this.form.max_days_per_year };
                const url = this.editId ? `/hr/leave-types/${this.editId}` : '/hr/leave-types';
                const method = this.editId ? 'PUT' : 'POST';
                await apiFetch(url, { method, body: JSON.stringify(payload) });
                toast(this.editId ? 'Leave type updated.' : 'Leave type created.');
                this.showModal = false;
                await this.load();
            } catch (e) {
                this.formError = e.message ?? 'Unexpected error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async deleteType(t) {
            if (!confirm(`Delete "${t.name}"?`)) return;
            try {
                await apiFetch(`/hr/leave-types/${t.id}`, { method: 'DELETE' });
                toast('Leave type deleted.');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Cannot delete leave type.', 'error');
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\hr\leave-types.blade.php ENDPATH**/ ?>