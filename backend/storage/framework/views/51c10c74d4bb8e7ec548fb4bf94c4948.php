<?php $__env->startSection('title', 'Calendar'); ?>
<?php $__env->startSection('page-title', 'Calendar'); ?>
<?php $__env->startSection('page-desc', 'Meetings, visits, reminders and upcoming events'); ?>

<?php $__env->startSection('content'); ?>
<style>
/* ── Wrapper ── */
.cal-wrap { display:flex; flex-direction:column; gap:16px; }

/* ── Stat cards ── */
.cal-stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }
@media(max-width:900px){ .cal-stat-grid{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:500px){ .cal-stat-grid{ grid-template-columns:1fr 1fr; } }
.cal-stat-card { background:#fff; border-radius:14px; padding:14px 16px; border:1px solid #e2e8f0; display:flex; align-items:center; gap:12px; }
.dark .cal-stat-card { background:#1e293b; border-color:#334155; }
.cal-stat-icon { width:38px; height:38px; border-radius:11px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.cal-stat-val  { font-size:20px; font-weight:800; line-height:1.1; color:#0f172a; }
.dark .cal-stat-val { color:#f1f5f9; }
.cal-stat-lbl  { font-size:11px; color:#94a3b8; font-weight:500; margin-top:2px; }
.cal-stat-sub  { font-size:11px; font-weight:600; margin-top:2px; }

/* ── Calendar header bar ── */
.cal-header { background:#fff; border-radius:14px; border:1px solid #e2e8f0; padding:12px 16px; display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.dark .cal-header { background:#1e293b; border-color:#334155; }

/* ── Month grid ── */
.cal-grid { background:#fff; border-radius:16px; border:1px solid #e2e8f0; overflow:hidden; }
.dark .cal-grid { background:#1e293b; border-color:#334155; }
.cal-dow-row { display:grid; grid-template-columns:repeat(7,1fr); }
.cal-dow-cell { padding:10px 8px; text-align:center; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; background:#f8fafc; border-bottom:1px solid #e2e8f0; }
.dark .cal-dow-cell { background:#0f172a; border-color:#334155; }
.cal-cells { display:grid; grid-template-columns:repeat(7,1fr); }
.cal-cell {
    min-height:108px; padding:6px 6px 4px; border-right:1px solid #f1f5f9; border-bottom:1px solid #f1f5f9;
    cursor:pointer; transition:background .12s; position:relative; display:flex; flex-direction:column; gap:2px;
}
.dark .cal-cell { border-color:#1e293b; }
.cal-cell:nth-child(7n) { border-right:none; }
.cal-cell:hover { background:#f8faff; }
.dark .cal-cell:hover { background:#0d1424; }
.cal-cell.other-month { background:#fafafa; opacity:.55; }
.dark .cal-cell.other-month { background:#0a0f1a; }
.cal-cell.is-today { background:#eff6ff !important; }
.dark .cal-cell.is-today { background:rgba(99,102,241,.08) !important; }
.cal-cell.is-selected { background:#e0e7ff !important; }
.dark .cal-cell.is-selected { background:rgba(99,102,241,.15) !important; }
.cal-day-num { font-size:13px; font-weight:700; color:#334155; line-height:1; margin-bottom:2px; display:flex; align-items:center; gap:5px; }
.dark .cal-day-num { color:#94a3b8; }
.cal-today-circle { width:22px; height:22px; border-radius:50%; background:#1B3EB6; display:flex; align-items:center; justify-content:center; color:#fff; font-size:12px; font-weight:800; flex-shrink:0; }

/* ── Event pills ── */
.cal-pill { font-size:10px; font-weight:700; padding:2px 5px; border-radius:5px; display:flex; align-items:center; gap:3px; white-space:nowrap; overflow:hidden; cursor:pointer; max-width:100%; transition:opacity .12s; }
.cal-pill:hover { opacity:.82; }
.cal-pill.is-done { opacity:.42; text-decoration:line-through; }
.cal-pill.is-overdue { box-shadow:inset 0 0 0 1.5px #ef4444; }
.cal-pill-more { background:#f1f5f9 !important; color:#64748b !important; border:1px solid #e2e8f0 !important; }
.dark .cal-pill-more { background:#1e293b !important; color:#94a3b8 !important; border-color:#334155 !important; }
.cal-pill-icon { font-size:9px; flex-shrink:0; line-height:1; }

/* ── Legend bar ── */
.cal-legend { display:flex; flex-wrap:wrap; gap:10px 16px; align-items:center; background:#fff; border-radius:12px; padding:11px 16px; border:1px solid #e2e8f0; }
.dark .cal-legend { background:#1e293b; border-color:#334155; }
.cal-legend-item { display:flex; align-items:center; gap:5px; font-size:11px; font-weight:600; color:#64748b; white-space:nowrap; }
.cal-legend-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }

/* ── Side panel ── */
.cal-side-panel {
    position:fixed; right:20px; top:70px; bottom:20px; z-index:50;
    width:100%; max-width:400px; max-height:calc(100vh - 90px);
    background:#fff; border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,.18);
    border:1px solid #e2e8f0; display:flex; flex-direction:column; overflow:hidden;
}
.dark .cal-side-panel { background:#1e293b; border-color:#334155; }
.cal-side-header { flex-shrink:0; padding:16px 20px; background:linear-gradient(135deg,#1B3EB6,#0D2272); }
.cal-side-body { flex:1; overflow-y:auto; padding:14px; display:flex; flex-direction:column; gap:10px; }

/* ── Event cards in side panel ── */
.cal-ev-card { border-radius:12px; border:1px solid; padding:12px; transition:box-shadow .15s; display:flex; gap:10px; }
.cal-ev-card:hover { box-shadow:0 4px 14px rgba(0,0,0,.09); }
.cal-ev-card.is-done .cal-ev-title { text-decoration:line-through; opacity:.55; }
.cal-ev-card.is-overdue { border-color:#fca5a5 !important; background:#fff8f8 !important; }
.dark .cal-ev-card.is-overdue { background:rgba(239,68,68,.07) !important; }
.cal-ev-bar { width:3px; min-height:100%; border-radius:3px; flex-shrink:0; }

/* ── Recurrence day chips ── */
.rec-day-chip {
    width:34px; height:34px; border-radius:50%; font-size:12px; font-weight:800;
    display:flex; align-items:center; justify-content:center; border:2px solid;
    cursor:pointer; transition:all .15s; user-select:none; flex-shrink:0;
}

/* ── Color swatches ── */
.color-swatch { width:24px; height:24px; border-radius:50%; cursor:pointer; border:3px solid transparent; transition:all .15s; flex-shrink:0; }
.color-swatch.selected { border-color:#fff; box-shadow:0 0 0 2.5px #1B3EB6; transform:scale(1.18); }
</style>

<div x-data="calendarPage()" x-init="init()" class="cal-wrap">

    
    <div class="cal-stat-grid">
        <div class="cal-stat-card" style="border-color:#bfdbfe">
            <div class="cal-stat-icon" style="background:#eff6ff">
                <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="2.5"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <div class="cal-stat-val" style="color:#2563eb" x-text="statToday"></div>
                <div class="cal-stat-lbl">Today</div>
                <div class="cal-stat-sub" style="color:#93c5fd" x-text="todayTitles"></div>
            </div>
        </div>
        <div class="cal-stat-card" style="border-color:#fde68a">
            <div class="cal-stat-icon" style="background:#fffbeb">
                <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="#d97706" stroke-width="2.5"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="cal-stat-val" style="color:#d97706" x-text="statWeek"></div>
                <div class="cal-stat-lbl">This Week</div>
                <div class="cal-stat-sub" style="color:#fbbf24">Upcoming</div>
            </div>
        </div>
        <div class="cal-stat-card" style="border-color:#ddd6fe">
            <div class="cal-stat-icon" style="background:#ede9fe">
                <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="#7c3aed" stroke-width="2.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <div class="cal-stat-val" x-text="statMonth"></div>
                <div class="cal-stat-lbl" x-text="viewMonthName + ' Total'"></div>
                <div class="cal-stat-sub text-gray-400" x-text="statMonthTypes"></div>
            </div>
        </div>
        <div class="cal-stat-card" style="border-color:#bbf7d0">
            <div class="cal-stat-icon" style="background:#f0fdf4">
                <svg style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="2.5"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="cal-stat-val" style="color:#16a34a" x-text="statCompleted"></div>
                <div class="cal-stat-lbl">Completed</div>
                <div class="cal-stat-sub" style="color:#4ade80">This month</div>
            </div>
        </div>
    </div>

    
    <div class="cal-header">
        <button @click="prevMonth()" class="w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-600 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M15 19l-7-7 7-7"/></svg>
        </button>
        <h2 class="text-base font-black text-gray-900 dark:text-white min-w-[160px] text-center" x-text="monthYearLabel"></h2>
        <button @click="nextMonth()" class="w-8 h-8 rounded-lg border border-gray-200 dark:border-gray-600 flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-4 h-4 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 5l7 7-7 7"/></svg>
        </button>
        <button @click="goToday()" class="px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 text-xs font-bold text-indigo-600 hover:bg-indigo-50 transition-colors">
            Today
        </button>

        
        <div class="flex gap-1.5 ml-1 flex-wrap">
            <button @click="typeFilter = ''"
                    class="px-2.5 py-1 rounded-full text-xs font-bold transition-all border"
                    :class="typeFilter === ''
                        ? 'bg-gray-800 text-white border-gray-800'
                        : 'bg-white text-gray-500 border-gray-200 hover:border-gray-400 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500'">
                All
            </button>
            <template x-for="f in typeFilters" :key="f.v">
                <button @click="typeFilter = typeFilter === f.v ? '' : f.v"
                        class="px-2.5 py-1 rounded-full text-xs font-bold transition-all border"
                        :style="typeFilter === f.v
                            ? 'background:'+f.dot+';color:#fff;border-color:'+f.dot
                            : 'background:#fff;color:#64748b;border-color:#e2e8f0'"
                        x-text="f.l"></button>
            </template>
        </div>

        <select x-model="assigneeFilter" class="text-xs font-semibold rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 px-2 py-1.5">
            <option value="">All Assignees</option>
            <template x-for="u in users" :key="u.id">
                <option :value="u.id" x-text="u.name"></option>
            </template>
        </select>

        <div class="ml-auto flex items-center gap-2">
            <div x-show="loading" class="flex items-center gap-1.5">
                <svg class="animate-spin w-4 h-4 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            </div>
            <button @click="openAddModal(null)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-bold text-white transition-opacity hover:opacity-90"
                    style="background:#1B3EB6">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
                Add Event
            </button>
        </div>
    </div>

    
    <div class="cal-grid">
        <div class="cal-dow-row">
            <template x-for="d in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="d">
                <div class="cal-dow-cell" x-text="d"></div>
            </template>
        </div>
        <div class="cal-cells">
            <template x-for="cell in calendarCells" :key="cell.key">
                <div class="cal-cell"
                     :class="{
                         'other-month': !cell.currentMonth,
                         'is-today':    cell.isToday,
                         'is-selected': dayPanel.open && dayPanel.dateStr === cell.dateStr
                     }"
                     @click="openDay(cell)">

                    <div class="cal-day-num">
                        <template x-if="cell.isToday">
                            <span class="cal-today-circle" x-text="cell.day"></span>
                        </template>
                        <template x-if="!cell.isToday">
                            <span x-text="cell.day"
                                  :style="cell.currentMonth ? '' : 'color:#cbd5e1'"></span>
                        </template>
                    </div>

                    
                    <template x-for="(ev, idx) in cell.events.slice(0, 3)" :key="ev.id + '-' + (ev.occurrence_date ?? idx)">
                        <div class="cal-pill"
                             :class="{ 'is-done': evCompleted(ev), 'is-overdue': evOverdue(ev) }"
                             :style="'background:'+typeColor(ev.type).bg+';color:'+typeColor(ev.type).text+';border:1px solid '+typeColor(ev.type).border"
                             @click.stop="openDay(cell)">
                            <template x-if="evCompleted(ev)">
                                <span class="cal-pill-icon" style="color:#16a34a">✓</span>
                            </template>
                            <template x-if="evOverdue(ev)">
                                <span class="cal-pill-icon" style="color:#ef4444;font-weight:900">!</span>
                            </template>
                            <span class="truncate min-w-0 flex-1" x-text="ev.title"></span>
                            <template x-if="ev.recurrence && ev.recurrence !== 'none'">
                                <span class="cal-pill-icon flex-shrink-0 ml-auto" style="opacity:.7" title="Recurring">↻</span>
                            </template>
                            <template x-if="!ev.all_day && fmtTime(ev.start_at)">
                                <span class="cal-pill-icon flex-shrink-0 ml-auto opacity-60 text-[9px]"
                                      x-text="fmtTime(ev.start_at)"></span>
                            </template>
                        </div>
                    </template>
                    <template x-if="cell.events.length > 3">
                        <div class="cal-pill cal-pill-more" @click.stop="openDay(cell)">
                            +<span x-text="cell.events.length - 3"></span> more
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </div>

    
    <div class="cal-legend">
        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 mr-1">Legend</span>
        <template x-for="f in typeFilters" :key="f.v">
            <div class="cal-legend-item">
                <div class="cal-legend-dot" :style="'background:'+f.dot"></div>
                <span x-text="f.l"></span>
            </div>
        </template>
        <div class="cal-legend-item" style="margin-left:8px">
            <span style="font-size:11px;color:#16a34a;font-weight:900;line-height:1">✓</span>
            <span>Completed</span>
        </div>
        <div class="cal-legend-item">
            <span style="font-size:12px;color:#ef4444;font-weight:900;line-height:1">!</span>
            <span>Overdue</span>
        </div>
        <div class="cal-legend-item">
            <span style="font-size:13px;color:#94a3b8;line-height:1">↻</span>
            <span>Recurring</span>
        </div>
    </div>

    
    <div x-show="dayPanel.open" x-transition.opacity
         class="fixed inset-0 z-40"
         style="background:rgba(0,0,0,.28)"
         @click.self="dayPanel.open=false"></div>

    
    <div x-show="dayPanel.open" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-x-8"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-8"
         class="cal-side-panel">

        <div class="cal-side-header">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider mb-0.5"
                       style="color:rgba(255,255,255,.55)">
                        <span x-text="dayPanel.events.length"></span>
                        Event<span x-show="dayPanel.events.length !== 1">s</span>
                    </p>
                    <h3 class="text-base font-black text-white" x-text="dayPanel.dateLabel"></h3>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="openAddModal(dayPanel.dateStr)"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-white transition-colors"
                            style="background:rgba(255,255,255,.15)"
                            title="Add event on this day">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
                    </button>
                    <button @click="dayPanel.open=false"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-white hover:bg-white/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <div class="cal-side-body">
            <template x-if="dayPanel.events.length === 0">
                <div class="text-center py-12 text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-2 opacity-25" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <p class="text-sm font-medium">No events on this day</p>
                    <button @click="openAddModal(dayPanel.dateStr)"
                            class="mt-3 px-4 py-1.5 rounded-lg text-sm font-bold text-white"
                            style="background:#1B3EB6">Add Event</button>
                </div>
            </template>

            <template x-for="ev in dayPanel.events" :key="ev.id + '-' + (ev.occurrence_date ?? '')">
                <div class="cal-ev-card"
                     :class="{ 'is-done': evCompleted(ev), 'is-overdue': evOverdue(ev) }"
                     :style="'border-color:'+typeColor(ev.type).border+';background:'+typeColor(ev.type).bg+'28'">

                    
                    <div class="cal-ev-bar" :style="'background:'+(ev.color && ev.color !== '#3B82F6' ? ev.color : typeColor(ev.type).dot)"></div>

                    <div class="flex-1 min-w-0">
                        
                        <div class="flex items-start justify-between gap-2 mb-1.5">
                            <div class="min-w-0">
                                <p class="cal-ev-title text-sm font-bold text-gray-900 dark:text-white truncate" x-text="ev.title"></p>
                                <div class="flex flex-wrap items-center gap-1 mt-1">
                                    
                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wide"
                                          :style="'background:'+typeColor(ev.type).bg+';color:'+typeColor(ev.type).text"
                                          x-text="typeLabel(ev.type)"></span>
                                    
                                    <template x-if="evCompleted(ev)">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-green-100 text-green-700">Done</span>
                                    </template>
                                    
                                    <template x-if="evOverdue(ev)">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-red-100 text-red-700">Overdue</span>
                                    </template>
                                    
                                    <template x-if="ev.recurrence && ev.recurrence !== 'none'">
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-gray-100 text-gray-500">↻ Recurring</span>
                                    </template>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                
                                <button @click.stop="toggleComplete(ev)"
                                        :title="evCompleted(ev) ? 'Mark incomplete' : 'Mark complete'"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
                                        :class="evCompleted(ev)
                                            ? 'bg-green-100 text-green-600 hover:bg-green-200'
                                            : 'text-gray-300 hover:bg-green-50 hover:text-green-500'">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
                                </button>
                                
                                <button @click.stop="openEditModal(ev)"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-indigo-50 text-gray-400 hover:text-indigo-600 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                
                                <button @click.stop="deleteEvent(ev)"
                                        class="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-red-50 text-gray-400 hover:text-red-500 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>

                        
                        <div class="space-y-1 text-xs text-gray-500">
                            <template x-if="ev.all_day">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span class="font-semibold text-indigo-600">All Day</span>
                                </div>
                            </template>
                            <template x-if="!ev.all_day && fmtTime(ev.start_at)">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span x-text="fmtTime(ev.start_at) + (fmtTime(ev.end_at) ? ' – '+fmtTime(ev.end_at) : '')"></span>
                                </div>
                            </template>
                            <template x-if="ev.location">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span x-text="ev.location"></span>
                                </div>
                            </template>
                            <template x-if="(ev.attendees ?? []).length">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span x-text="ev.attendees.map(a => a.name).join(', ')"></span>
                                </div>
                            </template>
                            <template x-if="ev.description">
                                <p class="leading-relaxed pt-0.5" x-text="ev.description"></p>
                            </template>
                            <template x-if="ev.is_company_wide">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-indigo-50 text-indigo-600">Company-wide</span>
                            </template>
                            <template x-if="ev.linked_module === 'work_task' && ev.linked_id">
                                <a :href="BASE + '/task-manager/board?open=' + ev.linked_id" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[9px] font-bold bg-violet-50 text-violet-600 hover:bg-violet-100">
                                    <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                                    Linked task
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    
    <div x-show="eModal.open" x-cloak x-transition.opacity
         class="fixed inset-0 z-[60] flex items-center justify-center p-4"
         style="background:rgba(0,0,0,.55)"
         @click.self="eModal.open=false">
        <div x-show="eModal.open" x-transition
             class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">

            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700"
                 style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
                <h3 class="font-black text-white text-base" x-text="eModal.isEdit ? 'Edit Event' : 'New Event'"></h3>
            </div>

            <div class="p-5 space-y-4 max-h-[70vh] overflow-y-auto">

                
                <div>
                    <label class="label">Type</label>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="t in typeFilters" :key="t.v">
                            <button type="button"
                                    @click="eModal.form.type = t.v"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold border-2 transition-all"
                                    :style="eModal.form.type === t.v
                                        ? 'background:'+t.dot+';color:#fff;border-color:'+t.dot
                                        : 'background:'+t.bg+';color:'+t.text+';border-color:'+t.border"
                                    x-text="t.l"></button>
                        </template>
                    </div>
                </div>

                
                <div>
                    <label class="label">Title <span class="text-red-500">*</span></label>
                    <input type="text" x-model="eModal.form.title" class="input" placeholder="Event title" />
                </div>

                
                <div>
                    <label class="label">Assign To</label>
                    <select x-model="eModal.form.assigned_to" class="input">
                        <option value="">— Unassigned —</option>
                        <template x-for="u in users" :key="u.id">
                            <option :value="u.id" x-text="u.name"></option>
                        </template>
                    </select>
                </div>

                
                <div class="flex items-center gap-2">
                    <input type="checkbox" x-model="eModal.form.all_day" id="calAllDay" class="rounded" />
                    <label for="calAllDay" class="text-sm font-medium text-gray-700 dark:text-gray-300">All Day</label>
                </div>

                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Date <span class="text-red-500">*</span></label>
                        <input type="date" x-model="eModal.form.date" class="input" />
                    </div>
                    <template x-if="!eModal.form.all_day">
                        <div>
                            <label class="label">Start Time</label>
                            <input type="time" x-model="eModal.form.start_time" class="input" />
                        </div>
                    </template>
                </div>
                <template x-if="!eModal.form.all_day">
                    <div>
                        <label class="label">End Time</label>
                        <input type="time" x-model="eModal.form.end_time" class="input" />
                    </div>
                </template>

                
                <div>
                    <label class="label">Repeat</label>
                    <div class="flex gap-1.5 flex-wrap">
                        <template x-for="r in recurrenceOptions" :key="r.v">
                            <button type="button"
                                    @click="eModal.form.recurrence = r.v; if(r.v !== 'weekly') eModal.form.recurrence_days = []"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold border-2 transition-all"
                                    :class="eModal.form.recurrence === r.v
                                        ? 'bg-indigo-600 text-white border-indigo-600'
                                        : 'bg-white text-gray-500 border-gray-200 hover:border-indigo-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500'"
                                    x-text="r.l"></button>
                        </template>
                    </div>
                </div>

                
                <template x-if="eModal.form.recurrence === 'weekly'">
                    <div>
                        <label class="label">Repeat On</label>
                        <div class="flex gap-2 flex-wrap">
                            <template x-for="(day, idx) in ['S','M','T','W','T','F','S']" :key="idx">
                                <button type="button"
                                        @click="toggleRecurrenceDay(idx)"
                                        class="rec-day-chip"
                                        :style="eModal.form.recurrence_days.includes(idx)
                                            ? 'background:#1B3EB6;color:#fff;border-color:#1B3EB6'
                                            : 'background:#fff;color:#64748b;border-color:#e2e8f0'"
                                        x-text="day"></button>
                            </template>
                        </div>
                    </div>
                </template>

                
                <template x-if="eModal.form.recurrence !== 'none'">
                    <div>
                        <label class="label">
                            End Repeat
                            <span class="text-gray-400 font-normal text-xs ml-1">(optional)</span>
                        </label>
                        <input type="date" x-model="eModal.form.recurrence_end_date" class="input" />
                    </div>
                </template>

                
                <div>
                    <label class="label">Location</label>
                    <input type="text" x-model="eModal.form.location" class="input" placeholder="Optional" />
                </div>

                
                <div>
                    <label class="label">Description</label>
                    <textarea x-model="eModal.form.description" rows="2" class="input resize-none" placeholder="Optional details…"></textarea>
                </div>

                
                <div>
                    <label class="label">Color</label>
                    <div class="flex gap-2 flex-wrap">
                        <template x-for="c in colorOptions" :key="c">
                            <button type="button"
                                    @click="eModal.form.color = c"
                                    class="color-swatch"
                                    :class="eModal.form.color === c ? 'selected' : ''"
                                    :style="'background:'+c"
                                    :title="c"></button>
                        </template>
                    </div>
                </div>

                
                <div class="flex items-center gap-2">
                    <input type="checkbox" x-model="eModal.form.is_company_wide" id="calCompany" class="rounded" />
                    <label for="calCompany" class="text-sm font-medium text-gray-700 dark:text-gray-300">Company-wide event</label>
                </div>

            </div>

            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-700 flex gap-3">
                <template x-if="eModal.isEdit">
                    <button @click="deleteEvent(eModal.editEvent)" :disabled="eModal.saving"
                            class="px-4 py-2 rounded-xl border border-red-200 text-red-600 text-sm font-bold hover:bg-red-50 transition-colors">
                        Delete
                    </button>
                </template>
                <button @click="eModal.open=false" :disabled="eModal.saving" class="flex-1 btn-secondary text-sm">Cancel</button>
                <button @click="saveEvent()" :disabled="eModal.saving"
                        class="flex-1 text-sm font-bold text-white py-2 px-4 rounded-xl transition-opacity hover:opacity-90"
                        style="background:#1B3EB6">
                    <svg x-show="eModal.saving" class="animate-spin w-4 h-4 mr-1 inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                    <span x-text="eModal.saving ? 'Saving…' : (eModal.isEdit ? 'Save Changes' : 'Add Event')"></span>
                </button>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function calendarPage() {
    const TODAY = new Date();
    TODAY.setHours(0, 0, 0, 0);
    const todayStr = TODAY.toISOString().slice(0, 10);

    return {
        items:      [],
        users:      [],
        loading:    true,
        viewYear:   TODAY.getFullYear(),
        viewMonth:  TODAY.getMonth(),
        typeFilter: '',
        assigneeFilter: '',
        dayPanel:   { open: false, dateLabel: '', dateStr: '', events: [] },
        eModal: {
            open: false, isEdit: false, editId: null, editEvent: null, saving: false,
            form: {
                title: '', type: 'meeting', date: todayStr, start_time: '', end_time: '',
                all_day: false, location: '', description: '', is_company_wide: false, assigned_to: '',
                recurrence: 'none', recurrence_days: [], recurrence_end_date: '', color: '#3b82f6',
                linked_module: null, linked_id: null,
            },
        },
        todayStr,

        typeFilters: [
            { v: 'meeting',          l: 'Meeting',          bg: '#eff6ff', text: '#1d4ed8', border: '#bfdbfe', dot: '#3b82f6' },
            { v: 'customer_visit',   l: 'Customer Visit',   bg: '#f5f3ff', text: '#6d28d9', border: '#ddd6fe', dot: '#8b5cf6' },
            { v: 'follow_up',        l: 'Follow Up',        bg: '#fff7ed', text: '#c2410c', border: '#fed7aa', dot: '#f97316' },
            { v: 'payment_reminder', l: 'Payment Reminder', bg: '#fef2f2', text: '#b91c1c', border: '#fecaca', dot: '#ef4444' },
            { v: 'delivery',         l: 'Delivery',         bg: '#f0fdf4', text: '#15803d', border: '#bbf7d0', dot: '#22c55e' },
            { v: 'other',            l: 'Other',            bg: '#f8fafc', text: '#475569', border: '#e2e8f0', dot: '#94a3b8' },
        ],

        recurrenceOptions: [
            { v: 'none',    l: 'No Repeat' },
            { v: 'weekly',  l: 'Weekly' },
            { v: 'monthly', l: 'Monthly' },
            { v: 'yearly',  l: 'Yearly' },
        ],

        colorOptions: ['#3b82f6','#8b5cf6','#f97316','#ef4444','#22c55e','#06b6d4','#ec4899','#f59e0b','#6366f1','#94a3b8'],

        // ── Init ──
        async init() {
            window.addEventListener('branch-switched', () => this.loadEvents());
            await Promise.all([this.loadEvents(), this.loadUsers()]);
            this.applyTaskPrefill();
        },

        async loadUsers() {
            try {
                this.users = await apiFetch('/events/assignable-users').then(r => r.json());
            } catch (e) { /* assignee dropdowns just stay empty */ }
        },

        // ── Coming from a "Schedule" action on a Task Manager task (see task-manager/board.blade.php) ──
        applyTaskPrefill() {
            let raw;
            try { raw = sessionStorage.getItem('calendar_task_prefill'); } catch (e) { return; }
            if (!raw) return;
            sessionStorage.removeItem('calendar_task_prefill');
            let prefill;
            try { prefill = JSON.parse(raw); } catch (e) { return; }
            this.openAddModal(prefill.date ?? null, prefill);
        },

        async loadEvents() {
            this.loading = true;
            try {
                const r = await apiFetch('/events');
                if (!r) return;
                const d = await r.json();
                this.items = Array.isArray(d) ? d : (d.data ?? []);
            } catch { toast('Failed to load events', 'error'); }
            finally   { this.loading = false; }
        },

        // ── View labels ──
        get viewMonthName() {
            return new Date(this.viewYear, this.viewMonth).toLocaleString('default', { month: 'long' });
        },
        get monthYearLabel() {
            return new Date(this.viewYear, this.viewMonth).toLocaleString('default', { month: 'long', year: 'numeric' });
        },

        // ── Stats ──
        get statToday()  { return this._getEventsForDate(todayStr, this.items).length; },
        get todayTitles() {
            const evs = this._getEventsForDate(todayStr, this.items);
            return evs.map(e => e.title).join(', ').slice(0, 28) || '—';
        },
        get statWeek() {
            const end = new Date(TODAY); end.setDate(end.getDate() + 7);
            let count = 0, d = new Date(TODAY);
            while (d <= end) {
                count += this._getEventsForDate(d.toISOString().slice(0, 10), this.items).length;
                d.setDate(d.getDate() + 1);
            }
            return count;
        },
        get statMonth() {
            const y = this.viewYear, m = this.viewMonth;
            const monthStart = `${y}-${String(m+1).padStart(2,'0')}-01`;
            const monthEnd   = new Date(y, m + 1, 0).toISOString().slice(0, 10);
            let count = 0;
            const daysInMonth = new Date(y, m + 1, 0).getDate();
            for (let i = 1; i <= daysInMonth; i++) {
                const ds = `${y}-${String(m+1).padStart(2,'0')}-${String(i).padStart(2,'0')}`;
                count += this._getEventsForDate(ds, this.items).length;
            }
            return count;
        },
        get statMonthTypes() {
            const y = this.viewYear, m = this.viewMonth;
            const daysInMonth = new Date(y, m + 1, 0).getDate();
            const types = new Set();
            for (let i = 1; i <= daysInMonth; i++) {
                const ds = `${y}-${String(m+1).padStart(2,'0')}-${String(i).padStart(2,'0')}`;
                this._getEventsForDate(ds, this.items).forEach(e => types.add(e.type));
            }
            const labels = [...types].map(t => this.typeLabel(t));
            return labels.slice(0, 3).join(', ') + (labels.length > 3 ? '…' : '') || '—';
        },
        get statCompleted() {
            const prefix = `${this.viewYear}-${String(this.viewMonth + 1).padStart(2, '0')}`;
            let count = 0;
            this.items.forEach(ev => {
                (ev.completions ?? []).forEach(c => {
                    if (String(c.occurrence_date).slice(0, 7) === prefix) count++;
                });
            });
            return count;
        },

        // ── Core: check if an event has an occurrence on a given date ──
        _getOccurrenceOnDate(ev, dateStr) {
            const evStartDate = (ev.start_at ?? '').slice(0, 10);
            if (!evStartDate) return null;

            const recurrence = ev.recurrence ?? 'none';

            if (recurrence === 'none' || !recurrence) {
                return evStartDate === dateStr ? { ...ev, occurrence_date: dateStr } : null;
            }

            const evStart  = new Date(evStartDate + 'T00:00:00');
            const occDate  = new Date(dateStr + 'T00:00:00');

            if (occDate < evStart) return null;

            if (ev.recurrence_end_date) {
                const endD = new Date(String(ev.recurrence_end_date).slice(0, 10) + 'T00:00:00');
                if (occDate > endD) return null;
            }

            if (recurrence === 'monthly') {
                if (occDate.getDate() === evStart.getDate())
                    return { ...ev, occurrence_date: dateStr };
            } else if (recurrence === 'yearly') {
                if (occDate.getMonth() === evStart.getMonth() && occDate.getDate() === evStart.getDate())
                    return { ...ev, occurrence_date: dateStr };
            } else if (recurrence === 'weekly') {
                const days = Array.isArray(ev.recurrence_days) ? ev.recurrence_days : [];
                if (days.includes(occDate.getDay()))
                    return { ...ev, occurrence_date: dateStr };
            }
            return null;
        },

        _getEventsForDate(dateStr, source) {
            const result = [];
            (source ?? this.filtered).forEach(ev => {
                const occ = this._getOccurrenceOnDate(ev, dateStr);
                if (occ) result.push(occ);
            });
            return result.sort((a, b) => (a.start_at ?? '') < (b.start_at ?? '') ? -1 : 1);
        },

        // ── Completion helpers ──
        evCompleted(ev) {
            const occDate = ev.occurrence_date ?? (ev.start_at ?? '').slice(0, 10);
            return (ev.completions ?? []).some(c => String(c.occurrence_date).slice(0, 10) === occDate);
        },

        evOverdue(ev) {
            if (this.evCompleted(ev)) return false;
            const occDate = ev.occurrence_date ?? (ev.start_at ?? '').slice(0, 10);
            return occDate < todayStr;
        },

        // ── Toggle completion ──
        async toggleComplete(ev) {
            const occDate = ev.occurrence_date ?? (ev.start_at ?? '').slice(0, 10);
            try {
                const r = await apiFetch('/events/' + ev.id + '/complete', {
                    method: 'POST',
                    body: JSON.stringify({ occurrence_date: occDate }),
                });
                if (!r || !r.ok) { toast('Failed to update', 'error'); return; }
                const d = await r.json();
                const idx = this.items.findIndex(e => e.id === ev.id);
                if (idx >= 0) this.items.splice(idx, 1, { ...this.items[idx], completions: d.completions });
                if (this.dayPanel.open) this._refreshDayPanel();
                toast(d.completions.some(c => String(c.occurrence_date).slice(0,10) === occDate) ? 'Marked complete' : 'Marked incomplete');
            } catch { toast('Failed to update', 'error'); }
        },

        _refreshDayPanel() {
            const cell = this.calendarCells.find(c => c.dateStr === this.dayPanel.dateStr);
            if (cell) this.dayPanel.events = [...cell.events];
        },

        // ── Filtered items (by type + assignee) ──
        get filtered() {
            let list = this.items;
            if (this.typeFilter) list = list.filter(e => e.type === this.typeFilter);
            if (this.assigneeFilter) list = list.filter(e => (e.attendees ?? []).some(a => String(a.id) === String(this.assigneeFilter)));
            return list;
        },

        // ── Calendar grid ──
        get calendarCells() {
            const firstDay    = new Date(this.viewYear, this.viewMonth, 1).getDay();
            const daysInMonth = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
            const daysInPrev  = new Date(this.viewYear, this.viewMonth, 0).getDate();
            const cells       = [];

            for (let i = firstDay - 1; i >= 0; i--) {
                const d = daysInPrev - i;
                const m = this.viewMonth === 0 ? 11 : this.viewMonth - 1;
                const y = this.viewMonth === 0 ? this.viewYear - 1 : this.viewYear;
                const ds = `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                cells.push({ key: 'p'+ds, day: d, dateStr: ds, currentMonth: false, isToday: ds === todayStr, events: this._getEventsForDate(ds) });
            }
            for (let d = 1; d <= daysInMonth; d++) {
                const ds = `${this.viewYear}-${String(this.viewMonth+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                cells.push({ key: 'c'+ds, day: d, dateStr: ds, currentMonth: true, isToday: ds === todayStr, events: this._getEventsForDate(ds) });
            }
            const remaining = 42 - cells.length;
            for (let d = 1; d <= remaining; d++) {
                const m = this.viewMonth === 11 ? 0 : this.viewMonth + 1;
                const y = this.viewMonth === 11 ? this.viewYear + 1 : this.viewYear;
                const ds = `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                cells.push({ key: 'n'+ds, day: d, dateStr: ds, currentMonth: false, isToday: ds === todayStr, events: this._getEventsForDate(ds) });
            }
            return cells;
        },

        // ── Navigation ──
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

        // ── Day panel ──
        openDay(cell) {
            const dt = new Date(cell.dateStr + 'T00:00:00');
            this.dayPanel = {
                open: true,
                dateStr: cell.dateStr,
                dateLabel: dt.toLocaleDateString('default', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
                events: [...cell.events],
            };
        },

        // ── Add / Edit modal ──
        openAddModal(dateStr, prefill = null) {
            this.eModal = {
                open: true, isEdit: false, editId: null, editEvent: null, saving: false,
                form: {
                    title: prefill?.title ?? '', type: prefill?.type ?? 'meeting', date: dateStr ?? todayStr, start_time: '', end_time: '',
                    all_day: false, location: '', description: prefill?.description ?? '', is_company_wide: false,
                    assigned_to: prefill?.assigned_to ?? '',
                    recurrence: 'none', recurrence_days: [], recurrence_end_date: '', color: '#3b82f6',
                    linked_module: prefill?.linked_module ?? null, linked_id: prefill?.linked_id ?? null,
                },
            };
        },

        openEditModal(ev) {
            const d  = (ev.start_at ?? '').slice(0, 10);
            const st = ev.all_day ? '' : (ev.start_at ?? '').slice(11, 16);
            const et = ev.all_day ? '' : (ev.end_at   ?? '').slice(11, 16);
            this.eModal = {
                open: true, isEdit: true, editId: ev.id, editEvent: ev, saving: false,
                form: {
                    title: ev.title ?? '', type: ev.type ?? 'meeting', date: d,
                    start_time: st, end_time: et, all_day: !!ev.all_day,
                    location: ev.location ?? '', description: ev.description ?? '',
                    is_company_wide: !!ev.is_company_wide,
                    assigned_to: (ev.attendees ?? [])[0]?.id ?? '',
                    recurrence: ev.recurrence ?? 'none',
                    recurrence_days: Array.isArray(ev.recurrence_days) ? [...ev.recurrence_days] : [],
                    recurrence_end_date: ev.recurrence_end_date ? String(ev.recurrence_end_date).slice(0, 10) : '',
                    color: ev.color ?? '#3b82f6',
                    linked_module: ev.linked_module ?? null, linked_id: ev.linked_id ?? null,
                },
            };
        },

        toggleRecurrenceDay(idx) {
            const days = this.eModal.form.recurrence_days;
            const i = days.indexOf(idx);
            if (i >= 0) days.splice(i, 1);
            else        days.push(idx);
        },

        async saveEvent() {
            if (!this.eModal.form.title.trim() || !this.eModal.form.date) {
                toast('Title and date are required', 'error'); return;
            }
            this.eModal.saving = true;
            const f = this.eModal.form;
            const start_at = f.all_day
                ? f.date + 'T00:00:00'
                : f.date + (f.start_time ? 'T' + f.start_time + ':00' : 'T00:00:00');
            const end_at = (!f.all_day && f.end_time) ? f.date + 'T' + f.end_time + ':00' : null;
            const body = {
                title: f.title, type: f.type, start_at, end_at, all_day: f.all_day,
                location: f.location || null, description: f.description || null,
                is_company_wide: f.is_company_wide, color: f.color,
                attendee_ids: f.assigned_to ? [f.assigned_to] : [],
                linked_module: f.linked_module || null, linked_id: f.linked_id || null,
                recurrence: f.recurrence,
                recurrence_days: f.recurrence === 'weekly' ? f.recurrence_days : null,
                recurrence_end_date: f.recurrence_end_date || null,
            };
            try {
                const isEdit = this.eModal.isEdit;
                const url    = isEdit ? '/events/' + this.eModal.editId : '/events';
                const method = isEdit ? 'PUT' : 'POST';
                const r = await apiFetch(url, { method, body: JSON.stringify(body) });
                if (!r || !r.ok) { const e = await r.json(); toast(e.message ?? 'Failed', 'error'); return; }
                const saved = await r.json();
                if (isEdit) {
                    const idx = this.items.findIndex(e => e.id === this.eModal.editId);
                    if (idx >= 0) this.items.splice(idx, 1, { ...saved, completions: this.items[idx]?.completions ?? [] });
                } else {
                    this.items.push({ ...saved, completions: [] });
                }
                if (this.dayPanel.open) this._refreshDayPanel();
                toast(isEdit ? 'Event updated' : 'Event added');
                this.eModal.open = false;
            } catch { toast('Failed to save event', 'error'); }
            finally   { this.eModal.saving = false; }
        },

        async deleteEvent(ev) {
            if (!ev || !confirm(`Delete "${ev.title}"?`)) return;
            try {
                const r = await apiFetch('/events/' + ev.id, { method: 'DELETE' });
                if (!r || !r.ok) { toast('Failed to delete', 'error'); return; }
                this.items = this.items.filter(e => e.id !== ev.id);
                if (this.dayPanel.open) this.dayPanel.events = this.dayPanel.events.filter(e => e.id !== ev.id);
                this.eModal.open = false;
                toast('Event deleted');
            } catch { toast('Failed to delete event', 'error'); }
        },

        // ── Helpers ──
        typeColor(type) {
            return this.typeFilters.find(t => t.v === type) ?? this.typeFilters.find(t => t.v === 'other');
        },
        typeLabel(type) {
            const t = this.typeFilters.find(t => t.v === type);
            return t ? t.l : (type ?? '—');
        },
        fmtTime(dt) {
            if (!dt) return '';
            try {
                const t = String(dt).slice(11, 16);
                if (!t || t === '00:00') return '';
                const [h, m] = t.split(':').map(Number);
                const ampm = h >= 12 ? 'PM' : 'AM';
                return ((h % 12) || 12) + (m ? ':' + String(m).padStart(2, '0') : '') + ' ' + ampm;
            } catch { return ''; }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\calendar\index.blade.php ENDPATH**/ ?>