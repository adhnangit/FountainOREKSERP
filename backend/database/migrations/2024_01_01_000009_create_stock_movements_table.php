<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['purchase_in', 'sale_out', 'sale_return', 'purchase_return', 'adjustment_in', 'adjustment_out', 'transfer_in', 'transfer_out', 'opening'])->index();
            $table->string('reference_type')->nullable(); // invoice, grn, transfer, adjustment
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->decimal('balance_quantity', 12, 2)->default(0);
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('serial_number')->nullable();
            $table->text('notes')->nullable();
            $table->date('movement_date');
            $table->timestamps();
            $table->index(['product_id', 'branch_id', 'movement_date']);
        });

        Schema::create('inter_branch_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('to_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('dispatched_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('transfer_number')->unique();
            $table->enum('status', ['requested', 'approved', 'dispatched', 'received', 'cancelled'])->default('requested');
            $table->date('request_date');
            $table->date('dispatch_date')->nullable();
            $table->date('received_date')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('inter_branch_transfers')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->decimal('requested_quantity', 12, 2);
            $table->decimal('dispatched_quantity', 12, 2)->default(0);
            $table->decimal('received_quantity', 12, 2)->default(0);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->string('batch_number')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('adjustment_number')->unique();
            $table->enum('status', ['draft', 'approved', 'cancelled'])->default('draft');
            $table->date('adjustment_date');
            $table->text('reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adjustment_id')->constrained('stock_adjustments')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('system_quantity', 12, 2);
            $table->decimal('physical_quantity', 12, 2);
            $table->decimal('difference', 12, 2);
            $table->decimal('unit_cost', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('transfer_items');
        Schema::dropIfExists('inter_branch_transfers');
        Schema::dropIfExists('stock_movements');
    }
};
