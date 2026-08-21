<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Supplier;
use App\Services\AccountingService;
use Illuminate\Console\Command;

class SyncAccountingAccounts extends Command
{
    protected $signature   = 'accounting:sync-accounts';
    protected $description = 'Create AR/AP sub-ledger accounts for all existing customers and suppliers';

    public function handle(AccountingService $svc): int
    {
        $customers = Customer::whereNull('account_id')->get();
        $cCount = 0;
        foreach ($customers as $customer) {
            if ($svc->ensureCustomerAccount($customer)) {
                $cCount++;
                $this->line("  [AR] {$customer->name}");
            }
        }
        $this->info("Customer accounts created: {$cCount}");

        $suppliers = Supplier::whereNull('account_id')->get();
        $sCount = 0;
        foreach ($suppliers as $supplier) {
            if ($svc->ensureSupplierAccount($supplier)) {
                $sCount++;
                $this->line("  [AP] {$supplier->name}");
            }
        }
        $this->info("Supplier accounts created: {$sCount}");

        return self::SUCCESS;
    }
}
