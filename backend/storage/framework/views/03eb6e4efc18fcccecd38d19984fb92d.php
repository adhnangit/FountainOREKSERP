<?php $__env->startSection('title', 'Invoices'); ?>
<?php $__env->startSection('page-title', 'Invoices'); ?>
<?php $__env->startSection('page-desc', 'Manage and track all customer invoices'); ?>

<?php $__env->startSection('content'); ?>
<style>
/* ── Stats Cards ── */
.inv-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
.inv-stat-card{background:#fff;border-radius:14px;padding:18px 20px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:14px;transition:box-shadow .2s,transform .2s;cursor:default}
.inv-stat-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08);transform:translateY(-2px)}
.inv-stat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.inv-stat-icon svg{width:22px;height:22px}
.inv-stat-val{font-size:22px;font-weight:800;line-height:1.1;letter-spacing:-.5px}
.inv-stat-lbl{font-size:11.5px;color:#94a3b8;font-weight:500;margin-top:2px}
.inv-stat-sub{font-size:11px;font-weight:600;margin-top:4px;padding:2px 8px;border-radius:20px;display:inline-block}

/* ── Toolbar ── */
.inv-toolbar{background:#fff;border-radius:14px;padding:14px 18px;border:1px solid #e2e8f0;margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.inv-search-wrap{position:relative;flex:1;min-width:200px;max-width:340px}
.inv-search-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none}
.inv-search-wrap input{width:100%;border:1px solid #e2e8f0;border-radius:9px;padding:7px 12px 7px 34px;font-size:13px;color:#1e293b;background:#f8fafc;outline:none;transition:border-color .15s,box-shadow .15s}
.inv-search-wrap input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);background:#fff}
.inv-date-input{border:1px solid #e2e8f0;border-radius:9px;padding:7px 10px;font-size:12.5px;color:#334155;background:#f8fafc;outline:none}
.inv-date-input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}

/* ── Status Tabs ── */
.inv-tabs{display:flex;gap:4px;background:#f1f5f9;border-radius:10px;padding:3px}
.inv-tab{padding:5px 14px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;border:none;background:transparent;color:#64748b;transition:all .15s;white-space:nowrap;display:flex;align-items:center;gap:5px}
.inv-tab:hover{background:#e2e8f0;color:#334155}
.inv-tab.active{background:#fff;color:#1e293b;box-shadow:0 1px 4px rgba(0,0,0,.1)}
.inv-tab-count{font-size:10px;font-weight:700;background:#e2e8f0;color:#475569;border-radius:20px;padding:0 6px;line-height:18px}
.inv-tab.active .inv-tab-count{background:#6366f1;color:#fff}

/* ── Table Card ── */
.inv-table-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden}
.inv-table{width:100%;border-collapse:separate;border-spacing:0}
.inv-table thead th{padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e2e8f0;white-space:nowrap}
.inv-table thead th:first-child{padding-left:20px}
.inv-table tbody tr{transition:background .1s}
.inv-table tbody tr:hover{background:#f8faff}
.inv-table tbody td{padding:13px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.inv-table tbody td:first-child{padding-left:20px}
.inv-table tbody tr:last-child td{border-bottom:none}

/* ── Customer Avatar ── */
.cust-avatar{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;color:#fff}
.inv-num{font-size:13px;font-weight:700;color:#1e293b;letter-spacing:.01em}
.inv-type-chip{font-size:10px;font-weight:600;padding:1px 7px;border-radius:4px;background:#ede9fe;color:#7c3aed;margin-top:3px;display:inline-block}
.inv-cust-name{font-size:13px;font-weight:600;color:#1e293b}
.inv-cust-sub{font-size:11px;color:#94a3b8;margin-top:1px}
.inv-date-val{font-size:13px;color:#334155;font-weight:500}
.inv-due-warn{font-size:11px;color:#ef4444;font-weight:600;display:flex;align-items:center;gap:3px;margin-top:2px}
.inv-amount{font-size:14px;font-weight:800;color:#0f172a;letter-spacing:-.3px}
.inv-amount-sub{font-size:11px;color:#94a3b8;margin-top:2px}
.inv-paid-bar{height:3px;border-radius:2px;background:#e2e8f0;margin-top:5px;overflow:hidden}
.inv-paid-fill{height:100%;border-radius:2px;background:#10b981;transition:width .4s}

/* ── Status Badges ── */
.s-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;text-transform:capitalize;white-space:nowrap}
.s-badge::before{content:'';width:6px;height:6px;border-radius:50%;flex-shrink:0}
.s-draft{background:#f1f5f9;color:#64748b}.s-draft::before{background:#94a3b8}
.s-pending{background:#fffbeb;color:#b45309}.s-pending::before{background:#f59e0b}
.s-partial{background:#fff7ed;color:#c2410c}.s-partial::before{background:#f97316}
.s-paid{background:#f0fdf4;color:#15803d}.s-paid::before{background:#22c55e}
.s-overdue{background:#fef2f2;color:#b91c1c}.s-overdue::before{background:#ef4444}
.s-cancelled{background:#f8fafc;color:#94a3b8}.s-cancelled::before{background:#cbd5e1}
.s-proforma{background:#ede9fe;color:#7c3aed}.s-proforma::before{background:#8b5cf6}

/* ── Action Buttons ── */
.inv-action-btn{width:30px;height:30px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .15s;color:#64748b;text-decoration:none}
.inv-action-btn:hover{background:#f1f5f9;border-color:#c7d2fe;color:#4f46e5}
.inv-action-btn svg{width:14px;height:14px}

/* ── Pagination ── */
.inv-pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid #f1f5f9}
.inv-page-info{font-size:12.5px;color:#94a3b8}
.inv-page-btns{display:flex;gap:4px}
.inv-page-btn{min-width:30px;height:30px;padding:0 6px;border-radius:7px;border:1px solid #e2e8f0;background:#fff;font-size:12px;font-weight:600;color:#475569;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s}
.inv-page-btn:hover:not(:disabled):not(.active){background:#f8fafc}
.inv-page-btn.active{background:#6366f1;color:#fff;border-color:#6366f1}
.inv-page-btn:disabled{opacity:.35;cursor:default}

/* ── Empty State ── */
.inv-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:64px 24px;text-align:center}
.inv-empty svg{width:56px;height:56px;color:#e2e8f0}
.inv-empty h5{font-size:16px;font-weight:700;color:#475569;margin-top:14px}
.inv-empty p{font-size:13px;color:#94a3b8;margin-top:4px}

/* ── Dark Mode ── */
.dark .inv-stat-card{background:#1e293b;border-color:#334155}
.dark .inv-stat-lbl{color:#64748b}
.dark .inv-toolbar{background:#1e293b;border-color:#334155}
.dark .inv-search-wrap input{background:#0f172a;border-color:#334155;color:#e2e8f0}
.dark .inv-search-wrap input:focus{background:#1e293b}
.dark .inv-date-input{background:#0f172a;border-color:#334155;color:#cbd5e1}
.dark .inv-tabs{background:#0f172a}
.dark .inv-tab:hover{background:#1e293b}
.dark .inv-tab.active{background:#1e293b}
.dark .inv-table-card{background:#1e293b;border-color:#334155}
.dark .inv-table thead th{background:#0f172a;border-color:#334155}
.dark .inv-table tbody tr:hover{background:#1e3351}
.dark .inv-table tbody td{border-color:#1e293b}
.dark .inv-num{color:#e2e8f0}
.dark .inv-cust-name{color:#e2e8f0}
.dark .inv-date-val{color:#cbd5e1}
.dark .inv-amount{color:#f1f5f9}
.dark .inv-action-btn{background:#1e293b;border-color:#334155;color:#94a3b8}
.dark .inv-action-btn:hover{background:#253347;border-color:#6366f1}
.dark .inv-pagination{border-color:#334155}
.dark .inv-page-btn{background:#1e293b;border-color:#334155;color:#94a3b8}
.dark .inv-empty svg{color:#334155}
.dark .inv-empty h5{color:#94a3b8}
</style>

<div x-data="invoiceList()" x-init="init()">

  
  <div class="inv-stats">
    <div class="inv-stat-card" @click="setTab('')" style="cursor:pointer">
      <div class="inv-stat-icon" style="background:#ede9fe">
        <svg fill="none" viewBox="0 0 24 24" stroke="#7c3aed" stroke-width="1.8"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
      </div>
      <div>
        <div class="inv-stat-val" style="color:#7c3aed" x-text="stats.total_count??0"></div>
        <div class="inv-stat-lbl">Total Invoices</div>
        <div class="inv-stat-sub" style="background:#ede9fe;color:#7c3aed" x-text="'Rs '+fmtCompact(stats.total_amount??0)"></div>
      </div>
    </div>
    <div class="inv-stat-card" @click="setTab('paid')" style="cursor:pointer">
      <div class="inv-stat-icon" style="background:#dcfce7">
        <svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="1.8"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <div class="inv-stat-val" style="color:#16a34a" x-text="stats.paid_count??0"></div>
        <div class="inv-stat-lbl">Paid</div>
        <div class="inv-stat-sub" style="background:#dcfce7;color:#16a34a" x-text="'Rs '+fmtCompact(stats.paid_amount??0)"></div>
      </div>
    </div>
    <div class="inv-stat-card" @click="setTab('pending')" style="cursor:pointer">
      <div class="inv-stat-icon" style="background:#fef9c3">
        <svg fill="none" viewBox="0 0 24 24" stroke="#b45309" stroke-width="1.8"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <div class="inv-stat-val" style="color:#b45309" x-text="stats.pending_count??0"></div>
        <div class="inv-stat-lbl">Pending</div>
        <div class="inv-stat-sub" style="background:#fef9c3;color:#b45309" x-text="'Rs '+fmtCompact(stats.outstanding??0)+' due'"></div>
      </div>
    </div>
    <div class="inv-stat-card" @click="setTab('overdue')" style="cursor:pointer">
      <div class="inv-stat-icon" style="background:#fee2e2">
        <svg fill="none" viewBox="0 0 24 24" stroke="#b91c1c" stroke-width="1.8"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
      </div>
      <div>
        <div class="inv-stat-val" style="color:#b91c1c" x-text="stats.overdue_count??0"></div>
        <div class="inv-stat-lbl">Overdue</div>
        <div class="inv-stat-sub" style="background:#fee2e2;color:#b91c1c">Needs attention</div>
      </div>
    </div>
  </div>

  
  <div class="inv-toolbar">
    <div class="inv-search-wrap">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" x-model="search" @input.debounce.400ms="page=1;load()" placeholder="Search invoice # or customer…">
    </div>
    <input type="date" x-model="fromDate" @change="page=1;load()" class="inv-date-input" title="From date">
    <input type="date" x-model="toDate" @change="page=1;load()" class="inv-date-input" title="To date">
    <div class="inv-tabs">
      <button class="inv-tab" :class="activeTab===''?'active':''" @click="setTab('')">
        All <span class="inv-tab-count" x-text="stats.total_count??0"></span>
      </button>
      <button class="inv-tab" :class="activeTab==='pending'?'active':''" @click="setTab('pending')">
        Pending <span class="inv-tab-count" x-text="stats.pending_count??0"></span>
      </button>
      <button class="inv-tab" :class="activeTab==='paid'?'active':''" @click="setTab('paid')">
        Paid <span class="inv-tab-count" x-text="stats.paid_count??0"></span>
      </button>
      <button class="inv-tab" :class="activeTab==='overdue'?'active':''" @click="setTab('overdue')">
        Overdue <span class="inv-tab-count" x-text="stats.overdue_count??0"></span>
      </button>
      <button class="inv-tab" :class="activeTab==='draft'?'active':''" @click="setTab('draft')">
        Draft <span class="inv-tab-count" x-text="stats.draft_count??0"></span>
      </button>
    </div>
    <div style="margin-left:auto">
      <a href="<?php echo e(url('/invoices/create')); ?>"
         style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-radius:10px;padding:8px 18px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;text-decoration:none;box-shadow:0 4px 12px rgba(99,102,241,.35);transition:opacity .15s"
         onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
        <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
        New Invoice
      </a>
    </div>
  </div>

  
  <div class="inv-table-card">
    <div class="overflow-x-auto">
      <table class="inv-table">
        <thead>
          <tr>
            <th>Invoice</th>
            <th>Customer</th>
            <th>Invoice Date</th>
            <th>Due Date</th>
            <th style="text-align:right">Amount</th>
            <th style="text-align:center">Status</th>
            <th style="text-align:right">Actions</th>
          </tr>
        </thead>
        <tbody>
          
          <template x-if="loading">
            <tr>
              <td colspan="7" style="text-align:center;padding:56px 24px">
                <div style="display:flex;align-items:center;justify-content:center;gap:8px;color:#94a3b8;font-size:13px">
                  <svg class="animate-spin" style="width:20px;height:20px;color:#6366f1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                  Loading invoices…
                </div>
              </td>
            </tr>
          </template>

          
          <template x-if="!loading && invoices.length === 0">
            <tr>
              <td colspan="7">
                <div class="inv-empty">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.2">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                  </svg>
                  <h5>No invoices found</h5>
                  <p>Try adjusting your search or filter, or create a new invoice.</p>
                </div>
              </td>
            </tr>
          </template>

          
          <template x-for="inv in invoices" :key="inv.id">
            <tr>
              <td>
                <div class="inv-num" x-text="inv.invoice_number || ('#INV-'+inv.id)"></div>
                <div class="inv-type-chip" x-show="inv.type && inv.type!=='invoice'" x-text="inv.type"></div>
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:10px">
                  <div class="cust-avatar" :style="'background:'+avatarColor(inv.customer?.name)">
                    <span x-text="initials(inv.customer?.name)"></span>
                  </div>
                  <div>
                    <div class="inv-cust-name" x-text="inv.customer?.name||'—'"></div>
                    <div class="inv-cust-sub" x-text="inv.customer?.code||''"></div>
                  </div>
                </div>
              </td>
              <td>
                <div class="inv-date-val" x-text="fmtDate(inv.invoice_date)"></div>
              </td>
              <td>
                <div class="inv-date-val" x-text="inv.due_date?fmtDate(inv.due_date):'—'"></div>
                <div class="inv-due-warn" x-show="isOverdue(inv)">
                  <svg style="width:10px;height:10px" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                  Overdue
                </div>
              </td>
              <td style="text-align:right">
                <div class="inv-amount" x-text="'Rs '+fmtMoney(inv.total)"></div>
                <div class="inv-amount-sub" x-show="inv.paid_amount>0&&inv.status!=='paid'"
                     x-text="'Paid: Rs '+fmtMoney(inv.paid_amount)"></div>
                <div class="inv-paid-bar" x-show="inv.total>0&&inv.status!=='draft'">
                  <div class="inv-paid-fill" :style="'width:'+Math.min(100,((inv.paid_amount??0)/(inv.total||1))*100)+'%'"></div>
                </div>
              </td>
              <td style="text-align:center">
                <span class="s-badge" :class="'s-'+inv.status" x-text="inv.status"></span>
              </td>
              <td style="text-align:right">
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:5px">
                  <a :href="BASE+'/invoices/'+inv.id" class="inv-action-btn" title="View">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                  </a>
                  <button class="inv-action-btn" @click="printInvoice(inv)" title="Print">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m0-10V5a2 2 0 012-2h6a2 2 0 012 2v2M7 17h10v4H7v-4z"/></svg>
                  </button>
                  <button class="inv-action-btn" @click="downloadPdf(inv)" title="Download PDF">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0120 9.414V19a2 2 0 01-2 2z"/></svg>
                  </button>
                  <button class="inv-action-btn" @click="deleteInvoice(inv)"
                          style="border-color:#fecaca;color:#ef4444"
                          onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background=''"
                          title="Delete" x-show="!['paid','partially_paid'].includes(inv.status)">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    
    <div class="inv-pagination" x-show="meta.total > 0">
      <div class="inv-page-info"
           x-text="'Showing '+meta.from+'–'+meta.to+' of '+meta.total+' invoices'"></div>
      <div class="inv-page-btns">
        <button class="inv-page-btn" @click="page=1;load()" :disabled="page<=1">
          <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M11 19l-7-7 7-7M18 19l-7-7 7-7"/></svg>
        </button>
        <button class="inv-page-btn" @click="page--;load()" :disabled="page<=1">
          <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M15 19l-7-7 7-7"/></svg>
        </button>
        <template x-for="p in pageNumbers" :key="p">
          <button class="inv-page-btn" :class="p===page?'active':''"
                  @click="page=p;load()" x-text="p"></button>
        </template>
        <button class="inv-page-btn" @click="page++;load()" :disabled="page>=meta.last_page">
          <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 5l7 7-7 7"/></svg>
        </button>
        <button class="inv-page-btn" @click="page=meta.last_page;load()" :disabled="page>=meta.last_page">
          <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M13 5l7 7-7 7M6 5l7 7-7 7"/></svg>
        </button>
      </div>
    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function invoiceList() {
  const COLORS = ['#6366f1','#8b5cf6','#0ea5e9','#10b981','#f59e0b','#ef4444','#ec4899','#14b8a6'];
  return {
    loading: true,
    invoices: [],
    stats: {},
    search: '',
    activeTab: '',
    fromDate: '',
    toDate: '',
    page: 1,
    meta: { total: 0, from: 0, to: 0, last_page: 1 },

    get pageNumbers() {
      const total = this.meta.last_page;
      const cur = this.page;
      if (total <= 7) return Array.from({length: total}, (_, i) => i + 1);
      const pages = new Set([1, total, cur, cur-1, cur+1].filter(p => p >= 1 && p <= total));
      return [...pages].sort((a,b) => a-b);
    },

    async init() {
      await Promise.all([this.loadStats(), this.load()]);
    },
    async loadStats() {
      try {
        const r = await apiFetch('/invoices-stats');
        this.stats = await r.json();
      } catch(e) {}
    },
    async load() {
      this.loading = true;
      try {
        const params = new URLSearchParams({ page: this.page, per_page: 15 });
        if (this.search)     params.set('search', this.search);
        if (this.activeTab)  params.set('status', this.activeTab);
        if (this.fromDate)   params.set('from_date', this.fromDate);
        if (this.toDate)     params.set('to_date', this.toDate);
        const r = await apiFetch('/invoices?' + params);
        const d = await r.json();
        this.invoices = d.data || d || [];
        if (d.meta) this.meta = d.meta;
        else this.meta = { total: d.total ?? this.invoices.length, from: d.from ?? 1, to: d.to ?? this.invoices.length, last_page: d.last_page ?? 1 };
      } catch(e) { toast('Failed to load invoices', 'error'); }
      this.loading = false;
    },
    setTab(tab) { this.activeTab = tab; this.page = 1; this.load(); },
    async deleteInvoice(inv) {
      const label = inv.invoice_number || ('#INV-' + inv.id);
      if (!confirm(`Delete ${label}? This cannot be undone.`)) return;
      try {
        const r = await apiFetch('/invoices/' + inv.id, { method: 'DELETE' });
        if (r.ok) {
          toast('Invoice deleted', 'success');
          await Promise.all([this.loadStats(), this.load()]);
        } else {
          const e = await r.json();
          toast(e.message || 'Failed to delete', 'error');
        }
      } catch(e) { toast('Error deleting invoice', 'error'); }
    },
    async downloadPdf(inv) {
      try {
        const r = await apiFetch('/invoices/' + inv.id + '/pdf');
        const blob = await r.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'invoice-' + (inv.invoice_number || inv.id) + '.pdf';
        document.body.appendChild(a); a.click(); document.body.removeChild(a);
        URL.revokeObjectURL(url);
      } catch(e) { toast(e.message ?? 'PDF download failed', 'error'); }
    },
    async printInvoice(inv) {
      try {
        const r = await apiFetch('/invoices/' + inv.id + '/pdf');
        if (!r.ok) { toast('Failed to generate PDF', 'error'); return; }
        const blob = await r.blob();
        const url = URL.createObjectURL(blob);
        const iframe = document.createElement('iframe');
        iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0';
        iframe.src = url;
        document.body.appendChild(iframe);
        iframe.onload = () => {
          setTimeout(() => {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
          }, 300);
        };
        setTimeout(() => { document.body.removeChild(iframe); URL.revokeObjectURL(url); }, 60000);
      } catch(e) { toast('Print failed', 'error'); }
    },
    isOverdue(inv) {
      if (!inv.due_date || inv.status === 'paid' || inv.status === 'cancelled') return false;
      return new Date(inv.due_date) < new Date();
    },
    initials(name) {
      if (!name) return '?';
      return name.split(' ').slice(0,2).map(w => w[0]).join('').toUpperCase();
    },
    avatarColor(name) {
      if (!name) return COLORS[0];
      let h = 0;
      for (let i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) % COLORS.length;
      return COLORS[Math.abs(h)];
    },
    fmtDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}); },
    fmtMoney(n) { return Number(n??0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); },
    fmtCompact(n) { const v = Math.abs(Number(n??0)); if(v>=1e6) return (v/1e6).toFixed(1)+'M'; if(v>=1e3) return (v/1e3).toFixed(1)+'K'; return v.toFixed(0); },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views/invoices/index.blade.php ENDPATH**/ ?>