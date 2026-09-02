<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cheque;
use App\Models\JournalEntry;
use App\Models\Supplier;
use App\Services\AccountingService;
use App\Services\BranchContextService;
use App\Services\NumberGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    public function __construct(
        private NumberGeneratorService $numbers,
        private AccountingService $accounting,
        private BranchContextService $branchContext
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = Supplier::query();
        $this->branchContext->applyScope($q);
        if ($request->search) {
            $q->where(fn($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%")
                ->orWhere('company', 'like', "%{$request->search}%")
            );
        }
        if ($request->is_active !== null) $q->where('is_active', $request->boolean('is_active'));

        $suppliers = $q->orderBy('name')->paginate($request->input('per_page', 20));

        // Balance per supplier = the account's raw ledger balance, full stop —
        // deliberately the same figure the Chart of Accounts shows for that
        // account, so the two views can never disagree. See
        // Supplier::getOutstandingBalanceAttribute() for the full reasoning;
        // this is the identical calculation as a bulk aggregate query for
        // list-page performance, not a call into the model accessor itself.
        $suppliers->getCollection()->loadMissing('account:id,opening_balance');
        $accountIds = $suppliers->getCollection()->pluck('account_id')->filter();
        $sums = \App\Models\JournalEntryLine::whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted'))
            ->selectRaw('account_id, SUM(debit) as dr, SUM(credit) as cr')
            ->groupBy('account_id')
            ->get()->keyBy('account_id');

        $suppliers->getCollection()->transform(function ($supplier) use ($sums) {
            $row = $sums[$supplier->account_id] ?? null;
            $openingBalance = (float) ($supplier->account->opening_balance ?? 0);
            // Supplier accounts are always credit-normal (AP/liability).
            $supplier->balance = $openingBalance + (float) ($row->cr ?? 0) - (float) ($row->dr ?? 0);
            return $supplier;
        });

        return response()->json($suppliers);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'tax_number' => 'nullable|string',
            'payment_terms_days' => 'nullable|integer|min:0',
            'opening_balance' => 'nullable|numeric',
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'bank_branch' => 'nullable|string',
            'product_categories' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($data) {
            $data['code'] = $this->numbers->supplierCode();
            $supplier = Supplier::create($data);

            // Auto-create AP sub-ledger account in chart of accounts
            $this->accounting->ensureSupplierAccount($supplier);

            return response()->json($supplier, 201);
        }, 5);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->load(['purchaseOrders' => fn($q) => $q->latest()->limit(10)]);
        $supplier->append('outstanding_balance');
        $supplier->balance = $supplier->outstanding_balance;
        return response()->json($supplier);
    }

    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'company' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'tax_number' => 'nullable|string',
            'payment_terms_days' => 'nullable|integer',
            'bank_name' => 'nullable|string',
            'bank_account' => 'nullable|string',
            'bank_branch' => 'nullable|string',
            'product_categories' => 'nullable|array',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $supplier->update($data);
        return response()->json($supplier->fresh());
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();
        return response()->json(['message' => 'Supplier deleted.']);
    }

    /**
     * Records a payment made against this supplier's opening balance. Mirrors
     * CustomerController::payOpeningBalance() with debit/credit reversed — a
     * supplier's payable is credit-normal, so paying it down debits the payable
     * and credits the cash/bank/cheque account (the opposite of receiving a
     * customer payment).
     */
    public function payOpeningBalance(Request $request, Supplier $supplier): JsonResponse
    {
        $data = $request->validate([
            'amount'           => 'required|numeric|min:0.01',
            'payment_method'   => 'required|in:cash,cheque,bank_transfer',
            'payment_date'     => 'required|date',
            'reference_number' => 'nullable|string',
            'notes'            => 'nullable|string',
            'cheque_number'    => 'required_if:payment_method,cheque|nullable|string',
            'bank_name'        => 'required_if:payment_method,cheque|nullable|string',
            'cheque_date'      => 'required_if:payment_method,cheque|nullable|date',
        ]);

        $supplier->loadMissing('account');
        if (!$supplier->account_id || !$supplier->account) {
            return response()->json(['message' => 'This supplier has no linked ledger account — contact an administrator.'], 422);
        }

        return DB::transaction(function () use ($data, $supplier, $request) {
            $branchId = $supplier->branch_id;
            $amount   = (float) $data['amount'];

            // acc_cheques_in_hand is specifically for cheques we've *received* (an
            // asset held until deposited) — not applicable to a cheque we *issue*
            // to pay a supplier. This system has no separate "cheques payable"
            // clearing account, so an issued cheque credits the bank account
            // directly, same as a bank transfer.
            $creditAccountId = match ($data['payment_method']) {
                'cheque', 'bank_transfer' => $this->accounting->getAccountId($branchId, 'acc_bank')
                            ?: $this->accounting->getAccountId($branchId, 'acc_cash'),
                default => $this->accounting->getAccountId($branchId, 'acc_cash')
                            ?: $this->accounting->getAccountId($branchId, 'acc_bank'),
            };

            if (!$creditAccountId) {
                return response()->json(['message' => 'No cash/bank account is configured in Accounting Settings for this payment method.'], 422);
            }

            $cheque = null;
            if ($data['payment_method'] === 'cheque') {
                $cheque = Cheque::create([
                    'branch_id'            => $branchId,
                    'created_by'           => $request->user()->id,
                    'direction'            => 'issued',
                    'party_id'             => $supplier->id,
                    'party_type'           => 'supplier',
                    'cheque_number'        => $data['cheque_number'],
                    'bank_name'            => $data['bank_name'],
                    'cheque_date'          => $data['cheque_date'],
                    'received_issued_date' => $data['payment_date'],
                    'amount'               => $amount,
                    'status'               => 'in_hand',
                ]);
            }

            $this->accounting->createEntry(
                $branchId, 'opening_balance_payment',
                "Opening balance payment – {$supplier->name}" . ($cheque ? " (cheque {$cheque->cheque_number})" : ''),
                $data['payment_date'],
                [
                    ['account_id' => $supplier->account_id, 'debit' => $amount, 'credit' => 0,      'description' => "Opening balance payment – {$supplier->name}"],
                    ['account_id' => $creditAccountId,       'debit' => 0,      'credit' => $amount, 'description' => "Payment made – {$supplier->name}"],
                ],
                $request->user()->id,
                $cheque ? 'cheque' : 'supplier',
                $cheque?->id ?: $supplier->id
            );

            // opening_balance itself is never touched — it stays a frozen historical
            // figure. The journal entry above is what actually reduces what's owed;
            // Account::openingBalancePaid() nets it out for every balance calculation.
            $supplier->refresh()->loadMissing('account');

            return response()->json([
                'message'             => 'Payment recorded.',
                'supplier'            => $supplier,
                'outstanding_balance' => $supplier->outstanding_balance,
                'cheque'              => $cheque,
            ]);
        });
    }

    /** History of payments recorded against this supplier's opening balance, newest first. */
    public function openingBalancePayments(Supplier $supplier): JsonResponse
    {
        if (!$supplier->account_id) {
            return response()->json([]);
        }

        $entries = JournalEntry::whereIn('type', ['opening_balance_payment', 'opening_balance_payment_reversed'])
            ->where('status', 'posted')
            ->whereHas('lines', fn($q) => $q->where('account_id', $supplier->account_id))
            ->with('lines')
            ->orderByDesc('entry_date')
            ->orderByDesc('id')
            ->get();

        $chequeIds = $entries->where('reference_type', 'cheque')->pluck('reference_id')->unique();
        $cheques = Cheque::whereIn('id', $chequeIds)->get()->keyBy('id');

        $payments = $entries->where('type', 'opening_balance_payment')->map(function ($je) use ($entries, $cheques) {
            $reversal = $entries->first(fn($e) => $e->type === 'opening_balance_payment_reversed'
                && $e->reference_type === $je->reference_type
                && $e->reference_id === $je->reference_id);
            $debitLine = $je->lines->firstWhere('debit', '>', 0);
            $cheque = $je->reference_type === 'cheque' ? ($cheques[$je->reference_id] ?? null) : null;

            return [
                'journal_entry_id' => $je->id,
                'date'             => $je->entry_date,
                'entry_number'     => $je->entry_number,
                'amount'           => (float) ($debitLine->debit ?? 0),
                'description'      => $je->description,
                'reversed'         => (bool) $reversal,
                'cheque'           => $cheque ? [
                    'id' => $cheque->id, 'cheque_number' => $cheque->cheque_number,
                    'bank_name' => $cheque->bank_name, 'status' => $cheque->status,
                ] : null,
            ];
        })->values();

        return response()->json($payments);
    }

    /** Reverses a previously recorded opening-balance payment — mirrors InvoiceController::deletePayment(). */
    public function deleteOpeningBalancePayment(Request $request, Supplier $supplier, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->type !== 'opening_balance_payment' || $journalEntry->status !== 'posted') {
            return response()->json(['message' => 'Payment not found.'], 404);
        }
        $journalEntry->loadMissing('lines');
        if (!$journalEntry->lines->contains('account_id', $supplier->account_id)) {
            return response()->json(['message' => 'Payment does not belong to this supplier.'], 404);
        }

        $alreadyReversed = JournalEntry::where('type', 'opening_balance_payment_reversed')
            ->where('reference_type', $journalEntry->reference_type)
            ->where('reference_id', $journalEntry->reference_id)
            ->exists();
        if ($alreadyReversed) {
            return response()->json(['message' => 'This payment has already been reversed.'], 422);
        }

        $cheque = $journalEntry->reference_type === 'cheque' ? Cheque::find($journalEntry->reference_id) : null;
        if ($cheque && $cheque->status !== 'in_hand') {
            return response()->json([
                'message' => "This payment's cheque has already been {$cheque->status} — reverse the cheque status in Manage Cheque first, then delete the payment.",
            ], 422);
        }

        return DB::transaction(function () use ($request, $supplier, $journalEntry, $cheque) {
            $this->accounting->createEntry(
                $journalEntry->branch_id, 'opening_balance_payment_reversed',
                "Payment deleted – reversing opening balance payment for {$supplier->name}",
                now()->toDateString(),
                $journalEntry->lines->map(fn($l) => [
                    'account_id'  => $l->account_id,
                    'debit'       => (float) $l->credit,
                    'credit'      => (float) $l->debit,
                    'description' => "Reversal – {$journalEntry->description}",
                ])->all(),
                $request->user()->id, $journalEntry->reference_type, $journalEntry->reference_id
            );

            if ($cheque) {
                $cheque->delete();
            }

            return response()->json(['message' => 'Payment deleted and reversed.']);
        });
    }

    public function ledger(Request $request, Supplier $supplier): JsonResponse
    {
        $invoices = \App\Models\SupplierInvoice::where('supplier_id', $supplier->id)
            ->when($request->from_date, fn($q) => $q->whereDate('invoice_date', '>=', $request->from_date))
            ->when($request->to_date, fn($q) => $q->whereDate('invoice_date', '<=', $request->to_date))
            ->with('payments')
            ->orderBy('invoice_date')
            ->get();

        return response()->json([
            'supplier' => $supplier,
            'transactions' => $invoices,
            'total_invoiced' => $invoices->sum('total'),
            'total_paid' => $invoices->sum('paid_amount'),
            'outstanding' => $invoices->sum('balance_due'),
        ]);
    }
}
