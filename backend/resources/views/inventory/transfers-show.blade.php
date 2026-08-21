@extends('layouts.app')
@section('title', 'Transfer Details')
@section('page-title', 'Transfer Details')
@section('page-desc', 'View stock transfer')

@section('content')
<div x-data="transferShow({{ $id ?? 0 }})" x-init="init()" x-cloak>

  <div class="max-w-3xl mx-auto space-y-5">

    <div class="flex items-center gap-3">
      <a href="{{ url('/inventory/transfers') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
      </a>
      <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100" x-text="t ? (t.reference ?? t.transfer_number ?? '#TRF-' + t.id) : 'Loading…'"></h2>
      <template x-if="t">
        <span :class="statusBadge(t.status)" x-text="t.status ?? 'pending'"></span>
      </template>
    </div>

    <div x-show="loading" class="card p-10 text-center text-gray-400">Loading…</div>

    <template x-if="!loading && t">
      <div class="space-y-5">

        <!-- Details card -->
        <div class="card p-5">
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
              <div class="text-xs text-gray-400 mb-0.5">From Branch</div>
              <div class="font-semibold text-gray-800 dark:text-gray-100" x-text="t.from_branch?.name ?? '—'"></div>
            </div>
            <div>
              <div class="text-xs text-gray-400 mb-0.5">To Branch</div>
              <div class="font-semibold text-gray-800 dark:text-gray-100" x-text="t.to_branch?.name ?? '—'"></div>
            </div>
            <div>
              <div class="text-xs text-gray-400 mb-0.5">Date</div>
              <div class="font-semibold" x-text="fmtDate(t.transfer_date ?? t.created_at)"></div>
            </div>
            <div>
              <div class="text-xs text-gray-400 mb-0.5">Status</div>
              <span :class="statusBadge(t.status)" x-text="t.status ?? 'pending'"></span>
            </div>
          </div>
          <template x-if="t.notes">
            <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400" x-text="t.notes"></div>
          </template>
        </div>

        <!-- Items -->
        <div class="card p-0 overflow-hidden">
          <div class="px-5 py-3 bg-gray-50 dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
            <span class="text-sm font-bold text-gray-700 dark:text-gray-200">Items</span>
          </div>
          <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/40">
              <tr>
                <th class="table-hd text-left">Product</th>
                <th class="table-hd text-right">Quantity</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <template x-for="item in (t.items ?? [])" :key="item.id">
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                  <td class="table-td font-medium text-gray-800 dark:text-gray-100" x-text="item.product?.name ?? item.product_name ?? '—'"></td>
                  <td class="table-td text-right font-bold tabular-nums" x-text="item.quantity"></td>
                </tr>
              </template>
              <tr x-show="!t.items?.length">
                <td colspan="2" class="table-td text-center text-gray-400 py-6">No items</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-3">
          <template x-if="t.status === 'pending' || t.status === 'draft'">
            <button @click="approve()" :disabled="acting"
                    class="btn-primary flex items-center gap-2 disabled:opacity-60">
              <span x-text="acting ? 'Processing…' : 'Approve Transfer'"></span>
            </button>
          </template>
          <template x-if="t.status === 'approved'">
            <button @click="dispatch()" :disabled="acting"
                    class="btn-primary flex items-center gap-2 disabled:opacity-60">
              <span x-text="acting ? 'Processing…' : 'Dispatch'"></span>
            </button>
          </template>
          <template x-if="t.status === 'dispatched'">
            <button @click="receive()" :disabled="acting"
                    class="btn-primary flex items-center gap-2 disabled:opacity-60" style="background:#059669">
              <span x-text="acting ? 'Processing…' : 'Mark Received'"></span>
            </button>
          </template>
        </div>

      </div>
    </template>

  </div>
</div>
@endsection

@push('scripts')
<script>
function transferShow(id) {
  return {
    id, t: null, loading: true, acting: false,

    async init() {
      const r = await apiFetch('/transfers/' + this.id);
      if (!r) return;
      const d = await r.json();
      this.t = d.transfer ?? d;
      this.loading = false;
    },

    statusBadge(s) {
      return 'badge ' + ({ pending:'badge-warning', approved:'badge-primary', dispatched:'badge-warning', completed:'badge-success', cancelled:'badge-danger', draft:'badge-gray' }[s] ?? 'badge-gray');
    },

    async doAction(url, method = 'POST') {
      this.acting = true;
      try {
        const r = await apiFetch(url, { method, headers: { ...authHeaders(), 'Content-Type': 'application/json' } });
        if (!r) return;
        if (!r.ok) { const e = await r.json(); toast(e.message ?? 'Action failed', 'error'); return; }
        await this.init();
        toast('Done', 'success');
      } catch(e) { toast('Action failed', 'error'); }
      finally { this.acting = false; }
    },

    approve()  { this.doAction('/transfers/' + this.id + '/approve'); },
    dispatch() { this.doAction('/transfers/' + this.id + '/dispatch'); },
    receive()  { this.doAction('/transfers/' + this.id + '/receive'); },
  };
}
</script>
@endpush
