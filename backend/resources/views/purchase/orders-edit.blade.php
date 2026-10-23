@extends('layouts.app')
@section('title', 'Edit Supplier Invoice')
@section('page-title', 'Edit Supplier Invoice')
@section('page-desc', 'Update supplier invoice details')

@push('head')
<style>
  /* This page uses the green/purchase accent instead of the default blue for its searchable dropdowns */
  .search-dd-item:hover { background:#f0fdf4; }
  .dark .search-dd-item:hover { background:rgba(16,185,129,0.1); }
  .search-dd-item.active { background:#f0fdf4; }
  .dark .search-dd-item.active { background:rgba(16,185,129,0.12); }
</style>
@endpush

@section('content')
<div x-data="poEditPage()" x-init="init()" class="px-6 pb-12">
<template x-if="loading">
  <div class="py-16 text-center text-sm text-gray-400">Loading invoice…</div>
</template>
<form x-show="!loading" @submit.prevent="submit()">
<div class="flex flex-col lg:flex-row gap-6">

  {{-- ══ LEFT COLUMN ══ --}}
  <div class="w-full lg:flex-[65] min-w-0 space-y-5">

    {{-- Locked notice --}}
    <div x-show="itemsLocked" class="rounded-xl px-4 py-3 text-xs font-medium flex items-start gap-2"
         style="background:#fffbeb;border:1px solid #fde68a;color:#92400e">
      <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
      <span>Items can't be changed — this invoice already has a payment recorded or its goods receipt has been confirmed. You can still update the details on the right.</span>
    </div>

    {{-- Items Cart --}}
    <div class="card overflow-hidden">

      {{-- Header --}}
      <div class="flex items-center justify-between px-6 py-4"
           style="background:linear-gradient(135deg,#065f46 0%,#064e3b 100%)">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl flex items-center justify-center"
               style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
            <svg style="width:18px;height:18px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-white">Invoice Items</h3>
            <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">
              <span x-text="items.length"></span> item<span x-show="items.length !== 1">s</span>
              &nbsp;·&nbsp;<span class="font-semibold text-white" x-text="fmtMoney(subtotal)"></span>
            </p>
          </div>
        </div>
        <button type="button" @click="addRow()" x-show="!itemsLocked"
                class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all active:scale-95"
                style="background:rgba(255,255,255,0.18);color:#fff;border:1px solid rgba(255,255,255,0.25);"
                onmouseover="this.style.background='rgba(255,255,255,0.28)'"
                onmouseout="this.style.background='rgba(255,255,255,0.18)'">
          <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
          Add Item
        </button>
      </div>

      {{-- Cart Rows --}}
      <div class="divide-y divide-gray-50 dark:divide-gray-700/40">
        <template x-for="(row, idx) in items" :key="idx">
          <div class="group px-5 py-4 hover:bg-emerald-50/40 dark:hover:bg-emerald-900/10 transition-colors">
            <div class="flex items-start gap-3">

              {{-- Row # --}}
              <div class="flex-shrink-0 mt-1 w-6 h-6 rounded-md flex items-center justify-center text-xs font-bold"
                   style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46"
                   x-text="idx + 1"></div>

              <div class="flex-1 min-w-0">
                {{-- Locked read-only row --}}
                <template x-if="itemsLocked">
                  <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                      <div class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate" x-text="row.product_name"></div>
                      <div class="text-xs text-gray-400" x-text="row.qty + ' x Rs.' + row.unit_cost"></div>
                    </div>
                    <div class="text-sm font-bold tabular-nums flex-shrink-0" style="color:#065f46" x-text="fmtMoney(row.line_total)"></div>
                  </div>
                </template>

                <template x-if="!itemsLocked">
                <div>
                {{-- Product / Service toggle --}}
                <div class="flex items-center gap-1 mb-2">
                  <button type="button"
                          @click="row.type = 'product'; row.service_id = ''; row.unit_cost = 0; calcRow(idx)"
                          class="text-[11px] font-semibold px-2.5 py-1 rounded-md transition-colors"
                          :class="row.type !== 'service' ? 'bg-emerald-700 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'">Product</button>
                  <button type="button"
                          @click="row.type = 'service'; row.product_id = ''; row.unit_cost = 0; calcRow(idx)"
                          class="text-[11px] font-semibold px-2.5 py-1 rounded-md transition-colors"
                          :class="row.type === 'service' ? 'bg-emerald-700 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'">Service</button>
                </div>

                {{-- Product dropdown --}}
                <div class="flex items-center gap-2 mb-2.5" x-show="row.type !== 'service'">
                  <div class="search-dd flex-1 min-w-0" @click.away="row.open = false" @keydown.escape="row.open = false">
                    <button type="button"
                            @click="row.open = !row.open; if(row.open){ const r=$el.getBoundingClientRect(); row.ddStyle='top:'+(r.bottom+4)+'px;left:'+r.left+'px;width:'+r.width+'px;'; }"
                            class="input text-sm w-full text-left flex items-center justify-between gap-2"
                            :class="!row.product_id ? 'border-emerald-200 dark:border-emerald-700/60' : ''">
                      <span class="truncate"
                            :class="row.product_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                            x-text="row.product_id ? (products.find(p => p.id == row.product_id)?.name || '—') : '— Select product —'"></span>
                      <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="row.open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="row.open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu" :style="row.ddStyle">
                      <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                        <input x-model="row.q" type="text" placeholder="Search product…"
                               class="input text-sm w-full py-1.5" @keydown.stop />
                      </div>
                      <div class="max-h-52 overflow-y-auto py-1">
                        <template x-for="p in products.filter(p => !row.q || p.name.toLowerCase().includes(row.q.toLowerCase()) || (p.sku && p.sku.toLowerCase().includes(row.q.toLowerCase())))" :key="p.id">
                          <button type="button"
                                  @click="row.product_id = p.id; onProductSelect(row); row.open = false; row.q = ''"
                                  class="search-dd-item"
                                  :class="row.product_id == p.id ? 'active' : ''">
                            <div class="flex-1 min-w-0">
                              <div class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate" x-text="p.name"></div>
                              <div class="text-xs text-gray-400" x-text="(p.sku || '') + (p.unit ? ' · ' + p.unit : '') + (p.cost_price ? ' · Cost: Rs.' + p.cost_price : '')"></div>
                            </div>
                            <template x-if="row.product_id == p.id">
                              <div class="w-4 h-4 rounded-full flex items-center justify-center flex-shrink-0" style="background:#065f46">
                                <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                              </div>
                            </template>
                          </button>
                        </template>
                        <div x-show="products.filter(p => !row.q || p.name.toLowerCase().includes(row.q.toLowerCase())).length === 0"
                             class="px-4 py-3 text-xs text-gray-400 text-center">No products found</div>
                      </div>
                    </div>
                  </div>
                  <button type="button" @click="removeRow(idx)"
                          x-show="items.length > 1"
                          class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all"
                          title="Remove">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>

                {{-- Service dropdown --}}
                <div class="flex items-center gap-2 mb-2.5" x-show="row.type === 'service'">
                  <div class="search-dd flex-1 min-w-0" @click.away="row.open = false" @keydown.escape="row.open = false">
                    <button type="button"
                            @click="row.open = !row.open; if(row.open){ const r=$el.getBoundingClientRect(); row.ddStyle='top:'+(r.bottom+4)+'px;left:'+r.left+'px;width:'+r.width+'px;'; }"
                            class="input text-sm w-full text-left flex items-center justify-between gap-2"
                            :class="!row.service_id ? 'border-emerald-200 dark:border-emerald-700/60' : ''">
                      <span class="truncate"
                            :class="row.service_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                            x-text="row.service_id ? (services.find(s => s.id == row.service_id)?.name || '—') : '— Select service —'"></span>
                      <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="row.open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="row.open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu" :style="row.ddStyle">
                      <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                        <input x-model="row.q" type="text" placeholder="Search service…"
                               class="input text-sm w-full py-1.5" @keydown.stop />
                      </div>
                      <div class="max-h-52 overflow-y-auto py-1">
                        <template x-for="s in services.filter(s => !row.q || s.name.toLowerCase().includes(row.q.toLowerCase()) || (s.code && s.code.toLowerCase().includes(row.q.toLowerCase())))" :key="s.id">
                          <button type="button"
                                  @click="row.service_id = s.id; onServiceSelect(row); row.open = false; row.q = ''"
                                  class="search-dd-item"
                                  :class="row.service_id == s.id ? 'active' : ''">
                            <div class="flex-1 min-w-0">
                              <div class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate" x-text="s.name"></div>
                              <div class="text-xs text-gray-400" x-text="(s.code || '') + (s.unit ? ' · ' + s.unit : '')"></div>
                            </div>
                            <template x-if="row.service_id == s.id">
                              <div class="w-4 h-4 rounded-full flex items-center justify-center flex-shrink-0" style="background:#065f46">
                                <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                              </div>
                            </template>
                          </button>
                        </template>
                        <div x-show="services.filter(s => !row.q || s.name.toLowerCase().includes(row.q.toLowerCase())).length === 0"
                             class="px-4 py-3 text-xs text-gray-400 text-center">No services found</div>
                      </div>
                    </div>
                  </div>
                  <button type="button" @click="removeRow(idx)"
                          x-show="items.length > 1"
                          class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all"
                          title="Remove">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>

                {{-- Qty + Cost --}}
                <div class="flex items-center gap-2">
                  {{-- Qty stepper --}}
                  <div class="flex items-center rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden flex-shrink-0" style="height:36px">
                    <button type="button" @click="row.qty = Math.max(1,(row.qty||1)-1); calcRow(idx)"
                            class="w-8 flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors border-r border-gray-200 dark:border-gray-600 h-full text-lg leading-none select-none">−</button>
                    <input type="number" x-model.number="row.qty" @input="calcRow(idx)" min="1" step="1"
                           class="w-14 text-center text-sm font-semibold text-gray-700 dark:text-gray-200 bg-transparent border-none outline-none h-full tabular-nums" style="box-shadow:none" />
                    <button type="button" @click="row.qty = (row.qty||0)+1; calcRow(idx)"
                            class="w-8 flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors border-l border-gray-200 dark:border-gray-600 h-full text-lg leading-none select-none">+</button>
                  </div>
                  {{-- Unit cost --}}
                  <div class="flex-1 min-w-0 relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium pointer-events-none">Rs.</span>
                    <input type="number" x-model.number="row.unit_cost" @input="calcRow(idx)" min="0" step="0.01" placeholder="0.00"
                           class="input text-sm text-right tabular-nums w-full" style="padding-left:2rem" />
                  </div>
                  {{-- Line total --}}
                  <div class="text-right flex-shrink-0 min-w-[90px]">
                    <div class="text-xs text-gray-400 mb-0.5">Total</div>
                    <div class="text-sm font-bold tabular-nums" style="color:#065f46" x-text="fmtMoney(row.line_total)"></div>
                  </div>
                </div>
                </div>
                </template>
              </div>
            </div>
          </div>
        </template>

        {{-- Empty state --}}
        <div x-show="items.length === 0" class="py-14 text-center">
          <p class="text-sm font-medium text-gray-400">No items added</p>
        </div>
      </div>

      {{-- Total footer --}}
      <div class="border-t border-gray-100 dark:border-gray-700">
        <div class="mx-4 mb-4 mt-4 rounded-xl px-6 py-4 flex items-center justify-between"
             style="background:linear-gradient(135deg,#065f46,#064e3b)">
          <div>
            <div class="text-xs font-semibold uppercase tracking-wider mb-0.5" style="color:rgba(255,255,255,0.7)">Invoice Total</div>
            <div class="text-xs" style="color:rgba(255,255,255,0.55)">
              <span x-text="items.length"></span> item<span x-show="items.length !== 1">s</span>
            </div>
          </div>
          <div class="text-2xl font-black tabular-nums text-white" x-text="fmtMoney(subtotal)"></div>
        </div>
      </div>

    </div>{{-- end cart --}}

    {{-- Notes --}}
    <div class="card overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40">
        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Notes</h3>
        <p class="text-xs text-gray-400 mt-0.5">Delivery instructions or additional remarks</p>
      </div>
      <div class="px-6 py-4">
        <textarea x-model="form.notes" rows="3"
                  placeholder="e.g. Deliver to warehouse B, contact warehouse manager on arrival."
                  class="input resize-none w-full text-sm"></textarea>
      </div>
    </div>

  </div>{{-- end left --}}

  {{-- ══ RIGHT COLUMN ══ --}}
  <div class="w-full lg:flex-[35]">
  <div class="lg:sticky lg:top-6 space-y-5">

    {{-- Payment summary (read-only — use "Pay" from the list to record a payment) --}}
    <div class="card overflow-hidden">
      <div class="px-5 py-4 flex items-center justify-between"
           style="background:linear-gradient(135deg,#065f46,#064e3b)">
        <h3 class="text-sm font-bold text-white">Payment Status</h3>
        <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
              :class="payBadge(form.payment_status)" x-text="payLabel(form.payment_status)"></span>
      </div>
      <div class="px-5 py-4 grid grid-cols-3 gap-3 text-center">
        <div>
          <div class="text-xs text-gray-400 mb-0.5">Total</div>
          <div class="text-sm font-bold tabular-nums" x-text="fmtMoney(subtotal)"></div>
        </div>
        <div>
          <div class="text-xs text-gray-400 mb-0.5">Paid</div>
          <div class="text-sm font-bold tabular-nums text-green-700" x-text="fmtMoney(form.paid_amount)"></div>
        </div>
        <div>
          <div class="text-xs text-gray-400 mb-0.5">Balance</div>
          <div class="text-sm font-bold tabular-nums text-red-600" x-text="fmtMoney(Math.max(0, subtotal - form.paid_amount))"></div>
        </div>
      </div>
      <p class="px-5 pb-4 text-xs text-gray-400">Use "Pay" from the Supplier Invoices list to record a payment.</p>
    </div>

    {{-- Supplier & Details --}}
    <div class="card">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 rounded-t-xl"
           style="background:linear-gradient(135deg,#065f46,#064e3b)">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-white">Supplier &amp; Invoice Details</h3>
            <p class="text-xs" style="color:rgba(255,255,255,0.6)" x-text="form.po_number"></p>
          </div>
        </div>
      </div>
      <div class="px-5 py-4 space-y-4">

        <div>
          <label class="label">Supplier <span class="text-red-500">*</span></label>
          <div class="search-dd" x-data="{ open: false, q: '', ddStyle: '' }" @click.away="open = false" @keydown.escape="open = false">
            <button type="button" @click="open = !open; if(open){ const r=$el.getBoundingClientRect(); ddStyle='top:'+(r.bottom+4)+'px;left:'+r.left+'px;width:'+r.width+'px;'; $nextTick(() => $refs.sPo?.focus()) }"
                    class="input w-full text-left flex items-center justify-between gap-2">
              <span class="truncate" :class="form.supplier_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                    x-text="form.supplier_id ? (suppliers.find(s => s.id == form.supplier_id)?.name || '—') : 'Select supplier…'"></span>
              <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu" :style="ddStyle">
              <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                <input x-ref="sPo" x-model="q" type="text" placeholder="Search supplier…" class="input text-sm w-full py-1.5" @keydown.stop />
              </div>
              <div class="max-h-52 overflow-y-auto py-1">
                <template x-for="s in suppliers.filter(s => !q || s.name.toLowerCase().includes(q.toLowerCase()))" :key="s.id">
                  <button type="button" @click="form.supplier_id = s.id; open = false; q = ''"
                          class="search-dd-item" :class="form.supplier_id == s.id ? 'active' : ''">
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate flex-1" x-text="s.name"></span>
                  </button>
                </template>
                <div x-show="suppliers.filter(s => !q || s.name.toLowerCase().includes(q.toLowerCase())).length === 0"
                     class="px-4 py-3 text-xs text-gray-400 text-center">No suppliers found</div>
              </div>
            </div>
          </div>
        </div>

        <div>
          <label class="label">Invoice Date <span class="text-red-500">*</span></label>
          <input type="date" x-model="form.order_date" class="input" required />
        </div>

        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="label">Due Date</label>
            <input type="date" x-model="form.due_date" class="input" />
          </div>
          <div>
            <label class="label">Expected Delivery</label>
            <input type="date" x-model="form.expected_date" class="input" />
          </div>
        </div>

        <div>
          <label class="label">Supplier Invoice Ref</label>
          <input type="text" x-model="form.supplier_invoice_ref" placeholder="Supplier's invoice number" class="input font-mono" />
        </div>

        <div>
          <label class="label">Our Reference</label>
          <input type="text" x-model="form.reference" placeholder="Internal reference" class="input font-mono" />
        </div>

        <div x-show="form.payment_status === 'unpaid'">
          <label class="label">Payment Terms (days)</label>
          <input type="number" x-model.number="form.payment_terms_days" min="0" placeholder="e.g. 30" class="input text-sm" />
        </div>

      </div>
    </div>

    {{-- Actions --}}
    <div class="flex gap-3">
      <a href="{{ url('/purchase-orders') }}" class="btn-secondary flex-1 text-center">Cancel</a>
      <button type="submit" :disabled="submitting" class="btn-primary flex-1">
        <span x-text="submitting ? 'Saving…' : 'Save Changes'"></span>
      </button>
    </div>

  </div>
  </div>{{-- end right --}}

</div>
</form>
</div>
@endsection

@push('scripts')
<script>
function poEditPage() {
    return {
        get id() {
            const parts = window.location.pathname.split('/').filter(Boolean);
            return parts[parts.length - 2];
        },
        loading: true,
        suppliers: [],
        products: [],
        services: [],
        submitting: false,
        subtotal: 0,
        itemsLocked: false,
        form: {
            po_number: '',
            supplier_id: '',
            order_date: '',
            due_date: '',
            expected_date: '',
            supplier_invoice_ref: '',
            reference: '',
            payment_terms_days: 30,
            notes: '',
            payment_status: 'unpaid',
            paid_amount: 0,
        },
        items: [],
        async init() {
            try {
                const [pr, svr, sr, poR] = await Promise.all([
                    apiFetch('/products?per_page=5000').then(r => r.json()),
                    apiFetch('/services?per_page=999&is_active=1').then(r => r.json()),
                    apiFetch('/suppliers?per_page=200').then(r => r.json()),
                    apiFetch('/purchase-orders/' + this.id).then(r => r.json()),
                ]);
                this.products  = pr.data ?? pr ?? [];
                this.services  = svr.data ?? svr ?? [];
                this.suppliers = sr.data ?? sr ?? [];

                const po = poR.data ?? poR;
                this.form = {
                    po_number: po.po_number,
                    supplier_id: po.supplier_id,
                    order_date: po.order_date ? po.order_date.slice(0, 10) : '',
                    due_date: po.due_date ? po.due_date.slice(0, 10) : '',
                    expected_date: po.expected_date ? po.expected_date.slice(0, 10) : '',
                    supplier_invoice_ref: po.supplier_invoice_ref ?? '',
                    reference: po.reference ?? '',
                    payment_terms_days: po.payment_terms_days ?? 0,
                    notes: po.notes ?? '',
                    payment_status: po.payment_status,
                    paid_amount: parseFloat(po.paid_amount ?? 0),
                };
                this.itemsLocked = parseFloat(po.paid_amount ?? 0) > 0
                    || (po.grns ?? []).some(g => g.status !== 'draft');
                this.items = (po.items ?? []).map(it => ({
                    type: it.item_type ?? (it.service_id ? 'service' : 'product'),
                    product_id: it.product_id ?? '',
                    service_id: it.service_id ?? '',
                    product_name: it.product_name,
                    qty: parseFloat(it.quantity),
                    unit_cost: parseFloat(it.unit_price),
                    line_total: parseFloat(it.total),
                    open: false, q: '', ddStyle: '',
                }));
                this.calcTotals();
            } catch (e) {
                toast('Failed to load invoice', 'error');
            } finally {
                this.loading = false;
            }
        },
        addRow() {
            this.items.push({ type: 'product', product_id: '', service_id: '', qty: 1, unit_cost: 0, line_total: 0, open: false, q: '', ddStyle: '' });
        },
        removeRow(idx) {
            this.items.splice(idx, 1);
            this.calcTotals();
        },
        onProductSelect(row) {
            const p = this.products.find(p => p.id == row.product_id);
            if (p) {
                row.unit_cost = parseFloat(p.cost_price ?? p.price ?? 0);
                row.line_total = (row.qty || 1) * row.unit_cost;
                this.calcTotals();
            }
        },
        onServiceSelect(row) {
            row.line_total = (row.qty || 1) * (row.unit_cost || 0);
            this.calcTotals();
        },
        calcRow(idx) {
            const r = this.items[idx];
            r.line_total = (r.qty || 0) * (r.unit_cost || 0);
            this.calcTotals();
        },
        calcTotals() {
            this.subtotal = this.items.reduce((s, r) => s + (r.line_total || 0), 0);
        },
        fmtMoney(v) {
            return 'Rs. ' + (parseFloat(v) || 0).toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        payBadge(status) {
            return { paid: 'bg-green-100 text-green-700', partially_paid: 'bg-amber-100 text-amber-700', unpaid: 'bg-red-100 text-red-700' }[status] ?? 'bg-gray-100 text-gray-600';
        },
        payLabel(status) {
            return { paid: 'Paid', partially_paid: 'Partial', unpaid: 'Unpaid' }[status] ?? status;
        },
        async submit() {
            if (!this.form.supplier_id) { toast('Please select a supplier', 'error'); return; }
            if (!this.itemsLocked && this.items.some(r => r.type === 'service' ? !r.service_id : !r.product_id)) {
                toast('Please select a product or service for every row', 'error');
                return;
            }
            this.submitting = true;
            try {
                const payload = {
                    supplier_id: this.form.supplier_id,
                    order_date: this.form.order_date,
                    due_date: this.form.due_date || null,
                    expected_date: this.form.expected_date || null,
                    supplier_invoice_ref: this.form.supplier_invoice_ref,
                    reference: this.form.reference,
                    payment_terms_days: this.form.payment_terms_days,
                    notes: this.form.notes,
                };
                if (!this.itemsLocked) {
                    payload.items = this.items.map(r => ({
                        product_id: r.type === 'service' ? null : r.product_id,
                        service_id: r.type === 'service' ? r.service_id : null,
                        quantity:   r.qty,
                        unit_price: r.unit_cost,
                        tax_percent: 0,
                    }));
                }
                await apiFetch('/purchase-orders/' + this.id, { method: 'PUT', body: JSON.stringify(payload) });
                toast('Supplier invoice updated', 'success');
                window.location.href = BASE + '/purchase-orders';
            } catch (e) {
                toast(e.message ?? 'Failed to update invoice', 'error');
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
@endpush
