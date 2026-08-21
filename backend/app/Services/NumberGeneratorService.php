<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Branch;
use App\Models\DocumentSequence;
use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReturn;
use App\Models\GoodsReceiptNote;
use App\Models\SupplierInvoice;
use App\Models\Expense;
use App\Models\InterBranchTransfer;
use App\Models\StockAdjustment;
use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class NumberGeneratorService
{
    /**
     * Highest 5-digit numeric suffix already in use for $column within $query.
     * Used to seed a sequence so it can never collide with a number issued before
     * the sequence existed — including ones left behind by deleted rows (count()
     * alone undercounts when gaps exist, which previously caused a collision).
     */
    private function maxSuffix(Builder $query, string $column): int
    {
        return (int) ($query->selectRaw("MAX(CAST(RIGHT(`{$column}`, 5) AS UNSIGNED)) as m")->value('m') ?? 0);
    }

    /**
     * Atomically reserve the next number for a (branch, document type, year) sequence.
     *
     * This is called from inside the caller's own DB::transaction() (e.g. InvoiceController
     * wraps the whole invoice creation in one). A SELECT-then-INSERT-then-UPDATE approach
     * (even with lockForUpdate()/insertOrIgnore()) can still deadlock under heavy concurrent
     * load: nested transactions don't get Laravel's automatic deadlock retry (a deadlock
     * rolls back the *entire* outer transaction, so silently retrying just the inner part
     * would be unsafe — Laravel deliberately disables it), and MySQL's own gap-lock checks
     * on a fresh unique key can deadlock concurrent inserts even with INSERT IGNORE.
     *
     * The fix is to make the reservation a single atomic statement instead of multiple
     * round-trips: MySQL's `INSERT ... ON DUPLICATE KEY UPDATE col = LAST_INSERT_ID(expr)`
     * idiom increments-or-creates the counter in one indivisible operation that MySQL
     * itself serializes correctly, with no risk of deadlocking the surrounding transaction.
     *
     * The counter is reconciled against the actual table max on every call
     * (GREATEST(last_number, actualMax) + 1). A counter that lags behind real data —
     * restored DB dump, manually inserted rows, a reset sequence row — would otherwise
     * re-issue numbers that already exist and blow up on the unique index (seen in
     * production with purchase_orders.po_number).
     */
    private function nextNumber(int $branchId, string $documentType, int $year, \Closure $seedMax): int
    {
        $actualMax = $seedMax();

        $updated = DB::update(
            'UPDATE document_sequences SET last_number = LAST_INSERT_ID(GREATEST(last_number, ?) + 1), updated_at = NOW()
             WHERE branch_id = ? AND document_type = ? AND year = ?',
            [$actualMax, $branchId, $documentType, $year]
        );

        if (!$updated) {
            // Row doesn't exist yet — atomically create-or-increment in one statement so a
            // concurrent request doing the same thing can never deadlock or collide with us.
            DB::insert(
                'INSERT INTO document_sequences (branch_id, document_type, year, last_number, created_at, updated_at)
                 VALUES (?, ?, ?, ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE last_number = LAST_INSERT_ID(GREATEST(last_number, ?) + 1)',
                [$branchId, $documentType, $year, $actualMax + 1, $actualMax]
            );
        }

        return (int) DB::selectOne('SELECT LAST_INSERT_ID() as n')->n;
    }

    /**
     * Same as nextNumber() but for sequences with no branch/year scoping (sentinel 0).
     *
     * Uses the same GREATEST(counter, actualMax) + 1 reconciliation as nextNumber() so the
     * counter can never fall behind codes already present in the table (soft-deleted rows,
     * data cleanups, restored dumps).
     */
    private function nextGlobalNumber(string $documentType, \Closure $seedMax): int
    {
        $actualMax = $seedMax();

        $updated = DB::update(
            'UPDATE document_sequences SET last_number = LAST_INSERT_ID(GREATEST(last_number, ?) + 1), updated_at = NOW()
             WHERE branch_id = 0 AND document_type = ? AND year = 0',
            [$actualMax, $documentType]
        );

        if (!$updated) {
            $seed = $actualMax + 1;
            DB::insert(
                'INSERT INTO document_sequences (branch_id, document_type, year, last_number, created_at, updated_at)
                 VALUES (0, ?, 0, ?, NOW(), NOW())
                 ON DUPLICATE KEY UPDATE last_number = LAST_INSERT_ID(GREATEST(last_number, ?) + 1)',
                [$documentType, $seed, $actualMax]
            );
        }

        return (int) DB::selectOne('SELECT LAST_INSERT_ID() as n')->n;
    }

    public function invoiceNumber(int $branchId, string $type = 'invoice'): string
    {
        $branch = Branch::find($branchId);
        $prefix = $branch ? strtoupper($branch->code) : 'INV';
        $prefix .= match($type) {
            'proforma' => '-PRO-',
            'credit_note' => '-CN-',
            default => '-INV-',
        };
        $year = (int) date('Y');
        $count = $this->nextNumber($branchId, "invoice:{$type}", $year, fn() => $this->maxSuffix(
            Invoice::withTrashed()->where('branch_id', $branchId)->where('type', $type)->whereYear('created_at', $year),
            'invoice_number'
        ));
        return $prefix . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function poNumber(int $branchId): string
    {
        $branch = Branch::find($branchId);
        $prefix = ($branch ? strtoupper($branch->code) : 'BR') . '-INV-';
        $year = (int) date('Y');
        $count = $this->nextNumber($branchId, 'purchase_order', $year, fn() => $this->maxSuffix(
            PurchaseOrder::withTrashed()->where('branch_id', $branchId)->whereYear('created_at', $year),
            'po_number'
        ));
        return $prefix . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function grnNumber(int $branchId): string
    {
        $branch = Branch::find($branchId);
        $prefix = ($branch ? strtoupper($branch->code) : 'BR') . '-GRN-';
        $year = (int) date('Y');
        $count = $this->nextNumber($branchId, 'grn', $year, fn() => $this->maxSuffix(
            GoodsReceiptNote::withTrashed()->where('branch_id', $branchId)->whereYear('created_at', $year),
            'grn_number'
        ));
        return $prefix . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function batchCode(int $branchId): string
    {
        $branch = Branch::find($branchId);
        $prefix = ($branch ? strtoupper($branch->code) : 'BR') . '-BATCH-';
        $year = (int) date('Y');
        $count = $this->nextNumber($branchId, 'batch', $year, fn() => $this->maxSuffix(
            Batch::where('branch_id', $branchId)->whereYear('created_at', $year),
            'batch_code'
        ));
        return $prefix . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function supplierInvoiceNumber(int $branchId): string
    {
        $branch = Branch::find($branchId);
        $prefix = ($branch ? strtoupper($branch->code) : 'BR') . '-SINV-';
        $year = (int) date('Y');
        $count = $this->nextNumber($branchId, 'supplier_invoice', $year, fn() => $this->maxSuffix(
            SupplierInvoice::withTrashed()->where('branch_id', $branchId)->whereYear('created_at', $year),
            'invoice_number'
        ));
        return $prefix . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function expenseNumber(int $branchId): string
    {
        $branch = Branch::find($branchId);
        $prefix = ($branch ? strtoupper($branch->code) : 'BR') . '-EXP-';
        $year = (int) date('Y');
        $count = $this->nextNumber($branchId, 'expense', $year, fn() => $this->maxSuffix(
            Expense::withTrashed()->where('branch_id', $branchId)->whereYear('created_at', $year),
            'expense_number'
        ));
        return $prefix . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function transferNumber(): string
    {
        $year = (int) date('Y');
        $count = $this->nextNumber(0, 'transfer', $year, fn() => $this->maxSuffix(
            InterBranchTransfer::withTrashed()->whereYear('created_at', $year),
            'transfer_number'
        ));
        return 'TRF-' . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function journalNumber(int $branchId): string
    {
        $branch = Branch::find($branchId);
        $prefix = ($branch ? strtoupper($branch->code) : 'BR') . '-JV-';
        $year = (int) date('Y');
        $count = $this->nextNumber($branchId, 'journal', $year, fn() => $this->maxSuffix(
            JournalEntry::withTrashed()->where('branch_id', $branchId)->whereYear('created_at', $year),
            'entry_number'
        ));
        return $prefix . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function customerCode(): string
    {
        $count = $this->nextGlobalNumber('customer', fn() => $this->maxSuffix(\App\Models\Customer::withTrashed(), 'code'));
        return 'CUST-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function supplierCode(): string
    {
        $count = $this->nextGlobalNumber('supplier', fn() => $this->maxSuffix(\App\Models\Supplier::withTrashed(), 'code'));
        return 'SUPP-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function productCode(): string
    {
        $count = $this->nextGlobalNumber('product', fn() => $this->maxSuffix(\App\Models\Product::withTrashed(), 'code'));
        return 'PROD-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function serviceCode(): string
    {
        $count = $this->nextGlobalNumber('service', fn() => $this->maxSuffix(\App\Models\Service::withTrashed(), 'code'));
        return 'SVC-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function employeeCode(): string
    {
        $count = $this->nextGlobalNumber('employee', fn() => $this->maxSuffix(\App\Models\Employee::withTrashed(), 'employee_code'));
        return 'EMP-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function assetCode(): string
    {
        $count = $this->nextGlobalNumber('asset', fn() => $this->maxSuffix(\App\Models\Asset::withTrashed(), 'asset_code'));
        return 'AST-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function purchaseReturnNumber(int $branchId): string
    {
        $branch = Branch::find($branchId);
        $prefix = ($branch ? strtoupper($branch->code) : 'BR') . '-PRET-';
        $year = (int) date('Y');
        $count = $this->nextNumber($branchId, 'purchase_return', $year, fn() => $this->maxSuffix(
            PurchaseReturn::withTrashed()->where('branch_id', $branchId)->whereYear('created_at', $year),
            'return_number'
        ));
        return $prefix . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    public function adjustmentNumber(int $branchId): string
    {
        $branch = Branch::find($branchId);
        $prefix = ($branch ? strtoupper($branch->code) : 'BR') . '-ADJ-';
        $year = (int) date('Y');
        $count = $this->nextNumber($branchId, 'adjustment', $year, fn() => $this->maxSuffix(
            StockAdjustment::withTrashed()->where('branch_id', $branchId)->whereYear('created_at', $year),
            'adjustment_number'
        ));
        return $prefix . $year . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
