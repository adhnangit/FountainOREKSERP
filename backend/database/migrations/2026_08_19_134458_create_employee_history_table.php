<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('field_changed');
            $table->string('old_value')->nullable();
            $table->string('new_value')->nullable();
            $table->date('effective_date')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('employee_id');
            $table->index('field_changed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_history');
    }
};
