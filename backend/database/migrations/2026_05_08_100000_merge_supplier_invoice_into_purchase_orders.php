<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add payment tracking columns to purchase_orders
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->default(0)->after('total');
            $table->decimal('balance_due', 15, 2)->default(0)->after('paid_amount');
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid')->after('balance_due');
            $table->string('supplier_invoice_ref')->nullable()->after('reference'); // Supplier's own invoice number
            $table->date('invoice_date')->nullable()->after('order_date');
            $table->date('due_date')->nullable()->after('expected_date');
        });

        // Make supplier_invoice_id nullable in supplier_payments so PO payments can use same table
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropForeign(['supplier_invoice_id']);
        });
        DB::statement('ALTER TABLE supplier_payments MODIFY COLUMN supplier_invoice_id BIGINT UNSIGNED NULL');
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->foreign('supplier_invoice_id')->references('id')->on('supplier_invoices')->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->after('supplier_invoice_id')
                  ->constrained('purchase_orders')->nullOnDelete();
        });

        // Seed balance_due for any existing POs
        DB::statement('UPDATE purchase_orders SET balance_due = total WHERE balance_due = 0 AND total > 0');
    }

    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->dropColumn('purchase_order_id');
            $table->dropForeign(['supplier_invoice_id']);
        });
        DB::statement('ALTER TABLE supplier_payments MODIFY COLUMN supplier_invoice_id BIGINT UNSIGNED NOT NULL');
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->foreign('supplier_invoice_id')->references('id')->on('supplier_invoices')->cascadeOnDelete();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'balance_due', 'payment_status', 'supplier_invoice_ref', 'invoice_date', 'due_date']);
        });
    }
};
