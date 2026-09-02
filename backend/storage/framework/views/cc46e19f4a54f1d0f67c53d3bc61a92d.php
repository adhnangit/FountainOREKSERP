<?php $__env->startSection('title', 'Performance Cycles'); ?>
<?php $__env->startSection('page-title', 'Performance Cycles'); ?>
<?php $__env->startSection('page-desc', 'Review periods and their generated reviews'); ?>
<?php $sec = 'hr'; ?>

<?php $__env->startSection('content'); ?>
<div x-data="performanceCyclesPage()" x-init="init()" x-cloak>

    <div class="flex justify-end mb-6">
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Cycle
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
                    <th class="table-hd">Period</th>
                    <th class="table-hd text-center">Reviews</th>
                    <th class="table-hd">Status</th>
                    <th class="table-hd">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                <template x-for="c in cycles" :key="c.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                        <td class="table-td font-medium text-gray-900 dark:text-gray-100" x-text="c.name"></td>
                        <td class="table-td text-gray-500" x-text="fmtDate(c.start_date) + ' – ' + fmtDate(c.end_date)"></td>
                        <td class="table-td text-center">
                            <a :href="BASE + '/hr/performance-reviews?cycle_id=' + c.id" class="text-indigo-600 hover:underline" x-text="c.reviews_count"></a>
                        </td>
                        <td class="table-td">
                            <select x-model="c.status" @change="updateStatus(c)" class="input text-xs w-auto">
                                <option value="draft">Draft</option>
                                <option value="active">Active</option>
                                <option value="closed">Closed</option>
                            </select>
                        </td>
                        <td class="table-td">
                            <div class="flex items-center gap-3">
                                <button @click="generateReviews(c)" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Generate Reviews</button>
                                <button @click="deleteCycle(c)" class="text-sm font-medium text-red-500 hover:text-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && cycles.length === 0">
                    <td colspan="5" class="text-center text-gray-400 py-16">No performance cycles yet.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Create Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">New Performance Cycle</h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">
                <div>
                    <label class="label">Name <span class="text-red-500">*</span></label>
                    <input x-model="form.name" type="text" class="input w-full" placeholder="e.g. 2026 Q1 Review" required />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Start Date <span class="text-red-500">*</span></label>
                        <input type="date" x-model="form.start_date" class="input w-full" required />
                    </div>
                    <div>
                        <label class="label">End Date <span class="text-red-500">*</span></label>
                        <input type="date" x-model="form.end_date" class="input w-full" required />
                    </div>
                </div>
                <div x-show="formError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : 'Create'"></button>
                </div>
            </form>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function performanceCyclesPage() {
    return {
        cycles: [],
        loading: true,
        showModal: false,
        saving: false,
        formError: '',
        form: {},

        async init() { await this.load(); },

        async load() {
            this.loading = true;
            try {
                this.cycles = await apiFetch('/hr/performance-cycles').then(r => r.json());
            } catch (e) {
                toast('Failed to load performance cycles', 'error');
            } finally {
                this.loading = false;
            }
        },

        openCreate() {
            this.form = { name: '', start_date: '', end_date: '' };
            this.formError = '';
            this.showModal = true;
        },

        async save() {
            this.saving = true;
            this.formError = '';
            try {
                await apiFetch('/hr/performance-cycles', { method: 'POST', body: JSON.stringify(this.form) });
                toast('Cycle created.', 'success');
                this.showModal = false;
                await this.load();
            } catch (e) {
                this.formError = e.message ?? 'Failed to create cycle.';
            } finally {
                this.saving = false;
            }
        },

        async updateStatus(c) {
            try {
                await apiFetch('/hr/performance-cycles/' + c.id, { method: 'PUT', body: JSON.stringify({ status: c.status }) });
                toast('Status updated.', 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to update status', 'error');
            }
        },

        async generateReviews(c) {
            if (!confirm(`Generate a pending review for every active employee who doesn't already have one in "${c.name}"?`)) return;
            try {
                const res = await apiFetch('/hr/performance-cycles/' + c.id + '/generate-reviews', { method: 'POST', body: JSON.stringify({}) }).then(r => r.json());
                toast(`${res.created} review(s) generated.`, 'success');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to generate reviews', 'error');
            }
        },

        async deleteCycle(c) {
            if (!confirm(`Delete "${c.name}"?`)) return;
            try {
                await apiFetch('/hr/performance-cycles/' + c.id, { method: 'DELETE' });
                toast('Cycle deleted.', 'success');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Cannot delete cycle.', 'error');
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\hr\performance-cycles.blade.php ENDPATH**/ ?>