<?php $__env->startSection('title', 'Cheque Calendar'); ?>
<?php $__env->startSection('page-title', 'Cheque Calendar'); ?>
<?php $__env->startSection('page-desc', 'Track cheques by due date'); ?>

<?php $__env->startSection('content'); ?>
<style>
/* ── Calendar Layout ── */
.chq-cal-wrap { display: flex; flex-direction: column; gap: 16px; }

/* ── Alert Strip ── */
.chq-alert-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 12px; }
@media(max-width:900px)  { .chq-alert-grid { grid-template-columns: repeat(2,1fr); } }
@media(max-width:500px)  { .chq-alert-grid { grid-template-columns: 1fr 1fr; } }
.chq-alert-card { background:#fff; border-radius:14px; padding:14px 16px; border:1px solid #e2e8f0; display:flex; align-items:center; gap:12px; }
.dark .chq-alert-card { background:#1e293b; border-color:#334155; }
.chq-alert-icon { width:38px; height:38px; border-radius:11px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.chq-alert-val  { font-size:18px; font-weight:800; line-height:1.1; color:#0f172a; }
.dark .chq-alert-val { color:#f1f5f9; }
.chq-alert-lbl  { font-size:11px; color:#94a3b8; font-weight:500; margin-top:2px; }
.chq-alert-amt  { font-size:11px; font-weight:600; margin-top:2px; }

/* ── Calendar Header ── */
.chq-cal-header { background:#fff; border-radius:14px; border:1px solid #e2e8f0; padding:14px 20px; display:flex; align-items:center; gap:12px; }
.dark .chq-cal-header { background:#1e293b; border-color:#334155; }

/* ── Month Grid ── */
.chq-cal-grid  { background:#fff; border-radius:16px; border:1px solid #e2e8f0; overflow:hidden; }
.dark .chq-cal-grid { background:#1e293b; border-color:#334155; }
.chq-cal-days-header { display:grid; grid-template-columns:repeat(7,1fr); }
.chq-cal-day-hdr { padding:10px 8px; text-align:center; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; background:#f8fafc; border-bottom:1px solid #e2e8f0; }
.dark .chq-cal-day-hdr { background:#0f172a; border-color:#334155; }
.chq-cal-cells { display:grid; grid-template-columns:repeat(7,1fr); }
.chq-cal-cell  {
    min-height: 110px; padding: 8px; border-right: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9;
    cursor: pointer; transition: background .12s; position: relative; display: flex; flex-direction: column; gap: 4px;
}
.dark .chq-cal-cell { border-color: #1e293b; }
.chq-cal-cell:nth-child(7n) { border-right: none; }
.chq-cal-cell:hover { background: #f8faff; }
.dark .chq-cal-cell:hover { background: #0f172a; }
.chq-cal-cell.other-month { background: #fafafa; }
.dark .chq-cal-cell.other-month { background: #0f172a; }
.chq-cal-cell.other-month .chq-day-num { color: #cbd5e1; }
.chq-cal-cell.is-today { background: #eff6ff; }
.dark .chq-cal-cell.is-today { background: rgba(99,102,241,.08); }
.chq-cal-cell.has-overdue { background: #fff5f5; }
.dark .chq-cal-cell.has-overdue { background: rgba(239,68,68,.06); }
.chq-day-num { font-size: 13px; font-weight: 700; color: #334155; line-height: 1; margin-bottom: 2px; display: flex; align-items: center; gap: 5px; }
.dark .chq-day-num { color: #94a3b8; }
.chq-today-dot { width: 22px; height: 22px; border-radius: 50%; background: #1B3EB6; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 12px; font-weight: 800; }

/* ── Cheque Pills in cell ── */
.chq-pill { font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 20px; display: flex; align-items: center; gap: 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer; max-width: 100%; }
.chq-pill-hand   { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
.chq-pill-dep    { background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe; }
.chq-pill-clear  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.chq-pill-bounce { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.chq-pill-trans  { background: #ecfeff; color: #0e7490; border: 1px solid #a5f3fc; }
.chq-pill-cancel { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
.chq-pill-more   { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-size: 10px; }

/* ── Day Detail Panel ── */
.chq-detail-overlay { position: fixed; inset: 0; z-index: 50; display: flex; align-items: flex-end; justify-content: flex-end; padding: 20px; pointer-events: none; }
.chq-detail-panel {
    width: 420px; max-height: 85vh; background: #fff; border-radius: 20px; box-shadow: 0 25px 60px rgba(0,0,0,.18);
    border: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden; pointer-events: all;
    transform: translateX(0); transition: transform .25s cubic-bezier(.4,0,.2,1), opacity .2s;
}
.dark .chq-detail-panel { background: #1e293b; border-color: #334155; }
@media(max-width:600px) { .chq-detail-overlay { padding: 0; align-items: flex-end; justify-content: center; } .chq-detail-panel { width: 100%; border-radius: 20px 20px 0 0; max-height: 80vh; } }

/* ── Legend ── */
.chq-legend { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
.chq-legend-item { display: flex; align-items: center; gap: 5px; font-size: 11px; font-weight: 600; color: #64748b; }
.chq-legend-dot  { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }

.dark .chq-alert-card  { color: #f1f5f9; }
.dark .chq-cal-cell.other-month  { opacity: .6; }
.dark .chq-cal-cell.is-today { background: rgba(99,102,241,.1); }
</style>

<div x-data="chequeCalendar()" x-init="init()" class="chq-cal-wrap">

    
    <div class="chq-alert-grid">
        <div class="chq-alert-card" style="border-color:#fecaca">
            <div class="chq-alert-icon" style="background:#fef2f2">
                <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="#dc2626" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <div>
                <div class="chq-alert-val text-red-600" x-text="overdue.count"></div>
                <div class="chq-alert-lbl">Overdue</div>
                <div class="chq-alert-amt text-red-500" x-text="fmtMoney(overdue.amount)"></div>
            </div>
        </div>
        <div class="chq-alert-card" style="border-color:#fde68a">
            <div class="chq-alert-icon" style="background:#fffbeb">
                <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <div class="chq-alert-val text-amber-600" x-text="dueToday.count"></div>
                <div class="chq-alert-lbl">Due Today</div>
                <div class="chq-alert-amt text-amber-500" x-text="fmtMoney(dueToday.amount)"></div>
            </div>
        </div>
        <div class="chq-alert-card" style="border-color:#bfdbfe">
            <div class="chq-alert-icon" style="background:#eff6ff">
                <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <div>
                <div class="chq-alert-val text-blue-600" x-text="dueWeek.count"></div>
                <div class="chq-alert-lbl">Due This Week</div>
                <div class="chq-alert-amt text-blue-500" x-text="fmtMoney(dueWeek.amount)"></div>
            </div>
        </div>
        <div class="chq-alert-card">
            <div class="chq-alert-icon" style="background:#ede9fe">
                <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="#7c3aed" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <div>
                <div class="chq-alert-val" x-text="monthTotal.count"></div>
                <div class="chq-alert-lbl" x-text="monthName + ' Total'"></div>
                <div class="chq-alert-amt" x-text="fmtMoney(monthTotal.amount)"></div>
            </div>
        </div>
    </div>

    
    <div class="chq-cal-header">
        <button @click="prevMonth()"
                class="w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-600 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M15 19l-7-7 7-7"/></svg>
        </button>
        <h2 class="text-base font-black text-gray-900 dark:text-white min-w-[160px] text-center" x-text="monthYear"></h2>
        <button @click="nextMonth()"
                class="w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-600 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 5l7 7-7 7"/></svg>
        </button>
        <button @click="goToday()"
                class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-colors ml-1">
            Today
        </button>

        
        <div class="flex gap-1.5 ml-4">
            <button @click="dirFilter=''"         :class="dirFilter===''?'bg-gray-800 dark:bg-indigo-600 text-white':'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">All</button>
            <button @click="dirFilter='received'" :class="dirFilter==='received'?'bg-green-600 text-white':'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">📨 Received</button>
            <button @click="dirFilter='issued'"   :class="dirFilter==='issued'?'bg-red-500 text-white':'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 border border-gray-200 dark:border-gray-600'"
                    class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">📄 Issued</button>
        </div>

        <div class="ml-auto flex items-center gap-2" x-show="loading">
            <svg class="animate-spin w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            <span class="text-xs text-gray-400">Loading…</span>
        </div>

        
        <div class="chq-legend ml-auto" x-show="!loading">
            <div class="chq-legend-item"><div class="chq-legend-dot" style="background:#f59e0b"></div>In Hand</div>
            <div class="chq-legend-item"><div class="chq-legend-dot" style="background:#06b6d4"></div>Transferred</div>
            <div class="chq-legend-item"><div class="chq-legend-dot" style="background:#3b82f6"></div>Deposited</div>
            <div class="chq-legend-item"><div class="chq-legend-dot" style="background:#22c55e"></div>Cleared</div>
            <div class="chq-legend-item"><div class="chq-legend-dot" style="background:#ef4444"></div>Bounced</div>
        </div>
    </div>

    
    <div class="chq-cal-grid">
        <div class="chq-cal-days-header">
            <template x-for="d in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="d">
                <div class="chq-cal-day-hdr" x-text="d"></div>
            </template>
        </div>
        <div class="chq-cal-cells">
            <template x-for="cell in calendarCells" :key="cell.key">
                <div class="chq-cal-cell"
                     :class="{
                         'other-month': !cell.currentMonth,
                         'is-today':     cell.isToday,
                         'has-overdue':  cell.hasOverdue && !cell.isToday,
                     }"
                     @click="openDay(cell)">

                    
                    <div class="chq-day-num">
                        <template x-if="cell.isToday">
                            <span class="chq-today-dot" x-text="cell.day"></span>
                        </template>
                        <template x-if="!cell.isToday">
                            <span x-text="cell.day"
                                  :class="cell.hasOverdue ? 'text-red-500 font-black' : (cell.currentMonth ? '' : 'text-gray-300 dark:text-gray-600')"></span>
                        </template>
                        <template x-if="cell.hasOverdue && !cell.isToday">
                            <span class="text-[9px] font-bold text-red-500 ml-auto">OVERDUE</span>
                        </template>
                    </div>

                    
                    <template x-for="(ch, idx) in cell.cheques.slice(0, 3)" :key="ch.id">
                        <div class="chq-pill" :class="pillClass(ch)"
                             :title="pillTitle(ch)"
                             @click.stop="openChequeDetail(ch)">
                            <span x-text="ch.direction === 'received' ? '↓' : '↑'" class="flex-shrink-0" style="font-size:9px"></span>
                            <span class="truncate" x-text="(ch.customer?.name || ch.supplier?.name || '—').split(' ')[0]"></span>
                            <span class="ml-auto flex-shrink-0 font-black" x-text="fmtK(ch.amount)"></span>
                        </div>
                    </template>
                    <template x-if="cell.cheques.length > 3">
                        <div class="chq-pill chq-pill-more" @click.stop="openDay(cell)">
                            +<span x-text="cell.cheques.length - 3"></span> more
                        </div>
                    </template>

                    
                    <template x-if="cell.cheques.length > 0">
                        <div class="text-[10px] font-bold mt-auto pt-1 text-gray-400 tabular-nums text-right"
                             x-text="fmtMoney(cell.cheques.reduce((s,c)=>s+parseFloat(c.amount||0),0))"></div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    
    <div x-show="dayPanel.open" x-transition.opacity
         class="fixed inset-0 z-40"
         style="background:rgba(0,0,0,.35)"
         @click.self="dayPanel.open = false">
    </div>
    <div x-show="dayPanel.open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-x-8"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-8"
         class="fixed right-5 top-20 bottom-5 z-50 w-full max-w-[420px] bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden"
         style="max-height:calc(100vh - 100px)">

        
        <div class="flex-shrink-0 p-5 border-b border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-0.5">Cheque Due Date</p>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white" x-text="dayPanel.dateLabel"></h3>
                </div>
                <div class="flex items-center gap-2">
                    <div class="text-right">
                        <div class="text-xs text-gray-400" x-text="dayPanel.cheques.length + ' cheque' + (dayPanel.cheques.length===1?'':'s')"></div>
                        <div class="text-sm font-black text-gray-800 dark:text-white tabular-nums"
                             x-text="fmtMoney(dayPanel.cheques.reduce((s,c)=>s+parseFloat(c.amount||0),0))"></div>
                    </div>
                    <button @click="dayPanel.open = false"
                            class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 flex items-center justify-center transition-colors">
                        <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            
            <div class="flex flex-wrap gap-1.5 mt-3">
                <template x-for="grp in dayBreakdown" :key="grp.status">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                          :class="grp.cls"
                          x-text="grp.label + ' ' + grp.count + ' · ' + fmtMoney(grp.amount)"></span>
                </template>
            </div>
        </div>

        
        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            <template x-if="dayPanel.cheques.length === 0">
                <div class="text-center py-12 text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <p class="text-sm font-medium">No cheques on this date</p>
                </div>
            </template>
            <template x-for="ch in dayPanel.cheques" :key="ch.id">
                <div class="rounded-xl border p-3.5 transition-all hover:shadow-md"
                     :class="cardClass(ch)">
                    
                    <div class="flex items-start justify-between gap-2 mb-2.5">
                        <div class="flex items-center gap-2 min-w-0">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                                 :style="'background:' + (ch.direction==='received' ? '#1B3EB6' : '#059669')"
                                 x-text="(ch.direction==='received'?(ch.customer?.name||'?'):(ch.supplier?.name||'?')).charAt(0).toUpperCase()"></div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-900 dark:text-white truncate"
                                   x-text="ch.direction==='received'?(ch.customer?.name||'—'):(ch.supplier?.name||'—')"></p>
                                <p class="text-[10px] text-gray-400 capitalize" x-text="ch.party_type"></p>
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-base font-black tabular-nums"
                               :class="ch.direction==='received' ? 'text-green-700' : 'text-red-700'"
                               x-text="fmtMoney(ch.amount)"></p>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                                  :class="ch.direction==='received' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                  x-text="ch.direction==='received' ? '↓ Received' : '↑ Issued'"></span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-1.5 text-xs mb-2.5">
                        <div class="bg-white/60 dark:bg-gray-700/40 rounded-lg p-2">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">Cheque #</p>
                            <p class="font-mono font-bold text-gray-700 dark:text-gray-200" x-text="ch.cheque_number || '—'"></p>
                        </div>
                        <div class="bg-white/60 dark:bg-gray-700/40 rounded-lg p-2">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">Bank</p>
                            <p class="font-semibold text-gray-700 dark:text-gray-200 truncate" x-text="ch.bank_name || '—'"></p>
                        </div>
                        <div class="bg-white/60 dark:bg-gray-700/40 rounded-lg p-2">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">Status</p>
                            <span class="inline-flex items-center gap-1 font-bold" :class="statusTextClass(ch.status)" x-text="statusLabel(ch.status)"></span>
                        </div>
                        <div class="bg-white/60 dark:bg-gray-700/40 rounded-lg p-2">
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wide mb-0.5">Received / Issued</p>
                            <p class="font-semibold text-gray-700 dark:text-gray-200" x-text="fmtDate(ch.received_issued_date)"></p>
                        </div>
                    </div>
                    
                    <template x-if="ch.status==='in_hand' && ch.cheque_date < todayStr">
                        <div class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg mb-2.5 text-xs font-semibold text-red-700"
                             style="background:#fef2f2;border:1px solid #fecaca">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            Overdue — cheque date has passed but still In Hand
                        </div>
                    </template>

                    
                    <div class="mt-2.5 border-t border-gray-200/70 dark:border-gray-600/50 pt-2.5 space-y-2">
                        <p class="text-[9.5px] font-bold text-gray-400 uppercase tracking-widest">Transaction History</p>

                        
                        <template x-if="ch.invoice_links?.length">
                            <div class="space-y-1.5">
                                <p class="text-[9px] font-bold text-blue-500 uppercase tracking-wide flex items-center gap-1">
                                    <svg style="width:10px;height:10px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Applied to Customer Invoices
                                </p>
                                <template x-for="lnk in ch.invoice_links" :key="lnk.id">
                                    <div class="px-2.5 py-2 rounded-lg text-xs"
                                         style="background:#eff6ff;border:1px solid #bfdbfe">
                                        <div class="flex items-center justify-between mb-1">
                                            <div class="flex items-center gap-1.5">
                                                <div class="w-6 h-6 rounded-md bg-blue-600 flex items-center justify-center text-white font-bold text-[9px] flex-shrink-0"
                                                     x-text="(lnk.invoice?.customer?.name||'?').charAt(0).toUpperCase()"></div>
                                                <div>
                                                    <p class="font-bold text-blue-800"
                                                       x-text="lnk.invoice?.customer?.name || ch.customer?.name || '—'"></p>
                                                    <p class="text-[9px] text-blue-400">Customer</p>
                                                </div>
                                            </div>
                                            <span class="font-black text-blue-700 tabular-nums" x-text="fmtMoney(lnk.amount||0)"></span>
                                        </div>
                                        <div class="flex items-center gap-1.5 pt-1 border-t border-blue-100">
                                            <svg style="width:9px;height:9px" fill="none" viewBox="0 0 24 24" stroke="#3b82f6" stroke-width="2.5"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            <span class="font-mono font-semibold text-blue-600"
                                                  x-text="lnk.invoice?.invoice_number || ('INV-'+lnk.invoice_id)"></span>
                                            <span class="text-blue-300">·</span>
                                            <span class="text-blue-400">Customer Invoice</span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        
                        <template x-if="ch.supplier_payments?.length">
                            <div class="space-y-1.5">
                                <p class="text-[9px] font-bold text-red-500 uppercase tracking-wide flex items-center gap-1">
                                    <svg style="width:10px;height:10px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    Handed Over to Supplier
                                </p>
                                <template x-for="sp in ch.supplier_payments" :key="sp.id">
                                    <div class="px-2.5 py-2 rounded-lg text-xs"
                                         style="background:#fef2f2;border:1px solid #fecaca">
                                        
                                        <div class="flex items-center justify-between mb-1.5">
                                            <div class="flex items-center gap-1.5">
                                                <div class="w-6 h-6 rounded-md bg-red-500 flex items-center justify-center text-white font-bold text-[9px] flex-shrink-0"
                                                     x-text="(sp.supplier_invoice?.supplier?.name || sp.purchase_order?.supplier?.name || '?').charAt(0).toUpperCase()"></div>
                                                <div>
                                                    <p class="font-bold text-red-800"
                                                       x-text="sp.supplier_invoice?.supplier?.name || sp.purchase_order?.supplier?.name || '—'"></p>
                                                    <p class="text-[9px] text-red-400">Supplier</p>
                                                </div>
                                            </div>
                                            <span class="font-black text-red-700 tabular-nums" x-text="fmtMoney(sp.amount||0)"></span>
                                        </div>
                                        
                                        <div class="flex flex-wrap items-center gap-1.5 pt-1 border-t border-red-100">
                                            <template x-if="sp.supplier_invoice?.invoice_number">
                                                <span class="inline-flex items-center gap-1 font-mono text-[9px] font-semibold text-red-600">
                                                    <svg style="width:8px;height:8px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                    <span x-text="sp.supplier_invoice.invoice_number"></span>
                                                </span>
                                            </template>
                                            <template x-if="sp.purchase_order?.po_number">
                                                <span class="inline-flex items-center gap-1 font-mono text-[9px] font-semibold text-red-600">
                                                    <svg style="width:8px;height:8px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                                    PO: <span x-text="sp.purchase_order.po_number"></span>
                                                </span>
                                            </template>
                                            <template x-if="sp.payment_date">
                                                <span class="text-[9px] text-red-400" x-text="'· ' + fmtDate(sp.payment_date)"></span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        
                        <template x-if="ch.purchase_orders?.length">
                            <div class="space-y-1.5">
                                <p class="text-[9px] font-bold text-amber-500 uppercase tracking-wide flex items-center gap-1">
                                    <svg style="width:10px;height:10px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    Reserved on Purchase Order
                                </p>
                                <template x-for="po in ch.purchase_orders" :key="po.id">
                                    <div class="flex items-center justify-between px-2.5 py-2 rounded-lg text-xs"
                                         style="background:#fffbeb;border:1px solid #fde68a">
                                        <div>
                                            <p class="font-mono font-bold text-amber-700" x-text="po.po_number"></p>
                                            <p class="text-[9px] text-amber-500 mt-0.5" x-text="po.supplier?.name || '—'"></p>
                                        </div>
                                        <span class="font-bold text-amber-700 capitalize text-[10px] px-2 py-0.5 rounded-full bg-amber-100"
                                              x-text="(po.status||'').replace(/_/g,' ')"></span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        
                        <template x-if="ch.expenses?.length">
                            <div class="space-y-1.5">
                                <p class="text-[9px] font-bold text-green-600 uppercase tracking-wide flex items-center gap-1">
                                    <svg style="width:10px;height:10px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                                    Expenses Paid with This Cheque
                                </p>
                                <template x-for="exp in ch.expenses" :key="exp.id">
                                    <div class="px-2.5 py-2 rounded-lg text-xs"
                                         style="background:#f0fdf4;border:1px solid #bbf7d0">
                                        <div class="flex items-center justify-between mb-1">
                                            <div>
                                                <p class="font-mono font-bold text-green-700" x-text="exp.expense_number || '—'"></p>
                                                <p class="text-[9px] text-green-600 mt-0.5 truncate" x-text="exp.description"></p>
                                                <p class="text-[9px] text-gray-400 mt-0.5" x-text="exp.account?.name || ''"></p>
                                            </div>
                                            <div class="text-right flex-shrink-0">
                                                <span class="font-black text-green-700 tabular-nums" x-text="fmtMoney(exp.amount||0)"></span>
                                                <div class="text-[9px] mt-0.5" x-text="fmtDate(exp.expense_date)"></div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        
                        <template x-if="ch.status==='bounced'">
                            <div class="px-2.5 py-2 rounded-lg text-xs"
                                 style="background:#fef2f2;border:1px solid #fecaca">
                                <p class="font-bold text-red-700 flex items-center gap-1 mb-1">
                                    <svg style="width:11px;height:11px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                    Cheque Bounced
                                </p>
                                <p class="text-red-600" x-text="'Bounce Date: ' + fmtDate(ch.bounced_date)"></p>
                                <p x-show="ch.bounce_reason" class="text-red-500 mt-0.5" x-text="'Reason: ' + ch.bounce_reason"></p>
                            </div>
                        </template>

                        
                        <template x-if="ch.deposited_date || ch.cleared_date">
                            <div class="flex gap-2">
                                <template x-if="ch.deposited_date">
                                    <div class="flex-1 px-2.5 py-2 rounded-lg text-xs" style="background:#eff6ff;border:1px solid #bfdbfe">
                                        <p class="text-[9px] font-bold text-blue-400 uppercase mb-0.5">Deposited</p>
                                        <p class="font-bold text-blue-700" x-text="fmtDate(ch.deposited_date)"></p>
                                        <p x-show="ch.deposit_slip_number" class="text-[9px] text-blue-400 mt-0.5 font-mono" x-text="ch.deposit_slip_number"></p>
                                    </div>
                                </template>
                                <template x-if="ch.cleared_date">
                                    <div class="flex-1 px-2.5 py-2 rounded-lg text-xs" style="background:#f0fdf4;border:1px solid #bbf7d0">
                                        <p class="text-[9px] font-bold text-green-400 uppercase mb-0.5">Cleared</p>
                                        <p class="font-bold text-green-700" x-text="fmtDate(ch.cleared_date)"></p>
                                    </div>
                                </template>
                            </div>
                        </template>

                        
                        <template x-if="ch.notes">
                            <div class="px-2.5 py-2 rounded-lg text-xs bg-gray-50 dark:bg-gray-700/40 border border-gray-200 dark:border-gray-600">
                                <p class="text-[9px] font-bold text-gray-400 uppercase mb-0.5">Notes</p>
                                <p class="text-gray-600 dark:text-gray-300 leading-relaxed" x-text="ch.notes"></p>
                            </div>
                        </template>

                        
                        <template x-if="!ch.invoice_links?.length && !ch.supplier_payments?.length && !ch.purchase_orders?.length && !ch.expenses?.length && !ch.deposited_date && !ch.cleared_date && !ch.notes && ch.status!=='bounced'">
                            <p class="text-[10px] text-gray-400 italic text-center py-1">No transaction history yet</p>
                        </template>
                    </div>

                    
                    <div class="flex gap-2 pt-3 mt-1 border-t border-gray-200/70 dark:border-gray-600/50">
                        <a href="<?php echo e(url('/cheques')); ?>"
                           class="flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 text-xs font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex-shrink-0">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            Register
                        </a>

                        
                        <template x-if="canChange(ch)">
                            <div class="flex-1 relative" x-data="{ ddOpen: false }">
                                <button @click="ddOpen=!ddOpen"
                                        class="w-full flex items-center justify-center gap-1.5 py-1.5 rounded-lg text-xs font-bold text-white transition-colors"
                                        style="background:#1B3EB6">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M4 4v5h5M20 20v-5h-5M4 9a9 9 0 0114.1-3.9M20 15a9 9 0 01-14.1 3.9"/></svg>
                                    Update Status
                                    <svg class="w-3 h-3 transition-transform" :class="ddOpen?'rotate-180':''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="ddOpen" @click.outside="ddOpen=false" x-transition
                                     class="absolute bottom-full mb-1.5 left-0 right-0 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-600 rounded-xl shadow-xl py-1 z-10">
                                    <div class="px-3 py-1.5 border-b border-gray-100 dark:border-gray-700">
                                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wide">Current: <span :class="statusTextClass(ch.status)" x-text="statusLabel(ch.status)"></span></p>
                                    </div>
                                    <template x-if="ch.status==='in_hand'">
                                        <div>
                                            <button @click="openStatusModal(ch,'deposited');ddOpen=false"
                                                    class="w-full text-left px-3.5 py-2.5 text-xs hover:bg-blue-50 dark:hover:bg-blue-900/20 font-semibold flex items-center gap-2.5">
                                                <span class="w-7 h-7 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                    <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2.5"><path d="M12 19V5m-7 7l7-7 7 7"/></svg>
                                                </span>
                                                <div>
                                                    <p class="text-blue-700 font-bold">Mark Deposited</p>
                                                    <p class="text-[9px] text-gray-400 font-normal">Cheque submitted to bank</p>
                                                </div>
                                            </button>
                                            <button @click="openStatusModal(ch,'cleared');ddOpen=false"
                                                    class="w-full text-left px-3.5 py-2.5 text-xs hover:bg-green-50 dark:hover:bg-green-900/20 font-semibold flex items-center gap-2.5">
                                                <span class="w-7 h-7 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                                                    <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </span>
                                                <div>
                                                    <p class="text-green-700 font-bold">Mark Cleared</p>
                                                    <p class="text-[9px] text-gray-400 font-normal">Payment confirmed by bank</p>
                                                </div>
                                            </button>
                                            <button @click="openStatusModal(ch,'bounced');ddOpen=false"
                                                    class="w-full text-left px-3.5 py-2.5 text-xs hover:bg-red-50 dark:hover:bg-red-900/20 font-semibold flex items-center gap-2.5">
                                                <span class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                                    <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="#dc2626" stroke-width="2.5"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                                </span>
                                                <div>
                                                    <p class="text-red-700 font-bold">Mark Bounced</p>
                                                    <p class="text-[9px] text-gray-400 font-normal">Cheque returned / dishonored</p>
                                                </div>
                                            </button>
                                            <div class="border-t border-gray-100 dark:border-gray-700 mx-2 my-0.5"></div>
                                            <button @click="openStatusModal(ch,'cancelled');ddOpen=false"
                                                    class="w-full text-left px-3.5 py-2.5 text-xs hover:bg-gray-50 dark:hover:bg-gray-700 font-semibold flex items-center gap-2.5">
                                                <span class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                                    <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
                                                </span>
                                                <div>
                                                    <p class="text-gray-500 font-bold">Cancel Cheque</p>
                                                    <p class="text-[9px] text-gray-400 font-normal">Void — no longer valid</p>
                                                </div>
                                            </button>
                                        </div>
                                    </template>
                                    <template x-if="ch.status==='deposited'">
                                        <div>
                                            <button @click="openStatusModal(ch,'cleared');ddOpen=false"
                                                    class="w-full text-left px-3.5 py-2.5 text-xs hover:bg-green-50 dark:hover:bg-green-900/20 font-semibold flex items-center gap-2.5">
                                                <span class="w-7 h-7 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                                                    <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                </span>
                                                <div>
                                                    <p class="text-green-700 font-bold">Mark Cleared</p>
                                                    <p class="text-[9px] text-gray-400 font-normal">Bank confirmed settlement</p>
                                                </div>
                                            </button>
                                            <button @click="openStatusModal(ch,'bounced');ddOpen=false"
                                                    class="w-full text-left px-3.5 py-2.5 text-xs hover:bg-red-50 dark:hover:bg-red-900/20 font-semibold flex items-center gap-2.5">
                                                <span class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                                    <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="#dc2626" stroke-width="2.5"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                                </span>
                                                <div>
                                                    <p class="text-red-700 font-bold">Mark Bounced</p>
                                                    <p class="text-[9px] text-gray-400 font-normal">Bank returned cheque</p>
                                                </div>
                                            </button>
                                        </div>
                                    </template>
                                    
                                    <template x-if="ch.status==='transferred'">
                                        <div>
                                            <div class="px-3.5 py-2 border-b border-gray-100 dark:border-gray-700">
                                                <p class="text-[9px] text-gray-400">This cheque was given to a supplier. If the supplier's bank returned it, mark it as Bounced.</p>
                                            </div>
                                            <button @click="openStatusModal(ch,'bounced');ddOpen=false"
                                                    class="w-full text-left px-3.5 py-2.5 text-xs hover:bg-red-50 dark:hover:bg-red-900/20 font-semibold flex items-center gap-2.5">
                                                <span class="w-7 h-7 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                                    <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="#dc2626" stroke-width="2.5"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                                </span>
                                                <div>
                                                    <p class="text-red-700 font-bold">Mark Bounced</p>
                                                    <p class="text-[9px] text-gray-400 font-normal">Supplier's bank returned the cheque</p>
                                                </div>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>

                        
                        <template x-if="!canChange(ch)">
                            <div class="flex-1 flex items-center justify-center">
                                <span class="text-[10px] font-bold px-3 py-1.5 rounded-lg"
                                      :class="{
                                          'bg-cyan-100 text-cyan-700':   ch.status==='transferred',
                                          'bg-green-100 text-green-700': ch.status==='cleared',
                                          'bg-red-100 text-red-700':     ch.status==='bounced',
                                          'bg-gray-100 text-gray-500':   ['cancelled','returned'].includes(ch.status),
                                      }"
                                      x-text="statusLabel(ch.status) + ' — No further action'"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    
    <div x-show="sModal.open" x-transition.opacity
         class="fixed inset-0 z-[60] flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.55)" @click.self="sModal.open=false">
        <div x-show="sModal.open" x-transition
             class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-sm max-h-[90vh] overflow-y-auto">
            <div class="h-1.5 w-full"
                 :class="{
                     'bg-blue-500':   sModal.newStatus==='deposited',
                     'bg-green-500':  sModal.newStatus==='cleared',
                     'bg-red-500':    sModal.newStatus==='bounced',
                     'bg-orange-400': sModal.newStatus==='cancelled',
                 }"></div>
            <div class="p-5 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0"
                         :class="{
                             'bg-blue-100':   sModal.newStatus==='deposited',
                             'bg-green-100':  sModal.newStatus==='cleared',
                             'bg-red-100':    sModal.newStatus==='bounced',
                             'bg-orange-100': sModal.newStatus==='cancelled',
                         }">
                        <svg x-show="sModal.newStatus==='deposited'" class="w-5 h-5 text-blue-600"   fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 19V5m-7 7l7-7 7 7"/></svg>
                        <svg x-show="sModal.newStatus==='cleared'"   class="w-5 h-5 text-green-600"  fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <svg x-show="sModal.newStatus==='bounced'"   class="w-5 h-5 text-red-600"    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        <svg x-show="sModal.newStatus==='cancelled'" class="w-5 h-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/></svg>
                    </div>
                    <div>
                        <h3 class="font-black text-gray-900 dark:text-white">
                            Mark as <span x-text="statusLabel(sModal.newStatus)"></span>
                        </h3>
                        <p class="text-xs text-gray-400 mt-0.5">Confirm the status change below.</p>
                    </div>
                </div>

                
                <div class="rounded-xl bg-gray-50 dark:bg-gray-700/40 p-3.5 text-sm space-y-1.5">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Cheque #</span>
                        <span class="font-mono font-bold dark:text-white" x-text="sModal.cheque?.cheque_number||'—'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Party</span>
                        <span class="font-semibold dark:text-white"
                              x-text="sModal.cheque?.direction==='received'?(sModal.cheque?.customer?.name||'—'):(sModal.cheque?.supplier?.name||'—')"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Amount</span>
                        <span class="font-black dark:text-white" x-text="fmtMoney(sModal.cheque?.amount||0)"></span>
                    </div>
                    <div class="flex justify-between items-center pt-1 border-t border-gray-200 dark:border-gray-600">
                        <span class="text-gray-500">Status change</span>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-semibold" :class="statusTextClass(sModal.cheque?.status)" x-text="statusLabel(sModal.cheque?.status)"></span>
                            <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            <span class="text-xs font-semibold" :class="statusTextClass(sModal.newStatus)" x-text="statusLabel(sModal.newStatus)"></span>
                        </div>
                    </div>
                </div>

                
                <template x-if="['deposited','cleared'].includes(sModal.newStatus)">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1 block"
                               x-text="sModal.newStatus==='deposited' ? 'Deposited Date' : 'Cleared Date'"></label>
                        <input type="date" x-model="sModal.date" class="input text-sm w-full" />
                    </div>
                </template>

                
                <template x-if="sModal.newStatus==='bounced'">
                    <div class="space-y-3">
                        <div class="rounded-xl p-3 text-xs" style="background:#fef2f2;border:1px solid #fecaca">
                            <p class="font-bold text-red-700">⚠ Invoice payment will be reversed automatically.</p>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1 block">Bounce Date</label>
                            <input type="date" x-model="sModal.date" class="input text-sm w-full" />
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1 block">Reason <span class="text-gray-400">(optional)</span></label>
                            <input type="text" x-model="sModal.reason" placeholder="e.g. Insufficient funds" class="input text-sm w-full" />
                        </div>
                    </div>
                </template>

                
                <template x-if="sModal.newStatus==='cancelled'">
                    <div class="rounded-xl p-3 text-xs" style="background:#fff7ed;border:1px solid #fed7aa">
                        <p class="text-orange-700">This cheque will be cancelled. Only a Super Admin can reverse this.</p>
                    </div>
                </template>

                <div class="flex gap-3 pt-1">
                    <button @click="sModal.open=false" :disabled="sModal.saving" class="flex-1 btn btn-secondary text-sm">Cancel</button>
                    <button @click="submitStatus()" :disabled="sModal.saving"
                            class="flex-1 btn text-white text-sm"
                            :class="{
                                'bg-blue-600 hover:bg-blue-700':   sModal.newStatus==='deposited',
                                'bg-green-600 hover:bg-green-700': sModal.newStatus==='cleared',
                                'bg-red-600 hover:bg-red-700':     sModal.newStatus==='bounced',
                                'bg-orange-500 hover:bg-orange-600': sModal.newStatus==='cancelled',
                            }">
                        <svg x-show="sModal.saving" class="animate-spin w-4 h-4 mr-1 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        <span x-text="sModal.saving ? 'Saving…' : 'Confirm'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function chequeCalendar() {
    const TODAY = new Date();
    TODAY.setHours(0,0,0,0);
    const todayStr = TODAY.toISOString().slice(0,10);

    return {
        items:     [],
        loading:   true,
        viewYear:  TODAY.getFullYear(),
        viewMonth: TODAY.getMonth(),   // 0-based
        dirFilter: '',
        dayPanel:  { open: false, dateLabel: '', dateStr: '', cheques: [] },
        sModal:    { open: false, cheque: null, newStatus: '', date: todayStr, reason: '', saving: false },
        todayStr,

        /* ── Init — load ALL cheques once ── */
        async init() {
            this.loading = true;
            try {
                const r = await apiFetch('/cheques?per_page=1000');
                const d = await r.json();
                this.items = d.data ?? d ?? [];
            } catch {
                toast('Failed to load cheques', 'error');
            } finally {
                this.loading = false;
            }
        },

        /* ── Navigation (no reload needed — all data is in memory) ── */
        prevMonth() {
            if (this.viewMonth === 0) { this.viewMonth = 11; this.viewYear--; }
            else this.viewMonth--;
            this.dayPanel.open = false;
        },
        nextMonth() {
            if (this.viewMonth === 11) { this.viewMonth = 0; this.viewYear++; }
            else this.viewMonth++;
            this.dayPanel.open = false;
        },
        goToday() {
            this.viewYear  = TODAY.getFullYear();
            this.viewMonth = TODAY.getMonth();
            this.dayPanel.open = false;
        },

        /* ── Computed: calendar header ── */
        get monthName() {
            return new Date(this.viewYear, this.viewMonth).toLocaleString('default', { month: 'long' });
        },
        get monthYear() {
            return new Date(this.viewYear, this.viewMonth).toLocaleString('default', { month: 'long', year: 'numeric' });
        },

        /* ── Filtered items ── */
        get filtered() {
            if (!this.dirFilter) return this.items;
            return this.items.filter(c => c.direction === this.dirFilter);
        },

        /* ── Build calendar cells ── */
        get calendarCells() {
            const firstDay = new Date(this.viewYear, this.viewMonth, 1).getDay(); // 0=Sun
            const daysInMonth = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
            const daysInPrev  = new Date(this.viewYear, this.viewMonth, 0).getDate();
            const cells = [];

            // Group cheques by date string
            const byDate = {};
            this.filtered.forEach(c => {
                const k = (c.cheque_date ?? '').slice(0,10);
                if (!byDate[k]) byDate[k] = [];
                byDate[k].push(c);
            });

            // Previous month padding
            for (let i = firstDay - 1; i >= 0; i--) {
                const d = daysInPrev - i;
                const m = this.viewMonth === 0 ? 11 : this.viewMonth - 1;
                const y = this.viewMonth === 0 ? this.viewYear - 1 : this.viewYear;
                const ds = `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                const chs = byDate[ds] ?? [];
                cells.push({ key: 'p'+ds, day: d, dateStr: ds, currentMonth: false, isToday: false,
                             hasOverdue: chs.some(c=>c.status==='in_hand'&&c.cheque_date<todayStr), cheques: chs });
            }
            // Current month
            for (let d = 1; d <= daysInMonth; d++) {
                const ds = `${this.viewYear}-${String(this.viewMonth+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                const chs = byDate[ds] ?? [];
                cells.push({ key: 'c'+ds, day: d, dateStr: ds, currentMonth: true,
                             isToday: ds === todayStr,
                             hasOverdue: chs.some(c=>c.status==='in_hand'&&ds<todayStr), cheques: chs });
            }
            // Next month padding
            const remaining = 42 - cells.length;
            for (let d = 1; d <= remaining; d++) {
                const m = this.viewMonth === 11 ? 0 : this.viewMonth + 1;
                const y = this.viewMonth === 11 ? this.viewYear + 1 : this.viewYear;
                const ds = `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                const chs = byDate[ds] ?? [];
                cells.push({ key: 'n'+ds, day: d, dateStr: ds, currentMonth: false, isToday: false,
                             hasOverdue: false, cheques: chs });
            }
            return cells;
        },

        /* ── Alert Strip Computed ── */
        get overdue() {
            const list = this.items.filter(c => c.status === 'in_hand' && c.cheque_date < todayStr);
            return { count: list.length, amount: list.reduce((s,c)=>s+parseFloat(c.amount||0),0) };
        },
        get dueToday() {
            const list = this.items.filter(c => c.status === 'in_hand' && (c.cheque_date??'').slice(0,10) === todayStr);
            return { count: list.length, amount: list.reduce((s,c)=>s+parseFloat(c.amount||0),0) };
        },
        get dueWeek() {
            const end = new Date(TODAY); end.setDate(end.getDate()+7);
            const endStr = end.toISOString().slice(0,10);
            const list = this.items.filter(c => c.status === 'in_hand' && (c.cheque_date??'') >= todayStr && (c.cheque_date??'') <= endStr);
            return { count: list.length, amount: list.reduce((s,c)=>s+parseFloat(c.amount||0),0) };
        },
        get monthTotal() {
            const list = this.items.filter(c => {
                const d = (c.cheque_date??'').slice(0,7);
                return d === `${this.viewYear}-${String(this.viewMonth+1).padStart(2,'0')}`;
            });
            return { count: list.length, amount: list.reduce((s,c)=>s+parseFloat(c.amount||0),0) };
        },

        /* ── Day Panel ── */
        openDay(cell) {
            if (cell.cheques.length === 0) return;
            const dt = new Date(cell.dateStr + 'T00:00:00');
            this.dayPanel = {
                open: true,
                dateStr: cell.dateStr,
                dateLabel: dt.toLocaleDateString('default', { weekday:'long', year:'numeric', month:'long', day:'numeric' }),
                cheques: [...cell.cheques].sort((a,b) => (b.amount - a.amount)),
            };
        },
        openChequeDetail(ch) {
            const cell = this.calendarCells.find(c => c.dateStr === (ch.cheque_date??'').slice(0,10));
            if (cell) this.openDay(cell);
        },

        get dayBreakdown() {
            const map = {
                in_hand:     { label:'In Hand',     cls:'bg-amber-100 text-amber-700', count:0, amount:0 },
                transferred: { label:'Transferred', cls:'bg-cyan-100 text-cyan-700',   count:0, amount:0 },
                deposited:   { label:'Deposited',   cls:'bg-blue-100 text-blue-700',   count:0, amount:0 },
                cleared:     { label:'Cleared',     cls:'bg-green-100 text-green-700', count:0, amount:0 },
                bounced:     { label:'Bounced',     cls:'bg-red-100 text-red-700',     count:0, amount:0 },
                cancelled:   { label:'Cancelled',   cls:'bg-gray-100 text-gray-600',   count:0, amount:0 },
            };
            this.dayPanel.cheques.forEach(c => {
                if (map[c.status]) { map[c.status].count++; map[c.status].amount += parseFloat(c.amount||0); }
            });
            return Object.entries(map).filter(([,v])=>v.count>0).map(([s,v])=>({status:s,...v}));
        },

        /* ── Status Change ── */
        openStatusModal(ch, newStatus) {
            this.sModal = { open: true, cheque: ch, newStatus, date: todayStr, reason: '', saving: false };
        },
        async submitStatus() {
            const { cheque, newStatus, date, reason } = this.sModal;
            if (!cheque || !newStatus) return;
            this.sModal.saving = true;
            const body = { status: newStatus };
            if (newStatus === 'deposited') body.deposited_date = date;
            if (newStatus === 'cleared')   body.cleared_date   = date;
            if (newStatus === 'bounced')   { body.bounced_date = date; body.bounce_reason = reason || null; }
            try {
                const r = await apiFetch('/cheques/'+cheque.id, { method:'PUT', body:JSON.stringify(body) });
                if (r.ok) {
                    const updated = await r.json();
                    const idx = this.items.findIndex(c=>c.id===cheque.id);
                    if (idx >= 0) this.items[idx] = { ...this.items[idx], ...updated };
                    // Refresh day panel cheque list
                    if (this.dayPanel.open) {
                        const cell = this.calendarCells.find(c => c.dateStr === this.dayPanel.dateStr);
                        if (cell) this.dayPanel.cheques = [...cell.cheques].sort((a,b) => b.amount - a.amount);
                    }
                    this.sModal.open = false;
                    toast('Status updated to ' + this.statusLabel(newStatus), 'success');
                } else {
                    const e = await r.json();
                    toast(e.message || 'Failed to update', 'error');
                }
            } catch { toast('Failed to update status', 'error'); }
            finally  { this.sModal.saving = false; }
        },

        /* ── Helpers ── */
        canChange(ch) { return ['in_hand', 'deposited', 'transferred'].includes(ch.status); },

        pillClass(ch) {
            return {
                in_hand:     'chq-pill-hand',
                deposited:   'chq-pill-dep',
                cleared:     'chq-pill-clear',
                bounced:     'chq-pill-bounce',
                transferred: 'chq-pill-trans',
                cancelled:   'chq-pill-cancel',
                returned:    'chq-pill-cancel',
            }[ch.status] ?? 'chq-pill-cancel';
        },
        pillTitle(ch) {
            const name = ch.direction==='received'?(ch.customer?.name||'?'):(ch.supplier?.name||'?');
            return `${ch.cheque_number} · ${name} · ${this.statusLabel(ch.status)} · Rs.${parseFloat(ch.amount||0).toLocaleString()}`;
        },
        cardClass(ch) {
            const base = 'border ';
            const map = {
                in_hand:     'border-amber-200 bg-amber-50/60 dark:bg-amber-900/10',
                deposited:   'border-blue-200  bg-blue-50/60  dark:bg-blue-900/10',
                cleared:     'border-green-200 bg-green-50/60 dark:bg-green-900/10',
                bounced:     'border-red-200   bg-red-50/60   dark:bg-red-900/10',
                transferred: 'border-cyan-200  bg-cyan-50/60  dark:bg-cyan-900/10',
                cancelled:   'border-gray-200  bg-gray-50     dark:bg-gray-700/30',
            };
            return base + (map[ch.status] ?? map.cancelled);
        },
        statusLabel(s) {
            return {in_hand:'In Hand',deposited:'Deposited',cleared:'Cleared',bounced:'Bounced',
                    cancelled:'Cancelled',returned:'Returned',transferred:'Transferred'}[s]??(s||'—');
        },
        statusTextClass(s) {
            return {in_hand:'text-amber-600',deposited:'text-blue-600',cleared:'text-green-600',
                    bounced:'text-red-600',transferred:'text-cyan-600',cancelled:'text-gray-500'}[s]??'text-gray-500';
        },
        fmtMoney(v) {
            return 'Rs. ' + parseFloat(v||0).toLocaleString('en-LK',{minimumFractionDigits:2,maximumFractionDigits:2});
        },
        fmtK(v) {
            const n = parseFloat(v||0);
            if (n >= 100000) return (n/100000).toFixed(1).replace(/\.0$/,'')+'L';
            if (n >= 1000)   return (n/1000).toFixed(1).replace(/\.0$/,'')+'k';
            return n.toFixed(0);
        },
        fmtDate(d) {
            if (!d) return '—';
            const s = String(d).slice(0, 10);
            if (!/^\d{4}-\d{2}-\d{2}$/.test(s)) return '—';
            try { return new Date(s + 'T00:00:00').toLocaleDateString('default', {day:'numeric',month:'short',year:'numeric'}); }
            catch { return '—'; }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\cheques\calendar.blade.php ENDPATH**/ ?>