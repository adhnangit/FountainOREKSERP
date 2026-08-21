<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('payment_method')->default('on_account')->after('terms');
            $table->unsignedSmallInteger('payment_terms_days')->default(0)->after('payment_method');
            $table->string('reference')->nullable()->after('payment_terms_days');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_terms_days', 'reference']);
        });
    }
};
