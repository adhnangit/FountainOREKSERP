@extends('layouts.app')
@section('title', 'Stock Transfers')
@section('page-title', 'Stock Transfers')
@section('page-desc', 'Manage inter-branch stock transfers')

@section('content')
<style>
.trf-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
.trf-stat-card{background:#fff;border-radius:14px;padding:18px 20px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:14px;transition:box-shadow .2s,transform .2s;cursor:pointer}
.trf-stat-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08);transform:translateY(-2px)}
.trf-stat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.trf-stat-icon svg{width:22px;height:22px}
.trf-stat-val{font-size:22px;font-weight:800;line-height:1.1;letter-spacing:-.5px}
.trf-stat-lbl{font-size:11.5px;color:#94a3b8;font-weight:500;margin-top:2px}

.trf-toolbar{background:#fff;border-radius:14px;padding:14px 18px;border:1px solid #e2e8f0;margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.trf-search-wrap{position:relative;flex:1;min-width:200px;max-width:340px}
.trf-search-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none}
.trf-search-wrap input{width:100%;border:1px solid #e2e8f0;border-radius:9px;padding:7px 12px 7px 34px;font-size:13px;color:#1e293b;background:#f8fafc;outline:none;transition:border-color .15s,box-shadow .15s}
.trf-search-wrap input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);background:#fff}

.trf-table-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden}
.trf-table{width:100%;border-collapse:separate;border-spacing:0}
.trf-table thead th{padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e2e8f0;white-space:nowrap}
.trf-table thead th:first-child{padding-left:20px}
.trf-table tbody tr{transition:background .1s}
.trf-table tbody tr:hover{background:#f8faff}
.trf-table tbody td{padding:13px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.trf-table tbody td:first-child{padding-left:20px}
.trf-table tbody tr:last-child td{border-bottom:none}

.trf-icon{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#eef2ff;color:#4f46e5}
.trf-ref{font-size:13px;font-weight:700;color:#4f46e5}
.trf-route{font-size:13px;color:#1e293b;font-weight:500}

.trf-action-btn{width:30px;height:30px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .15s;color:#64748b;text-decoration:none}
.trf-action-btn:hover{background:#f1f5f9;border-color:#c7d2fe;color:#4f46e5}
.trf-action-btn svg{width:14px;height:14px}

.trf-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:64px 24px;text-align:center}
.trf-empty svg{width:56px;height:56px;color:#e2e8f0}
.trf-empty h5{font-size:16px;font-weight:700;color:#475569;margin-top:14px}
.trf-empty p{font-size:13px;color:#94a3b8;margin-top:4px}

.dark .trf-stat-card{background:#1e293b;border-color:#334155}
.dark .trf-stat-lbl{color:#64748b}
.dark .trf-toolbar{background:#1e293b;border-color:#334155}
.dark .trf-search-wrap input{background:#0f172a;border-color:#334155;color:#e2e8f0}
.dark .trf-search-wrap input:focus{background:#1e293b}
.dark .trf-table-card{background:#1e293b;border-color:#334155}
.dark .trf-table thead th{background:#0f172a;border-color:#334155}
.dark .trf-table tbody tr:hover{background:#1e3351}
.dark .trf-table tbody td{border-color:#1e293b}
.dark .trf-route{color:#e2e8f0}
.dark .trf-icon{background:#1e3a5f;color:#93c5fd}
.dark .trf-action-btn{background:#1e293b;border-color:#334155;color:#94a3b8}
.dark .trf-action-btn:hover{background:#253347;border-color:#6366f1}
.dark .trf-empty svg{color:#334155}
.dark .trf-empty h5{color:#94a3b8}
</style>

<div x-data="transfersPage()" x-init="init()">

  {{-- Stats Cards --}}
  <div class="trf-stats">
    <div class="trf-stat-card" @click="statusFilter=''">
      <div class="trf-stat-icon" style="background:#eef2ff">
        <svg fill="none" viewBox="0 0 24 24" stroke="#4f46e5" stroke-width="1.8"><path d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4"/></svg>
      </div>
      <div>
        <div class="trf-stat-val" style="color:#4f46e5" x-text="items.length"></div>
        <div class="trf-stat-lbl">Total Transfers</div>
      </div>
    </div>
    <div class="trf-stat-card" @click="statusFilter='pending'">
      <div class="trf-stat-icon" style="background:#fef9c3">
        <svg fill="none" viewBox="0 0 24 24" stroke="#b45309" stroke-width="1.8"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <div class="trf-stat-val" style="color:#b45309" x-text="countByStatus('pending')"></div>
        <div class="trf-stat-lbl">Pending</div>
      </div>
    </div>
    <div class="trf-stat-card" @click="statusFilter='approved'">
      <div class="trf-stat-icon" style="background:#eff6ff">
        <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="1.8"><path d="M5 13l4 4L19 7"/></svg>
      </div>
      <div>
        <div class="trf-stat-val" style="color:#2563eb" x-text="countByStatus('approved')"></div>
        <div class="trf-stat-lbl">Approved</div>
      </div>
    </div>
    <div class="trf-stat-card" @click="statusFilter='completed'">
      <div class="trf-stat-icon" style="background:#dcfce7">
        <svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="1.8"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <div class="trf-stat-val" style="color:#16a34a" x-text="countByStatus('completed')"></div>
        <div class="trf-stat-lbl">Completed</div>
      </div>
    </div>
  </div>

  {{-- Toolbar --}}
  <div class="trf-toolbar">
    <div class="trf-search-wrap">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" x-model="search" placeholder="Search ref# or branch…">
    </div>
    <div style="margin-left:auto">
      <a href="{{ url('/inventory/transfers/create') }}"
         style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-radius:10px;padding:8px 18px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;text-decoration:none;box-shadow:0 4px 12px rgba(99,102,241,.35);transition:opacity .15s"
         onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
        <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
        New Transfer
      </a>
    </div>
  </div>

  {{-- Table --}}
  <div class="trf-table-card">
    <div x-show="loading" class="flex items-center justify-center py-16">
      <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>
    <div x-show="!loading" class="overflow-x-auto">
      <table class="trf-table">
        <thead>
          <tr>
            <th>Ref #</th>
            <th>Route</th>
            <th style="text-align:right">Items</th>
            <th>Date</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <template x-for="t in filtered" :key="t.id">
            <tr>
              <td><span class="trf-ref" x-text="t.reference ?? t.transfer_number ?? ('#TRF-' + t.id)"></span></td>
              <td>
                <div class="flex items-center gap-2 trf-route">
                  <span x-text="t.from_branch?.name ?? '—'"></span>
                  <svg style="width:13px;height:13px;flex-shrink:0" fill="none" viewBox="0 0 24 24" stroke="#94a3b8" stroke-width="2.5"><path d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                  <span x-text="t.to_branch?.name ?? '—'"></span>
                </div>
              </td>
              <td style="text-align:right" class="text-sm text-gray-600 dark:text-gray-300 tabular-nums" x-text="t.items_count ?? t.items?.length ?? '—'"></td>
              <td class="text-sm text-gray-600 dark:text-gray-300" x-text="fmtDate(t.transfer_date ?? t.created_at)"></td>
              <td>
                <span :class="statusBadge(t.status)" x-text="t.status ?? 'pending'"></span>
              </td>
              <td>
                <a :href="BASE + '/inventory/transfers/' + t.id" class="trf-action-btn" title="View">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      <div x-show="!loading && filtered.length === 0" class="trf-empty">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4"/></svg>
        <h5>No transfers found</h5>
        <p>Try adjusting your search or filters</p>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function transfersPage() {
    return {
        items: [],
        loading: true,
        search: '',
        statusFilter: '',
        get filtered() {
            const q = this.search.toLowerCase();
            return this.items.filter(t => {
                const matchSearch = !q ||
                    (t.reference ?? t.transfer_number ?? '').toLowerCase().includes(q) ||
                    (t.from_branch?.name ?? '').toLowerCase().includes(q) ||
                    (t.to_branch?.name ?? '').toLowerCase().includes(q);
                const matchStatus = !this.statusFilter || (t.status ?? 'pending') === this.statusFilter;
                return matchSearch && matchStatus;
            });
        },
        countByStatus(status) { return this.items.filter(t => (t.status ?? 'pending') === status).length; },
        async init() {
            window.addEventListener('branch-switched', () => this.init());
            try {
                const r = await apiFetch('/transfers');
                if (!r) return;
                const data = await r.json();
                this.items = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load transfers', 'error');
            } finally {
                this.loading = false;
            }
        },
        statusBadge(status) {
            const map = {
                pending: 'badge-warning',
                approved: 'badge-primary',
                completed: 'badge-success',
                cancelled: 'badge-danger',
                draft: 'badge-gray',
            };
            return 'badge ' + (map[status] ?? 'badge-gray');
        },
        fmtDate(d) { if (!d) return '—'; return new Date(d).toLocaleDateString('en-GB',{day:'2-digit',month:'short',year:'numeric'}); },
    };
}
</script>
@endpush
