<?php $__env->startSection('title', 'Holidays'); ?>
<?php $__env->startSection('page-title', 'Holidays'); ?>
<?php $__env->startSection('page-desc', 'Company and branch holiday calendar'); ?>
<?php $sec = 'hr'; ?>

<?php $__env->startSection('content'); ?>
<div x-data="holidaysPage()" x-init="init()" x-cloak>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="text-sm text-gray-500" x-text="holidays.length + ' holiday(s)'"></div>
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Holiday
        </button>
    </div>

    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/40">
                <tr>
                    <th class="table-hd">Date</th>
                    <th class="table-hd">Name</th>
                    <th class="table-hd">Branch</th>
                    <th class="table-hd">Recurring</th>
                    <th class="table-hd">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                <template x-for="h in holidays" :key="h.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                        <td class="table-td font-medium text-gray-900 dark:text-gray-100" x-text="fmtDate(h.date)"></td>
                        <td class="table-td" x-text="h.name"></td>
                        <td class="table-td text-gray-500" x-text="branchName(h.branch_id)"></td>
                        <td class="table-td text-gray-500" x-text="h.is_recurring_yearly ? 'Yearly' : '—'"></td>
                        <td class="table-td">
                            <div class="flex items-center gap-3">
                                <button @click="openEdit(h)" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</button>
                                <button @click="deleteHoliday(h)" class="text-sm font-medium text-red-500 hover:text-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && holidays.length === 0">
                    <td colspan="5" class="text-center text-gray-400 py-16">No holidays set up yet.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editId ? 'Edit Holiday' : 'New Holiday'"></h3>
                <button @click="showModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">
                <div>
                    <label class="label">Date <span class="text-red-500">*</span></label>
                    <input x-model="form.date" type="date" class="input w-full" required />
                </div>
                <div>
                    <label class="label">Name <span class="text-red-500">*</span></label>
                    <input x-model="form.name" type="text" class="input w-full" placeholder="e.g. New Year's Day" required />
                </div>
                <div>
                    <label class="label">Branch</label>
                    <select x-model="form.branch_id" class="input w-full">
                        <option value="">— All / Company-wide —</option>
                        <template x-for="b in branches" :key="b.id"><option :value="b.id" x-text="b.name"></option></template>
                    </select>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="form.is_recurring_yearly" class="rounded text-indigo-600" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Repeats every year on this date</span>
                </label>
                <div>
                    <label class="label">Notes</label>
                    <textarea x-model="form.notes" rows="2" class="input w-full resize-none"></textarea>
                </div>
                <div x-show="formError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : (editId ? 'Update Holiday' : 'Create Holiday')"></button>
                </div>
            </form>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function holidaysPage() {
    return {
        holidays: [],
        branches: [],
        defaultBranchId: '',
        loading: true,
        showModal: false,
        editId: null,
        saving: false,
        formError: '',
        form: {},

        blank() {
            return { date: new Date().toISOString().slice(0, 10), name: '', branch_id: this.defaultBranchId, is_recurring_yearly: false, notes: '' };
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
                const r = await apiFetch('/hr/holidays');
                this.holidays = await r.json();
            } catch (e) {
                toast(e.message ?? 'Failed to load holidays', 'error');
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

        openEdit(h) {
            this.editId = h.id;
            this.form = {
                date: h.date?.slice(0, 10),
                name: h.name,
                branch_id: h.branch_id ?? '',
                is_recurring_yearly: h.is_recurring_yearly,
                notes: h.notes ?? '',
            };
            this.formError = '';
            this.showModal = true;
        },

        async save() {
            this.saving = true;
            this.formError = '';
            try {
                const url = this.editId ? `/hr/holidays/${this.editId}` : '/hr/holidays';
                const method = this.editId ? 'PUT' : 'POST';
                await apiFetch(url, { method, body: JSON.stringify(this.form) });
                toast(this.editId ? 'Holiday updated.' : 'Holiday created.');
                this.showModal = false;
                await this.load();
            } catch (e) {
                this.formError = e.message ?? 'Unexpected error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async deleteHoliday(h) {
            if (!confirm(`Delete "${h.name}"?`)) return;
            try {
                await apiFetch(`/hr/holidays/${h.id}`, { method: 'DELETE' });
                toast('Holiday deleted.');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Cannot delete holiday.', 'error');
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\hr\holidays.blade.php ENDPATH**/ ?>