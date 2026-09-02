@extends('layouts.app')
@section('title', 'Customers')
@section('page-title', 'Customers')
@section('page-desc', 'Manage your customer accounts')

@section('content')
<style>
/* ── Stats Cards ── */
.cst-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
.cst-stat-card{background:#fff;border-radius:14px;padding:18px 20px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:14px;transition:box-shadow .2s,transform .2s}
.cst-stat-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08);transform:translateY(-2px)}
.cst-stat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.cst-stat-icon svg{width:22px;height:22px}
.cst-stat-val{font-size:22px;font-weight:800;line-height:1.1;letter-spacing:-.5px}
.cst-stat-lbl{font-size:11.5px;color:#94a3b8;font-weight:500;margin-top:2px}
.cst-stat-sub{font-size:11px;font-weight:600;margin-top:4px;padding:2px 8px;border-radius:20px;display:inline-block}

/* ── Toolbar ── */
.cst-toolbar{background:#fff;border-radius:14px;padding:14px 18px;border:1px solid #e2e8f0;margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.cst-search-wrap{position:relative;flex:1;min-width:200px;max-width:340px}
.cst-search-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none}
.cst-search-wrap input{width:100%;border:1px solid #e2e8f0;border-radius:9px;padding:7px 12px 7px 34px;font-size:13px;color:#1e293b;background:#f8fafc;outline:none;transition:border-color .15s,box-shadow .15s}
.cst-search-wrap input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);background:#fff}
.cst-select{border:1px solid #e2e8f0;border-radius:9px;padding:7px 10px;font-size:12.5px;color:#334155;background:#f8fafc;outline:none}
.cst-select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}

/* ── Table Card ── */
.cst-table-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden}
.cst-table{width:100%;border-collapse:separate;border-spacing:0}
.cst-table thead th{padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e2e8f0;white-space:nowrap}
.cst-table thead th:first-child{padding-left:20px}
.cst-table tbody tr{transition:background .1s}
.cst-table tbody tr:hover{background:#f8faff}
.cst-table tbody td{padding:13px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.cst-table tbody td:first-child{padding-left:20px}
.cst-table tbody tr:last-child td{border-bottom:none}

.cst-avatar{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;color:#fff}
.cst-name{font-size:13px;font-weight:600;color:#1e293b}
.cst-sub{font-size:11px;color:#94a3b8;margin-top:1px}
.cst-chip{font-size:10.5px;font-weight:600;padding:1px 8px;border-radius:20px;display:inline-block}
.cst-balance{font-size:14px;font-weight:800;letter-spacing:-.3px}

.cst-action-btn{width:30px;height:30px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .15s;color:#64748b;text-decoration:none}
.cst-action-btn:hover{background:#f1f5f9;border-color:#c7d2fe;color:#4f46e5}
.cst-action-btn svg{width:14px;height:14px}

.cst-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:64px 24px;text-align:center}
.cst-empty svg{width:56px;height:56px;color:#e2e8f0}
.cst-empty h5{font-size:16px;font-weight:700;color:#475569;margin-top:14px}
.cst-empty p{font-size:13px;color:#94a3b8;margin-top:4px}

/* ── Dark Mode ── */
.dark .cst-stat-card{background:#1e293b;border-color:#334155}
.dark .cst-stat-lbl{color:#64748b}
.dark .cst-toolbar{background:#1e293b;border-color:#334155}
.dark .cst-search-wrap input{background:#0f172a;border-color:#334155;color:#e2e8f0}
.dark .cst-search-wrap input:focus{background:#1e293b}
.dark .cst-select{background:#0f172a;border-color:#334155;color:#cbd5e1}
.dark .cst-table-card{background:#1e293b;border-color:#334155}
.dark .cst-table thead th{background:#0f172a;border-color:#334155}
.dark .cst-table tbody tr:hover{background:#1e3351}
.dark .cst-table tbody td{border-color:#1e293b}
.dark .cst-name{color:#e2e8f0}
.dark .cst-action-btn{background:#1e293b;border-color:#334155;color:#94a3b8}
.dark .cst-action-btn:hover{background:#253347;border-color:#6366f1}
.dark .cst-empty svg{color:#334155}
.dark .cst-empty h5{color:#94a3b8}
</style>

<div x-data="customersPage()" x-init="init()">

  {{-- Stats Cards --}}
  <div class="cst-stats">
    <div class="cst-stat-card">
      <div class="cst-stat-icon" style="background:#eef2ff">
        <svg fill="none" viewBox="0 0 24 24" stroke="#4f46e5" stroke-width="1.8"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
      </div>
      <div>
        <div class="cst-stat-val" style="color:#4f46e5" x-text="items.length"></div>
        <div class="cst-stat-lbl">Total Customers</div>
      </div>
    </div>
    <div class="cst-stat-card">
      <div class="cst-stat-icon" style="background:#dcfce7">
        <svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="1.8"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <div class="cst-stat-val" style="color:#16a34a" x-text="activeCount"></div>
        <div class="cst-stat-lbl">Active</div>
        <div class="cst-stat-sub" style="background:#dcfce7;color:#16a34a" x-text="walkInCount + ' walk-in'"></div>
      </div>
    </div>
    <div class="cst-stat-card">
      <div class="cst-stat-icon" style="background:#eff6ff">
        <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="1.8"><path d="M9 7h6m0 10v-3m-3 3v-3m-3 3v-3m9-8H4a1 1 0 00-1 1v10a1 1 0 001 1h16a1 1 0 001-1V6a1 1 0 00-1-1z"/></svg>
      </div>
      <div>
        <div class="cst-stat-val" style="color:#2563eb" x-text="fmtCompact(totalCreditLimit)"></div>
        <div class="cst-stat-lbl">Total Credit Limit</div>
      </div>
    </div>
    <div class="cst-stat-card">
      <div class="cst-stat-icon" style="background:#fee2e2">
        <svg fill="none" viewBox="0 0 24 24" stroke="#b91c1c" stroke-width="1.8"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <div class="cst-stat-val" style="color:#b91c1c" x-text="fmtCompact(totalOutstanding)"></div>
        <div class="cst-stat-lbl">Total Outstanding</div>
      </div>
    </div>
  </div>

  {{-- Toolbar --}}
  <div class="cst-toolbar">
    <div class="cst-search-wrap">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" x-model="search" placeholder="Search name, phone, city, district…">
    </div>
    <select x-model="filterDistrict" class="cst-select">
      <option value="">All Districts</option>
      <template x-for="d in districts" :key="d"><option :value="d" x-text="d"></option></template>
    </select>
    <div style="margin-left:auto">
      <a href="{{ url('/customers/create') }}"
         style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-radius:10px;padding:8px 18px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;text-decoration:none;box-shadow:0 4px 12px rgba(99,102,241,.35);transition:opacity .15s"
         onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
        <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
        New Customer
      </a>
    </div>
  </div>

  {{-- Table --}}
  <div class="cst-table-card">
    <div x-show="loading" class="flex items-center justify-center py-16">
      <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>
    <div x-show="!loading" class="overflow-x-auto">
      <table class="cst-table">
        <thead>
          <tr>
            <th>Customer</th>
            <th x-show="isAllBranches">Branch</th>
            <th>Phone</th>
            <th>City</th>
            <th>District</th>
            <th style="text-align:right">Credit Limit</th>
            <th style="text-align:right">Balance</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <template x-for="c in filtered" :key="c.id">
            <tr>
              <td>
                <div class="flex items-center gap-3">
                  <div class="cst-avatar" :style="'background:'+avatarColor(c.name)" x-text="initials(c.name)"></div>
                  <div>
                    <div class="cst-name" x-text="c.name"></div>
                    <div class="cst-sub" x-show="c.is_walk_in" style="color:#b45309">Walk-in</div>
                  </div>
                </div>
              </td>
              <td x-show="isAllBranches">
                <span class="cst-chip" style="background:#eef2ff;color:#4f46e5" x-text="c.branch?.name ?? '—'"></span>
              </td>
              <td class="text-sm text-gray-600 dark:text-gray-300" x-text="c.phone ?? '—'"></td>
              <td class="text-sm text-gray-600 dark:text-gray-300" x-text="c.city ?? '—'"></td>
              <td>
                <span x-show="c.district" class="cst-chip" style="background:#eff6ff;color:#2563eb" x-text="c.district"></span>
                <span x-show="!c.district" class="text-gray-400">—</span>
              </td>
              <td class="text-sm text-gray-600 dark:text-gray-300 tabular-nums" style="text-align:right" x-text="fmtMoney(c.credit_limit ?? 0)"></td>
              <td style="text-align:right">
                <span class="cst-balance tabular-nums" :style="(c.balance ?? 0) > 0 ? 'color:#dc2626' : 'color:#16a34a'" x-text="fmtMoney(c.balance ?? 0)"></span>
              </td>
              <td>
                <a :href="BASE + '/customers/' + c.id" class="cst-action-btn" title="View">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      <div x-show="!loading && filtered.length === 0" class="cst-empty">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        <h5>No customers found</h5>
        <p>Try adjusting your search or filters</p>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function customersPage() {
    const COLORS = ['#6366f1','#8b5cf6','#0ea5e9','#10b981','#f59e0b','#ef4444','#ec4899','#14b8a6'];
    return {
        items: [],
        loading: true,
        search: '',
        filterDistrict: '',
        isAllBranches: localStorage.getItem('medri_branch') === 'all' || !localStorage.getItem('medri_branch'),
        districts: ['Ampara','Anuradhapura','Badulla','Batticaloa','Colombo','Galle','Gampaha','Hambantota','Jaffna','Kalutara','Kandy','Kegalle','Kilinochchi','Kurunegala','Mannar','Matale','Matara','Monaragala','Mullaitivu','Nuwara Eliya','Polonnaruwa','Puttalam','Ratnapura','Trincomalee','Vavuniya'],
        get filtered() {
            const q = this.search.toLowerCase();
            return this.items.filter(c => {
                const matchSearch = !q ||
                    (c.name ?? '').toLowerCase().includes(q) ||
                    (c.phone ?? '').toLowerCase().includes(q) ||
                    (c.email ?? '').toLowerCase().includes(q) ||
                    (c.city ?? '').toLowerCase().includes(q) ||
                    (c.district ?? '').toLowerCase().includes(q);
                const matchDistrict = !this.filterDistrict || c.district === this.filterDistrict;
                return matchSearch && matchDistrict;
            });
        },
        get activeCount() { return this.items.filter(c => c.is_active !== false).length; },
        get walkInCount() { return this.items.filter(c => c.is_walk_in).length; },
        get totalCreditLimit() { return this.items.reduce((s, c) => s + (parseFloat(c.credit_limit) || 0), 0); },
        get totalOutstanding() { return this.items.reduce((s, c) => s + Math.max(0, parseFloat(c.balance) || 0), 0); },
        async init() {
            try {
                const data = await apiFetch('/customers?per_page=500').then(r => r.json());
                this.items = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load customers', 'error');
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
