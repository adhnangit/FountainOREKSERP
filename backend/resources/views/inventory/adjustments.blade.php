@extends('layouts.app')
@section('title', 'Stock Adjustments')
@section('page-title', 'Stock Adjustments')
@section('page-desc', 'Record manual stock adjustments')

@section('content')
<style>
.adj-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
.adj-stat-card{background:#fff;border-radius:14px;padding:18px 20px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:14px;transition:box-shadow .2s,transform .2s;cursor:pointer}
.adj-stat-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08);transform:translateY(-2px)}
.adj-stat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.adj-stat-icon svg{width:22px;height:22px}
.adj-stat-val{font-size:22px;font-weight:800;line-height:1.1;letter-spacing:-.5px}
.adj-stat-lbl{font-size:11.5px;color:#94a3b8;font-weight:500;margin-top:2px}

.adj-toolbar{background:#fff;border-radius:14px;padding:14px 18px;border:1px solid #e2e8f0;margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.adj-search-wrap{position:relative;flex:1;min-width:200px;max-width:340px}
.adj-search-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none}
.adj-search-wrap input{width:100%;border:1px solid #e2e8f0;border-radius:9px;padding:7px 12px 7px 34px;font-size:13px;color:#1e293b;background:#f8fafc;outline:none;transition:border-color .15s,box-shadow .15s}
.adj-search-wrap input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);background:#fff}

.adj-table-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden}
.adj-table{width:100%;border-collapse:separate;border-spacing:0}
.adj-table thead th{padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e2e8f0;white-space:nowrap}
.adj-table thead th:first-child{padding-left:20px}
.adj-table tbody tr{transition:background .1s}
.adj-table tbody tr:hover{background:#f8faff}
.adj-table tbody td{padding:13px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.adj-table tbody td:first-child{padding-left:20px}
.adj-table tbody tr:last-child td{border-bottom:none}
.adj-ref{font-size:13px;font-weight:700;color:#4f46e5}

.adj-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:64px 24px;text-align:center}
.adj-empty svg{width:56px;height:56px;color:#e2e8f0}
.adj-empty h5{font-size:16px;font-weight:700;color:#475569;margin-top:14px}
.adj-empty p{font-size:13px;color:#94a3b8;margin-top:4px}

.dark .adj-stat-card{background:#1e293b;border-color:#334155}
.dark .adj-stat-lbl{color:#64748b}
.dark .adj-toolbar{background:#1e293b;border-color:#334155}
.dark .adj-search-wrap input{background:#0f172a;border-color:#334155;color:#e2e8f0}
.dark .adj-search-wrap input:focus{background:#1e293b}
.dark .adj-table-card{background:#1e293b;border-color:#334155}
.dark .adj-table thead th{background:#0f172a;border-color:#334155}
.dark .adj-table tbody tr:hover{background:#1e3351}
.dark .adj-table tbody td{border-color:#1e293b}
.dark .adj-empty svg{color:#334155}
.dark .adj-empty h5{color:#94a3b8}
</style>

<div x-data="adjustmentsPage()" x-init="init()">

  {{-- Stats Cards --}}
  <div class="adj-stats">
    <div class="adj-stat-card" @click="showPendingOnly=false; search=''">
      <div class="adj-stat-icon" style="background:#eef2ff">
        <svg fill="none" viewBox="0 0 24 24" stroke="#4f46e5" stroke-width="1.8"><path d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
      </div>
      <div>
        <div class="adj-stat-val" style="color:#4f46e5" x-text="items.length"></div>
        <div class="adj-stat-lbl">Total Adjustments</div>
      </div>
    </div>
    <div class="adj-stat-card" @click="showPendingOnly=true">
      <div class="adj-stat-icon" style="background:#fef9c3">
        <svg fill="none" viewBox="0 0 24 24" stroke="#b45309" stroke-width="1.8"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <div class="adj-stat-val" style="color:#b45309" x-text="pendingCount"></div>
        <div class="adj-stat-lbl">Draft / Pending</div>
      </div>
    </div>
    <div class="adj-stat-card">
      <div class="adj-stat-icon" style="background:#dcfce7">
        <svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="1.8"><path d="M7 16V4m0 0L3 8m4-4l4 4"/></svg>
      </div>
      <div>
        <div class="adj-stat-val" style="color:#16a34a" x-text="approvedCount"></div>
        <div class="adj-stat-lbl">Approved</div>
      </div>
    </div>
    <div class="adj-stat-card">
      <div class="adj-stat-icon" style="background:#eff6ff">
        <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="1.8"><path d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
      </div>
      <div>
        <div class="adj-stat-val" :style="totalNetDiff >= 0 ? 'color:#16a34a' : 'color:#dc2626'" x-text="(totalNetDiff > 0 ? '+' : '') + totalNetDiff"></div>
        <div class="adj-stat-lbl">Net Qty Change</div>
      </div>
    </div>
  </div>

  <!-- Pending-approval banner — a draft has NOT touched stock yet -->
  <div x-show="!loading && pendingCount > 0"
       class="mb-4 flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-amber-300 bg-amber-50 dark:bg-amber-900/10 dark:border-amber-800">
      <div class="flex items-center gap-2.5">
          <svg class="w-4.5 h-4.5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
          <span class="text-sm text-amber-800 dark:text-amber-300">
              <strong x-text="pendingCount"></strong>
              <span x-text="pendingCount === 1 ? 'adjustment is' : 'adjustments are'"></span>
              still in draft — stock hasn't changed for <span x-text="pendingCount === 1 ? 'it' : 'them'"></span> yet.
          </span>
      </div>
      <button @click="showPendingOnly = true; search = ''" x-show="!showPendingOnly"
              class="text-xs font-semibold text-amber-700 dark:text-amber-400 hover:underline flex-shrink-0">Show pending</button>
  </div>

  {{-- Toolbar --}}
  <div class="adj-toolbar">
    <div class="adj-search-wrap">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" x-model="search" placeholder="Search ref# or product…">
    </div>
    <button @click="showPendingOnly = !showPendingOnly"
            :class="showPendingOnly ? 'bg-amber-100 border-amber-400 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' : 'border-gray-200 dark:border-gray-600 text-gray-500'"
            class="text-xs font-semibold px-3 py-2 rounded-lg border transition-colors">
        Pending only
    </button>
    <div style="margin-left:auto">
      <a href="{{ url('/inventory/adjustments/create') }}"
         style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-radius:10px;padding:8px 18px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;text-decoration:none;box-shadow:0 4px 12px rgba(99,102,241,.35);transition:opacity .15s"
         onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
        <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
        New Adjustment
      </a>
    </div>
  </div>

  {{-- Table --}}
  <div class="adj-table-card">
    <div x-show="loading" class="flex items-center justify-center py-16">
      <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>
    <div x-show="!loading" class="overflow-x-auto">
      <table class="adj-table">
        <thead>
          <tr>
            <th>Ref #</th>
            <th>Products</th>
            <th style="text-align:right">Qty Change</th>
            <th>Reason</th>
            <th>Date</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <template x-for="a in filtered" :key="a.id">
            <tr>
              <td><span class="adj-ref" x-text="a.adjustment_number ?? ('#ADJ-' + a.id)"></span></td>
              <td class="text-sm text-gray-700 dark:text-gray-200" x-text="productSummary(a)"></td>
              <td style="text-align:right" class="font-semibold tabular-nums"
                  :class="netDiff(a) >= 0 ? 'text-green-600' : 'text-red-600'"
                  x-text="(netDiff(a) > 0 ? '+' : '') + netDiff(a)"></td>
              <td class="text-sm text-gray-600 dark:text-gray-300" x-text="a.reason ?? '—'"></td>
              <td class="text-sm text-gray-600 dark:text-gray-300" x-text="fmtDate(a.adjustment_date ?? a.created_at)"></td>
              <td>
                <span :class="statusBadge(a.status)" x-text="a.status ?? '—'"></span>
              </td>
              <td>
                <div class="flex items-center gap-3">
                  <button @click="detail = a" class="text-indigo-600 hover:underline text-sm font-medium">View</button>
                  <a x-show="a.status === 'draft' && hasPerm('inventory.adjustments.create')"
                     :href="BASE + '/inventory/adjustments/create?edit=' + a.id"
                     class="text-amber-600 hover:underline text-sm font-medium">Edit</a>
                  <button x-show="a.status === 'draft' && hasPerm('inventory.adjustments.approve')"
                          @click="approve(a)"
                          class="text-green-600 hover:underline text-sm font-medium">Approve</button>
                  <button x-show="a.status === 'draft' && hasPerm('inventory.adjustments.create')"
                          @click="deleteDraft(a)"
                          class="text-red-500 hover:underline text-sm font-medium">Delete</button>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      <div x-show="!loading && filtered.length === 0" class="adj-empty">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        <h5>No adjustments found</h5>
        <p>Try adjusting your search or filters</p>
      </div>
    </div>
  </div>

  {{-- ── Detail Modal ── --}}
  <template x-if="detail">
      <div class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.55)" @click.self="detail = null">
          <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-2xl z-10 flex flex-col overflow-hidden" style="max-height:90vh">

              <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between flex-shrink-0">
                  <div>
                      <h3 class="text-base font-semibold text-gray-900 dark:text-white" x-text="detail.adjustment_number"></h3>
                      <p class="text-xs text-gray-400 mt-0.5">
                          <span x-text="fmtDate(detail.adjustment_date ?? detail.created_at)"></span>
                          <span x-show="detail.branch"> · <span x-text="detail.branch?.name"></span></span>
                          <span x-show="detail.created_by?.name"> · by <span x-text="detail.created_by?.name ?? ''"></span></span>
                      </p>
                  </div>
                  <div class="flex items-center gap-3">
                      <span :class="statusBadge(detail.status)" x-text="detail.status"></span>
                      <button @click="detail = null" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                      </button>
                  </div>
              </div>

              <div class="flex-1 overflow-y-auto">
                  <div x-show="detail.reason" class="px-6 pt-4">
                      <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Reason</div>
                      <p class="text-sm text-gray-700 dark:text-gray-300" x-text="detail.reason"></p>
                  </div>
                  <div class="px-6 py-4">
                      <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Items</div>
                      <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-700">
                          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                              <thead class="bg-gray-50 dark:bg-gray-700/40">
                                  <tr>
                                      <th class="table-hd">Product</th>
                                      <th class="table-hd">Batch</th>
                                      <th class="table-hd text-right">System Qty</th>
                                      <th class="table-hd text-right">Physical Qty</th>
                                      <th class="table-hd text-right">Difference</th>
                                  </tr>
                              </thead>
                              <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                  <template x-for="it in (detail.items ?? [])" :key="it.id">
                                      <tr>
                                          <td class="table-td" x-text="it.product?.name ?? ('#' + it.product_id)"></td>
                                          <td class="table-td text-gray-500" x-text="it.batch?.batch_code ?? 'All batches'"></td>
                                          <td class="table-td text-right tabular-nums" x-text="parseFloat(it.system_quantity)"></td>
                                          <td class="table-td text-right tabular-nums" x-text="parseFloat(it.physical_quantity)"></td>
                                          <td class="table-td text-right font-semibold tabular-nums"
                                              :class="it.difference >= 0 ? 'text-green-600' : 'text-red-600'"
                                              x-text="(it.difference > 0 ? '+' : '') + parseFloat(it.difference)"></td>
                                      </tr>
                                  </template>
                              </tbody>
                          </table>
                      </div>
                      <p x-show="detail.status === 'draft'" class="text-xs text-amber-600 bg-amber-50 dark:bg-amber-900/20 rounded-lg p-3 mt-3">
                          Draft — stock has not been updated yet. Approving will apply these differences to inventory.
                      </p>
                  </div>
              </div>

              <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-3 flex-shrink-0">
                  <button @click="detail = null" class="btn-secondary">Close</button>
                  <a x-show="detail.status === 'draft' && hasPerm('inventory.adjustments.create')"
                     :href="BASE + '/inventory/adjustments/create?edit=' + detail.id"
                     class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold border border-amber-300 text-amber-700 hover:bg-amber-50 transition-colors">
                      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                      Edit
                  </a>
                  <button x-show="detail.status === 'draft' && hasPerm('inventory.adjustments.approve')"
                          @click="approve(detail)" :disabled="approving"
                          class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold bg-green-600 hover:bg-green-700 text-white transition-colors disabled:opacity-50">
                      <svg x-show="!approving" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
                      <svg x-show="approving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                      <span x-text="approving ? 'Approving…' : 'Approve & Update Stock'"></span>
                  </button>
              </div>
          </div>
      </div>
  </template>
</div>
@endsection

@push('scripts')
<script>
function adjustmentsPage() {
    return {
        items: [],
        loading: true,
        search: '',
        showPendingOnly: false,
        detail: null,
        approving: false,
        get pendingCount() {
            return this.items.filter(a => a.status === 'draft').length;
        },
        get approvedCount() {
            return this.items.filter(a => a.status === 'approved').length;
        },
        get totalNetDiff() {
            return this.items.reduce((s, a) => s + this.netDiff(a), 0);
        },
        get filtered() {
            const q = this.search.toLowerCase();
            let list = this.items;
            if (this.showPendingOnly) list = list.filter(a => a.status === 'draft');
            if (!q) return list;
            return list.filter(a =>
                (a.adjustment_number ?? '').toLowerCase().includes(q) ||
                (a.items ?? []).some(it => (it.product?.name ?? '').toLowerCase().includes(q))
            );
        },
        init() {
            this.load();
        },
        async load() {
            this.loading = true;
            try {
                const r = await apiFetch('/adjustments');
                if (!r) return;
                const data = await r.json();
                this.items = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load adjustments', 'error');
            } finally {
                this.loading = false;
            }
        },
        productSummary(a) {
            const its = a.items ?? [];
            if (!its.length) return '—';
            const first = its[0].product?.name ?? ('#' + its[0].product_id);
            return its.length === 1 ? first : first + ' +' + (its.length - 1) + ' more';
        },
        netDiff(a) {
            return (a.items ?? []).reduce((s, it) => s + parseFloat(it.difference ?? 0), 0);
        },
        async deleteDraft(a) {
            if (!confirm('Delete draft ' + (a.adjustment_number ?? '') + '? This cannot be undone.')) return;
            try {
                await apiFetch(`/adjustments/${a.id}`, { method: 'DELETE' });
                toast('Draft adjustment deleted');
                this.detail = null;
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to delete adjustment', 'error');
            }
        },
        async approve(a) {
            if (!confirm('Approve ' + (a.adjustment_number ?? 'this adjustment') + ' and update stock?')) return;
            this.approving = true;
            try {
                await apiFetch(`/adjustments/${a.id}/approve`, { method: 'POST' });
                toast('Adjustment approved — stock updated');
                this.detail = null;
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to approve adjustment', 'error');
            } finally {
                this.approving = false;
            }
        },
        statusBadge(status) {
            const map = { approved: 'badge-success', pending: 'badge-warning', rejected: 'badge-danger', draft: 'badge-gray' };
            return 'badge ' + (map[status] ?? 'badge-gray');
        }
    };
}
</script>
@endpush
