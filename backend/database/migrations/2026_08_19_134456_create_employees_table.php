<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->string('employee_code')->unique();

            // Employment placement
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->foreignId('reporting_manager_id')->nullable()->constrained('employees')->nullOnDelete();

            // Personal info
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('nic_passport')->nullable();
            $table->string('nationality')->nullable();
            $table->string('photo_path')->nullable();

            // Contact info
            $table->string('personal_email')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone2')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();

            // Emergency contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relationship')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            // Bank details (for payroll)
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();

            // Employment details
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'intern'])->default('full_time');
            $table->date('join_date');
            $table->unsignedSmallInteger('probation_period_months')->nullable();
            $table->date('confirmation_date')->nullable();
            $table->enum('employment_status', ['active', 'on_leave', 'suspended', 'terminated'])->default('active');
            $table->date('exit_date')->nullable();
            $table->text('exit_reason')->nullable();

            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('branch_id');
            $table->index('department_id');
            $table->index('designation_id');
            $table->index('reporting_manager_id');
            $table->index('employment_status');
            $table->index('join_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
