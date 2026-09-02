<?php $__env->startSection('title', 'Products'); ?>
<?php $__env->startSection('page-title', 'Products'); ?>
<?php $__env->startSection('page-desc', 'Manage your product catalogue'); ?>

<?php $__env->startSection('content'); ?>
<style>
.prd-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px}
.prd-stat-card{background:#fff;border-radius:14px;padding:18px 20px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:14px;transition:box-shadow .2s,transform .2s}
.prd-stat-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08);transform:translateY(-2px)}
.prd-stat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.prd-stat-icon svg{width:22px;height:22px}
.prd-stat-val{font-size:22px;font-weight:800;line-height:1.1;letter-spacing:-.5px}
.prd-stat-lbl{font-size:11.5px;color:#94a3b8;font-weight:500;margin-top:2px}

.prd-toolbar{background:#fff;border-radius:14px;padding:14px 18px;border:1px solid #e2e8f0;margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.prd-search-wrap{position:relative;flex:1;min-width:200px;max-width:340px}
.prd-search-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none}
.prd-search-wrap input{width:100%;border:1px solid #e2e8f0;border-radius:9px;padding:7px 12px 7px 34px;font-size:13px;color:#1e293b;background:#f8fafc;outline:none;transition:border-color .15s,box-shadow .15s}
.prd-search-wrap input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);background:#fff}
.prd-select{border:1px solid #e2e8f0;border-radius:9px;padding:7px 10px;font-size:12.5px;color:#334155;background:#f8fafc;outline:none}
.prd-select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}

.prd-table-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden}
.prd-table{width:100%;border-collapse:separate;border-spacing:0}
.prd-table thead th{padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e2e8f0;white-space:nowrap}
.prd-table thead th:first-child{padding-left:20px}
.prd-table tbody tr{transition:background .1s}
.prd-table tbody tr:hover{background:#f8faff}
.prd-table tbody td{padding:13px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.prd-table tbody td:first-child{padding-left:20px}
.prd-table tbody tr:last-child td{border-bottom:none}

.prd-icon{width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#eef2ff;color:#4f46e5}
.prd-name{font-size:13px;font-weight:600;color:#1e293b}
.prd-sub{font-size:11px;color:#94a3b8;margin-top:1px;font-family:monospace}

.prd-action-btn{width:30px;height:30px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .15s;color:#64748b;text-decoration:none}
.prd-action-btn:hover{background:#f1f5f9;border-color:#c7d2fe;color:#4f46e5}
.prd-action-btn svg{width:14px;height:14px}

.prd-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:64px 24px;text-align:center}
.prd-empty svg{width:56px;height:56px;color:#e2e8f0}
.prd-empty h5{font-size:16px;font-weight:700;color:#475569;margin-top:14px}
.prd-empty p{font-size:13px;color:#94a3b8;margin-top:4px}

.dark .prd-stat-card{background:#1e293b;border-color:#334155}
.dark .prd-stat-lbl{color:#64748b}
.dark .prd-toolbar{background:#1e293b;border-color:#334155}
.dark .prd-search-wrap input{background:#0f172a;border-color:#334155;color:#e2e8f0}
.dark .prd-search-wrap input:focus{background:#1e293b}
.dark .prd-select{background:#0f172a;border-color:#334155;color:#cbd5e1}
.dark .prd-table-card{background:#1e293b;border-color:#334155}
.dark .prd-table thead th{background:#0f172a;border-color:#334155}
.dark .prd-table tbody tr:hover{background:#1e3351}
.dark .prd-table tbody td{border-color:#1e293b}
.dark .prd-name{color:#e2e8f0}
.dark .prd-icon{background:#1e3a5f;color:#93c5fd}
.dark .prd-action-btn{background:#1e293b;border-color:#334155;color:#94a3b8}
.dark .prd-action-btn:hover{background:#253347;border-color:#6366f1}
.dark .prd-empty svg{color:#334155}
.dark .prd-empty h5{color:#94a3b8}
</style>

<div x-data="productsPage()" x-init="init()">

  
  <div class="prd-stats">
    <div class="prd-stat-card">
      <div class="prd-stat-icon" style="background:#eef2ff">
        <svg fill="none" viewBox="0 0 24 24" stroke="#4f46e5" stroke-width="1.8"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
      </div>
      <div>
        <div class="prd-stat-val" style="color:#4f46e5" x-text="items.length"></div>
        <div class="prd-stat-lbl">Total Products</div>
      </div>
    </div>
    <div class="prd-stat-card">
      <div class="prd-stat-icon" style="background:#dcfce7">
        <svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="1.8"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <div class="prd-stat-val" style="color:#16a34a" x-text="activeCount"></div>
        <div class="prd-stat-lbl">Active</div>
      </div>
    </div>
    <div class="prd-stat-card">
      <div class="prd-stat-icon" style="background:#fef9c3">
        <svg fill="none" viewBox="0 0 24 24" stroke="#b45309" stroke-width="1.8"><path d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
      </div>
      <div>
        <div class="prd-stat-val" style="color:#b45309" x-text="lowStockCount"></div>
        <div class="prd-stat-lbl">Low Stock</div>
      </div>
    </div>
    <div class="prd-stat-card">
      <div class="prd-stat-icon" style="background:#eff6ff">
        <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="1.8"><path d="M9 7h6m0 10v-3m-3 3v-3m-3 3v-3m9-8H4a1 1 0 00-1 1v10a1 1 0 001 1h16a1 1 0 001-1V6a1 1 0 00-1-1z"/></svg>
      </div>
      <div>
        <div class="prd-stat-val" style="color:#2563eb" x-text="fmtCompact(totalStockValue)"></div>
        <div class="prd-stat-lbl">Total Stock Value</div>
      </div>
    </div>
  </div>

  
  <div class="prd-toolbar">
    <div class="prd-search-wrap">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" x-model="search" placeholder="Search name, SKU…">
    </div>
    <select x-model="categoryFilter" class="prd-select">
      <option value="">All Categories</option>
      <template x-for="cat in categories" :key="cat.id">
        <option :value="cat.id" x-text="cat.name"></option>
      </template>
    </select>
    <div style="margin-left:auto">
      <a href="<?php echo e(url('/products/create')); ?>"
         style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-radius:10px;padding:8px 18px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;text-decoration:none;box-shadow:0 4px 12px rgba(99,102,241,.35);transition:opacity .15s"
         onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
        <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
        New Product
      </a>
    </div>
  </div>

  
  <div class="prd-table-card">
    <div x-show="loading" class="flex items-center justify-center py-16">
      <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>
    <div x-show="!loading" class="overflow-x-auto">
      <table class="prd-table">
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Unit</th>
            <th style="text-align:right">Cost Price</th>
            <th style="text-align:right">Sale Price</th>
            <th :style="isAllBranches ? '' : 'text-align:right'">Stock Qty</th>
            <th style="text-align:right">Stock Value</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <template x-for="p in filtered" :key="p.id">
            <tr>
              <td>
                <div class="flex items-center gap-3">
                  <div class="prd-icon">
                    <svg style="width:16px;height:16px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                  </div>
                  <div>
                    <div class="prd-name" x-text="p.name"></div>
                    <div class="prd-sub" x-text="p.sku ?? '—'"></div>
                  </div>
                </div>
              </td>
              <td class="text-sm text-gray-600 dark:text-gray-300" x-text="p.category?.name ?? '—'"></td>
              <td class="text-sm text-gray-600 dark:text-gray-300" x-text="p.unit ?? '—'"></td>
              <td class="text-sm text-gray-600 dark:text-gray-300 tabular-nums" style="text-align:right" x-text="fmtMoney(p.cost_price ?? 0)"></td>
              <td class="text-sm font-semibold text-gray-800 dark:text-gray-100 tabular-nums" style="text-align:right" x-text="fmtMoney(p.sale_price ?? p.selling_price ?? 0)"></td>
              <td>
                <template x-if="!isAllBranches">
                  <span :class="stockQty(p) <= (p.reorder_level ?? 0) ? 'text-red-600 font-bold' : 'text-gray-800 dark:text-gray-100 font-semibold'"
                        class="tabular-nums" x-text="stockQty(p)"></span>
                </template>
                <template x-if="isAllBranches">
                  <div class="flex flex-wrap items-center gap-1">
                    <template x-for="s in (p.branch_stocks ?? p.branchStocks ?? [])" :key="s.branch_id">
                      <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-semibold"
                            :class="parseFloat(s.quantity) <= 0 ? 'bg-red-50 text-red-600' : 'bg-indigo-50 text-indigo-700'">
                        <span x-text="s.branch?.code ?? s.branch?.name ?? 'B'+s.branch_id"></span>
                        <span class="opacity-60">:</span>
                        <span x-text="parseFloat(s.quantity)"></span>
                      </span>
                    </template>
                    <template x-if="(p.branch_stocks ?? p.branchStocks ?? []).length > 1">
                      <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full font-bold bg-gray-100 text-gray-700 border border-gray-200">
                        Total: <span x-text="stockQty(p)"></span>
                      </span>
                    </template>
                    <span x-show="!(p.branch_stocks ?? p.branchStocks ?? []).length" class="text-gray-400 text-xs">—</span>
                  </div>
                </template>
              </td>
              <td class="text-sm font-semibold text-gray-700 dark:text-gray-200 tabular-nums" style="text-align:right" x-text="fmtMoney(stockValue(p))"></td>
              <td>
                <span :class="(p.is_active ?? true) ? 'badge-success' : 'badge-gray'"
                    x-text="(p.is_active ?? true) ? 'Active' : 'Inactive'"></span>
              </td>
              <td>
                <a :href="BASE + '/products/' + p.id" class="prd-action-btn" title="View">
                  <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </a>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
      <div x-show="!loading && filtered.length === 0" class="prd-empty">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        <h5>No products found</h5>
        <p>Try adjusting your search or filters</p>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function productsPage() {
    return {
        items: [],
        categories: [],
        loading: true,
        search: '',
        categoryFilter: '',
        isAllBranches: localStorage.getItem('medri_branch') === 'all' || !localStorage.getItem('medri_branch'),
        get filtered() {
            let list = this.items;
            if (this.categoryFilter) list = list.filter(p => p.category_id == this.categoryFilter);
            const q = this.search.toLowerCase();
            if (!q) return list;
            return list.filter(p =>
                (p.name ?? '').toLowerCase().includes(q) ||
                (p.sku ?? '').toLowerCase().includes(q)
            );
        },
        get activeCount() { return this.items.filter(p => (p.is_active ?? true)).length; },
        get lowStockCount() { return this.items.filter(p => stockQtyOf(p) <= (p.reorder_level ?? 0)).length; },
        get totalStockValue() { return this.items.reduce((s, p) => s + stockValueOf(p), 0); },
        stockQty(p) {
            if (p.stock_qty != null) return p.stock_qty;
            const stocks = p.branch_stocks ?? p.branchStocks ?? [];
            if (!stocks.length) return 0;
            return stocks.reduce((s, b) => s + parseFloat(b.quantity ?? 0), 0);
        },
        stockValue(p) {
            const stocks = p.branch_stocks ?? p.branchStocks ?? [];
            if (!stocks.length) return 0;
            return stocks.reduce((s, b) => s + parseFloat(b.quantity ?? 0) * parseFloat(b.avg_cost ?? 0), 0);
        },
        get activeCount() { return this.items.filter(p => (p.is_active ?? true)).length; },
        get lowStockCount() { return this.items.filter(p => this.stockQty(p) <= (p.reorder_level ?? 0)).length; },
        get totalStockValue() { return this.items.reduce((s, p) => s + this.stockValue(p), 0); },
        async init() {
            try {
                const [pr, cr] = await Promise.all([
                    apiFetch('/products?per_page=200').then(r => r.json()),
                    apiFetch('/products/categories').then(r => r.json()),
                ]);
                this.items = pr.data ?? pr ?? [];
                this.categories = cr.data ?? cr ?? [];
            } catch (e) {
                toast('Failed to load products', 'error');
            } finally {
                this.loading = false;
            }
        },
        fmtMoney(n) { return Number(n??0).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); },
        fmtCompact(n) { const v = Math.abs(Number(n??0)); if(v>=1e6) return (v/1e6).toFixed(1)+'M'; if(v>=1e3) return (v/1e3).toFixed(1)+'K'; return v.toFixed(0); },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\products\index.blade.php ENDPATH**/ ?>