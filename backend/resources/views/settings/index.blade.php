@extends('layouts.app')
@section('title', 'Company Settings')
@section('page-title', 'Company Settings')
@section('page-desc', 'Configure your company information and preferences')

@section('content')
<div x-data="settingsPage()" x-init="init()">

    <!-- Loading -->
    <div x-show="loading" class="flex items-center justify-center py-20">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" class="max-w-2xl mx-auto">

        <!-- ══════════════════════════════════════
             DATA CLEANUP  (Super Admin only)
        ══════════════════════════════════════ -->
        {{-- Clear Data panel hidden ahead of customer handover --}}
        <div x-show="false" x-data="cleanupPanel()"
             class="mb-6 rounded-2xl overflow-hidden border-2 border-dashed border-red-200">

            <!-- Header -->
            <div class="flex items-center justify-between px-5 py-3" style="background:#fff1f2">
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                    <span class="text-sm font-bold text-red-700">Clear Data</span>
                    <span class="text-xs text-red-400 font-medium">— Super Admin only · irreversible</span>
                </div>
                <button type="button" @click="showPanel = !showPanel"
                        class="text-xs text-red-500 hover:text-red-700 font-semibold transition-colors"
                        x-text="showPanel ? '▲ Hide' : '▼ Show'"></button>
            </div>

            <div x-show="showPanel" x-transition class="bg-white p-5 space-y-4">

                <!-- Branch selector -->
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <label class="text-sm font-semibold text-gray-600 flex-shrink-0">Branch:</label>
                    <select x-model="selectedBranch" @change="loadCounts()" class="input py-1.5 text-sm flex-1">
                        <option value="all">All Branches</option>
                        <template x-for="b in branches" :key="b.id">
                            <option :value="b.id" x-text="b.name"></option>
                        </template>
                    </select>
                    <button @click="loadCounts()" type="button"
                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Refresh
                    </button>
                </div>

                <!-- Section: Sales -->
                <div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-1">Sales &amp; Customers</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <template x-for="item in salesItems" :key="item.key">
                            <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-gray-50 hover:border-red-100 transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" :class="item.iconBg">
                                        <svg class="w-4 h-4" :class="item.iconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path :d="item.icon"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-800 truncate" x-text="item.label"></div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            <span x-text="countsLoading ? '…' : (counts[item.key] ?? 0)"></span> records
                                        </div>
                                    </div>
                                </div>
                                <button type="button"
                                        @click="clear(item.key, item.label, item.detail)"
                                        :disabled="!!clearing"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 transition-colors disabled:opacity-50 flex-shrink-0 ml-2">
                                    <svg x-show="clearing === item.key" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                                        <path fill="currentColor" d="M4 12a8 8 0 018-8v8z" class="opacity-75"/>
                                    </svg>
                                    <svg x-show="clearing !== item.key" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Clear
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Section: Purchase -->
                <div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-1">Purchasing &amp; Suppliers</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <template x-for="item in purchaseItems" :key="item.key">
                            <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-gray-50 hover:border-red-100 transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" :class="item.iconBg">
                                        <svg class="w-4 h-4" :class="item.iconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path :d="item.icon"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-800 truncate" x-text="item.label"></div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            <span x-text="countsLoading ? '…' : (counts[item.key] ?? 0)"></span> records
                                        </div>
                                    </div>
                                </div>
                                <button type="button"
                                        @click="clear(item.key, item.label, item.detail)"
                                        :disabled="!!clearing"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 transition-colors disabled:opacity-50 flex-shrink-0 ml-2">
                                    <svg x-show="clearing === item.key" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                                        <path fill="currentColor" d="M4 12a8 8 0 018-8v8z" class="opacity-75"/>
                                    </svg>
                                    <svg x-show="clearing !== item.key" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Clear
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Section: Finance -->
                <div>
                    <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 px-1">Finance &amp; Accounting</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <template x-for="item in financeItems" :key="item.key">
                            <div class="flex items-center justify-between p-3 rounded-xl border border-gray-100 bg-gray-50 hover:border-red-100 transition-colors">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" :class="item.iconBg">
                                        <svg class="w-4 h-4" :class="item.iconColor" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path :d="item.icon"/>
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-800 truncate" x-text="item.label"></div>
                                        <div class="text-xs text-gray-400 mt-0.5">
                                            <span x-text="countsLoading ? '…' : (counts[item.key] ?? 0)"></span> records
                                        </div>
                                    </div>
                                </div>
                                <button type="button"
                                        @click="clear(item.key, item.label, item.detail)"
                                        :disabled="!!clearing"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 transition-colors disabled:opacity-50 flex-shrink-0 ml-2">
                                    <svg x-show="clearing === item.key" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                                        <path fill="currentColor" d="M4 12a8 8 0 018-8v8z" class="opacity-75"/>
                                    </svg>
                                    <svg x-show="clearing !== item.key" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Clear
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Clear All button -->
                <div class="pt-2 border-t border-red-100">
                    <button type="button"
                            @click="clearAll()"
                            :disabled="!!clearing"
                            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold bg-red-600 hover:bg-red-700 text-white transition-colors disabled:opacity-50">
                        <svg x-show="clearing === 'all'" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"/>
                            <path fill="currentColor" d="M4 12a8 8 0 018-8v8z" class="opacity-75"/>
                        </svg>
                        <svg x-show="clearing !== 'all'" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Clear All Data
                    </button>
                    <p class="text-xs text-red-400 text-center mt-1.5">
                        Clears all modules including invoices, supplier invoices, purchase orders, expenses, cheques, journal entries &amp; stock movements. Cannot be undone.
                    </p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit()">

            <!-- Company Info -->
            <div class="card p-6 mb-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Company Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div class="sm:col-span-2">
                        <label class="label">Company Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.company_name" class="input" placeholder="Your Company Name" required />
                    </div>

                    <div>
                        <label class="label">Company Email</label>
                        <input type="email" x-model="form.company_email" class="input" placeholder="info@company.com" />
                    </div>

                    <div>
                        <label class="label">Company Phone</label>
                        <input type="tel" x-model="form.company_phone" class="input" placeholder="+94 11 000 0000" />
                    </div>

                    <div class="sm:col-span-2">
                        <label class="label">Company Address</label>
                        <textarea x-model="form.company_address" rows="3" class="input" placeholder="Full company address…"></textarea>
                    </div>

                    <div>
                        <label class="label">City</label>
                        <input type="text" x-model="form.city" class="input" placeholder="City" />
                    </div>

                    <div>
                        <label class="label">Country</label>
                        <input type="text" x-model="form.country" class="input" placeholder="Sri Lanka" />
                    </div>

                    <div>
                        <label class="label">Tax / VAT Number</label>
                        <input type="text" x-model="form.tax_number" class="input" placeholder="Optional" />
                    </div>

                    <div>
                        <label class="label">Registration Number</label>
                        <input type="text" x-model="form.registration_number" class="input" placeholder="Optional" />
                    </div>

                </div>
            </div>

            <!-- Financial Settings -->
            <div class="card p-6 mb-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">Financial Settings</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div>
                        <label class="label">Default Currency</label>
                        <select x-model="form.currency" class="input">
                            <option value="LKR">LKR — Sri Lankan Rupee</option>
                            <option value="USD">USD — US Dollar</option>
                            <option value="EUR">EUR — Euro</option>
                            <option value="GBP">GBP — British Pound</option>
                            <option value="AED">AED — UAE Dirham</option>
                            <option value="INR">INR — Indian Rupee</option>
                        </select>
                    </div>

                    <div>
                        <label class="label">Currency Symbol</label>
                        <input type="text" x-model="form.currency_symbol" class="input" placeholder="Rs." />
                    </div>

                    <div>
                        <label class="label">Financial Year Start</label>
                        <select x-model="form.financial_year_start" class="input">
                            <option value="01-01">January (01-01)</option>
                            <option value="04-01">April (04-01)</option>
                            <option value="07-01">July (07-01)</option>
                            <option value="10-01">October (10-01)</option>
                        </select>
                    </div>

                    <div>
                        <label class="label">Default Tax Rate (%)</label>
                        <input type="number" x-model.number="form.default_tax_rate" class="input" min="0" max="100" step="0.1" placeholder="0" />
                    </div>

                    <div>
                        <label class="label">Default Payment Terms (days)</label>
                        <input type="number" x-model.number="form.payment_terms_days" class="input" min="0" step="1" placeholder="30" />
                    </div>

                    <div>
                        <label class="label">Invoice Prefix</label>
                        <input type="text" x-model="form.invoice_prefix" class="input" placeholder="INV-" />
                    </div>

                </div>
            </div>

            <!-- System Settings -->
            <div class="card p-6 mb-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-4 pb-2 border-b border-gray-100">System Preferences</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <div>
                        <label class="label">Date Format</label>
                        <select x-model="form.date_format" class="input">
                            <option value="d/m/Y">DD/MM/YYYY</option>
                            <option value="m/d/Y">MM/DD/YYYY</option>
                            <option value="Y-m-d">YYYY-MM-DD</option>
                            <option value="d-m-Y">DD-MM-YYYY</option>
                        </select>
                    </div>

                    <div>
                        <label class="label">Timezone</label>
                        <select x-model="form.timezone" class="input">
                            <option value="Asia/Colombo">Asia/Colombo (UTC+5:30)</option>
                            <option value="UTC">UTC</option>
                            <option value="Asia/Dubai">Asia/Dubai (UTC+4)</option>
                            <option value="Asia/Kolkata">Asia/Kolkata (UTC+5:30)</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2 flex items-center gap-3">
                        <input type="checkbox" x-model="form.low_stock_alerts" id="low_stock_alerts" class="rounded border-gray-300" />
                        <label for="low_stock_alerts" class="text-sm text-gray-700">Enable low stock email alerts</label>
                    </div>

                    <div class="sm:col-span-2 flex items-center gap-3">
                        <input type="checkbox" x-model="form.overdue_invoice_alerts" id="overdue_alerts" class="rounded border-gray-300" />
                        <label for="overdue_alerts" class="text-sm text-gray-700">Enable overdue invoice email alerts</label>
                    </div>

                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-3">
                <button type="button" @click="load()" class="btn-secondary">Discard</button>
                <button type="submit" :disabled="submitting" class="btn-primary">
                    <template x-if="submitting">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                    </template>
                    <span x-text="submitting ? 'Saving…' : 'Save Settings'"></span>
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function cleanupPanel() {
    const TRASH = 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16';
    const DOC   = 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z';
    const BOX   = 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4';
    const TRUCK = 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0H3m2 0h4';
    const BILL  = 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2';
    const CHECK = 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z';
    const WALLET= 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z';
    const LEDGER= 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253';

    return {
        showPanel: false,
        clearing: null,
        countsLoading: false,
        selectedBranch: 'all',
        branches: [],
        counts: {},

        salesItems: [
            { key: 'proforma', label: 'Proforma Invoices', icon: DOC, iconBg: 'bg-blue-50', iconColor: 'text-blue-500',
              detail: 'All proforma invoices' },
            { key: 'invoices', label: 'Sales Invoices', icon: DOC, iconBg: 'bg-indigo-50', iconColor: 'text-indigo-500',
              detail: 'All sales invoices, payments & linked cheques' },
        ],
        purchaseItems: [
            { key: 'supplier_invoices', label: 'Supplier Invoices', icon: BILL, iconBg: 'bg-orange-50', iconColor: 'text-orange-500',
              sub: 'Includes all supplier payments', detail: 'Supplier invoices and all related payments' },
            { key: 'purchase', label: 'Purchase Orders & GRNs', icon: TRUCK, iconBg: 'bg-yellow-50', iconColor: 'text-yellow-600',
              sub: 'Purchase orders + goods receipts', detail: 'Purchase orders, GRNs and related items' },
        ],
        financeItems: [
            { key: 'expenses', label: 'Expenses', icon: WALLET, iconBg: 'bg-green-50', iconColor: 'text-green-500',
              detail: 'All expense records' },
            { key: 'cheques', label: 'Cheques', icon: CHECK, iconBg: 'bg-purple-50', iconColor: 'text-purple-500',
              detail: 'All cheques and invoice links' },
            { key: 'ledger', label: 'Journal Entries', icon: LEDGER, iconBg: 'bg-gray-100', iconColor: 'text-gray-500',
              detail: 'All journal entries and ledger lines' },
        ],

        async loadCounts() {
            this.countsLoading = true;
            try {
                const qs = this.selectedBranch !== 'all' ? '?branch_id=' + this.selectedBranch : '';
                const [rc, rb] = await Promise.all([
                    apiFetch('/dev/counts' + qs),
                    apiFetch('/branches'),
                ]);
                if (rc?.ok) this.counts = await rc.json();
                if (rb?.ok) {
                    const d = await rb.json();
                    this.branches = d.data ?? d ?? [];
                }
            } catch {}
            finally { this.countsLoading = false; }
        },

        async clear(target, label, detail) {
            const branchNote = this.selectedBranch !== 'all' ? ' for the selected branch' : '';
            if (!confirm('Clear ' + (detail ?? label) + branchNote + '?\n\nThis CANNOT be undone.')) return;
            this.clearing = target;
            try {
                const r = await apiFetch('/dev/cleanup', {
                    method: 'POST',
                    body: JSON.stringify({ target, branch_id: this.selectedBranch !== 'all' ? this.selectedBranch : null }),
                });
                const d = await r.json();
                if (r?.ok) {
                    toast(d.message ?? 'Cleared successfully', 'success');
                    await this.loadCounts();
                } else {
                    toast(d.message ?? 'Failed to clear', 'error');
                }
            } catch (e) {
                toast('Error: ' + e.message, 'error');
            } finally {
                this.clearing = null;
            }
        },

        async clearAll() {
            const branchNote = this.selectedBranch !== 'all' ? ' for the selected branch' : '';
            if (!confirm('Clear ALL data (invoices, supplier invoices, purchase orders, expenses, cheques, journal entries & stock movements)' + branchNote + '?\n\nThis CANNOT be undone.')) return;
            this.clearing = 'all';
            try {
                const r = await apiFetch('/dev/cleanup', {
                    method: 'POST',
                    body: JSON.stringify({ target: 'all', branch_id: this.selectedBranch !== 'all' ? this.selectedBranch : null }),
                });
                const d = await r.json();
                if (r?.ok) {
                    toast(d.message ?? 'All data cleared', 'success');
                    await this.loadCounts();
                } else {
                    toast(d.message ?? 'Failed', 'error');
                }
            } catch (e) {
                toast('Error: ' + e.message, 'error');
            } finally {
                this.clearing = null;
            }
        },
    };
}

