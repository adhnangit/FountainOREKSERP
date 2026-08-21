@extends('layouts.app')
@section('title', 'Goods Received Notes')
@section('page-title', 'Goods Received Notes')
@section('page-desc', 'Track all incoming goods receipts')

@section('content')
<div x-data="grnsListPage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <input x-model="search" type="text" placeholder="Search GRN# or supplier…" class="input w-64" />
        <a href="{{ url('/grns/create') }}" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New GRN
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
                        <th class="table-hd">GRN #</th>
                        <th class="table-hd">PO #</th>
                        <th class="table-hd">Supplier</th>
                        <th class="table-hd">Received Date</th>
                        <th class="table-hd">Status</th>
                        <th class="table-hd">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="grn in filtered" :key="grn.id">
                        <tr class="hover:bg-gray-50">
                            <td class="table-td font-medium text-indigo-600" x-text="grn.grn_number ?? ('#GRN-' + grn.id)"></td>
                            <td class="table-td" x-text="grn.purchase_order?.po_number ?? ('PO-' + grn.purchase_order_id)"></td>
                            <td class="table-td" x-text="grn.supplier?.name ?? grn.purchase_order?.supplier?.name ?? '—'"></td>
                            <td class="table-td" x-text="fmtDate(grn.received_date)"></td>
                            <td class="table-td">
                                <span :class="statusBadge(grn.status)" x-text="grn.status ?? 'received'"></span>
                            </td>
                            <td class="table-td">
                                <a :href="BASE + '/grns/' + grn.id" class="text-indigo-600 hover:underline text-sm font-medium">View</a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && filtered.length === 0">
                        <td colspan="6" class="table-td text-center text-gray-400 py-10">No GRNs found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function grnsListPage() {
    return {
        items: [],
        loading: true,
        search: '',
        get filtered() {
            const q = this.search.toLowerCase();
            if (!q) return this.items;
            return this.items.filter(g =>
                (g.grn_number ?? '').toLowerCase().includes(q) ||
                (g.supplier?.name ?? g.purchase_order?.supplier?.name ?? '').toLowerCase().includes(q)
            );
        },
        async init() {
            try {
                const res = await apiFetch('/grns');
                const data = await res.json();
                this.items = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load GRNs', 'error');
            } finally {
                this.loading = false;
            }
        },
        statusBadge(status) {
            const map = {
                received: 'badge-success',
                partial: 'badge-warning',
                pending: 'badge-gray',
                cancelled: 'badge-danger',
            };
            return 'badge ' + (map[status] ?? 'badge-gray');
        }
    };
}
</script>
@endpush
