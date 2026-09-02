<?php $__env->startSection('title', 'My HR'); ?>
<?php $__env->startSection('page-title', 'My HR'); ?>
<?php $__env->startSection('page-desc', 'Your profile, leave, attendance and payslips'); ?>

<?php $__env->startSection('content'); ?>
<style>
.my-tabs{display:flex;gap:4px;margin-bottom:18px;background:#f1f5f9;padding:4px;border-radius:12px;width:fit-content;flex-wrap:wrap}
.my-tab{padding:8px 18px;font-size:13px;font-weight:600;border-radius:9px;color:#64748b;cursor:pointer;transition:all .15s;background:transparent;border:none;font-family:inherit}
.my-tab.active{background:#fff;color:#1e293b;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.dark .my-tabs{background:#0f172a} .dark .my-tab{color:#94a3b8} .dark .my-tab.active{background:#1e293b;color:#e2e8f0}
</style>
<div x-data="myPortalPage()" x-init="init()">

    <div x-show="loading" class="flex items-center justify-center py-20">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" x-cloak>
        <div class="my-tabs">
            <button @click="tab = 'profile'" class="my-tab" :class="tab === 'profile' ? 'active' : ''">Profile</button>
            <button @click="tab = 'leave'; loadLeave()" class="my-tab" :class="tab === 'leave' ? 'active' : ''">Leave</button>
            <button @click="tab = 'attendance'; loadAttendance()" class="my-tab" :class="tab === 'attendance' ? 'active' : ''">Attendance</button>
            <button @click="tab = 'payslips'; loadPayslips()" class="my-tab" :class="tab === 'payslips' ? 'active' : ''">Payslips</button>
            <button @click="tab = 'documents'; loadDocuments()" class="my-tab" :class="tab === 'documents' ? 'active' : ''">Documents</button>
            <button @click="tab = 'checklist'; loadChecklist()" class="my-tab" :class="tab === 'checklist' ? 'active' : ''" x-show="checklistTasks.length || tab === 'checklist'">Onboarding</button>
        </div>

        <!-- Profile -->
        <div x-show="tab === 'profile'" class="max-w-2xl">
            <div class="card p-6">
                <div class="flex items-center gap-4 mb-5">
                    <img x-show="emp.photo_path" :src="API + '/my/employee/photo'" class="w-14 h-14 rounded-xl object-cover" />
                    <div x-show="!emp.photo_path" class="w-14 h-14 rounded-xl flex items-center justify-center font-bold text-white text-xl" style="background:linear-gradient(135deg,#0f4c81,#1a7abf)" x-text="(emp.first_name||'?').charAt(0).toUpperCase()"></div>
                    <div>
                        <div class="font-bold text-lg" x-text="[emp.first_name, emp.last_name].filter(Boolean).join(' ')"></div>
                        <div class="text-xs text-gray-400" x-text="[emp.designation?.name, emp.department?.name].filter(Boolean).join(' · ')"></div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="label text-xs">Phone</label><input type="tel" x-model="profileForm.phone" class="input" /></div>
                    <div><label class="label text-xs">Alternate Phone</label><input type="tel" x-model="profileForm.phone2" class="input" /></div>
                    <div class="col-span-2"><label class="label text-xs">Personal Email</label><input type="email" x-model="profileForm.personal_email" class="input" /></div>
                    <div class="col-span-2"><label class="label text-xs">Address</label><textarea x-model="profileForm.address" rows="2" class="input"></textarea></div>
                    <div><label class="label text-xs">City</label><input type="text" x-model="profileForm.city" class="input" /></div>
                    <div><label class="label text-xs">District</label><input type="text" x-model="profileForm.district" class="input" /></div>
                </div>
                <h4 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mt-5 mb-2">Emergency Contact</h4>
                <div class="grid grid-cols-3 gap-4">
                    <div><label class="label text-xs">Name</label><input type="text" x-model="profileForm.emergency_contact_name" class="input" /></div>
                    <div><label class="label text-xs">Relationship</label><input type="text" x-model="profileForm.emergency_contact_relationship" class="input" /></div>
                    <div><label class="label text-xs">Phone</label><input type="tel" x-model="profileForm.emergency_contact_phone" class="input" /></div>
                </div>
                <div class="flex justify-end mt-5">
                    <button @click="saveProfile()" class="btn-primary" :disabled="profileSaving" x-text="profileSaving ? 'Saving…' : 'Save Changes'"></button>
                </div>
            </div>
        </div>

        <!-- Leave -->
        <div x-show="tab === 'leave'">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
                <template x-for="b in leaveBalances" :key="b.leave_type_id">
                    <div class="card p-4">
                        <div class="text-xs text-gray-400 uppercase tracking-wide" x-text="b.leave_type?.name"></div>
                        <div class="text-xl font-bold mt-1" x-text="(b.remaining_days ?? (b.allocated_days - b.used_days)) + ' / ' + b.allocated_days"></div>
                    </div>
                </template>
            </div>
            <div class="flex justify-end mb-3">
                <button @click="openRequestLeave()" class="btn-primary text-sm">Request Leave</button>
            </div>
            <div class="card overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/40"><tr><th class="table-hd">Type</th><th class="table-hd">Dates</th><th class="table-hd text-center">Days</th><th class="table-hd">Status</th><th class="table-hd"></th></tr></thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                        <template x-for="r in myLeaveRequests" :key="r.id">
                            <tr>
                                <td class="table-td" x-text="r.leave_type?.name"></td>
                                <td class="table-td text-gray-500" x-text="fmtDate(r.start_date) + (r.start_date !== r.end_date ? ' – ' + fmtDate(r.end_date) : '')"></td>
                                <td class="table-td text-center" x-text="r.total_days"></td>
                                <td class="table-td"><span class="badge" :class="leaveStatusBadge(r.status)" x-text="r.status"></span></td>
                                <td class="table-td"><button x-show="['pending','approved'].includes(r.status)" @click="cancelLeave(r)" class="text-sm text-red-500 hover:text-red-700">Cancel</button></td>
                            </tr>
                        </template>
                        <tr x-show="!myLeaveRequests.length"><td colspan="5" class="text-center text-gray-400 py-10">No leave requests yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Attendance -->
        <div x-show="tab === 'attendance'">
            <div class="card overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/40"><tr><th class="table-hd">Date</th><th class="table-hd">Status</th><th class="table-hd">Time In</th><th class="table-hd">Time Out</th></tr></thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                        <template x-for="a in myAttendance" :key="a.id">
                            <tr>
                                <td class="table-td" x-text="fmtDate(a.date)"></td>
                                <td class="table-td capitalize" x-text="a.status.replace('_',' ')"></td>
                                <td class="table-td" x-text="a.time_in ?? '—'"></td>
                                <td class="table-td" x-text="a.time_out ?? '—'"></td>
                            </tr>
                        </template>
                        <tr x-show="!myAttendance.length"><td colspan="4" class="text-center text-gray-400 py-10">No attendance recorded yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payslips -->
        <div x-show="tab === 'payslips'">
            <div class="card overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/40"><tr><th class="table-hd">Period</th><th class="table-hd text-right">Net Pay</th><th class="table-hd">Status</th><th class="table-hd"></th></tr></thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                        <template x-for="p in myPayslips" :key="p.id">
                            <tr>
                                <td class="table-td" x-text="monthNames[p.payroll_run?.month] + ' ' + p.payroll_run?.year"></td>
                                <td class="table-td text-right tabular-nums" x-text="fmtMoney(p.net_pay)"></td>
                                <td class="table-td"><span class="badge" :class="p.payroll_run?.status === 'paid' ? 'badge-success' : 'badge-warning'" x-text="p.payroll_run?.status"></span></td>
                                <td class="table-td"><a :href="API + '/my/payslips/' + p.id + '/pdf'" target="_blank" class="text-indigo-600 hover:underline text-sm font-medium">PDF</a></td>
                            </tr>
                        </template>
                        <tr x-show="!myPayslips.length"><td colspan="4" class="text-center text-gray-400 py-10">No payslips yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Documents -->
        <div x-show="tab === 'documents'">
            <div class="card overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/40"><tr><th class="table-hd">Title</th><th class="table-hd">Type</th><th class="table-hd">Expiry</th><th class="table-hd"></th></tr></thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                        <template x-for="d in myDocuments" :key="d.id">
                            <tr>
                                <td class="table-td" x-text="d.title"></td>
                                <td class="table-td" x-text="d.document_type"></td>
                                <td class="table-td" x-text="fmtDate(d.expiry_date)"></td>
                                <td class="table-td"><a :href="API + '/my/documents/' + d.id + '/stream'" target="_blank" class="text-indigo-600 hover:underline text-sm font-medium">View</a></td>
                            </tr>
                        </template>
                        <tr x-show="!myDocuments.length"><td colspan="4" class="text-center text-gray-400 py-10">No documents on file.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Onboarding checklist -->
        <div x-show="tab === 'checklist'">
            <div class="card overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/40"><tr><th class="table-hd">Task</th><th class="table-hd">Due</th><th class="table-hd">Status</th></tr></thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                        <template x-for="t in checklistTasks" :key="t.id">
                            <tr>
                                <td class="table-td" :class="t.status === 'completed' ? 'line-through text-gray-400' : ''" x-text="t.title"></td>
                                <td class="table-td text-gray-500" x-text="fmtDate(t.due_date)"></td>
                                <td class="table-td"><span class="badge" :class="t.status === 'completed' ? 'badge-success' : 'badge-warning'" x-text="t.status"></span></td>
                            </tr>
                        </template>
                        <tr x-show="!checklistTasks.length"><td colspan="3" class="text-center text-gray-400 py-10">No tasks assigned.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Request Leave Modal -->
    <div x-show="showLeaveModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showLeaveModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Request Leave</h3>
                <button @click="showLeaveModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="submitLeaveRequest()" class="p-6 space-y-4">
                <div>
                    <label class="label">Leave Type <span class="text-red-500">*</span></label>
                    <select x-model="leaveForm.leave_type_id" class="input w-full" required>
                        <option value="">— Select —</option>
                        <template x-for="t in leaveTypes" :key="t.id"><option :value="t.id" x-text="t.name"></option></template>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Start Date <span class="text-red-500">*</span></label>
                        <input type="date" x-model="leaveForm.start_date" class="input w-full" required />
                    </div>
                    <div>
                        <label class="label">End Date <span class="text-red-500">*</span></label>
                        <input type="date" x-model="leaveForm.end_date" class="input w-full" :disabled="leaveForm.is_half_day" required />
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="leaveForm.is_half_day" @change="leaveForm.end_date = leaveForm.is_half_day ? leaveForm.start_date : leaveForm.end_date" class="rounded text-indigo-600" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Half day</span>
                </label>
                <div>
                    <label class="label">Reason</label>
                    <textarea x-model="leaveForm.reason" rows="2" class="input w-full resize-none"></textarea>
                </div>
                <div x-show="leaveError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="leaveError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showLeaveModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="leaveSaving" x-text="leaveSaving ? 'Submitting…' : 'Submit Request'"></button>
                </div>
            </form>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function myPortalPage() {
    return {
        loading: true,
        tab: 'profile',
        emp: {},
        profileForm: {},
        profileSaving: false,
        leaveBalances: [],
        myLeaveRequests: [],
        leaveTypes: [],
        myAttendance: [],
        myPayslips: [],
        myDocuments: [],
        checklistTasks: [],
        showLeaveModal: false,
        leaveForm: {},
        leaveSaving: false,
        leaveError: '',
        monthNames: ['','January','February','March','April','May','June','July','August','September','October','November','December'],

        leaveStatusBadge(status) {
            const map = { pending: 'badge-warning', approved: 'badge-success', rejected: 'badge-danger', cancelled: 'badge-gray' };
            return map[status] ?? 'badge-gray';
        },

        async init() {
            try {
                this.emp = await apiFetch('/my/employee').then(r => r.json());
                this.profileForm = {
                    phone: this.emp.phone ?? '', phone2: this.emp.phone2 ?? '', personal_email: this.emp.personal_email ?? '',
                    address: this.emp.address ?? '', city: this.emp.city ?? '', district: this.emp.district ?? '',
                    emergency_contact_name: this.emp.emergency_contact_name ?? '', emergency_contact_relationship: this.emp.emergency_contact_relationship ?? '',
                    emergency_contact_phone: this.emp.emergency_contact_phone ?? '',
                };
            } catch (e) {
                toast('Failed to load your profile', 'error');
            } finally {
                this.loading = false;
            }
        },

        async saveProfile() {
            this.profileSaving = true;
            try {
                this.emp = await apiFetch('/my/employee', { method: 'PUT', body: JSON.stringify(this.profileForm) }).then(r => r.json());
                toast('Profile updated.', 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to update profile', 'error');
            } finally {
                this.profileSaving = false;
            }
        },

        async loadLeave() {
            try {
                const [bal, reqs, types] = await Promise.all([
                    apiFetch('/my/leave-balances').then(r => r.json()),
                    apiFetch('/my/leave-requests').then(r => r.json()),
                    apiFetch('/hr/leave-types?active_only=1').then(r => r.json()).catch(() => []),
                ]);
                this.leaveBalances = bal;
                this.myLeaveRequests = reqs;
                this.leaveTypes = types;
            } catch (e) {
                toast('Failed to load leave data', 'error');
            }
        },

        openRequestLeave() {
            this.leaveForm = { leave_type_id: '', start_date: '', end_date: '', is_half_day: false, reason: '' };
            this.leaveError = '';
            this.showLeaveModal = true;
        },

        async submitLeaveRequest() {
            this.leaveSaving = true;
            this.leaveError = '';
            try {
                await apiFetch('/my/leave-requests', { method: 'POST', body: JSON.stringify(this.leaveForm) });
                toast('Leave request submitted.', 'success');
                this.showLeaveModal = false;
                await this.loadLeave();
            } catch (e) {
                this.leaveError = e.message ?? 'Failed to submit request.';
            } finally {
                this.leaveSaving = false;
            }
        },

        async cancelLeave(r) {
            if (!confirm('Cancel this leave request?')) return;
            try {
                await apiFetch('/my/leave-requests/' + r.id + '/cancel', { method: 'POST', body: JSON.stringify({}) });
                toast('Leave request cancelled.', 'success');
                await this.loadLeave();
            } catch (e) {
                toast(e.message ?? 'Failed to cancel', 'error');
            }
        },

        async loadAttendance() {
            try {
                this.myAttendance = await apiFetch('/my/attendance').then(r => r.json());
            } catch (e) {
                toast('Failed to load attendance', 'error');
            }
        },

        async loadPayslips() {
            try {
                this.myPayslips = await apiFetch('/my/payslips').then(r => r.json());
            } catch (e) {
                toast('Failed to load payslips', 'error');
            }
        },

        async loadDocuments() {
            try {
                this.myDocuments = await apiFetch('/my/documents').then(r => r.json());
            } catch (e) {
                toast('Failed to load documents', 'error');
            }
        },

        async loadChecklist() {
            try {
                this.checklistTasks = await apiFetch('/my/checklist-tasks').then(r => r.json());
            } catch (e) {
                toast('Failed to load checklist', 'error');
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\my\dashboard.blade.php ENDPATH**/ ?>