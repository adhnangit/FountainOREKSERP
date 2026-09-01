<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_task_subtasks', function (Blueprint $table) {
            $table->enum('priority', ['Low', 'Medium', 'High'])->default('Medium')->after('title');
            $table->enum('status', ['Pending', 'In Progress', 'Completed', 'Cancelled'])->default('Pending')->after('completed');
        });
    }

    public function down(): void
    {
        Schema::table('work_task_subtasks', function (Blueprint $table) {
            $table->dropColumn(['priority', 'status']);
        });
    }
};
