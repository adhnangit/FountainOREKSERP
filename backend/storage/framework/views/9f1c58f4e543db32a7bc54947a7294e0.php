<?php $__env->startSection('title', 'Leave Balances'); ?>
<?php $__env->startSection('page-title', 'Leave Balances'); ?>
<?php $__env->startSection('page-desc', 'Yearly leave allocations and remaining balances per employee'); ?>
<?php $sec = 'hr'; ?>

<?php $__env->startSection('content'); ?>
<div x-data="leaveBalancesPage()" x-init="init()">

    <div class="flex flex-wrap items-center gap-2 mb-4">
        <select x-model.number="year" @change="load()" class="input w-auto">
            <template x-for="y in yearOptions" :key="y"><option :value="y" x-text="y"></option></template>
        </select>
        <select x-model="filterType" @change="load()" class="input w-auto">
            <option value="">All Balance-Tracked Leave Types</option>
            <template x-for="t in allTypes" :key="t.id"><option :value="t.id" x-text="t.name"></option></template>
        </select>
        <div class="flex-1"></div>
        <button @click="showAllocate = true" class="btn-primary inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Allocate Leave
        </button>
    </div>

    <div x-show="loading" class="flex items-center justify-center py-16">
        <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
    </div>

    <div x-show="!loading" class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800/40">
                    <tr>
                        <th class="table-hd">Employee</th>
                        <template x-for="t in leaveTypes" :key="t.id">
                            <th class="table-hd text-center" x-text="t.name"></th>
                        </template>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                    <template x-for="row in employees" :key="row.employee_id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                            <td class="table-td">
                                <div class="font-medium text-gray-900 dark:text-gray-100" x-text="row.name"></div>
                                <div class="text-xs text-gray-400" x-text="row.department"></div>
                            </td>
                            <template x-for="b in row.balances" :key="b.leave_type_id">
                                <td class="table-td text-center cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900/20" @click="openEdit(row, b)">
                                    <span class="font-semibold" x-text="b.remaining_days ?? (b.allocated_days - b.used_days)"></span>
                                    <span class="text-gray-400 text-xs"> / <span x-text="b.allocated_days"></span></span>
                                </td>
                            </template>
                        </tr>
                    </template>
                    <tr x-show="!loading && employees.length === 0">
                        <td :colspan="leaveTypes.length + 1" class="text-center text-gray-400 py-16">No active employees found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-3 text-xs text-gray-400 border-t border-gray-100 dark:border-gray-700">Showing remaining / allocated days. Click a cell to adjust it.</div>
    </div>

    <!-- Allocate Modal -->
    <div x-show="showAllocate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showAllocate = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Allocate Leave</h3>
                <button @click="showAllocate = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-500">Sets the allocated days for every active employee in the current branch view, for one leave type and year.</p>
                <div>
                    <label class="label">Leave Type <span class="text-red-500">*</span></label>
                    <select x-model="allocateForm.leave_type_id" class="input w-full">
                        <option value="">— Select —</option>
                        <template x-for="t in allTypes" :key="t.id"><option :value="t.id" x-text="t.name"></option></template>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="label">Year</label>
                        <input type="number" x-model.number="allocateForm.year" class="input w-full" />
                    </div>
                    <div>
                        <label class="label">Days</label>
                        <input type="number" step="0.5" min="0" x-model.number="allocateForm.allocated_days" class="input w-full" />
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" x-model="allocateForm.overwrite_existing" class="rounded text-indigo-600" />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Overwrite existing allocations for this year</span>
                </label>
                <div x-show="allocateError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="allocateError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showAllocate = false" class="btn-secondary">Cancel</button>
                    <button @click="runAllocate()" class="btn-primary" :disabled="allocating" x-text="allocating ? 'Allocating…' : 'Allocate'"></button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Balance Modal -->
    <div x-show="showEdit" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showEdit = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100" x-text="editEmployeeName + ' — ' + editTypeName"></h3>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="label">Allocated Days</label>
                    <input type="number" step="0.5" min="0" x-model.number="editForm.allocated_days" class="input w-full" />
                </div>
                <div>
                    <label class="label">Carried Forward</label>
                    <input type="number" step="0.5" min="0" x-model.number="editForm.carried_forward" class="input w-full" />
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showEdit = false" class="btn-secondary">Cancel</button>
                    <button @click="saveEdit()" class="btn-primary" :disabled="editSaving" x-text="editSaving ? 'Saving…' : 'Save'"></button>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function leaveBalancesPage() {
    return {
        year: new Date().getFullYear(),
        yearOptions: Array.from({length: 4}, (_, i) => new Date().getFullYear() - 1 + i),
        filterType: '',
        allTypes: [],
        leaveTypes: [],
        employees: [],
        loading: true,
        showAllocate: false,
        allocateForm: { leave_type_id: '', year: new Date().getFullYear(), allocated_days: '', overwrite_existing: false },
        allocateError: '',
        allocating: false,
        showEdit: false,
        editBalanceId: null,
        editEmployeeName: '',
        editTypeName: '',
        editForm: {},
        editSaving: false,

        async init() {
            try {
                this.allTypes = await apiFetch('/hr/leave-types?active_only=1').then(r => r.json());
                this.allTypes = this.allTypes.filter(t => t.max_days_per_year !== null);
            } catch (_) {}
            await this.load();
        },

        async load() {
            this.loading = true;
            try {
                const params = new URLSearchParams({ year: this.year });
                if (this.filterType) params.set('leave_type_id', this.filterType);
                const data = await apiFetch('/hr/leave-balances?' + params.toString()).then(r => r.json());
                this.employees = data.employees ?? [];
                this.leaveTypes = data.leave_types ?? [];
            } catch (e) {
                toast('Failed to load leave balances', 'error');
            } finally {
                this.loading = false;
            }
        },

        openEdit(row, balance) {
            this.editBalanceId = balance.id ?? null;
            this.editEmployeeName = row.name;
            this.editTypeName = this.leaveTypes.find(t => t.id === balance.leave_type_id)?.name ?? '';
            this.editForm = {
                employee_id: row.employee_id,
                leave_type_id: balance.leave_type_id,
                allocated_days: parseFloat(balance.allocated_days ?? 0),
                carried_forward: parseFloat(balance.carried_forward ?? 0),
            };
            this.showEdit = true;
        },

        async saveEdit() {
            this.editSaving = true;
            try {
                if (this.editBalanceId) {
                    await apiFetch('/hr/leave-balances/' + this.editBalanceId, {
                        method: 'PUT',
                        body: JSON.stringify({ allocated_days: this.editForm.allocated_days, carried_forward: this.editForm.carried_forward }),
                    });
                } else {
                    // No balance row exists yet for this employee+type+year — create it via allocate.
                    await apiFetch('/hr/leave-balances/allocate', {
                        method: 'POST',
                        body: JSON.stringify({
                            leave_type_id: this.editForm.leave_type_id,
                            year: this.year,
                            allocated_days: this.editForm.allocated_days,
                            overwrite_existing: true,
                        }),
                    });
                }
                toast('Balance updated.', 'success');
                this.showEdit = false;
                await this.load();
            } catch (e) {
                toast(e.message ?? 'Failed to update balance', 'error');
            } finally {
                this.editSaving = false;
            }
        },

        async runAllocate() {
            if (!this.allocateForm.leave_type_id) { this.allocateError = 'Select a leave type.'; return; }
            this.allocating = true;
            this.allocateError = '';
            try {
                const res = await apiFetch('/hr/leave-balances/allocate', {
                    method: 'POST',
                    body: JSON.stringify(this.allocateForm),
                }).then(r => r.json());
                toast(`Allocated to ${res.allocated} employee(s).`, 'success');
                this.showAllocate = false;
                await this.load();
            } catch (e) {
                this.allocateError = e.message ?? 'Failed to allocate.';
            } finally {
                this.allocating = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\xampp8.2\htdocs\FountainOREKS\backend\resources\views\hr\leave-balances.blade.php ENDPATH**/ ?>