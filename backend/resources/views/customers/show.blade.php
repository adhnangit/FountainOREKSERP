@extends('layouts.app')
@section('title', 'Customer Detail')
@section('page-title', 'Customer Detail')
@section('page-desc', 'Customer information, invoices and ledger')

@section('content')
<style>
.cd-hero{font-family:'Inter',sans-serif;border-radius:18px;background:linear-gradient(135deg,#0f172a,#1e3a5f,#2563eb);box-shadow:0 12px 32px rgba(15,23,42,.18);padding:26px 28px;color:#fff;position:relative;overflow:hidden}
.cd-hero::after{content:'';position:absolute;top:-60px;right:-60px;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.10),transparent 70%)}
.cd-avatar{width:56px;height:56px;border-radius:16px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;letter-spacing:-.02em;flex-shrink:0;backdrop-filter:blur(6px)}
.cd-chip{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.16);padding:3px 11px;border-radius:20px;font-size:11.5px;font-weight:600;display:inline-flex;align-items:center;gap:5px;white-space:nowrap}
.cd-hdr-btn{border-radius:10px;padding:7px 14px;font-size:12.5px;font-family:inherit;font-weight:600;background:rgba(255,255,255,.14);color:#fff;border:1px solid rgba(255,255,255,.22);cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .15s;white-space:nowrap}
.cd-hdr-btn:hover{background:rgba(255,255,255,.24)}
.cd-hdr-btn-del{border-radius:10px;padding:7px 10px;font-family:inherit;background:rgba(239,68,68,.18);color:#fecaca;border:1px solid rgba(239,68,68,.3);cursor:pointer;display:flex;align-items:center;transition:background .15s}
.cd-hdr-btn-del:hover{background:rgba(239,68,68,.32);color:#fff}
.cd-stat-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px;margin-top:16px}
.cd-stat-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px;box-shadow:0 1px 3px rgba(0,0,0,.03)}
.cd-stat-lbl{font-size:10.5px;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;font-weight:700;margin-bottom:7px}
.cd-stat-val{font-size:21px;font-weight:800;letter-spacing:-.01em;line-height:1.1}
.cd-meter{height:6px;border-radius:4px;background:#f1f5f9;overflow:hidden;margin-top:9px}
.cd-meter-fill{height:100%;border-radius:4px;transition:width .3s}
.cd-tabs{display:flex;gap:4px;margin:22px 0 18px;background:#f1f5f9;padding:4px;border-radius:12px;width:fit-content}
.cd-tab{padding:8px 18px;font-size:13px;font-weight:600;border-radius:9px;color:#64748b;cursor:pointer;transition:all .15s;background:transparent;border:none;font-family:inherit}
.cd-tab.active{background:#fff;color:#1e293b;box-shadow:0 1px 3px rgba(0,0,0,.08)}
.cd-info-row{display:flex;align-items:flex-start;gap:12px;padding:11px 0;border-bottom:1px solid #f1f5f9}
.cd-info-row:last-child{border-bottom:none}
.cd-info-icon{width:34px;height:34px;border-radius:10px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.cd-info-lbl{font-size:10.5px;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;font-weight:700}
.cd-info-val{font-size:14px;color:#1e293b;font-weight:500;margin-top:1px}
.cd-table thead th{background:#f8fafc;padding:11px 16px;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;font-weight:700;border-bottom:1px solid #e2e8f0}
.cd-table tbody td{padding:12px 16px;font-size:13.5px;border-bottom:1px solid #f1f5f9;color:#1e293b}
.cd-table tbody tr{transition:background .12s}
.cd-table tbody tr:hover{background:#f8fafc}
.dark .cd-stat-card{background:#1e293b;border-color:#334155}
.dark .cd-stat-val{color:#f1f5f9}
.dark .cd-tabs{background:#0f172a}
.dark .cd-tab{color:#94a3b8}
.dark .cd-tab.active{background:#1e293b;color:#e2e8f0}
.dark .cd-info-row{border-color:#1e293b}
.dark .cd-info-icon{background:#1e3a5f;color:#93c5fd}
.dark .cd-info-lbl{color:#64748b}
.dark .cd-info-val{color:#e2e8f0}
.dark .cd-table thead th{background:#1e293b;border-color:#334155;color:#64748b}
.dark .cd-table tbody td{border-color:#1e293b;color:#e2e8f0}
.dark .cd-table tbody tr:hover{background:#1e3351}
</style>
<div x-data="customerShowPage()" x-init="init()">

    <!-- Loading -->
    <div x-show="loading" class="flex items-center justify-center py-20">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" x-cloak>
        <a href="{{ url('/customers') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mb-4 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Back to Customers
        </a>

        <!-- Hero -->
        <div class="cd-hero">
            <div class="flex items-start justify-between gap-4 relative" style="z-index:1">
                <div class="flex items-start gap-4 min-w-0">
                    <div class="cd-avatar" x-text="(customer.name ?? '?').trim().charAt(0).toUpperCase()"></div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h1 class="text-2xl font-bold truncate" x-text="customer.name ?? ''"></h1>
                            <span class="cd-chip" :style="customer.is_active === false ? 'background:rgba(239,68,68,.22);border-color:rgba(239,68,68,.3)' : 'background:rgba(34,197,94,.2);border-color:rgba(34,197,94,.3)'">
                                <span class="w-1.5 h-1.5 rounded-full" :style="customer.is_active === false ? 'background:#fca5a5' : 'background:#86efac'"></span>
                                <span x-text="customer.is_active === false ? 'Inactive' : 'Active'"></span>
                            </span>
                            <span class="cd-chip" x-show="customer.is_walk_in" style="background:rgba(245,158,11,.2);border-color:rgba(245,158,11,.3)">Walk-in</span>
                        </div>
                        <div class="flex items-center gap-2 mt-2.5 flex-wrap">
                            <span class="cd-chip" x-show="customer.code"><span x-text="customer.code"></span></span>
                            <span class="cd-chip capitalize" x-show="customer.type" x-text="customer.type"></span>
                            <span class="cd-chip" x-show="customer.phone">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span x-text="customer.phone"></span>
                            </span>
                            <span class="cd-chip" x-show="customer.city || customer.district">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span x-text="[customer.city, customer.district].filter(Boolean).join(', ')"></span>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a :href="BASE + '/customers/' + id + '/bulk-payment'" class="cd-hdr-btn">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3v-3m-3 3v-3m9-8H4a1 1 0 00-1 1v10a1 1 0 001 1h16a1 1 0 001-1V6a1 1 0 00-1-1z"/></svg>
                        Bulk Payment
                    </a>
                    <button @click="openEdit()" class="cd-hdr-btn">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Edit
                    </button>
                    <button @click="deleteCustomer()" class="cd-hdr-btn-del" title="Delete customer">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </div>

            <!-- Stat cards -->
            <div class="cd-stat-row relative" style="z-index:1">
                <div class="cd-stat-card">
                    <div class="cd-stat-lbl">Balance Due</div>
                    <div class="flex items-end justify-between gap-2">
                        <div class="cd-stat-val" :style="(customer.balance ?? 0) > 0 ? 'color:#dc2626' : 'color:#16a34a'" x-text="fmtMoney(customer.balance ?? 0)"></div>
                        <button x-show="(customer.balance ?? 0) > 0" @click="openPayModal()"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold border border-green-200 bg-green-50 text-green-700 hover:bg-green-100 transition-colors flex-shrink-0">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m0-2c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Pay
                        </button>
                    </div>
                </div>
                <div class="cd-stat-card">
                    <div class="cd-stat-lbl">Credit Limit</div>
                    <div class="cd-stat-val text-gray-800 dark:text-gray-100" x-text="fmtMoney(customer.credit_limit ?? 0)"></div>
                </div>
                <div class="cd-stat-card">
                    <div class="cd-stat-lbl">Credit Utilization</div>
                    <div class="cd-stat-val text-gray-800 dark:text-gray-100" x-text="(customer.credit_utilization ?? 0).toFixed(0) + '%'"></div>
                    <div class="cd-meter">
                        <div class="cd-meter-fill" :style="`width:${Math.min(100, customer.credit_utilization ?? 0)}%;background:${(customer.credit_utilization ?? 0) >= 90 ? '#dc2626' : (customer.credit_utilization ?? 0) >= 70 ? '#f59e0b' : '#16a34a'}`"></div>
                    </div>
                </div>
                <div class="cd-stat-card" x-show="(customer.credit_balance ?? 0) > 0">
                    <div class="cd-stat-lbl">Credit on Account</div>
                    <div class="cd-stat-val" style="color:#2563eb" x-text="fmtMoney(customer.credit_balance ?? 0)"></div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="cd-tabs">
            <button @click="activeTab = 'overview'" class="cd-tab" :class="activeTab === 'overview' ? 'active' : ''">Overview</button>
            <button @click="activeTab = 'invoices'; loadInvoices()" class="cd-tab" :class="activeTab === 'invoices' ? 'active' : ''">Invoices</button>
            <button @click="activeTab = 'ledger'; loadLedger()" class="cd-tab" :class="activeTab === 'ledger' ? 'active' : ''">Ledger</button>
        </div>

        <!-- Overview Tab -->
        <div x-show="activeTab === 'overview'">
            <div class="card p-6">
                <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-1">Contact Information</h3>
                <div class="divide-y divide-gray-50 dark:divide-gray-800">
                    <div class="cd-info-row">
                        <div class="cd-info-icon"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg></div>
                        <div><div class="cd-info-lbl">Phone</div><div class="cd-info-val" x-text="customer.phone ?? '—'"></div></div>
                    </div>
                    <div class="cd-info-row">
                        <div class="cd-info-icon"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg></div>
                        <div><div class="cd-info-lbl">Email</div><div class="cd-info-val" x-text="customer.email ?? '—'"></div></div>
                    </div>
                    <div class="cd-info-row">
                        <div class="cd-info-icon"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
                        <div><div class="cd-info-lbl">Address</div><div class="cd-info-val" x-text="customer.address ?? '—'"></div></div>
                    </div>
                    <div class="cd-info-row">
                        <div class="cd-info-icon"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2M5 21H3m8-14h.01M11 11h.01M11 15h.01M7 7h.01M7 11h.01M7 15h.01M15 7h.01M15 11h.01M15 15h.01"/></svg></div>
                        <div><div class="cd-info-lbl">City / District</div><div class="cd-info-val" x-text="[customer.city, customer.district].filter(Boolean).join(' — ') || '—'"></div></div>
                    </div>
                    <div class="cd-info-row">
                        <div class="cd-info-icon"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
                        <div><div class="cd-info-lbl">Tax / NIC #</div><div class="cd-info-val" x-text="customer.tax_number ?? '—'"></div></div>
                    </div>
                    <div class="cd-info-row">
                        <div class="cd-info-icon"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m0-2c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                        <div><div class="cd-info-lbl">Credit Terms</div><div class="cd-info-val" x-text="(customer.payment_terms_days ?? customer.credit_days ?? 0) + ' days'"></div></div>
                    </div>
                </div>
            </div>

            <!-- Opening Balance Payment History -->
            <div class="card p-6 mt-5" x-show="obPayments.length > 0 || loadingObPayments">
                <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-3">Opening Balance Payment History</h3>
                <div x-show="loadingObPayments" class="flex items-center justify-center py-6">
                    <svg class="animate-spin w-5 h-5 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                </div>
                <div x-show="!loadingObPayments" class="overflow-x-auto -mx-6">
                    <table class="cd-table min-w-full">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Cheque / Ref</th>
                                <th>Status</th>
                                <th class="text-right">Amount</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="p in obPayments" :key="p.journal_entry_id">
                                <tr :class="p.reversed ? 'opacity-50' : ''">
                                    <td x-text="fmtDate(p.date)"></td>
                                    <td>
                                        <span x-show="p.cheque" class="font-mono text-xs text-gray-600 dark:text-gray-300" x-text="p.cheque?.cheque_number + ' — ' + p.cheque?.bank_name"></span>
                                        <span x-show="!p.cheque" class="text-gray-400 text-xs" x-text="p.description"></span>
                                    </td>
                                    <td>
                                        <span x-show="p.reversed" class="badge badge-gray">Reversed</span>
                                        <span x-show="!p.reversed && p.cheque" class="badge" :class="{
                                            'badge-warning': p.cheque?.status === 'in_hand',
                                            'badge-primary': p.cheque?.status === 'deposited',
                                            'badge-success': p.cheque?.status === 'cleared',
                                            'badge-danger':  p.cheque?.status === 'bounced',
                                        }" x-text="p.cheque?.status?.replace('_',' ')"></span>
                                        <span x-show="!p.reversed && !p.cheque" class="badge badge-success">Recorded</span>
                                    </td>
                                    <td class="text-right font-semibold tabular-nums" :class="p.reversed ? 'line-through' : ''" x-text="fmtMoney(p.amount)"></td>
                                    <td class="text-right">
                                        <button x-show="!p.reversed && (!p.cheque || p.cheque.status === 'in_hand')"
                                                @click="deleteObPayment(p)"
                                                class="text-gray-400 hover:text-red-600 transition-colors" title="Delete this payment">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                        <span x-show="p.reversed || (p.cheque && p.cheque.status !== 'in_hand')" class="text-gray-300 text-xs" title="Cheque already processed — reverse its status in Manage Cheque first">—</span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Record Payment Modal (against opening balance / general account, no invoice needed) -->
        <div x-show="showPayModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
            <div class="absolute inset-0" style="background:rgba(15,23,42,.65)" @click="showPayModal=false"></div>
            <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h5 class="text-lg font-bold text-gray-800 dark:text-gray-100">Record Payment</h5>
                        <p class="text-sm text-gray-400 mt-0.5">Against outstanding balance — <span x-text="fmtMoney(customer.balance ?? 0)"></span> owed</p>
                    </div>
                    <button @click="showPayModal=false" class="text-gray-400 hover:text-gray-600 ml-4">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="label">Amount <span class="text-red-500">*</span></label>
                            <input type="number" x-model.number="payForm.amount" step="0.01" min="0.01" class="input" placeholder="0.00" />
                        </div>
                        <div>
                            <label class="label">Payment Date <span class="text-red-500">*</span></label>
                            <input type="date" x-model="payForm.payment_date" class="input" />
                        </div>
                    </div>
                    <div>
                        <label class="label">Payment Method</label>
                        <select x-model="payForm.payment_method" class="input">
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="credit_card">Credit Card</option>
                            <option value="online">Online</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Reference Number</label>
                        <input type="text" x-model="payForm.reference_number" class="input" placeholder="Transaction ID, receipt #…" />
                    </div>
                    <!-- Cheque-specific fields -->
                    <div x-show="payForm.payment_method === 'cheque'"
                         class="space-y-4 p-4 rounded-xl border border-purple-100 dark:border-purple-800/50"
                         style="background:#faf5ff">
                        <div class="text-xs font-semibold text-purple-600 uppercase tracking-wider mb-1">Cheque Details</div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">Cheque Number <span class="text-red-500">*</span></label>
                                <input type="text" x-model="payForm.cheque_number" class="input" placeholder="001234" />
                            </div>
                            <div>
                                <label class="label">Cheque Date <span class="text-red-500">*</span></label>
                                <input type="date" x-model="payForm.cheque_date" class="input" />
                            </div>
                        </div>
                        <div>
                            <label class="label">Bank Name <span class="text-red-500">*</span></label>
                            <div x-data="{bq:'',bOpen:false}" @click.outside="bOpen=false" class="relative">
                                <input type="text" :value="payForm.bank_name"
                                    @input="payForm.bank_name=$event.target.value;bq=$event.target.value;bOpen=true"
                                    @focus="bq=payForm.bank_name||'';bOpen=true" @keydown.escape="bOpen=false"
                                    class="input" placeholder="Search bank…" autocomplete="off" />
                                <ul x-show="bOpen" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 rounded-xl shadow-xl max-h-48 overflow-y-auto">
                                    <template x-for="b in banks.filter(b=>b.name.toLowerCase().includes(bq.toLowerCase()))" :key="b.id">
                                        <li @mousedown.prevent="payForm.bank_name=b.name;bq=b.name;bOpen=false"
                                            :class="payForm.bank_name===b.name?'bg-indigo-50 text-indigo-700 font-medium':'hover:bg-gray-50 text-gray-700'"
                                            class="px-3 py-2 text-sm cursor-pointer" x-text="b.name"></li>
                                    </template>
                                    <li x-show="!banks.filter(b=>b.name.toLowerCase().includes(bq.toLowerCase())).length" class="px-3 py-2 text-sm text-gray-400 text-center">No banks found</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="label">Notes</label>
                        <textarea x-model="payForm.notes" rows="2" class="input"></textarea>
                    </div>
                    <div x-show="payError" class="text-sm text-red-600 bg-red-50 dark:bg-red-900/20 rounded-lg p-3" x-text="payError"></div>
                </div>
                <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700/40">
                    <button @click="showPayModal=false" class="btn-secondary">Cancel</button>
                    <button @click="submitPayment()" :disabled="paySaving"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-green-600 hover:bg-green-700 text-white transition-colors disabled:opacity-50">
                        <svg x-show="paySaving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        <span x-text="paySaving ? 'Saving…' : 'Record Payment'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Invoices Tab -->
        <div x-show="activeTab === 'invoices'">
            <div class="card p-0 overflow-visible rounded-2xl">
                <div x-show="loadingInvoices" class="flex items-center justify-center py-12">
                    <svg class="animate-spin w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                </div>
                <div x-show="!loadingInvoices" class="overflow-x-auto">
                    <table class="cd-table min-w-full">
                        <thead>
                            <tr>
                                <th class="rounded-tl-2xl">Invoice #</th>
                                <th>Date</th>
                                <th>Due Date</th>
                                <th class="text-right">Amount</th>
                                <th class="text-right">Balance</th>
                                <th>Status</th>
                                <th class="rounded-tr-2xl">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="inv in invoices" :key="inv.id">
                                <tr>
                                    <td class="font-semibold text-indigo-600" x-text="inv.invoice_number ?? ('#INV-' + inv.id)"></td>
                                    <td x-text="fmtDate(inv.invoice_date)"></td>
                                    <td x-text="fmtDate(inv.due_date)"></td>
                                    <td class="text-right font-semibold tabular-nums" x-text="fmtMoney(inv.total_amount ?? 0)"></td>
                                    <td class="text-right tabular-nums" x-text="fmtMoney(inv.balance_due ?? 0)"></td>
                                    <td>
                                        <span :class="invStatusBadge(inv.status)" x-text="inv.status"></span>
                                    </td>
                                    <td>
                                        <a :href="BASE + '/invoices/' + inv.id" class="text-indigo-600 hover:underline text-sm font-medium">View</a>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="invoices.length === 0 && !loadingInvoices">
                                <td colspan="7" class="text-center text-gray-400 py-10">No invoices found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Ledger Tab -->
        <div x-show="activeTab === 'ledger'">
            <div class="card p-0 overflow-visible rounded-2xl">
                <div x-show="loadingLedger" class="flex items-center justify-center py-12">
                    <svg class="animate-spin w-6 h-6 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                </div>
                <div x-show="!loadingLedger" class="overflow-x-auto">
                    <table class="cd-table min-w-full">
                        <thead>
                            <tr>
                                <th class="rounded-tl-2xl">Date</th>
                                <th>Invoice #</th>
                                <th class="text-right">Invoice Amount</th>
                                <th class="text-right">Paid</th>
                                <th class="text-right">Balance Due</th>
                                <th class="rounded-tr-2xl">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="entry in ledger" :key="entry.id">
                                <tr>
                                    <td x-text="fmtDate(entry.invoice_date)"></td>
                                    <td class="font-semibold text-indigo-600" x-text="entry.invoice_number ?? ('#INV-' + entry.id)"></td>
                                    <td class="text-right font-semibold tabular-nums" x-text="fmtMoney(entry.total ?? 0)"></td>
                                    <td class="text-right tabular-nums" style="color:#16a34a" x-text="fmtMoney(entry.paid_amount ?? 0)"></td>
                                    <td class="text-right font-bold tabular-nums"
                                        :style="(entry.balance_due ?? 0) > 0 ? 'color:#dc2626' : 'color:#15803d'"
                                        x-text="fmtMoney(entry.balance_due ?? 0)"></td>
                                    <td>
                                        <span :class="invStatusBadge(entry.status)" x-text="entry.status"></span>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="ledger.length === 0 && !loadingLedger">
                                <td colspan="6" class="text-center text-gray-400 py-10">No ledger entries found.</td>
                            </tr>
                        </tbody>
                        <tfoot x-show="ledger.length > 0" style="background:#f8fafc" class="dark:bg-gray-800/60 border-t-2 border-gray-200 dark:border-gray-700">
                            <tr>
                                <td colspan="4" class="text-right font-semibold text-gray-600 dark:text-gray-300 uppercase text-xs tracking-wide py-3 px-4">Total Due</td>
                                <td class="text-right font-bold text-base tabular-nums py-3 px-4"
                                    :style="ledgerOutstanding > 0 ? 'color:#dc2626' : 'color:#15803d'"
                                    x-text="fmtMoney(ledgerOutstanding)"></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ EDIT MODAL ══ -->
    <div x-show="showEdit" class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0" style="background:rgba(15,23,42,.65);backdrop-filter:blur(4px)" @click="showEdit=false"></div>
        <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10 overflow-y-auto" style="max-height:90vh">
            <div class="flex items-center justify-between mb-5">
                <h5 class="text-lg font-bold text-gray-800 dark:text-gray-100">Edit Customer</h5>
                <button @click="showEdit=false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="label text-xs">Full Name <span class="text-red-500">*</span></label>
                    <input type="text" x-model="editForm.name" class="input" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label text-xs">Phone</label>
                        <input type="tel" x-model="editForm.phone" class="input" />
                    </div>
                    <div>
                        <label class="label text-xs">Email</label>
                        <input type="email" x-model="editForm.email" class="input" />
                    </div>
                </div>
                <div>
                    <label class="label text-xs">Address</label>
                    <textarea x-model="editForm.address" rows="2" class="input"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label text-xs">District <span class="text-red-500">*</span></label>
                        <select x-model="editForm.district" @change="editForm.city=''" class="input">
                            <option value="">— Select —</option>
                            <template x-for="d in districtList" :key="d"><option :value="d" x-text="d"></option></template>
                        </select>
                    </div>
                    <div>
                        <label class="label text-xs">City <span class="text-red-500">*</span></label>
                        <select x-model="editForm.city" class="input" :disabled="!editForm.district">
                            <option value="">— Select —</option>
                            <template x-for="c in (districtCities[editForm.district] ?? [])" :key="c"><option :value="c" x-text="c"></option></template>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label text-xs">Credit Limit (Rs.)</label>
                        <input type="number" x-model.number="editForm.credit_limit" min="0" class="input" />
                    </div>
                    <div>
                        <label class="label text-xs">Credit Days</label>
                        <input type="number" x-model.number="editForm.payment_terms_days" min="0" class="input" />
                    </div>
                </div>
                <div>
                    <label class="label text-xs">NIC / Business Reg #</label>
                    <input type="text" x-model="editForm.tax_number" class="input" />
                </div>
                <div>
                    <label class="label text-xs">Notes</label>
                    <textarea x-model="editForm.notes" rows="2" class="input"></textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" x-model="editForm.is_active" id="cust-active" class="rounded text-indigo-600" />
                    <label for="cust-active" class="text-sm text-gray-700 dark:text-gray-300">Active customer</label>
                </div>
                <div x-show="editError" class="text-sm text-red-600 bg-red-50 rounded-lg p-3" x-text="editError"></div>
            </div>
            <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700/40">
                <button @click="showEdit=false" class="btn-secondary">Cancel</button>
                <button @click="saveEdit()" class="btn-primary" :disabled="editSaving">
                    <span x-show="editSaving">Saving…</span>
                    <span x-show="!editSaving">Save Changes</span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function customerShowPage() {
    return {
        loading: true,
        loadingInvoices: false,
        loadingLedger: false,
        activeTab: 'overview',
        customer: {},
        invoices: [],
        ledger: [],
        ledgerOutstanding: 0,
        showEdit: false,
        editSaving: false,
        editError: '',
        editForm: {},
        showPayModal: false,
        paySaving: false,
        payError: '',
        payForm: {},
        banks: [],
        obPayments: [],
        loadingObPayments: false,
        districtCities: {},
        get districtList() { return Object.keys(this.districtCities); },
        get id() { return window.location.pathname.split('/').filter(Boolean).pop(); },
        async init() {
            this.districtCities = await loadDistrictCities();
            try {
                const data = await apiFetch('/customers/' + this.id).then(r => r.json());
                // API returns { customer: {...}, cheque_analysis: {...} }
                this.customer = data.customer ?? data.data ?? data;
            } catch (e) {
                toast('Failed to load customer', 'error');
            } finally {
                this.loading = false;
            }
            this.loadObPayments();
        },
        async loadObPayments() {
            this.loadingObPayments = true;
            try {
                const data = await apiFetch('/customers/' + this.id + '/opening-balance-payments').then(r => r.json());
                this.obPayments = data ?? [];
            } catch (e) { /* non-critical, leave list empty */ }
            finally { this.loadingObPayments = false; }
        },
        async deleteObPayment(p) {
            if (!confirm(`Delete this ${fmtMoney(p.amount)} opening-balance payment${p.cheque ? ' (cheque ' + p.cheque.cheque_number + ')' : ''}? This restores the amount to their outstanding balance.`)) return;
            try {
                const r = await apiFetch('/customers/' + this.id + '/opening-balance-payments/' + p.journal_entry_id, { method: 'DELETE' });
                const d = await r.json();
                if (!r.ok) { toast(d.message ?? 'Failed to delete payment.', 'error'); return; }
                toast('Payment deleted.', 'success');
                await this.loadObPayments();
                const fresh = await apiFetch('/customers/' + this.id).then(r => r.json());
                this.customer = fresh.customer ?? fresh.data ?? fresh;
            } catch (e) {
                toast('Failed to delete payment.', 'error');
            }
        },
        async loadInvoices() {
            if (this.invoices.length) return;
            this.loadingInvoices = true;
            try {
                const data = await apiFetch('/invoices?customer_id=' + this.id + '&per_page=100').then(r => r.json());
                this.invoices = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load invoices', 'error');
            } finally {
                this.loadingInvoices = false;
            }
        },
        async loadLedger() {
            if (this.ledger.length) return;
            this.loadingLedger = true;
            try {
                const data = await apiFetch('/customers/' + this.id + '/ledger').then(r => r.json());
                this.ledger = data.transactions ?? data.data ?? data ?? [];
                this.ledgerOutstanding = data.outstanding ?? 0;
            } catch (e) {
                toast('Failed to load ledger', 'error');
            } finally {
                this.loadingLedger = false;
            }
        },
        openEdit() {
            this.editError = '';
            const savedCity = this.customer.city ?? '';
            this.editForm = {
                name:               this.customer.name ?? '',
                phone:              this.customer.phone ?? '',
                email:              this.customer.email ?? '',
                address:            this.customer.address ?? '',
                district:           this.customer.district ?? '',
                city:               '',
                credit_limit:       parseFloat(this.customer.credit_limit ?? 0),
                payment_terms_days: parseInt(this.customer.payment_terms_days ?? this.customer.credit_days ?? 30),
                tax_number:         this.customer.tax_number ?? '',
                notes:              this.customer.notes ?? '',
                is_active:          this.customer.is_active !== false,
            };
            this.showEdit = true;
            this.$nextTick(() => { this.editForm.city = savedCity; });
        },
        async saveEdit() {
            this.editError = '';
            if (!this.editForm.name)     { this.editError = 'Name is required.'; return; }
            if (!this.editForm.district) { this.editError = 'District is required.'; return; }
            if (!this.editForm.city)     { this.editError = 'City is required.'; return; }
            this.editSaving = true;
            try {
                const r = await apiFetch('/customers/' + this.id, { method: 'PUT', body: JSON.stringify(this.editForm) });
                if (!r || !r.ok) {
                    const err = await r?.json().catch(() => ({}));
                    this.editError = err.message ?? Object.values(err.errors ?? {})[0]?.[0] ?? 'Failed to save.';
                    return;
                }
                const updated = await r.json();
                this.customer = { ...this.customer, ...(updated.data ?? updated) };
                this.showEdit = false;
                toast('Customer updated.', 'success');
            } finally { this.editSaving = false; }
        },
        async openPayModal() {
            this.payError = '';
            this.banks = await loadBanks();
            this.payForm = {
                amount: 0,
                payment_date: new Date().toISOString().slice(0, 10),
                payment_method: 'cash',
                reference_number: '',
                notes: '',
                cheque_number: '',
                bank_name: '',
                cheque_date: '',
            };
            this.showPayModal = true;
        },
        async submitPayment() {
            this.payError = '';
            if (!this.payForm.amount || this.payForm.amount <= 0) { this.payError = 'Enter a payment amount greater than zero.'; return; }
            if (this.payForm.payment_method === 'cheque') {
                if (!this.payForm.cheque_number) { this.payError = 'Enter cheque number.'; return; }
                if (!this.payForm.bank_name)     { this.payError = 'Enter bank name.'; return; }
                if (!this.payForm.cheque_date)   { this.payError = 'Enter cheque date.'; return; }
            }
            if (this.payForm.amount > (this.customer.balance ?? 0) + 0.01) {
                const proceed = confirm(`This payment (${fmtMoney(this.payForm.amount)}) is more than the ${fmtMoney(this.customer.balance ?? 0)} currently owed. Continue anyway? (Check the payment history below first if you're not sure this hasn't already been recorded.)`);
                if (!proceed) return;
            }
            this.paySaving = true;
            try {
                const r = await apiFetch('/customers/' + this.id + '/opening-balance-payment', { method: 'POST', body: JSON.stringify(this.payForm) });
                const d = await r.json();
                if (!r.ok) { this.payError = d.message ?? Object.values(d.errors ?? {})[0]?.[0] ?? 'Failed to record payment.'; return; }
                this.customer = { ...this.customer, ...(d.customer ?? {}), balance: d.outstanding_balance ?? this.customer.balance };
                this.showPayModal = false;
                this.ledger = []; // force ledger tab to reload with the new transaction next time it's opened
                await this.loadObPayments();
                toast(d.message || 'Payment recorded.', 'success');
            } catch (e) {
                this.payError = 'Failed to record payment.';
            } finally {
                this.paySaving = false;
            }
        },
        async deleteCustomer() {
            if (!confirm('Delete this customer? This cannot be undone.')) return;
            try {
                await apiFetch('/customers/' + this.id, { method: 'DELETE' });
                toast('Customer deleted', 'success');
                window.location.href = BASE + '/customers';
            } catch (e) {
                toast('Failed to delete customer', 'error');
            }
        },
        invStatusBadge(status) {
            const map = { paid: 'badge-success', partially_paid: 'badge-warning', confirmed: 'badge-warning', unpaid: 'badge-danger', draft: 'badge-gray', overdue: 'badge-danger', cancelled: 'badge-gray', opening: 'badge-gray' };
            return 'badge ' + (map[status] ?? 'badge-gray');
        }
    };
}
</script>
@endpush
