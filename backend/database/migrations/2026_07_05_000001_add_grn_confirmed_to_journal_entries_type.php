<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE journal_entries MODIFY COLUMN type ENUM(
            'manual','sales','purchase','payment_received','payment_made',
            'expense','adjustment','cheque_bounced','grn_confirmed'
        ) NOT NULL DEFAULT 'manual'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE journal_entries MODIFY COLUMN type ENUM(
            'manual','sales','purchase','payment_received','payment_made',
            'expense','adjustment','cheque_bounced'
        ) NOT NULL DEFAULT 'manual'");
    }
};
