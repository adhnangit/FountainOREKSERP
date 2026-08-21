@extends('layouts.app')
@section('title', 'Purchase Returns')
@section('page-title', 'Purchase Returns')
@section('page-desc', 'Debit notes issued for goods returned to suppliers')
@php $sec = 'procurement'; @endphp

@section('content')
<div x-data="purchaseReturnsPage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <input x-model.debounce.400ms="search" @input.debounce.400ms="load()" type="text"
               placeholder="Search DN #, PO # or supplier…" class="input w-72" />
        <a href="{{ url('/purchase-returns/create') }}" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Return
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
                        <th class="table-hd">Debit Note #</th>
                        <th class="table-hd">Purchase Order</th>
                        <th class="table-hd">Supplier</th>
                        <th class="table-hd">Date</th>
                        <th class="table-hd text-right">Amount</th>
                        <th class="table-hd">Reason</th>
                        <th class="table-hd">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="r in items" :key="r.id">
                        <tr class="hover:bg-gray-50">
                            <td class="table-td font-medium text-indigo-600" x-text="r.return_number"></td>
                            <td class="table-td">
                                <a x-show="r.purchase_order" :href="BASE + '/purchase-orders/' + r.purchase_order_id"
                                   class="text-indigo-600 hover:underline" x-text="r.purchase_order?.po_number"></a>
                                <span x-show="!r.purchase_order" class="text-gray-400">—</span>
                            </td>
                            <td class="table-td" x-text="r.supplier?.name ?? '—'"></td>
                            <td class="table-td" x-text="fmtDate(r.return_date)"></td>
                            <td class="table-td text-right font-semibold text-red-600" x-text="fmtMoney(r.total)"></td>
                            <td class="table-td max-w-[220px] truncate" x-text="r.reason ?? '—'"></td>
                            <td class="table-td">
                                <button @click="detail = r" class="text-indigo-600 hover:underline text-sm font-medium">View</button>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && items.length === 0">
                        <td colspan="7" class="table-td text-center text-gray-400 py-10">No purchase returns yet.</td>
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
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white" x-text="detail.return_number"></h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            <span x-text="fmtDate(detail.return_date)"></span>
                            <span x-show="detail.purchase_order"> · against <span class="font-medium" x-text="detail.purchase_order?.po_number"></span></span>
                            <span x-show="detail.supplier"> · <span x-text="detail.supplier?.name"></span></span>
                        </p>
                    </div>
                    <button @click="detail = null" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto">
                    <div x-show="detail.reason" class="px-6 pt-4">
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Reason</div>
                        <p class="text-sm text-gray-700 dark:text-gray-300" x-text="detail.reason"></p>
                    </div>
                    <div class="px-6 py-4">
                        <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Returned Items</div>
                        <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700/40">
                                    <tr>
                                        <th class="table-hd">Product</th>
                                        <th class="table-hd text-right">Qty</th>
                                        <th class="table-hd text-right">Unit Price</th>
                                        <th class="table-hd text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    <template x-for="it in (detail.items ?? [])" :key="it.id">
                                        <tr>
                                            <td class="table-td" x-text="it.product_name ?? it.product?.name"></td>
                                            <td class="table-td text-right tabular-nums" x-text="parseFloat(it.quantity)"></td>
                                            <td class="table-td text-right tabular-nums" x-text="fmtMoney(it.unit_price)"></td>
                                            <td class="table-td text-right tabular-nums font-semibold" x-text="fmtMoney(it.total)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        <div class="flex justify-end mt-3">
                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100">
                                Total returned: <span class="text-red-600" x-text="fmtMoney(detail.total)"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end flex-shrink-0">
                    <button @click="detail = null" class="btn-secondary">Close</button>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection

@push('scripts')
<script>
function purchaseReturnsPage() {
    return {
        items: [],
        loading: true,
        search: '',
        detail: null,
        init() { this.load(); },
        async load() {
            this.loading = true;
            try {
                const r = await apiFetch('/purchase-returns' + (this.search ? '?search=' + encodeURIComponent(this.search) : ''));
                if (!r) return;
                const data = await r.json();
                this.items = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load purchase returns', 'error');
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endpush
