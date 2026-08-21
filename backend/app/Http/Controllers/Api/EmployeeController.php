<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeHistory;
use App\Services\BranchContextService;
use App\Services\NumberGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function __construct(
        private NumberGeneratorService $numbers,
        private BranchContextService $branchContext
    ) {}

    private const TRACKED_FIELDS = ['branch_id', 'department_id', 'designation_id', 'reporting_manager_id', 'employment_status', 'basic_salary'];

    public function index(Request $request): JsonResponse
    {
        $q = Employee::query();
        $this->branchContext->applyScope($q);

        if ($request->search) {
            $q->where(fn($q) => $q
                ->where('first_name', 'like', "%{$request->search}%")
                ->orWhere('last_name', 'like', "%{$request->search}%")
                ->orWhere('employee_code', 'like', "%{$request->search}%")
                ->orWhere('personal_email', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%")
            );
        }
        if ($request->department_id) $q->where('department_id', $request->department_id);
        if ($request->designation_id) $q->where('designation_id', $request->designation_id);
        if ($request->employment_status) $q->where('employment_status', $request->employment_status);
        if ($request->is_active !== null) $q->where('is_active', $request->boolean('is_active'));

        $employees = $q->with(['branch', 'department', 'designation', 'reportingManager'])
            ->orderBy('first_name')
            ->paginate($request->input('per_page', 50));

        return response()->json($employees);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id|unique:employees,user_id',
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'reporting_manager_id' => 'nullable|exists:employees,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'nic_passport' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'personal_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'phone2' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'bank_name' => 'nullable|string|max:100',
            'bank_branch' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'basic_salary' => 'nullable|numeric|min:0',
            'epf_etf_applicable' => 'nullable|boolean',
            'employment_type' => 'nullable|in:full_time,part_time,contract,intern',
            'join_date' => 'required|date',
            'probation_period_months' => 'nullable|integer|min:0',
            'confirmation_date' => 'nullable|date',
            'employment_status' => 'nullable|in:active,on_leave,suspended,terminated',
            'exit_date' => 'nullable|date',
            'exit_reason' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
            'photo' => 'nullable|file|max:2048|extensions:jpg,jpeg,png,gif,webp',
        ]);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $ext = $file->getClientOriginalExtension();
            $data['photo_path'] = $file->storeAs('employees/photos', Str::random(40) . ($ext ? '.' . $ext : ''), 'public');
        }
        unset($data['photo']);

        return DB::transaction(function () use ($data) {
            $data['employee_code'] = $this->numbers->employeeCode();
            $data['created_by'] = auth()->id();
            $employee = Employee::create($data);

            EmployeeHistory::create([
                'employee_id' => $employee->id,
                'field_changed' => 'employment_status',
                'old_value' => null,
                'new_value' => $employee->employment_status,
                'effective_date' => $employee->join_date,
                'changed_by' => auth()->id(),
                'notes' => 'Employee record created.',
            ]);

            return response()->json($employee->load(['branch', 'department', 'designation', 'reportingManager']), 201);
        }, 5);
    }

    public function show(Employee $employee): JsonResponse
    {
        $employee->load([
            'branch', 'department', 'designation', 'reportingManager', 'user',
            'directReports' => fn($q) => $q->select('id', 'first_name', 'last_name', 'employee_code', 'designation_id', 'photo_path', 'reporting_manager_id'),
            'documents' => fn($q) => $q->latest(),
            'history' => fn($q) => $q->latest('effective_date')->latest('id'),
            'payslips' => fn($q) => $q->with('payrollRun:id,month,year,status')->latest(),
        ]);
        return response()->json($employee);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id|unique:employees,user_id,' . $employee->id,
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'reporting_manager_id' => 'nullable|exists:employees,id|not_in:' . $employee->id,
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'nic_passport' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'personal_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'phone2' => 'nullable|string|max:30',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:30',
            'bank_name' => 'nullable|string|max:100',
            'bank_branch' => 'nullable|string|max:100',
            'bank_account_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'basic_salary' => 'nullable|numeric|min:0',
            'epf_etf_applicable' => 'nullable|boolean',
            'employment_type' => 'nullable|in:full_time,part_time,contract,intern',
            'join_date' => 'sometimes|date',
            'probation_period_months' => 'nullable|integer|min:0',
            'confirmation_date' => 'nullable|date',
            'employment_status' => 'nullable|in:active,on_leave,suspended,terminated',
            'exit_date' => 'nullable|date',
            'exit_reason' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'notes' => 'nullable|string',
            'photo' => 'nullable|file|max:2048|extensions:jpg,jpeg,png,gif,webp',
            'remove_photo' => 'nullable|boolean',
        ]);

        if ($request->hasFile('photo')) {
            if ($employee->photo_path) Storage::disk('public')->delete($employee->photo_path);
            $file = $request->file('photo');
            $ext = $file->getClientOriginalExtension();
            $data['photo_path'] = $file->storeAs('employees/photos', Str::random(40) . ($ext ? '.' . $ext : ''), 'public');
        } elseif ($request->boolean('remove_photo')) {
            if ($employee->photo_path) Storage::disk('public')->delete($employee->photo_path);
            $data['photo_path'] = null;
        }
        unset($data['photo'], $data['remove_photo']);

        DB::transaction(function () use ($data, $employee) {
            $changes = [];
            foreach (self::TRACKED_FIELDS as $field) {
                if (array_key_exists($field, $data) && $data[$field] != $employee->{$field}) {
                    $changes[$field] = ['old' => $employee->{$field}, 'new' => $data[$field]];
                }
            }

            $employee->update($data);

            foreach ($changes as $field => $change) {
                EmployeeHistory::create([
                    'employee_id' => $employee->id,
                    'field_changed' => $field,
                    'old_value' => $change['old'] !== null ? (string) $change['old'] : null,
                    'new_value' => $change['new'] !== null ? (string) $change['new'] : null,
                    'effective_date' => now()->toDateString(),
                    'changed_by' => auth()->id(),
                ]);
            }
        }, 5);

        return response()->json($employee->fresh(['branch', 'department', 'designation', 'reportingManager']));
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $employee->delete();
        return response()->json(['message' => 'Employee deleted.']);
    }

    public function orgChart(): JsonResponse
    {
        $q = Employee::where('is_active', true)->with(['designation', 'department']);
        $this->branchContext->applyScope($q);
        $employees = $q->get(['id', 'first_name', 'last_name', 'employee_code', 'designation_id', 'department_id', 'branch_id', 'photo_path', 'reporting_manager_id']);

        $byManager = $employees->groupBy('reporting_manager_id');

        $build = function ($managerId) use (&$build, $byManager) {
            return ($byManager->get($managerId) ?? collect())->map(fn($e) => [
                'id' => $e->id,
                'name' => $e->full_name,
                'employee_code' => $e->employee_code,
                'designation' => $e->designation?->name,
                'department' => $e->department?->name,
                'photo_path' => $e->photo_path,
                'reports' => $build($e->id),
            ])->values();
        };

        return response()->json($build(null));
    }

    public function history(Employee $employee): JsonResponse
    {
        return response()->json($employee->history()->with('changedBy:id,name')->latest('effective_date')->latest('id')->get());
    }

    public function photo(Employee $employee)
    {
        $path = $employee->photo_path;
        abort_unless($path && Storage::disk('public')->exists($path), 404);
        $mimes = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png', 'gif' => 'image/gif', 'webp' => 'image/webp'];
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return response(Storage::disk('public')->get($path), 200, [
            'Content-Type' => $mimes[$ext] ?? 'application/octet-stream',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }

    public function bulkImport(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|extensions:csv,xlsx,xls',
        ]);

        $path = $request->file('file')->getRealPath();
        $rows = \PhpOffice\PhpSpreadsheet\IOFactory::load($path)->getActiveSheet()->toArray(null, true, true, false);

        if (empty($rows)) {
            return response()->json(['message' => 'The file is empty.'], 422);
        }

        $header = array_map(fn($h) => strtolower(trim((string) $h)), array_shift($rows));
        $col = array_flip($header);

        $required = ['first_name', 'join_date'];
        foreach ($required as $r) {
            if (!isset($col[$r])) {
                return response()->json(['message' => "Missing required column: {$r}. Expected columns: first_name, last_name, branch_code, department_code, designation_code, join_date, employment_type, phone, personal_email, nic_passport."], 422);
            }
        }

        $branches = \App\Models\Branch::pluck('id', 'code')->mapWithKeys(fn($id, $code) => [strtoupper($code) => $id]);
        $departments = \App\Models\Department::pluck('id', 'code')->mapWithKeys(fn($id, $code) => [strtoupper($code) => $id]);
        $designations = \App\Models\Designation::pluck('id', 'code')->mapWithKeys(fn($id, $code) => [strtoupper($code) => $id]);

        $imported = 0;
        $skipped = [];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2; // +1 for 0-index, +1 for header row
            $get = fn($key) => isset($col[$key]) ? trim((string) ($row[$col[$key]] ?? '')) : '';

            $firstName = $get('first_name');
            $joinDateRaw = $get('join_date');
            if ($firstName === '' || $joinDateRaw === '') {
                $skipped[] = "Row {$rowNum}: missing first_name or join_date.";
                continue;
            }

            try {
                $joinDate = \Carbon\Carbon::parse($joinDateRaw)->toDateString();
            } catch (\Exception $e) {
                $skipped[] = "Row {$rowNum}: unrecognized join_date '{$joinDateRaw}'.";
                continue;
            }

            $branchCode = strtoupper($get('branch_code'));
            $deptCode = strtoupper($get('department_code'));
            $desigCode = strtoupper($get('designation_code'));

            if ($branchCode && !isset($branches[$branchCode])) {
                $skipped[] = "Row {$rowNum}: unknown branch_code '{$branchCode}'.";
                continue;
            }
            if ($deptCode && !isset($departments[$deptCode])) {
                $skipped[] = "Row {$rowNum}: unknown department_code '{$deptCode}'.";
                continue;
            }
            if ($desigCode && !isset($designations[$desigCode])) {
                $skipped[] = "Row {$rowNum}: unknown designation_code '{$desigCode}'.";
                continue;
            }

            $employmentType = strtolower($get('employment_type')) ?: 'full_time';
            if (!in_array($employmentType, ['full_time', 'part_time', 'contract', 'intern'])) {
                $employmentType = 'full_time';
            }

            $email = $get('personal_email');
            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $skipped[] = "Row {$rowNum}: invalid personal_email '{$email}'.";
                continue;
            }

            DB::transaction(function () use (
                $firstName, $get, $joinDate, $branchCode, $deptCode, $desigCode,
                $branches, $departments, $designations, $employmentType, $email
            ) {
                $employee = Employee::create([
                    'employee_code' => $this->numbers->employeeCode(),
                    'branch_id' => $branchCode ? $branches[$branchCode] : null,
                    'department_id' => $deptCode ? $departments[$deptCode] : null,
                    'designation_id' => $desigCode ? $designations[$desigCode] : null,
                    'first_name' => $firstName,
                    'last_name' => $get('last_name') ?: null,
                    'phone' => $get('phone') ?: null,
                    'personal_email' => $email ?: null,
                    'nic_passport' => $get('nic_passport') ?: null,
                    'employment_type' => $employmentType,
                    'join_date' => $joinDate,
                    'employment_status' => 'active',
                    'created_by' => auth()->id(),
                ]);

                EmployeeHistory::create([
                    'employee_id' => $employee->id,
                    'field_changed' => 'employment_status',
                    'old_value' => null,
                    'new_value' => 'active',
                    'effective_date' => $joinDate,
                    'changed_by' => auth()->id(),
                    'notes' => 'Imported via bulk CSV/Excel import.',
                ]);
            }, 5);

            $imported++;
        }

        return response()->json([
            'imported' => $imported,
            'skipped_count' => count($skipped),
            'skipped' => $skipped,
        ]);
    }
}