function settingsPage() {
    return {
        loading: true,
        submitting: false,
        get isSuperAdmin() {
            try {
                const u = JSON.parse(localStorage.getItem('medri_user') || '{}');
                return !!(u.is_super_admin || (u.roles ?? []).includes('super_admin'));
            } catch { return false; }
        },
        form: {
            company_name: '',
            company_email: '',
            company_phone: '',
            company_address: '',
            city: '',
            country: 'Sri Lanka',
            tax_number: '',
            registration_number: '',
            currency: 'LKR',
            currency_symbol: 'Rs.',
            financial_year_start: '01-01',
            default_tax_rate: 0,
            payment_terms_days: 30,
            invoice_prefix: 'INV-',
            date_format: 'd/m/Y',
            timezone: 'Asia/Colombo',
            low_stock_alerts: true,
            overdue_invoice_alerts: true,
        },
        async init() {
            await this.load();
        },
        async load() {
            this.loading = true;
            try {
                const data = await apiFetch('/settings').then(r => r.json());
                const settings = data.data ?? data ?? {};
                // Merge settings into form (keep defaults for missing keys)
                Object.keys(this.form).forEach(key => {
                    if (settings[key] !== undefined) this.form[key] = settings[key];
                });
            } catch (e) {
                toast('Failed to load settings', 'error');
            } finally {
                this.loading = false;
            }
        },
        async submit() {
            this.submitting = true;
            try {
                await apiFetch('/settings', { method: 'PUT', body: JSON.stringify(this.form) });
                toast('Settings saved successfully', 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to save settings', 'error');
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
@endpush
