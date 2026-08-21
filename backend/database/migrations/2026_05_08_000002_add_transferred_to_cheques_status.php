<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE cheques MODIFY COLUMN status ENUM('in_hand','deposited','cleared','bounced','cancelled','returned','transferred') NOT NULL DEFAULT 'in_hand'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE cheques MODIFY COLUMN status ENUM('in_hand','deposited','cleared','bounced','cancelled','returned') NOT NULL DEFAULT 'in_hand'");
    }
};
