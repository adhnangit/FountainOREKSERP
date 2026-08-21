<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->enum('recurrence', ['none', 'weekly', 'monthly', 'yearly'])->default('none')->after('reminder_minutes');
            $table->json('recurrence_days')->nullable()->after('recurrence');
            $table->date('recurrence_end_date')->nullable()->after('recurrence_days');
        });

        Schema::create('calendar_event_completions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('calendar_events')->cascadeOnDelete();
            $table->date('occurrence_date');
            $table->foreignId('completed_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->unique(['event_id', 'occurrence_date']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_event_completions');
        Schema::table('calendar_events', function (Blueprint $table) {
            $table->dropColumn(['recurrence', 'recurrence_days', 'recurrence_end_date']);
        });
    }
};
