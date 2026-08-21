@extends('layouts.app')
@section('title', 'Edit Supplier')
@section('page-title', 'Edit Supplier')
@section('page-desc', 'Update supplier account details')

@section('content')
<div x-data="supplierEditPage()" x-init="init()" class="px-6 pb-12">
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
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-white">Contact Details</h3>
          <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">Supplier name, phone, email and address</p>
        </div>
      </div>
      <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

        <div class="sm:col-span-2">
          <label class="label">Supplier Name <span class="text-red-500">*</span></label>
          <input type="text" x-model="form.name" class="input" placeholder="Supplier / Company name" required />
        </div>

        <div>
          <label class="label">Phone</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            </span>
            <input type="tel" x-model="form.phone" class="input pl-9" placeholder="+94 77 000 0000" />
          </div>
        </div>

        <div>
          <label class="label">Email</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </span>
            <input type="email" x-model="form.email" class="input pl-9" placeholder="supplier@example.com" />
          </div>
        </div>

        <div class="sm:col-span-2">
          <label class="label">Address</label>
          <textarea x-model="form.address" rows="2" class="input" placeholder="Street address…"></textarea>
        </div>

        <div>
          <label class="label">City</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
              <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </span>
            <input type="text" x-model="form.city" class="input pl-9" placeholder="City" />
          </div>
        </div>

        <div>
          <label class="label">Contact Person</label>
          <input type="text" x-model="form.contact_person" class="input" placeholder="Name of primary contact" />
        </div>

      </div>
    </div>

  </div>

  {{-- ══ RIGHT COLUMN ══ --}}
  <div class="w-full lg:flex-[38]">
  <div class="lg:sticky lg:top-6 space-y-5">

    <div class="card overflow-hidden">
      <div class="flex items-center gap-3 px-5 py-4"
           style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%)">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
             style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
          <svg style="width:16px;height:16px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-white">Business Details</h3>
          <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">Terms, tax & additional info</p>
        </div>
      </div>
      <div class="px-5 py-4 space-y-4">

        <div>
          <label class="label">Payment Terms</label>
          <select x-model="form.payment_terms" class="input">
            <option value="">Select terms…</option>
            <option value="immediate">Immediate</option>
            <option value="net_7">Net 7 Days</option>
            <option value="net_15">Net 15 Days</option>
            <option value="net_30">Net 30 Days</option>
            <option value="net_60">Net 60 Days</option>
            <option value="net_90">Net 90 Days</option>
          </select>
        </div>

        <div>
          <label class="label">Tax / VAT Number</label>
          <input type="text" x-model="form.tax_number" class="input" placeholder="Optional" />
        </div>

        <div>
          <label class="label">Notes</label>
          <textarea x-model="form.notes" rows="3" class="input resize-none" placeholder="Any additional notes…"></textarea>
        </div>

      </div>
    </div>

    <div class="flex gap-3">
      <a :href="BASE + '/suppliers/' + id" class="btn-secondary flex-1 text-center">Cancel</a>
      <button type="submit" :disabled="submitting" class="btn-primary flex-1">
        <span x-text="submitting ? 'Saving…' : 'Update Supplier'"></span>
      </button>
    </div>

  </div>
  </div>

</div>
</form>
</div>
@endsection

@push('scripts')
<script>
function supplierEditPage() {
    return {
        submitting: false,
        get id() { return window.location.pathname.split('/').filter(Boolean).at(-2); },
        form: {
            name: '', phone: '', email: '', address: '',
            city: '', payment_terms: '', tax_number: '',
            contact_person: '', notes: '',
        },
        async init() {
            try {
                const s = await apiFetch('/suppliers/' + this.id).then(r => r.json());
                const d = s.data ?? s;
                this.form = {
                    name:           d.name           ?? '',
                    phone:          d.phone          ?? '',
                    email:          d.email          ?? '',
                    address:        d.address        ?? '',
                    city:           d.city           ?? '',
                    payment_terms:  d.payment_terms  ?? '',
                    tax_number:     d.tax_number     ?? '',
                    contact_person: d.contact_person ?? '',
                    notes:          d.notes          ?? '',
                };
            } catch (e) {
                toast('Failed to load supplier', 'error');
            }
        },
        async submit() {
            this.submitting = true;
            try {
                await apiFetch('/suppliers/' + this.id, { method: 'PUT', body: JSON.stringify(this.form) });
                toast('Supplier updated', 'success');
                window.location.href = BASE + '/suppliers/' + this.id;
            } catch (e) {
                toast(e.message ?? 'Failed to update supplier', 'error');
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
@endpush
