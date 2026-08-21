<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'employee_code', 'branch_id', 'department_id', 'designation_id', 'reporting_manager_id',
        'first_name', 'last_name', 'date_of_birth', 'gender', 'marital_status', 'nic_passport', 'nationality', 'photo_path',
        'personal_email', 'phone', 'phone2', 'address', 'city', 'district',
        'emergency_contact_name', 'emergency_contact_relationship', 'emergency_contact_phone',
        'bank_name', 'bank_branch', 'bank_account_name', 'bank_account_number',
        'basic_salary', 'epf_etf_applicable',
        'employment_type', 'join_date', 'probation_period_months', 'confirmation_date',
        'employment_status', 'exit_date', 'exit_reason',
        'is_active', 'created_by', 'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'join_date' => 'date',
        'confirmation_date' => 'date',
        'exit_date' => 'date',
        'probation_period_months' => 'integer',
        'basic_salary' => 'decimal:2',
        'epf_etf_applicable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function designation(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    public function reportingManager(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'reporting_manager_id');
    }

    public function directReports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'reporting_manager_id');
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveBalances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function salaryComponents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SalaryComponent::class);
    }

    public function performanceReviews(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function performanceGoals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PerformanceGoal::class);
    }

    public function checklistTasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmployeeChecklistTask::class);
    }

    public function assetAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function payslips(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function history(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmployeeHistory::class);
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . ($this->last_name ?? ''));
    }
}
