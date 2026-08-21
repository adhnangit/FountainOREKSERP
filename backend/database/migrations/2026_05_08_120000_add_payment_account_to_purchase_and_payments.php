<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->after('reference');
            $table->string('cheque_type', 20)->nullable()->after('account_id');       // 'issued' | 'received'
            $table->string('cheque_number', 50)->nullable()->after('cheque_type');
            $table->string('cheque_bank_name', 100)->nullable()->after('cheque_number');
            $table->date('cheque_date')->nullable()->after('cheque_bank_name');
            $table->unsignedBigInteger('received_cheque_id')->nullable()->after('cheque_date');
        });

        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->after('reference_number');
            $table->string('cheque_type', 20)->nullable()->after('account_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['account_id', 'cheque_type', 'cheque_number', 'cheque_bank_name', 'cheque_date', 'received_cheque_id']);
        });

        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropColumn(['account_id', 'cheque_type']);
        });
    }
};
