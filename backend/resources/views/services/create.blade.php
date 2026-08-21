@extends('layouts.app')
@section('title', 'New Service')
@section('page-title', 'New Service')
@section('page-desc', 'Add a billable service — equipment rental, or any other non-stock charge')

@section('content')
<div x-data="serviceCreatePage()" x-init="init()" class="px-6 pb-12">
<form @submit.prevent="submit()">
<div class="flex flex-col lg:flex-row gap-6">

  {{-- ══ LEFT COLUMN ══ --}}
  <div class="w-full lg:flex-[62] min-w-0 space-y-5">

    <div class="card overflow-hidden">
      <div class="flex items-center gap-3 px-6 py-4"
           style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%)">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
             style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
          <svg style="width:18px;height:18px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-white">Service Information</h3>
          <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">Name, rate and description</p>
        </div>
      </div>
      <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

        <div class="sm:col-span-2">
          <label class="label">Service Name <span class="text-red-500">*</span></label>
          <input type="text" x-model="form.name" class="input" placeholder="e.g. Surgical Machinery Rental" required />
        </div>

        <div>
          <label class="label">Unit</label>
          <input type="text" x-model="form.unit" class="input" placeholder="e.g. per day, per session, flat rate" />
        </div>

        <div>
          <label class="label">Rate (LKR) <span class="text-red-500">*</span></label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium pointer-events-none">Rs.</span>
            <input type="number" x-model.number="form.rate" class="input pl-9 tabular-nums" min="0" step="0.01" placeholder="0.00" required />
          </div>
        </div>

        <div class="sm:col-span-2">
          <label class="label">Description</label>
          <textarea x-model="form.description" rows="3" class="input resize-none" placeholder="Details, terms, what's included…"></textarea>
        </div>

      </div>
    </div>

  </div>{{-- end left --}}

  {{-- ══ RIGHT COLUMN ══ --}}
  <div class="w-full lg:flex-[38]">
  <div class="lg:sticky lg:top-6 space-y-5">

    <div class="card overflow-hidden">
      <div class="flex items-center gap-3 px-5 py-4"
           style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%)">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
             style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
          <svg style="width:16px;height:16px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-white">Classification</h3>
          <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">Branch, category and status</p>
        </div>
      </div>
      <div class="px-5 py-4 space-y-4">

        <div>
          <label class="label">Branch <span class="text-red-500">*</span></label>
          <select x-model="form.branch_id" class="input" required>
            <option value="">Select branch…</option>
            <template x-for="b in branches" :key="b.id">
              <option :value="b.id" x-text="b.name"></option>
            </template>
          </select>
        </div>

        <div>
          <div class="flex items-center justify-between">
            <label class="label">Category</label>
            <button type="button" @click="openCategoryModal()" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">+ New Category</button>
          </div>
          <select x-model="form.category_id" class="input">
            <option value="">Select category…</option>
            <template x-for="cat in categories" :key="cat.id">
              <option :value="cat.id" x-text="cat.name"></option>
            </template>
          </select>
        </div>

        <label class="flex items-center gap-3 cursor-pointer py-1">
          <div class="relative flex-shrink-0">
            <input type="checkbox" x-model="form.is_active" class="sr-only peer" id="is_active" />
            <div class="w-10 h-6 rounded-full transition-colors peer-checked:bg-blue-600 bg-gray-200 dark:bg-gray-700"></div>
            <div class="absolute left-1 top-1 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></div>
          </div>
          <div>
            <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Service</div>
            <div class="text-xs text-gray-400">Available for billing on invoices</div>
          </div>
        </label>

      </div>
    </div>

    {{-- Preview --}}
    <div class="card overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Preview</h3>
      </div>
      <div class="px-5 py-4 space-y-3">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
               style="background:linear-gradient(135deg,#1B3EB6,#0D2272)">
            <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
          </div>
          <div class="min-w-0">
            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate"
                 x-text="form.name || 'Service Name'"></div>
            <div class="text-xs text-gray-400 truncate" x-text="form.unit || 'No unit set'"></div>
          </div>
        </div>
        <div class="border-t border-gray-100 dark:border-gray-700 pt-3">
          <div class="text-xs text-gray-400">Rate</div>
          <div class="font-bold mt-0.5" style="color:#1B3EB6" x-text="form.rate ? 'Rs. ' + Number(form.rate).toLocaleString() : '—'"></div>
        </div>
      </div>
    </div>

    {{-- Actions --}}
    <div class="flex gap-3">
      <a href="{{ url('/services') }}" class="btn-secondary flex-1 text-center">Cancel</a>
      <button type="submit" :disabled="submitting" class="btn-primary flex-1">
        <span x-text="submitting ? 'Saving…' : 'Create Service'"></span>
      </button>
    </div>

  </div>
  </div>{{-- end right --}}

