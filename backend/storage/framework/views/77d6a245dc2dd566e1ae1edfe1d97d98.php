
<?php $__env->startSection('title', 'Dashboard'); ?>
<?php $__env->startSection('page-title', 'Dashboard'); ?>
<?php $__env->startSection('page-desc', 'Business performance overview'); ?>

<?php $__env->startPush('head'); ?>
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.54.0/dist/apexcharts.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/d3@7/dist/d3.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/topojson-client@3/dist/topojson-client.min.js"></script>
<style>
  .kpi-card { border-radius:12px; overflow:hidden; color:#fff; position:relative; }
  .kpi-card::after  { content:''; position:absolute; right:-10px; top:-10px; width:56px; height:56px; border-radius:50%; background:rgba(255,255,255,0.08); pointer-events:none; }
  .kpi-card::before { content:''; position:absolute; right:22px; bottom:-18px; width:38px; height:38px; border-radius:50%; background:rgba(255,255,255,0.05); pointer-events:none; }
  .kpi-shine { height:2px; background:linear-gradient(90deg,rgba(255,255,255,0.35) 0%,rgba(255,255,255,0.05) 100%); }

  .sect-hd {
    padding:14px 20px;
    display:flex; align-items:center; justify-content:space-between;
    border-radius:12px 12px 0 0;
  }
  .sect-hd-title { font-size:13px; font-weight:700; color:#fff; letter-spacing:0.02em; display:flex; align-items:center; gap:8px; }
  .sect-hd-badge { font-size:11px; background:rgba(255,255,255,0.18); color:#fff; padding:2px 10px; border-radius:999px; font-weight:600; }

  .rep-avatar { width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; color:#fff; flex-shrink:0; }
  .pbar { height:7px; background:#e2e8f0; border-radius:4px; overflow:hidden; }
  .pbar-fill { height:100%; border-radius:4px; transition:width .7s ease; }
  .dark .pbar { background:#2d3748; }

  .aging-tbl th { padding:10px 14px; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; text-align:right; }
  .aging-tbl th:first-child { text-align:left; }
  .aging-tbl td { padding:11px 14px; font-size:13px; text-align:right; font-variant-numeric:tabular-nums; border-bottom:1px solid #f1f5f9; }
  .aging-tbl td:first-child { text-align:left; font-weight:600; }
  .aging-tbl tr:last-child td { border-bottom:none; }
  .dark .aging-tbl td { border-color:#2d3748; }

  .due-tbl th { padding:10px 14px; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; text-align:left; }
  .due-tbl td { padding:10px 14px; font-size:13px; color:#374151; border-bottom:1px solid #f1f5f9; }
  .due-tbl tr:last-child td { border-bottom:none; }
  .due-tbl tr:hover td { background:#f8fafc; }
  .dark .due-tbl td { color:#cbd5e1; border-color:#2d3748; }
  .dark .due-tbl tr:hover td { background:#1a2035; }

  .today-tbl th { padding:10px 16px; font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; }
  .today-tbl td { padding:11px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f1f5f9; }
  .today-tbl tr:last-child td { border-bottom:none; }
  .today-tbl tr:hover td { background:#f8fafc; }
  .dark .today-tbl td { color:#cbd5e1; border-color:#2d3748; }
  .dark .today-tbl tr:hover td { background:#1a2035; }

  .branch-badge { display:inline-flex; align-items:center; padding:1px 8px; border-radius:999px; font-size:10.5px; font-weight:700; background:#eef2ff; color:#1B3EB6; }
  .dark .branch-badge { background:rgba(27,62,182,0.2); color:#a5b4fc; }

  .spin { animation:spin .9s linear infinite; }
  @keyframes spin { to { transform:rotate(360deg); } }
  .chart-empty { display:flex; align-items:center; justify-content:center; height:180px; color:#94a3b8; font-size:13px; flex-direction:column; gap:8px; }

  /* Customer map */
  .map-bubble-pulse, .bubble-pulse { animation: bubble-pulse 2.5s ease-in-out infinite; transform-box: fill-box; transform-origin: center; }
  @keyframes bubble-pulse {
    0%, 100% { opacity: 0.15; transform: scale(1); }
    50%       { opacity: 0.35; transform: scale(1.2); }
  }
  .district-row:hover { background: rgba(255,255,255,0.07) !important; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div x-data="dashboard()" x-init="init()" x-cloak>

  <!-- ─── Loading ─── -->
  <div x-show="loading" class="flex items-center justify-center py-28">
    <div class="text-center">
      <svg class="spin w-10 h-10 mx-auto mb-4" style="color:#1B3EB6" fill="none" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="30 70" stroke-linecap="round"/>
      </svg>
      <p class="text-sm text-gray-400">Loading dashboard…</p>
    </div>
  </div>

  <!-- ─── Main Content ─── -->
  <div x-show="!loading" class="space-y-4">

    <!-- ════════════════════════════════════════
         FILTER BAR
    ════════════════════════════════════════ -->
    <div class="flex items-center justify-between flex-wrap gap-2">

      <!-- Period pills -->
      <div class="flex items-center gap-1 p-1 rounded-xl flex-wrap" style="background:#111827">
        <template x-for="p in periods" :key="p.key">
          <button @click="setPeriod(p.key)"
                  class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all"
                  :style="period === p.key
                    ? 'background:#1B3EB6;color:#fff;box-shadow:0 2px 8px rgba(27,62,182,0.45)'
                    : 'background:transparent;color:#9ca3af'"
                  :class="period !== p.key ? 'hover:text-white hover:bg-white/10' : ''">
            <span x-html="p.icon" class="opacity-80" style="font-size:11px"></span>
            <span x-text="p.label"></span>
          </button>
        </template>

        <!-- Compare toggle -->
        <button @click="compare = !compare; reload()"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all ml-1"
                :style="compare
                  ? 'background:#059669;color:#fff'
                  : 'background:transparent;color:#6b7280;border:1px solid #374151'"
                :class="!compare ? 'hover:text-white hover:bg-white/10' : ''">
          <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path d="M8 7h12M8 12h12M8 17h12M4 7h.01M4 12h.01M4 17h.01"/>
          </svg>
          <span x-text="compare ? 'Compare ON' : 'Compare OFF'"></span>
        </button>
      </div>

      <!-- Custom date range (shown when period = 'custom') -->
      <div x-show="period === 'custom'" x-transition class="flex items-center gap-2">
        <input type="date" x-model="customFrom" @change="reload()"
               class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:outline-none focus:border-primary-500" />
        <span class="text-gray-400 text-xs">→</span>
        <input type="date" x-model="customTo" @change="reload()"
               class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 focus:outline-none focus:border-primary-500" />
      </div>

      <!-- Period label (right side) -->
      <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold" style="background:#111827;color:#9ca3af">
        <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <span x-text="periodLabel" style="color:#d1d5db"></span>
      </div>
    </div>

    <!-- ════════════════════════════════════════
         ROW 1 — Primary KPI Cards (compact horizontal)
    ════════════════════════════════════════ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3" x-show="widgetVisible('kpi_overview')" x-cloak>

      <!-- Customers -->
      <div class="kpi-card" style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
        <div class="flex items-center gap-3 px-4 py-5">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.16)">
            <svg class="w-4.5 h-4.5 text-white" style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[10.5px] font-semibold uppercase tracking-wider leading-none mb-1" style="color:rgba(255,255,255,0.55)">Total Customers</p>
            <p class="text-[22px] font-black leading-none" x-text="(d.kpis?.total_customers || 0).toLocaleString()"></p>
            <template x-if="compare && delta('total_customers') !== null">
              <span class="inline-flex items-center text-[10px] font-bold mt-1 px-1.5 py-0.5 rounded"
                    :style="delta('total_customers') >= 0 ? 'background:rgba(34,168,69,0.3);color:#86efac' : 'background:rgba(239,68,68,0.3);color:#fca5a5'"
                    x-text="(delta('total_customers') >= 0 ? '▲ ' : '▼ ') + Math.abs(delta('total_customers')) + '% vs prev'"></span>
            </template>
          </div>
        </div>
        <div class="kpi-shine"></div>
      </div>

      <!-- Products -->
      <div class="kpi-card" style="background:linear-gradient(135deg,#0891b2,#0e7490)">
        <div class="flex items-center gap-3 px-4 py-5">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.16)">
            <svg style="width:18px;height:18px" class="text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[10.5px] font-semibold uppercase tracking-wider leading-none mb-1" style="color:rgba(255,255,255,0.55)">Total Products</p>
            <p class="text-[22px] font-black leading-none" x-text="(d.kpis?.total_products || 0).toLocaleString()"></p>
          </div>
        </div>
        <div class="kpi-shine"></div>
      </div>

      <!-- Suppliers -->
      <div class="kpi-card" style="background:linear-gradient(135deg,#7c3aed,#5b21b6)">
        <div class="flex items-center gap-3 px-4 py-5">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.16)">
            <svg style="width:18px;height:18px" class="text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/><path d="M3 21h18M9 21V11h6v10"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[10.5px] font-semibold uppercase tracking-wider leading-none mb-1" style="color:rgba(255,255,255,0.55)">Total Suppliers</p>
            <p class="text-[22px] font-black leading-none" x-text="(d.kpis?.total_suppliers || 0).toLocaleString()"></p>
          </div>
        </div>
        <div class="kpi-shine"></div>
      </div>

      <!-- Total Invoices -->
      <div class="kpi-card" style="background:linear-gradient(135deg,#0f766e,#0d6b63)">
        <div class="flex items-center gap-3 px-4 py-5">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.16)">
            <svg style="width:18px;height:18px" class="text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[10.5px] font-semibold uppercase tracking-wider leading-none mb-1" style="color:rgba(255,255,255,0.55)">Total Invoices</p>
            <p class="text-[22px] font-black leading-none" x-text="(d.kpis?.total_invoices || 0).toLocaleString()"></p>
          </div>
        </div>
        <div class="kpi-shine"></div>
      </div>
    </div>

    <!-- ════════════════════════════════════════
         ROW 2 — Financial KPIs (compact with sub-label)
    ════════════════════════════════════════ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3" x-show="widgetVisible('financials')" x-cloak>

      <!-- Month Sales -->
      <div class="kpi-card" style="background:linear-gradient(135deg,#059669,#047857)">
        <div class="flex items-center gap-3 px-4 py-5">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.16)">
            <svg style="width:18px;height:18px" class="text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[10.5px] font-semibold uppercase tracking-wider leading-none mb-0.5" style="color:rgba(255,255,255,0.55)">Period Sales</p>
            <p class="text-[17px] font-black leading-tight truncate" x-text="fmtMoney(d.kpis?.period_sales || 0)"></p>
            <template x-if="compare && delta('period_sales') !== null">
              <span class="inline-flex items-center text-[10px] font-bold px-1.5 py-0.5 rounded"
                    :style="delta('period_sales') >= 0 ? 'background:rgba(34,168,69,0.3);color:#86efac' : 'background:rgba(239,68,68,0.3);color:#fca5a5'"
                    x-text="(delta('period_sales') >= 0 ? '▲ ' : '▼ ') + Math.abs(delta('period_sales')) + '%'"></span>
            </template>
            <template x-if="!compare">
              <p class="text-[9.5px] leading-none mt-0.5" style="color:rgba(255,255,255,0.4)" x-text="'Today: ' + fmtMoney(d.kpis?.today_sales || 0)"></p>
            </template>
          </div>
        </div>
        <div class="kpi-shine"></div>
      </div>

      <!-- Month Purchases -->
      <div class="kpi-card" style="background:linear-gradient(135deg,#d97706,#b45309)">
        <div class="flex items-center gap-3 px-4 py-5">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.16)">
            <svg style="width:18px;height:18px" class="text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[10.5px] font-semibold uppercase tracking-wider leading-none mb-0.5" style="color:rgba(255,255,255,0.55)">Period Purchases</p>
            <p class="text-[17px] font-black leading-tight truncate" x-text="fmtMoney(d.kpis?.period_purchases || 0)"></p>
            <template x-if="compare && delta('period_purchases') !== null">
              <span class="inline-flex items-center text-[10px] font-bold px-1.5 py-0.5 rounded"
                    :style="delta('period_purchases') >= 0 ? 'background:rgba(239,68,68,0.3);color:#fca5a5' : 'background:rgba(34,168,69,0.3);color:#86efac'"
                    x-text="(delta('period_purchases') >= 0 ? '▲ ' : '▼ ') + Math.abs(delta('period_purchases')) + '%'"></span>
            </template>
            <template x-if="!compare">
              <p class="text-[9.5px] leading-none mt-0.5" style="color:rgba(255,255,255,0.4)">COGS / Supplier Invoices</p>
            </template>
          </div>
        </div>
        <div class="kpi-shine"></div>
      </div>

      <!-- Month Expenses -->
      <div class="kpi-card" style="background:linear-gradient(135deg,#dc2626,#b91c1c)">
        <div class="flex items-center gap-3 px-4 py-5">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.16)">
            <svg style="width:18px;height:18px" class="text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[10.5px] font-semibold uppercase tracking-wider leading-none mb-0.5" style="color:rgba(255,255,255,0.55)">Period Expenses</p>
            <p class="text-[17px] font-black leading-tight truncate" x-text="fmtMoney(d.kpis?.period_expenses || 0)"></p>
            <template x-if="compare && delta('period_expenses') !== null">
              <span class="inline-flex items-center text-[10px] font-bold px-1.5 py-0.5 rounded"
                    :style="delta('period_expenses') >= 0 ? 'background:rgba(239,68,68,0.3);color:#fca5a5' : 'background:rgba(34,168,69,0.3);color:#86efac'"
                    x-text="(delta('period_expenses') >= 0 ? '▲ ' : '▼ ') + Math.abs(delta('period_expenses')) + '%'"></span>
            </template>
            <template x-if="!compare">
              <p class="text-[9.5px] leading-none mt-0.5" style="color:rgba(255,255,255,0.4)">Approved expenses only</p>
            </template>
          </div>
        </div>
        <div class="kpi-shine"></div>
      </div>

      <!-- Outstanding -->
      <div class="kpi-card" style="background:linear-gradient(135deg,#475569,#334155)">
        <div class="flex items-center gap-3 px-4 py-5">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.16)">
            <svg style="width:18px;height:18px" class="text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[10.5px] font-semibold uppercase tracking-wider leading-none mb-0.5" style="color:rgba(255,255,255,0.55)">Outstanding Dues</p>
            <p class="text-[17px] font-black leading-tight truncate" x-text="fmtMoney(d.kpis?.outstanding || 0)"></p>
            <template x-if="compare && delta('outstanding') !== null">
              <span class="inline-flex items-center text-[10px] font-bold px-1.5 py-0.5 rounded"
                    :style="delta('outstanding') >= 0 ? 'background:rgba(239,68,68,0.3);color:#fca5a5' : 'background:rgba(34,168,69,0.3);color:#86efac'"
                    x-text="(delta('outstanding') >= 0 ? '▲ ' : '▼ ') + Math.abs(delta('outstanding')) + '%'"></span>
            </template>
            <template x-if="!compare">
              <p class="text-[9.5px] leading-none mt-0.5" style="color:rgba(255,255,255,0.4)">
                <span x-text="d.kpis?.overdue_count || 0"></span> overdue invoices
              </p>
            </template>
          </div>
        </div>
        <div class="kpi-shine"></div>
      </div>
    </div>

    <!-- ════════════════════════════════════════
         DAILY REVENUE & COLLECTIONS TREND
    ════════════════════════════════════════ -->
    <div class="card overflow-hidden" x-show="widgetVisible('revenue_chart')" x-cloak>
      <div class="sect-hd" style="background:linear-gradient(135deg,#0D2272,#1B3EB6)">
        <div class="sect-hd-title">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
          </svg>
          Daily Revenue &amp; Collections
        </div>
        <div class="flex items-center gap-3">
          <span class="inline-flex items-center gap-1.5 text-xs font-semibold" style="color:rgba(255,255,255,0.6)">
            <span class="w-2.5 h-2.5 rounded-full" style="background:#818cf8;display:inline-block"></span>Revenue
          </span>
          <span class="inline-flex items-center gap-1.5 text-xs font-semibold" style="color:rgba(255,255,255,0.6)">
            <span class="w-2.5 h-2.5 rounded-full" style="background:#34d399;display:inline-block"></span>Collected
          </span>
          <span class="sect-hd-badge" x-text="periodLabel"></span>
        </div>
      </div>
      <div class="p-4" style="min-height:280px;position:relative">
        <div id="trendChart" style="min-height:250px"></div>
        <div class="chart-empty" style="position:absolute;inset:0" x-show="!d.daily_revenue?.length">
          <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
          </svg>
          No revenue data for this period
        </div>
      </div>
    </div>

    <!-- ════════════════════════════════════════
         ROW 3 — Cheque Summary KPIs
    ════════════════════════════════════════ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3" x-show="widgetVisible('cheque_summary')" x-cloak>

      <!-- Received Cheques in Hand -->
      <a href="<?php echo e(url('/cheques')); ?>?direction=received&status=in_hand"
         class="kpi-card block" style="background:linear-gradient(135deg,#065f46,#047857)">
        <div class="flex items-center gap-3 px-4 py-4">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.16)">
            <svg style="width:18px;height:18px" class="text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[10px] font-semibold uppercase tracking-wider leading-none mb-0.5" style="color:rgba(255,255,255,0.55)">Cheques in Hand</p>
            <p class="text-[17px] font-black leading-tight truncate" x-text="fmtMoney(d.cheque_stats?.received_in_hand?.total || 0)"></p>
            <p class="text-[9.5px] leading-none mt-0.5" style="color:rgba(255,255,255,0.45)"
               x-text="(d.cheque_stats?.received_in_hand?.count || 0) + ' received cheque(s)'"></p>
          </div>
        </div>
        <div class="kpi-shine"></div>
      </a>

      <!-- Received cheques maturing this month -->
      <a href="<?php echo e(url('/cheques')); ?>" class="kpi-card block" style="background:linear-gradient(135deg,#0891b2,#0e7490)">
        <div class="flex items-center gap-3 px-4 py-4">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.16)">
            <svg style="width:18px;height:18px" class="text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[10px] font-semibold uppercase tracking-wider leading-none mb-0.5" style="color:rgba(255,255,255,0.55)">To Clear This Month</p>
            <p class="text-[17px] font-black leading-tight truncate" x-text="fmtMoney(d.cheque_stats?.received_this_month?.total || 0)"></p>
            <p class="text-[9.5px] leading-none mt-0.5" style="color:rgba(255,255,255,0.45)"
               x-text="(d.cheque_stats?.received_this_month?.count || 0) + ' maturing this month'"></p>
          </div>
        </div>
        <div class="kpi-shine"></div>
      </a>

      <!-- Issued cheques outstanding -->
      <a href="<?php echo e(url('/cheques')); ?>?direction=issued" class="kpi-card block" style="background:linear-gradient(135deg,#b45309,#92400e)">
        <div class="flex items-center gap-3 px-4 py-4">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.16)">
            <svg style="width:18px;height:18px" class="text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[10px] font-semibold uppercase tracking-wider leading-none mb-0.5" style="color:rgba(255,255,255,0.55)">Own Cheques Given</p>
            <p class="text-[17px] font-black leading-tight truncate" x-text="fmtMoney(d.cheque_stats?.issued_active?.total || 0)"></p>
            <p class="text-[9.5px] leading-none mt-0.5" style="color:rgba(255,255,255,0.45)"
               x-text="(d.cheque_stats?.issued_active?.count || 0) + ' outstanding cheque(s)'"></p>
          </div>
        </div>
        <div class="kpi-shine"></div>
      </a>

      <!-- Issued cheques this month -->
      <a href="<?php echo e(url('/cheques')); ?>?direction=issued" class="kpi-card block" style="background:linear-gradient(135deg,#7c3aed,#5b21b6)">
        <div class="flex items-center gap-3 px-4 py-4">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.16)">
            <svg style="width:18px;height:18px" class="text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[10px] font-semibold uppercase tracking-wider leading-none mb-0.5" style="color:rgba(255,255,255,0.55)">Own Cheques This Month</p>
            <p class="text-[17px] font-black leading-tight truncate" x-text="fmtMoney(d.cheque_stats?.issued_this_month?.total || 0)"></p>
            <p class="text-[9.5px] leading-none mt-0.5" style="color:rgba(255,255,255,0.45)"
               x-text="(d.cheque_stats?.issued_this_month?.count || 0) + ' issued this month'"></p>
          </div>
        </div>
        <div class="kpi-shine"></div>
      </a>

    </div>

    <!-- Party Cheques alert row — only shown when there are earmarked cheques -->
    <div x-show="(d.cheque_stats?.party_cheques?.count || 0) > 0">
      <a href="<?php echo e(url('/cheques')); ?>?direction=received&status=in_hand"
         class="kpi-card block" style="background:linear-gradient(135deg,#4338ca,#312e81)">
        <div class="flex items-center gap-4 px-5 py-4">
          <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.15)">
            <svg style="width:22px;height:22px" class="text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
            </svg>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-[10.5px] font-semibold uppercase tracking-wider leading-none mb-1" style="color:rgba(255,255,255,0.55)">
              Party Cheques &mdash; Received &amp; Committed to Suppliers
            </p>
            <p class="text-[20px] font-black leading-tight" x-text="fmtMoney(d.cheque_stats?.party_cheques?.total || 0)"></p>
            <p class="text-[9.5px] leading-none mt-1" style="color:rgba(255,255,255,0.45)"
               x-text="(d.cheque_stats?.party_cheques?.count || 0) + ' customer cheque(s) in hand, earmarked for supplier payment — awaiting deposit'"></p>
          </div>
          <div class="flex-shrink-0">
            <span class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-lg" style="background:rgba(255,255,255,0.18);color:#fff">
              View Details
              <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 5l7 7-7 7"/></svg>
            </span>
          </div>
        </div>
        <div class="kpi-shine"></div>
      </a>
    </div>

    <!-- ════════════════════════════════════════
         BRANCH PERFORMANCE (Super Admin All Branches)
    ════════════════════════════════════════ -->
    <div x-show="(d.branch_stats && d.branch_stats.length) && widgetVisible('branch_performance')" x-cloak>
      <div class="grid grid-cols-1 lg:grid-cols-5 gap-4">

        <!-- Branch table (3/5) -->
        <div class="lg:col-span-3 card overflow-hidden">
          <div class="sect-hd" style="background:linear-gradient(135deg,#0D2272,#060e38)">
            <div class="sect-hd-title">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
              </svg>
              Branch Performance
            </div>
            <span class="sect-hd-badge" x-text="(d.branch_stats?.length || 0) + ' Branches'"></span>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead style="background:#f8fafc" class="dark:bg-gray-800">
                <tr>
                  <th class="table-hd text-left py-3 pl-4">Branch</th>
                  <th class="table-hd text-right py-3">Today</th>
                  <th class="table-hd text-right py-3">Period Sales</th>
                  <th class="table-hd text-right py-3">Outstanding</th>
                  <th class="table-hd text-right py-3 pr-4">Share</th>
                </tr>
              </thead>
              <tbody>
                <template x-for="(b, i) in (d.branch_stats || [])" :key="b.id">
                  <tr class="border-t border-gray-100 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700/40 transition-colors">
                    <td class="table-td pl-4">
                      <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-[11px] font-black flex-shrink-0"
                             :style="'background:' + ['#1B3EB6','#059669','#d97706','#dc2626','#7c3aed','#0891b2'][i % 6]"
                             x-text="(b.code || b.name).substring(0,2).toUpperCase()"></div>
                        <div>
                          <div class="font-semibold text-gray-800 dark:text-gray-100 text-sm" x-text="b.name"></div>
                          <div class="text-xs text-gray-400" x-text="b.code || ''"></div>
                        </div>
                      </div>
                    </td>
                    <td class="table-td text-right font-semibold text-green-600 text-sm" x-text="fmtMoney(b.sales_today)"></td>
                    <td class="table-td text-right font-bold text-sm" style="color:#1B3EB6" x-text="fmtMoney(b.sales_month)"></td>
                    <td class="table-td text-right font-semibold text-red-600 text-sm" x-text="fmtMoney(b.outstanding)"></td>
                    <td class="table-td pr-4">
                      <div class="flex items-center justify-end gap-2">
                        <div class="pbar" style="width:60px">
                          <div class="pbar-fill"
                               :style="'width:' + Math.min(100, Math.round((b.sales_month / (Math.max(...(d.branch_stats||[]).map(x=>x.sales_month)) || 1)) * 100)) + '%;background:' + ['#1B3EB6','#059669','#d97706','#dc2626','#7c3aed','#0891b2'][i % 6]"></div>
                        </div>
                        <span class="text-xs font-bold text-gray-500 w-8 text-right"
                              x-text="Math.round((b.sales_month / ((d.branch_stats||[]).reduce((s,x)=>s+x.sales_month,0) || 1)) * 100) + '%'"></span>
                      </div>
                    </td>
                  </tr>
                </template>
              </tbody>
              <tfoot style="background:#f8fafc;border-top:2px solid #e2e8f0" class="dark:bg-gray-800 dark:border-gray-700">
                <tr>
                  <td class="pl-4 py-2.5 text-xs font-black text-gray-500 uppercase tracking-wide">Total</td>
                  <td class="table-td text-right font-black text-green-700 text-sm"
                      x-text="fmtMoney((d.branch_stats||[]).reduce((s,b)=>s+b.sales_today,0))"></td>
                  <td class="table-td text-right font-black text-sm" style="color:#1B3EB6"
                      x-text="fmtMoney((d.branch_stats||[]).reduce((s,b)=>s+b.sales_month,0))"></td>
                  <td class="table-td text-right font-black text-red-700 text-sm"
                      x-text="fmtMoney((d.branch_stats||[]).reduce((s,b)=>s+b.outstanding,0))"></td>
                  <td class="pr-4"></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Branch bar chart (2/5) -->
        <div class="lg:col-span-2 card overflow-hidden">
          <div class="sect-hd" style="background:linear-gradient(135deg,#1e3a5f,#0D2272)">
            <div class="sect-hd-title">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
              Sales vs Outstanding
            </div>
            <span class="sect-hd-badge" x-text="periodLabel"></span>
          </div>
          <div class="p-4">
            <div id="branchPerfChart"></div>
          </div>
        </div>

      </div>
    </div>

    <!-- ════════════════════════════════════════
         TARGET PROGRESS (Admin — All Branches)
    ════════════════════════════════════════ -->
    <template x-if="widgetVisible('target_progress')">
      <div x-data="targetWidget()" x-init="init()" class="card overflow-hidden">

        
        <div class="sect-hd" style="background:linear-gradient(135deg,#0D2272,#1B3EB6)">
          <div class="sect-hd-title">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            Target Progress
          </div>
          <div class="flex items-center gap-2">
            <button @click="activeTab='monthly'"
                    class="text-xs font-semibold px-3 py-1 rounded-lg transition-all"
                    :style="activeTab==='monthly'?'background:rgba(255,255,255,.25);color:#fff':'background:rgba(255,255,255,.08);color:rgba(255,255,255,.5)'"
                    x-text="monthName+' '+tYear"></button>
            <button @click="activeTab='annual'"
                    class="text-xs font-semibold px-3 py-1 rounded-lg transition-all"
                    :style="activeTab==='annual'?'background:rgba(255,255,255,.25);color:#fff':'background:rgba(255,255,255,.08);color:rgba(255,255,255,.5)'"
                    x-text="'Annual '+tYear"></button>
            <a href="<?php echo e(url('/targets')); ?>"
               class="text-xs font-semibold px-3 py-1 rounded-lg ml-1"
               style="background:rgba(255,255,255,.12);color:rgba(255,255,255,.8)">Manage →</a>
          </div>
        </div>

        
        <div x-show="tLoading" class="flex justify-center py-8">
          <svg class="animate-spin w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
          </svg>
        </div>

        
        <div x-show="!tLoading">

          
          <template x-if="groupedRows.length===0">
            <div class="text-center text-gray-400 py-10">
              <div class="text-2xl mb-2">🎯</div>
              No targets set for this period.
              <a href="<?php echo e(url('/targets')); ?>" class="block mt-1 text-blue-600 hover:underline text-sm font-medium">Set targets →</a>
            </div>
          </template>

          
          <template x-for="group in groupedRows" :key="group.key">
            <div class="border-b border-gray-100 dark:border-gray-700 last:border-0">

              
              <div class="flex items-center gap-3 px-4 py-2.5"
                   :style="group.is_rep ? 'background:#f0fdf4' : 'background:#eff6ff'"
                   class="dark:bg-gray-800">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-black flex-shrink-0"
                     :style="group.is_rep ? 'background:#059669' : 'background:#1B3EB6'"
                     x-text="(group.label||'?')[0].toUpperCase()"></div>
                <div class="flex-1 min-w-0">
                  <div class="font-bold text-sm text-gray-800 dark:text-gray-100" x-text="group.label"></div>
                  <div class="text-xs text-gray-500" x-text="group.sub"></div>
                </div>
                <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full"
                      :style="group.is_rep ? 'background:#dcfce7;color:#166534' : 'background:#dbeafe;color:#1d4ed8'"
                      x-text="group.is_rep ? 'Sales Rep' : 'Branch'"></span>
                <span class="text-xs font-bold px-2.5 py-0.5 rounded-full"
                      :style="group.overall_pct>=100?'background:#dcfce7;color:#166534':group.overall_pct>=70?'background:#fef9c3;color:#854d0e':'background:#fee2e2;color:#b91c1c'"
                      x-text="group.overall_pct+'% overall'"></span>
              </div>

              
              <table class="w-full">
                <tbody>
                  <template x-for="t in group.targets" :key="t.id">
                    <tr class="border-t border-gray-50 dark:border-gray-700/50 hover:bg-gray-50/60 dark:hover:bg-gray-700/20 transition-colors">

                      
                      <td class="py-3 pl-8 pr-2" style="width:200px">
                        <div class="flex items-center gap-2">
                          <div class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0"
                               :style="typeStyle(t.type).bg">
                            <span style="font-size:11px" x-text="typeStyle(t.type).icon"></span>
                          </div>
                          <span class="text-xs font-semibold" :style="typeStyle(t.type).text" x-text="typeStyle(t.type).label"></span>
                        </div>
                      </td>

                      
                      <td class="py-3 px-3 text-right" style="width:150px">
                        <div class="text-[10px] text-gray-400 uppercase tracking-wider">Target</div>
                        <div class="text-sm font-bold text-gray-700 dark:text-gray-200 tabular-nums"
                             x-text="fmtVal(t.target_value, t.type)"></div>
                      </td>

                      
                      <td class="py-3 px-3 text-right" style="width:150px">
                        <div class="text-[10px] text-gray-400 uppercase tracking-wider">Achieved</div>
                        <div class="text-sm font-bold tabular-nums"
                             :class="t.pct>=100?'text-green-600':t.pct>=70?'text-yellow-600':'text-red-500'"
                             x-text="fmtVal(t.achieved_value, t.type)"></div>
                      </td>

                      
                      <td class="py-3 px-3 text-right" style="width:150px">
                        <div class="text-[10px] text-gray-400 uppercase tracking-wider">Remaining</div>
                        <div class="text-sm font-semibold text-orange-600 tabular-nums"
                             x-text="t.pct>=100 ? '✓ Met' : fmtVal(Math.max(0, t.target_value - t.achieved_value), t.type)"></div>
                      </td>

                      
                      <td class="py-3 pl-3 pr-4">
                        <div class="flex items-center gap-2">
                          <div class="pbar flex-1" style="min-width:80px">
                            <div class="pbar-fill"
                                 :style="'width:'+Math.min(t.pct,100)+'%;background:'+(t.pct>=100?'#22A845':t.pct>=70?'#f59e0b':'#E31E24')"></div>
                          </div>
                          <span class="text-xs font-black w-10 text-right flex-shrink-0"
                                :class="t.pct>=100?'text-green-600':t.pct>=70?'text-yellow-600':'text-red-500'"
                                x-text="t.pct+'%'"></span>
                        </div>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </template>
        </div>
      </div>
    </template>

    <!-- ════════════════════════════════════════
         TODAY'S SALES REPORT
    ════════════════════════════════════════ -->
    <div class="card overflow-hidden" x-show="widgetVisible('today_sales')" x-cloak>
      <div class="sect-hd" style="background:linear-gradient(135deg,#0f172a,#1e293b)">
        <div class="sect-hd-title">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Today's Sales Report
        </div>
        <div class="flex items-center gap-3">
          <span class="text-xs font-semibold" style="color:rgba(255,255,255,0.5)"
                x-text="'Total: ' + fmtMoney((d.today_sales || []).reduce((s,r)=>s+r.total, 0))"></span>
          <span class="sect-hd-badge" x-text="(d.today_sales?.length || 0) + ' invoices'"></span>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="today-tbl w-full">
          <thead style="background:#f8fafc" class="dark:bg-gray-800">
            <tr>
              <th class="text-left">#</th>
              <th class="text-left">Invoice</th>
              <th class="text-left">Customer</th>
              <th class="text-left">Branch</th>
              <th class="text-right">Total</th>
              <th class="text-right">Paid</th>
              <th class="text-right">Balance</th>
              <th class="text-left">Status</th>
              <th class="text-right">Time</th>
            </tr>
          </thead>
          <tbody>
            <template x-for="(inv, idx) in (d.today_sales || [])" :key="inv.id">
              <tr>
                <td class="text-gray-400 text-xs" x-text="idx + 1"></td>
                <td>
                  <a :href="BASE + '/invoices/' + inv.id"
                     class="font-semibold text-primary-700 dark:text-primary-400 hover:underline text-xs"
                     x-text="inv.invoice_number"></a>
                </td>
                <td class="font-medium text-gray-800 dark:text-gray-100" x-text="inv.customer"></td>
                <td>
                  <span class="branch-badge" x-text="inv.branch"></span>
                </td>
                <td class="text-right font-bold text-gray-800 dark:text-gray-100" x-text="fmtMoney(inv.total)"></td>
                <td class="text-right text-green-600 dark:text-green-400" x-text="fmtMoney(inv.paid_amount)"></td>
                <td class="text-right text-red-600 dark:text-red-400" x-text="inv.balance_due > 0 ? fmtMoney(inv.balance_due) : '—'"></td>
                <td>
                  <span class="badge"
                        :class="{
                          'badge-success': inv.status === 'paid',
                          'badge-warning': inv.status === 'partially_paid' || inv.status === 'confirmed',
                          'badge-gray':    inv.status === 'draft',
                          'badge-danger':  inv.status === 'cancelled',
                        }"
                        x-text="inv.status?.replace('_',' ')"></span>
                </td>
                <td class="text-right text-xs text-gray-400" x-text="inv.created_at"></td>
              </tr>
            </template>
            <template x-if="!d.today_sales?.length">
              <tr>
                <td colspan="9" class="text-center text-gray-400 py-12">
                  <div class="text-3xl mb-2">📋</div>
                  No invoices recorded today
                </td>
              </tr>
            </template>
          </tbody>
          <template x-if="d.today_sales?.length">
            <tfoot style="background:#f0f4ff;border-top:2px solid #c7d2fe" class="dark:bg-primary-900/20 dark:border-primary-900/40">
              <tr>
                <td colspan="4" class="pl-4 py-3 text-xs font-black text-primary-700 dark:text-primary-300 uppercase tracking-wide">
                  Daily Total
                </td>
                <td class="text-right pr-4 py-3 font-black text-primary-800 dark:text-primary-200"
                    x-text="fmtMoney((d.today_sales || []).reduce((s,r)=>s+r.total, 0))"></td>
                <td class="text-right pr-4 py-3 font-black text-green-700 dark:text-green-300"
                    x-text="fmtMoney((d.today_sales || []).reduce((s,r)=>s+r.paid_amount, 0))"></td>
                <td class="text-right pr-4 py-3 font-black text-red-600 dark:text-red-400"
                    x-text="fmtMoney((d.today_sales || []).reduce((s,r)=>s+r.balance_due, 0))"></td>
                <td colspan="2"></td>
              </tr>
            </tfoot>
          </template>
        </table>
      </div>
    </div>

    <!-- ════════════════════════════════════════
         ROW 3 — Sales Reps Achievement + Due Aging
    ════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4" x-show="widgetVisible('sales_reps_aging')" x-cloak>

      <!-- Cheque Details Widget (3/5) -->
      <div class="lg:col-span-3 card overflow-hidden" x-data="chequeWidget()" x-init="init()">

        
        <div class="sect-hd" style="background:linear-gradient(135deg,#0f766e,#134e4a)">
          <div class="sect-hd-title">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Cheque Details
          </div>
          <div class="flex items-center gap-2">
            <span class="text-xs font-semibold" style="color:rgba(255,255,255,.55)" x-text="fmtMoney(totalAmount)"></span>
            <span class="sect-hd-badge" x-text="cheques.length + ' cheques'"></span>
          </div>
        </div>

        
        <div class="flex flex-wrap items-center gap-2 px-3 py-2.5 border-b border-gray-100 dark:border-gray-700" style="background:#f8fafc" :style="$el.classList.contains('dark')?'background:#1e293b':''">
          
          <div class="flex gap-1 flex-shrink-0">
            <button @click="setDay('today')"
                    class="text-xs font-semibold px-3 py-1.5 rounded-lg border transition-all"
                    :style="cDay==='today'?'background:#0f766e;color:#fff;border-color:#0f766e':'background:#fff;color:#475569;border-color:#e2e8f0'">
              Today
            </button>
            <button @click="setDay('tomorrow')"
                    class="text-xs font-semibold px-3 py-1.5 rounded-lg border transition-all"
                    :style="cDay==='tomorrow'?'background:#0f766e;color:#fff;border-color:#0f766e':'background:#fff;color:#475569;border-color:#e2e8f0'">
              Tomorrow
            </button>
            <button @click="setDay('dayafter')"
                    class="text-xs font-semibold px-3 py-1.5 rounded-lg border transition-all"
                    :style="cDay==='dayafter'?'background:#0f766e;color:#fff;border-color:#0f766e':'background:#fff;color:#475569;border-color:#e2e8f0'">
              Day After
            </button>
          </div>
          
          <select x-model="cBank" @change="load()"
                  class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200 min-w-0"
                  style="max-width:130px">
            <option value="">All Banks</option>
            <template x-for="b in banks" :key="b"><option :value="b" x-text="b"></option></template>
          </select>
          
          <input type="date" x-model="cCustomDate" @change="cDay='';load()"
                 class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-gray-200"
                 style="max-width:130px" />
          <button x-show="cCustomDate" @click="cCustomDate='';setDay('today')"
                  class="text-xs text-gray-400 hover:text-gray-600 px-1">✕ Clear</button>
        </div>

        
        <div x-show="cLoading" class="flex justify-center py-8">
          <svg class="animate-spin w-5 h-5 text-teal-500" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
          </svg>
        </div>

        
        <div x-show="!cLoading" class="overflow-x-auto">
          <table class="w-full">
            <thead style="background:#f0fdfa" class="dark:bg-gray-800">
              <tr>
                <th class="table-hd text-left py-2.5 pl-4" style="width:28px"></th>
                <th class="table-hd text-left py-2.5">Cheque #</th>
                <th class="table-hd text-left py-2.5">Party</th>
                <th class="table-hd text-left py-2.5">Bank</th>
                <th class="table-hd text-right py-2.5">Amount</th>
                <th class="table-hd text-center py-2.5">Status</th>
                <th class="table-hd py-2.5 pr-3 text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <template x-for="c in cheques" :key="c.id">
                <tr class="border-t border-gray-100 dark:border-gray-700 hover:bg-teal-50/40 dark:hover:bg-gray-700/30 transition-colors">
                  <td class="pl-4 py-2.5">
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded"
                          :style="c.direction==='received'?'background:#dcfce7;color:#166534':'background:#fff7ed;color:#c2410c'"
                          x-text="c.direction==='received'?'IN':'OUT'"></span>
                  </td>
                  <td class="table-td py-2.5">
                    <div class="font-semibold text-gray-800 dark:text-gray-100 text-xs" x-text="c.cheque_number"></div>
                    <div class="text-[10px] text-gray-400" x-text="fmtDate(c.cheque_date)"></div>
                  </td>
                  <td class="table-td py-2.5">
                    <div class="text-xs font-medium text-gray-700 dark:text-gray-200" x-text="partyName(c)"></div>
                    <div class="text-[10px] text-gray-400 capitalize" x-text="c.party_type ?? ''"></div>
                  </td>
                  <td class="table-td py-2.5 text-xs text-gray-600 dark:text-gray-300" x-text="c.bank_name ?? '—'"></td>
                  <td class="table-td py-2.5 text-right font-bold text-sm"
                      :style="c.direction==='received'?'color:#059669':'color:#d97706'"
                      x-text="fmtMoney(c.amount)"></td>
                  <td class="table-td py-2.5 text-center">
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                          :style="'background:'+statusStyle(c.status).bg+';color:'+statusStyle(c.status).text"
                          x-text="statusStyle(c.status).label"></span>
                  </td>
                  <td class="table-td py-2.5 pr-3 text-right">
                    <button @click="viewDetail(c.id)"
                            class="text-xs font-semibold px-2.5 py-1 rounded-lg border transition-all"
                            style="border-color:#0f766e;color:#0f766e"
                            onmouseover="this.style.background='#f0fdfa'" onmouseout="this.style.background=''">
                      View
                    </button>
                  </td>
                </tr>
              </template>
              <template x-if="cheques.length===0 && !cLoading">
                <tr>
                  <td colspan="7" class="text-center text-gray-400 py-10 text-sm">
                    <div class="text-2xl mb-2">📋</div>
                    No cheques due <span x-text="cCustomDate ? 'on '+cCustomDate : 'for '+dayLabel"></span>
                  </td>
                </tr>
              </template>
            </tbody>
            <template x-if="cheques.length > 0">
              <tfoot style="background:#f0fdfa;border-top:2px solid #99f6e4" class="dark:bg-teal-900/20">
                <tr>
                  <td colspan="4" class="pl-4 py-2.5 text-xs font-black text-teal-700 dark:text-teal-300 uppercase tracking-wide">
                    Total
                  </td>
                  <td class="text-right pr-0 py-2.5 font-black text-sm text-teal-800 dark:text-teal-200"
                      x-text="fmtMoney(totalAmount)"></td>
                  <td colspan="2"></td>
                </tr>
              </tfoot>
            </template>
          </table>
        </div>

        
        <div x-show="showDetail"
             class="fixed inset-0 z-50 flex items-center justify-center p-4"
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
          <div class="absolute inset-0" style="background:rgba(15,23,42,.65);backdrop-filter:blur(4px)" @click="showDetail=false"></div>
          <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-xl z-10 overflow-hidden flex flex-col" style="max-height:90vh">

            
            <div class="h-1.5 flex-shrink-0"
                 :class="viewCheque?.direction === 'received' ? 'bg-green-500' : 'bg-amber-500'"></div>

            
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-start gap-3 flex-shrink-0">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                   :class="viewCheque?.direction === 'received' ? 'bg-green-100' : 'bg-amber-100'">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                     :stroke="viewCheque?.direction === 'received' ? '#16a34a' : '#b45309'">
                  <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center flex-wrap gap-1.5">
                  <h3 class="font-black text-gray-900 dark:text-white text-base font-mono"
                      x-text="viewCheque?.cheque_number || '—'"></h3>
                  <span class="text-xs font-bold px-2 py-0.5 rounded-full"
                        :style="'background:'+statusStyle(viewCheque?.status).bg+';color:'+statusStyle(viewCheque?.status).text"
                        x-text="statusStyle(viewCheque?.status).label"></span>
                </div>
                <p class="text-xs text-gray-500 mt-0.5"
                   x-text="(viewCheque?.direction === 'received' ? '📨 Received from customer' : '📄 Issued to supplier') + ' · ' + (viewCheque?.bank_name || 'Unknown Bank')"></p>
              </div>
              <button @click="showDetail=false"
                      class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 transition-colors">
                <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>

            
            <div x-show="viewLoading" class="flex justify-center py-12">
              <svg class="animate-spin w-6 h-6 text-teal-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
              </svg>
            </div>

            
            <div x-show="!viewLoading && viewCheque" class="overflow-y-auto flex-1 p-5 space-y-5">

              
              <div class="grid grid-cols-3 gap-3">
                <div class="rounded-xl p-3 text-center bg-gray-50 dark:bg-gray-700/40">
                  <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide mb-1">Amount</p>
                  <p class="font-black text-gray-900 dark:text-white text-lg leading-none" x-text="fmtMoney(viewCheque?.amount || 0)"></p>
                </div>
                <div class="rounded-xl p-3 text-center bg-gray-50 dark:bg-gray-700/40">
                  <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide mb-1">Cheque Date</p>
                  <p class="font-bold text-gray-700 dark:text-gray-200 text-sm" x-text="fmtDate(viewCheque?.cheque_date)"></p>
                </div>
                <div class="rounded-xl p-3 text-center bg-gray-50 dark:bg-gray-700/40">
                  <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide mb-1"
                     x-text="viewCheque?.direction === 'received' ? 'Received On' : 'Issued On'"></p>
                  <p class="font-bold text-gray-700 dark:text-gray-200 text-sm" x-text="fmtDate(viewCheque?.received_issued_date)"></p>
                </div>
              </div>

              
              <div class="flex items-center gap-2 text-sm">
                <span class="text-gray-400">Branch:</span>
                <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="viewCheque?.branch?.name ?? '—'"></span>
              </div>

              
              <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-2"
                   x-text="viewCheque?.direction === 'received' ? 'Received From' : 'Issued To'"></p>
                <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 dark:border-gray-600">
                  <div class="w-9 h-9 rounded-lg flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                       :style="'background:' + (viewCheque?.party_type === 'customer' ? '#1B3EB6' : '#059669')"
                       x-text="(viewCheque?.party_type === 'customer'
                           ? (viewCheque?.customer?.name || '?')
                           : (viewCheque?.supplier?.name || '?')
                       ).charAt(0).toUpperCase()"></div>
                  <div>
                    <p class="font-semibold text-gray-800 dark:text-gray-100 text-sm"
                       x-text="viewCheque?.party_type === 'customer'
                           ? (viewCheque?.customer?.name || '—')
                           : (viewCheque?.supplier?.name || '—')"></p>
                    <p class="text-xs text-gray-400 capitalize" x-text="viewCheque?.party_type || ''"></p>
                  </div>
                </div>
              </div>

              
              <div class="flex items-center gap-3">
                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Transaction History</span>
                <div class="flex-1 h-px bg-gray-200 dark:bg-gray-600"></div>
              </div>

              
              <template x-if="viewCheque?.invoice_links?.length">
                <div>
                  <p class="text-[10px] font-bold text-blue-600 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Applied to Customer Invoices
                  </p>
                  <div class="space-y-2">
                    <template x-for="lnk in viewCheque.invoice_links" :key="lnk.id">
                      <div class="flex items-center justify-between p-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                        <div>
                          <p class="font-mono font-bold text-blue-700 text-sm" x-text="lnk.invoice?.invoice_number || ('INV-' + lnk.invoice_id)"></p>
                          <p class="text-xs text-blue-500 mt-0.5" x-text="lnk.invoice?.customer?.name || '—'"></p>
                        </div>
                        <span class="font-black text-blue-700 text-sm" x-text="fmtMoney(lnk.amount || 0)"></span>
                      </div>
                    </template>
                  </div>
                </div>
              </template>

              
              <template x-if="viewCheque?.supplier_payments?.length">
                <div>
                  <p class="text-[10px] font-bold text-red-600 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
                    Handed Over to Suppliers
                  </p>
                  <div class="space-y-2">
                    <template x-for="sp in viewCheque.supplier_payments" :key="sp.id">
                      <div class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        <div class="flex items-center justify-between">
                          <div>
                            <p class="font-bold text-red-700 text-sm" x-text="sp.supplier_invoice?.supplier?.name || sp.purchase_order?.supplier?.name || 'Supplier'"></p>
                            <p class="text-xs font-mono text-red-500 mt-0.5" x-text="sp.supplier_invoice?.invoice_number || sp.purchase_order?.po_number || '—'"></p>
                          </div>
                          <span class="font-black text-red-700 text-sm" x-text="fmtMoney(sp.amount || 0)"></span>
                        </div>
                        <div class="mt-1.5 text-[10px] text-red-400 font-medium" x-text="'Payment Date: ' + fmtDate(sp.payment_date)"></div>
                      </div>
                    </template>
                  </div>
                </div>
              </template>

              
              <template x-if="viewCheque?.purchase_orders?.length">
                <div>
                  <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Reserved on Purchase Orders
                  </p>
                  <div class="space-y-2">
                    <template x-for="po in viewCheque.purchase_orders" :key="po.id">
                      <div class="flex items-center justify-between p-3 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                        <div>
                          <p class="font-mono font-bold text-amber-800 text-sm" x-text="po.po_number"></p>
                          <p class="text-xs text-amber-600 mt-0.5" x-text="po.supplier?.name || '—'"></p>
                        </div>
                        <div class="text-right">
                          <span class="font-black text-amber-700 text-sm" x-text="fmtMoney(po.total || 0)"></span>
                          <p class="text-[10px] text-amber-500 capitalize mt-0.5" x-text="(po.status || '').replace(/_/g,' ')"></p>
                        </div>
                      </div>
                    </template>
                  </div>
                </div>
              </template>

              
              <template x-if="viewCheque?.expenses?.length">
                <div>
                  <p class="text-[10px] font-bold text-green-600 uppercase tracking-wide mb-2 flex items-center gap-1.5">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                    Expenses Paid with This Cheque
                  </p>
                  <div class="space-y-2">
                    <template x-for="exp in viewCheque.expenses" :key="exp.id">
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

              
              <template x-if="viewCheque?.status === 'bounced'">
                <div class="p-4 rounded-xl" style="background:#fef2f2;border:1px solid #fecaca">
                  <p class="font-bold text-red-700 text-sm flex items-center gap-1.5 mb-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    Cheque Bounced
                  </p>
                  <div class="text-xs text-red-600 space-y-1">
                    <div x-text="'Bounce Date: ' + fmtDate(viewCheque.bounced_date)"></div>
                    <div x-show="viewCheque.bounce_reason" x-text="'Reason: ' + viewCheque.bounce_reason"></div>
                  </div>
                </div>
              </template>

              
              <template x-if="viewCheque?.deposit_slip_number || viewCheque?.deposited_date || viewCheque?.cleared_date">
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 space-y-2">
                  <div class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Banking Details</div>
                  <template x-if="viewCheque?.deposit_slip_number">
                    <div class="flex gap-2 text-sm"><span class="text-gray-400 w-32">Deposit Slip:</span><span class="font-medium text-gray-700 dark:text-gray-300" x-text="viewCheque.deposit_slip_number"></span></div>
                  </template>
                  <template x-if="viewCheque?.deposited_date">
                    <div class="flex gap-2 text-sm"><span class="text-gray-400 w-32">Deposited:</span><span class="font-medium text-gray-700 dark:text-gray-300" x-text="fmtDate(viewCheque.deposited_date)"></span></div>
                  </template>
                  <template x-if="viewCheque?.cleared_date">
                    <div class="flex gap-2 text-sm"><span class="text-gray-400 w-32">Cleared:</span><span class="font-medium text-green-600" x-text="fmtDate(viewCheque.cleared_date)"></span></div>
                  </template>
                </div>
              </template>

              
              <template x-if="viewCheque?.notes">
                <div>
                  <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide mb-2">Notes</p>
                  <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-700/40 text-sm text-gray-600 dark:text-gray-300 leading-relaxed"
                       x-text="viewCheque.notes"></div>
                </div>
              </template>

              
              <template x-if="!viewCheque?.invoice_links?.length && !viewCheque?.supplier_payments?.length && !viewCheque?.purchase_orders?.length && !viewCheque?.expenses?.length && !viewCheque?.notes">
                <div class="text-center py-8 text-gray-400">
                  <svg class="w-10 h-10 mx-auto mb-3 opacity-25" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                  </svg>
                  <p class="text-sm font-medium">No transaction history yet</p>
                  <p class="text-xs mt-1">This cheque hasn't been applied to any invoice or payment</p>
                </div>
              </template>

              
              <div class="text-[11px] text-gray-400 text-right" x-text="'Created by ' + (viewCheque?.created_by?.name ?? '—')"></div>

            </div>

            
            <div class="p-4 border-t border-gray-100 dark:border-gray-700 flex-shrink-0 flex justify-end">
              <button @click="showDetail=false" class="px-5 py-2 rounded-lg text-sm font-medium bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition-colors">Close</button>
            </div>

          </div>
        </div>

      </div>

      <!-- Due Details Aging (2/5) -->
      <div class="lg:col-span-2 card overflow-hidden">
        <div class="sect-hd" style="background:linear-gradient(135deg,#7c3aed,#5b21b6)">
          <div class="sect-hd-title">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            Due Details Aging
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="aging-tbl w-full">
            <thead style="background:#f8fafc" class="dark:bg-gray-800">
              <tr>
                <th class="py-3 pl-4">Period</th>
                <th style="color:#059669">Customer Dues</th>
                <th style="color:#d97706">Supplier Dues</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="pl-4">
                  <span class="inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:#22A845"></span>
                    0 – 30 Days
                  </span>
                </td>
                <td class="font-semibold" style="color:#16a34a" x-text="fmtMoney(d.aging?.customer?.['0_30'] || 0)"></td>
                <td class="font-semibold" style="color:#16a34a" x-text="fmtMoney(d.aging?.supplier?.['0_30'] || 0)"></td>
              </tr>
              <tr>
                <td class="pl-4">
                  <span class="inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:#eab308"></span>
                    30 – 60 Days
                  </span>
                </td>
                <td class="font-semibold" style="color:#ca8a04" x-text="fmtMoney(d.aging?.customer?.['30_60'] || 0)"></td>
                <td class="font-semibold" style="color:#ca8a04" x-text="fmtMoney(d.aging?.supplier?.['30_60'] || 0)"></td>
              </tr>
              <tr>
                <td class="pl-4">
                  <span class="inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:#f97316"></span>
                    60 – 90 Days
                  </span>
                </td>
                <td class="font-semibold" style="color:#ea580c" x-text="fmtMoney(d.aging?.customer?.['60_90'] || 0)"></td>
                <td class="font-semibold" style="color:#ea580c" x-text="fmtMoney(d.aging?.supplier?.['60_90'] || 0)"></td>
              </tr>
              <tr>
                <td class="pl-4">
                  <span class="inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:#ef4444"></span>
                    90 – 120 Days
                  </span>
                </td>
                <td class="font-semibold text-red-600" x-text="fmtMoney(d.aging?.customer?.['90_120'] || 0)"></td>
                <td class="font-semibold text-red-600" x-text="fmtMoney(d.aging?.supplier?.['90_120'] || 0)"></td>
              </tr>
              <tr style="background:#fff8f8" class="dark:bg-red-900/10">
                <td class="pl-4">
                  <span class="inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:#991b1b"></span>
                    <span class="font-black text-red-800 dark:text-red-400">Over 120 Days</span>
                  </span>
                </td>
                <td class="font-black text-red-700 dark:text-red-400" x-text="fmtMoney(d.aging?.customer?.over_120 || 0)"></td>
                <td class="font-black text-red-700 dark:text-red-400" x-text="fmtMoney(d.aging?.supplier?.over_120 || 0)"></td>
              </tr>
            </tbody>
            <tfoot style="background:#f8fafc;border-top:2px solid #e2e8f0" class="dark:bg-gray-800 dark:border-gray-700">
              <tr>
                <td class="pl-4 py-3 text-xs font-black text-gray-500 uppercase tracking-wide">Total</td>
                <td class="text-right pr-4 py-3 font-black text-gray-800 dark:text-gray-100 text-sm"
                    x-text="fmtMoney(Object.values(d.aging?.customer || {}).reduce((a,b)=>a+b,0))"></td>
                <td class="text-right pr-4 py-3 font-black text-gray-800 dark:text-gray-100 text-sm"
                    x-text="fmtMoney(Object.values(d.aging?.supplier || {}).reduce((a,b)=>a+b,0))"></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>

    <!-- ════════════════════════════════════════
         ROW 4 — Charts
    ════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-4" x-show="widgetVisible('charts')" x-cloak>

      <!-- Best Sale Products Bar Chart (3/5) -->
      <div class="lg:col-span-3 card overflow-hidden">
        <div class="sect-hd" style="background:linear-gradient(135deg,#059669,#047857)">
          <div class="sect-hd-title">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
            Best Sale Products
          </div>
          <span class="sect-hd-badge" x-text="periodLabel"></span>
        </div>
        <div class="p-4" style="min-height:240px;position:relative">
          <div id="bestProductsChart"></div>
          <div class="chart-empty" style="position:absolute;inset:0" x-show="!d.best_products?.length">
            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            No sales data for this month
          </div>
        </div>
      </div>

      <!-- Expense Pie Chart (2/5) -->
      <div class="lg:col-span-2 card overflow-hidden">
        <div class="sect-hd" style="background:linear-gradient(135deg,#dc2626,#991b1b)">
          <div class="sect-hd-title">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
            </svg>
            Expense Statement
          </div>
          <span class="sect-hd-badge" x-text="periodLabel"></span>
        </div>
        <div class="p-4" style="min-height:240px;position:relative">
          <div id="expenseChart"></div>
          <div class="mt-3 space-y-1.5 max-h-28 overflow-y-auto" x-show="d.expense_categories?.length">
            <template x-for="(cat, ci) in (d.expense_categories || [])" :key="ci">
              <div class="flex items-center justify-between text-xs">
                <div class="flex items-center gap-1.5">
                  <span class="w-2.5 h-2.5 rounded-sm flex-shrink-0"
                        :style="'background:' + expColors[ci % expColors.length]"></span>
                  <span class="text-gray-600 dark:text-gray-400 truncate max-w-[120px]" x-text="cat.name"></span>
                </div>
                <span class="font-semibold text-gray-700 dark:text-gray-300 ml-2" x-text="fmtMoney(cat.total)"></span>
              </div>
            </template>
          </div>
          <div class="chart-empty" style="position:absolute;inset:0" x-show="!d.expense_categories?.length">
            <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
            </svg>
            No expense data for this period
          </div>
        </div>
      </div>
    </div>

    <!-- ════════════════════════════════════════
         ROW 5 — Monthly Due Tables
    ════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4" x-show="widgetVisible('due_tables')" x-cloak>

      <!-- This Month's Sales Due -->
      <div class="card overflow-hidden">
        <div class="sect-hd" style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
          <div class="sect-hd-title">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            This Month's Sales Due
          </div>
          <span class="sect-hd-badge" x-text="(d.sales_due_today?.length || 0) + ' invoices'"></span>
        </div>
        <div class="overflow-x-auto">
          <table class="due-tbl w-full">
            <thead style="background:#f8fafc" class="dark:bg-gray-800">
              <tr>
                <th>Invoice</th>
                <th>Customer</th>
                <th class="text-right">Invoice Total</th>
                <th class="text-right">Balance Due</th>
                <th class="text-right">Due Date</th>
              </tr>
            </thead>
            <tbody>
              <template x-for="inv in (d.sales_due_today || [])" :key="inv.id">
                <tr>
                  <td>
                    <a :href="BASE + '/invoices/' + inv.id" class="font-semibold text-primary-700 dark:text-primary-400 hover:underline text-xs" x-text="inv.invoice_number"></a>
                  </td>
                  <td class="text-gray-700 dark:text-gray-300" x-text="inv.customer"></td>
                  <td class="text-right text-gray-500" x-text="fmtMoney(inv.total)"></td>
                  <td class="text-right font-bold text-red-600 dark:text-red-400" x-text="fmtMoney(inv.balance_due)"></td>
                  <td class="text-right text-xs text-gray-500" x-text="inv.due_date ? fmtDate(inv.due_date) : '—'"></td>
                </tr>
              </template>
              <template x-if="!d.sales_due_today?.length">
                <tr>
                  <td colspan="5" class="text-center text-gray-400 py-8">
                    <span class="text-xl block mb-1">✓</span>No sales due this month
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>

      <!-- This Month's Purchase Due -->
      <div class="card overflow-hidden">
        <div class="sect-hd" style="background:linear-gradient(135deg,#d97706,#b45309)">
          <div class="sect-hd-title">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
            This Month's Purchase Due
          </div>
          <span class="sect-hd-badge" x-text="(d.purchase_due_today?.length || 0) + ' invoices'"></span>
        </div>
        <div class="overflow-x-auto">
          <table class="due-tbl w-full">
            <thead style="background:#f8fafc" class="dark:bg-gray-800">
              <tr>
                <th>Invoice</th>
                <th>Supplier</th>
                <th class="text-right">Invoice Total</th>
                <th class="text-right">Balance Due</th>
                <th class="text-right">Due Date</th>
              </tr>
            </thead>
            <tbody>
              <template x-for="inv in (d.purchase_due_today || [])" :key="inv.id">
                <tr>
                  <td>
                    <span class="font-semibold text-yellow-700 dark:text-yellow-400 text-xs" x-text="inv.invoice_number"></span>
                  </td>
                  <td class="text-gray-700 dark:text-gray-300" x-text="inv.supplier"></td>
                  <td class="text-right text-gray-500" x-text="fmtMoney(inv.total)"></td>
                  <td class="text-right font-bold text-orange-600 dark:text-orange-400" x-text="fmtMoney(inv.balance_due)"></td>
                  <td class="text-right text-xs text-gray-500" x-text="inv.due_date ? fmtDate(inv.due_date) : '—'"></td>
                </tr>
              </template>
              <template x-if="!d.purchase_due_today?.length">
                <tr>
                  <td colspan="5" class="text-center text-gray-400 py-8">
                    <span class="text-xl block mb-1">✓</span>No purchases due this month
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ════════════════════════════════════════
         ROW 6 — Low Stock Alert
    ════════════════════════════════════════ -->
    <div x-show="(d.low_stock && d.low_stock.length) && widgetVisible('low_stock')" x-transition class="card overflow-hidden">
      <div class="sect-hd" style="background:linear-gradient(135deg,#b91c1c,#7f1d1d)">
        <div class="sect-hd-title">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
          Low Stock Alert
        </div>
        <div class="flex items-center gap-3">
          <span class="text-xs font-semibold" style="color:rgba(255,255,255,0.5)">Items at or below reorder level</span>
          <span class="sect-hd-badge" x-text="(d.low_stock?.length || 0) + ' items'"></span>
        </div>
      </div>
      <div class="overflow-x-auto">
        <table class="today-tbl w-full">
          <thead style="background:#fff8f8" class="dark:bg-red-900/10">
            <tr>
              <th class="text-left">#</th>
              <th class="text-left">Product</th>
              <th class="text-left">Code</th>
              <template x-if="d.branch_stats && d.branch_stats.length">
                <th class="text-left">Branch</th>
              </template>
              <th class="text-right">Current Stock</th>
              <th class="text-right">Reorder Level</th>
              <th class="text-right">Gap</th>
              <th class="text-left">Status</th>
            </tr>
          </thead>
          <tbody>
            <template x-for="(item, idx) in (d.low_stock || [])" :key="item.product_id + '-' + item.branch_id">
              <tr :class="item.quantity <= 0 ? 'bg-red-50 dark:bg-red-900/10' : ''">
                <td class="text-gray-400 text-xs" x-text="idx + 1"></td>
                <td>
                  <a :href="BASE + '/products/' + item.product_id"
                     class="font-semibold text-gray-800 dark:text-gray-100 hover:underline text-sm"
                     x-text="item.product_name"></a>
                </td>
                <td class="text-xs text-gray-400 font-mono" x-text="item.product_code || '—'"></td>
                <template x-if="d.branch_stats && d.branch_stats.length">
                  <td>
                    <span class="branch-badge" x-text="item.branch_name"></span>
                  </td>
                </template>
                <td class="text-right font-bold tabular-nums"
                    :class="item.quantity <= 0 ? 'text-red-700 dark:text-red-400' : 'text-orange-600 dark:text-orange-400'"
                    x-text="item.quantity.toFixed(0) + ' ' + item.unit"></td>
                <td class="text-right text-gray-500 tabular-nums"
                    x-text="item.reorder_level.toFixed(0) + ' ' + item.unit"></td>
                <td class="text-right font-semibold tabular-nums text-red-600 dark:text-red-400"
                    x-text="(item.reorder_level - item.quantity).toFixed(0) + ' ' + item.unit"></td>
                <td>
                  <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                        :class="item.quantity <= 0
                          ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'
                          : 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400'"
                        x-text="item.quantity <= 0 ? 'Out of Stock' : 'Low Stock'">
                  </span>
                </td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ════════════════════════════════════════
         CUSTOMER DISTRIBUTION MAP
    ════════════════════════════════════════ -->
    <div class="card overflow-hidden" x-data="customerMap()" x-init="init()">

      <!-- Header -->
      <div class="sect-hd" style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 60%,#0a1a55 100%)">
        <div class="sect-hd-title">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          Customer Distribution — Sri Lanka
        </div>
        <div class="flex items-center gap-2">
          <span class="text-xs" style="color:rgba(255,255,255,.5)" x-text="mapData.mapped + ' mapped / ' + mapData.total + ' total'"></span>
          <span class="sect-hd-badge" x-text="mapData.districts?.length + ' / 25 districts'"></span>
        </div>
      </div>

      <!-- Body -->
      <div class="flex flex-col lg:flex-row" style="min-height:480px;background:linear-gradient(160deg,#0f1729 0%,#0d1a3a 100%)">

        <!-- MAP COLUMN -->
        <div class="flex-1 flex items-center justify-center p-6 relative" style="min-height:420px">

          <!-- Loading skeleton -->
          <div x-show="mapLoading" class="absolute inset-0 flex items-center justify-center">
            <svg class="spin w-8 h-8" style="color:#3b82f6" fill="none" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="30 70" stroke-linecap="round"/>
            </svg>
          </div>

          <div x-show="!mapLoading" class="relative" style="width:320px;height:440px">

            <!-- d3-rendered Sri Lanka SVG map -->
            <svg id="lk-map-svg" viewBox="0 0 320 440" style="width:320px;height:440px;display:block"></svg>

            <!-- Tooltip -->
            <div x-show="tooltip.visible" x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="absolute z-20 pointer-events-none"
                 :style="'left:' + tooltip.x + 'px;top:' + tooltip.y + 'px;transform:translate(-50%,-110%)'">
              <div class="rounded-xl shadow-xl px-3.5 py-2.5 text-left min-w-[160px]" style="background:#0f172a;border:1px solid rgba(255,255,255,0.12)">
                <div class="flex items-center gap-2 mb-1.5">
                  <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="'background:' + tooltip.color"></div>
                  <span class="text-xs font-bold text-white" x-text="tooltip.district"></span>
                  <span class="text-xs px-1.5 py-0.5 rounded font-semibold ml-auto" style="background:rgba(59,130,246,0.2);color:#93c5fd" x-text="tooltip.province"></span>
                </div>
                <div class="text-lg font-black text-white" x-text="tooltip.count + ' customer' + (tooltip.count !== 1 ? 's' : '')"></div>
                <div class="text-xs mt-1.5 space-y-0.5" x-show="tooltip.cities?.length">
                  <div class="text-gray-400 font-medium mb-1">Top cities:</div>
                  <template x-for="c in (tooltip.cities || []).slice(0, 4)" :key="c.city">
                    <div class="flex items-center justify-between gap-3">
                      <span class="text-gray-300 truncate" x-text="c.city"></span>
                      <span class="text-gray-400 font-semibold tabular-nums" x-text="c.count"></span>
                    </div>
                  </template>
                </div>
                <div x-show="!tooltip.count" class="text-xs text-gray-500 mt-1">No customers recorded</div>
              </div>
            </div>

          </div>
        </div>

        <!-- STATS COLUMN -->
        <div class="w-full lg:w-80 flex flex-col border-t lg:border-t-0 lg:border-l" style="border-color:rgba(255,255,255,0.07)">

          <!-- Province legend tabs -->
          <div class="px-4 pt-4 pb-2">
            <div class="flex items-center justify-between mb-3">
              <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Customer Coverage</span>
              <span class="text-xs text-gray-500" x-text="mapData.districts?.length + ' / 25 districts'"></span>
            </div>

            <!-- Province summary pills -->
            <div class="flex flex-wrap gap-1.5 mb-4">
              <template x-for="prov in provinces" :key="prov.name">
                <button @click="filterProvince = filterProvince === prov.name ? '' : prov.name"
                        class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-semibold transition-all"
                        :style="filterProvince === prov.name ? 'background:' + prov.color + ';color:#fff' : 'background:rgba(255,255,255,0.06);color:' + prov.color + ';border:1px solid ' + prov.color + '40'"
                        x-text="prov.short + ' (' + prov.count + ')'">
                </button>
              </template>
            </div>
          </div>

          <!-- District ranked list -->
          <div class="flex-1 overflow-y-auto" style="max-height:340px">
            <div class="px-4 pb-4 space-y-1.5">
              <template x-for="(d, i) in filteredDistricts" :key="d.district">
                <div class="flex items-center gap-3 p-2.5 rounded-xl transition-colors cursor-default group"
                     style="background:rgba(255,255,255,0.04)"
                     @mouseenter="highlightDistrict(d.district)"
                     @mouseleave="highlightDistrict(null)">
                  <!-- Rank -->
                  <div class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                       :style="i < 3 ? 'background:' + districtColor(d.district) + ';color:#fff' : 'background:rgba(255,255,255,0.08);color:#94a3b8'"
                       x-text="i + 1"></div>
                  <!-- District name + province -->
                  <div class="flex-1 min-w-0">
                    <div class="text-sm font-semibold text-gray-200 truncate" x-text="d.district"></div>
                    <div class="text-xs font-medium" :style="'color:' + districtColor(d.district)" x-text="districtProvince(d.district)"></div>
                  </div>
                  <!-- Bar + count -->
                  <div class="flex items-center gap-2 flex-shrink-0">
                    <div class="w-20 h-1.5 rounded-full" style="background:rgba(255,255,255,0.08)">
                      <div class="h-full rounded-full transition-all"
                           :style="'width:' + Math.round((d.count / (filteredDistricts[0]?.count || 1)) * 100) + '%;background:' + districtColor(d.district)"></div>
                    </div>
                    <span class="text-sm font-bold tabular-nums text-gray-200 w-8 text-right" x-text="d.count"></span>
                  </div>
                </div>
              </template>
              <div x-show="filteredDistricts.length === 0" class="text-center text-gray-500 text-sm py-8">No data</div>
            </div>
          </div>

          <!-- Footer stats -->
          <div class="border-t px-4 py-3 grid grid-cols-3 gap-3" style="border-color:rgba(255,255,255,0.07)">
            <div class="text-center">
              <div class="text-xl font-black text-white" x-text="mapData.total ?? 0"></div>
              <div class="text-xs text-gray-500 mt-0.5">Total</div>
            </div>
            <div class="text-center border-x" style="border-color:rgba(255,255,255,0.07)">
              <div class="text-xl font-black" style="color:#3b82f6" x-text="mapData.mapped ?? 0"></div>
              <div class="text-xs text-gray-500 mt-0.5">Mapped</div>
            </div>
            <div class="text-center">
              <div class="text-xl font-black text-amber-400" x-text="mapData.unmapped ?? 0"></div>
              <div class="text-xs text-gray-500 mt-0.5">Unmapped</div>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div><!-- /!loading -->
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function chequeWidget() {
  const _d   = n => { const x = new Date(); x.setDate(x.getDate() + n); return x.toISOString().split('T')[0]; };
  const dateMap = { today: _d(0), tomorrow: _d(1), dayafter: _d(2) };
  return {
    cDay: 'today',
    cBank: '',
    cCustomDate: '',
    cheques: [],
    cLoading: true,
    banks: [],
    showDetail: false,
    viewCheque: null,
    viewLoading: false,

    get activeDate() { return this.cCustomDate || dateMap[this.cDay] || dateMap.today; },
    get dayLabel()   { return { today:'Today', tomorrow:'Tomorrow', dayafter:'Day After' }[this.cDay] || 'Selected Date'; },
    get totalAmount(){ return this.cheques.reduce((s,c) => s + parseFloat(c.amount ?? 0), 0); },

    async init() { await this.load(); },

    async load() {
      this.cLoading = true;
      try {
        const date = this.activeDate;
        let url = `/cheques?from_date=${date}&to_date=${date}&per_page=100`;
        if (this.cBank) url += `&bank_name=${encodeURIComponent(this.cBank)}`;
        const r = await apiFetch(url);
        if (!r) return;
        const d = await r.json();
        this.cheques = d.data ?? d ?? [];
        const bSet = new Set(this.cheques.map(c => c.bank_name).filter(Boolean));
        if (!this.banks.length && bSet.size) this.banks = [...bSet].sort();
      } finally { this.cLoading = false; }
    },

    setDay(day) { this.cDay = day; this.cCustomDate = ''; this.load(); },

    async viewDetail(id) {
      this.showDetail = true;
      this.viewCheque = null;
      this.viewLoading = true;
      try {
        const r = await apiFetch(`/cheques/${id}`);
        if (r) this.viewCheque = await r.json();
      } finally { this.viewLoading = false; }
    },

    statusStyle(status) {
      const m = {
        in_hand:     { bg:'#dbeafe', text:'#1d4ed8', label:'In Hand' },
        deposited:   { bg:'#fef9c3', text:'#92400e', label:'Deposited' },
        transferred: { bg:'#f3e8ff', text:'#7e22ce', label:'Transferred' },
        cleared:     { bg:'#dcfce7', text:'#166534', label:'Cleared' },
        bounced:     { bg:'#fee2e2', text:'#b91c1c', label:'Bounced' },
        cancelled:   { bg:'#f1f5f9', text:'#64748b', label:'Cancelled' },
        returned:    { bg:'#fef3c7', text:'#92400e', label:'Returned' },
      };
      return m[status] ?? { bg:'#f1f5f9', text:'#64748b', label: status ?? '—' };
    },

    partyName(c) {
      return c.customer?.name ?? c.supplier?.name ?? c.party_name ?? '—';
    },
  };
}

function targetWidget() {
  const now  = new Date();
  const months = ['','January','February','March','April','May','June','July','August','September','October','November','December'];
  return {
    tLoading: true,
    activeTab: 'monthly',
    tYear:  now.getFullYear(),
    tMonth: now.getMonth() + 1,
    monthly: [],
    annual:  [],
    get monthName() { return months[this.tMonth]; },
    get activeRows() { return this.activeTab === 'monthly' ? this.monthly : this.annual; },
    get groupedRows() {
      const map = {};
      for (const t of this.activeRows) {
        const key = t.branch_id + '_' + (t.user_id || 'branch');
        if (!map[key]) {
          map[key] = {
            key,
            label:   t.user_id ? (t.user_name || 'Rep') : (t.branch_name || 'Branch'),
            sub:     t.user_id ? (t.branch_name || '') : '',
            is_rep:  !!t.user_id,
            targets: [],
          };
        }
        map[key].targets.push(t);
      }
      return Object.values(map).map(g => {
        const total_tv = g.targets.reduce((s, t) => s + (t.target_value || 0), 0);
        const total_av = g.targets.reduce((s, t) => s + (t.achieved_value || 0), 0);
        g.overall_pct  = total_tv > 0 ? Math.round((total_av / total_tv) * 100) : 0;
        return g;
      });
    },
    typeStyle(type) {
      const map = {
        revenue:       { icon: '💰', label: 'Revenue',       bg: 'background:#eff6ff', text: 'color:#1d4ed8' },
        quantity:      { icon: '📦', label: 'Products Sold', bg: 'background:#f0fdf4', text: 'color:#166534' },
        new_customers: { icon: '👥', label: 'New Customers', bg: 'background:#fdf4ff', text: 'color:#7e22ce' },
      };
      return map[type] ?? { icon: '🎯', label: type, bg: 'background:#f8fafc', text: 'color:#475569' };
    },
    fmtVal(value, type) {
      if (type === 'revenue') return fmtMoney(value);
      const n = Number(value).toLocaleString('en-US', { maximumFractionDigits: 0 });
      return type === 'quantity' ? n + ' units' : n;
    },
    async init() {
      try {
        const r = await apiFetch(`/targets/branch-summary?year=${this.tYear}&month=${this.tMonth}`);
        if (!r) return;
        const d = await r.json();
        const calc = t => ({ ...t,
          target_value:   parseFloat(t.target_value   ?? 0),
          achieved_value: parseFloat(t.achieved_value ?? 0),
          pct: t.target_value > 0
               ? Math.round((parseFloat(t.achieved_value ?? 0) / parseFloat(t.target_value)) * 100)
               : 0,
        });
        this.monthly = (d.monthly ?? []).map(calc);
        this.annual  = (d.annual  ?? []).map(calc);
      } finally { this.tLoading = false; }
    },
  };
}

function dashboard() {
  const _u = JSON.parse(localStorage.getItem('medri_user') || '{}');
  const _isAdmin = !!_u.is_super_admin || (_u.roles ?? []).includes('super_admin') || (_u.roles ?? []).includes('admin');
  return {
    isSuperAdmin: _isAdmin,
    loading: true,
    d: {},
    widgetConfig: {},  // { widget_key: true/false }
    prev: null,
    charts: {},
    compare: false,
    period: 'this_month',
    customFrom: '',
    customTo: '',
    expColors: ['#1B3EB6','#dc2626','#d97706','#059669','#7c3aed','#0891b2','#f97316','#ec4899','#10b981','#6366f1'],

    periods: [
      { key: 'today',        label: 'Today',        icon: '✦' },
      { key: 'this_week',    label: 'This Week',    icon: '📅' },
      { key: 'this_month',   label: 'This Month',   icon: '📅' },
      { key: 'last_month',   label: 'Last Month',   icon: '←' },
      { key: 'this_quarter', label: 'This Quarter', icon: '📅' },
      { key: 'this_year',    label: 'This Year',    icon: '📅' },
      { key: 'custom',       label: 'Custom',       icon: '📅' },
    ],

    get periodLabel() {
      const now = new Date();
      const fmt = (d) => d.toLocaleDateString('en-GB', { day:'numeric', month:'short', year:'numeric' });
      const r = this.dateRange();
      if (this.period === 'custom' && this.customFrom && this.customTo) {
        return fmt(new Date(this.customFrom)) + ' – ' + fmt(new Date(this.customTo));
      }
      return fmt(r.from) + ' – ' + fmt(r.to);
    },

    dateRange() {
      const now   = new Date();
      const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
      let from = new Date(today), to = new Date(today);

      if (this.period === 'today') {
        from = to = today;
      } else if (this.period === 'this_week') {
        const day = today.getDay(); // 0=Sun
        from = new Date(today); from.setDate(today.getDate() - day);
        to   = new Date(from);  to.setDate(from.getDate() + 6);
      } else if (this.period === 'this_month') {
        from = new Date(now.getFullYear(), now.getMonth(), 1);
        to   = new Date(now.getFullYear(), now.getMonth() + 1, 0);
      } else if (this.period === 'last_month') {
        from = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        to   = new Date(now.getFullYear(), now.getMonth(), 0);
      } else if (this.period === 'this_quarter') {
        const q = Math.floor(now.getMonth() / 3);
        from = new Date(now.getFullYear(), q * 3, 1);
        to   = new Date(now.getFullYear(), q * 3 + 3, 0);
      } else if (this.period === 'this_year') {
        from = new Date(now.getFullYear(), 0, 1);
        to   = new Date(now.getFullYear(), 11, 31);
      } else if (this.period === 'custom') {
        from = this.customFrom ? new Date(this.customFrom) : today;
        to   = this.customTo   ? new Date(this.customTo)   : today;
      }
      return { from, to };
    },

    toISO(d) { return d.toISOString().split('T')[0]; },

    setPeriod(key) {
      this.period = key;
      if (key !== 'custom') this.reload();
    },

    widgetVisible(key) {
      if (Object.keys(this.widgetConfig).length === 0) return true; // default show all
      return this.widgetConfig[key] !== false;
    },

    async init() {
      window.addEventListener('branch-switched', () => this.reload());
      // Load widget config for current role (non-blocking)
      apiFetch('/dashboard-widget-settings').then(async r => {
        if (r && r.ok) {
          const data = await r.json();
          const cfg = {};
          (data.widgets ?? []).forEach(w => { cfg[w.key] = w.is_visible; });
          this.widgetConfig = cfg;
        }
      }).catch(() => {});
      await this.load();
    },

    async reload() {
      Object.values(this.charts).forEach(c => { try { c?.destroy(); } catch(e){} });
      this.charts = {};
      await this.load();
    },

    prevRange() {
      const { from, to } = this.dateRange();
      const diff = to - from; // ms
      return {
        from: new Date(from - diff - 86400000),
        to:   new Date(from - 86400000),
      };
    },

    async load() {
      this.loading = true;
      try {
        const { from, to } = this.dateRange();
        const qs = `?from=${this.toISO(from)}&to=${this.toISO(to)}`;
        const r = await apiFetch('/dashboard' + qs);
        if (!r || !r.ok) { this.loading = false; toast('Failed to load dashboard data', 'error'); return; }
        const data = await r.json();

        // Compare: fetch previous period
        if (this.compare) {
          const { from: pf, to: pt } = this.prevRange();
          const rp = await apiFetch(`/dashboard?from=${this.toISO(pf)}&to=${this.toISO(pt)}`);
          this.prev = rp ? await rp.json() : null;
        } else {
          this.prev = null;
        }

        // Set loading=false BEFORE assigning d so the x-show="!loading" wrapper is
        // already visible when $nextTick fires and charts try to measure their canvas.
        this.loading = false;
        this.d = data;
        this.$nextTick(() => this.renderCharts());
      } catch(e) {
        console.error('Dashboard load error:', e);
        this.loading = false;
      }
    },

    delta(key) {
      if (!this.compare || !this.prev?.kpis) return null;
      const cur  = this.d?.kpis?.[key] || 0;
      const prev = this.prev?.kpis?.[key] || 0;
      if (prev === 0) return cur > 0 ? 100 : 0;
      return Math.round(((cur - prev) / prev) * 100);
    },

    renderCharts() {
      this.$nextTick(() => {
        this.renderTrendChart();
        this.renderBestProducts();
        this.renderExpensePie();
        this.renderBranchChart();
      });
    },

    _apexFmt(v) {
      return 'Rs. ' + Number(v).toLocaleString('en-LK', { minimumFractionDigits: 2 });
    },
    _apexYFmt(v) {
      if (v >= 1000000) return 'Rs.' + (v/1000000).toFixed(1) + 'M';
      if (v >= 1000)    return 'Rs.' + (v/1000).toFixed(0) + 'k';
      return 'Rs.' + v;
    },

    renderTrendChart() {
      const el = document.getElementById('trendChart');
      if (!el) return;
      if (this.charts.trend) { try { this.charts.trend.destroy(); } catch(e){} this.charts.trend = null; }
      el.innerHTML = '';
      const data = this.d.daily_revenue || [];
      if (!data.length) return;
      const fmt = d => new Date(d + 'T00:00:00').toLocaleDateString('en-GB', { day: 'numeric', month: 'short' });
      const categories   = data.map(r => fmt(r.date));
      const revenueData  = data.map(r => r.revenue);
      const collectedData = data.map(r => r.collected);
      const fmtY   = v => this._apexYFmt(v);
      const fmtTip = v => this._apexFmt(v);
      this.charts.trend = new ApexCharts(el, {
        chart: { type: 'area', height: 250, toolbar: { show: false }, fontFamily: 'inherit',
                 animations: { enabled: true, speed: 600 }, zoom: { enabled: false } },
        series: [
          { name: 'Revenue',   data: revenueData },
          { name: 'Collected', data: collectedData },
        ],
        colors: ['#6366f1', '#10b981'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.02, stops: [0, 100] } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: [2.5, 2.5] },
        xaxis: {
          categories,
          labels: { style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'inherit' }, rotate: -30, rotateAlways: false },
          axisBorder: { show: false }, axisTicks: { show: false },
          tickAmount: Math.min(categories.length, 15),
        },
        yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '10px', fontFamily: 'inherit' }, formatter: fmtY } },
        grid: { borderColor: '#f1f5f9', padding: { top: 0, right: 12, bottom: 0, left: 4 } },
        tooltip: { y: { formatter: fmtTip }, theme: 'light', shared: true, intersect: false },
        legend: { show: true, position: 'top', horizontalAlign: 'right', fontSize: '11px', fontFamily: 'inherit',
                  labels: { colors: '#64748b' }, markers: { size: 6, shape: 'circle' } },
        markers: { size: 3, colors: ['#6366f1', '#10b981'], strokeColors: '#fff', strokeWidth: 2, hover: { size: 5 } },
      });
      this.charts.trend.render();
    },

    renderBranchChart() {
      const el = document.getElementById('branchPerfChart');
      if (!el || !this.d.branch_stats?.length) return;
      if (this.charts.branch) { this.charts.branch.destroy(); this.charts.branch = null; }

      const labels       = this.d.branch_stats.map(b => b.name);
      const salesData    = this.d.branch_stats.map(b => b.sales_month);
      const outstandData = this.d.branch_stats.map(b => b.outstanding);
      const fmtY = v => this._apexYFmt(v);
      const fmtTip = v => this._apexFmt(v);

      this.charts.branch = new ApexCharts(el, {
        chart: { type: 'bar', height: 220, toolbar: { show: false }, fontFamily: 'inherit',
                 animations: { enabled: true, speed: 350 } },
        series: [
          { name: 'Period Sales',  data: salesData },
          { name: 'Outstanding',   data: outstandData },
        ],
        colors: ['#1B3EB6', '#dc2626'],
        fill: { opacity: [0.85, 0.55] },
        plotOptions: { bar: { borderRadius: 4, columnWidth: '65%', borderRadiusApplication: 'end' } },
        dataLabels: { enabled: false },
        legend: { show: true, position: 'top', fontSize: '11px', fontFamily: 'inherit',
                  labels: { colors: '#64748b' }, markers: { size: 6, shape: 'circle' } },
        xaxis: { categories: labels,
                 labels: { style: { colors: '#64748b', fontSize: '11px', fontFamily: 'inherit' } },
                 axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '10px', fontFamily: 'inherit' },
                           formatter: fmtY } },
        grid: { borderColor: '#f1f5f9', padding: { top: 0, right: 8, bottom: 0, left: 8 } },
        tooltip: { y: { formatter: fmtTip }, theme: 'light' },
      });
      this.charts.branch.render();
    },

    renderBestProducts() {
      const el = document.getElementById('bestProductsChart');
      if (!el || !this.d.best_products?.length) return;
      if (this.charts.products) { this.charts.products.destroy(); this.charts.products = null; }

      const labels = this.d.best_products.map(p => p.name.length > 20 ? p.name.substring(0,20) + '…' : p.name);
      const data   = this.d.best_products.map(p => p.total);
      const maxVal = Math.max(...data) || 1;
      const fmtY   = v => this._apexYFmt(v);
      const fmtTip = v => this._apexFmt(v);

      const fillColors = data.map(v => {
        const ratio = v / maxVal;
        const hex = Math.round(55 + 200 * ratio).toString(16).padStart(2,'0');
        return '#1B3EB6' + hex;
      });

      this.charts.products = new ApexCharts(el, {
        chart: { type: 'bar', height: 220, toolbar: { show: false }, fontFamily: 'inherit',
                 animations: { enabled: true, speed: 350 } },
        series: [{ name: 'Sales', data }],
        colors: fillColors,
        fill: { type: 'solid' },
        plotOptions: { bar: { borderRadius: 6, columnWidth: '58%', distributed: true,
                              borderRadiusApplication: 'end' } },
        dataLabels: { enabled: false },
        legend: { show: false },
        xaxis: { categories: labels,
                 labels: { style: { colors: '#64748b', fontSize: '11px', fontFamily: 'inherit' },
                           rotate: -30, rotateAlways: false, trim: true, maxHeight: 60 },
                 axisBorder: { show: false }, axisTicks: { show: false } },
        yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '10px', fontFamily: 'inherit' },
                           formatter: fmtY } },
        grid: { borderColor: '#f1f5f9', padding: { top: 0, right: 8, bottom: 0, left: 8 } },
        tooltip: { y: { formatter: fmtTip }, theme: 'light' },
      });
      this.charts.products.render();
    },

    renderExpensePie() {
      const el = document.getElementById('expenseChart');
      if (!el || !this.d.expense_categories?.length) return;
      if (this.charts.expenses) { this.charts.expenses.destroy(); this.charts.expenses = null; }

      const colors = this.expColors;
      const labels = this.d.expense_categories.map(c => c.name);
      const data   = this.d.expense_categories.map(c => parseFloat(c.total));
      const fmtTip = v => this._apexFmt(v);

      this.charts.expenses = new ApexCharts(el, {
        chart: { type: 'donut', height: 170, fontFamily: 'inherit',
                 animations: { enabled: true, speed: 350 } },
        series: data,
        labels,
        colors: labels.map((_, i) => colors[i % colors.length]),
        plotOptions: { pie: { donut: { size: '62%', labels: {
          show: true, total: { show: true, label: 'Total', fontSize: '11px', color: '#64748b',
            formatter: w => 'Rs. ' + w.globals.seriesTotals.reduce((a,b)=>a+b,0).toLocaleString('en-LK',{minimumFractionDigits:0}) }
        } } } },
        dataLabels: { enabled: false },
        legend: { show: false },
        stroke: { width: 2, colors: ['#fff'] },
        tooltip: { y: { formatter: fmtTip }, theme: 'light' },
      });
      this.charts.expenses.render();
    },
  };
}

