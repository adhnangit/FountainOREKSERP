<?php $__env->startSection('title', 'New Customer'); ?>
<?php $__env->startSection('page-title', 'New Customer'); ?>
<?php $__env->startSection('page-desc', 'Add a new customer account'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="customerCreatePage()" x-init="init()" class="px-6 pb-12">
<form @submit.prevent="submit()">
<div class="flex flex-col lg:flex-row gap-6">

  
  <div class="w-full lg:flex-[62] min-w-0 space-y-5">

    
    <div class="card overflow-hidden">
      <div class="flex items-center gap-3 px-6 py-4"
           style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%)">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0"
             style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
          <svg style="width:18px;height:18px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-white">Contact Details</h3>
          <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">Name, phone, email and address</p>
        </div>
      </div>
      <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">

        <div class="sm:col-span-2">
          <label class="label">Full Name <span class="text-red-500">*</span></label>
          <input type="text" x-model="form.name" class="input" placeholder="Customer name" required />
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
            <input type="email" x-model="form.email" class="input pl-9" placeholder="customer@example.com" />
          </div>
        </div>

        <div class="sm:col-span-2">
          <label class="label">Address</label>
          <textarea x-model="form.address" rows="2" class="input" placeholder="Street address…"></textarea>
        </div>

        
        <div>
          <label class="label">District <span class="text-red-500">*</span></label>
          <select x-model="form.district" @change="form.city = ''" class="input" required>
            <option value="">— Select District —</option>
            <template x-for="d in Object.keys(districtCities)" :key="d">
              <option :value="d" x-text="d"></option>
            </template>
          </select>
        </div>

        <div>
          <label class="label">City <span class="text-red-500">*</span></label>
          <select x-model="form.city" class="input" :disabled="!form.district" required>
            <option value="">— Select City —</option>
            <template x-for="c in (districtCities[form.district] ?? [])" :key="c">
              <option :value="c" x-text="c"></option>
            </template>
          </select>
          <p x-show="!form.district" class="text-xs text-gray-400 mt-1">Select a district first</p>
        </div>

      </div>
    </div>

  </div>

  
  <div class="w-full lg:flex-[38]">
  <div class="lg:sticky lg:top-6 space-y-5">

    
    <div class="card overflow-hidden">
      <div class="flex items-center gap-3 px-5 py-4"
           style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%)">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
             style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
          <svg style="width:16px;height:16px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
          </svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-white">Account Settings</h3>
          <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">Credit & identity details</p>
        </div>
      </div>
      <div class="px-5 py-4 space-y-4">

        <div>
          <label class="label">Branch <span class="text-red-500">*</span></label>
          <select x-model="form.branch_id" class="input" required>
            <option value="">— Select Branch —</option>
            <template x-for="b in branches" :key="b.id">
              <option :value="b.id" x-text="b.name"></option>
            </template>
          </select>
        </div>

        <div>
          <label class="label">Customer Type <span class="text-red-500">*</span></label>
          <select x-model="form.type" class="input" required>
            <option value="individual">Individual</option>
            <option value="hospital">Hospital</option>
            <option value="clinic">Clinic</option>
            <option value="pharmacy">Pharmacy</option>
            <option value="distributor">Distributor</option>
            <option value="other">Other</option>
          </select>
        </div>

        <div>
          <label class="label">Credit Limit (LKR)</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium pointer-events-none">Rs.</span>
            <input type="number" x-model.number="form.credit_limit" class="input pl-9 tabular-nums" min="0" step="1" placeholder="0" />
          </div>
        </div>

        <div>
          <label class="label">Credit Days</label>
          <div class="relative">
            <input type="number" x-model.number="form.payment_terms_days" class="input pr-14 tabular-nums" min="0" step="1" placeholder="30" />
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium pointer-events-none">days</span>
          </div>
        </div>

        <div>
          <label class="label">NIC / Business Reg #</label>
          <input type="text" x-model="form.tax_number" class="input" placeholder="Optional" />
        </div>

      </div>
    </div>

    
    <div class="card overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Preview</h3>
      </div>
      <div class="px-5 py-4 space-y-3">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white text-sm flex-shrink-0"
               style="background:linear-gradient(135deg,#1B3EB6,#0D2272)"
               x-text="form.name ? form.name.charAt(0).toUpperCase() : '?'"></div>
          <div class="min-w-0">
            <div class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate"
                 x-text="form.name || 'Customer Name'"></div>
            <div class="text-xs text-gray-400 truncate"
                 x-text="form.email || form.phone || 'No contact info'"></div>
          </div>
        </div>
        <div class="border-t border-gray-100 dark:border-gray-700 pt-3 grid grid-cols-2 gap-2 text-xs">
          <div>
            <div class="text-gray-400">District</div>
            <div class="font-medium text-gray-700 dark:text-gray-300 mt-0.5" x-text="form.district || '—'"></div>
          </div>
          <div>
            <div class="text-gray-400">City</div>
            <div class="font-medium text-gray-700 dark:text-gray-300 mt-0.5" x-text="form.city || '—'"></div>
          </div>
          <div>
            <div class="text-gray-400">Credit Limit</div>
            <div class="font-medium text-gray-700 dark:text-gray-300 mt-0.5" x-text="form.credit_limit ? 'Rs. ' + Number(form.credit_limit).toLocaleString() : '—'"></div>
          </div>
          <div>
            <div class="text-gray-400">Credit Days</div>
            <div class="font-medium text-gray-700 dark:text-gray-300 mt-0.5" x-text="form.payment_terms_days ? form.payment_terms_days + ' days' : '—'"></div>
          </div>
        </div>
      </div>
    </div>

    
    <div class="flex gap-3">
      <a href="<?php echo e(url('/customers')); ?>" class="btn-secondary flex-1 text-center">Cancel</a>
      <button type="submit" :disabled="submitting" class="btn-primary flex-1">
        <span x-text="submitting ? 'Saving…' : 'Create Customer'"></span>
      </button>
    </div>

  </div>
  </div>

</div>
</form>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function customerCreatePage() {
    return {
        submitting: false,
        branches: [],
        form: {
            branch_id: '',
            type: 'individual',
            name: '',
            phone: '',
            email: '',
            address: '',
            district: '',
            city: '',
            credit_limit: 0,
            payment_terms_days: 30,
            tax_number: '',
        },

        districtCities: {},

        async init() {
            try {
                const bd = await apiFetch('/branches').then(r => r.json());
                this.branches = bd.data ?? bd ?? [];
            } catch (e) {}

            this.districtCities = await loadDistrictCities();

            const u = JSON.parse(localStorage.getItem('medri_user') || '{}');
            const bid = localStorage.getItem('medri_branch');
            this.form.branch_id = (bid && bid !== 'all') ? bid : (u.default_branch_id ?? '');
        },

        async submit() {
            if (!this.form.branch_id) { toast('Please select a branch', 'error'); return; }
            if (!this.form.type)      { toast('Please select a customer type', 'error'); return; }
            if (!this.form.district) { toast('Please select a district', 'error'); return; }
            if (!this.form.city)     { toast('Please select a city', 'error'); return; }
            this.submitting = true;
            try {
                await apiFetch('/customers', { method: 'POST', body: JSON.stringify(this.form) });
                toast('Customer created successfully', 'success');
                window.location.href = BASE + '/customers';
            } catch (e) {
                toast(e.message ?? 'Failed to create customer', 'error');
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\customers\create.blade.php ENDPATH**/ ?>