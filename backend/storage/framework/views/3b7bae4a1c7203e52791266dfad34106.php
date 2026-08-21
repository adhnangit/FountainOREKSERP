<?php $__env->startSection('title', 'Cheque History'); ?>
<?php $__env->startSection('page-title', 'Cheque History'); ?>
<?php $__env->startSection('page-desc', 'Track the complete lifecycle of customer cheques'); ?>

<?php $__env->startSection('content'); ?>
<style>
/* ── Layout ── */
.ch-hero{border-radius:20px;overflow:hidden;position:relative;margin-bottom:20px}
.ch-hero-bg{position:absolute;inset:0;background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 55%,#312e81 100%)}
.ch-hero-pattern{position:absolute;inset:0;opacity:.06;background-image:radial-gradient(circle,#fff 1px,transparent 1px);background-size:24px 24px}
.ch-hero-content{position:relative;padding:28px 32px 24px}

/* ── Filter Bar ── */
.ch-filter{background:#fff;border-radius:16px;border:1px solid #e2e8f0;padding:18px 20px;margin-bottom:20px}
.dark .ch-filter{background:#1e293b;border-color:#334155}
.ch-filter-label{font-size:10.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;display:block}

/* ── Stat cards ── */
.ch-stats{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:20px}
@media(max-width:1200px){.ch-stats{grid-template-columns:repeat(3,1fr)}}
@media(max-width:640px){.ch-stats{grid-template-columns:repeat(2,1fr)}}
.ch-stat{background:#fff;border-radius:14px;border:1px solid #e2e8f0;padding:14px 16px;transition:box-shadow .2s,transform .2s}
.ch-stat:hover{box-shadow:0 6px 20px rgba(0,0,0,.07);transform:translateY(-1px)}
.dark .ch-stat{background:#1e293b;border-color:#334155}
.ch-stat-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:10px}
.ch-stat-val{font-size:20px;font-weight:800;color:#0f172a;letter-spacing:-.5px;line-height:1}
.dark .ch-stat-val{color:#f1f5f9}
.ch-stat-lbl{font-size:10.5px;color:#94a3b8;font-weight:600;margin-top:3px}
.ch-stat-sub{font-size:11px;font-weight:600;margin-top:4px;color:#64748b}

/* ── Table ── */
.ch-tbl-card{background:#fff;border-radius:16px;border:1px solid #e2e8f0;overflow:hidden}
.dark .ch-tbl-card{background:#1e293b;border-color:#334155}
.ch-tbl{width:100%;border-collapse:separate;border-spacing:0}
.ch-tbl thead th{padding:10px 14px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e2e8f0;white-space:nowrap}
.dark .ch-tbl thead th{background:#0f172a;border-color:#334155;color:#64748b}
.ch-tbl thead th:first-child{padding-left:20px;border-radius:0}
.ch-tbl tbody tr{transition:background .12s}
.ch-tbl tbody tr:hover{background:#f8faff}
.dark .ch-tbl tbody tr:hover{background:#0f172a}
.ch-tbl tbody td{padding:13px 14px;border-bottom:1px solid #f1f5f9;vertical-align:top;font-size:13px}
.dark .ch-tbl tbody td{border-color:#334155;color:#cbd5e1}
.ch-tbl tbody td:first-child{padding-left:20px}
.ch-tbl tbody tr:last-child td{border-bottom:none}

/* ── Journey flow ── */
.ch-journey{display:flex;align-items:flex-start;flex-wrap:wrap;gap:4px}
.ch-jnode{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:99px;font-size:10.5px;font-weight:700;white-space:nowrap}
.ch-jarrow{color:#94a3b8;font-size:11px;margin:0 1px}
.ch-jn-recv{background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0}
.ch-jn-hand{background:#fff7ed;color:#c2410c;border:1px solid #fed7aa}
.ch-jn-inv{background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe}
.ch-jn-supp{background:#fef2f2;color:#b91c1c;border:1px solid #fecaca}
.ch-jn-po{background:#fffbeb;color:#b45309;border:1px solid #fde68a}
.ch-jn-party{background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe}

/* ── Status badges ── */
.chq-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:10.5px;font-weight:700;white-space:nowrap}
.chq-badge::before{content:'';width:5px;height:5px;border-radius:50%}
.chq-b-hand{background:#fffbeb;color:#b45309}.chq-b-hand::before{background:#f59e0b}
.chq-b-dep{background:#eff6ff;color:#1d4ed8}.chq-b-dep::before{background:#3b82f6}
.chq-b-clear{background:#f0fdf4;color:#15803d}.chq-b-clear::before{background:#22c55e}
.chq-b-bounce{background:#fef2f2;color:#b91c1c}.chq-b-bounce::before{background:#ef4444}
.chq-b-cancel{background:#f8fafc;color:#64748b}.chq-b-cancel::before{background:#94a3b8}
.chq-b-trans{background:#ecfeff;color:#0e7490}.chq-b-trans::before{background:#06b6d4}
.chq-b-party{background:#f5f3ff;color:#6d28d9;border:1px solid #c4b5fd;font-size:10px;padding:2px 8px;border-radius:20px;font-weight:700;display:inline-flex;align-items:center;gap:3px}

/* ── Bounce risk indicator ── */
.ch-bounce-risk-low{color:#16a34a;background:#f0fdf4;border:1px solid #bbf7d0}
.ch-bounce-risk-med{color:#b45309;background:#fffbeb;border:1px solid #fde68a}
.ch-bounce-risk-high{color:#b91c1c;background:#fef2f2;border:1px solid #fecaca}

/* ── Welcome state ── */
.ch-welcome{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:60px 24px;text-align:center}

/* ── Dark extras ── */
.dark .ch-filter-label{color:#64748b}
.dark .ch-stat-lbl{color:#64748b}
.dark .ch-stat-sub{color:#475569}
</style>

<div x-data="chequeHistory()" x-init="init()">

    
    <div class="ch-hero mb-5">
        <div class="ch-hero-bg"></div>
        <div class="ch-hero-pattern"></div>
        <div class="ch-hero-content">
            <div class="flex items-start justify-between flex-wrap gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,0.15)">
                            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-xl font-black text-white leading-none">Cheque History</h1>
                            <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.55)">Customer cheque lifecycle — from receipt to clearance</p>
                        </div>
                    </div>
                </div>

                
                <template x-if="!selectedCustomer && searched && !loading">
                    <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl" style="background:rgba(255,255,255,0.12)">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.2)">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                        </div>
                        <div>
                            <p class="font-bold text-white text-sm">Search Results</p>
                            <p class="text-xs" style="color:rgba(255,255,255,0.5)"
                               x-text="(analysis?.total_count || 0) + ' cheques · ' + fmtMoney(analysis?.total_amount || 0)"></p>
                        </div>
                    </div>
                </template>
                <template x-if="selectedCustomer">
                    <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl" style="background:rgba(255,255,255,0.12);backdrop-filter:blur(8px)">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-sm font-black flex-shrink-0"
                             style="background:rgba(255,255,255,0.2)"
                             x-text="(selectedCustomer?.name||'?').charAt(0).toUpperCase()"></div>
                        <div>
                            <p class="font-bold text-white text-sm" x-text="selectedCustomer?.name"></p>
                            <p class="text-xs" style="color:rgba(255,255,255,0.5)"
                               x-text="(analysis?.total_count || 0) + ' cheques · ' + fmtMoney(analysis?.total_amount || 0)"></p>
                        </div>
                        <template x-if="analysis?.bounce_rate > 0">
                            <div class="ml-2 px-2.5 py-1 rounded-lg text-xs font-bold"
                                 :class="{
                                     'ch-bounce-risk-high': analysis.bounce_rate >= 30,
                                     'ch-bounce-risk-med':  analysis.bounce_rate >= 10 && analysis.bounce_rate < 30,
                                     'ch-bounce-risk-low':  analysis.bounce_rate < 10,
                                 }"
                                 x-text="'⚡ ' + analysis.bounce_rate + '% bounce rate'"></div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    
    <div class="ch-filter">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">

            
            <div class="lg:col-span-2">
                <label class="ch-filter-label">Customer</label>
                <select x-model="customerId" @change="onCustomerChange()"
                        class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100">
                    <option value="">— Select customer —</option>
                    <template x-for="c in customers" :key="c.id">
                        <option :value="c.id" x-text="c.name + (c.code ? ' (' + c.code + ')' : '')"></option>
                    </template>
                </select>
            </div>

            
            <div>
                <label class="ch-filter-label">Cheque #</label>
                <input type="text" x-model="filters.chequeNumber" @keyup.enter="loadHistory()"
                       placeholder="e.g. 012345"
                       class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-indigo-400" />
            </div>

            
            <div>
                <label class="ch-filter-label">From Date</label>
                <input type="date" x-model="filters.fromDate"
                       class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-indigo-400" />
            </div>

            
            <div>
                <label class="ch-filter-label">To Date</label>
                <input type="date" x-model="filters.toDate"
                       class="w-full border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-indigo-400" />
            </div>

            
            <div class="flex flex-col gap-1.5">
                <label class="ch-filter-label">Status</label>
                <div class="flex items-center gap-2">
                    <select x-model="filters.status"
                            class="flex-1 border border-gray-200 dark:border-gray-600 rounded-lg px-3 py-2 text-sm bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-indigo-400">
                        <option value="">All</option>
                        <option value="in_hand">In Hand</option>
                        <option value="transferred">Transferred</option>
                        <option value="deposited">Deposited</option>
                        <option value="cleared">Cleared</option>
                        <option value="bounced">Bounced</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button @click="loadHistory()" :disabled="!canSearch || loading"
                            class="flex-shrink-0 h-9 px-4 rounded-lg text-sm font-bold text-white flex items-center gap-1.5 transition-all"
                            style="background:#1B3EB6"
                            :class="(!canSearch || loading) ? 'opacity-50 cursor-not-allowed' : 'hover:opacity-90'">
                        <svg x-show="loading" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        <svg x-show="!loading" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                        Search
                    </button>
                </div>
            </div>

        </div>

        
        <div class="flex items-center gap-2 mt-3 flex-wrap">
            <span class="text-xs text-gray-400 font-semibold">Quick:</span>
            <button @click="setQuickRange('this_month')" class="px-2.5 py-1 rounded-lg border text-xs font-semibold transition-colors"
                    :class="quickRange==='this_month' ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-gray-200 dark:border-gray-600 text-gray-500 hover:bg-gray-50'">
                This Month
            </button>
            <button @click="setQuickRange('last_month')" class="px-2.5 py-1 rounded-lg border text-xs font-semibold transition-colors"
                    :class="quickRange==='last_month' ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-gray-200 dark:border-gray-600 text-gray-500 hover:bg-gray-50'">
                Last Month
            </button>
            <button @click="setQuickRange('this_year')" class="px-2.5 py-1 rounded-lg border text-xs font-semibold transition-colors"
                    :class="quickRange==='this_year' ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-gray-200 dark:border-gray-600 text-gray-500 hover:bg-gray-50'">
                This Year
            </button>
            <button @click="setQuickRange('')" class="px-2.5 py-1 rounded-lg border text-xs font-semibold transition-colors"
                    :class="quickRange==='' ? 'bg-indigo-600 border-indigo-600 text-white' : 'border-gray-200 dark:border-gray-600 text-gray-500 hover:bg-gray-50'">
                All Time
            </button>
        </div>
    </div>

    
    <template x-if="analysis && searched">
        <div class="ch-stats mb-5">

            <div class="ch-stat">
                <div class="ch-stat-icon" style="background:#ede9fe">
                    <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="#7c3aed" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                </div>
                <div class="ch-stat-val" x-text="analysis.total_count || 0"></div>
                <div class="ch-stat-lbl">Total Cheques</div>
                <div class="ch-stat-sub" x-text="fmtMoney(analysis.total_amount || 0)"></div>
            </div>

            <div class="ch-stat">
                <div class="ch-stat-icon" style="background:#fffbeb">
                    <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <div class="ch-stat-val" x-text="analysis.in_hand_count || 0"></div>
                <div class="ch-stat-lbl">In Hand</div>
                <div class="ch-stat-sub" x-text="fmtMoney(analysis.in_hand_amount || 0)"></div>
            </div>

            <div class="ch-stat">
                <div class="ch-stat-icon" style="background:#f5f3ff">
                    <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="#6d28d9" stroke-width="2">
                        <path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                </div>
                <div class="ch-stat-val" x-text="analysis.party_count || 0"></div>
                <div class="ch-stat-lbl">Party Cheques</div>
                <div class="ch-stat-sub" x-text="fmtMoney(analysis.party_amount || 0)"></div>
            </div>

            <div class="ch-stat">
                <div class="ch-stat-icon" style="background:#eff6ff">
                    <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><path d="M12 19V5m-7 7l7-7 7 7"/></svg>
                </div>
                <div class="ch-stat-val" x-text="analysis.deposited_count || 0"></div>
                <div class="ch-stat-lbl">Deposited</div>
                <div class="ch-stat-sub" x-text="fmtMoney(analysis.deposited_amount || 0)"></div>
            </div>

            <div class="ch-stat">
                <div class="ch-stat-icon" style="background:#f0fdf4">
                    <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div class="ch-stat-val" x-text="analysis.cleared_count || 0"></div>
                <div class="ch-stat-lbl">Cleared</div>
                <div class="ch-stat-sub" x-text="fmtMoney(analysis.cleared_amount || 0)"></div>
            </div>

            <div class="ch-stat" :class="(analysis.bounced_count || 0) > 0 ? 'border-red-200' : ''">
                <div class="ch-stat-icon" style="background:#fef2f2">
                    <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="#dc2626" stroke-width="2"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                </div>
                <div class="ch-stat-val" :class="(analysis.bounced_count || 0) > 0 ? 'text-red-600' : ''"
                     x-text="analysis.bounced_count || 0"></div>
                <div class="ch-stat-lbl">Bounced</div>
                <div class="ch-stat-sub text-red-600" x-show="(analysis.bounced_count || 0) > 0"
                     x-text="analysis.bounce_rate + '% rate'"></div>
                <div class="ch-stat-sub" x-show="(analysis.bounced_count || 0) === 0">No bounces</div>
            </div>

        </div>
    </template>

    

    
    <template x-if="!searched && !loading">
        <div class="ch-tbl-card">
            <div class="ch-welcome">
                <div class="w-20 h-20 rounded-2xl flex items-center justify-center mb-5" style="background:linear-gradient(135deg,#ede9fe,#ddd6fe)">
                    <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="#7c3aed" stroke-width="1.5">
                        <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-700 dark:text-gray-200 mb-2">Search Cheques</h3>
                <p class="text-sm text-gray-400 max-w-sm">Select a customer, enter a cheque number, or pick a date range — then click Search to view the full cheque lifecycle.</p>
            </div>
        </div>
    </template>

    
    <template x-if="loading">
        <div class="ch-tbl-card">
            <div class="flex items-center justify-center py-20 text-gray-400 gap-3">
                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                <span class="text-sm font-medium">Loading cheque history…</span>
            </div>
        </div>
    </template>

    
    <template x-if="searched && !loading">
        <div class="ch-tbl-card">

            
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span class="font-bold text-gray-700 dark:text-gray-200 text-sm" x-text="cheques.length + ' cheque(s) found'"></span>
                    <template x-if="filters.fromDate || filters.toDate">
                        <span class="text-xs text-gray-400"
                              x-text="(filters.fromDate ? 'from ' + fmtDate(filters.fromDate) : '') + (filters.toDate ? ' to ' + fmtDate(filters.toDate) : '')"></span>
                    </template>
                </div>
                <div class="flex items-center gap-2">
                    <div class="text-xs font-semibold text-gray-500"
                         x-text="'Total: ' + fmtMoney(cheques.reduce((s,c) => s + parseFloat(c.amount || 0), 0))"></div>
                </div>
            </div>

            
            <template x-if="cheques.length === 0">
                <div class="ch-welcome" style="padding:40px">
                    <svg class="w-12 h-12 opacity-20 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                        <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <p class="text-sm font-medium text-gray-500">No cheques found</p>
                    <p class="text-xs text-gray-400 mt-1">Try adjusting the date range or clearing the status filter</p>
                </div>
            </template>

            <div x-show="cheques.length > 0" class="overflow-x-auto">
                <table class="ch-tbl">
                    <thead>
                        <tr>
                            <th class="text-left">Cheque #</th>
                            <th x-show="!customerId" class="text-left">Customer</th>
                            <th class="text-left">Bank</th>
                            <th class="text-right">Amount</th>
                            <th class="text-left">Cheque Date</th>
                            <th class="text-left">Received On</th>
                            <th class="text-left">Status</th>
                            <th class="text-left">Journey</th>
                            <th class="text-left">Handed To Supplier</th>
                            <th class="text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="ch in cheques" :key="ch.id">
                            <tr>
                                
                                <td>
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                                             :class="ch.status === 'bounced' ? 'bg-red-100' : ch.status === 'cleared' ? 'bg-green-100' : 'bg-indigo-100'">
                                            <svg style="width:13px;height:13px" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                                 :stroke="ch.status === 'bounced' ? '#dc2626' : ch.status === 'cleared' ? '#16a34a' : '#4f46e5'">
                                                <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                        </div>
                                        <span class="font-mono font-black text-sm text-gray-800 dark:text-gray-100"
                                              x-text="ch.cheque_number || '—'"></span>
                                    </div>
                                </td>

                                
                                <td x-show="!customerId">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                             style="background:#1B3EB6"
                                             x-text="(ch.customer?.name || '?').charAt(0).toUpperCase()"></div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="ch.customer?.name || '—'"></p>
                                            <p class="text-xs text-gray-400" x-text="ch.customer?.code || ''"></p>
                                        </div>
                                    </div>
                                </td>

                                
                                <td class="text-gray-500 dark:text-gray-400 text-sm" x-text="ch.bank_name || '—'"></td>

                                
                                <td class="text-right font-black text-gray-900 dark:text-white" x-text="fmtMoney(ch.amount || 0)"></td>

                                
                                <td>
                                    <span class="text-sm" x-text="fmtDate(ch.cheque_date)"></span>
                                    <div x-show="ch.status === 'in_hand' && ch.cheque_date && new Date(ch.cheque_date) < new Date()"
                                         class="text-xs text-red-500 font-semibold mt-0.5">Overdue</div>
                                </td>

                                
                                <td class="text-sm text-gray-500 dark:text-gray-400" x-text="fmtDate(ch.received_issued_date)"></td>

                                
                                <td>
                                    <span class="chq-badge" :class="statusBadgeClass(ch.status)" x-text="statusLabel(ch.status)"></span>
                                    <template x-if="ch.status === 'in_hand' && (ch.purchase_orders?.length || ch.supplier_payments?.length)">
                                        <div class="mt-1">
                                            <span class="chq-b-party">
                                                <svg style="width:9px;height:9px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                    <path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                                </svg>
                                                Party Cheque
                                            </span>
                                            <div class="text-[10px] font-medium mt-0.5" style="color:#7c3aed"
                                                 x-text="'Clears: ' + fmtDate(ch.cheque_date)"></div>
                                        </div>
                                    </template>
                                    <template x-if="ch.status === 'bounced'">
                                        <div class="mt-1 text-[10px] text-red-500 font-medium"
                                             x-text="ch.bounce_reason ? '⚡ ' + ch.bounce_reason : '⚡ Bounced'"></div>
                                    </template>
                                </td>

                                
                                <td>
                                    <div class="ch-journey">
                                        
                                        <span class="ch-jnode ch-jn-recv">
                                            <svg style="width:9px;height:9px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M19 9l-7-7-7 7M5 15l7 7 7-7"/></svg>
                                            Received
                                        </span>
                                        
                                        <template x-for="lnk in (ch.invoice_links || [])" :key="lnk.id">
                                            <template x-if="lnk.invoice">
                                                <div class="flex items-center gap-0.5">
                                                    <span class="ch-jarrow">→</span>
                                                    <span class="ch-jnode ch-jn-inv" x-text="lnk.invoice.invoice_number"></span>
                                                </div>
                                            </template>
                                        </template>
                                        
                                        <template x-for="sp in (ch.supplier_payments || [])" :key="sp.id">
                                            <div class="flex items-center gap-0.5">
                                                <span class="ch-jarrow">→</span>
                                                <span class="ch-jnode ch-jn-supp"
                                                      x-text="sp.supplier_invoice?.supplier?.name || 'Supplier'"></span>
                                            </div>
                                        </template>
                                        
                                        <template x-for="po in (ch.purchase_orders || [])" :key="po.id">
                                            <div class="flex items-center gap-0.5">
                                                <span class="ch-jarrow">→</span>
                                                <span class="ch-jnode ch-jn-po" x-text="'PO: ' + po.po_number"></span>
                                            </div>
                                        </template>
                                        
                                        <template x-for="exp in (ch.expenses || [])" :key="'exp-' + exp.id">
                                            <div class="flex items-center gap-0.5">
                                                <span class="ch-jarrow">→</span>
                                                <span class="ch-jnode" style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0"
                                                      x-text="'Exp: ' + (exp.expense_number || exp.description)"></span>
                                            </div>
                                        </template>
                                        
                                        <template x-if="ch.status === 'cleared'">
                                            <div class="flex items-center gap-0.5">
                                                <span class="ch-jarrow">→</span>
                                                <span class="ch-jnode ch-jn-recv">✓ Cleared</span>
                                            </div>
                                        </template>
                                        <template x-if="ch.status === 'bounced'">
                                            <div class="flex items-center gap-0.5">
                                                <span class="ch-jarrow">→</span>
                                                <span class="ch-jnode" style="background:#fef2f2;color:#b91c1c;border:1px solid #fecaca">⚡ Bounced</span>
                                            </div>
                                        </template>
                                    </div>
                                </td>

                                
                                <td>
                                    <template x-if="!ch.supplier_payments?.length && !ch.purchase_orders?.length">
                                        <span class="text-xs text-gray-300 dark:text-gray-600">—</span>
                                    </template>
                                    <template x-for="sp in (ch.supplier_payments || [])" :key="sp.id">
                                        <div class="mb-1.5">
                                            <div class="font-semibold text-sm text-red-700 dark:text-red-400"
                                                 x-text="sp.supplier_invoice?.supplier?.name || '—'"></div>
                                            <div class="text-xs text-gray-500 mt-0.5"
                                                 x-text="(sp.supplier_invoice?.invoice_number || '') + (sp.payment_date ? ' · ' + fmtDate(sp.payment_date) : '')"></div>
                                            <div class="text-xs font-bold text-red-600" x-text="fmtMoney(sp.amount || 0)"></div>
                                        </div>
                                    </template>
                                    <template x-for="po in (ch.purchase_orders || [])" :key="'po-' + po.id">
                                        <template x-if="!ch.supplier_payments?.length">
                                            <div class="px-2 py-1 rounded-lg text-xs font-semibold" style="background:#fffbeb;color:#b45309;border:1px solid #fde68a">
                                                Reserved: <span x-text="po.po_number"></span>
                                                <span class="block text-xs opacity-70 mt-0.5">Pending deposit</span>
                                            </div>
                                        </template>
                                    </template>
                                </td>

                                
                                <td>
                                    <button @click="openDetail(ch)"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all hover:shadow-md"
                                            style="background:#f0f9ff;border:1px solid #bae6fd;color:#0369a1;white-space:nowrap">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>

                    
                    <tfoot x-show="cheques.length > 0"
                           style="background:#f8fafc;border-top:2px solid #e2e8f0" class="dark:bg-gray-800/60 dark:border-gray-700">
                        <tr>
                            <td colspan="2" class="pl-5 py-3 text-xs font-black text-gray-500 uppercase tracking-wide">
                                Total (<span x-text="cheques.length"></span> cheques)
                            </td>
                            <td class="text-right py-3 font-black text-gray-900 dark:text-white"
                                x-text="fmtMoney(cheques.reduce((s,c) => s + parseFloat(c.amount || 0), 0))"></td>
                            <td colspan="6"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </template>

    
    <template x-if="detail.open && detail.cheque">
        <div class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-10"
             style="background:rgba(15,23,42,0.55);backdrop-filter:blur(4px)"
             @click.self="closeDetail()">
            <div class="w-full max-w-2xl bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden"
                 style="max-height:90vh;display:flex;flex-direction:column">

                
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700"
                     style="background:linear-gradient(135deg,#1B3EB6,#312e81)">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center" style="background:rgba(255,255,255,0.15)">
                            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="font-black text-white text-base leading-none">Cheque History</h2>
                            <p class="text-xs mt-0.5 font-mono" style="color:rgba(255,255,255,0.6)"
                               x-text="detail.cheque.cheque_number"></p>
                        </div>
                    </div>
                    <button @click="closeDetail()" class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors hover:bg-white/20">
                        <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                
                <div class="overflow-y-auto flex-1 px-6 py-5 space-y-5">

                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="rounded-xl p-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Cheque #</div>
                            <div class="font-mono font-black text-gray-800 text-sm" x-text="detail.cheque.cheque_number || '—'"></div>
                        </div>
                        <div class="rounded-xl p-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Bank</div>
                            <div class="font-semibold text-gray-800 text-sm" x-text="detail.cheque.bank_name || '—'"></div>
                        </div>
                        <div class="rounded-xl p-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Amount</div>
                            <div class="font-black text-gray-900 text-sm" x-text="fmtMoney(detail.cheque.amount || 0)"></div>
                        </div>
                        <div class="rounded-xl p-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Status</div>
                            <span class="chq-badge" :class="statusBadgeClass(detail.cheque.status)" x-text="statusLabel(detail.cheque.status)"></span>
                        </div>
                        <div class="rounded-xl p-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Cheque Date</div>
                            <div class="font-semibold text-gray-800 text-sm" x-text="fmtDate(detail.cheque.cheque_date)"></div>
                        </div>
                        <div class="rounded-xl p-3" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Received On</div>
                            <div class="font-semibold text-gray-800 text-sm" x-text="fmtDate(detail.cheque.received_issued_date)"></div>
                        </div>
                        <div class="rounded-xl p-3 sm:col-span-2" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Branch</div>
                            <div class="font-semibold text-gray-800 text-sm" x-text="detail.cheque.branch?.name || '—'"></div>
                        </div>
                    </div>

                    
                    <template x-if="detail.cheque.customer">
                        <div class="rounded-xl overflow-hidden" style="border:1px solid #bfdbfe">
                            <div class="px-4 py-2.5 flex items-center gap-2" style="background:#eff6ff;border-bottom:1px solid #bfdbfe">
                                <svg class="w-3.5 h-3.5" style="color:#2563eb" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-xs font-bold uppercase tracking-wide" style="color:#1d4ed8">Customer</span>
                            </div>
                            <div class="px-4 py-3 flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-black text-sm flex-shrink-0"
                                     style="background:#1B3EB6"
                                     x-text="(detail.cheque.customer?.name || '?').charAt(0).toUpperCase()"></div>
                                <div>
                                    <div class="font-bold text-gray-800 dark:text-gray-100" x-text="detail.cheque.customer?.name || '—'"></div>
                                    <div class="text-xs text-gray-400" x-text="detail.cheque.customer?.phone || detail.cheque.customer?.email || ''"></div>
                                </div>
                            </div>
                        </div>
                    </template>

                    
                    <template x-if="detail.cheque.invoice_links?.length">
                        <div class="rounded-xl overflow-hidden" style="border:1px solid #bfdbfe">
                            <div class="px-4 py-2.5 flex items-center gap-2" style="background:#eff6ff;border-bottom:1px solid #bfdbfe">
                                <svg class="w-3.5 h-3.5" style="color:#2563eb" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-xs font-bold uppercase tracking-wide" style="color:#1d4ed8">Customer Invoices Applied</span>
                            </div>
                            <div class="divide-y divide-blue-50">
                                <template x-for="lnk in detail.cheque.invoice_links" :key="lnk.id">
                                    <div x-show="lnk.invoice" class="px-4 py-3 flex items-center justify-between gap-3">
                                        <div>
                                            <div class="font-bold text-sm" style="color:#1d4ed8" x-text="lnk.invoice?.invoice_number || '—'"></div>
                                            <div class="text-xs text-gray-400 mt-0.5" x-text="fmtDate(lnk.invoice?.invoice_date)"></div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-black text-sm" style="color:#1d4ed8" x-text="fmtMoney(lnk.amount || 0)"></div>
                                            <div class="text-[10px] text-gray-400">applied</div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    
                    <template x-if="detail.cheque.supplier_payments?.length">
                        <div class="rounded-xl overflow-hidden" style="border:1px solid #fecaca">
                            <div class="px-4 py-2.5 flex items-center gap-2" style="background:#fef2f2;border-bottom:1px solid #fecaca">
                                <svg class="w-3.5 h-3.5" style="color:#dc2626" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-xs font-bold uppercase tracking-wide" style="color:#b91c1c">Handed to Supplier</span>
                            </div>
                            <div class="divide-y divide-red-50">
                                <template x-for="sp in detail.cheque.supplier_payments" :key="sp.id">
                                    <div class="px-4 py-3">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <div class="font-bold text-sm" style="color:#b91c1c"
                                                     x-text="sp.supplier_invoice?.supplier?.name || '—'"></div>
                                                <div class="text-xs text-gray-500 mt-0.5"
                                                     x-text="(sp.supplier_invoice?.invoice_number || '') + (sp.payment_date ? ' · ' + fmtDate(sp.payment_date) : '')"></div>
                                            </div>
                                            <div class="font-black text-sm" style="color:#b91c1c" x-text="fmtMoney(sp.amount || 0)"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    
                    <template x-if="detail.cheque.purchase_orders?.length">
                        <div class="rounded-xl overflow-hidden" style="border:1px solid #fde68a">
                            <div class="px-4 py-2.5 flex items-center gap-2" style="background:#fffbeb;border-bottom:1px solid #fde68a">
                                <svg class="w-3.5 h-3.5" style="color:#b45309" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span class="text-xs font-bold uppercase tracking-wide" style="color:#b45309">Reserved on Purchase Orders</span>
                            </div>
                            <div class="divide-y divide-yellow-50">
                                <template x-for="po in detail.cheque.purchase_orders" :key="po.id">
                                    <div class="px-4 py-3">
                                        <div class="flex items-center justify-between gap-3 mb-2">
                                            <div class="font-black text-sm" style="color:#b45309" x-text="po.po_number || '—'"></div>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold" style="background:#fef3c7;color:#92400e"
                                                  x-text="po.status || 'pending'"></span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
                                            <div>
                                                <span class="text-gray-400 font-semibold">Supplier: </span>
                                                <span class="text-gray-700 dark:text-gray-300 font-semibold" x-text="po.supplier?.name || '—'"></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-400 font-semibold">Order Date: </span>
                                                <span class="text-gray-700 dark:text-gray-300" x-text="fmtDate(po.order_date)"></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-400 font-semibold">Expected Delivery: </span>
                                                <span class="text-gray-700 dark:text-gray-300" x-text="fmtDate(po.expected_delivery_date) || '—'"></span>
                                            </div>
                                            <div>
                                                <span class="text-gray-400 font-semibold">Payment Method: </span>
                                                <span class="text-gray-700 dark:text-gray-300" x-text="po.payment_method || '—'"></span>
                                            </div>
                                            <template x-if="po.reference_number">
                                                <div class="col-span-2">
                                                    <span class="text-gray-400 font-semibold">Reference: </span>
                                                    <span class="text-gray-700 dark:text-gray-300" x-text="po.reference_number"></span>
                                                </div>
                                            </template>
                                            <template x-if="po.notes">
                                                <div class="col-span-2 mt-1 px-2.5 py-1.5 rounded-lg text-gray-600 dark:text-gray-400"
                                                     style="background:#fef9c3;border:1px solid #fde68a"
                                                     x-text="po.notes"></div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    
                    <template x-if="detail.cheque.expenses?.length">
                        <div class="rounded-xl overflow-hidden" style="border:1px solid #bbf7d0">
                            <div class="px-4 py-2.5 flex items-center gap-2" style="background:#f0fdf4;border-bottom:1px solid #bbf7d0">
                                <svg class="w-3.5 h-3.5" style="color:#15803d" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2zM10 8.5a.5.5 0 11-1 0 .5.5 0 011 0zm5 5a.5.5 0 11-1 0 .5.5 0 011 0z"/>
                                </svg>
                                <span class="text-xs font-bold uppercase tracking-wide" style="color:#15803d">Expenses Paid with This Cheque</span>
                            </div>
                            <div class="divide-y divide-green-50">
                                <template x-for="exp in detail.cheque.expenses" :key="exp.id">
                                    <div class="px-4 py-3">
                                        <div class="flex items-center justify-between gap-3 mb-1">
                                            <div>
                                                <div class="font-bold text-sm" style="color:#15803d"
                                                     x-text="exp.expense_number || '—'"></div>
                                                <div class="text-xs text-gray-600 mt-0.5" x-text="exp.description"></div>
                                                <div class="text-xs text-gray-400 mt-0.5"
                                                     x-text="(exp.account?.name || '') + (exp.expense_date ? ' · ' + fmtDate(exp.expense_date) : '')"></div>
                                            </div>
                                            <div class="text-right flex-shrink-0">
                                                <div class="font-black text-sm" style="color:#15803d"
                                                     x-text="fmtMoney(exp.amount || 0)"></div>
                                                <div class="text-[10px] mt-0.5 px-2 py-0.5 rounded-full font-semibold inline-block"
                                                     :class="exp.status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
                                                     x-text="exp.status"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    
                    <template x-if="detail.cheque.status === 'bounced'">
                        <div class="rounded-xl overflow-hidden" style="border:1px solid #fecaca">
                            <div class="px-4 py-2.5 flex items-center gap-2" style="background:#fef2f2;border-bottom:1px solid #fecaca">
                                <svg class="w-3.5 h-3.5" style="color:#dc2626" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                                </svg>
                                <span class="text-xs font-bold uppercase tracking-wide" style="color:#b91c1c">⚡ Bounce Details</span>
                            </div>
                            <div class="px-4 py-3 grid grid-cols-2 gap-3 text-xs">
                                <div>
                                    <div class="text-gray-400 font-semibold mb-0.5">Bounced Date</div>
                                    <div class="font-bold text-red-600" x-text="fmtDate(detail.cheque.bounced_date) || '—'"></div>
                                </div>
                                <div>
                                    <div class="text-gray-400 font-semibold mb-0.5">Reason</div>
                                    <div class="font-bold text-red-600" x-text="detail.cheque.bounce_reason || '—'"></div>
                                </div>
                            </div>
                        </div>
                    </template>

                    
                    <template x-if="detail.cheque.deposited_date || detail.cheque.cleared_date || detail.cheque.deposit_slip_number">
                        <div class="rounded-xl px-4 py-3 grid grid-cols-3 gap-3 text-xs" style="background:#f0fdf4;border:1px solid #bbf7d0">
                            <div>
                                <div class="text-gray-400 font-semibold mb-0.5">Deposited</div>
                                <div class="font-bold text-green-700" x-text="fmtDate(detail.cheque.deposited_date) || '—'"></div>
                            </div>
                            <div>
                                <div class="text-gray-400 font-semibold mb-0.5">Cleared</div>
                                <div class="font-bold text-green-700" x-text="fmtDate(detail.cheque.cleared_date) || '—'"></div>
                            </div>
                            <div>
                                <div class="text-gray-400 font-semibold mb-0.5">Slip #</div>
                                <div class="font-bold text-green-700" x-text="detail.cheque.deposit_slip_number || '—'"></div>
                            </div>
                        </div>
                    </template>

                    
                    <template x-if="detail.cheque.notes">
                        <div class="rounded-xl px-4 py-3 text-xs" style="background:#f8fafc;border:1px solid #e2e8f0">
                            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-1">Notes</div>
                            <div class="text-gray-600 dark:text-gray-300" x-text="detail.cheque.notes"></div>
                        </div>
                    </template>

                </div>

                
                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end"
                     style="background:#f8fafc">
                    <button @click="closeDetail()"
                            class="px-5 py-2 rounded-xl text-sm font-bold text-white transition-opacity hover:opacity-90"
                            style="background:#1B3EB6">Close</button>
                </div>

            </div>
        </div>
    </template>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function chequeHistory() {
    return {
        customers:        [],
        customerId:       '',
        selectedCustomer: null,
        cheques:          [],
        analysis:         null,
        loading:          false,
        searched:         false,
        quickRange:       '',

        filters: {
            chequeNumber: '',
            fromDate:     '',
            toDate:       '',
            status:       '',
        },

        detail: { open: false, cheque: null },

        get canSearch() {
            return !!(this.customerId || this.filters.chequeNumber || this.filters.fromDate || this.filters.toDate || this.filters.status);
        },

        async init() {
            try {
                const r = await apiFetch('/customers?per_page=500&is_active=1');
                const d = await r.json();
                this.customers = (d.data ?? d ?? []).sort((a, b) => a.name.localeCompare(b.name));
            } catch {
                toast('Failed to load customers', 'error');
            }

            // Pre-select customer from URL ?customer_id=X
            const params = new URLSearchParams(window.location.search);
            if (params.get('customer_id')) {
                this.customerId = params.get('customer_id');
                await this.loadHistory();
            }
        },

        onCustomerChange() {
            this.selectedCustomer = this.customers.find(c => c.id == this.customerId) || null;
            this.cheques  = [];
            this.analysis = null;
            this.searched = false;
            if (this.customerId) this.loadHistory();
        },

        setQuickRange(range) {
            this.quickRange = range;
            const now = new Date();
            const pad = n => String(n).padStart(2, '0');
            const iso = d => `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}`;

            if (range === 'this_month') {
                this.filters.fromDate = iso(new Date(now.getFullYear(), now.getMonth(), 1));
                this.filters.toDate   = iso(new Date(now.getFullYear(), now.getMonth()+1, 0));
            } else if (range === 'last_month') {
                this.filters.fromDate = iso(new Date(now.getFullYear(), now.getMonth()-1, 1));
                this.filters.toDate   = iso(new Date(now.getFullYear(), now.getMonth(), 0));
            } else if (range === 'this_year') {
                this.filters.fromDate = iso(new Date(now.getFullYear(), 0, 1));
                this.filters.toDate   = iso(new Date(now.getFullYear(), 11, 31));
            } else {
                this.filters.fromDate = '';
                this.filters.toDate   = '';
            }
            if (this.canSearch) this.loadHistory();
        },

        async loadHistory() {
            if (!this.canSearch) return;
            this.loading = true;
            try {
                const params = {};
                if (this.customerId)          { params.party_type = 'customer'; params.party_id = this.customerId; }
                if (this.filters.chequeNumber) params.cheque_number = this.filters.chequeNumber;
                if (this.filters.fromDate)     params.from_date     = this.filters.fromDate;
                if (this.filters.toDate)       params.to_date       = this.filters.toDate;
                if (this.filters.status)       params.status        = this.filters.status;

                const r = await apiFetch('/cheques/party-history?' + new URLSearchParams(params).toString());
                const d = await r.json();
                this.cheques  = d.cheques  ?? [];
                this.analysis = d.analysis ?? null;
                this.selectedCustomer = this.customers.find(c => c.id == this.customerId) || null;
                this.searched = true;
            } catch {
                toast('Failed to load cheque history', 'error');
            } finally {
                this.loading = false;
            }
        },

        openDetail(ch) { this.detail.cheque = ch; this.detail.open = true; },
        closeDetail()  { this.detail.open = false; this.detail.cheque = null; },

        statusLabel(s) {
            return {in_hand:'In Hand',deposited:'Deposited',cleared:'Cleared',bounced:'Bounced',cancelled:'Cancelled',returned:'Returned',transferred:'Transferred'}[s] ?? (s || '—');
        },
        statusBadgeClass(s) {
            return {in_hand:'chq-b-hand',deposited:'chq-b-dep',cleared:'chq-b-clear',bounced:'chq-b-bounce',cancelled:'chq-b-cancel',transferred:'chq-b-trans'}[s] ?? 'chq-b-cancel';
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/cheques/history.blade.php ENDPATH**/ ?>