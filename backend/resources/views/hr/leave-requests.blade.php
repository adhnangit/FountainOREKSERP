@extends('layouts.app')
@section('title', 'Leave Requests')
@section('page-title', 'Leave Requests')
@section('page-desc', 'Review, approve and track staff leave')
@php $sec = 'hr'; @endphp

@section('content')
<div x-data="leaveRequestsPage()" x-init="init()">

    <div class="flex flex-wrap items-center gap-2 mb-4">
        <select x-model="filterStatus" @change="load()" class="input w-auto">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="cancelled">Cancelled</option>
        </select>
        <div class="flex-1"></div>
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Leave Request
        </button>
    </div>

    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/40">
                <tr>
                    <th class="table-hd">Employee</th>
                    <th class="table-hd">Type</th>
                    <th class="table-hd">Dates</th>
                    <th class="table-hd text-center">Days</th>
                    <th class="table-hd">Status</th>
                    <th class="table-hd">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                <template x-for="r in requests" :key="r.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                        <td class="table-td">
                            <div class="font-medium text-gray-900 dark:text-gray-100" x-text="[r.employee?.first_name, r.employee?.last_name].filter(Boolean).join(' ')"></div>
                            <div class="text-xs text-gray-400" x-text="r.employee?.employee_code"></div>
                        </td>
                        <td class="table-td" x-text="r.leave_type?.name"></td>
                        <td class="table-td text-gray-500">
                            <span x-text="fmtDate(r.start_date)"></span>
                            <span x-show="r.start_date !== r.end_date"> – <span x-text="fmtDate(r.end_date)"></span></span>
                            <span x-show="r.is_half_day" class="text-xs text-amber-600"> (half day)</span>
                        </td>
                        <td class="table-td text-center tabular-nums" x-text="r.total_days"></td>
                        <td class="table-td">
                            <span class="badge" :class="statusBadge(r.status)" x-text="r.status"></span>
                        </td>
                        <td class="table-td">
                            <div class="flex items-center gap-3">
                                <button x-show="r.status === 'pending'" @click="decide(r, 'approve')" class="text-sm font-medium text-green-600 hover:text-green-800">Approve</button>
                                <button x-show="r.status === 'pending'" @click="decide(r, 'reject')" class="text-sm font-medium text-red-500 hover:text-red-700">Reject</button>
                                <button x-show="['pending','approved'].includes(r.status)" @click="decide(r, 'cancel')" class="text-sm font-medium text-gray-500 hover:text-gray-700">Cancel</button>
                                <button @click="viewNotes(r)" x-show="r.decision_notes || r.reason" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Details</button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && requests.length === 0">
                    <td colspan="6" class="text-center text-gray-400 py-16">No leave requests found.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- New Request Modal -->
    <div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showCreate = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">New Leave Request</h3>
                <button @click="showCreate = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="submitCreate()" class="p-6 space-y-4">
                <div>
                    <label class="label">Employee <span class="text-red-500">*</span></label>
                    <select x-model="form.employee_id" class="input w-full" required>
                        <option value="">— Select —</option>
                        <template x-for="e in employees" :key="e.id"><option :value="e.id" x-text="[e.first_name, e.last_name].filter(Boolean).join(' ')"></option></template>
                    </select>
                </div>
                <div>
                    <label class="label">Leave Type <span class="text-red-500">*</span></label>
                    <select x-model="form.leave_type_id" class="input w-full" required>
                        <option value="">— Select —</option>
                        <template x-for="t in leaveTypes" :key="t.id"><option :value="t.id" x-text="t.name"></option></template>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Start Date <span class="text-red-500">*</span></label>
                        <input type="date" x-model="form.start_date" class="input w-full" required />
                    </div>
                    <div>
                        <label class="label">End Date <span class="text-red-500">*</span></label>
                        <input type="date" x-model="form.end_date" class="input w-full" :disabled="form.is_half_day" required />
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="form.is_half_day" @change="form.end_date = form.is_half_day ? form.start_date : form.end_date" class="rounded text-indigo-600" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Half day</span>
                </label>
                <div x-show="form.is_half_day">
                    <label class="label">Which half?</label>
                    <select x-model="form.half_day_period" class="input w-full">
                        <option value="first_half">First half</option>
                        <option value="second_half">Second half</option>
                    </select>
                </div>
                <div>
                    <label class="label">Reason</label>
                    <textarea x-model="form.reason" rows="2" class="input w-full resize-none"></textarea>
                </div>
                <div x-show="createError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="createError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showCreate = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="creating" x-text="creating ? 'Submitting…' : 'Submit Request'"></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Decision Modal (approve/reject/cancel notes) -->
    <div x-show="showDecision" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showDecision = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 capitalize" x-text="decisionAction + ' Leave Request'"></h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="label">Notes <span x-show="decisionAction === 'reject'" class="text-red-500">*</span></label>
                    <textarea x-model="decisionNotes" rows="3" class="input w-full resize-none" placeholder="Optional"></textarea>
                </div>
                <div x-show="decisionError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="decisionError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showDecision = false" class="btn-secondary">Cancel</button>
                    <button @click="confirmDecision()" class="btn-primary" :disabled="deciding" x-text="deciding ? 'Working…' : 'Confirm'"></button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Notes Modal -->
    <div x-show="showNotes" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showNotes = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-3">
            <div x-show="notesRequest?.reason">
                <div class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-1">Reason</div>
                <div class="text-sm text-gray-700 dark:text-gray-300" x-text="notesRequest?.reason"></div>
            </div>
            <div x-show="notesRequest?.decision_notes">
                <div class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-1">Decision Notes</div>
                <div class="text-sm text-gray-700 dark:text-gray-300" x-text="notesRequest?.decision_notes"></div>
            </div>
            <div class="flex justify-end pt-2">
                <button @click="showNotes = false" class="btn-secondary">Close</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function leaveRequestsPage() {
    return {
        requests: [],
        employees: [],
        leaveTypes: [],
        filterStatus: '',
        loading: true,
        showCreate: false,
        creating: false,
        createError: '',
        form: {},
        showDecision: false,
        decisionAction: '',
        decisionRequest: null,
        decisionNotes: '',
        decisionError: '',
        deciding: false,
        showNotes: false,
        notesRequest: null,

        statusBadge(status) {
            const map = { pending: 'badge-warning', approved: 'badge-success', rejected: 'badge-danger', cancelled: 'badge-gray' };
            return map[status] ?? 'badge-gray';
        },

        async init() {
            try {
                const [ed, td] = await Promise.all([
                    apiFetch('/hr/employees?per_page=500').then(r => r.json()),
                    apiFetch('/hr/leave-types?active_only=1').then(r => r.json()),
                ]);
                this.employees = ed.data ?? ed ?? [];
                this.leaveTypes = td ?? [];
            } catch (_) {}
            await this.load();
        },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.filterStatus) params.set('status', this.filterStatus);
                const data = await apiFetch('/hr/leave-requests?' + params.toString()).then(r => r.json());
                this.requests = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load leave requests', 'error');
            } finally {
                this.loading = false;
            }
        },

        openCreate() {
            this.form = { employee_id: '', leave_type_id: '', start_date: '', end_date: '', is_half_day: false, half_day_period: 'first_half', reason: '' };
            this.createError = '';
            this.showCreate = true;
        },

        async submitCreate() {
            this.creating = true;
            this.createError = '';
            try {
                await apiFetch('/hr/leave-requests', { method: 'POST', body: JSON.stringify(this.form) });
                toast('Leave request submitted.', 'success');
                this.showCreate = false;
                await this.load();
            } catch (e) {
                this.createError = e.message ?? 'Failed to submit request.';
            } finally {
                this.creating = false;
            }
        },

        decide(request, action) {
            this.decisionRequest = request;
            this.decisionAction = action;
            this.decisionNotes = '';
            this.decisionError = '';
            this.showDecision = true;
        },

        async confirmDecision() {
            if (this.decisionAction === 'reject' && !this.decisionNotes) {
                this.decisionError = 'Please provide a reason for rejecting this request.';
                return;
            }
            this.deciding = true;
            this.decisionError = '';
            try {
                await apiFetch(`/hr/leave-requests/${this.decisionRequest.id}/${this.decisionAction}`, {
                    method: 'POST',
                    body: JSON.stringify({ decision_notes: this.decisionNotes || null }),
                });
                toast('Leave request updated.', 'success');
                this.showDecision = false;
                await this.load();
            } catch (e) {
                this.decisionError = e.message ?? 'Action failed.';
            } finally {
                this.deciding = false;
            }
        },

        viewNotes(r) {
            this.notesRequest = r;
            this.showNotes = true;
        },
    };
}
</script>
@endpush
