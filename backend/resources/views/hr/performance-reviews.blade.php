@extends('layouts.app')
@section('title', 'Performance Reviews')
@section('page-title', 'Performance Reviews')
@section('page-desc', 'Ratings and feedback across your review cycles')
@php $sec = 'hr'; @endphp

@section('content')
<div x-data="performanceReviewsPage()" x-init="init()">

    <div class="flex flex-wrap items-center gap-2 mb-4">
        <select x-model="filterCycle" @change="load()" class="input w-auto">
            <option value="">All Cycles</option>
            <template x-for="c in cycles" :key="c.id"><option :value="c.id" x-text="c.name"></option></template>
        </select>
        <select x-model="filterStatus" @change="load()" class="input w-auto">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
        </select>
    </div>

    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" class="card overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800/40">
                <tr>
                    <th class="table-hd">Employee</th>
                    <th class="table-hd">Cycle</th>
                    <th class="table-hd">Reviewer</th>
                    <th class="table-hd">Rating</th>
                    <th class="table-hd">Status</th>
                    <th class="table-hd">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                <template x-for="r in reviews" :key="r.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                        <td class="table-td font-medium text-gray-900 dark:text-gray-100" x-text="[r.employee?.first_name, r.employee?.last_name].filter(Boolean).join(' ')"></td>
                        <td class="table-td text-gray-500" x-text="r.cycle?.name"></td>
                        <td class="table-td text-gray-500" x-text="r.reviewer?.name ?? '—'"></td>
                        <td class="table-td" x-text="r.overall_rating ? '★'.repeat(r.overall_rating) : '—'"></td>
                        <td class="table-td"><span class="badge" :class="statusBadge(r.status)" x-text="r.status.replace('_',' ')"></span></td>
                        <td class="table-td"><button @click="openReview(r)" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Open</button></td>
                    </tr>
                </template>
                <tr x-show="!loading && reviews.length === 0">
                    <td colspan="6" class="text-center text-gray-400 py-16">No reviews found.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Review Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="[active.employee?.first_name, active.employee?.last_name].filter(Boolean).join(' ') + ' — ' + active.cycle?.name"></h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="label">Overall Rating</label>
                    <select x-model.number="editForm.overall_rating" class="input w-full">
                        <option value="">— None —</option>
                        <option value="1">★ (1)</option>
                        <option value="2">★★ (2)</option>
                        <option value="3">★★★ (3)</option>
                        <option value="4">★★★★ (4)</option>
                        <option value="5">★★★★★ (5)</option>
                    </select>
                </div>
                <div>
                    <label class="label">Reviewer Comments</label>
                    <textarea x-model="editForm.reviewer_comments" rows="3" class="input w-full resize-none"></textarea>
                </div>
                <div>
                    <label class="label">Employee Comments</label>
                    <textarea x-model="editForm.employee_comments" rows="3" class="input w-full resize-none"></textarea>
                </div>
                <div>
                    <label class="label">Status</label>
                    <select x-model="editForm.status" class="input w-full">
                        <option value="pending">Pending</option>
                        <option value="in_progress">In Progress</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <div x-show="active.goals?.length">
                    <h4 class="text-xs font-semibold uppercase text-gray-400 tracking-wider mt-4 mb-2">Goals</h4>
                    <ul class="space-y-1 text-sm">
                        <template x-for="g in (active.goals ?? [])" :key="g.id">
                            <li class="flex justify-between border-b border-gray-50 dark:border-gray-800 py-1.5">
                                <span x-text="g.title"></span>
                                <span class="text-gray-400" x-text="g.progress_percent + '%'"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button @click="save()" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : 'Save'"></button>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function performanceReviewsPage() {
    return {
        reviews: [],
        cycles: [],
        filterCycle: '',
        filterStatus: '',
        loading: true,
        showModal: false,
        saving: false,
        active: {},
        editForm: {},

        statusBadge(status) {
            const map = { pending: 'badge-gray', in_progress: 'badge-warning', completed: 'badge-success' };
            return map[status] ?? 'badge-gray';
        },

        async init() {
            const params = new URLSearchParams(window.location.search);
            if (params.get('cycle_id')) this.filterCycle = params.get('cycle_id');
            try {
                this.cycles = await apiFetch('/hr/performance-cycles').then(r => r.json());
            } catch (_) {}
            await this.load();
        },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams();
                if (this.filterCycle) params.set('cycle_id', this.filterCycle);
                if (this.filterStatus) params.set('status', this.filterStatus);
                this.reviews = await apiFetch('/hr/performance-reviews?' + params.toString()).then(r => r.json());
            } catch (e) {
                toast('Failed to load reviews', 'error');
            } finally {
                this.loading = false;
            }
        },

        async openReview(r) {
            this.active = await apiFetch('/hr/performance-reviews/' + r.id).then(res => res.json());
            this.editForm = {
                overall_rating: this.active.overall_rating ?? '',
                reviewer_comments: this.active.reviewer_comments ?? '',
                employee_comments: this.active.employee_comments ?? '',
                status: this.active.status,
            };
            this.showModal = true;
        },

        async save() {
            this.saving = true;
            try {
                await apiFetch('/hr/performance-reviews/' + this.active.id, { method: 'PUT', body: JSON.stringify(this.editForm) });
                toast('Review saved.', 'success');
                this.showModal = false;
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to save review', 'error');
            } finally {
                this.saving = false;
            }
        },
    };
}
</script>
@endpush
