<?php $__env->startSection('title', 'Candidates'); ?>
<?php $__env->startSection('page-title', 'Candidates'); ?>
<?php $__env->startSection('page-desc', 'Applicants across all your hiring pipelines'); ?>
<?php $sec = 'hr'; ?>

<?php $__env->startSection('content'); ?>
<div x-data="candidatesPage()" x-init="init()">

    <div class="flex flex-wrap items-center gap-2 mb-4">
        <input x-model="search" type="text" placeholder="Search name, email, phone…" class="input w-full sm:w-64" />
        <select x-model="filterJob" @change="load()" class="input w-auto">
            <option value="">All Job Openings</option>
            <template x-for="j in jobs" :key="j.id"><option :value="j.id" x-text="j.title"></option></template>
        </select>
        <select x-model="filterStatus" @change="load()" class="input w-auto">
            <option value="">All Statuses</option>
            <option value="applied">Applied</option>
            <option value="screening">Screening</option>
            <option value="interview">Interview</option>
            <option value="offer">Offer</option>
            <option value="hired">Hired</option>
            <option value="rejected">Rejected</option>
            <option value="withdrawn">Withdrawn</option>
        </select>
        <div class="flex-1"></div>
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Candidate
        </button>
    </div>

    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/40">
                <tr>
                    <th class="table-hd">Candidate</th>
                    <th class="table-hd">Job Opening</th>
                    <th class="table-hd">Contact</th>
                    <th class="table-hd">Rating</th>
                    <th class="table-hd">Status</th>
                    <th class="table-hd">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                <template x-for="c in filtered" :key="c.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                        <td class="table-td font-medium text-gray-900 dark:text-gray-100" x-text="[c.first_name, c.last_name].filter(Boolean).join(' ')"></td>
                        <td class="table-td text-gray-500" x-text="c.job_opening?.title ?? '—'"></td>
                        <td class="table-td text-gray-500">
                            <div x-text="c.email ?? '—'"></div>
                            <div class="text-xs" x-text="c.phone ?? ''"></div>
                        </td>
                        <td class="table-td" x-text="c.rating ? '★'.repeat(c.rating) : '—'"></td>
                        <td class="table-td">
                            <span class="badge" :class="statusBadge(c.status)" x-text="c.status"></span>
                        </td>
                        <td class="table-td">
                            <a :href="BASE + '/hr/candidates/' + c.id" class="text-indigo-600 hover:underline text-sm font-medium">View</a>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && filtered.length === 0">
                    <td colspan="6" class="text-center text-gray-400 py-16">No candidates found.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- New Candidate Modal -->
    <div x-show="showCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showCreate = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">New Candidate</h3>
                <button @click="showCreate = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="submitCreate()" class="p-6 space-y-4">
                <div>
                    <label class="label">Job Opening</label>
                    <select x-model="form.job_opening_id" class="input w-full">
                        <option value="">— General Application —</option>
                        <template x-for="j in jobs" :key="j.id"><option :value="j.id" x-text="j.title"></option></template>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">First Name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="form.first_name" class="input w-full" required />
                    </div>
                    <div>
                        <label class="label">Last Name</label>
                        <input type="text" x-model="form.last_name" class="input w-full" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Email</label>
                        <input type="email" x-model="form.email" class="input w-full" />
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input type="tel" x-model="form.phone" class="input w-full" />
                    </div>
                </div>
                <div>
                    <label class="label">Source</label>
                    <input type="text" x-model="form.source" class="input w-full" placeholder="e.g. LinkedIn, Referral, Walk-in" />
                </div>
                <div>
                    <label class="label">Resume</label>
                    <input type="file" @change="resumeFile = $event.target.files[0]" accept=".pdf,.doc,.docx" class="input w-full" />
                </div>
                <div>
                    <label class="label">Notes</label>
                    <textarea x-model="form.notes" rows="2" class="input w-full resize-none"></textarea>
                </div>
                <div x-show="createError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="createError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showCreate = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="creating" x-text="creating ? 'Saving…' : 'Add Candidate'"></button>
                </div>
            </form>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function candidatesPage() {
    return {
        candidates: [],
        jobs: [],
        search: '',
        filterJob: '',
        filterStatus: '',
        loading: true,
        showCreate: false,
        creating: false,
        createError: '',
        form: {},
        resumeFile: null,

        get filtered() {
            const q = this.search.toLowerCase();
            if (!q) return this.candidates;
            return this.candidates.filter(c =>
                [c.first_name, c.last_name, c.email, c.phone].filter(Boolean).some(v => v.toLowerCase().includes(q))
            );
        },

        statusBadge(status) {
            const map = { applied: 'badge-gray', screening: 'badge-primary', interview: 'badge-warning', offer: 'badge-primary', hired: 'badge-success', rejected: 'badge-danger', withdrawn: 'badge-gray' };
            return map[status] ?? 'badge-gray';
        },

        async init() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('job_opening_id')) this.filterJob = params.get('job_opening_id');
            try {
                this.jobs = await apiFetch('/hr/job-openings').then(r => r.json());
            } catch (_) {}
            await this.load();
        },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.filterJob) params.set('job_opening_id', this.filterJob);
                if (this.filterStatus) params.set('status', this.filterStatus);
                params.set('per_page', 500);
                const data = await apiFetch('/hr/candidates?' + params.toString()).then(r => r.json());
                this.candidates = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load candidates', 'error');
            } finally {
                this.loading = false;
            }
        },

        openCreate() {
            this.form = { job_opening_id: this.filterJob || '', first_name: '', last_name: '', email: '', phone: '', source: '', notes: '' };
            this.resumeFile = null;
            this.createError = '';
            this.showCreate = true;
        },

        async submitCreate() {
            if (!this.form.first_name) { this.createError = 'First name is required.'; return; }
            this.creating = true;
            this.createError = '';
            try {
                const fd = new FormData();
                Object.entries(this.form).forEach(([k, v]) => { if (v !== '' && v !== null) fd.append(k, v); });
                if (this.resumeFile) fd.append('resume', this.resumeFile);
                const created = await apiFetch('/hr/candidates', { method: 'POST', body: fd }).then(r => r.json());
                toast('Candidate added.', 'success');
                this.showCreate = false;
                window.location.href = BASE + '/hr/candidates/' + created.id;
            } catch (e) {
                this.createError = e.message ?? 'Failed to add candidate.';
            } finally {
                this.creating = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\hr\candidates-index.blade.php ENDPATH**/ ?>