<?php $__env->startSection('title', 'Edit Invoice'); ?>
<?php $__env->startSection('page-title', 'Edit Invoice'); ?>
<?php $__env->startSection('page-desc', 'Correct a draft invoice before confirming it'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="invoiceEdit()" x-init="init()" class="px-6 pb-12">

  <template x-if="loading">
    <div class="flex items-center justify-center py-24 text-gray-400">
      <svg class="animate-spin w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
      Loading invoice…
    </div>
  </template>

  <template x-if="!loading && notFound">
    <div class="card p-12 text-center text-gray-400">
      Invoice not found.
      <div class="mt-3"><a href="<?php echo e(url('/invoices')); ?>" class="text-blue-600 hover:underline text-sm">Back to Invoices</a></div>
    </div>
  </template>

  <template x-if="!loading && !notFound">
  <div class="flex flex-col lg:flex-row gap-6">

    
    <div class="w-full lg:flex-[65] min-w-0 space-y-5">

      
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
                <span x-text="items.length"></span> item<span x-show="items.length !== 1">s</span>
                &nbsp;·&nbsp;<span class="font-semibold text-white" x-text="fmtMoney(grandTotal)"></span>
              </p>
            </div>
          </div>
          <button type="button" @click="addRow()"
                  class="flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all active:scale-95"
                  style="background:rgba(255,255,255,0.18);color:#fff;border:1px solid rgba(255,255,255,0.25);"
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

                  
                  <div class="flex items-center gap-1 mb-2">
                    <button type="button"
                            @click="row.type = 'product'; row.service_id = null; row.unit_price = 0; row.service_rate = 0; calcLine(row)"
                            class="text-[11px] font-semibold px-2.5 py-1 rounded-md transition-colors"
                            :class="row.type !== 'service' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'">Product</button>
                    <button type="button"
                            @click="row.type = 'service'; row.product_id = null; row.batch_id = null; row.batch_number = null; row.batch_cost = null; row.batch_allocations = []; row.unit_price = 0; calcLine(row)"
                            class="text-[11px] font-semibold px-2.5 py-1 rounded-md transition-colors"
                            :class="row.type === 'service' ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'">Service</button>
                  </div>

                  
                  <div class="flex items-center gap-2 mb-2.5" x-show="row.type !== 'service'">
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
                              x-text="row.product_id ? (products.find(p => p.id == row.product_id)?.name || '—') : '— Select product —'"></span>
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
                    <button type="button" @click="removeRow(idx)"
                            x-show="items.length > 1"
                            class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all"
                            title="Remove">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </div>

                  
                  <div class="flex items-center gap-2 mb-2.5" x-show="row.type === 'service'">
                    <div class="search-dd flex-1 min-w-0"
                         x-data="{ open: false, q: '' }"
                         @click.away="open = false"
                         @keydown.escape="open = false">
                      <button type="button"
                              @click="open = !open; if(open) $nextTick(() => $refs.sv?.focus())"
                              class="input text-sm w-full text-left flex items-center justify-between gap-2"
                              :class="!row.service_id ? 'border-blue-200 dark:border-blue-700/60' : ''">
                        <span class="truncate"
                              :class="row.service_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                              x-text="row.service_id ? (services.find(s => s.id == row.service_id)?.name || '—') : '— Select service —'"></span>
                        <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                      </button>
                      <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu">
                        <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                          <input x-ref="sv" x-model="q" type="text"
                                 placeholder="Search by name or code…"
                                 class="input text-sm w-full py-1.5" @keydown.stop />
                        </div>
                        <div class="max-h-52 overflow-y-auto py-1">
                          <template x-for="s in services.filter(s => !q || s.name.toLowerCase().includes(q.toLowerCase()) || (s.code && s.code.toLowerCase().includes(q.toLowerCase())))" :key="s.id">
                            <button type="button"
                                    @click="row.service_id = s.id; fillService(row); open = false; q = ''"
                                    class="search-dd-item"
                                    :class="row.service_id == s.id ? 'active' : ''">
                              <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate" x-text="s.name"></div>
                                <div class="text-xs text-gray-400" x-text="(s.code || '') + (s.unit ? ' · ' + s.unit : '')"></div>
                              </div>
                              <div class="text-xs font-semibold text-gray-500 flex-shrink-0" x-text="fmtMoney(s.rate)"></div>
                            </button>
                          </template>
                          <div x-show="services.filter(s => !q || s.name.toLowerCase().includes(q.toLowerCase())).length === 0"
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

                  
                  <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap"
                       x-show="!row.batch_allocations || row.batch_allocations.length <= 1">
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

                  
                  <template x-if="row.product_id && (!row.batch_allocations || row.batch_allocations.length <= 1)">
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
                      <template x-if="row.batch_cost > 0 && row.unit_price > 0">
                        <span class="text-xs ml-1" :class="row.unit_price >= row.batch_cost ? 'text-green-600' : 'text-red-600'">
                          Cost: Rs.<span x-text="parseFloat(row.batch_cost).toLocaleString()"></span>
                          · Margin: <strong x-text="(((row.unit_price-row.batch_cost)/row.batch_cost)*100).toFixed(1)+'%'"></strong>
                          <span x-show="row.unit_price < row.batch_cost"> ⚠ Below cost!</span>
                        </span>
                      </template>
                    </div>
                  </template>

                  
                  <template x-if="row.type === 'service' && row.service_rate > 0">
                    <div class="flex items-center gap-1.5 mt-1.5 text-xs text-gray-400">
                      <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                      <span>Catalog rate: Rs.<span x-text="parseFloat(row.service_rate).toLocaleString('en-LK',{minimumFractionDigits:2})"></span></span>
                      <span x-show="row.unit_price != row.service_rate" class="text-orange-500 font-medium">(adjusted)</span>
                    </div>
                  </template>

                  
                  <template x-if="row.product_id && row.batch_allocations && row.batch_allocations.length > 1">
                    <div class="mt-2 rounded-xl overflow-hidden" style="border:1px solid #bae6fd">

                      <div class="px-3 py-2 flex items-center justify-between" style="background:linear-gradient(135deg,#0369a1,#0284c7)">
                        <div class="flex items-center gap-2">
                          <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                          <span class="text-xs font-bold text-white">Cost Layers — Enter qty to sell from each</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                          <span class="text-xs text-white/70">Price:</span>
                          <div class="relative">
                            <span class="absolute left-2 top-1/2 -translate-y-1/2 text-xs text-white/60 pointer-events-none">Rs.</span>
                            <input type="number" x-model.number="row.unit_price" @input="calcLine(row)" min="0" step="0.01"
                                   class="text-xs text-right tabular-nums font-bold text-white bg-white/20 border border-white/30 rounded-lg h-7 w-28"
                                   style="padding-left:1.75rem;padding-right:0.5rem;outline:none" />
                          </div>
                          <div class="flex items-center rounded-lg overflow-hidden border border-white/30" style="height:28px">
                            <input type="number" x-model.number="row.discount" @input="calcLine(row)" min="0" max="100" step="0.5" placeholder="0"
                                   class="w-10 text-center text-xs text-white bg-white/20 border-none outline-none h-full tabular-nums" style="box-shadow:none" />
                            <span class="pr-1.5 text-xs text-white/70">%</span>
                          </div>
                        </div>
                      </div>

                      <div class="divide-y divide-blue-50" style="background:#f0f9ff">
                        <template x-for="(alloc, ai) in row.batch_allocations" :key="ai">
                          <div class="flex items-center gap-3 px-3 py-2.5">

                            <div class="flex-shrink-0 px-2.5 py-1 rounded-lg text-xs font-bold tabular-nums"
                                 style="background:#fff;border:1px solid #bae6fd;color:#0369a1;min-width:100px;text-align:center">
                              Rs.<span x-text="parseFloat(alloc.unit_cost||0).toLocaleString('en-LK',{minimumFractionDigits:2})"></span>
                              <div class="text-[10px] font-normal mt-0.5 opacity-70">cost/unit</div>
                            </div>

                            <div class="flex-shrink-0 text-xs text-center" style="min-width:70px">
                              <div class="font-bold text-gray-700" x-text="parseFloat(alloc.available_qty).toFixed(0)"></div>
                              <div class="text-gray-400" x-text="products.find(p=>p.id==row.product_id)?.unit||'pcs'"></div>
                              <div class="text-[10px] text-gray-400">available</div>
                            </div>

                            <div class="flex items-center rounded-lg border overflow-hidden flex-shrink-0"
                                 :style="(alloc.selling_qty||0) > alloc.available_qty ? 'border-color:#ef4444;background:#fff1f2' : 'border-color:#bae6fd;background:#fff'"
                                 style="height:36px">
                              <button type="button"
                                      @click="alloc.selling_qty = Math.max(0,(alloc.selling_qty||0)-1); calcLine(row)"
                                      class="w-7 h-full flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors text-lg leading-none select-none border-r border-blue-100">−</button>
                              <input type="number" x-model.number="alloc.selling_qty" @input="calcLine(row)"
                                     min="0" step="1"
                                     class="w-14 text-center text-sm font-bold bg-transparent border-none outline-none h-full tabular-nums"
                                     :class="(alloc.selling_qty||0) > alloc.available_qty ? 'text-red-600' : 'text-gray-700'"
                                     style="box-shadow:none" />
                              <button type="button"
                                      @click="alloc.selling_qty = (alloc.selling_qty||0)+1; calcLine(row)"
                                      class="w-7 h-full flex items-center justify-center text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors text-lg leading-none select-none border-l border-blue-100">+</button>
                            </div>

                            <div class="flex-1 text-xs text-right" x-show="(alloc.selling_qty||0) > 0 && row.unit_price > 0">
                              <div :class="row.unit_price >= alloc.unit_cost ? 'text-green-600' : 'text-red-600'" class="font-semibold">
                                <span x-text="alloc.unit_cost > 0 ? (((row.unit_price - alloc.unit_cost) / alloc.unit_cost) * 100).toFixed(1) + '%' : '—'"></span>
                                margin
                              </div>
                              <div class="text-gray-400 tabular-nums"
                                   x-text="'Rs.' + ((row.unit_price - alloc.unit_cost) * (alloc.selling_qty||0)).toLocaleString('en-LK',{minimumFractionDigits:2})"></div>
                              <div x-show="row.unit_price < alloc.unit_cost" class="text-red-600 font-bold text-[10px]">⚠ Below cost!</div>
                            </div>

                          </div>
                        </template>
                      </div>

                      <div class="px-3 py-2 flex items-center justify-between text-xs" style="background:#e0f2fe;border-top:1px solid #bae6fd">
                        <div class="flex items-center gap-3">
                          <span class="text-blue-700">
                            Total qty: <strong x-text="row.quantity + ' ' + (products.find(p=>p.id==row.product_id)?.unit||'pcs')"></strong>
                          </span>
                          <template x-if="row.quantity > stockQty(products.find(p=>p.id==row.product_id))">
                            <span class="text-red-600 font-bold flex items-center gap-1">
                              <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                              Exceeds available stock!
                            </span>
                          </template>
                        </div>
                        <div class="font-bold tabular-nums" style="color:#1B3EB6" x-text="fmtMoney(row.total)"></div>
                      </div>

                    </div>
                  </template>

                  
                  <template x-if="row.product_id && row.batches_loading">
                    <div class="flex items-center gap-2 text-xs text-gray-400 mt-1.5 py-1">
                      <svg class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                      Loading cost layers…
                    </div>
                  </template>

                  
                  <input type="text" x-model="row.description"
                         placeholder="Description / batch / notes (optional)"
                         class="input text-xs mt-2 w-full bg-gray-50/70 dark:bg-gray-800/40 text-gray-500" />
                </div>
              </div>
            </div>
          </template>

          
          <div x-show="items.length > 0" class="px-5 py-3">
            <button type="button" @click="addRow()"
                    class="w-full flex items-center justify-center gap-1.5 py-2.5 rounded-lg text-xs font-semibold border border-dashed border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50/50 dark:hover:bg-blue-900/10 transition-colors">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
              Add Item
            </button>
          </div>

          <div x-show="items.length === 0" class="py-16 text-center">
            <div class="w-14 h-14 rounded-2xl mx-auto mb-3 flex items-center justify-center" style="background:#eef2ff">
              <svg class="w-7 h-7" style="color:#818cf8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <p class="text-sm font-medium text-gray-400">No items yet</p>
            <p class="text-xs text-gray-300 dark:text-gray-600 mt-1">Click <strong>"Add Item"</strong> to add products</p>
          </div>
        </div>

        
        <div class="border-t border-gray-100 dark:border-gray-700">
          <div class="px-6 pt-4 pb-2 flex justify-end">
            <div class="w-80 space-y-2">
              <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                <span>Subtotal</span>
                <span class="font-medium tabular-nums" x-text="fmtMoney(subtotal)"></span>
              </div>
              <div class="flex justify-between text-sm" x-show="discountTotal > 0">
                <span class="text-gray-500 dark:text-gray-400">Discount</span>
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
              <div class="text-xs flex items-center gap-1" style="color:rgba(255,255,255,0.55)">
                <span x-text="items.length"></span> item<span x-show="items.length !== 1">s</span>
                <span x-show="taxRate > 0">&nbsp;· <span x-text="taxRate"></span>% tax</span>
              </div>
            </div>
            <div class="text-right">
              <div class="text-2xl font-black tabular-nums text-white" x-text="fmtMoney(grandTotal)"></div>
              <div class="text-xs mt-0.5" style="color:rgba(255,255,255,0.55)">grand total</div>
            </div>
          </div>
        </div>

      </div>

      
      <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40">
          <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Notes &amp; Terms</h3>
          <p class="text-xs text-gray-400 mt-0.5">Payment terms, delivery instructions, or internal notes</p>
        </div>
        <div class="px-6 py-4">
          <textarea x-model="form.notes" rows="4"
                    placeholder="e.g. Payment due within 30 days. Delivery to warehouse B."
                    class="input resize-none w-full text-sm"></textarea>
        </div>
      </div>

    </div>

    
    <div class="w-full lg:flex-[35]">
    <div class="lg:sticky lg:top-6 space-y-5">

      
      <div class="card overflow-visible">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 rounded-t-xl"
             style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
              <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            </div>
            <div>
              <h3 class="text-sm font-bold text-white">Customer &amp; Details</h3>
              <p class="text-xs" style="color:rgba(255,255,255,0.6)">Invoice metadata</p>
            </div>
          </div>
        </div>
        <div class="px-5 py-4 space-y-4">

          <div class="rounded-lg px-3 py-2.5 bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700">
            <p class="text-xs text-gray-400 mb-1">Customer</p>
            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="inv.customer?.name || '—'"></p>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div class="rounded-lg px-3 py-2.5 bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700">
              <p class="text-xs text-gray-400 mb-1">Branch</p>
              <p class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="inv.branch?.name || '—'"></p>
            </div>
            <div class="rounded-lg px-3 py-2.5 bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700">
              <p class="text-xs text-gray-400 mb-1">Sales Rep</p>
              <p class="text-sm font-medium text-gray-700 dark:text-gray-200" x-text="inv.salesRep?.name || inv.sales_rep?.name || '—'"></p>
            </div>
          </div>
          <p class="text-xs text-gray-400">Customer, branch, and sales rep can't be changed here — cancel and recreate the invoice if these need to change.</p>

          
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

        </div>
      </div>

      
      <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40">
          <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Order Summary</h3>
        </div>
        <div class="p-5 space-y-3">
          <div class="flex items-center justify-between text-xs text-gray-400">
            <span>Line items</span>
            <span class="font-semibold text-gray-600 dark:text-gray-300" x-text="items.length"></span>
          </div>
          <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
            <span>Subtotal</span>
            <span class="font-medium tabular-nums" x-text="fmtMoney(subtotal)"></span>
          </div>
          <div class="flex justify-between text-sm text-red-500" x-show="discountTotal > 0">
            <span>Discount</span>
            <span class="font-medium tabular-nums" x-text="'– ' + fmtMoney(discountTotal)"></span>
          </div>
          <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400" x-show="taxAmount > 0">
            <span>Tax (<span x-text="taxRate"></span>%)</span>
            <span class="font-medium tabular-nums" x-text="fmtMoney(taxAmount)"></span>
          </div>
          <div class="border-t border-dashed border-gray-200 dark:border-gray-600 pt-3">
            <div class="flex justify-between items-center">
              <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Total</span>
              <span class="text-xl font-black tabular-nums" style="color:#1B3EB6" x-text="fmtMoney(grandTotal)"></span>
            </div>
          </div>
        </div>
      </div>

      
      <div class="space-y-2.5">
        <button type="button" @click="save(false)" :disabled="submitting"
                class="btn-secondary w-full flex items-center justify-center gap-2 py-2.5">
          <template x-if="submitting"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg></template>
          <template x-if="!submitting"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg></template>
          <span x-text="submitting ? 'Saving…' : 'Save Changes'"></span>
        </button>
        <button type="button" @click="save(true)" :disabled="submitting"
                class="w-full flex items-center justify-center gap-2 py-2.5 rounded-lg font-semibold text-sm text-white transition-all shadow hover:shadow-lg active:scale-[0.98]"
                style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
          <template x-if="submitting"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg></template>
          <template x-if="!submitting"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></template>
          <span x-text="submitting ? 'Saving…' : 'Save & Confirm'"></span>
        </button>
        <a :href="BASE + '/invoices/' + id"
           class="btn-secondary w-full flex items-center justify-center gap-2 py-2.5 text-center">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
          Cancel
        </a>
      </div>

    </div>
    </div>

  </div>
  </template>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function invoiceEdit() {
  return {
    loading:  true,
    notFound: false,
    inv:      null,
    products: [],
    services: [],
    items:    [],
    submitting: false,
    taxRate:      0,
    subtotal:     0,
    discountTotal:0,
    taxAmount:    0,
    grandTotal:   0,
    form: {
      invoice_date: '',
      due_date:     '',
      notes:        '',
    },

    get id() {
      const parts = window.location.pathname.split('/').filter(Boolean);
      return parts[parts.length - 2];
    },

    async init() {
      const [pd, svd] = await Promise.all([
        apiFetch('/products?per_page=999').then(r => r.json()),
        apiFetch('/services?per_page=999&is_active=1').then(r => r.json()),
      ]);
      this.products = pd.data || pd || [];
      this.services = svd.data || svd || [];

      try {
        const r = await apiFetch('/invoices/' + this.id);
        if (!r.ok) { this.notFound = true; this.loading = false; return; }
        this.inv = await r.json();
      } catch (e) { this.notFound = true; this.loading = false; return; }

      if (this.inv.status !== 'draft') {
        toast('Only draft invoices can be edited', 'error');
        window.location.href = BASE + '/invoices/' + this.id;
        return;
      }

      this.form.invoice_date = (this.inv.invoice_date || '').slice(0, 10);
      this.form.due_date     = (this.inv.due_date || '').slice(0, 10);
      this.form.notes        = this.inv.notes || '';
      this.taxRate           = parseFloat(this.inv.tax_percent || 0);

      this.items = (this.inv.items || []).map(item => {
        const isService = !!item.service_id;
        return {
          type:         isService ? 'service' : 'product',
          product_id:   item.product_id || '',
          service_id:   item.service_id || '',
          service_rate: isService ? parseFloat((this.services.find(s => s.id == item.service_id) || {}).rate || 0) : 0,
          description:  item.notes || '',
          quantity:     parseFloat(item.quantity),
          unit_price:   parseFloat(item.unit_price),
          discount:     parseFloat(item.discount_percent || 0),
          total:        parseFloat(item.total),
          batch_id:     item.batch_id || null,
          batch_number: item.batch_number || null,
          batch_cost:   item.unit_cost || null,
          batches:            [],
          batch_allocations:  [],
          batches_loading:    false,
        };
      });
      if (this.items.length === 0) this.addRow();

      this.calcTotals();
      this.loading = false;
    },

    addRow() {
      this.items.push({ type: 'product', product_id: '', service_id: '', service_rate: 0, description: '', quantity: 1, unit_price: 0, discount: 0, total: 0, batch_id: null, batch_number: null, batch_cost: null, batches: [], batch_allocations: [], batches_loading: false });
    },

    removeRow(i) { this.items.splice(i, 1); this.calcTotals(); },

    stockQty(p) {
      if (!p) return 0;
      const s = p.branch_stocks?.[0] ?? p.branchStocks?.[0];
      return parseFloat(s?.quantity ?? s?.stock ?? 0);
    },

    async fillProduct(row) {
      const p = this.products.find(x => x.id == row.product_id);
      if (!p) return;
      const requestedQty   = parseFloat(row.quantity) || 1;
      row.unit_price       = parseFloat(p.selling_price || p.price || 0);
      row.batch_id         = null;
      row.batch_number     = null;
      row.batch_cost       = null;
      row.batches          = [];
      row.batch_allocations = [];
      row.batches_loading  = true;
      this.calcLine(row);
      try {
        const branchId = this.inv?.branch_id;
        const r    = await apiFetch('/products/' + p.id + '/batches' + (branchId ? '?branch_id=' + branchId : ''));
        const data = await r.json();
        row.batches = Array.isArray(data) ? data : (data.data ?? []);
        if (row.batches.length === 1) {
          row.batch_id     = row.batches[0].id;
          row.batch_number = row.batches[0].batch_number;
          row.batch_cost   = parseFloat(row.batches[0].unit_cost || 0);
          if (row.batches[0].selling_price) row.unit_price = parseFloat(row.batches[0].selling_price);
        } else if (row.batches.length > 1) {
          row.batch_allocations = row.batches.map((b, i) => ({
            batch_id:      b.id,
            batch_number:  b.batch_number,
            expiry_date:   b.expiry_date,
            unit_cost:     parseFloat(b.unit_cost || 0),
            selling_price: parseFloat(b.selling_price || 0),
            available_qty: parseFloat(b.available_qty || 0),
            selling_qty:   i === 0 ? Math.min(requestedQty, parseFloat(b.available_qty || 0)) : 0,
          }));
        }
      } catch (_) {}
      row.batches_loading = false;
      this.calcLine(row);
    },

    fillService(row) {
      const s = this.services.find(x => x.id == row.service_id);
      if (!s) return;
      row.unit_price   = parseFloat(s.rate || 0);
      row.service_rate = parseFloat(s.rate || 0);
      this.calcLine(row);
    },

    calcLine(row) {
      if (row.batch_allocations && row.batch_allocations.length > 1) {
        row.quantity = row.batch_allocations.reduce((s, a) => s + (parseFloat(a.selling_qty) || 0), 0);
      }
      const base = (row.quantity || 0) * (row.unit_price || 0);
      row.total  = base - (base * ((row.discount || 0) / 100));
      this.calcTotals();
    },

    calcTotals() {
      this.subtotal      = this.items.reduce((s, r) => s + ((r.quantity||0)*(r.unit_price||0)), 0);
      this.discountTotal = this.items.reduce((s, r) => s + ((r.quantity||0)*(r.unit_price||0)*((r.discount||0)/100)), 0);
      this.taxAmount     = (this.subtotal - this.discountTotal) * ((this.taxRate||0) / 100);
      this.grandTotal    = this.subtotal - this.discountTotal + this.taxAmount;
    },

    async save(thenConfirm) {
      if (!this.items.length) { toast('Add at least one item', 'error'); return; }
      if (this.items.some(r => r.type === 'service' ? !r.service_id : !r.product_id)) {
        toast('Every line needs a product or service selected', 'error'); return;
      }

      this.submitting = true;
      try {
        const payload = {
          invoice_date: this.form.invoice_date,
          due_date:     this.form.due_date || null,
          notes:        this.form.notes || null,
          tax_percent:  this.taxRate,
          items: this.items.flatMap(r => {
            if (r.type === 'service') {
              return [{ service_id: r.service_id, quantity: r.quantity, unit_price: r.unit_price, discount_percent: r.discount || 0, notes: r.description || null }];
            }
            if (r.batch_allocations && r.batch_allocations.length > 1) {
              return r.batch_allocations
                .filter(a => (parseFloat(a.selling_qty) || 0) > 0)
                .map(a => ({
                  product_id:       r.product_id,
                  batch_id:         a.batch_id || null,
                  quantity:         parseFloat(a.selling_qty),
                  unit_price:       r.unit_price,
                  discount_percent: r.discount || 0,
                  batch_number:     a.batch_number || null,
                  notes:            r.description || null,
                }));
            }
            return [{ product_id: r.product_id, batch_id: r.batch_id || null, quantity: r.quantity, unit_price: r.unit_price, discount_percent: r.discount || 0, batch_number: r.batch_number || null, notes: r.description || null }];
          }),
        };

        const res = await apiFetch('/invoices/' + this.id, { method: 'PUT', body: JSON.stringify(payload) });
        if (!res.ok) {
          const e = await res.json();
          toast(e.message || 'Failed to save changes', 'error');
          this.submitting = false;
          return;
        }

        if (thenConfirm) {
          const cr = await apiFetch('/invoices/' + this.id + '/confirm', { method: 'POST' });
          if (!cr.ok) {
            const e = await cr.json();
            toast('Saved, but failed to confirm: ' + (e.message || ''), 'error');
            setTimeout(() => { window.location.href = BASE + '/invoices/' + this.id; }, 800);
            return;
          }
          toast('Invoice saved and confirmed', 'success');
        } else {
          toast('Invoice updated', 'success');
        }
        setTimeout(() => { window.location.href = BASE + '/invoices/' + this.id; }, 500);
      } catch (e) {
        toast(e.message || 'Failed to save changes', 'error');
        this.submitting = false;
      }
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\invoices\edit.blade.php ENDPATH**/ ?>