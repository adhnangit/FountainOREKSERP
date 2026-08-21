@extends('layouts.app')
@section('title', 'Product Detail')
@section('page-title', 'Product Detail')
@section('page-desc', 'Product information, stock levels and movement history')

@push('head')
<style>
  .stat-tile { border-radius:12px; overflow:hidden; color:#fff; position:relative; }
  .stat-tile::after  { content:''; position:absolute; right:-10px; top:-10px; width:56px; height:56px; border-radius:50%; background:rgba(255,255,255,0.08); pointer-events:none; }
  .stat-tile::before { content:''; position:absolute; right:22px; bottom:-18px; width:38px; height:38px; border-radius:50%; background:rgba(255,255,255,0.05); pointer-events:none; }
  .stat-shine { height:2px; background:linear-gradient(90deg,rgba(255,255,255,0.35) 0%,rgba(255,255,255,0.05) 100%); }
  .section-hd { display:flex; align-items:center; gap:12px; padding:16px 20px; background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%); }
  .section-icon { width:34px; height:34px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.2); }
</style>
@endpush

@section('content')
<div x-data="productShowPage()" x-init="init()">

    <div x-show="loading" class="flex items-center justify-center py-20">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" class="pb-12">
        <!-- Top bar -->
        <div class="flex items-center justify-between mb-5">
            <a href="{{ url('/products') }}" class="btn-secondary inline-flex items-center gap-2 text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5M12 5l-7 7 7 7"/></svg>
                Back
            </a>
            <div class="flex items-center gap-2">
                <a :href="BASE + '/products/' + id + '/edit'" class="btn-secondary text-sm inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Edit
                </a>
                <button @click="confirmDelete()" class="btn-danger text-sm inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
            </div>
        </div>

        <!-- Hero -->
        <div class="card overflow-hidden mb-5">
            <div class="p-6">
                <div class="flex flex-col sm:flex-row sm:items-start gap-5">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center flex-shrink-0"
                         style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%)">
                        <svg style="width:30px;height:30px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2 mb-1.5">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white" x-text="product.name ?? '—'"></h2>
                            <span class="badge" :class="(product.is_active ?? true) ? 'badge-success' : 'badge-gray'"
                                x-text="(product.is_active ?? true) ? 'Active' : 'Inactive'"></span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-3" x-text="'SKU: ' + (product.sku ?? 'N/A') + ' · Code: ' + (product.code ?? 'N/A')"></p>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="badge badge-primary" x-text="product.category?.name ?? 'Uncategorised'"></span>
                            <span class="badge badge-gray" x-text="(product.unit ?? 'pcs') + ' · unit of measure'"></span>
                            <span class="badge" :class="product.branch?.name ? 'badge-gray' : 'badge-gray'" x-show="product.branch?.name" x-text="product.branch?.name"></span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-4" x-show="product.description" x-text="product.description"></p>
                    </div>
                </div>
            </div>

            <!-- Stat tiles -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 px-6 pb-6">
                <div class="stat-tile" style="background:linear-gradient(135deg,#64748b,#475569)">
                    <div class="flex items-center gap-3 px-4 py-4">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.16)">
                            <svg style="width:17px;height:17px" class="text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3v-6m-3 6v-9m12 0l-8-4-8 4m16 0l-8 4m0 0L4 7"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10.5px] font-semibold uppercase tracking-wider leading-none mb-1" style="color:rgba(255,255,255,0.65)">Cost Price</p>
                            <p class="text-[18px] font-black leading-none" x-text="fmtMoney(product.cost_price ?? 0)"></p>
                        </div>
                    </div>
                    <div class="stat-shine"></div>
                </div>
                <div class="stat-tile" style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
                    <div class="flex items-center gap-3 px-4 py-4">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.16)">
                            <svg style="width:17px;height:17px" class="text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 12v-2m0-8c-1.11 0-2.08.402-2.599 1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10.5px] font-semibold uppercase tracking-wider leading-none mb-1" style="color:rgba(255,255,255,0.65)">Sale Price</p>
                            <p class="text-[18px] font-black leading-none" x-text="fmtMoney(product.selling_price ?? product.sale_price ?? 0)"></p>
                        </div>
                    </div>
                    <div class="stat-shine"></div>
                </div>
                <div class="stat-tile" :style="marginPct >= 0 ? 'background:linear-gradient(135deg,#16a34a,#15803d)' : 'background:linear-gradient(135deg,#dc2626,#b91c1c)'">
                    <div class="flex items-center gap-3 px-4 py-4">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.16)">
                            <svg style="width:17px;height:17px" class="text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10.5px] font-semibold uppercase tracking-wider leading-none mb-1" style="color:rgba(255,255,255,0.65)">Margin</p>
                            <p class="text-[18px] font-black leading-none" x-text="marginPct.toFixed(1) + '%'"></p>
                        </div>
                    </div>
                    <div class="stat-shine"></div>
                </div>
                <div class="stat-tile" style="background:linear-gradient(135deg,#d97706,#b45309)">
                    <div class="flex items-center gap-3 px-4 py-4">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.16)">
                            <svg style="width:17px;height:17px" class="text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10.5px] font-semibold uppercase tracking-wider leading-none mb-1" style="color:rgba(255,255,255,0.65)">Reorder Level</p>
                            <p class="text-[18px] font-black leading-none" x-text="(product.reorder_level ?? 0) + ' ' + (product.unit ?? 'pcs')"></p>
                        </div>
                    </div>
                    <div class="stat-shine"></div>
                </div>
            </div>
        </div>

        <!-- Stock per Branch -->
        <div class="card overflow-hidden mb-5">
            <div class="section-hd">
                <div class="section-icon">
                    <svg style="width:16px;height:16px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16M9 21V11h6v10M9 7h.01M15 7h.01M9 11h.01M15 11h.01"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-white">Stock by Branch</h3>
                    <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">Live quantity and value across all branches</p>
                </div>
                <div class="text-right flex-shrink-0" x-show="!loadingStock">
                    <p class="text-[10px] font-semibold uppercase tracking-wider" style="color:rgba(255,255,255,0.55)">Total Stock</p>
                    <p class="text-sm font-bold text-white" x-text="totalStockQty + ' ' + (product.unit ?? 'pcs') + '  ·  ' + fmtMoney(totalStockValue)"></p>
                </div>
            </div>
            <div x-show="loadingStock" class="flex items-center justify-center py-8">
                <svg class="animate-spin w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            </div>
            <div x-show="!loadingStock" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/60">
                    <thead class="bg-gray-50 dark:bg-gray-800/40">
                        <tr>
                            <th class="table-hd">Branch</th>
                            <th class="table-hd text-right">Quantity</th>
                            <th class="table-hd text-right">Stock Value</th>
                            <th class="table-hd">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                        <template x-for="s in stockData" :key="s.branch_id ?? s.id ?? Math.random()">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="table-td font-medium text-gray-900 dark:text-gray-100" x-text="s.branch?.name ?? s.branch_name ?? '—'"></td>
                                <td class="table-td text-right font-semibold tabular-nums"
                                    :class="(s.quantity ?? 0) <= (product.reorder_level ?? 0) ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-gray-200'"
                                    x-text="s.quantity ?? 0"></td>
                                <td class="table-td text-right tabular-nums" x-text="fmtMoney((s.quantity ?? 0) * (product.cost_price ?? 0))"></td>
                                <td class="table-td">
                                    <span class="badge" :class="(s.quantity ?? 0) <= 0 ? 'badge-danger' : (s.quantity ?? 0) <= (product.reorder_level ?? 0) ? 'badge-warning' : 'badge-success'"
                                        x-text="(s.quantity ?? 0) <= 0 ? 'Out of Stock' : (s.quantity ?? 0) <= (product.reorder_level ?? 0) ? 'Low Stock' : 'In Stock'"></span>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="stockData.length === 0 && !loadingStock">
                            <td colspan="4" class="table-td text-center text-gray-400 py-10">No stock data available.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Batches -->
        <div class="card overflow-hidden mb-5">
            <div class="section-hd">
                <div class="section-icon">
                    <svg style="width:16px;height:16px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-white">Batches</h3>
                    <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">Stock lots with remaining quantity, oldest first</p>
                </div>
            </div>
            <div x-show="loadingBatches" class="flex items-center justify-center py-8">
                <svg class="animate-spin w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            </div>
            <div x-show="!loadingBatches" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/60">
                    <thead class="bg-gray-50 dark:bg-gray-800/40">
                        <tr>
                            <th class="table-hd">Batch</th>
                            <th class="table-hd">Received</th>
                            <th class="table-hd">Expiry</th>
                            <th class="table-hd text-right">Remaining Qty</th>
                            <th class="table-hd text-right">Cost Price</th>
                            <th class="table-hd text-right">Selling Price</th>
                            <th class="table-hd" x-show="isSuperAdmin"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                        <template x-for="b in batches" :key="b.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="table-td font-medium text-gray-900 dark:text-gray-100" x-text="b.batch_code + (b.batch_number ? ' (' + b.batch_number + ')' : '')"></td>
                                <td class="table-td text-gray-600 dark:text-gray-300" x-text="b.received_date ?? '—'"></td>
                                <td class="table-td text-gray-600 dark:text-gray-300" x-text="b.expiry_date ?? '—'"></td>
                                <td class="table-td text-right font-semibold tabular-nums" :class="(b.available_qty ?? 0) < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-800 dark:text-gray-200'" x-text="b.available_qty ?? 0"></td>
                                <td class="table-td text-right">
                                    <template x-if="isSuperAdmin && editingBatchId === b.id">
                                        <input type="number" x-model.number="editBatchForm.cost_price" min="0" step="0.01" class="input w-28 text-right" />
                                    </template>
                                    <template x-if="!(isSuperAdmin && editingBatchId === b.id)">
                                        <span class="tabular-nums text-gray-700 dark:text-gray-200" x-text="fmtMoney(b.unit_cost ?? 0)"></span>
                                    </template>
                                </td>
                                <td class="table-td text-right">
                                    <template x-if="isSuperAdmin && editingBatchId === b.id">
                                        <input type="number" x-model.number="editBatchForm.selling_price" min="0" step="0.01" class="input w-28 text-right" />
                                    </template>
                                    <template x-if="!(isSuperAdmin && editingBatchId === b.id)">
                                        <span class="tabular-nums text-gray-700 dark:text-gray-200" x-text="fmtMoney(b.selling_price ?? 0)"></span>
                                    </template>
                                </td>
                                <td class="table-td" x-show="isSuperAdmin">
                                    <template x-if="editingBatchId !== b.id">
                                        <button class="text-sm font-medium text-indigo-600 hover:text-indigo-800" @click="startEditBatch(b)">Edit</button>
                                    </template>
                                    <template x-if="editingBatchId === b.id">
                                        <div class="flex gap-3">
                                            <button class="text-sm font-medium text-indigo-600 hover:text-indigo-800" @click="saveBatchPrice(b)">Save</button>
                                            <button class="text-sm font-medium text-gray-400 hover:text-gray-600" @click="editingBatchId = null">Cancel</button>
                                        </div>
                                    </template>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="batches.length === 0 && !loadingBatches">
                            <td colspan="7" class="table-td text-center text-gray-400 py-10">No batches with stock remaining.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Stock Movements -->
        <div class="card overflow-hidden">
            <div class="section-hd">
                <div class="section-icon">
                    <svg style="width:16px;height:16px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4M16 17H4m0 0l4 4m-4-4l4-4"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-white">Stock Movements</h3>
                    <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">Full ledger of every stock in/out event</p>
                </div>
            </div>
            <div x-show="loadingMovements" class="flex items-center justify-center py-8">
                <svg class="animate-spin w-6 h-6 text-indigo-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
            </div>
            <div x-show="!loadingMovements" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700/60">
                    <thead class="bg-gray-50 dark:bg-gray-800/40">
                        <tr>
                            <th class="table-hd">Date</th>
                            <th class="table-hd">Type</th>
                            <th class="table-hd">Reference</th>
                            <th class="table-hd">Branch</th>
                            <th class="table-hd text-right">Qty In</th>
                            <th class="table-hd text-right">Qty Out</th>
                            <th class="table-hd text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                        <template x-for="m in movements" :key="m.id ?? Math.random()">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                <td class="table-td text-gray-600 dark:text-gray-300" x-text="fmtDate(m.movement_date ?? m.created_at)"></td>
                                <td class="table-td">
                                    <span class="badge"
                                          :class="m.type?.includes('in') ? 'badge-success' : 'badge-danger'"
                                          x-text="(m.type ?? '—').replace('_',' ')"></span>
                                </td>
                                <td class="table-td text-xs text-gray-500 dark:text-gray-400"
                                    x-text="m.reference_type ? m.reference_type.replace('_',' ') + ' #' + m.reference_id : (m.notes ?? '—')"></td>
                                <td class="table-td text-gray-600 dark:text-gray-300" x-text="m.branch?.name ?? '—'"></td>
                                <td class="table-td text-right font-semibold text-green-600 dark:text-green-400 tabular-nums"
                                    x-text="parseFloat(m.quantity) > 0 ? '+' + parseFloat(m.quantity).toFixed(2) : '—'"></td>
                                <td class="table-td text-right font-semibold text-red-500 dark:text-red-400 tabular-nums"
                                    x-text="parseFloat(m.quantity) < 0 ? parseFloat(m.quantity).toFixed(2) : '—'"></td>
                                <td class="table-td text-right font-bold tabular-nums"
                                    :class="parseFloat(m.balance_quantity ?? 0) < 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-700 dark:text-gray-200'"
                                    x-text="m.balance_quantity != null ? parseFloat(m.balance_quantity).toFixed(2) : '—'"></td>
                            </tr>
                        </template>
                        <tr x-show="movements.length === 0 && !loadingMovements">
                            <td colspan="7" class="table-td text-center text-gray-400 py-10">No movements recorded.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Delete confirmation -->
    <div x-show="showDeleteConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showDeleteConfirm = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm p-6">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 bg-red-50 dark:bg-red-900/20">
                    <svg class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <p class="font-bold text-gray-800 dark:text-gray-100">Delete Product?</p>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">
                <span x-text="'“' + (product.name ?? 'This product') + '”'"></span> will be permanently removed. This cannot be undone.
            </p>
            <div x-show="deleteError" class="mt-3 text-sm text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-400 rounded-lg px-3 py-2" x-text="deleteError"></div>
            <div class="flex justify-end gap-3 mt-5">
                <button @click="showDeleteConfirm = false" class="btn-secondary">Cancel</button>
                <button @click="doDelete()" class="btn-danger" :disabled="deleting" x-text="deleting ? 'Deleting…' : 'Delete Product'"></button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function productShowPage() {
    return {
        loading: true,
        loadingStock: true,
        loadingMovements: true,
        loadingBatches: true,
        product: {},
        stockData: [],
        movements: [],
        batches: [],
        isSuperAdmin: false,
        editingBatchId: null,
        editBatchForm: { cost_price: 0, selling_price: 0 },
        showDeleteConfirm: false,
        deleting: false,
        deleteError: '',
        get id() { return window.location.pathname.split('/').filter(Boolean).pop(); },
        get marginPct() {
            const cost = parseFloat(this.product.cost_price ?? 0);
            const sale = parseFloat(this.product.selling_price ?? this.product.sale_price ?? 0);
            if (!sale) return 0;
            return ((sale - cost) / sale) * 100;
        },
        get totalStockQty() {
            return this.stockData.reduce((s, r) => s + (parseFloat(r.quantity) || 0), 0);
        },
        get totalStockValue() {
            const cost = parseFloat(this.product.cost_price ?? 0);
            return this.totalStockQty * cost;
        },
        async init() {
            try {
                const u = JSON.parse(localStorage.getItem('medri_user') || '{}');
                this.isSuperAdmin = !!u.is_super_admin || (u.roles ?? []).includes('super_admin');
            } catch (_) {}
            try {
                const r = await apiFetch('/products/' + this.id);
                if (!r) return;
                const data = await r.json();
                this.product = data.data ?? data;
            } catch (e) {
                toast('Failed to load product', 'error');
            } finally {
                this.loading = false;
            }
            this.loadStock();
            this.loadMovements();
            this.loadBatches();
        },
        async loadBatches() {
            try {
                const branchId = this.product.branch_id;
                const r = await apiFetch('/products/' + this.id + '/batches' + (branchId ? '?branch_id=' + branchId : ''));
                if (!r) return;
                const data = await r.json();
                this.batches = Array.isArray(data) ? data : (data.data ?? []);
            } catch (e) {
                this.batches = [];
            } finally {
                this.loadingBatches = false;
            }
        },
        startEditBatch(b) {
            this.editingBatchId = b.id;
            this.editBatchForm = { cost_price: parseFloat(b.unit_cost ?? 0), selling_price: parseFloat(b.selling_price ?? 0) };
        },
        async saveBatchPrice(b) {
            try {
                await apiFetch('/products/' + this.id + '/batches/' + b.id, { method: 'PUT', body: JSON.stringify(this.editBatchForm) });
                toast('Batch price updated', 'success');
                this.editingBatchId = null;
                this.loadBatches();
            } catch (e) {
                toast('Failed to update batch price', 'error');
            }
        },
        async loadStock() {
            try {
                const r = await apiFetch('/products/' + this.id + '/stock');
                if (!r) return;
                const data = await r.json();
                this.stockData = data.stock ?? data.data ?? [];
            } catch (e) {
                this.stockData = [];
            } finally {
                this.loadingStock = false;
            }
        },
        async loadMovements() {
            try {
                const r = await apiFetch('/products/' + this.id + '/movements');
                if (!r) return;
                const data = await r.json();
                this.movements = data.data ?? data ?? [];
            } catch (e) {
                this.movements = [];
            } finally {
                this.loadingMovements = false;
            }
        },
        confirmDelete() {
            this.deleteError = '';
            this.showDeleteConfirm = true;
        },
        async doDelete() {
            this.deleting = true;
            this.deleteError = '';
            try {
                const r = await apiFetch('/products/' + this.id, { method: 'DELETE' });
                if (!r) return;
                if (!r.ok) {
                    const e = await r.json().catch(() => ({}));
                    this.deleteError = e.message ?? 'Cannot delete this product.';
                    return;
                }
                toast('Product deleted.', 'success');
                window.location.href = BASE + '/products';
            } catch (e) {
                this.deleteError = e.message ?? 'Unexpected error. Please try again.';
            } finally {
                this.deleting = false;
            }
        },
    };
}
</script>
@endpush
