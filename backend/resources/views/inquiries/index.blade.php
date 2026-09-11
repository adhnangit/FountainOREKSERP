@extends('layouts.app')
@section('title', 'Inquiries & Leads')
@section('page-title', 'Inquiries & Leads')
@section('page-desc', 'Track incoming inquiries from first contact through to a won or lost deal')

@section('content')
<style>
.iq-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:20px}
.iq-stat-card{background:#fff;border-radius:14px;padding:16px 18px;border:1px solid #e2e8f0;display:flex;align-items:center;gap:12px}
.iq-stat-icon{width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.iq-stat-icon svg{width:20px;height:20px}
.iq-stat-val{font-size:21px;font-weight:800;line-height:1.1;letter-spacing:-.5px}
.iq-stat-lbl{font-size:11px;color:#94a3b8;font-weight:600;margin-top:2px;text-transform:uppercase;letter-spacing:.04em}

.iq-toolbar{background:#fff;border-radius:14px;padding:14px 18px;border:1px solid #e2e8f0;margin-bottom:16px;display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.iq-search-wrap{position:relative;flex:1;min-width:200px;max-width:320px}
.iq-search-wrap svg{position:absolute;left:10px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:#94a3b8;pointer-events:none}
.iq-search-wrap input{width:100%;border:1px solid #e2e8f0;border-radius:9px;padding:7px 12px 7px 34px;font-size:13px;color:#1e293b;background:#f8fafc;outline:none;transition:border-color .15s,box-shadow .15s}
.iq-search-wrap input:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12);background:#fff}
.iq-select{border:1px solid #e2e8f0;border-radius:9px;padding:7px 30px 7px 12px;font-size:12.5px;color:#334155;background:#f8fafc;outline:none;min-width:150px;appearance:none;-webkit-appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2394a3b8'%3E%3Cpath fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z' clip-rule='evenodd'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 8px center;background-size:14px}
.iq-select:focus{border-color:#6366f1;box-shadow:0 0 0 3px rgba(99,102,241,.12)}

.iq-table-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;overflow:hidden}
.iq-table{width:100%;border-collapse:separate;border-spacing:0}
.iq-table thead th{padding:10px 16px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#94a3b8;background:#f8fafc;border-bottom:1px solid #e2e8f0;white-space:nowrap;text-align:left}
.iq-table thead th:first-child{padding-left:20px}
.iq-row:hover{background:#f8faff}
.iq-table tbody td{padding:13px 16px;vertical-align:middle}
.iq-table tbody td:first-child{padding-left:20px}
.iq-row td{border-bottom:1px solid #f1f5f9}

.iq-avatar{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:11.5px;font-weight:700;flex-shrink:0;color:#fff}
.iq-name{font-size:13.5px;font-weight:700;color:#1e293b;cursor:pointer}
.iq-name:hover{color:#4f46e5;text-decoration:underline}
.iq-meta{font-size:11.5px;color:#94a3b8;margin-top:2px}
.iq-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap}
.iq-badge::before{content:'';width:6px;height:6px;border-radius:50%;flex-shrink:0;background:currentColor}
.iq-action-btn{width:29px;height:29px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;transition:all .15s;color:#64748b}
.iq-action-btn:hover{background:#f1f5f9;border-color:#c7d2fe;color:#4f46e5}
.iq-action-btn.danger:hover{background:#fef2f2;border-color:#fecaca;color:#ef4444}
.iq-action-btn svg{width:14px;height:14px}

.iq-pagination{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;border-top:1px solid #f1f5f9}
.iq-page-info{font-size:12.5px;color:#94a3b8}
.iq-page-btns{display:flex;gap:4px}
.iq-page-btn{min-width:29px;height:29px;padding:0 6px;border-radius:7px;border:1px solid #e2e8f0;background:#fff;font-size:12px;font-weight:600;color:#475569;cursor:pointer;display:flex;align-items:center;justify-content:center}
.iq-page-btn:hover:not(:disabled):not(.active){background:#f8fafc}
.iq-page-btn.active{background:#6366f1;color:#fff;border-color:#6366f1}
.iq-page-btn:disabled{opacity:.4;cursor:not-allowed}

.iq-empty{text-align:center;padding:60px 20px;color:#94a3b8}
.iq-empty svg{width:40px;height:40px;margin:0 auto 10px;color:#cbd5e1}

/* Follow-up modal */
.iq-fu-grid{display:grid;grid-template-columns:260px 1fr;gap:0}
@media (max-width:720px){.iq-fu-grid{grid-template-columns:1fr}}
.iq-fu-profile{background:#f8fafc;border-right:1px solid #eef0f7;padding:20px}
@media (max-width:720px){.iq-fu-profile{border-right:none;border-bottom:1px solid #eef0f7}}
.iq-fu-note{background:#eef2ff;border:1px solid #c7d2fe;border-radius:10px;padding:10px 12px;font-size:12px;color:#4338ca;margin-top:14px;line-height:1.5}
.iq-stream{max-height:260px;overflow-y:auto;padding:16px 20px}
.iq-stream-item{border-left:2px solid #e2e8f0;padding:0 0 16px 14px;position:relative}
.iq-stream-item::before{content:'';position:absolute;left:-5px;top:2px;width:8px;height:8px;border-radius:50%;background:#6366f1}
.iq-stream-item:last-child{padding-bottom:0}

.dark .iq-stat-card,.dark .iq-toolbar,.dark .iq-table-card{background:#1e293b;border-color:#334155}
.dark .iq-table thead th{background:#0f172a;border-color:#334155;color:#64748b}
.dark .iq-row:hover{background:rgba(99,102,241,.06)}
.dark .iq-row td{border-color:#334155}
.dark .iq-name{color:#f1f5f9}
.dark .iq-fu-profile{background:#0f172a;border-color:#334155}
.dark .iq-select,.dark .iq-search-wrap input{background:#0f172a;border-color:#334155;color:#cbd5e1}
</style>

<div x-data="inquiriesPage()" x-init="init()" x-cloak>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex gap-2">
            <a href="{{ url('/inquiries/subjects') }}" class="btn-secondary text-sm">Subjects</a>
            <a href="{{ url('/inquiries/statuses') }}" class="btn-secondary text-sm">Statuses</a>
        </div>
        <button @click="openCreate()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Inquiry
        </button>
    </div>

    <!-- Stats -->
    <div class="iq-stats">
        <div class="iq-stat-card">
            <div class="iq-stat-icon" style="background:#eef2ff;color:#4f46e5">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-4a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            </div>
            <div><div class="iq-stat-val" x-text="stats.total ?? 0"></div><div class="iq-stat-lbl">Total Leads</div></div>
        </div>
        <div class="iq-stat-card">
            <div class="iq-stat-icon" style="background:#f0fdf4;color:#15803d">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div><div class="iq-stat-val" x-text="stats.won ?? 0"></div><div class="iq-stat-lbl">Won Deals</div></div>
        </div>
        <div class="iq-stat-card">
            <div class="iq-stat-icon" style="background:#fffbeb;color:#b45309">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m0-2c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div><div class="iq-stat-val" x-text="fmtMoney(stats.pipeline_value ?? 0)"></div><div class="iq-stat-lbl">Pipeline Value</div></div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="iq-toolbar">
        <div class="iq-search-wrap">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" x-model="filters.search" @input.debounce.400ms="page = 1; load()" placeholder="Search name, subject, phone…" />
        </div>
        <select class="iq-select" x-model="filters.status" @change="page = 1; load()">
            <option value="">All Statuses</option>
            <template x-for="s in statuses" :key="s.id"><option :value="s.name" x-text="s.name"></option></template>
        </select>
        <select class="iq-select" x-model="filters.subject" @change="page = 1; load()">
            <option value="">All Subjects</option>
            <template x-for="s in subjects" :key="s.id"><option :value="s.name" x-text="s.name"></option></template>
        </select>
        <button class="btn-secondary text-sm ml-auto" @click="resetFilters()" x-show="filters.search || filters.status || filters.subject">Clear Filters</button>
    </div>

    <!-- Table -->
    <div class="iq-table-card">
        <div class="overflow-x-auto">
        <table class="iq-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Customer Details</th>
                    <th>Subject</th>
                    <th>Representative</th>
                    <th>Value</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="inq in inquiries" :key="inq.id">
                    <tr class="iq-row">
                        <td><span class="text-[12.5px] text-gray-600 dark:text-gray-300" x-text="fmtDate(inq.created_at)"></span></td>
                        <td>
                            <div class="iq-name" @click="openFollowup(inq)" x-text="inq.name"></div>
                            <div class="iq-meta" x-show="inq.phone" x-text="inq.phone"></div>
                            <div class="iq-meta" x-show="inq.email" x-text="inq.email"></div>
                        </td>
                        <td><span class="text-[12.5px] text-gray-600 dark:text-gray-300" x-text="inq.subject"></span></td>
                        <td>
                            <div class="flex items-center gap-2" x-show="inq.assignee">
                                <div class="iq-avatar" :style="'background:' + avatarColor(inq.assignee?.name)" x-text="initials(inq.assignee?.name)"></div>
                                <span class="text-[12.5px] text-gray-700 dark:text-gray-200" x-text="inq.assignee?.name"></span>
                            </div>
                            <span class="text-[12px] text-gray-400" x-show="!inq.assignee">Unassigned</span>
                        </td>
                        <td><span class="text-[13px] font-semibold text-gray-800 dark:text-gray-100" x-text="fmtMoney(inq.potential_value)"></span></td>
                        <td>
                            <span class="iq-badge" :style="'background:' + statusColor(inq.status) + '22; color:' + statusColor(inq.status)" x-text="inq.status"></span>
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button class="iq-action-btn" title="Follow-ups" @click="openFollowup(inq)">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                </button>
                                <button class="iq-action-btn" title="Edit" @click="openEdit(inq)">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <button class="iq-action-btn danger" title="Delete" @click="deleteInquiry(inq)">
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>

                <tr x-show="!loading && inquiries.length === 0">
                    <td colspan="7">
                        <div class="iq-empty">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-4a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            <h5 class="font-semibold text-gray-500">No inquiries found</h5>
                            <p class="text-sm">Log a new inquiry or adjust your filters.</p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        </div>

        <div class="iq-pagination" x-show="meta.total > 0">
            <div class="iq-page-info" x-text="'Showing ' + meta.from + '–' + meta.to + ' of ' + meta.total + ' leads'"></div>
            <div class="iq-page-btns">
                <button class="iq-page-btn" @click="page--; load()" :disabled="page <= 1">
                    <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M15 19l-7-7 7-7"/></svg>
                </button>
                <template x-for="p in pageNumbers" :key="p">
                    <button class="iq-page-btn" :class="p === page ? 'active' : ''" @click="page = p; load()" x-text="p"></button>
                </template>
                <button class="iq-page-btn" @click="page++; load()" :disabled="page >= meta.last_page">
                    <svg style="width:12px;height:12px" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Create / Edit Modal -->
    <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showModal = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editId ? 'Edit Inquiry' : 'New Inquiry'"></h3>
                <button @click="showModal = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form @submit.prevent="save()" class="p-6 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Customer Name <span class="text-red-500">*</span></label>
                        <input x-model="form.name" type="text" class="input w-full" required />
                    </div>
                    <div>
                        <label class="label">Subject <span class="text-red-500">*</span></label>
                        <select x-model="form.subject" class="input w-full" required>
                            <option value="">— Select —</option>
                            <template x-for="s in subjects" :key="s.id"><option :value="s.name" x-text="s.name"></option></template>
                        </select>
                    </div>
                    <div>
                        <label class="label">Phone</label>
                        <input x-model="form.phone" type="text" class="input w-full" />
                    </div>
                    <div>
                        <label class="label">Email</label>
                        <input x-model="form.email" type="email" class="input w-full" />
                    </div>
                    <div>
                        <label class="label">Status <span class="text-red-500">*</span></label>
                        <select x-model="form.status" class="input w-full" required>
                            <template x-for="s in statuses" :key="s.id"><option :value="s.name" x-text="s.name"></option></template>
                        </select>
                    </div>
                    <div>
                        <label class="label">Potential Value</label>
                        <input x-model.number="form.potential_value" type="number" step="0.01" min="0" class="input w-full" />
                    </div>
                    <div class="col-span-2">
                        <label class="label">Assign To</label>
                        <select x-model="form.assigned_to" class="input w-full">
                            <option value="">— Unassigned —</option>
                            <template x-for="u in users" :key="u.id"><option :value="u.id" x-text="u.name"></option></template>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="label">Message</label>
                        <textarea x-model="form.message" rows="2" class="input w-full"></textarea>
                    </div>
                    <div class="col-span-2">
                        <label class="label">Internal Notes</label>
                        <textarea x-model="form.internal_notes" rows="2" class="input w-full" placeholder="Private notes, not shared with the customer"></textarea>
                    </div>
                </div>

                <div x-show="formError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="formError"></div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" :disabled="saving" x-text="saving ? 'Saving…' : 'Save Inquiry'"></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Follow-up / Activity Modal -->
    <div x-show="showFollowup" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="closeFollowup()">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] overflow-y-auto" x-show="fuInquiry">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Follow-up History</h3>
                <button @click="closeFollowup()" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="iq-fu-grid" x-show="fuInquiry">
                <!-- Lead Profile pane -->
                <div class="iq-fu-profile">
                    <div class="flex items-center gap-3">
                        <div class="iq-avatar" style="width:44px;height:44px;font-size:15px" :style="'background:' + avatarColor(fuInquiry?.name)" x-text="initials(fuInquiry?.name)"></div>
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-gray-100" x-text="fuInquiry?.name"></div>
                            <span class="iq-badge mt-1" :style="'background:' + statusColor(fuInquiry?.status) + '22; color:' + statusColor(fuInquiry?.status)" x-text="fuInquiry?.status"></span>
                        </div>
                    </div>
                    <div class="mt-4 space-y-2 text-[12.5px] text-gray-600 dark:text-gray-300">
                        <div class="flex items-center gap-2" x-show="fuInquiry?.phone">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            <span x-text="fuInquiry?.phone"></span>
                        </div>
                        <div class="flex items-center gap-2" x-show="fuInquiry?.email">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <span x-text="fuInquiry?.email"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V6m0 10v2m0-2c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span x-text="fmtMoney(fuInquiry?.potential_value)"></span>
                        </div>
                    </div>
                    <div class="iq-fu-note" x-show="fuInquiry?.internal_notes" x-text="fuInquiry?.internal_notes"></div>
                </div>

                <!-- Activity + Register Interaction -->
                <div>
                    <div class="iq-stream">
                        <template x-for="f in fuInquiry?.followups ?? []" :key="f.id">
                            <div class="iq-stream-item">
                                <div class="flex items-center justify-between">
                                    <span class="text-[12px] font-semibold text-gray-700 dark:text-gray-200" x-text="fmtDateTime(f.followup_date)"></span>
                                    <button class="text-gray-300 hover:text-red-500" @click="deleteFollowup(f)">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                                <div class="text-[11px] text-gray-400 mt-0.5" x-text="'by ' + (f.user?.name ?? '—')"></div>
                                <span class="iq-badge mt-1" x-show="f.outcome" style="background:#eef2ff;color:#4338ca" x-text="f.outcome"></span>
                                <p class="text-[13px] text-gray-700 dark:text-gray-200 mt-1.5" x-text="f.notes"></p>
                            </div>
                        </template>
                        <p class="text-sm text-gray-400" x-show="!(fuInquiry?.followups ?? []).length">No follow-ups logged yet.</p>
                    </div>

                    <form @submit.prevent="addFollowup()" class="border-t border-gray-100 dark:border-gray-700 p-5 space-y-3">
                        <div class="text-[12px] font-bold text-gray-500 uppercase tracking-wide">Register Interaction</div>
                        <div>
                            <label class="label">Date &amp; Time <span class="text-red-500">*</span></label>
                            <input x-model="fuForm.followup_date" type="datetime-local" class="input w-full" required />
                        </div>
                        <div>
                            <label class="label">Notes <span class="text-red-500">*</span></label>
                            <textarea x-model="fuForm.notes" rows="3" class="input w-full" placeholder="What happened during this interaction?" required></textarea>
                        </div>
                        <div>
                            <label class="label">Outcome</label>
                            <select x-model="fuForm.outcome" class="input w-full">
                                <option value="">— No change in phase —</option>
                                <option value="Warm Interest">Warm Interest</option>
                                <option value="Neutral / Evaluating">Neutral / Evaluating</option>
                                <option value="Cold / Unreachable">Cold / Unreachable</option>
                                <option value="Deal Won 🏆">Deal Won 🏆</option>
                                <option value="Lead Terminated">Lead Terminated</option>
                            </select>
                        </div>
                        <div x-show="fuError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="fuError"></div>
                        <div class="flex justify-end gap-3 pt-1">
                            <button type="button" @click="closeFollowup()" class="btn-secondary">Dismiss</button>
                            <button type="submit" class="btn-primary" :disabled="fuSaving" x-text="fuSaving ? 'Recording…' : 'Record'"></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function inquiriesPage() {
    return {
        inquiries: [], subjects: [], statuses: [], users: [],
        stats: {}, meta: { total: 0, from: 0, to: 0, last_page: 1 },
        loading: true, page: 1,
        filters: { search: '', status: '', subject: '' },

        showModal: false, editId: null, saving: false, formError: '', form: {},

        showFollowup: false, fuInquiry: null, fuForm: {}, fuSaving: false, fuError: '',

        async init() {
            await Promise.all([this.loadSubjects(), this.loadStatuses(), this.loadUsers()]);
            await this.load();
        },

        get pageNumbers() {
            const total = this.meta.last_page || 1;
            const cur = this.page;
            const span = 2;
            let start = Math.max(1, cur - span);
            let end = Math.min(total, cur + span);
            const pages = [];
            for (let i = start; i <= end; i++) pages.push(i);
            return pages;
        },

        async loadSubjects() {
            try { this.subjects = await apiFetch('/inquiry-subjects').then(r => r.json()); } catch (e) {}
        },
        async loadStatuses() {
            try { this.statuses = await apiFetch('/inquiry-statuses').then(r => r.json()); } catch (e) {}
        },
        async loadUsers() {
            try { this.users = await apiFetch('/inquiries/assignable-users').then(r => r.json()); } catch (e) {}
        },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ page: this.page, per_page: 10 });
                if (this.filters.search) params.set('search', this.filters.search);
                if (this.filters.status) params.set('status', this.filters.status);
                if (this.filters.subject) params.set('subject', this.filters.subject);
                const data = await apiFetch('/inquiries?' + params.toString()).then(r => r.json());
                this.inquiries = data.inquiries?.data ?? [];
                this.meta = {
                    total: data.inquiries?.total ?? 0,
                    from: data.inquiries?.from ?? 0,
                    to: data.inquiries?.to ?? 0,
                    last_page: data.inquiries?.last_page ?? 1,
                };
                this.stats = data.stats ?? {};
            } catch (e) {
                toast(e.message ?? 'Failed to load inquiries', 'error');
            } finally {
                this.loading = false;
            }
        },

        resetFilters() {
            this.filters = { search: '', status: '', subject: '' };
            this.page = 1;
            this.load();
        },

        blank() {
            return {
                name: '', email: '', phone: '', subject: '', source: '', message: '',
                internal_notes: '', status: this.statuses[0]?.name ?? 'New', potential_value: 0, assigned_to: '',
            };
        },

        openCreate() {
            this.editId = null;
            this.form = this.blank();
            this.formError = '';
            this.showModal = true;
        },

        openEdit(inq) {
            this.editId = inq.id;
            this.form = {
                name: inq.name, email: inq.email ?? '', phone: inq.phone ?? '', subject: inq.subject,
                source: inq.source ?? '', message: inq.message ?? '', internal_notes: inq.internal_notes ?? '',
                status: inq.status, potential_value: inq.potential_value ?? 0, assigned_to: inq.assigned_to ?? '',
            };
            this.formError = '';
            this.showModal = true;
        },

        async save() {
            if (!this.form.name || !this.form.subject || !this.form.status) {
                toast('Name, subject and status are required', 'error');
                return;
            }
            this.saving = true;
            this.formError = '';
            try {
                const payload = { ...this.form, assigned_to: this.form.assigned_to || null };
                const url = this.editId ? '/inquiries/' + this.editId : '/inquiries';
                const method = this.editId ? 'PUT' : 'POST';
                await apiFetch(url, { method, body: JSON.stringify(payload) });
                toast(this.editId ? 'Inquiry updated.' : 'Inquiry created.');
                this.showModal = false;
                await this.load();
            } catch (e) {
                this.formError = e.message ?? 'Unexpected error. Please try again.';
            } finally {
                this.saving = false;
            }
        },

        async deleteInquiry(inq) {
            if (!confirm(`Delete the inquiry from "${inq.name}"? This cannot be undone.`)) return;
            try {
                await apiFetch('/inquiries/' + inq.id, { method: 'DELETE' });
                toast('Inquiry deleted.');
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Cannot delete inquiry.', 'error');
            }
        },

        async openFollowup(inq) {
            this.showFollowup = true;
            this.fuInquiry = null;
            this.fuForm = { followup_date: this.nowLocal(), notes: '', outcome: '' };
            this.fuError = '';
            try {
                this.fuInquiry = await apiFetch('/inquiries/' + inq.id).then(r => r.json());
            } catch (e) {
                toast(e.message ?? 'Failed to load follow-up history', 'error');
                this.showFollowup = false;
            }
        },

        closeFollowup() {
            this.showFollowup = false;
            this.fuInquiry = null;
        },

        async addFollowup() {
            if (!this.fuForm.notes || this.fuForm.notes.length < 5) {
                toast('Notes must be at least 5 characters', 'error');
                return;
            }
            this.fuSaving = true;
            this.fuError = '';
            try {
                await apiFetch('/inquiries/' + this.fuInquiry.id + '/followups', {
                    method: 'POST',
                    body: JSON.stringify({
                        followup_date: this.fuForm.followup_date,
                        notes: this.fuForm.notes,
                        outcome: this.fuForm.outcome || null,
                    }),
                });
                toast('Follow-up recorded.');
                this.fuForm = { followup_date: this.nowLocal(), notes: '', outcome: '' };
                this.fuInquiry = await apiFetch('/inquiries/' + this.fuInquiry.id).then(r => r.json());
                await this.load();
            } catch (e) {
                this.fuError = e.message ?? 'Unexpected error. Please try again.';
            } finally {
                this.fuSaving = false;
            }
        },

        async deleteFollowup(f) {
            if (!confirm('Delete this follow-up entry?')) return;
            try {
                await apiFetch('/inquiries/' + this.fuInquiry.id + '/followups/' + f.id, { method: 'DELETE' });
                this.fuInquiry = await apiFetch('/inquiries/' + this.fuInquiry.id).then(r => r.json());
            } catch (e) {
                toast(e.message ?? 'Cannot delete follow-up.', 'error');
            }
        },

        statusColor(name) {
            return this.statuses.find(s => s.name === name)?.color ?? '#6b7280';
        },

        initials(name) {
            if (!name) return '?';
            return name.trim().split(/\s+/).slice(0, 2).map(w => w[0].toUpperCase()).join('');
        },

        avatarColor(name) {
            const colors = ['#4f46e5','#0891b2','#059669','#d97706','#dc2626','#7c3aed','#0369a1','#65a30d'];
            let hash = 0;
            for (const c of (name ?? '')) hash = (hash * 31 + c.charCodeAt(0)) % colors.length;
            return colors[Math.abs(hash) % colors.length];
        },

        fmtMoney(v) {
            const n = Number(v ?? 0);
            return 'Rs. ' + n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        fmtDate(d) {
            if (!d) return '—';
            return new Date(d).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
        },

        fmtDateTime(d) {
            if (!d) return '—';
            return new Date(d).toLocaleString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
        },

        nowLocal() {
            const d = new Date();
            d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
            return d.toISOString().slice(0, 16);
        },
    };
}
</script>
@endpush
