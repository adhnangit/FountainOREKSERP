@extends('layouts.app')
@section('title', 'Districts & Cities')
@section('page-title', 'Districts & Cities')
@section('page-desc', 'Manage the district/city list used across address forms')

@section('content')
<div x-data="districtsCitiesPage()" x-init="init()" x-cloak>

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <input x-model="search" type="text" placeholder="Search district or city…" class="input w-full sm:w-72" />
        <button @click="openAddDistrict()" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add District
        </button>
    </div>

    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        <template x-for="d in filtered" :key="d.id">
            <div class="card p-5">
                <div class="flex items-start justify-between mb-2">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-gray-100" x-text="d.name"></h3>
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full mt-1 inline-block"
                              :class="d.is_active ? 'badge-success' : 'badge-danger'"
                              x-text="d.is_active ? 'Active' : 'Inactive'"></span>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <button @click="openEditDistrict(d)" class="text-xs text-indigo-600 hover:underline font-medium">Edit</button>
                        <button @click="deleteDistrict(d)" class="text-xs text-red-500 hover:underline font-medium">Delete</button>
                    </div>
                </div>
                <ul class="text-sm divide-y divide-gray-50 dark:divide-gray-800 mt-3 max-h-56 overflow-y-auto">
                    <template x-for="c in cityMatches(d)" :key="c.id">
                        <li class="flex items-center justify-between py-1.5">
                            <span :class="!c.is_active ? 'text-gray-400 line-through' : ''" x-text="c.name"></span>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <button @click="openEditCity(d, c)" class="text-xs text-indigo-600 hover:underline">Edit</button>
                                <button @click="deleteCity(d, c)" class="text-gray-300 hover:text-red-500">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </li>
                    </template>
                    <li x-show="!cityMatches(d).length" class="text-gray-400 py-1.5">No cities yet.</li>
                </ul>
                <button @click="openAddCity(d)" class="text-xs text-indigo-600 hover:underline mt-2">+ Add city</button>
            </div>
        </template>
        <div x-show="filtered.length === 0" class="text-center text-gray-400 py-16 col-span-full">No districts found.</div>
    </div>

    <!-- District Modal -->
    <div x-show="districtModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5)" @click.self="districtModal.open = false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-800 dark:text-gray-100" x-text="districtModal.id ? 'Edit District' : 'Add District'"></h2>
                <button @click="districtModal.open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div>
                <label class="label">District Name <span class="text-red-500">*</span></label>
                <input type="text" x-model="districtModal.name" class="input w-full" placeholder="e.g. Colombo" />
            </div>
            <label class="flex items-center gap-2 cursor-pointer" x-show="districtModal.id">
                <input type="checkbox" x-model="districtModal.is_active" class="rounded text-indigo-600" />
                <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
            </label>
            <div class="flex gap-3 pt-2">
                <button @click="districtModal.open = false" class="btn-secondary flex-1">Cancel</button>
                <button @click="saveDistrict()" :disabled="saving" class="btn-primary flex-1">
                    <span x-text="saving ? 'Saving…' : (districtModal.id ? 'Update' : 'Add')"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- City Modal -->
    <div x-show="cityModal.open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5)" @click.self="cityModal.open = false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-sm p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-base font-bold text-gray-800 dark:text-gray-100" x-text="(cityModal.id ? 'Edit City in ' : 'Add City to ') + cityModal.districtName"></h2>
                <button @click="cityModal.open = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div>
                <label class="label">City Name <span class="text-red-500">*</span></label>
                <input type="text" x-model="cityModal.name" class="input w-full" placeholder="e.g. Nugegoda" />
            </div>
            <label class="flex items-center gap-2 cursor-pointer" x-show="cityModal.id">
                <input type="checkbox" x-model="cityModal.is_active" class="rounded text-indigo-600" />
                <span class="text-sm text-gray-700 dark:text-gray-300">Active</span>
            </label>
            <div class="flex gap-3 pt-2">
                <button @click="cityModal.open = false" class="btn-secondary flex-1">Cancel</button>
                <button @click="saveCity()" :disabled="saving" class="btn-primary flex-1">
                    <span x-text="saving ? 'Saving…' : (cityModal.id ? 'Update' : 'Add')"></span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function districtsCitiesPage() {
    return {
        districts: [],
        loading: true,
        saving: false,
        search: '',
        districtModal: { open: false, id: null, name: '', is_active: true },
        cityModal: { open: false, id: null, districtId: null, districtName: '', name: '', is_active: true },

        get filtered() {
            const q = this.search.toLowerCase();
            if (!q) return this.districts;
            return this.districts.filter(d =>
                d.name.toLowerCase().includes(q) ||
                (d.cities ?? []).some(c => c.name.toLowerCase().includes(q))
            );
        },

        cityMatches(d) {
            const q = this.search.toLowerCase();
            const cities = (d.cities ?? []).slice().sort((a, b) => a.name.localeCompare(b.name));
            if (!q || d.name.toLowerCase().includes(q)) return cities;
            return cities.filter(c => c.name.toLowerCase().includes(q));
        },

        async init() {
            this.loading = true;
            try {
                const r = await apiFetch('/districts?active_only=false');
                this.districts = await r.json();
            } catch (e) {
                toast('Failed to load districts', 'error');
            } finally {
                this.loading = false;
            }
        },

        openAddDistrict() {
            this.districtModal = { open: true, id: null, name: '', is_active: true };
        },

        openEditDistrict(d) {
            this.districtModal = { open: true, id: d.id, name: d.name, is_active: d.is_active };
        },

        async saveDistrict() {
            if (!this.districtModal.name.trim()) { toast('District name is required', 'error'); return; }
            this.saving = true;
            try {
                const method = this.districtModal.id ? 'PUT' : 'POST';
                const path = this.districtModal.id ? `/districts/${this.districtModal.id}` : '/districts';
                const r = await apiFetch(path, { method, body: JSON.stringify({ name: this.districtModal.name.trim(), is_active: this.districtModal.is_active }) });
                if (!r.ok) { const e = await r.json(); throw new Error(e.message ?? 'Save failed'); }
                const saved = await r.json();
                if (this.districtModal.id) {
                    const idx = this.districts.findIndex(x => x.id === saved.id);
                    if (idx >= 0) this.districts[idx] = { ...saved, cities: this.districts[idx].cities };
                } else {
                    this.districts.push(saved);
                    this.districts.sort((a, b) => a.name.localeCompare(b.name));
                }
                window._districtsCache = null;
                this.districtModal.open = false;
                toast(this.districtModal.id ? 'District updated' : 'District added', 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to save', 'error');
            } finally {
                this.saving = false;
            }
        },

        async deleteDistrict(d) {
            if (!confirm(`Delete "${d.name}"? This cannot be undone.`)) return;
            try {
                const r = await apiFetch(`/districts/${d.id}`, { method: 'DELETE' });
                const body = await r.json();
                if (!r.ok) { toast(body.message ?? 'Cannot delete district.', 'error'); return; }
                this.districts = this.districts.filter(x => x.id !== d.id);
                window._districtsCache = null;
                toast('District deleted', 'success');
            } catch (e) {
                toast('Failed to delete district', 'error');
            }
        },

        openAddCity(d) {
            this.cityModal = { open: true, id: null, districtId: d.id, districtName: d.name, name: '', is_active: true };
        },

        openEditCity(d, c) {
            this.cityModal = { open: true, id: c.id, districtId: d.id, districtName: d.name, name: c.name, is_active: c.is_active };
        },

        async saveCity() {
            if (!this.cityModal.name.trim()) { toast('City name is required', 'error'); return; }
            this.saving = true;
            try {
                const method = this.cityModal.id ? 'PUT' : 'POST';
                const path = this.cityModal.id ? `/cities/${this.cityModal.id}` : '/cities';
                const r = await apiFetch(path, { method, body: JSON.stringify({
                    district_id: this.cityModal.districtId,
                    name: this.cityModal.name.trim(),
                    is_active: this.cityModal.is_active,
                })});
                if (!r.ok) { const e = await r.json(); throw new Error(e.message ?? 'Save failed'); }
                const saved = await r.json();
                const district = this.districts.find(x => x.id === this.cityModal.districtId);
                if (district) {
                    district.cities = district.cities ?? [];
                    const idx = district.cities.findIndex(c => c.id === saved.id);
                    if (idx >= 0) district.cities[idx] = saved; else district.cities.push(saved);
                }
                window._districtsCache = null;
                this.cityModal.open = false;
                toast(this.cityModal.id ? 'City updated' : 'City added', 'success');
            } catch (e) {
                toast(e.message ?? 'Failed to save', 'error');
            } finally {
                this.saving = false;
            }
        },

        async deleteCity(d, c) {
            if (!confirm(`Delete "${c.name}"?`)) return;
            try {
                const r = await apiFetch(`/cities/${c.id}`, { method: 'DELETE' });
                if (!r.ok) throw new Error();
                d.cities = (d.cities ?? []).filter(x => x.id !== c.id);
                window._districtsCache = null;
                toast('City deleted', 'success');
            } catch (e) {
                toast('Failed to delete city', 'error');
            }
        },
    };
}
</script>
@endpush
