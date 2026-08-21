
<?php $__env->startSection('title', 'Expenses'); ?>
<?php $__env->startSection('page-title', 'Expenses'); ?>
<?php $__env->startSection('page-desc', 'Track and manage business expenses'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="expensesPage()" x-init="init()">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex flex-col sm:flex-row gap-2">
            <input x-model="search" type="text" placeholder="Search description or category…" class="input w-full sm:w-64" />
            <select x-model="statusFilter" class="input w-40">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
        <a href="<?php echo e(url('/expenses/create')); ?>" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Expense
        </a>
    </div>

    <div class="card p-0 overflow-hidden">
        <div x-show="loading" class="flex items-center justify-center py-16">
            <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
        </div>
        <div x-show="!loading" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-hd">Date</th>
                        <th class="table-hd">Account / Category</th>
                        <th class="table-hd">Description</th>
                        <th class="table-hd">Reference</th>
                        <th class="table-hd text-right">Amount</th>
                        <th class="table-hd">Submitted By</th>
                        <th class="table-hd">Status</th>
                        <th class="table-hd">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="e in filtered" :key="e.id">
                        <tr class="hover:bg-gray-50">
                            <td class="table-td" x-text="fmtDate(e.expense_date)"></td>
                            <td class="table-td" x-text="e.account?.name ?? e.category?.name ?? '—'"></td>
                            <td class="table-td" x-text="e.description ?? '—'"></td>
                            <td class="table-td text-xs text-gray-500" x-text="e.reference_number ?? '—'"></td>
                            <td class="table-td text-right font-semibold" x-text="fmtMoney(e.amount ?? 0)"></td>
                            <td class="table-td" x-text="e.created_by?.name ?? '—'"></td>
                            <td class="table-td">
                                <span :class="statusBadge(e.status)" x-text="e.status ?? 'pending'"></span>
                            </td>
                            <td class="table-td">
                                <div class="flex items-center gap-3">
                                    <a :href="BASE + '/expenses/' + e.id" class="text-indigo-600 hover:underline text-sm font-medium">View</a>
                                    <template x-if="['draft','pending'].includes(e.status)">
                                        <a :href="BASE + '/expenses/' + e.id + '/edit'" class="text-amber-600 hover:underline text-sm font-medium">Edit</a>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && filtered.length === 0">
                        <td colspan="8" class="table-td text-center text-gray-400 py-10">No expenses found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function expensesPage() {
    return {
        items: [],
        loading: true,
        search: '',
        statusFilter: '',
        get filtered() {
            let list = this.items;
            if (this.statusFilter) list = list.filter(e => e.status === this.statusFilter);
            const q = this.search.toLowerCase();
            if (!q) return list;
            return list.filter(e =>
                (e.description ?? '').toLowerCase().includes(q) ||
                (e.account?.name ?? e.category?.name ?? '').toLowerCase().includes(q)
            );
        },
        async init() {
            window.addEventListener('branch-switched', () => this.init());
            try {
                const r = await apiFetch('/expenses');
                if (!r) return;
                const data = await r.json();
                this.items = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load expenses', 'error');
            } finally {
                this.loading = false;
            }
        },
        statusBadge(status) {
            const map = { pending: 'badge-warning', approved: 'badge-success', rejected: 'badge-danger' };
            return 'badge ' + (map[status] ?? 'badge-gray');
        }
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/expenses/index.blade.php ENDPATH**/ ?>