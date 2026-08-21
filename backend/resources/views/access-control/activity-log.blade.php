@extends('layouts.app')
@section('title', 'Activity Log')
@section('page-title', 'Activity Log')
@section('page-desc', 'Track all system actions and user activity')

@section('content')
<div x-data="activityLogPage()" x-init="init()">

    <!-- Filters -->
    <div class="card p-4 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-end gap-3">
            <div>
                <label class="label text-xs">Date From</label>
                <input type="date" x-model="dateFrom" class="input" />
            </div>
            <div>
                <label class="label text-xs">Date To</label>
                <input type="date" x-model="dateTo" class="input" />
            </div>
            <div>
                <label class="label text-xs">User</label>
                <input type="text" x-model="userSearch" class="input w-40" placeholder="User name…" />
            </div>
            <div>
                <label class="label text-xs">Action</label>
                <select x-model="actionFilter" class="input w-36">
                    <option value="">All Actions</option>
                    <option value="created">Created</option>
                    <option value="updated">Updated</option>
                    <option value="deleted">Deleted</option>
                    <option value="login">Login</option>
                    <option value="logout">Logout</option>
                </select>
            </div>
            <button @click="load()" :disabled="loading" class="btn-primary">
                <span x-text="loading ? 'Loading…' : 'Apply'"></span>
            </button>
            <button @click="resetFilters()" class="btn-secondary">Reset</button>
        </div>
    </div>

    <div class="card p-0 overflow-hidden">
        <div x-show="loading" class="flex items-center justify-center py-16">
            <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
        </div>
        <div x-show="!loading" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="table-hd">Time</th>
                        <th class="table-hd">User</th>
                        <th class="table-hd">Action</th>
                        <th class="table-hd">Resource</th>
                        <th class="table-hd">Details</th>
                        <th class="table-hd">IP Address</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="log in filtered" :key="log.id ?? Math.random()">
                        <tr class="hover:bg-gray-50">
                            <td class="table-td text-xs text-gray-500 whitespace-nowrap"
                                x-text="log.created_at ? new Date(log.created_at).toLocaleString() : '—'"></td>
                            <td class="table-td">
                                <div class="font-medium text-gray-900 text-sm" x-text="log.user?.name ?? log.causer?.name ?? '—'"></div>
                                <div class="text-xs text-gray-400" x-text="log.user?.email ?? log.causer?.email ?? ''"></div>
                            </td>
                            <td class="table-td">
                                <span :class="actionBadge(log.event ?? log.action)" x-text="log.event ?? log.action ?? '—'"></span>
                            </td>
                            <td class="table-td">
                                <div class="font-medium text-sm text-gray-800" x-text="log.subject_type?.split('\\\\').pop() ?? log.resource ?? '—'"></div>
                                <div class="text-xs text-gray-400" x-text="log.subject_id ? '#' + log.subject_id : ''"></div>
                            </td>
                            <td class="table-td text-xs text-gray-500 max-w-xs truncate"
                                :title="JSON.stringify(log.properties ?? log.description ?? '')"
                                x-text="log.description ?? (log.properties?.attributes ? JSON.stringify(log.properties.attributes).slice(0, 80) : '—')"></td>
                            <td class="table-td font-mono text-xs" x-text="log.ip_address ?? log.properties?.ip ?? '—'"></td>
                        </tr>
                    </template>
                    <tr x-show="!loading && filtered.length === 0">
                        <td colspan="6" class="table-td text-center text-gray-400 py-10">No activity logs found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="px-4 py-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500" x-show="meta.total > 0 && !loading">
            <span x-text="'Showing ' + (meta.from ?? 1) + '–' + (meta.to ?? items.length) + ' of ' + meta.total"></span>
            <div class="flex gap-1">
                <button @click="page--; load()" :disabled="page <= 1" class="btn-secondary text-xs py-1 px-2 disabled:opacity-40">Prev</button>
                <button @click="page++; load()" :disabled="page >= (meta.last_page ?? 1)" class="btn-secondary text-xs py-1 px-2 disabled:opacity-40">Next</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function activityLogPage() {
    return {
        items: [],
        loading: true,
        dateFrom: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0, 10),
        dateTo:   new Date().toISOString().slice(0, 10),
        userSearch: '',
        actionFilter: '',
        page: 1,
        meta: { total: 0, from: 1, to: 0, last_page: 1 },
        get filtered() {
            let list = this.items;
            const q = this.userSearch.toLowerCase();
            if (q) list = list.filter(l => (l.user?.name ?? l.causer?.name ?? '').toLowerCase().includes(q));
            if (this.actionFilter) list = list.filter(l => (l.event ?? l.action ?? '') === this.actionFilter);
            return list;
        },
        async init() { await this.load(); },
        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ per_page: 30, page: this.page });
                if (this.dateFrom) params.set('date_from', this.dateFrom);
                if (this.dateTo)   params.set('date_to',   this.dateTo);
                const data = await apiFetch('/activity-log?' + params).then(r => r.json());
                this.items = data.data ?? data ?? [];
                if (data.meta) this.meta = data.meta;
                else if (data.total !== undefined) {
                    this.meta = { total: data.total, from: data.from ?? 1, to: data.to ?? this.items.length, last_page: data.last_page ?? 1 };
                }
            } catch (e) {
                toast('Failed to load activity log', 'error');
            } finally {
                this.loading = false;
            }
        },
        resetFilters() {
            this.dateFrom    = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().slice(0, 10);
            this.dateTo      = new Date().toISOString().slice(0, 10);
            this.userSearch  = '';
            this.actionFilter = '';
            this.page        = 1;
            this.load();
        },
        actionBadge(action) {
            const map = {
                created: 'badge-success',
                updated: 'badge-primary',
                deleted: 'badge-danger',
                login:   'badge-gray',
                logout:  'badge-gray',
            };
            return 'badge ' + (map[action] ?? 'badge-gray');
        },
    };
}
</script>
@endpush
