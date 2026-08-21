<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('basic_salary', 12, 2)->nullable()->after('bank_account_number');
            $table->boolean('epf_etf_applicable')->default(true)->after('basic_salary');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['basic_salary', 'epf_etf_applicable']);
        });
    }
};
