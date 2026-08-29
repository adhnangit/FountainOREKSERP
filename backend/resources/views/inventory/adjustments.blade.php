@extends('layouts.app')
@section('title', 'Stock Adjustments')
@section('page-title', 'Stock Adjustments')
@section('page-desc', 'Record manual stock adjustments')

@section('content')
<div x-data="adjustmentsPage()" x-init="init()">

    <!-- Pending-approval banner — a draft has NOT touched stock yet -->
    <div x-show="!loading && pendingCount > 0"
         class="mb-4 flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-amber-300 bg-amber-50 dark:bg-amber-900/10 dark:border-amber-800">
        <div class="flex items-center gap-2.5">
            <svg class="w-4.5 h-4.5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
            <span class="text-sm text-amber-800 dark:text-amber-300">
                <strong x-text="pendingCount"></strong>
                <span x-text="pendingCount === 1 ? 'adjustment is' : 'adjustments are'"></span>
                still in draft — stock hasn't changed for <span x-text="pendingCount === 1 ? 'it' : 'them'"></span> yet.
            </span>
        </div>
        <button @click="showPendingOnly = true; search = ''" x-show="!showPendingOnly"
                class="text-xs font-semibold text-amber-700 dark:text-amber-400 hover:underline flex-shrink-0">Show pending</button>
    </div>

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2">
            <input x-model="search" type="text" placeholder="Search ref# or product…" class="input w-64" />
            <button @click="showPendingOnly = !showPendingOnly"
                    :class="showPendingOnly ? 'bg-amber-100 border-amber-400 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' : 'border-gray-200 dark:border-gray-600 text-gray-500'"
                    class="text-xs font-semibold px-3 py-2 rounded-lg border transition-colors">
                Pending only
            </button>
        </div>
        <a href="{{ url('/inventory/adjustments/create') }}" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Adjustment
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
                        <th class="table-hd">Ref #</th>
                        <th class="table-hd">Products</th>
                        <th class="table-hd text-right">Qty Change</th>
                        <th class="table-hd">Reason</th>
                        <th class="table-hd">Date</th>
                        <th class="table-hd">Status</th>
                        <th class="table-hd">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="a in filtered" :key="a.id">
                        <tr class="hover:bg-gray-50">
                            <td class="table-td font-medium text-indigo-600" x-text="a.adjustment_number ?? ('#ADJ-' + a.id)"></td>
                            <td class="table-td" x-text="productSummary(a)"></td>
                            <td class="table-td text-right font-semibold"
                                :class="netDiff(a) >= 0 ? 'text-green-600' : 'text-red-600'"
                                x-text="(netDiff(a) > 0 ? '+' : '') + netDiff(a)"></td>
                            <td class="table-td" x-text="a.reason ?? '—'"></td>
                            <td class="table-td" x-text="fmtDate(a.adjustment_date ?? a.created_at)"></td>
                            <td class="table-td">
                                <span :class="statusBadge(a.status)" x-text="a.status ?? '—'"></span>
                            </td>
                            <td class="table-td">
                                <div class="flex items-center gap-3">
                                    <button @click="detail = a" class="text-indigo-600 hover:underline text-sm font-medium">View</button>
                                    <a x-show="a.status === 'draft' && hasPerm('inventory.adjustments.create')"
                                       :href="BASE + '/inventory/adjustments/create?edit=' + a.id"
                                       class="text-amber-600 hover:underline text-sm font-medium">Edit</a>
                                    <button x-show="a.status === 'draft' && hasPerm('inventory.adjustments.approve')"
                                            @click="approve(a)"
                                            class="text-green-600 hover:underline text-sm font-medium">Approve</button>
                                    <button x-show="a.status === 'draft' && hasPerm('inventory.adjustments.create')"
                                            @click="deleteDraft(a)"
                                            class="text-red-500 hover:underline text-sm font-medium">Delete</button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && filtered.length === 0">
                        <td colspan="7" class="table-td text-center text-gray-400 py-10">No adjustments found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ── Detail Modal ── --}}
    <template x-if="detail">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.55)" @click.self="detail = null">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl z-10 flex flex-col overflow-hidden" style="max-height:90vh">

                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between flex-shrink-0">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white" x-text="detail.adjustment_number"></h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <span x-text="fmtDate(detail.adjustment_date ?? detail.created_at)"></span>
                            <span x-show="detail.branch"> · <span x-text="detail.branch?.name"></span></span>
                            <span x-show="detail.created_by?.name"> · by <span x-text="detail.created_by?.name ?? ''"></span></span>
                        </p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span :class="statusBadge(detail.status)" x-text="detail.status"></span>
                        <button @click="detail = null" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <div x-show="detail.reason" class="px-6 pt-4">
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Reason</div>
                        <p class="text-sm text-gray-700 dark:text-gray-300" x-text="detail.reason"></p>
                    </div>
                    <div class="px-6 py-4">
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Items</div>
                        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/40">
                                    <tr>
                                        <th class="table-hd">Product</th>
                                        <th class="table-hd">Batch</th>
                                        <th class="table-hd text-right">System Qty</th>
                                        <th class="table-hd text-right">Physical Qty</th>
                                        <th class="table-hd text-right">Difference</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <template x-for="it in (detail.items ?? [])" :key="it.id">
                                        <tr>
                                            <td class="table-td" x-text="it.product?.name ?? ('#' + it.product_id)"></td>
                                            <td class="table-td text-gray-500" x-text="it.batch?.batch_code ?? 'All batches'"></td>
                                            <td class="table-td text-right tabular-nums" x-text="parseFloat(it.system_quantity)"></td>
                                            <td class="table-td text-right tabular-nums" x-text="parseFloat(it.physical_quantity)"></td>
                                            <td class="table-td text-right font-semibold tabular-nums"
                                                :class="it.difference >= 0 ? 'text-green-600' : 'text-red-600'"
                                                x-text="(it.difference > 0 ? '+' : '') + parseFloat(it.difference)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <p x-show="detail.status === 'draft'" class="text-xs text-amber-600 bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 mt-3">
                            Draft — stock has not been updated yet. Approving will apply these differences to inventory.
                        </p>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 flex-shrink-0">
                    <button @click="detail = null" class="btn-secondary">Close</button>
                    <a x-show="detail.status === 'draft' && hasPerm('inventory.adjustments.create')"
                       :href="BASE + '/inventory/adjustments/create?edit=' + detail.id"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border border-amber-300 text-amber-700 hover:bg-amber-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </a>
                    <button x-show="detail.status === 'draft' && hasPerm('inventory.adjustments.approve')"
                            @click="approve(detail)" :disabled="approving"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-green-600 hover:bg-green-700 text-white transition-colors disabled:opacity-50">
                        <svg x-show="!approving" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                        <svg x-show="approving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        <span x-text="approving ? 'Approving…' : 'Approve & Update Stock'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
