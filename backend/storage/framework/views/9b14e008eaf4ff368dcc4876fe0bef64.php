<?php $__env->startSection('title', 'Attendance'); ?>
<?php $__env->startSection('page-title', 'Attendance'); ?>
<?php $__env->startSection('page-desc', 'Mark daily attendance and review monthly summaries'); ?>
<?php $sec = 'hr'; ?>

<?php $__env->startSection('content'); ?>
<div x-data="attendancePage()" x-init="init()">

    <div class="ed-tabs" style="display:flex;gap:4px;margin-bottom:18px;background:#f1f5f9;padding:4px;border-radius:12px;width:fit-content">
        <button @click="tab = 'mark'; loadMark()" class="ed-tab" :class="tab === 'mark' ? 'active' : ''"
                style="padding:8px 18px;font-size:13px;font-weight:600;border-radius:9px;color:#64748b;cursor:pointer;transition:all .15s;background:transparent;border:none;font-family:inherit"
                :style="tab === 'mark' ? 'background:#fff;color:#1e293b;box-shadow:0 1px 3px rgba(0,0,0,.08)' : ''">Mark Attendance</button>
        <button @click="tab = 'summary'; loadSummary()" class="ed-tab" :class="tab === 'summary' ? 'active' : ''"
                style="padding:8px 18px;font-size:13px;font-weight:600;border-radius:9px;color:#64748b;cursor:pointer;transition:all .15s;background:transparent;border:none;font-family:inherit"
                :style="tab === 'summary' ? 'background:#fff;color:#1e293b;box-shadow:0 1px 3px rgba(0,0,0,.08)' : ''">Monthly Summary</button>
    </div>

    <!-- ══ MARK ATTENDANCE ══ -->
    <div x-show="tab === 'mark'">
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <input type="date" x-model="markDate" @change="loadMark()" class="input w-auto" />
            <select x-model="filterDept" @change="loadMark()" class="input w-auto">
                <option value="">All Departments</option>
                <template x-for="d in flatDepartments" :key="d.id"><option :value="d.id" x-text="d.name"></option></template>
            </select>
            <button @click="markAllPresent()" class="btn-secondary">Mark All Unmarked Present</button>
            <div class="flex-1"></div>
            <button @click="saveMark()" class="btn-primary" :disabled="markSaving" x-text="markSaving ? 'Saving…' : 'Save Attendance'"></button>
        </div>

        <div x-show="markLoading" class="flex items-center justify-center py-16">
            <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
        </div>

        <div x-show="!markLoading" class="card overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800/40">
                    <tr>
                        <th class="table-hd">Employee</th>
                        <th class="table-hd">Status</th>
                        <th class="table-hd">Time In</th>
                        <th class="table-hd">Time Out</th>
                        <th class="table-hd">Notes</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                    <template x-for="row in markRows" :key="row.employee_id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                            <td class="table-td">
                                <div class="font-medium text-gray-900 dark:text-gray-100" x-text="row.name"></div>
                                <div class="text-xs text-gray-400" x-text="[row.department, row.designation].filter(Boolean).join(' · ')"></div>
                            </td>
                            <td class="table-td">
                                <select x-model="row.status" class="input w-auto">
                                    <option value="">— Unmarked —</option>
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="half_day">Half Day</option>
                                    <option value="late">Late</option>
                                    <option value="on_leave">On Leave</option>
                                    <option value="holiday">Holiday</option>
                                    <option value="weekend">Weekend</option>
                                </select>
                            </td>
                            <td class="table-td"><input type="time" x-model="row.time_in" class="input w-auto" /></td>
                            <td class="table-td"><input type="time" x-model="row.time_out" class="input w-auto" /></td>
                            <td class="table-td"><input type="text" x-model="row.notes" class="input w-full" placeholder="Optional" /></td>
                        </tr>
                    </template>
                    <tr x-show="!markLoading && markRows.length === 0">
                        <td colspan="5" class="text-center text-gray-400 py-16">No active employees found for this filter.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ══ MONTHLY SUMMARY ══ -->
    <div x-show="tab === 'summary'">
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <select x-model.number="summaryMonth" @change="loadSummary()" class="input w-auto">
                <template x-for="(m, i) in monthNames" :key="i"><option :value="i + 1" x-text="m"></option></template>
            </select>
            <select x-model.number="summaryYear" @change="loadSummary()" class="input w-auto">
                <template x-for="y in yearOptions" :key="y"><option :value="y" x-text="y"></option></template>
            </select>
            <select x-model="filterDept" @change="loadSummary()" class="input w-auto">
                <option value="">All Departments</option>
                <template x-for="d in flatDepartments" :key="d.id"><option :value="d.id" x-text="d.name"></option></template>
            </select>
        </div>

        <div x-show="summaryLoading" class="flex items-center justify-center py-16">
            <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
        </div>

        <div x-show="!summaryLoading" class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/40">
                        <tr>
                            <th class="table-hd">Employee</th>
                            <th class="table-hd text-center">Present</th>
                            <th class="table-hd text-center">Absent</th>
                            <th class="table-hd text-center">Half Day</th>
                            <th class="table-hd text-center">Late</th>
                            <th class="table-hd text-center">On Leave</th>
                            <th class="table-hd text-center">Holiday</th>
                            <th class="table-hd text-right">Work Hours</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                        <template x-for="row in summaryRows" :key="row.employee_id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                                <td class="table-td font-medium text-gray-900 dark:text-gray-100">
                                    <a :href="BASE + '/hr/employees/' + row.employee_id" class="hover:underline" x-text="row.name"></a>
                                </td>
                                <td class="table-td text-center" x-text="row.counts.present"></td>
                                <td class="table-td text-center" :class="row.counts.absent > 0 ? 'text-red-600 font-semibold' : ''" x-text="row.counts.absent"></td>
                                <td class="table-td text-center" x-text="row.counts.half_day"></td>
                                <td class="table-td text-center" :class="row.counts.late > 0 ? 'text-amber-600 font-semibold' : ''" x-text="row.counts.late"></td>
                                <td class="table-td text-center" x-text="row.counts.on_leave"></td>
                                <td class="table-td text-center" x-text="row.counts.holiday"></td>
                                <td class="table-td text-right tabular-nums" x-text="row.total_work_hours"></td>
                            </tr>
                        </template>
                        <tr x-show="!summaryLoading && summaryRows.length === 0">
                            <td colspan="8" class="text-center text-gray-400 py-16">No active employees found for this filter.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function attendancePage() {
    return {
        tab: 'mark',
        departments: [],
        filterDept: '',

        markDate: new Date().toISOString().slice(0, 10),
        markRows: [],
        markLoading: true,
        markSaving: false,

        summaryMonth: new Date().getMonth() + 1,
        summaryYear: new Date().getFullYear(),
        summaryRows: [],
        summaryLoading: true,
        monthNames: ['January','February','March','April','May','June','July','August','September','October','November','December'],
        yearOptions: Array.from({length: 6}, (_, i) => new Date().getFullYear() - i),

        get flatDepartments() {
            const flat = [];
            const walk = (list, prefix = '') => list.forEach(d => {
                flat.push({ id: d.id, name: prefix + d.name });
                if (d.children?.length) walk(d.children, prefix + '— ');
            });
            walk(this.departments);
            return flat;
        },

        async init() {
            try {
                this.departments = await apiFetch('/hr/departments').then(r => r.json());
            } catch (_) {}
            await this.loadMark();
        },

        async loadMark() {
            this.markLoading = true;
            try {
                const params = new URLSearchParams({ date: this.markDate });
                if (this.filterDept) params.set('department_id', this.filterDept);
                const data = await apiFetch('/hr/attendance/for-date?' + params.toString()).then(r => r.json());
                this.markRows = data.map(r => ({
                    employee_id: r.employee_id,
                    name: r.name,
                    department: r.department,
                    designation: r.designation,
                    status: r.attendance?.status ?? '',
                    time_in: (r.attendance?.time_in ?? '').slice(0, 5),
                    time_out: (r.attendance?.time_out ?? '').slice(0, 5),
                    notes: r.attendance?.notes ?? '',
                }));
            } catch (e) {
                toast('Failed to load employees for this date', 'error');
            } finally {
                this.markLoading = false;
            }
        },

        markAllPresent() {
            this.markRows.forEach(r => { if (!r.status) r.status = 'present'; });
        },

        async saveMark() {
            const records = this.markRows.filter(r => r.status).map(r => ({
                employee_id: r.employee_id,
                status: r.status,
                time_in: r.time_in || null,
                time_out: r.time_out || null,
                notes: r.notes || null,
            }));
            if (records.length === 0) { toast('Mark at least one employee before saving', 'error'); return; }
            this.markSaving = true;
            try {
                const res = await apiFetch('/hr/attendance/bulk-mark', {
                    method: 'POST',
                    body: JSON.stringify({ date: this.markDate, records }),
                }).then(r => r.json());
                toast(`Attendance saved for ${res.marked} employee(s).`, 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to save attendance', 'error');
            } finally {
                this.markSaving = false;
            }
        },

        async loadSummary() {
            this.summaryLoading = true;
            try {
                const params = new URLSearchParams({ month: this.summaryMonth, year: this.summaryYear });
                if (this.filterDept) params.set('department_id', this.filterDept);
                this.summaryRows = await apiFetch('/hr/attendance/summary?' + params.toString()).then(r => r.json());
            } catch (e) {
                toast('Failed to load monthly summary', 'error');
            } finally {
                this.summaryLoading = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/hr/attendance.blade.php ENDPATH**/ ?>