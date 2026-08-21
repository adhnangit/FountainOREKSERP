@extends('layouts.app')
@section('title', 'Job Openings')
@section('page-title', 'Job Openings')
@section('page-desc', 'Open positions and hiring pipelines')
@php $sec = 'hr'; @endphp

@section('content')
<div x-data="jobOpeningsPage()" x-init="init()" x-cloak>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <select x-model="filterStatus" @change="load()" class="input w-auto">
            <option value="">All Statuses</option>
            <option value="open">Open</option>
            <option value="on_hold">On Hold</option>
            <option value="closed">Closed</option>
            <option value="filled">Filled</option>
        </select>
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Job Opening
        </button>
    </div>

    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/40">
                <tr>
                    <th class="table-hd">Title</th>
                    <th class="table-hd">Department</th>
                    <th class="table-hd">Branch</th>
                    <th class="table-hd text-center">Openings</th>
                    <th class="table-hd text-center">Candidates</th>
                    <th class="table-hd">Status</th>
                    <th class="table-hd">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                <template x-for="j in jobs" :key="j.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                        <td class="table-td font-medium text-gray-900 dark:text-gray-100" x-text="j.title"></td>
                        <td class="table-td text-gray-500" x-text="j.department?.name ?? '—'"></td>
                        <td class="table-td text-gray-500" x-text="j.branch?.name ?? 'All Branches'"></td>
                        <td class="table-td text-center" x-text="j.openings_count"></td>
                        <td class="table-td text-center">
                            <a :href="BASE + '/hr/candidates?job_opening_id=' + j.id" class="text-indigo-600 hover:underline" x-text="j.candidates_count"></a>
                        </td>
                        <td class="table-td">
                            <span class="badge" :class="statusBadge(j.status)" x-text="j.status.replace('_',' ')"></span>
                        </td>
                        <td class="table-td">
                            <div class="flex items-center gap-3">
                                <button @click="openEdit(j)" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</button>
                                <button @click="deleteJob(j)" class="text-sm font-medium text-red-500 hover:text-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                </template>
                <tr x-show="!loading && jobs.length === 0">
                    <td colspan="7" class="text-center text-gray-400 py-16">No job openings yet.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editId ? 'Edit Job Opening' : 'New Job Opening'"></h3>
                <button @click="showModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">
                <div>
                    <label class="label">Title <span class="text-red-500">*</span></label>
                    <input x-model="form.title" type="text" class="input w-full" placeholder="e.g. Sales Executive" required />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Branch</label>
                        <select x-model="form.branch_id" class="input w-full">
                            <option value="">— All Branches —</option>
                            <template x-for="b in branches" :key="b.id"><option :value="b.id" x-text="b.name"></option></template>
                        </select>
                    </div>
                    <div>
                        <label class="label">Department</label>
                        <select x-model="form.department_id" class="input w-full">
                            <option value="">— None —</option>
                            <template x-for="d in flatDepartments" :key="d.id"><option :value="d.id" x-text="d.name"></option></template>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Employment Type</label>
                        <select x-model="form.employment_type" class="input w-full">
                            <option value="full_time">Full-time</option>
                            <option value="part_time">Part-time</option>
                            <option value="contract">Contract</option>
                            <option value="intern">Intern</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Openings</label>
                        <input type="number" min="1" x-model.number="form.openings_count" class="input w-full" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Posted Date</label>
                        <input type="date" x-model="form.posted_date" class="input w-full" />
                    </div>
                    <div>
                        <label class="label">Closing Date</label>
                        <input type="date" x-model="form.closing_date" class="input w-full" />
                    </div>
                </div>
                <div>
                    <label class="label">Description</label>
                    <textarea x-model="form.description" rows="3" class="input w-full resize-none"></textarea>
                </div>
                <div>
                    <label class="label">Requirements</label>
                    <textarea x-model="form.requirements" rows="3" class="input w-full resize-none"></textarea>
                </div>
                <div x-show="editId">
                    <label class="label">Status</label>
                    <select x-model="form.status" class="input w-full">
                        <option value="open">Open</option>
                        <option value="on_hold">On Hold</option>
                        <option value="closed">Closed</option>
                        <option value="filled">Filled</option>
                    </select>
                </div>
                <div x-show="formError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : (editId ? 'Update' : 'Create')"></button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function jobOpeningsPage() {
    return {
        jobs: [],
        branches: [],
        departments: [],
        filterStatus: '',
        loading: true,
        showModal: false,
        editId: null,
        saving: false,
        formError: '',
        form: {},

        get flatDepartments() {
            const flat = [];
            const walk = (list, prefix = '') => list.forEach(d => {
                flat.push({ id: d.id, name: prefix + d.name });
                if (d.children?.length) walk(d.children, prefix + '— ');
            });
            walk(this.departments);
            return flat;
        },

        statusBadge(status) {
            const map = { open: 'badge-success', on_hold: 'badge-warning', closed: 'badge-gray', filled: 'badge-primary' };
            return map[status] ?? 'badge-gray';
        },

        blank() {
            return { branch_id: '', department_id: '', title: '', description: '', requirements: '', employment_type: 'full_time', openings_count: 1, posted_date: new Date().toISOString().slice(0, 10), closing_date: '' };
        },

        async init() {
            try {
                const [bd, dd] = await Promise.all([
                    apiFetch('/branches').then(r => r.json()),
                    apiFetch('/hr/departments').then(r => r.json()),
                ]);
                this.branches = bd.data ?? bd ?? [];
                this.departments = dd ?? [];
            } catch (_) {}
            await this.load();
        },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.filterStatus) params.set('status', this.filterStatus);
                this.jobs = await apiFetch('/hr/job-openings?' + params.toString()).then(r => r.json());
            } catch (e) {
                toast('Failed to load job openings', 'error');
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

        openEdit(j) {
            this.editId = j.id;
            this.form = {
                branch_id: j.branch_id ?? '',
                department_id: j.department_id ?? '',
                title: j.title,
                description: j.description ?? '',
                requirements: j.requirements ?? '',
                employment_type: j.employment_type,
                openings_count: j.openings_count,
                posted_date: j.posted_date?.slice(0, 10) ?? '',
                closing_date: j.closing_date?.slice(0, 10) ?? '',
                status: j.status,
            };
            this.formError = '';
            this.showModal = true;
        },

        async save() {
            this.saving = true;
            this.formError = '';
            try {
                const url = this.editId ? `/hr/job-openings/${this.editId}` : '/hr/job-openings';
                const method = this.editId ? 'PUT' : 'POST';
                await apiFetch(url, { method, body: JSON.stringify(this.form) });
                toast(this.editId ? 'Job opening updated.' : 'Job opening created.');
                this.showModal = false;
                await this.load();
            } catch (e) {
                this.formError = e.message ?? 'Unexpected error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async deleteJob(j) {
            if (!confirm(`Delete "${j.title}"?`)) return;
            try {
                await apiFetch(`/hr/job-openings/${j.id}`, { method: 'DELETE' });
                toast('Job opening deleted.');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Cannot delete job opening.', 'error');
            }
        },
    };
}
</script>
@endpush
