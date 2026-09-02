@extends('layouts.app')
@section('title', 'Suppliers')
@section('page-title', 'Suppliers')
@section('page-desc', 'Manage your supplier accounts')

@section('content')
<style>
.sup-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
.sup-stat-card{background:#fff;border-radius:14px;padding:18px 20px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:14px;transition:box-shadow .2s,transform .2s}
.sup-stat-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08);transform:translateY(-2px)}
.sup-stat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.sup-stat-icon svg{width:22px;height:22px}
.sup-stat-val{font-size:22px;font-weight:800;line-height:1.1;letter-spacing:-.5px}
.sup-stat-lbl{font-size:11.5px;color:#94a3b8;font-weight:500;margin-top:2px}
.sup-stat-sub{font-size:11px;font-weight:600;margin-top:4px;padding:2px 8px;border-radius:20px;display:inline-block}

.sup-toolbar{background:#fff;border-radius:14px;padding:14px 18px;border:1px solid #e2e8f0;margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.sup-search-wrap{position:relative;flex:1;min-width:200px;max-width:340px}
.sup-search-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none}
.sup-search-wrap input{width:100%;border:1px solid #e2e8f0;border-radius:9px;padding:7px 12px 7px 34px;font-size:13px;color:#1e293b;background:#f8fafc;outline:none;transition:border-color .15s,box-shadow .15s}
.sup-search-wrap input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);background:#fff}

.sup-table-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden}
.sup-table{width:100%;border-collapse:separate;border-spacing:0}
.sup-table thead th{padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e2e8f0;white-space:nowrap}
.sup-table thead th:first-child{padding-left:20px}
.sup-table tbody tr{transition:background .1s}
.sup-table tbody tr:hover{background:#f8faff}
.sup-table tbody td{padding:13px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.sup-table tbody td:first-child{padding-left:20px}
.sup-table tbody tr:last-child td{border-bottom:none}

.sup-avatar{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;color:#fff}
.sup-name{font-size:13px;font-weight:600;color:#1e293b}
.sup-chip{font-size:10.5px;font-weight:600;padding:1px 8px;border-radius:20px;display:inline-block}
.sup-balance{font-size:14px;font-weight:800;letter-spacing:-.3px}

.sup-action-btn{width:30px;height:30px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .15s;color:#64748b;text-decoration:none}
.sup-action-btn:hover{background:#f1f5f9;border-color:#c7d2fe;color:#4f46e5}
.sup-action-btn svg{width:14px;height:14px}

.sup-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:64px 24px;text-align:center}
.sup-empty svg{width:56px;height:56px;color:#e2e8f0}
.sup-empty h5{font-size:16px;font-weight:700;color:#475569;margin-top:14px}
.sup-empty p{font-size:13px;color:#94a3b8;margin-top:4px}

.dark .sup-stat-card{background:#1e293b;border-color:#334155}
.dark .sup-stat-lbl{color:#64748b}
.dark .sup-toolbar{background:#1e293b;border-color:#334155}
.dark .sup-search-wrap input{background:#0f172a;border-color:#334155;color:#e2e8f0}
.dark .sup-search-wrap input:focus{background:#1e293b}
.dark .sup-table-card{background:#1e293b;border-color:#334155}
.dark .sup-table thead th{background:#0f172a;border-color:#334155}
.dark .sup-table tbody tr:hover{background:#1e3351}
.dark .sup-table tbody td{border-color:#1e293b}
.dark .sup-name{color:#e2e8f0}
.dark .sup-action-btn{background:#1e293b;border-color:#334155;color:#94a3b8}
.dark .sup-action-btn:hover{background:#253347;border-color:#6366f1}
.dark .sup-empty svg{color:#334155}
.dark .sup-empty h5{color:#94a3b8}
</style>

<div x-data="suppliersPage()" x-init="init()">

  {{-- Stats Cards --}}
  <div class="sup-stats">
    <div class="sup-stat-card">
      <div class="sup-stat-icon" style="background:#eef2ff">
        <svg fill="none" viewBox="0 0 24 24" stroke="#4f46e5" stroke-width="1.8"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
      </div>
      <div>
        <div class="sup-stat-val" style="color:#4f46e5" x-text="items.length"></div>
        <div class="sup-stat-lbl">Total Suppliers</div>
      </div>
    </div>
    <div class="sup-stat-card">
      <div class="sup-stat-icon" style="background:#dcfce7">
        <svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="1.8"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <div class="sup-stat-val" style="color:#16a34a" x-text="activeCount"></div>
        <div class="sup-stat-lbl">Active</div>
      </div>
    </div>
    <div class="sup-stat-card">
      <div class="sup-stat-icon" style="background:#fee2e2">
        <svg fill="none" viewBox="0 0 24 24" stroke="#b91c1c" stroke-width="1.8"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <div class="sup-stat-val" style="color:#b91c1c" x-text="fmtCompact(totalOutstanding)"></div>
        <div class="sup-stat-lbl">Total Outstanding</div>
      </div>
    </div>
    <div class="sup-stat-card">
      <div class="sup-stat-icon" style="background:#eff6ff">
        <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="1.8"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/></svg>
      </div>
      <div>
        <div class="sup-stat-val" style="color:#2563eb" x-text="withBalanceCount"></div>
        <div class="sup-stat-lbl">With Outstanding Balance</div>
      </div>
    </div>
  </div>

  {{-- Toolbar --}}
  <div class="sup-toolbar">
    <div class="sup-search-wrap">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" x-model="search" placeholder="Search name, phone, email…">
    </div>
    <div style="margin-left:auto">
      <a href="{{ url('/suppliers/create') }}"
         style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-radius:10px;padding:8px 18px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;text-decoration:none;box-shadow:0 4px 12px rgba(99,102,241,.35);transition:opacity .15s"
         onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
        <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
        New Supplier
      </a>
    </div>
  </div>

  {{-- Table --}}
  <div class="sup-table-card">
    <div x-show="loading" class="flex items-center justify-center py-16">
      <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>
    <div x-show="!loading" class="overflow-x-auto">
      <table class="sup-table">
        <thead>
          <tr>
            <th>Supplier</th>
            <th>Phone</th>
            <th>Email</th>
            <th>City</th>
            <th>Payment Terms</th>
            <th style="text-align:right">Balance</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <template x-for="s in filtered" :key="s.id">
            <tr>
              <td>
                <div class="flex items-center gap-3">
                  <div class="sup-avatar" :style="'background:'+avatarColor(s.name)" x-text="initials(s.name)"></div>
                  <div class="sup-name" x-text="s.name"></div>
                </div>
              </td>
              <td class="text-sm text-gray-600 dark:text-gray-300" x-text="s.phone ?? '—'"></td>
              <td class="text-sm text-gray-600 dark:text-gray-300" x-text="s.email ?? '—'"></td>
              <td class="text-sm text-gray-600 dark:text-gray-300" x-text="s.city ?? '—'"></td>
              <td>
                <span x-show="s.payment_terms" class="sup-chip" style="background:#eef2ff;color:#4f46e5" x-text="s.payment_terms"></span>
                <span x-show="!s.payment_terms" class="text-gray-400">—</span>
              </td>
              <td style="text-align:right">
                <span class="sup-balance tabular-nums" :style="(s.balance ?? 0) > 0 ? 'color:#dc2626' : 'color:#16a34a'" x-text="fmtMoney(s.balance ?? 0)"></span>
              </td>
              <td>
                <a :href="BASE + '/suppliers/' + s.id" class="sup-action-btn" title="View">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      <div x-show="!loading && filtered.length === 0" class="sup-empty">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
        <h5>No suppliers found</h5>
        <p>Try adjusting your search</p>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function suppliersPage() {
    const COLORS = ['#6366f1','#8b5cf6','#0ea5e9','#10b981','#f59e0b','#ef4444','#ec4899','#14b8a6'];
    return {
        items: [],
        loading: true,
        search: '',
        get filtered() {
            const q = this.search.toLowerCase();
            if (!q) return this.items;
            return this.items.filter(s =>
                (s.name ?? '').toLowerCase().includes(q) ||
                (s.phone ?? '').toLowerCase().includes(q) ||
                (s.email ?? '').toLowerCase().includes(q)
            );
        },
        get activeCount() { return this.items.filter(s => s.is_active !== false).length; },
        get totalOutstanding() { return this.items.reduce((s, r) => s + Math.max(0, parseFloat(r.balance) || 0), 0); },
        get withBalanceCount() { return this.items.filter(s => (parseFloat(s.balance) || 0) > 0).length; },
        async init() {
            try {
                const data = await apiFetch('/suppliers?per_page=500').then(r => r.json());
                this.items = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load suppliers', 'error');
            } finally {
                this.loading = false;
            }
        },
        initials(name) {
            if (!name) return '?';
            return name.split(' ').slice(0,2).map(w => w[0]).join('').toUpperCase();
        },
        avatarColor(name) {
            if (!name) return COLORS[0];
            let h = 0;
            for (let i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) % COLORS.length;
            return COLORS[Math.abs(h)];
        },
        fmtMoney(n) { return Number(n??0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); },
        fmtCompact(n) { const v = Math.abs(Number(n??0)); if(v>=1e6) return (v/1e6).toFixed(1)+'M'; if(v>=1e3) return (v/1e3).toFixed(1)+'K'; return v.toFixed(0); },
    };
}
</script>
@endpush
