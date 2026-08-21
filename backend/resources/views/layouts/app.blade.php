<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'MedRi ERP')</title>
  <script>
    if (!localStorage.getItem('medri_token') && !sessionStorage.getItem('medri_token') && !window.location.pathname.includes('/login')) {
      window.location.replace('{{ url('/login') }}');
    }
    if (localStorage.getItem('medri_dark') === 'true') document.documentElement.classList.add('dark');
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: { extend: { colors: {
        primary: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#4f6fe0',600:'#1B3EB6',700:'#0D2272',800:'#091d5a',900:'#070d1f' },
        danger:  { 50:'#fff1f2',500:'#E31E24',600:'#c41a20' },
        success: { 50:'#f0fdf4',500:'#22A845',600:'#1a8a38' },
        warning: { 50:'#fffbeb',500:'#f59e0b',600:'#d97706' },
      }}}
    }
  </script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
  <style>
    * { box-sizing: border-box; }
    body { font-family: 'Inter', system-ui, sans-serif; }
    [x-cloak] { display: none !important; }

    /* ─────────────── PRINT ─────────────── */
    @media print {
      aside, header, .no-print { display: none !important; }
      body, html { overflow: visible !important; background: #fff !important; }
      .flex-1.flex.flex-col { width: 100vw !important; overflow: visible !important; }
      main { padding: 8px 16px !important; overflow: visible !important; }
      .card { box-shadow: none !important; border: 1px solid #e5e7eb !important; page-break-inside: avoid; }
      .print-header { display: flex !important; }
      table { font-size: 11px !important; }
      th, td { padding: 4px 6px !important; }
    }
    .print-header { display: none; }

    /* ─────────────── SIDEBAR ─────────────── */
    .sidebar {
      background: linear-gradient(160deg, #0e1628 0%, #080c18 60%, #060a14 100%);
      border-right: 1px solid rgba(255,255,255,0.05);
    }

    /* Nav item icon box */
    .n-icon {
      width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
      background: rgba(255,255,255,0.06);
      transition: background 0.2s, box-shadow 0.2s;
      color: #7a8fa8;
    }

    /* Group button (expandable section header) */
    .n-group {
      display: flex; align-items: center; gap: 10px;
      padding: 5px 8px 5px 6px; border-radius: 9px;
      width: 100%; border: none; cursor: pointer; background: transparent;
      font-size: 13px; font-weight: 500; color: #8a9db5;
      transition: all 0.18s; text-align: left;
    }
    .n-group:hover { background: rgba(255,255,255,0.06); color: #c0cfe8; }
    .n-group:hover .n-icon { background: rgba(255,255,255,0.1); color: #c0cfe8; }
    .n-group.sec-active { color: #93c5fd; }
    .n-group.sec-active .n-icon { background: rgba(27,62,182,0.35); color: #93c5fd; box-shadow: 0 0 12px rgba(27,62,182,0.3); }

    /* Direct nav link */
    .n-link {
      display: flex; align-items: center; gap: 10px;
      padding: 5px 8px 5px 6px; border-radius: 9px;
      font-size: 13px; font-weight: 500; color: #8a9db5;
      text-decoration: none; transition: all 0.18s;
    }
    .n-link:hover { background: rgba(255,255,255,0.06); color: #c0cfe8; }
    .n-link:hover .n-icon { background: rgba(255,255,255,0.1); color: #c0cfe8; }
    .n-link.active { color: #e2e8f0; background: rgba(27,62,182,0.18); }
    .n-link.active .n-icon { background: #1B3EB6; color: #fff; box-shadow: 0 2px 12px rgba(27,62,182,0.5); }

    /* Sub-item list container */
    .n-sub-list {
      position: relative; margin: 3px 0 3px 21px;
      padding-left: 15px;
    }
    .n-sub-list::before {
      content: ''; position: absolute; left: 0; top: 4px; bottom: 4px;
      width: 1px; background: linear-gradient(180deg, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0.03) 100%);
      border-radius: 1px;
    }

    /* Sub-item link */
    .n-sub {
      display: flex; align-items: center; gap: 8px;
      padding: 5px 8px; border-radius: 7px;
      font-size: 12.5px; font-weight: 400; color: #6b7fa0;
      text-decoration: none; transition: all 0.15s;
      position: relative;
    }
    .n-sub::before {
      content: ''; width: 5px; height: 5px; border-radius: 50%;
      background: #1e293b; flex-shrink: 0;
      transition: background 0.15s, transform 0.15s;
    }
    .n-sub:hover { color: #8aa0be; background: rgba(255,255,255,0.04); }
    .n-sub:hover::before { background: #3b82f6; }
    .n-sub.active { color: #60a5fa; font-weight: 500; }
    .n-sub.active::before { background: #3b82f6; transform: scale(1.3); box-shadow: 0 0 6px rgba(59,130,246,0.6); }

    /* Chevron */
    .n-chevron { margin-left: auto; transition: transform 0.22s cubic-bezier(0.4,0,0.2,1); color: #4a5f7a; }
    .n-chevron.open { transform: rotate(90deg); }

    /* Collapsed icon-only mode */
    .n-link.collapsed-link, .n-group.collapsed-btn { justify-content: center; padding: 7px; }

    /* ─────────────── CARDS ─────────────── */
    .card { background: #fff; border-radius: 12px; border: 1px solid #f1f5f9; box-shadow: 0 1px 4px rgba(0,0,0,0.06), 0 6px 20px rgba(0,0,0,0.04); }
    .dark .card { background: #1e2533; border-color: #2d3748; }

    /* ─────────────── BUTTONS ─────────────── */
    .btn, .btn-primary, .btn-secondary, .btn-danger {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 8px 16px; border-radius: 8px; font-weight: 500; font-size: 13.5px;
      transition: all 0.15s; outline: none; cursor: pointer; border: none; text-decoration: none;
    }
    .btn { background: #f1f5f9; color: #374151; }
    .btn-primary { background: #1B3EB6; color: #fff; box-shadow: 0 2px 8px rgba(27,62,182,0.3); }
    .btn-primary:hover { background: #0D2272; box-shadow: 0 4px 12px rgba(27,62,182,0.4); transform: translateY(-1px); }
    .btn-secondary { background: #f1f5f9; color: #374151; }
    .btn-secondary:hover { background: #e2e8f0; }
    .dark .btn-secondary { background: #2d3748; color: #e2e8f0; }
    .dark .btn-secondary:hover { background: #374151; }
    .btn-danger { background: #E31E24; color: #fff; box-shadow: 0 2px 8px rgba(227,30,36,0.3); }
    .btn-danger:hover { background: #c41a20; transform: translateY(-1px); }

    /* ─────────────── BADGES ─────────────── */
    .badge { display: inline-flex; align-items: center; padding: 2px 10px; border-radius: 999px; font-size: 11px; font-weight: 600; }
    .badge-success { background: #f0fdf4; color: #15803d; }
    .dark .badge-success { background: rgba(34,168,69,0.15); color: #4ade80; }
    .badge-danger  { background: #fff1f2; color: #be123c; }
    .dark .badge-danger  { background: rgba(227,30,36,0.15); color: #f87171; }
    .badge-warning { background: #fffbeb; color: #b45309; }
    .dark .badge-warning { background: rgba(245,158,11,0.15); color: #fbbf24; }
    .badge-gray    { background: #e2e8f0; color: #475569; }
    .dark .badge-gray    { background: #374151; color: #cbd5e1; }
    .badge-primary { background: #eef2ff; color: #1B3EB6; }
    .dark .badge-primary { background: rgba(27,62,182,0.15); color: #a5b4fc; }

    /* ─────────────── SEARCHABLE DROPDOWN ─────────────── */
    .search-dd { position:relative; }
    .search-dd-menu {
      position:absolute; left:0; right:0; top:calc(100% + 4px);
      background:#fff; border:1px solid #e2e8f0; border-radius:12px;
      box-shadow:0 8px 30px rgba(0,0,0,0.12); z-index:70; overflow:hidden;
    }
    .dark .search-dd-menu { background:#1e2533; border-color:#2d3748; }
    .search-dd-item { display:flex; align-items:center; gap:8px; width:100%; padding:9px 12px; text-align:left; transition:background 0.1s; cursor:pointer; border:none; background:transparent; }
    .search-dd-item:hover { background:#eef2ff; }
    .dark .search-dd-item:hover { background:rgba(27,62,182,0.12); }
    .search-dd-item.active { background:#eef2ff; }
    .dark .search-dd-item.active { background:rgba(27,62,182,0.15); }

    /* ─────────────── TABLE ─────────────── */
    .table-hd { padding: 11px 16px; text-align: left; font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.06em; }
    .table-td { padding: 12px 16px; font-size: 13.5px; color: #374151; }
    .dark .table-td { color: #cbd5e1; }

    /* ─────────────── FORM ─────────────── */
    .input { width: 100%; padding: 9px 12px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13.5px; color: #111827; transition: all 0.15s; outline: none; }
    .input:focus { border-color: #1B3EB6; box-shadow: 0 0 0 3px rgba(27,62,182,0.12); }
    .dark .input { background: #1e2533; border-color: #2d3748; color: #f1f5f9; }
    .label { display: block; font-size: 11px; font-weight: 600; color: #64748b; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.06em; }

    /* ─────────────── SCROLLBAR ─────────────── */
    ::-webkit-scrollbar { width: 4px; height: 4px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 4px; }
    .main-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; }
    .dark .main-scroll::-webkit-scrollbar-thumb { background: #334155; }
  </style>
  @stack('head')
</head>

@php
$sec = '';
if (request()->is('invoices*') || request()->is('proforma-invoices*'))                                    $sec = 'sales';
elseif (request()->is('cheques*') || request()->routeIs('cheques.calendar'))                              $sec = 'cheques';
elseif (request()->is('purchase-orders*') || request()->is('grns*') || request()->is('purchase-returns*'))  $sec = 'procurement';
elseif (request()->is('customers*') || request()->is('suppliers*'))                                        $sec = 'partners';
elseif (request()->is('products*') || request()->is('inventory*'))                                         $sec = 'inventory';
elseif (request()->is('services*'))                                                                        $sec = 'services';
elseif (request()->is('expenses*'))                                                                        $sec = 'finance';
elseif (request()->is('accounting*'))                                                                      $sec = 'accounting';
elseif (request()->is('reports*'))                                                                         $sec = 'reports';
elseif (request()->is('hr*'))                                                                              $sec = 'hr';
elseif (request()->is('access-control*') || request()->is('settings/branches*') || request()->is('settings/banks*') || request()->is('settings/districts-cities*') || request()->is('settings/dashboard-widgets*')) $sec = 'admin';
@endphp

<body class="bg-slate-50 dark:bg-gray-900 text-gray-900 dark:text-white">

<div class="flex h-screen overflow-hidden" x-data="layout()" x-init="init()">

  <!-- ══════════════════ SIDEBAR ══════════════════ -->
  <aside class="sidebar flex flex-col flex-shrink-0 transition-all duration-300 overflow-hidden"
         :class="sidebarOpen ? 'w-[230px]' : 'w-0 lg:w-[58px]'">
 
    <!-- Logo -->
    <div class="flex items-center flex-shrink-0 transition-all duration-300 px-3"
         style="min-height:60px; border-bottom:1px solid rgba(255,255,255,0.05)"
         :class="'justify-center'">
      <!-- Collapsed: small ECG icon -->
      <div x-show="!sidebarOpen" class="w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0"
           style="background:linear-gradient(135deg,rgba(27,62,182,0.6),rgba(13,34,114,0.8));box-shadow:0 2px 10px rgba(27,62,182,0.4)">
        <svg viewBox="0 0 24 24" fill="none" stroke="#22A845" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px">
          <polyline points="2,12 5,12 7,6 9,18 11,10 13,14 15,12 22,12"/>
        </svg>
      </div>
      <!-- Expanded: full logo image -->
      <div x-show="sidebarOpen" class="py-2 flex justify-center w-full">
        <img src="{{ asset('backend/public/images/medri-logo.png') }}" alt="MedRi" style="height:40px;width:auto;object-fit:contain" />
      </div>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3 px-2 space-y-0.5">

      {{-- Dashboard --}}
      <a href="{{ url('/') }}"
         x-show="hasPerm('dashboard.view')"
         class="n-link {{ request()->is('/') || request()->is('dashboard*') ? 'active' : '' }}"
         :class="sidebarOpen ? '' : 'collapsed-link'">
        <div class="n-icon">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/>
            <rect x="14" y="14" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/>
          </svg>
        </div>
        <span x-show="sidebarOpen" class="text-[13px]">Dashboard</span>
      </a>

      {{-- SALES --}}
      <div x-show="hasPerm('invoices.view')">
        <button @click="sidebarOpen ? toggle('sales') : null"
                class="n-group {{ $sec === 'sales' ? 'sec-active' : '' }}"
                :class="sidebarOpen ? '' : 'collapsed-btn'">
          <div class="n-icon">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
          </div>
          <span x-show="sidebarOpen" class="flex-1 text-[13px]">Sales</span>
          <svg x-show="sidebarOpen" class="n-chevron w-3.5 h-3.5" :class="{'open': open.sales}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <div x-show="open.sales && sidebarOpen" class="n-sub-list">
          <a href="{{ url('/invoices') }}"          class="n-sub {{ request()->is('invoices*')          ? 'active' : '' }}">Invoices</a>
          <a href="{{ url('/proforma-invoices') }}" x-show="hasPerm('proforma.view')"
             class="n-sub {{ request()->is('proforma-invoices*') ? 'active' : '' }}">Proforma Invoices</a>
          <a href="{{ url('/sales-returns') }}"     class="n-sub {{ request()->is('sales-returns*')     ? 'active' : '' }}">Sales Returns</a>
        </div>
      </div>

      {{-- MANAGE CHEQUE --}}
      <div x-show="hasPerm('cheques.view')">
        <button @click="sidebarOpen ? toggle('cheques') : null"
                class="n-group {{ $sec === 'cheques' ? 'sec-active' : '' }}"
                :class="sidebarOpen ? '' : 'collapsed-btn'">
          <div class="n-icon">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
          </div>
          <span x-show="sidebarOpen" class="flex-1 text-[13px]">Manage Cheque</span>
          <svg x-show="sidebarOpen" class="n-chevron w-3.5 h-3.5" :class="{'open': open.cheques}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <div x-show="open.cheques && sidebarOpen" class="n-sub-list">
          <a href="{{ url('/cheques') }}"          class="n-sub {{ request()->routeIs('cheques.index')    ? 'active' : '' }}">Manage Cheque</a>
          <a href="{{ url('/cheques/calendar') }}" class="n-sub {{ request()->routeIs('cheques.calendar') ? 'active' : '' }}">Cheque Calendar</a>
          <a href="{{ url('/cheques/history') }}"  class="n-sub {{ request()->routeIs('cheques.history')  ? 'active' : '' }}">Track Cheque</a>
        </div>
      </div>

      {{-- PROCUREMENT --}}
      <div x-show="hasPerm('purchase_orders.view')">
        <button @click="sidebarOpen ? toggle('procurement') : null"
                class="n-group {{ $sec === 'procurement' ? 'sec-active' : '' }}"
                :class="sidebarOpen ? '' : 'collapsed-btn'">
          <div class="n-icon">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
          </div>
          <span x-show="sidebarOpen" class="flex-1 text-[13px]">Purchase</span>
          <svg x-show="sidebarOpen" class="n-chevron w-3.5 h-3.5" :class="{'open': open.procurement}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <div x-show="open.procurement && sidebarOpen" class="n-sub-list">
          <a href="{{ url('/purchase-orders') }}" class="n-sub {{ request()->is('purchase-orders*') ? 'active' : '' }}">Supplier Invoices</a>
          <a href="{{ url('/purchase-returns') }}" class="n-sub {{ request()->is('purchase-returns*') ? 'active' : '' }}">Purchase Returns</a>
        </div>
      </div>

      {{-- CUSTOMER & SUPPLIER --}}
      <div x-show="hasPerm('customers.view')">
        <button @click="sidebarOpen ? toggle('partners') : null"
                class="n-group {{ $sec === 'partners' ? 'sec-active' : '' }}"
                :class="sidebarOpen ? '' : 'collapsed-btn'">
          <div class="n-icon">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
            </svg>
          </div>
          <span x-show="sidebarOpen" class="flex-1 text-[13px]">Customer & Supplier</span>
          <svg x-show="sidebarOpen" class="n-chevron w-3.5 h-3.5" :class="{'open': open.partners}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <div x-show="open.partners && sidebarOpen" class="n-sub-list">
          <a href="{{ url('/customers') }}" class="n-sub {{ request()->is('customers*') ? 'active' : '' }}">Customers</a>
          <a href="{{ url('/suppliers') }}" class="n-sub {{ request()->is('suppliers*') ? 'active' : '' }}">Suppliers</a>
        </div>
      </div>

      {{-- INVENTORY --}}
      <div x-show="hasPerm('inventory.view')">
        <button @click="sidebarOpen ? toggle('inventory') : null"
                class="n-group {{ $sec === 'inventory' ? 'sec-active' : '' }}"
                :class="sidebarOpen ? '' : 'collapsed-btn'">
          <div class="n-icon">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
          </div>
          <span x-show="sidebarOpen" class="flex-1 text-[13px]">Inventory</span>
          <svg x-show="sidebarOpen" class="n-chevron w-3.5 h-3.5" :class="{'open': open.inventory}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <div x-show="open.inventory && sidebarOpen" class="n-sub-list">
          <a href="{{ url('/products') }}"              class="n-sub {{ request()->is('products*') && !request()->is('products/categories*') ? 'active' : '' }}">Products</a>
          <a href="{{ url('/products/categories') }}"   class="n-sub {{ request()->is('products/categories*')   ? 'active' : '' }}">Categories</a>
          <a href="{{ url('/inventory/transfers') }}"   class="n-sub {{ request()->is('inventory/transfers*')   ? 'active' : '' }}">Transfers</a>
          <a href="{{ url('/inventory/adjustments') }}" class="n-sub {{ request()->is('inventory/adjustments*') ? 'active' : '' }}">Adjustments</a>
          <a href="{{ url('/inventory/low-stock') }}"   class="n-sub {{ request()->is('inventory/low-stock*')   ? 'active' : '' }}">Low Stock</a>
        </div>
      </div>

      {{-- SERVICE --}}
      <div x-show="hasPerm('services.view')">
        <button @click="sidebarOpen ? toggle('services') : null"
                class="n-group {{ $sec === 'services' ? 'sec-active' : '' }}"
                :class="sidebarOpen ? '' : 'collapsed-btn'">
          <div class="n-icon">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
          <span x-show="sidebarOpen" class="flex-1 text-[13px]">Service</span>
          <svg x-show="sidebarOpen" class="n-chevron w-3.5 h-3.5" :class="{'open': open.services}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <div x-show="open.services && sidebarOpen" class="n-sub-list">
          <a href="{{ url('/services/categories') }}" class="n-sub {{ request()->is('services/categories*') ? 'active' : '' }}">Service Category</a>
          <a href="{{ url('/services') }}"             class="n-sub {{ request()->is('services*') && !request()->is('services/categories*') ? 'active' : '' }}">Manage Service</a>
        </div>
      </div>

      {{-- EXPENSES --}}
      <div x-show="hasPerm('expenses.view')">
        <button @click="sidebarOpen ? toggle('finance') : null"
                class="n-group {{ $sec === 'finance' ? 'sec-active' : '' }}"
                :class="sidebarOpen ? '' : 'collapsed-btn'">
          <div class="n-icon">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <span x-show="sidebarOpen" class="flex-1 text-[13px]">Expenses</span>
          <svg x-show="sidebarOpen" class="n-chevron w-3.5 h-3.5" :class="{'open': open.finance}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <div x-show="open.finance && sidebarOpen" class="n-sub-list">
          <a href="{{ url('/expenses') }}" class="n-sub {{ request()->is('expenses*') ? 'active' : '' }}">Manage Expenses</a>
        </div>
      </div>

      {{-- TARGETS --}}
      <a href="{{ url('/targets') }}" x-show="hasPerm('targets.view')"
         class="n-link {{ request()->is('targets*') ? 'active' : '' }}"
         :class="sidebarOpen ? '' : 'collapsed-link'">
        <div class="n-icon">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1" fill="currentColor"/>
          </svg>
        </div>
        <span x-show="sidebarOpen" class="text-[13px]">Targets</span>
      </a>

      {{-- ACCOUNTING --}}
      <div x-show="hasPerm('accounting.view')">
        <button @click="sidebarOpen ? toggle('accounting') : null"
                class="n-group {{ $sec === 'accounting' ? 'sec-active' : '' }}"
                :class="sidebarOpen ? '' : 'collapsed-btn'">
          <div class="n-icon">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </div>
          <span x-show="sidebarOpen" class="flex-1 text-[13px]">Accounting</span>
          <svg x-show="sidebarOpen" class="n-chevron w-3.5 h-3.5" :class="{'open': open.accounting}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <div x-show="open.accounting && sidebarOpen" class="n-sub-list">
          <a href="{{ url('/accounting/chart-of-accounts') }}" class="n-sub {{ request()->is('accounting/chart-of-accounts*') ? 'active' : '' }}">Chart of Accounts</a>
          <a href="{{ url('/accounting/journal') }}"           class="n-sub {{ request()->is('accounting/journal*')           ? 'active' : '' }}">Journal</a>
          <a href="{{ url('/accounting/trial-balance') }}"     class="n-sub {{ request()->is('accounting/trial-balance*')     ? 'active' : '' }}">Trial Balance</a>
          <a href="{{ url('/accounting/profit-loss') }}"       class="n-sub {{ request()->is('accounting/profit-loss*')       ? 'active' : '' }}">Profit &amp; Loss</a>
          <a href="{{ url('/accounting/balance-sheet') }}"     class="n-sub {{ request()->is('accounting/balance-sheet*')     ? 'active' : '' }}">Balance Sheet</a>
          <a href="{{ url('/accounting/ledger') }}"            class="n-sub {{ request()->is('accounting/ledger*')            ? 'active' : '' }}">General Ledger</a>
          <a href="{{ url('/accounting/settings') }}"          class="n-sub {{ request()->is('accounting/settings*')          ? 'active' : '' }}">Settings</a>
        </div>
      </div>

      {{-- CALENDAR --}}
      <a href="{{ url('/calendar') }}" x-show="hasPerm('calendar.view')"
         class="n-link {{ request()->is('calendar*') ? 'active' : '' }}"
         :class="sidebarOpen ? '' : 'collapsed-link'">
        <div class="n-icon">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
          </svg>
        </div>
        <span x-show="sidebarOpen" class="text-[13px]">Calendar</span>
      </a>

      {{-- TASKS --}}
      <a href="{{ url('/tasks') }}" x-show="hasPerm('tasks.view')"
         class="n-link {{ request()->is('tasks*') ? 'active' : '' }}"
         :class="sidebarOpen ? '' : 'collapsed-link'">
        <div class="n-icon">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
          </svg>
        </div>
        <span x-show="sidebarOpen" class="text-[13px]">Tasks</span>
      </a>

      {{-- REPORTS --}}
      <div x-show="hasPerm('reports.view')">
        <button @click="sidebarOpen ? toggle('reports') : null"
                class="n-group {{ $sec === 'reports' ? 'sec-active' : '' }}"
                :class="sidebarOpen ? '' : 'collapsed-btn'">
          <div class="n-icon">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
          </div>
          <span x-show="sidebarOpen" class="flex-1 text-[13px]">Reports</span>
          <svg x-show="sidebarOpen" class="n-chevron w-3.5 h-3.5" :class="{'open': open.reports}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <div x-show="open.reports && sidebarOpen" class="n-sub-list">
          <a href="{{ url('/reports') }}"                class="n-sub {{ request()->is('reports') ? 'active' : '' }}">All Reports</a>
          <a href="{{ url('/reports/sales') }}"          class="n-sub {{ request()->is('reports/sales*')          ? 'active' : '' }}">Sales</a>
          <a href="{{ url('/reports/purchase') }}"       class="n-sub {{ request()->is('reports/purchase*')       ? 'active' : '' }}">Purchases</a>
          <a href="{{ url('/reports/inventory') }}"      class="n-sub {{ request()->is('reports/inventory*')      ? 'active' : '' }}">Inventory</a>
          <a href="{{ url('/reports/customer-aging') }}" class="n-sub {{ request()->is('reports/customer-aging*') ? 'active' : '' }}">Customer Aging</a>
          <a href="{{ url('/reports/supplier-aging') }}" class="n-sub {{ request()->is('reports/supplier-aging*') ? 'active' : '' }}">Supplier Aging</a>
          <a href="{{ url('/reports/expenses') }}"       class="n-sub {{ request()->is('reports/expenses*')       ? 'active' : '' }}">Expenses</a>
          <a href="{{ url('/reports/cheques') }}"        class="n-sub {{ request()->is('reports/cheques*')        ? 'active' : '' }}">Cheques</a>
          <a href="{{ url('/reports/targets') }}"        class="n-sub {{ request()->is('reports/targets*')        ? 'active' : '' }}">Targets</a>
          <a href="{{ url('/reports/stock-movement') }}" class="n-sub {{ request()->is('reports/stock-movement*') ? 'active' : '' }}">Stock Movement</a>
        </div>
      </div>

      {{-- HUMAN RESOURCES --}}
      <div x-show="hasPerm('hr.employees.view') || hasPerm('hr.departments.view') || hasPerm('hr.designations.view') || hasPerm('hr.attendance.view') || hasPerm('hr.holidays.view') || hasPerm('hr.leave_requests.view') || hasPerm('hr.leave_balances.view') || hasPerm('hr.leave_types.view') || hasPerm('hr.payroll.view') || hasPerm('hr.jobs.view') || hasPerm('hr.candidates.view') || hasPerm('hr.performance.view') || hasPerm('hr.checklists.view') || hasPerm('hr.assets.view') || hasPerm('hr.reports.view')">
        <button @click="sidebarOpen ? toggle('hr') : null"
                class="n-group {{ $sec === 'hr' ? 'sec-active' : '' }}"
                :class="sidebarOpen ? '' : 'collapsed-btn'">
          <div class="n-icon">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zM6 8a2 2 0 11-4 0 2 2 0 014 0zM22 8a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <span x-show="sidebarOpen" class="flex-1 text-[13px]">Human Resources</span>
          <svg x-show="sidebarOpen" class="n-chevron w-3.5 h-3.5" :class="{'open': open.hr}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <div x-show="open.hr && sidebarOpen" class="n-sub-list">
          <a href="{{ url('/hr/employees') }}"     x-show="hasPerm('hr.employees.view')"
             class="n-sub {{ request()->is('hr/employees*') ? 'active' : '' }}">Employees</a>
          <a href="{{ url('/hr/org-chart') }}"      x-show="hasPerm('hr.employees.view')"
             class="n-sub {{ request()->is('hr/org-chart*') ? 'active' : '' }}">Org Chart</a>
          <a href="{{ url('/hr/attendance') }}"     x-show="hasPerm('hr.attendance.view')"
             class="n-sub {{ request()->is('hr/attendance*') ? 'active' : '' }}">Attendance</a>
          <a href="{{ url('/hr/holidays') }}"       x-show="hasPerm('hr.holidays.view')"
             class="n-sub {{ request()->is('hr/holidays*') ? 'active' : '' }}">Holidays</a>
          <a href="{{ url('/hr/leave-requests') }}" x-show="hasPerm('hr.leave_requests.view')"
             class="n-sub {{ request()->is('hr/leave-requests*') ? 'active' : '' }}">Leave Requests</a>
          <a href="{{ url('/hr/leave-balances') }}" x-show="hasPerm('hr.leave_balances.view')"
             class="n-sub {{ request()->is('hr/leave-balances*') ? 'active' : '' }}">Leave Balances</a>
          <a href="{{ url('/hr/leave-types') }}"    x-show="hasPerm('hr.leave_types.view')"
             class="n-sub {{ request()->is('hr/leave-types*') ? 'active' : '' }}">Leave Types</a>
          <a href="{{ url('/hr/payroll') }}"        x-show="hasPerm('hr.payroll.view')"
             class="n-sub {{ request()->is('hr/payroll*') ? 'active' : '' }}">Payroll</a>
          <a href="{{ url('/hr/job-openings') }}"   x-show="hasPerm('hr.jobs.view')"
             class="n-sub {{ request()->is('hr/job-openings*') ? 'active' : '' }}">Job Openings</a>
          <a href="{{ url('/hr/candidates') }}"     x-show="hasPerm('hr.candidates.view')"
             class="n-sub {{ request()->is('hr/candidates*') ? 'active' : '' }}">Candidates</a>
          <a href="{{ url('/hr/departments') }}"    x-show="hasPerm('hr.departments.view')"
             class="n-sub {{ request()->is('hr/departments*') ? 'active' : '' }}">Departments</a>
          <a href="{{ url('/hr/designations') }}"   x-show="hasPerm('hr.designations.view')"
             class="n-sub {{ request()->is('hr/designations*') ? 'active' : '' }}">Designations</a>
          <a href="{{ url('/hr/performance-cycles') }}" x-show="hasPerm('hr.performance.view')"
             class="n-sub {{ request()->is('hr/performance-cycles*') ? 'active' : '' }}">Performance Cycles</a>
          <a href="{{ url('/hr/performance-reviews') }}" x-show="hasPerm('hr.performance.view')"
             class="n-sub {{ request()->is('hr/performance-reviews*') ? 'active' : '' }}">Performance Reviews</a>
          <a href="{{ url('/hr/checklist-templates') }}" x-show="hasPerm('hr.checklists.view')"
             class="n-sub {{ request()->is('hr/checklist-templates*') ? 'active' : '' }}">Onboarding Templates</a>
          <a href="{{ url('/hr/assets') }}"         x-show="hasPerm('hr.assets.view')"
             class="n-sub {{ request()->is('hr/assets*') ? 'active' : '' }}">Assets</a>
          <a href="{{ url('/hr/reports') }}"        x-show="hasPerm('hr.reports.view')"
             class="n-sub {{ request()->is('hr/reports*') ? 'active' : '' }}">HR Reports</a>
        </div>
      </div>

      {{-- MY HR (self-service) --}}
      <a href="{{ url('/my') }}" x-show="user?.has_employee_record"
         class="n-link {{ request()->is('my*') ? 'active' : '' }}"
         :class="sidebarOpen ? '' : 'collapsed-link'">
        <div class="n-icon">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <span x-show="sidebarOpen" class="text-[13px]">My HR</span>
      </a>

      {{-- MY TEAM (manager portal) --}}
      <a href="{{ url('/manager/team') }}" x-show="user?.is_manager"
         class="n-link {{ request()->is('manager*') ? 'active' : '' }}"
         :class="sidebarOpen ? '' : 'collapsed-link'">
        <div class="n-icon">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <span x-show="sidebarOpen" class="text-[13px]">My Team</span>
      </a>

      {{-- ANNOUNCEMENTS --}}
      <a href="{{ url('/announcements') }}"
         class="n-link {{ request()->is('announcements*') ? 'active' : '' }}"
         :class="sidebarOpen ? '' : 'collapsed-link'">
        <div class="n-icon">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
          </svg>
        </div>
        <span x-show="sidebarOpen" class="text-[13px]">Announcements</span>
      </a>

      {{-- ACCESS CONTROL --}}
      <div x-show="hasPerm('users.view') || hasPerm('roles.view') || hasPerm('activity_log.view')">
        <button @click="sidebarOpen ? toggle('admin') : null"
                class="n-group {{ $sec === 'admin' ? 'sec-active' : '' }}"
                :class="sidebarOpen ? '' : 'collapsed-btn'">
          <div class="n-icon">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          </div>
          <span x-show="sidebarOpen" class="flex-1 text-[13px]">Access Control</span>
          <svg x-show="sidebarOpen" class="n-chevron w-3.5 h-3.5" :class="{'open': open.admin}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 18l6-6-6-6"/></svg>
        </button>
        <div x-show="open.admin && sidebarOpen" class="n-sub-list">
          <a href="{{ url('/access-control/users') }}"        x-show="hasPerm('users.view')"
             class="n-sub {{ request()->is('access-control/users*')        ? 'active' : '' }}">Users</a>
          <a href="{{ url('/access-control/roles') }}"        x-show="hasPerm('roles.view')"
             class="n-sub {{ request()->is('access-control/roles*')        ? 'active' : '' }}">Roles</a>
          <a href="{{ url('/access-control/activity-log') }}" x-show="hasPerm('activity_log.view')"
             class="n-sub {{ request()->is('access-control/activity-log*') ? 'active' : '' }}">Activity Log</a>
          <a x-show="user?.roles?.includes('super_admin') || user?.roles?.includes('branch_manager')"
             href="{{ url('/settings/dashboard-widgets') }}"
             class="n-sub {{ request()->is('settings/dashboard-widgets*') ? 'active' : '' }}">Dashboard Widgets</a>
          <a x-show="user?.roles?.includes('super_admin')"
             href="{{ url('/settings/branches') }}"
             class="n-sub {{ request()->is('settings/branches*') ? 'active' : '' }}">Branches</a>
          <a href="{{ url('/settings/banks') }}"
             class="n-sub {{ request()->is('settings/banks*') ? 'active' : '' }}">Banks</a>
          <a href="{{ url('/settings/districts-cities') }}"
             class="n-sub {{ request()->is('settings/districts-cities*') ? 'active' : '' }}">Districts &amp; Cities</a>
        </div>
      </div>

      {{-- OFFICE DIRECTORY --}}
      <a href="{{ url('/directory') }}" x-show="hasPerm('directory.view')"
         class="n-link {{ request()->is('directory*') ? 'active' : '' }}"
         :class="sidebarOpen ? '' : 'collapsed-link'">
        <div class="n-icon">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <span x-show="sidebarOpen" class="text-[13px]">Office Directory</span>
      </a>

      {{-- SETTINGS --}}
      <a href="{{ url('/settings') }}" class="n-link {{ request()->is('settings*') ? 'active' : '' }}"
         :class="sidebarOpen ? '' : 'collapsed-link'">
        <div class="n-icon">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/>
          </svg>
        </div>
        <span x-show="sidebarOpen" class="text-[13px]">Settings</span>
      </a>

    </nav>

    <!-- User footer -->
    <div class="flex-shrink-0 p-2" style="border-top:1px solid rgba(255,255,255,0.05)">
      <div class="flex items-center rounded-xl p-2 transition-colors cursor-pointer"
           style="gap:10px"
           :class="sidebarOpen ? 'hover:bg-white/5' : 'justify-center'">
        <div class="relative flex-shrink-0">
          <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-sm font-bold"
               style="background:linear-gradient(135deg,#1B3EB6,#0D2272);box-shadow:0 2px 8px rgba(27,62,182,0.4)"
               x-text="(user?.name || 'U').charAt(0).toUpperCase()"></div>
          <div class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2"
               style="background:#22A845;border-color:#080c18"></div>
        </div>
        <div class="flex-1 min-w-0" x-show="sidebarOpen">
          <div class="text-sm font-semibold truncate leading-none mb-0.5" style="color:#e2e8f0" x-text="user?.name || 'User'"></div>
          <div class="text-[10px] truncate capitalize" style="color:#3d4f6b" x-text="(user?.roles?.[0] || '').replace('_',' ')"></div>
        </div>
        <button @click="logout()" x-show="sidebarOpen" title="Logout"
                class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center transition-colors"
                style="color:#2d3d5a"
                onmouseover="this.style.background='rgba(255,255,255,0.08)';this.style.color='#ef4444'"
                onmouseout="this.style.background='transparent';this.style.color='#2d3d5a'">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
        </button>
      </div>
    </div>
  </aside>

  <!-- ══════════════════ MAIN ══════════════════ -->
  <div class="flex-1 flex flex-col overflow-hidden">

    <!-- Topbar -->
    <header class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 px-5 flex items-center justify-between flex-shrink-0"
            style="height:60px;box-shadow:0 1px 3px rgba(0,0,0,0.04)">
      <div class="flex items-center gap-3">
        <button @click="sidebarOpen = !sidebarOpen"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
          <svg class="w-4.5 h-4.5" style="width:18px;height:18px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
          </svg>
        </button>
        <div>
          <h1 class="text-[15px] font-semibold text-gray-800 dark:text-white leading-none">@yield('page-title', 'Dashboard')</h1>
          <p class="text-[11px] text-gray-400 mt-0.5 hidden sm:block">@yield('page-desc', '')</p>
        </div>
      </div>
      <div class="flex items-center gap-1.5">

        <!-- ── Branch Switcher ── -->
        <div class="relative hidden sm:block" x-data="{ branchOpen: false }">
          <button @click="branchOpen = !branchOpen"
                  class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all hover:shadow-sm"
                  style="background:#f0f4ff;border:1px solid #e0e7ff">
            <svg class="w-3.5 h-3.5 flex-shrink-0" style="color:#1B3EB6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/><path d="M3 21h18M9 21V11h6v10"/>
            </svg>
            <span class="text-[12px] font-semibold max-w-[120px] truncate" style="color:#1B3EB6"
                  x-text="activeBranchName"></span>
            <svg class="w-3 h-3 flex-shrink-0 transition-transform" style="color:#1B3EB6"
                 :style="branchOpen ? 'transform:rotate(180deg)' : ''"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
              <path d="M19 9l-7 7-7-7"/>
            </svg>
          </button>

          <!-- Branch dropdown -->
          <div x-show="branchOpen"
               @click.away="branchOpen = false"
               x-transition:enter="transition ease-out duration-100"
               x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
               x-transition:enter-end="opacity-100 scale-100 translate-y-0"
               class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 z-50 overflow-hidden"
               style="top:100%">
            <div class="px-3 py-2 border-b border-gray-100 dark:border-gray-700">
              <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Switch Branch</p>
            </div>
            <div class="py-1 max-h-60 overflow-y-auto">
              <!-- All Branches option (super_admin only) -->
              <template x-if="user?.roles?.includes('super_admin') || user?.roles?.includes('admin')">
                <button @click="localStorage.setItem('medri_branch','all'); window.location.reload()"
                        class="flex items-center gap-3 w-full px-3 py-2.5 text-left transition-colors hover:bg-gray-50 dark:hover:bg-gray-700">
                  <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                       :style="!activeBranchId ? 'background:#1B3EB6' : 'background:#f0f4ff'">
                    <svg class="w-3.5 h-3.5" :style="!activeBranchId ? 'color:#fff' : 'color:#1B3EB6'"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium" :class="!activeBranchId ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-200'">All Branches</div>
                    <div class="text-xs text-gray-400">Cumulative view</div>
                  </div>
                  <svg x-show="!activeBranchId" class="w-4 h-4 flex-shrink-0" style="color:#1B3EB6"
                       fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path d="M5 13l4 4L19 7"/>
                  </svg>
                </button>
              </template>
              <template x-for="b in switcherBranches" :key="b.id">
                <button @click="switchBranch(b); branchOpen = false"
                        class="flex items-center gap-3 w-full px-3 py-2.5 text-left transition-colors hover:bg-gray-50 dark:hover:bg-gray-700">
                  <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0"
                       :style="b.id == activeBranchId ? 'background:#1B3EB6' : 'background:#f0f4ff'">
                    <svg class="w-3.5 h-3.5" :style="b.id == activeBranchId ? 'color:#fff' : 'color:#1B3EB6'"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                      <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/><path d="M3 21h18M9 21V11h6v10"/>
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium truncate"
                         :class="b.id == activeBranchId ? 'text-primary-700 dark:text-primary-300' : 'text-gray-700 dark:text-gray-200'"
                         x-text="b.name"></div>
                    <div class="text-xs text-gray-400 truncate" x-text="b.code || ''"></div>
                  </div>
                  <svg x-show="b.id == activeBranchId" class="w-4 h-4 flex-shrink-0" style="color:#1B3EB6"
                       fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path d="M5 13l4 4L19 7"/>
                  </svg>
                </button>
              </template>
              <div x-show="!switcherBranches.length" class="px-4 py-3 text-xs text-gray-400 text-center">No branches assigned</div>
            </div>
          </div>
        </div>

        <!-- ── Dark Toggle ── -->
        <button @click="toggleDark()"
                class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all">
          <svg x-show="!dark" style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
          <svg x-show="dark"  style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
        </button>

        <!-- ── Profile Dropdown ── -->
        <div class="relative" x-data="{ profileOpen: false }">
          <button @click="profileOpen = !profileOpen"
                  class="w-8 h-8 rounded-full flex items-center justify-center text-white text-[13px] font-bold transition-all hover:shadow-lg hover:scale-105"
                  style="background:linear-gradient(135deg,#1B3EB6,#0D2272);box-shadow:0 2px 8px rgba(27,62,182,0.35)"
                  x-text="(user?.name || 'U').charAt(0).toUpperCase()">
          </button>

          <!-- Profile dropdown -->
          <div x-show="profileOpen"
               @click.away="profileOpen = false"
               x-transition:enter="transition ease-out duration-100"
               x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
               x-transition:enter-end="opacity-100 scale-100 translate-y-0"
               class="absolute right-0 mt-2 w-60 bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 z-50 overflow-hidden"
               style="top:100%">

            <!-- User info header -->
            <div class="px-4 py-3.5 border-b border-gray-100 dark:border-gray-700"
                 style="background:linear-gradient(135deg,#f8faff,#eef2ff)">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                     style="background:linear-gradient(135deg,#1B3EB6,#0D2272)"
                     x-text="(user?.name || 'U').charAt(0).toUpperCase()"></div>
                <div class="min-w-0">
                  <div class="font-semibold text-sm text-gray-800 truncate" x-text="user?.name || 'User'"></div>
                  <div class="text-xs text-gray-400 truncate" x-text="user?.email || ''"></div>
                  <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold mt-0.5"
                        style="background:#eef2ff;color:#1B3EB6"
                        x-text="(user?.roles?.[0] || '').replace('_',' ')"></span>
                </div>
              </div>
            </div>

            <!-- Menu items -->
            <div class="py-1.5">
              <a href="{{ url('/settings') }}"
                 class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                 @click="profileOpen = false">
                <div class="w-7 h-7 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                  <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3"/>
                  </svg>
                </div>
                Settings
              </a>
              <a href="{{ url('/access-control/activity-log') }}"
                 class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                 @click="profileOpen = false">
                <div class="w-7 h-7 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                  <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                  </svg>
                </div>
                Activity Log
              </a>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-700 py-1.5">
              <button @click="logout(); profileOpen = false"
                      class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors text-left">
                <div class="w-7 h-7 rounded-lg bg-red-50 dark:bg-red-900/30 flex items-center justify-center">
                  <svg class="w-3.5 h-3.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                  </svg>
                </div>
                Sign Out
              </button>
            </div>
          </div>
        </div>

      </div>
    </header>

    <!-- Content -->
    <main class="flex-1 overflow-y-auto main-scroll p-4 md:p-6">
      @yield('content')
    </main>
  </div>
</div>

<div class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none" id="toasts"></div>

<script>
const API = '{{ url('/api') }}';
const BASE = '{{ url('') }}';
function authHeaders(isFormData = false) {
  const token = localStorage.getItem('medri_token') || sessionStorage.getItem('medri_token');
  const h = { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' };
  if (!isFormData) h['Content-Type'] = 'application/json';
  const bid = localStorage.getItem('medri_branch');
  if (bid && bid !== 'all') h['X-Branch-Id'] = bid;
  return h;
}
function setApiToken(token) {
  localStorage.setItem('medri_token', token);
  document.cookie = 'medri_api_token=' + encodeURIComponent(token) + '; path=/; SameSite=Lax; max-age=86400';
}
function clearApiToken() {
  localStorage.clear();
  sessionStorage.clear();
  document.cookie = 'medri_api_token=; path=/; SameSite=Lax; max-age=0';
}
async function apiFetch(path, opts = {}) {
  const isFormData = opts.body instanceof FormData;
  const res = await fetch(API + path, { headers: authHeaders(isFormData), ...opts });
  if (res.status === 401) { clearApiToken(); window.location.href = '{{ url('/login') }}'; return; }
  if (!res.ok) {
    let message = `Request failed (${res.status})`;
    try {
      const body = await res.json();
      const firstError = Object.values(body.errors ?? {})[0]?.[0];
      message = firstError ?? body.message ?? message;
    } catch (e) {}
    throw new Error(message);
  }
  return res;
}
function toast(msg, type = 'success') {
  const c = { success:'#22A845', error:'#E31E24', warning:'#f59e0b', info:'#1B3EB6' };
  const el = document.createElement('div');
  el.style.cssText = `pointer-events:auto;display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:12px;color:#fff;font-size:13.5px;font-weight:500;box-shadow:0 8px 24px rgba(0,0,0,0.2);background:${c[type]||c.info};max-width:340px;`;
  el.innerHTML = msg;
  document.getElementById('toasts').appendChild(el);
  setTimeout(() => { el.style.opacity='0'; el.style.transform='translateX(8px)'; el.style.transition='all 0.3s'; setTimeout(()=>el.remove(),300); }, 3700);
}
function fmtMoney(v) {
  if (v == null) return '—';
  return 'Rs. ' + parseFloat(v).toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
function fmtDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}
function exportCSV(filename, headers, rows) {
  const esc = v => '"' + String(v ?? '').replace(/"/g, '""') + '"';
  const csv = [headers, ...rows].map(r => r.map(esc).join(',')).join('\n');
  const blob = new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8;' });
  const a = document.createElement('a');
  a.href = URL.createObjectURL(blob);
  a.download = filename + '_' + new Date().toISOString().slice(0,10) + '.csv';
  document.body.appendChild(a);
  a.click();
  setTimeout(() => { URL.revokeObjectURL(a.href); a.remove(); }, 100);
}
// Cached bank list — call loadBanks() in any page init() that needs a bank dropdown
window._banksCache = null;
async function loadBanks() {
  if (window._banksCache) return window._banksCache;
  try {
    const r = await apiFetch('/banks?active_only=true');
    window._banksCache = await r.json();
  } catch (e) { window._banksCache = []; }
  return window._banksCache;
}
// Cached { districtName: [cityName, ...] } map — call loadDistrictCities() in any
// page init() that needs the district/city address dropdowns.
window._districtsCache = null;
async function loadDistrictCities() {
  if (window._districtsCache) return window._districtsCache;
  try {
    const r = await apiFetch('/districts?active_only=true');
    const districts = await r.json();
    const map = {};
    districts.forEach(d => { map[d.name] = (d.cities ?? []).map(c => c.name); });
    window._districtsCache = map;
  } catch (e) { window._districtsCache = {}; }
  return window._districtsCache;
}
function layout() {
  return {
    sidebarOpen: window.innerWidth >= 1024,
    dark: localStorage.getItem('medri_dark') === 'true',
    user: null,
    activeBranchId: null,
    switcherBranches: [],
    get activeBranchName() {
      if (!this.activeBranchId) return 'All Branches';
      const b = this.switcherBranches.find(x => String(x.id) === String(this.activeBranchId));
      return b?.name || 'All Branches';
    },
    open: {
      sales:       '{{ $sec }}' === 'sales',
      cheques:     '{{ $sec }}' === 'cheques',
      procurement: '{{ $sec }}' === 'procurement',
      partners:    '{{ $sec }}' === 'partners',
      inventory:   '{{ $sec }}' === 'inventory',
      services:    '{{ $sec }}' === 'services',
      finance:     '{{ $sec }}' === 'finance',
      accounting:  '{{ $sec }}' === 'accounting',
      reports:     '{{ $sec }}' === 'reports',
      hr:          '{{ $sec }}' === 'hr',
      admin:       '{{ $sec }}' === 'admin',
    },
    hasPerm(p) {
      if (!this.user) return true; // show by default before user loads
      if (this.user.is_super_admin) return true;
      if ((this.user.roles ?? []).includes('super_admin')) return true;
      return (this.user.permissions ?? []).includes(p);
    },
    init() {
      const u = localStorage.getItem('medri_user');
      if (u) { try { this.user = JSON.parse(u); } catch {} }
      const stored = localStorage.getItem('medri_branch');
      if (stored === 'all') {
        this.activeBranchId = null;          // restore "All Branches" on refresh
      } else {
        this.activeBranchId = stored
          || this.user?.default_branch_id
          || this.user?.branches?.[0]?.id
          || null;
      }
      this.switcherBranches = this.user?.branches ?? [];
      this.loadSwitcherBranches();
      window.addEventListener('branches-changed', () => this.loadSwitcherBranches());
    },
    async loadSwitcherBranches() {
      const isSuper = this.user?.is_super_admin || (this.user?.roles ?? []).includes('super_admin');
      try {
        if (isSuper) {
          // Super admins can access every branch — list them all
          const r = await apiFetch('/branches?active_only=true');
          if (r) this.switcherBranches = await r.json();
        } else {
          // Others: refresh assigned branches (cached copy goes stale after login)
          const r = await apiFetch('/auth/me');
          if (r) {
            const me = await r.json();
            this.user = me;
            localStorage.setItem('medri_user', JSON.stringify(me));
            this.switcherBranches = me.branches ?? [];
          }
        }
      } catch (e) { /* keep cached list on failure */ }
    },
    toggle(sec) { this.open[sec] = !this.open[sec]; },
    switchBranch(branch) {
      this.activeBranchId = branch.id;
      localStorage.setItem('medri_branch', branch.id);
      window.location.reload();
    },
    toggleDark() {
      this.dark = !this.dark;
      localStorage.setItem('medri_dark', this.dark);
      document.documentElement.classList.toggle('dark', this.dark);
    },
    logout() {
      apiFetch('/auth/logout', { method: 'POST' }).finally(() => { clearApiToken(); window.location.href = '{{ url('/login') }}'; });
    }
  };
}
</script>
@stack('scripts')
</body>
</html>
