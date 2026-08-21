<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\Payslip;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    // Sri Lankan statutory rates — hardcoded for this first version, not yet configurable.
    private const EPF_EMPLOYEE_RATE = 0.08;
    private const EPF_EMPLOYER_RATE = 0.12;
    private const ETF_EMPLOYER_RATE = 0.03;

    // Simple flat divisor for pro-rating unpaid leave against basic salary — not calendar-aware
    // (doesn't vary by how many days are actually in the given month).
    private const STANDARD_WORKING_DAYS = 30;

    public function generateRun(PayrollRun $run): void
    {
        if ($run->status !== 'draft') {
            throw new \RuntimeException('Only a draft payroll run can be generated or regenerated.');
        }

        $employees = Employee::where('branch_id', $run->branch_id)->where('is_active', true)->get();

        DB::transaction(function () use ($run, $employees) {
            foreach ($employees as $employee) {
                $this->generatePayslip($run, $employee);
            }
        }, 5);
    }

    private function generatePayslip(PayrollRun $run, Employee $employee): Payslip
    {
        $basicSalary = (float) ($employee->basic_salary ?? 0);

        $components = $employee->salaryComponents()->where('is_active', true)->get();
        $allowances = (float) $components->where('type', 'allowance')->sum('amount');
        $deductions = (float) $components->where('type', 'deduction')->sum('amount');

        $grossPay = $basicSalary + $allowances;

        $unpaidLeaveDays = $this->unpaidLeaveDays((int) $employee->id, (int) $run->month, (int) $run->year);
        $unpaidLeaveDeduction = ($unpaidLeaveDays > 0 && $basicSalary > 0)
            ? round(($basicSalary / self::STANDARD_WORKING_DAYS) * $unpaidLeaveDays, 2)
            : 0.0;

        $epfEmployee = $epfEmployer = $etfEmployer = 0.0;
        if ($employee->epf_etf_applicable) {
            $epfEmployee = round($grossPay * self::EPF_EMPLOYEE_RATE, 2);
            $epfEmployer = round($grossPay * self::EPF_EMPLOYER_RATE, 2);
            $etfEmployer = round($grossPay * self::ETF_EMPLOYER_RATE, 2);
        }

        $netPay = round($grossPay - $deductions - $unpaidLeaveDeduction - $epfEmployee, 2);

        return Payslip::updateOrCreate(
            ['payroll_run_id' => $run->id, 'employee_id' => $employee->id],
            [
                'basic_salary' => $basicSalary,
                'total_allowances' => $allowances,
                'total_deductions' => $deductions,
                'unpaid_leave_days' => $unpaidLeaveDays,
                'unpaid_leave_deduction' => $unpaidLeaveDeduction,
                'gross_pay' => $grossPay,
                'epf_employee' => $epfEmployee,
                'epf_employer' => $epfEmployer,
                'etf_employer' => $etfEmployer,
                'net_pay' => $netPay,
                'components' => $components->map(fn($c) => [
                    'name' => $c->name, 'type' => $c->type, 'amount' => (float) $c->amount,
                ])->values()->all(),
            ]
        );
    }

    /** Absent days count fully; unpaid-leave-type days (from Module 3) count full or half. */
    private function unpaidLeaveDays(int $employeeId, int $month, int $year): float
    {
        $absent = Attendance::where('employee_id', $employeeId)
            ->whereYear('date', $year)->whereMonth('date', $month)
            ->where('status', 'absent')->count();

        $unpaidLeaveRows = Attendance::where('employee_id', $employeeId)
            ->whereYear('date', $year)->whereMonth('date', $month)
            ->whereIn('status', ['on_leave', 'half_day'])
            ->whereHas('leaveRequest.leaveType', fn($q) => $q->where('is_paid', false))
            ->get(['status']);

        $unpaidLeaveDays = $unpaidLeaveRows->sum(fn($a) => $a->status === 'half_day' ? 0.5 : 1);

        return (float) $absent + $unpaidLeaveDays;
    }
}
