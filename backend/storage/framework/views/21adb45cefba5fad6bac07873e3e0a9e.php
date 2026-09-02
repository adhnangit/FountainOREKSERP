<?php $__env->startSection('title', 'Branch Management'); ?>
<?php $__env->startSection('page-title', 'Branch Management'); ?>
<?php $__env->startSection('page-desc', 'Create and manage company branches'); ?>
<?php $sec = 'admin'; ?>

<?php $__env->startSection('content'); ?>
<div x-data="branchesPage()" x-init="init()" x-cloak>

    <!-- Super-admin guard -->
    <template x-if="!isSuperAdmin">
        <div class="flex flex-col items-center justify-center py-32 text-gray-400">
            <svg class="w-12 h-12 mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <p class="font-semibold text-gray-500">Access Restricted</p>
            <p class="text-sm mt-1">Only Super Admins can manage branches.</p>
        </div>
    </template>

    <template x-if="isSuperAdmin">
        <div>
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
                <div class="text-sm text-gray-500" x-text="branches.length + ' branches configured'"></div>
                <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Branch
                </button>
            </div>

            <!-- Loading -->
            <div x-show="loading" class="flex items-center justify-center py-16">
                <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            </div>

            <!-- Branches grid -->
            <div x-show="!loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <template x-for="b in branches" :key="b.id">
                    <div class="card p-5 flex flex-col gap-3 hover:shadow-md transition-shadow">

                        <!-- Header -->
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-black flex-shrink-0"
                                     :style="'background:linear-gradient(135deg,' + branchColor(b.code) + ')'">
                                    <span x-text="b.code.substring(0,2).toUpperCase()"></span>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900 leading-tight" x-text="b.name"></div>
                                    <div class="text-xs text-gray-400 font-mono mt-0.5" x-text="b.code"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <template x-if="b.is_head_office">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded" style="background:#eef2ff;color:#1B3EB6">HQ</span>
                                </template>
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                                      :class="b.is_active ? 'badge-success' : 'badge-danger'"
                                      x-text="b.is_active ? 'Active' : 'Inactive'"></span>
                            </div>
                        </div>

                        <!-- Details -->
                        <dl class="space-y-1.5 text-sm flex-1">
                            <template x-if="b.manager_name">
                                <div class="flex items-center gap-2 text-gray-600">
                                    <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span x-text="b.manager_name"></span>
                                </div>
                            </template>
                            <template x-if="b.phone">
                                <div class="flex items-center gap-2 text-gray-600">
                                    <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span x-text="b.phone"></span>
                                </div>
                            </template>
                            <template x-if="b.email">
                                <div class="flex items-center gap-2 text-gray-600">
                                    <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span x-text="b.email"></span>
                                </div>
                            </template>
                            <template x-if="b.city || b.address">
                                <div class="flex items-start gap-2 text-gray-600">
                                    <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span x-text="[b.address, b.city].filter(Boolean).join(', ')"></span>
                                </div>
                            </template>
                        </dl>

                        <!-- Actions -->
                        <div class="flex gap-2 pt-2 border-t border-gray-100">
                            <button @click="openEdit(b)"
                                    class="flex-1 text-center text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-lg py-1.5 transition-colors">
                                Edit
                            </button>
                            <button @click="toggleActive(b)"
                                    class="flex-1 text-center text-sm font-medium rounded-lg py-1.5 transition-colors"
                                    :class="b.is_active ? 'text-orange-600 hover:bg-orange-50' : 'text-green-600 hover:bg-green-50'"
                                    x-text="b.is_active ? 'Deactivate' : 'Activate'">
                            </button>
                            <button @click="deleteBranch(b)"
                                    x-show="!b.is_head_office"
                                    class="flex-1 text-center text-sm font-medium text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg py-1.5 transition-colors">
                                Delete
                            </button>
                        </div>
                    </div>
                </template>

                <div x-show="!loading && branches.length === 0" class="col-span-full text-center text-gray-400 py-16">
                    No branches found. Create your first branch.
                </div>
            </div>
        </div>
    </template>

    <!-- Create / Edit Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="text-lg font-semibold text-gray-900" x-text="editId ? 'Edit Branch' : 'New Branch'"></h3>
                <button @click="showModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Branch Name <span class="text-red-500">*</span></label>
                        <input x-model="form.name" type="text" class="input w-full" placeholder="e.g. Colombo Branch" required />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Branch Code <span class="text-red-500">*</span></label>
                        <input x-model="form.code" type="text" class="input w-full" placeholder="e.g. COL" maxlength="10" :disabled="!!editId" required />
                        <p x-show="!editId" class="text-xs text-gray-400 mt-0.5">Cannot be changed after creation.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Branch Logo</label>
                    <div class="flex items-center gap-3">
                        <template x-if="logoPreview">
                            <img :src="logoPreview" class="w-14 h-14 rounded-lg object-contain bg-gray-50 border border-gray-200 flex-shrink-0">
                        </template>
                        <template x-if="!logoPreview">
                            <div class="w-14 h-14 rounded-lg bg-gray-50 border border-dashed border-gray-300 flex-shrink-0"></div>
                        </template>
                        <div class="flex-1 min-w-0">
                            <input type="file" accept="image/*" @change="onLogoSelected($event)" class="text-xs w-full" />
                            <button type="button" x-show="logoPreview" @click="clearLogo()" class="text-xs text-red-500 hover:text-red-700 mt-1">Remove logo</button>
                            <p class="text-xs text-gray-400 mt-0.5">Used on this branch's invoice prints. PNG/JPG, max 2MB.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Manager Name</label>
                        <input x-model="form.manager_name" type="text" class="input w-full" placeholder="Branch Manager" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Phone</label>
                        <input x-model="form.phone" type="text" class="input w-full" placeholder="+94 11 234 5678" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Email</label>
                    <input x-model="form.email" type="email" class="input w-full" placeholder="branch@company.com" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Address</label>
                    <input x-model="form.address" type="text" class="input w-full" placeholder="Street address" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">City</label>
                    <input x-model="form.city" type="text" class="input w-full" placeholder="City" />
                </div>

                <div class="flex items-center gap-6 pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="form.is_active" class="rounded text-indigo-600" />
                        <span class="text-sm text-gray-700">Active</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="form.is_head_office" class="rounded text-indigo-600" />
                        <span class="text-sm text-gray-700">Head Office</span>
                    </label>
                </div>

                <div class="pt-3 border-t border-gray-100">
                    <button type="button" @click="showInvoiceTemplate = !showInvoiceTemplate"
                            class="flex items-center justify-between w-full text-left">
                        <span class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Invoice Print Template</span>
                        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="showInvoiceTemplate ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <p class="text-xs text-gray-400 mt-1">Customize the footer note, payment instructions, and signature labels printed on this branch's invoices. Leave blank to use the default text.</p>

                    <div x-show="showInvoiceTemplate" x-cloak class="space-y-3 mt-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Footer Note</label>
                            <input x-model="form.invoice_footer_note" type="text" class="input w-full" placeholder="e.g. NOTE: RETURNS WILL NOT BE ACCEPTED AFTER 14 DAYS FROM THE DATE OF DELIVERY. (T &amp; C APPLY)" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Payment / Bank Details</label>
                            <textarea x-model="form.invoice_bank_details" rows="3" class="input w-full resize-none" placeholder='All cheques should be drawn in favour of &quot;COMPANY NAME&quot; &amp; crossed &quot;A/C Payee Only&quot;&#10;Bankers: Bank Name - Branch  A/C No: 000000000&#10;Please state your account number &amp; invoice number when making payments by cheque'></textarea>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Signature 1</label>
                                <input x-model="form.invoice_signature_label_1" type="text" class="input w-full" placeholder="Prepared By" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Signature 2</label>
                                <input x-model="form.invoice_signature_label_2" type="text" class="input w-full" placeholder="Checked By" />
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Signature 3</label>
                                <input x-model="form.invoice_signature_label_3" type="text" class="input w-full" placeholder="Customer Signature & Company Seal" />
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="formError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : (editId ? 'Update Branch' : 'Create Branch')"></button>
                </div>
            </form>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function branchesPage() {
    return {
        branches: [],
        loading: true,
        showModal: false,
        editId: null,
        saving: false,
        formError: '',
        form: {},
        isSuperAdmin: false,
        logoFile: null,
        logoPreview: null,
        removeLogo: false,
        showInvoiceTemplate: false,

        blank() {
            return {
                name:'', code:'', manager_name:'', phone:'', email:'', address:'', city:'', is_active:true, is_head_office:false,
                invoice_footer_note:'', invoice_bank_details:'', invoice_signature_label_1:'', invoice_signature_label_2:'', invoice_signature_label_3:'',
            };
        },

        onLogoSelected(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.logoFile = file;
            this.removeLogo = false;
            this.logoPreview = URL.createObjectURL(file);
        },

        clearLogo() {
            this.logoFile = null;
            this.logoPreview = null;
            this.removeLogo = true;
        },

        async init() {
            const u = localStorage.getItem('medri_user');
            const user = u ? JSON.parse(u) : null;
            this.isSuperAdmin = user?.roles?.includes('super_admin') ?? false;
            if (this.isSuperAdmin) await this.load();
            else this.loading = false;
        },

        async load() {
            this.loading = true;
            try {
                const r = await apiFetch('/branches');
                if (!r) return;
                this.branches = await r.json();
            } catch(e) {
                toast('Failed to load branches', 'error');
            } finally {
                this.loading = false;
            }
        },

        openCreate() {
            this.editId = null;
            this.form = this.blank();
            this.logoFile = null;
            this.logoPreview = null;
            this.removeLogo = false;
            this.showInvoiceTemplate = false;
            this.formError = '';
            this.showModal = true;
        },

        openEdit(b) {
            this.editId = b.id;
            const tpl = b.settings?.invoice_template ?? {};
            const sig = tpl.signature_labels ?? [];
            this.form = {
                name: b.name, code: b.code, manager_name: b.manager_name ?? '', phone: b.phone ?? '', email: b.email ?? '', address: b.address ?? '', city: b.city ?? '', is_active: b.is_active, is_head_office: b.is_head_office,
                invoice_footer_note: tpl.footer_note ?? '',
                invoice_bank_details: tpl.bank_details ?? '',
                invoice_signature_label_1: sig[0] ?? '',
                invoice_signature_label_2: sig[1] ?? '',
                invoice_signature_label_3: sig[2] ?? '',
            };
            this.logoFile = null;
            this.logoPreview = b.logo_url ?? null;
            this.removeLogo = false;
            this.showInvoiceTemplate = false;
            this.formError = '';
            this.showModal = true;
        },

        async save() {
            this.saving = true;
            this.formError = '';
            try {
                const fd = new FormData();
                Object.entries(this.form).forEach(([k, v]) => fd.append(k, typeof v === 'boolean' ? (v ? '1' : '0') : v));
                if (this.logoFile) fd.append('logo', this.logoFile);
                if (this.editId) {
                    fd.append('_method', 'PUT');
                    if (this.removeLogo) fd.append('remove_logo', '1');
                }
                const url = this.editId ? `/branches/${this.editId}` : '/branches';
                await apiFetch(url, { method: 'POST', body: fd });
                toast(this.editId ? 'Branch updated.' : 'Branch created.');
                this.showModal = false;
                await this.load();
                window.dispatchEvent(new Event('branches-changed'));
            } catch(e) {
                this.formError = e.message ?? 'Unexpected error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async toggleActive(b) {
            try {
                const r = await apiFetch(`/branches/${b.id}`, { method:'PUT', body: JSON.stringify({ is_active: !b.is_active }) });
                if (!r) return;
                toast(b.is_active ? 'Branch deactivated.' : 'Branch activated.');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to update branch', 'error');
            }
        },

        async deleteBranch(b) {
            if (!confirm(`Delete "${b.name}"? This cannot be undone.`)) return;
            try {
                const r = await apiFetch(`/branches/${b.id}`, { method: 'DELETE' });
                if (!r) return;
                toast('Branch deleted.');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Cannot delete.', 'error');
            }
        },

        branchColor(code) {
            const colors = [
                '#1B3EB6,#0D2272','#059669,#047857','#d97706,#b45309',
                '#dc2626,#b91c1c','#7c3aed,#5b21b6','#0891b2,#0e7490',
            ];
            let h = 0;
            for (let i = 0; i < code.length; i++) h = (h * 31 + code.charCodeAt(i)) % colors.length;
            return colors[Math.abs(h)];
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\settings\branches.blade.php ENDPATH**/ ?>