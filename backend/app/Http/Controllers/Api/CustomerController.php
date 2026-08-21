<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cheque;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Services\AccountingService;
use App\Services\BranchContextService;
use App\Services\NumberGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function __construct(
        private BranchContextService $branchContext,
        private NumberGeneratorService $numbers,
        private AccountingService $accounting
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = Customer::query();
        $this->branchContext->applyScope($q);

        if ($request->search) {
            $q->where(fn($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%")
                ->orWhere('company', 'like', "%{$request->search}%")
                ->orWhere('city', 'like', "%{$request->search}%")
                ->orWhere('district', 'like', "%{$request->search}%")
            );
        }

        if ($request->type) $q->where('type', $request->type);
        if ($request->district) $q->where('district', $request->district);
        if ($request->city) $q->where('city', 'like', "%{$request->city}%");
        if ($request->is_active !== null) $q->where('is_active', $request->boolean('is_active'));

        $customers = $q->with(['assignedUser', 'branch'])
            ->orderBy('name')
            ->paginate($request->input('per_page', 20));

        // Real outstanding balance per customer: open invoices (excluding
        // credit notes, which share this table but always carry balance_due=0)
        // plus the account's opening balance (predates invoice-level tracking)
        // minus whatever's already been paid against it minus any standing
        // credit_balance (an over-return credit) — the same calculation the
        // Customer Aging Report uses, so this list and that report can't
        // disagree. Aggregate queries throughout, no N+1.
        $ids = $customers->getCollection()->pluck('id');
        $invoiceBalances = Invoice::whereIn('customer_id', $ids)
            ->where('type', 'invoice')
            ->whereIn('status', ['confirmed', 'partially_paid'])
            ->selectRaw('customer_id, SUM(balance_due) as bal')
            ->groupBy('customer_id')
            ->pluck('bal', 'customer_id');

        $customers->getCollection()->loadMissing('account:id,opening_balance');
        $accountIds = $customers->getCollection()->pluck('account_id')->filter();
        // Must stay in lockstep with Account::openingBalancePaid()'s filter
        // (opening_balance_payment/_reversed, plus a manual entry with no
        // reference_type) — this is a duplicated aggregate query for list-page
        // performance, not a call into the model accessor itself.
        $obPaid = \App\Models\JournalEntryLine::whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', fn($q) => $q
                ->where('status', 'posted')
                ->where(function ($q2) {
                    $q2->whereIn('type', ['opening_balance_payment', 'opening_balance_payment_reversed'])
                       ->orWhere(function ($q3) {
                           $q3->where('type', 'manual')->whereNull('reference_type');
                       });
                }))
            ->selectRaw('account_id, SUM(credit - debit) as net')
            ->groupBy('account_id')
            ->pluck('net', 'account_id');

        $customers->getCollection()->transform(function ($customer) use ($invoiceBalances, $obPaid) {
            $obRemaining = (float) ($customer->account->opening_balance ?? 0) - (float) ($obPaid[$customer->account_id] ?? 0);
            $customer->balance = (float) ($invoiceBalances[$customer->id] ?? 0)
                + $obRemaining
                - (float) $customer->credit_balance;
            return $customer;
        });

        return response()->json($customers);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'type' => 'required|in:hospital,clinic,individual,distributor,pharmacy,other',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'phone2' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'city' => 'required|string|max:100',
            'district' => 'required|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'opening_balance' => 'nullable|numeric',
            'assigned_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'is_walk_in' => 'nullable|boolean',
        ]);

        return DB::transaction(function () use ($data) {
            $data['code'] = $this->numbers->customerCode();
            $customer = Customer::create($data);

            // Auto-create AR sub-ledger account in chart of accounts
            $this->accounting->ensureCustomerAccount($customer);

            return response()->json($customer->load(['assignedUser', 'branch']), 201);
        }, 5);
    }

    public function show(Customer $customer): JsonResponse
    {
        $customer->load(['assignedUser', 'branch', 'invoices' => fn($q) => $q->latest()->limit(10)]);
        $customer->append(['outstanding_balance', 'credit_utilization']);
        $customer->balance = $customer->outstanding_balance;

        // Cheque analysis
        $cheques = Cheque::where('party_type', 'customer')
            ->where('party_id', $customer->id)
            ->selectRaw('COUNT(*) as total, SUM(amount) as total_amount, SUM(CASE WHEN status="bounced" THEN 1 ELSE 0 END) as bounced_count, SUM(CASE WHEN status="bounced" THEN amount ELSE 0 END) as bounced_amount, MAX(bounced_date) as last_bounce_date')
            ->first();

        return response()->json([
            'customer' => $customer,
            'cheque_analysis' => $cheques,
        ]);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'company' => 'nullable|string|max:255',
            'type' => 'sometimes|in:hospital,clinic,individual,distributor,pharmacy,other',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'phone2' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'city' => 'sometimes|string|max:100',
            'district' => 'sometimes|string|max:100',
            'country' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:50',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:0',
            'assigned_user_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $customer->update($data);
        return response()->json($customer->fresh(['assignedUser', 'branch']));
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $customer->delete();
        return response()->json(['message' => 'Customer deleted.']);
    }

    public function ledger(Request $request, Customer $customer): JsonResponse
    {
        $invoices = Invoice::where('customer_id', $customer->id)
            ->where('type', 'invoice') // exclude credit notes — they share this table but aren't sales
            ->when($request->from_date, fn($q) => $q->whereDate('invoice_date', '>=', $request->from_date))
            ->when($request->to_date, fn($q) => $q->whereDate('invoice_date', '<=', $request->to_date))
            ->with('payments')
            ->orderBy('invoice_date')
            ->get();

        // Prepend the account's opening balance as its own ledger line — it
        // predates all invoice-level tracking, so without it this ledger's
        // total silently disagrees with the customer's real balance shown
        // everywhere else (Overview tab, Aging Report, Chart of Accounts).
        // opening_balance itself stays frozen at its original migrated figure;
        // whatever's been paid against it since (via Record Payment) shows as
        // this row's paid_amount, same as any invoice.
        $customer->loadMissing('account:id,opening_balance');
        $opening = (float) ($customer->account->opening_balance ?? 0);
        $obPaid  = (float) ($customer->account?->openingBalancePaid() ?? 0);
        $obDue   = $opening - $obPaid;
        $transactions = collect();
        if (abs($opening) > 0.001) {
            $transactions->push([
                'id'             => 'opening-balance',
                'invoice_date'   => $customer->created_at?->toDateString(),
                'invoice_number' => 'Opening Balance',
                'total'          => $opening,
                'paid_amount'    => $obPaid,
                'balance_due'    => $obDue,
                'status'         => $obDue <= 0.001 ? 'paid' : ($obPaid > 0 ? 'partially_paid' : 'opening'),
            ]);
        }
        $transactions = $transactions->concat($invoices);

        return response()->json([
            'customer' => $customer,
            'transactions' => $transactions->values(),
            'total_invoiced' => $invoices->sum('total') + $opening,
            'total_paid' => $invoices->sum('paid_amount') + $obPaid,
            'outstanding' => $invoices->sum('balance_due') + $obDue - (float) $customer->credit_balance,
        ]);
    }

    /**
     * Records a payment against a customer's migrated opening balance — the part
     * of their debt that predates invoice-level tracking in this system, so there's
     * no invoice to attach a normal payment to. Unlike invoice overpayment (which
     * goes to credit_balance, a genuine spare-credit pool for future invoices),
     * this reduces the account's opening_balance directly — the money already paid
     * down old debt, it isn't spare credit waiting to be reused.
     */
    public function payOpeningBalance(Request $request, Customer $customer): JsonResponse
    {
        $data = $request->validate([
            'amount'           => 'required|numeric|min:0.01',
            'payment_method'   => 'required|in:cash,cheque,bank_transfer,credit_card,online',
            'payment_date'     => 'required|date',
            'reference_number' => 'nullable|string',
            'notes'            => 'nullable|string',
            'cheque_number'    => 'required_if:payment_method,cheque|nullable|string',
            'bank_name'        => 'required_if:payment_method,cheque|nullable|string',
            'cheque_date'      => 'required_if:payment_method,cheque|nullable|date',
        ]);

        $customer->loadMissing('account');
        if (!$customer->account_id || !$customer->account) {
            return response()->json(['message' => 'This customer has no linked ledger account — contact an administrator.'], 422);
        }

        return DB::transaction(function () use ($data, $customer, $request) {
            $branchId = $customer->branch_id;
            $amount   = (float) $data['amount'];

            $debitAccountId = match ($data['payment_method']) {
                'cheque' => $this->accounting->getAccountId($branchId, 'acc_cheques_in_hand')
                            ?: $this->accounting->getAccountId($branchId, 'acc_cash'),
                'bank_transfer', 'credit_card', 'online' => $this->accounting->getAccountId($branchId, 'acc_bank')
                            ?: $this->accounting->getAccountId($branchId, 'acc_cash'),
                default => $this->accounting->getAccountId($branchId, 'acc_cash')
                            ?: $this->accounting->getAccountId($branchId, 'acc_bank'),
            };

            if (!$debitAccountId) {
                return response()->json(['message' => 'No cash/bank/cheque account is configured in Accounting Settings for this payment method.'], 422);
            }

            $cheque = null;
            if ($data['payment_method'] === 'cheque') {
                $cheque = Cheque::create([
                    'branch_id'            => $branchId,
                    'created_by'           => $request->user()->id,
                    'direction'            => 'received',
                    'party_id'             => $customer->id,
                    'party_type'           => 'customer',
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
                "Opening balance payment – {$customer->name}" . ($cheque ? " (cheque {$cheque->cheque_number})" : ''),
                $data['payment_date'],
                [
                    ['account_id' => $debitAccountId,       'debit' => $amount, 'credit' => 0,      'description' => "Payment received – {$customer->name}"],
                    ['account_id' => $customer->account_id, 'debit' => 0,       'credit' => $amount, 'description' => "Opening balance payment – {$customer->name}"],
                ],
                $request->user()->id,
                $cheque ? 'cheque' : 'customer',
                $cheque?->id ?: $customer->id
            );

            // opening_balance itself is never touched — it stays a frozen historical
            // figure. The journal entry above is what actually reduces what's owed;
            // Account::openingBalancePaid() nets it out for every balance calculation.
            $customer->refresh()->loadMissing('account');

            return response()->json([
                'message'              => 'Payment recorded.',
                'customer'             => $customer,
                'outstanding_balance'  => $customer->outstanding_balance,
                'cheque'               => $cheque,
            ]);
        });
    }

    /** History of payments recorded against this customer's opening balance, newest first. */
    public function openingBalancePayments(Customer $customer): JsonResponse
    {
        if (!$customer->account_id) {
            return response()->json([]);
        }

        $entries = JournalEntry::whereIn('type', ['opening_balance_payment', 'opening_balance_payment_reversed'])
            ->where('status', 'posted')
            ->whereHas('lines', fn($q) => $q->where('account_id', $customer->account_id))
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
            $creditLine = $je->lines->firstWhere('credit', '>', 0);
            $cheque = $je->reference_type === 'cheque' ? ($cheques[$je->reference_id] ?? null) : null;

            return [
                'journal_entry_id' => $je->id,
                'date'             => $je->entry_date,
                'entry_number'     => $je->entry_number,
                'amount'           => (float) ($creditLine->credit ?? 0),
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
    public function deleteOpeningBalancePayment(Request $request, Customer $customer, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->type !== 'opening_balance_payment' || $journalEntry->status !== 'posted') {
            return response()->json(['message' => 'Payment not found.'], 404);
        }
        $journalEntry->loadMissing('lines');
        if (!$journalEntry->lines->contains('account_id', $customer->account_id)) {
            return response()->json(['message' => 'Payment does not belong to this customer.'], 404);
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

        return DB::transaction(function () use ($request, $customer, $journalEntry, $cheque) {
            $this->accounting->createEntry(
                $journalEntry->branch_id, 'opening_balance_payment_reversed',
                "Payment deleted – reversing opening balance payment for {$customer->name}",
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

    public function aging(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();
        $today = now();

        $invoices = Invoice::query()
            ->where('type', 'invoice') // exclude credit notes — they share this table but always carry balance_due=0
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereIn('status', ['confirmed', 'partially_paid'])
            ->with('customer')
            ->get();

        $aging = [
            '0_30' => ['count' => 0, 'amount' => 0],
            '31_60' => ['count' => 0, 'amount' => 0],
            '61_90' => ['count' => 0, 'amount' => 0],
            '90_plus' => ['count' => 0, 'amount' => 0],
        ];

        foreach ($invoices as $inv) {
            if (!$inv->due_date) continue;
            $days = $today->diffInDays($inv->due_date, false);
            $days = $days <= 0 ? abs($days) : 0;

            $bucket = match(true) {
                $days <= 30 => '0_30',
                $days <= 60 => '31_60',
                $days <= 90 => '61_90',
                default => '90_plus',
            };
            $aging[$bucket]['count']++;
            $aging[$bucket]['amount'] += $inv->balance_due;
        }

        return response()->json(['aging' => $aging, 'invoices' => $invoices]);
    }
}
