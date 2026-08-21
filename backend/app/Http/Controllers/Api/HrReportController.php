<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\PayrollRun;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;

class HrReportController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    public function dashboard(): JsonResponse
    {
        $employeesQuery = Employee::query();
        // Qualified column — departments and branches both have their own branch_id, which
        // makes an unqualified "branch_id" ambiguous once byDepartment()/byBranch() below
        // join against them. applyScope() already supports passing a qualified column name.
        $this->branchContext->applyScope($employeesQuery, 'employees.branch_id');
        // Qualified with the table name — both departments and branches also have their own
        // is_active column, and byDepartment()/byBranch() below join against them, which
        // makes an unqualified "is_active" ambiguous to MySQL once the join is added.
        $activeEmployees = (clone $employeesQuery)->where('employees.is_active', true);

        $headcount = (clone $activeEmployees)->count();

        $byDepartment = (clone $activeEmployees)
            ->selectRaw('departments.name as department, count(*) as total')
            ->join('departments', 'departments.id', '=', 'employees.department_id')
            ->groupBy('departments.name')
            ->orderByDesc('total')
            ->get();

        $byBranch = (clone $activeEmployees)
            ->selectRaw('branches.name as branch, count(*) as total')
            ->join('branches', 'branches.id', '=', 'employees.branch_id')
            ->groupBy('branches.name')
            ->orderByDesc('total')
            ->get();

        $employeeIds = (clone $employeesQuery)->pluck('id');

        $thisMonthAttendance = Attendance::whereIn('employee_id', $employeeIds)
            ->whereYear('date', now()->year)->whereMonth('date', now()->month)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $leaveDaysThisMonth = (float) LeaveRequest::whereIn('employee_id', $employeeIds)
            ->where('status', 'approved')
            ->whereYear('start_date', now()->year)->whereMonth('start_date', now()->month)
            ->sum('total_days');

        $terminatedLast12Months = (clone $employeesQuery)
            ->where('employment_status', 'terminated')
            ->where('exit_date', '>=', now()->subMonths(12)->toDateString())
            ->count();

        $payrollTrend = PayrollRun::whereIn('branch_id', $this->branchContext->isAllBranches()
                ? \App\Models\Branch::pluck('id')
                : [$this->branchContext->getBranchId() ?? -1])
            ->where('status', 'paid')
            ->with('payslips:id,payroll_run_id,net_pay')
            ->get()
            ->groupBy(fn($run) => $run->year . '-' . str_pad($run->month, 2, '0', STR_PAD_LEFT))
            ->map(fn($runs, $period) => [
                'period' => $period,
                'total_net_pay' => round((float) $runs->flatMap->payslips->sum('net_pay'), 2),
            ])
            ->sortBy('period')
            ->values()
            ->take(-6);

        $openJobs = \App\Models\JobOpening::where('status', 'open')->count();
        $pendingLeave = LeaveRequest::whereIn('employee_id', $employeeIds)->where('status', 'pending')->count();

        return response()->json([
            'headcount' => $headcount,
            'by_department' => $byDepartment,
            'by_branch' => $byBranch,
            'attendance_this_month' => $thisMonthAttendance,
            'leave_days_this_month' => $leaveDaysThisMonth,
            'terminated_last_12_months' => $terminatedLast12Months,
            'payroll_trend' => $payrollTrend->values(),
            'open_job_openings' => $openJobs,
            'pending_leave_requests' => $pendingLeave,
        ]);
    }
}
