<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_task_followups', function (Blueprint $table) {
            $table->foreignId('subtask_id')->nullable()->after('task_id')
                ->constrained('work_task_subtasks')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('work_task_followups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subtask_id');
        });
    }
};
