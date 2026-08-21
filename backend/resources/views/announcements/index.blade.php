@extends('layouts.app')
@section('title', 'Announcements')
@section('page-title', 'Announcements')
@section('page-desc', 'Company news and updates')

@section('content')
<div x-data="announcementsPage()" x-init="init()">

    <div class="flex justify-end mb-6" x-show="canManage">
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Announcement
        </button>
    </div>

    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" class="space-y-4 max-w-3xl">
        <template x-for="a in announcements" :key="a.id">
            <div class="card p-6" :class="!a.is_read ? 'ring-2 ring-indigo-200 dark:ring-indigo-800' : ''" @mouseenter="markRead(a)">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="font-semibold text-gray-900 dark:text-gray-100" x-text="a.title"></h3>
                            <span class="badge badge-warning" x-show="a.is_pinned">Pinned</span>
                            <span class="badge badge-primary" x-show="!a.is_read">New</span>
                        </div>
                        <div class="text-xs text-gray-400 mt-1" x-text="(a.created_by?.name ?? 'System') + ' — ' + fmtDate(a.published_at)"></div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0" x-show="canManage">
                        <button @click="openEdit(a)" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Edit</button>
                        <button @click="deleteAnnouncement(a)" class="text-sm font-medium text-red-500 hover:text-red-700">Delete</button>
                    </div>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300 mt-3 whitespace-pre-line" x-text="a.body"></p>
            </div>
        </template>
        <div x-show="announcements.length === 0" class="text-center text-gray-400 py-16">No announcements yet.</div>
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editId ? 'Edit Announcement' : 'New Announcement'"></h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">
                <div>
                    <label class="label">Title <span class="text-red-500">*</span></label>
                    <input x-model="form.title" type="text" class="input w-full" required />
                </div>
                <div>
                    <label class="label">Body <span class="text-red-500">*</span></label>
                    <textarea x-model="form.body" rows="5" class="input w-full resize-none" required></textarea>
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
                        <label class="label">Expires</label>
                        <input type="date" x-model="form.expires_at" class="input w-full" />
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="form.is_pinned" class="rounded text-indigo-600" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Pin to top</span>
                </label>
                <div x-show="formError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Publishing…' : (editId ? 'Update' : 'Publish')"></button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function announcementsPage() {
    return {
        announcements: [],
        branches: [],
        loading: true,
        showModal: false,
        editId: null,
        saving: false,
        formError: '',
        form: {},

        get canManage() {
            const user = JSON.parse(localStorage.getItem('medri_user') || '{}');
            return (user.permissions ?? []).includes('hr.announcements.manage');
        },

        async init() {
            try {
                const bd = await apiFetch('/branches').then(r => r.json());
                this.branches = bd.data ?? bd ?? [];
            } catch (_) {}
            await this.load();
        },

        async load() {
            this.loading = true;
            try {
                this.announcements = await apiFetch('/announcements').then(r => r.json());
            } catch (e) {
                toast('Failed to load announcements', 'error');
            } finally {
                this.loading = false;
            }
        },

        async markRead(a) {
            if (a.is_read) return;
            a.is_read = true;
            try {
                await apiFetch('/announcements/' + a.id + '/read', { method: 'POST', body: JSON.stringify({}) });
            } catch (_) {}
        },

        openCreate() {
            this.editId = null;
            this.form = { title: '', body: '', branch_id: '', expires_at: '', is_pinned: false };
            this.formError = '';
            this.showModal = true;
        },

        openEdit(a) {
            this.editId = a.id;
            this.form = { title: a.title, body: a.body, branch_id: a.branch_id ?? '', expires_at: a.expires_at?.slice(0, 10) ?? '', is_pinned: a.is_pinned };
            this.formError = '';
            this.showModal = true;
        },

        async save() {
            this.saving = true;
            this.formError = '';
            try {
                const url = this.editId ? `/hr/announcements/${this.editId}` : '/hr/announcements';
                const method = this.editId ? 'PUT' : 'POST';
                await apiFetch(url, { method, body: JSON.stringify(this.form) });
                toast(this.editId ? 'Announcement updated.' : 'Announcement published.');
                this.showModal = false;
                await this.load();
            } catch (e) {
                this.formError = e.message ?? 'Unexpected error.';
            } finally {
                this.saving = false;
            }
        },

        async deleteAnnouncement(a) {
            if (!confirm(`Delete "${a.title}"?`)) return;
            try {
                await apiFetch(`/hr/announcements/${a.id}`, { method: 'DELETE' });
                toast('Announcement deleted.');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Cannot delete announcement.', 'error');
            }
        },
    };
}
</script>
@endpush
