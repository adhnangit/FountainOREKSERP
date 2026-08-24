@extends('layouts.app')
@section('title', 'Task Categories')
@section('page-title', 'Task Manager — Categories')
@section('page-desc', 'Organize work tasks into categories for tracking and reporting')

@section('content')
<div x-data="taskCategoriesPage()" x-init="init()" x-cloak>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex gap-2">
            <a href="{{ url('/task-manager') }}" class="btn-secondary text-sm">Dashboard</a>
            <a href="{{ url('/task-manager/board') }}" class="btn-secondary text-sm">Task Board</a>
        </div>
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Category
        </button>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <!-- Categories list -->
    <div x-show="!loading" class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/40">
                <tr>
                    <th class="table-hd">Category</th>
                    <th class="table-hd text-center">Tasks</th>
                    <th class="table-hd text-center">Status</th>
                    <th class="table-hd text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                <template x-for="cat in categories" :key="cat.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                        <td class="table-td font-medium text-gray-900 dark:text-gray-100">
                            <span :style="'margin-left:' + (cat.depth * 18) + 'px'" class="inline-flex items-center gap-2">
                                <span x-show="cat.depth > 0" class="text-gray-300">↳</span>
                                <span class="w-3.5 h-3.5 rounded" :style="'background:' + cat.color + '; display:inline-block; border:1px solid rgba(0,0,0,.08)'"></span>
                                <span x-text="cat.name"></span>
                            </span>
                        </td>
                        <td class="table-td text-center">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full badge-primary" x-text="cat.tasks_count"></span>
                        </td>
                        <td class="table-td text-center">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full" :class="cat.status === 'Active' ? 'badge-success' : 'badge-danger'" x-text="cat.status"></span>
                        </td>
                        <td class="table-td text-right">
                            <div class="flex items-center justify-end gap-3">
                                <button @click="openCreate(cat.id)" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">+ Sub</button>
                                <button @click="openEdit(cat)" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</button>
                                <button @click="deleteCategory(cat)" class="text-sm font-medium text-red-500 hover:text-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                </template>

                <tr x-show="!loading && categories.length === 0">
                    <td colspan="4" class="text-center text-gray-400 py-16">No categories yet. Add categories to organize your tasks (e.g. Sales, Maintenance, Admin).</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editId ? 'Edit Category' : 'Add New Category'"></h3>
                <button @click="showModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="label">Category Name <span class="text-red-500">*</span></label>
                        <input x-model="form.name" type="text" class="input w-full" placeholder="e.g. Sales Follow-up" required />
                    </div>
                    <div>
                        <label class="label">Color</label>
                        <input x-model="form.color" type="color" class="input w-full p-1 h-[42px]" />
                    </div>
                </div>
                <div>
                    <label class="label">Status</label>
                    <select x-model="form.status" class="input w-full">
                        <option value="Active">Active</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                <div>
                    <label class="label">Parent Category</label>
                    <select x-model="form.parent_id" class="input w-full">
                        <option value="">— Top Level (no parent) —</option>
                        <template x-for="c in categories.filter(c => c.id !== editId)" :key="c.id">
                            <option :value="c.id" x-text="'—'.repeat(c.depth) + ' ' + c.name"></option>
                        </template>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Leave blank for a top-level category, or pick one to make this a sub-category.</p>
                </div>

                <div x-show="formError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : 'Save Category'"></button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function taskCategoriesPage() {
    return {
        categories: [],
        loading: true,
        showModal: false,
        editId: null,
        saving: false,
        formError: '',
        form: {},

        blank(parentId = '') {
            return { name: '', color: '#2563EB', status: 'Active', parent_id: parentId };
        },

        async init() {
            await this.load();
        },

        async load() {
            this.loading = true;
            try {
                this.categories = await apiFetch('/work-task-categories').then(r => r.json());
            } catch (e) {
                toast(e.message ?? 'Failed to load categories', 'error');
            } finally {
                this.loading = false;
            }
        },

        openCreate(parentId = '') {
            this.editId = null;
            this.form = this.blank(parentId);
            this.formError = '';
            this.showModal = true;
        },

        openEdit(cat) {
            this.editId = cat.id;
            this.form = {
                name: cat.name,
                color: cat.color,
                status: cat.status,
                parent_id: cat.parent_id ?? '',
            };
            this.formError = '';
            this.showModal = true;
        },

        async save() {
            if (!this.form.name) { toast('Category name is required', 'error'); return; }
            this.saving = true;
            this.formError = '';
            try {
                const payload = { ...this.form, parent_id: this.form.parent_id || null };
                const url = this.editId ? '/work-task-categories/' + this.editId : '/work-task-categories';
                const method = this.editId ? 'PUT' : 'POST';
                await apiFetch(url, { method, body: JSON.stringify(payload) });
                toast(this.editId ? 'Category updated.' : 'Category created.');
                this.showModal = false;
                await this.load();
            } catch (e) {
                this.formError = e.message ?? 'Unexpected error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async deleteCategory(cat) {
            if (!confirm(`Delete "${cat.name}"? Its sub-categories will move up a level, and its tasks will become uncategorized.`)) return;
            try {
                await apiFetch('/work-task-categories/' + cat.id, { method: 'DELETE' });
                toast('Category deleted.');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Cannot delete category.', 'error');
            }
        },
    };
}
</script>
@endpush
