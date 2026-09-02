<?php $__env->startSection('title', 'Supplier Invoices'); ?>
<?php $__env->startSection('page-title', 'Supplier Invoices'); ?>
<?php $__env->startSection('page-desc', 'Manage invoices received from suppliers'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="supplierInvoicesPage()" x-init="init()">

  
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <div class="flex flex-col sm:flex-row gap-2">
      <input x-model="search" type="text" placeholder="Search invoice# or supplier…" class="input w-64" />
      <select x-model="statusFilter" class="input w-48">
        <option value="">All Statuses</option>
        <option value="pending">Pending</option>
        <option value="partially_paid">Partially Paid</option>
        <option value="paid">Paid</option>
        <option value="cancelled">Cancelled</option>
      </select>
    </div>
    <button @click="openCreate()" class="btn-primary flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
      New Supplier Invoice
    </button>
  </div>

  
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="card p-4">
      <p class="text-xs text-gray-500 mb-1">Total Invoices</p>
      <p class="text-2xl font-bold text-gray-800 dark:text-gray-100" x-text="items.length"></p>
    </div>
    <div class="card p-4">
      <p class="text-xs text-gray-500 mb-1">Total Amount</p>
      <p class="text-2xl font-bold" style="color:#1B3EB6" x-text="fmtMoney(items.reduce((s,i)=>s+parseFloat(i.total||0),0))"></p>
    </div>
    <div class="card p-4">
      <p class="text-xs text-gray-500 mb-1">Amount Paid</p>
      <p class="text-2xl font-bold text-green-600" x-text="fmtMoney(items.reduce((s,i)=>s+parseFloat(i.paid_amount||0),0))"></p>
    </div>
    <div class="card p-4">
      <p class="text-xs text-gray-500 mb-1">Outstanding</p>
      <p class="text-2xl font-bold text-red-600" x-text="fmtMoney(items.reduce((s,i)=>s+parseFloat(i.balance_due||0),0))"></p>
    </div>
  </div>

  
  <div class="card p-0 overflow-hidden">
    <div x-show="loading" class="flex items-center justify-center py-16">
      <svg class="animate-spin w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
      </svg>
    </div>
    <div x-show="!loading" class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800/40">
          <tr>
            <th class="table-hd">Invoice #</th>
            <th class="table-hd">Supplier</th>
            <th class="table-hd">Date</th>
            <th class="table-hd">Due Date</th>
            <th class="table-hd text-right">Amount</th>
            <th class="table-hd text-right">Paid</th>
            <th class="table-hd text-right">Balance</th>
            <th class="table-hd">Status</th>
            <th class="table-hd">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
          <template x-for="inv in filtered" :key="inv.id">
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
              <td class="table-td font-medium" style="color:#1B3EB6" x-text="inv.invoice_number ?? ('#SINV-' + inv.id)"></td>
              <td class="table-td">
                <a :href="BASE + '/suppliers/' + inv.supplier?.id" class="hover:underline font-medium text-gray-800 dark:text-gray-100"
                   x-text="inv.supplier?.name ?? '—'"></a>
              </td>
              <td class="table-td text-sm text-gray-500" x-text="fmtDate(inv.invoice_date)"></td>
              <td class="table-td text-sm"
                  :class="isOverdue(inv) ? 'text-red-600 font-semibold' : 'text-gray-500'"
                  x-text="inv.due_date ? fmtDate(inv.due_date) : '—'"></td>
              <td class="table-td text-right font-semibold tabular-nums" x-text="fmtMoney(inv.total ?? 0)"></td>
              <td class="table-td text-right tabular-nums text-green-700" x-text="fmtMoney(inv.paid_amount ?? 0)"></td>
              <td class="table-td text-right tabular-nums font-semibold"
                  :class="(inv.balance_due ?? 0) > 0 ? 'text-red-600' : 'text-gray-400'"
                  x-text="fmtMoney(inv.balance_due ?? 0)"></td>
              <td class="table-td">
                <span class="text-xs px-2.5 py-1 rounded-full font-semibold"
                      :class="statusClass(inv.status)"
                      x-text="statusLabel(inv.status)"></span>
              </td>
              <td class="table-td">
                <div class="flex items-center gap-2">
                  <template x-if="inv.grn && inv.grn.status === 'draft'">
                    <button @click="openReceive(inv)"
                            class="text-sm font-semibold px-3 py-1 rounded-lg transition-colors"
                            style="background:#f0fdf4;color:#15803d;border:1px solid #86efac"
                            onmouseover="this.style.background='#dcfce7'"
                            onmouseout="this.style.background='#f0fdf4'">
                      Receive Items
                    </button>
                  </template>
                  <template x-if="inv.grn && inv.grn.status === 'confirmed'">
                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold bg-green-100 text-green-700">Received</span>
                  </template>
                  <template x-if="inv.balance_due > 0 && inv.status !== 'cancelled'">
                    <button @click="openPayment(inv)"
                            class="text-sm font-semibold px-3 py-1 rounded-lg transition-colors"
                            style="background:#eef2ff;color:#1B3EB6"
                            onmouseover="this.style.background='#c7d2fe'"
                            onmouseout="this.style.background='#eef2ff'">
                      Pay
                    </button>
                  </template>
                  <template x-if="inv.balance_due <= 0 && (!inv.grn || inv.grn.status === 'confirmed')">
                    <span class="text-xs text-gray-400">Settled</span>
                  </template>
                </div>
              </td>
            </tr>
          </template>
          <tr x-show="!loading && filtered.length === 0">
            <td colspan="9" class="table-td text-center text-gray-400 py-12">No supplier invoices found.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  
  <template x-if="showCreate">
    <div class="fixed inset-0 z-50 flex items-start justify-center p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
      <div class="absolute inset-0 bg-black/50" @click="showCreate = false"></div>
      <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-xl z-10 my-4">

        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700"
             style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-base font-bold text-white">New Supplier Invoice</h3>
              <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.6)">Record a bill received from supplier</p>
            </div>
            <button @click="showCreate = false" class="text-white/60 hover:text-white">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>

        <div class="px-6 py-5 space-y-4 max-h-[80vh] overflow-y-auto">

          <div class="grid grid-cols-2 gap-3">
            <div class="col-span-2">
              <label class="label">Supplier <span class="text-red-500">*</span></label>
              <div class="search-dd" x-data="{ open: false, q: '' }" @click.away="open = false" @keydown.escape="open = false">
                <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.sInv?.focus())"
                        class="input w-full text-left flex items-center justify-between gap-2">
                  <span class="truncate" :class="cf.supplier_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                        x-text="cf.supplier_id ? (suppliers.find(s => s.id == cf.supplier_id)?.name || '—') : '— Select supplier —'"></span>
                  <svg class="w-3.5 h-3.5 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu">
                  <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                    <input x-ref="sInv" x-model="q" type="text" placeholder="Search supplier…" class="input text-sm w-full py-1.5" @keydown.stop />
                  </div>
                  <div class="max-h-52 overflow-y-auto py-1">
                    <template x-for="s in suppliers.filter(s => !q || s.name.toLowerCase().includes(q.toLowerCase()))" :key="s.id">
                      <button type="button" @click="cf.supplier_id = s.id; open = false; q = ''"
                              class="search-dd-item" :class="cf.supplier_id == s.id ? 'active' : ''">
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
              <input type="date" x-model="cf.invoice_date" class="input" />
            </div>
            <div>
              <label class="label">Due Date</label>
              <input type="date" x-model="cf.due_date" class="input" />
            </div>
            <div class="col-span-2">
              <label class="label">Supplier's Invoice #</label>
              <input type="text" x-model="cf.supplier_invoice_number" class="input" placeholder="e.g. INV-0091 (from supplier)" />
            </div>
          </div>

          
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="label mb-0">Items <span class="text-red-500">*</span></label>
              <button type="button" @click="addItem()"
                      class="text-xs font-semibold px-3 py-1 rounded-lg"
                      style="background:#eef2ff;color:#1B3EB6">+ Add Row</button>
            </div>
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-visible">
              <table class="w-full text-xs">
                <thead style="background:#f8fafc">
                  <tr>
                    <th class="text-left px-2 py-2 font-semibold text-gray-600 w-1/3 rounded-tl-xl">Product</th>
                    <th class="text-right px-2 py-2 font-semibold text-gray-600 w-16">Qty</th>
                    <th class="text-right px-2 py-2 font-semibold text-gray-600 w-24">Unit Cost</th>
                    <th class="text-left px-2 py-2 font-semibold text-gray-600 w-20">Batch #</th>
                    <th class="text-left px-2 py-2 font-semibold text-gray-600 w-24">Expiry</th>
                    <th class="text-right px-2 py-2 font-semibold text-gray-600 w-20">Total</th>
                    <th class="w-8 rounded-tr-xl"></th>
                  </tr>
                </thead>
                <tbody>
                  <template x-for="(row, idx) in cf.items" :key="idx">
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                      <td class="px-2 py-1.5">
                        <div class="search-dd" x-data="{ open: false, q: '' }" @click.away="open = false" @keydown.escape="open = false">
                          <button type="button" @click="open = !open; if(open) $nextTick(() => $refs.pRow?.focus())"
                                  class="input text-xs py-1 w-full text-left flex items-center justify-between gap-1">
                            <span class="truncate" :class="row.product_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                                  x-text="row.product_id ? (products.find(p => p.id == row.product_id)?.name || '—') : '— Product —'"></span>
                            <svg class="w-3 h-3 text-gray-400 flex-shrink-0 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                          </button>
                          <div x-show="open" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="search-dd-menu">
                            <div class="p-2 border-b border-gray-100 dark:border-gray-700">
                              <input x-ref="pRow" x-model="q" type="text" placeholder="Search product…" class="input text-xs w-full py-1.5" @keydown.stop />
                            </div>
                            <div class="max-h-52 overflow-y-auto py-1">
                              <template x-for="p in products.filter(p => !q || p.name.toLowerCase().includes(q.toLowerCase()))" :key="p.id">
                                <button type="button" @click="row.product_id = p.id; calcCreateTotal(); open = false; q = ''"
                                        class="search-dd-item" :class="row.product_id == p.id ? 'active' : ''">
                                  <span class="text-xs font-medium text-gray-800 dark:text-gray-100 truncate flex-1" x-text="p.name"></span>
                                </button>
                              </template>
                              <div x-show="products.filter(p => !q || p.name.toLowerCase().includes(q.toLowerCase())).length === 0"
                                   class="px-4 py-3 text-xs text-gray-400 text-center">No products found</div>
                            </div>
                          </div>
                        </div>
                      </td>
                      <td class="px-2 py-1.5">
                        <input type="number" x-model.number="row.quantity" @input="calcCreateTotal()"
                               min="0.01" step="0.01" class="input text-xs py-1 text-right tabular-nums w-full" />
                      </td>
                      <td class="px-2 py-1.5">
                        <input type="number" x-model.number="row.unit_cost" @input="calcCreateTotal()"
                               min="0" step="0.01" class="input text-xs py-1 text-right tabular-nums w-full" />
                      </td>
                      <td class="px-2 py-1.5">
                        <input type="text" x-model="row.batch_number" class="input text-xs py-1 w-full" placeholder="Optional" />
                      </td>
                      <td class="px-2 py-1.5">
                        <input type="date" x-model="row.expiry_date" class="input text-xs py-1 w-full" />
                      </td>
                      <td class="px-2 py-1.5 text-right font-semibold tabular-nums text-gray-700 dark:text-gray-200"
                          x-text="fmtMoney((row.quantity||0)*(row.unit_cost||0))"></td>
                      <td class="px-1 py-1.5 text-center">
                        <button type="button" @click="cf.items.splice(idx,1); calcCreateTotal()"
                                x-show="cf.items.length > 1"
                                class="text-gray-300 hover:text-red-500 transition-colors text-base leading-none">✕</button>
                      </td>
                    </tr>
                  </template>
                </tbody>
              </table>
            </div>
          </div>

          
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="label">Tax Amount</label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">Rs.</span>
                <input type="number" x-model.number="cf.tax_amount" @input="calcCreateTotal()" min="0" step="0.01"
                       class="input text-right tabular-nums" style="padding-left:2rem" placeholder="0.00" />
              </div>
            </div>
            <div>
              <label class="label">Discount Amount</label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">Rs.</span>
                <input type="number" x-model.number="cf.discount_amount" @input="calcCreateTotal()" min="0" step="0.01"
                       class="input text-right tabular-nums" style="padding-left:2rem" placeholder="0.00" />
              </div>
            </div>
          </div>

          
          <div class="rounded-xl px-4 py-3" style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
            <div class="flex items-center justify-between">
              <span class="text-sm font-semibold text-white">Invoice Total</span>
              <span class="text-xl font-black tabular-nums text-white" x-text="fmtMoney(cf.total)"></span>
            </div>
            <div class="flex items-center justify-between mt-1 opacity-70">
              <span class="text-xs text-white">Subtotal</span>
              <span class="text-xs tabular-nums text-white" x-text="fmtMoney(cf.subtotal)"></span>
            </div>
          </div>

          <div>
            <label class="label">Notes</label>
            <textarea x-model="cf.notes" rows="2" class="input resize-none w-full text-sm" placeholder="Optional notes…"></textarea>
          </div>

        </div>

        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
          <button @click="showCreate = false" class="btn-secondary">Cancel</button>
          <button @click="submitCreate()" :disabled="creating"
                  class="btn-primary flex items-center gap-2">
            <template x-if="creating">
              <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            </template>
            <span x-text="creating ? 'Saving…' : 'Save Invoice'"></span>
          </button>
        </div>
      </div>
    </div>
  </template>

  
  <template x-if="showReceive">
    <div class="fixed inset-0 z-50 flex items-start justify-center p-4 overflow-y-auto"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
      <div class="absolute inset-0 bg-black/50" @click="showReceive = false"></div>
      <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-2xl z-10 my-4">

        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700"
             style="background:linear-gradient(135deg,#15803d,#166534)">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-base font-bold text-white">Receive Items</h3>
              <p class="text-xs mt-0.5 text-white/60"
                 x-text="'Invoice: ' + (selectedReceiveInv?.invoice_number ?? '') + '  ·  Supplier: ' + (selectedReceiveInv?.supplier?.name ?? '')"></p>
            </div>
            <button @click="showReceive = false" class="text-white/60 hover:text-white">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>

        <div class="px-6 py-5">
          <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
            Review the items below and confirm receipt to update stock and post the accounting journal.
          </p>

          <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
              <thead style="background:#f8fafc" class="dark:bg-gray-800/50">
                <tr>
                  <th class="text-left px-3 py-2 font-semibold text-gray-600 dark:text-gray-400">Product</th>
                  <th class="text-right px-3 py-2 font-semibold text-gray-600 dark:text-gray-400">Qty</th>
                  <th class="text-right px-3 py-2 font-semibold text-gray-600 dark:text-gray-400">Unit Cost</th>
                  <th class="text-left px-3 py-2 font-semibold text-gray-600 dark:text-gray-400">Batch #</th>
                  <th class="text-left px-3 py-2 font-semibold text-gray-600 dark:text-gray-400">Expiry</th>
                  <th class="text-right px-3 py-2 font-semibold text-gray-600 dark:text-gray-400">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                <template x-for="item in (selectedReceiveInv?.grn?.items ?? [])" :key="item.id">
                  <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                    <td class="px-3 py-2.5 font-medium text-gray-800 dark:text-gray-100"
                        x-text="item.product?.name ?? item.product_name ?? '—'"></td>
                    <td class="px-3 py-2.5 text-right tabular-nums" x-text="parseFloat(item.quantity_received).toLocaleString()"></td>
                    <td class="px-3 py-2.5 text-right tabular-nums" x-text="fmtMoney(item.unit_cost)"></td>
                    <td class="px-3 py-2.5 text-gray-500" x-text="item.batch_number || '—'"></td>
                    <td class="px-3 py-2.5 text-gray-500 text-xs" x-text="item.expiry_date ? fmtDate(item.expiry_date) : '—'"></td>
                    <td class="px-3 py-2.5 text-right font-semibold tabular-nums"
                        x-text="fmtMoney(item.total_cost)"></td>
                  </tr>
                </template>
              </tbody>
              <tfoot style="background:#f8fafc" class="dark:bg-gray-800/50">
                <tr>
                  <td colspan="5" class="px-3 py-2.5 text-right text-sm font-semibold text-gray-700 dark:text-gray-300">Total Cost</td>
                  <td class="px-3 py-2.5 text-right font-bold tabular-nums text-gray-900 dark:text-gray-100"
                      x-text="fmtMoney((selectedReceiveInv?.grn?.items ?? []).reduce((s,i)=>s+parseFloat(i.total_cost||0),0))"></td>
                </tr>
              </tfoot>
            </table>
          </div>

          <div class="mt-4 rounded-xl p-3 text-sm text-green-800 bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-800 dark:text-green-300">
            <strong>On confirm:</strong> Stock will be added for each item and a journal entry (DR Inventory / CR Accounts Payable) will be posted.
          </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
          <button @click="showReceive = false" class="btn-secondary">Cancel</button>
          <button @click="submitReceive()" :disabled="receiving"
                  class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-all"
                  style="background:#15803d" onmouseover="this.style.background='#166534'" onmouseout="this.style.background='#15803d'">
            <template x-if="receiving">
              <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            </template>
            <span x-text="receiving ? 'Confirming…' : 'Confirm Receipt'"></span>
          </button>
        </div>
      </div>
    </div>
  </template>

  
  <template x-if="showPayment">
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
      <div class="absolute inset-0 bg-black/50" @click="showPayment = false"></div>
      <div class="relative bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md z-10 flex flex-col overflow-hidden" style="max-height:90vh">

        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex-shrink-0"
             style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-base font-bold text-white">Record Payment</h3>
              <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.6)"
                 x-text="'For: ' + (selectedInv?.invoice_number ?? '') + ' · Balance: ' + fmtMoney(selectedInv?.balance_due ?? 0)"></p>
            </div>
            <button @click="showPayment = false" class="text-white/60 hover:text-white">
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>
        </div>

        <div class="px-6 py-5 space-y-4 flex-1 overflow-y-auto">

          <div>
            <label class="label">Amount <span class="text-red-500">*</span></label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 pointer-events-none">Rs.</span>
              <input type="number" x-model.number="pf.amount" :max="selectedInv?.balance_due" min="0.01" step="0.01"
                     class="input text-right tabular-nums w-full" style="padding-left:2rem" />
            </div>
            <p class="text-xs text-gray-400 mt-1 text-right">
              Max: <span class="font-semibold" x-text="fmtMoney(selectedInv?.balance_due ?? 0)"></span>
            </p>
          </div>

          <div>
            <label class="label">Payment Method <span class="text-red-500">*</span></label>
            <div class="grid grid-cols-3 gap-2">
              <template x-for="pm in payMethods" :key="pm.v">
                <button type="button"
                        @click="pf.payment_method = pm.v; pf.account_id = null; pf.cheque_type = 'issued'; pf.received_cheque_id = null; pf.cheque_number = ''; pf.bank_name = ''; pf.cheque_date = ''"
                        class="px-2 py-2 rounded-lg border text-xs font-semibold transition-all"
                        :style="pf.payment_method === pm.v
                          ? 'background:' + pm.bg + ';border-color:' + pm.border + ';color:' + pm.color
                          : 'background:transparent;border-color:#e5e7eb;color:#6b7280'">
                  <span x-text="pm.icon"></span>
                  <span x-text="pm.label"></span>
                </button>
              </template>
            </div>
          </div>

          
          <div x-show="pf.payment_method === 'cash'" x-transition>
            <label class="label">Cash Account <span class="text-red-500">*</span></label>
            <select x-model="pf.account_id" class="input text-sm">
              <option value="">— Select cash account —</option>
              <template x-for="a in cashAccounts" :key="a.id">
                <option :value="a.id" x-text="a.name + '  (' + a.code + ')'"></option>
              </template>
            </select>
            <p class="text-xs text-amber-600 mt-1" x-show="!cashAccounts.length">No cash accounts in CoA.</p>
          </div>

          
          <div x-show="pf.payment_method === 'bank_transfer'" x-transition>
            <label class="label">Bank Account <span class="text-red-500">*</span></label>
            <select x-model="pf.account_id" class="input text-sm">
              <option value="">— Select bank account —</option>
              <template x-for="a in bankAccounts" :key="a.id">
                <option :value="a.id" x-text="a.name + '  (' + a.code + ')'"></option>
              </template>
            </select>
            <p class="text-xs text-amber-600 mt-1" x-show="!bankAccounts.length">No bank accounts in CoA.</p>
          </div>

          
          <div x-show="pf.payment_method === 'cheque'" x-transition class="space-y-3">
            <div>
              <label class="label">Cheque Mode</label>
              <div class="grid grid-cols-2 gap-2">
                <button type="button" @click="pf.cheque_type = 'issued'; pf.received_cheque_id = null"
                        class="px-3 py-2 rounded-lg border text-xs font-semibold transition-all"
                        :style="pf.cheque_type === 'issued'
                          ? 'background:#fffbeb;border-color:#d97706;color:#92400e'
                          : 'background:transparent;border-color:#e5e7eb;color:#6b7280'">
                  📄 Issue New Cheque
                </button>
                <button type="button" @click="pf.cheque_type = 'received'; pf.cheque_number = ''; pf.bank_name = ''; pf.cheque_date = ''"
                        class="px-3 py-2 rounded-lg border text-xs font-semibold transition-all"
                        :style="pf.cheque_type === 'received'
                          ? 'background:#ede9fe;border-color:#7c3aed;color:#5b21b6'
                          : 'background:transparent;border-color:#e5e7eb;color:#6b7280'">
                  📨 Received Cheque
                </button>
              </div>
            </div>

            
            <div x-show="pf.cheque_type === 'issued'" class="space-y-2 p-3 rounded-xl"
                 style="background:#fffbeb;border:1px solid #fde68a">
              <p class="text-xs font-semibold" style="color:#92400e">Issue our own cheque</p>
              <div>
                <label class="label text-xs">Bank Account <span class="text-red-500">*</span></label>
                <select x-model="pf.account_id" class="input text-sm">
                  <option value="">— Select bank account —</option>
                  <template x-for="a in bankAccounts" :key="a.id">
                    <option :value="a.id" x-text="a.name + '  (' + a.code + ')'"></option>
                  </template>
                </select>
              </div>
              <div>
                <label class="label text-xs">Cheque Number <span class="text-red-500">*</span></label>
                <input type="text" x-model="pf.cheque_number" class="input text-sm" placeholder="e.g. 001234" />
              </div>
              <div class="grid grid-cols-2 gap-2">
                <div>
                  <label class="label text-xs">Bank Name <span class="text-red-500">*</span></label>
                  <div x-data="{bq:'',bOpen:false}" @click.outside="bOpen=false" class="relative">
                    <input type="text" :value="pf.bank_name"
                      @input="pf.bank_name=$event.target.value;bq=$event.target.value;bOpen=true"
                      @focus="bq=pf.bank_name||'';bOpen=true" @keydown.escape="bOpen=false"
                      class="input text-sm" placeholder="Search bank…" autocomplete="off" />
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
                <div>
                  <label class="label text-xs">Cheque Date <span class="text-red-500">*</span></label>
                  <input type="date" x-model="pf.cheque_date" class="input text-sm" />
                </div>
              </div>
            </div>

            
            <div x-show="pf.cheque_type === 'received'" class="space-y-2 p-3 rounded-xl"
                 style="background:#ede9fe;border:1px solid #c4b5fd">
              <p class="text-xs font-semibold" style="color:#5b21b6">Use a customer's cheque you hold</p>
              <select x-model="pf.received_cheque_id" class="input text-sm">
                <option value="">— Select received cheque —</option>
                <template x-for="c in receivedCheques" :key="c.id">
                  <option :value="c.id"
                          x-text="'#' + c.cheque_number + '  ' + c.bank_name + '  Rs. ' + parseFloat(c.amount).toLocaleString() + (c.customer ? '  (' + c.customer.name + ')' : '')"></option>
                </template>
              </select>
              <p class="text-xs" style="color:#7c3aed" x-show="!receivedCheques.length">No received cheques in hand for this branch.</p>
              <p class="text-xs text-gray-500">This cheque will be marked as <strong>deposited</strong> once payment is recorded.</p>
            </div>
          </div>

          <div>
            <label class="label">Payment Date <span class="text-red-500">*</span></label>
            <input type="date" x-model="pf.payment_date" class="input" />
          </div>

          <div>
            <label class="label">Reference #</label>
            <input type="text" x-model="pf.reference_number" class="input" placeholder="Optional" />
          </div>

        </div>

        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 flex-shrink-0">
          <button @click="showPayment = false" class="btn-secondary">Cancel</button>
          <button @click="submitPayment()" :disabled="paying"
                  class="btn-primary flex items-center gap-2">
            <template x-if="paying">
              <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            </template>
            <span x-text="paying ? 'Recording…' : 'Record Payment'"></span>
          </button>
        </div>
      </div>
    </div>
  </template>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function supplierInvoicesPage() {
  return {
    items: [], suppliers: [], products: [], loading: true,
    cashAccounts: [], bankAccounts: [], receivedCheques: [],
    search: '', statusFilter: '',
    showCreate: false, creating: false,
    showPayment: false, paying: false,
    showReceive: false, receiving: false,
    selectedInv: null, selectedReceiveInv: null,

    payMethods: [
      { v:'cash',          label:'Cash',     icon:'💵', bg:'#f0fdf4', border:'#22c55e', color:'#15803d' },
      { v:'bank_transfer', label:'Bank',     icon:'🏦', bg:'#faf5ff', border:'#a855f7', color:'#7e22ce' },
      { v:'cheque',        label:'Cheque',   icon:'📄', bg:'#fffbeb', border:'#f59e0b', color:'#b45309' },
    ],

    cf: { supplier_id:'', supplier_invoice_number:'', invoice_date: new Date().toISOString().slice(0,10), due_date:'', subtotal:0, tax_amount:0, discount_amount:0, total:0, notes:'', items:[{ product_id:'', quantity:1, unit_cost:0, batch_number:'', expiry_date:'' }] },
    pf: { amount:0, payment_method:'cash', payment_date: new Date().toISOString().slice(0,10), reference_number:'', account_id:null, cheque_type:'issued', received_cheque_id:null, cheque_number:'', bank_name:'', cheque_date:'' },
    banks: [],

    get filtered() {
      let list = this.items;
      if (this.statusFilter) list = list.filter(i => i.status === this.statusFilter);
      const q = this.search.toLowerCase();
      if (!q) return list;
      return list.filter(i =>
        (i.invoice_number ?? '').toLowerCase().includes(q) ||
        (i.supplier?.name ?? '').toLowerCase().includes(q)
      );
    },

    isOverdue(inv) {
      if (!inv.due_date || inv.status === 'paid' || inv.status === 'cancelled') return false;
      return new Date(inv.due_date) < new Date();
    },

    statusClass(s) {
      const map = {
        paid:           'bg-green-100 text-green-700',
        pending:        'bg-yellow-100 text-yellow-700',
        partially_paid: 'bg-blue-100 text-blue-700',
        overdue:        'bg-red-100 text-red-700',
        cancelled:      'bg-gray-100 text-gray-500',
      };
      return map[s] ?? 'bg-gray-100 text-gray-500';
    },

    statusLabel(s) {
      const map = { paid:'Paid', pending:'Pending', partially_paid:'Partial', cancelled:'Cancelled' };
      return map[s] ?? s;
    },

    async init() {
      try {
        const [invR, suppR, prodR, accR, chqR] = await Promise.all([
          apiFetch('/supplier-invoices').then(r => r.json()),
          apiFetch('/suppliers?per_page=999').then(r => r.json()),
          apiFetch('/products?per_page=500').then(r => r.json()),
          apiFetch('/accounting/accounts').then(r => r.json()),
          apiFetch('/cheques?direction=received&status=in_hand&per_page=100').then(r => r.json()),
        ]);
        this.items     = invR.data  ?? invR  ?? [];
        this.suppliers = suppR.data ?? suppR ?? [];
        this.products  = prodR.data ?? prodR ?? [];
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

    calcCreateTotal() {
      this.cf.subtotal = this.cf.items.reduce((s, r) => s + (parseFloat(r.quantity)||0) * (parseFloat(r.unit_cost)||0), 0);
      this.cf.total = this.cf.subtotal + parseFloat(this.cf.tax_amount || 0) - parseFloat(this.cf.discount_amount || 0);
    },

    addItem() {
      this.cf.items.push({ product_id:'', quantity:1, unit_cost:0, batch_number:'', expiry_date:'' });
    },

    openCreate() {
      this.cf = {
        supplier_id:'', supplier_invoice_number:'',
        invoice_date: new Date().toISOString().slice(0,10), due_date:'',
        subtotal:0, tax_amount:0, discount_amount:0, total:0, notes:'',
        items:[{ product_id:'', quantity:1, unit_cost:0, batch_number:'', expiry_date:'' }],
      };
      this.showCreate = true;
    },

    async submitCreate() {
      if (!this.cf.supplier_id)  { toast('Select a supplier', 'error'); return; }
      if (!this.cf.invoice_date) { toast('Invoice date required', 'error'); return; }
      const validItems = this.cf.items.filter(r => r.product_id && r.quantity > 0);
      if (!validItems.length)    { toast('Add at least one item with a product', 'error'); return; }

      const me = await (await apiFetch('/auth/me')).json();
      const branchId = localStorage.getItem('medri_branch') || me.default_branch_id;

      this.creating = true;
      try {
        const r = await apiFetch('/supplier-invoices', {
          method: 'POST',
          body: JSON.stringify({
            branch_id:               parseInt(branchId),
            supplier_id:             parseInt(this.cf.supplier_id),
            supplier_invoice_number: this.cf.supplier_invoice_number || null,
            invoice_date:            this.cf.invoice_date,
            due_date:                this.cf.due_date || null,
            tax_amount:              parseFloat(this.cf.tax_amount || 0),
            discount_amount:         parseFloat(this.cf.discount_amount || 0),
            notes:                   this.cf.notes || null,
            items: validItems.map(row => ({
              product_id:   parseInt(row.product_id),
              quantity:     parseFloat(row.quantity),
              unit_cost:    parseFloat(row.unit_cost),
              batch_number: row.batch_number || null,
              expiry_date:  row.expiry_date  || null,
            })),
          }),
        });
        const d = await r.json();
        if (r.ok) {
          this.items.unshift(d);
          this.showCreate = false;
          toast('Invoice created — click "Receive Items" to confirm stock receipt', 'success');
        } else {
          const msg = d.errors ? Object.values(d.errors).flat().join(' · ') : (d.message || 'Failed to create invoice');
          toast(msg, 'error');
        }
      } finally {
        this.creating = false;
      }
    },

    openReceive(inv) {
      this.selectedReceiveInv = inv;
      this.showReceive = true;
    },

    async submitReceive() {
      this.receiving = true;
      try {
        const r = await apiFetch('/supplier-invoices/' + this.selectedReceiveInv.id + '/receive', { method: 'POST' });
        const d = await r.json();
        if (r.ok) {
          const idx = this.items.findIndex(i => i.id === this.selectedReceiveInv.id);
          if (idx !== -1 && this.items[idx].grn) {
            this.items[idx].grn.status = 'confirmed';
          }
          this.showReceive = false;
          toast('Items received — stock updated and journal posted', 'success');
        } else {
          toast(d.message || 'Failed to confirm receipt', 'error');
        }
      } finally {
        this.receiving = false;
      }
    },

    openPayment(inv) {
      this.selectedInv = inv;
      this.pf = {
        amount:             parseFloat(inv.balance_due ?? 0),
        payment_method:     'cash',
        payment_date:       new Date().toISOString().slice(0,10),
        reference_number:   '',
        account_id:         null,
        cheque_type:        'issued',
        received_cheque_id: null,
        cheque_number:      '',
        bank_name:          '',
        cheque_date:        '',
      };
      // Pre-select first cash account if available
      if (this.cashAccounts.length) this.pf.account_id = this.cashAccounts[0].id;
      this.showPayment = true;
    },

    async submitPayment() {
      if (!this.pf.amount || this.pf.amount <= 0) { toast('Enter a payment amount', 'error'); return; }
      if ((this.pf.payment_method === 'cash' || this.pf.payment_method === 'bank_transfer') && !this.pf.account_id) {
        toast('Please select the account to use for this payment', 'error'); return;
      }
      if (this.pf.payment_method === 'cheque') {
        if (this.pf.cheque_type === 'issued') {
          if (!this.pf.account_id)     { toast('Select a bank account for the cheque', 'error'); return; }
          if (!this.pf.cheque_number)  { toast('Enter cheque number', 'error'); return; }
          if (!this.pf.bank_name)      { toast('Enter bank name', 'error'); return; }
          if (!this.pf.cheque_date)    { toast('Enter cheque date', 'error'); return; }
        } else {
          if (!this.pf.received_cheque_id) { toast('Select a received cheque from the list', 'error'); return; }
        }
      }

      this.paying = true;
      try {
        const r = await apiFetch('/supplier-invoices/' + this.selectedInv.id + '/payment', {
          method: 'POST',
          body: JSON.stringify({
            amount:             this.pf.amount,
            payment_method:     this.pf.payment_method,
            payment_date:       this.pf.payment_date,
            reference_number:   this.pf.reference_number || null,
            account_id:         this.pf.account_id ? parseInt(this.pf.account_id) : null,
            cheque_type:        this.pf.cheque_type || 'issued',
            received_cheque_id: this.pf.received_cheque_id ? parseInt(this.pf.received_cheque_id) : null,
            cheque_number:      this.pf.cheque_number || null,
            bank_name:          this.pf.bank_name || null,
            cheque_date:        this.pf.cheque_date || null,
          }),
        });
        const d = await r.json();
        if (r.ok) {
          const idx = this.items.findIndex(i => i.id === this.selectedInv.id);
          if (idx !== -1) this.items.splice(idx, 1, Object.assign({}, this.items[idx], d.invoice ?? {}));
          this.showPayment = false;
          toast('Payment recorded and journal entry posted', 'success');
        } else {
          toast(d.message || 'Payment failed', 'error');
        }
      } finally {
        this.paying = false;
      }
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\purchase\supplier-invoices.blade.php ENDPATH**/ ?>