<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // null = branch target
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['revenue', 'quantity', 'new_customers'])->default('revenue');
            $table->enum('period', ['monthly', 'quarterly', 'annual'])->default('monthly');
            $table->integer('year');
            $table->integer('period_number'); // 1-12 for monthly, 1-4 for quarterly, 1 for annual
            $table->decimal('target_value', 15, 2);
            $table->decimal('achieved_value', 15, 2)->default(0);
            $table->decimal('alert_threshold_percent', 5, 2)->default(50);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['branch_id', 'year', 'period_number']);
            $table->index(['user_id', 'year', 'period_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};
