@extends('layouts.app')
@section('title', 'Payroll')
@section('page-title', 'Payroll')
@section('page-desc', 'Generate and track monthly payroll runs')
@php $sec = 'hr'; @endphp

@section('content')
<div x-data="payrollPage()" x-init="init()">

    <div class="flex flex-wrap items-center gap-2 mb-4">
        <select x-model.number="filterYear" @change="load()" class="input w-auto">
            <template x-for="y in yearOptions" :key="y"><option :value="y" x-text="y"></option></template>
        </select>
        <select x-model="filterStatus" @change="load()" class="input w-auto">
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="paid">Paid</option>
        </select>
        <div class="flex-1"></div>
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Payroll Run
        </button>
    </div>

    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/40">
                <tr>
                    <th class="table-hd">Period</th>
                    <th class="table-hd">Branch</th>
                    <th class="table-hd text-center">Employees</th>
                    <th class="table-hd">Status</th>
                    <th class="table-hd">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                <template x-for="run in runs" :key="run.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                        <td class="table-td font-medium text-gray-900 dark:text-gray-100" x-text="monthNames[run.month] + ' ' + run.year"></td>
                        <td class="table-td text-gray-500" x-text="run.branch?.name"></td>
                        <td class="table-td text-center" x-text="run.payslips_count"></td>
                        <td class="table-td">
                            <span class="badge" :class="run.status === 'paid' ? 'badge-success' : 'badge-warning'" x-text="run.status"></span>
                        </td>
                        <td class="table-td">
                            <a :href="BASE + '/hr/payroll/' + run.id" class="text-indigo-600 hover:underline text-sm font-medium">View</a>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && runs.length === 0">
                    <td colspan="5" class="text-center text-gray-400 py-16">No payroll runs yet.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- New Run Modal -->
    <div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showCreate = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">New Payroll Run</h3>
                <button @click="showCreate = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="submitCreate()" class="p-6 space-y-4">
                <div>
                    <label class="label">Branch <span class="text-red-500">*</span></label>
                    <select x-model="form.branch_id" class="input w-full" required>
                        <option value="">— Select —</option>
                        <template x-for="b in branches" :key="b.id"><option :value="b.id" x-text="b.name"></option></template>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Month <span class="text-red-500">*</span></label>
                        <select x-model.number="form.month" class="input w-full">
                            <template x-for="(m, i) in monthNames.slice(1)" :key="i"><option :value="i + 1" x-text="m"></option></template>
                        </select>
                    </div>
                    <div>
                        <label class="label">Year <span class="text-red-500">*</span></label>
                        <input type="number" x-model.number="form.year" class="input w-full" required />
                    </div>
                </div>
                <p class="text-xs text-gray-400">This will generate a draft payslip for every active employee in the selected branch, using their current salary, active allowances/deductions and this month's attendance/leave records.</p>
                <div x-show="createError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="createError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showCreate = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="creating" x-text="creating ? 'Generating…' : 'Generate Run'"></button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function payrollPage() {
    return {
        runs: [],
        branches: [],
        loading: true,
        filterYear: new Date().getFullYear(),
        filterStatus: '',
        yearOptions: Array.from({length: 4}, (_, i) => new Date().getFullYear() - 1 + i),
        monthNames: ['','January','February','March','April','May','June','July','August','September','October','November','December'],
        showCreate: false,
        creating: false,
        createError: '',
        form: {},

        async init() {
            try {
                const bd = await apiFetch('/branches').then(r => r.json());
                this.branches = bd.data ?? bd ?? [];
            } catch (_) {}
            await this.load();
        },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ year: this.filterYear });
                if (this.filterStatus) params.set('status', this.filterStatus);
                this.runs = await apiFetch('/hr/payroll-runs?' + params.toString()).then(r => r.json());
            } catch (e) {
                toast('Failed to load payroll runs', 'error');
            } finally {
                this.loading = false;
            }
        },

        openCreate() {
            const u = JSON.parse(localStorage.getItem('medri_user') || '{}');
            const stored = localStorage.getItem('medri_branch');
            const bid = (stored && stored !== 'all') ? stored : (u.default_branch_id ?? '');
            const now = new Date();
            this.form = { branch_id: bid, month: now.getMonth() + 1, year: now.getFullYear() };
            this.createError = '';
            this.showCreate = true;
        },

        async submitCreate() {
            if (!this.form.branch_id) { this.createError = 'Select a branch.'; return; }
            this.creating = true;
            this.createError = '';
            try {
                const run = await apiFetch('/hr/payroll-runs', { method: 'POST', body: JSON.stringify(this.form) }).then(r => r.json());
                toast('Payroll run generated.', 'success');
                window.location.href = BASE + '/hr/payroll/' + run.id;
            } catch (e) {
                this.createError = e.message ?? 'Failed to generate payroll run.';
            } finally {
                this.creating = false;
            }
        },
    };
}
</script>
@endpush
