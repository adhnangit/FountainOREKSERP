<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            // Denormalized from employees.branch_id at write time so attendance rows can be
            // branch-scoped directly, the same way invoice_payments.account_id records the
            // exact account used rather than re-deriving it later.
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'half_day', 'late', 'on_leave', 'holiday', 'weekend'])->default('present');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->decimal('work_hours', 5, 2)->nullable();
            $table->unsignedSmallInteger('late_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('source', ['manual', 'bulk', 'import'])->default('manual');
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
            $table->index('branch_id');
            $table->index('date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
