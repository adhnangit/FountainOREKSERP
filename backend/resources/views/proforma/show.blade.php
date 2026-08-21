@extends('layouts.app')
@section('title', 'Proforma Invoice')
@section('page-title', 'Proforma Invoice')
@section('page-desc', 'View and manage this draft invoice')

@push('head')
<style>
  .cv-dd { position:relative; }
  .cv-dd-menu {
    position:absolute; left:0; right:0; top:calc(100% + 3px);
    background:#fff; border:1px solid #e2e8f0; border-radius:10px;
    box-shadow:0 8px 24px rgba(0,0,0,0.13); z-index:200; overflow:hidden;
  }
  .dark .cv-dd-menu { background:#1e2533; border-color:#2d3748; }
  .cv-dd-item { display:flex; align-items:center; gap:8px; width:100%; padding:8px 10px; text-align:left; cursor:pointer; border:none; background:transparent; transition:background 0.1s; }
  .cv-dd-item:hover, .cv-dd-item.active { background:#eef2ff; }
  .dark .cv-dd-item:hover, .dark .cv-dd-item.active { background:rgba(27,62,182,0.15); }
</style>
@endpush

@section('content')
<div x-data="proformaShow()" x-init="init()">

  <template x-if="!loading && proforma">
    <div class="max-w-4xl mx-auto space-y-5">

      <!-- Top bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
          <a href="{{ url('/proforma-invoices') }}"
             class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-100 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 19l-7-7 7-7"/></svg>
            Back
          </a>
          <h2 class="text-lg font-bold text-gray-800 dark:text-white"
              x-text="proforma.proforma_number || ('#PI-' + String(proforma.id).padStart(4,'0'))"></h2>
          <span class="badge" :class="{
            'badge-warning': proforma.status === 'draft',
            'badge-primary': proforma.status === 'sent',
            'badge-success': proforma.status === 'converted',
            'badge-danger':  proforma.status === 'cancelled',
          }" x-text="proforma.status"></span>
        </div>
        <div class="flex items-center gap-2">
          <button @click="window.print()"
                  class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print
          </button>
          <template x-if="proforma.status === 'draft' || proforma.status === 'sent'">
            <a :href="BASE + '/proforma-invoices/' + proforma.id + '/convert'"
               class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-lg text-sm font-semibold bg-primary-600 hover:bg-primary-700 text-white transition-colors">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Convert to Invoice
            </a>
          </template>
        </div>
      </div>

      <!-- Converted notice -->
      <template x-if="proforma.status === 'converted' && proforma.invoice_id">
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl border"
             style="background:#f0fdf4;border-color:#bbf7d0">
          <svg class="w-4 h-4 flex-shrink-0" style="color:#22A845" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span class="text-xs" style="color:#14532d">
            Converted to invoice.
            <a :href="BASE + '/invoices/' + proforma.invoice_id" class="font-semibold underline ml-1">View Invoice →</a>
          </span>
        </div>
      </template>

      <!-- Details card -->
      <div class="card p-6">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-6">
          <div>
            <div class="label">Customer</div>
            <div class="font-semibold text-sm text-gray-800 dark:text-white" x-text="proforma.customer?.name || '—'"></div>
            <div class="text-xs text-gray-400" x-text="proforma.customer?.phone || ''"></div>
          </div>
          <div>
            <div class="label">Branch</div>
            <div class="font-semibold text-sm text-gray-800 dark:text-white" x-text="proforma.branch?.name || '—'"></div>
          </div>
          <div>
            <div class="label">Date</div>
            <div class="font-semibold text-sm text-gray-800 dark:text-white" x-text="fmtDate(proforma.proforma_date || proforma.created_at)"></div>
          </div>
          <div>
            <div class="label">Valid Until</div>
            <div class="font-semibold text-sm" x-text="proforma.valid_until ? fmtDate(proforma.valid_until) : '—'"
                 :class="isExpired ? 'text-red-500' : 'text-gray-800 dark:text-white'"></div>
            <div x-show="isExpired" class="text-xs text-red-400 mt-0.5">Expired</div>
          </div>
          <div>
            <div class="label">Created By</div>
            <div class="font-semibold text-sm text-gray-800 dark:text-white" x-text="proforma.created_by?.name || '—'"></div>
          </div>
        </div>
      </div>

      <!-- Line items -->
      <div class="card overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/30">
              <tr>
                <th class="table-hd">Product</th>
                <th class="table-hd text-center">Qty</th>
                <th class="table-hd text-right">Unit Price</th>
                <th class="table-hd text-center">Discount</th>
                <th class="table-hd text-right">Total</th>
              </tr>
            </thead>
            <tbody>
              <template x-for="line in (proforma.items || [])" :key="line.id">
                <tr class="border-b border-gray-50 dark:border-gray-700/50">
                  <td class="table-td">
                    <div class="font-medium" x-text="line.product?.name || line.product_name || '—'"></div>
                    <div class="text-xs text-gray-400" x-text="line.product?.sku || ''"></div>
                  </td>
                  <td class="table-td text-center" x-text="line.quantity || line.qty"></td>
                  <td class="table-td text-right" x-text="fmtMoney(line.unit_price)"></td>
                  <td class="table-td text-center" x-text="(line.discount || line.discount_percent || 0) + '%'"></td>
                  <td class="table-td text-right font-semibold" x-text="fmtMoney(line.total || line.line_total)"></td>
                </tr>
              </template>
            </tbody>
            <tfoot class="border-t border-gray-200 dark:border-gray-700">
              <tr x-show="(proforma.discount_amount||0) > 0">
                <td colspan="4" class="table-td text-right text-gray-500">Discount</td>
                <td class="table-td text-right text-red-500 font-semibold" x-text="'– ' + fmtMoney(proforma.discount_amount)"></td>
              </tr>
              <tr class="bg-gray-50 dark:bg-gray-700/30">
                <td colspan="4" class="table-td text-right font-bold text-gray-800 dark:text-white">Total</td>
                <td class="table-td text-right font-bold text-lg" style="color:#1B3EB6"
                    x-text="fmtMoney(proforma.total_amount || proforma.total)"></td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Notes -->
      <div class="card p-5" x-show="proforma.notes">
        <div class="label mb-2">Notes / Terms</div>
        <p class="text-sm text-gray-600 dark:text-gray-300 whitespace-pre-line" x-text="proforma.notes"></p>
      </div>

    </div>
  </template>

  <template x-if="loading">
    <div class="flex items-center justify-center h-64 text-gray-400">Loading…</div>
  </template>

  <!-- ===================== CONVERT MODAL ===================== -->
  <div x-show="showConvertModal" x-cloak
       class="fixed inset-0 z-50 flex items-start justify-center p-4 overflow-y-auto">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showConvertModal = false"></div>
    <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-3xl z-10 my-6">

      <!-- Modal header -->
      <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between"
           style="background:linear-gradient(135deg,#1B3EB6,#2563eb);border-radius:1rem 1rem 0 0">
        <div>
          <h3 class="text-base font-bold text-white">Convert to Invoice</h3>
          <p class="text-xs text-blue-200 mt-0.5">Review and edit before confirming — changes here won't affect the original proforma</p>
        </div>
        <button @click="showConvertModal = false"
                class="p-1.5 rounded-lg text-blue-200 hover:text-white hover:bg-white/20 transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <div class="px-6 py-5 space-y-5">

        <!-- Invoice dates & terms -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
          <div>
            <label class="label">Invoice Date <span class="text-red-500">*</span></label>
            <input type="date" x-model="convertForm.invoice_date" class="input" />
          </div>
          <div>
            <label class="label">Due Date</label>
            <input type="date" x-model="convertForm.due_date" class="input" />
          </div>
          <div>
            <label class="label">Tax %</label>
            <input type="number" x-model.number="convertForm.tax_percent" min="0" max="100" step="0.01"
                   class="input" placeholder="0" @input="recalc()" />
          </div>
        </div>

        <!-- Line items editor -->
        <div>
          <div class="flex items-center justify-between mb-2">
            <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">Line Items</div>
            <button @click="addLine()"
                    class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 hover:text-primary-700">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
              Add Line
            </button>
          </div>
          <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
              <thead class="bg-gray-50 dark:bg-gray-700/50">
                <tr>
                  <th class="table-hd">Product</th>
                  <th class="table-hd text-right w-20">Qty</th>
                  <th class="table-hd text-right w-28">Unit Price</th>
                  <th class="table-hd text-right w-20">Disc %</th>
                  <th class="table-hd text-right w-28">Line Total</th>
                  <th class="table-hd w-8"></th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <template x-for="(line, idx) in convertForm.items" :key="idx">
                  <tr>
                    <!-- Product dropdown cell -->
                    <td class="table-td" style="min-width:180px">
                      <div class="cv-dd"
                           x-data="{ open: false, q: '' }"
                           @click.away="open = false"
                           @keydown.escape="open = false">
                        <button type="button"
                                @click="open = !open; if(open) $nextTick(() => $refs['cps'+idx]?.focus())"
                                class="input text-xs w-full text-left flex items-center justify-between gap-1 py-1.5 px-2">
                          <span class="truncate"
                                :class="line.product_id ? 'text-gray-800 dark:text-gray-100' : 'text-gray-400'"
                                x-text="line.product_id ? (products.find(p=>p.id==line.product_id)?.name || line.product_name || '—') : (line.product_name || '— Select —')"></span>
                          <svg class="w-3 h-3 text-gray-400 flex-shrink-0" :class="open?'rotate-180':''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open" class="cv-dd-menu">
                          <div class="p-1.5 border-b border-gray-100 dark:border-gray-700">
                            <input :x-ref="'cps'+idx" x-ref="'cps'+idx" x-model="q" type="text"
                                   placeholder="Search…" class="input text-xs py-1 w-full" @keydown.stop />
                          </div>
                          <div class="max-h-48 overflow-y-auto py-1">
                            <template x-for="p in products.filter(p => !q || p.name.toLowerCase().includes(q.toLowerCase()) || (p.code||'').toLowerCase().includes(q.toLowerCase()))" :key="p.id">
                              <button type="button"
                                      @click="selectProduct(line, p); open=false; q=''"
                                      class="cv-dd-item"
                                      :class="line.product_id==p.id ? 'active' : ''">
                                <div class="flex-1 min-w-0">
                                  <div class="text-xs font-medium text-gray-800 dark:text-gray-100 truncate" x-text="p.name"></div>
                                  <div class="text-[10px] text-gray-400" x-text="p.code||''"></div>
                                </div>
                                <div class="text-right flex-shrink-0">
                                  <div class="text-xs font-semibold tabular-nums"
                                       :class="stockQty(p)<=0?'text-red-500':stockQty(p)<=(p.reorder_level||5)?'text-orange-500':'text-green-600'"
                                       x-text="stockQty(p)+' '+(p.unit||'pcs')"></div>
                                  <div class="text-[10px] text-gray-400">in stock</div>
                                </div>
                              </button>
                            </template>
                            <div x-show="products.filter(p=>!q||p.name.toLowerCase().includes(q.toLowerCase())).length===0"
                                 class="px-3 py-2 text-xs text-gray-400 text-center">No products found</div>
                          </div>
                        </div>
                      </div>
                      <!-- stock badge under selected product -->
                      <template x-if="line.product_id">
                        <div class="flex items-center gap-1 mt-1">
                          <span class="text-[10px] px-1.5 py-0.5 rounded font-medium"
                                :class="stockQty(products.find(p=>p.id==line.product_id))<=0
                                  ? 'bg-red-50 text-red-600'
                                  : stockQty(products.find(p=>p.id==line.product_id))<=(products.find(p=>p.id==line.product_id)?.reorder_level||5)
                                    ? 'bg-orange-50 text-orange-600'
                                    : 'bg-green-50 text-green-600'"
                                x-text="'Stock: '+stockQty(products.find(p=>p.id==line.product_id))+' '+(products.find(p=>p.id==line.product_id)?.unit||'pcs')"></span>
                          <span x-show="line.quantity > stockQty(products.find(p=>p.id==line.product_id))"
                                class="text-[10px] text-red-500 font-medium">⚠ Exceeds stock</span>
                        </div>
                      </template>
                    </td>
                    <td class="table-td">
                      <input type="number" x-model.number="line.quantity" min="0.01" step="0.01"
                             class="input text-right py-1 px-2 text-xs" @input="recalc()" />
                    </td>
                    <td class="table-td">
                      <input type="number" x-model.number="line.unit_price" min="0" step="0.01"
                             class="input text-right py-1 px-2 text-xs" @input="recalc()" />
                    </td>
                    <td class="table-td">
                      <input type="number" x-model.number="line.discount_percent" min="0" max="100" step="0.01"
                             class="input text-right py-1 px-2 text-xs" @input="recalc()" />
                    </td>
                    <td class="table-td text-right font-semibold tabular-nums text-gray-800 dark:text-gray-100"
                        x-text="fmtMoney(lineTotal(line))"></td>
                    <td class="table-td text-center">
                      <button @click="removeLine(idx)" x-show="convertForm.items.length > 1"
                              class="p-1 rounded text-gray-300 hover:text-red-500 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                      </button>
                    </td>
                  </tr>
                </template>
              </tbody>
              <!-- Totals -->
              <tfoot class="bg-gray-50 dark:bg-gray-700/30 text-sm">
                <tr x-show="convertForm.tax_percent > 0">
                  <td colspan="4" class="table-td text-right text-gray-500 text-xs">Subtotal</td>
                  <td class="table-td text-right tabular-nums" x-text="fmtMoney(calcSubtotal)"></td>
                  <td></td>
                </tr>
                <tr x-show="convertForm.tax_percent > 0">
                  <td colspan="4" class="table-td text-right text-gray-500 text-xs" x-text="'Tax ('+convertForm.tax_percent+'%)'"></td>
                  <td class="table-td text-right tabular-nums text-gray-600 dark:text-gray-300" x-text="fmtMoney(calcTax)"></td>
                  <td></td>
                </tr>
                <tr>
                  <td colspan="4" class="table-td text-right font-bold text-blue-700 dark:text-blue-300">Grand Total</td>
                  <td class="table-td text-right font-bold text-base text-blue-700 dark:text-blue-300 tabular-nums" x-text="fmtMoney(calcTotal)"></td>
                  <td></td>
                </tr>
              </tfoot>
            </table>
          </div>
        </div>

        <!-- Notes -->
        <div>
          <label class="label">Notes</label>
          <textarea x-model="convertForm.notes" rows="2" class="input"></textarea>
        </div>

      </div>

      <!-- Modal footer -->
      <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3">
        <button @click="showConvertModal = false" class="btn-secondary">Cancel</button>
        <button @click="submitConvert()" :disabled="converting"
                class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-semibold bg-primary-600 hover:bg-primary-700 text-white transition-colors disabled:opacity-50">
          <svg x-show="!converting" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <svg x-show="converting" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
          <span x-text="converting ? 'Converting…' : 'Confirm & Convert'"></span>
        </button>
      </div>
    </div>
  </div>

</div>

@push('scripts')
<script>
function proformaShow() {
  const id = location.pathname.split('/').filter(Boolean).pop();
  return {
    loading: true, proforma: null, products: [],
    showConvertModal: false, converting: false,
    convertForm: {
      invoice_date: new Date().toISOString().slice(0,10),
      due_date: '', tax_percent: 0, notes: '', items: [],
    },

    get isExpired() {
      if (!this.proforma?.valid_until) return false;
      return new Date(this.proforma.valid_until) < new Date();
    },
    get calcSubtotal() {
      return (this.convertForm.items || []).reduce((s, l) => s + this.lineTotal(l), 0);
    },
    get calcTax() {
      return Math.round(this.calcSubtotal * (this.convertForm.tax_percent || 0) / 100 * 100) / 100;
    },
    get calcTotal() {
      return Math.round((this.calcSubtotal + this.calcTax) * 100) / 100;
    },

    async init() {
      try {
        const [pr, proformaR] = await Promise.all([
          apiFetch('/products?per_page=999').then(r => r.json()),
          apiFetch('/proforma-invoices/' + id),
        ]);
        this.products = pr.data || pr || [];
        this.proforma = await proformaR.json();
      } catch { toast('Failed to load', 'error'); }
      this.loading = false;
    },

    stockQty(p) {
      if (!p) return 0;
      const s = p.branch_stocks?.[0] ?? p.branchStocks?.[0];
      return parseFloat(s?.quantity ?? s?.stock ?? 0);
    },

    openConvertModal() {
      const p = this.proforma;
      this.convertForm = {
        invoice_date: new Date().toISOString().slice(0,10),
        due_date: p.valid_until ? p.valid_until.slice(0,10) : '',
        tax_percent: 0,
        notes: p.notes || '',
        items: (p.items || []).map(it => ({
          product_id:       it.product_id,
          product_name:     it.product?.name || it.product_name || '—',
          quantity:         parseFloat(it.quantity || it.qty || 1),
          unit_price:       parseFloat(it.unit_price || 0),
          discount_percent: parseFloat(it.discount || it.discount_percent || 0),
        })),
      };
      this.showConvertModal = true;
    },

    selectProduct(line, p) {
      line.product_id       = p.id;
      line.product_name     = p.name;
      line.unit_price       = parseFloat(p.selling_price || p.price || 0);
      line.discount_percent = 0;
    },

    lineTotal(line) {
      const base = (line.quantity || 0) * (line.unit_price || 0);
      const disc = base * ((line.discount_percent || 0) / 100);
      return Math.round((base - disc) * 100) / 100;
    },

    recalc() { /* totals are computed properties — nothing needed */ },

    addLine() {
      this.convertForm.items.push({ product_id: null, product_name: '', quantity: 1, unit_price: 0, discount_percent: 0 });
    },

    removeLine(idx) {
      this.convertForm.items.splice(idx, 1);
    },

    async submitConvert() {
      if (!this.convertForm.invoice_date) { toast('Invoice date is required', 'error'); return; }
      if (!this.convertForm.items.length) { toast('At least one line item is required', 'error'); return; }
      this.converting = true;
      try {
        const r = await apiFetch('/proforma-invoices/' + id + '/convert', {
          method: 'POST',
          body: JSON.stringify(this.convertForm),
        });
        const d = await r.json();
        if (r.ok) {
          toast('Converted successfully! Redirecting…', 'success');
          setTimeout(() => window.location.href = BASE + '/invoices/' + d.invoice_id, 1200);
        } else {
          toast(d.message || 'Conversion failed', 'error');
        }
      } catch { toast('Conversion failed', 'error'); }
      this.converting = false;
    },
  };
}
</script>
@endpush
@endsection