/* ═══════════════════════════════════════════
   CUSTOMER MAP COMPONENT
═══════════════════════════════════════════ */
function customerMap() {

  const DISTRICT_META = {
    'LK.CO':{ en:'Colombo',       province:'Western',       color:'#3b82f6' },
    'LK.GQ':{ en:'Gampaha',       province:'Western',       color:'#3b82f6' },
    'LK.KT':{ en:'Kalutara',      province:'Western',       color:'#3b82f6' },
    'LK.KY':{ en:'Kandy',         province:'Central',       color:'#10b981' },
    'LK.MT':{ en:'Matale',        province:'Central',       color:'#10b981' },
    'LK.NW':{ en:'Nuwara Eliya',  province:'Central',       color:'#10b981' },
    'LK.GL':{ en:'Galle',         province:'Southern',      color:'#14b8a6' },
    'LK.MH':{ en:'Matara',        province:'Southern',      color:'#14b8a6' },
    'LK.HB':{ en:'Hambantota',    province:'Southern',      color:'#14b8a6' },
    'LK.JA':{ en:'Jaffna',        province:'Northern',      color:'#8b5cf6' },
    'LK.KL':{ en:'Kilinochchi',   province:'Northern',      color:'#8b5cf6' },
    'LK.MB':{ en:'Mannar',        province:'Northern',      color:'#8b5cf6' },
    'LK.VA':{ en:'Vavuniya',      province:'Northern',      color:'#8b5cf6' },
    'LK.MP':{ en:'Mullaitivu',    province:'Northern',      color:'#8b5cf6' },
    'LK.TC':{ en:'Trincomalee',   province:'Eastern',       color:'#ec4899' },
    'LK.BC':{ en:'Batticaloa',    province:'Eastern',       color:'#ec4899' },
    'LK.AP':{ en:'Ampara',        province:'Eastern',       color:'#ec4899' },
    'LK.KG':{ en:'Kurunegala',    province:'North Western', color:'#6366f1' },
    'LK.PX':{ en:'Puttalam',      province:'North Western', color:'#6366f1' },
    'LK.AD':{ en:'Anuradhapura',  province:'North Central', color:'#0ea5e9' },
    'LK.PR':{ en:'Polonnaruwa',   province:'North Central', color:'#0ea5e9' },
    'LK.BD':{ en:'Badulla',       province:'Uva',           color:'#f59e0b' },
    'LK.MJ':{ en:'Monaragala',    province:'Uva',           color:'#f59e0b' },
    'LK.RN':{ en:'Ratnapura',     province:'Sabaragamuwa',  color:'#f97316' },
    'LK.KE':{ en:'Kegalle',       province:'Sabaragamuwa',  color:'#f97316' },
  };

  const PROVINCE_META = [
    { name:'Northern',       short:'North',     color:'#8b5cf6' },
    { name:'North Central',  short:'N.Central', color:'#0ea5e9' },
    { name:'North Western',  short:'N.West',    color:'#6366f1' },
    { name:'Eastern',        short:'East',      color:'#ec4899' },
    { name:'Central',        short:'Central',   color:'#10b981' },
    { name:'Western',        short:'West',      color:'#3b82f6' },
    { name:'Sabaragamuwa',   short:'Sabara',    color:'#f97316' },
    { name:'Uva',            short:'Uva',       color:'#f59e0b' },
    { name:'Southern',       short:'South',     color:'#14b8a6' },
  ];

  /* Build a name→meta lookup for fast reverse-lookup by district English name */
  const NAME_TO_META = {};
  for (const [id, m] of Object.entries(DISTRICT_META)) {
    NAME_TO_META[m.en.toLowerCase()] = { ...m, id };
  }

  return {
    mapLoading: true,
    mapData: { districts: [], total: 0, mapped: 0, unmapped: 0 },
    filterProvince: '',
    highlighted: null,
    tooltip: { visible: false, district: '', province: '', count: 0, color: '', cities: [], x: 0, y: 0 },

    /* _apiData holds the raw districts array from the API so renderMap can use it */
    _apiData: [],

    get provinces() {
      return PROVINCE_META.map(p => ({
        ...p,
        count: this.mapData.districts
          .filter(d => this._metaFor(d.district)?.province === p.name)
          .reduce((s, d) => s + d.count, 0),
      }));
    },

    get filteredDistricts() {
      let list = [...this.mapData.districts].sort((a, b) => b.count - a.count);
      if (this.filterProvince) {
        list = list.filter(d => this._metaFor(d.district)?.province === this.filterProvince);
      }
      return list;
    },

    _metaFor(name) {
      return NAME_TO_META[name?.toLowerCase()] ?? null;
    },

    districtProvince(name) {
      return this._metaFor(name)?.province ?? '';
    },

    districtColor(name) {
      return this._metaFor(name)?.color ?? '#475569';
    },

    async init() {
      try {
        const [apiData, topoData] = await Promise.all([
          apiFetch('/dashboard/customer-map').then(r => r.json()),
          fetch('https://raw.githubusercontent.com/markmarkoh/datamaps/master/src/js/data/lka.topo.json').then(r => r.json()),
        ]);
        this.mapData   = apiData;
        this._apiData  = apiData.districts ?? [];
        this.renderMap(topoData, this._apiData);
      } catch (e) {
        console.warn('Customer map failed', e);
      } finally {
        this.mapLoading = false;
      }
    },

    renderMap(topoData, apiDistricts) {
      const W = 320, H = 440;
      const svg = d3.select('#lk-map-svg');
      svg.selectAll('*').remove();

      /* Defs */
      const defs = svg.append('defs');
      defs.append('filter').attr('id','map-glow')
        .html('<feGaussianBlur stdDeviation="2.5" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>');
      defs.append('radialGradient').attr('id','sea-bg')
        .attr('cx','50%').attr('cy','50%').attr('r','50%')
        .html('<stop offset="0%" stop-color="#1e3a5f" stop-opacity="0.25"/><stop offset="100%" stop-color="#0d1a3a" stop-opacity="0"/>');

      /* Ocean background */
      svg.append('ellipse')
        .attr('cx', W/2).attr('cy', H/2)
        .attr('rx', W * 0.9).attr('ry', H * 0.85)
        .attr('fill','url(#sea-bg)');

      /* Build customer count lookup keyed by English district name (lower) */
      const countMap = {};
      const citiesMap = {};
      for (const row of apiDistricts) {
        const key = row.district?.toLowerCase();
        if (key) { countMap[key] = row.count; citiesMap[key] = row.cities ?? []; }
      }
      const maxCount = Math.max(1, ...Object.values(countMap));

      /* TopoJSON → GeoJSON */
      const objKey = Object.keys(topoData.objects)[0];
      const geo = topojson.feature(topoData, topoData.objects[objKey]);

      /* Projection fitted to SVG viewport */
      const projection = d3.geoMercator().fitSize([W, H], geo);
      const path = d3.geoPath().projection(projection);

      /* Province color per feature */
      const colorFor = (f) => {
        const id   = f.id ?? f.properties?.iso;
        const meta = DISTRICT_META[id];
        if (meta) return meta.color;
        /* fallback: try matching by name */
        const name = (f.properties?.name ?? '').toLowerCase();
        return NAME_TO_META[name]?.color ?? '#334155';
      };

      const nameFor = (f) => {
        const id = f.id ?? f.properties?.iso;
        return DISTRICT_META[id]?.en ?? f.properties?.name ?? '';
      };

      const self = this;

      /* Draw district paths */
      svg.append('g').attr('class','districts')
        .selectAll('path')
        .data(geo.features)
        .join('path')
          .attr('d', path)
          .attr('fill', f => {
            const name = nameFor(f).toLowerCase();
            const cnt  = countMap[name] ?? 0;
            return colorFor(f);
          })
          .attr('fill-opacity', f => {
            const name = nameFor(f).toLowerCase();
            return (countMap[name] ?? 0) > 0 ? 0.82 : 0.22;
          })
          .attr('stroke','#0f172a')
          .attr('stroke-width', 0.8)
          .style('cursor','pointer')
          .on('mouseenter', function(event, f) {
            d3.select(this).attr('fill-opacity', 1).attr('stroke-width', 1.5).attr('stroke','#fff');
            const name  = nameFor(f);
            const key   = name.toLowerCase();
            const id    = f.id ?? f.properties?.iso;
            const meta  = DISTRICT_META[id] ?? NAME_TO_META[key] ?? {};
            const cnt   = countMap[key] ?? 0;
            const [cx, cy] = path.centroid(f);
            self.tooltip = {
              visible: true,
              district: name,
              province: meta.province ?? '',
              count: cnt,
              color: meta.color ?? '#475569',
              cities: citiesMap[key] ?? [],
              x: cx,
              y: cy,
            };
          })
          .on('mouseleave', function(event, f) {
            const name  = nameFor(f).toLowerCase();
            const cnt   = countMap[name] ?? 0;
            d3.select(this)
              .attr('fill-opacity', cnt > 0 ? 0.82 : 0.22)
              .attr('stroke-width', 0.8)
              .attr('stroke','#0f172a');
            self.tooltip.visible = false;
          });

      /* District boundary lines (subtle inner strokes already handled by path stroke) */

      /* Bubble markers for districts that have customers */
      const bubbleGroup = svg.append('g').attr('class','bubbles').attr('pointer-events','none');
      for (const f of geo.features) {
        const name  = nameFor(f);
        const key   = name.toLowerCase();
        const cnt   = countMap[key] ?? 0;
        if (cnt === 0) continue;

        const [cx, cy] = path.centroid(f);
        const id   = f.id ?? f.properties?.iso;
        const meta = DISTRICT_META[id] ?? NAME_TO_META[key] ?? {};
        const col  = meta.color ?? '#3b82f6';
        const r    = Math.max(6, Math.round(6 + (cnt / maxCount) * 16));

        /* Pulse ring */
        bubbleGroup.append('circle')
          .attr('cx', cx).attr('cy', cy)
          .attr('r', r + 7)
          .attr('fill', col).attr('fill-opacity', 0.15)
          .attr('class','bubble-pulse');

        /* Inner ring */
        bubbleGroup.append('circle')
          .attr('cx', cx).attr('cy', cy)
          .attr('r', r + 3)
          .attr('fill', col).attr('fill-opacity', 0.28);

        /* Main bubble */
        bubbleGroup.append('circle')
          .attr('cx', cx).attr('cy', cy)
          .attr('r', r)
          .attr('fill', col)
          .attr('stroke','rgba(255,255,255,0.55)')
          .attr('stroke-width', 1.2)
          .attr('filter','url(#map-glow)');

        /* Count label inside bubble (only if large enough) */
        if (r >= 10) {
          bubbleGroup.append('text')
            .attr('x', cx).attr('y', cy + 3.5)
            .attr('text-anchor','middle')
            .attr('font-size', Math.min(r - 2, 11))
            .attr('font-weight','700')
            .attr('fill','#fff')
            .attr('pointer-events','none')
            .text(cnt);
        }
      }

      /* Compass rose */
      const cr = svg.append('g').attr('transform','translate(298,26)').attr('opacity',0.55);
      cr.append('circle').attr('r',11).attr('fill','none').attr('stroke','#475569').attr('stroke-width',0.8);
      cr.append('path').attr('d','M0,-9 L1.8,-2 L0,-3.5 L-1.8,-2 Z').attr('fill','#93c5fd');
      cr.append('path').attr('d','M0,9 L1.8,2 L0,3.5 L-1.8,2 Z').attr('fill','#475569');
      cr.append('path').attr('d','M-9,0 L-2,1.8 L-3.5,0 L-2,-1.8 Z').attr('fill','#475569');
      cr.append('path').attr('d','M9,0 L2,1.8 L3.5,0 L2,-1.8 Z').attr('fill','#475569');
      cr.append('text').attr('y',-12).attr('text-anchor','middle').attr('font-size',6).attr('fill','#93c5fd').attr('font-weight','700').text('N');

      /* Subtle grid lines */
      const grid = svg.append('g').attr('stroke','#ffffff').attr('stroke-width',0.3).attr('opacity',0.06);
      [H*0.2, H*0.4, H*0.6, H*0.8].forEach(y => grid.append('line').attr('x1',0).attr('x2',W).attr('y1',y).attr('y2',y));
      [W*0.25, W*0.5, W*0.75].forEach(x => grid.append('line').attr('x1',x).attr('x2',x).attr('y1',0).attr('y2',H));
    },

    highlightDistrict(name) {
      this.highlighted = name;
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\dashboard\index.blade.php ENDPATH**/ ?>