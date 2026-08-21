<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make category_id nullable so expenses can use direct CoA accounts
        DB::statement('ALTER TABLE expenses MODIFY COLUMN category_id BIGINT UNSIGNED NULL');

        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->after('category_id');
            $table->unsignedBigInteger('payment_account_id')->nullable()->after('account_id');
            $table->string('reference_number', 100)->nullable()->after('payment_method');
            $table->string('cheque_number', 100)->nullable()->after('reference_number');
            $table->string('bank_name', 100)->nullable()->after('cheque_number');
            $table->date('cheque_date')->nullable()->after('bank_name');
            $table->unsignedBigInteger('cheque_id')->nullable()->after('cheque_date');

            $table->foreign('account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('payment_account_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('cheque_id')->references('id')->on('cheques')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropForeign(['payment_account_id']);
            $table->dropForeign(['cheque_id']);
            $table->dropColumn(['account_id', 'payment_account_id', 'reference_number', 'cheque_number', 'bank_name', 'cheque_date', 'cheque_id']);
        });

        DB::statement('ALTER TABLE expenses MODIFY COLUMN category_id BIGINT UNSIGNED NOT NULL');
    }
};
