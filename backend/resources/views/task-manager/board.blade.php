@extends('layouts.app')
@section('title', 'Task Board')
@section('page-title', 'Task Manager — Task Board')
@section('page-desc', 'Assign, filter and track internal work tasks to completion')

@section('content')
<div x-data="taskBoardPage()" x-init="init()" x-cloak>

    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex gap-2">
            <a href="{{ url('/task-manager') }}" class="btn-secondary text-sm">Dashboard</a>
            <a href="{{ url('/task-manager/categories') }}" class="btn-secondary text-sm">Categories</a>
        </div>
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Task
        </button>
    </div>

    <!-- Filter bar -->
    <div class="card p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="label">Category</label>
                <select x-model="filters.category_id" @change="page=1; load()" class="input w-full">
                    <option value="">All Categories</option>
                    <template x-for="c in categories" :key="c.id">
                        <option :value="c.id" x-text="'—'.repeat(c.depth) + ' ' + c.name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="label">Status</label>
                <select x-model="filters.status" @change="page=1; load()" class="input w-full">
                    <option value="">All Statuses</option>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div>
                <label class="label">Priority</label>
                <select x-model="filters.priority" @change="page=1; load()" class="input w-full">
                    <option value="">All Priorities</option>
                    <option value="Low">Low</option>
                    <option value="Medium">Medium</option>
                    <option value="High">High</option>
                </select>
            </div>
            <div>
                <label class="label">Assignee</label>
                <select x-model="filters.assigned_to" @change="page=1; load()" class="input w-full">
                    <option value="">All Assignees</option>
                    <template x-for="u in users" :key="u.id">
                        <option :value="u.id" x-text="u.name"></option>
                    </template>
                </select>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3 mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
            <div class="flex-1 min-w-[220px]">
                <label class="label">Search</label>
                <input type="text" x-model="filters.search" @input.debounce.400ms="page=1; load()" class="input w-full" placeholder="Search by task title…" />
            </div>
            <label class="flex items-center gap-2 cursor-pointer mt-4">
                <input type="checkbox" x-model="filters.overdue" @change="page=1; load()" class="rounded text-red-600" />
                <span class="text-sm font-medium text-red-600">Overdue only</span>
            </label>
            <button @click="resetFilters()" class="btn-secondary text-sm mt-4 ml-auto">Reset Filters</button>
        </div>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <!-- Table -->
    <div x-show="!loading" class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/40">
                <tr>
                    <th class="table-hd">Task</th>
                    <th class="table-hd">Category</th>
                    <th class="table-hd">Assignee</th>
                    <th class="table-hd text-center">Priority</th>
                    <th class="table-hd text-center">Status</th>
                    <th class="table-hd">Due Date</th>
                    <th class="table-hd text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                <template x-for="task in tasks" :key="task.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                        <td class="table-td">
                            <div class="font-semibold text-gray-900 dark:text-gray-100 cursor-pointer hover:underline" @click="openDetail(task.id)" x-text="task.title"></div>
                            <div class="text-xs text-gray-400 max-w-[260px] truncate" x-show="task.description" x-text="task.description"></div>
                            <div class="text-xs text-gray-400 mt-0.5" x-show="task.followups_count > 0" x-text="task.followups_count + ' update' + (task.followups_count > 1 ? 's' : '')"></div>
                        </td>
                        <td class="table-td">
                            <span x-show="task.category" class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                                  :style="'background:' + (task.category?.color || '#94a3b8') + '22; color:' + (task.category?.color || '#94a3b8')"
                                  x-text="task.category?.name"></span>
                            <span x-show="!task.category" class="text-gray-300 text-xs">—</span>
                        </td>
                        <td class="table-td text-gray-600 dark:text-gray-300" x-text="task.assignee?.name ?? '—'"></td>
                        <td class="table-td text-center">
                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full"
                                  :class="task.priority === 'High' ? 'badge-danger' : (task.priority === 'Medium' ? 'badge-warning' : 'badge-gray')"
                                  x-text="task.priority"></span>
                        </td>
                        <td class="table-td text-center">
                            <select @change="quickStatus(task, $event.target.value)" class="text-[11px] font-semibold px-2 py-1 rounded-full border-0 cursor-pointer"
                                    :class="task.status === 'Completed' ? 'badge-success' : (task.status === 'In Progress' ? 'badge-primary' : (task.status === 'Cancelled' ? 'badge-gray' : 'badge-warning'))">
                                <option value="Pending" :selected="task.status === 'Pending'">Pending</option>
                                <option value="In Progress" :selected="task.status === 'In Progress'">In Progress</option>
                                <option value="Completed" :selected="task.status === 'Completed'">Completed</option>
                                <option value="Cancelled" :selected="task.status === 'Cancelled'">Cancelled</option>
                            </select>
                        </td>
                        <td class="table-td">
                            <span x-show="task.due_date" class="text-xs font-medium" :class="isOverdue(task) ? 'text-red-600' : 'text-gray-600 dark:text-gray-300'" x-text="fmtDate(task.due_date)"></span>
                            <span x-show="!task.due_date" class="text-gray-300 text-xs">—</span>
                        </td>
                        <td class="table-td text-right">
                            <div class="flex items-center justify-end gap-3">
                                <button @click="openDetail(task.id)" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">View</button>
                                <button @click="openEdit(task)" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</button>
                                <button @click="deleteTask(task)" class="text-sm font-medium text-red-500 hover:text-red-700">Delete</button>
                            </div>
                        </td>
                    </tr>
                </template>

                <tr x-show="!loading && tasks.length === 0">
                    <td colspan="7" class="text-center text-gray-400 py-16">No tasks found. Create a task or adjust your filters.</td>
                </tr>
            </tbody>
        </table>

        <!-- Pagination -->
        <div x-show="meta.last_page > 1" class="flex items-center justify-between px-4 py-3 border-t border-gray-100 dark:border-gray-700">
            <span class="text-xs text-gray-400">Showing <span x-text="meta.from"></span>–<span x-text="meta.to"></span> of <span x-text="meta.total"></span></span>
            <div class="flex gap-2">
                <button @click="page--; load()" :disabled="page <= 1" class="btn-secondary text-xs py-1 px-2 disabled:opacity-40">Prev</button>
                <button @click="page++; load()" :disabled="page >= meta.last_page" class="btn-secondary text-xs py-1 px-2 disabled:opacity-40">Next</button>
            </div>
        </div>
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editId ? 'Edit Task' : 'Add New Task'"></h3>
                <button @click="showModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">
                <div>
                    <label class="label">Title <span class="text-red-500">*</span></label>
                    <input x-model="form.title" type="text" class="input w-full" placeholder="e.g. Follow up with supplier on PUR-0021" required />
                </div>
                <div>
                    <label class="label">Description</label>
                    <textarea x-model="form.description" rows="3" class="input w-full resize-none" placeholder="Details about this task…"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Category</label>
                        <select x-model="form.category_id" class="input w-full">
                            <option value="">— No Category —</option>
                            <template x-for="c in categories" :key="c.id">
                                <option :value="c.id" x-text="'—'.repeat(c.depth) + ' ' + c.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="label">Assign To</label>
                        <select x-model="form.assigned_to" class="input w-full">
                            <option value="">— Unassigned —</option>
                            <template x-for="u in users" :key="u.id">
                                <option :value="u.id" x-text="u.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="label">Priority</label>
                        <select x-model="form.priority" class="input w-full">
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Status</label>
                        <select x-model="form.status" class="input w-full">
                            <option value="Pending">Pending</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Due Date</label>
                        <input type="date" x-model="form.due_date" class="input w-full" />
                    </div>
                </div>

                <div x-show="formError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : 'Save Task'"></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Detail / Followup Modal -->
    <div x-show="showDetail" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="closeDetail()">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto" x-show="detailTask">
            <div class="flex items-start justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="detailTask?.title"></h3>
                    <p class="text-xs text-gray-400 mt-1">
                        <span x-show="detailTask?.category" class="text-[11px] font-semibold px-2 py-0.5 rounded-full mr-1"
                              :style="'background:' + (detailTask?.category?.color || '#94a3b8') + '22; color:' + (detailTask?.category?.color || '#94a3b8')"
                              x-text="detailTask?.category?.name"></span>
                        Assigned to <span x-text="detailTask?.assignee?.name ?? 'Unassigned'"></span>
                        <template x-if="detailTask?.due_date"><span> · Due <span x-text="fmtDate(detailTask.due_date)"></span></span></template>
                    </p>
                </div>
                <button @click="closeDetail()" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4" x-show="detailTask?.description" x-text="detailTask?.description"></p>

                <div class="flex flex-wrap gap-2 mb-5">
                    <template x-for="st in ['Pending','In Progress','Completed','Cancelled']" :key="st">
                        <button @click="quickStatus(detailTask, st, true)"
                                class="text-xs font-semibold px-3 py-1.5 rounded-full border transition-colors"
                                :class="detailTask?.status === st ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-500 hover:bg-gray-50'"
                                x-text="st"></button>
                    </template>
                </div>

                <div class="text-xs font-bold uppercase tracking-wide text-gray-400 mb-2">Follow-ups &amp; Activity</div>
                <div class="flex gap-2 mb-4">
                    <input type="text" x-model="newNote" @keydown.enter="addFollowup()" class="input flex-1" placeholder="Add a follow-up note…" />
                    <button @click="addFollowup()" class="btn-primary text-sm">Add</button>
                </div>

                <div class="space-y-2 max-h-64 overflow-y-auto">
                    <template x-for="fu in (detailTask?.followups ?? [])" :key="fu.id">
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg px-3 py-2">
                            <div class="flex justify-between text-xs text-gray-400 mb-1">
                                <span class="font-semibold text-gray-600 dark:text-gray-300" x-text="fu.user?.name ?? 'System'"></span>
                                <span x-text="timeAgo(fu.created_at)"></span>
                            </div>
                            <div class="text-sm text-gray-700 dark:text-gray-200" x-text="fu.note"></div>
                        </div>
                    </template>
                    <p x-show="!(detailTask?.followups ?? []).length" class="text-sm text-gray-400 text-center py-6">No follow-ups yet. Add the first update above.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100 dark:border-gray-700">
                <button @click="closeDetail()" class="btn-secondary">Close</button>
                <button @click="openEdit(detailTask); closeDetail()" class="btn-primary">Edit Task</button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function taskBoardPage() {
    return {
        tasks: [],
        categories: [],
        users: [],
        loading: true,
        page: 1,
        meta: { total: 0, from: 1, to: 0, last_page: 1 },
        filters: { category_id: '', status: '', priority: '', assigned_to: '', overdue: false, search: '' },

        showModal: false,
        editId: null,
        saving: false,
        formError: '',
        form: {},

        showDetail: false,
        detailTask: null,
        newNote: '',

        blank() {
            return { title: '', description: '', category_id: '', assigned_to: '', priority: 'Medium', status: 'Pending', due_date: '' };
        },

        async init() {
            await Promise.all([this.loadCategories(), this.loadUsers()]);
            await this.load();
        },

        async loadCategories() {
            try {
                this.categories = await apiFetch('/work-task-categories').then(r => r.json());
            } catch (e) { /* filter dropdown just stays empty */ }
        },

        async loadUsers() {
            try {
                this.users = await apiFetch('/work-tasks/assignable-users').then(r => r.json());
            } catch (e) { /* dropdown just stays empty */ }
        },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page: this.page, per_page: 15 });
                if (this.filters.category_id) params.set('category_id', this.filters.category_id);
                if (this.filters.status) params.set('status', this.filters.status);
                if (this.filters.priority) params.set('priority', this.filters.priority);
                if (this.filters.assigned_to) params.set('assigned_to', this.filters.assigned_to);
                if (this.filters.overdue) params.set('overdue', '1');
                if (this.filters.search) params.set('search', this.filters.search);
                const data = await apiFetch('/work-tasks?' + params.toString()).then(r => r.json());
                this.tasks = data.data ?? [];
                this.meta = { total: data.total ?? 0, from: data.from ?? 0, to: data.to ?? 0, last_page: data.last_page ?? 1 };
            } catch (e) {
                toast(e.message ?? 'Failed to load tasks', 'error');
            } finally {
                this.loading = false;
            }
        },

        resetFilters() {
            this.filters = { category_id: '', status: '', priority: '', assigned_to: '', overdue: false, search: '' };
            this.page = 1;
            this.load();
        },

        openCreate() {
            this.editId = null;
            this.form = this.blank();
            this.formError = '';
            this.showModal = true;
        },

        openEdit(task) {
            this.editId = task.id;
            this.form = {
                title: task.title ?? '',
                description: task.description ?? '',
                category_id: task.category_id ?? task.category?.id ?? '',
                assigned_to: task.assigned_to ?? task.assignee?.id ?? '',
                priority: task.priority ?? 'Medium',
                status: task.status ?? 'Pending',
                due_date: task.due_date ? task.due_date.slice(0, 10) : '',
            };
            this.formError = '';
            this.showModal = true;
        },

        async save() {
            if (!this.form.title) { toast('Title is required', 'error'); return; }
            this.saving = true;
            this.formError = '';
            try {
                const payload = {
                    ...this.form,
                    category_id: this.form.category_id || null,
                    assigned_to: this.form.assigned_to || null,
                    due_date: this.form.due_date || null,
                };
                const url = this.editId ? '/work-tasks/' + this.editId : '/work-tasks';
                const method = this.editId ? 'PUT' : 'POST';
                await apiFetch(url, { method, body: JSON.stringify(payload) });
                toast(this.editId ? 'Task updated.' : 'Task created.');
                this.showModal = false;
                await this.load();
            } catch (e) {
                this.formError = e.message ?? 'Unexpected error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async deleteTask(task) {
            if (!confirm(`Delete "${task.title}"? This cannot be undone.`)) return;
            try {
                await apiFetch('/work-tasks/' + task.id, { method: 'DELETE' });
                toast('Task deleted.');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to delete task', 'error');
            }
        },

        async quickStatus(task, status, fromDetail = false) {
            if (!task) return;
            try {
                const updated = await apiFetch('/work-tasks/' + task.id + '/status', { method: 'PATCH', body: JSON.stringify({ status }) }).then(r => r.json());
                const idx = this.tasks.findIndex(t => t.id === task.id);
                if (idx >= 0) this.tasks[idx] = updated;
                if (fromDetail) await this.openDetail(task.id);
                toast('Status updated.');
            } catch (e) {
                toast(e.message ?? 'Failed to update status', 'error');
            }
        },

        async openDetail(id) {
            try {
                this.detailTask = await apiFetch('/work-tasks/' + id).then(r => r.json());
                this.newNote = '';
                this.showDetail = true;
            } catch (e) {
                toast(e.message ?? 'Failed to load task', 'error');
            }
        },

        closeDetail() {
            this.showDetail = false;
            this.detailTask = null;
        },

        async addFollowup() {
            if (!this.newNote.trim()) return;
            try {
                await apiFetch('/work-tasks/' + this.detailTask.id + '/followups', { method: 'POST', body: JSON.stringify({ note: this.newNote }) });
                this.newNote = '';
                await this.openDetail(this.detailTask.id);
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to add follow-up', 'error');
            }
        },

        isOverdue(task) {
            return task.due_date && !['Completed', 'Cancelled'].includes(task.status) && new Date(task.due_date) < new Date().setHours(0, 0, 0, 0);
        },

        fmtDate(d) {
            if (!d) return '—';
            return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        },

        timeAgo(d) {
            if (!d) return '';
            const secs = Math.floor((new Date() - new Date(d)) / 1000);
            if (secs < 60) return 'just now';
            const mins = Math.floor(secs / 60); if (mins < 60) return mins + 'm ago';
            const hrs = Math.floor(mins / 60); if (hrs < 24) return hrs + 'h ago';
            const days = Math.floor(hrs / 24); if (days < 30) return days + 'd ago';
            return this.fmtDate(d);
        },
    };
}
</script>
@endpush
