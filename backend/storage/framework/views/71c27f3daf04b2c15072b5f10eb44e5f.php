
<?php $__env->startSection('title', 'Create Invoice'); ?>
<?php $__env->startSection('page-title', 'Create Invoice'); ?>
<?php $__env->startSection('page-desc', 'Create a new confirmed sales invoice'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="invoiceCreate()" x-init="init()" class="px-6 pb-12">
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

        
        <div>
          <label class="label">Branch <span class="text-red-500">*</span></label>
          <select x-model="form.branch_id" @change="form.customer_id = ''; customerBalance = null; loadBranchUsers()" class="input" required>
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
                    @click="open = !open; if(open) $nextTick(() => $refs.cs?.focus())"
                    class="input text-sm w-full text-left flex items-center justify-between gap-2"
                    :class="!form.customer_id ? 'border-blue-200' : ''">
              <span class="truncate"
                    :class="form.customer_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                    x-text="form.customer_id ? (customers.find(c => c.id == form.customer_id)?.name || '—') : '— Select customer —'"></span>
              <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu">
              <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                <input x-ref="cs" x-model="q" type="text"
                       placeholder="Search customer name or phone…"
                       class="input text-sm w-full py-1.5" @keydown.stop />
              </div>
              <button type="button"
                      @click="open = false; openWalkInModal()"
                      class="w-full flex items-center gap-2 px-3 py-2 text-sm font-medium text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 border-b border-gray-100 dark:border-gray-700">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Walk-in Customer
              </button>
              <div class="max-h-52 overflow-y-auto py-1">
                <template x-for="c in filteredCustomers.filter(c => !q || c.name.toLowerCase().includes(q.toLowerCase()) || (c.phone && c.phone.includes(q)))" :key="c.id">
                  <button type="button"
                          @click="form.customer_id = c.id; loadCustomerBalance(); open = false; q = ''"
                          class="search-dd-item"
                          :class="form.customer_id == c.id ? 'active' : ''">
                    <div class="flex-1 min-w-0">
                      <div class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate flex items-center gap-1.5">
                        <span x-text="c.name"></span>
                        <span x-show="c.is_walk_in" class="text-[10px] font-semibold px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700">Walk-in</span>
                      </div>
                      <div class="text-xs text-gray-400" x-text="c.phone || ''"></div>
                    </div>
                    <template x-if="form.customer_id == c.id">
                      <div class="w-4 h-4 rounded-full flex items-center justify-center flex-shrink-0" style="background:#1B3EB6">
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
          
          <div x-show="creditLimitExceeded" x-transition
               class="mt-2 rounded-lg px-3 py-2.5" style="background:#fef2f2;border:1px solid #ef4444">
            <div class="flex items-start gap-2">
              <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
              <div class="text-xs">
                <p class="font-bold text-red-700">Credit limit will be exceeded — invoice will be rejected</p>
                <div class="text-red-600 mt-1 space-y-0.5">
                  <div class="flex justify-between gap-4"><span>Credit limit</span><span class="font-semibold tabular-nums" x-text="fmtMoney(customerCreditLimit)"></span></div>
                  <div class="flex justify-between gap-4"><span>Outstanding</span><span class="font-semibold tabular-nums" x-text="fmtMoney(customerBalance ?? 0)"></span></div>
                  <div class="flex justify-between gap-4"><span>This invoice</span><span class="font-semibold tabular-nums" x-text="fmtMoney(grandTotal)"></span></div>
                  <div class="flex justify-between gap-4 pt-0.5 border-t border-red-200"><span class="font-bold">Over limit by</span><span class="font-black tabular-nums" x-text="fmtMoney(creditOverBy)"></span></div>
                </div>
                <p class="text-red-500 mt-1.5">Collect a payment against the outstanding balance or reduce the invoice amount.</p>
              </div>
            </div>
          </div>
          
          <div x-show="customerCreditBalance > 0" x-transition
               class="mt-2 rounded-lg px-3 py-2.5 flex items-center justify-between gap-2"
               style="background:#eff6ff;border:1px solid #bfdbfe">
            <div class="flex items-center gap-2">
              <svg class="w-3.5 h-3.5 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
              <span class="text-xs font-medium text-blue-700">Credit on account</span>
            </div>
            <span class="text-xs font-black tabular-nums text-blue-600" x-text="fmtMoney(customerCreditBalance)"></span>
          </div>
        </div>

        
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
          <label class="label">Reference / PO #</label>
          <input type="text" x-model="form.reference" placeholder="Optional" class="input text-sm" />
        </div>

        
        <div>
          <label class="label">Sales Rep <span class="text-red-500">*</span></label>
          <div class="search-dd" x-data="{ open: false, q: '' }" @click.away="open = false" @keydown.escape="open = false">
            <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.repSel?.focus())"
                    class="input text-sm w-full text-left flex items-center justify-between gap-2">
              <span class="truncate" :class="form.sales_rep_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                    x-text="form.sales_rep_id ? (branchUsers.find(u => u.id == form.sales_rep_id)?.name || '—') : '— Select sales rep —'"></span>
              <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu">
              <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                <input x-ref="repSel" x-model="q" type="text" placeholder="Search sales rep…" class="input text-sm w-full py-1.5" @keydown.stop />
              </div>
              <div class="max-h-52 overflow-y-auto py-1">
                <template x-for="u in branchUsers.filter(u => !q || u.name.toLowerCase().includes(q.toLowerCase()))" :key="u.id">
                  <button type="button" @click="form.sales_rep_id = u.id; open = false; q = ''"
                          class="search-dd-item" :class="form.sales_rep_id == u.id ? 'active' : ''">
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-100 truncate flex-1" x-text="u.name"></span>
                  </button>
                </template>
                <div x-show="branchUsers.filter(u => !q || u.name.toLowerCase().includes(q.toLowerCase())).length === 0"
                     class="px-4 py-3 text-xs text-gray-400 text-center">No sales reps found</div>
              </div>
            </div>
          </div>
          <p class="text-xs text-gray-400 mt-1">Invoice revenue will be counted toward this rep's target</p>
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

    
    <div class="card">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-800/40 rounded-t-xl">
        <div class="flex items-center justify-between gap-3">
          <div>
            <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Record Payment</h3>
            <p class="text-xs text-gray-400 mt-0.5">Collect payment at time of creation</p>
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

      <div x-show="payment.enabled" x-transition:enter="transition ease-out duration-150"
           x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
           class="px-5 py-4 space-y-3">

        
        <template x-if="customerCreditBalance > 0">
          <div class="rounded-xl border border-blue-200 bg-blue-50 dark:bg-blue-900/20 dark:border-blue-700 p-3">
            <div class="flex items-start justify-between gap-3">
              <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <div>
                  <p class="text-xs font-semibold text-blue-800 dark:text-blue-200">Customer credit available</p>
                  <p class="text-xs text-blue-600 dark:text-blue-300" x-text="fmtMoney(customerCreditBalance) + ' on account'"></p>
                </div>
              </div>
              <label class="flex items-center gap-2 cursor-pointer flex-shrink-0">
                <span class="text-xs font-medium text-blue-700 dark:text-blue-300">Apply</span>
                <div class="relative" style="width:36px;height:20px">
                  <input type="checkbox" x-model="payment.use_credit" @change="onUseCreditChange()" class="sr-only peer" />
                  <div class="w-full h-full rounded-full transition-colors"
                       :style="payment.use_credit ? 'background:#1B3EB6' : 'background:#d1d5db'"></div>
                  <div class="absolute top-0.5 left-0.5 w-[16px] h-[16px] bg-white rounded-full shadow transition-transform"
                       :style="payment.use_credit ? 'transform:translateX(16px)' : ''"></div>
                </div>
              </label>
            </div>
            <template x-if="payment.use_credit">
              <div class="mt-2 pt-2 border-t border-blue-200 dark:border-blue-700 space-y-0.5">
                <div class="flex justify-between text-xs text-blue-700 dark:text-blue-300">
                  <span>Credit applied:</span>
                  <span class="font-semibold" x-text="fmtMoney(Math.min(customerCreditBalance, grandTotal))"></span>
                </div>
                <div class="flex justify-between text-xs text-blue-700 dark:text-blue-300">
                  <span>Remaining to collect:</span>
                  <span class="font-semibold" x-text="fmtMoney(Math.max(0, grandTotal - customerCreditBalance))"></span>
                </div>
              </div>
            </template>
          </div>
        </template>

        
        <div x-show="!payment.use_credit || remainingAfterCredit > 0">
          <label class="label">Payment Method <span class="text-red-500">*</span></label>
          <select x-model="payment.method"
                  @change="payment.cheque_number = ''; payment.bank_name = ''; payment.cheque_date = ''; payment.account_id = null"
                  class="input text-sm">
            <option value="cash">Cash</option>
            <option value="cheque">Cheque</option>
            <option value="bank_transfer">Bank Transfer</option>
          </select>
        </div>

        
        <div x-show="(!payment.use_credit || remainingAfterCredit > 0) && payment.method === 'cash'">
          <label class="label">Cash Account <span class="text-red-500">*</span></label>
          <select x-model.number="payment.account_id" class="input text-sm">
            <option :value="null">Select cash account…</option>
            <template x-for="a in cashAccounts" :key="a.id">
              <option :value="a.id" x-text="a.code + ' — ' + a.name"></option>
            </template>
          </select>
          <p class="text-xs text-gray-400 mt-1" x-show="!cashAccounts.length">No cash accounts for this branch — add one first.</p>
        </div>

        
        <div x-show="(!payment.use_credit || remainingAfterCredit > 0) && payment.method === 'bank_transfer'">
          <label class="label">Bank Account <span class="text-red-500">*</span></label>
          <select x-model.number="payment.account_id" class="input text-sm">
            <option :value="null">Select bank account…</option>
            <template x-for="a in bankAccounts" :key="a.id">
              <option :value="a.id" x-text="a.code + ' — ' + a.name"></option>
            </template>
          </select>
          <p class="text-xs text-gray-400 mt-1" x-show="!bankAccounts.length">No bank accounts for this branch — add one first.</p>
        </div>

        
        <div x-show="!payment.use_credit || remainingAfterCredit > 0">
          <label class="label">Amount Received <span x-show="!payment.use_credit" class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium pointer-events-none">Rs.</span>
            <input type="number" x-model.number="payment.amount" @input="onPayAmountChange()" min="0" step="0.01"
                   class="input text-sm text-right tabular-nums w-full" style="padding-left:2rem"
                   :class="payOverpayment > 0 ? 'border-amber-400 ring-1 ring-amber-300' : ''" />
          </div>
          <div class="flex items-center justify-between mt-1">
            <span class="text-xs text-gray-400">
              <span x-show="payOverpayment <= 0">You may enter more than the total</span>
              <span x-show="payOverpayment > 0" class="text-amber-600 font-medium">Overpayment detected</span>
            </span>
            <span class="text-xs text-gray-400">
              Invoice total: <span class="font-semibold" x-text="fmtMoney(grandTotal)"></span>
            </span>
          </div>
          
          <div x-show="payOverpayment > 0" x-transition
               class="mt-2 rounded-lg p-3 flex items-start gap-2.5"
               style="background:#fffbeb;border:1px solid #fde68a">
            <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
              <p class="text-xs font-semibold text-amber-800">Overpayment of <span x-text="fmtMoney(payOverpayment)"></span></p>
              <p class="text-xs text-amber-700 mt-0.5">
                Invoice (<span x-text="fmtMoney(grandTotal)"></span>) will be fully paid.
                The extra <span class="font-semibold" x-text="fmtMoney(payOverpayment)"></span> will be saved as a credit on this customer's account for future invoices.
              </p>
            </div>
          </div>
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
              <ul x-show="bOpen" class="absolute z-50 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-200 rounded-xl shadow-xl max-h-48 overflow-y-auto">
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

    
    <div class="space-y-2.5">
      <button type="button" @click="submit('draft')" :disabled="submitting"
              class="btn-secondary w-full flex items-center justify-center gap-2 py-2.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
        Save Draft
      </button>
      <button type="button" @click="submit('confirmed')" :disabled="submitting"
              class="w-full flex items-center justify-center gap-2 py-2.5 rounded-lg font-semibold text-sm text-white transition-all shadow hover:shadow-lg active:scale-[0.98]"
              style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
        <template x-if="submitting"><svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg></template>
        <template x-if="!submitting"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></template>
        <span x-text="submitting ? 'Creating…' : 'Create Invoice'"></span>
      </button>
      <a href="<?php echo e(url('/invoices')); ?>"
         class="btn-secondary w-full flex items-center justify-center gap-2 py-2.5 text-center">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
        Cancel
      </a>
    </div>

  </div>
  </div>

