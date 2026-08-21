<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('batch_code')->unique();
            $table->string('batch_number')->nullable();
            $table->foreignId('grn_item_id')->nullable()->constrained('grn_items')->nullOnDelete();
            $table->enum('source_type', ['grn', 'opening_migration', 'opening', 'adjustment_in', 'transfer_in', 'backorder'])->default('grn');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('cost_price', 15, 2);
            $table->decimal('selling_price', 15, 2);
            $table->decimal('quantity_received', 12, 2);
            $table->decimal('quantity_remaining', 12, 2);
            $table->date('received_date');
            $table->date('expiry_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'branch_id', 'received_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
