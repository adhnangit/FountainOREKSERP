<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['meeting', 'customer_visit', 'follow_up', 'payment_reminder', 'delivery', 'other'])->default('other');
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->boolean('all_day')->default(false);
            $table->string('color')->default('#3B82F6');
            $table->string('location')->nullable();
            $table->boolean('is_company_wide')->default(false);
            $table->string('linked_module')->nullable();
            $table->unsignedBigInteger('linked_id')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->integer('reminder_minutes')->default(30);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('calendar_event_attendees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('calendar_events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['invited', 'accepted', 'declined'])->default('invited');
            $table->timestamps();
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->enum('status', ['todo', 'in_progress', 'on_hold', 'done', 'cancelled'])->default('todo');
            $table->date('due_date')->nullable();
            $table->string('linked_module')->nullable();
            $table->unsignedBigInteger('linked_id')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_frequency')->nullable();
            $table->date('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['assigned_to', 'status']);
            $table->index(['branch_id', 'status']);
        });

        Schema::create('task_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('comment');
            $table->string('attachment')->nullable();
            $table->timestamps();
        });

        Schema::create('office_directory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('contact_type', ['internal', 'external'])->default('internal');
            $table->string('name');
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('company')->nullable();
            $table->enum('category', ['employee', 'vendor', 'service_provider', 'hospital', 'clinic', 'courier', 'other'])->default('employee');
            $table->string('phone')->nullable();
            $table->string('phone2')->nullable();
            $table->string('email')->nullable();
            $table->string('extension')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_directory');
        Schema::dropIfExists('task_comments');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('calendar_event_attendees');
        Schema::dropIfExists('calendar_events');
    }
};
