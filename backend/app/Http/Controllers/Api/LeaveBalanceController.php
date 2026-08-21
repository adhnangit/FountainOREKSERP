<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveBalanceController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate(['year' => 'required|integer|min:2000']);

        $eq = Employee::where('is_active', true)->with(['department']);
        $this->branchContext->applyScope($eq);
        if ($request->department_id) $eq->where('department_id', $request->department_id);
        $employees = $eq->orderBy('first_name')->get();

        $typesQuery = LeaveType::where('is_active', true)->whereNotNull('max_days_per_year');
        if ($request->leave_type_id) $typesQuery->where('id', $request->leave_type_id);
        $types = $typesQuery->orderBy('name')->get();

        $balances = LeaveBalance::where('year', $request->year)
            ->whereIn('employee_id', $employees->pluck('id'))
            ->whereIn('leave_type_id', $types->pluck('id'))
            ->get()
            ->keyBy(fn($b) => $b->employee_id . '-' . $b->leave_type_id);

        $data = $employees->map(fn($e) => [
            'employee_id' => $e->id,
            'employee_code' => $e->employee_code,
            'name' => $e->full_name,
            'department' => $e->department?->name,
            'balances' => $types->map(fn($t) => $balances->get($e->id . '-' . $t->id) ?? [
                'employee_id' => $e->id,
                'leave_type_id' => $t->id,
                'year' => (int) $request->year,
                'allocated_days' => 0,
                'used_days' => 0,
                'carried_forward' => 0,
                'remaining_days' => 0,
            ])->values(),
        ]);

        return response()->json(['employees' => $data->values(), 'leave_types' => $types]);
    }

    /** Bulk-create/update balances for every active employee in scope for one leave type + year. */
    public function allocate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'year' => 'required|integer|min:2000',
            'allocated_days' => 'required|numeric|min:0',
            'overwrite_existing' => 'nullable|boolean',
        ]);

        $eq = Employee::where('is_active', true);
        $this->branchContext->applyScope($eq);
        if ($request->department_id) $eq->where('department_id', $request->department_id);
        $employeeIds = $eq->pluck('id');

        $overwrite = $data['overwrite_existing'] ?? false;

        $count = DB::transaction(function () use ($data, $employeeIds, $overwrite) {
            $n = 0;
            foreach ($employeeIds as $employeeId) {
                $existing = LeaveBalance::where('employee_id', $employeeId)
                    ->where('leave_type_id', $data['leave_type_id'])
                    ->where('year', $data['year'])
                    ->first();

                if ($existing && !$overwrite) {
                    continue;
                }

                LeaveBalance::updateOrCreate(
                    ['employee_id' => $employeeId, 'leave_type_id' => $data['leave_type_id'], 'year' => $data['year']],
                    ['allocated_days' => $data['allocated_days']]
                );
                $n++;
            }
            return $n;
        }, 5);

        return response()->json(['allocated' => $count]);
    }

    public function update(Request $request, LeaveBalance $leaveBalance): JsonResponse
    {
        $data = $request->validate([
            'allocated_days' => 'sometimes|numeric|min:0',
            'carried_forward' => 'sometimes|numeric|min:0',
        ]);

        $leaveBalance->update($data);
        return response()->json($leaveBalance->fresh());
    }
}
