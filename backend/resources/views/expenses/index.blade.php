@extends('layouts.app')
@section('title', 'Expenses')
@section('page-title', 'Expenses')
@section('page-desc', 'Track and manage business expenses')

@section('content')
<div x-data="expensesPage()" x-init="init()">

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-3">
        <div class="flex flex-col sm:flex-row flex-wrap gap-2">
            <input x-model.debounce.400ms="search" @input.debounce.400ms="page=1;load()" type="text" placeholder="Search description or category…" class="input w-full sm:w-64" />
            <select x-model="statusFilter" @change="page=1;load()" class="input w-40">
                <option value="">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <input x-model="fromDate" @change="page=1;load()" type="date" class="exp-date-input" title="From date" />
            <input x-model="toDate" @change="page=1;load()" type="date" class="exp-date-input" title="To date" />
        </div>
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-1.5">
                <button @click="exportFile('xlsx')" :disabled="exporting" class="btn-secondary text-xs py-1.5 px-2.5 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Excel
                </button>
                <button @click="exportFile('csv')" :disabled="exporting" class="btn-secondary text-xs py-1.5 px-2.5 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    CSV
                </button>
                <button @click="exportFile('pdf')" :disabled="exporting" class="btn-secondary text-xs py-1.5 px-2.5 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    PDF
                </button>
            </div>
            <a href="{{ url('/expenses/create') }}" x-show="hasPerm('expenses.create')" class="btn-primary inline-flex items-center gap-2 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Expense
            </a>
        </div>
    </div>

    <p class="text-xs text-gray-400 mb-4" x-show="meta.total > 0">
        <span x-text="meta.total"></span> expense<span x-show="meta.total !== 1">s</span>
        <span x-show="search || statusFilter || fromDate || toDate"> matching filters</span>
        &nbsp;·&nbsp; Total <span class="font-semibold text-gray-600" x-text="fmtMoney(stats.total_amount)"></span>
    </p>

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
                    <template x-for="e in items" :key="e.id">
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
                                    <template x-if="['draft','pending'].includes(e.status) && hasPerm('expenses.create')">
                                        <a :href="BASE + '/expenses/' + e.id + '/edit'" class="text-amber-600 hover:underline text-sm font-medium">Edit</a>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && items.length === 0">
                        <td colspan="8" class="table-td text-center text-gray-400 py-10">No expenses found.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="inv-pagination" x-show="meta.total > 0">
            <div class="inv-page-info"
                 x-text="'Showing '+meta.from+'–'+meta.to+' of '+meta.total+' expenses'"></div>
            <div class="inv-page-btns">
                <button class="inv-page-btn" @click="page=1;load()" :disabled="page<=1">
                    <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M11 19l-7-7 7-7M18 19l-7-7 7-7"/></svg>
                </button>
                <button class="inv-page-btn" @click="page--;load()" :disabled="page<=1">
                    <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M15 19l-7-7 7-7"/></svg>
                </button>
                <template x-for="p in pageNumbers" :key="p">
                    <button class="inv-page-btn" :class="p===page?'active':''"
                            @click="page=p;load()" x-text="p"></button>
                </template>
                <button class="inv-page-btn" @click="page++;load()" :disabled="page>=meta.last_page">
                    <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 5l7 7-7 7"/></svg>
                </button>
                <button class="inv-page-btn" @click="page=meta.last_page;load()" :disabled="page>=meta.last_page">
                    <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M13 5l7 7-7 7M6 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.exp-date-input{border:1px solid #e2e8f0;border-radius:9px;padding:7px 10px;font-size:12.5px;color:#334155;background:#f8fafc;outline:none}
.exp-date-input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}
.dark .exp-date-input{background:#0f172a;border-color:#334155;color:#cbd5e1}
.inv-pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid #f1f5f9}
.inv-page-info{font-size:12.5px;color:#94a3b8}
.inv-page-btns{display:flex;gap:4px}
.inv-page-btn{min-width:30px;height:30px;padding:0 6px;border-radius:7px;border:1px solid #e2e8f0;background:#fff;font-size:12px;font-weight:600;color:#475569;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s}
.inv-page-btn:hover:not(:disabled):not(.active){background:#f8fafc}
.inv-page-btn.active{background:#6366f1;color:#fff;border-color:#6366f1}
.inv-page-btn:disabled{opacity:.35;cursor:default}
.dark .inv-pagination{border-color:#334155}
.dark .inv-page-btn{background:#1e293b;border-color:#334155;color:#94a3b8}
</style>
@endsection

@push('scripts')
<script>
function expensesPage() {
    return {
        items: [],
        loading: true,
        exporting: false,
        search: '',
        statusFilter: '',
        fromDate: '',
        toDate: '',
        page: 1,
        meta: { total: 0, from: 0, to: 0, last_page: 1 },
        stats: { total_count: 0, total_amount: 0 },

        get pageNumbers() {
            const total = this.meta.last_page;
            const cur = this.page;
            if (total <= 7) return Array.from({length: total}, (_, i) => i + 1);
            const pages = new Set([1, total, cur, cur-1, cur+1].filter(p => p >= 1 && p <= total));
            return [...pages].sort((a,b) => a-b);
        },

        filterParams() {
            const params = new URLSearchParams();
            if (this.search)     params.set('search', this.search);
            if (this.statusFilter) params.set('status', this.statusFilter);
            if (this.fromDate)   params.set('from_date', this.fromDate);
            if (this.toDate)     params.set('to_date', this.toDate);
            return params;
        },

        async init() {
            window.addEventListener('branch-switched', () => { this.page = 1; this.load(); });
            await this.load();
        },
        async load() {
            this.loading = true;
            try {
                const params = this.filterParams();
                params.set('page', this.page);
                params.set('per_page', 20);
                const r = await apiFetch('/expenses?' + params);
                if (!r) return;
                const data = await r.json();
                this.items = data.data ?? data ?? [];
                this.meta = { total: data.total ?? this.items.length, from: data.from ?? 0, to: data.to ?? 0, last_page: data.last_page ?? 1 };
                if (data.stats) this.stats = data.stats;
            } catch (e) {
                toast('Failed to load expenses', 'error');
            } finally {
                this.loading = false;
            }
        },
        async exportFile(format) {
            this.exporting = true;
            showGlobalLoading('Preparing export…');
            try {
                const r = await apiFetch('/expenses/export/' + format + '?' + this.filterParams());
                if (!r.ok) { toast('Export failed', 'error'); return; }
                const blob = await r.blob();
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'expenses_' + new Date().toISOString().slice(0,10) + '.' + format;
                document.body.appendChild(a); a.click(); document.body.removeChild(a);
                URL.revokeObjectURL(url);
            } catch (e) {
                toast('Export failed', 'error');
            } finally {
                this.exporting = false;
                hideGlobalLoading();
            }
        },
        statusBadge(status) {
            const map = { pending: 'badge-warning', approved: 'badge-success', rejected: 'badge-danger' };
            return 'badge ' + (map[status] ?? 'badge-gray');
        }
    };
}
</script>
@endpush
