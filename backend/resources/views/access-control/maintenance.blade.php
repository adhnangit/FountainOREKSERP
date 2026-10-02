@extends('layouts.app')
@section('title', 'Maintenance Mode')
@section('page-title', 'Maintenance Mode')
@section('page-desc', 'Take the whole system offline for everyone except authorized admins')

@section('content')
<div x-data="maintenancePage()" x-init="init()" class="px-6 pb-12 max-w-xl">

  <div x-show="loading" class="text-center text-xs text-gray-400 py-10">Loading…</div>

  <div x-show="!loading" class="space-y-4">
    <div class="card p-6">
      <div class="flex items-center justify-between">
        <div>
          <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">System Maintenance Mode</h3>
          <p class="text-xs text-gray-400 mt-1 max-w-sm">
            When enabled, every page and API request is blocked for everyone except an admin who signs in through the
            separate access URL below. Regular staff will only see a maintenance notice.
          </p>
        </div>
        <label class="relative flex-shrink-0 cursor-pointer ml-4" style="width:44px;height:24px">
          <input type="checkbox" x-model="enabled" @change="save()" class="sr-only peer" />
          <div class="w-full h-full rounded-full transition-colors" :style="enabled ? 'background:#E31E24' : 'background:#d1d5db'"></div>
          <div class="absolute top-0.5 left-0.5 w-[18px] h-[18px] bg-white rounded-full shadow transition-transform" :style="enabled ? 'transform:translateX(20px)' : ''"></div>
        </label>
      </div>

      <div x-show="enabled" class="mt-4 flex items-center gap-2 px-3 py-2.5 rounded-xl" style="background:#fef2f2">
        <svg class="w-4 h-4 flex-shrink-0" style="color:#b91c1c" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" d="M11.25 15h1.5"/></svg>
        <span class="text-xs font-semibold" style="color:#b91c1c">Maintenance mode is ON — the system is offline for everyone else right now.</span>
      </div>
    </div>

    <div class="card p-6">
      <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-1">Message Shown to Users</h3>
      <p class="text-xs text-gray-400 mb-3">Displayed on the maintenance page while it's active.</p>
      <textarea x-model="message" rows="3" class="input text-sm" placeholder="OREKS is currently undergoing scheduled maintenance. We'll be back shortly."></textarea>
      <button type="button" @click="save()" :disabled="saving" class="btn-primary mt-3 px-5 py-2.5 rounded-xl text-sm font-bold disabled:opacity-50">
        <span x-show="!saving">Save</span>
        <span x-show="saving">Saving…</span>
      </button>
    </div>

    <div class="card p-6" style="background:#eef2ff">
      <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100 mb-1">Admin Access URL</h3>
      <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
        Bookmark this now — it's the only way in while maintenance mode is on. Anyone can sign in through it, but only
        accounts with the <span class="font-semibold">Manage Maintenance Mode</span> permission (or Super Admin) actually get past the lock.
      </p>
      <div class="flex items-center gap-2">
        <code class="flex-1 text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 truncate" x-text="accessUrl"></code>
        <button type="button" @click="copyUrl()" class="btn-secondary px-3 py-2 rounded-lg text-xs font-semibold flex-shrink-0">Copy</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function maintenancePage() {
  return {
    loading: true,
    saving: false,
    enabled: false,
    message: '',
    accessUrl: '{{ url('/system-access') }}',

    async init() {
      try {
        const r = await apiFetch('/system/maintenance');
        const data = await r.json();
        this.enabled = data.enabled;
        this.message = data.message || '';
      } catch (e) {
        toast(e.message || 'Failed to load maintenance status', 'error');
      } finally {
        this.loading = false;
      }
    },
    async save() {
      this.saving = true;
      try {
        await apiFetch('/system/maintenance', {
          method: 'POST',
          body: JSON.stringify({ enabled: this.enabled, message: this.message }),
        });
        toast(this.enabled ? 'Maintenance mode enabled.' : 'Maintenance mode disabled.', this.enabled ? 'warning' : 'success');
      } catch (e) {
        toast(e.message || 'Failed to save.', 'error');
        this.enabled = !this.enabled;
      } finally {
        this.saving = false;
      }
    },
    copyUrl() {
      navigator.clipboard?.writeText(this.accessUrl);
      toast('Copied to clipboard.', 'success');
    },
  };
}
</script>
@endpush
