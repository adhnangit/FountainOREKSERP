<?php $__env->startSection('title', 'Inquiry Subjects'); ?>
<?php $__env->startSection('page-title', 'Inquiries — Subjects'); ?>
<?php $__env->startSection('page-desc', 'Manage the subject/category options used when logging an inquiry'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="inquirySubjectsPage()" x-init="init()" x-cloak>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex gap-2">
            <a href="<?php echo e(url('/inquiries')); ?>" class="btn-secondary text-sm">All Inquiries</a>
            <a href="<?php echo e(url('/inquiries/statuses')); ?>" class="btn-secondary text-sm">Statuses</a>
        </div>
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Subject
        </button>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <!-- Subjects list -->
    <div x-show="!loading" class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/40">
                <tr>
                    <th class="table-hd">Subject</th>
                    <th class="table-hd text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                <template x-for="s in subjects" :key="s.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                        <td class="table-td font-medium text-gray-900 dark:text-gray-100" x-text="s.name"></td>
                        <td class="table-td text-right">
                            <div class="flex items-center justify-end gap-3">
                                <button @click="openEdit(s)" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</button>
                                <button @click="deleteSubject(s)" class="text-sm font-medium text-red-500 hover:text-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                </template>

                <tr x-show="!loading && subjects.length === 0">
                    <td colspan="2" class="text-center text-gray-400 py-16">No subjects yet. Add subjects to categorize incoming inquiries (e.g. Pricing, Support, Partnership).</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editId ? 'Edit Subject' : 'Add New Subject'"></h3>
                <button @click="showModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">
                <div>
                    <label class="label">Subject Name <span class="text-red-500">*</span></label>
                    <input x-model="form.name" type="text" class="input w-full" placeholder="e.g. Pricing Inquiry" required />
                </div>

                <div x-show="formError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : 'Save Subject'"></button>
                </div>
            </form>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function inquirySubjectsPage() {
    return {
        subjects: [],
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
                this.subjects = await apiFetch('/inquiry-subjects').then(r => r.json());
            } catch (e) {
                toast(e.message ?? 'Failed to load subjects', 'error');
            } finally {
                this.loading = false;
            }
        },

        openCreate() {
            this.editId = null;
            this.form = { name: '' };
            this.formError = '';
            this.showModal = true;
        },

        openEdit(s) {
            this.editId = s.id;
            this.form = { name: s.name };
            this.formError = '';
            this.showModal = true;
        },

        async save() {
            if (!this.form.name) { toast('Subject name is required', 'error'); return; }
            this.saving = true;
            this.formError = '';
            try {
                const url = this.editId ? '/inquiry-subjects/' + this.editId : '/inquiry-subjects';
                const method = this.editId ? 'PUT' : 'POST';
                await apiFetch(url, { method, body: JSON.stringify(this.form) });
                toast(this.editId ? 'Subject updated.' : 'Subject created.');
                this.showModal = false;
                await this.load();
            } catch (e) {
                this.formError = e.message ?? 'Unexpected error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async deleteSubject(s) {
            if (!confirm(`Delete "${s.name}"?`)) return;
            try {
                await apiFetch('/inquiry-subjects/' + s.id, { method: 'DELETE' });
                toast('Subject deleted.');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Cannot delete subject.', 'error');
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views/inquiries/subjects.blade.php ENDPATH**/ ?>