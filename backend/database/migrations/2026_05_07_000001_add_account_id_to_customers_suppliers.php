<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete()->after('branch_id');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Account::class);
            $table->dropColumn('account_id');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Account::class);
            $table->dropColumn('account_id');
        });
    }
};
