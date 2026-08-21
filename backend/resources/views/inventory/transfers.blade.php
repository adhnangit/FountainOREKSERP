@extends('layouts.app')
@section('title', 'Stock Transfers')
@section('page-title', 'Stock Transfers')
@section('page-desc', 'Manage inter-branch stock transfers')

@section('content')
<div x-data="transfersPage()" x-init="init()">

    <div class="flex items-center justify-between mb-6">
        <input x-model="search" type="text" placeholder="Search ref# or branch…" class="input w-64" />
        <a href="{{ url('/inventory/transfers/create') }}" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Transfer
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
                        <th class="table-hd">From Branch</th>
                        <th class="table-hd">To Branch</th>
                        <th class="table-hd text-right">Items Count</th>
                        <th class="table-hd">Date</th>
                        <th class="table-hd">Status</th>
                        <th class="table-hd">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="t in filtered" :key="t.id">
                        <tr class="hover:bg-gray-50">
                            <td class="table-td font-medium text-indigo-600" x-text="t.reference ?? t.transfer_number ?? ('#TRF-' + t.id)"></td>
                            <td class="table-td" x-text="t.from_branch?.name ?? '—'"></td>
                            <td class="table-td" x-text="t.to_branch?.name ?? '—'"></td>
                            <td class="table-td text-right" x-text="t.items_count ?? t.items?.length ?? '—'"></td>
                            <td class="table-td" x-text="fmtDate(t.transfer_date ?? t.created_at)"></td>
                            <td class="table-td">
                                <span :class="statusBadge(t.status)" x-text="t.status ?? 'pending'"></span>
                            </td>
                            <td class="table-td">
                                <a :href="BASE + '/inventory/transfers/' + t.id" class="text-indigo-600 hover:underline text-sm font-medium">View</a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && filtered.length === 0">
                        <td colspan="7" class="table-td text-center text-gray-400 py-10">No transfers found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function transfersPage() {
    return {
        items: [],
        loading: true,
        search: '',
        get filtered() {
            const q = this.search.toLowerCase();
            if (!q) return this.items;
            return this.items.filter(t =>
                (t.reference ?? t.transfer_number ?? '').toLowerCase().includes(q) ||
                (t.from_branch?.name ?? '').toLowerCase().includes(q) ||
                (t.to_branch?.name ?? '').toLowerCase().includes(q)
            );
        },
        async init() {
            window.addEventListener('branch-switched', () => this.init());
            try {
                const r = await apiFetch('/transfers');
                if (!r) return;
                const data = await r.json();
                this.items = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load transfers', 'error');
            } finally {
                this.loading = false;
            }
        },
        statusBadge(status) {
            const map = {
                pending: 'badge-warning',
                approved: 'badge-primary',
                completed: 'badge-success',
                cancelled: 'badge-danger',
                draft: 'badge-gray',
            };
            return 'badge ' + (map[status] ?? 'badge-gray');
        }
    };
}
</script>
@endpush
