@extends('layouts.app')
@section('title', 'Suppliers')
@section('page-title', 'Suppliers')
@section('page-desc', 'Manage your supplier accounts')

@section('content')
<div x-data="suppliersPage()" x-init="init()">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <input x-model="search" type="text" placeholder="Search name, phone, email…" class="input w-full sm:w-72" />
        <a href="{{ url('/suppliers/create') }}" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Supplier
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
                        <th class="table-hd">Name</th>
                        <th class="table-hd">Phone</th>
                        <th class="table-hd">Email</th>
                        <th class="table-hd">City</th>
                        <th class="table-hd">Payment Terms</th>
                        <th class="table-hd text-right">Balance</th>
                        <th class="table-hd">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="s in filtered" :key="s.id">
                        <tr class="hover:bg-gray-50">
                            <td class="table-td font-medium text-gray-900" x-text="s.name"></td>
                            <td class="table-td" x-text="s.phone ?? '—'"></td>
                            <td class="table-td" x-text="s.email ?? '—'"></td>
                            <td class="table-td" x-text="s.city ?? '—'"></td>
                            <td class="table-td" x-text="s.payment_terms ?? '—'"></td>
                            <td class="table-td text-right"
                                :class="(s.balance ?? 0) > 0 ? 'text-red-600 font-semibold' : 'text-gray-600'"
                                x-text="fmtMoney(s.balance ?? 0)"></td>
                            <td class="table-td">
                                <a :href="BASE + '/suppliers/' + s.id" class="text-indigo-600 hover:underline text-sm font-medium">View</a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && filtered.length === 0">
                        <td colspan="7" class="table-td text-center text-gray-400 py-10">No suppliers found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function suppliersPage() {
    return {
        items: [],
        loading: true,
        search: '',
        get filtered() {
            const q = this.search.toLowerCase();
            if (!q) return this.items;
            return this.items.filter(s =>
                (s.name ?? '').toLowerCase().includes(q) ||
                (s.phone ?? '').toLowerCase().includes(q) ||
                (s.email ?? '').toLowerCase().includes(q)
            );
        },
        async init() {
            try {
                const data = await apiFetch('/suppliers').then(r => r.json());
                this.items = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load suppliers', 'error');
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endpush
