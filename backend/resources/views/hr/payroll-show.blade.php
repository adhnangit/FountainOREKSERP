@extends('layouts.app')
@section('title', 'Payroll Run')
@section('page-title', 'Payroll Run')
@section('page-desc', 'Review payslips for this period')
@php $sec = 'hr'; @endphp

@section('content')
<div x-data="payrollShowPage()" x-init="init()">

    <div x-show="loading" class="flex items-center justify-center py-20">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" x-cloak>
        <a href="{{ url('/hr/payroll') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mb-4 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Back to Payroll
        </a>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100" x-text="monthNames[run.month] + ' ' + run.year + ' — ' + (run.branch?.name ?? '')"></h2>
                <span class="badge mt-1 inline-block" :class="run.status === 'paid' ? 'badge-success' : 'badge-warning'" x-text="run.status"></span>
            </div>
            <div class="flex items-center gap-2">
                <button x-show="run.status === 'draft'" @click="regenerate()" class="btn-secondary" :disabled="working">Regenerate</button>
                <button x-show="run.status === 'draft'" @click="markPaid()" class="btn-primary" :disabled="working">Mark as Paid</button>
                <button x-show="run.status === 'draft'" @click="deleteRun()" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-red-50 text-red-600 hover:bg-red-100 transition-colors" :disabled="working">Delete</button>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="card p-4">
                <div class="text-xs text-gray-400 uppercase tracking-wide">Employees</div>
                <div class="text-xl font-bold mt-1" x-text="run.payslips?.length ?? 0"></div>
            </div>
            <div class="card p-4">
                <div class="text-xs text-gray-400 uppercase tracking-wide">Total Gross</div>
                <div class="text-xl font-bold mt-1" x-text="fmtMoney(totals.gross)"></div>
            </div>
            <div class="card p-4">
                <div class="text-xs text-gray-400 uppercase tracking-wide">Total Deductions</div>
                <div class="text-xl font-bold mt-1" x-text="fmtMoney(totals.deductions)"></div>
            </div>
            <div class="card p-4">
                <div class="text-xs text-gray-400 uppercase tracking-wide">Total Net Pay</div>
                <div class="text-xl font-bold mt-1 text-green-600" x-text="fmtMoney(totals.net)"></div>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800/40">
                        <tr>
                            <th class="table-hd">Employee</th>
                            <th class="table-hd text-right">Basic</th>
                            <th class="table-hd text-right">Allowances</th>
                            <th class="table-hd text-right">Deductions</th>
                            <th class="table-hd text-right">Unpaid Leave</th>
                            <th class="table-hd text-right">EPF (8%)</th>
                            <th class="table-hd text-right">Net Pay</th>
                            <th class="table-hd"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                        <template x-for="p in run.payslips" :key="p.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                                <td class="table-td">
                                    <a :href="BASE + '/hr/employees/' + p.employee_id" class="font-medium text-gray-900 dark:text-gray-100 hover:underline" x-text="[p.employee?.first_name, p.employee?.last_name].filter(Boolean).join(' ')"></a>
                                    <div class="text-xs text-gray-400" x-text="p.employee?.employee_code"></div>
                                </td>
                                <td class="table-td text-right tabular-nums" x-text="fmtMoney(p.basic_salary)"></td>
                                <td class="table-td text-right tabular-nums" x-text="fmtMoney(p.total_allowances)"></td>
                                <td class="table-td text-right tabular-nums" x-text="fmtMoney(p.total_deductions)"></td>
                                <td class="table-td text-right tabular-nums" :class="p.unpaid_leave_days > 0 ? 'text-amber-600' : ''" x-text="p.unpaid_leave_deduction > 0 ? fmtMoney(p.unpaid_leave_deduction) : '—'"></td>
                                <td class="table-td text-right tabular-nums" x-text="p.epf_employee > 0 ? fmtMoney(p.epf_employee) : '—'"></td>
                                <td class="table-td text-right font-semibold tabular-nums" x-text="fmtMoney(p.net_pay)"></td>
                                <td class="table-td"><a :href="API + '/hr/payslips/' + p.id + '/pdf'" target="_blank" class="text-indigo-600 hover:underline text-sm font-medium">PDF</a></td>
                            </tr>
                        </template>
                        <tr x-show="!(run.payslips?.length)">
                            <td colspan="8" class="text-center text-gray-400 py-16">No payslips in this run.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function payrollShowPage() {
    return {
        loading: true,
        working: false,
        run: {},
        monthNames: ['','January','February','March','April','May','June','July','August','September','October','November','December'],

        get id() { return window.location.pathname.split('/').filter(Boolean).pop(); },

        get totals() {
            const payslips = this.run.payslips ?? [];
            return {
                gross: payslips.reduce((s, p) => s + parseFloat(p.gross_pay ?? 0), 0),
                deductions: payslips.reduce((s, p) => s + parseFloat(p.total_deductions ?? 0) + parseFloat(p.unpaid_leave_deduction ?? 0) + parseFloat(p.epf_employee ?? 0), 0),
                net: payslips.reduce((s, p) => s + parseFloat(p.net_pay ?? 0), 0),
            };
        },

        async init() {
            await this.load();
        },

        async load() {
            this.loading = true;
            try {
                this.run = await apiFetch('/hr/payroll-runs/' + this.id).then(r => r.json());
            } catch (e) {
                toast('Failed to load payroll run', 'error');
            } finally {
                this.loading = false;
            }
        },

        async regenerate() {
            if (!confirm('Recompute every payslip in this run from current salary, components and attendance? Any manual changes will be lost.')) return;
            this.working = true;
            try {
                this.run = await apiFetch('/hr/payroll-runs/' + this.id + '/regenerate', { method: 'POST' }).then(r => r.json());
                toast('Payroll run regenerated.', 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to regenerate', 'error');
            } finally {
                this.working = false;
            }
        },

        async markPaid() {
            if (!confirm(`Mark this payroll run as paid? Total net pay: ${fmtMoney(this.totals.net)}. This locks it from further changes.`)) return;
            this.working = true;
            try {
                this.run = await apiFetch('/hr/payroll-runs/' + this.id + '/mark-paid', { method: 'POST', body: JSON.stringify({}) }).then(r => r.json());
                toast('Payroll run marked as paid.', 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to mark as paid', 'error');
            } finally {
                this.working = false;
            }
        },

        async deleteRun() {
            if (!confirm('Delete this draft payroll run and all its payslips?')) return;
            this.working = true;
            try {
                await apiFetch('/hr/payroll-runs/' + this.id, { method: 'DELETE' });
                toast('Payroll run deleted.', 'success');
                window.location.href = BASE + '/hr/payroll';
            } catch (e) {
                toast(e.message ?? 'Failed to delete', 'error');
                this.working = false;
            }
        },
    };
}
</script>
@endpush
