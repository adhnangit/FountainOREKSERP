@extends('layouts.app')
@section('title', 'Employee Detail')
@section('page-title', 'Employee Detail')
@section('page-desc', 'Staff profile, documents and history')
@php $sec = 'hr'; @endphp

@section('content')
<style>
.ed-hero{font-family:'Inter',sans-serif;border-radius:18px;background:linear-gradient(135deg,#0f172a,#1e3a5f,#2563eb);box-shadow:0 12px 32px rgba(15,23,42,.18);padding:26px 28px;color:#fff;position:relative;overflow:hidden}
.ed-hero::after{content:'';position:absolute;top:-60px;right:-60px;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.10),transparent 70%)}
.ed-avatar{width:56px;height:56px;border-radius:16px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;letter-spacing:-.02em;flex-shrink:0;backdrop-filter:blur(6px);object-fit:cover}
.ed-chip{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.16);padding:3px 11px;border-radius:20px;font-size:11.5px;font-weight:600;display:inline-flex;align-items:center;gap:5px;white-space:nowrap}
.ed-hdr-btn{border-radius:10px;padding:7px 14px;font-size:12.5px;font-family:inherit;font-weight:600;background:rgba(255,255,255,.14);color:#fff;border:1px solid rgba(255,255,255,.22);cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .15s;white-space:nowrap}
.ed-hdr-btn:hover{background:rgba(255,255,255,.24)}
.ed-hdr-btn-del{border-radius:10px;padding:7px 10px;font-family:inherit;background:rgba(239,68,68,.18);color:#fecaca;border:1px solid rgba(239,68,68,.3);cursor:pointer;display:flex;align-items:center;transition:background .15s}
.ed-hdr-btn-del:hover{background:rgba(239,68,68,.32);color:#fff}
.ed-tabs{display:flex;gap:4px;margin:22px 0 18px;background:#f1f5f9;padding:4px;border-radius:12px;width:fit-content}
.ed-tab{padding:8px 18px;font-size:13px;font-weight:600;border-radius:9px;color:#64748b;cursor:pointer;transition:all .15s;background:transparent;border:none;font-family:inherit}
.ed-tab.active{background:#fff;color:#1e293b;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.ed-info-row{display:flex;align-items:flex-start;gap:12px;padding:11px 0;border-bottom:1px solid #f1f5f9}
.ed-info-row:last-child{border-bottom:none}
.ed-info-icon{width:34px;height:34px;border-radius:10px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.ed-info-lbl{font-size:10.5px;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;font-weight:700}
.ed-info-val{font-size:14px;color:#1e293b;font-weight:500;margin-top:1px}
.ed-table thead th{background:#f8fafc;padding:11px 16px;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;font-weight:700;border-bottom:1px solid #e2e8f0}
.ed-table tbody td{padding:12px 16px;font-size:13.5px;border-bottom:1px solid #f1f5f9;color:#1e293b}
.ed-table tbody tr:hover{background:#f8fafc}
.dark .ed-tabs{background:#0f172a}
.dark .ed-tab{color:#94a3b8}
.dark .ed-tab.active{background:#1e293b;color:#e2e8f0}
.dark .ed-info-row{border-color:#1e293b}
.dark .ed-info-icon{background:#1e3a5f;color:#93c5fd}
.dark .ed-info-lbl{color:#64748b}
.dark .ed-info-val{color:#e2e8f0}
.dark .ed-table thead th{background:#1e293b;border-color:#334155;color:#64748b}
.dark .ed-table tbody td{border-color:#1e293b;color:#e2e8f0}
.dark .ed-table tbody tr:hover{background:#1e3351}
</style>
<div x-data="employeeShowPage()" x-init="init()">

    <div x-show="loading" class="flex items-center justify-center py-20">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" x-cloak>
        <a href="{{ url('/hr/employees') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mb-4 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Back to Employees
        </a>

        <!-- Hero -->
        <div class="ed-hero">
            <div class="flex items-start justify-between gap-4 relative" style="z-index:1">
                <div class="flex items-start gap-4 min-w-0">
                    <img x-show="emp.photo_path" :src="API + '/hr/employees/' + emp.id + '/photo'" class="ed-avatar" />
                    <div x-show="!emp.photo_path" class="ed-avatar" x-text="(emp.first_name ?? '?').charAt(0).toUpperCase()"></div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h1 class="text-2xl font-bold truncate" x-text="[emp.first_name, emp.last_name].filter(Boolean).join(' ')"></h1>
                            <span class="ed-chip" :style="statusChipStyle(emp.employment_status)">
                                <span x-text="(emp.employment_status ?? 'active').replace('_',' ')"></span>
                            </span>
                        </div>
                        <div class="flex items-center gap-2 mt-2.5 flex-wrap">
                            <span class="ed-chip" x-text="emp.employee_code"></span>
                            <span class="ed-chip" x-show="emp.designation" x-text="emp.designation?.name"></span>
                            <span class="ed-chip" x-show="emp.department" x-text="emp.department?.name"></span>
                            <span class="ed-chip" x-show="emp.branch" x-text="emp.branch?.name"></span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <button @click="openEdit()" class="ed-hdr-btn">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </button>
                    <button @click="deleteEmployee()" class="ed-hdr-btn-del" title="Delete employee">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="ed-tabs">
            <button @click="activeTab = 'overview'" class="ed-tab" :class="activeTab === 'overview' ? 'active' : ''">Overview</button>
            <button @click="activeTab = 'documents'" class="ed-tab" :class="activeTab === 'documents' ? 'active' : ''">Documents <span x-show="emp.documents?.length" x-text="'(' + emp.documents.length + ')'"></span></button>
            <button @click="activeTab = 'history'" class="ed-tab" :class="activeTab === 'history' ? 'active' : ''">History</button>
            <button @click="activeTab = 'payroll'; loadSalaryComponents()" class="ed-tab" :class="activeTab === 'payroll' ? 'active' : ''"
                    x-show="user?.roles?.includes('super_admin') || user?.roles?.includes('hr_admin')">Payroll</button>
            <button @click="activeTab = 'checklist'; loadChecklistTasks()" class="ed-tab" :class="activeTab === 'checklist' ? 'active' : ''">Onboarding / Offboarding</button>
            <button @click="activeTab = 'reports'" class="ed-tab" :class="activeTab === 'reports' ? 'active' : ''" x-show="emp.direct_reports?.length">Direct Reports <span x-text="'(' + emp.direct_reports.length + ')'"></span></button>
        </div>

        <!-- Overview Tab -->
        <div x-show="activeTab === 'overview'" class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            <div class="card p-6">
                <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-1">Personal &amp; Contact</h3>
                <div class="divide-y divide-gray-50 dark:divide-gray-800">
                    <div class="ed-info-row"><div class="ed-info-icon">📅</div><div><div class="ed-info-lbl">Date of Birth</div><div class="ed-info-val" x-text="fmtDate(emp.date_of_birth) "></div></div></div>
                    <div class="ed-info-row"><div class="ed-info-icon">⚧</div><div><div class="ed-info-lbl">Gender</div><div class="ed-info-val capitalize" x-text="emp.gender ?? '—'"></div></div></div>
                    <div class="ed-info-row"><div class="ed-info-icon">💍</div><div><div class="ed-info-lbl">Marital Status</div><div class="ed-info-val capitalize" x-text="emp.marital_status ?? '—'"></div></div></div>
                    <div class="ed-info-row"><div class="ed-info-icon">🪪</div><div><div class="ed-info-lbl">NIC / Passport</div><div class="ed-info-val" x-text="emp.nic_passport ?? '—'"></div></div></div>
                    <div class="ed-info-row"><div class="ed-info-icon">📞</div><div><div class="ed-info-lbl">Phone</div><div class="ed-info-val" x-text="[emp.phone, emp.phone2].filter(Boolean).join(' / ') || '—'"></div></div></div>
                    <div class="ed-info-row"><div class="ed-info-icon">✉️</div><div><div class="ed-info-lbl">Email</div><div class="ed-info-val" x-text="emp.personal_email ?? '—'"></div></div></div>
                    <div class="ed-info-row"><div class="ed-info-icon">📍</div><div><div class="ed-info-lbl">Address</div><div class="ed-info-val" x-text="[emp.address, emp.city, emp.district].filter(Boolean).join(', ') || '—'"></div></div></div>
                </div>
            </div>

            <div class="space-y-5">
                <div class="card p-6">
                    <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-1">Employment</h3>
                    <div class="divide-y divide-gray-50 dark:divide-gray-800">
                        <div class="ed-info-row"><div class="ed-info-icon">📆</div><div><div class="ed-info-lbl">Join Date</div><div class="ed-info-val" x-text="fmtDate(emp.join_date)"></div></div></div>
                        <div class="ed-info-row"><div class="ed-info-icon">🧾</div><div><div class="ed-info-lbl">Employment Type</div><div class="ed-info-val capitalize" x-text="(emp.employment_type ?? '').replace('_',' ') || '—'"></div></div></div>
                        <div class="ed-info-row"><div class="ed-info-icon">⏳</div><div><div class="ed-info-lbl">Probation Period</div><div class="ed-info-val" x-text="emp.probation_period_months ? emp.probation_period_months + ' months' : '—'"></div></div></div>
                        <div class="ed-info-row"><div class="ed-info-icon">✅</div><div><div class="ed-info-lbl">Confirmation Date</div><div class="ed-info-val" x-text="fmtDate(emp.confirmation_date)"></div></div></div>
                        <div class="ed-info-row"><div class="ed-info-icon">🧑‍💼</div><div><div class="ed-info-lbl">Reporting Manager</div><div class="ed-info-val" x-text="emp.reporting_manager ? [emp.reporting_manager.first_name, emp.reporting_manager.last_name].filter(Boolean).join(' ') : '—'"></div></div></div>
                    </div>
                </div>

                <div class="card p-6">
                    <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-1">Emergency Contact</h3>
                    <div class="divide-y divide-gray-50 dark:divide-gray-800">
                        <div class="ed-info-row"><div class="ed-info-icon">🧍</div><div><div class="ed-info-lbl">Name</div><div class="ed-info-val" x-text="emp.emergency_contact_name ?? '—'"></div></div></div>
                        <div class="ed-info-row"><div class="ed-info-icon">🔗</div><div><div class="ed-info-lbl">Relationship</div><div class="ed-info-val" x-text="emp.emergency_contact_relationship ?? '—'"></div></div></div>
                        <div class="ed-info-row"><div class="ed-info-icon">📞</div><div><div class="ed-info-lbl">Phone</div><div class="ed-info-val" x-text="emp.emergency_contact_phone ?? '—'"></div></div></div>
                    </div>
                </div>

                <div class="card p-6" x-show="user?.roles?.includes('super_admin') || user?.roles?.includes('hr_admin')">
                    <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-1">Bank Details</h3>
                    <div class="divide-y divide-gray-50 dark:divide-gray-800">
                        <div class="ed-info-row"><div class="ed-info-icon">🏦</div><div><div class="ed-info-lbl">Bank / Branch</div><div class="ed-info-val" x-text="[emp.bank_name, emp.bank_branch].filter(Boolean).join(' — ') || '—'"></div></div></div>
                        <div class="ed-info-row"><div class="ed-info-icon">💳</div><div><div class="ed-info-lbl">Account</div><div class="ed-info-val" x-text="[emp.bank_account_name, emp.bank_account_number].filter(Boolean).join(' / ') || '—'"></div></div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Documents Tab -->
        <div x-show="activeTab === 'documents'">
            <div class="flex justify-end mb-3">
                <button @click="showDocModal = true" class="btn-primary inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Upload Document
                </button>
            </div>
            <div class="card p-0 overflow-visible rounded-2xl">
                <div class="overflow-x-auto">
                    <table class="ed-table min-w-full">
                        <thead><tr><th>Title</th><th>Type</th><th>Expiry</th><th>Uploaded By</th><th>Actions</th></tr></thead>
                        <tbody>
                            <template x-for="doc in (emp.documents ?? [])" :key="doc.id">
                                <tr>
                                    <td class="font-medium" x-text="doc.title"></td>
                                    <td x-text="doc.document_type"></td>
                                    <td>
                                        <span x-text="fmtDate(doc.expiry_date) ?? '—'"
                                              :class="isExpiringSoon(doc.expiry_date) ? 'text-red-600 font-semibold' : ''"></span>
                                    </td>
                                    <td x-text="doc.uploaded_by?.name ?? '—'"></td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <a :href="API + '/hr/documents/' + doc.id + '/stream'" target="_blank" class="text-indigo-600 hover:underline text-sm font-medium">View</a>
                                            <button @click="deleteDocument(doc)" class="text-red-500 hover:text-red-700 text-sm font-medium">Delete</button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="!(emp.documents?.length)"><td colspan="5" class="text-center text-gray-400 py-10">No documents uploaded.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- History Tab -->
        <div x-show="activeTab === 'history'">
            <div class="card p-0 overflow-visible rounded-2xl">
                <div class="overflow-x-auto">
                    <table class="ed-table min-w-full">
                        <thead><tr><th>Date</th><th>Field</th><th>From</th><th>To</th><th>Changed By</th></tr></thead>
                        <tbody>
                            <template x-for="h in (emp.history ?? [])" :key="h.id">
                                <tr>
                                    <td x-text="fmtDate(h.effective_date)"></td>
                                    <td class="capitalize" x-text="h.field_changed.replace(/_/g,' ')"></td>
                                    <td x-text="h.old_value ?? '—'"></td>
                                    <td class="font-medium" x-text="h.new_value ?? '—'"></td>
                                    <td x-text="h.changed_by?.name ?? '—'"></td>
                                </tr>
                            </template>
                            <tr x-show="!(emp.history?.length)"><td colspan="5" class="text-center text-gray-400 py-10">No history recorded.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Payroll Tab -->
        <div x-show="activeTab === 'payroll'" class="space-y-5">
            <div class="card p-6">
                <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-3">Compensation</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div><div class="ed-info-lbl">Basic Salary</div><div class="ed-info-val" x-text="emp.basic_salary ? fmtMoney(emp.basic_salary) : '—'"></div></div>
                    <div><div class="ed-info-lbl">EPF / ETF</div><div class="ed-info-val" x-text="emp.epf_etf_applicable !== false ? 'Applicable' : 'Not applicable'"></div></div>
                </div>
            </div>

            <div class="card p-0 overflow-visible rounded-2xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Recurring Allowances &amp; Deductions</h3>
                    <button @click="showComponentModal = true" class="btn-primary text-xs px-3 py-1.5">Add Component</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="ed-table min-w-full">
                        <thead><tr><th>Name</th><th>Type</th><th class="text-right">Amount</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            <template x-for="c in salaryComponents" :key="c.id">
                                <tr>
                                    <td class="font-medium" x-text="c.name"></td>
                                    <td class="capitalize" x-text="c.type"></td>
                                    <td class="text-right tabular-nums" x-text="fmtMoney(c.amount)"></td>
                                    <td>
                                        <span class="badge" :class="c.is_active ? 'badge-success' : 'badge-gray'" x-text="c.is_active ? 'Active' : 'Inactive'"></span>
                                    </td>
                                    <td><button @click="deleteSalaryComponent(c)" class="text-red-500 hover:text-red-700 text-sm">Delete</button></td>
                                </tr>
                            </template>
                            <tr x-show="!salaryComponents.length"><td colspan="5" class="text-center text-gray-400 py-10">No recurring allowances or deductions set up.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card p-0 overflow-visible rounded-2xl">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Payslip History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="ed-table min-w-full">
                        <thead><tr><th>Period</th><th class="text-right">Net Pay</th><th>Status</th><th></th></tr></thead>
                        <tbody>
                            <template x-for="p in (emp.payslips ?? [])" :key="p.id">
                                <tr>
                                    <td x-text="monthNames[p.payroll_run?.month] + ' ' + p.payroll_run?.year"></td>
                                    <td class="text-right tabular-nums" x-text="fmtMoney(p.net_pay)"></td>
                                    <td><span class="badge" :class="p.payroll_run?.status === 'paid' ? 'badge-success' : 'badge-warning'" x-text="p.payroll_run?.status"></span></td>
                                    <td><a :href="API + '/hr/payslips/' + p.id + '/pdf'" target="_blank" class="text-indigo-600 hover:underline text-sm font-medium">PDF</a></td>
                                </tr>
                            </template>
                            <tr x-show="!(emp.payslips?.length)"><td colspan="4" class="text-center text-gray-400 py-10">No payslips generated yet.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Salary Component Modal -->
        <div x-show="showComponentModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showComponentModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Add Component</h3>
                    <button @click="showComponentModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="label">Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="componentForm.name" class="input w-full" placeholder="e.g. Transport Allowance" />
                    </div>
                    <div>
                        <label class="label">Type</label>
                        <select x-model="componentForm.type" class="input w-full">
                            <option value="allowance">Allowance</option>
                            <option value="deduction">Deduction</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Amount (Rs.)</label>
                        <input type="number" step="0.01" min="0" x-model.number="componentForm.amount" class="input w-full" />
                    </div>
                    <div x-show="componentError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="componentError"></div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showComponentModal = false" class="btn-secondary">Cancel</button>
                        <button @click="saveSalaryComponent()" class="btn-primary" :disabled="componentSaving" x-text="componentSaving ? 'Saving…' : 'Add'"></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Onboarding / Offboarding Tab -->
        <div x-show="activeTab === 'checklist'" class="space-y-5">
            <template x-for="type in ['onboarding', 'offboarding']" :key="type">
                <div class="card p-0 overflow-visible rounded-2xl">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider capitalize" x-text="type"></h3>
                        <div class="flex items-center gap-2">
                            <select x-model="templatePick[type]" class="input text-xs w-auto">
                                <option value="">— Apply Template —</option>
                                <template x-for="t in templates.filter(t => t.type === type)" :key="t.id"><option :value="t.id" x-text="t.name"></option></template>
                            </select>
                            <button @click="applyTemplate(type)" class="btn-secondary text-xs px-3 py-1.5" :disabled="!templatePick[type]">Apply</button>
                            <button @click="openAddTask(type)" class="btn-primary text-xs px-3 py-1.5">+ Task</button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="ed-table min-w-full">
                            <thead><tr><th>Task</th><th>Due</th><th>Status</th><th></th></tr></thead>
                            <tbody>
                                <template x-for="task in checklistTasks.filter(t => t.type === type)" :key="task.id">
                                    <tr>
                                        <td class="font-medium" :class="task.status === 'completed' ? 'line-through text-gray-400' : ''" x-text="task.title"></td>
                                        <td x-text="fmtDate(task.due_date)"></td>
                                        <td>
                                            <label class="flex items-center gap-1.5 cursor-pointer text-xs">
                                                <input type="checkbox" :checked="task.status === 'completed'" @change="toggleTask(task)" class="rounded text-indigo-600" />
                                                <span x-text="task.status === 'completed' ? 'Done' : 'Pending'"></span>
                                            </label>
                                        </td>
                                        <td><button @click="deleteTask(task)" class="text-red-500 hover:text-red-700 text-sm">Delete</button></td>
                                    </tr>
                                </template>
                                <tr x-show="!checklistTasks.filter(t => t.type === type).length"><td colspan="4" class="text-center text-gray-400 py-10">No tasks yet.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </template>
        </div>

        <!-- Add Task Modal -->
        <div x-show="showTaskModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showTaskModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 capitalize" x-text="taskForm.type + ' Task'"></h3>
                    <button @click="showTaskModal = false" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="label">Title <span class="text-red-500">*</span></label>
                        <input type="text" x-model="taskForm.title" class="input w-full" />
                    </div>
                    <div>
                        <label class="label">Due Date</label>
                        <input type="date" x-model="taskForm.due_date" class="input w-full" />
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="showTaskModal = false" class="btn-secondary">Cancel</button>
                        <button @click="saveTask()" class="btn-primary" :disabled="taskSaving" x-text="taskSaving ? 'Saving…' : 'Add'"></button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Direct Reports Tab -->
        <div x-show="activeTab === 'reports'">
            <div class="card p-0 overflow-visible rounded-2xl">
                <div class="overflow-x-auto">
                    <table class="ed-table min-w-full">
                        <thead><tr><th>Name</th><th>Code</th><th></th></tr></thead>
                        <tbody>
                            <template x-for="r in (emp.direct_reports ?? [])" :key="r.id">
                                <tr>
                                    <td class="font-medium" x-text="[r.first_name, r.last_name].filter(Boolean).join(' ')"></td>
                                    <td x-text="r.employee_code"></td>
                                    <td><a :href="BASE + '/hr/employees/' + r.id" class="text-indigo-600 hover:underline text-sm font-medium">View</a></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Document Modal -->
    <div x-show="showDocModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showDocModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Upload Document</h3>
                <button @click="showDocModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="label">Title <span class="text-red-500">*</span></label>
                    <input type="text" x-model="docForm.title" class="input w-full" placeholder="e.g. NIC Copy" />
                </div>
                <div>
                    <label class="label">Document Type <span class="text-red-500">*</span></label>
                    <input type="text" x-model="docForm.document_type" class="input w-full" placeholder="e.g. identification, contract, certificate" />
                </div>
                <div>
                    <label class="label">Expiry Date</label>
                    <input type="date" x-model="docForm.expiry_date" class="input w-full" />
                </div>
                <div>
                    <label class="label">File <span class="text-red-500">*</span></label>
                    <input type="file" @change="docFile = $event.target.files[0]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="input w-full" />
                </div>
                <div>
                    <label class="label">Notes</label>
                    <textarea x-model="docForm.notes" rows="2" class="input w-full resize-none"></textarea>
                </div>
                <div x-show="docError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="docError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showDocModal = false" class="btn-secondary">Cancel</button>
                    <button @click="uploadDocument()" class="btn-primary" :disabled="docSaving" x-text="docSaving ? 'Uploading…' : 'Upload'"></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showEdit = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Edit Employee</h3>
                <button @click="showEdit = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-5">
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="label text-xs">First Name <span class="text-red-500">*</span></label><input type="text" x-model="editForm.first_name" class="input" /></div>
                    <div><label class="label text-xs">Last Name</label><input type="text" x-model="editForm.last_name" class="input" /></div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="label text-xs">Phone</label><input type="tel" x-model="editForm.phone" class="input" /></div>
                    <div><label class="label text-xs">Email</label><input type="email" x-model="editForm.personal_email" class="input" /></div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="label text-xs">Branch</label>
                        <select x-model="editForm.branch_id" class="input">
                            <option value="">— None —</option>
                            <template x-for="b in branches" :key="b.id"><option :value="b.id" x-text="b.name"></option></template>
                        </select>
                    </div>
                    <div>
                        <label class="label text-xs">Department</label>
                        <select x-model="editForm.department_id" class="input">
                            <option value="">— None —</option>
                            <template x-for="d in flatDepartments" :key="d.id"><option :value="d.id" x-text="d.name"></option></template>
                        </select>
                    </div>
                    <div>
                        <label class="label text-xs">Designation</label>
                        <select x-model="editForm.designation_id" class="input">
                            <option value="">— None —</option>
                            <template x-for="d in designations" :key="d.id"><option :value="d.id" x-text="d.name"></option></template>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label text-xs">Reporting Manager</label>
                        <select x-model="editForm.reporting_manager_id" class="input">
                            <option value="">— None —</option>
                            <template x-for="m in employees.filter(e => e.id !== emp.id)" :key="m.id"><option :value="m.id" x-text="[m.first_name, m.last_name].filter(Boolean).join(' ')"></option></template>
                        </select>
                    </div>
                    <div>
                        <label class="label text-xs">Employment Status</label>
                        <select x-model="editForm.employment_status" class="input">
                            <option value="active">Active</option>
                            <option value="on_leave">On Leave</option>
                            <option value="suspended">Suspended</option>
                            <option value="terminated">Terminated</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4" x-show="user?.roles?.includes('super_admin') || user?.roles?.includes('hr_admin')">
                    <div>
                        <label class="label text-xs">Basic Salary (Rs.)</label>
                        <input type="number" step="0.01" min="0" x-model.number="editForm.basic_salary" class="input" />
                    </div>
                    <div class="flex items-end pb-2.5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="editForm.epf_etf_applicable" class="rounded text-indigo-600" />
                            <span class="text-sm text-gray-700 dark:text-gray-300">EPF/ETF applicable</span>
                        </label>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <img x-show="emp.photo_path && !removePhoto" :src="API + '/hr/employees/' + emp.id + '/photo'" class="w-12 h-12 rounded-lg object-cover" />
                    <input type="file" accept="image/*" @change="editPhotoFile = $event.target.files[0]" class="text-xs" />
                    <label class="flex items-center gap-1.5 text-xs text-gray-500" x-show="emp.photo_path">
                        <input type="checkbox" x-model="removePhoto" class="rounded text-indigo-600" /> Remove photo
                    </label>
                </div>
                <div x-show="editError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="editError"></div>
            </div>
            <div class="flex justify-end gap-3 px-6 pb-6">
                <button @click="showEdit = false" class="btn-secondary">Cancel</button>
                <button @click="saveEdit()" class="btn-primary" :disabled="editSaving" x-text="editSaving ? 'Saving…' : 'Save Changes'"></button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function employeeShowPage() {
    return {
        loading: true,
        activeTab: 'overview',
        emp: {},
        branches: [],
        departments: [],
        designations: [],
        employees: [],
        user: JSON.parse(localStorage.getItem('medri_user') || '{}'),
        showEdit: false,
        editSaving: false,
        editError: '',
        editForm: {},
        editPhotoFile: null,
        removePhoto: false,
        showDocModal: false,
        docSaving: false,
        docError: '',
        docFile: null,
        docForm: {},
        salaryComponents: [],
        showComponentModal: false,
        componentSaving: false,
        componentError: '',
        componentForm: { name: '', type: 'allowance', amount: '' },
        monthNames: ['','January','February','March','April','May','June','July','August','September','October','November','December'],
        checklistTasks: [],
        templates: [],
        templatePick: { onboarding: '', offboarding: '' },
        showTaskModal: false,
        taskSaving: false,
        taskForm: {},

        get id() { return window.location.pathname.split('/').filter(Boolean).pop(); },

        get flatDepartments() {
            const flat = [];
            const walk = (list, prefix = '') => list.forEach(d => {
                flat.push({ id: d.id, name: prefix + d.name });
                if (d.children?.length) walk(d.children, prefix + '— ');
            });
            walk(this.departments);
            return flat;
        },

        statusChipStyle(status) {
            const map = {
                active: 'background:rgba(34,197,94,.2);border-color:rgba(34,197,94,.3)',
                on_leave: 'background:rgba(245,158,11,.2);border-color:rgba(245,158,11,.3)',
                suspended: 'background:rgba(239,68,68,.2);border-color:rgba(239,68,68,.3)',
                terminated: 'background:rgba(100,116,139,.3);border-color:rgba(100,116,139,.4)',
            };
            return map[status ?? 'active'] ?? map.active;
        },

        isExpiringSoon(date) {
            if (!date) return false;
            const days = (new Date(date) - new Date()) / 86400000;
            return days < 30;
        },

        async init() {
            try {
                const [empData, bd, dd, gd, ed] = await Promise.all([
                    apiFetch('/hr/employees/' + this.id).then(r => r.json()),
                    apiFetch('/branches').then(r => r.json()),
                    apiFetch('/hr/departments').then(r => r.json()),
                    apiFetch('/hr/designations').then(r => r.json()),
                    apiFetch('/hr/employees?per_page=500').then(r => r.json()),
                ]);
                this.emp = empData;
                this.emp.direct_reports = empData.direct_reports ?? [];
                this.branches = bd.data ?? bd ?? [];
                this.departments = dd ?? [];
                this.designations = gd ?? [];
                this.employees = ed.data ?? ed ?? [];
            } catch (e) {
                toast('Failed to load employee', 'error');
            } finally {
                this.loading = false;
            }
        },

        async reload() {
            const empData = await apiFetch('/hr/employees/' + this.id).then(r => r.json());
            this.emp = empData;
        },

        openEdit() {
            this.editError = '';
            this.editPhotoFile = null;
            this.removePhoto = false;
            this.editForm = {
                first_name: this.emp.first_name ?? '',
                last_name: this.emp.last_name ?? '',
                phone: this.emp.phone ?? '',
                personal_email: this.emp.personal_email ?? '',
                branch_id: this.emp.branch_id ?? '',
                department_id: this.emp.department_id ?? '',
                designation_id: this.emp.designation_id ?? '',
                reporting_manager_id: this.emp.reporting_manager_id ?? '',
                employment_status: this.emp.employment_status ?? 'active',
                basic_salary: this.emp.basic_salary ?? '',
                epf_etf_applicable: this.emp.epf_etf_applicable !== false,
            };
            this.showEdit = true;
        },

        async saveEdit() {
            if (!this.editForm.first_name) { this.editError = 'First name is required.'; return; }
            this.editSaving = true;
            this.editError = '';
            try {
                const fd = new FormData();
                fd.append('_method', 'PUT');
                Object.entries(this.editForm).forEach(([k, v]) => {
                    fd.append(k, typeof v === 'boolean' ? (v ? '1' : '0') : (v ?? ''));
                });
                if (this.editPhotoFile) fd.append('photo', this.editPhotoFile);
                if (this.removePhoto) fd.append('remove_photo', '1');
                await apiFetch('/hr/employees/' + this.id, { method: 'POST', body: fd });
                toast('Employee updated.', 'success');
                this.showEdit = false;
                await this.reload();
            } catch (e) {
                this.editError = e.message ?? 'Failed to save.';
            } finally {
                this.editSaving = false;
            }
        },

        async deleteEmployee() {
            if (!confirm(`Delete ${this.emp.first_name}? This cannot be undone.`)) return;
            try {
                await apiFetch('/hr/employees/' + this.id, { method: 'DELETE' });
                toast('Employee deleted.', 'success');
                window.location.href = BASE + '/hr/employees';
            } catch (e) {
                toast(e.message ?? 'Failed to delete employee', 'error');
            }
        },

        async uploadDocument() {
            if (!this.docForm.title || !this.docForm.document_type || !this.docFile) {
                this.docError = 'Title, document type and file are required.';
                return;
            }
            this.docSaving = true;
            this.docError = '';
            try {
                const fd = new FormData();
                Object.entries(this.docForm).forEach(([k, v]) => { if (v) fd.append(k, v); });
                fd.append('file', this.docFile);
                await apiFetch('/hr/employees/' + this.id + '/documents', { method: 'POST', body: fd });
                toast('Document uploaded.', 'success');
                this.showDocModal = false;
                this.docForm = {};
                this.docFile = null;
                await this.reload();
            } catch (e) {
                this.docError = e.message ?? 'Failed to upload document.';
            } finally {
                this.docSaving = false;
            }
        },

        async deleteDocument(doc) {
            if (!confirm(`Delete "${doc.title}"?`)) return;
            try {
                await apiFetch('/hr/documents/' + doc.id, { method: 'DELETE' });
                toast('Document deleted.', 'success');
                await this.reload();
            } catch (e) {
                toast(e.message ?? 'Failed to delete document', 'error');
            }
        },

        async loadSalaryComponents() {
            try {
                this.salaryComponents = await apiFetch('/hr/employees/' + this.id + '/salary-components').then(r => r.json());
            } catch (e) {
                toast('Failed to load salary components', 'error');
            }
        },

        async saveSalaryComponent() {
            if (!this.componentForm.name || !this.componentForm.amount) {
                this.componentError = 'Name and amount are required.';
                return;
            }
            this.componentSaving = true;
            this.componentError = '';
            try {
                await apiFetch('/hr/employees/' + this.id + '/salary-components', {
                    method: 'POST',
                    body: JSON.stringify(this.componentForm),
                });
                toast('Component added.', 'success');
                this.showComponentModal = false;
                this.componentForm = { name: '', type: 'allowance', amount: '' };
                await this.loadSalaryComponents();
            } catch (e) {
                this.componentError = e.message ?? 'Failed to add component.';
            } finally {
                this.componentSaving = false;
            }
        },

        async deleteSalaryComponent(c) {
            if (!confirm(`Delete "${c.name}"?`)) return;
            try {
                await apiFetch('/hr/salary-components/' + c.id, { method: 'DELETE' });
                toast('Component deleted.', 'success');
                await this.loadSalaryComponents();
            } catch (e) {
                toast(e.message ?? 'Failed to delete component', 'error');
            }
        },

        async loadChecklistTasks() {
            try {
                const [tasks, templates] = await Promise.all([
                    apiFetch('/hr/employees/' + this.id + '/checklist-tasks').then(r => r.json()),
                    apiFetch('/hr/checklist-templates').then(r => r.json()),
                ]);
                this.checklistTasks = tasks;
                this.templates = templates;
            } catch (e) {
                toast('Failed to load checklist', 'error');
            }
        },

        async applyTemplate(type) {
            if (!this.templatePick[type]) return;
            try {
                const res = await apiFetch('/hr/employees/' + this.id + '/checklist-tasks/apply-template', {
                    method: 'POST',
                    body: JSON.stringify({ template_id: this.templatePick[type] }),
                }).then(r => r.json());
                toast(`${res.created} task(s) added.`, 'success');
                this.templatePick[type] = '';
                await this.loadChecklistTasks();
            } catch (e) {
                toast(e.message ?? 'Failed to apply template', 'error');
            }
        },

        openAddTask(type) {
            this.taskForm = { type, title: '', due_date: '' };
            this.showTaskModal = true;
        },

        async saveTask() {
            if (!this.taskForm.title) { toast('Title is required', 'error'); return; }
            this.taskSaving = true;
            try {
                await apiFetch('/hr/employees/' + this.id + '/checklist-tasks', { method: 'POST', body: JSON.stringify(this.taskForm) });
                toast('Task added.', 'success');
                this.showTaskModal = false;
                await this.loadChecklistTasks();
            } catch (e) {
                toast(e.message ?? 'Failed to add task', 'error');
            } finally {
                this.taskSaving = false;
            }
        },

        async toggleTask(task) {
            const newStatus = task.status === 'completed' ? 'pending' : 'completed';
            try {
                await apiFetch('/hr/checklist-tasks/' + task.id, { method: 'PUT', body: JSON.stringify({ status: newStatus }) });
                task.status = newStatus;
            } catch (e) {
                toast(e.message ?? 'Failed to update task', 'error');
            }
        },

        async deleteTask(task) {
            if (!confirm(`Remove "${task.title}"?`)) return;
            try {
                await apiFetch('/hr/checklist-tasks/' + task.id, { method: 'DELETE' });
                await this.loadChecklistTasks();
            } catch (e) {
                toast(e.message ?? 'Failed to remove task', 'error');
            }
        },
    };
}
</script>
@endpush
