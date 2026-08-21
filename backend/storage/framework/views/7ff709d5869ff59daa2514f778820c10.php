<?php $__env->startSection('title', 'Bank Management'); ?>
<?php $__env->startSection('page-title', 'Bank Management'); ?>
<?php $__env->startSection('page-desc', 'Manage the list of banks available across the system'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="banksPage()" x-init="init()">

  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
    <input x-model="search" type="text" placeholder="Search bank name or SWIFT…" class="input w-full sm:w-72" />
    <button @click="openAdd()" class="btn-primary inline-flex items-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Add Bank
    </button>
  </div>

  
  <div class="card p-0 overflow-hidden">
    <div x-show="loading" class="flex items-center justify-center py-16">
      <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>
    <div x-show="!loading" class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800">
          <tr>
            <th class="table-hd">#</th>
            <th class="table-hd">Bank Name</th>
            <th class="table-hd">Short Name</th>
            <th class="table-hd">SWIFT Code</th>
            <th class="table-hd">Status</th>
            <th class="table-hd">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700">
          <template x-for="(b, i) in filtered" :key="b.id">
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
              <td class="table-td text-gray-400 text-xs" x-text="i + 1"></td>
              <td class="table-td font-medium text-gray-900 dark:text-gray-100" x-text="b.name"></td>
              <td class="table-td text-gray-600 dark:text-gray-300" x-text="b.short_name || '—'"></td>
              <td class="table-td font-mono text-xs text-gray-500" x-text="b.swift_code || '—'"></td>
              <td class="table-td">
                <span :class="b.is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-400'"
                      class="text-xs font-semibold px-2 py-0.5 rounded-full"
                      x-text="b.is_active ? 'Active' : 'Inactive'"></span>
              </td>
              <td class="table-td">
                <div class="flex items-center gap-2">
                  <button @click="openEdit(b)" class="text-xs text-indigo-600 hover:underline font-medium">Edit</button>
                  <button @click="toggleActive(b)"
                          :class="b.is_active ? 'text-yellow-600' : 'text-green-600'"
                          class="text-xs hover:underline font-medium"
                          x-text="b.is_active ? 'Deactivate' : 'Activate'"></button>
                  <button @click="deleteBank(b)" class="text-xs text-red-500 hover:underline font-medium">Delete</button>
                </div>
              </td>
            </tr>
          </template>
          <tr x-show="!loading && filtered.length === 0">
            <td colspan="6" class="table-td text-center text-gray-400 py-10">No banks found.</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  
  <div x-show="modal.open" x-cloak
       class="fixed inset-0 z-50 flex items-center justify-center p-4"
       style="background:rgba(0,0,0,0.5)">
    <div @click.outside="modal.open = false"
         class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-4 max-h-[90vh] overflow-y-auto">

      <div class="flex items-center justify-between">
        <h2 class="text-base font-bold text-gray-800 dark:text-gray-100"
            x-text="modal.id ? 'Edit Bank' : 'Add Bank'"></h2>
        <button @click="modal.open = false" class="w-7 h-7 rounded-lg flex items-center justify-center hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-400">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <div class="space-y-3">
        <div>
          <label class="label">Bank Name <span class="text-red-500">*</span></label>
          <input type="text" x-model="modal.name" class="input" placeholder="e.g. Commercial Bank of Ceylon" required />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label">Short Name</label>
            <input type="text" x-model="modal.short_name" class="input" placeholder="e.g. CBC" maxlength="20" />
          </div>
          <div>
            <label class="label">SWIFT Code</label>
            <input type="text" x-model="modal.swift_code" class="input font-mono" placeholder="e.g. CCEYLKLX" maxlength="20" />
          </div>
        </div>
        <div class="flex items-center gap-2 pt-1">
          <input type="checkbox" x-model="modal.is_active" id="bankActive" class="rounded" />
          <label for="bankActive" class="text-sm text-gray-600 dark:text-gray-300">Active (available in dropdowns)</label>
        </div>
      </div>

      <div class="flex gap-3 pt-2">
        <button @click="modal.open = false" class="btn-secondary flex-1">Cancel</button>
        <button @click="save()" :disabled="saving" class="btn-primary flex-1">
          <span x-text="saving ? 'Saving…' : (modal.id ? 'Update Bank' : 'Add Bank')"></span>
        </button>
      </div>
    </div>
  </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function banksPage() {
  return {
    items: [], loading: true, saving: false, search: '',
    modal: { open: false, id: null, name: '', short_name: '', swift_code: '', is_active: true },

    get filtered() {
      const q = this.search.toLowerCase();
      if (!q) return this.items;
      return this.items.filter(b =>
        (b.name ?? '').toLowerCase().includes(q) ||
        (b.short_name ?? '').toLowerCase().includes(q) ||
        (b.swift_code ?? '').toLowerCase().includes(q)
      );
    },

    async init() {
      try {
        const r = await apiFetch('/banks?active_only=false');
        this.items = await r.json();
      } catch (e) { toast('Failed to load banks', 'error'); }
      finally { this.loading = false; }
    },

    openAdd() {
      this.modal = { open: true, id: null, name: '', short_name: '', swift_code: '', is_active: true };
    },

    openEdit(b) {
      this.modal = { open: true, id: b.id, name: b.name, short_name: b.short_name ?? '', swift_code: b.swift_code ?? '', is_active: b.is_active };
    },

    async save() {
      if (!this.modal.name.trim()) { toast('Bank name is required', 'error'); return; }
      this.saving = true;
      try {
        const method = this.modal.id ? 'PUT' : 'POST';
        const path   = this.modal.id ? `/banks/${this.modal.id}` : '/banks';
        const r = await apiFetch(path, { method, body: JSON.stringify({
          name: this.modal.name.trim(),
          short_name: this.modal.short_name || null,
          swift_code: this.modal.swift_code || null,
          is_active: this.modal.is_active,
        })});
        if (!r.ok) { const e = await r.json(); throw new Error(e.message ?? 'Save failed'); }
        const saved = await r.json();
        if (this.modal.id) {
          const idx = this.items.findIndex(b => b.id === this.modal.id);
          if (idx >= 0) this.items[idx] = saved;
        } else {
          this.items.push(saved);
          this.items.sort((a, b) => a.name.localeCompare(b.name));
        }
        window._banksCache = null; // invalidate global cache
        this.modal.open = false;
        toast(this.modal.id ? 'Bank updated' : 'Bank added', 'success');
      } catch (e) { toast(e.message ?? 'Failed to save', 'error'); }
      finally { this.saving = false; }
    },

    async toggleActive(b) {
      try {
        const r = await apiFetch(`/banks/${b.id}`, { method: 'PUT', body: JSON.stringify({ is_active: !b.is_active }) });
        if (!r.ok) throw new Error();
        const updated = await r.json();
        const idx = this.items.findIndex(x => x.id === b.id);
        if (idx >= 0) this.items[idx] = updated;
        window._banksCache = null;
        toast(updated.is_active ? 'Bank activated' : 'Bank deactivated', 'success');
      } catch (e) { toast('Failed to update status', 'error'); }
    },

    async deleteBank(b) {
      if (!confirm(`Delete "${b.name}"? This cannot be undone.`)) return;
      try {
        const r = await apiFetch(`/banks/${b.id}`, { method: 'DELETE' });
        if (!r.ok) throw new Error();
        this.items = this.items.filter(x => x.id !== b.id);
        window._banksCache = null;
        toast('Bank deleted', 'success');
      } catch (e) { toast('Failed to delete bank', 'error'); }
    },
  };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/settings/banks.blade.php ENDPATH**/ ?>