<?php $__env->startSection('title', 'Departments'); ?>
<?php $__env->startSection('page-title', 'Departments'); ?>
<?php $__env->startSection('page-desc', 'Organize your staff into departments and sub-departments'); ?>
<?php $sec = 'hr'; ?>

<?php $__env->startSection('content'); ?>
<div x-data="departmentsPage()" x-init="init()" x-cloak>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="text-sm text-gray-500" x-text="departments.length + ' top-level departments'"></div>
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Department
        </button>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <!-- Departments list -->
    <div x-show="!loading" class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/40">
                <tr>
                    <th class="table-hd">Name</th>
                    <th class="table-hd">Code</th>
                    <th class="table-hd">Branch</th>
                    <th class="table-hd">Employees</th>
                    <th class="table-hd">Status</th>
                    <th class="table-hd">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                <template x-for="dept in departments" :key="dept.id">
                    <template x-for="row in [dept, ...(dept.children||[])]" :key="row.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                            <td class="table-td font-medium text-gray-900 dark:text-gray-100">
                                <span x-show="row.parent_id" class="text-gray-300 mr-1">└</span>
                                <span x-text="row.name"></span>
                            </td>
                            <td class="table-td text-gray-500 font-mono text-xs" x-text="row.code"></td>
                            <td class="table-td text-gray-500" x-text="branchName(row.branch_id)"></td>
                            <td class="table-td text-gray-500" x-text="row.employees_count ?? 0"></td>
                            <td class="table-td">
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                                      :class="row.is_active ? 'badge-success' : 'badge-danger'"
                                      x-text="row.is_active ? 'Active' : 'Inactive'"></span>
                            </td>
                            <td class="table-td">
                                <div class="flex items-center gap-3">
                                    <button @click="openEdit(row)" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</button>
                                    <button @click="deleteDepartment(row)" class="text-sm font-medium text-red-500 hover:text-red-700">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </template>

                <tr x-show="!loading && departments.length === 0">
                    <td colspan="6" class="text-center text-gray-400 py-16">No departments yet. Create your first one.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editId ? 'Edit Department' : 'New Department'"></h3>
                <button @click="showModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">

                <div>
                    <label class="label">Branch</label>
                    <select x-model="form.branch_id" class="input w-full">
                        <option value="">— All / Company-wide —</option>
                        <template x-for="b in branches" :key="b.id">
                            <option :value="b.id" x-text="b.name"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="label">Name <span class="text-red-500">*</span></label>
                    <input x-model="form.name" type="text" class="input w-full" placeholder="e.g. Sales &amp; Marketing" required />
                </div>

                <div>
                    <label class="label">Code <span class="text-red-500">*</span></label>
                    <input x-model="form.code" type="text" class="input w-full font-mono" placeholder="e.g. SALES" maxlength="20" required />
                </div>

                <div>
                    <label class="label">Parent Department</label>
                    <select x-model="form.parent_id" class="input w-full">
                        <option value="">— None (top-level) —</option>
                        <template x-for="p in departments.filter(d => d.id !== editId)" :key="p.id">
                            <option :value="p.id" x-text="p.name"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="label">Description</label>
                    <textarea x-model="form.description" rows="2" class="input w-full resize-none" placeholder="Optional"></textarea>
                </div>

                <label class="flex items-center gap-2 cursor-pointer" x-show="editId">
                    <input type="checkbox" x-model="form.is_active" class="rounded text-indigo-600" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
                </label>

                <div x-show="formError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : (editId ? 'Update Department' : 'Create Department')"></button>
                </div>
            </form>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function departmentsPage() {
    return {
        departments: [],
        branches: [],
        defaultBranchId: '',
        loading: true,
        showModal: false,
        editId: null,
        saving: false,
        formError: '',
        form: {},

        blank() {
            return { branch_id: this.defaultBranchId, name: '', code: '', parent_id: '', description: '', is_active: true };
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
                const r = await apiFetch('/hr/departments');
                this.departments = await r.json();
            } catch (e) {
                toast(e.message ?? 'Failed to load departments', 'error');
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

        openEdit(dept) {
            this.editId = dept.id;
            this.form = {
                branch_id: dept.branch_id ?? '',
                name: dept.name,
                code: dept.code,
                parent_id: dept.parent_id ?? '',
                description: dept.description ?? '',
                is_active: dept.is_active,
            };
            this.formError = '';
            this.showModal = true;
        },

        async save() {
            this.saving = true;
            this.formError = '';
            try {
                const url = this.editId ? `/hr/departments/${this.editId}` : '/hr/departments';
                const method = this.editId ? 'PUT' : 'POST';
                await apiFetch(url, { method, body: JSON.stringify(this.form) });
                toast(this.editId ? 'Department updated.' : 'Department created.');
                this.showModal = false;
                await this.load();
            } catch (e) {
                this.formError = e.message ?? 'Unexpected error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async deleteDepartment(dept) {
            if (!confirm(`Delete "${dept.name}"? This cannot be undone.`)) return;
            try {
                await apiFetch(`/hr/departments/${dept.id}`, { method: 'DELETE' });
                toast('Department deleted.');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Cannot delete department.', 'error');
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\hr\departments.blade.php ENDPATH**/ ?>