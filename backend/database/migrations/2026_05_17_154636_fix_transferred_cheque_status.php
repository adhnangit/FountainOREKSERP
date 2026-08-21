<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Received customer cheques used to pay suppliers were incorrectly
    // marked 'deposited'. Update them to 'transferred'.
    public function up(): void
    {
        DB::statement("
            UPDATE cheques
            SET status = 'transferred'
            WHERE status = 'deposited'
              AND direction = 'received'
              AND (
                id IN (
                    SELECT received_cheque_id FROM purchase_orders
                    WHERE cheque_type = 'received' AND received_cheque_id IS NOT NULL
                )
                OR id IN (
                    SELECT cheque_id FROM supplier_payments
                    WHERE cheque_type = 'received' AND cheque_id IS NOT NULL
                )
              )
        ");
    }

    public function down(): void
    {
        DB::statement("
            UPDATE cheques
            SET status = 'deposited'
            WHERE status = 'transferred'
              AND direction = 'received'
              AND (
                id IN (
                    SELECT received_cheque_id FROM purchase_orders
                    WHERE cheque_type = 'received' AND received_cheque_id IS NOT NULL
                )
                OR id IN (
                    SELECT cheque_id FROM supplier_payments
                    WHERE cheque_type = 'received' AND cheque_id IS NOT NULL
                )
              )
        ");
    }
};
