<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->decimal('basic_salary', 12, 2)->default(0);
            $table->decimal('total_allowances', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('unpaid_leave_days', 5, 1)->default(0);
            $table->decimal('unpaid_leave_deduction', 12, 2)->default(0);
            $table->decimal('gross_pay', 12, 2)->default(0);
            $table->decimal('epf_employee', 12, 2)->default(0);
            $table->decimal('epf_employer', 12, 2)->default(0);
            $table->decimal('etf_employer', 12, 2)->default(0);
            $table->decimal('net_pay', 12, 2)->default(0);
            // Snapshot of the allowance/deduction breakdown at generation time, so a payslip
            // stays historically accurate even if salary_components change later — the same
            // "never edit posted history" reasoning as invoice_items.unit_cost snapshotting.
            $table->json('components')->nullable();
            $table->timestamps();

            $table->unique(['payroll_run_id', 'employee_id']);
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
