<?php $__env->startSection('title', 'User Management'); ?>
<?php $__env->startSection('page-title', 'User Management'); ?>
<?php $__env->startSection('page-desc', 'Manage system users and their access'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="usersPage()" x-init="init()">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <input x-model="search" type="text" placeholder="Search name or email…" class="input w-full sm:w-72" />
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add User
        </button>
    </div>

    <div class="card p-0 overflow-hidden">
        <div x-show="loading" class="flex items-center justify-center py-16">
            <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
        </div>
        <div x-show="!loading" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-hd">Name</th>
                        <th class="table-hd">Email</th>
                        <th class="table-hd">Role</th>
                        <th class="table-hd">Branch</th>
                        <th class="table-hd">Status</th>
                        <th class="table-hd">Last Login</th>
                        <th class="table-hd">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="user in filtered" :key="user.id">
                        <tr class="hover:bg-gray-50">
                            <td class="table-td">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs shrink-0"
                                        x-text="(user.name ?? '?').charAt(0).toUpperCase()"></div>
                                    <span class="font-medium text-gray-900" x-text="user.name ?? '—'"></span>
                                </div>
                            </td>
                            <td class="table-td" x-text="user.email ?? '—'"></td>
                            <td class="table-td">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700"
                                      x-text="user.roles?.[0]?.name ?? '—'"></span>
                            </td>
                            <td class="table-td" x-text="user.default_branch?.name ?? user.branches?.[0]?.name ?? '—'"></td>
                            <td class="table-td">
                                <span :class="(user.is_active ?? true) ? 'badge-success' : 'badge-gray'"
                                    x-text="(user.is_active ?? true) ? 'Active' : 'Inactive'"></span>
                            </td>
                            <td class="table-td text-gray-500 text-xs" x-text="user.last_login_at ? fmtDate(user.last_login_at) : 'Never'"></td>
                            <td class="table-td">
                                <div class="flex gap-3">
                                    <button @click="openEdit(user)" class="text-indigo-600 hover:underline text-sm font-medium">Edit</button>
                                    <button @click="resetPassword(user)" class="text-yellow-600 hover:underline text-sm font-medium">Reset PW</button>
                                    <button @click="toggleActive(user)"
                                        class="text-sm font-medium"
                                        :class="(user.is_active ?? true) ? 'text-red-500 hover:underline' : 'text-green-600 hover:underline'"
                                        x-text="(user.is_active ?? true) ? 'Deactivate' : 'Activate'"></button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && filtered.length === 0">
                        <td colspan="7" class="table-td text-center text-gray-400 py-10">No users found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create / Edit User Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative card p-6 w-full max-w-lg z-10 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-semibold text-gray-900 mb-4" x-text="editingUser ? 'Edit User' : 'Add User'"></h3>
            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="label">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.name" class="input" placeholder="Full name" />
                    </div>
                    <div>
                        <label class="label">Email <span class="text-red-500">*</span></label>
                        <input type="email" x-model="form.email" class="input" placeholder="user@company.com"
                               :disabled="!!editingUser" :class="editingUser ? 'bg-gray-50 text-gray-400' : ''" />
                    </div>
                    <div x-show="!editingUser">
                        <label class="label">Password <span class="text-red-500">*</span></label>
                        <input type="password" x-model="form.password" class="input" placeholder="Min. 8 characters" />
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input type="text" x-model="form.phone" class="input" placeholder="+94 77 123 4567" />
                    </div>
                    <div>
                        <label class="label">Designation</label>
                        <input type="text" x-model="form.designation" class="input" placeholder="e.g. Sales Executive" />
                    </div>
                    <div>
                        <label class="label">Department</label>
                        <input type="text" x-model="form.department" class="input" placeholder="e.g. Sales" />
                    </div>
                    <div>
                        <label class="label">Employee ID</label>
                        <input type="text" x-model="form.employee_id" class="input" placeholder="EMP-001"
                               :disabled="!!editingUser" :class="editingUser ? 'bg-gray-50 text-gray-400' : ''" />
                    </div>
                    <div>
                        <label class="label">Role <span class="text-red-500">*</span></label>
                        <select x-model="form.role" class="input">
                            <option value="">Select role…</option>
                            <template x-for="r in roles" :key="r.id">
                                <option :value="r.name" x-text="r.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="label">Default Branch</label>
                        <select x-model="form.default_branch_id" class="input">
                            <option value="">Select branch…</option>
                            <template x-for="b in branches" :key="b.id">
                                <option :value="b.id" x-text="b.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="label">Branch Access</label>
                        <div class="flex flex-wrap gap-2 mt-1">
                            <template x-for="b in branches" :key="b.id">
                                <label class="flex items-center gap-1.5 text-sm cursor-pointer">
                                    <input type="checkbox" class="rounded border-gray-300 text-indigo-600"
                                           :value="b.id"
                                           :checked="form.branch_ids.includes(b.id)"
                                           @change="toggleBranch(b.id, $event.target.checked)" />
                                    <span x-text="b.name"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" x-model="form.is_active" id="user_active" class="rounded border-gray-300" />
                        <label for="user_active" class="text-sm text-gray-700">Active</label>
                    </div>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-5">
                <button @click="showModal = false" class="btn-secondary">Cancel</button>
                <button @click="submit()" :disabled="submitting" class="btn-primary">
                    <span x-text="submitting ? 'Saving…' : (editingUser ? 'Save Changes' : 'Create User')"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div x-show="showResetModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showResetModal = false"></div>
        <div class="relative card p-6 w-full max-w-sm z-10">
            <h3 class="text-base font-semibold text-gray-900 mb-1">Reset Password</h3>
            <p class="text-sm text-gray-500 mb-4" x-text="'Set a new password for ' + (resetTarget?.name ?? '')"></p>
            <div class="space-y-3">
                <input type="password" x-model="newPassword" class="input" placeholder="New password (min. 8 chars)" />
            </div>
            <div class="flex justify-end gap-3 mt-4">
                <button @click="showResetModal = false" class="btn-secondary">Cancel</button>
                <button @click="submitReset()" :disabled="submitting" class="btn-primary">
                    <span x-text="submitting ? 'Saving…' : 'Reset Password'"></span>
                </button>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function usersPage() {
    return {
        items: [],
        roles: [],
        branches: [],
        loading: true,
        showModal: false,
        showResetModal: false,
        submitting: false,
        editingUser: null,
        resetTarget: null,
        newPassword: '',
        search: '',
        form: {
            name: '', email: '', password: '', phone: '', designation: '',
            department: '', employee_id: '', role: '', default_branch_id: '',
            branch_ids: [], is_active: true
        },

        get filtered() {
            const q = this.search.toLowerCase();
            if (!q) return this.items;
            return this.items.filter(u =>
                (u.name ?? '').toLowerCase().includes(q) ||
                (u.email ?? '').toLowerCase().includes(q)
            );
        },

        async init() {
            try {
                const [ur, br, rr] = await Promise.all([
                    apiFetch('/users').then(r => r.json()),
                    apiFetch('/branches').then(r => r.json()).catch(() => ({ data: [] })),
                    apiFetch('/roles').then(r => r.json()).catch(() => []),
                ]);
                this.items    = ur.data ?? ur ?? [];
                this.branches = br.data ?? br ?? [];
                this.roles    = rr.data ?? rr ?? [];
            } catch (e) {
                toast('Failed to load users', 'error');
            } finally {
                this.loading = false;
            }
        },

        openCreate() {
            this.editingUser = null;
            this.form = {
                name: '', email: '', password: '', phone: '', designation: '',
                department: '', employee_id: '', role: '', default_branch_id: '',
                branch_ids: [], is_active: true
            };
            this.showModal = true;
        },

        openEdit(user) {
            this.editingUser = user;
            this.form = {
                name:              user.name ?? '',
                email:             user.email ?? '',
                password:          '',
                phone:             user.phone ?? '',
                designation:       user.designation ?? '',
                department:        user.department ?? '',
                employee_id:       user.employee_id ?? '',
                role:              user.roles?.[0]?.name ?? '',
                default_branch_id: user.default_branch_id ?? '',
                branch_ids:        (user.branches ?? []).map(b => b.id),
                is_active:         user.is_active ?? true,
            };
            this.showModal = true;
        },

        toggleBranch(id, checked) {
            if (checked) {
                if (!this.form.branch_ids.includes(id)) this.form.branch_ids.push(id);
            } else {
                this.form.branch_ids = this.form.branch_ids.filter(x => x !== id);
            }
        },

        async submit() {
            if (!this.form.name || !this.form.email) {
                toast('Name and email are required', 'error'); return;
            }
            if (!this.editingUser && !this.form.password) {
                toast('Password is required', 'error'); return;
            }
            if (!this.form.role) {
                toast('Please select a role', 'error'); return;
            }
            this.submitting = true;
            try {
                const payload = {
                    name:              this.form.name,
                    phone:             this.form.phone || null,
                    designation:       this.form.designation || null,
                    department:        this.form.department || null,
                    role:              this.form.role,
                    default_branch_id: this.form.default_branch_id || null,
                    branch_ids:        this.form.branch_ids,
                    is_active:         this.form.is_active,
                };

                let r, d;
                if (this.editingUser) {
                    r = await apiFetch('/users/' + this.editingUser.id, {
                        method: 'PUT',
                        body: JSON.stringify(payload),
                    });
                    d = await r.json();
                    if (r.ok) {
                        const idx = this.items.findIndex(u => u.id === this.editingUser.id);
                        if (idx !== -1) this.items[idx] = d.data ?? d;
                        toast('User updated');
                    } else {
                        toast(d.message ?? 'Failed to update user', 'error'); return;
                    }
                } else {
                    payload.email       = this.form.email;
                    payload.password    = this.form.password;
                    payload.employee_id = this.form.employee_id || null;
                    r = await apiFetch('/users', {
                        method: 'POST',
                        body: JSON.stringify(payload),
                    });
                    d = await r.json();
                    if (r.ok) {
                        this.items.unshift(d.data ?? d);
                        toast('User created');
                    } else {
                        toast(d.message ?? 'Failed to create user', 'error'); return;
                    }
                }
                this.showModal = false;
            } catch (e) {
                toast(e.message ?? 'An error occurred', 'error');
            } finally {
                this.submitting = false;
            }
        },

        async toggleActive(user) {
            const newState = !(user.is_active ?? true);
            const r = await apiFetch('/users/' + user.id, {
                method: 'PUT',
                body: JSON.stringify({ is_active: newState }),
            });
            if (r?.ok) {
                user.is_active = newState;
                toast(newState ? 'User activated' : 'User deactivated');
            } else {
                toast('Failed to update user', 'error');
            }
        },

        resetPassword(user) {
            this.resetTarget  = user;
            this.newPassword  = '';
            this.showResetModal = true;
        },

        async submitReset() {
            if (!this.newPassword || this.newPassword.length < 8) {
                toast('Password must be at least 8 characters', 'error'); return;
            }
            this.submitting = true;
            try {
                const r = await apiFetch('/users/' + this.resetTarget.id + '/reset-password', {
                    method: 'POST',
                    body: JSON.stringify({ password: this.newPassword }),
                });
                if (r?.ok) {
                    toast('Password reset successfully');
                    this.showResetModal = false;
                } else {
                    const d = await r.json();
                    toast(d.message ?? 'Failed', 'error');
                }
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/access-control/users.blade.php ENDPATH**/ ?>