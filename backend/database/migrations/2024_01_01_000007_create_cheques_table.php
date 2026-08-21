<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cheques', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->enum('direction', ['received', 'issued']); // received from customer, issued to supplier
            $table->unsignedBigInteger('party_id');             // customer_id or supplier_id
            $table->enum('party_type', ['customer', 'supplier']);
            $table->string('cheque_number');
            $table->string('bank_name');
            $table->string('bank_branch')->nullable();
            $table->date('cheque_date');
            $table->date('received_issued_date');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['in_hand', 'deposited', 'cleared', 'bounced', 'cancelled', 'returned'])->default('in_hand');
            $table->date('deposited_date')->nullable();
            $table->date('cleared_date')->nullable();
            $table->date('bounced_date')->nullable();
            $table->text('bounce_reason')->nullable();
            $table->boolean('is_re_presented')->default(false);
            $table->unsignedBigInteger('original_cheque_id')->nullable();
            $table->string('deposit_slip_number')->nullable();
            $table->text('notes')->nullable();
            $table->string('attachment')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['branch_id', 'status']);
            $table->index(['party_type', 'party_id']);
        });

        Schema::create('cheque_invoice_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cheque_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('invoice_id');
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cheque_invoice_links');
        Schema::dropIfExists('cheques');
    }
};
