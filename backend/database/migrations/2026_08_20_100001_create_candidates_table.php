<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_opening_id')->nullable()->constrained('job_openings')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('resume_path')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('source')->nullable();
            $table->enum('status', ['applied', 'screening', 'interview', 'offer', 'hired', 'rejected', 'withdrawn'])->default('applied');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('notes')->nullable();
            $table->decimal('offered_salary', 12, 2)->nullable();
            $table->date('offer_date')->nullable();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('job_opening_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
