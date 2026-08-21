<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cheque;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Services\AccountingService;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ChequeController extends Controller
{
    public function __construct(
        private BranchContextService $branchContext,
        private AccountingService $accounting,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = Cheque::query();
        $this->branchContext->applyScope($q);

        if ($request->status) $q->where('status', $request->status);
        if ($request->direction) $q->where('direction', $request->direction);
        if ($request->party_type) $q->where('party_type', $request->party_type);
        if ($request->bank_name) $q->where('bank_name', 'like', "%{$request->bank_name}%");
        if ($request->from_date) $q->whereDate('cheque_date', '>=', $request->from_date);
        if ($request->to_date) $q->whereDate('cheque_date', '<=', $request->to_date);
        if ($request->search) {
            $q->where(fn($q) => $q->where('cheque_number', 'like', "%{$request->search}%")
                ->orWhere('bank_name', 'like', "%{$request->search}%")
            );
        }

        return response()->json(
            $q->with([
                'branch', 'createdBy', 'customer', 'supplier',
                'invoiceLinks.invoice.customer',
                'supplierPayments.supplierInvoice.supplier',
                'supplierPayments.purchaseOrder.supplier',
                'purchaseOrders.supplier',
                'expenses.account',
            ])->latest('cheque_date')->paginate($request->input('per_page', 20))
        );
    }

    public function show(Cheque $cheque): JsonResponse
    {
        return response()->json($cheque->load([
            'branch', 'createdBy', 'customer', 'supplier',
            'invoiceLinks.invoice.customer',
            'supplierPayments.supplierInvoice.supplier',
            'supplierPayments.purchaseOrder.supplier',
            'purchaseOrders.supplier',
            'expenses.account',
        ]));
    }

    public function update(Request $request, Cheque $cheque): JsonResponse
    {
        $data = $request->validate([
            'status' => 'required|in:in_hand,deposited,transferred,cleared,bounced,cancelled,returned',
            'deposited_date' => 'nullable|date',
            'cleared_date' => 'nullable|date',
            'bounced_date' => 'nullable|date',
            'bounce_reason' => 'nullable|string',
            'deposit_slip_number' => 'nullable|string',
            'account_id' => 'nullable|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);

        $wasBounced     = $data['status'] === 'bounced' && $cheque->status !== 'bounced';
        $wasUnbounced   = $cheque->status === 'bounced' && $data['status'] !== 'bounced';
        $wasCleared     = $data['status'] === 'cleared' && $cheque->status !== 'cleared';
        $wasUncleared   = $cheque->status === 'cleared' && $data['status'] !== 'cleared';
        // A cheque handed over to a supplier (status 'transferred') already had its
        // AP settled at hand-over time (PurchaseController::useReceivedCheque() posts
        // Dr AP / Cr Cash-or-Bank immediately) — no bank account of ours ever receives
        // this money, so clearing it from here is a pure status flip, not a deposit.
        $wasTransferred = $cheque->status === 'transferred';
        // Leaving 'transferred' via anything other than clearing (the success path —
        // the supplier banked it, nothing to undo) or bouncing (already fully handled
        // below, including its own supplier-payment reversal) means the hand-over to
        // the supplier itself is being undone — e.g. the super-admin "Reverse" action
        // correcting a mistake. The supplier payment(s) that hand-over funded must be
        // undone too, or the supplier's AP stays settled while the cheque goes back
        // into the available pool — a double-booking risk.
        $wasUntransferred = $wasTransferred && !in_array($data['status'], ['transferred', 'bounced', 'cleared']);

        // A received cheque needs to know which of our own bank accounts it's
        // going into to post the clearing entry — captured at 'deposited' or
        // 'cleared' time (whichever happens first), and carried forward. Not
        // applicable to a transferred cheque, which never touches our own bank.
        if (!empty($data['account_id']) && in_array($data['status'], ['deposited', 'cleared']) && !$wasTransferred) {
            $data['deposit_account_id'] = $data['account_id'];
        }
        unset($data['account_id']);

        $cheque->update($data);

        if ($wasCleared && $cheque->direction === 'received' && !$wasTransferred) {
            try {
                $branchId = $cheque->branch_id;
                $bankId   = $cheque->deposit_account_id
                            ?: $this->accounting->getAccountId($branchId, 'acc_bank');
                $chequeAccId = $this->accounting->getAccountId($branchId, 'acc_cheques_in_hand')
                               ?: $this->accounting->getAccountId($branchId, 'acc_cash');
                if ($bankId && $chequeAccId) {
                    $this->accounting->createEntry(
                        $branchId, 'cheque_cleared',
                        "Cheque Cleared – {$cheque->cheque_number}",
                        $cheque->cleared_date?->toDateString() ?? now()->toDateString(),
                        [
                            ['account_id' => $bankId,      'debit' => (float) $cheque->amount, 'credit' => 0,                       'description' => "Cheque cleared – {$cheque->cheque_number}"],
                            ['account_id' => $chequeAccId, 'debit' => 0,                        'credit' => (float) $cheque->amount, 'description' => "Cheque cleared – {$cheque->cheque_number}"],
                        ],
                        $request->user()->id, 'cheque', $cheque->id
                    );
                } else {
                    Log::error('cheque clear journal skipped: missing bank or cheques-in-hand account', ['cheque_id' => $cheque->id]);
                }
            } catch (\Throwable $e) {
                Log::error('cheque clear journal failed', ['cheque_id' => $cheque->id, 'error' => $e->getMessage()]);
            }
        }

        if ($wasUncleared && $cheque->direction === 'received') {
            try {
                $branchId = $cheque->branch_id;
                // Reverse whichever bank account the original clearing entry used —
                // and only if one was actually posted. A transferred cheque's clear
                // never posted one (see above), so there's nothing to reverse; no
                // fallback account here, or un-clearing a transferred cheque would
                // fabricate a bank entry that was never real.
                $clearEntry = JournalEntry::where('reference_type', 'cheque')->where('reference_id', $cheque->id)
                    ->where('type', 'cheque_cleared')->where('status', 'posted')
                    ->with('lines')->latest('id')->first();
                if ($clearEntry) {
                    $bankId = $clearEntry->lines->firstWhere('debit', '>', 0)?->account_id;
                    $chequeAccId = $this->accounting->getAccountId($branchId, 'acc_cheques_in_hand')
                                   ?: $this->accounting->getAccountId($branchId, 'acc_cash');
                    if ($bankId && $chequeAccId) {
                        $this->accounting->createEntry(
                            $branchId, 'cheque_cleared',
                            "Cheque Clearing Reversed – {$cheque->cheque_number}",
                            now()->toDateString(),
                            [
                                ['account_id' => $chequeAccId, 'debit' => (float) $cheque->amount, 'credit' => 0,                       'description' => "Cheque clearing reversed – {$cheque->cheque_number}"],
                                ['account_id' => $bankId,      'debit' => 0,                        'credit' => (float) $cheque->amount, 'description' => "Cheque clearing reversed – {$cheque->cheque_number}"],
                            ],
                            $request->user()->id, 'cheque', $cheque->id
                        );
                    }
                }
            } catch (\Throwable $e) {
                Log::error('cheque un-clear journal failed', ['cheque_id' => $cheque->id, 'error' => $e->getMessage()]);
            }
        }

        if ($wasUntransferred) {
            DB::transaction(function () use ($cheque, $request) {
                $cheque->load(['supplierPayments.supplierInvoice.supplier', 'supplierPayments.purchaseOrder.supplier']);
                $today  = now()->toDateString();
                $userId = $request->user()->id;

                foreach ($cheque->supplierPayments as $payment) {
                    $amount = (float) $payment->amount;

                    if ($payment->supplierInvoice) {
                        $si      = $payment->supplierInvoice;
                        $newPaid = max(0, (float) $si->paid_amount - $amount);
                        $si->update([
                            'paid_amount' => $newPaid,
                            'balance_due' => (float) $si->total - $newPaid,
                            'status'      => $newPaid <= 0 ? 'pending' : 'partially_paid',
                        ]);
                    }

                    if ($payment->purchaseOrder) {
                        $po      = $payment->purchaseOrder;
                        $newPaid = max(0, (float) $po->paid_amount - $amount);
                        $po->update([
                            'paid_amount'    => $newPaid,
                            'balance_due'    => (float) $po->total - $newPaid,
                            'payment_status' => $newPaid <= 0 ? 'unpaid' : 'partially_paid',
                        ]);
                    }

                    // Journal: DR Payment Account, CR AP (reverse the supplier payment) —
                    // same shape as the bounce-reversal below, since the accounting effect
                    // is identical: this cheque no longer pays this supplier.
                    try {
                        $supplier = $payment->supplierInvoice?->supplier
                                 ?? $payment->purchaseOrder?->supplier;
                        $branchId = $payment->branch_id;
                        $apId     = $supplier?->account_id
                                    ?: $this->accounting->getAccountId($branchId, 'acc_trade_payables');
                        $payAccId = $payment->account_id
                                    ?: $this->accounting->getAccountId($branchId, 'acc_cheques_in_hand')
                                    ?: $this->accounting->getAccountId($branchId, 'acc_cash');
                        $ref = $payment->supplierInvoice?->invoice_number
                            ?? $payment->purchaseOrder?->po_number
                            ?? 'N/A';
                        if ($apId && $payAccId) {
                            $this->accounting->createEntry(
                                $branchId, 'payment_reversed',
                                "Cheque {$cheque->cheque_number} reversed from 'transferred' – {$ref}",
                                $today,
                                [
                                    ['account_id' => $payAccId, 'debit' => $amount, 'credit' => 0,       'description' => "Cheque {$cheque->cheque_number} un-transferred – restore payment account"],
                                    ['account_id' => $apId,     'debit' => 0,        'credit' => $amount, 'description' => "Restore AP – {$ref}"],
                                ],
                                $userId, 'cheque', $cheque->id
                            );
                        }
                    } catch (\Throwable $e) {
                        Log::error('cheque un-transfer supplier journal failed', ['cheque_id' => $cheque->id, 'payment_id' => $payment->id, 'error' => $e->getMessage()]);
                    }
                }
            });
        }

        if ($wasBounced) {
            DB::transaction(function () use ($cheque, $request) {
                $cheque->load([
                    'invoiceLinks.invoice.customer',
                    'supplierPayments.supplierInvoice.supplier',
                    'supplierPayments.purchaseOrder.supplier',
                ]);

                $today  = now()->toDateString();
                $userId = $request->user()->id;

                // ── 1. Reverse customer invoice payments ──────────────────────
                $totalReversed = 0;
                $creditCustomer = null;
                foreach ($cheque->invoiceLinks as $link) {
                    $invoice = $link->invoice;
                    if (!$invoice) continue;

                    $reversed   = (float) $link->amount;
                    $totalReversed += $reversed;
                    $creditCustomer = $creditCustomer ?: $invoice->customer;
                    $newPaid    = max(0, (float) $invoice->paid_amount - $reversed);
                    $newBalance = (float) $invoice->total - $newPaid;
                    $invoice->update([
                        'paid_amount' => $newPaid,
                        'balance_due' => $newBalance,
                        'status'      => $newPaid <= 0 ? 'confirmed' : 'partially_paid',
                    ]);

                    // Journal: DR AR, CR Cheques in Hand (reverse the receipt)
                    try {
                        $branchId    = $invoice->branch_id;
                        $arId        = $invoice->customer?->account_id
                                       ?: $this->accounting->getAccountId($branchId, 'acc_trade_receivables');
                        $chequeAccId = $this->accounting->getAccountId($branchId, 'acc_cheques_in_hand')
                                       ?: $this->accounting->getAccountId($branchId, 'acc_cash');
                        if ($arId && $chequeAccId) {
                            $this->accounting->createEntry(
                                $branchId, 'cheque_bounced',
                                "Cheque Bounced – {$cheque->cheque_number} / Invoice {$invoice->invoice_number}",
                                $today,
                                [
                                    ['account_id' => $arId,        'debit' => $reversed, 'credit' => 0,        'description' => "Bounced cheque {$cheque->cheque_number}"],
                                    ['account_id' => $chequeAccId, 'debit' => 0,          'credit' => $reversed, 'description' => "Bounced cheque {$cheque->cheque_number}"],
                                ],
                                $userId, 'cheque', $cheque->id
                            );
                        }
                    } catch (\Throwable $e) {
                        Log::error('cheque bounce invoice journal failed', ['cheque_id' => $cheque->id, 'error' => $e->getMessage()]);
                    }
                }

                // Reverse customer credit for any overpayment portion — computed once
                // across the whole cheque, not per invoice link.
                $overpayment = round((float) $cheque->amount - $totalReversed, 2);
                if ($overpayment > 0 && $creditCustomer) {
                    $newCredit = max(0, (float) $creditCustomer->credit_balance - $overpayment);
                    $creditCustomer->update(['credit_balance' => $newCredit]);
                }

                // ── 2. Reverse supplier payments (cheque transferred to supplier) ──
                foreach ($cheque->supplierPayments as $payment) {
                    $amount = (float) $payment->amount;

                    // Reverse supplier invoice balance
                    if ($payment->supplierInvoice) {
                        $si      = $payment->supplierInvoice;
                        $newPaid = max(0, (float) $si->paid_amount - $amount);
                        $si->update([
                            'paid_amount' => $newPaid,
                            'balance_due' => (float) $si->total - $newPaid,
                            'status'      => $newPaid <= 0 ? 'pending' : 'partially_paid',
                        ]);
                    }

                    // Reverse purchase order payment balance
                    if ($payment->purchaseOrder) {
                        $po      = $payment->purchaseOrder;
                        $newPaid = max(0, (float) $po->paid_amount - $amount);
                        $po->update([
                            'paid_amount'    => $newPaid,
                            'balance_due'    => (float) $po->total - $newPaid,
                            'payment_status' => $newPaid <= 0 ? 'unpaid' : 'partially_paid',
                        ]);
                    }

                    // Journal: DR Payment Account, CR AP (reverse the supplier payment)
                    // Original was: DR AP, CR Cash/Cheque Account → reverse it
                    try {
                        $supplier = $payment->supplierInvoice?->supplier
                                 ?? $payment->purchaseOrder?->supplier;
                        $branchId = $payment->branch_id;
                        $apId     = $supplier?->account_id
                                    ?: $this->accounting->getAccountId($branchId, 'acc_trade_payables');
                        $payAccId = $payment->account_id
                                    ?: $this->accounting->getAccountId($branchId, 'acc_cheques_in_hand')
                                    ?: $this->accounting->getAccountId($branchId, 'acc_cash');
                        $ref = $payment->supplierInvoice?->invoice_number
                            ?? $payment->purchaseOrder?->po_number
                            ?? 'N/A';
                        if ($apId && $payAccId) {
                            $this->accounting->createEntry(
                                $branchId, 'cheque_bounced',
                                "Cheque Bounced (Supplier) – {$cheque->cheque_number} / {$ref}",
                                $today,
                                [
                                    ['account_id' => $payAccId, 'debit' => $amount, 'credit' => 0,       'description' => "Bounced cheque reversal {$cheque->cheque_number}"],
                                    ['account_id' => $apId,     'debit' => 0,        'credit' => $amount, 'description' => "Restore AP – {$ref}"],
                                ],
                                $userId, 'cheque', $cheque->id
                            );
                        }
                    } catch (\Throwable $e) {
                        Log::error('cheque bounce supplier journal failed', ['cheque_id' => $cheque->id, 'payment_id' => $payment->id, 'error' => $e->getMessage()]);
                    }
                }

                // ── 3. Reverse a customer opening-balance payment (no invoice to link to) ──
                // Look at whichever of this cheque's opening-balance entries is most recent:
                // if it's the original payment (not yet reversed), reverse it now. This stays
                // correct across repeated bounce/un-bounce cycles, not just the first one.
                $obPayment = JournalEntry::where('reference_type', 'cheque')
                    ->where('reference_id', $cheque->id)
                    ->whereIn('type', ['opening_balance_payment', 'opening_balance_payment_reversed'])
                    ->where('status', 'posted')
                    ->with('lines')
                    ->latest('id')
                    ->first();
                if ($obPayment && $obPayment->type === 'opening_balance_payment') {
                    // opening_balance itself was never touched when this payment was
                    // recorded (see Account::openingBalancePaid()) — reversing the
                    // journal entry alone is enough to restore what's owed.
                    if ($obPayment->lines->isNotEmpty()) {
                        try {
                            $this->accounting->createEntry(
                                $cheque->branch_id, 'opening_balance_payment_reversed',
                                "Cheque Bounced – Opening balance payment reversed – {$cheque->cheque_number}",
                                $today,
                                $obPayment->lines->map(fn($l) => [
                                    'account_id'  => $l->account_id,
                                    'debit'       => (float) $l->credit,
                                    'credit'      => (float) $l->debit,
                                    'description' => "Cheque {$cheque->cheque_number} bounced — reversing opening balance payment",
                                ])->all(),
                                $userId, 'cheque', $cheque->id
                            );
                        } catch (\Throwable $e) {
                            Log::error('cheque bounce opening-balance-payment journal failed', ['cheque_id' => $cheque->id, 'error' => $e->getMessage()]);
                        }
                    }
                }

                // ── 4. Reverse expense payments made with this cheque ─────────
                $expenses = Expense::where('cheque_id', $cheque->id)->get();
                foreach ($expenses as $expense) {
                    // Payment reversed — set to pending so it appears as "needs re-payment"
                    $expense->update([
                        'status'    => 'pending',
                        'notes'     => trim(($expense->notes ?? '') . "\nPayment reversed: cheque {$cheque->cheque_number} bounced on {$today}."),
                    ]);

                    // Journal: DR Payment Account, CR Expense Account (reverse original expense payment)
                    try {
                        if ($expense->account_id && $expense->payment_account_id) {
                            $this->accounting->createEntry(
                                $expense->branch_id, 'cheque_bounced',
                                "Cheque Bounced (Expense) – {$cheque->cheque_number} / {$expense->description}",
                                $today,
                                [
                                    ['account_id' => $expense->payment_account_id, 'debit' => (float) $expense->amount, 'credit' => 0,                        'description' => "Restore payment – {$expense->description}"],
                                    ['account_id' => $expense->account_id,         'debit' => 0,                         'credit' => (float) $expense->amount, 'description' => "Reverse expense – {$expense->description}"],
                                ],
                                $userId, 'expense', $expense->id
                            );
                        }
                    } catch (\Throwable $e) {
                        Log::error('cheque bounce expense journal failed', ['cheque_id' => $cheque->id, 'expense_id' => $expense->id, 'error' => $e->getMessage()]);
                    }
                }
            });
        }

        if ($wasUnbounced) {
            DB::transaction(function () use ($cheque, $request) {
                $cheque->load([
                    'invoiceLinks.invoice.customer',
                    'supplierPayments.supplierInvoice.supplier',
                    'supplierPayments.purchaseOrder.supplier',
                ]);

                $today  = now()->toDateString();
                $userId = $request->user()->id;

                // ── 1. Re-apply customer invoice payments ──────────────────────
                $totalReapplied = 0;
                $creditCustomer = null;
                foreach ($cheque->invoiceLinks as $link) {
                    $invoice = $link->invoice;
                    if (!$invoice) continue;

                    $amount = (float) $link->amount;
                    $totalReapplied += $amount;
                    $creditCustomer = $creditCustomer ?: $invoice->customer;
                    $newPaid    = (float) $invoice->paid_amount + $amount;
                    $newBalance = max(0, (float) $invoice->total - $newPaid);
                    $invoice->update([
                        'paid_amount' => $newPaid,
                        'balance_due' => $newBalance,
                        'status'      => $newBalance <= 0 ? 'paid' : 'partially_paid',
                    ]);

                    // Journal: DR Cheques in Hand, CR AR (re-apply the receipt)
                    try {
                        $branchId    = $invoice->branch_id;
                        $arId        = $invoice->customer?->account_id
                                       ?: $this->accounting->getAccountId($branchId, 'acc_trade_receivables');
                        $chequeAccId = $this->accounting->getAccountId($branchId, 'acc_cheques_in_hand')
                                       ?: $this->accounting->getAccountId($branchId, 'acc_cash');
                        if ($arId && $chequeAccId) {
                            $this->accounting->createEntry(
                                $branchId, 'cheque_bounced',
                                "Cheque Re-presented – {$cheque->cheque_number} / Invoice {$invoice->invoice_number}",
                                $today,
                                [
                                    ['account_id' => $chequeAccId, 'debit' => $amount, 'credit' => 0,       'description' => "Cheque {$cheque->cheque_number} reinstated"],
                                    ['account_id' => $arId,        'debit' => 0,        'credit' => $amount, 'description' => "Cheque {$cheque->cheque_number} reinstated"],
                                ],
                                $userId, 'cheque', $cheque->id
                            );
                        }
                    } catch (\Throwable $e) {
                        Log::error('cheque un-bounce invoice journal failed', ['cheque_id' => $cheque->id, 'error' => $e->getMessage()]);
                    }
                }

                // Re-apply the overpayment credit once, across the whole cheque
                $overpayment = round((float) $cheque->amount - $totalReapplied, 2);
                if ($overpayment > 0 && $creditCustomer) {
                    $creditCustomer->update(['credit_balance' => (float) $creditCustomer->credit_balance + $overpayment]);
                }

                // ── 2. Re-apply supplier payments ──────────────────────────────
                foreach ($cheque->supplierPayments as $payment) {
                    $amount = (float) $payment->amount;

                    if ($payment->supplierInvoice) {
                        $si      = $payment->supplierInvoice;
                        $newPaid = (float) $si->paid_amount + $amount;
                        $si->update([
                            'paid_amount' => $newPaid,
                            'balance_due' => max(0, (float) $si->total - $newPaid),
                            'status'      => $newPaid >= (float) $si->total ? 'paid' : 'partially_paid',
                        ]);
                    }

                    if ($payment->purchaseOrder) {
                        $po      = $payment->purchaseOrder;
                        $newPaid = (float) $po->paid_amount + $amount;
                        $po->update([
                            'paid_amount'    => $newPaid,
                            'balance_due'    => max(0, (float) $po->total - $newPaid),
                            'payment_status' => $newPaid >= (float) $po->total ? 'paid' : 'partially_paid',
                        ]);
                    }

                    // Journal: DR AP, CR Payment Account (re-apply the supplier payment)
                    try {
                        $supplier = $payment->supplierInvoice?->supplier
                                 ?? $payment->purchaseOrder?->supplier;
                        $branchId = $payment->branch_id;
                        $apId     = $supplier?->account_id
                                    ?: $this->accounting->getAccountId($branchId, 'acc_trade_payables');
                        $payAccId = $payment->account_id
                                    ?: $this->accounting->getAccountId($branchId, 'acc_cheques_in_hand')
                                    ?: $this->accounting->getAccountId($branchId, 'acc_cash');
                        $ref = $payment->supplierInvoice?->invoice_number
                            ?? $payment->purchaseOrder?->po_number
                            ?? 'N/A';
                        if ($apId && $payAccId) {
                            $this->accounting->createEntry(
                                $branchId, 'cheque_bounced',
                                "Cheque Re-presented (Supplier) – {$cheque->cheque_number} / {$ref}",
                                $today,
                                [
                                    ['account_id' => $apId,     'debit' => $amount, 'credit' => 0,       'description' => "Re-apply AP – {$ref}"],
                                    ['account_id' => $payAccId, 'debit' => 0,        'credit' => $amount, 'description' => "Cheque {$cheque->cheque_number} reinstated"],
                                ],
                                $userId, 'cheque', $cheque->id
                            );
                        }
                    } catch (\Throwable $e) {
                        Log::error('cheque un-bounce supplier journal failed', ['cheque_id' => $cheque->id, 'payment_id' => $payment->id, 'error' => $e->getMessage()]);
                    }
                }

                // ── 3. Re-apply a customer opening-balance payment (no invoice to link to) ──
                $obReversal = JournalEntry::where('reference_type', 'cheque')
                    ->where('reference_id', $cheque->id)
                    ->whereIn('type', ['opening_balance_payment', 'opening_balance_payment_reversed'])
                    ->where('status', 'posted')
                    ->with('lines')
                    ->latest('id')
                    ->first();
                if ($obReversal && $obReversal->type === 'opening_balance_payment_reversed') {
                    if ($obReversal->lines->isNotEmpty()) {
                        try {
                            $this->accounting->createEntry(
                                $cheque->branch_id, 'opening_balance_payment',
                                "Cheque Re-presented – Opening balance payment reinstated – {$cheque->cheque_number}",
                                $today,
                                $obReversal->lines->map(fn($l) => [
                                    'account_id'  => $l->account_id,
                                    'debit'       => (float) $l->credit,
                                    'credit'      => (float) $l->debit,
                                    'description' => "Cheque {$cheque->cheque_number} reinstated",
                                ])->all(),
                                $userId, 'cheque', $cheque->id
                            );
                        } catch (\Throwable $e) {
                            Log::error('cheque un-bounce opening-balance-payment journal failed', ['cheque_id' => $cheque->id, 'error' => $e->getMessage()]);
                        }
                    }
                }

                // ── 4. Re-apply expense payments made with this cheque ─────────
                $expenses = Expense::where('cheque_id', $cheque->id)->get();
                foreach ($expenses as $expense) {
                    $expense->update([
                        'status' => 'approved',
                        'notes'  => trim(($expense->notes ?? '') . "\nPayment reinstated: cheque {$cheque->cheque_number} re-presented on {$today}."),
                    ]);

                    // Journal: DR Expense Account, CR Payment Account (re-apply original expense payment)
                    try {
                        if ($expense->account_id && $expense->payment_account_id) {
                            $this->accounting->createEntry(
                                $expense->branch_id, 'cheque_bounced',
                                "Cheque Re-presented (Expense) – {$cheque->cheque_number} / {$expense->description}",
                                $today,
                                [
                                    ['account_id' => $expense->account_id,         'debit' => (float) $expense->amount, 'credit' => 0,                        'description' => "Re-apply expense – {$expense->description}"],
                                    ['account_id' => $expense->payment_account_id, 'debit' => 0,                         'credit' => (float) $expense->amount, 'description' => "Cheque {$cheque->cheque_number} reinstated"],
                                ],
                                $userId, 'expense', $expense->id
                            );
                        }
                    } catch (\Throwable $e) {
                        Log::error('cheque un-bounce expense journal failed', ['cheque_id' => $cheque->id, 'expense_id' => $expense->id, 'error' => $e->getMessage()]);
                    }
                }
            });
        }

        return response()->json($cheque->fresh());
    }

    public function dueToday(Request $request): JsonResponse
    {
        $q = Cheque::where('status', 'in_hand')->whereDate('cheque_date', today());
        $this->branchContext->applyScope($q);
        return response()->json($q->with('branch')->get());
    }

    public function dueThisWeek(Request $request): JsonResponse
    {
        $q = Cheque::where('status', 'in_hand')
            ->whereBetween('cheque_date', [today(), today()->addDays(7)]);
        $this->branchContext->applyScope($q);
        return response()->json($q->with('branch')->orderBy('cheque_date')->get());
    }

    public function bankSummary(Request $request): JsonResponse
    {
        $q = Cheque::query();
        $this->branchContext->applyScope($q);

        $summary = $q->selectRaw('bank_name, status, direction, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('bank_name', 'status', 'direction')
            ->get();

        return response()->json($summary);
    }

    public function partyHistory(Request $request): JsonResponse
    {
        $request->validate([
            'party_type' => 'nullable|in:customer,supplier',
            'party_id'   => 'nullable|integer',
        ]);

        $query = Cheque::with([
            'branch', 'customer', 'supplier',
            'invoiceLinks.invoice',
            'supplierPayments.supplierInvoice.supplier',
            'purchaseOrders.supplier',
            'expenses.account',
        ]);
        $this->branchContext->applyScope($query);

        // Scope to specific party when provided
        if ($request->party_id) {
            $query->where('party_type', $request->party_type ?? 'customer')
                  ->where('party_id', (int) $request->party_id);
        } elseif ($request->party_type) {
            $query->where('party_type', $request->party_type);
        } else {
            // No party filter → default to received cheques for this view
            $query->where('direction', 'received');
        }

        if ($request->cheque_number) {
            $query->where('cheque_number', 'like', '%' . $request->cheque_number . '%');
        }
        if ($request->from_date) {
            $query->whereDate('cheque_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('cheque_date', '<=', $request->to_date);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $cheques = $query->orderByDesc('cheque_date')->get();

        // All-time totals for this party (bounce rate) — only meaningful when a specific party is selected
        if ($request->party_id) {
            $allQuery = Cheque::where('party_type', $request->party_type ?? 'customer')
                ->where('party_id', (int) $request->party_id)
                ->with(['purchaseOrders', 'supplierPayments']);
            $this->branchContext->applyScope($allQuery);
            $all = $allQuery->get();
        } else {
            $all = $cheques; // fall back to filtered set when no party specified
        }

        $analysis = [
            'total_count'      => $cheques->count(),
            'total_amount'     => (float) $cheques->sum('amount'),
            'in_hand_count'    => $cheques->where('status', 'in_hand')->count(),
            'in_hand_amount'   => (float) $cheques->where('status', 'in_hand')->sum('amount'),
            'party_count'      => $cheques->filter(fn($c) => $c->status === 'in_hand' && $c->purchaseOrders->isNotEmpty())->count(),
            'party_amount'     => (float) $cheques->filter(fn($c) => $c->status === 'in_hand' && $c->purchaseOrders->isNotEmpty())->sum('amount'),
            'deposited_count'  => $cheques->where('status', 'deposited')->count(),
            'deposited_amount' => (float) $cheques->where('status', 'deposited')->sum('amount'),
            'cleared_count'    => $cheques->where('status', 'cleared')->count(),
            'cleared_amount'   => (float) $cheques->where('status', 'cleared')->sum('amount'),
            'bounced_count'    => $cheques->where('status', 'bounced')->count(),
            'bounced_amount'   => (float) $cheques->where('status', 'bounced')->sum('amount'),
            'bounce_rate'      => $all->count() > 0
                ? round($all->where('status', 'bounced')->count() / $all->count() * 100, 2) : 0,
            'last_bounce_date' => $all->where('status', 'bounced')->max('bounced_date'),
            'used_for_supplier'=> $cheques->filter(fn($c) => $c->supplierPayments->isNotEmpty())->count(),
        ];

        return response()->json(['cheques' => $cheques, 'analysis' => $analysis]);
    }
}
