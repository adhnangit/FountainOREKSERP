<?php $__env->startSection('title', 'Staff Directory'); ?>
<?php $__env->startSection('page-title', 'Staff Directory'); ?>
<?php $__env->startSection('page-desc', 'Team contacts and department listing'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="directoryPage()" x-init="init()">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <input x-model="search" type="text" placeholder="Search by name, department…" class="input w-full sm:w-72" />
        <button @click="showModal = true" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Contact
        </button>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <!-- Contact Grid -->
    <div x-show="!loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <template x-for="contact in filtered" :key="contact.id">
            <div class="card p-5 flex flex-col gap-3">
                <!-- Avatar + Name -->
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm shrink-0"
                        x-text="(contact.name ?? '?').charAt(0).toUpperCase()"></div>
                    <div>
                        <div class="font-semibold text-gray-900 text-sm leading-tight" x-text="contact.name ?? '—'"></div>
                        <div class="text-xs text-indigo-600" x-text="contact.position ?? contact.title ?? '—'"></div>
                    </div>
                </div>
                <!-- Department -->
                <div class="text-xs text-gray-500 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span x-text="contact.department ?? '—'"></span>
                </div>
                <!-- Phone -->
                <div x-show="contact.phone" class="text-xs text-gray-600 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <a :href="'tel:' + contact.phone" class="hover:text-indigo-600" x-text="contact.phone"></a>
                </div>
                <!-- Email -->
                <div x-show="contact.email" class="text-xs text-gray-600 flex items-center gap-1 truncate">
                    <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <a :href="'mailto:' + contact.email" class="hover:text-indigo-600 truncate" x-text="contact.email"></a>
                </div>
            </div>
        </template>

        <!-- Empty -->
        <div x-show="!loading && filtered.length === 0" class="sm:col-span-2 lg:col-span-3 xl:col-span-4 card p-10 text-center text-gray-400">
            No contacts found.
        </div>
    </div>

    <!-- Add Contact Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative card p-6 w-full max-w-md z-10 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Add Contact</h3>
            <div class="space-y-4">
                <div>
                    <label class="label">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.name" class="input" placeholder="Full name" />
                </div>
                <div>
                    <label class="label">Position / Title</label>
                    <input type="text" x-model="form.position" class="input" placeholder="e.g. Sales Manager" />
                </div>
                <div>
                    <label class="label">Department</label>
                    <input type="text" x-model="form.department" class="input" placeholder="e.g. Finance" />
                </div>
                <div>
                    <label class="label">Phone</label>
                    <input type="tel" x-model="form.phone" class="input" placeholder="+94 77 000 0000" />
                </div>
                <div>
                    <label class="label">Email</label>
                    <input type="email" x-model="form.email" class="input" placeholder="staff@company.com" />
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-5">
                <button @click="showModal = false" class="btn-secondary">Cancel</button>
                <button @click="submit()" :disabled="submitting" class="btn-primary">
                    <span x-text="submitting ? 'Saving…' : 'Add Contact'"></span>
                </button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function directoryPage() {
    return {
        items: [],
        loading: true,
        showModal: false,
        submitting: false,
        search: '',
        form: { name: '', position: '', department: '', phone: '', email: '' },
        get filtered() {
            const q = this.search.toLowerCase();
            if (!q) return this.items;
            return this.items.filter(c =>
                (c.name ?? '').toLowerCase().includes(q) ||
                (c.department ?? '').toLowerCase().includes(q) ||
                (c.position ?? c.title ?? '').toLowerCase().includes(q)
            );
        },
        async init() {
            try {
                const data = await apiFetch('/directory');
                this.items = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load directory', 'error');
            } finally {
                this.loading = false;
            }
        },
        async submit() {
            if (!this.form.name) { toast('Name is required', 'error'); return; }
            this.submitting = true;
            try {
                const data = await apiFetch('/directory', { method: 'POST', body: JSON.stringify(this.form) });
                this.items.unshift(data.data ?? data);
                toast('Contact added', 'success');
                this.showModal = false;
                this.form = { name: '', position: '', department: '', phone: '', email: '' };
            } catch (e) {
                toast(e.message ?? 'Failed to add contact', 'error');
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/directory/index.blade.php ENDPATH**/ ?>