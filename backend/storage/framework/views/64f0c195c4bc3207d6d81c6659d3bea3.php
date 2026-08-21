
<?php $__env->startSection('title', 'Create Supplier Invoice'); ?>
<?php $__env->startSection('page-title', 'Create Supplier Invoice'); ?>
<?php $__env->startSection('page-desc', 'Create a new supplier invoice / purchase order'); ?>

<?php $__env->startPush('head'); ?>
<style>
  /* This page uses the green/purchase accent instead of the default blue for its searchable dropdowns */
  .search-dd-item:hover { background:#f0fdf4; }
  .dark .search-dd-item:hover { background:rgba(16,185,129,0.1); }
  .search-dd-item.active { background:#f0fdf4; }
  .dark .search-dd-item.active { background:rgba(16,185,129,0.12); }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div x-data="poCreatePage()" x-init="init()" class="px-6 pb-12">
<form @submit.prevent="submit()">
<div class="flex flex-col lg:flex-row gap-6">

  
  <div class="w-full lg:flex-[65] min-w-0 space-y-5">

    
    <div class="card overflow-hidden">

      
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
          <div class="group px-5 py-4 hover:bg-emerald-50/40 dark:hover:bg-emerald-900/10 transition-colors">
            <div class="flex items-start gap-3">

              
              <div class="flex-shrink-0 mt-1 w-6 h-6 rounded-md flex items-center justify-center text-xs font-bold"
                   style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46"
                   x-text="idx + 1"></div>

              <div class="flex-1 min-w-0">
                
                <div class="flex items-center gap-2 mb-2.5">
                  <div class="search-dd flex-1 min-w-0" @click.away="row.open = false" @keydown.escape="row.open = false">
                    <button type="button"
                            @click="row.open = !row.open"
                            class="input text-sm w-full text-left flex items-center justify-between gap-2"
                            :class="!row.product_id ? 'border-emerald-200 dark:border-emerald-700/60' : ''">
                      <span class="truncate"
                            :class="row.product_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                            x-text="row.product_id ? (products.find(p => p.id == row.product_id)?.name || '—') : '— Select product —'"></span>
                      <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="row.open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="row.open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu">
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
                      <button type="button" @click="openProductModal(idx)"
                              class="w-full flex items-center gap-2 px-4 py-2.5 text-xs font-semibold text-emerald-700 border-t border-gray-100 dark:border-gray-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/20">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M12 4v16m8-8H4"/></svg>
                        Create New Product
                      </button>
                    </div>
                  </div>
                  <button type="button" @click="removeRow(idx)"
                          x-show="items.length > 1"
                          class="flex-shrink-0 w-7 h-7 rounded-lg flex items-center justify-center text-gray-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all"
                          title="Remove">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  </button>
                </div>

                
                <div class="flex items-center gap-2">
                  
                  <div class="flex items-center rounded-lg border border-gray-200 dark:border-gray-600 overflow-hidden flex-shrink-0" style="height:36px">
                    <button type="button" @click="row.qty = Math.max(1,(row.qty||1)-1); calcRow(idx)"
                            class="w-8 flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors border-r border-gray-200 dark:border-gray-600 h-full text-lg leading-none select-none">−</button>
                    <input type="number" x-model.number="row.qty" @input="calcRow(idx)" min="1" step="1"
                           class="w-14 text-center text-sm font-semibold text-gray-700 dark:text-gray-200 bg-transparent border-none outline-none h-full tabular-nums" style="box-shadow:none" />
                    <button type="button" @click="row.qty = (row.qty||0)+1; calcRow(idx)"
                            class="w-8 flex items-center justify-center text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors border-l border-gray-200 dark:border-gray-600 h-full text-lg leading-none select-none">+</button>
                  </div>
                  
                  <div class="flex-1 min-w-0 relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium pointer-events-none">Rs.</span>
                    <input type="number" x-model.number="row.unit_cost" @input="calcRow(idx)" min="0" step="0.01" placeholder="0.00"
                           class="input text-sm text-right tabular-nums w-full" style="padding-left:2rem" />
                  </div>
                  
                  <div class="text-right flex-shrink-0 min-w-[90px]">
                    <div class="text-xs text-gray-400 mb-0.5">Total</div>
                    <div class="text-sm font-bold tabular-nums" style="color:#065f46" x-text="fmtMoney(row.line_total)"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>

        
        <div x-show="items.length === 0" class="py-14 text-center">
          <div class="w-12 h-12 rounded-xl mx-auto mb-3 flex items-center justify-center" style="background:#d1fae5">
            <svg class="w-6 h-6" style="color:#059669" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          </div>
          <p class="text-sm font-medium text-gray-400">No items added</p>
        </div>
      </div>

      
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

    </div>

    
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

  </div>

  
  <div class="w-full lg:flex-[35]">
  <div class="lg:sticky lg:top-6 space-y-5">

    
    <div class="card">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 rounded-t-xl"
           style="background:linear-gradient(135deg,#065f46,#064e3b)">
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
            <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
          </div>
          <div>
            <h3 class="text-sm font-bold text-white">Supplier &amp; Invoice Details</h3>
            <p class="text-xs" style="color:rgba(255,255,255,0.6)">Invoice information</p>
          </div>
        </div>
      </div>
      <div class="px-5 py-4 space-y-4">

        
        <div x-show="isSuperAdmin">
          <label class="label">Branch <span class="text-red-500">*</span></label>
          <select x-model="form.branch_id" class="input">
            <option value="">Select branch…</option>
            <template x-for="b in branches" :key="b.id">
              <option :value="b.id" x-text="b.name"></option>
            </template>
          </select>
        </div>

        <div>
          <label class="label">Supplier <span class="text-red-500">*</span></label>
          <div class="search-dd" x-data="{ open: false, q: '' }" @click.away="open = false" @keydown.escape="open = false">
            <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.sPo?.focus())"
                    class="input w-full text-left flex items-center justify-between gap-2">
              <span class="truncate" :class="form.supplier_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                    x-text="form.supplier_id ? (suppliers.find(s => s.id == form.supplier_id)?.name || '—') : 'Select supplier…'"></span>
              <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu">
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

        <div>
          <label class="label">Payment Method <span class="text-red-500">*</span></label>
          <div class="grid grid-cols-2 gap-2">
            <template x-for="pm in paymentMethods" :key="pm.value">
              <button type="button" @click="form.payment_method = pm.value; form.account_id = null; form.cheque_type = 'issued'; form.received_cheque_id = null; form.cheque_number = ''; form.cheque_bank_name = ''; form.cheque_date = ''"
                      class="px-3 py-2.5 rounded-lg border text-xs font-semibold transition-all flex items-center gap-1.5"
                      :style="form.payment_method === pm.value
                        ? 'background:'+pm.bg+';border-color:'+pm.border+';color:'+pm.color
                        : 'background:transparent;border-color:#e5e7eb;color:#6b7280'">
                <span x-text="pm.icon"></span>
                <span x-text="pm.label"></span>
              </button>
            </template>
          </div>
        </div>

        <div x-show="form.payment_method === 'on_account'">
          <label class="label">Payment Terms (days)</label>
          <input type="number" x-model.number="form.payment_terms_days" min="0" placeholder="e.g. 30" class="input text-sm" />
        </div>

        
        <div x-show="form.payment_method === 'cash'" x-transition class="space-y-1">
          <label class="label">Cash Account <span class="text-red-500">*</span></label>
          <select x-model="form.account_id" class="input text-sm">
            <option value="">— Select cash account —</option>
            <template x-for="a in cashAccounts" :key="a.id">
              <option :value="a.id" x-text="a.name + '  (' + a.code + ')'"></option>
            </template>
          </select>
          <p class="text-xs text-amber-600 mt-0.5" x-show="!cashAccounts.length">No cash accounts in CoA — add one first.</p>
        </div>

        
        <div x-show="form.payment_method === 'bank_transfer'" x-transition class="space-y-1">
          <label class="label">Bank Account <span class="text-red-500">*</span></label>
          <select x-model="form.account_id" class="input text-sm">
            <option value="">— Select bank account —</option>
            <template x-for="a in bankAccounts" :key="a.id">
              <option :value="a.id" x-text="a.name + '  (' + a.code + ')'"></option>
            </template>
          </select>
          <p class="text-xs text-amber-600 mt-0.5" x-show="!bankAccounts.length">No bank accounts in CoA — add one first.</p>
        </div>

        
        <div x-show="form.payment_method === 'cheque'" x-transition class="space-y-3">
          <div>
            <label class="label">Cheque Mode</label>
            <div class="grid grid-cols-2 gap-2">
              <button type="button" @click="form.cheque_type='issued'; form.received_cheque_id=null"
                      class="px-3 py-2.5 rounded-lg border text-xs font-semibold transition-all flex items-center gap-1.5"
                      :style="form.cheque_type==='issued'
                        ? 'background:#fffbeb;border-color:#d97706;color:#92400e'
                        : 'background:transparent;border-color:#e5e7eb;color:#6b7280'">
                📄 Issue New Cheque
              </button>
              <button type="button" @click="form.cheque_type='received'; form.cheque_number=''; form.cheque_bank_name=''; form.cheque_date=''"
                      class="px-3 py-2.5 rounded-lg border text-xs font-semibold transition-all flex items-center gap-1.5"
                      :style="form.cheque_type==='received'
                        ? 'background:#ede9fe;border-color:#7c3aed;color:#5b21b6'
                        : 'background:transparent;border-color:#e5e7eb;color:#6b7280'">
                📨 Received Cheque
              </button>
            </div>
          </div>

          
          <div x-show="form.cheque_type === 'issued'" class="space-y-2 p-3 rounded-xl"
               style="background:#fffbeb;border:1px solid #fde68a">
            <p class="text-xs font-semibold" style="color:#92400e">Issue our own cheque</p>
            <div>
              <label class="label text-xs">Bank Account <span class="text-red-500">*</span></label>
              <select x-model="form.account_id" class="input text-sm">
                <option value="">— Select bank account —</option>
                <template x-for="a in bankAccounts" :key="a.id">
                  <option :value="a.id" x-text="a.name + '  (' + a.code + ')'"></option>
                </template>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="label text-xs">Cheque Number</label>
                <input type="text" x-model="form.cheque_number" class="input text-sm" placeholder="e.g. 001234" />
              </div>
              <div>
                <label class="label text-xs">Cheque Date</label>
                <input type="date" x-model="form.cheque_date" class="input text-sm" />
              </div>
            </div>
            <div>
              <label class="label text-xs">Bank Name</label>
              <div x-data="{bq:'',bOpen:false}" @click.outside="bOpen=false" class="relative">
                <input type="text" :value="form.cheque_bank_name"
                  @input="form.cheque_bank_name=$event.target.value;bq=$event.target.value;bOpen=true"
                  @focus="bq=form.cheque_bank_name||'';bOpen=true" @keydown.escape="bOpen=false"
                  class="input text-sm" placeholder="Search bank…" autocomplete="off" />
                <ul x-show="bOpen" class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-xl shadow-xl max-h-44 overflow-y-auto">
                  <template x-for="b in banks.filter(b=>b.name.toLowerCase().includes(bq.toLowerCase()))" :key="b.id">
                    <li @mousedown.prevent="form.cheque_bank_name=b.name;bq=b.name;bOpen=false"
                        :class="form.cheque_bank_name===b.name?'bg-indigo-50 text-indigo-700 font-medium':'hover:bg-gray-50 text-gray-700'"
                        class="px-3 py-2 text-sm cursor-pointer" x-text="b.name"></li>
                  </template>
                  <li x-show="!banks.filter(b=>b.name.toLowerCase().includes(bq.toLowerCase())).length" class="px-3 py-2 text-sm text-gray-400 text-center">No banks found</li>
                </ul>
              </div>
            </div>
          </div>

          
          <div x-show="form.cheque_type === 'received'" class="space-y-2 p-3 rounded-xl"
               style="background:#ede9fe;border:1px solid #c4b5fd">
            <p class="text-xs font-semibold" style="color:#5b21b6">Use a customer's cheque in hand</p>
            <select x-model="form.received_cheque_id" class="input text-sm">
              <option value="">— Select cheque in hand —</option>
              <template x-for="c in receivedCheques" :key="c.id">
                <option :value="c.id"
                        x-text="'#' + c.cheque_number + ' · ' + c.bank_name + ' · Rs. ' + parseFloat(c.amount).toLocaleString() + (c.customer ? ' (' + c.customer.name + ')' : '')"></option>
              </template>
            </select>
            <p class="text-xs text-purple-600" x-show="!receivedCheques.length">No received cheques in hand for this branch.</p>
          </div>
        </div>


      </div>
    </div>

    
    <div class="flex gap-3">
      <a href="<?php echo e(url('/purchase-orders')); ?>" class="btn-secondary flex-1 text-center">Cancel</a>
      <button type="submit" :disabled="submitting" class="btn-primary flex-1">
        <span x-text="submitting ? 'Saving…' : 'Create Invoice'"></span>
      </button>
    </div>

  </div>
  </div>

</div>
</form>


<div x-show="showProductModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showProductModal = false">
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
      <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">New Product</h3>
      <button @click="showProductModal = false" class="text-gray-400 hover:text-gray-600">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="px-5 py-4 space-y-3">
      <div>
        <label class="label">Name <span class="text-red-500">*</span></label>
        <input type="text" x-model="newProduct.name" class="input" placeholder="Product name" />
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label">SKU</label>
          <input type="text" x-model="newProduct.sku" class="input font-mono" placeholder="Optional" />
        </div>
        <div>
          <label class="label">Unit</label>
          <select x-model="newProduct.unit" class="input">
            <option value="pcs">Pieces</option>
            <option value="kg">Kilograms</option>
            <option value="g">Grams</option>
            <option value="l">Litres</option>
            <option value="ml">Millilitres</option>
            <option value="box">Box</option>
            <option value="pack">Pack</option>
            <option value="set">Set</option>
          </select>
        </div>
      </div>
      <div>
        <div class="flex items-center justify-between">
          <label class="label">Category</label>
          <button type="button" @click="openCategoryModal()" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">+ New Category</button>
        </div>
        <select x-model="newProduct.category_id" class="input">
          <option value="">Select category…</option>
          <template x-for="cat in categories" :key="cat.id">
            <option :value="cat.id" x-text="cat.name"></option>
          </template>
        </select>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="label">Cost Price</label>
          <input type="number" x-model.number="newProduct.cost_price" class="input tabular-nums" min="0" step="0.01" placeholder="0.00" />
        </div>
        <div>
          <label class="label">Selling Price <span class="text-red-500">*</span></label>
          <input type="number" x-model.number="newProduct.selling_price" class="input tabular-nums" min="0" step="0.01" placeholder="0.00" />
        </div>
      </div>
    </div>
    <div class="flex justify-end gap-3 px-5 py-4 border-t border-gray-100 dark:border-gray-700">
      <button type="button" @click="showProductModal = false" class="btn-secondary">Cancel</button>
      <button type="button" @click="createProductQuick()" :disabled="creatingProduct" class="btn-primary">
        <span x-text="creatingProduct ? 'Saving…' : 'Create Product'"></span>
      </button>
    </div>
  </div>
</div>


<div x-show="showCategoryModal" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 p-4" @click.self="showCategoryModal = false">
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
      <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">New Category</h3>
      <button @click="showCategoryModal = false" class="text-gray-400 hover:text-gray-600">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="px-5 py-4 space-y-3">
      <div>
        <label class="label">Name <span class="text-red-500">*</span></label>
        <input type="text" x-model="newCategory.name" class="input" placeholder="e.g. Surgical Equipment" />
      </div>
      <div>
        <label class="label">Code <span class="text-red-500">*</span></label>
        <input type="text" x-model="newCategory.code" class="input font-mono" placeholder="e.g. SURG" maxlength="20" />
      </div>
    </div>
    <div class="flex justify-end gap-3 px-5 py-4 border-t border-gray-100 dark:border-gray-700">
      <button type="button" @click="showCategoryModal = false" class="btn-secondary">Cancel</button>
      <button type="button" @click="createCategoryQuick()" :disabled="creatingCategory" class="btn-primary">
        <span x-text="creatingCategory ? 'Saving…' : 'Create Category'"></span>
      </button>
    </div>
  </div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function poCreatePage() {
    return {
        suppliers: [],
        products: [],
        categories: [],
        cashAccounts: [],
        bankAccounts: [],
        receivedCheques: [],
        submitting: false,
        subtotal: 0,
        showProductModal: false,
        creatingProduct: false,
        productModalRowIdx: null,
        newProduct: { name: '', sku: '', category_id: '', unit: 'pcs', cost_price: 0, selling_price: 0 },
        showCategoryModal: false,
        creatingCategory: false,
        newCategory: { name: '', code: '' },
        paymentMethods: [
            { value:'on_account',    label:'On Account',    icon:'📋', bg:'#eff6ff', border:'#3b82f6', color:'#1d4ed8' },
            { value:'cash',          label:'Cash',          icon:'💵', bg:'#f0fdf4', border:'#22c55e', color:'#15803d' },
            { value:'bank_transfer', label:'Bank Transfer', icon:'🏦', bg:'#faf5ff', border:'#a855f7', color:'#7e22ce' },
            { value:'cheque',        label:'Cheque',        icon:'📄', bg:'#fffbeb', border:'#f59e0b', color:'#b45309' },
        ],
        branches: [],
        isSuperAdmin: false,
        form: {
            branch_id: '',
            supplier_id: '',
            order_date: new Date().toISOString().slice(0, 10),
            due_date: '',
            expected_date: '',
            supplier_invoice_ref: '',
            reference: '',
            payment_method: 'on_account',
            payment_terms_days: 30,
            account_id: null,
            cheque_type: 'issued',
            received_cheque_id: null,
            cheque_number: '',
            cheque_bank_name: '',
            cheque_date: '',
            status: 'confirmed',
            notes: '',
        },
        banks: [],
        items: [{ product_id: '', qty: 1, unit_cost: 0, line_total: 0, open: false, q: '' }],
        async init() {
            try {
                const [sr, pr, accR, chqR, catR, brR] = await Promise.all([
                    apiFetch('/suppliers?per_page=200').then(r => r.json()),
                    apiFetch('/products?per_page=200').then(r => r.json()),
                    apiFetch('/accounting/accounts').then(r => r.json()),
                    apiFetch('/cheques?direction=received&status=in_hand&per_page=100').then(r => r.json()),
                    apiFetch('/products/categories').then(r => r.json()),
                    // Users without branches.view fall back to their assigned branches
                    apiFetch('/branches?active_only=true').then(r => r.json())
                        .catch(() => JSON.parse(localStorage.getItem('medri_user') || '{}').branches ?? []),
                ]);
                this.branches = brR.data ?? brR ?? [];
                const u = JSON.parse(localStorage.getItem('medri_user') || '{}');
                this.isSuperAdmin = !!u.is_super_admin || (u.roles ?? []).includes('super_admin');
                const storedBranch = localStorage.getItem('medri_branch');
                if (storedBranch && storedBranch !== 'all') {
                    this.form.branch_id = parseInt(storedBranch);
                } else if (u.default_branch_id) {
                    this.form.branch_id = u.default_branch_id;
                } else if (!this.isSuperAdmin && (u.branches ?? []).length) {
                    this.form.branch_id = u.branches[0].id;
                }
                this.suppliers = sr.data ?? sr ?? [];
                this.products  = pr.data ?? pr ?? [];
                const accounts = Array.isArray(accR) ? accR : (accR.data ?? []);
                this.cashAccounts    = accounts.filter(a => a.is_cash_account);
                this.bankAccounts    = accounts.filter(a => a.is_bank_account);
                this.receivedCheques = chqR.data ?? chqR ?? [];
                this.categories      = catR.data ?? catR ?? [];
                this.banks = await loadBanks();
            } catch (e) {
                toast('Failed to load data', 'error');
            }
        },
        openProductModal(rowIdx) {
            if (!this.form.branch_id) {
                toast('Switch your branch to create the product', 'error');
                return;
            }
            this.productModalRowIdx = rowIdx;
            this.newProduct = { name: '', sku: '', category_id: '', unit: 'pcs', cost_price: 0, selling_price: 0 };
            this.items[rowIdx].open = false;
            this.showProductModal = true;
        },
        async createProductQuick() {
            if (!this.newProduct.name || !this.newProduct.selling_price) {
                toast('Enter a name and selling price', 'error');
                return;
            }
            if (!this.form.branch_id) {
                toast('Switch your branch to create the product', 'error');
                return;
            }
            this.creatingProduct = true;
            try {
                const payload = { ...this.newProduct, branch_id: this.form.branch_id };
                const r = await apiFetch('/products', { method: 'POST', body: JSON.stringify(payload) });
                const product = await r.json();
                this.products.push(product);
                if (this.productModalRowIdx !== null) {
                    const row = this.items[this.productModalRowIdx];
                    row.product_id = product.id;
                    this.onProductSelect(row);
                }
                this.showProductModal = false;
                toast('Product created', 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to create product', 'error');
            } finally {
                this.creatingProduct = false;
            }
        },
        openCategoryModal() {
            if (!this.form.branch_id) {
                toast('Switch your branch to create the product', 'error');
                return;
            }
            this.newCategory = { name: '', code: '' };
            this.showCategoryModal = true;
        },
        async createCategoryQuick() {
            if (!this.newCategory.name || !this.newCategory.code) {
                toast('Enter a name and code for the category', 'error');
                return;
            }
            if (!this.form.branch_id) {
                toast('Switch your branch to create the product', 'error');
                return;
            }
            this.creatingCategory = true;
            try {
                const payload = { ...this.newCategory, branch_id: this.form.branch_id };
                const r = await apiFetch('/products/categories', { method: 'POST', body: JSON.stringify(payload) });
                const cat = await r.json();
                this.categories.push(cat);
                this.newProduct.category_id = cat.id;
                this.showCategoryModal = false;
                toast('Category created', 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to create category', 'error');
            } finally {
                this.creatingCategory = false;
            }
        },
        addRow() {
            this.items.push({ product_id: '', qty: 1, unit_cost: 0, line_total: 0, open: false, q: '' });
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
        async submit() {
            if (!this.form.branch_id) {
                toast(this.isSuperAdmin ? 'Please select a branch' : 'No branch is assigned to your account', 'error');
                return;
            }
            if (!this.form.supplier_id) { toast('Please select a supplier', 'error'); return; }
            if (this.items.some(r => !r.product_id)) { toast('Please select a product for every row', 'error'); return; }
            this.submitting = true;
            try {
                const branchId = parseInt(this.form.branch_id);
                const payload = {
                    ...this.form,
                    branch_id:          branchId,
                    account_id:         this.form.account_id         ? parseInt(this.form.account_id)         : null,
                    received_cheque_id: this.form.received_cheque_id ? parseInt(this.form.received_cheque_id) : null,
                    items: this.items.map(r => ({
                        product_id: r.product_id,
                        quantity:   r.qty,
                        unit_price: r.unit_cost,
                        tax_percent: 0,
                    })),
                };
                const r = await apiFetch('/purchase-orders', { method: 'POST', body: JSON.stringify(payload) });
                if (r.ok) {
                    const created = await r.json();
                    toast('Supplier invoice created', 'success');
                    const newId = (created.data ?? created).id;
                    window.location.href = BASE + '/purchase-orders';
                } else {
                    const e = await r.json();
                    toast(e.message ?? 'Failed to create', 'error');
                }
            } catch (e) {
                toast(e.message ?? 'Failed to create purchase order', 'error');
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/purchase/orders-create.blade.php ENDPATH**/ ?>