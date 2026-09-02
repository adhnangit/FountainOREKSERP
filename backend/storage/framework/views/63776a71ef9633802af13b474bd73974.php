<?php $__env->startSection('title', 'New Employee'); ?>
<?php $__env->startSection('page-title', 'New Employee'); ?>
<?php $__env->startSection('page-desc', 'Add a new staff record'); ?>
<?php $sec = 'hr'; ?>

<?php $__env->startSection('content'); ?>
<div x-data="employeeCreatePage()" x-init="init()" class="px-6 pb-12">
<form @submit.prevent="submit()">
<div class="flex flex-col lg:flex-row gap-6">

  
  <div class="w-full lg:flex-[62] min-w-0 space-y-5">

    
    <div class="card overflow-hidden">
      <div class="flex items-center gap-3 px-6 py-4" style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%)">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
          <svg style="width:18px;height:18px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-white">Personal Information</h3>
          <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">Name, birth date and identification</p>
        </div>
      </div>
      <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="label">First Name <span class="text-red-500">*</span></label>
          <input type="text" x-model="form.first_name" class="input" required />
        </div>
        <div>
          <label class="label">Last Name</label>
          <input type="text" x-model="form.last_name" class="input" />
        </div>
        <div>
          <label class="label">Date of Birth</label>
          <input type="date" x-model="form.date_of_birth" class="input" />
        </div>
        <div>
          <label class="label">Gender</label>
          <select x-model="form.gender" class="input">
            <option value="">— Select —</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label class="label">Marital Status</label>
          <select x-model="form.marital_status" class="input">
            <option value="">— Select —</option>
            <option value="single">Single</option>
            <option value="married">Married</option>
            <option value="divorced">Divorced</option>
            <option value="widowed">Widowed</option>
          </select>
        </div>
        <div>
          <label class="label">NIC / Passport #</label>
          <input type="text" x-model="form.nic_passport" class="input" />
        </div>
        <div>
          <label class="label">Nationality</label>
          <input type="text" x-model="form.nationality" class="input" placeholder="e.g. Sri Lankan" />
        </div>
      </div>
    </div>

    
    <div class="card overflow-hidden">
      <div class="flex items-center gap-3 px-6 py-4" style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%)">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
          <svg style="width:18px;height:18px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-white">Contact Information</h3>
          <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">Phone, email and address</p>
        </div>
      </div>
      <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="label">Phone</label>
          <input type="tel" x-model="form.phone" class="input" placeholder="+94 77 000 0000" />
        </div>
        <div>
          <label class="label">Alternate Phone</label>
          <input type="tel" x-model="form.phone2" class="input" />
        </div>
        <div class="sm:col-span-2">
          <label class="label">Personal Email</label>
          <input type="email" x-model="form.personal_email" class="input" />
        </div>
        <div class="sm:col-span-2">
          <label class="label">Address</label>
          <textarea x-model="form.address" rows="2" class="input"></textarea>
        </div>
        <div>
          <label class="label">City</label>
          <input type="text" x-model="form.city" class="input" />
        </div>
        <div>
          <label class="label">District</label>
          <input type="text" x-model="form.district" class="input" />
        </div>
      </div>
    </div>

    
    <div class="card overflow-hidden">
      <div class="flex items-center gap-3 px-6 py-4" style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%)">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
          <svg style="width:18px;height:18px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-white">Emergency Contact</h3>
          <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">Who to contact in an emergency</p>
        </div>
      </div>
      <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="label">Name</label>
          <input type="text" x-model="form.emergency_contact_name" class="input" />
        </div>
        <div>
          <label class="label">Relationship</label>
          <input type="text" x-model="form.emergency_contact_relationship" class="input" placeholder="e.g. Spouse" />
        </div>
        <div>
          <label class="label">Phone</label>
          <input type="tel" x-model="form.emergency_contact_phone" class="input" />
        </div>
      </div>
    </div>

    
    <div class="card overflow-hidden">
      <div class="flex items-center gap-3 px-6 py-4" style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%)">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
          <svg style="width:18px;height:18px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M5 6l7-3 7 3M4 10v9m4-9v9m4-9v9m4-9v9m4-9v9M3 19h18"/></svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-white">Bank Details</h3>
          <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">For payroll — used by a future Payroll module</p>
        </div>
      </div>
      <div class="px-6 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="label">Bank Name</label>
          <input type="text" x-model="form.bank_name" class="input" />
        </div>
        <div>
          <label class="label">Branch</label>
          <input type="text" x-model="form.bank_branch" class="input" />
        </div>
        <div>
          <label class="label">Account Name</label>
          <input type="text" x-model="form.bank_account_name" class="input" />
        </div>
        <div>
          <label class="label">Account Number</label>
          <input type="text" x-model="form.bank_account_number" class="input" />
        </div>
      </div>
    </div>

  </div>

  
  <div class="w-full lg:flex-[38]">
  <div class="lg:sticky lg:top-6 space-y-5">

    
    <div class="card overflow-hidden">
      <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Photo</h3>
      </div>
      <div class="px-5 py-5 flex items-center gap-4">
        <img x-show="photoPreview" :src="photoPreview" class="w-16 h-16 rounded-xl object-cover flex-shrink-0" />
        <div x-show="!photoPreview" class="w-16 h-16 rounded-xl flex items-center justify-center font-bold text-white text-xl flex-shrink-0"
             style="background:linear-gradient(135deg,#0f4c81,#1a7abf)"
             x-text="form.first_name ? form.first_name.charAt(0).toUpperCase() : '?'"></div>
        <div>
          <input type="file" accept="image/*" @change="onPhotoChange($event)" class="text-xs" />
          <p class="text-[11px] text-gray-400 mt-1">JPG, PNG, GIF or WEBP. Max 2MB.</p>
        </div>
      </div>
    </div>

    
    <div class="card overflow-hidden">
      <div class="flex items-center gap-3 px-5 py-4" style="background:linear-gradient(135deg,#1B3EB6 0%,#0D2272 100%)">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2)">
          <svg style="width:16px;height:16px;color:#fff" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        </div>
        <div>
          <h3 class="text-sm font-bold text-white">Employment Details</h3>
          <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65)">Placement, role and join date</p>
        </div>
      </div>
      <div class="px-5 py-4 space-y-4">
        <div>
          <label class="label">Branch</label>
          <select x-model="form.branch_id" class="input">
            <option value="">— None —</option>
            <template x-for="b in branches" :key="b.id"><option :value="b.id" x-text="b.name"></option></template>
          </select>
        </div>
        <div>
          <label class="label">Department</label>
          <select x-model="form.department_id" @change="form.designation_id=''" class="input">
            <option value="">— None —</option>
            <template x-for="d in flatDepartments" :key="d.id"><option :value="d.id" x-text="d.name"></option></template>
          </select>
        </div>
        <div>
          <label class="label">Designation</label>
          <select x-model="form.designation_id" class="input">
            <option value="">— None —</option>
            <template x-for="d in filteredDesignations" :key="d.id"><option :value="d.id" x-text="d.name"></option></template>
          </select>
        </div>
        <div>
          <label class="label">Reporting Manager</label>
          <select x-model="form.reporting_manager_id" class="input">
            <option value="">— None —</option>
            <template x-for="m in employees" :key="m.id"><option :value="m.id" x-text="[m.first_name, m.last_name].filter(Boolean).join(' ')"></option></template>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label">Employment Type</label>
            <select x-model="form.employment_type" class="input">
              <option value="full_time">Full-time</option>
              <option value="part_time">Part-time</option>
              <option value="contract">Contract</option>
              <option value="intern">Intern</option>
            </select>
          </div>
          <div>
            <label class="label">Join Date <span class="text-red-500">*</span></label>
            <input type="date" x-model="form.join_date" class="input" required />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label">Probation (months)</label>
            <input type="number" x-model.number="form.probation_period_months" min="0" class="input" />
          </div>
          <div>
            <label class="label">Status</label>
            <select x-model="form.employment_status" class="input">
              <option value="active">Active</option>
              <option value="on_leave">On Leave</option>
              <option value="suspended">Suspended</option>
              <option value="terminated">Terminated</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label">Basic Salary (Rs.)</label>
            <input type="number" step="0.01" min="0" x-model.number="form.basic_salary" class="input" placeholder="Optional" />
          </div>
          <div class="flex items-end pb-2.5">
            <label class="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" x-model="form.epf_etf_applicable" class="rounded text-indigo-600" />
              <span class="text-sm text-gray-700 dark:text-gray-300">EPF/ETF applicable</span>
            </label>
          </div>
        </div>
        <div>
          <label class="label">Notes</label>
          <textarea x-model="form.notes" rows="2" class="input resize-none"></textarea>
        </div>
      </div>
    </div>

    
    <div class="flex gap-3">
      <a href="<?php echo e(url('/hr/employees')); ?>" class="btn-secondary flex-1 text-center">Cancel</a>
      <button type="submit" :disabled="submitting" class="btn-primary flex-1">
        <span x-text="submitting ? 'Saving…' : 'Create Employee'"></span>
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
function employeeCreatePage() {
    return {
        submitting: false,
        branches: [],
        departments: [],
        designations: [],
        employees: [],
        photoFile: null,
        photoPreview: null,
        form: {
            first_name: '', last_name: '', date_of_birth: '', gender: '', marital_status: '',
            nic_passport: '', nationality: '',
            phone: '', phone2: '', personal_email: '', address: '', city: '', district: '',
            emergency_contact_name: '', emergency_contact_relationship: '', emergency_contact_phone: '',
            bank_name: '', bank_branch: '', bank_account_name: '', bank_account_number: '',
            branch_id: '', department_id: '', designation_id: '', reporting_manager_id: '',
            employment_type: 'full_time', join_date: new Date().toISOString().slice(0, 10),
            probation_period_months: '', employment_status: 'active', notes: '',
            basic_salary: '', epf_etf_applicable: true,
        },

        get flatDepartments() {
            const flat = [];
            const walk = (list, prefix = '') => list.forEach(d => {
                flat.push({ id: d.id, name: prefix + d.name });
                if (d.children?.length) walk(d.children, prefix + '— ');
            });
            walk(this.departments);
            return flat;
        },
        get filteredDesignations() {
            if (!this.form.department_id) return this.designations;
            return this.designations.filter(d => d.department_id == this.form.department_id);
        },

        onPhotoChange(e) {
            const file = e.target.files[0];
            if (!file) return;
            this.photoFile = file;
            this.photoPreview = URL.createObjectURL(file);
        },

        async init() {
            try {
                const [bd, dd, gd, ed] = await Promise.all([
                    apiFetch('/branches').then(r => r.json()),
                    apiFetch('/hr/departments').then(r => r.json()),
                    apiFetch('/hr/designations').then(r => r.json()),
                    apiFetch('/hr/employees?per_page=500').then(r => r.json()),
                ]);
                this.branches = bd.data ?? bd ?? [];
                this.departments = dd ?? [];
                this.designations = gd ?? [];
                this.employees = ed.data ?? ed ?? [];
                const u = JSON.parse(localStorage.getItem('medri_user') || '{}');
                const stored = localStorage.getItem('medri_branch');
                const bid = (stored && stored !== 'all') ? stored : u.default_branch_id;
                if (bid) this.form.branch_id = bid;
            } catch (_) {}
        },

        async submit() {
            if (!this.form.first_name) { toast('First name is required', 'error'); return; }
            if (!this.form.join_date) { toast('Join date is required', 'error'); return; }
            this.submitting = true;
            try {
                const fd = new FormData();
                Object.entries(this.form).forEach(([k, v]) => {
                    if (v === '' || v === null) return;
                    fd.append(k, typeof v === 'boolean' ? (v ? '1' : '0') : v);
                });
                if (this.photoFile) fd.append('photo', this.photoFile);
                const r = await apiFetch('/hr/employees', { method: 'POST', body: fd });
                const created = await r.json();
                toast('Employee created successfully', 'success');
                window.location.href = BASE + '/hr/employees/' + created.id;
            } catch (e) {
                toast(e.message ?? 'Failed to create employee', 'error');
            } finally {
                this.submitting = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\hr\employees-create.blade.php ENDPATH**/ ?>