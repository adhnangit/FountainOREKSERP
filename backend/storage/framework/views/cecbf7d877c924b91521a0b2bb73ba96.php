
<?php $__env->startSection('title', 'New Proforma Invoice'); ?>
<?php $__env->startSection('page-title', 'New Proforma Invoice'); ?>
<?php $__env->startSection('page-desc', 'Draft invoice — no stock or account impact until converted by admin'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="proformaCreate()" x-init="init()" class="px-6 pb-12">

  
  <div class="flex items-start gap-3 px-5 py-4 rounded-xl border border-amber-200 dark:border-amber-700/60 bg-amber-50 dark:bg-amber-900/20 mb-6">
    <div class="w-7 h-7 rounded-full bg-amber-100 dark:bg-amber-800/50 flex-shrink-0 mt-0.5 flex items-center justify-center">
      <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    </div>
    <div>
      <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Proforma Invoice</p>
      <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5">This does <strong>not</strong> affect stock or accounts. An admin will convert it to a real invoice when approved.</p>
    </div>
  </div>

  <div class="flex flex-col lg:flex-row gap-6">

    
    <div class="w-full lg:flex-[65] min-w-0 space-y-5">

      
      <div class="card overflow-hidden">
        
        <div class="flex items-center justify-between px-6 py-4"
             style="background:linear-gradient(135deg,#d97706 0%,#92400e 100%)">
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
                <span x-text="form.lines.length"></span> product<span x-show="form.lines.length !== 1">s</span>
                &nbsp;·&nbsp;<span class="font-semibold text-white" x-text="fmtMoney(totals.total)"></span>
              </p>
            </div>
          </div>
          <button type="button" @click="addLine()"
                  class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all active:scale-95"
                  style="background:rgba(255,255,255,0.18);color:#fff;border:1px solid rgba(255,255,255,0.25)"
                  onmouseover="this.style.background='rgba(255,255,255,0.28)'"
                  onmouseout="this.style.background='rgba(255,255,255,0.18)'">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
            Add Item
          </button>
        </div>

        
        <div class="divide-y divide-gray-50 dark:divide-gray-700/40">
          <template x-for="(line, i) in form.lines" :key="i">
            <div class="group px-5 py-4 hover:bg-amber-50/40 dark:hover:bg-amber-900/10 transition-colors">
              <div class="flex items-start gap-3">

                
                <div class="flex-shrink-0 mt-1 w-6 h-6 rounded-md flex items-center justify-center text-xs font-bold"
                     style="background:#fef3c7;border:1px solid #fde68a;color:#d97706"
                     x-text="i + 1"></div>

                <div class="flex-1 min-w-0">

                  
                  <div class="flex items-center gap-2 mb-2.5">
                    <div class="search-dd flex-1 min-w-0"
                         x-data="{ open: false, q: '' }"
                         @click.away="open = false"
                         @keydown.escape="open = false">
                      <button type="button"
                              @click="open = !open; if(open) $nextTick(() => $refs['ps_' + i]?.focus())"
                              class="input text-sm w-full text-left flex items-center justify-between gap-2"
                              :class="!line.product_id ? 'border-amber-300 dark:border-amber-700/60' : ''">
                        <span class="truncate"
                              :class="line.product_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                              x-text="line.product_id ? (products.find(p => p.id == line.product_id)?.name || '—') : '— Select product —'"></span>
                        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                      </button>
                      <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu">
                        <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                          <input :x-ref="'ps_' + i" x-ref="'ps_' + i" x-model="q" type="text"
                                 placeholder="Search product name or SKU…"
                                 class="input text-sm w-full py-1.5"
                                 @keydown.stop />
                        </div>
                        <div class="max-h-52 overflow-y-auto py-1">
                          <template x-for="p in products.filter(p => !q || p.name.toLowerCase().includes(q.toLowerCase()) || (p.code && p.code.toLowerCase().includes(q.toLowerCase())))" :key="p.id">
                            <button type="button"
                                    @click="line.product_id = p.id; fillProduct(line); open = false; q = ''"
                                    class="search-dd-item"
                                    :class="line.product_id == p.id ? 'active' : ''">
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
                                <template x-if="line.product_id == p.id">
                                  <div class="w-4 h-4 rounded-full flex items-center justify-center" style="background:#d97706">
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
                    <button type="button" @click="form.lines.splice(i, 1); calcTotals()"
                            x-show="form.lines.length > 1"
                            class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all"
                            title="Remove">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </div>

                  
                  <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                    
                    <div class="flex items-center rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden flex-shrink-0" style="height:36px">
                      <button type="button" @click="line.qty = Math.max(0.01, (line.qty||1)-1); calcLine(line)"
                              class="w-8 flex items-center justify-center text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors border-r border-gray-200 dark:border-gray-600 h-full text-lg leading-none select-none">−</button>
                      <input type="number" x-model.number="line.qty" @input="calcLine(line)" min="0.01" step="0.01"
                             class="w-14 text-center text-sm font-semibold text-gray-700 dark:text-gray-200 bg-transparent border-none outline-none h-full tabular-nums" style="box-shadow:none" />
                      <button type="button" @click="line.qty = (line.qty||0)+1; calcLine(line)"
                              class="w-8 flex items-center justify-center text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition-colors border-l border-gray-200 dark:border-gray-600 h-full text-lg leading-none select-none">+</button>
                    </div>
                    
                    <div class="flex-1 min-w-0 relative">
                      <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium pointer-events-none">Rs.</span>
                      <input type="number" x-model.number="line.unit_price" @input="calcLine(line)" min="0" step="0.01" placeholder="0.00"
                             class="input text-sm text-right tabular-nums w-full" style="padding-left:2rem" />
                    </div>
                    
                    <div class="flex items-center rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden flex-shrink-0" style="height:36px;width:80px">
                      <input type="number" x-model.number="line.discount" @input="calcLine(line)" min="0" max="100" step="0.5" placeholder="0"
                             class="flex-1 w-0 text-center text-sm text-gray-700 dark:text-gray-200 bg-transparent border-none outline-none h-full tabular-nums" style="box-shadow:none" />
                      <span class="pr-2 text-xs text-gray-400 font-medium">%</span>
                    </div>
                    
                    <div class="text-right flex-shrink-0 min-w-[90px]">
                      <div class="text-xs text-gray-400 mb-0.5">Total</div>
                      <div class="text-sm font-bold tabular-nums" style="color:#d97706" x-text="fmtMoney(line.total)"></div>
                    </div>
                  </div>

                  
                  <template x-if="line.product_id">
                    <div class="flex items-center gap-2 mt-1.5">
                      <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-md text-xs"
                           :class="stockQty(products.find(p=>p.id==line.product_id)) <= 0
                             ? 'bg-red-50 text-red-600 border border-red-200'
                             : stockQty(products.find(p=>p.id==line.product_id)) <= (products.find(p=>p.id==line.product_id)?.reorder_level||5)
                               ? 'bg-orange-50 text-orange-600 border border-orange-200'
                               : 'bg-green-50 text-green-600 border border-green-200'">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <span x-text="'Stock: ' + stockQty(products.find(p=>p.id==line.product_id)) + ' ' + (products.find(p=>p.id==line.product_id)?.unit||'pcs')"></span>
                      </div>
                      <div x-show="line.qty > stockQty(products.find(p=>p.id==line.product_id))"
                           class="flex items-center gap-1 text-xs text-red-600 font-medium">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Exceeds available stock
                      </div>
                    </div>
                  </template>
                  
                  <input type="text" x-model="line.description"
                         placeholder="Description / batch / notes (optional)"
                         class="input text-xs mt-2 w-full bg-gray-50/70 dark:bg-gray-800/40 text-gray-500" />
                </div>
              </div>
            </div>
          </template>

          
          <div x-show="form.lines.length === 0" class="py-16 text-center">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#fef3c7">
              <svg class="w-7 h-7 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <p class="text-sm font-medium text-gray-400">No items yet</p>
            <p class="text-xs text-gray-300 dark:text-gray-600 mt-1">Click <strong>"Add Item"</strong> to begin</p>
          </div>
        </div>

        
        <div class="border-t border-gray-100 dark:border-gray-700">
          <div class="px-6 pt-4 pb-2 flex justify-end">
            <div class="w-80 space-y-2">
              <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                <span>Subtotal</span>
                <span class="font-medium tabular-nums" x-text="fmtMoney(totals.subtotal)"></span>
              </div>
              <div class="flex justify-between text-sm" x-show="totals.discount > 0">
                <span class="text-gray-500 dark:text-gray-400">Discount</span>
                <span class="font-medium tabular-nums text-red-500" x-text="'– ' + fmtMoney(totals.discount)"></span>
              </div>
            </div>
          </div>
          <div class="mx-4 mb-4 rounded-xl px-6 py-4 flex items-center justify-between"
               style="background:linear-gradient(135deg,#fffbeb,#fef3c7);border:1px solid #fde68a">
            <div>
              <div class="text-xs font-semibold text-amber-700 uppercase tracking-wider mb-0.5">Proforma Total</div>
              <div class="text-xs text-amber-600"><span x-text="form.lines.length"></span> item<span x-show="form.lines.length !== 1">s</span></div>
            </div>
            <div class="text-right">
              <div class="text-2xl font-black tabular-nums" style="color:#d97706" x-text="fmtMoney(totals.total)"></div>
              <div class="text-xs text-amber-600 mt-0.5">incl. all discounts</div>
            </div>
          </div>
        </div>

      </div>

      
      <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40">
          <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Notes &amp; Terms</h3>
        </div>
        <div class="px-6 py-4">
          <textarea x-model="form.notes" rows="4"
                    placeholder="e.g. This quote is valid for 7 days. Prices subject to change."
                    class="input resize-none w-full text-sm"></textarea>
        </div>
      </div>

    </div>

    
    <div class="w-full lg:flex-[35]">
    <div class="lg:sticky lg:top-6 space-y-5">

      
      <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700"
             style="background:linear-gradient(135deg,#d97706,#92400e)">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
              <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
              <h3 class="text-sm font-bold text-white">Customer &amp; Details</h3>
              <p class="text-xs" style="color:rgba(255,255,255,0.6)">Proforma metadata</p>
            </div>
          </div>
        </div>
        <div class="px-5 py-4 space-y-4">

          
          <div>
            <label class="label">Branch <span class="text-red-500">*</span></label>
            <select x-model="form.branch_id" @change="form.customer_id = ''; customerBalance = null" class="input" required>
              <option value="">Select branch…</option>
              <template x-for="b in branches" :key="b.id">
                <option :value="b.id" x-text="b.name"></option>
              </template>
            </select>
          </div>

          
          <div>
            <label class="label">Customer <span class="text-red-500">*</span></label>
            
            <div class="search-dd"
                 x-data="{ open: false, q: '' }"
                 @click.away="open = false"
                 @keydown.escape="open = false">
              <button type="button"
                      @click="open = !open; if(open) $nextTick(() => $refs.cSearch?.focus())"
                      class="input text-sm w-full text-left flex items-center justify-between gap-2"
                      :class="!form.customer_id ? 'border-amber-300' : ''">
                <span class="truncate"
                      :class="form.customer_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                      x-text="form.customer_id ? (filteredCustomers.find(c => c.id == form.customer_id)?.name || customers.find(c => c.id == form.customer_id)?.name || '—') : '— Select customer —'"></span>
                <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
              </button>
              <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu">
                <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                  <input x-ref="cSearch" x-model="q" type="text"
                         placeholder="Search customer name or phone…"
                         class="input text-sm w-full py-1.5" @keydown.stop />
                </div>
                <div class="max-h-52 overflow-y-auto py-1">
                  <template x-for="c in filteredCustomers.filter(c => !q || c.name.toLowerCase().includes(q.toLowerCase()) || (c.phone && c.phone.includes(q)))" :key="c.id">
                    <button type="button"
                            @click="form.customer_id = c.id; loadCustomerBalance(); open = false; q = ''"
                            class="search-dd-item"
                            :class="form.customer_id == c.id ? 'active' : ''">
                      <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate" x-text="c.name"></div>
                        <div class="text-xs text-gray-400" x-text="c.phone || ''"></div>
                      </div>
                      <template x-if="form.customer_id == c.id">
                        <div class="w-4 h-4 rounded-full flex items-center justify-center flex-shrink-0" style="background:#d97706">
                          <svg class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                      </template>
                    </button>
                  </template>
                  <div x-show="filteredCustomers.filter(c => !q || c.name.toLowerCase().includes(q.toLowerCase())).length === 0"
                       class="px-4 py-3 text-xs text-gray-400 text-center">
                    <span x-show="!form.branch_id">Select a branch first</span>
                    <span x-show="form.branch_id">No customers found for this branch</span>
                  </div>
                </div>
              </div>
            </div>
            
            <div x-show="customerBalance !== null" x-transition
                 class="mt-2 rounded-lg px-3 py-2.5 flex items-center justify-between gap-2"
                 :style="customerBalance > 0 ? 'background:#fff1f2;border:1px solid #fecdd3' : 'background:#f0fdf4;border:1px solid #bbf7d0'">
              <span class="text-xs font-medium" :class="customerBalance > 0 ? 'text-red-700' : 'text-green-700'">
                <span x-show="customerBalance > 0">Outstanding balance</span>
                <span x-show="customerBalance <= 0">No outstanding balance</span>
              </span>
              <span class="text-xs font-black tabular-nums"
                    :class="customerBalance > 0 ? 'text-red-600' : 'text-green-600'"
                    x-text="fmtMoney(Math.abs(customerBalance))"></span>
            </div>
          </div>

          
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="label">Proforma Date <span class="text-red-500">*</span></label>
              <input type="date" x-model="form.proforma_date" class="input" required />
            </div>
            <div>
              <label class="label">Valid Until</label>
              <input type="date" x-model="form.valid_until" class="input" />
            </div>
          </div>

          
          <div>
            <label class="label">Reference / PO #</label>
            <input type="text" x-model="form.reference" placeholder="Customer PO number" class="input text-sm" />
          </div>

        </div>
      </div>

      
      <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40">
          <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Quote Summary</h3>
        </div>
        <div class="p-5 space-y-3">
          <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
            <span>Subtotal</span>
            <span class="font-medium tabular-nums" x-text="fmtMoney(totals.subtotal)"></span>
          </div>
          <div class="flex justify-between text-sm text-red-500" x-show="totals.discount > 0">
            <span>Discount</span>
            <span class="font-medium tabular-nums" x-text="'– ' + fmtMoney(totals.discount)"></span>
          </div>
          <div class="border-t border-dashed border-gray-200 dark:border-gray-600 pt-3">
            <div class="flex justify-between items-center">
              <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Total</span>
              <span class="text-xl font-black tabular-nums" style="color:#d97706" x-text="fmtMoney(totals.total)"></span>
            </div>
          </div>
          <div x-show="form.valid_until" class="flex items-center gap-2 text-xs text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 rounded-lg px-3 py-2">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Valid until <strong class="ml-1" x-text="fmtDate(form.valid_until)"></strong>
          </div>
        </div>
      </div>

      
      <div class="space-y-2.5">
        <button type="button" @click="save('draft')" :disabled="saving"
                class="btn-secondary w-full flex items-center justify-center gap-2 py-2.5">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
          Save Draft
        </button>
        <button type="button" @click="save('sent')" :disabled="saving"
                class="w-full flex items-center justify-center gap-2 py-2.5 rounded-lg font-semibold text-sm text-white transition-all shadow hover:shadow-md active:scale-[0.98]"
                style="background:linear-gradient(135deg,#f59e0b,#d97706)">
          <template x-if="saving"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg></template>
          <template x-if="!saving"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg></template>
          <span x-text="saving ? 'Sending…' : 'Send to Admin'"></span>
        </button>
        <a href="<?php echo e(url('/proforma-invoices')); ?>" class="btn-secondary w-full flex items-center justify-center gap-2 py-2.5 text-center">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
          Cancel
        </a>
      </div>

    </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function proformaCreate() {
  return {
    saving:          false,
    customers:       [],
    branches:        [],
    products:        [],
    customerBalance: null,
    form: {
      customer_id:   '',
      branch_id:     '',
      reference:     '',
      proforma_date: new Date().toISOString().slice(0, 10),
      valid_until:   new Date(Date.now() + 7 * 864e5).toISOString().slice(0, 10),
      notes:         '',
      lines:         [],
    },
    totals: { subtotal: 0, discount: 0, total: 0 },

    get filteredCustomers() {
      if (!this.form.branch_id) return this.customers;
      return this.customers.filter(c => c.branch_id == this.form.branch_id);
    },

    async init() {
      const [cu, br, pr] = await Promise.all([
        apiFetch('/customers?per_page=999').then(r => r.json()),
        apiFetch('/branches').then(r => r.json()),
        apiFetch('/products?per_page=999').then(r => r.json()),
      ]);
      this.customers = cu.data || cu || [];
      this.branches  = br.data || br || [];
      this.products  = pr.data || pr || [];
      try {
        const u = JSON.parse(localStorage.getItem('medri_user') || '{}');
        const branchId = localStorage.getItem('medri_branch') || u.default_branch_id;
        if (branchId) this.form.branch_id = branchId;
      } catch (_) {}
      this.addLine();
    },

    async loadCustomerBalance() {
      this.customerBalance = null;
      if (!this.form.customer_id) return;
      try {
        const r = await apiFetch('/customers/' + this.form.customer_id);
        const d = await r.json();
        this.customerBalance = parseFloat(d.outstanding_balance ?? d.balance ?? 0);
      } catch (_) { this.customerBalance = null; }
    },

    addLine() {
      this.form.lines.push({ product_id: '', description: '', qty: 1, unit_price: 0, discount: 0, total: 0 });
    },

    stockQty(p) {
      if (!p) return 0;
      const s = p.branch_stocks?.[0] ?? p.branchStocks?.[0];
      return parseFloat(s?.quantity ?? s?.stock ?? 0);
    },

    fillProduct(line) {
      const p = this.products.find(x => x.id == line.product_id);
      if (p) {
        line.description = line.description || '';
        line.unit_price  = parseFloat(p.selling_price || p.price || 0);
      }
      this.calcLine(line);
    },

    calcLine(line) {
      const base = (line.qty || 0) * (line.unit_price || 0);
      line.total = base - (base * ((line.discount || 0) / 100));
      this.calcTotals();
    },

    calcTotals() {
      this.totals.subtotal = this.form.lines.reduce((s, l) => s + ((l.qty||0)*(l.unit_price||0)), 0);
      this.totals.discount = this.form.lines.reduce((s, l) => s + ((l.qty||0)*(l.unit_price||0)*((l.discount||0)/100)), 0);
      this.totals.total    = this.totals.subtotal - this.totals.discount;
    },

    async save(status) {
      if (!this.form.customer_id)    { toast('Please select a customer', 'error'); return; }
      if (!this.form.branch_id)      { toast('Please select a branch', 'error'); return; }
      if (!this.form.lines.length)   { toast('Add at least one item', 'error'); return; }
      this.saving = true;
      try {
        const res = await apiFetch('/proforma-invoices', {
          method: 'POST',
          body: JSON.stringify({
            branch_id:     this.form.branch_id,
            customer_id:   this.form.customer_id,
            proforma_date: this.form.proforma_date,
            valid_until:   this.form.valid_until || null,
            reference:     this.form.reference || null,
            notes:         this.form.notes || null,
            status,
            items: this.form.lines.map(l => ({
              product_id:       l.product_id,
              quantity:         l.qty,
              unit_price:       l.unit_price,
              discount_percent: l.discount || 0,
              notes:            l.description || null,
            })),
          }),
        });
        const d = await res.json();
        if (res.ok) {
          toast('Proforma invoice saved', 'success');
          setTimeout(() => { window.location.href = BASE + '/proforma-invoices/' + d.id; }, 600);
        } else {
          toast(d.message || 'Save failed', 'error');
        }
      } catch (_) { toast('An unexpected error occurred', 'error'); }
      this.saving = false;
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/proforma/create.blade.php ENDPATH**/ ?>