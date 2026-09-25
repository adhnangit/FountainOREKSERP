@extends('layouts.app')
@section('title', 'Supplier Invoices')
@section('page-title', 'Supplier Invoices')
@section('page-desc', 'Manage purchase invoices and supplier payments')

@push('head')
<style>
  /* Native number-input spinner arrows eat width inside these narrow, right-aligned
     cells and can crowd out the digits themselves (e.g. "12" rendering as invisible
     under the spinner) — hide them and rely on the qty/cost steppers elsewhere for
     increment/decrement instead. */
  .no-spinner::-webkit-outer-spin-button,
  .no-spinner::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
  .no-spinner { -moz-appearance: textfield; }

  .po-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
  .po-stat-card{background:#fff;border-radius:14px;padding:18px 20px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:14px;transition:box-shadow .2s,transform .2s}
  .po-stat-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08);transform:translateY(-2px)}
  .po-stat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
  .po-stat-icon svg{width:22px;height:22px}
  .po-stat-val{font-size:22px;font-weight:800;line-height:1.1;letter-spacing:-.5px}
  .po-stat-lbl{font-size:11.5px;color:#94a3b8;font-weight:500;margin-top:2px}

  .po-toolbar{background:#fff;border-radius:14px;padding:14px 18px;border:1px solid #e2e8f0;margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
  .po-search-wrap{position:relative;flex:1;min-width:200px;max-width:300px}
  .po-search-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none}
  .po-search-wrap input{width:100%;border:1px solid #e2e8f0;border-radius:9px;padding:7px 12px 7px 34px;font-size:13px;color:#1e293b;background:#f8fafc;outline:none;transition:border-color .15s,box-shadow .15s}
  .po-search-wrap input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);background:#fff}
  .po-select{border:1px solid #e2e8f0;border-radius:9px;padding:7px 10px;font-size:12.5px;color:#334155;background:#f8fafc;outline:none}
  .po-select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}

  .po-table-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden}
  .po-table{width:100%;border-collapse:separate;border-spacing:0}
  .po-table thead th{padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e2e8f0;white-space:nowrap}
  .po-table thead th:first-child{padding-left:20px}
  .po-table tbody tr{transition:background .1s}
  .po-table tbody tr:hover{background:#f8faff}
  .po-table tbody td{padding:13px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
  .po-table tbody td:first-child{padding-left:20px}
  .po-table tbody tr:last-child td{border-bottom:none}
  .po-num{font-size:13px;font-weight:700;color:#4f46e5}

  .po-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:64px 24px;text-align:center}
  .po-empty svg{width:56px;height:56px;color:#e2e8f0}
  .po-empty h5{font-size:16px;font-weight:700;color:#475569;margin-top:14px}
  .po-empty p{font-size:13px;color:#94a3b8;margin-top:4px}

  .dark .po-stat-card{background:#1e293b;border-color:#334155}
  .dark .po-stat-lbl{color:#64748b}
  .dark .po-toolbar{background:#1e293b;border-color:#334155}
  .dark .po-search-wrap input{background:#0f172a;border-color:#334155;color:#e2e8f0}
  .dark .po-search-wrap input:focus{background:#1e293b}
  .dark .po-select{background:#0f172a;border-color:#334155;color:#cbd5e1}
  .dark .po-table-card{background:#1e293b;border-color:#334155}
  .dark .po-table thead th{background:#0f172a;border-color:#334155}
  .dark .po-table tbody tr:hover{background:#1e3351}
  .dark .po-table tbody td{border-color:#1e293b}
  .dark .po-empty svg{color:#334155}
  .dark .po-empty h5{color:#94a3b8}

  .inv-pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid #f1f5f9}
  .inv-page-info{font-size:12.5px;color:#94a3b8}
  .inv-page-btns{display:flex;gap:4px}
  .inv-page-btn{min-width:30px;height:30px;padding:0 6px;border-radius:7px;border:1px solid #e2e8f0;background:#fff;font-size:12px;font-weight:600;color:#475569;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s}
  .inv-page-btn:hover:not(:disabled):not(.active){background:#f8fafc}
  .inv-page-btn.active{background:#6366f1;color:#fff;border-color:#6366f1}
  .inv-page-btn:disabled{opacity:.35;cursor:default}
  .dark .inv-pagination{border-color:#334155}
  .dark .inv-page-btn{background:#1e293b;border-color:#334155;color:#94a3b8}
</style>
@endpush

@section('content')
<div x-data="supplierInvListPage()" x-init="init()">

    {{-- Stats Cards --}}
    <div class="po-stats">
        <div class="po-stat-card">
            <div class="po-stat-icon" style="background:#eef2ff">
                <svg fill="none" viewBox="0 0 24 24" stroke="#4f46e5" stroke-width="1.8"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
            <div>
                <div class="po-stat-val" style="color:#4f46e5" x-text="stats.total_count"></div>
                <div class="po-stat-lbl">Total Invoices</div>
            </div>
        </div>
        <div class="po-stat-card">
            <div class="po-stat-icon" style="background:#eff6ff">
                <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="1.8"><path d="M9 7h6m0 10v-3m-3 3v-3m-3 3v-3m9-8H4a1 1 0 00-1 1v10a1 1 0 001 1h16a1 1 0 001-1V6a1 1 0 00-1-1z"/></svg>
            </div>
            <div>
                <div class="po-stat-val" style="color:#2563eb" x-text="fmtCompact(stats.total_amount)"></div>
                <div class="po-stat-lbl">Invoice Value</div>
            </div>
        </div>
        <div class="po-stat-card">
            <div class="po-stat-icon" style="background:#dcfce7">
                <svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="1.8"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="po-stat-val" style="color:#16a34a" x-text="fmtCompact(stats.paid_amount)"></div>
                <div class="po-stat-lbl">Paid</div>
            </div>
        </div>
        <div class="po-stat-card">
            <div class="po-stat-icon" style="background:#fee2e2">
                <svg fill="none" viewBox="0 0 24 24" stroke="#b91c1c" stroke-width="1.8"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div class="po-stat-val" style="color:#b91c1c" x-text="fmtCompact(stats.balance_due)"></div>
                <div class="po-stat-lbl">Outstanding</div>
            </div>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="po-toolbar">
        <div class="po-search-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" x-model.debounce.400ms="search" @input.debounce.400ms="page=1;load()" placeholder="Search invoice# or supplier…">
        </div>
        <select x-model="statusFilter" @change="page=1;load()" class="po-select">
            <option value="">All Statuses</option>
            <option value="confirmed">Confirmed</option>
            <option value="partially_received">Partially Received</option>
            <option value="received">Received</option>
            <option value="cancelled">Cancelled</option>
        </select>
        <select x-model="payFilter" @change="page=1;load()" class="po-select">
            <option value="">All Payments</option>
            <option value="unpaid">Unpaid</option>
            <option value="partially_paid">Partial</option>
            <option value="paid">Paid</option>
        </select>
        <div style="margin-left:auto">
            <a x-show="hasPerm('purchase_orders.create')" href="{{ url('/purchase-orders/create') }}"
               class="btn-page-action">
                <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
                New Supplier Invoice
            </a>
        </div>
    </div>

    <div class="po-table-card">
        <div x-show="loading" class="flex items-center justify-center py-16">
            <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
        </div>
        <div x-show="!loading" class="overflow-x-auto">
            <table class="po-table">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Due Date</th>
                        <th style="text-align:right">Total</th>
                        <th style="text-align:right">Paid</th>
                        <th style="text-align:right">Balance</th>
                        <th>GRN</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="po in items" :key="po.id">
                        <tr>
                            <td>
                                <a :href="BASE + '/purchase-orders/' + po.id" class="po-num hover:underline"
                                   x-text="po.po_number ?? ('#INV-' + po.id)"></a>
                            </td>
                            <td class="text-sm font-medium text-gray-800 dark:text-gray-100" x-text="po.supplier?.name ?? '—'"></td>
                            <td class="text-sm text-gray-500" x-text="fmtDate(po.order_date)"></td>
                            <td class="text-sm"
                                :class="isOverdue(po) ? 'text-red-600 font-semibold' : 'text-gray-500'"
                                x-text="po.due_date ? fmtDate(po.due_date) : '—'"></td>
                            <td style="text-align:right" class="text-sm font-semibold tabular-nums" x-text="fmtMoney(po.total ?? 0)"></td>
                            <td style="text-align:right" class="text-sm tabular-nums text-green-700" x-text="fmtMoney(po.paid_amount ?? 0)"></td>
                            <td style="text-align:right" class="text-sm tabular-nums font-semibold"
                                :class="parseFloat(po.balance_due ?? 0) > 0 ? 'text-red-600' : 'text-gray-400'"
                                x-text="fmtMoney(po.balance_due ?? 0)"></td>
                            <td>
                                <span :class="grnBadge(po)" x-text="grnLabel(po)"></span>
                            </td>
                            <td>
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                                      :class="payBadge(po.payment_status)"
                                      x-text="payLabel(po.payment_status)"></span>
                            </td>
                            <td style="white-space:nowrap">
                                <div class="flex items-center gap-1.5 flex-nowrap">
                                    <template x-if="po.grns && po.grns.some(g => g.status === 'draft') && hasPerm('grns.confirm')">
                                        <button @click="openReceive(po)"
                                                class="text-xs font-semibold px-2.5 py-1 rounded-lg transition-colors whitespace-nowrap flex-shrink-0"
                                                style="background:#dcfce7;color:#15803d;border:1px solid #86efac"
                                                onmouseover="this.style.background='#bbf7d0'"
                                                onmouseout="this.style.background='#dcfce7'">
                                            Receive Items
                                        </button>
                                    </template>
                                    <a :href="BASE + '/purchase-orders/' + po.id"
                                       class="text-indigo-600 hover:underline text-sm font-medium flex-shrink-0">View</a>
                                    <button @click="printPO(po)"
                                            class="text-gray-500 hover:underline text-sm font-medium flex-shrink-0">Print</button>
                                    <template x-if="parseFloat(po.balance_due ?? 0) > 0 && po.status !== 'cancelled' && hasPerm('purchase_orders.payment')">
                                        <button @click="openPay(po)"
                                                class="text-sm font-semibold px-2.5 py-1 rounded-lg transition-colors flex-shrink-0"
                                                style="background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0">
                                            Pay
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
            <div x-show="!loading && items.length === 0" class="po-empty">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <h5>No supplier invoices found</h5>
                <p>Try adjusting your search or filters</p>
            </div>
        </div>

        {{-- Pagination --}}
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

    {{-- ══ RECEIVE ITEMS MODAL ══ --}}
    <template x-if="showReceive">
        <div class="fixed inset-0 z-50 flex items-start justify-center p-4 overflow-y-auto"
             style="background:rgba(15,23,42,0.6);backdrop-filter:blur(4px)"
             @click.self="showReceive = false">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-6xl my-6">

                {{-- Header --}}
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 rounded-t-2xl"
                     style="background:linear-gradient(135deg,#065f46,#064e3b)">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-white">Receive Items</h3>
                            <p class="text-xs mt-0.5 text-white/60"
                               x-text="(selReceivePo?.po_number ?? '') + '  ·  Supplier: ' + (selReceivePo?.supplier?.name ?? '')"></p>
                        </div>
                        <button @click="showReceive = false" class="text-white/60 hover:text-white">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-5 space-y-4">

                    {{-- Loading state --}}
                    <div x-show="receiveLoading" class="flex items-center justify-center py-10">
                        <svg class="animate-spin w-6 h-6 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        <span class="ml-3 text-sm text-gray-500">Loading product details…</span>
                    </div>

                    <div x-show="!receiveLoading">
                        <p class="text-xs text-gray-500 mb-3">Review quantities, update batch info and selling prices before confirming receipt.</p>

                        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                            <table class="w-full text-xs">
                                <thead style="background:#dcfce7">
                                    <tr>
                                        <th class="text-left px-3 py-2 font-bold text-gray-800 min-w-[180px]">Product</th>
                                        <th class="text-right px-3 py-2 font-bold text-gray-800 w-16">Stock</th>
                                        <th class="text-right px-3 py-2 font-bold text-gray-800 w-20">Qty</th>
                                        <th class="text-right px-3 py-2 font-bold text-gray-800 w-24">Cost Price</th>
                                        <th class="text-left px-3 py-2 font-bold text-gray-800 w-24">Batch #</th>
                                        <th class="text-left px-3 py-2 font-bold text-gray-800 w-28">Expiry Date</th>
                                        <th class="text-right px-3 py-2 font-bold text-amber-800 w-24 bg-yellow-100">Current Sell</th>
                                        <th class="text-right px-3 py-2 font-bold text-emerald-800 w-24 bg-emerald-100">New Sell Price</th>
                                        <th class="text-right px-3 py-2 font-bold text-gray-800 w-24">Total</th>
                                        <th class="w-8"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40 bg-white dark:bg-gray-900">
                                    <template x-for="row in receiveFormItems" :key="row.grn_item_id ?? row._tempId">
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                                            <td class="px-3 py-2 align-top">
                                                <div class="search-dd" style="min-width:170px"
                                                     x-data="{ open: false, q: '', ddStyle: '' }"
                                                     @click.away="open = false"
                                                     @keydown.escape="open = false">
                                                    <button type="button"
                                                            @click="open = !open; if(open){ const r=$el.getBoundingClientRect(); ddStyle='top:'+(r.bottom+4)+'px;left:'+r.left+'px;width:max('+r.width+'px,260px);'; $nextTick(() => $refs.rps?.focus()) }"
                                                            class="input text-xs py-1.5 w-full text-left flex items-center justify-between gap-2 font-semibold"
                                                            :class="!row.product_id ? 'border-blue-200 dark:border-blue-700/60' : ''">
                                                        <span class="truncate" :class="row.product_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400 font-normal'"
                                                              x-text="row.product_id ? row.product_name : '— Select product —'"></span>
                                                        <svg class="w-3 h-3 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                                                    </button>
                                                    <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu" :style="ddStyle">
                                                        <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                                                            <input x-ref="rps" x-model="q" type="text" placeholder="Search by name or SKU…"
                                                                   class="input text-sm w-full py-1.5" @keydown.stop />
                                                        </div>
                                                        <div class="max-h-52 overflow-y-auto py-1">
                                                            <template x-for="p in receiveProductList.filter(p => !q || p.name.toLowerCase().includes(q.toLowerCase()) || (p.code && p.code.toLowerCase().includes(q.toLowerCase())))" :key="p.id">
                                                                <button type="button"
                                                                        @click="row.product_id = p.id; onReceiveRowProductChange(row); open = false; q = ''"
                                                                        class="search-dd-item" :class="row.product_id == p.id ? 'active' : ''">
                                                                    <div class="flex-1 min-w-0">
                                                                        <div class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate" x-text="p.name"></div>
                                                                        <div class="text-xs text-gray-400" x-text="(p.code || '') + (p.unit ? ' · ' + p.unit : '')"></div>
                                                                    </div>
                                                                    <template x-if="row.product_id == p.id">
                                                                        <div class="w-4 h-4 rounded-full flex items-center justify-center flex-shrink-0" style="background:#1B3EB6">
                                                                            <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                                                                        </div>
                                                                    </template>
                                                                </button>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1.5 mt-1">
                                                    <span class="text-gray-500 text-xs" x-text="row.unit"></span>
                                                    <span x-show="row.original_product_id && row.product_id && row.product_id !== row.original_product_id"
                                                          class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-amber-100 text-amber-700">
                                                        Substituted
                                                    </span>
                                                    <span x-show="!row.grn_item_id"
                                                          class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-blue-100 text-blue-700">
                                                        New — not on PO
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2 text-right tabular-nums">
                                                <span class="font-semibold" :class="row.current_stock > 0 ? 'text-emerald-700' : 'text-gray-400'"
                                                      x-text="parseFloat(row.current_stock).toLocaleString()"></span>
                                            </td>
                                            <td class="px-3 py-2" style="min-width:70px">
                                                <input type="number" x-model.number="row.quantity_received"
                                                       min="0.01" step="0.01"
                                                       class="no-spinner input text-sm font-semibold text-gray-900 dark:text-gray-100 py-1 text-right tabular-nums w-full" />
                                            </td>
                                            <td class="px-3 py-2" style="min-width:85px">
                                                <input type="number" x-model.number="row.unit_cost"
                                                       min="0" step="0.01"
                                                       class="no-spinner input text-sm font-semibold text-gray-900 dark:text-gray-100 py-1 text-right tabular-nums w-full" />
                                            </td>
                                            <td class="px-3 py-2" style="min-width:90px">
                                                <input type="text" x-model="row.batch_number"
                                                       class="input text-xs py-1 w-full" placeholder="Optional" />
                                            </td>
                                            <td class="px-3 py-2" style="min-width:130px">
                                                <input type="date" x-model="row.expiry_date"
                                                       class="input text-xs py-1 w-full" />
                                            </td>
                                            <td class="px-3 py-2 text-right tabular-nums bg-yellow-50/50 dark:bg-yellow-900/10">
                                                <span class="text-amber-800 font-semibold" x-text="fmtMoney(row.current_selling_price)"></span>
                                            </td>
                                            <td class="px-3 py-2 bg-emerald-50/50 dark:bg-emerald-900/10" style="min-width:100px">
                                                <input type="number" x-model.number="row.selling_price"
                                                       min="0" step="0.01"
                                                       class="no-spinner input text-sm font-semibold text-gray-900 dark:text-gray-100 py-1 text-right tabular-nums w-full border-emerald-300 focus:border-emerald-500"
                                                       placeholder="0.00" />
                                            </td>
                                            <td class="px-3 py-2 text-right font-semibold tabular-nums text-gray-700 dark:text-gray-200"
                                                x-text="fmtMoney((row.quantity_received||0)*(row.unit_cost||0))"></td>
                                            <td class="px-3 py-2 text-center">
                                                <button type="button" x-show="receiveFormItems.length > 1" @click="removeReceiveRow(row)"
                                                        class="text-gray-400 hover:text-red-600" title="Remove this line — it won't be received">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot style="background:#f0fdf4">
                                    <tr>
                                        <td colspan="8" class="px-3 py-2.5 text-right text-sm font-semibold text-gray-700">Total Receipt Cost</td>
                                        <td class="px-3 py-2.5 text-right font-bold tabular-nums text-emerald-800"
                                            x-text="fmtMoney(receiveFormItems.reduce((s,r)=>s+(r.quantity_received||0)*(r.unit_cost||0),0))"></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <button type="button" @click="addReceiveRow()"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 hover:border-emerald-300 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
                            Add item not on this PO
                        </button>

                        <div class="p-3 rounded-xl text-xs bg-blue-50 border border-blue-200 text-blue-700 dark:bg-blue-900/20 dark:border-blue-800 dark:text-blue-300">
                            <strong>On confirm:</strong> Stock is added, avg cost updated, DR Inventory / CR Accounts Payable journal posted. Selling prices are updated for items where a new price is entered.
                            A product picker lets you correct a line if the supplier sent a substitute, or add something that wasn't on the original order.
                        </div>
                    </div>

                </div>

                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                    <button @click="showReceive = false" class="btn-secondary">Cancel</button>
                    <button @click="submitReceive()" :disabled="receiving || receiveLoading"
                            class="flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold text-white transition-all"
                            style="background:#065f46" onmouseover="this.style.background='#064e3b'" onmouseout="this.style.background='#065f46'">
                        <svg x-show="receiving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        <span x-text="receiving ? 'Confirming…' : 'Confirm Receipt'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- ══ PAYMENT MODAL ══ --}}
    <template x-if="showPay">
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background:rgba(15,23,42,0.55);backdrop-filter:blur(4px)"
             @click.self="showPay = false">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">

                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700"
                     style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-bold text-white">Record Payment</h3>
                            <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.6)"
                               x-text="(selPo?.po_number ?? '') + ' · Balance: ' + fmtMoney(selPo?.balance_due ?? 0)"></p>
                        </div>
                        <button @click="showPay = false" class="text-white/60 hover:text-white">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-5 space-y-4 max-h-[70vh] overflow-y-auto">

                    {{-- Payment Method --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">Payment Method</label>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="m in payMethods" :key="m.v">
                                <button type="button"
                                        @click="pf.payment_method = m.v; pf.account_id = m.v === 'cash' ? (cashAccounts[0]?.id ?? null) : (bankAccounts[0]?.id ?? null)"
                                        :style="pf.payment_method === m.v ? `background:${m.bg};border:2px solid ${m.border};color:${m.color}` : 'background:#f9fafb;border:2px solid #e5e7eb;color:#6b7280'"
                                        class="py-2 rounded-xl text-xs font-bold flex flex-col items-center gap-1 transition-all">
                                    <span x-text="m.icon" class="text-base"></span>
                                    <span x-text="m.label"></span>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Amount & Date --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Amount *</label>
                            <input x-model="pf.amount" type="number" step="0.01" min="0.01" class="input" placeholder="0.00" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Payment Date *</label>
                            <input x-model="pf.payment_date" type="date" class="input" />
                        </div>
                    </div>

                    {{-- Cash / Bank Account --}}
                    <template x-if="pf.payment_method === 'cash'">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Cash Account *</label>
                            <select x-model="pf.account_id" class="input">
                                <option :value="null">— Select —</option>
                                <template x-for="a in cashAccounts" :key="a.id">
                                    <option :value="a.id" x-text="a.name"></option>
                                </template>
                            </select>
                        </div>
                    </template>
                    <template x-if="pf.payment_method === 'bank_transfer'">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Bank Account *</label>
                            <select x-model="pf.account_id" class="input">
                                <option :value="null">— Select —</option>
                                <template x-for="a in bankAccounts" :key="a.id">
                                    <option :value="a.id" x-text="a.name"></option>
                                </template>
                            </select>
                        </div>
                    </template>

                    {{-- Cheque --}}
                    <template x-if="pf.payment_method === 'cheque'">
                        <div class="space-y-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Cheque Type</label>
                                <div class="flex gap-3">
                                    <label class="flex items-center gap-2 cursor-pointer text-sm">
                                        <input type="radio" x-model="pf.cheque_type" value="issued" class="accent-indigo-600" /> We Issue
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer text-sm">
                                        <input type="radio" x-model="pf.cheque_type" value="received" class="accent-indigo-600" /> Use Received
                                    </label>
                                </div>
                            </div>
                            <template x-if="pf.cheque_type === 'issued'">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Bank Account *</label>
                                        <select x-model="pf.account_id" class="input">
                                            <option :value="null">— Select —</option>
                                            <template x-for="a in bankAccounts" :key="a.id">
                                                <option :value="a.id" x-text="a.name"></option>
                                            </template>
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Cheque Number *</label>
                                            <input x-model="pf.cheque_number" type="text" class="input" placeholder="e.g. 001234" />
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-500 mb-1">Bank Name *</label>
                                            <div x-data="{bq:'',bOpen:false}" @click.outside="bOpen=false" class="relative">
                                              <input type="text" :value="pf.bank_name"
                                                @input="pf.bank_name=$event.target.value;bq=$event.target.value;bOpen=true"
                                                @focus="bq=pf.bank_name||'';bOpen=true" @keydown.escape="bOpen=false"
                                                class="input" placeholder="Search bank…" autocomplete="off" />
                                              <ul x-show="bOpen" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-44 overflow-y-auto">
                                                <template x-for="b in banks.filter(b=>b.name.toLowerCase().includes(bq.toLowerCase()))" :key="b.id">
                                                  <li @mousedown.prevent="pf.bank_name=b.name;bq=b.name;bOpen=false"
                                                      :class="pf.bank_name===b.name?'bg-indigo-50 text-indigo-700 font-medium':'hover:bg-gray-50 text-gray-700'"
                                                      class="px-3 py-2 text-sm cursor-pointer" x-text="b.name"></li>
                                                </template>
                                                <li x-show="!banks.filter(b=>b.name.toLowerCase().includes(bq.toLowerCase())).length" class="px-3 py-2 text-sm text-gray-400 text-center">No banks found</li>
                                              </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-500 mb-1">Cheque Date *</label>
                                        <input x-model="pf.cheque_date" type="date" class="input" />
                                    </div>
                                </div>
                            </template>
                            <template x-if="pf.cheque_type === 'received'">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-500 mb-1">Select Received Cheque *</label>
                                    <select x-model="pf.received_cheque_id" class="input">
                                        <option :value="null">— Select in-hand cheque —</option>
                                        <template x-for="c in receivedCheques" :key="c.id">
                                            <option :value="c.id" x-text="(c.cheque_number ?? c.id) + ' — Rs.' + parseFloat(c.amount||0).toLocaleString()"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Reference --}}
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Reference / Notes</label>
                        <input x-model="pf.reference_number" type="text" class="input" placeholder="Optional reference…" />
                    </div>

                </div>

                <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
                    <button @click="showPay = false" class="btn-secondary">Cancel</button>
                    <button @click="submitPay()" :disabled="paying" class="btn-primary flex items-center gap-2">
                        <svg x-show="paying" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                        <span x-text="paying ? 'Recording…' : 'Record Payment'"></span>
                    </button>
                </div>
            </div>
        </div>
    </template>

</div>
@endsection

@push('scripts')
<script>
function supplierInvListPage() {
    return {
        items: [], loading: true,
        cashAccounts: [], bankAccounts: [], receivedCheques: [],
        search: '', statusFilter: '', payFilter: '',
        page: 1,
        meta: { total: 0, from: 0, to: 0, last_page: 1 },
        stats: { total_count: 0, total_amount: 0, paid_amount: 0, balance_due: 0 },
        showPay: false, paying: false, selPo: null,
        showReceive: false, receiving: false, receiveLoading: false,
        selReceivePo: null, receiveFormItems: [], receiveProductMap: {}, receiveProductList: [], receiveRemovedIds: [],

        payMethods: [
            { v:'cash',          label:'Cash',   icon:'💵', bg:'#f0fdf4', border:'#22c55e', color:'#15803d' },
            { v:'bank_transfer', label:'Bank',   icon:'🏦', bg:'#faf5ff', border:'#a855f7', color:'#7e22ce' },
            { v:'cheque',        label:'Cheque', icon:'📄', bg:'#fffbeb', border:'#f59e0b', color:'#b45309' },
        ],
        pf: { amount:0, payment_method:'cash', payment_date: new Date().toISOString().slice(0,10),
              reference_number:'', account_id:null, cheque_type:'issued',
              received_cheque_id:null, cheque_number:'', bank_name:'', cheque_date:'' },
        banks: [],

        get pageNumbers() {
            const total = this.meta.last_page;
            const cur = this.page;
            if (total <= 7) return Array.from({length: total}, (_, i) => i + 1);
            const pages = new Set([1, total, cur, cur-1, cur+1].filter(p => p >= 1 && p <= total));
            return [...pages].sort((a,b) => a-b);
        },

        isOverdue(po) {
            if (!po.due_date || po.payment_status === 'paid' || po.status === 'cancelled') return false;
            return new Date(po.due_date) < new Date();
        },

        async printPO(po) {
            showGlobalLoading('Preparing purchase order…');
            try {
                const r = await apiFetch('/purchase-orders/' + po.id + '/pdf');
                if (!r.ok) { toast('Failed to generate PDF', 'error'); hideGlobalLoading(); return; }
                const blob = await r.blob();
                const url = URL.createObjectURL(blob);
                const iframe = document.createElement('iframe');
                iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0';
                iframe.src = url;
                document.body.appendChild(iframe);
                iframe.onload = () => {
                    hideGlobalLoading();
                    setTimeout(() => {
                        iframe.contentWindow.focus();
                        iframe.contentWindow.print();
                    }, 300);
                };
                setTimeout(() => { document.body.removeChild(iframe); URL.revokeObjectURL(url); }, 60000);
            } catch(e) { toast('Print failed', 'error'); hideGlobalLoading(); }
        },

        async init() {
            try {
                const [accR, chqR] = await Promise.all([
                    apiFetch('/accounting/accounts').then(r => r.json()),
                    apiFetch('/cheques?direction=received&status=in_hand&per_page=100').then(r => r.json()),
                    this.load(),
                ]);
                const accounts = Array.isArray(accR) ? accR : (accR.data ?? []);
                this.cashAccounts    = accounts.filter(a => a.is_cash_account);
                this.bankAccounts    = accounts.filter(a => a.is_bank_account);
                this.receivedCheques = chqR.data ?? chqR ?? [];
                this.banks = await loadBanks();
            } catch (e) {
                toast('Failed to load data', 'error');
            } finally {
                this.loading = false;
            }
        },
        async load() {
            const params = new URLSearchParams({ page: this.page, per_page: 20 });
            if (this.search)       params.set('search', this.search);
            if (this.statusFilter) params.set('status', this.statusFilter);
            if (this.payFilter)    params.set('payment_status', this.payFilter);
            const r = await apiFetch('/purchase-orders?' + params);
            const d = await r.json();
            this.items = d.data ?? d ?? [];
            this.meta = { total: d.total ?? this.items.length, from: d.from ?? 0, to: d.to ?? 0, last_page: d.last_page ?? 1 };
            if (d.stats) this.stats = d.stats;
        },

        openPay(po) {
            this.selPo = po;
            this.pf = { amount: parseFloat(po.balance_due ?? 0), payment_method:'cash',
                        payment_date: new Date().toISOString().slice(0,10),
                        reference_number:'', account_id: this.cashAccounts[0]?.id ?? null,
                        cheque_type:'issued', received_cheque_id:null,
                        cheque_number:'', bank_name:'', cheque_date:'' };
            this.showPay = true;
        },

        async submitPay() {
            if (!this.pf.amount || this.pf.amount <= 0) { toast('Enter a payment amount', 'error'); return; }
            if ((this.pf.payment_method === 'cash' || this.pf.payment_method === 'bank_transfer') && !this.pf.account_id) {
                toast('Please select the account', 'error'); return;
            }
            if (this.pf.payment_method === 'cheque' && this.pf.cheque_type === 'issued') {
                if (!this.pf.account_id)    { toast('Select a bank account', 'error'); return; }
                if (!this.pf.cheque_number) { toast('Enter cheque number', 'error'); return; }
                if (!this.pf.bank_name)     { toast('Enter bank name', 'error'); return; }
                if (!this.pf.cheque_date)   { toast('Enter cheque date', 'error'); return; }
            }
            if (this.pf.payment_method === 'cheque' && this.pf.cheque_type === 'received' && !this.pf.received_cheque_id) {
                toast('Select a received cheque', 'error'); return;
            }
            this.paying = true;
            try {
                const r = await apiFetch('/purchase-orders/' + this.selPo.id + '/payment', {
                    method: 'POST', body: JSON.stringify({
                        amount:             this.pf.amount,
                        payment_method:     this.pf.payment_method,
                        payment_date:       this.pf.payment_date,
                        reference_number:   this.pf.reference_number || null,
                        account_id:         this.pf.account_id ? parseInt(this.pf.account_id) : null,
                        cheque_type:        this.pf.cheque_type,
                        received_cheque_id: this.pf.received_cheque_id ? parseInt(this.pf.received_cheque_id) : null,
                        cheque_number:      this.pf.cheque_number || null,
                        bank_name:          this.pf.bank_name || null,
                        cheque_date:        this.pf.cheque_date || null,
                    }),
                });
                const d = await r.json();
                if (r.ok) {
                    await this.load(); // refreshes both this page's rows and the stat cards (paid/outstanding changed)
                    this.showPay = false;
                    toast('Payment recorded and journal posted', 'success');
                } else {
                    toast(d.message ?? 'Payment failed', 'error');
                }
            } finally { this.paying = false; }
        },

        async openReceive(po) {
            this.selReceivePo = po;
            this.receiveFormItems = [];
            this.receiveRemovedIds = [];
            this.receiveLoading = true;
            this.showReceive = true;

            try {
                // Re-fetch fresh rather than trusting the row already on this page —
                // the list can go stale (e.g. just-created GRN items) and this modal
                // writes real stock.
                const fresh = await apiFetch('/purchase-orders/' + po.id).then(r => r.json());
                this.selReceivePo = fresh;

                const draftGrn = (fresh.grns ?? []).find(g => g.status === 'draft');
                if (!draftGrn || !draftGrn.items || !draftGrn.items.length) {
                    this.receiveLoading = false;
                    return;
                }

                const branchId = parseInt(localStorage.getItem('medri_branch')) || null;
                const prodR = await apiFetch('/products?per_page=5000' + (branchId ? '&branch_id=' + branchId : '')).then(r => r.json());
                const prodList = prodR.data ?? prodR ?? [];
                const prodMap = {};
                prodList.forEach(p => {
                    prodMap[p.id] = {
                        name: p.name,
                        unit: p.unit,
                        selling_price: parseFloat(p.selling_price || 0),
                        stock: parseFloat((p.branchStocks?.[0]?.quantity) ?? 0),
                    };
                });
                this.receiveProductMap = prodMap;
                this.receiveProductList = prodList;

                this.receiveFormItems = draftGrn.items.map(item => {
                    const qty = Number(item.quantity_received) || Number(item.quantity_ordered) || 0;
                    return {
                        grn_item_id:           item.id,
                        product_id:            item.product_id,
                        original_product_id:   item.product_id, // what this line started as — used to flag a supplier substitution
                        product_name:          item.product?.name ?? item.product_name ?? '—',
                        unit:                  item.product?.unit ?? item.unit ?? '',
                        quantity_received:     qty,
                        unit_cost:             Number(item.unit_cost) || 0,
                        batch_number:          item.batch_number ?? '',
                        expiry_date:           item.expiry_date ? item.expiry_date.slice(0, 10) : '',
                        current_stock:         prodMap[item.product_id]?.stock ?? 0,
                        current_selling_price: prodMap[item.product_id]?.selling_price ?? 0,
                        selling_price:         prodMap[item.product_id]?.selling_price ?? 0,
                    };
                });
            } catch (e) {
                toast('Could not load product details', 'error');
            } finally {
                this.receiveLoading = false;
            }
        },

        // Fired when a row's product picker selection changes — both for swapping an
        // existing line to a supplier substitute and for picking a product on a
        // freshly added row. Refreshes the row's stock/price context to the NEW
        // product's own values rather than leaving the old product's.
        onReceiveRowProductChange(row) {
            const p = this.receiveProductMap[row.product_id];
            if (!p) return;
            row.product_name = p.name;
            row.unit = p.unit;
            row.current_stock = p.stock;
            row.current_selling_price = p.selling_price;
            row.selling_price = p.selling_price;
        },
        addReceiveRow() {
            this._receiveTempSeq = (this._receiveTempSeq || 0) + 1;
            this.receiveFormItems.push({
                grn_item_id: null,
                _tempId: 'new-' + this._receiveTempSeq,
                product_id: '',
                original_product_id: null, // never had one — this line wasn't on the PO at all
                product_name: '',
                unit: '',
                quantity_received: 1,
                unit_cost: 0,
                batch_number: '',
                expiry_date: '',
                current_stock: 0,
                current_selling_price: 0,
                selling_price: 0,
            });
        },
        removeReceiveRow(row) {
            // An existing line (has a grn_item_id) must be flagged for the backend
            // to actually delete — just dropping it from this array would leave it
            // in the GRN untouched, so it'd still get received as-is.
            if (row.grn_item_id) this.receiveRemovedIds.push(row.grn_item_id);
            this.receiveFormItems = this.receiveFormItems.filter(r => r !== row);
        },

        async submitReceive() {
            const draftGrn = (this.selReceivePo?.grns ?? []).find(g => g.status === 'draft');
            if (!draftGrn) { toast('No pending GRN found', 'error'); return; }
            if (this.receiveFormItems.some(row => !row.product_id)) {
                toast('Select a product for every line before confirming', 'error'); return;
            }
            this.receiving = true;
            try {
                const existingRows = this.receiveFormItems.filter(row => row.grn_item_id);
                const newRows      = this.receiveFormItems.filter(row => !row.grn_item_id);
                const r = await apiFetch('/grns/' + draftGrn.id + '/confirm', {
                    method: 'POST',
                    body: JSON.stringify({
                        items: existingRows.map(row => ({
                            grn_item_id:        row.grn_item_id,
                            product_id:          row.product_id, // only applied when it differs from the line's original product (a supplier substitution)
                            quantity_received:  parseFloat(row.quantity_received),
                            unit_cost:          parseFloat(row.unit_cost),
                            batch_number:       row.batch_number || null,
                            expiry_date:        row.expiry_date  || null,
                            selling_price:      row.selling_price > 0 ? parseFloat(row.selling_price) : null,
                        })),
                        new_items: newRows.map(row => ({
                            product_id:          row.product_id,
                            quantity_received:  parseFloat(row.quantity_received),
                            unit_cost:          parseFloat(row.unit_cost),
                            batch_number:       row.batch_number || null,
                            expiry_date:        row.expiry_date  || null,
                            selling_price:      row.selling_price > 0 ? parseFloat(row.selling_price) : null,
                        })),
                        removed_grn_item_ids: this.receiveRemovedIds,
                    }),
                });
                const d = await r.json();
                if (r.ok) {
                    const idx = this.items.findIndex(i => i.id === this.selReceivePo.id);
                    if (idx !== -1) {
                        const grns = this.items[idx].grns ?? [];
                        const gi = grns.findIndex(g => g.id === draftGrn.id);
                        if (gi !== -1) grns[gi].status = 'confirmed';
                        this.items[idx] = { ...this.items[idx], grns, status: 'received' };
                    }
                    this.showReceive = false;
                    toast('Items received — stock and selling prices updated', 'success');
                } else {
                    toast(d.message ?? 'Failed to confirm receipt', 'error');
                }
            } finally {
                this.receiving = false;
            }
        },

        grnLabel(po) {
            if (!po.grns || po.grns.length === 0) return 'No GRN';
            const statuses = po.grns.map(g => g.status);
            if (statuses.some(s => s === 'confirmed')) return 'Received';
            if (statuses.some(s => s === 'partially_received')) return 'Partial';
            if (statuses.every(s => s === 'draft')) return 'GRN Draft';
            return 'GRN ' + po.grns.length;
        },
        grnBadge(po) {
            if (!po.grns || po.grns.length === 0) return 'badge badge-gray';
            const statuses = po.grns.map(g => g.status);
            if (statuses.some(s => s === 'confirmed')) return 'badge badge-success';
            if (statuses.some(s => s === 'partially_received')) return 'badge badge-warning';
            if (statuses.every(s => s === 'draft')) return 'badge badge-primary';
            return 'badge badge-gray';
        },
        payLabel(s)  { return { unpaid:'Unpaid', partially_paid:'Partial', paid:'Paid' }[s ?? 'unpaid'] ?? 'Unpaid'; },
        payBadge(s)  { return { unpaid:'bg-red-100 text-red-700', partially_paid:'bg-yellow-100 text-yellow-700', paid:'bg-green-100 text-green-700' }[s ?? 'unpaid'] ?? 'bg-gray-100 text-gray-500'; },
        fmtMoney(v)  { return 'Rs. ' + (parseFloat(v)||0).toLocaleString('en-LK',{minimumFractionDigits:2,maximumFractionDigits:2}); },
        fmtDate(d)   { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}); },
        fmtCompact(n) { const v = Math.abs(Number(n??0)); if(v>=1e6) return (v/1e6).toFixed(1)+'M'; if(v>=1e3) return (v/1e3).toFixed(1)+'K'; return v.toFixed(0); },
    };
}
</script>
@endpush
