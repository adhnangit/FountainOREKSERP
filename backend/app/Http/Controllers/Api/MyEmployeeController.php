<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Payslip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Employee Self-Service: every endpoint here scopes to "the Employee record linked to the
 * currently logged-in user" — gated only by having that link (employees.user_id), never by
 * hr.* permissions. A sales_person with no HR permissions at all can still see their own
 * leave balance and request time off; they just can't see anyone else's.
 */
class MyEmployeeController extends Controller
{
    private function currentEmployee(): Employee
    {
        return Employee::where('user_id', auth()->id())->firstOrFail();
    }

    public function profile(): JsonResponse
    {
        $employee = $this->currentEmployee()->load(['branch', 'department', 'designation', 'reportingManager']);
        return response()->json($employee);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $employee = $this->currentEmployee();

        // Deliberately narrow — an employee can keep their own contact details current,
        // but salary, branch/department/designation and employment status stay HR-managed.
        $data = $request->validate([
            'phone' => 'nullable|string|max:30',
            'phone2' => 'nullable|string|max:30',
            'personal_email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:30',
        ]);

        $employee->update($data);
        return response()->json($employee->fresh());
    }

    public function leaveBalances(): JsonResponse
    {
        $employee = $this->currentEmployee();
        $year = (int) (request('year') ?? now()->year);

        $types = LeaveType::where('is_active', true)->whereNotNull('max_days_per_year')->orderBy('name')->get();
        $balances = LeaveBalance::where('employee_id', $employee->id)->where('year', $year)->get()->keyBy('leave_type_id');

        return response()->json($types->map(fn($t) => $balances->get($t->id) ?? [
            'leave_type_id' => $t->id, 'leave_type' => $t, 'year' => $year,
            'allocated_days' => 0, 'used_days' => 0, 'carried_forward' => 0, 'remaining_days' => 0,
        ])->values());
    }

    public function leaveRequests(): JsonResponse
    {
        $employee = $this->currentEmployee();
        return response()->json(
            LeaveRequest::where('employee_id', $employee->id)->with('leaveType')->orderByDesc('start_date')->get()
        );
    }

    /** Delegates the actual creation to LeaveRequestController::store() so overlap checks, half-day rules and auto-approval logic aren't duplicated — only the employee_id is forced here. */
    public function requestLeave(Request $request, LeaveRequestController $leaveRequests): JsonResponse
    {
        $employee = $this->currentEmployee();
        $request->merge(['employee_id' => $employee->id]);
        return $leaveRequests->store($request);
    }

    public function cancelLeaveRequest(Request $request, LeaveRequest $leaveRequest, LeaveRequestController $leaveRequests): JsonResponse
    {
        abort_unless($leaveRequest->employee_id === $this->currentEmployee()->id, 403);
        return $leaveRequests->cancel($request, $leaveRequest);
    }

    public function attendance(Request $request): JsonResponse
    {
        $employee = $this->currentEmployee();
        $q = Attendance::where('employee_id', $employee->id);
        if ($request->month) $q->whereMonth('date', $request->month);
        if ($request->year) $q->whereYear('date', $request->year);

        return response()->json($q->orderByDesc('date')->get());
    }

    public function documents(): JsonResponse
    {
        return response()->json($this->currentEmployee()->documents()->latest()->get());
    }

    public function checklistTasks(): JsonResponse
    {
        return response()->json($this->currentEmployee()->checklistTasks()->orderBy('sort_order')->get());
    }

    public function payslips(): JsonResponse
    {
        $employee = $this->currentEmployee();
        return response()->json(
            $employee->payslips()->with('payrollRun:id,month,year,status')->latest()->get()
        );
    }

    public function payslipPdf(Payslip $payslip)
    {
        abort_unless($payslip->employee_id === $this->currentEmployee()->id, 403);
        return app(PayrollRunController::class)->payslipPdf($payslip);
    }

    public function photo()
    {
        return app(EmployeeController::class)->photo($this->currentEmployee());
    }

    public function documentStream(\App\Models\EmployeeDocument $document)
    {
        abort_unless($document->employee_id === $this->currentEmployee()->id, 403);
        return app(EmployeeDocumentController::class)->stream($document);
    }
}
