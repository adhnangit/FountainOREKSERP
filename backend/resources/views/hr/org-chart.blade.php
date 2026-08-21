@extends('layouts.app')
@section('title', 'Org Chart')
@section('page-title', 'Organization Chart')
@section('page-desc', 'Reporting structure across the company')
@php $sec = 'hr'; @endphp

@section('content')
<style>
.oc-node{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:12px 16px;box-shadow:0 1px 3px rgba(0,0,0,.04);display:inline-flex;align-items:center;gap:10px;min-width:200px}
.dark .oc-node{background:#1e293b;border-color:#334155}
.oc-avatar{width:36px;height:36px;border-radius:10px;object-fit:cover;flex-shrink:0}
.oc-avatar-ph{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:14px;flex-shrink:0;background:linear-gradient(135deg,#0f4c81,#1a7abf)}
.oc-name{font-size:13px;font-weight:700;color:#1e293b;white-space:nowrap}
.dark .oc-name{color:#e2e8f0}
.oc-role{font-size:11px;color:#94a3b8}
.oc-branch{position:relative;padding-left:36px;margin-top:14px}
.oc-branch::before{content:'';position:absolute;left:17px;top:-14px;bottom:50%;width:1.5px;background:#e2e8f0}
.dark .oc-branch::before{background:#334155}
.oc-branch::after{content:'';position:absolute;left:17px;top:50%;width:19px;height:1.5px;background:#e2e8f0}
.dark .oc-branch::after{background:#334155}
.oc-root{display:flex;flex-direction:column}
</style>
<div x-data="orgChartPage()" x-init="init()">

    <div x-show="loading" class="flex items-center justify-center py-20">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading && tree.length === 0" class="text-center text-gray-400 py-20">
        No active employees with a reporting structure yet.
    </div>

    <div x-show="!loading" x-cloak class="card p-8 overflow-x-auto">
        <template x-for="root in tree" :key="root.id">
            <div x-html="renderNode(root)"></div>
        </template>
    </div>
</div>
@endsection

@push('scripts')
<script>
function orgChartPage() {
    return {
        loading: true,
        tree: [],

        async init() {
            try {
                this.tree = await apiFetch('/hr/employees/org-chart').then(r => r.json());
            } catch (e) {
                toast('Failed to load org chart', 'error');
            } finally {
                this.loading = false;
            }
        },

        esc(s) {
            const d = document.createElement('div');
            d.textContent = s ?? '';
            return d.innerHTML;
        },

        avatarHtml(e) {
            if (e.photo_path) {
                return `<img class="oc-avatar" src="${API}/hr/employees/${e.id}/photo" />`;
            }
            const initial = this.esc((e.name || '?').charAt(0).toUpperCase());
            return `<div class="oc-avatar-ph">${initial}</div>`;
        },

        renderNode(e) {
            const nodeHtml = `
                <a href="${BASE}/hr/employees/${e.id}" class="oc-node" style="text-decoration:none">
                    ${this.avatarHtml(e)}
                    <div>
                        <div class="oc-name">${this.esc(e.name)}</div>
                        <div class="oc-role">${this.esc(e.designation || e.department || '')}</div>
                    </div>
                </a>`;
            if (!e.reports || e.reports.length === 0) {
                return `<div class="oc-root">${nodeHtml}</div>`;
            }
            const children = e.reports.map(r => `<div>${this.renderNode(r)}</div>`).join('');
            return `<div class="oc-root">${nodeHtml}<div class="oc-branch" style="display:flex;flex-direction:column;gap:14px">${children}</div></div>`;
        },
    };
}
</script>
@endpush
