<?php $__env->startSection('title', 'Roles & Permissions'); ?>
<?php $__env->startSection('page-title', 'Roles & Permissions'); ?>
<?php $__env->startSection('page-desc', 'Manage system roles and their permissions'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="rolesPage()" x-init="init()">

    
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <div class="text-sm text-gray-500" x-text="roles.length + ' roles configured'"></div>
            <template x-if="selectedRole">
                <span class="text-xs px-2.5 py-1 rounded-full font-bold bg-indigo-100 text-indigo-700"
                      x-text="selectedRole.name.replace('_',' ')"></span>
            </template>
        </div>
        <button @click="showModal = true" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Role
        </button>
    </div>

    
    <div x-show="loading" class="flex items-center justify-center py-20">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
        </svg>
    </div>

    <div x-show="!loading" class="grid grid-cols-1 lg:grid-cols-12 gap-5">

        
        <div class="lg:col-span-4">
            <div class="card overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-100 dark:border-gray-700"
                     style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
                    <h3 class="text-sm font-bold text-white">Roles</h3>
                    <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.6)"
                       x-text="roles.length + ' roles · click to manage permissions'"></p>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-gray-700/40">
                    <template x-for="role in roles" :key="role.id">
                        <div class="flex items-center gap-3 px-4 py-3 cursor-pointer transition-all"
                             :class="selectedRole?.id === role.id
                               ? 'bg-indigo-50 dark:bg-indigo-900/20 border-l-4 border-indigo-500'
                               : 'hover:bg-gray-50 dark:hover:bg-gray-800/20 border-l-4 border-transparent'"
                             @click="selectedRole = role">
                            
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 text-lg"
                                 :style="roleStyle(role.name)">
                                <span x-text="roleIcon(role.name)"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 capitalize"
                                     x-text="role.name.replace(/_/g,' ')"></div>
                                <div class="text-xs text-gray-400 mt-0.5"
                                     x-text="permCountFor(role) + ' permissions'"></div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                                      :class="(role.users_count > 0) ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-400'"
                                      x-text="role.users_count + ' user' + (role.users_count !== 1 ? 's' : '')"></span>
                                <svg class="w-4 h-4 text-gray-300 transition-transform"
                                     :class="selectedRole?.id === role.id ? 'text-indigo-500 rotate-90' : ''"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        
        <div class="lg:col-span-8">

            
            <div x-show="!selectedRole" class="card flex flex-col items-center justify-center py-24 text-gray-400">
                <svg class="w-14 h-14 opacity-20 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.2">
                    <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                <p class="font-semibold text-base">Select a role</p>
                <p class="text-sm mt-1">Click any role on the left to manage its permissions</p>
            </div>

            
            <div x-show="selectedRole" class="space-y-4">

                
                <div class="card px-5 py-4 flex items-center justify-between"
                     :style="'background:' + (roleColor(selectedRole?.name) ?? '#1B3EB6') + '18;border-left:4px solid ' + (roleColor(selectedRole?.name) ?? '#1B3EB6')">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl" x-text="roleIcon(selectedRole?.name)"></span>
                        <div>
                            <div class="font-bold text-gray-800 dark:text-gray-100 capitalize"
                                 x-text="selectedRole?.name.replace(/_/g,' ')"></div>
                            <div class="text-xs text-gray-500 mt-0.5"
                                 x-text="permCountFor(selectedRole) + ' of ' + permissions.length + ' permissions enabled'"></div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="toggleAllPerms(true)"
                                class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-all"
                                style="background:#dcfce7;color:#15803d;border:1px solid #86efac">
                            Grant All
                        </button>
                        <button @click="toggleAllPerms(false)"
                                class="text-xs font-semibold px-3 py-1.5 rounded-lg transition-all"
                                style="background:#fee2e2;color:#dc2626;border:1px solid #fca5a5">
                            Revoke All
                        </button>
                    </div>
                </div>

                
                <template x-for="cat in categories" :key="cat.key">
                    <div class="card overflow-hidden">
                        
                        <div class="px-4 py-3 flex items-center justify-between border-b border-gray-100 dark:border-gray-700"
                             :style="'background:' + cat.bg">
                            <div class="flex items-center gap-2.5">
                                <span class="text-base" x-text="cat.icon"></span>
                                <div>
                                    <div class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="cat.label"></div>
                                    <div class="text-xs text-gray-400"
                                         x-text="catEnabledCount(cat) + ' / ' + catPermissions(cat).length + ' enabled'"></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                
                                <div class="w-20 h-1.5 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                    <div class="h-full rounded-full transition-all"
                                         :style="'width:' + catPct(cat) + '%;background:' + cat.color"></div>
                                </div>
                                
                                <button @click="toggleCategory(cat, catEnabledCount(cat) < catPermissions(cat).length)"
                                        class="text-xs font-semibold px-2.5 py-1 rounded-lg border transition-all"
                                        :style="catEnabledCount(cat) === catPermissions(cat).length
                                          ? 'background:#fee2e2;color:#dc2626;border-color:#fca5a5'
                                          : 'background:#dcfce7;color:#15803d;border-color:#86efac'"
                                        x-text="catEnabledCount(cat) === catPermissions(cat).length ? 'Revoke All' : 'Grant All'">
                                </button>
                            </div>
                        </div>

                        
                        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-2">
                            <template x-for="perm in catPermissions(cat)" :key="perm.id">
                                <label class="flex items-start gap-2.5 p-2.5 rounded-xl cursor-pointer transition-all group"
                                       :class="hasPermission(perm.id)
                                         ? 'bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-700'
                                         : 'hover:bg-gray-50 dark:hover:bg-gray-800/20 border border-transparent'">
                                    <input type="checkbox"
                                           :checked="hasPermission(perm.id)"
                                           @change="togglePermission(perm.id, $event.target.checked)"
                                           class="mt-0.5 rounded border-gray-300 text-indigo-600 flex-shrink-0" />
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-gray-700 dark:text-gray-200 leading-tight"
                                             x-text="permLabel(perm.name)"></div>
                                        <div class="text-[10px] text-gray-400 font-mono mt-0.5" x-text="perm.name"></div>
                                    </div>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(15,23,42,0.55);backdrop-filter:blur(4px)"
         @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700"
                 style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
                <h3 class="text-base font-bold text-white">New Role</h3>
                <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.6)">Create a custom role and assign permissions</p>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div>
                    <label class="label">Role Name <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.name" class="input" placeholder="e.g. sales_manager" />
                    <p class="text-xs text-gray-400 mt-1">Use lowercase with underscores (e.g. sales_manager)</p>
                </div>
                <div>
                    <label class="label">Description</label>
                    <textarea x-model="form.description" rows="3" class="input" placeholder="Describe this role's responsibilities…"></textarea>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                <button @click="showModal = false" class="btn-secondary">Cancel</button>
                <button @click="submitRole()" :disabled="submitting" class="btn-primary flex items-center gap-2">
                    <svg x-show="submitting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                    </svg>
                    <span x-text="submitting ? 'Creating…' : 'Create Role'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function rolesPage() {
    return {
        roles: [], permissions: [],
        selectedRole: null,
        loading: true, showModal: false, submitting: false,
        form: { name: '', description: '' },

        categories: [
            { key: 'dashboard',   label: 'Dashboard',            icon: '📊', color: '#1B3EB6', bg: '#eff6ff', prefix: ['dashboard'] },
            { key: 'sales',       label: 'Sales & Invoices',      icon: '🧾', color: '#059669', bg: '#f0fdf4', prefix: ['invoices','proforma'] },
            { key: 'procurement', label: 'Procurement',           icon: '🛒', color: '#7c3aed', bg: '#f5f3ff', prefix: ['purchase_orders','grns','supplier_invoices'] },
            { key: 'customers',   label: 'Customers & Suppliers', icon: '👥', color: '#0891b2', bg: '#ecfeff', prefix: ['customers','suppliers'] },
            { key: 'inventory',   label: 'Products & Inventory',  icon: '📦', color: '#d97706', bg: '#fffbeb', prefix: ['products','inventory'] },
            { key: 'cheques',     label: 'Cheques',               icon: '🏦', color: '#065f46', bg: '#f0fdf4', prefix: ['cheques'] },
            { key: 'expenses',    label: 'Expenses & Targets',    icon: '💸', color: '#dc2626', bg: '#fef2f2', prefix: ['expenses','targets'] },
            { key: 'accounting',  label: 'Accounting',            icon: '📒', color: '#4338ca', bg: '#eef2ff', prefix: ['accounting'] },
            { key: 'reports',     label: 'Reports',               icon: '📈', color: '#0369a1', bg: '#f0f9ff', prefix: ['reports'] },
            { key: 'collab',      label: 'Calendar, Tasks & Directory', icon: '📅', color: '#6b7280', bg: '#f9fafb', prefix: ['calendar','tasks','directory'] },
            { key: 'admin',       label: 'Access Control & Settings', icon: '🔐', color: '#1B3EB6', bg: '#fafafa', prefix: ['users','roles','activity_log','settings','branches'] },
        ],

        async init() {
            try {
                const [rr, pr] = await Promise.all([
                    apiFetch('/roles').then(r => r.json()),
                    apiFetch('/permissions').then(r => r.json()),
                ]);
                this.roles       = rr.data ?? rr ?? [];
                this.permissions = pr.data ?? pr ?? [];
            } catch (e) {
                toast('Failed to load roles', 'error');
            } finally {
                this.loading = false;
            }
        },

        catPermissions(cat) {
            return this.permissions.filter(p => cat.prefix.some(pfx => p.name.startsWith(pfx)));
        },

        catEnabledCount(cat) {
            return this.catPermissions(cat).filter(p => this.hasPermission(p.id)).length;
        },

        catPct(cat) {
            const total = this.catPermissions(cat).length;
            return total > 0 ? Math.round((this.catEnabledCount(cat) / total) * 100) : 0;
        },

        permCountFor(role) {
            if (!role) return 0;
            return (role.permissions ?? []).length;
        },

        hasPermission(permId) {
            if (!this.selectedRole) return false;
            return (this.selectedRole.permissions ?? []).some(p => (p.id ?? p) === permId);
        },

        async togglePermission(permId, checked) {
            if (!this.selectedRole) return;
            if (!this.selectedRole.permissions) this.selectedRole.permissions = [];
            if (checked) {
                const perm = this.permissions.find(p => p.id === permId);
                if (perm && !this.selectedRole.permissions.some(p => p.id === permId))
                    this.selectedRole.permissions.push(perm);
            } else {
                this.selectedRole.permissions = this.selectedRole.permissions.filter(p => (p.id ?? p) !== permId);
            }
            await this._syncPerms();
        },

        async toggleCategory(cat, enable) {
            const catPerms = this.catPermissions(cat);
            catPerms.forEach(perm => {
                if (enable) {
                    if (!this.selectedRole.permissions.some(p => p.id === perm.id))
                        this.selectedRole.permissions.push(perm);
                } else {
                    this.selectedRole.permissions = this.selectedRole.permissions.filter(p => (p.id ?? p) !== perm.id);
                }
            });
            await this._syncPerms();
        },

        async toggleAllPerms(enable) {
            if (enable) {
                this.selectedRole.permissions = [...this.permissions];
            } else {
                this.selectedRole.permissions = [];
            }
            await this._syncPerms();
        },

        async _syncPerms() {
            const permNames = (this.selectedRole.permissions ?? []).map(p => p.name ?? p);
            const r = await apiFetch('/roles/' + this.selectedRole.id, {
                method: 'PUT',
                body: JSON.stringify({ permissions: permNames }),
            });
            if (r && r.ok) {
                toast('Permissions updated', 'success');
                // Update the role in the roles list too
                const idx = this.roles.findIndex(r => r.id === this.selectedRole.id);
                if (idx !== -1) this.roles[idx] = { ...this.roles[idx], permissions: this.selectedRole.permissions };
            } else {
                toast('Failed to update permissions', 'error');
            }
        },

        async submitRole() {
            if (!this.form.name) { toast('Role name is required', 'error'); return; }
            this.submitting = true;
            try {
                const r = await apiFetch('/roles', { method: 'POST', body: JSON.stringify(this.form) });
                const data = await r.json();
                this.roles.push(data.data ?? data);
                toast('Role created', 'success');
                this.showModal = false;
                this.form = { name: '', description: '' };
            } catch (e) {
                toast(e.message ?? 'Failed to create role', 'error');
            } finally {
                this.submitting = false;
            }
        },

        permLabel(name) {
            const labels = {
                'dashboard.view':'View Dashboard',
                'invoices.view':'View Invoices','invoices.create':'Create Invoices',
                'invoices.edit':'Edit Invoices','invoices.delete':'Delete Invoices',
                'invoices.confirm':'Confirm Invoices','invoices.payment':'Record Payment',
                'invoices.cancel':'Cancel Invoices',
                'proforma.view':'View Proforma','proforma.create':'Create Proforma','proforma.convert':'Convert to Invoice',
                'purchase_orders.view':'View POs','purchase_orders.create':'Create POs',
                'purchase_orders.approve':'Approve POs','purchase_orders.payment':'PO Payments',
                'grns.view':'View GRNs','grns.create':'Create GRNs','grns.confirm':'Confirm GRNs',
                'supplier_invoices.view':'View Supplier Invoices','supplier_invoices.create':'Create Supplier Invoices',
                'supplier_invoices.payment':'Supplier Payments',
                'customers.view':'View Customers','customers.create':'Add Customers',
                'customers.edit':'Edit Customers','customers.delete':'Delete Customers',
                'suppliers.view':'View Suppliers','suppliers.create':'Add Suppliers',
                'suppliers.edit':'Edit Suppliers','suppliers.delete':'Delete Suppliers',
                'products.view':'View Products','products.create':'Add Products',
                'products.edit':'Edit Products','products.delete':'Delete Products',
                'products.categories.create':'Create Categories',
                'inventory.view':'View Inventory','inventory.transfers.create':'Create Transfers',
                'inventory.transfers.approve':'Approve Transfers','inventory.adjustments.create':'Create Adjustments',
                'inventory.adjustments.approve':'Approve Adjustments',
                'cheques.view':'View Cheques','cheques.create':'Create Cheques',
                'cheques.update':'Update Cheques','cheques.delete':'Delete Cheques',
                'cheques.process':'Process Cheques','cheques.details':'View Cheque Details',
                'cheques.history':'Cheque History',
                'expenses.view':'View Expenses','expenses.create':'Create Expenses',
                'expenses.approve':'Approve Expenses','expenses.delete':'Delete Expenses',
                'targets.view':'View Targets','targets.create':'Create Targets','targets.edit':'Edit Targets',
                'accounting.view':'View Accounting','accounting.journal.create':'Create Journal Entries',
                'accounting.reports':'Accounting Reports','accounting.settings':'Accounting Settings',
                'reports.view':'View Reports',
                'calendar.view':'View Calendar','calendar.create':'Create Events',
                'tasks.view':'View Tasks','tasks.create':'Create Tasks',
                'directory.view':'View Directory','directory.create':'Add Directory Entries',
                'users.view':'View Users','users.create':'Create Users',
                'users.edit':'Edit Users','users.delete':'Delete Users',
                'roles.view':'View Roles','roles.edit':'Edit Roles',
                'activity_log.view':'View Activity Log',
                'settings.view':'View Settings','settings.edit':'Edit Settings',
                'branches.view':'View Branches','branches.create':'Create Branches',
                'branches.edit':'Edit Branches','branches.delete':'Delete Branches',
            };
            return labels[name] ?? name.split('.').map(w => w.charAt(0).toUpperCase()+w.slice(1)).join(' ');
        },

        roleIcon(name) {
            const m = { super_admin:'👑', branch_manager:'🏢', sales_person:'💼',
                        accountant:'📊', inventory_manager:'📦', purchase_officer:'🛒',
                        hr_admin:'👥', viewer:'👁' };
            return m[name] ?? '👤';
        },

        roleColor(name) {
            const m = { super_admin:'#1B3EB6', branch_manager:'#065f46', sales_person:'#d97706',
                        accountant:'#7c3aed', inventory_manager:'#0891b2', purchase_officer:'#059669',
                        hr_admin:'#dc2626', viewer:'#6b7280' };
            return m[name] ?? '#1B3EB6';
        },

        roleStyle(name) {
            const c = this.roleColor(name);
            return `background:${c}18;color:${c}`;
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/access-control/roles.blade.php ENDPATH**/ ?>