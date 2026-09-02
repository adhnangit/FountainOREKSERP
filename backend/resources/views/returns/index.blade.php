@extends('layouts.app')
@section('title', 'Sales Returns')
@section('page-title', 'Sales Returns')
@section('page-desc', 'Credit notes issued for returned goods')
@php $sec = 'sales'; @endphp

@section('content')
<style>
.sr-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px}
.sr-stat-card{background:#fff;border-radius:14px;padding:18px 20px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:14px;transition:box-shadow .2s,transform .2s}
.sr-stat-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08);transform:translateY(-2px)}
.sr-stat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sr-stat-icon svg{width:22px;height:22px}
.sr-stat-val{font-size:22px;font-weight:800;line-height:1.1;letter-spacing:-.5px}
.sr-stat-lbl{font-size:11.5px;color:#94a3b8;font-weight:500;margin-top:2px}

.sr-toolbar{background:#fff;border-radius:14px;padding:14px 18px;border:1px solid #e2e8f0;margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.sr-search-wrap{position:relative;flex:1;min-width:200px;max-width:340px}
.sr-search-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none}
.sr-search-wrap input{width:100%;border:1px solid #e2e8f0;border-radius:9px;padding:7px 12px 7px 34px;font-size:13px;color:#1e293b;background:#f8fafc;outline:none;transition:border-color .15s,box-shadow .15s}
.sr-search-wrap input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);background:#fff}

.sr-table-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden}
.sr-table{width:100%;border-collapse:separate;border-spacing:0}
.sr-table thead th{padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e2e8f0;white-space:nowrap}
.sr-table thead th:first-child{padding-left:20px}
.sr-table tbody tr{transition:background .1s}
.sr-table tbody tr:hover{background:#f8faff}
.sr-table tbody td{padding:13px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.sr-table tbody td:first-child{padding-left:20px}
.sr-table tbody tr:last-child td{border-bottom:none}
.sr-num{font-size:13px;font-weight:700;color:#4f46e5}

.sr-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:64px 24px;text-align:center}
.sr-empty svg{width:56px;height:56px;color:#e2e8f0}
.sr-empty h5{font-size:16px;font-weight:700;color:#475569;margin-top:14px}
.sr-empty p{font-size:13px;color:#94a3b8;margin-top:4px}

.dark .sr-stat-card{background:#1e293b;border-color:#334155}
.dark .sr-stat-lbl{color:#64748b}
.dark .sr-toolbar{background:#1e293b;border-color:#334155}
.dark .sr-search-wrap input{background:#0f172a;border-color:#334155;color:#e2e8f0}
.dark .sr-search-wrap input:focus{background:#1e293b}
.dark .sr-table-card{background:#1e293b;border-color:#334155}
.dark .sr-table thead th{background:#0f172a;border-color:#334155}
.dark .sr-table tbody tr:hover{background:#1e3351}
.dark .sr-table tbody td{border-color:#1e293b}
.dark .sr-empty svg{color:#334155}
.dark .sr-empty h5{color:#94a3b8}
</style>

<div x-data="salesReturnsPage()" x-init="init()">

  {{-- Stats Cards --}}
  <div class="sr-stats">
    <div class="sr-stat-card">
      <div class="sr-stat-icon" style="background:#eef2ff">
        <svg fill="none" viewBox="0 0 24 24" stroke="#4f46e5" stroke-width="1.8"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
      </div>
      <div>
        <div class="sr-stat-val" style="color:#4f46e5" x-text="items.length"></div>
        <div class="sr-stat-lbl">Total Returns</div>
      </div>
    </div>
    <div class="sr-stat-card">
      <div class="sr-stat-icon" style="background:#fee2e2">
        <svg fill="none" viewBox="0 0 24 24" stroke="#b91c1c" stroke-width="1.8"><path d="M9 7h6m0 10v-3m-3 3v-3m-3 3v-3m9-8H4a1 1 0 00-1 1v10a1 1 0 001 1h16a1 1 0 001-1V6a1 1 0 00-1-1z"/></svg>
      </div>
      <div>
        <div class="sr-stat-val" style="color:#b91c1c" x-text="fmtCompact(totalAmount)"></div>
        <div class="sr-stat-lbl">Total Credited</div>
      </div>
    </div>
    <div class="sr-stat-card">
      <div class="sr-stat-icon" style="background:#fef9c3">
        <svg fill="none" viewBox="0 0 24 24" stroke="#b45309" stroke-width="1.8"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
      </div>
      <div>
        <div class="sr-stat-val" style="color:#b45309" x-text="thisMonthCount"></div>
        <div class="sr-stat-lbl">This Month</div>
      </div>
    </div>
  </div>

  {{-- Toolbar --}}
  <div class="sr-toolbar">
    <div class="sr-search-wrap">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input x-model.debounce.400ms="search" @input.debounce.400ms="load()" type="text" placeholder="Search CN #, invoice # or customer…">
    </div>
    <div style="margin-left:auto">
      <a href="{{ url('/sales-returns/create') }}"
         style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-radius:10px;padding:8px 18px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;text-decoration:none;box-shadow:0 4px 12px rgba(99,102,241,.35);transition:opacity .15s"
         onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
        <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
        New Return
      </a>
    </div>
  </div>

  {{-- Table --}}
  <div class="sr-table-card">
    <div x-show="loading" class="flex items-center justify-center py-16">
      <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>
    <div x-show="!loading" class="overflow-x-auto">
      <table class="sr-table">
        <thead>
          <tr>
            <th>Credit Note #</th>
            <th>Original Invoice</th>
            <th>Customer</th>
            <th>Date</th>
            <th style="text-align:right">Amount</th>
            <th>Reason</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <template x-for="r in items" :key="r.id">
            <tr>
              <td><span class="sr-num" x-text="r.invoice_number"></span></td>
              <td>
                <a x-show="r.original_invoice" :href="BASE + '/invoices/' + r.original_invoice_id"
                   class="text-indigo-600 hover:underline text-sm" x-text="r.original_invoice?.invoice_number"></a>
                <span x-show="!r.original_invoice" class="text-sm text-gray-400">—</span>
              </td>
              <td class="text-sm font-medium text-gray-800 dark:text-gray-100" x-text="r.customer?.name ?? '—'"></td>
              <td class="text-sm text-gray-600 dark:text-gray-300" x-text="fmtDate(r.invoice_date)"></td>
              <td style="text-align:right" class="text-sm font-semibold tabular-nums text-red-600" x-text="fmtMoney(r.total)"></td>
              <td class="text-sm text-gray-600 dark:text-gray-300 max-w-[220px] truncate" x-text="r.notes ?? '—'"></td>
              <td>
                <button @click="detail = r" class="text-indigo-600 hover:underline text-sm font-medium">View</button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      <div x-show="!loading && items.length === 0" class="sr-empty">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
        <h5>No sales returns yet</h5>
        <p>Create a return when a customer sends goods back</p>
      </div>
    </div>
  </div>

  {{-- ── Detail Modal ── --}}
  <template x-if="detail">
      <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.55)" @click.self="detail = null">
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl z-10 flex flex-col overflow-hidden" style="max-height:90vh">

              <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between flex-shrink-0">
                  <div>
                      <h3 class="text-base font-semibold text-gray-900 dark:text-white" x-text="detail.invoice_number"></h3>
                      <p class="text-xs text-gray-400 mt-0.5">
                          <span x-text="fmtDate(detail.invoice_date)"></span>
                          <span x-show="detail.original_invoice"> · against <span class="font-medium" x-text="detail.original_invoice?.invoice_number"></span></span>
                          <span x-show="detail.customer"> · <span x-text="detail.customer?.name"></span></span>
                      </p>
                  </div>
                  <button @click="detail = null" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                  </button>
              </div>

              <div class="flex-1 overflow-y-auto">
                  <div x-show="detail.notes" class="px-6 pt-4">
                      <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Reason</div>
                      <p class="text-sm text-gray-700 dark:text-gray-300" x-text="detail.notes"></p>
                  </div>
                  <div class="px-6 py-4">
                      <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Returned Items</div>
                      <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                              <thead class="bg-gray-50 dark:bg-gray-700/40">
                                  <tr>
                                      <th class="table-hd">Product</th>
                                      <th class="table-hd text-right">Qty</th>
                                      <th class="table-hd text-right">Unit Price</th>
                                      <th class="table-hd text-right">Total</th>
                                  </tr>
                              </thead>
                              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                  <template x-for="it in (detail.items ?? [])" :key="it.id">
                                      <tr>
                                          <td class="table-td" x-text="it.product_name ?? it.product?.name"></td>
                                          <td class="table-td text-right tabular-nums" x-text="parseFloat(it.quantity)"></td>
                                          <td class="table-td text-right tabular-nums" x-text="fmtMoney(it.unit_price)"></td>
                                          <td class="table-td text-right tabular-nums font-semibold" x-text="fmtMoney(it.total)"></td>
                                      </tr>
                                  </template>
                              </tbody>
                          </table>
                      </div>
                      <div class="flex justify-end mt-3">
                          <div class="text-sm font-bold text-gray-800 dark:text-gray-100">
                              Total credited: <span class="text-red-600" x-text="fmtMoney(detail.total)"></span>
                          </div>
                      </div>
                  </div>
              </div>

              <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end flex-shrink-0">
                  <button @click="detail = null" class="btn-secondary">Close</button>
              </div>
          </div>
      </div>
  </template>
</div>
@endsection

@push('scripts')
<script>
function salesReturnsPage() {
    return {
        items: [],
        loading: true,
        search: '',
        detail: null,
        get totalAmount() { return this.items.reduce((s, r) => s + (parseFloat(r.total) || 0), 0); },
        get thisMonthCount() {
            const now = new Date();
            return this.items.filter(r => {
                const d = new Date(r.invoice_date);
                return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear();
            }).length;
        },
        init() { this.load(); },
        async load() {
            this.loading = true;
            try {
                const r = await apiFetch('/sales-returns' + (this.search ? '?search=' + encodeURIComponent(this.search) : ''));
                if (!r) return;
                const data = await r.json();
                this.items = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load sales returns', 'error');
            } finally {
                this.loading = false;
            }
        },
        fmtCompact(n) { const v = Math.abs(Number(n??0)); if(v>=1e6) return (v/1e6).toFixed(1)+'M'; if(v>=1e3) return (v/1e3).toFixed(1)+'K'; return v.toFixed(0); },
    };
}
</script>
@endpush
