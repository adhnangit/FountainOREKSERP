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
        Schema::table('invoice_items', function (Blueprint $table) {
            // Snapshot of the product's avg_cost at the moment this line was sold —
            // the same figure postCogsJournal() uses, captured once so the invoice's
            // per-item profit view always agrees with the COGS actually posted to
            // the books, even if the product's avg_cost changes later.
            $table->decimal('unit_cost', 15, 2)->nullable()->after('unit_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn('unit_cost');
        });
    }
};
