<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE journal_entries MODIFY COLUMN type ENUM(
            'manual','sales','purchase','payment_received','payment_made','expense','adjustment',
            'cheque_bounced','grn_confirmed','sales_return','payment_reversed','purchase_return',
            'cogs','cogs_reversal','sales_reversed','opening_balance_payment','opening_balance_payment_reversed',
            'cheque_cleared'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE journal_entries MODIFY COLUMN type ENUM(
            'manual','sales','purchase','payment_received','payment_made','expense','adjustment',
            'cheque_bounced','grn_confirmed','sales_return','payment_reversed','purchase_return',
            'cogs','cogs_reversal','sales_reversed','opening_balance_payment','opening_balance_payment_reversed'
        ) NOT NULL");
    }
};
