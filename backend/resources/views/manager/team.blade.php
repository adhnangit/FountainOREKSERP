@extends('layouts.app')
@section('title', 'My Team')
@section('page-title', 'My Team')
@section('page-desc', 'Your direct reports — attendance and leave')

@section('content')
<style>
.mt-tabs{display:flex;gap:4px;margin-bottom:18px;background:#f1f5f9;padding:4px;border-radius:12px;width:fit-content}
.mt-tab{padding:8px 18px;font-size:13px;font-weight:600;border-radius:9px;color:#64748b;cursor:pointer;transition:all .15s;background:transparent;border:none;font-family:inherit}
.mt-tab.active{background:#fff;color:#1e293b;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.dark .mt-tabs{background:#0f172a} .dark .mt-tab{color:#94a3b8} .dark .mt-tab.active{background:#1e293b;color:#e2e8f0}
</style>
<div x-data="managerTeamPage()" x-init="init()">

    <div x-show="loading" class="flex items-center justify-center py-20">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" x-cloak>
        <div class="mt-tabs">
            <button @click="tab = 'roster'" class="mt-tab" :class="tab === 'roster' ? 'active' : ''">Roster</button>
            <button @click="tab = 'attendance'; loadAttendance()" class="mt-tab" :class="tab === 'attendance' ? 'active' : ''">Attendance</button>
            <button @click="tab = 'leave'; loadLeave()" class="mt-tab" :class="tab === 'leave' ? 'active' : ''">Leave Requests <span x-show="pendingCount" x-text="'(' + pendingCount + ')'"></span></button>
        </div>

        <!-- Roster -->
        <div x-show="tab === 'roster'" class="card overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800/40"><tr><th class="table-hd">Name</th><th class="table-hd">Designation</th><th class="table-hd">Department</th><th class="table-hd">Branch</th><th class="table-hd"></th></tr></thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                    <template x-for="e in team" :key="e.id">
                        <tr>
                            <td class="table-td font-medium" x-text="[e.first_name, e.last_name].filter(Boolean).join(' ')"></td>
                            <td class="table-td text-gray-500" x-text="e.designation?.name ?? '—'"></td>
                            <td class="table-td text-gray-500" x-text="e.department?.name ?? '—'"></td>
                            <td class="table-td text-gray-500" x-text="e.branch?.name ?? '—'"></td>
                            <td class="table-td"><a :href="BASE + '/hr/employees/' + e.id" class="text-indigo-600 hover:underline text-sm">View</a></td>
                        </tr>
                    </template>
                    <tr x-show="!team.length"><td colspan="5" class="text-center text-gray-400 py-16">No one reports to you yet.</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Attendance -->
        <div x-show="tab === 'attendance'">
            <div class="flex items-center gap-2 mb-4">
                <input type="date" x-model="attDate" @change="loadAttendance()" class="input w-auto" />
                <button @click="markAllPresent()" class="btn-secondary">Mark All Unmarked Present</button>
                <div class="flex-1"></div>
                <button @click="saveAttendance()" class="btn-primary" :disabled="attSaving" x-text="attSaving ? 'Saving…' : 'Save Attendance'"></button>
            </div>
            <div class="card overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/40"><tr><th class="table-hd">Employee</th><th class="table-hd">Status</th><th class="table-hd">Notes</th></tr></thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                        <template x-for="row in attRows" :key="row.employee_id">
                            <tr>
                                <td class="table-td font-medium" x-text="row.name"></td>
                                <td class="table-td">
                                    <select x-model="row.status" class="input w-auto">
                                        <option value="">— Unmarked —</option>
                                        <option value="present">Present</option>
                                        <option value="absent">Absent</option>
                                        <option value="half_day">Half Day</option>
                                        <option value="late">Late</option>
                                        <option value="on_leave">On Leave</option>
                                    </select>
                                </td>
                                <td class="table-td"><input type="text" x-model="row.notes" class="input w-full" /></td>
                            </tr>
                        </template>
                        <tr x-show="!attRows.length"><td colspan="3" class="text-center text-gray-400 py-16">No team members found.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Leave Requests -->
        <div x-show="tab === 'leave'">
            <div class="card overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/40"><tr><th class="table-hd">Employee</th><th class="table-hd">Type</th><th class="table-hd">Dates</th><th class="table-hd">Status</th><th class="table-hd"></th></tr></thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                        <template x-for="r in teamLeave" :key="r.id">
                            <tr>
                                <td class="table-td font-medium" x-text="[r.employee?.first_name, r.employee?.last_name].filter(Boolean).join(' ')"></td>
                                <td class="table-td" x-text="r.leave_type?.name"></td>
                                <td class="table-td text-gray-500" x-text="fmtDate(r.start_date) + (r.start_date !== r.end_date ? ' – ' + fmtDate(r.end_date) : '')"></td>
                                <td class="table-td"><span class="badge" :class="leaveStatusBadge(r.status)" x-text="r.status"></span></td>
                                <td class="table-td">
                                    <div class="flex items-center gap-3" x-show="r.status === 'pending'">
                                        <button @click="decide(r, 'approve')" class="text-sm font-medium text-green-600 hover:text-green-800">Approve</button>
                                        <button @click="decide(r, 'reject')" class="text-sm font-medium text-red-500 hover:text-red-700">Reject</button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="!teamLeave.length"><td colspan="5" class="text-center text-gray-400 py-16">No leave requests from your team.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function managerTeamPage() {
    return {
        loading: true,
        tab: 'roster',
        team: [],
        attDate: new Date().toISOString().slice(0, 10),
        attRows: [],
        attSaving: false,
        teamLeave: [],

        get pendingCount() { return this.teamLeave.filter(r => r.status === 'pending').length; },

        leaveStatusBadge(status) {
            const map = { pending: 'badge-warning', approved: 'badge-success', rejected: 'badge-danger', cancelled: 'badge-gray' };
            return map[status] ?? 'badge-gray';
        },

        async init() {
            try {
                this.team = await apiFetch('/manager/team').then(r => r.json());
            } catch (e) {
                toast('Failed to load your team', 'error');
            } finally {
                this.loading = false;
            }
        },

        async loadAttendance() {
            try {
                const data = await apiFetch('/manager/team/attendance?date=' + this.attDate).then(r => r.json());
                this.attRows = data.map(r => ({
                    employee_id: r.employee_id, name: r.name,
                    status: r.attendance?.status ?? '', notes: r.attendance?.notes ?? '',
                }));
            } catch (e) {
                toast('Failed to load attendance', 'error');
            }
        },

        markAllPresent() {
            this.attRows.forEach(r => { if (!r.status) r.status = 'present'; });
        },

        async saveAttendance() {
            const records = this.attRows.filter(r => r.status).map(r => ({ employee_id: r.employee_id, status: r.status, notes: r.notes || null }));
            if (!records.length) { toast('Mark at least one team member first', 'error'); return; }
            this.attSaving = true;
            try {
                const res = await apiFetch('/manager/team/attendance/bulk-mark', { method: 'POST', body: JSON.stringify({ date: this.attDate, records }) }).then(r => r.json());
                toast(`Attendance saved for ${res.marked} team member(s).`, 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to save attendance', 'error');
            } finally {
                this.attSaving = false;
            }
        },

        async loadLeave() {
            try {
                this.teamLeave = await apiFetch('/manager/team/leave-requests').then(r => r.json());
            } catch (e) {
                toast('Failed to load team leave requests', 'error');
            }
        },

        async decide(r, action) {
            if (action === 'reject' && !confirm('Reject this leave request?')) return;
            try {
                await apiFetch(`/manager/team/leave-requests/${r.id}/${action}`, { method: 'POST', body: JSON.stringify({}) });
                toast('Leave request updated.', 'success');
                await this.loadLeave();
            } catch (e) {
                toast(e.message ?? 'Action failed', 'error');
            }
        },
    };
}
</script>
@endpush
