<?php $__env->startSection('title', 'Task Manager'); ?>
<?php $__env->startSection('page-title', 'Task Manager Dashboard'); ?>
<?php $__env->startSection('page-desc', 'Overview of internal work-task performance across all categories'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="taskManagerDashboard()" x-init="init()" x-cloak>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="inline-flex rounded-xl p-1 bg-gray-100 dark:bg-gray-800">
            <button @click="setScope(true)" class="text-xs font-semibold px-3.5 py-1.5 rounded-lg transition-all"
                    :class="myTasksOnly ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500'">
                My Tasks
            </button>
            <button @click="setScope(false)" class="text-xs font-semibold px-3.5 py-1.5 rounded-lg transition-all"
                    :class="!myTasksOnly ? 'bg-white dark:bg-gray-700 text-indigo-600 dark:text-indigo-400 shadow-sm' : 'text-gray-500'">
                All Tasks
            </button>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo e(url('/task-manager/categories')); ?>" class="btn-secondary text-sm">Categories</a>
            <a href="<?php echo e(url('/task-manager/board')); ?>" class="btn-primary text-sm">Task Board</a>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" class="space-y-4">

        <!-- KPI cards -->
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
            <div class="card p-4">
                <div class="text-2xl font-black text-gray-900 dark:text-gray-100" x-text="stats.total ?? 0"></div>
                <div class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mt-1">Total Tasks</div>
            </div>
            <div class="card p-4">
                <div class="text-2xl font-black" style="color:#a16207" x-text="stats.pending ?? 0"></div>
                <div class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mt-1">Pending</div>
            </div>
            <div class="card p-4">
                <div class="text-2xl font-black" style="color:#1d4ed8" x-text="stats.in_progress ?? 0"></div>
                <div class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mt-1">In Progress</div>
            </div>
            <div class="card p-4">
                <div class="text-2xl font-black" style="color:#15803d" x-text="stats.completed ?? 0"></div>
                <div class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mt-1">Completed</div>
            </div>
            <div class="card p-4">
                <div class="text-2xl font-black" style="color:#b91c1c" x-text="stats.overdue ?? 0"></div>
                <div class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mt-1">Overdue</div>
            </div>
            <div class="card p-4">
                <div class="text-2xl font-black" style="color:#7c3aed" x-text="stats.due_soon ?? 0"></div>
                <div class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mt-1">Due in 7 Days</div>
            </div>
        </div>

        <!-- Rate bars -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="card p-5">
                <div class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-3">Completion Rate</div>
                <div class="flex items-center gap-4">
                    <div class="text-4xl font-black" style="color:#2563eb" x-text="(stats.completion_rate ?? 0) + '%'"></div>
                    <div class="flex-1">
                        <div class="h-2.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                            <div class="h-full rounded-full" :style="'width:' + (stats.completion_rate ?? 0) + '%; background:linear-gradient(90deg,#3b82f6,#2563eb)'"></div>
                        </div>
                        <div class="text-xs text-gray-400 mt-1.5" x-text="(stats.completed ?? 0) + ' of ' + ((stats.total ?? 0) - (stats.cancelled ?? 0)) + ' active tasks completed'"></div>
                    </div>
                </div>
            </div>
            <div class="card p-5">
                <div class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-3">On-Time Delivery</div>
                <template x-if="stats.on_time_rate === null || stats.on_time_rate === undefined">
                    <p class="text-sm text-gray-400">No completed tasks yet to measure on-time performance.</p>
                </template>
                <template x-if="stats.on_time_rate !== null && stats.on_time_rate !== undefined">
                    <div class="flex items-center gap-4">
                        <div class="text-4xl font-black" :style="'color:' + (stats.on_time_rate >= 80 ? '#16a34a' : (stats.on_time_rate >= 50 ? '#d97706' : '#dc2626'))" x-text="stats.on_time_rate + '%'"></div>
                        <div class="flex-1">
                            <div class="h-2.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                <div class="h-full rounded-full" :style="'width:' + stats.on_time_rate + '%; background:' + (stats.on_time_rate >= 80 ? '#16a34a' : (stats.on_time_rate >= 50 ? '#d97706' : '#dc2626'))"></div>
                            </div>
                            <div class="text-xs text-gray-400 mt-1.5">Of completed tasks finished on or before their due date</div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Category breakdown / overdue / due soon -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="card p-5">
                <div class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-3">By Category</div>
                <template x-for="cat in categoryBreakdown" :key="cat.name">
                    <div class="mb-3.5">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs font-semibold text-gray-600 dark:text-gray-300 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full" :style="'background:' + cat.color"></span> <span x-text="cat.name"></span>
                            </span>
                            <span class="text-[11px] text-gray-400" x-text="cat.completed + '/' + cat.total + ' · ' + cat.percentage + '%'"></span>
                        </div>
                        <div class="h-1.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                            <div class="h-full rounded-full" :style="'width:' + cat.percentage + '%; background:' + cat.color"></div>
                        </div>
                        <div x-show="cat.overdue > 0" class="text-[10.5px] font-semibold mt-1" style="color:#dc2626" x-text="cat.overdue + ' overdue'"></div>
                    </div>
                </template>
                <p x-show="!categoryBreakdown.length" class="text-sm text-gray-400">No categorized tasks yet.</p>
                <div x-show="uncategorizedCount > 0" class="pt-2 mt-1 border-t border-dashed border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <span class="text-xs font-semibold text-gray-600 dark:text-gray-300 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-gray-300"></span> Uncategorized
                    </span>
                    <span class="text-[11px] text-gray-400" x-text="uncategorizedCount"></span>
                </div>
            </div>

            <div class="card p-5">
                <div class="text-sm font-bold mb-3" style="color:#b91c1c">Overdue Tasks</div>
                <template x-for="task in overdueTasks" :key="task.id">
                    <div class="flex justify-between items-center py-2 border-b border-gray-50 dark:border-gray-800 last:border-0">
                        <div>
                            <div class="text-xs font-semibold text-gray-700 dark:text-gray-200" x-text="task.title"></div>
                            <div class="text-[11px] text-gray-400" x-text="task.assignee?.name ?? 'Unassigned'"></div>
                        </div>
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full badge-danger" x-text="fmtDate(task.due_date)"></span>
                    </div>
                </template>
                <p x-show="!overdueTasks.length" class="text-sm text-gray-400">Nothing overdue. Great job!</p>
            </div>

            <div class="card p-5">
                <div class="text-sm font-bold mb-3" style="color:#7c3aed">Due in Next 7 Days</div>
                <template x-for="task in dueSoonTasks" :key="task.id">
                    <div class="flex justify-between items-center py-2 border-b border-gray-50 dark:border-gray-800 last:border-0">
                        <div>
                            <div class="text-xs font-semibold text-gray-700 dark:text-gray-200" x-text="task.title"></div>
                            <div class="text-[11px] text-gray-400" x-text="task.assignee?.name ?? 'Unassigned'"></div>
                        </div>
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full badge-warning" x-text="fmtDate(task.due_date)"></span>
                    </div>
                </template>
                <p x-show="!dueSoonTasks.length" class="text-sm text-gray-400">Nothing due soon.</p>
            </div>
        </div>

        <!-- Recent activity -->
        <div class="card p-5">
            <div class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-3">Recent Activity</div>
            <template x-for="fu in recentFollowups" :key="fu.id">
                <div class="flex gap-2.5 py-2 border-b border-gray-50 dark:border-gray-800 last:border-0">
                    <div class="w-1.5 h-1.5 rounded-full bg-indigo-500 mt-1.5 flex-shrink-0"></div>
                    <div>
                        <div class="text-xs text-gray-700 dark:text-gray-200">
                            <strong x-text="fu.user?.name ?? 'System'"></strong> on <strong x-text="fu.task?.title ?? 'a deleted task'"></strong>: <span x-text="fu.note"></span>
                        </div>
                        <div class="text-[10.5px] text-gray-400 mt-0.5" x-text="timeAgo(fu.created_at)"></div>
                    </div>
                </div>
            </template>
            <p x-show="!recentFollowups.length" class="text-sm text-gray-400">No activity yet.</p>
        </div>

    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function taskManagerDashboard() {
    return {
        loading: true,
        myTasksOnly: true,
        stats: {},
        categoryBreakdown: [],
        uncategorizedCount: 0,
        overdueTasks: [],
        dueSoonTasks: [],
        recentFollowups: [],

        async init() {
            try {
                const stored = localStorage.getItem('tm_dashboard_scope');
                if (stored) this.myTasksOnly = stored === 'mine';
            } catch (e) {}
            await this.load();
        },

        setScope(mine) {
            this.myTasksOnly = mine;
            try { localStorage.setItem('tm_dashboard_scope', mine ? 'mine' : 'all'); } catch (e) {}
            this.load();
        },

        async load() {
            this.loading = true;
            try {
                const params = this.myTasksOnly ? '?my_tasks=1' : '';
                const data = await apiFetch('/work-tasks/dashboard' + params).then(r => r.json());
                this.stats = data.stats ?? {};
                this.categoryBreakdown = data.category_breakdown ?? [];
                this.uncategorizedCount = data.uncategorized_count ?? 0;
                this.overdueTasks = data.overdue_tasks ?? [];
                this.dueSoonTasks = data.due_soon_tasks ?? [];
                this.recentFollowups = data.recent_followups ?? [];
            } catch (e) {
                toast('Failed to load dashboard', 'error');
            } finally {
                this.loading = false;
            }
        },

        fmtDate(d) {
            if (!d) return '—';
            return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
        },

        timeAgo(d) {
            if (!d) return '';
            const secs = Math.floor((new Date() - new Date(d)) / 1000);
            if (secs < 60) return 'just now';
            const mins = Math.floor(secs / 60); if (mins < 60) return mins + 'm ago';
            const hrs = Math.floor(mins / 60); if (hrs < 24) return hrs + 'h ago';
            const days = Math.floor(hrs / 24); if (days < 30) return days + 'd ago';
            return this.fmtDate(d);
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views/task-manager/dashboard.blade.php ENDPATH**/ ?>