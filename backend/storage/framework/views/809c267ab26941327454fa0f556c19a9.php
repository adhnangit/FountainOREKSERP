<?php $__env->startSection('title', 'Onboarding / Offboarding Templates'); ?>
<?php $__env->startSection('page-title', 'Checklist Templates'); ?>
<?php $__env->startSection('page-desc', 'Reusable onboarding and offboarding task lists'); ?>
<?php $sec = 'hr'; ?>

<?php $__env->startSection('content'); ?>
<div x-data="checklistTemplatesPage()" x-init="init()" x-cloak>

    <div class="flex flex-wrap items-center justify-between gap-2 mb-6">
        <select x-model="filterType" @change="load()" class="input w-auto">
            <option value="">All Types</option>
            <option value="onboarding">Onboarding</option>
            <option value="offboarding">Offboarding</option>
        </select>
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Template
        </button>
    </div>

    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <template x-for="t in templates" :key="t.id">
            <div class="card p-5">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100" x-text="t.name"></h3>
                        <span class="badge mt-1" :class="t.type === 'onboarding' ? 'badge-success' : 'badge-warning'" x-text="t.type"></span>
                        <span class="badge badge-gray mt-1" x-show="!t.is_active">Inactive</span>
                    </div>
                    <button @click="deleteTemplate(t)" class="text-red-500 hover:text-red-700 text-sm">Delete</button>
                </div>
                <ul class="text-sm divide-y divide-gray-50 dark:divide-gray-800 mt-3">
                    <template x-for="item in t.items" :key="item.id">
                        <li class="flex items-center justify-between py-1.5">
                            <span x-text="item.title"></span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-400" x-text="(item.due_days_offset >= 0 ? '+' : '') + item.due_days_offset + 'd'"></span>
                                <button @click="deleteItem(t, item)" class="text-gray-300 hover:text-red-500">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </li>
                    </template>
                    <li x-show="!t.items?.length" class="text-gray-400 py-1.5">No items yet.</li>
                </ul>
                <button @click="openAddItem(t)" class="text-xs text-indigo-600 hover:underline mt-2">+ Add item</button>
            </div>
        </template>
        <div x-show="templates.length === 0" class="text-center text-gray-400 py-16 col-span-2">No templates yet.</div>
    </div>

    <!-- New Template Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">New Template</h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">
                <div>
                    <label class="label">Name <span class="text-red-500">*</span></label>
                    <input x-model="form.name" type="text" class="input w-full" placeholder="e.g. Standard Onboarding" required />
                </div>
                <div>
                    <label class="label">Type</label>
                    <select x-model="form.type" class="input w-full">
                        <option value="onboarding">Onboarding</option>
                        <option value="offboarding">Offboarding</option>
                    </select>
                </div>
                <div x-show="formError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : 'Create'"></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Item Modal -->
    <div x-show="showItemModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showItemModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Add Item</h3>
                <button @click="showItemModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="label">Title <span class="text-red-500">*</span></label>
                    <input type="text" x-model="itemForm.title" class="input w-full" />
                </div>
                <div>
                    <label class="label">Due (days relative to join/exit date)</label>
                    <input type="number" x-model.number="itemForm.due_days_offset" class="input w-full" placeholder="e.g. -3, 0, 7" />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showItemModal = false" class="btn-secondary">Cancel</button>
                    <button @click="saveItem()" class="btn-primary" :disabled="itemSaving" x-text="itemSaving ? 'Saving…' : 'Add'"></button>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function checklistTemplatesPage() {
    return {
        templates: [],
        filterType: '',
        loading: true,
        showModal: false,
        saving: false,
        formError: '',
        form: {},
        showItemModal: false,
        itemSaving: false,
        itemForm: {},
        activeTemplate: null,

        async init() { await this.load(); },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.filterType) params.set('type', this.filterType);
                this.templates = await apiFetch('/hr/checklist-templates?' + params.toString()).then(r => r.json());
            } catch (e) {
                toast('Failed to load templates', 'error');
            } finally {
                this.loading = false;
            }
        },

        openCreate() {
            this.form = { name: '', type: 'onboarding' };
            this.formError = '';
            this.showModal = true;
        },

        async save() {
            this.saving = true;
            this.formError = '';
            try {
                await apiFetch('/hr/checklist-templates', { method: 'POST', body: JSON.stringify(this.form) });
                toast('Template created.', 'success');
                this.showModal = false;
                await this.load();
            } catch (e) {
                this.formError = e.message ?? 'Failed to create template.';
            } finally {
                this.saving = false;
            }
        },

        openAddItem(t) {
            this.activeTemplate = t;
            this.itemForm = { title: '', due_days_offset: 0 };
            this.showItemModal = true;
        },

        async saveItem() {
            if (!this.itemForm.title) { toast('Title is required', 'error'); return; }
            this.itemSaving = true;
            try {
                await apiFetch('/hr/checklist-templates/' + this.activeTemplate.id + '/items', { method: 'POST', body: JSON.stringify(this.itemForm) });
                toast('Item added.', 'success');
                this.showItemModal = false;
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to add item', 'error');
            } finally {
                this.itemSaving = false;
            }
        },

        async deleteItem(t, item) {
            if (!confirm(`Remove "${item.title}"?`)) return;
            try {
                await apiFetch('/hr/checklist-template-items/' + item.id, { method: 'DELETE' });
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to remove item', 'error');
            }
        },

        async deleteTemplate(t) {
            if (!confirm(`Delete "${t.name}"?`)) return;
            try {
                await apiFetch('/hr/checklist-templates/' + t.id, { method: 'DELETE' });
                toast('Template deleted.', 'success');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Cannot delete template.', 'error');
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\hr\checklist-templates.blade.php ENDPATH**/ ?>