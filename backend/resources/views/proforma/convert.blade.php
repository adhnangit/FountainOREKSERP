@extends('layouts.app')
@section('title', 'Convert Proforma to Invoice')
@section('page-title', 'Convert to Invoice')
@section('page-desc', 'Review, edit and confirm the proforma invoice')

@section('content')
<div x-data="proformaConvert()" x-init="init()" class="px-6 pb-12">

  <!-- Loading -->
  <template x-if="loading">
    <div class="flex items-center justify-center py-24 text-gray-400">
      <svg class="animate-spin w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
      Loading proforma…
    </div>
  </template>

  <template x-if="!loading">
    <div>

      <!-- Back + proforma badge -->
      <div class="flex items-center gap-3 mb-6">
        <a :href="BASE + '/proforma-invoices/' + proformaId"
           class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-100 transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 19l-7-7 7-7"/></svg>
          Back to Proforma
        </a>
        <span class="text-gray-300 dark:text-gray-600">|</span>
        <span class="text-sm text-gray-500">Converting:
          <strong class="text-gray-700 dark:text-gray-200" x-text="proforma?.invoice_number || ('#PI-'+proformaId)"></strong>
        </span>
      </div>

      <div class="flex flex-col lg:flex-row gap-6">

        {{-- ══ LEFT COLUMN ══ --}}
        <div class="w-full lg:flex-[65] min-w-0 space-y-5">

          {{-- Line Items --}}
          <div class="card overflow-hidden">
            <div class="flex items-center justify-between px-6 py-4"
                 style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%)">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                     style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
                  <svg style="width:18px;height:18px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                  </svg>
                </div>
                <div>
                  <h3 class="text-sm font-bold text-white">Line Items</h3>
                  <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">
                    <span x-text="items.length"></span> product<span x-show="items.length !== 1">s</span>
                    &nbsp;·&nbsp;<span class="font-semibold text-white" x-text="fmtMoney(grandTotal)"></span>
                  </p>
                </div>
              </div>
              <button type="button" @click="addRow()"
                      class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all active:scale-95"
                      style="background:rgba(255,255,255,0.18);color:#fff;border:1px solid rgba(255,255,255,0.25)"
                      onmouseover="this.style.background='rgba(255,255,255,0.28)'"
                      onmouseout="this.style.background='rgba(255,255,255,0.18)'">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
                Add Item
              </button>
            </div>

            <div class="divide-y divide-gray-50 dark:divide-gray-700/40">
              <template x-for="(row, idx) in items" :key="idx">
                <div class="group px-5 py-4 hover:bg-blue-50/40 dark:hover:bg-blue-900/10 transition-colors">
                  <div class="flex items-start gap-3">
                    <div class="flex-shrink-0 mt-1 w-6 h-6 rounded-md flex items-center justify-center text-xs font-bold"
                         style="background:#eef2ff;border:1px solid #c7d2fe;color:#1B3EB6"
                         x-text="idx + 1"></div>
                    <div class="flex-1 min-w-0">

                      {{-- Searchable product dropdown --}}
                      <div class="flex items-center gap-2 mb-2.5">
                        <div class="search-dd flex-1 min-w-0"
                             x-data="{ open: false, q: '' }"
                             @click.away="open = false"
                             @keydown.escape="open = false">
                          <button type="button"
                                  @click="open = !open; if(open) $nextTick(() => $refs.ps?.focus())"
                                  class="input text-sm w-full text-left flex items-center justify-between gap-2"
                                  :class="!row.product_id ? 'border-blue-200 dark:border-blue-700/60' : ''">
                            <span class="truncate"
                                  :class="row.product_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                                  x-text="row.product_id ? (products.find(p => p.id == row.product_id)?.name || row.product_name || '—') : '— Select product —'"></span>
                            <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                          </button>
                          <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu">
                            <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                              <input x-ref="ps" x-model="q" type="text"
                                     placeholder="Search by name or SKU…"
                                     class="input text-sm w-full py-1.5" @keydown.stop />
                            </div>
                            <div class="max-h-52 overflow-y-auto py-1">
                              <template x-for="p in products.filter(p => !q || p.name.toLowerCase().includes(q.toLowerCase()) || (p.code && p.code.toLowerCase().includes(q.toLowerCase())))" :key="p.id">
                                <button type="button"
                                        @click="row.product_id = p.id; fillProduct(row); open = false; q = ''"
                                        class="search-dd-item"
                                        :class="row.product_id == p.id ? 'active' : ''">
                                  <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate" x-text="p.name"></div>
                                    <div class="text-xs text-gray-400" x-text="(p.code || '') + (p.unit ? ' · ' + p.unit : '')"></div>
                                  </div>
                                  <div class="flex items-center gap-2 flex-shrink-0">
                                    <div class="text-right">
                                      <div class="text-xs font-semibold tabular-nums"
                                           :class="stockQty(p) <= 0 ? 'text-red-500' : stockQty(p) <= (p.reorder_level||5) ? 'text-orange-500' : 'text-green-600'"
                                           x-text="stockQty(p) + ' ' + (p.unit||'pcs')"></div>
                                      <div class="text-[10px] text-gray-400">in stock</div>
                                    </div>
                                    <template x-if="row.product_id == p.id">
                                      <div class="w-4 h-4 rounded-full flex items-center justify-center" style="background:#1B3EB6">
                                        <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                                      </div>
                                    </template>
                                  </div>
                                </button>
                              </template>
                              <div x-show="products.filter(p => !q || p.name.toLowerCase().includes(q.toLowerCase())).length === 0"
                                   class="px-4 py-3 text-xs text-gray-400 text-center">No products found</div>
                            </div>
                          </div>
                        </div>
                        <button type="button" @click="removeRow(idx)" x-show="items.length > 1"
                                class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                      </div>

                      {{-- Qty + Price + Discount + Total --}}
                      <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                        <div class="flex items-center rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden flex-shrink-0" style="height:36px">
                          <button type="button" @click="row.quantity = Math.max(0.01,(row.quantity||1)-1); calcLine(row)"
                                  class="w-8 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors border-r border-gray-200 dark:border-gray-600 h-full text-lg leading-none select-none">−</button>
                          <input type="number" x-model.number="row.quantity" @input="calcLine(row)" min="0.01" step="0.01"
                                 class="w-14 text-center text-sm font-semibold text-gray-700 dark:text-gray-200 bg-transparent border-none outline-none h-full tabular-nums" style="box-shadow:none" />
                          <button type="button" @click="row.quantity = (row.quantity||0)+1; calcLine(row)"
                                  class="w-8 flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors border-l border-gray-200 dark:border-gray-600 h-full text-lg leading-none select-none">+</button>
                        </div>
                        <div class="flex-1 min-w-0 relative">
                          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium pointer-events-none">Rs.</span>
                          <input type="number" x-model.number="row.unit_price" @input="calcLine(row)" min="0" step="0.01" placeholder="0.00"
                                 class="input text-sm text-right tabular-nums w-full" style="padding-left:2rem" />
                        </div>
                        <div class="flex items-center rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden flex-shrink-0" style="height:36px;width:80px">
                          <input type="number" x-model.number="row.discount" @input="calcLine(row)" min="0" max="100" step="0.5" placeholder="0"
                                 class="flex-1 w-0 text-center text-sm text-gray-700 dark:text-gray-200 bg-transparent border-none outline-none h-full tabular-nums" style="box-shadow:none" />
                          <span class="pr-2 text-xs text-gray-400 font-medium">%</span>
                        </div>
                        <div class="text-right flex-shrink-0 min-w-[90px]">
                          <div class="text-xs text-gray-400 mb-0.5">Total</div>
                          <div class="text-sm font-bold tabular-nums" style="color:#1B3EB6" x-text="fmtMoney(row.total)"></div>
                        </div>
                      </div>

                      {{-- Stock badge + warning --}}
                      <template x-if="row.product_id">
                        <div class="flex items-center gap-2 mt-1.5">
                          <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs"
                               :class="stockQty(products.find(p=>p.id==row.product_id)) <= 0
                                 ? 'bg-red-50 text-red-600 border border-red-200'
                                 : stockQty(products.find(p=>p.id==row.product_id)) <= (products.find(p=>p.id==row.product_id)?.reorder_level||5)
                                   ? 'bg-orange-50 text-orange-600 border border-orange-200'
                                   : 'bg-green-50 text-green-600 border border-green-200'">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            <span x-text="'Stock: ' + stockQty(products.find(p=>p.id==row.product_id)) + ' ' + (products.find(p=>p.id==row.product_id)?.unit||'pcs')"></span>
                          </div>
                          <div x-show="row.quantity > stockQty(products.find(p=>p.id==row.product_id))"
                               class="flex items-center gap-1 text-xs text-red-600 font-medium">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            Exceeds available stock
                          </div>
                        </div>
                      </template>

                      <input type="text" x-model="row.description"
                             placeholder="Description / batch / notes (optional)"
                             class="input text-xs mt-2 w-full bg-gray-50/70 dark:bg-gray-800/40 text-gray-500" />
                    </div>
                  </div>
                </div>
              </template>

              <div x-show="items.length === 0" class="py-16 text-center">
                <p class="text-sm text-gray-400">No items — click <strong>Add Item</strong></p>
              </div>
            </div>

            {{-- Totals footer --}}
            <div class="border-t border-gray-100 dark:border-gray-700">
              <div class="px-6 pt-4 pb-2 flex justify-end">
                <div class="w-80 space-y-2">
                  <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                    <span>Subtotal</span>
                    <span class="font-medium tabular-nums" x-text="fmtMoney(subtotal)"></span>
                  </div>
                  <div class="flex justify-between text-sm" x-show="discountTotal > 0">
                    <span class="text-gray-500">Discount</span>
                    <span class="font-medium tabular-nums text-red-500" x-text="'– ' + fmtMoney(discountTotal)"></span>
                  </div>
                  <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1.5">
                      Tax
                      <span class="text-xs">(<input type="number" x-model.number="taxRate" @input="calcTotals()"
                              min="0" max="100" step="0.1"
                              class="w-10 bg-transparent border-b border-gray-300 dark:border-gray-600 text-center text-xs focus:outline-none focus:border-blue-400 tabular-nums" />%)</span>
                    </span>
                    <span class="tabular-nums font-medium" x-text="fmtMoney(taxAmount)"></span>
                  </div>
                </div>
              </div>
              <div class="mx-4 mb-4 rounded-xl px-6 py-4 flex items-center justify-between"
                   style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
                <div>
                  <div class="text-xs font-semibold uppercase tracking-wider mb-0.5" style="color:rgba(255,255,255,0.7)">Invoice Total</div>
                  <div class="text-xs" style="color:rgba(255,255,255,0.55)">
                    <span x-text="items.length"></span> item<span x-show="items.length !== 1">s</span>
                    <span x-show="taxRate > 0">&nbsp;· <span x-text="taxRate"></span>% tax</span>
                  </div>
                </div>
                <div class="text-2xl font-black tabular-nums text-white" x-text="fmtMoney(grandTotal)"></div>
              </div>
            </div>
          </div>

          {{-- Notes --}}
          <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40">
              <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Notes &amp; Terms</h3>
            </div>
            <div class="px-6 py-4">
              <textarea x-model="form.notes" rows="4"
                        placeholder="Payment terms, delivery instructions…"
                        class="input resize-none w-full text-sm"></textarea>
            </div>
          </div>

        </div>{{-- end left --}}

        {{-- ══ RIGHT COLUMN ══ --}}
        <div class="w-full lg:flex-[35]">
        <div class="lg:sticky lg:top-6 space-y-5">

          {{-- Invoice Details --}}
          <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700"
                 style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
                  <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                </div>
                <div>
                  <h3 class="text-sm font-bold text-white">Invoice Details</h3>
                  <p class="text-xs" style="color:rgba(255,255,255,0.6)" x-text="proforma?.customer?.name || ''"></p>
                </div>
              </div>
            </div>
            <div class="px-5 py-4 space-y-4">
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="label">Invoice Date <span class="text-red-500">*</span></label>
                  <input type="date" x-model="form.invoice_date" class="input" required />
                </div>
                <div>
                  <label class="label">Due Date</label>
                  <input type="date" x-model="form.due_date" class="input" />
                </div>
              </div>
              <div>
                <label class="label">Payment Terms (days)</label>
                <input type="number" x-model.number="form.payment_terms_days" min="0" class="input text-sm" placeholder="30" />
              </div>
            </div>
          </div>

          {{-- Order Summary --}}
          <div class="card overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40">
              <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Order Summary</h3>
            </div>
            <div class="p-5 space-y-3">
              <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                <span>Subtotal</span><span class="font-medium tabular-nums" x-text="fmtMoney(subtotal)"></span>
              </div>
              <div class="flex justify-between text-sm text-red-500" x-show="discountTotal > 0">
                <span>Discount</span><span class="font-medium tabular-nums" x-text="'– ' + fmtMoney(discountTotal)"></span>
              </div>
              <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400" x-show="taxAmount > 0">
                <span>Tax (<span x-text="taxRate"></span>%)</span>
                <span class="font-medium tabular-nums" x-text="fmtMoney(taxAmount)"></span>
              </div>
              <div class="border-t border-dashed border-gray-200 dark:border-gray-600 pt-3 flex justify-between items-center">
                <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Total</span>
                <span class="text-xl font-black tabular-nums" style="color:#1B3EB6" x-text="fmtMoney(grandTotal)"></span>
              </div>
            </div>
          </div>

          {{-- Record Payment --}}
          <div class="card">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40 rounded-t-xl">
              <div class="flex items-center justify-between gap-3">
                <div>
                  <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Record Payment</h3>
                  <p class="text-xs text-gray-400 mt-0.5">Collect payment at time of conversion</p>
                </div>
                <label class="relative flex-shrink-0 cursor-pointer" style="width:40px;height:22px">
                  <input type="checkbox" x-model="payment.enabled" class="sr-only peer" />
                  <div class="w-full h-full rounded-full transition-colors"
                       :style="payment.enabled ? 'background:#1B3EB6' : 'background:#d1d5db'"></div>
                  <div class="absolute top-0.5 left-0.5 w-[18px] h-[18px] bg-white rounded-full shadow transition-transform"
                       :style="payment.enabled ? 'transform:translateX(18px)' : ''"></div>
                </label>
              </div>
            </div>

            <div x-show="payment.enabled"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="px-5 py-4 space-y-3">

              <div>
                <label class="label">Payment Method <span class="text-red-500">*</span></label>
                <select x-model="payment.method"
                        @change="payment.cheque_number=''; payment.bank_name=''; payment.cheque_date=''"
                        class="input text-sm">
                  <option value="cash">Cash</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="cheque">Cheque</option>
                  <option value="credit_card">Credit Card</option>
                  <option value="online">Online</option>
                </select>
              </div>

              <div>
                <label class="label">Amount <span class="text-red-500">*</span></label>
                <div class="relative">
                  <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium pointer-events-none">Rs.</span>
                  <input type="number" x-model.number="payment.amount" min="0.01" step="0.01"
                         class="input text-sm text-right tabular-nums w-full" style="padding-left:2rem" />
                </div>
                <p class="text-xs text-gray-400 mt-1 text-right">
                  Total: <span class="font-semibold" x-text="fmtMoney(grandTotal)"></span>
                </p>
              </div>

              <div>
                <label class="label">Payment Date <span class="text-red-500">*</span></label>
                <input type="date" x-model="payment.date" class="input text-sm" />
              </div>

              <div>
                <label class="label">Reference #</label>
                <input type="text" x-model="payment.reference" placeholder="Optional" class="input text-sm" />
              </div>

              <div x-show="payment.method === 'cheque'" x-transition
                   class="space-y-3 p-3 rounded-xl border"
                   style="background:#fffbeb;border-color:#fde68a">
                <p class="text-xs font-semibold" style="color:#92400e">Cheque Details</p>
                <div>
                  <label class="label">Cheque Number <span class="text-red-500">*</span></label>
                  <input type="text" x-model="payment.cheque_number" class="input text-sm" placeholder="e.g. 001234" />
                </div>
                <div>
                  <label class="label">Bank Name <span class="text-red-500">*</span></label>
                  <div x-data="{bq:'',bOpen:false}" @click.outside="bOpen=false" class="relative">
                    <input type="text" :value="payment.bank_name"
                      @input="payment.bank_name=$event.target.value;bq=$event.target.value;bOpen=true"
                      @focus="bq=payment.bank_name||'';bOpen=true" @keydown.escape="bOpen=false"
                      class="input text-sm" placeholder="Search bank…" autocomplete="off" />
                    <ul x-show="bOpen" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-44 overflow-y-auto">
                      <template x-for="b in banks.filter(b=>b.name.toLowerCase().includes(bq.toLowerCase()))" :key="b.id">
                        <li @mousedown.prevent="payment.bank_name=b.name;bq=b.name;bOpen=false"
                            :class="payment.bank_name===b.name?'bg-indigo-50 text-indigo-700 font-medium':'hover:bg-gray-50 text-gray-700'"
                            class="px-3 py-2 text-sm cursor-pointer" x-text="b.name"></li>
                      </template>
                      <li x-show="!banks.filter(b=>b.name.toLowerCase().includes(bq.toLowerCase())).length" class="px-3 py-2 text-sm text-gray-400 text-center">No banks found</li>
                    </ul>
                  </div>
                </div>
                <div>
                  <label class="label">Cheque Date <span class="text-red-500">*</span></label>
                  <input type="date" x-model="payment.cheque_date" class="input text-sm" />
                </div>
              </div>

            </div>
          </div>

          {{-- Actions --}}
          <div class="space-y-2.5">
            <button type="button" @click="submit()" :disabled="submitting"
                    class="w-full flex items-center justify-center gap-2 py-2.5 rounded-lg font-semibold text-sm text-white transition-all shadow hover:shadow-lg active:scale-[0.98]"
                    style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
              <template x-if="submitting">
                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
              </template>
              <template x-if="!submitting">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </template>
              <span x-text="submitting ? 'Converting…' : 'Confirm & Convert to Invoice'"></span>
            </button>
            <a :href="BASE + '/proforma-invoices/' + proformaId"
               class="btn-secondary w-full flex items-center justify-center gap-2 py-2.5 text-center">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
              Cancel
            </a>
          </div>

        </div>{{-- end sticky --}}
        </div>{{-- end right --}}

      </div>
    </div>
  </template>

