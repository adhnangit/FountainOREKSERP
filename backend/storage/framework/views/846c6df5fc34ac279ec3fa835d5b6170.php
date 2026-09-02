<?php $__env->startSection('title', 'Candidate Detail'); ?>
<?php $__env->startSection('page-title', 'Candidate Detail'); ?>
<?php $__env->startSection('page-desc', 'Application, interviews and pipeline history'); ?>
<?php $sec = 'hr'; ?>

<?php $__env->startSection('content'); ?>
<style>
.cs-hero{font-family:'Inter',sans-serif;border-radius:18px;background:linear-gradient(135deg,#0f172a,#1e3a5f,#2563eb);box-shadow:0 12px 32px rgba(15,23,42,.18);padding:26px 28px;color:#fff}
.cs-avatar{width:56px;height:56px;border-radius:16px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.22);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:800;flex-shrink:0}
.cs-chip{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.16);padding:3px 11px;border-radius:20px;font-size:11.5px;font-weight:600;display:inline-flex;align-items:center;gap:5px;white-space:nowrap}
.cs-btn{border-radius:10px;padding:7px 14px;font-size:12.5px;font-family:inherit;font-weight:600;background:rgba(255,255,255,.14);color:#fff;border:1px solid rgba(255,255,255,.22);cursor:pointer;display:flex;align-items:center;gap:6px}
.cs-btn:hover{background:rgba(255,255,255,.24)}
.cs-btn-hire{background:#22c55e;border-color:#22c55e}
.cs-btn-hire:hover{background:#16a34a}
</style>
<div x-data="candidateShowPage()" x-init="init()">

    <div x-show="loading" class="flex items-center justify-center py-20">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" x-cloak>
        <a href="<?php echo e(url('/hr/candidates')); ?>" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mb-4 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 12H5M12 5l-7 7 7 7"/></svg>
            Back to Candidates
        </a>

        <div class="cs-hero mb-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4 min-w-0">
                    <div class="cs-avatar" x-text="(c.first_name ?? '?').charAt(0).toUpperCase()"></div>
                    <div class="min-w-0">
                        <h1 class="text-2xl font-bold" x-text="[c.first_name, c.last_name].filter(Boolean).join(' ')"></h1>
                        <div class="flex items-center gap-2 mt-2.5 flex-wrap">
                            <span class="cs-chip" x-text="c.status"></span>
                            <span class="cs-chip" x-show="c.job_opening" x-text="c.job_opening?.title"></span>
                            <span class="cs-chip" x-show="c.email" x-text="c.email"></span>
                            <span class="cs-chip" x-show="c.phone" x-text="c.phone"></span>
                            <span class="cs-chip" x-show="c.rating" x-text="'★'.repeat(c.rating ?? 0)"></span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    <a x-show="c.resume_path" :href="API + '/hr/candidates/' + c.id + '/resume'" target="_blank" class="cs-btn">Resume</a>
                    <a x-show="c.employee" :href="BASE + '/hr/employees/' + c.employee?.id" class="cs-btn" x-text="'View Employee'"></a>
                    <button x-show="!c.employee && canHire" @click="openHire()" class="cs-btn cs-btn-hire">Hire</button>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            <div class="lg:col-span-1 space-y-5">
                <div class="card p-6">
                    <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-3">Pipeline Status</h3>
                    <select x-model="statusForm" @change="changeStatus()" class="input w-full" :disabled="c.status === 'hired'">
                        <option value="applied">Applied</option>
                        <option value="screening">Screening</option>
                        <option value="interview">Interview</option>
                        <option value="offer">Offer</option>
                        <option value="rejected">Rejected</option>
                        <option value="withdrawn">Withdrawn</option>
                        <option value="hired" x-show="c.status === 'hired'">Hired</option>
                    </select>
                    <div class="mt-4" x-show="c.status === 'offer'">
                        <label class="label text-xs">Offered Salary (Rs.)</label>
                        <input type="number" step="0.01" min="0" x-model.number="offerForm.offered_salary" class="input w-full mb-2" />
                        <label class="label text-xs">Offer Date</label>
                        <input type="date" x-model="offerForm.offer_date" class="input w-full mb-2" />
                        <button @click="saveOffer()" class="btn-secondary w-full text-xs">Save Offer Details</button>
                    </div>
                    <div class="mt-4">
                        <label class="label text-xs">Rating</label>
                        <select x-model.number="ratingForm" @change="changeRating()" class="input w-full">
                            <option value="">— None —</option>
                            <option value="1">★</option>
                            <option value="2">★★</option>
                            <option value="3">★★★</option>
                            <option value="4">★★★★</option>
                            <option value="5">★★★★★</option>
                        </select>
                    </div>
                </div>

                <div class="card p-6">
                    <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-3">Notes</h3>
                    <textarea x-model="notesForm" @blur="saveNotes()" rows="4" class="input w-full resize-none" placeholder="Internal notes…"></textarea>
                </div>

                <div class="card p-6" x-show="c.cover_letter">
                    <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mb-2">Cover Letter</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 whitespace-pre-line" x-text="c.cover_letter"></p>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-5">
                <div class="card p-0 overflow-visible rounded-2xl">
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Interviews</h3>
                        <button @click="showInterviewModal = true" class="btn-primary text-xs px-3 py-1.5">Schedule Interview</button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="ed-table min-w-full" style="width:100%">
                            <thead><tr><th class="table-hd">When</th><th class="table-hd">Mode</th><th class="table-hd">Interviewer</th><th class="table-hd">Status</th><th class="table-hd">Feedback</th></tr></thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                                <template x-for="iv in (c.interviews ?? [])" :key="iv.id">
                                    <tr>
                                        <td class="table-td" x-text="fmtDateTime(iv.scheduled_at)"></td>
                                        <td class="table-td capitalize" x-text="iv.mode.replace('_',' ')"></td>
                                        <td class="table-td" x-text="iv.interviewer?.name ?? '—'"></td>
                                        <td class="table-td">
                                            <select x-model="iv.status" @change="updateInterview(iv)" class="input text-xs w-auto">
                                                <option value="scheduled">Scheduled</option>
                                                <option value="completed">Completed</option>
                                                <option value="cancelled">Cancelled</option>
                                                <option value="no_show">No Show</option>
                                            </select>
                                        </td>
                                        <td class="table-td">
                                            <input type="text" x-model="iv.feedback" @blur="updateInterview(iv)" class="input text-xs w-full" placeholder="Add feedback…" />
                                        </td>
                                    </tr>
                                </template>
                                <tr x-show="!(c.interviews?.length)"><td colspan="5" class="text-center text-gray-400 py-10">No interviews scheduled.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card p-0 overflow-visible rounded-2xl">
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                        <h3 class="text-xs font-semibold uppercase text-gray-400 tracking-wider">Pipeline History</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="ed-table min-w-full" style="width:100%">
                            <thead><tr><th class="table-hd">Date</th><th class="table-hd">From</th><th class="table-hd">To</th><th class="table-hd">By</th></tr></thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/40">
                                <template x-for="h in (c.status_history ?? [])" :key="h.id">
                                    <tr>
                                        <td class="table-td" x-text="fmtDate(h.created_at)"></td>
                                        <td class="table-td" x-text="h.old_status ?? '—'"></td>
                                        <td class="table-td font-medium" x-text="h.new_status"></td>
                                        <td class="table-td" x-text="h.changed_by?.name ?? '—'"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Interview Modal -->
    <div x-show="showInterviewModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showInterviewModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Schedule Interview</h3>
                <button @click="showInterviewModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="label">Date &amp; Time <span class="text-red-500">*</span></label>
                    <input type="datetime-local" x-model="interviewForm.scheduled_at" class="input w-full" />
                </div>
                <div>
                    <label class="label">Mode</label>
                    <select x-model="interviewForm.mode" class="input w-full">
                        <option value="in_person">In Person</option>
                        <option value="phone">Phone</option>
                        <option value="video">Video</option>
                    </select>
                </div>
                <div>
                    <label class="label">Interviewer</label>
                    <select x-model="interviewForm.interviewer_id" class="input w-full">
                        <option value="">— None —</option>
                        <template x-for="u in users" :key="u.id"><option :value="u.id" x-text="u.name"></option></template>
                    </select>
                </div>
                <div>
                    <label class="label">Location / Link</label>
                    <input type="text" x-model="interviewForm.location_or_link" class="input w-full" />
                </div>
                <div x-show="interviewError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="interviewError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showInterviewModal = false" class="btn-secondary">Cancel</button>
                    <button @click="scheduleInterview()" class="btn-primary" :disabled="interviewSaving" x-text="interviewSaving ? 'Saving…' : 'Schedule'"></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Hire Modal -->
    <div x-show="showHireModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showHireModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Hire Candidate</h3>
                <button @click="showHireModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-500">This creates a new employee record for <span class="font-semibold" x-text="[c.first_name, c.last_name].filter(Boolean).join(' ')"></span> and marks this candidate as hired.</p>
                <div>
                    <label class="label">Branch</label>
                    <select x-model="hireForm.branch_id" class="input w-full">
                        <option value="">— None —</option>
                        <template x-for="b in branches" :key="b.id"><option :value="b.id" x-text="b.name"></option></template>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Department</label>
                        <select x-model="hireForm.department_id" class="input w-full">
                            <option value="">— None —</option>
                            <template x-for="d in flatDepartments" :key="d.id"><option :value="d.id" x-text="d.name"></option></template>
                        </select>
                    </div>
                    <div>
                        <label class="label">Designation</label>
                        <select x-model="hireForm.designation_id" class="input w-full">
                            <option value="">— None —</option>
                            <template x-for="d in designations" :key="d.id"><option :value="d.id" x-text="d.name"></option></template>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Join Date <span class="text-red-500">*</span></label>
                        <input type="date" x-model="hireForm.join_date" class="input w-full" required />
                    </div>
                    <div>
                        <label class="label">Basic Salary (Rs.)</label>
                        <input type="number" step="0.01" min="0" x-model.number="hireForm.basic_salary" class="input w-full" />
                    </div>
                </div>
                <div x-show="hireError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="hireError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showHireModal = false" class="btn-secondary">Cancel</button>
                    <button @click="submitHire()" class="btn-primary" :disabled="hiring" x-text="hiring ? 'Hiring…' : 'Confirm Hire'"></button>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function candidateShowPage() {
    return {
        loading: true,
        c: {},
        user: JSON.parse(localStorage.getItem('medri_user') || '{}'),
        statusForm: '',
        ratingForm: '',
        notesForm: '',
        offerForm: { offered_salary: '', offer_date: '' },
        branches: [],
        departments: [],
        designations: [],
        users: [],
        showInterviewModal: false,
        interviewSaving: false,
        interviewError: '',
        interviewForm: {},
        showHireModal: false,
        hiring: false,
        hireError: '',
        hireForm: {},

        get id() { return window.location.pathname.split('/').filter(Boolean).pop(); },
        get canHire() { return (this.user.permissions ?? []).includes('hr.candidates.hire'); },
        fmtDateTime(d) {
            if (!d) return '—';
            return new Date(d).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
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

        async init() {
            try {
                const [cd, bd, dd, gd, ud] = await Promise.all([
                    apiFetch('/hr/candidates/' + this.id).then(r => r.json()),
                    apiFetch('/branches').then(r => r.json()),
                    apiFetch('/hr/departments').then(r => r.json()),
                    apiFetch('/hr/designations').then(r => r.json()),
                    apiFetch('/users?per_page=200').then(r => r.json()).catch(() => ({ data: [] })),
                ]);
                this.c = cd;
                this.statusForm = cd.status;
                this.ratingForm = cd.rating ?? '';
                this.notesForm = cd.notes ?? '';
                this.offerForm = { offered_salary: cd.offered_salary ?? '', offer_date: cd.offer_date?.slice(0, 10) ?? '' };
                this.branches = bd.data ?? bd ?? [];
                this.departments = dd ?? [];
                this.designations = gd ?? [];
                this.users = ud.data ?? ud ?? [];
            } catch (e) {
                toast('Failed to load candidate', 'error');
            } finally {
                this.loading = false;
            }
        },

        async reload() {
            this.c = await apiFetch('/hr/candidates/' + this.id).then(r => r.json());
        },

        async changeStatus() {
            try {
                await apiFetch('/hr/candidates/' + this.id, { method: 'PUT', body: JSON.stringify({ status: this.statusForm }) });
                toast('Status updated.', 'success');
                await this.reload();
            } catch (e) {
                toast(e.message ?? 'Failed to update status', 'error');
                this.statusForm = this.c.status;
            }
        },

        async changeRating() {
            try {
                await apiFetch('/hr/candidates/' + this.id, { method: 'PUT', body: JSON.stringify({ rating: this.ratingForm || null }) });
                toast('Rating updated.', 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to update rating', 'error');
            }
        },

        async saveNotes() {
            try {
                await apiFetch('/hr/candidates/' + this.id, { method: 'PUT', body: JSON.stringify({ notes: this.notesForm }) });
            } catch (e) {
                toast('Failed to save notes', 'error');
            }
        },

        async saveOffer() {
            try {
                await apiFetch('/hr/candidates/' + this.id, { method: 'PUT', body: JSON.stringify(this.offerForm) });
                toast('Offer details saved.', 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to save offer', 'error');
            }
        },

        async scheduleInterview() {
            if (!this.interviewForm.scheduled_at) { this.interviewError = 'Date & time is required.'; return; }
            this.interviewSaving = true;
            this.interviewError = '';
            try {
                await apiFetch('/hr/candidates/' + this.id + '/interviews', { method: 'POST', body: JSON.stringify(this.interviewForm) });
                toast('Interview scheduled.', 'success');
                this.showInterviewModal = false;
                this.interviewForm = {};
                await this.reload();
            } catch (e) {
                this.interviewError = e.message ?? 'Failed to schedule interview.';
            } finally {
                this.interviewSaving = false;
            }
        },

        async updateInterview(iv) {
            try {
                await apiFetch('/hr/candidate-interviews/' + iv.id, {
                    method: 'PUT',
                    body: JSON.stringify({ status: iv.status, feedback: iv.feedback }),
                });
                toast('Interview updated.', 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to update interview', 'error');
            }
        },

        openHire() {
            this.hireForm = {
                branch_id: this.c.job_opening?.branch_id ?? '',
                department_id: this.c.job_opening?.department_id ?? '',
                designation_id: this.c.job_opening?.designation_id ?? '',
                join_date: new Date().toISOString().slice(0, 10),
                basic_salary: this.c.offered_salary ?? '',
            };
            this.hireError = '';
            this.showHireModal = true;
        },

        async submitHire() {
            if (!this.hireForm.join_date) { this.hireError = 'Join date is required.'; return; }
            this.hiring = true;
            this.hireError = '';
            try {
                const res = await apiFetch('/hr/candidates/' + this.id + '/hire', { method: 'POST', body: JSON.stringify(this.hireForm) }).then(r => r.json());
                toast('Candidate hired.', 'success');
                window.location.href = BASE + '/hr/employees/' + res.employee.id;
            } catch (e) {
                this.hireError = e.message ?? 'Failed to hire candidate.';
            } finally {
                this.hiring = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\hr\candidates-show.blade.php ENDPATH**/ ?>