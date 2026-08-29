<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_adjustment_items', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('product_id')
                ->constrained('batches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_adjustment_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('batch_id');
        });
    }
};
