@extends('layouts.app')
@section('title', 'Customers')
@section('page-title', 'Customers')
@section('page-desc', 'Manage your customer accounts')

@section('content')
<div x-data="customersPage()" x-init="init()">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div class="flex flex-wrap gap-2 flex-1">
            <input x-model="search" type="text" placeholder="Search name, phone, city, district…" class="input w-full sm:w-64" />
            <select x-model="filterDistrict" class="input w-full sm:w-44">
                <option value="">All Districts</option>
                <template x-for="d in districts" :key="d"><option :value="d" x-text="d"></option></template>
            </select>
        </div>
        <a href="{{ url('/customers/create') }}" class="btn-primary inline-flex items-center gap-2 whitespace-nowrap">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Customer
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
                        <th x-show="isAllBranches" class="table-hd">Branch</th>
                        <th class="table-hd">Phone</th>
                        <th class="table-hd">City</th>
                        <th class="table-hd">District</th>
                        <th class="table-hd text-right">Credit Limit</th>
                        <th class="table-hd text-right">Balance</th>
                        <th class="table-hd">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="c in filtered" :key="c.id">
                        <tr class="hover:bg-gray-50">
                            <td class="table-td font-medium text-gray-900">
                                <span x-text="c.name"></span>
                                <span x-show="c.is_walk_in" class="ml-1.5 text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700">Walk-in</span>
                            </td>
                            <td x-show="isAllBranches" class="table-td">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700" x-text="c.branch?.name ?? '—'"></span>
                            </td>
                            <td class="table-td" x-text="c.phone ?? '—'"></td>
                            <td class="table-td" x-text="c.city ?? '—'"></td>
                            <td class="table-td">
                                <span x-show="c.district" class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700" x-text="c.district"></span>
                                <span x-show="!c.district" class="text-gray-400">—</span>
                            </td>
                            <td class="table-td text-right" x-text="fmtMoney(c.credit_limit ?? 0)"></td>
                            <td class="table-td text-right"
                                :class="(c.balance ?? 0) > 0 ? 'text-red-600 font-semibold' : 'text-gray-600'"
                                x-text="fmtMoney(c.balance ?? 0)"></td>
                            <td class="table-td">
                                <a :href="BASE + '/customers/' + c.id" class="text-indigo-600 hover:underline text-sm font-medium">View</a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && filtered.length === 0">
                        <td :colspan="isAllBranches ? 9 : 8" class="table-td text-center text-gray-400 py-10">No customers found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function customersPage() {
    return {
        items: [],
        loading: true,
        search: '',
        filterDistrict: '',
        isAllBranches: localStorage.getItem('medri_branch') === 'all' || !localStorage.getItem('medri_branch'),
        districts: ['Ampara','Anuradhapura','Badulla','Batticaloa','Colombo','Galle','Gampaha','Hambantota','Jaffna','Kalutara','Kandy','Kegalle','Kilinochchi','Kurunegala','Mannar','Matale','Matara','Monaragala','Mullaitivu','Nuwara Eliya','Polonnaruwa','Puttalam','Ratnapura','Trincomalee','Vavuniya'],
        get filtered() {
            const q = this.search.toLowerCase();
            return this.items.filter(c => {
                const matchSearch = !q ||
                    (c.name ?? '').toLowerCase().includes(q) ||
                    (c.phone ?? '').toLowerCase().includes(q) ||
                    (c.email ?? '').toLowerCase().includes(q) ||
                    (c.city ?? '').toLowerCase().includes(q) ||
                    (c.district ?? '').toLowerCase().includes(q);
                const matchDistrict = !this.filterDistrict || c.district === this.filterDistrict;
                return matchSearch && matchDistrict;
            });
        },
        async init() {
            try {
                const data = await apiFetch('/customers?per_page=500').then(r => r.json());
                this.items = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load customers', 'error');
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endpush
