<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // product_id must become optional — a service line item has no product.
        // The existing FK constraint stays intact; MySQL allows NULL through a
        // foreign key column without dropping/recreating the constraint.
        DB::statement('ALTER TABLE invoice_items MODIFY product_id BIGINT UNSIGNED NULL');

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('product_id')->constrained('services')->nullOnDelete();
            $table->enum('item_type', ['product', 'service'])->default('product')->after('service_id');
        });

        DB::statement("UPDATE invoice_items SET item_type = 'product' WHERE product_id IS NOT NULL");
    }

    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_id');
            $table->dropColumn('item_type');
        });
        DB::statement('ALTER TABLE invoice_items MODIFY product_id BIGINT UNSIGNED NOT NULL');
    }
};
