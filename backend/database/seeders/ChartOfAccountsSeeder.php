<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\FinancialYear;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        // Financial Year
        FinancialYear::firstOrCreate(
            ['name' => 'FY 2025-2026'],
            ['start_date' => '2025-04-01', 'end_date' => '2026-03-31', 'status' => 'active']
        );

        // ── Account Groups (flat — 13 categories) ────────────────────

        $gIncome    = $this->group('Income Accounts',              'INC_ACCTS',  'income',     null, 1);
        $gPurchase  = $this->group('Purchase Accounts',            'PUR_ACCTS',  'expense',    null, 2);
        $gOperExp   = $this->group('Operating Expense Accounts',   'OPR_EXP',    'expense',    null, 3);
        $gSalary    = $this->group('Salary Accounts',              'SAL_ACCTS',  'expense',    null, 4);
        $gBank      = $this->group('Bank Accounts',                'BANK_ACCTS', 'asset',      null, 5);
        $gCash      = $this->group('Cash Accounts',                'CASH_ACCTS', 'asset',      null, 6);
        $gCustomer  = $this->group('Customer Accounts',            'CUST_ACCTS', 'asset',      null, 7);
        $gSupplier  = $this->group('Supplier Accounts',            'SUPP_ACCTS', 'liability',  null, 8);
        $gEquity    = $this->group('Equity / Investment Accounts', 'EQUITY',     'equity',     null, 9);
        $gFixed     = $this->group('Fixed Asset Accounts',         'FIXED_ASST', 'asset',      null, 10);
        $gLiability = $this->group('Liability / Credit Accounts',  'LIAB_ACCTS', 'liability',  null, 11);
        $gLoan      = $this->group('Loan Accounts',                'LOAN_ACCTS', 'liability',  null, 12);
        $gAdvance   = $this->group('Advance / Prepayment Accounts','ADV_ACCTS',  'asset',      null, 13);

        // ── System Accounts (required for journal entry mappings) ────

        $accRev  = $this->account($gIncome->id,    'Product Sales',      '4001', 'income',    'credit');
        $accSRet = $this->account($gIncome->id,    'Sales Returns',      '4003', 'income',    'debit');
        $accSvc  = $this->account($gIncome->id,    'Service Income',     '4004', 'income',    'credit');
        $accCOGS = $this->account($gPurchase->id,  'Cost of Goods Sold', '5001', 'expense',   'debit');
        $accBank = $this->account($gBank->id,      'Main Bank Account',  '1101', 'asset',     'debit', 0, false, true);
        $accCash = $this->account($gCash->id,      'Cash on Hand',       '1001', 'asset',     'debit', 0, true,  false);
        $accAR   = $this->account($gCustomer->id,  'Trade Receivables',  '1201', 'asset',     'debit');
        $accAP   = $this->account($gSupplier->id,  'Trade Payables',     '2001', 'liability', 'credit');
        $accInv  = $this->account($gFixed->id,     'Stock in Hand',      '1301', 'asset',     'debit');
        $accTax  = $this->account($gLiability->id, 'VAT Payable',        '2101', 'liability', 'credit');

        // ── System Settings (default account mappings) ────────────────

        $mappings = [
            'acc_trade_receivables' => $accAR->id,
            'acc_trade_payables'    => $accAP->id,
            'acc_sales_revenue'     => $accRev->id,
            'acc_sales_returns'     => $accSRet->id,
            'acc_service_income'    => $accSvc->id,
            'acc_cogs'              => $accCOGS->id,
            'acc_inventory'         => $accInv->id,
            'acc_cash'              => $accCash->id,
            'acc_bank'              => $accBank->id,
            'acc_tax_payable'       => $accTax->id,
        ];

        foreach ($mappings as $key => $value) {
            SystemSetting::updateOrCreate(
                ['key' => $key, 'branch_id' => null],
                ['value' => (string) $value, 'group' => 'accounting']
            );
        }

        $this->command->info('✅  Chart of Accounts seeded: ' . AccountGroup::count() . ' groups, ' . Account::count() . ' accounts.');
    }

    private function group(string $name, string $code, string $type, ?int $parentId, int $sort = 0): AccountGroup
    {
        return AccountGroup::firstOrCreate(
            ['code' => $code],
            ['name' => $name, 'type' => $type, 'parent_id' => $parentId, 'sort_order' => $sort]
        );
    }

    private function account(
        int $groupId, string $name, string $code, string $type,
        string $normalBalance, float $openingBalance = 0,
        bool $isCash = false, bool $isBank = false
    ): Account {
        return Account::firstOrCreate(
            ['code' => $code],
            [
                'group_id'        => $groupId,
                'name'            => $name,
                'type'            => $type,
                'normal_balance'  => $normalBalance,
                'opening_balance' => $openingBalance,
                'is_cash_account' => $isCash,
                'is_bank_account' => $isBank,
                'is_active'       => true,
            ]
        );
    }
}
