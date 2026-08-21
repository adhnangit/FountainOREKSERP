<?php $__env->startSection('title', 'Expense Detail'); ?>
<?php $__env->startSection('page-title', 'Expense Detail'); ?>
<?php $__env->startSection('page-desc', 'View expense information'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="expenseShow()" x-init="init()">

    <template x-if="loading">
        <div class="flex items-center justify-center py-24 text-gray-400">
            <svg class="animate-spin w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            Loading…
        </div>
    </template>

    <template x-if="!loading && exp">
        <div class="space-y-5">

            <!-- Top bar -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <a href="<?php echo e(url('/expenses')); ?>"
                   class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-800 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    Back to Expenses
                </a>
                <div class="flex gap-2 flex-wrap">
                    <template x-if="['draft','pending'].includes(exp.status)">
                        <a :href="BASE + '/expenses/' + exp.id + '/edit'"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium border border-indigo-200 text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </a>
                    </template>
                    <template x-if="exp.status === 'pending'">
                        <button @click="approve()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-green-600 hover:bg-green-700 text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Approve
                        </button>
                    </template>
                    <template x-if="exp.status === 'pending'">
                        <button @click="showRejectModal = true"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 transition-colors">
                            Reject
                        </button>
                    </template>
                    <template x-if="exp.status !== 'approved'">
                        <button @click="deleteExpense()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-red-700 hover:bg-red-800 text-white transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Delete
                        </button>
                    </template>
                </div>
            </div>

            <!-- Header card -->
            <div class="card p-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-1">
                            <h2 class="text-xl font-bold text-gray-900" x-text="exp.expense_number"></h2>
                            <span :class="statusBadge(exp.status)" x-text="exp.status"></span>
                        </div>
                        <p class="text-gray-500 text-sm" x-text="exp.description"></p>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-gray-900" x-text="fmtMoney(exp.amount)"></div>
                        <div class="text-sm text-gray-400 mt-1" x-text="fmtDate(exp.expense_date)"></div>
                    </div>
                </div>
            </div>

            <!-- Details grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                <!-- Expense Info -->
                <div class="card p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Expense Details</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">Branch</dt>
                            <dd class="font-medium text-gray-900" x-text="exp.branch?.name ?? '—'"></dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">Category</dt>
                            <dd class="font-medium text-gray-900" x-text="exp.category?.name ?? '—'"></dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">Expense Account</dt>
                            <dd class="font-medium text-gray-900" x-text="exp.account?.name ?? '—'"></dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">Payment Account</dt>
                            <dd class="font-medium text-gray-900" x-text="exp.payment_account?.name ?? exp.paymentAccount?.name ?? '—'"></dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">Payment Method</dt>
                            <dd class="font-medium text-gray-900 capitalize" x-text="(exp.payment_method ?? '—').replace('_',' ')"></dd>
                        </div>
                        <template x-if="exp.reference_number">
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500">Reference No.</dt>
                                <dd class="font-medium text-gray-900" x-text="exp.reference_number"></dd>
                            </div>
                        </template>
                        <template x-if="exp.cheque_number">
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500">Cheque No.</dt>
                                <dd class="font-medium text-gray-900" x-text="exp.cheque_number"></dd>
                            </div>
                        </template>
                        <template x-if="exp.bank_name">
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500">Bank</dt>
                                <dd class="font-medium text-gray-900" x-text="exp.bank_name"></dd>
                            </div>
                        </template>
                        <template x-if="exp.cheque_date">
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500">Cheque Date</dt>
                                <dd class="font-medium text-gray-900" x-text="fmtDate(exp.cheque_date)"></dd>
                            </div>
                        </template>
                    </dl>
                </div>

                <!-- Submission / Approval Info -->
                <div class="card p-6 space-y-4">
                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Workflow</h3>
                    <dl class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">Submitted By</dt>
                            <dd class="font-medium text-gray-900" x-text="exp.created_by?.name ?? '—'"></dd>
                        </div>
                        <div class="flex justify-between text-sm">
                            <dt class="text-gray-500">Submitted On</dt>
                            <dd class="font-medium text-gray-900" x-text="exp.created_at ? fmtDate(exp.created_at) : '—'"></dd>
                        </div>
                        <template x-if="exp.approved_by">
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500">Approved By</dt>
                                <dd class="font-medium text-gray-900" x-text="exp.approved_by?.name ?? '—'"></dd>
                            </div>
                        </template>
                        <template x-if="exp.approved_at">
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500">Approved On</dt>
                                <dd class="font-medium text-gray-900" x-text="fmtDate(exp.approved_at)"></dd>
                            </div>
                        </template>
                        <template x-if="exp.rejection_reason">
                            <div class="flex justify-between text-sm">
                                <dt class="text-gray-500">Rejection Reason</dt>
                                <dd class="font-medium text-red-600" x-text="exp.rejection_reason"></dd>
                            </div>
                        </template>
                    </dl>

                    <!-- Notes -->
                    <template x-if="exp.notes">
                        <div class="pt-3 border-t border-gray-100">
                            <p class="text-xs text-gray-500 mb-1 uppercase tracking-wide font-semibold">Notes</p>
                            <p class="text-sm text-gray-700" x-text="exp.notes"></p>
                        </div>
                    </template>
                </div>
            </div>

        </div>
    </template>

    <template x-if="!loading && !exp">
        <div class="text-center py-24 text-gray-400">Expense not found.</div>
    </template>

    <!-- Reject Modal -->
    <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 max-h-[90vh] overflow-y-auto" @click.stop>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Reject Expense</h3>
            <textarea x-model="rejectReason" rows="3" placeholder="Reason for rejection…" class="input w-full mb-4"></textarea>
            <div class="flex justify-end gap-2">
                <button @click="showRejectModal = false" class="btn-secondary">Cancel</button>
                <button @click="reject()" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white text-sm font-medium">Reject</button>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function expenseShow() {
    const id = location.pathname.split('/').pop();
    return {
        exp: null,
        loading: true,
        showRejectModal: false,
        rejectReason: '',

        async init() {
            try {
                const r = await apiFetch('/expenses/' + id);
                if (!r) return;
                this.exp = await r.json();
            } catch (e) {
                toast('Failed to load expense', 'error');
            } finally {
                this.loading = false;
            }
        },

        async approve() {
            if (!confirm('Approve this expense?')) return;
            const r = await apiFetch('/expenses/' + id + '/approve', { method: 'POST' });
            if (!r) return;
            const d = await r.json();
            if (r.ok) { toast('Expense approved'); this.exp = d.expense ?? this.exp; this.exp.status = 'approved'; }
            else toast(d.message ?? 'Failed', 'error');
        },

        async reject() {
            if (!this.rejectReason.trim()) { toast('Enter a reason', 'error'); return; }
            const r = await apiFetch('/expenses/' + id + '/reject', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ reason: this.rejectReason })
            });
            if (!r) return;
            const d = await r.json();
            if (r.ok) { toast('Expense rejected'); this.showRejectModal = false; this.exp.status = 'rejected'; this.exp.rejection_reason = this.rejectReason; }
            else toast(d.message ?? 'Failed', 'error');
        },

        async deleteExpense() {
            if (!confirm('Delete this expense? This cannot be undone.')) return;
            const r = await apiFetch('/expenses/' + id, { method: 'DELETE' });
            if (!r) return;
            if (r.ok) { toast('Expense deleted'); location.href = BASE + '/expenses'; }
            else { const d = await r.json(); toast(d.message ?? 'Failed', 'error'); }
        },

        statusBadge(s) {
            const map = { pending: 'badge-warning', approved: 'badge-success', rejected: 'badge-danger' };
            return 'badge ' + (map[s] ?? 'badge-gray');
        }
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/expenses/show.blade.php ENDPATH**/ ?>