function adjustmentsPage() {
    return {
        items: [],
        loading: true,
        search: '',
        showPendingOnly: false,
        detail: null,
        approving: false,
        get pendingCount() {
            return this.items.filter(a => a.status === 'draft').length;
        },
        get filtered() {
            const q = this.search.toLowerCase();
            let list = this.items;
            if (this.showPendingOnly) list = list.filter(a => a.status === 'draft');
            if (!q) return list;
            return list.filter(a =>
                (a.adjustment_number ?? '').toLowerCase().includes(q) ||
                (a.items ?? []).some(it => (it.product?.name ?? '').toLowerCase().includes(q))
            );
        },
        init() {
            this.load();
        },
        async load() {
            this.loading = true;
            try {
                const r = await apiFetch('/adjustments');
                if (!r) return;
                const data = await r.json();
                this.items = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load adjustments', 'error');
            } finally {
                this.loading = false;
            }
        },
        productSummary(a) {
            const its = a.items ?? [];
            if (!its.length) return '—';
            const first = its[0].product?.name ?? ('#' + its[0].product_id);
            return its.length === 1 ? first : first + ' +' + (its.length - 1) + ' more';
        },
        netDiff(a) {
            return (a.items ?? []).reduce((s, it) => s + parseFloat(it.difference ?? 0), 0);
        },
        async deleteDraft(a) {
            if (!confirm('Delete draft ' + (a.adjustment_number ?? '') + '? This cannot be undone.')) return;
            try {
                await apiFetch(`/adjustments/${a.id}`, { method: 'DELETE' });
                toast('Draft adjustment deleted');
                this.detail = null;
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to delete adjustment', 'error');
            }
        },
        async approve(a) {
            if (!confirm('Approve ' + (a.adjustment_number ?? 'this adjustment') + ' and update stock?')) return;
            this.approving = true;
            try {
                await apiFetch(`/adjustments/${a.id}/approve`, { method: 'POST' });
                toast('Adjustment approved — stock updated');
                this.detail = null;
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to approve adjustment', 'error');
            } finally {
                this.approving = false;
            }
        },
        statusBadge(status) {
            const map = { approved: 'badge-success', pending: 'badge-warning', rejected: 'badge-danger', draft: 'badge-gray' };
            return 'badge ' + (map[status] ?? 'badge-gray');
        }
    };
}
</script>
@endpush
