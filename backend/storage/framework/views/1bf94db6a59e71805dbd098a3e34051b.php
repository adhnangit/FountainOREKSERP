<?php $__env->startSection('title', 'Chart of Accounts'); ?>
<?php $__env->startSection('page-title', 'Chart of Accounts'); ?>
<?php $__env->startSection('page-desc', 'Manage your financial structure — Head Accounts & Sub-Accounts'); ?>

<?php $__env->startSection('content'); ?>
<style>
.coa-wrapper{font-family:'Inter',sans-serif;height:calc(100vh - 178px);display:flex;border-radius:12px;overflow:hidden;border:1px solid #e2e8f0;box-shadow:0 4px 24px rgba(0,0,0,.06)}
.coa-sidebar{width:360px;min-width:360px;flex-shrink:0;overflow-y:auto;background:#fff;border-right:1px solid #e2e8f0;display:flex;flex-direction:column}
.coa-sidebar-header{padding:14px 14px 10px;background:#f8fafc;border-bottom:1px solid #e2e8f0;position:sticky;top:0;z-index:10}
.coa-sidebar-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:10px}
.coa-search-box{border-radius:8px;border:1px solid #e2e8f0;overflow:hidden;display:flex;align-items:center;background:#fff;margin-bottom:8px}
.coa-search-box svg{width:14px;height:14px;flex-shrink:0;margin:0 8px;color:#94a3b8}
.coa-search-box input{border:none;outline:none;padding:7px 8px 7px 0;font-size:12.5px;flex:1;background:transparent;color:#1e293b}
.coa-filter-select{border:1px solid #e2e8f0;border-radius:8px;padding:6px 10px;font-size:12px;color:#475569;width:100%;background:#fff;outline:none}
.coa-type-group{display:flex;align-items:center;justify-content:space-between;padding:8px 14px;background:#f1f5f9;border-bottom:1px solid #e2e8f0;cursor:pointer;user-select:none;transition:background .15s}
.coa-type-group:hover{background:#e8f0fe}
.coa-type-badge{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;padding:2px 9px;border-radius:20px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:260px}
.tb-income{background:#dcfce7;color:#166534}.tb-expense{background:#fef9c3;color:#854d0e}
.tb-asset{background:#dbeafe;color:#1d4ed8}.tb-liability{background:#fee2e2;color:#b91c1c}.tb-equity{background:#d1fae5;color:#065f46}
.coa-acc-row{display:flex;align-items:center;justify-content:space-between;padding:8px 14px 8px 18px;border-bottom:1px solid #f1f5f9;cursor:pointer;transition:background .12s}
.coa-acc-row:hover{background:#f8fafc}
.coa-acc-row.active{background:linear-gradient(135deg,#1e3a5f,#2563eb);border-bottom-color:transparent;box-shadow:0 4px 15px rgba(30,58,95,.2)}
.coa-acc-row.active .coa-acc-name{color:#fff;font-weight:600}
.coa-acc-row.active .coa-acc-bal{color:rgba(255,255,255,.75)}
.coa-acc-row.active .coa-acc-dot{background:rgba(255,255,255,.5)}
.coa-acc-dot{width:5px;height:5px;border-radius:50%;background:#cbd5e1;margin-right:8px;flex-shrink:0}
.coa-acc-name{font-size:13px;font-weight:400;color:#1e293b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.coa-acc-bal{font-size:11.5px;color:#94a3b8;white-space:nowrap}
.coa-plus-btn{width:20px;height:20px;border-radius:50%;background:#22c55e;color:#fff;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;transition:background .15s}
.coa-plus-btn:hover{background:#16a34a}
.coa-main{flex:1;overflow-y:auto;display:flex;flex-direction:column;background:#f8fafc;min-width:0}
.coa-acc-header{background:linear-gradient(135deg,#0f172a,#1e3a5f,#2563eb);color:#fff;padding:20px 28px;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;box-shadow:0 4px 20px rgba(0,0,0,.15);gap:12px}
.coa-acc-header h4{margin:0;font-size:20px;font-weight:700}
.coa-acc-meta{font-size:12px;opacity:.8;margin-top:6px;display:flex;gap:8px;flex-wrap:wrap}
.coa-meta-chip{background:rgba(255,255,255,.15);padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;backdrop-filter:blur(4px)}
.coa-hdr-actions{display:flex;gap:8px;flex-shrink:0}
.coa-hdr-btn{border-radius:8px;padding:6px 14px;font-size:12px;font-family:inherit;background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);cursor:pointer;display:flex;align-items:center;gap:5px;transition:background .15s;white-space:nowrap}
.coa-hdr-btn:hover{background:rgba(255,255,255,.25)}
.coa-hdr-btn-del{border-radius:8px;padding:6px 12px;font-family:inherit;background:#ef4444;color:#fff;border:none;cursor:pointer;display:flex;align-items:center;transition:background .15s}
.coa-hdr-btn-del:hover{background:#dc2626}
.coa-duration-bar{display:flex;align-items:center;gap:8px;padding:10px 24px;background:#fff;border-bottom:1px solid #e2e8f0;flex-shrink:0;flex-wrap:wrap}
.coa-dur-label{font-size:12px;font-weight:600;color:#64748b}
.coa-dur-btn{border:1px solid #e2e8f0;background:#f8fafc;border-radius:8px;padding:4px 12px;font-size:12px;color:#334155;cursor:pointer;transition:all .1s;font-family:inherit;line-height:1.5}
.coa-dur-btn:hover{background:#f1f5f9}
.coa-dur-btn.active{background:#1e293b;color:#fff;border-color:#1e293b}
.coa-nav-btn{border:1px solid #e2e8f0;background:#fff;border-radius:8px;padding:3px 11px;font-size:16px;line-height:1.6;color:#334155;cursor:pointer;font-family:inherit;transition:background .1s}
.coa-nav-btn:hover{background:#f1f5f9}
.coa-period-lbl{font-size:13px;font-weight:600;color:#1e293b;white-space:nowrap}
.coa-clear-btn{margin-left:auto;border:1px solid #fca5a5;background:#fff;border-radius:8px;padding:4px 14px;font-size:11.5px;color:#ef4444;cursor:pointer;transition:all .15s;font-family:inherit;display:flex;align-items:center;gap:4px}
.coa-clear-btn:hover{background:#fee2e2}
.coa-custom-date{border:1px solid #e2e8f0;border-radius:8px;padding:4px 10px;font-size:12px;color:#334155;outline:none;background:#f8fafc;font-family:inherit}
.coa-balance-row{display:flex;gap:14px;padding:14px 24px;background:#fff;border-bottom:1px solid #e2e8f0;flex-shrink:0}
.coa-bal-card{flex:1;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0;padding:14px 18px}
.coa-bal-lbl{font-size:10px;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;font-weight:700;margin-bottom:6px}
.coa-bal-val{font-size:22px;font-weight:800;display:flex;align-items:center;gap:8px}
.coa-bal-val.debit{color:#2563eb}.coa-bal-val.credit{color:#10b981}.coa-bal-val.danger{color:#ef4444}
.coa-badge-dr{font-size:11px;font-weight:700;background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:6px}
.coa-badge-cr{font-size:11px;font-weight:700;background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:6px}
.coa-section{padding:20px 24px;background:#f8fafc;flex:1}
.coa-sec-title{font-size:11px;text-transform:uppercase;letter-spacing:.08em;font-weight:700;color:#94a3b8;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.coa-sec-title::after{content:'';flex:1;height:1px;background:#e2e8f0}
.coa-ledger-wrap{background:#fff;border-radius:10px;overflow:hidden;border:1px solid #e2e8f0}
.coa-table{width:100%;border-collapse:separate;border-spacing:0}
.coa-table thead th{background:#fff;padding:10px 14px;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;font-weight:700;border-bottom:1px solid #e2e8f0}
.coa-table tbody tr{transition:background .12s}
.coa-table tbody tr:hover{background:#f0f7ff}
.coa-table tbody td{padding:10px 14px;font-size:13px;border-bottom:1px solid #f1f5f9;vertical-align:middle;color:#1e293b}
.coa-table tfoot td{padding:10px 14px;font-size:13px;background:#f8fafc;border-top:2px solid #e2e8f0;font-weight:700}
.coa-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;flex:1;padding:80px 20px;text-align:center;background:#fff}
.coa-empty svg{width:56px;height:56px;color:#cbd5e1}
.coa-empty h5{font-size:16px;margin-top:14px;color:#475569;font-weight:700}
.coa-empty p{font-size:13px;max-width:280px;color:#94a3b8;margin-top:6px}
/* dark */
.dark .coa-wrapper{border-color:#334155}
.dark .coa-sidebar{background:#1e293b;border-right-color:#334155}
.dark .coa-sidebar-header{background:#0f172a;border-bottom-color:#334155}
.dark .coa-search-box{background:#1e293b;border-color:#334155}
.dark .coa-search-box input{color:#e2e8f0}
.dark .coa-filter-select{background:#1e293b;border-color:#334155;color:#cbd5e1}
.dark .coa-type-group{background:#1a2535;border-bottom-color:#334155}
.dark .coa-type-group:hover{background:#253347}
.dark .coa-acc-row{border-bottom-color:#1e293b}
.dark .coa-acc-row:hover{background:#253347}
.dark .coa-acc-name{color:#e2e8f0}
.dark .coa-main{background:#0f172a}
.dark .coa-duration-bar,.dark .coa-balance-row{background:#1e293b;border-color:#334155}
.dark .coa-dur-btn{background:#1e293b;border-color:#334155;color:#cbd5e1}
.dark .coa-dur-btn.active{background:#e2e8f0;color:#1e293b;border-color:#e2e8f0}
.dark .coa-nav-btn,.dark .coa-custom-date{background:#1e293b;border-color:#334155;color:#cbd5e1}
.dark .coa-period-lbl{color:#e2e8f0}
.dark .coa-clear-btn{background:#1e293b;border-color:#7f1d1d;color:#fca5a5}
.dark .coa-clear-btn:hover{background:#450a0a}
.dark .coa-section{background:#0f172a}
.dark .coa-sec-title::after{background:#334155}
.dark .coa-bal-card{background:#0f172a;border-color:#334155}
.dark .coa-ledger-wrap{border-color:#334155}
.dark .coa-table thead th{background:#1e293b;border-color:#334155;color:#64748b}
.dark .coa-table tbody td{border-color:#1e293b;color:#e2e8f0}
.dark .coa-table tbody tr:hover{background:#1e3351}
.dark .coa-table tfoot td{background:#1e293b;border-color:#334155;color:#e2e8f0}
.dark .coa-empty{background:#1e293b}
.dark .coa-empty h5{color:#94a3b8}
</style>

<div x-data="chartOfAccounts()" x-init="init()">

  
  <div class="flex items-center justify-between mb-3 gap-3 flex-wrap">
    <div x-show="obCheck" x-cloak
         class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold"
         :style="obCheck?.balanced
            ? 'background:#dcfce7;color:#166534'
            : 'background:#fef3c7;color:#92400e'">
      <svg x-show="obCheck?.balanced" style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M5 13l4 4L19 7"/></svg>
      <svg x-show="!obCheck?.balanced" style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 9v4m0 4h.01M10.29 3.86l-8.18 14.14A1.5 1.5 0 003.5 20h17a1.5 1.5 0 001.39-2L13.71 3.86a1.5 1.5 0 00-2.42 0z"/></svg>
      <span x-show="obCheck?.balanced">Opening balances are in balance</span>
      <span x-show="!obCheck?.balanced">
        Opening balances off by Rs <span x-text="fmtAmount(Math.abs(obCheck?.difference??0))"></span> — a historical opening balance needs correcting; new account entries auto-balance via "Opening Balance Equity"
      </span>
    </div>
    <button @click="openCreateModal(null)"
            style="background:#1B3EB6"
            class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white shadow transition-opacity hover:opacity-90">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
      Add Head Account
    </button>
  </div>

  <div class="coa-wrapper">

    
    <div class="coa-sidebar">
      <div class="coa-sidebar-header">
        <div class="coa-sidebar-title">List of Accounts</div>
        <div class="coa-search-box">
          <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
          <input type="text" placeholder="Search accounts..." x-model="search" @input="buildFiltered()">
        </div>
        <select class="coa-filter-select" x-model="filterGroup" @change="buildFiltered()">
          <option value="">All Categories</option>
          <template x-for="g in groups" :key="g.id">
            <option :value="g.id" x-text="g.name"></option>
          </template>
        </select>
      </div>

      <div x-show="listLoading" class="flex justify-center py-10">
        <svg class="animate-spin w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
      </div>

      <div x-show="!listLoading">
        <template x-for="group in filteredGroups" :key="group.id">
          <div>
            <div class="coa-type-group" @click="toggleCollapse(group.id)">
              <span class="coa-type-badge" :class="'tb-'+group.type" x-text="group.name"></span>
              <svg :style="`width:13px;height:13px;color:#94a3b8;flex-shrink:0;transition:transform .2s;${collapsed[group.id]?'':'transform:rotate(90deg)'}`"
                   fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path d="M9 18l6-6-6-6"/>
              </svg>
            </div>
            <div x-show="!collapsed[group.id]">
              <template x-for="acc in group.accounts" :key="acc.id">
                <div class="coa-acc-row" :class="selected?.id===acc.id?'active':''"
                     @click="selectAccount(acc)">
                  <div style="display:flex;align-items:center;min-width:0;flex:1">
                    <span class="coa-acc-dot"></span>
                    <span class="coa-acc-name" x-text="acc.name"></span>
                    <span x-show="acc.branch_name" x-text="acc.branch_name"
                          style="margin-left:6px;padding:1px 6px;border-radius:6px;font-size:10px;font-weight:600;color:#64748b;background:#f1f5f9;flex-shrink:0;white-space:nowrap"></span>
                  </div>
                  <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;margin-left:6px">
                    <span class="coa-acc-bal"
                          x-text="fmtCompact(acc.balance)+' '+(acc.normal_balance==='debit'?'Dr':'Cr')"></span>
                    <button class="coa-plus-btn" @click.stop="openCreateModal(acc)" title="Add Sub-account">
                      <svg style="width:11px;height:11px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
                    </button>
                  </div>
                </div>
              </template>
              <template x-if="group.accounts.length===0">
                <div style="padding:8px 18px;font-size:12px;color:#94a3b8;font-style:italic">No accounts</div>
              </template>
            </div>
          </div>
        </template>
        <template x-if="!listLoading&&filteredGroups.length===0">
          <div style="padding:40px 14px;text-align:center;font-size:13px;color:#94a3b8">No accounts found.</div>
        </template>
      </div>
    </div>

    
    <div class="coa-main">

      
      <div x-show="!selected" class="coa-empty">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0120 9.414V19a2 2 0 01-2 2z"/>
        </svg>
        <h5>Select an Account</h5>
        <p>Click any account in the left panel to view its details and full transaction ledger.</p>
      </div>

      <template x-if="selected">
        <div style="display:flex;flex-direction:column;flex:1;min-height:0">

          
          <div class="coa-acc-header">
            <div style="min-width:0">
              <h4 x-text="selected.name"></h4>
              <div class="coa-acc-meta">
                <span class="coa-meta-chip">Code: <span x-text="selected.code"></span></span>
                <span class="coa-meta-chip capitalize" x-text="selected.type"></span>
                <span class="coa-meta-chip" x-text="selected.group||'Head Account'"></span>
                <span class="coa-meta-chip" x-show="selected.branch_name" x-text="selected.branch_name"></span>
              </div>
            </div>
            <div class="coa-hdr-actions">
              <button class="coa-hdr-btn" @click="openCreateModal(selected)">
                <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
                Sub-account
              </button>
              <button class="coa-hdr-btn" @click="openEditModal(selected)">
                <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
              </button>
              <button class="coa-hdr-btn-del" @click="confirmDelete(selected)">
                <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </div>
          </div>

          
          <div class="coa-duration-bar">
            <span class="coa-dur-label">Duration</span>
            <template x-for="d in durations" :key="d.value">
              <button class="coa-dur-btn" :class="duration===d.value?'active':''"
                      @click="setDuration(d.value)" x-text="d.label"></button>
            </template>
            <template x-if="duration!=='all'&&duration!=='custom'">
              <div style="display:flex;align-items:center;gap:6px">
                <button class="coa-nav-btn" @click="navigatePeriod('prev')">&#8249;</button>
                <span class="coa-period-lbl" x-text="periodLabel"></span>
                <button class="coa-nav-btn" @click="navigatePeriod('next')">&#8250;</button>
              </div>
            </template>
            <template x-if="duration==='custom'">
              <div style="display:flex;align-items:center;gap:6px">
                <input type="date" x-model="fromDate" @change="loadLedger()" class="coa-custom-date">
                <span style="font-size:12px;color:#64748b">to</span>
                <input type="date" x-model="toDate" @change="loadLedger()" class="coa-custom-date">
              </div>
            </template>
            <button x-show="duration!=='all'" class="coa-clear-btn" @click="setDuration('all')">
              <svg style="width:11px;height:11px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M6 18L18 6M6 6l12 12"/></svg>
              Clear All Filters
            </button>
          </div>

          
          <div class="coa-balance-row">
            <div class="coa-bal-card">
              <div class="coa-bal-lbl">Opening Balance</div>
              <div class="coa-bal-val" :class="balClass(ledger?.opening_balance??0,selected)">
                <span style="font-size:14px;opacity:.6;font-weight:600">Rs</span>
                <span x-text="fmtAmount(Math.abs(ledger?.opening_balance??0))"></span>
                <span :class="drCrBadge(ledger?.opening_balance??0,selected)"
                      x-text="drCrLabel(ledger?.opening_balance??0,selected)"></span>
              </div>
            </div>
            <div class="coa-bal-card">
              <div class="coa-bal-lbl">Closing Balance</div>
              <div class="coa-bal-val" :class="balClass(ledger?.closing_balance??0,selected)">
                <span style="font-size:14px;opacity:.6;font-weight:600">Rs</span>
                <span x-text="fmtAmount(Math.abs(ledger?.closing_balance??0))"></span>
                <span :class="drCrBadge(ledger?.closing_balance??0,selected)"
                      x-text="drCrLabel(ledger?.closing_balance??0,selected)"></span>
              </div>
            </div>
          </div>

          
          <div class="coa-section">
            <div class="coa-sec-title">Transaction Ledger</div>

            <div x-show="ledgerLoading" style="text-align:center;padding:30px">
              <svg class="animate-spin" style="width:24px;height:24px;color:#3b82f6;display:inline-block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            </div>

            <div x-show="!ledgerLoading&&(ledger?.entries??[]).length>0" class="coa-ledger-wrap">
              <table class="coa-table">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Particular / Description</th>
                    <th style="text-align:right">Dr</th>
                    <th style="text-align:right">Cr</th>
                    <th style="text-align:right">Balance</th>
                    <th style="text-align:center">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <template x-for="(entry,idx) in (ledger?.entries??[])" :key="idx">
                    <tr>
                      <td style="color:#64748b;font-size:12px" x-text="fmtDateLong(entry.date)"></td>
                      <td>
                        <div style="font-weight:500" x-text="entry.description"></div>
                        <div style="font-size:11px;color:#94a3b8;font-family:monospace" x-text="entry.entry_number"></div>
                      </td>
                      <td style="text-align:right;font-weight:700;color:#10b981"
                          x-text="entry.debit>0?fmtAmount(entry.debit):'—'"></td>
                      <td style="text-align:right;font-weight:700;color:#ef4444"
                          x-text="entry.credit>0?fmtAmount(entry.credit):'—'"></td>
                      <td style="text-align:right">
                        <span :style="'font-size:14px;font-weight:800;color:'+(entry.balance<0?'#ef4444':'#2563eb')"
                              x-text="fmtAmount(Math.abs(entry.balance))"></span>
                        <span style="font-size:10px;opacity:.7;margin-left:3px"
                              x-text="drCrLabel(entry.balance, selected)"></span>
                      </td>
                      <td style="text-align:center">
                        <button @click="deleteJournalEntry(entry)"
                                style="background:none;border:none;cursor:pointer;color:#cbd5e1;padding:4px;transition:color .15s"
                                onmouseover="this.style.color='#ef4444'" onmouseout="this.style.color='#cbd5e1'">
                          <svg style="width:14px;height:14px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                      </td>
                    </tr>
                  </template>
                </tbody>
                <tfoot>
                  <tr>
                    <td colspan="2" style="text-align:right;text-transform:uppercase;font-size:11px;letter-spacing:.05em;color:#64748b">Period Total</td>
                    <td style="text-align:right;color:#10b981;font-size:14px" x-text="fmtAmount(periodDebit)"></td>
                    <td style="text-align:right;color:#ef4444;font-size:14px" x-text="fmtAmount(periodCredit)"></td>
                    <td :style="'text-align:right;font-size:15px;background:#eff6ff;color:'+((ledger?.closing_balance??0)<0?'#ef4444':'#2563eb')">
                      <span x-text="fmtAmount(Math.abs(ledger?.closing_balance??0))"></span>
                      <span style="font-size:10px;opacity:.7;margin-left:3px"
                            x-text="drCrLabel(ledger?.closing_balance??0, selected)"></span>
                    </td>
                    <td></td>
                  </tr>
                </tfoot>
              </table>
            </div>

            <div x-show="!ledgerLoading&&(ledger?.entries??[]).length===0&&ledger"
                 style="text-align:center;padding:40px 20px;background:#fff;border-radius:10px;border:1px solid #e2e8f0;color:#94a3b8">
              <svg style="width:40px;height:40px;opacity:.4;margin:0 auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0120 9.414V19a2 2 0 01-2 2z"/>
              </svg>
              <p style="font-weight:600;color:#475569;margin-top:10px">No transactions available</p>
              <p style="font-size:13px;margin-top:4px">Post journal entries against this account to see its ledger.</p>
            </div>
          </div>

        </div>
      </template>
    </div>

  </div>


<div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
  <div class="absolute inset-0" style="background:rgba(15,23,42,.65);backdrop-filter:blur(4px)" @click="showModal=false"></div>
  <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md p-6 z-10 max-h-[90vh] overflow-y-auto">
    <div class="flex items-start justify-between mb-4">
      <div>
        <h5 class="text-lg font-bold text-gray-800 dark:text-gray-100"
            x-text="editMode?'Edit Account':(modalParent?'Add Sub-Account':'Add Head Account')"></h5>
        <p class="text-sm text-gray-400 mt-0.5"
           x-text="modalParent&&!editMode?'Under: '+modalParent.name:'Top-level account'"></p>
      </div>
      <button @click="showModal=false" class="text-gray-400 hover:text-gray-600 ml-4">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="space-y-4">
      <template x-if="!editMode">
        <div>
          <label class="label text-xs">Account Category <span class="text-red-500">*</span></label>
          <select x-model="form.group_id" @change="onGroupChange()" class="input">
            <option value="">— Select Category —</option>
            <template x-for="g in groups" :key="g.id">
              <option :value="g.id" x-text="g.name"></option>
            </template>
          </select>
        </div>
      </template>
      <template x-if="!editMode">
        <div>
          <label class="label text-xs">Branch <span class="text-red-500">*</span></label>
          <select x-model="form.branch_id" class="input">
            <option value="">— Select Branch —</option>
            <template x-for="b in branches" :key="b.id">
              <option :value="b.id" x-text="b.name"></option>
            </template>
          </select>
        </div>
      </template>
      <div>
        <label class="label text-xs">Account Name <span class="text-red-500">*</span></label>
        <input type="text" x-model="form.name" class="input" placeholder="e.g. Electricity" />
      </div>
      <div>
        <label class="label text-xs">Opening Balance</label>
        <div class="relative">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-gray-400 font-medium">Rs</span>
          <input type="number" x-model="form.opening_balance" step="0.01" class="input pl-9" placeholder="0.00" />
        </div>
      </div>
      <div x-show="modalError" class="text-sm text-red-600 bg-red-50 dark:bg-red-900/20 rounded-lg p-3" x-text="modalError"></div>
    </div>
    <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100 dark:border-gray-700/40">
      <button @click="showModal=false" class="btn-secondary">Cancel</button>
      <button @click="submitModal()" class="btn-primary" :disabled="modalSaving">
        <span x-show="modalSaving">Saving…</span>
        <span x-show="!modalSaving" x-text="editMode?'Save Changes':'Create Account'"></span>
      </button>
    </div>
  </div>
</div>


<div x-show="showDeleteConfirm" class="fixed inset-0 z-50 flex items-center justify-center p-4"
     x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
  <div class="absolute inset-0" style="background:rgba(15,23,42,.65)" @click="showDeleteConfirm=false"></div>
  <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10">
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
      </div>
      <div>
        <p class="font-bold text-gray-800 dark:text-gray-100">Delete Account?</p>
        <p class="text-sm text-gray-500" x-text="'Permanently delete &quot;'+(deleteTarget?.name??'')+' &quot;'"></p>
      </div>
    </div>
    <p class="text-sm text-amber-700 bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 mb-5">
      Accounts with existing journal entries cannot be deleted.
    </p>
    <div class="flex justify-end gap-3">
      <button @click="showDeleteConfirm=false" class="btn-secondary">Cancel</button>
      <button @click="doDelete()" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg text-sm transition-colors">Delete</button>
    </div>
  </div>
</div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function chartOfAccounts() {
  const today = new Date().toISOString().slice(0,10);
  const params = new URLSearchParams(window.location.search);
  return {
    accounts:[], groups:[], filteredGroups:[], obCheck:null, branches:[], defaultBranchId:'',
    selected:null, ledger:null,
    listLoading:true, ledgerLoading:false,
    search:'', filterGroup:'',
    duration:'all',
    fromDate:new Date(new Date().getFullYear(),new Date().getMonth(),1).toISOString().slice(0,10),
    toDate:today,
    _cur:new Date(), collapsed:{},
    durations:[
      {value:'all',label:'All Time'},{value:'monthly',label:'Monthly'},
      {value:'quarterly',label:'Quarterly'},{value:'yearly',label:'Yearly'},
      {value:'custom',label:'Custom Range'},
    ],
    showModal:false, editMode:false, modalParent:null, modalSaving:false, modalError:'',
    showDeleteConfirm:false, deleteTarget:null,
    form:{code:'',name:'',group_id:'',type:'expense',normal_balance:'debit',opening_balance:0,is_cash_account:false,is_bank_account:false,branch_id:''},
    typeNormalBalance:{asset:'debit',expense:'debit',income:'credit',liability:'credit',equity:'credit'},

    get periodDebit()  { return (this.ledger?.entries??[]).reduce((s,e)=>s+(e.debit??0),0); },
    get periodCredit() { return (this.ledger?.entries??[]).reduce((s,e)=>s+(e.credit??0),0); },
    get periodLabel() {
      const d=this._cur;
      if(this.duration==='monthly')   return d.toLocaleDateString('en-US',{month:'short',year:'numeric'});
      if(this.duration==='quarterly') return 'Q'+(Math.floor(d.getMonth()/3)+1)+' '+d.getFullYear();
      if(this.duration==='yearly')    return String(d.getFullYear());
      return '';
    },
    fmtAmount(n){ return Number(n??0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); },
    fmtCompact(n){ const v=Math.abs(Number(n??0)); if(v>=1e6)return(v/1e6).toFixed(1)+'M'; if(v>=1000)return(v/1000).toFixed(1)+'K'; return v.toFixed(2); },
    fmtDateLong(d){ if(!d)return''; return new Date(d).toLocaleDateString('en-US',{day:'2-digit',weekday:'short',month:'short',year:'numeric'}); },
    balClass(val,acc){ if(!acc)return'debit'; return val>=0?(acc.normal_balance==='debit'?'debit':'credit'):'danger'; },
    drCrLabel(val,acc){ if(!acc)return val>=0?'Dr':'Cr'; return acc.normal_balance==='debit'?(val>=0?'Dr':'Cr'):(val>=0?'Cr':'Dr'); },
    drCrBadge(val,acc){ return this.drCrLabel(val,acc)==='Dr'?'coa-badge-dr':'coa-badge-cr'; },

    async init(){
      const [accR,grpR,brR]=await Promise.all([apiFetch('/accounting/accounts'),apiFetch('/accounting/groups'),apiFetch('/branches')]);
      this.accounts=await accR.json();
      this.groups=(await grpR.json()).map(g=>({...g,accounts:this.accounts.filter(a=>a.group_id==g.id)}));
      const bd=await brR.json();
      this.branches=bd.data??bd??[];
      try{
        const u=JSON.parse(localStorage.getItem('medri_user')||'{}');
        const stored=localStorage.getItem('medri_branch');
        this.defaultBranchId=(stored&&stored!=='all')?stored:(u.default_branch_id??'');
      }catch(_){}
      this.buildFiltered(); this.listLoading=false;
      const id=params.get('account_id');
      if(id){ const a=this.accounts.find(a=>a.id==id); if(a)await this.selectAccount(a); }
      this.loadObCheck();
    },
    async loadObCheck(){
      try{ const r=await apiFetch('/accounting/accounts/opening-balance-check'); this.obCheck=await r.json(); }
      catch(e){ /* non-critical indicator, ignore failures */ }
    },
    buildFiltered(){
      // Always re-derive each group's account list from the live `accounts`
      // array rather than a stale per-group snapshot — otherwise a delete/
      // create/edit updates `accounts` but the sidebar keeps showing the old
      // list until a full page reload.
      let gs=this.groups.map(g=>({...g,accounts:this.accounts.filter(a=>a.group_id==g.id)}));
      if(this.filterGroup) gs=gs.filter(g=>g.id==this.filterGroup);
      if(this.search.trim()){ const q=this.search.toLowerCase(); gs=gs.map(g=>({...g,accounts:g.accounts.filter(a=>a.name.toLowerCase().includes(q)||a.code.toLowerCase().includes(q))})).filter(g=>g.accounts.length>0); }
      else gs=gs.filter(g=>g.accounts.length>0);
      this.filteredGroups=gs;
    },
    toggleCollapse(id){ this.collapsed[id]=!this.collapsed[id]; },
    async selectAccount(acc){ this.selected=acc; this.ledger=null; await this.loadLedger(); },
    async loadLedger(){
      if(!this.selected)return;
      this.ledgerLoading=true;
      const [from,to]=this.getDateRange();
      try{ const r=await apiFetch(`/accounting/general-ledger?account_id=${this.selected.id}&from_date=${from}&to_date=${to}`); this.ledger=await r.json(); }
      catch(e){ toast(e.message??'Failed to load ledger','error'); }
      finally{ this.ledgerLoading=false; }
    },
    getDateRange(){
      const t=new Date().toISOString().slice(0,10),d=this._cur;
      if(this.duration==='all')return['2000-01-01',t];
      if(this.duration==='monthly')return[new Date(d.getFullYear(),d.getMonth(),1).toISOString().slice(0,10),new Date(d.getFullYear(),d.getMonth()+1,0).toISOString().slice(0,10)];
      if(this.duration==='quarterly'){const q=Math.floor(d.getMonth()/3);return[new Date(d.getFullYear(),q*3,1).toISOString().slice(0,10),new Date(d.getFullYear(),q*3+3,0).toISOString().slice(0,10)];}
      if(this.duration==='yearly')return[new Date(d.getFullYear(),0,1).toISOString().slice(0,10),new Date(d.getFullYear(),11,31).toISOString().slice(0,10)];
      return[this.fromDate,this.toDate];
    },
    setDuration(d){ this.duration=d; this._cur=new Date(); if(d!=='custom')this.loadLedger(); },
    navigatePeriod(dir){
      const d=new Date(this._cur);
      if(this.duration==='monthly')   dir==='next'?d.setMonth(d.getMonth()+1):d.setMonth(d.getMonth()-1);
      if(this.duration==='quarterly') dir==='next'?d.setMonth(d.getMonth()+3):d.setMonth(d.getMonth()-3);
      if(this.duration==='yearly')    dir==='next'?d.setFullYear(d.getFullYear()+1):d.setFullYear(d.getFullYear()-1);
      this._cur=d; this.loadLedger();
    },
    onGroupChange(){
      const g=this.groups.find(g=>g.id==this.form.group_id);
      if(g){ this.form.type=g.type; this.form.normal_balance=this.typeNormalBalance[g.type]??'debit'; }
    },
    openCreateModal(parent){
      this.editMode=false; this.modalParent=parent; this.modalError='';
      const grpId=parent?.group_id??'';
      const g=this.groups.find(g=>g.id==grpId);
      this.form={code:'',name:'',group_id:grpId,type:g?.type??'expense',normal_balance:this.typeNormalBalance[g?.type??'expense']??'debit',opening_balance:0,is_cash_account:false,is_bank_account:false,branch_id:parent?.branch_id??this.defaultBranchId??''};
      this.showModal=true;
    },
    openEditModal(acc){
      this.editMode=true; this.modalParent=null; this.modalError='';
      this.form={code:acc.code,name:acc.name,group_id:acc.group_id,type:acc.type,normal_balance:acc.normal_balance,opening_balance:acc.opening_balance,is_cash_account:acc.is_cash_account,is_bank_account:acc.is_bank_account};
      this.showModal=true;
    },
    async submitModal(){
      if(!this.form.name){this.modalError='Account name is required.';return;}
      if(!this.editMode&&!this.form.group_id){this.modalError='Please select a category.';return;}
      if(!this.editMode&&!this.form.branch_id){this.modalError='Please select a branch.';return;}
      this.modalSaving=true; this.modalError='';
      try{
        if(!this.editMode&&!this.form.code) this.form.code='ACC'+Date.now().toString().slice(-5);
        const r=this.editMode
          ?await apiFetch(`/accounting/accounts/${this.selected.id}`,{method:'PUT',body:JSON.stringify(this.form)})
          :await apiFetch('/accounting/accounts',{method:'POST',body:JSON.stringify(this.form)});
        if(!r.ok){const e=await r.json();this.modalError=e.message??Object.values(e.errors??{})[0]?.[0]??'Error saving.';return;}
        const saved=await r.json();
        if(this.editMode){
          const i=this.accounts.findIndex(a=>a.id===saved.id);
          if(i>=0){this.accounts[i]={...this.accounts[i],...saved,group:saved.group?.name??this.accounts[i].group};this.selected=this.accounts[i];}
        } else {
          this.accounts.push({...saved,group:saved.group?.name??'',balance:Number(saved.opening_balance??0)});
        }
        this.buildFiltered(); this.showModal=false;
        this.loadObCheck();
        toast(this.editMode?'Account updated.':'Account created.','success');
      }finally{this.modalSaving=false;}
    },
    async deleteJournalEntry(entry) {
      if (!entry.journal_entry_id) { toast('Cannot identify journal entry.', 'error'); return; }
      if (!confirm('Delete this journal entry (' + entry.entry_number + ') permanently?')) return;
      const r = await apiFetch('/accounting/journal-entries/' + entry.journal_entry_id, { method: 'DELETE' });
      if (r.ok) {
        if (this.ledger) this.ledger.entries = this.ledger.entries.filter(e => e.journal_entry_id !== entry.journal_entry_id);
        if (typeof toast === 'function') toast('Journal entry deleted.', 'success');
      } else {
        const err = await r.json().catch(() => ({}));
        toast(err.message || 'Failed to delete.', 'error');
      }
    },
    confirmDelete(acc){this.deleteTarget=acc;this.showDeleteConfirm=true;},
    async doDelete(){
      if(!this.deleteTarget)return;
      const r=await apiFetch(`/accounting/accounts/${this.deleteTarget.id}`,{method:'DELETE'});
      if(r.ok){
        this.accounts=this.accounts.filter(a=>a.id!==this.deleteTarget.id);
        this.buildFiltered();
        if(this.selected?.id===this.deleteTarget.id){this.selected=null;this.ledger=null;}
        this.loadObCheck();
        toast('Account deleted.','success');
      }else{const e=await r.json();toast(e.message??'Cannot delete this account.','error');}
      this.showDeleteConfirm=false;this.deleteTarget=null;
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/accounting/chart-of-accounts.blade.php ENDPATH**/ ?>