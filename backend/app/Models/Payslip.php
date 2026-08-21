<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_id', 'employee_id', 'basic_salary', 'total_allowances', 'total_deductions',
        'unpaid_leave_days', 'unpaid_leave_deduction', 'gross_pay',
        'epf_employee', 'epf_employer', 'etf_employer', 'net_pay', 'components',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'unpaid_leave_days' => 'decimal:1',
        'unpaid_leave_deduction' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'epf_employee' => 'decimal:2',
        'epf_employer' => 'decimal:2',
        'etf_employer' => 'decimal:2',
        'net_pay' => 'decimal:2',
        'components' => 'array',
    ];

    public function payrollRun(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