</div>


<div x-show="showWalkInModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showWalkInModal = false">
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Walk-in Customer</h3>
      <button @click="showWalkInModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <form @submit.prevent="saveWalkIn()" class="p-6 space-y-4">
      <p class="text-xs text-gray-400 -mt-1">For a one-off, not-yet-registered customer. Just the essentials — a full profile can be added later from Customers.</p>
      <div>
        <label class="label">Name <span class="text-red-500">*</span></label>
        <input x-model="walkInForm.name" type="text" class="input w-full" placeholder="Customer name" required autofocus />
      </div>
      <div>
        <label class="label">Phone</label>
        <input x-model="walkInForm.phone" type="tel" class="input w-full" placeholder="+94 77 000 0000" />
      </div>
      <div>
        <label class="label">Address</label>
        <textarea x-model="walkInForm.address" rows="2" class="input w-full resize-none" placeholder="Optional"></textarea>
      </div>
      <div x-show="walkInError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="walkInError"></div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" @click="showWalkInModal = false" class="btn-secondary">Cancel</button>
        <button type="submit" class="btn-primary" :disabled="walkInSaving" x-text="walkInSaving ? 'Saving…' : 'Add & Select'"></button>
      </div>
    </form>
  </div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function invoiceCreate() {
  return {
    customers:            [],
    products:             [],
    services:             [],
    branches:             [],
    banks:                [],
    branchUsers:          [],
    items:                [],
    submitting:           false,
    customerBalance:      null,
    customerCreditBalance:0,
    customerCreditLimit:  0,
    showWalkInModal:      false,
    walkInSaving:         false,
    walkInError:          '',
    walkInForm:           { name: '', phone: '', address: '' },
    taxRate:              0,
    subtotal:             0,
    discountTotal:        0,
    taxAmount:            0,
    grandTotal:           0,
    get remainingAfterCredit() {
      return Math.max(0, this.grandTotal - (this.payment.use_credit ? Math.min(this.customerCreditBalance, this.grandTotal) : 0));
    },
    get payOverpayment() {
      if (!this.payment.enabled) return 0;
      return Math.max(0, parseFloat(this.payment.amount || 0) - this.remainingAfterCredit);
    },
    form: {
      customer_id:  '',
      branch_id:    '',
      invoice_date: new Date().toISOString().slice(0, 10),
      due_date:     '',
      reference:    '',
      notes:        '',
      sales_rep_id: '',
    },
    payment: {
      enabled:       false,
      method:        'cash',
      amount:        0,
      date:          new Date().toISOString().slice(0, 10),
      reference:     '',
      notes:         '',
      cheque_number: '',
      bank_name:     '',
      cheque_date:   '',
      account_id:    null,
      use_credit:    false,
    },
    accounts: [],

    get filteredCustomers() {
      if (!this.form.branch_id) return this.customers;
      return this.customers.filter(c => c.branch_id == this.form.branch_id);
    },
    get cashAccounts() { return this.accounts.filter(a => a.group === 'Cash Accounts' && !/cheque/i.test(a.name) && a.branch_id == this.form.branch_id); },
    get bankAccounts() { return this.accounts.filter(a => a.group === 'Bank Accounts' && a.branch_id == this.form.branch_id); },

    async init() {
      const [cd, pd, svd, bd, ad] = await Promise.all([
        apiFetch('/customers?per_page=999').then(r => r.json()),
        apiFetch('/products?per_page=999').then(r => r.json()),
        apiFetch('/services?per_page=999&is_active=1').then(r => r.json()),
        apiFetch('/branches').then(r => r.json()),
        apiFetch('/accounting/accounts').then(r => r.json()),
      ]);
      this.customers = cd.data || cd || [];
      this.products  = pd.data || pd || [];
      this.services  = svd.data || svd || [];
      this.branches  = bd.data || bd || [];
      this.accounts  = ad || [];
      this.banks     = await loadBanks();
      try {
        const u = JSON.parse(localStorage.getItem('medri_user') || '{}');
        const stored = localStorage.getItem('medri_branch');
        const bid = (stored && stored !== 'all') ? stored : u.default_branch_id;
        if (bid) { this.form.branch_id = bid; await this.loadBranchUsers(); }
      } catch (_) {}
      if (this.items.length === 0) this.addRow();
      this.$watch('payment.enabled', enabled => {
        if (enabled) this.payment.amount = this.grandTotal;
      });
    },

    async loadBranchUsers() {
      this.branchUsers = [];
      this.form.sales_rep_id = '';
      if (!this.form.branch_id) return;
      try {
        const r = await apiFetch('/users?branch_id=' + this.form.branch_id + '&per_page=999&is_active=1');
        const d = await r.json();
        this.branchUsers = d.data || d || [];
      } catch (_) {}
    },

    get creditLimitExceeded() {
      return this.customerCreditLimit > 0
        && this.customerBalance !== null
        && (this.customerBalance + this.grandTotal) > this.customerCreditLimit;
    },
    get creditOverBy() {
      return Math.max(0, (this.customerBalance ?? 0) + this.grandTotal - this.customerCreditLimit);
    },

    async loadCustomerBalance() {
      this.customerBalance       = null;
      this.customerCreditBalance = 0;
      this.customerCreditLimit   = 0;
      this.payment.use_credit    = false;
      if (!this.form.customer_id) return;
      try {
        const r = await apiFetch('/customers/' + this.form.customer_id);
        const d = await r.json();
        const cust = d.customer ?? d;
        this.customerBalance       = parseFloat(cust.outstanding_balance ?? cust.balance ?? 0);
        this.customerCreditBalance = parseFloat(cust.credit_balance ?? 0);
        this.customerCreditLimit   = parseFloat(cust.credit_limit ?? 0);
      } catch (_) { this.customerBalance = null; }
    },

    openWalkInModal() {
      if (!this.form.branch_id) { toast('Select a branch first', 'error'); return; }
      this.walkInForm = { name: '', phone: '', address: '' };
      this.walkInError = '';
      this.showWalkInModal = true;
    },

    async saveWalkIn() {
      if (!this.walkInForm.name.trim()) return;
      this.walkInSaving = true;
      this.walkInError = '';
      try {
        const branch = this.branches.find(b => b.id == this.form.branch_id);
        const payload = {
          branch_id:   this.form.branch_id,
          name:        this.walkInForm.name.trim(),
          phone:       this.walkInForm.phone.trim() || null,
          address:     this.walkInForm.address.trim() || null,
          type:        'individual',
          city:        branch?.city || 'N/A',
          district:    branch?.city || 'N/A',
          is_walk_in:  true,
        };
        const r = await apiFetch('/customers', { method: 'POST', body: JSON.stringify(payload) });
        const created = await r.json();
        this.customers.push(created);
        this.form.customer_id = created.id;
        this.showWalkInModal = false;
        await this.loadCustomerBalance();
        toast('Walk-in customer added.', 'success');
      } catch (e) {
        this.walkInError = e.message ?? 'Failed to add walk-in customer.';
      } finally {
        this.walkInSaving = false;
      }
    },

    onUseCreditChange() {
      this.payment.amount = this.remainingAfterCredit;
    },

    onPayAmountChange() { /* payOverpayment is a computed getter — updates reactively */ },

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
        const branchId = this.form.branch_id;
        const r    = await apiFetch('/products/' + p.id + '/batches' + (branchId ? '?branch_id=' + branchId : ''));
        const data = await r.json();
        row.batches = Array.isArray(data) ? data : (data.data ?? []);
        if (row.batches.length === 1) {
          // Single batch — use regular qty stepper
          row.batch_id     = row.batches[0].id;
          row.batch_number = row.batches[0].batch_number;
          row.batch_cost   = parseFloat(row.batches[0].unit_cost || 0);
          if (row.batches[0].selling_price) row.unit_price = parseFloat(row.batches[0].selling_price);
        } else if (row.batches.length > 1) {
          // Multiple batches — show allocation UI, oldest batch (first, since
          // the endpoint already orders oldest-first) defaults to covering the
          // requested quantity; staff can still override any layer manually.
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

    async submit(status) {
      if (!this.form.customer_id)   { toast('Please select a customer', 'error'); return; }
      if (!this.form.branch_id)     { toast('Please select a branch', 'error'); return; }
      if (!this.form.sales_rep_id)  { toast('Please select a sales rep', 'error'); return; }
      if (!this.items.length)       { toast('Add at least one item', 'error'); return; }
      if (this.items.some(r => r.type === 'service' ? !r.service_id : !r.product_id)) {
        toast('Every line needs a product or service selected', 'error'); return;
      }
      if (this.creditLimitExceeded) {
        toast('Credit limit exceeded: limit ' + fmtMoney(this.customerCreditLimit)
          + ', outstanding ' + fmtMoney(this.customerBalance ?? 0)
          + ' + this invoice ' + fmtMoney(this.grandTotal)
          + ' is over by ' + fmtMoney(this.creditOverBy), 'error');
        return;
      }

      if (this.payment.enabled && status === 'confirmed') {
        if (!this.payment.amount && !this.payment.use_credit) { toast('Please enter a payment amount', 'error'); return; }
        if (this.payment.amount > 0 && this.payment.method === 'cheque') {
          if (!this.payment.cheque_number) { toast('Please enter the cheque number', 'error'); return; }
          if (!this.payment.bank_name)     { toast('Please enter the bank name', 'error'); return; }
          if (!this.payment.cheque_date)   { toast('Please enter the cheque date', 'error'); return; }
        }
        if (this.payment.amount > 0 && this.payment.method === 'cash' && !this.payment.account_id) {
          toast('Select which cash account this payment went into', 'error'); return;
        }
        if (this.payment.amount > 0 && this.payment.method === 'bank_transfer' && !this.payment.account_id) {
          toast('Select which bank account this payment went into', 'error'); return;
        }
      }

      this.submitting = true;
      try {
        const payload = {
          branch_id:    this.form.branch_id,
          customer_id:  this.form.customer_id,
          type:         'invoice',
          sales_rep_id: this.form.sales_rep_id || null,
          invoice_date: this.form.invoice_date,
          due_date:     this.form.due_date || null,
          notes:        this.form.notes || null,
          tax_percent:  this.taxRate,
          status,
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

        if (this.payment.enabled && status === 'confirmed') {
          payload.initial_payment = {
            payment_method:   this.payment.method,
            amount:           parseFloat(this.payment.amount || 0),
            payment_date:     this.payment.date,
            reference_number: this.payment.reference || null,
            notes:            this.payment.notes || null,
            cheque_number:    this.payment.cheque_number || null,
            bank_name:        this.payment.bank_name || null,
            cheque_date:      this.payment.cheque_date || null,
            account_id:       this.payment.account_id || null,
            use_credit:       this.payment.use_credit || false,
          };
        }

        const res = await apiFetch('/invoices', { method: 'POST', body: JSON.stringify(payload) });
        if (res.ok) {
          const d = await res.json();
          toast('Invoice created successfully', 'success');
          setTimeout(() => { window.location.href = BASE + '/invoices/' + (d.id || ''); }, 600);
        } else {
          const e = await res.json();
          toast(e.message || 'Failed to create invoice', 'error');
        }
      } catch (e) { toast(e.message || 'Failed to create invoice', 'error'); }
      this.submitting = false;
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\invoices\create.blade.php ENDPATH**/ ?>