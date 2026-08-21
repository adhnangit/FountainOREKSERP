@extends('layouts.app')
@section('title', 'Tasks')
@section('page-title', 'Task Board')
@section('page-desc', 'Manage team tasks and track progress')

@section('content')
<div x-data="tasksPage()" x-init="init()">

    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex gap-1 flex-wrap">
            <template x-for="tab in statusTabs" :key="tab.key">
                <button @click="statusFilter = tab.key"
                    :class="statusFilter === tab.key ? 'btn-primary' : 'btn-secondary'"
                    class="text-sm" x-text="tab.label"></button>
            </template>
        </div>
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Task
        </button>
    </div>

    <!-- Loading -->
    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <!-- Task Cards -->
    <div x-show="!loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <template x-for="task in filtered" :key="task.id">
            <div class="card p-5 hover:shadow-md transition-shadow">
                <div class="flex items-start justify-between gap-2 mb-3">
                    <div class="font-semibold text-gray-900 text-sm leading-tight" x-text="task.title ?? '—'"></div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span :class="priorityBadge(task.priority)" x-text="task.priority ?? 'medium'" class="capitalize"></span>
                        <button @click="openEdit(task)" title="Edit task" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button @click="deleteTask(task)" title="Delete task" class="text-gray-400 hover:text-red-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mb-3 line-clamp-2" x-text="task.description ?? ''"></p>
                <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span x-text="task.assigned_to?.name ?? task.assigned_to ?? 'Unassigned'"></span>
                    </span>
                    <span class="flex items-center gap-1"
                        :class="task.due_date && new Date(task.due_date) < new Date() && !['done','cancelled'].includes(task.status) ? 'text-red-500' : ''">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span x-text="task.due_date ? fmtDate(task.due_date) : 'No due date'"></span>
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span :class="statusBadge(task.status)" x-text="statusLabel(task.status)"></span>
                    <div class="flex gap-1">
                        <template x-if="!['done','cancelled'].includes(task.status)">
                            <button @click="markComplete(task.id)" class="text-xs text-green-600 hover:underline font-medium">Complete</button>
                        </template>
                        <template x-if="task.status === 'todo'">
                            <button @click="markInProgress(task.id)" class="text-xs text-blue-600 hover:underline font-medium ml-2">Start</button>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <!-- Empty -->
        <div x-show="!loading && filtered.length === 0" class="sm:col-span-2 lg:col-span-3 card p-10 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            No tasks found.
        </div>
    </div>

    <!-- New Task Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="showModal = false"></div>
        <div class="relative card p-6 w-full max-w-md z-10 max-h-[90vh] overflow-y-auto">
            <h3 class="text-base font-semibold text-gray-900 mb-4" x-text="editId ? 'Edit Task' : 'New Task'"></h3>
            <div class="space-y-4">
                <div>
                    <label class="label">Title <span class="text-red-500">*</span></label>
                    <input type="text" x-model="form.title" class="input" placeholder="Task title" />
                </div>
                <div>
                    <label class="label">Description</label>
                    <textarea x-model="form.description" rows="3" class="input" placeholder="Details…"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Priority</label>
                        <select x-model="form.priority" class="input">
                            <option value="low">Low</option>
                            <option value="medium" selected>Medium</option>
                            <option value="high">High</option>
                            <option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Due Date</label>
                        <input type="date" x-model="form.due_date" class="input" />
                    </div>
                </div>
                <div x-show="editId">
                    <label class="label">Status</label>
                    <select x-model="form.status" class="input">
                        <option value="todo">To Do</option>
                        <option value="in_progress">In Progress</option>
                        <option value="on_hold">On Hold</option>
                        <option value="done">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label class="label">Assign To</label>
                    <select x-model="form.assigned_to" class="input">
                        <option value="">Unassigned</option>
                        <template x-for="u in users" :key="u.id">
                            <option :value="u.id" x-text="u.name"></option>
                        </template>
                    </select>
                    <p x-show="!users.length" class="text-xs text-gray-400 mt-1">No active users found.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-5">
                <button @click="showModal = false" class="btn-secondary">Cancel</button>
                <button @click="submit()" :disabled="submitting" class="btn-primary">
                    <span x-text="submitting ? 'Saving…' : (editId ? 'Save Changes' : 'Create Task')"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function tasksPage() {
    return {
        items: [],
        users: [],
        loading: true,
        showModal: false,
        submitting: false,
        editId: null,
        statusFilter: 'all',
        statusTabs: [
            { key: 'all',         label: 'All' },
            { key: 'todo',        label: 'To Do' },
            { key: 'in_progress', label: 'In Progress' },
            { key: 'done',        label: 'Completed' },
        ],
        form: {
            title: '',
            description: '',
            priority: 'medium',
            due_date: '',
            assigned_to: '',
            status: 'todo',
        },
        blank() {
            return { title: '', description: '', priority: 'medium', due_date: '', assigned_to: '', status: 'todo' };
        },
        openCreate() {
            this.editId = null;
            this.form = this.blank();
            this.showModal = true;
        },
        openEdit(task) {
            this.editId = task.id;
            this.form = {
                title: task.title ?? '',
                description: task.description ?? '',
                priority: task.priority ?? 'medium',
                due_date: task.due_date ? task.due_date.slice(0, 10) : '',
                assigned_to: task.assigned_to?.id ?? task.assigned_to ?? '',
                status: task.status ?? 'todo',
            };
            this.showModal = true;
        },
        get filtered() {
            if (this.statusFilter === 'all') return this.items;
            return this.items.filter(t => t.status === this.statusFilter);
        },
        async init() {
            try {
                const data = await apiFetch('/tasks').then(r => r.json());
                this.items = data.data ?? data ?? [];
            } catch (e) {
                toast('Failed to load tasks', 'error');
            } finally {
                this.loading = false;
            }
            try {
                this.users = await apiFetch('/tasks/assignable-users').then(r => r.json());
            } catch (e) { /* dropdown just stays empty if this fails */ }
        },
        async submit() {
            if (!this.form.title) { toast('Title is required', 'error'); return; }
            this.submitting = true;
            try {
                const payload = { ...this.form, assigned_to: this.form.assigned_to ? parseInt(this.form.assigned_to) : null };
                if (!this.editId) delete payload.status; // store() always creates as 'todo' server-side
                const url = this.editId ? '/tasks/' + this.editId : '/tasks';
                const method = this.editId ? 'PATCH' : 'POST';
                const data = await apiFetch(url, { method, body: JSON.stringify(payload) }).then(r => r.json());
                const saved = data.data ?? data;
                if (this.editId) {
                    const idx = this.items.findIndex(t => t.id === this.editId);
                    if (idx >= 0) this.items[idx] = saved;
                    toast('Task updated', 'success');
                } else {
                    this.items.unshift(saved);
                    toast('Task created', 'success');
                }
                this.showModal = false;
                this.editId = null;
                this.form = this.blank();
            } catch (e) {
                toast(e.message ?? 'Failed to save task', 'error');
            } finally {
                this.submitting = false;
            }
        },
        async deleteTask(task) {
            if (!confirm(`Delete "${task.title}"? This cannot be undone.`)) return;
            try {
                await apiFetch('/tasks/' + task.id, { method: 'DELETE' });
                this.items = this.items.filter(t => t.id !== task.id);
                toast('Task deleted', 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to delete task', 'error');
            }
        },
        async markComplete(id) {
            try {
                await apiFetch('/tasks/' + id, { method: 'PATCH', body: JSON.stringify({ status: 'done' }) });
                const idx = this.items.findIndex(t => t.id === id);
                if (idx >= 0) this.items[idx].status = 'done';
                toast('Task completed', 'success');
            } catch (e) {
                toast('Failed to update task', 'error');
            }
        },
        async markInProgress(id) {
            try {
                await apiFetch('/tasks/' + id, { method: 'PATCH', body: JSON.stringify({ status: 'in_progress' }) });
                const idx = this.items.findIndex(t => t.id === id);
                if (idx >= 0) this.items[idx].status = 'in_progress';
                toast('Task started', 'success');
            } catch (e) {
                toast('Failed to update task', 'error');
            }
        },
        priorityBadge(priority) {
            const map = { urgent: 'badge-danger', high: 'badge-danger', medium: 'badge-warning', low: 'badge-success' };
            return 'badge ' + (map[priority] ?? 'badge-gray');
        },
        statusBadge(status) {
            const map = { todo: 'badge-gray', in_progress: 'badge-primary', on_hold: 'badge-warning', done: 'badge-success', cancelled: 'badge-danger' };
            return 'badge ' + (map[status] ?? 'badge-gray');
        },
        statusLabel(status) {
            const map = { todo: 'To Do', in_progress: 'In Progress', on_hold: 'On Hold', done: 'Completed', cancelled: 'Cancelled' };
            return map[status] ?? (status ?? '—');
        },
    };
}
</script>
@endpush