</div>
@endsection

@push('scripts')
<script>
function proformaConvert() {
  const proformaId = location.pathname.split('/').filter(Boolean).slice(-2)[0];
  return {
    loading:    true,
    submitting: false,
    proformaId,
    proforma:   null,
    products:   [], banks: [],
    items:      [],
    taxRate:    0,
    subtotal:   0,
    discountTotal: 0,
    taxAmount:  0,
    grandTotal: 0,
    form: {
      invoice_date:       new Date().toISOString().slice(0, 10),
      due_date:           '',
      payment_terms_days: 30,
      notes:              '',
    },
    payment: {
      enabled:       false,
      method:        'cash',
      amount:        0,
      date:          new Date().toISOString().slice(0, 10),
      reference:     '',
      cheque_number: '',
      bank_name:     '',
      cheque_date:   '',
    },

    async init() {
      try {
        const [proformaR, productsR] = await Promise.all([
          apiFetch('/proforma-invoices/' + proformaId),
          apiFetch('/products?per_page=999').then(r => r.json()),
        ]);
        this.proforma = await proformaR.json();
        this.products = productsR.data || productsR || [];
        this.banks    = await loadBanks();

        // Pre-fill form from proforma
        this.form.due_date = this.proforma.valid_until
          ? this.proforma.valid_until.slice(0, 10) : '';
        this.form.notes = this.proforma.notes || '';

        // Pre-fill items from proforma
        this.items = (this.proforma.items || []).map(it => ({
          product_id:   it.product_id,
          product_name: it.product?.name || it.product_name || '',
          description:  '',
          quantity:     parseFloat(it.quantity || it.qty || 1),
          unit_price:   parseFloat(it.unit_price || 0),
          discount:     parseFloat(it.discount_percent || it.discount || 0),
          total:        parseFloat(it.total || it.line_total || 0),
        }));
        this.calcTotals();
      } catch (e) {
        toast('Failed to load proforma', 'error');
      }
      this.loading = false;
      this.$watch('payment.enabled', v => { if (v) this.payment.amount = this.grandTotal; });
    },

    stockQty(p) {
      if (!p) return 0;
      const s = p.branch_stocks?.[0] ?? p.branchStocks?.[0];
      return parseFloat(s?.quantity ?? s?.stock ?? 0);
    },

    addRow() {
      this.items.push({ product_id: '', product_name: '', description: '', quantity: 1, unit_price: 0, discount: 0, total: 0 });
    },

    removeRow(i) { this.items.splice(i, 1); this.calcTotals(); },

    fillProduct(row) {
      const p = this.products.find(x => x.id == row.product_id);
      if (p) {
        row.product_name = p.name;
        row.unit_price   = parseFloat(p.selling_price || p.price || 0);
      }
      this.calcLine(row);
    },

    calcLine(row) {
      const base = (row.quantity || 0) * (row.unit_price || 0);
      row.total  = base - (base * ((row.discount || 0) / 100));
      this.calcTotals();
    },

    calcTotals() {
      this.subtotal      = this.items.reduce((s, r) => s + ((r.quantity||0)*(r.unit_price||0)), 0);
      this.discountTotal = this.items.reduce((s, r) => s + ((r.quantity||0)*(r.unit_price||0)*((r.discount||0)/100)), 0);
      this.taxAmount     = (this.subtotal - this.discountTotal) * ((this.taxRate||0) / 100);
      this.grandTotal    = this.subtotal - this.discountTotal + this.taxAmount;
      if (this.payment.enabled) this.payment.amount = this.grandTotal;
    },

    async submit() {
      if (!this.items.length)             { toast('Add at least one item', 'error'); return; }
      if (!this.form.invoice_date)        { toast('Invoice date is required', 'error'); return; }
      const missingProduct = this.items.find(r => !r.product_id);
      if (missingProduct)                 { toast('Please select a product for all items', 'error'); return; }

      if (this.payment.enabled) {
        if (!this.payment.amount || this.payment.amount <= 0) { toast('Enter a payment amount', 'error'); return; }
        if (this.payment.method === 'cheque') {
          if (!this.payment.cheque_number) { toast('Enter cheque number', 'error'); return; }
          if (!this.payment.bank_name)     { toast('Enter bank name', 'error'); return; }
          if (!this.payment.cheque_date)   { toast('Enter cheque date', 'error'); return; }
        }
      }

      this.submitting = true;
      try {
        const payload = {
          invoice_date:       this.form.invoice_date,
          due_date:           this.form.due_date || null,
          payment_terms_days: this.form.payment_terms_days || 30,
          tax_percent:        this.taxRate || 0,
          notes:              this.form.notes || null,
          items: this.items.map(r => ({
            product_id:       r.product_id,
            quantity:         r.quantity,
            unit_price:       r.unit_price,
            discount_percent: r.discount || 0,
          })),
        };

        const r = await apiFetch('/proforma-invoices/' + proformaId + '/convert', {
          method: 'POST',
          body: JSON.stringify(payload),
        });
        const d = await r.json();

        if (r.ok) {
          // If payment enabled, record it on the new invoice
          if (this.payment.enabled && this.payment.amount > 0) {
            await apiFetch('/invoices/' + d.invoice_id + '/payment', {
              method: 'POST',
              body: JSON.stringify({
                amount:           this.payment.amount,
                payment_method:   this.payment.method,
                payment_date:     this.payment.date,
                reference_number: this.payment.reference || null,
                cheque_number:    this.payment.cheque_number || null,
                bank_name:        this.payment.bank_name || null,
                cheque_date:      this.payment.cheque_date || null,
              }),
            });
          }
          toast('Converted successfully! Redirecting…', 'success');
          setTimeout(() => window.location.href = BASE + '/invoices/' + d.invoice_id, 800);
        } else {
          toast(d.message || 'Conversion failed', 'error');
        }
      } catch (e) {
        toast('An error occurred', 'error');
      }
      this.submitting = false;
    },
  };
}
</script>
@endpush
