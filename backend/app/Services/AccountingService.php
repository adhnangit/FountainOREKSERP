<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\Customer;
use App\Models\FinancialYear;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Supplier;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountingService
{
    public function __construct(private NumberGeneratorService $numbers) {}

    public function createEntry(
        int $branchId,
        string $type,
        string $description,
        string $date,
        array $lines,
        int $createdBy,
        string $referenceType = '',
        int $referenceId = 0,
        bool $autoPost = true
    ): ?JournalEntry {
        $year = FinancialYear::active();
        if (!$year) return null;

        // Skip if any required account ID is missing
        foreach ($lines as $line) {
            if (empty($line['account_id'])) return null;
        }

        return DB::transaction(function () use (
            $branchId, $type, $description, $date, $lines,
            $createdBy, $referenceType, $referenceId, $autoPost, $year
        ) {
            $totalDebit  = collect($lines)->sum('debit');
            $totalCredit = collect($lines)->sum('credit');

            $entry = JournalEntry::create([
                'branch_id'         => $branchId,
                'financial_year_id' => $year->id,
                'created_by'        => $createdBy,
                'entry_number'      => $this->numbers->journalNumber($branchId),
                'type'              => $type,
                'reference_type'    => $referenceType,
                'reference_id'      => $referenceId ?: null,
                'entry_date'        => $date,
                'description'       => $description,
                'total_debit'       => $totalDebit,
                'total_credit'      => $totalCredit,
                'status'            => $autoPost ? 'posted' : 'draft',
                'is_auto_generated' => true,
            ]);

            foreach ($lines as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $line['account_id'],
                    'debit'            => $line['debit'] ?? 0,
                    'credit'           => $line['credit'] ?? 0,
                    'description'      => $line['description'] ?? null,
                ]);
            }

            return $entry;
        });
    }

    public function getSetting(int $branchId, string $key): ?string
    {
        return SystemSetting::where(function ($q) use ($branchId) {
                $q->where('branch_id', $branchId)->orWhereNull('branch_id');
            })
            ->where('key', $key)
            ->orderByRaw('CASE WHEN branch_id IS NULL THEN 1 ELSE 0 END')
            ->value('value');
    }

    public function getAccountId(int $branchId, string $settingKey): int
    {
        return (int) ($this->getSetting($branchId, $settingKey) ?? 0);
    }

    /**
     * Auto-create an Accounts Receivable sub-ledger account for a customer.
     */
    public function ensureCustomerAccount(Customer $customer): ?Account
    {
        if ($customer->account_id) {
            return Account::find($customer->account_id);
        }

        $arGroup = AccountGroup::where('code', 'CUST_ACCTS')->first();
        if (!$arGroup) return null;

        $account = Account::create([
            'group_id'        => $arGroup->id,
            'name'            => $customer->name . ' - Receivable',
            'code'            => 'AR-' . $customer->code,
            'type'            => 'asset',
            'normal_balance'  => 'debit',
            'opening_balance' => (float) ($customer->opening_balance ?? 0),
            'branch_id'       => $customer->branch_id,
            'is_active'       => true,
            'description'     => 'AR sub-ledger for customer: ' . $customer->name,
        ]);

        $customer->updateQuietly(['account_id' => $account->id]);

        return $account;
    }

    /**
     * Reverse a cancelled invoice's original sales entry by mirroring its exact
     * lines with debit/credit swapped — safer than recomputing from the invoice's
     * current fields, since it can't drift from what was actually posted.
     */
    public function postSalesReversalJournal(Invoice $invoice, int $userId): void
    {
        try {
            $branchId = $invoice->branch_id;
            $original = JournalEntry::where('reference_type', 'invoice')->where('reference_id', $invoice->id)
                ->where('type', 'sales')->where('status', 'posted')->with('lines')->first();
            if (!$original) return;

            if (JournalEntry::where('reference_type', 'invoice')->where('reference_id', $invoice->id)
                    ->where('type', 'sales_reversed')->exists()) return;

            // Redirect the revenue line to a dedicated contra-revenue account so the cancellation
            // shows as its own P&L line (e.g. "Cancelled Sales") instead of silently shrinking
            // Sales Revenue with no trace of what happened.
            $revenueId   = $this->getAccountId($branchId, 'acc_sales_revenue');
            $cancelledId = $this->getAccountId($branchId, 'acc_sales_cancelled');

            $lines = $original->lines->map(function ($l) use ($revenueId, $cancelledId, $invoice) {
                $accountId = ($cancelledId && $l->account_id === $revenueId) ? $cancelledId : $l->account_id;
                return [
                    'account_id'  => $accountId,
                    'debit'       => (float) $l->credit,
                    'credit'      => (float) $l->debit,
                    'description' => "Cancellation reversal – {$invoice->invoice_number}",
                ];
            })->all();

            $this->createEntry(
                $branchId, 'sales_reversed', "Cancelled Invoice Reversal – {$invoice->invoice_number}",
                now()->toDateString(), $lines, $userId, 'invoice', $invoice->id
            );
        } catch (\Throwable $e) {
            Log::error('postSalesReversalJournal failed', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);
        }
    }

    public function postSalesJournal(Invoice $invoice, int $userId): void
    {
        try {
            $invoice->loadMissing(['customer', 'items']);
            $branchId  = $invoice->branch_id;
            $arId      = $invoice->customer->account_id
                         ?: $this->getAccountId($branchId, 'acc_trade_receivables');
            $revenueId = $this->getAccountId($branchId, 'acc_sales_revenue');
            $taxId     = $this->getAccountId($branchId, 'acc_tax_payable');

            if (!$arId || !$revenueId) return;

            if (JournalEntry::where('reference_type', 'invoice')
                    ->where('reference_id', $invoice->id)->where('type', 'sales')->exists()) return;

            $total     = (float) $invoice->total;
            $taxAmount = (float) $invoice->tax_amount;

            // Split net revenue between product sales and service income so each
            // shows as its own P&L line — every line item is net-of-tax already
            // (item.total includes item.tax_amount), so summing those per type
            // reconciles exactly to total - taxAmount without re-deriving tax splits.
            $productNet = 0.0;
            $serviceNet = 0.0;
            foreach ($invoice->items as $item) {
                $net = (float) $item->total - (float) $item->tax_amount;
                if ($item->service_id) { $serviceNet += $net; } else { $productNet += $net; }
            }
            $productNet = round($productNet, 2);
            $serviceNet = round($serviceNet, 2);

            $revenueBuckets = [];
            if ($productNet > 0) {
                $revenueBuckets[$revenueId] = ($revenueBuckets[$revenueId] ?? 0) + $productNet;
            }
            if ($serviceNet > 0) {
                // Falls back to the same Sales Revenue account if Service Income
                // hasn't been configured in Accounting Settings yet — the entry
                // still balances, it just isn't broken out as its own P&L line
                // until an admin sets acc_service_income.
                $serviceRevenueId = $this->getAccountId($branchId, 'acc_service_income') ?: $revenueId;
                $revenueBuckets[$serviceRevenueId] = ($revenueBuckets[$serviceRevenueId] ?? 0) + $serviceNet;
            }

            $lines = [
                ['account_id' => $arId, 'debit' => $total, 'credit' => 0, 'description' => "Invoice {$invoice->invoice_number}"],
            ];
            foreach ($revenueBuckets as $accountId => $amount) {
                $lines[] = ['account_id' => $accountId, 'debit' => 0, 'credit' => $amount, 'description' => "Sales – {$invoice->invoice_number}"];
            }
            if ($taxAmount > 0 && $taxId) {
                $lines[] = ['account_id' => $taxId, 'debit' => 0, 'credit' => $taxAmount, 'description' => "Tax – {$invoice->invoice_number}"];
            }

            $this->createEntry(
                $branchId, 'sales', "Sales Invoice {$invoice->invoice_number}",
                $invoice->invoice_date->toDateString(), $lines, $userId, 'invoice', $invoice->id
            );

            $this->postCogsJournal($invoice, $userId);
        } catch (\Throwable $e) {
            Log::error('postSalesJournal failed', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Post the cost side of a sale: Dr Cost of Goods Sold / Cr Inventory, valued
     * at each item's average cost at the moment of sale. Without this, Inventory
     * only ever grows (from purchases) and COGS never shows on the P&L.
     */
    public function postCogsJournal(Invoice $invoice, int $userId): void
    {
        try {
            $branchId    = $invoice->branch_id;
            $cogsId      = $this->getAccountId($branchId, 'acc_cogs');
            $inventoryId = $this->getAccountId($branchId, 'acc_inventory');
            if (!$cogsId || !$inventoryId) return;

            if (JournalEntry::where('reference_type', 'invoice')
                    ->where('reference_id', $invoice->id)->where('type', 'cogs')->exists()) return;

            $invoice->loadMissing('items');
            $totalCost = 0;
            $anyUncosted = false;
            foreach ($invoice->items as $item) {
                if (!$item->product_id) continue; // service lines have no inventory cost
                // Prefer the cost snapshot captured when the line was sold (matches
                // what the invoice's own profit view shows); fall back to a live
                // avg_cost lookup, and finally to the product's reference cost_price
                // when no purchase has ever established a real avg_cost (sold ahead
                // of the first GRN) — only for older items created before the
                // unit_cost snapshot existed.
                if ($item->unit_cost !== null) {
                    $unitCost = (float) $item->unit_cost;
                } else {
                    $unitCost = (float) (\App\Models\ProductBranchStock::where('product_id', $item->product_id)
                        ->where('branch_id', $branchId)->value('avg_cost'))
                        ?: (float) (\App\Models\Product::where('id', $item->product_id)->value('cost_price') ?? 0);
                }
                if ($unitCost <= 0) $anyUncosted = true;
                $totalCost += $unitCost * (float) $item->quantity;
            }
            $totalCost = round($totalCost, 2);
            $hasProductLines = $invoice->items->contains(fn($i) => (bool) $i->product_id);
            if ($totalCost <= 0) {
                if ($hasProductLines) {
                    Log::warning('postCogsJournal skipped: no cost basis available for any line', ['invoice_id' => $invoice->id]);
                }
                return; // pure-service invoices legitimately have no COGS
            }
            if ($anyUncosted) {
                Log::warning('postCogsJournal posted with one or more uncosted lines (no avg_cost or cost_price available)', ['invoice_id' => $invoice->id]);
            }

            $this->createEntry(
                $branchId, 'cogs', "Cost of Goods Sold – {$invoice->invoice_number}",
                $invoice->invoice_date->toDateString(),
                [
                    ['account_id' => $cogsId,      'debit' => $totalCost, 'credit' => 0,          'description' => "COGS – {$invoice->invoice_number}"],
                    ['account_id' => $inventoryId, 'debit' => 0,          'credit' => $totalCost, 'description' => "Inventory reduction – {$invoice->invoice_number}"],
                ],
                $userId, 'invoice', $invoice->id
            );
        } catch (\Throwable $e) {
            Log::error('postCogsJournal failed', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Reverse the cost side of a sale on a return: Dr Inventory / Cr Cost of Goods
     * Sold, for the exact cost value the returned goods were restocked at.
     */
    public function postCogsReversalJournal(Invoice $creditNote, int $branchId, float $costAmount, int $userId): void
    {
        try {
            $cogsId      = $this->getAccountId($branchId, 'acc_cogs');
            $inventoryId = $this->getAccountId($branchId, 'acc_inventory');
            if (!$cogsId || !$inventoryId || $costAmount <= 0) return;

            if (JournalEntry::where('reference_type', 'invoice')
                    ->where('reference_id', $creditNote->id)->where('type', 'cogs_reversal')->exists()) return;

            $costAmount = round($costAmount, 2);

            $this->createEntry(
                $branchId, 'cogs_reversal', "Cost reversal – {$creditNote->invoice_number}",
                $creditNote->invoice_date->toDateString(),
                [
                    ['account_id' => $inventoryId, 'debit' => $costAmount, 'credit' => 0,          'description' => "Inventory restored – {$creditNote->invoice_number}"],
                    ['account_id' => $cogsId,      'debit' => 0,           'credit' => $costAmount, 'description' => "COGS reversal – {$creditNote->invoice_number}"],
                ],
                $userId, 'invoice', $creditNote->id
            );
        } catch (\Throwable $e) {
            Log::error('postCogsReversalJournal failed', ['credit_note_id' => $creditNote->id, 'error' => $e->getMessage()]);
        }
    }

    public function postPaymentReceivedJournal(Invoice $invoice, float $amount, string $date, int $userId, string $paymentMethod = 'cash', ?int $accountId = null): void
    {
        try {
            $invoice->loadMissing('customer');
            $branchId = $invoice->branch_id;
            $arId     = $invoice->customer->account_id
                        ?: $this->getAccountId($branchId, 'acc_trade_receivables');

            // Prefer the specific cash/bank account the user picked at payment
            // time — a branch-level default is only a fallback for callers that
            // never collected one (e.g. older payments, or cheque receipts,
            // which land in Cheques in Hand until actually deposited/cleared).
            $debitAccountId = $accountId ?: match ($paymentMethod) {
                'cheque'       => $this->getAccountId($branchId, 'acc_cheques_in_hand') ?: $this->getAccountId($branchId, 'acc_cash'),
                'bank_transfer' => $this->getAccountId($branchId, 'acc_bank') ?: $this->getAccountId($branchId, 'acc_cash'),
                default        => $this->getAccountId($branchId, 'acc_cash') ?: $this->getAccountId($branchId, 'acc_bank'),
            };

            if (!$arId || !$debitAccountId) return;

            $this->createEntry(
                $branchId, 'payment_received', "Payment – Invoice {$invoice->invoice_number}",
                $date,
                [
                    ['account_id' => $debitAccountId, 'debit' => $amount, 'credit' => 0,       'description' => "Receipt – {$invoice->invoice_number}"],
                    ['account_id' => $arId,           'debit' => 0,        'credit' => $amount, 'description' => "Receipt – {$invoice->invoice_number}"],
                ],
                $userId, 'invoice', $invoice->id
            );
        } catch (\Throwable $e) {
            Log::error('postPaymentReceivedJournal failed', ['invoice_id' => $invoice->id, 'error' => $e->getMessage()]);
        }
    }

    public function postExpenseJournal(\App\Models\Expense $expense, int $userId): void
    {
        try {
            if (!$expense->account_id || !$expense->payment_account_id) return;

            $this->createEntry(
                $expense->branch_id,
                'expense',
                'Expense – ' . $expense->description,
                $expense->expense_date->toDateString(),
                [
                    ['account_id' => $expense->account_id,         'debit'  => (float) $expense->amount, 'credit' => 0,                            'description' => $expense->description],
                    ['account_id' => $expense->payment_account_id, 'debit'  => 0,                        'credit' => (float) $expense->amount,     'description' => $expense->description],
                ],
                $userId,
                'expense',
                $expense->id
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('postExpenseJournal failed', ['expense_id' => $expense->id, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Auto-create an Accounts Payable sub-ledger account for a supplier.
     */
    public function ensureSupplierAccount(Supplier $supplier): ?Account
    {
        if ($supplier->account_id) {
            return Account::find($supplier->account_id);
        }

        $apGroup = AccountGroup::where('code', 'SUPP_ACCTS')->first();
        if (!$apGroup) return null;

        $account = Account::create([
            'group_id'        => $apGroup->id,
            'name'            => $supplier->name . ' - Payable',
            'code'            => 'AP-' . $supplier->code,
            'type'            => 'liability',
            'normal_balance'  => 'credit',
            'opening_balance' => (float) ($supplier->opening_balance ?? 0),
            'branch_id'       => $supplier->branch_id,
            'is_active'       => true,
            'description'     => 'AP sub-ledger for supplier: ' . $supplier->name,
        ]);

        $supplier->updateQuietly(['account_id' => $account->id]);

        return $account;
    }
}
