<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Manager Portal: scoped to "employees who report to me" (employees.reporting_manager_id),
 * not to any hr.* permission. A branch_manager or sales_person with zero HR permissions
 * can still manage their own direct reports' attendance and leave here — "manager" is
 * data-driven per the Module 1 architecture decision, not a separate Spatie role.
 */
class ManagerController extends Controller
{
    private function currentEmployee(): Employee
    {
        return Employee::where('user_id', auth()->id())->firstOrFail();
    }

    private function teamIds(): array
    {
        return Employee::where('reporting_manager_id', $this->currentEmployee()->id)->pluck('id')->all();
    }

    public function team(): JsonResponse
    {
        $team = Employee::where('reporting_manager_id', $this->currentEmployee()->id)
            ->with(['department', 'designation', 'branch'])
            ->orderBy('first_name')
            ->get();
        return response()->json($team);
    }

    public function teamAttendanceForDate(Request $request): JsonResponse
    {
        $request->validate(['date' => 'required|date']);
        $teamIds = $this->teamIds();

        $employees = Employee::whereIn('id', $teamIds)->with(['department', 'designation'])->orderBy('first_name')->get();
        $existing = \App\Models\Attendance::whereDate('date', $request->date)->whereIn('employee_id', $teamIds)->get()->keyBy('employee_id');

        return response()->json($employees->map(fn($e) => [
            'employee_id' => $e->id,
            'employee_code' => $e->employee_code,
            'name' => $e->full_name,
            'department' => $e->department?->name,
            'designation' => $e->designation?->name,
            'attendance' => $existing->get($e->id),
        ])->values());
    }

    public function markTeamAttendance(Request $request, AttendanceController $attendance): JsonResponse
    {
        $teamIds = $this->teamIds();
        $records = $request->input('records', []);
        foreach ($records as $rec) {
            abort_unless(in_array($rec['employee_id'] ?? null, $teamIds, true), 403, 'You can only mark attendance for your own direct reports.');
        }
        return $attendance->bulkMark($request);
    }

    public function teamLeaveRequests(Request $request): JsonResponse
    {
        $q = LeaveRequest::whereIn('employee_id', $this->teamIds())
            ->with(['employee:id,first_name,last_name,employee_code', 'leaveType']);
        if ($request->status) $q->where('status', $request->status);

        return response()->json($q->orderByDesc('start_date')->get());
    }

    public function approveTeamLeave(Request $request, LeaveRequest $leaveRequest, LeaveRequestController $leaveRequests): JsonResponse
    {
        abort_unless(in_array($leaveRequest->employee_id, $this->teamIds(), true), 403, 'This employee does not report to you.');
        return $leaveRequests->approve($request, $leaveRequest);
    }

    public function rejectTeamLeave(Request $request, LeaveRequest $leaveRequest, LeaveRequestController $leaveRequests): JsonResponse
    {
        abort_unless(in_array($leaveRequest->employee_id, $this->teamIds(), true), 403, 'This employee does not report to you.');
        return $leaveRequests->reject($request, $leaveRequest);
    }
}
