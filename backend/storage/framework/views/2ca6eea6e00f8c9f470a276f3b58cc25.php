<?php $__env->startSection('title', 'HR Reports'); ?>
<?php $__env->startSection('page-title', 'HR Reports & Analytics'); ?>
<?php $__env->startSection('page-desc', 'Headcount, attendance, leave and payroll at a glance'); ?>
<?php $sec = 'hr'; ?>

<?php $__env->startSection('content'); ?>
<div x-data="hrReportsPage()" x-init="init()">

    <div x-show="loading" class="flex items-center justify-center py-20">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" x-cloak>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="card p-4"><div class="text-xs text-gray-400 uppercase tracking-wide">Active Headcount</div><div class="text-2xl font-bold mt-1" x-text="data.headcount"></div></div>
            <div class="card p-4"><div class="text-xs text-gray-400 uppercase tracking-wide">Present This Month</div><div class="text-2xl font-bold mt-1 text-green-600" x-text="data.attendance_this_month?.present ?? 0"></div></div>
            <div class="card p-4"><div class="text-xs text-gray-400 uppercase tracking-wide">Absent This Month</div><div class="text-2xl font-bold mt-1 text-red-600" x-text="data.attendance_this_month?.absent ?? 0"></div></div>
            <div class="card p-4"><div class="text-xs text-gray-400 uppercase tracking-wide">Leave Days This Month</div><div class="text-2xl font-bold mt-1" x-text="data.leave_days_this_month"></div></div>
            <div class="card p-4"><div class="text-xs text-gray-400 uppercase tracking-wide">Open Job Openings</div><div class="text-2xl font-bold mt-1" x-text="data.open_job_openings"></div></div>
            <div class="card p-4"><div class="text-xs text-gray-400 uppercase tracking-wide">Pending Leave Requests</div><div class="text-2xl font-bold mt-1 text-amber-600" x-text="data.pending_leave_requests"></div></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
            <div class="card p-6">
                <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-3">Headcount by Department</h3>
                <div class="space-y-2">
                    <template x-for="d in data.by_department" :key="d.department">
                        <div class="flex items-center justify-between text-sm">
                            <span x-text="d.department"></span>
                            <div class="flex items-center gap-2 flex-1 mx-3">
                                <div class="h-2 rounded-full bg-indigo-100 dark:bg-indigo-900/40 flex-1">
                                    <div class="h-2 rounded-full bg-indigo-500" :style="`width:${(d.total / maxDept) * 100}%`"></div>
                                </div>
                            </div>
                            <span class="font-semibold tabular-nums" x-text="d.total"></span>
                        </div>
                    </template>
                    <div x-show="!data.by_department?.length" class="text-gray-400 text-sm">No department data yet.</div>
                </div>
            </div>
            <div class="card p-6">
                <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-3">Headcount by Branch</h3>
                <div class="space-y-2">
                    <template x-for="b in data.by_branch" :key="b.branch">
                        <div class="flex items-center justify-between text-sm">
                            <span x-text="b.branch"></span>
                            <div class="flex items-center gap-2 flex-1 mx-3">
                                <div class="h-2 rounded-full bg-blue-100 dark:bg-blue-900/40 flex-1">
                                    <div class="h-2 rounded-full bg-blue-500" :style="`width:${(b.total / maxBranch) * 100}%`"></div>
                                </div>
                            </div>
                            <span class="font-semibold tabular-nums" x-text="b.total"></span>
                        </div>
                    </template>
                    <div x-show="!data.by_branch?.length" class="text-gray-400 text-sm">No branch data yet.</div>
                </div>
            </div>
        </div>

        <div class="card p-6">
            <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-3">Payroll Cost — Last 6 Paid Periods</h3>
            <div class="space-y-2">
                <template x-for="p in data.payroll_trend" :key="p.period">
                    <div class="flex items-center justify-between text-sm">
                        <span x-text="p.period"></span>
                        <div class="flex items-center gap-2 flex-1 mx-3">
                            <div class="h-2 rounded-full bg-green-100 dark:bg-green-900/40 flex-1">
                                <div class="h-2 rounded-full bg-green-500" :style="`width:${(p.total_net_pay / maxPayroll) * 100}%`"></div>
                            </div>
                        </div>
                        <span class="font-semibold tabular-nums" x-text="fmtMoney(p.total_net_pay)"></span>
                    </div>
                </template>
                <div x-show="!data.payroll_trend?.length" class="text-gray-400 text-sm">No paid payroll runs yet.</div>
            </div>
        </div>

        <div class="text-xs text-gray-400 mt-4" x-show="data.terminated_last_12_months !== undefined">
            <span x-text="data.terminated_last_12_months"></span> employee(s) terminated in the last 12 months.
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function hrReportsPage() {
    return {
        loading: true,
        data: {},

        get maxDept() { return Math.max(1, ...(this.data.by_department ?? []).map(d => d.total)); },
        get maxBranch() { return Math.max(1, ...(this.data.by_branch ?? []).map(b => b.total)); },
        get maxPayroll() { return Math.max(1, ...(this.data.payroll_trend ?? []).map(p => p.total_net_pay)); },

        async init() {
            try {
                this.data = await apiFetch('/hr/reports/dashboard').then(r => r.json());
            } catch (e) {
                toast('Failed to load HR reports', 'error');
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\hr\reports.blade.php ENDPATH**/ ?>