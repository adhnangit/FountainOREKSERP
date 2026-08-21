<?php $__env->startSection('title', 'Employees'); ?>
<?php $__env->startSection('page-title', 'Employees'); ?>
<?php $__env->startSection('page-desc', 'Manage your staff records'); ?>
<?php $sec = 'hr'; ?>

<?php $__env->startSection('content'); ?>
<div x-data="employeesPage()" x-init="init()">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex flex-wrap items-center gap-2">
            <input x-model="search" type="text" placeholder="Search name, code, phone, email…" class="input w-full sm:w-64" />
            <select x-model="filterDept" class="input w-auto">
                <option value="">All Departments</option>
                <template x-for="d in flatDepartments" :key="d.id"><option :value="d.id" x-text="d.name"></option></template>
            </select>
            <select x-model="filterStatus" class="input w-auto">
                <option value="">All Statuses</option>
                <option value="active">Active</option>
                <option value="on_leave">On Leave</option>
                <option value="suspended">Suspended</option>
                <option value="terminated">Terminated</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button @click="showImport = true" class="btn-secondary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M7 10l5 5 5-5M12 15V3"/></svg>
                Import
            </button>
            <a href="<?php echo e(url('/hr/employees/create')); ?>" class="btn-primary inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Employee
            </a>
        </div>
    </div>

    <div class="card p-0 overflow-hidden">
        <div x-show="loading" class="flex items-center justify-center py-16">
            <svg class="animate-spin w-8 h-8 text-indigo-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
        </div>
        <div x-show="!loading" class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800/40">
                    <tr>
                        <th class="table-hd">Employee</th>
                        <th class="table-hd">Code</th>
                        <th class="table-hd">Department</th>
                        <th class="table-hd">Designation</th>
                        <th class="table-hd">Branch</th>
                        <th class="table-hd">Status</th>
                        <th class="table-hd">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-100 dark:divide-gray-700/40">
                    <template x-for="e in filtered" :key="e.id">
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/20">
                            <td class="table-td">
                                <div class="flex items-center gap-3">
                                    <img x-show="e.photo_path" :src="API + '/hr/employees/' + e.id + '/photo'" class="w-8 h-8 rounded-full object-cover flex-shrink-0" />
                                    <div x-show="!e.photo_path" class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                                         style="background:linear-gradient(135deg,#0f4c81,#1a7abf)"
                                         x-text="(e.first_name||'?').charAt(0).toUpperCase()"></div>
                                    <span class="font-medium text-gray-900 dark:text-gray-100" x-text="[e.first_name, e.last_name].filter(Boolean).join(' ')"></span>
                                </div>
                            </td>
                            <td class="table-td text-gray-500 font-mono text-xs" x-text="e.employee_code"></td>
                            <td class="table-td text-gray-500" x-text="e.department?.name ?? '—'"></td>
                            <td class="table-td text-gray-500" x-text="e.designation?.name ?? '—'"></td>
                            <td class="table-td text-gray-500" x-text="e.branch?.name ?? '—'"></td>
                            <td class="table-td">
                                <span class="badge" :class="statusBadge(e.employment_status)" x-text="(e.employment_status ?? 'active').replace('_',' ')"></span>
                            </td>
                            <td class="table-td">
                                <a :href="BASE + '/hr/employees/' + e.id" class="text-indigo-600 hover:underline text-sm font-medium">View</a>
                            </td>
                        </tr>
                    </template>
                    <tr x-show="!loading && filtered.length === 0">
                        <td colspan="7" class="table-td text-center text-gray-400 py-10">No employees found.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Import Modal -->
    <div x-show="showImport" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" @click.self="showImport = false">
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Bulk Import Employees</h3>
                <button @click="showImport = false" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-6 space-y-4">
                <p class="text-sm text-gray-500">
                    Upload a CSV or Excel file with columns: <span class="font-mono text-xs">first_name, last_name, branch_code, department_code, designation_code, join_date, employment_type, phone, personal_email, nic_passport</span>.
                    Only <span class="font-mono text-xs">first_name</span> and <span class="font-mono text-xs">join_date</span> are required.
                </p>
                <input type="file" @change="importFile = $event.target.files[0]" accept=".csv,.xlsx,.xls" class="input w-full" />
                <div x-show="importResult" class="text-sm rounded-lg p-3 bg-gray-50 dark:bg-gray-900/40 space-y-2">
                    <div class="font-semibold text-green-600" x-text="importResult ? importResult.imported + ' employee(s) imported.' : ''"></div>
                    <div x-show="importResult?.skipped_count > 0" class="text-amber-600" x-text="importResult.skipped_count + ' row(s) skipped:'"></div>
                    <ul class="list-disc pl-5 text-xs text-gray-500 max-h-40 overflow-y-auto">
                        <template x-for="msg in (importResult?.skipped ?? [])" :key="msg"><li x-text="msg"></li></template>
                    </ul>
                </div>
                <div x-show="importError" class="text-sm text-red-600 bg-red-50 rounded-lg px-3 py-2" x-text="importError"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showImport = false" class="btn-secondary">Close</button>
                    <button @click="runImport()" class="btn-primary" :disabled="!importFile || importing" x-text="importing ? 'Importing…' : 'Import'"></button>
                </div>
            </div>
        </div>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function employeesPage() {
    return {
        items: [],
        departments: [],
        loading: true,
        search: '',
        filterDept: '',
        filterStatus: '',
        showImport: false,
        importFile: null,
        importing: false,
        importResult: null,
        importError: '',

        get flatDepartments() {
            const flat = [];
            const walk = (list, prefix = '') => list.forEach(d => {
                flat.push({ id: d.id, name: prefix + d.name });
                if (d.children?.length) walk(d.children, prefix + '— ');
            });
            walk(this.departments);
            return flat;
        },

        get filtered() {
            const q = this.search.toLowerCase();
            return this.items.filter(e => {
                if (this.filterDept && e.department_id != this.filterDept) return false;
                if (this.filterStatus && (e.employment_status ?? 'active') !== this.filterStatus) return false;
                if (!q) return true;
                return [e.first_name, e.last_name, e.employee_code, e.phone, e.personal_email]
                    .filter(Boolean).some(v => v.toLowerCase().includes(q));
            });
        },

        statusBadge(status) {
            const map = { active: 'badge-success', on_leave: 'badge-warning', suspended: 'badge-danger', terminated: 'badge-gray' };
            return map[status ?? 'active'] ?? 'badge-gray';
        },

        async init() {
            try {
                const [empData, deptData] = await Promise.all([
                    apiFetch('/hr/employees?per_page=500').then(r => r.json()),
                    apiFetch('/hr/departments').then(r => r.json()),
                ]);
                this.items = empData.data ?? empData ?? [];
                this.departments = deptData ?? [];
            } catch (e) {
                toast('Failed to load employees', 'error');
            } finally {
                this.loading = false;
            }
        },

        async runImport() {
            if (!this.importFile) return;
            this.importing = true;
            this.importError = '';
            this.importResult = null;
            try {
                const fd = new FormData();
                fd.append('file', this.importFile);
                const r = await apiFetch('/hr/employees/import', { method: 'POST', body: fd });
                this.importResult = await r.json();
                toast(`${this.importResult.imported} employee(s) imported.`, 'success');
                await this.init();
            } catch (e) {
                this.importError = e.message ?? 'Import failed.';
            } finally {
                this.importing = false;
            }
        },
    };
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/medrilk/system.medri.lk/backend/resources/views/hr/employees-index.blade.php ENDPATH**/ ?>