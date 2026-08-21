<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM(
            'draft','proforma','sent','confirmed','partially_paid','paid','cancelled','converted'
        ) NOT NULL DEFAULT 'draft'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE invoices MODIFY COLUMN status ENUM(
            'draft','proforma','confirmed','partially_paid','paid','cancelled'
        ) NOT NULL DEFAULT 'draft'");
    }
};
