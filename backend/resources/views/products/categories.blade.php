@extends('layouts.app')
@section('title', 'Product Categories')
@section('page-title', 'Product Categories')
@section('page-desc', 'Organize products into categories')
@php $sec = 'inventory'; @endphp

@section('content')
<style>
.cat-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px}
.cat-stat-card{background:#fff;border-radius:14px;padding:18px 20px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:14px;transition:box-shadow .2s,transform .2s}
.cat-stat-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.08);transform:translateY(-2px)}
.cat-stat-icon{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.cat-stat-icon svg{width:22px;height:22px}
.cat-stat-val{font-size:22px;font-weight:800;line-height:1.1;letter-spacing:-.5px}
.cat-stat-lbl{font-size:11.5px;color:#94a3b8;font-weight:500;margin-top:2px}

.cat-toolbar{background:#fff;border-radius:14px;padding:14px 18px;border:1px solid #e2e8f0;margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.cat-search-wrap{position:relative;flex:1;min-width:200px;max-width:340px}
.cat-search-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none}
.cat-search-wrap input{width:100%;border:1px solid #e2e8f0;border-radius:9px;padding:7px 12px 7px 34px;font-size:13px;color:#1e293b;background:#f8fafc;outline:none;transition:border-color .15s,box-shadow .15s}
.cat-search-wrap input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);background:#fff}

.cat-table-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden}
.cat-table{width:100%;border-collapse:separate;border-spacing:0}
.cat-table thead th{padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e2e8f0;white-space:nowrap}
.cat-table thead th:first-child{padding-left:20px}
.cat-table tbody tr{transition:background .1s}
.cat-table tbody tr:hover{background:#f8faff}
.cat-table tbody td{padding:13px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.cat-table tbody td:first-child{padding-left:20px}
.cat-table tbody tr:last-child td{border-bottom:none}

.cat-icon{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:#eef2ff;color:#4f46e5}
.cat-name{font-size:13px;font-weight:600;color:#1e293b}
.cat-code{font-size:11px;color:#94a3b8;margin-top:1px;font-family:monospace}
.cat-count-chip{font-size:11px;font-weight:700;padding:2px 9px;border-radius:20px;background:#eef2ff;color:#4f46e5}

.cat-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;padding:64px 24px;text-align:center}
.cat-empty svg{width:56px;height:56px;color:#e2e8f0}
.cat-empty h5{font-size:16px;font-weight:700;color:#475569;margin-top:14px}
.cat-empty p{font-size:13px;color:#94a3b8;margin-top:4px}

.dark .cat-stat-card{background:#1e293b;border-color:#334155}
.dark .cat-stat-lbl{color:#64748b}
.dark .cat-toolbar{background:#1e293b;border-color:#334155}
.dark .cat-search-wrap input{background:#0f172a;border-color:#334155;color:#e2e8f0}
.dark .cat-search-wrap input:focus{background:#1e293b}
.dark .cat-table-card{background:#1e293b;border-color:#334155}
.dark .cat-table thead th{background:#0f172a;border-color:#334155}
.dark .cat-table tbody tr:hover{background:#1e3351}
.dark .cat-table tbody td{border-color:#1e293b}
.dark .cat-name{color:#e2e8f0}
.dark .cat-icon{background:#1e3a5f;color:#93c5fd}
.dark .cat-empty svg{color:#334155}
.dark .cat-empty h5{color:#94a3b8}
</style>

<div x-data="categoriesPage()" x-init="init()" x-cloak>

  {{-- Stats Cards --}}
  <div class="cat-stats">
    <div class="cat-stat-card">
      <div class="cat-stat-icon" style="background:#eef2ff">
        <svg fill="none" viewBox="0 0 24 24" stroke="#4f46e5" stroke-width="1.8"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
      </div>
      <div>
        <div class="cat-stat-val" style="color:#4f46e5" x-text="categories.length"></div>
        <div class="cat-stat-lbl">Top-level Categories</div>
      </div>
    </div>
    <div class="cat-stat-card">
      <div class="cat-stat-icon" style="background:#dcfce7">
        <svg fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="1.8"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <div>
        <div class="cat-stat-val" style="color:#16a34a" x-text="activeCount"></div>
        <div class="cat-stat-lbl">Active</div>
      </div>
    </div>
    <div class="cat-stat-card">
      <div class="cat-stat-icon" style="background:#eff6ff">
        <svg fill="none" viewBox="0 0 24 24" stroke="#2563eb" stroke-width="1.8"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
      </div>
      <div>
        <div class="cat-stat-val" style="color:#2563eb" x-text="totalProducts"></div>
        <div class="cat-stat-lbl">Products Categorized</div>
      </div>
    </div>
  </div>

  {{-- Toolbar --}}
  <div class="cat-toolbar">
    <div class="cat-search-wrap">
      <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
      <input type="text" x-model="search" placeholder="Search category name or code…">
    </div>
    <div style="margin-left:auto">
      <button @click="openCreate()"
              style="background:linear-gradient(135deg,#4f46e5,#6366f1);color:#fff;border-radius:10px;padding:8px 18px;font-size:13px;font-weight:700;display:flex;align-items:center;gap:6px;border:none;cursor:pointer;box-shadow:0 4px 12px rgba(99,102,241,.35);transition:opacity .15s"
              onmouseover="this.style.opacity='.9'" onmouseout="this.style.opacity='1'">
        <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path d="M12 5v14M5 12h14"/></svg>
        New Category
      </button>
    </div>
  </div>

  <!-- Loading -->
  <div x-show="loading" class="flex items-center justify-center py-16">
    <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
  </div>

  <!-- Categories list -->
  <div x-show="!loading" class="cat-table-card">
    <div class="overflow-x-auto">
      <table class="cat-table">
        <thead>
          <tr>
            <th>Category</th>
            <th>Code</th>
            <th>Products</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <template x-for="cat in filteredCategories" :key="cat.id">
            <template x-for="row in [cat, ...(cat.children||[])]" :key="row.id">
              <tr>
                <td>
                  <div class="flex items-center gap-3">
                    <template x-if="!row.parent_id">
                      <div class="cat-icon">
                        <svg style="width:15px;height:15px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                      </div>
                    </template>
                    <span x-show="row.parent_id" class="text-gray-300 ml-2">└</span>
                    <div class="cat-name" x-text="row.name"></div>
                  </div>
                </td>
                <td><span class="cat-code" x-text="row.code"></span></td>
                <td><span class="cat-count-chip" x-text="row.products_count ?? 0"></span></td>
                <td>
                  <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                        :class="row.is_active ? 'badge-success' : 'badge-danger'"
                        x-text="row.is_active ? 'Active' : 'Inactive'"></span>
                </td>
                <td>
                  <div class="flex items-center gap-3">
                    <button @click="openEdit(row)" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</button>
                    <button @click="deleteCategory(row)" class="text-sm font-medium text-red-500 hover:text-red-700">Delete</button>
                  </div>
                </td>
              </tr>
            </template>
          </template>
        </tbody>
      </table>
      <div x-show="!loading && categories.length === 0" class="cat-empty">
        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        <h5>No categories yet</h5>
        <p>Create your first one to start organizing products</p>
      </div>
    </div>
  </div>

  <!-- Create / Edit Modal -->
  <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
      <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editId ? 'Edit Category' : 'New Category'"></h3>
              <button @click="showModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                  <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
          </div>
          <form @submit.prevent="save()" class="p-6 space-y-4">

              <template x-if="!editId">
                  <div>
                      <label class="label">Branch <span class="text-red-500">*</span></label>
                      <select x-model="form.branch_id" class="input w-full" required>
                          <option value="">— Select Branch —</option>
                          <template x-for="b in branches" :key="b.id">
                              <option :value="b.id" x-text="b.name"></option>
                          </template>
                      </select>
                  </div>
              </template>

              <div>
                  <label class="label">Name <span class="text-red-500">*</span></label>
                  <input x-model="form.name" type="text" class="input w-full" placeholder="e.g. Surgical Equipment" required />
              </div>

              <div>
                  <label class="label">Code <span class="text-red-500">*</span></label>
                  <input x-model="form.code" type="text" class="input w-full font-mono" placeholder="e.g. SURG" maxlength="20" required />
              </div>

              <div>
                  <label class="label">Parent Category</label>
                  <select x-model="form.parent_id" class="input w-full">
                      <option value="">— None (top-level) —</option>
                      <template x-for="p in categories.filter(c => c.id !== editId)" :key="p.id">
                          <option :value="p.id" x-text="p.name"></option>
                      </template>
                  </select>
              </div>

              <div>
                  <label class="label">Description</label>
                  <textarea x-model="form.description" rows="2" class="input w-full resize-none" placeholder="Optional"></textarea>
              </div>

              <label class="flex items-center gap-2 cursor-pointer" x-show="editId">
                  <input type="checkbox" x-model="form.is_active" class="rounded text-indigo-600" />
                  <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
              </label>

              <div x-show="formError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>

              <div class="flex justify-end gap-3 pt-2">
                  <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                  <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : (editId ? 'Update Category' : 'Create Category')"></button>
              </div>
          </form>
      </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
function categoriesPage() {
    return {
        categories: [],
        branches: [],
        defaultBranchId: '',
        loading: true,
        showModal: false,
        editId: null,
        saving: false,
        formError: '',
        search: '',
        form: {},

        get filteredCategories() {
            const q = this.search.toLowerCase().trim();
            if (!q) return this.categories;
            return this.categories.filter(cat =>
                [cat, ...(cat.children||[])].some(row =>
                    (row.name ?? '').toLowerCase().includes(q) || (row.code ?? '').toLowerCase().includes(q)
                )
            );
        },
        get activeCount() {
            return this.categories.reduce((s, cat) => s + [cat, ...(cat.children||[])].filter(r => r.is_active).length, 0);
        },
        get totalProducts() {
            return this.categories.reduce((s, cat) => s + [cat, ...(cat.children||[])].reduce((s2, r) => s2 + (r.products_count ?? 0), 0), 0);
        },

        blank() {
            return { name: '', code: '', parent_id: '', description: '', is_active: true, branch_id: this.defaultBranchId };
        },

        async init() {
            await this.load();
            try {
                const bd = await apiFetch('/branches').then(r => r.json());
                this.branches = bd.data ?? bd ?? [];
                const u = JSON.parse(localStorage.getItem('medri_user') || '{}');
                const stored = localStorage.getItem('medri_branch');
                this.defaultBranchId = (stored && stored !== 'all') ? stored : (u.default_branch_id ?? '');
            } catch (_) {}
        },

        async load() {
            this.loading = true;
            try {
                const r = await apiFetch('/products/categories');
                this.categories = await r.json();
            } catch (e) {
                toast(e.message ?? 'Failed to load categories', 'error');
            } finally {
                this.loading = false;
            }
        },

        openCreate() {
            this.editId = null;
            this.form = this.blank();
            this.formError = '';
            this.showModal = true;
        },

        openEdit(cat) {
            this.editId = cat.id;
            this.form = {
                name: cat.name,
                code: cat.code,
                parent_id: cat.parent_id ?? '',
                description: cat.description ?? '',
                is_active: cat.is_active,
            };
            this.formError = '';
            this.showModal = true;
        },

        async save() {
            this.saving = true;
            this.formError = '';
            try {
                const url = this.editId ? `/products/categories/${this.editId}` : '/products/categories';
                const method = this.editId ? 'PUT' : 'POST';
                await apiFetch(url, { method, body: JSON.stringify(this.form) });
                toast(this.editId ? 'Category updated.' : 'Category created.');
                this.showModal = false;
                await this.load();
            } catch (e) {
                this.formError = e.message ?? 'Unexpected error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async deleteCategory(cat) {
            if (!confirm(`Delete "${cat.name}"? This cannot be undone.`)) return;
            try {
                await apiFetch(`/products/categories/${cat.id}`, { method: 'DELETE' });
                toast('Category deleted.');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Cannot delete category.', 'error');
            }
        },
    };
}
</script>
@endpush
