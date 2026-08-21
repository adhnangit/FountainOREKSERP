<?php $__env->startSection('title', 'Cheque Register'); ?>
<?php $__env->startSection('page-title', 'Cheque Register'); ?>
<?php $__env->startSection('page-desc', 'Track and manage all cheques'); ?>

<?php $__env->startSection('content'); ?>
<style>
/* ── Stat Cards ── */
.chq-stats{display:grid;grid-template-columns:repeat(6,1fr);gap:14px;margin-bottom:22px}
@media(max-width:1200px){.chq-stats{grid-template-columns:repeat(3,1fr)}}
@media(max-width:640px){.chq-stats{grid-template-columns:repeat(2,1fr)}}
.chq-stat{background:#fff;border-radius:16px;padding:18px 20px;border:1px solid #e2e8f0;cursor:pointer;transition:box-shadow .2s,transform .2s,border-color .2s}
.chq-stat:hover{box-shadow:0 8px 24px rgba(0,0,0,.07);transform:translateY(-2px)}
.chq-stat.active{border-color:#1B3EB6;box-shadow:0 0 0 3px rgba(27,62,182,.12)}
.dark .chq-stat{background:#1e293b;border-color:#334155}
.dark .chq-stat.active{border-color:#6366f1}
.chq-stat-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-bottom:12px}
.chq-stat-icon svg{width:20px;height:20px}
.chq-stat-val{font-size:22px;font-weight:800;letter-spacing:-.5px;color:#0f172a;line-height:1.1}
.dark .chq-stat-val{color:#f1f5f9}
.chq-stat-lbl{font-size:11.5px;color:#94a3b8;font-weight:500;margin-top:3px}
.chq-stat-amt{font-size:11.5px;font-weight:600;margin-top:5px;color:#64748b}

/* ── Toolbar ── */
.chq-toolbar{background:#fff;border-radius:14px;padding:14px 18px;border:1px solid #e2e8f0;margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.dark .chq-toolbar{background:#1e293b;border-color:#334155}
.chq-search{position:relative;flex:1;min-width:200px;max-width:320px}
.chq-search svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none}
.chq-search input{width:100%;border:1px solid #e2e8f0;border-radius:9px;padding:7px 12px 7px 34px;font-size:13px;color:#1e293b;background:#f8fafc;outline:none;transition:border-color .15s,box-shadow .15s}
.chq-search input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);background:#fff}
.dark .chq-search input{background:#0f172a;border-color:#334155;color:#f1f5f9}
.chq-dir-btn{padding:6px 14px;border-radius:8px;border:1px solid #e2e8f0;font-size:12px;font-weight:600;cursor:pointer;background:#fff;color:#64748b;transition:all .15s;white-space:nowrap}
.chq-dir-btn:hover{background:#f1f5f9}
.chq-dir-btn.active{background:#1B3EB6;border-color:#1B3EB6;color:#fff}
.dark .chq-dir-btn{background:#1e293b;border-color:#334155;color:#94a3b8}

/* ── Tab Pills ── */
.chq-tabs{display:flex;gap:4px;background:#f1f5f9;border-radius:10px;padding:3px;flex-wrap:wrap}
.dark .chq-tabs{background:#0f172a}
.chq-tab{padding:5px 13px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;border:none;background:transparent;color:#64748b;transition:all .15s;white-space:nowrap;display:flex;align-items:center;gap:5px}
.chq-tab:hover{background:#e2e8f0;color:#334155}
.chq-tab.active{background:#fff;color:#1e293b;box-shadow:0 1px 4px rgba(0,0,0,.1)}
.dark .chq-tab.active{background:#1e293b;color:#f1f5f9}
.chq-tab-count{font-size:10px;font-weight:700;background:#e2e8f0;color:#475569;border-radius:20px;padding:0 6px;line-height:18px;min-width:18px;text-align:center}
.chq-tab.active .chq-tab-count{background:#1B3EB6;color:#fff}

/* ── Table ── */
.chq-table-card{background:#fff;border-radius:16px;border:1px solid #e2e8f0}
.dark .chq-table-card{background:#1e293b;border-color:#334155}
.chq-table{width:100%;border-collapse:separate;border-spacing:0}
.chq-table thead th{padding:10px 16px;font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e2e8f0;white-space:nowrap}
.dark .chq-table thead th{background:#0f172a;border-color:#334155}
.chq-table thead th:first-child{padding-left:22px;border-radius:16px 0 0 0}
.chq-table thead th:last-child{border-radius:0 16px 0 0}
.chq-table tbody tr{transition:background .1s;position:relative}
.chq-table tbody tr:hover{background:#f8faff}
.dark .chq-table tbody tr:hover{background:#0f172a}
.chq-table tbody td{padding:13px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.dark .chq-table tbody td{border-color:#334155}
.chq-table tbody td:first-child{padding-left:22px}
.chq-table tbody tr:last-child td{border-bottom:none}
.chq-table tbody tr:last-child td:first-child{border-bottom-left-radius:16px}
.chq-table tbody tr:last-child td:last-child{border-bottom-right-radius:16px}

/* ── Status Badges ── */
.chq-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap}
.chq-badge::before{content:'';width:6px;height:6px;border-radius:50%;flex-shrink:0}
.chq-b-hand{background:#fffbeb;color:#b45309}.chq-b-hand::before{background:#f59e0b}
.chq-b-dep{background:#eff6ff;color:#1d4ed8}.chq-b-dep::before{background:#3b82f6}
.chq-b-clear{background:#f0fdf4;color:#15803d}.chq-b-clear::before{background:#22c55e}
.chq-b-bounce{background:#fef2f2;color:#b91c1c}.chq-b-bounce::before{background:#ef4444}
.chq-b-cancel{background:#f8fafc;color:#64748b}.chq-b-cancel::before{background:#94a3b8}
.chq-b-ret{background:#f5f3ff;color:#7c3aed}.chq-b-ret::before{background:#8b5cf6}
.chq-b-trans{background:#ecfeff;color:#0e7490}.chq-b-trans::before{background:#06b6d4}
.chq-b-party{background:#ede9fe;color:#6d28d9;border:1px solid #c4b5fd;font-size:10px;padding:2px 8px;border-radius:20px;font-weight:700;display:inline-flex;align-items:center;gap:3px;line-height:1.5}

/* ── Action button ── */
.chq-act-btn{height:30px;padding:0 10px;border-radius:8px;font-size:12px;font-weight:600;border:1px solid;display:inline-flex;align-items:center;gap:5px;cursor:pointer;transition:all .15s;white-space:nowrap}
.chq-change-btn{background:#f8fafc;border-color:#e2e8f0;color:#475569}
.chq-change-btn:hover{background:#f1f5f9;border-color:#c7d2fe;color:#4f46e5}
.chq-rev-btn{background:#fff7ed;border-color:#fed7aa;color:#c2410c}
.chq-rev-btn:hover{background:#ffedd5;border-color:#fb923c}

/* ── Dark mode extras ── */
.dark .chq-stat-lbl{color:#64748b}
.dark .chq-stat-amt{color:#475569}
.dark .chq-table-card{color:#f1f5f9}
.dark .chq-change-btn{background:#0f172a;border-color:#334155;color:#94a3b8}
.dark .chq-change-btn:hover{background:#1e293b;border-color:#6366f1;color:#a5b4fc}

/* ── Overdue date ── */
.chq-overdue-date{color:#ef4444;font-weight:700}
</style>

<div x-data="chequesPage()" x-init="init()">

    
    <div class="chq-stats">
        <div class="chq-stat" :class="activeTab==='all'&&'active'" @click="setTab('all')">
            <div class="chq-stat-icon" style="background:#ede9fe">
                <svg fill="none" viewBox="0 0 24 24" stroke="#7c3aed" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
            </div>
            <div class="chq-stat-val" x-text="tabs.find(t=>t.key==='all')?.count||0"></div>
            <div class="chq-stat-lbl">Total Cheques</div>
            <div class="chq-stat-amt" x-text="fmtMoney(totalAmount('all'))"></div>
        </div>
        <div class="chq-stat" :class="activeTab==='in_hand'&&'active'" @click="setTab('in_hand')">
            <div class="chq-stat-icon" style="background:#fffbeb">
                <svg fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <div class="chq-stat-val" x-text="tabs.find(t=>t.key==='in_hand')?.count||0"></div>
            <div class="chq-stat-lbl">In Hand</div>
            <div class="chq-stat-amt" x-text="fmtMoney(totalAmount('in_hand'))"></div>
        </div>
        <div class="chq-stat" :class="activeTab==='deposited'&&'active'" @click="setTab('deposited')">
            <div class="chq-stat-icon" style="background:#eff6ff">
                <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2"><path d="M12 19V5m-7 7l7-7 7 7"/></svg>
            </div>
            <div class="chq-stat-val" x-text="tabs.find(t=>t.key==='deposited')?.count||0"></div>
            <div class="chq-stat-lbl">Deposited</div>
            <div class="chq-stat-amt" x-text="fmtMoney(totalAmount('deposited'))"></div>
        </div>
        <div class="chq-stat" :class="activeTab==='cleared'&&'active'" @click="setTab('cleared')">
            <div class="chq-stat-icon" style="background:#f0fdf4">
                <svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div class="chq-stat-val" x-text="tabs.find(t=>t.key==='cleared')?.count||0"></div>
            <div class="chq-stat-lbl">Cleared</div>
            <div class="chq-stat-amt" x-text="fmtMoney(totalAmount('cleared'))"></div>
        </div>
        <div class="chq-stat" :class="activeTab==='bounced'&&'active'" @click="setTab('bounced')">
            <div class="chq-stat-icon" style="background:#fef2f2">
                <svg fill="none" viewBox="0 0 24 24" stroke="#dc2626" stroke-width="2"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </div>
            <div class="chq-stat-val" x-text="tabs.find(t=>t.key==='bounced')?.count||0"></div>
            <div class="chq-stat-lbl">Bounced</div>
            <div class="chq-stat-amt" x-text="fmtMoney(totalAmount('bounced'))"></div>
        </div>
        <div class="chq-stat" :class="activeTab==='transferred'&&'active'" @click="setTab('transferred')">
            <div class="chq-stat-icon" style="background:#ecfeff">
                <svg fill="none" viewBox="0 0 24 24" stroke="#0891b2" stroke-width="2"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
            <div class="chq-stat-val" x-text="tabs.find(t=>t.key==='transferred')?.count||0"></div>
            <div class="chq-stat-lbl">Transferred</div>
            <div class="chq-stat-amt" x-text="fmtMoney(totalAmount('transferred'))"></div>
        </div>
    </div>

    
    <div class="chq-toolbar">
        <div class="chq-search">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
            <input x-model="search" @input="filterCheques()" type="text" placeholder="Search cheque #, party, bank…" />
        </div>

        <div class="flex gap-2 flex-wrap">
            <button @click="setDir('')"          :class="dirFilter===''&&'active'"         class="chq-dir-btn">All</button>
            <button @click="setDir('received')"  :class="dirFilter==='received'&&'active'" class="chq-dir-btn">📨 Received</button>
            <button @click="setDir('issued')"    :class="dirFilter==='issued'&&'active'"   class="chq-dir-btn">📄 Issued</button>
        </div>

        
        <div class="flex items-center gap-1.5 flex-wrap">
            <input type="date" x-model="fromDate" @change="filterCheques()"
                   class="border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1.5 text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-indigo-400" />
            <span class="text-xs text-gray-400">—</span>
            <input type="date" x-model="toDate" @change="filterCheques()"
                   class="border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1.5 text-xs bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 outline-none focus:border-indigo-400" />
            <button @click="fromDate=today; toDate=today; filterCheques()"
                    class="px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 transition-colors">
                Today
            </button>
            <button @click="fromDate=''; toDate=''; filterCheques()"
                    class="px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 text-xs font-semibold text-gray-500 hover:bg-gray-50 transition-colors">
                All Dates
            </button>
        </div>

        <div class="chq-tabs ml-auto">
            <template x-for="tab in tabs" :key="tab.key">
                <button @click="setTab(tab.key)" :class="activeTab===tab.key&&'active'" class="chq-tab">
                    <span x-text="tab.label"></span>
                    <span class="chq-tab-count" x-text="tab.count"></span>
                </button>
            </template>
        </div>
    </div>

    
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-9 h-9 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
                    <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7-7-7 7M5 15l7 7 7-7"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Received</p>
                    <p class="text-xs text-gray-400">Cheques from customers</p>
                </div>
                <div class="ml-auto text-right">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-green-100 text-green-700"
                          x-text="summaryReceived.count + ' cheque' + (summaryReceived.count===1?'':'s')"></span>
                </div>
            </div>
            <div class="text-2xl font-black text-green-700 tabular-nums mb-3" x-text="fmtMoney(summaryReceived.total)"></div>
            <div class="space-y-1.5 border-t border-gray-100 dark:border-gray-700 pt-3">
                <template x-if="summaryReceived.in_hand.count > 0">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-1.5 text-amber-600 font-medium">
                            <span class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"></span>In Hand
                            <span class="text-gray-400 font-normal" x-text="'(' + summaryReceived.in_hand.count + ')'"></span>
                        </span>
                        <span class="font-semibold text-amber-700 tabular-nums" x-text="fmtMoney(summaryReceived.in_hand.amount)"></span>
                    </div>
                </template>
                <template x-if="summaryReceived.transferred.count > 0">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-1.5 text-cyan-600 font-medium">
                            <span class="w-2 h-2 rounded-full bg-cyan-400 flex-shrink-0"></span>Transferred to Supplier
                            <span class="text-gray-400 font-normal" x-text="'(' + summaryReceived.transferred.count + ')'"></span>
                        </span>
                        <span class="font-semibold text-cyan-700 tabular-nums" x-text="fmtMoney(summaryReceived.transferred.amount)"></span>
                    </div>
                </template>
                <template x-if="summaryReceived.deposited.count > 0">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-1.5 text-blue-600 font-medium">
                            <span class="w-2 h-2 rounded-full bg-blue-400 flex-shrink-0"></span>Deposited to Bank
                            <span class="text-gray-400 font-normal" x-text="'(' + summaryReceived.deposited.count + ')'"></span>
                        </span>
                        <span class="font-semibold text-blue-700 tabular-nums" x-text="fmtMoney(summaryReceived.deposited.amount)"></span>
                    </div>
                </template>
                <template x-if="summaryReceived.cleared.count > 0">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-1.5 text-green-600 font-medium">
                            <span class="w-2 h-2 rounded-full bg-green-400 flex-shrink-0"></span>Cleared
                            <span class="text-gray-400 font-normal" x-text="'(' + summaryReceived.cleared.count + ')'"></span>
                        </span>
                        <span class="font-semibold text-green-700 tabular-nums" x-text="fmtMoney(summaryReceived.cleared.amount)"></span>
                    </div>
                </template>
                <template x-if="summaryReceived.bounced.count > 0">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-1.5 text-red-600 font-medium">
                            <span class="w-2 h-2 rounded-full bg-red-400 flex-shrink-0"></span>Bounced
                            <span class="text-gray-400 font-normal" x-text="'(' + summaryReceived.bounced.count + ')'"></span>
                        </span>
                        <span class="font-semibold text-red-700 tabular-nums" x-text="fmtMoney(summaryReceived.bounced.amount)"></span>
                    </div>
                </template>
                <template x-if="summaryReceived.count === 0">
                    <p class="text-xs text-gray-400 text-center py-1">No received cheques in period</p>
                </template>
            </div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="#dc2626" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7M5 9l7 7 7-7"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Issued</p>
                    <p class="text-xs text-gray-400">Cheques given to suppliers</p>
                </div>
                <div class="ml-auto text-right">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-100 text-red-700"
                          x-text="summaryIssued.count + ' cheque' + (summaryIssued.count===1?'':'s')"></span>
                </div>
            </div>
            <div class="text-2xl font-black text-red-700 tabular-nums mb-3" x-text="fmtMoney(summaryIssued.total)"></div>
            <div class="space-y-1.5 border-t border-gray-100 dark:border-gray-700 pt-3">
                <template x-if="summaryIssued.in_hand.count > 0">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-1.5 text-amber-600 font-medium">
                            <span class="w-2 h-2 rounded-full bg-amber-400 flex-shrink-0"></span>Pending Clearance
                            <span class="text-gray-400 font-normal" x-text="'(' + summaryIssued.in_hand.count + ')'"></span>
                        </span>
                        <span class="font-semibold text-amber-700 tabular-nums" x-text="fmtMoney(summaryIssued.in_hand.amount)"></span>
                    </div>
                </template>
                <template x-if="summaryIssued.deposited.count > 0">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-1.5 text-blue-600 font-medium">
                            <span class="w-2 h-2 rounded-full bg-blue-400 flex-shrink-0"></span>Presented to Bank
                            <span class="text-gray-400 font-normal" x-text="'(' + summaryIssued.deposited.count + ')'"></span>
                        </span>
                        <span class="font-semibold text-blue-700 tabular-nums" x-text="fmtMoney(summaryIssued.deposited.amount)"></span>
                    </div>
                </template>
                <template x-if="summaryIssued.cleared.count > 0">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-1.5 text-green-600 font-medium">
                            <span class="w-2 h-2 rounded-full bg-green-400 flex-shrink-0"></span>Cleared / Paid
                            <span class="text-gray-400 font-normal" x-text="'(' + summaryIssued.cleared.count + ')'"></span>
                        </span>
                        <span class="font-semibold text-green-700 tabular-nums" x-text="fmtMoney(summaryIssued.cleared.amount)"></span>
                    </div>
                </template>
                <template x-if="summaryIssued.bounced.count > 0">
                    <div class="flex items-center justify-between text-xs">
                        <span class="flex items-center gap-1.5 text-red-600 font-medium">
                            <span class="w-2 h-2 rounded-full bg-red-400 flex-shrink-0"></span>Bounced
                            <span class="text-gray-400 font-normal" x-text="'(' + summaryIssued.bounced.count + ')'"></span>
                        </span>
                        <span class="font-semibold text-red-700 tabular-nums" x-text="fmtMoney(summaryIssued.bounced.amount)"></span>
                    </div>
                </template>
                <template x-if="summaryIssued.count === 0">
                    <p class="text-xs text-gray-400 text-center py-1">No issued cheques in period</p>
                </template>
            </div>
        </div>

        
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
                     :class="summaryNet >= 0 ? 'bg-blue-100' : 'bg-orange-100'">
                    <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                         :stroke="summaryNet >= 0 ? '#1d4ed8' : '#c2410c'">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Net Position</p>
                    <p class="text-xs text-gray-400">Received − Issued</p>
                </div>
                <div class="ml-auto">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                          :class="summaryNet >= 0 ? 'bg-blue-100 text-blue-700' : 'bg-orange-100 text-orange-700'"
                          x-text="summaryNet >= 0 ? 'Inflow' : 'Outflow'"></span>
                </div>
            </div>
            <div class="text-2xl font-black tabular-nums mb-3"
                 :class="summaryNet >= 0 ? 'text-blue-700' : 'text-orange-700'"
                 x-text="(summaryNet < 0 ? '−' : '') + fmtMoney(Math.abs(summaryNet))"></div>
            <div class="space-y-1.5 border-t border-gray-100 dark:border-gray-700 pt-3">
                
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500 font-medium">Pending (In Hand)</span>
                    <span class="font-semibold tabular-nums"
                          :class="(summaryReceived.in_hand.amount - summaryIssued.in_hand.amount) >= 0 ? 'text-amber-600' : 'text-red-600'"
                          x-text="(summaryReceived.in_hand.amount - summaryIssued.in_hand.amount < 0 ? '−' : '') + fmtMoney(Math.abs(summaryReceived.in_hand.amount - summaryIssued.in_hand.amount))"></span>
                </div>
                
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500 font-medium">Transferred Out</span>
                    <span class="font-semibold text-cyan-700 tabular-nums" x-text="fmtMoney(summaryReceived.transferred.amount)"></span>
                </div>
                
                <div class="flex items-center justify-between text-xs">
                    <span class="text-gray-500 font-medium">Confirmed Cleared</span>
                    <span class="font-semibold tabular-nums"
                          :class="(summaryReceived.cleared.amount - summaryIssued.cleared.amount) >= 0 ? 'text-green-600' : 'text-red-600'"
                          x-text="(summaryReceived.cleared.amount - summaryIssued.cleared.amount < 0 ? '−' : '') + fmtMoney(Math.abs(summaryReceived.cleared.amount - summaryIssued.cleared.amount))"></span>
                </div>
                
                <template x-if="summaryReceived.bounced.count > 0 || summaryIssued.bounced.count > 0">
                    <div class="flex items-center justify-between text-xs pt-0.5 border-t border-red-100">
                        <span class="flex items-center gap-1 text-red-600 font-medium">
                            <svg style="width:11px;height:11px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            Bounced Risk
                        </span>
                        <span class="font-semibold text-red-600 tabular-nums"
                              x-text="fmtMoney(summaryReceived.bounced.amount + summaryIssued.bounced.amount)"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>

    
    <div class="chq-table-card">
        <template x-if="loading">
            <div class="flex items-center justify-center py-20 text-gray-400 gap-3">
                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                <span class="text-sm">Loading cheques…</span>
            </div>
        </template>

        <div x-show="!loading" class="overflow-x-auto">
            <table class="chq-table">
                <thead>
                    <tr>
                        <th>Cheque #</th>
                        <th>Party</th>
                        <th>Bank</th>
                        <th class="text-right">Amount</th>
                        <th>Received / Issued</th>
                        <th>Cheque Date</th>
                        <th>Direction</th>
                        <th>Status</th>
                        <th>Used For</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="ch in displayed" :key="ch.id">
                        <tr>
                            <td>
                                <span class="font-mono font-bold text-sm text-gray-800 dark:text-gray-100" x-text="ch.cheque_number || '—'"></span>
                            </td>
                            <td>
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                                         :style="'background:' + partyColor(ch.party_type)"
                                         x-text="partyInitial(ch)"></div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-800 dark:text-gray-100"
                                             x-text="ch.party_type==='customer'?(ch.customer?.name||'—'):(ch.supplier?.name||'—')"></div>
                                        <div class="text-xs text-gray-400 capitalize" x-text="ch.party_type||''"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm text-gray-600 dark:text-gray-300" x-text="ch.bank_name || '—'"></td>
                            <td class="text-right">
                                <span class="text-sm font-bold text-gray-800 dark:text-gray-100" x-text="fmtMoney(ch.amount||0)"></span>
                            </td>
                            <td class="text-sm text-gray-500 dark:text-gray-400" x-text="fmtDate(ch.received_issued_date)"></td>
                            <td>
                                <span :class="ch.status==='in_hand'&&new Date(ch.cheque_date)<new Date()?'chq-overdue-date':'text-sm text-gray-500 dark:text-gray-400'"
                                      x-text="fmtDate(ch.cheque_date)"></span>
                                <div x-show="ch.status==='in_hand'&&new Date(ch.cheque_date)<new Date()"
                                     class="text-xs text-red-500 font-semibold mt-0.5">Overdue</div>
                            </td>
                            <td>
                                <span class="chq-badge" :class="ch.direction==='received'?'chq-b-clear':'chq-b-hand'"
                                      x-text="ch.direction==='received'?'Received':'Issued'"></span>
                            </td>
                            <td>
                                <span class="chq-badge" :class="statusBadgeClass(ch.status)" x-text="statusLabel(ch.status)"></span>
                                <template x-if="ch.status === 'in_hand' && (ch.purchase_orders?.length || ch.supplier_payments?.length)">
                                    <div class="mt-1.5">
                                        <span class="chq-b-party">
                                            <svg class="w-2.5 h-2.5 flex-shrink-0" style="width:10px;height:10px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                                <path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                            </svg>
                                            Party Cheque
                                        </span>
                                        <div class="text-[10px] font-medium mt-0.5" style="color:#7c3aed" x-text="'Clears: ' + fmtDate(ch.cheque_date)"></div>
                                    </div>
                                </template>
                            </td>
                            
                            <td style="max-width:200px">
                                
                                <template x-for="sp in (ch.supplier_payments ?? [])" :key="sp.id">
                                    <div class="mb-1">
                                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-semibold bg-red-50 text-red-700 border border-red-200">
                                            <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                                            <span x-text="'Paid: ' + (sp.supplier_invoice?.supplier?.name ?? 'Supplier')"></span>
                                        </span>
                                        <div class="text-xs text-gray-400 mt-0.5 ml-1" x-text="sp.supplier_invoice?.invoice_number ?? ''"></div>
                                    </div>
                                </template>
                                
                                <template x-for="lnk in (ch.invoice_links ?? [])" :key="lnk.id">
                                    <div class="mb-1">
                                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span x-text="lnk.invoice?.invoice_number ?? ('INV-' + lnk.invoice_id)"></span>
                                        </span>
                                    </div>
                                </template>
                                
                                <template x-for="po in (ch.purchase_orders ?? [])" :key="po.id">
                                    <div class="mb-1">
                                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                            <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            <span x-text="'PO: ' + po.po_number"></span>
                                        </span>
                                    </div>
                                </template>
                                
                                <template x-for="exp in (ch.expenses ?? [])" :key="exp.id">
                                    <div class="mb-1">
                                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-semibold bg-green-50 text-green-700 border border-green-200">
                                            <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                            <span x-text="exp.expense_number || exp.description"></span>
                                        </span>
                                    </div>
                                </template>
                                
                                <div x-show="ch.notes && !(ch.supplier_payments?.length) && !(ch.invoice_links?.length) && !(ch.purchase_orders?.length) && !(ch.expenses?.length)"
                                     class="text-xs text-gray-400 italic truncate" x-text="ch.notes"></div>
                                
                                <span x-show="!(ch.supplier_payments?.length) && !(ch.invoice_links?.length) && !(ch.purchase_orders?.length) && !(ch.expenses?.length) && !ch.notes"
                                      class="text-xs text-gray-300">—</span>
                            </td>

                            <td>
                                <div class="flex items-center gap-2">
                                    
                                    <button @click="openHistory(ch)" title="View Full History"
                                            class="chq-act-btn" style="background:#f0f9ff;border-color:#bae6fd;color:#0369a1">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        History
                                    </button>
                                    <template x-if="canChange(ch)">
                                        <div x-data="{
                                            open: false,
                                            ddStyle: {},
                                            toggle(e) {
                                                if (!this.open) {
                                                    const r = e.currentTarget.getBoundingClientRect();
                                                    this.ddStyle = {
                                                        position:'fixed',
                                                        top: (r.bottom + 4) + 'px',
                                                        right: (window.innerWidth - r.right) + 'px',
                                                        zIndex: 9999,
                                                        minWidth: '144px'
                                                    };
                                                }
                                                this.open = !this.open;
                                            }
                                        }">
                                            <button @click="toggle($event)" class="chq-act-btn chq-change-btn">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M4 4v5h5M20 20v-5h-5M4 9a9 9 0 0114.1-3.9M20 15a9 9 0 01-14.1 3.9"/></svg>
                                                Change
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M19 9l-7 7-7-7"/></svg>
                                            </button>
                                            <div x-show="open" @click.outside="open=false" x-transition
                                                 :style="ddStyle"
                                                 class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-xl py-1">
                                                <button x-show="ch.status==='in_hand'"
                                                        @click="openConfirm(ch,'deposited');open=false"
                                                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-700 font-medium flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full bg-blue-400 flex-shrink-0"></span> Deposited
                                                </button>
                                                <button x-show="['in_hand','deposited','transferred'].includes(ch.status)"
                                                        @click="openConfirm(ch,'cleared');open=false"
                                                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-green-50 dark:hover:bg-green-900/20 text-green-700 font-medium flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full bg-green-400 flex-shrink-0"></span> Cleared
                                                    <template x-if="ch.status==='transferred'">
                                                        <span class="ml-auto text-[9px] font-bold px-1.5 py-0.5 rounded bg-green-100">by supplier</span>
                                                    </template>
                                                </button>
                                                <button x-show="['in_hand','deposited','transferred'].includes(ch.status)"
                                                        @click="openConfirm(ch,'bounced');open=false"
                                                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-red-50 dark:hover:bg-red-900/20 text-red-700 font-medium flex items-center gap-2">
                                                    <span class="w-2 h-2 rounded-full bg-red-400 flex-shrink-0"></span> Bounced
                                                    <template x-if="ch.status==='transferred'">
                                                        <span class="ml-auto text-[9px] font-bold px-1.5 py-0.5 rounded bg-red-100">returned by supplier</span>
                                                    </template>
                                                </button>
                                                <template x-if="ch.status==='in_hand'">
                                                    <div>
                                                        <div class="border-t border-gray-100 dark:border-gray-700 my-1"></div>
                                                        <button @click="openConfirm(ch,'cancelled');open=false"
                                                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-500 flex items-center gap-2">
                                                            <span class="w-2 h-2 rounded-full bg-gray-300 flex-shrink-0"></span> Cancelled
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <button x-show="isSuperAdmin && ['cleared','cancelled','bounced','transferred'].includes(ch.status)"
                                            @click="openReverse(ch)"
                                            class="chq-act-btn chq-rev-btn">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                                        Reverse
                                    </button>

                                    <span x-show="!canChange(ch)&&!(isSuperAdmin&&['cleared','cancelled','bounced'].includes(ch.status))"
                                          class="text-xs text-gray-400">—</span>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && displayed.length === 0">
                        <tr>
                            <td colspan="10" class="py-16 text-center">
                                <div class="flex flex-col items-center gap-3 text-gray-400">
                                    <svg class="w-12 h-12 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                                    <p class="text-sm font-medium">No cheques found</p>
                                    <p class="text-xs">Try adjusting your search or filter</p>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    
    <div x-show="confirm.open" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,0.55)" @click.self="closeConfirm()">
        <div x-show="confirm.open" x-transition
             class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">

            
            <div class="h-1.5 w-full"
                 :class="{
                    'bg-red-500':    confirm.newStatus==='bounced',
                    'bg-orange-400': confirm.newStatus==='cancelled',
                    'bg-blue-500':   confirm.newStatus==='deposited',
                    'bg-green-500':  confirm.newStatus==='cleared',
                 }"></div>

            <div class="p-6 space-y-5">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center"
                         :class="{
                            'bg-red-100':    confirm.newStatus==='bounced',
                            'bg-orange-100': confirm.newStatus==='cancelled',
                            'bg-blue-100':   confirm.newStatus==='deposited',
                            'bg-green-100':  confirm.newStatus==='cleared',
                         }">
                        <svg x-show="confirm.newStatus==='bounced'"   class="w-5 h-5 text-red-600"    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        <svg x-show="confirm.newStatus==='cancelled'" class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
                        <svg x-show="confirm.newStatus==='deposited'" class="w-5 h-5 text-blue-600"   fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        <svg x-show="confirm.newStatus==='cleared'"   class="w-5 h-5 text-green-600"  fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white text-base">
                            Mark as <span x-text="statusLabel(confirm.newStatus)"></span>
                        </h3>
                        <p class="text-sm text-gray-500 mt-0.5">Confirm the status change below.</p>
                    </div>
                </div>

                <div class="rounded-xl bg-gray-50 dark:bg-gray-700/40 p-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Cheque #</span>
                        <span class="font-mono font-bold text-gray-800 dark:text-gray-100" x-text="confirm.cheque?.cheque_number||'—'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Party</span>
                        <span class="font-semibold text-gray-800 dark:text-gray-100"
                              x-text="confirm.cheque?.party_type==='customer'?(confirm.cheque?.customer?.name||'—'):(confirm.cheque?.supplier?.name||'—')"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Amount</span>
                        <span class="font-black text-gray-900 dark:text-white" x-text="fmtMoney(confirm.cheque?.amount||0)"></span>
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-600 pt-2 flex justify-between items-center">
                        <span class="text-gray-500">Status change</span>
                        <div class="flex items-center gap-2">
                            <span class="chq-badge" :class="statusBadgeClass(confirm.cheque?.status)" x-text="statusLabel(confirm.cheque?.status)"></span>
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            <span class="chq-badge" :class="statusBadgeClass(confirm.newStatus)" x-text="statusLabel(confirm.newStatus)"></span>
                        </div>
                    </div>
                </div>

                <div x-show="confirm.newStatus==='bounced'"
                     class="rounded-xl p-3.5 text-sm space-y-1" style="background:#fef2f2;border:1px solid #fecaca">
                    <p class="font-bold text-red-700 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        Invoice payment will be reversed
                    </p>
                    <p class="text-red-600 text-xs">Linked invoices will be marked unpaid and the journal entry will be reversed automatically.</p>
                </div>

                <div x-show="confirm.newStatus==='bounced'" class="space-y-3">
                    <div>
                        <label class="label">Bounce Date</label>
                        <input type="date" x-model="confirm.bounced_date" class="input text-sm" />
                    </div>
                    <div>
                        <label class="label">Reason <span class="text-gray-400 text-xs">(optional)</span></label>
                        <input type="text" x-model="confirm.bounce_reason" placeholder="e.g. Insufficient funds" class="input text-sm" />
                    </div>
                </div>

                <div x-show="confirm.newStatus==='cancelled'"
                     class="rounded-xl p-3 text-xs" style="background:#fff7ed;border:1px solid #fed7aa">
                    <p class="text-orange-700">This cheque will be permanently cancelled. Only a Super Admin can reverse this action.</p>
                </div>

                <div x-show="['deposited','cleared'].includes(confirm.newStatus) && confirm.cheque?.direction==='received' && confirm.cheque?.status!=='transferred'" class="space-y-1">
                    <label class="label">Deposited Into <span class="text-gray-400 text-xs">(bank account)</span></label>
                    <select x-model.number="confirm.account_id" class="input text-sm">
                        <option :value="null">Select bank account…</option>
                        <template x-for="a in bankAccounts" :key="a.id">
                            <option :value="a.id" x-text="a.code + ' — ' + a.name"></option>
                        </template>
                    </select>
                    <p class="text-xs text-gray-400" x-show="!bankAccounts.length">No bank accounts in Chart of Accounts — add one first.</p>
                    <p class="text-xs text-amber-600" x-show="confirm.newStatus==='cleared' && !confirm.account_id && !confirm.cheque?.deposit_account_id">
                        Required to credit the correct bank account when this cheque clears.
                    </p>
                </div>

                <div x-show="confirm.newStatus==='cleared' && confirm.cheque?.status==='transferred'"
                     class="rounded-xl p-3 text-xs" style="background:#f0fdf4;border:1px solid #bbf7d0">
                    <p class="text-green-700">This cheque was handed over to a supplier, not deposited into our own bank — the payment to that supplier was already recorded when it was handed over. Marking it Cleared here just confirms the supplier successfully banked it; no new accounting entry is posted.</p>
                </div>

                <div class="flex gap-3 pt-1">
                    <button @click="closeConfirm()" :disabled="confirm.saving" class="flex-1 btn btn-secondary">Cancel</button>
                    <button @click="submitConfirm()" :disabled="confirm.saving"
                            class="flex-1 btn text-white"
                            :class="{
                                'bg-red-600 hover:bg-red-700':      confirm.newStatus==='bounced',
                                'bg-orange-500 hover:bg-orange-600':confirm.newStatus==='cancelled',
                                'bg-blue-600 hover:bg-blue-700':    confirm.newStatus==='deposited',
                                'bg-green-600 hover:bg-green-700':  confirm.newStatus==='cleared',
                            }">
                        <svg x-show="confirm.saving" class="animate-spin w-4 h-4 mr-1.5 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        <span x-text="confirm.saving?'Updating…':'Confirm'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    
    
    <div x-show="history.open" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,0.6)" @click.self="closeHistory()">
        <div x-show="history.open" x-transition
             class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-xl overflow-hidden flex flex-col"
             style="max-height:88vh">

            
            <div class="h-1.5 w-full flex-shrink-0"
                 :class="history.cheque?.direction === 'received' ? 'bg-green-500' : 'bg-amber-500'"></div>

            
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 flex items-start gap-3 flex-shrink-0">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                     :class="history.cheque?.direction === 'received' ? 'bg-green-100' : 'bg-amber-100'">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                         :stroke="history.cheque?.direction === 'received' ? '#16a34a' : '#b45309'">
                        <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center flex-wrap gap-1.5">
                        <h3 class="font-black text-gray-900 dark:text-white text-base font-mono"
                            x-text="history.cheque?.cheque_number || '—'"></h3>
                        <span class="chq-badge" :class="statusBadgeClass(history.cheque?.status)"
                              x-text="statusLabel(history.cheque?.status)"></span>
                        <template x-if="history.cheque?.status === 'in_hand' && (history.cheque?.purchase_orders?.length || history.cheque?.supplier_payments?.length)">
                            <span class="chq-b-party">
                                <svg style="width:10px;height:10px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                Party Cheque
                            </span>
                        </template>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5"
                       x-text="(history.cheque?.direction === 'received' ? '📨 Received from customer' : '📄 Issued to supplier') + ' · ' + (history.cheque?.bank_name || 'Unknown Bank')"></p>
                </div>
                <button @click="closeHistory()"
                        class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 transition-colors">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            
            <div class="overflow-y-auto flex-1 p-5 space-y-5">

                
                <div class="grid grid-cols-3 gap-3">
                    <div class="rounded-xl p-3 text-center bg-gray-50 dark:bg-gray-700/40">
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide mb-1">Amount</p>
                        <p class="font-black text-gray-900 dark:text-white text-lg leading-none" x-text="fmtMoney(history.cheque?.amount || 0)"></p>
                    </div>
                    <div class="rounded-xl p-3 text-center bg-gray-50 dark:bg-gray-700/40">
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide mb-1">Cheque Date</p>
                        <p class="font-bold text-gray-700 dark:text-gray-200 text-sm" x-text="fmtDate(history.cheque?.cheque_date)"></p>
                    </div>
                    <div class="rounded-xl p-3 text-center bg-gray-50 dark:bg-gray-700/40">
                        <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide mb-1"
                           x-text="history.cheque?.direction === 'received' ? 'Received On' : 'Issued On'"></p>
                        <p class="font-bold text-gray-700 dark:text-gray-200 text-sm" x-text="fmtDate(history.cheque?.received_issued_date)"></p>
                    </div>
                </div>

                
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-2"
                       x-text="history.cheque?.direction === 'received' ? 'Received From' : 'Issued To'"></p>
                    <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-600">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                             :style="'background:' + (history.cheque?.party_type === 'customer' ? '#1B3EB6' : '#059669')"
                             x-text="(history.cheque?.party_type === 'customer'
                                 ? (history.cheque?.customer?.name || '?')
                                 : (history.cheque?.supplier?.name || '?')
                             ).charAt(0).toUpperCase()"></div>
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm"
                               x-text="history.cheque?.party_type === 'customer'
                                   ? (history.cheque?.customer?.name || '—')
                                   : (history.cheque?.supplier?.name || '—')"></p>
                            <p class="text-xs text-gray-400 capitalize" x-text="history.cheque?.party_type || ''"></p>
                        </div>
                    </div>
                </div>

                
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Transaction History</span>
                    <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                </div>

                
                <template x-if="history.cheque?.invoice_links?.length">
                    <div>
                        <p class="text-[10px] font-bold text-blue-600 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Applied to Customer Invoices
                        </p>
                        <div class="space-y-2">
                            <template x-for="lnk in history.cheque.invoice_links" :key="lnk.id">
                                <div class="flex items-center justify-between p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                                    <div>
                                        <p class="font-mono font-bold text-blue-700 text-sm"
                                           x-text="lnk.invoice?.invoice_number || ('INV-' + lnk.invoice_id)"></p>
                                        <p class="text-xs text-blue-500 mt-0.5">Customer Sales Invoice</p>
                                    </div>
                                    <span class="font-black text-blue-700 text-sm" x-text="fmtMoney(lnk.amount || 0)"></span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                
                <template x-if="history.cheque?.supplier_payments?.length">
                    <div>
                        <p class="text-[10px] font-bold text-red-600 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                            Handed Over to Suppliers
                        </p>
                        <div class="space-y-2">
                            <template x-for="sp in history.cheque.supplier_payments" :key="sp.id">
                                <div class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-bold text-red-700 text-sm" x-text="sp.supplier_invoice?.supplier?.name || 'Supplier'"></p>
                                            <p class="text-xs font-mono text-red-500 mt-0.5" x-text="sp.supplier_invoice?.invoice_number || '—'"></p>
                                        </div>
                                        <span class="font-black text-red-700 text-sm" x-text="fmtMoney(sp.amount || 0)"></span>
                                    </div>
                                    <div class="mt-1.5 text-[10px] text-red-400 font-medium" x-text="'Payment Date: ' + fmtDate(sp.payment_date)"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                
                <template x-if="history.cheque?.purchase_orders?.length">
                    <div>
                        <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Reserved on Purchase Orders
                        </p>
                        <div class="space-y-3">
                            <template x-for="po in history.cheque.purchase_orders" :key="po.id">
                                <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 overflow-hidden">
                                    
                                    <div class="flex items-center justify-between px-3 py-2.5 border-b border-amber-100 dark:border-amber-800/50">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                            <span class="font-mono font-black text-amber-800 dark:text-amber-200 text-sm" x-text="po.po_number"></span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold px-2 py-0.5 rounded-full capitalize"
                                                  :class="{
                                                      'bg-green-100 text-green-700':  ['confirmed','approved'].includes(po.status),
                                                      'bg-blue-100 text-blue-700':    ['received','partially_received'].includes(po.status),
                                                      'bg-gray-100 text-gray-600':    po.status === 'draft',
                                                      'bg-red-100 text-red-700':      po.status === 'cancelled',
                                                      'bg-amber-100 text-amber-700':  !['confirmed','approved','received','partially_received','draft','cancelled'].includes(po.status),
                                                  }"
                                                  x-text="(po.status || '').replace(/_/g,' ')"></span>
                                            <span class="font-black text-amber-700 dark:text-amber-300 text-sm" x-text="fmtMoney(po.total || 0)"></span>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 text-sm">
                                        <div class="px-3 py-2 border-r border-amber-100 dark:border-amber-800/50">
                                            <p class="text-[9.5px] font-bold text-amber-500 uppercase tracking-wide mb-0.5">Supplier</p>
                                            <p class="font-semibold text-gray-800 dark:text-gray-100" x-text="po.supplier?.name || '—'"></p>
                                            <p class="text-xs text-gray-400 mt-0.5" x-show="po.supplier?.code" x-text="po.supplier?.code"></p>
                                        </div>
                                        <div class="px-3 py-2">
                                            <p class="text-[9.5px] font-bold text-amber-500 uppercase tracking-wide mb-0.5">Order Date</p>
                                            <p class="font-semibold text-gray-700 dark:text-gray-200" x-text="fmtDate(po.order_date)"></p>
                                        </div>
                                        <div class="px-3 py-2 border-t border-r border-amber-100 dark:border-amber-800/50">
                                            <p class="text-[9.5px] font-bold text-amber-500 uppercase tracking-wide mb-0.5">Expected Delivery</p>
                                            <p class="font-semibold text-gray-700 dark:text-gray-200" x-text="po.expected_date ? fmtDate(po.expected_date) : '—'"></p>
                                        </div>
                                        <div class="px-3 py-2 border-t border-amber-100 dark:border-amber-800/50">
                                            <p class="text-[9.5px] font-bold text-amber-500 uppercase tracking-wide mb-0.5">Payment Method</p>
                                            <p class="font-semibold text-gray-700 dark:text-gray-200 capitalize"
                                               x-text="(po.payment_method || '—').replace(/_/g,' ')"></p>
                                        </div>
                                    </div>
                                    
                                    <template x-if="po.reference || po.notes">
                                        <div class="px-3 py-2 border-t border-amber-100 dark:border-amber-800/50 space-y-0.5">
                                            <p x-show="po.reference" class="text-xs text-gray-500">
                                                <span class="font-semibold">Ref:</span> <span x-text="po.reference"></span>
                                            </p>
                                            <p x-show="po.notes" class="text-xs text-gray-400 italic" x-text="po.notes"></p>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                
                <template x-if="history.cheque?.expenses?.length">
                    <div>
                        <p class="text-[10px] font-bold text-green-600 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                            Expenses Paid with This Cheque
                        </p>
                        <div class="space-y-2">
                            <template x-for="exp in history.cheque.expenses" :key="exp.id">
                                <div class="p-3 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-mono font-bold text-green-700 text-sm" x-text="exp.expense_number || '—'"></p>
                                            <p class="text-xs text-green-600 mt-0.5" x-text="exp.description"></p>
                                            <p class="text-xs text-gray-400 mt-0.5" x-text="exp.account?.name || ''"></p>
                                        </div>
                                        <div class="text-right">
                                            <span class="font-black text-green-700 text-sm" x-text="fmtMoney(exp.amount || 0)"></span>
                                            <div class="text-[10px] mt-0.5 px-2 py-0.5 rounded-full font-semibold inline-block"
                                                 :class="exp.status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
                                                 x-text="exp.status"></div>
                                        </div>
                                    </div>
                                    <div class="mt-1.5 text-[10px] text-green-400 font-medium" x-text="'Expense Date: ' + fmtDate(exp.expense_date)"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                
                <template x-if="history.cheque?.status === 'bounced'">
                    <div class="p-4 rounded-xl" style="background:#fef2f2;border:1px solid #fecaca">
                        <p class="font-bold text-red-700 text-sm flex items-center gap-1.5 mb-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            Cheque Bounced
                        </p>
                        <div class="text-xs text-red-600 space-y-1">
                            <div x-text="'Bounce Date: ' + fmtDate(history.cheque.bounced_date)"></div>
                            <div x-show="history.cheque.bounce_reason" x-text="'Reason: ' + history.cheque.bounce_reason"></div>
                        </div>
                    </div>
                </template>

                
                <template x-if="history.cheque?.notes">
                    <div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-2">Notes</p>
                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-700/40 text-sm text-gray-600 dark:text-gray-300 leading-relaxed"
                             x-text="history.cheque.notes"></div>
                    </div>
                </template>

                
                <template x-if="!history.cheque?.invoice_links?.length && !history.cheque?.supplier_payments?.length && !history.cheque?.purchase_orders?.length && !history.cheque?.expenses?.length && !history.cheque?.notes">
                    <div class="text-center py-8 text-gray-400">
                        <svg class="w-12 h-12 mx-auto mb-3 opacity-25" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                            <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <p class="text-sm font-medium">No transaction history yet</p>
                        <p class="text-xs mt-1">This cheque hasn't been applied to any invoice or payment</p>
                    </div>
                </template>

            </div>

            
            <div class="p-4 border-t border-gray-100 dark:border-gray-700 flex-shrink-0 flex justify-end">
                <button @click="closeHistory()" class="btn btn-secondary text-sm px-5">Close</button>
            </div>

        </div>
    </div>

    <div x-show="reverse.open" x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         style="background:rgba(0,0,0,0.55)" @click.self="closeReverse()">
        <div x-show="reverse.open" x-transition
             class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">

            <div class="h-1.5 bg-orange-400 w-full"></div>

            <div class="p-6 space-y-5">
                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-gray-900 dark:text-white text-base">Reverse Cheque Status</h3>
                            <span class="text-xs px-2 py-0.5 rounded-full font-bold" style="background:#fff7ed;color:#c2410c">Super Admin</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5">Override — revert this cheque to a previous state.</p>
                    </div>
                </div>

                <div class="rounded-xl bg-gray-50 dark:bg-gray-700/40 p-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Cheque #</span>
                        <span class="font-mono font-bold" x-text="reverse.cheque?.cheque_number||'—'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Amount</span>
                        <span class="font-black" x-text="fmtMoney(reverse.cheque?.amount||0)"></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-500">Current status</span>
                        <span class="chq-badge" :class="statusBadgeClass(reverse.cheque?.status)" x-text="statusLabel(reverse.cheque?.status)"></span>
                    </div>
                </div>

                <div>
                    <label class="label">Revert to <span class="text-red-500">*</span></label>
                    <select x-model="reverse.newStatus" class="input text-sm">
                        <option value="">Select target status…</option>
                        <option value="in_hand">In Hand</option>
                        <option value="deposited">Deposited</option>
                        <option value="transferred">Transferred</option>
                    </select>
                </div>
                <div>
                    <label class="label">Reason for reversal <span class="text-red-500">*</span></label>
                    <input type="text" x-model="reverse.reason" placeholder="e.g. Status set incorrectly" class="input text-sm" />
                </div>

                <div class="rounded-xl p-3.5 text-xs" style="background:#fff7ed;border:1px solid #fed7aa">
                    <p class="font-bold text-orange-700 mb-1">⚠ Journal entries will NOT be auto-reversed</p>
                    <p class="text-orange-600">Manually correct any accounting entries in the Journal if needed.</p>
                </div>

                <div class="flex gap-3 pt-1">
                    <button @click="closeReverse()" :disabled="reverse.saving" class="flex-1 btn btn-secondary">Cancel</button>
                    <button @click="submitReverse()" :disabled="reverse.saving||!reverse.newStatus||!reverse.reason.trim()"
                            class="flex-1 btn text-white" style="background:#ea580c">
                        <svg x-show="reverse.saving" class="animate-spin w-4 h-4 mr-1.5 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        <span x-text="reverse.saving?'Reversing…':'Reverse Status'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function chequesPage() {
    return {
        items:        [],
        displayed:    [],
        loading:      true,
        search:       '',
        activeTab:    'all',
        dirFilter:    '',
        isSuperAdmin: false,
        today:        new Date().toISOString().slice(0, 10),
        fromDate:     '',
        toDate:       '',
        bankAccounts: [],

        get summaryItems() {
            return this.items.filter(c => {
                const d = (c.received_issued_date ?? c.cheque_date ?? '').slice(0, 10);
                return (!this.fromDate || d >= this.fromDate) && (!this.toDate || d <= this.toDate);
            });
        },
        get summaryReceived() {
            const list = this.summaryItems.filter(c => c.direction === 'received');
            const byStatus = (s) => {
                const sub = list.filter(c => c.status === s);
                return { count: sub.length, amount: sub.reduce((a, c) => a + parseFloat(c.amount||0), 0) };
            };
            return {
                count: list.length,
                total: list.reduce((s, c) => s + parseFloat(c.amount||0), 0),
                in_hand:     byStatus('in_hand'),
                transferred: byStatus('transferred'),
                deposited:   byStatus('deposited'),
                cleared:     byStatus('cleared'),
                bounced:     byStatus('bounced'),
            };
        },
        get summaryIssued() {
            const list = this.summaryItems.filter(c => c.direction === 'issued');
            const byStatus = (s) => {
                const sub = list.filter(c => c.status === s);
                return { count: sub.length, amount: sub.reduce((a, c) => a + parseFloat(c.amount||0), 0) };
            };
            return {
                count: list.length,
                total: list.reduce((s, c) => s + parseFloat(c.amount||0), 0),
                in_hand:   byStatus('in_hand'),
                deposited: byStatus('deposited'),
                cleared:   byStatus('cleared'),
                bounced:   byStatus('bounced'),
                cancelled: byStatus('cancelled'),
            };
        },
        get summaryNet() { return this.summaryReceived.total - this.summaryIssued.total; },

        tabs: [
            { key:'all',         label:'All',         count:0 },
            { key:'in_hand',     label:'In Hand',     count:0 },
            { key:'deposited',   label:'Deposited',   count:0 },
            { key:'cleared',     label:'Cleared',     count:0 },
            { key:'bounced',     label:'Bounced',     count:0 },
            { key:'cancelled',   label:'Cancelled',   count:0 },
            { key:'transferred', label:'Transferred', count:0 },
        ],

        confirm: {
            open:false, saving:false, cheque:null, newStatus:'',
            bounced_date: new Date().toISOString().slice(0,10), bounce_reason:'',
        },

        reverse: { open:false, saving:false, cheque:null, newStatus:'', reason:'' },

        history: { open: false, cheque: null },

        async init() {
            try {
                const u = localStorage.getItem('medri_user');
                if (u) {
                    const user = JSON.parse(u);
                    this.isSuperAdmin = user?.roles?.includes('super_admin') || false;
                }
            } catch {}
            try {
                const r = await apiFetch('/cheques?per_page=500');
                const d = await r.json();
                this.items = d.data ?? d ?? [];
                this.filterCheques();
            } catch {
                toast('Failed to load cheques', 'error');
            } finally {
                this.loading = false;
            }
            try {
                const accR = await (await apiFetch('/accounting/accounts')).json();
                const accounts = Array.isArray(accR) ? accR : (accR.data ?? []);
                this.bankAccounts = accounts.filter(a => a.is_bank_account);
            } catch {}
        },

        setTab(key) { this.activeTab = key; this.filterCheques(); },
        setDir(dir) { this.dirFilter = dir; this.filterCheques(); },

        // Same date-window predicate used everywhere (tiles, tabs, and the table itself)
        // so the counts shown can never drift from what's actually listed below them.
        inDateRange(c) {
            if (!this.fromDate && !this.toDate) return true;
            const d = (c.received_issued_date ?? c.cheque_date ?? '').slice(0, 10);
            return (!this.fromDate || d >= this.fromDate) && (!this.toDate || d <= this.toDate);
        },

        get dateFiltered() {
            let list = this.items.filter(c => this.inDateRange(c));
            if (this.dirFilter) list = list.filter(c => c.direction === this.dirFilter);
            return list;
        },

        totalAmount(status) {
            const list = status === 'all' ? this.dateFiltered : this.dateFiltered.filter(c => c.status === status);
            return list.reduce((s, c) => s + parseFloat(c.amount || 0), 0);
        },

        updateCounts() {
            this.tabs.forEach(t => {
                t.count = t.key==='all' ? this.dateFiltered.length : this.dateFiltered.filter(c=>c.status===t.key).length;
            });
        },

        filterCheques() {
            this.updateCounts();
            let list = this.dateFiltered;
            if (this.activeTab !== 'all') list = list.filter(c => c.status === this.activeTab);
            const q = this.search.toLowerCase();
            if (q) list = list.filter(c =>
                (c.cheque_number??'').toLowerCase().includes(q) ||
                (c.customer?.name??c.supplier?.name??'').toLowerCase().includes(q) ||
                (c.bank_name??'').toLowerCase().includes(q)
            );
            this.displayed = list;
        },

        canChange(ch) { return !['cleared','cancelled'].includes(ch.status); },

        partyInitial(ch) {
            const name = ch.party_type==='customer' ? ch.customer?.name : ch.supplier?.name;
            return (name||'?').charAt(0).toUpperCase();
        },
        partyColor(type) { return type==='customer' ? '#1B3EB6' : '#059669'; },

        openConfirm(ch, status) {
            if (!status) return;
            this.confirm = { open:true, saving:false, cheque:ch, newStatus:status,
                             bounced_date:new Date().toISOString().slice(0,10), bounce_reason:'',
                             account_id: ch.deposit_account_id ?? null };
        },
        closeConfirm() { this.confirm.open = false; },

        async submitConfirm() {
            const { cheque, newStatus, bounced_date, bounce_reason, account_id } = this.confirm;
            if (!cheque || !newStatus) return;
            if (newStatus === 'cleared' && cheque.direction === 'received' && cheque.status !== 'transferred' && !account_id && !cheque.deposit_account_id) {
                toast('Select which bank account this cheque was deposited into', 'error');
                return;
            }
            this.confirm.saving = true;
            try {
                const body = { status: newStatus };
                if (newStatus === 'bounced') { body.bounced_date = bounced_date; body.bounce_reason = bounce_reason||null; }
                if (['deposited','cleared'].includes(newStatus) && cheque.direction === 'received' && account_id) {
                    body.account_id = account_id;
                }
                const r = await apiFetch('/cheques/'+cheque.id, { method:'PUT', body:JSON.stringify(body) });
                if (r.ok) {
                    const updated = await r.json();
                    const idx = this.items.findIndex(c=>c.id===cheque.id);
                    if (idx >= 0) this.items[idx] = { ...this.items[idx], ...updated };
                    this.filterCheques(); this.closeConfirm();
                    toast('Status updated to '+this.statusLabel(newStatus), 'success');
                } else {
                    const e = await r.json();
                    toast(e.message||'Failed to update', 'error');
                }
            } catch { toast('Failed to update status', 'error'); }
            finally { this.confirm.saving = false; }
        },

        openHistory(ch) { this.history = { open: true, cheque: ch }; },
        closeHistory() { this.history.open = false; },

        openReverse(ch) {
            this.reverse = { open:true, saving:false, cheque:ch, newStatus:'', reason:'' };
        },
        closeReverse() { this.reverse.open = false; },

        async submitReverse() {
            const { cheque, newStatus, reason } = this.reverse;
            if (!cheque || !newStatus || !reason.trim()) return;
            this.reverse.saving = true;
            try {
                const r = await apiFetch('/cheques/'+cheque.id, {
                    method:'PUT',
                    body:JSON.stringify({ status:newStatus, notes:'[REVERSED] '+reason }),
                });
                if (r.ok) {
                    const updated = await r.json();
                    const idx = this.items.findIndex(c=>c.id===cheque.id);
                    if (idx >= 0) this.items[idx] = { ...this.items[idx], ...updated };
                    this.filterCheques(); this.closeReverse();
                    toast('Cheque reversed to '+this.statusLabel(newStatus), 'success');
                } else {
                    const e = await r.json();
                    toast(e.message||'Failed to reverse', 'error');
                }
            } catch { toast('Failed to reverse status', 'error'); }
            finally { this.reverse.saving = false; }
        },

        statusLabel(s) {
            return {in_hand:'In Hand',deposited:'Deposited',cleared:'Cleared',bounced:'Bounced',cancelled:'Cancelled',returned:'Returned',transferred:'Transferred'}[s] ?? (s||'—');
        },
        statusBadgeClass(s) {
            return {in_hand:'chq-b-hand',deposited:'chq-b-dep',cleared:'chq-b-clear',bounced:'chq-b-bounce',cancelled:'chq-b-cancel',returned:'chq-b-ret',transferred:'chq-b-trans'}[s] ?? 'chq-b-cancel';
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views/cheques/index.blade.php ENDPATH**/ ?>