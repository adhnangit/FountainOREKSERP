<?php $__env->startSection('title', 'Assets'); ?>
<?php $__env->startSection('page-title', 'Assets'); ?>
<?php $__env->startSection('page-desc', 'Company equipment and who currently holds it'); ?>
<?php $sec = 'hr'; ?>

<?php $__env->startSection('content'); ?>
<div x-data="assetsPage()" x-init="init()">

    <div class="flex flex-wrap items-center gap-2 mb-4">
        <input x-model="search" @input="load()" type="text" placeholder="Search name or code…" class="input w-full sm:w-64" />
        <select x-model="filterStatus" @change="load()" class="input w-auto">
            <option value="">All Statuses</option>
            <option value="available">Available</option>
            <option value="assigned">Assigned</option>
            <option value="under_repair">Under Repair</option>
            <option value="retired">Retired</option>
        </select>
        <div class="flex-1"></div>
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Asset
        </button>
    </div>

    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/40">
                <tr>
                    <th class="table-hd">Code</th>
                    <th class="table-hd">Name</th>
                    <th class="table-hd">Category</th>
                    <th class="table-hd">Assigned To</th>
                    <th class="table-hd">Status</th>
                    <th class="table-hd">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                <template x-for="a in assets" :key="a.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                        <td class="table-td font-mono text-xs text-gray-500" x-text="a.asset_code"></td>
                        <td class="table-td font-medium text-gray-900 dark:text-gray-100" x-text="a.name"></td>
                        <td class="table-td text-gray-500" x-text="a.category ?? '—'"></td>
                        <td class="table-td text-gray-500">
                            <a x-show="a.current_assignment" :href="BASE + '/hr/employees/' + a.current_assignment?.employee?.id" class="text-indigo-600 hover:underline" x-text="[a.current_assignment?.employee?.first_name, a.current_assignment?.employee?.last_name].filter(Boolean).join(' ')"></a>
                            <span x-show="!a.current_assignment">—</span>
                        </td>
                        <td class="table-td"><span class="badge" :class="statusBadge(a.status)" x-text="a.status.replace('_',' ')"></span></td>
                        <td class="table-td">
                            <div class="flex items-center gap-3">
                                <button x-show="a.status === 'available'" @click="openAssign(a)" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Assign</button>
                                <button x-show="a.status === 'assigned'" @click="openReturn(a)" class="text-sm font-medium text-amber-600 hover:text-amber-800">Return</button>
                                <button @click="openEdit(a)" class="text-sm font-medium text-gray-500 hover:text-gray-700">Edit</button>
                                <button @click="deleteAsset(a)" class="text-sm font-medium text-red-500 hover:text-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && assets.length === 0">
                    <td colspan="6" class="text-center text-gray-400 py-16">No assets yet.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editId ? 'Edit Asset' : 'New Asset'"></h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">
                <div>
                    <label class="label">Name <span class="text-red-500">*</span></label>
                    <input x-model="form.name" type="text" class="input w-full" placeholder="e.g. Dell Latitude 5420" required />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Category</label>
                        <input x-model="form.category" type="text" class="input w-full" placeholder="e.g. Laptop" />
                    </div>
                    <div>
                        <label class="label">Branch</label>
                        <select x-model="form.branch_id" class="input w-full">
                            <option value="">— All / Company-wide —</option>
                            <template x-for="b in branches" :key="b.id"><option :value="b.id" x-text="b.name"></option></template>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Purchase Date</label>
                        <input type="date" x-model="form.purchase_date" class="input w-full" />
                    </div>
                    <div>
                        <label class="label">Purchase Cost (Rs.)</label>
                        <input type="number" step="0.01" min="0" x-model.number="form.purchase_cost" class="input w-full" />
                    </div>
                </div>
                <div>
                    <label class="label">Serial Number</label>
                    <input x-model="form.serial_number" type="text" class="input w-full" />
                </div>
                <div>
                    <label class="label">Notes</label>
                    <textarea x-model="form.notes" rows="2" class="input w-full resize-none"></textarea>
                </div>
                <div x-show="formError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : (editId ? 'Update' : 'Create')"></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Assign Modal -->
    <div x-show="showAssignModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showAssignModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Assign Asset</h3>
                <button @click="showAssignModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="label">Employee <span class="text-red-500">*</span></label>
                    <select x-model="assignForm.employee_id" class="input w-full">
                        <option value="">— Select —</option>
                        <template x-for="e in employees" :key="e.id"><option :value="e.id" x-text="[e.first_name, e.last_name].filter(Boolean).join(' ')"></option></template>
                    </select>
                </div>
                <div>
                    <label class="label">Assigned Date</label>
                    <input type="date" x-model="assignForm.assigned_date" class="input w-full" />
                </div>
                <div>
                    <label class="label">Condition</label>
                    <input type="text" x-model="assignForm.condition_on_assign" class="input w-full" placeholder="e.g. Good, minor scratches" />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showAssignModal = false" class="btn-secondary">Cancel</button>
                    <button @click="submitAssign()" class="btn-primary" :disabled="working" x-text="working ? 'Saving…' : 'Assign'"></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Return Modal -->
    <div x-show="showReturnModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showReturnModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Return Asset</h3>
                <button @click="showReturnModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="label">Returned Date</label>
                    <input type="date" x-model="returnForm.returned_date" class="input w-full" />
                </div>
                <div>
                    <label class="label">Condition on Return</label>
                    <input type="text" x-model="returnForm.condition_on_return" class="input w-full" />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showReturnModal = false" class="btn-secondary">Cancel</button>
                    <button @click="submitReturn()" class="btn-primary" :disabled="working" x-text="working ? 'Saving…' : 'Confirm Return'"></button>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function assetsPage() {
    return {
        assets: [],
        branches: [],
        employees: [],
        search: '',
        filterStatus: '',
        loading: true,
        showModal: false,
        editId: null,
        saving: false,
        formError: '',
        form: {},
        showAssignModal: false,
        assignForm: {},
        showReturnModal: false,
        returnForm: {},
        working: false,
        activeAsset: null,

        statusBadge(status) {
            const map = { available: 'badge-success', assigned: 'badge-primary', under_repair: 'badge-warning', retired: 'badge-gray' };
            return map[status] ?? 'badge-gray';
        },

        async init() {
            try {
                const [bd, ed] = await Promise.all([
                    apiFetch('/branches').then(r => r.json()),
                    apiFetch('/hr/employees?per_page=500').then(r => r.json()),
                ]);
                this.branches = bd.data ?? bd ?? [];
                this.employees = ed.data ?? ed ?? [];
            } catch (_) {}
            await this.load();
        },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.filterStatus) params.set('status', this.filterStatus);
                if (this.search) params.set('search', this.search);
                this.assets = await apiFetch('/hr/assets?' + params.toString()).then(r => r.json());
            } catch (e) {
                toast('Failed to load assets', 'error');
            } finally {
                this.loading = false;
            }
        },

        openCreate() {
            this.editId = null;
            this.form = { name: '', category: '', branch_id: '', purchase_date: '', purchase_cost: '', serial_number: '', notes: '' };
            this.formError = '';
            this.showModal = true;
        },

        openEdit(a) {
            this.editId = a.id;
            this.form = {
                name: a.name, category: a.category ?? '', branch_id: a.branch_id ?? '',
                purchase_date: a.purchase_date?.slice(0, 10) ?? '', purchase_cost: a.purchase_cost ?? '',
                serial_number: a.serial_number ?? '', notes: a.notes ?? '',
            };
            this.formError = '';
            this.showModal = true;
        },

        async save() {
            this.saving = true;
            this.formError = '';
            try {
                const url = this.editId ? `/hr/assets/${this.editId}` : '/hr/assets';
                const method = this.editId ? 'PUT' : 'POST';
                await apiFetch(url, { method, body: JSON.stringify(this.form) });
                toast(this.editId ? 'Asset updated.' : 'Asset created.');
                this.showModal = false;
                await this.load();
            } catch (e) {
                this.formError = e.message ?? 'Unexpected error.';
            } finally {
                this.saving = false;
            }
        },

        async deleteAsset(a) {
            if (!confirm(`Delete "${a.name}"?`)) return;
            try {
                await apiFetch(`/hr/assets/${a.id}`, { method: 'DELETE' });
                toast('Asset deleted.');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Cannot delete asset.', 'error');
            }
        },

        openAssign(a) {
            this.activeAsset = a;
            this.assignForm = { employee_id: '', assigned_date: new Date().toISOString().slice(0, 10), condition_on_assign: '' };
            this.showAssignModal = true;
        },

        async submitAssign() {
            if (!this.assignForm.employee_id) { toast('Select an employee', 'error'); return; }
            this.working = true;
            try {
                await apiFetch('/hr/assets/' + this.activeAsset.id + '/assign', { method: 'POST', body: JSON.stringify(this.assignForm) });
                toast('Asset assigned.', 'success');
                this.showAssignModal = false;
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to assign asset', 'error');
            } finally {
                this.working = false;
            }
        },

        openReturn(a) {
            this.activeAsset = a;
            this.returnForm = { returned_date: new Date().toISOString().slice(0, 10), condition_on_return: '' };
            this.showReturnModal = true;
        },

        async submitReturn() {
            this.working = true;
            try {
                const assignmentId = this.activeAsset.current_assignment?.id;
                await apiFetch('/hr/asset-assignments/' + assignmentId + '/return', { method: 'POST', body: JSON.stringify(this.returnForm) });
                toast('Asset returned.', 'success');
                this.showReturnModal = false;
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to return asset', 'error');
            } finally {
                this.working = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\hr\assets.blade.php ENDPATH**/ ?>