<style>
/* ── Stats Cards ── */
.tb-stats{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:20px}
.tb-stat-card{background:#fff;border-radius:14px;padding:16px 18px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:12px;transition:box-shadow .2s,transform .2s;cursor:pointer}
.tb-stat-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08);transform:translateY(-2px)}
.tb-stat-card.active{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}
.tb-stat-icon{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.tb-stat-icon svg{width:20px;height:20px}
.tb-stat-val{font-size:21px;font-weight:800;line-height:1.1;letter-spacing:-.5px}
.tb-stat-lbl{font-size:11px;color:#94a3b8;font-weight:600;margin-top:2px;text-transform:uppercase;letter-spacing:.04em}

/* ── Toolbar ── */
.tb-toolbar{background:#fff;border-radius:14px;padding:14px 18px;border:1px solid #e2e8f0;margin-bottom:16px}
.tb-toolbar-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.tb-toolbar-row + .tb-toolbar-row{margin-top:12px;padding-top:12px;border-top:1px dashed #e2e8f0}
.tb-search-wrap{position:relative;flex:1;min-width:200px;max-width:320px}
.tb-search-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none}
.tb-search-wrap input{width:100%;border:1px solid #e2e8f0;border-radius:9px;padding:7px 12px 7px 34px;font-size:13px;color:#1e293b;background:#f8fafc;outline:none;transition:border-color .15s,box-shadow .15s}
.tb-search-wrap input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);background:#fff}
.tb-select{border:1px solid #e2e8f0;border-radius:9px;padding:7px 30px 7px 12px;font-size:12.5px;color:#334155;background:#f8fafc;outline:none;min-width:130px;transition:border-color .15s,box-shadow .15s;appearance:none;-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2394a3b8'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 8px center;background-size:14px}
.tb-select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}
.tb-overdue-toggle{display:flex;align-items:center;gap:6px;font-size:12px;font-weight:700;color:#b91c1c;cursor:pointer;background:#fef2f2;border:1px solid #fecaca;border-radius:9px;padding:7px 12px;white-space:nowrap}
.tb-overdue-toggle input{accent-color:#dc2626;cursor:pointer}

/* ── Status Tabs ── */
.tb-tabs{display:flex;gap:4px;background:#f1f5f9;border-radius:10px;padding:3px;flex-wrap:wrap}
.tb-tab{padding:5px 14px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;border:none;background:transparent;color:#64748b;transition:all .15s;white-space:nowrap}
.tb-tab:hover{background:#e2e8f0;color:#334155}
.tb-tab.active{background:#fff;color:#1e293b;box-shadow:0 1px 4px rgba(0,0,0,.1)}

.tb-btn-primary{background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-radius:10px;padding:8px 18px;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:6px;text-decoration:none;box-shadow:0 4px 12px rgba(99,102,241,.35);transition:opacity .15s;border:none;cursor:pointer}
.tb-btn-primary:hover{opacity:.9}
.tb-btn-ghost{font-size:12px;font-weight:600;color:#6366f1;background:none;border:none;cursor:pointer}
.tb-btn-ghost:hover{text-decoration:underline}

/* ── Table Card ── */
.tb-table-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden}
.tb-table{width:100%;border-collapse:separate;border-spacing:0}
.tb-table thead th{padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e2e8f0;white-space:nowrap;text-align:left}
.tb-table thead th:first-child{padding-left:20px}
.tb-row:hover{background:#f8faff}
.tb-table tbody td{padding:13px 16px;vertical-align:middle}
.tb-table tbody td:first-child{padding-left:20px}
.tb-row td{border-bottom:1px solid #f1f5f9}
.tb-row.expandable-open td{border-bottom-color:transparent}

.tb-avatar{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:11.5px;font-weight:700;flex-shrink:0;color:#fff}
.tb-title{font-size:13.5px;font-weight:700;color:#1e293b;cursor:pointer}
.tb-title:hover{color:#4f46e5;text-decoration:underline}
.tb-desc{font-size:11.5px;color:#94a3b8;margin-top:2px;max-width:280px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.tb-match-note{margin-top:5px;background:#eef2ff;border:1px solid #c7d2fe;border-radius:8px;padding:5px 8px;max-width:340px}
.tb-match-note div{display:flex;align-items:center;gap:5px;font-size:11px;color:#4338ca;line-height:1.4}
.tb-match-note div + div{margin-top:3px}
.tb-match-note svg{flex-shrink:0}
.dark .tb-match-note{background:rgba(99,102,241,.12);border-color:rgba(99,102,241,.3)}
.dark .tb-match-note div{color:#a5b4fc}
.tb-match-status{font-size:10px;font-weight:800;padding:1px 7px;border-radius:10px;white-space:nowrap;flex-shrink:0}
.tb-meta-btn{font-size:11px;color:#94a3b8;display:inline-flex;align-items:center;gap:4px;background:none;border:none;cursor:pointer;padding:0;margin-top:4px;margin-right:12px}
.tb-meta-btn:hover{color:#4f46e5}
.tb-meta-btn svg{width:11px;height:11px;transition:transform .15s}

.tb-date-val{font-size:12.5px;color:#334155;font-weight:500}
.tb-due-warn{font-size:11px;color:#ef4444;font-weight:600;display:flex;align-items:center;gap:3px;margin-top:2px}

/* ── Badges ── */
.tb-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap}
.tb-badge::before{content:'';width:6px;height:6px;border-radius:50%;flex-shrink:0}
.tb-badge-low{background:#f0fdf4;color:#15803d}.tb-badge-low::before{background:#22c55e}
.tb-badge-medium{background:#fffbeb;color:#b45309}.tb-badge-medium::before{background:#f59e0b}
.tb-badge-high{background:#fef2f2;color:#b91c1c}.tb-badge-high::before{background:#ef4444}
.tb-cat-chip{font-size:11px;font-weight:700;padding:3px 10px;border-radius:20px;white-space:nowrap;display:inline-flex;align-items:center;gap:5px}
.tb-cat-chip::before{content:'';width:6px;height:6px;border-radius:50%;flex-shrink:0;background:currentColor}
.tb-cat-parent{font-size:10px;color:#94a3b8;margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px}

/* ── Status dropdown (custom, not a native <select>) ── */
.tb-status-dd{position:relative;display:inline-block}
.tb-status-pill{display:inline-flex;align-items:center;gap:5px;padding:3px 8px 3px 10px;border-radius:20px;font-size:11px;font-weight:700;border:none;cursor:pointer}
.tb-status-pill::before{content:'';width:6px;height:6px;border-radius:50%;flex-shrink:0}
.tb-status-pill svg{width:11px;height:11px;transition:transform .15s;flex-shrink:0}
.tb-status-pending{background:#fffbeb;color:#b45309}.tb-status-pending::before{background:#f59e0b}
.tb-status-in-progress{background:#eff6ff;color:#1d4ed8}.tb-status-in-progress::before{background:#3b82f6}
.tb-status-completed{background:#f0fdf4;color:#15803d}.tb-status-completed::before{background:#22c55e}
.tb-status-cancelled{background:#f8fafc;color:#94a3b8}.tb-status-cancelled::before{background:#cbd5e1}
.tb-status-menu{position:absolute;top:calc(100% + 4px);left:0;z-index:20;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);padding:4px;min-width:132px}
.tb-status-opt{display:flex;align-items:center;gap:7px;width:100%;text-align:left;padding:6px 8px;border-radius:7px;border:none;background:none;font-size:12px;font-weight:600;color:#334155;cursor:pointer;white-space:nowrap}
.tb-status-opt:hover{background:#f1f5f9}
.tb-status-opt.active{background:#eef2ff;color:#4338ca}
.tb-status-opt-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.tb-dot-pending{background:#f59e0b}.tb-dot-in-progress{background:#3b82f6}.tb-dot-completed{background:#22c55e}.tb-dot-cancelled{background:#cbd5e1}
.dark .tb-status-menu{background:#1e293b;border-color:#334155}
.dark .tb-status-opt{color:#cbd5e1}
.dark .tb-status-opt:hover{background:#334155}
.dark .tb-status-opt.active{background:rgba(99,102,241,.15);color:#a5b4fc}

/* ── Days-running progress bar ── */
.tb-progress-track{width:80px;height:5px;border-radius:3px;background:#e2e8f0;overflow:hidden;margin-top:5px}
.tb-progress-fill{height:100%;border-radius:3px;transition:width .3s}
.tb-progress-lbl{font-size:12px;font-weight:700;color:#475569;margin-top:3px}
.dark .tb-progress-lbl{color:#cbd5e1}
.dark .tb-progress-track{background:#334155}

/* ── Action Buttons ── */
.tb-action-btn{width:29px;height:29px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .15s;color:#64748b}
.tb-action-btn:hover{background:#f1f5f9;border-color:#c7d2fe;color:#4f46e5}
.tb-action-btn.danger:hover{background:#fef2f2;border-color:#fecaca;color:#ef4444}
.tb-action-btn svg{width:14px;height:14px}

/* ── Expand Panel (sub-tasks) ── */
.tb-expand-row td{background:#fafbff;padding:0 16px 16px 16px !important}
.tb-expand-panel{padding-top:2px}
.tb-subtask{background:#fff;border:1px solid #eef0f7;border-radius:10px;padding:9px 14px;margin-bottom:6px}
.tb-subtask-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.tb-subtask-title{flex:1 1 220px;min-width:160px;font-size:13.5px}
.tb-subtask-days{font-size:12px;font-weight:700;color:#475569;white-space:nowrap;flex-shrink:0}
.dark .tb-subtask-days{color:#cbd5e1}
.tb-subtask-days.overdue{color:#ef4444;font-weight:700}
.tb-notes-link{font-size:12.5px;font-weight:600;color:#6366f1;background:#eef2ff;border:none;border-radius:7px;padding:5px 10px;cursor:pointer;white-space:nowrap;flex-shrink:0}
.tb-notes-link:hover{background:#e0e7ff}
.dark .tb-notes-link{background:rgba(99,102,241,.15);color:#a5b4fc}
.dark .tb-notes-link:hover{background:rgba(99,102,241,.25)}
.tb-assignee-select{font-size:12px;font-weight:500;color:#475569;background:#f8fafc;border:1px solid #e2e8f0;border-radius:7px;padding:4px 24px 4px 8px;outline:none;flex-shrink:0;max-width:120px;appearance:none;-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2394a3b8'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 6px center;background-size:12px}
.tb-assignee-select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}
.dark .tb-assignee-select{background-color:#0f172a;border-color:#334155;color:#cbd5e1}
.tb-note{background:#f8fafc;border-radius:8px;padding:8px 12px}
.tb-note-link{color:#4f46e5;text-decoration:underline;word-break:break-all}
.tb-note-link:hover{color:#4338ca}
.dark .tb-note-link{color:#a5b4fc}
.tb-add-row{display:flex;gap:8px;margin-top:6px}
.tb-mini-input{flex:1;border:1px solid #e2e8f0;border-radius:8px;padding:6px 10px;font-size:12.5px;outline:none;background:#fff}
.tb-mini-input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.1)}
.tb-mini-btn{border:1px solid #e2e8f0;border-radius:8px;padding:6px 12px;font-size:12px;font-weight:600;color:#475569;background:#fff;cursor:pointer;white-space:nowrap}
.tb-mini-btn:hover{background:#f8fafc}

/* ── Pagination ── */
.tb-pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid #f1f5f9}
.tb-page-info{font-size:12.5px;color:#94a3b8}
.tb-page-btns{display:flex;gap:4px}
.tb-page-btn{min-width:29px;height:29px;padding:0 6px;border-radius:7px;border:1px solid #e2e8f0;background:#fff;font-size:12px;font-weight:600;color:#475569;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s}
.tb-page-btn:hover:not(:disabled):not(.active){background:#f8fafc}
.tb-page-btn.active{background:#6366f1;color:#fff;border-color:#6366f1}
.tb-page-btn:disabled{opacity:.35;cursor:default}

/* ── Empty / Loading ── */
.tb-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:64px 24px;text-align:center}
.tb-empty svg{width:52px;height:52px;color:#e2e8f0}
.tb-empty h5{font-size:15px;font-weight:700;color:#475569;margin-top:14px}
.tb-empty p{font-size:12.5px;color:#94a3b8;margin-top:4px}

/* ── Dark Mode ── */
.dark .tb-stat-card{background:#1e293b;border-color:#334155}
.dark .tb-toolbar{background:#1e293b;border-color:#334155}
.dark .tb-toolbar-row + .tb-toolbar-row{border-color:#334155}
.dark .tb-search-wrap input,.dark .tb-select{background:#0f172a;border-color:#334155;color:#e2e8f0}
.dark .tb-tabs{background:#0f172a}
.dark .tb-tab:hover{background:#1e293b}
.dark .tb-tab.active{background:#1e293b;color:#f1f5f9}
.dark .tb-table-card{background:#1e293b;border-color:#334155}
.dark .tb-table thead th{background:#0f172a;border-color:#334155}
.dark .tb-row:hover{background:#1e3351}
.dark .tb-row td{border-color:#1e293b}
.dark .tb-title{color:#e2e8f0}
.dark .tb-date-val{color:#cbd5e1}
.dark .tb-action-btn{background:#1e293b;border-color:#334155;color:#94a3b8}
.dark .tb-action-btn:hover{background:#253347;border-color:#6366f1}
.dark .tb-expand-row td{background:#0f172a !important}
.dark .tb-subtask{background:#1e293b;border-color:#334155}
.dark .tb-note{background:#0f172a}
.dark .tb-mini-input{background:#1e293b;border-color:#334155;color:#e2e8f0}
.dark .tb-mini-btn{background:#1e293b;border-color:#334155;color:#cbd5e1}
.dark .tb-pagination{border-color:#334155}
.dark .tb-page-btn{background:#1e293b;border-color:#334155;color:#94a3b8}
.dark .tb-empty svg{color:#334155}
.dark .tb-empty h5{color:#94a3b8}
</style>

<div x-data="taskBoardPage(<?php echo e(($scopedToMe ?? false) ? 'true' : 'false'); ?>)" x-init="init()" x-cloak>

    
    <div class="tb-stats">
        <div class="tb-stat-card" :class="filters.status.length===0 && !filters.overdue ? 'active' : ''" @click="clearStatus()">
            <div class="tb-stat-icon" style="background:#ede9fe">
                <svg fill="none" viewBox="0 0 24 24" stroke="#7c3aed" stroke-width="1.8"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <div class="tb-stat-val" style="color:#7c3aed" x-text="stats.total ?? 0"></div>
                <div class="tb-stat-lbl">Total</div>
            </div>
        </div>
        <div class="tb-stat-card" :class="filters.status.includes('Pending') ? 'active' : ''" @click="toggleStatus('Pending')">
            <div class="tb-stat-icon" style="background:#fef9c3">
                <svg fill="none" viewBox="0 0 24 24" stroke="#b45309" stroke-width="1.8"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="tb-stat-val" style="color:#b45309" x-text="stats.pending ?? 0"></div>
                <div class="tb-stat-lbl">Pending</div>
            </div>
        </div>
        <div class="tb-stat-card" :class="filters.status.includes('In Progress') ? 'active' : ''" @click="toggleStatus('In Progress')">
            <div class="tb-stat-icon" style="background:#dbeafe">
                <svg fill="none" viewBox="0 0 24 24" stroke="#1d4ed8" stroke-width="1.8"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div>
                <div class="tb-stat-val" style="color:#1d4ed8" x-text="stats.in_progress ?? 0"></div>
                <div class="tb-stat-lbl">In Progress</div>
            </div>
        </div>
        <div class="tb-stat-card" :class="filters.status.includes('Completed') ? 'active' : ''" @click="toggleStatus('Completed')">
            <div class="tb-stat-icon" style="background:#dcfce7">
                <svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="1.8"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="tb-stat-val" style="color:#16a34a" x-text="stats.completed ?? 0"></div>
                <div class="tb-stat-lbl">Completed</div>
            </div>
        </div>
        <div class="tb-stat-card" :class="filters.overdue ? 'active' : ''" @click="setOverdueTab()">
            <div class="tb-stat-icon" style="background:#fee2e2">
                <svg fill="none" viewBox="0 0 24 24" stroke="#b91c1c" stroke-width="1.8"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </div>
            <div>
                <div class="tb-stat-val" style="color:#b91c1c" x-text="stats.overdue ?? 0"></div>
                <div class="tb-stat-lbl">Overdue</div>
            </div>
        </div>
    </div>

    
    <div class="tb-toolbar">
        <div class="tb-toolbar-row">
            <div class="tb-search-wrap">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" x-model="filters.search" @input.debounce.400ms="page=1; load()" placeholder="Search by task title…">
            </div>
            <select x-model="filters.category_id" @change="page=1; load()" class="tb-select">
                <option value="">All Categories</option>
                <template x-for="c in categories" :key="c.id">
                    <option :value="c.id" x-text="'—'.repeat(c.depth) + ' ' + c.name"></option>
                </template>
            </select>
            <select x-model="filters.priority" @change="page=1; load()" class="tb-select">
                <option value="">All Priorities</option>
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
            </select>
            <select x-model="filters.assigned_to" @change="page=1; load()" class="tb-select">
                <option value="">All Assignees</option>
                <template x-for="u in users" :key="u.id">
                    <option :value="u.id" x-text="u.name"></option>
                </template>
            </select>
            <label class="tb-overdue-toggle">
                <input type="checkbox" x-model="filters.overdue" @change="page=1; load()">
                Overdue only
            </label>
            <button class="tb-btn-ghost" @click="resetFilters()" style="margin-left:auto">Reset Filters</button>
        </div>
        <div class="tb-toolbar-row">
            <div class="tb-tabs">
                <button class="tb-tab" :class="filters.status.length===0 ? 'active' : ''" @click="clearStatus()">All</button>
                <button class="tb-tab" :class="filters.status.includes('Pending') ? 'active' : ''" @click="toggleStatus('Pending')">Pending</button>
                <button class="tb-tab" :class="filters.status.includes('In Progress') ? 'active' : ''" @click="toggleStatus('In Progress')">In Progress</button>
                <button class="tb-tab" :class="filters.status.includes('Completed') ? 'active' : ''" @click="toggleStatus('Completed')">Completed</button>
                <button class="tb-tab" :class="filters.status.includes('Cancelled') ? 'active' : ''" @click="toggleStatus('Cancelled')">Cancelled</button>
                <span class="text-[10px] text-gray-400 self-center ml-1" x-show="filters.status.length > 1">(any of these)</span>
            </div>
            <button @click="openCreate()" class="tb-btn-primary" style="margin-left:auto">
                <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
                Add Task
            </button>
        </div>
    </div>

    
    <div class="tb-table-card">
        <div class="overflow-x-auto">
            <table class="tb-table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Category</th>
                        <th>Assignee</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Due Date</th>
                        <th style="text-align:right;padding-right:20px">Actions</th>
                    </tr>
                </thead>

                
                <tbody x-show="loading">
                    <tr><td colspan="7" style="text-align:center;padding:56px 24px">
                        <div style="display:flex;align-items:center;justify-content:center;gap:8px;color:#94a3b8;font-size:13px">
                            <svg class="animate-spin" style="width:20px;height:20px;color:#6366f1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                            Loading tasks…
                        </div>
                    </td></tr>
                </tbody>

                <template x-for="task in tasks" :key="task.id">
                    <tbody>
                        <tr class="tb-row" :class="expandedTaskId === task.id ? 'expandable-open' : ''">
                            <td>
                                <div class="tb-title" @click="openDetail(task.id)" x-text="task.title"></div>
                                <div class="tb-desc" x-show="task.description" x-text="task.description"></div>
                                <template x-if="filters.assigned_to && (task.subtasks ?? []).length">
                                    <div class="tb-match-note">
                                        <template x-for="ms in task.subtasks" :key="ms.id">
                                            <div>
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                                <strong x-text="ms.title"></strong>
                                                <span class="tb-match-status" :class="'tb-status-' + ms.status.toLowerCase().replace(' ', '-')" x-text="ms.status"></span>
                                                — assigned to <strong x-text="ms.assignee?.name ?? '—'"></strong>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <div>
                                    <button class="tb-meta-btn" x-show="task.followups_count > 0" @click="openDetail(task.id)">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        <span x-text="task.followups_count"></span>
                                    </button>
                                    <button class="tb-meta-btn" @click="toggleExpand(task)">
                                        <svg :class="expandedTaskId === task.id ? 'rotate-90' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                        <span x-text="task.subtasks_count > 0 ? (task.subtasks_completed_count + '/' + task.subtasks_count + ' subtasks') : 'Sub-tasks'"></span>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <div class="tb-cat-parent" x-show="categoryParentPath(task)" x-text="categoryParentPath(task)"></div>
                                <span x-show="task.category" class="tb-cat-chip" :style="'background:' + (task.category?.color || '#94a3b8') + '1a; color:' + (task.category?.color || '#94a3b8')" x-text="task.category?.name"></span>
                                <span x-show="!task.category" class="text-gray-300 text-xs">—</span>
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px" x-show="task.assignee">
                                    <div class="tb-avatar" :style="'background:' + avatarColor(task.assignee?.name)" x-text="initials(task.assignee?.name)"></div>
                                    <span class="tb-date-val" x-text="task.assignee?.name"></span>
                                </div>
                                <span x-show="!task.assignee" class="text-gray-300 text-xs">Unassigned</span>
                            </td>
                            <td>
                                <span class="tb-badge" :class="'tb-badge-' + task.priority.toLowerCase()" x-text="task.priority"></span>
                            </td>
                            <td>
                                <div class="tb-status-dd" x-data="{ ddOpen: false }" @click.away="ddOpen = false">
                                    <button type="button" @click="ddOpen = !ddOpen" class="tb-status-pill" :class="'tb-status-' + task.status.toLowerCase().replace(' ', '-')">
                                        <span x-text="task.status"></span>
                                        <svg :class="ddOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div x-show="ddOpen" x-cloak class="tb-status-menu">
                                        <template x-for="st in ['Pending','In Progress','Completed','Cancelled']" :key="st">
                                            <button type="button" @click="quickStatus(task, st); ddOpen = false" class="tb-status-opt" :class="task.status === st ? 'active' : ''">
                                                <span class="tb-status-opt-dot" :class="'tb-dot-' + st.toLowerCase().replace(' ', '-')"></span>
                                                <span x-text="st"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="tb-date-val" x-show="task.due_date" x-text="fmtDate(task.due_date)"></div>
                                <div class="tb-due-warn" x-show="isOverdue(task)">
                                    <svg style="width:10px;height:10px" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    Overdue
                                </div>
                                <span x-show="!task.due_date" class="text-gray-300 text-xs">—</span>

                                <template x-if="!['Completed','Cancelled'].includes(task.status)">
                                    <div>
                                        <div class="tb-progress-track" x-show="task.due_date">
                                            <div class="tb-progress-fill" :style="'width:' + runProgress(task).pct + '%; background:' + (runProgress(task).overdue ? '#ef4444' : '#6366f1')"></div>
                                        </div>
                                        <div class="tb-progress-lbl" :class="runProgress(task).overdue ? 'text-red-500 font-semibold' : ''" x-text="runProgress(task).label"></div>
                                    </div>
                                </template>
                            </td>
                            <td style="text-align:right;padding-right:20px">
                                <div style="display:flex;align-items:center;justify-content:flex-end;gap:6px">
                                    <button @click="openDetail(task.id)" class="tb-action-btn" title="View">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button @click="openEdit(task)" class="tb-action-btn" title="Edit">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="scheduleOnCalendar(task)" class="tb-action-btn" title="Schedule on Calendar">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                    </button>
                                    <button @click="deleteTask(task)" class="tb-action-btn danger" title="Delete">
                                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr class="tb-expand-row" x-show="expandedTaskId === task.id">
                            <td colspan="7">
                                <div x-show="subtaskLoading" class="text-xs text-gray-400 py-2">Loading sub-tasks…</div>
                                <div x-show="!subtaskLoading" class="tb-expand-panel">
                                    <template x-for="st in (subtasksCache[task.id] ?? [])" :key="st.id">
                                        <div class="tb-subtask">
                                            <div class="tb-subtask-row">
                                                <div class="tb-status-dd" x-data="{ ddOpen: false }" @click.away="ddOpen = false">
                                                    <button type="button" @click="ddOpen = !ddOpen" class="tb-status-pill" :class="'tb-status-' + st.status.toLowerCase().replace(' ', '-')">
                                                        <span x-text="st.status"></span>
                                                        <svg :class="ddOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                                    </button>
                                                    <div x-show="ddOpen" x-cloak class="tb-status-menu">
                                                        <template x-for="opt in ['Pending','In Progress','Completed','Cancelled']" :key="opt">
                                                            <button type="button" @click="patchSubtask(task, st, { status: opt }); ddOpen = false" class="tb-status-opt" :class="st.status === opt ? 'active' : ''">
                                                                <span class="tb-status-opt-dot" :class="'tb-dot-' + opt.toLowerCase().replace(' ', '-')"></span>
                                                                <span x-text="opt"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </div>
                                                <span class="tb-subtask-title" :class="st.status === 'Completed' ? 'line-through text-gray-400' : 'text-gray-700 dark:text-gray-200'" x-text="st.title"></span>
                                                <select class="tb-assignee-select" @change="patchSubtask(task, st, { priority: $event.target.value })" title="Priority">
                                                    <option value="Low" :selected="st.priority === 'Low'">Low</option>
                                                    <option value="Medium" :selected="st.priority === 'Medium'">Medium</option>
                                                    <option value="High" :selected="st.priority === 'High'">High</option>
                                                </select>
                                                <select class="tb-assignee-select" @change="assignSubtask(task, st, $event.target.value)" title="Assign to">
                                                    <option value="" :selected="!st.assignee">Unassigned</option>
                                                    <template x-for="u in users" :key="u.id">
                                                        <option :value="u.id" :selected="st.assignee?.id === u.id" x-text="u.name"></option>
                                                    </template>
                                                </select>
                                                <input type="date" class="tb-assignee-select" style="max-width:140px" :value="st.due_date ? st.due_date.slice(0,10) : ''" @change="patchSubtask(task, st, { due_date: $event.target.value || null })" title="Due date" />
                                                <span class="tb-subtask-days" :class="subtaskProgress(st).overdue ? 'overdue' : ''" x-show="st.status !== 'Completed' && st.status !== 'Cancelled'" x-text="subtaskProgress(st).label"></span>
                                                <button @click="openSubtaskNotes(task, st)" class="tb-notes-link" title="Follow-up notes">
                                                    Notes<template x-if="(st.followups ?? []).length"><span x-text="' · ' + st.followups.length"></span></template>
                                                </button>
                                                <button @click="scheduleSubtaskOnCalendar(task, st)" class="text-gray-300 hover:text-indigo-600 flex-shrink-0" title="Schedule on Calendar">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                                </button>
                                                <button @click="deleteRowSubtask(task, st)" class="text-gray-300 hover:text-red-500 flex-shrink-0" title="Remove">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <p x-show="!(subtasksCache[task.id] ?? []).length" class="text-sm text-gray-400 py-1">No sub-tasks yet.</p>
                                    <div class="tb-add-row" style="margin-top:8px">
                                        <input type="text" x-model="newRowSubtask" @keydown.enter="addRowSubtask(task)" class="tb-mini-input" placeholder="Add a sub-task…" />
                                        <button @click="addRowSubtask(task)" class="tb-mini-btn">Add</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </template>

                <tbody x-show="!loading && tasks.length === 0">
                    <tr><td colspan="7">
                        <div class="tb-empty">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                            <h5>No tasks found</h5>
                            <p>Create a task or adjust your filters.</p>
                        </div>
                    </td></tr>
                </tbody>
            </table>
        </div>

        
        <div class="tb-pagination" x-show="meta.total > 0">
            <div class="tb-page-info" x-text="'Showing ' + meta.from + '–' + meta.to + ' of ' + meta.total + ' tasks'"></div>
            <div class="tb-page-btns">
                <button class="tb-page-btn" @click="page--; load()" :disabled="page <= 1">
                    <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M15 19l-7-7 7-7"/></svg>
                </button>
                <template x-for="p in pageNumbers" :key="p">
                    <button class="tb-page-btn" :class="p === page ? 'active' : ''" @click="page = p; load()" x-text="p"></button>
                </template>
                <button class="tb-page-btn" @click="page++; load()" :disabled="page >= meta.last_page">
                    <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editId ? 'Edit Task' : 'Add New Task'"></h3>
                <button @click="showModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">
                <div>
                    <label class="label">Title <span class="text-red-500">*</span></label>
                    <input x-model="form.title" type="text" class="input w-full" placeholder="e.g. Follow up with supplier on PUR-0021" required />
                </div>
                <div>
                    <label class="label">Description</label>
                    <textarea x-model="form.description" rows="3" class="input w-full resize-none" placeholder="Details about this task…"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Category</label>
                        <select x-model="form.category_id" class="input w-full">
                            <option value="">— No Category —</option>
                            <template x-for="c in categories" :key="c.id">
                                <option :value="c.id" x-text="'—'.repeat(c.depth) + ' ' + c.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="label">Assign To</label>
                        <select x-model="form.assigned_to" class="input w-full">
                            <option value="">— Unassigned —</option>
                            <template x-for="u in users" :key="u.id">
                                <option :value="u.id" x-text="u.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="label">Priority</label>
                        <select x-model="form.priority" class="input w-full">
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select x-model="form.status" class="input w-full">
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Due Date</label>
                        <input type="date" x-model="form.due_date" class="input w-full" />
                    </div>
                </div>

                <div x-show="formError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : 'Save Task'"></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Detail / Followup Modal -->
    <div x-show="showDetail" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="closeDetail()">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" x-show="detailTask">
            <div class="flex items-start justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="detailTask?.title"></h3>
                    <p class="text-xs text-gray-400 mt-1">
                        <span x-show="detailTask?.category" class="tb-cat-chip mr-1" :style="'background:' + (detailTask?.category?.color || '#94a3b8') + '1a; color:' + (detailTask?.category?.color || '#94a3b8')" x-text="detailTask?.category?.name"></span>
                        Assigned to <span x-text="detailTask?.assignee?.name ?? 'Unassigned'"></span>
                        <template x-if="detailTask?.due_date"><span> · Due <span x-text="fmtDate(detailTask.due_date)"></span></span></template>
                    </p>
                </div>
                <button @click="closeDetail()" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4" style="word-break:break-word" x-show="detailTask?.description" x-html="linkify(detailTask?.description)"></p>

                <div class="flex flex-wrap gap-2 mb-5">
                    <template x-for="st in ['Pending','In Progress','Completed','Cancelled']" :key="st">
                        <button @click="quickStatus(detailTask, st, true)"
                                class="text-xs font-semibold px-3 py-1.5 rounded-full border transition-colors"
                                :class="detailTask?.status === st ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-500 hover:bg-gray-50'"
                                x-text="st"></button>
                    </template>
                </div>

                <div class="flex items-center justify-between mb-2">
                    <div class="text-xs font-bold uppercase tracking-wide text-gray-400">Sub-tasks</div>
                    <span class="text-xs text-gray-400" x-show="(detailTask?.subtasks ?? []).length" x-text="subtaskCompletedCount + '/' + (detailTask?.subtasks ?? []).length + ' done'"></span>
                </div>
                <div class="space-y-1.5 mb-3">
                    <template x-for="st in (detailTask?.subtasks ?? [])" :key="st.id">
                        <div class="tb-subtask" style="margin-bottom:0">
                            <div class="tb-subtask-row">
                                <div class="tb-status-dd" x-data="{ ddOpen: false }" @click.away="ddOpen = false">
                                    <button type="button" @click="ddOpen = !ddOpen" class="tb-status-pill" :class="'tb-status-' + st.status.toLowerCase().replace(' ', '-')">
                                        <span x-text="st.status"></span>
                                        <svg :class="ddOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                    <div x-show="ddOpen" x-cloak class="tb-status-menu">
                                        <template x-for="opt in ['Pending','In Progress','Completed','Cancelled']" :key="opt">
                                            <button type="button" @click="patchSubtask(detailTask, st, { status: opt }); ddOpen = false" class="tb-status-opt" :class="st.status === opt ? 'active' : ''">
                                                <span class="tb-status-opt-dot" :class="'tb-dot-' + opt.toLowerCase().replace(' ', '-')"></span>
                                                <span x-text="opt"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <span class="tb-subtask-title" :class="st.status === 'Completed' ? 'line-through text-gray-400' : 'text-gray-700 dark:text-gray-200'" x-text="st.title"></span>
                                <select class="tb-assignee-select" @change="patchSubtask(detailTask, st, { priority: $event.target.value })" title="Priority">
                                    <option value="Low" :selected="st.priority === 'Low'">Low</option>
                                    <option value="Medium" :selected="st.priority === 'Medium'">Medium</option>
                                    <option value="High" :selected="st.priority === 'High'">High</option>
                                </select>
                                <select class="tb-assignee-select" @change="assignSubtask(detailTask, st, $event.target.value)" title="Assign to">
                                    <option value="" :selected="!st.assignee">Unassigned</option>
                                    <template x-for="u in users" :key="u.id">
                                        <option :value="u.id" :selected="st.assignee?.id === u.id" x-text="u.name"></option>
                                    </template>
                                </select>
                                <input type="date" class="tb-assignee-select" style="max-width:140px" :value="st.due_date ? st.due_date.slice(0,10) : ''" @change="patchSubtask(detailTask, st, { due_date: $event.target.value || null })" title="Due date" />
                                <span class="tb-subtask-days" :class="subtaskProgress(st).overdue ? 'overdue' : ''" x-show="st.status !== 'Completed' && st.status !== 'Cancelled'" x-text="subtaskProgress(st).label"></span>
                                <button @click="openSubtaskNotes(detailTask, st)" class="tb-notes-link flex-shrink-0" title="Follow-up notes">
                                    Notes<template x-if="(st.followups ?? []).length"><span x-text="' · ' + st.followups.length"></span></template>
                                </button>
                                <button @click="scheduleSubtaskOnCalendar(detailTask, st)" class="text-gray-300 hover:text-indigo-600 flex-shrink-0" title="Schedule on Calendar">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                </button>
                                <button @click="deleteSubtask(st)" class="text-gray-300 hover:text-red-500 flex-shrink-0" title="Remove">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                    <p x-show="!(detailTask?.subtasks ?? []).length" class="text-sm text-gray-400 py-1">No sub-tasks yet.</p>
                </div>
                <div class="flex gap-2 mb-5">
                    <input type="text" x-model="newSubtask" @keydown.enter="addSubtask()" class="input flex-1" placeholder="Add a sub-task…" />
                    <button @click="addSubtask()" class="btn-secondary text-sm">Add</button>
                </div>

                <div class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">Follow-ups &amp; Activity</div>
                <div class="flex gap-2 mb-4">
                    <input type="text" x-model="newNote" @keydown.enter="addFollowup()" class="input flex-1" placeholder="Add a follow-up note…" />
                    <button @click="addFollowup()" class="btn-primary text-sm">Add</button>
                </div>

                <div class="space-y-2 max-h-64 overflow-y-auto">
                    <template x-for="fu in (detailTask?.followups ?? [])" :key="fu.id">
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg px-3 py-2">
                            <div class="flex justify-between text-xs text-gray-400 mb-1">
                                <span class="font-semibold text-gray-600 dark:text-gray-300" x-text="fu.user?.name ?? 'System'"></span>
                                <span x-text="timeAgo(fu.created_at)"></span>
                            </div>
                            <div class="text-sm text-gray-700 dark:text-gray-200" style="word-break:break-word" x-html="linkify(fu.note)"></div>
                        </div>
                    </template>
                    <p x-show="!(detailTask?.followups ?? []).length" class="text-sm text-gray-400 text-center py-6">No follow-ups yet. Add the first update above.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                <button @click="closeDetail()" class="btn-secondary">Close</button>
                <button @click="scheduleOnCalendar(detailTask)" class="btn-secondary">Schedule on Calendar</button>
                <button @click="openEdit(detailTask); closeDetail()" class="btn-primary">Edit Task</button>
            </div>
        </div>
    </div>

    <!-- Sub-task Notes Modal -->
    <div x-show="notesSubtask" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="closeSubtaskNotes()">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg max-h-[85vh] overflow-y-auto">
            <div class="flex items-start justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <div class="text-xs font-bold uppercase tracking-wide text-gray-400">Sub-task Notes</div>
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mt-0.5" x-text="notesSubtask?.title"></h3>
                </div>
                <button @click="closeSubtaskNotes()" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6">
                <div class="flex gap-2 mb-4">
                    <input type="text" x-model="subtaskNewNote" @keydown.enter="addSubtaskNote(notesTask, notesSubtask)" class="input flex-1" placeholder="Add a note…" />
                    <button @click="addSubtaskNote(notesTask, notesSubtask)" class="btn-primary text-sm">Add</button>
                </div>
                <div class="space-y-2 max-h-72 overflow-y-auto">
                    <template x-for="f in (notesSubtask?.followups ?? [])" :key="f.id">
                        <div class="tb-note">
                            <div class="flex justify-between text-xs text-gray-400 mb-1">
                                <span class="font-semibold text-gray-600 dark:text-gray-300" x-text="f.user?.name ?? 'System'"></span>
                                <span x-text="timeAgo(f.created_at)"></span>
                            </div>
                            <div class="text-sm text-gray-700 dark:text-gray-200" style="word-break:break-word" x-html="linkify(f.note)"></div>
                        </div>
                    </template>
                    <p x-show="!(notesSubtask?.followups ?? []).length" class="text-sm text-gray-400 text-center py-6">No notes yet. Add the first update above.</p>
                </div>
            </div>
        </div>
    </div>

</div>

<?php $__env->startPush('scripts'); ?>
<script>
function taskBoardPage(scopedToMe = false) {
    return {
        tasks: [],
        categories: [],
        users: [],
        loading: true,
        page: 1,
        meta: { total: 0, from: 1, to: 0, last_page: 1 },
        filters: { category_id: '', status: [], priority: '', assigned_to: '', overdue: false, search: '' },
        stats: {},
        // Only true when embedded on the Task Manager Dashboard, which passes
        // scopedToMe=true when including this partial. On the standalone
        // Task Board page this stays permanently false/inert.
        myOnly: false,

        showModal: false,
        editId: null,
        saving: false,
        formError: '',
        form: {},

        showDetail: false,
        detailTask: null,
        newNote: '',
        newSubtask: '',

        expandedTaskId: null,
        subtasksCache: {},
        subtaskLoading: false,
        newRowSubtask: '',

        notesTask: null,
        notesSubtask: null,
        subtaskNewNote: '',

        get subtaskCompletedCount() {
            return (this.detailTask?.subtasks ?? []).filter(s => s.completed).length;
        },

        get pageNumbers() {
            const total = this.meta.last_page || 1;
            const cur = this.page;
            const span = 2;
            let start = Math.max(1, cur - span);
            let end = Math.min(total, cur + span);
            const pages = [];
            for (let i = start; i <= end; i++) pages.push(i);
            return pages;
        },

        blank() {
            return { title: '', description: '', category_id: '', assigned_to: '', priority: 'Medium', status: 'Pending', due_date: '' };
        },

        async init() {
            if (scopedToMe) {
                try {
                    const stored = localStorage.getItem('tm_dashboard_scope');
                    this.myOnly = stored ? stored === 'mine' : true;
                } catch (e) { this.myOnly = true; }
                // Dashboard's My Tasks / All Tasks toggle lives in a separate Alpine
                // component — this keeps the embedded table in sync with it live,
                // instead of only matching whatever scope was active on page load.
                window.addEventListener('tm-scope-changed', (e) => {
                    this.myOnly = e.detail.mine;
                    this.page = 1;
                    this.load();
                    this.loadStats();
                });
            }
            await Promise.all([this.loadCategories(), this.loadUsers(), this.loadStats()]);
            await this.load();
            const openId = new URLSearchParams(window.location.search).get('open');
            if (openId) this.openDetail(openId);
        },

        async loadCategories() {
            try {
                this.categories = await apiFetch('/work-task-categories').then(r => r.json());
            } catch (e) { /* filter dropdown just stays empty */ }
        },

        async loadUsers() {
            try {
                this.users = await apiFetch('/work-tasks/assignable-users').then(r => r.json());
            } catch (e) { /* dropdown just stays empty */ }
        },

        async loadStats() {
            try {
                const qs = this.myOnly ? '?my_tasks=1' : '';
                const data = await apiFetch('/work-tasks/dashboard' + qs).then(r => r.json());
                this.stats = data.stats ?? {};
            } catch (e) { /* stat cards just stay at 0 */ }
        },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page: this.page, per_page: 15 });
                if (this.myOnly) params.set('my_tasks', '1');
                if (this.filters.category_id) params.set('category_id', this.filters.category_id);
                if (this.filters.status.length) params.set('status', this.filters.status.join(','));
                if (this.filters.priority) params.set('priority', this.filters.priority);
                if (this.filters.assigned_to) params.set('assigned_to', this.filters.assigned_to);
                if (this.filters.overdue) params.set('overdue', '1');
                if (this.filters.search) params.set('search', this.filters.search);
                const data = await apiFetch('/work-tasks?' + params.toString()).then(r => r.json());
                this.tasks = data.data ?? [];
                this.meta = { total: data.total ?? 0, from: data.from ?? 0, to: data.to ?? 0, last_page: data.last_page ?? 1 };
            } catch (e) {
                toast(e.message ?? 'Failed to load tasks', 'error');
            } finally {
                this.loading = false;
            }
        },

        toggleStatus(status) {
            const idx = this.filters.status.indexOf(status);
            if (idx >= 0) this.filters.status.splice(idx, 1);
            else this.filters.status.push(status);
            this.filters.overdue = false;
            this.page = 1;
            this.load();
        },

        clearStatus() {
            this.filters.status = [];
            this.filters.overdue = false;
            this.page = 1;
            this.load();
        },

        setOverdueTab() {
            this.filters.overdue = !this.filters.overdue;
            this.page = 1;
            this.load();
        },

        resetFilters() {
            this.filters = { category_id: '', status: [], priority: '', assigned_to: '', overdue: false, search: '' };
            this.page = 1;
            this.load();
        },

        openCreate() {
            this.editId = null;
            this.form = this.blank();
            this.formError = '';
            this.showModal = true;
        },

        openEdit(task) {
            this.editId = task.id;
            this.form = {
                title: task.title ?? '',
                description: task.description ?? '',
                category_id: task.category_id ?? task.category?.id ?? '',
                assigned_to: task.assigned_to ?? task.assignee?.id ?? '',
                priority: task.priority ?? 'Medium',
                status: task.status ?? 'Pending',
                due_date: task.due_date ? task.due_date.slice(0, 10) : '',
            };
            this.formError = '';
            this.showModal = true;
        },

        async save() {
            if (!this.form.title) { toast('Title is required', 'error'); return; }
            this.saving = true;
            this.formError = '';
            try {
                const payload = {
                    ...this.form,
                    category_id: this.form.category_id || null,
                    assigned_to: this.form.assigned_to || null,
                    due_date: this.form.due_date || null,
                };
                const url = this.editId ? '/work-tasks/' + this.editId : '/work-tasks';
                const method = this.editId ? 'PUT' : 'POST';
                await apiFetch(url, { method, body: JSON.stringify(payload) });
                toast(this.editId ? 'Task updated.' : 'Task created.');
                this.showModal = false;
                await this.load();
                await this.loadStats();
            } catch (e) {
                this.formError = e.message ?? 'Unexpected error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async deleteTask(task) {
            if (!confirm(`Delete "${task.title}"? This cannot be undone.`)) return;
            try {
                await apiFetch('/work-tasks/' + task.id, { method: 'DELETE' });
                toast('Task deleted.');
                await this.load();
                await this.loadStats();
            } catch (e) {
                toast(e.message ?? 'Failed to delete task', 'error');
            }
        },

        async quickStatus(task, status, fromDetail = false) {
            if (!task) return;
            try {
                const updated = await apiFetch('/work-tasks/' + task.id + '/status', { method: 'PATCH', body: JSON.stringify({ status }) }).then(r => r.json());
                const idx = this.tasks.findIndex(t => t.id === task.id);
                if (idx >= 0) this.tasks[idx] = updated;
                if (fromDetail) await this.openDetail(task.id);
                await this.loadStats();
                toast('Status updated.');
            } catch (e) {
                toast(e.message ?? 'Failed to update status', 'error');
            }
        },

        async openDetail(id) {
            try {
                this.detailTask = await apiFetch('/work-tasks/' + id).then(r => r.json());
                this.newNote = '';
                this.showDetail = true;
            } catch (e) {
                toast(e.message ?? 'Failed to load task', 'error');
            }
        },

        closeDetail() {
            this.showDetail = false;
            this.detailTask = null;
        },

        scheduleOnCalendar(task) {
            const prefill = {
                title: task.title,
                description: task.description ?? '',
                assigned_to: task.assigned_to ?? task.assignee?.id ?? '',
                linked_module: 'work_task',
                linked_id: task.id,
                date: task.due_date ? task.due_date.slice(0, 10) : null,
            };
            try { sessionStorage.setItem('calendar_task_prefill', JSON.stringify(prefill)); } catch (e) {}
            window.location.href = '<?php echo e(url('/calendar')); ?>';
        },

        scheduleSubtaskOnCalendar(task, subtask) {
            const prefill = {
                title: task.title ? task.title + ' — ' + subtask.title : subtask.title,
                description: '',
                assigned_to: subtask.assigned_to ?? subtask.assignee?.id ?? '',
                linked_module: 'work_task',
                linked_id: task.id,
                date: subtask.due_date ? subtask.due_date.slice(0, 10) : null,
            };
            try { sessionStorage.setItem('calendar_task_prefill', JSON.stringify(prefill)); } catch (e) {}
            window.location.href = '<?php echo e(url('/calendar')); ?>';
        },

        async addFollowup() {
            if (!this.newNote.trim()) return;
            try {
                await apiFetch('/work-tasks/' + this.detailTask.id + '/followups', { method: 'POST', body: JSON.stringify({ note: this.newNote }) });
                this.newNote = '';
                await this.openDetail(this.detailTask.id);
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to add follow-up', 'error');
            }
        },

        async addSubtask() {
            if (!this.newSubtask.trim()) return;
            try {
                await apiFetch('/work-tasks/' + this.detailTask.id + '/subtasks', { method: 'POST', body: JSON.stringify({ title: this.newSubtask }) });
                this.newSubtask = '';
                await this.openDetail(this.detailTask.id);
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to add sub-task', 'error');
            }
        },

        async deleteSubtask(subtask) {
            if (!confirm(`Delete sub-task "${subtask.title}"? This cannot be undone.`)) return;
            try {
                await apiFetch('/work-tasks/' + this.detailTask.id + '/subtasks/' + subtask.id, { method: 'DELETE' });
                this.detailTask.subtasks = this.detailTask.subtasks.filter(s => s.id !== subtask.id);
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to remove sub-task', 'error');
            }
        },

        async assignSubtask(task, subtask, userId) {
            try {
                const updated = await apiFetch('/work-tasks/' + task.id + '/subtasks/' + subtask.id, { method: 'PATCH', body: JSON.stringify({ assigned_to: userId || null }) }).then(r => r.json());
                subtask.assignee = updated.assignee;
                toast('Sub-task assignee updated.');
            } catch (e) {
                toast(e.message ?? 'Failed to update assignee', 'error');
            }
        },

        async patchSubtask(task, subtask, payload) {
            const wasCompleted = subtask.status === 'Completed';
            try {
                const updated = await apiFetch('/work-tasks/' + task.id + '/subtasks/' + subtask.id, { method: 'PATCH', body: JSON.stringify(payload) }).then(r => r.json());
                Object.assign(subtask, updated);
                if ('status' in payload) {
                    const nowCompleted = subtask.status === 'Completed';
                    if (wasCompleted !== nowCompleted) {
                        task.subtasks_completed_count = Math.max(0, (task.subtasks_completed_count ?? 0) + (nowCompleted ? 1 : -1));
                    }
                }
            } catch (e) {
                toast(e.message ?? 'Failed to update sub-task', 'error');
            }
        },

        subtaskProgress(subtask) {
            const created = new Date(subtask.created_at);
            const now = new Date();
            const elapsedDays = Math.max(0, Math.floor((now - created) / 86400000));

            if (!subtask.due_date) {
                return { overdue: false, label: elapsedDays + ' day' + (elapsedDays !== 1 ? 's' : '') + ' running' };
            }

            const due = new Date(subtask.due_date);
            const overdue = now > due;
            return { overdue, label: overdue ? elapsedDays + ' days (overdue)' : elapsedDays + ' days running' };
        },

        async toggleExpand(task) {
            if (this.expandedTaskId === task.id) {
                this.expandedTaskId = null;
                return;
            }
            this.expandedTaskId = task.id;
            this.newRowSubtask = '';
            if (this.subtasksCache[task.id]) return;
            this.subtaskLoading = true;
            try {
                const full = await apiFetch('/work-tasks/' + task.id).then(r => r.json());
                this.subtasksCache[task.id] = full.subtasks ?? [];
            } catch (e) {
                toast(e.message ?? 'Failed to load sub-tasks', 'error');
                this.expandedTaskId = null;
            } finally {
                this.subtaskLoading = false;
            }
        },

        async addRowSubtask(task) {
            if (!this.newRowSubtask.trim()) return;
            try {
                const created = await apiFetch('/work-tasks/' + task.id + '/subtasks', { method: 'POST', body: JSON.stringify({ title: this.newRowSubtask }) }).then(r => r.json());
                (this.subtasksCache[task.id] ??= []).push(created);
                this.newRowSubtask = '';
                task.subtasks_count = (task.subtasks_count ?? 0) + 1;
            } catch (e) {
                toast(e.message ?? 'Failed to add sub-task', 'error');
            }
        },

        async deleteRowSubtask(task, subtask) {
            if (!confirm(`Delete sub-task "${subtask.title}"? This cannot be undone.`)) return;
            try {
                await apiFetch('/work-tasks/' + task.id + '/subtasks/' + subtask.id, { method: 'DELETE' });
                this.subtasksCache[task.id] = (this.subtasksCache[task.id] ?? []).filter(s => s.id !== subtask.id);
                task.subtasks_count = Math.max(0, (task.subtasks_count ?? 1) - 1);
                if (subtask.completed) task.subtasks_completed_count = Math.max(0, (task.subtasks_completed_count ?? 1) - 1);
            } catch (e) {
                toast(e.message ?? 'Failed to remove sub-task', 'error');
            }
        },

        openSubtaskNotes(task, subtask) {
            this.notesTask = task;
            this.notesSubtask = subtask;
            this.subtaskNewNote = '';
        },

        closeSubtaskNotes() {
            this.notesTask = null;
            this.notesSubtask = null;
        },

        async addSubtaskNote(task, subtask) {
            if (!this.subtaskNewNote.trim()) return;
            try {
                const created = await apiFetch('/work-tasks/' + task.id + '/subtasks/' + subtask.id + '/followups', { method: 'POST', body: JSON.stringify({ note: this.subtaskNewNote }) }).then(r => r.json());
                (subtask.followups ??= []).unshift(created);
                this.subtaskNewNote = '';
            } catch (e) {
                toast(e.message ?? 'Failed to add note', 'error');
            }
        },

        isOverdue(task) {
            return task.due_date && !['Completed', 'Cancelled'].includes(task.status) && new Date(task.due_date) < new Date().setHours(0, 0, 0, 0);
        },

        categoryParentPath(task) {
            if (!task.category || !task.category.parent_id) return '';
            const map = {};
            this.categories.forEach(c => { map[c.id] = c; });
            const chain = [];
            let curId = task.category.parent_id;
            let guard = 0;
            while (curId && map[curId] && guard++ < 20) {
                chain.unshift(map[curId].name);
                curId = map[curId].parent_id;
            }
            return chain.join(' › ');
        },

        runProgress(task) {
            const created = new Date(task.created_at);
            const now = new Date();
            const elapsedDays = Math.max(0, Math.floor((now - created) / 86400000));

            if (!task.due_date) {
                return { pct: 0, overdue: false, label: elapsedDays + ' day' + (elapsedDays !== 1 ? 's' : '') + ' running' };
            }

            const due = new Date(task.due_date);
            const totalDays = Math.max(1, Math.ceil((due - created) / 86400000));
            const overdue = now > due;
            const pct = overdue ? 100 : Math.min(100, Math.round((elapsedDays / totalDays) * 100));
            const label = overdue ? elapsedDays + ' days (overdue)' : elapsedDays + '/' + totalDays + ' days';
            return { pct, overdue, label };
        },

        initials(name) {
            if (!name) return '?';
            const parts = name.trim().split(/\s+/);
            return ((parts[0]?.[0] ?? '') + (parts[1]?.[0] ?? '')).toUpperCase();
        },

        avatarColor(name) {
            const palette = ['#4f46e5', '#0891b2', '#7c3aed', '#059669', '#d97706', '#db2777', '#0d9488', '#dc2626'];
            if (!name) return palette[0];
            let hash = 0;
            for (let i = 0; i < name.length; i++) hash = name.charCodeAt(i) + ((hash << 5) - hash);
            return palette[Math.abs(hash) % palette.length];
        },

        fmtDate(d) {
            if (!d) return '—';
            return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        },

        timeAgo(d) {
            if (!d) return '';
            const secs = Math.floor((new Date() - new Date(d)) / 1000);
            if (secs < 60) return 'just now';
            const mins = Math.floor(secs / 60); if (mins < 60) return mins + 'm ago';
            const hrs = Math.floor(mins / 60); if (hrs < 24) return hrs + 'h ago';
            const days = Math.floor(hrs / 24); if (days < 30) return days + 'd ago';
            return this.fmtDate(d);
        },

        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        },

        linkify(text) {
            if (!text) return '';
            const escaped = this.escapeHtml(text);
            return escaped.replace(/(https?:\/\/[^\s<]+|www\.[^\s<]+)/gi, (match) => {
                let trail = '';
                while (match.length && '.,;:!?)]}\'"'.includes(match[match.length - 1])) {
                    trail = match[match.length - 1] + trail;
                    match = match.slice(0, -1);
                }
                const href = /^www\./i.test(match) ? 'https://' + match : match;
                return '<a href="' + href + '" target="_blank" rel="noopener noreferrer" class="tb-note-link">' + match + '</a>' + trail;
            });
        },
    };
}
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views/task-manager/_board-table.blade.php ENDPATH**/ ?>