</div>
</form>

{{-- Quick-create Category modal --}}
<div x-show="showCategoryModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showCategoryModal = false">
  <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
      <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">New Category</h3>
      <button @click="showCategoryModal = false" class="text-gray-400 hover:text-gray-600">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>
    <div class="px-5 py-4 space-y-3">
      <div>
        <label class="label">Name <span class="text-red-500">*</span></label>
        <input type="text" x-model="newCategory.name" class="input" placeholder="e.g. Equipment Rental" />
      </div>
      <div>
        <label class="label">Code <span class="text-red-500">*</span></label>
        <input type="text" x-model="newCategory.code" class="input font-mono" placeholder="e.g. RENTAL" maxlength="20" />
      </div>
    </div>
    <div class="flex justify-end gap-3 px-5 py-4 border-t border-gray-100 dark:border-gray-700">
      <button type="button" @click="showCategoryModal = false" class="btn-secondary">Cancel</button>
      <button type="button" @click="createCategoryQuick()" :disabled="creatingCategory" class="btn-primary">
        <span x-text="creatingCategory ? 'Saving…' : 'Create Category'"></span>
      </button>
    </div>
  </div>
</div>
</div>
@endsection

@push('scripts')
<script>
function serviceCreatePage() {
    return {
        categories: [],
        branches: [],
        submitting: false,
        showCategoryModal: false,
        creatingCategory: false,
        newCategory: { name: '', code: '' },
        form: {
            name: '',
            branch_id: '',
            category_id: '',
            unit: '',
            rate: 0,
            description: '',
            is_active: true,
        },
        async init() {
            try {
                const data = await apiFetch('/services/categories').then(r => r.json());
                this.categories = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load categories', 'error');
            }
            try {
                const bd = await apiFetch('/branches').then(r => r.json());
                this.branches = bd.data ?? bd ?? [];
                const u = JSON.parse(localStorage.getItem('medri_user') || '{}');
                const stored = localStorage.getItem('medri_branch');
                const bid = (stored && stored !== 'all') ? stored : u.default_branch_id;
                if (bid) this.form.branch_id = bid;
            } catch (_) {}
        },
        async submit() {
            if (!this.form.branch_id) { toast('Please select a branch', 'error'); return; }
            this.submitting = true;
            try {
                await apiFetch('/services', { method: 'POST', body: JSON.stringify(this.form) });
                toast('Service created successfully', 'success');
                window.location.href = BASE + '/services';
            } catch (e) {
                toast(e.message ?? 'Failed to create service', 'error');
            } finally {
                this.submitting = false;
            }
        },
        openCategoryModal() {
            if (!this.form.branch_id) { toast('Select a branch first', 'error'); return; }
            this.newCategory = { name: '', code: '' };
            this.showCategoryModal = true;
        },
        async createCategoryQuick() {
            if (!this.newCategory.name || !this.newCategory.code) {
                toast('Enter a name and code for the category', 'error');
                return;
            }
            this.creatingCategory = true;
            try {
                const payload = { ...this.newCategory, branch_id: this.form.branch_id };
                const r = await apiFetch('/services/categories', { method: 'POST', body: JSON.stringify(payload) });
                const cat = await r.json();
                this.categories.push(cat);
                this.form.category_id = cat.id;
                this.showCategoryModal = false;
                toast('Category created', 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to create category', 'error');
            } finally {
                this.creatingCategory = false;
            }
        },
    };
}
</script>
@endpush
