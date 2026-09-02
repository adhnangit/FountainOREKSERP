<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountGroup;
use App\Models\FinancialYear;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\SystemSetting;
use App\Services\AccountingService;
use App\Services\BranchContextService;
use App\Services\NumberGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountingController extends Controller
{
    private const OPENING_BALANCE_EQUITY_NAME = 'Opening Balance Equity';

    public function __construct(
        private AccountingService    $accounting,
        private BranchContextService $branchContext,
        private NumberGeneratorService $numbers
    ) {}

    // ── Chart of Accounts ────────────────────────────────────────────

    public function accounts(Request $request): JsonResponse
    {
        $q = Account::with('group');
        $this->branchContext->applyScope($q);
        if (!$request->boolean('include_inactive', false)) {
            $q->where('is_active', true);
        }
        if ($request->search) {
            $q->where(fn($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%")
            );
        }
        if ($request->type)     $q->where('type', $request->type);
        if ($request->group_id) $q->where('group_id', $request->group_id);

        // Balances must match what the general ledger shows for the same branch
        // context: posted entries only, filtered to the active branch.
        $branchId = $this->branchContext->getBranchId();
        $sums = \App\Models\JournalEntryLine::join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted')
            ->when($branchId, fn($qq) => $qq->where('journal_entries.branch_id', $branchId))
            ->selectRaw('journal_entry_lines.account_id, SUM(journal_entry_lines.debit) as dr, SUM(journal_entry_lines.credit) as cr')
            ->groupBy('journal_entry_lines.account_id')
            ->get()->keyBy('account_id');

        $balanceFor = function ($a) use ($sums, $branchId) {
            $dr = (float) ($sums[$a->id]->dr ?? 0);
            $cr = (float) ($sums[$a->id]->cr ?? 0);
            $ob = $this->accountOpeningBalance($a, $branchId);
            return $a->normal_balance === 'debit'
                ? $ob + $dr - $cr
                : $ob + $cr - $dr;
        };

        // In "All Branches" view, the same account name can legitimately exist
        // once per branch (each branch's own clone) — load branch names so the
        // frontend can show e.g. "Cash in Hand — MEDRI EAST" instead of three
        // indistinguishable "Cash in Hand" rows.
        $branchNames = $this->branchContext->isAllBranches()
            ? \App\Models\Branch::pluck('name', 'id')
            : collect();

        $accounts = $q->orderBy('code')->get()
            ->map(fn($a) => [
                'id'             => $a->id,
                'code'           => $a->code,
                'name'           => $a->name,
                'branch_id'      => $a->branch_id,
                'branch_name'    => $branchNames[$a->branch_id] ?? null,
                'type'           => $a->type,
                'group_id'       => (int) $a->group_id,
                'group'          => $a->group?->name,
                'normal_balance' => $a->normal_balance,
                'is_bank_account'=> $a->is_bank_account,
                'is_cash_account'=> $a->is_cash_account,
                'is_active'      => $a->is_active,
                'opening_balance'=> (float) $a->opening_balance,
                'balance'        => $balanceFor($a),
                'description'    => $a->description,
            ])
            // Zero-balance accounts are shown by default — every consumer of this
            // endpoint (Chart of Accounts, journal entry / expense / payment account
            // pickers, General Ledger) is either "browse everything" or "pick an
            // account to post to," and a freshly created account with no activity
            // yet is completely normal in both cases. Hiding it was actively
            // harmful: it read as "my newly created account disappeared" (it
            // hadn't — this filter was just hiding it) the moment its balance was
            // zero, which is true for every account for at least a moment after
            // creation. Pass hide_zero_balance=1 for the old decluttered view
            // (e.g. a branch's freshly cloned, still-unused structural accounts).
            ->when($request->boolean('hide_zero_balance', false), fn($c) => $c->filter(
                fn($a) => abs($a['balance']) > 0.001 || $a['is_bank_account'] || $a['is_cash_account']
            ))
            ->values();

        return response()->json($accounts);
    }

    /**
     * A user scoped to one branch (or a specific branch selected by a
     * super-admin) must never write to another branch's account via a
     * crafted branch_id — only "All Branches" mode (super-admin, no branch
     * selected) is exempt.
     */
    private function assertBranchWriteAllowed(int $branchId): void
    {
        if (!$this->branchContext->isAllBranches() && $this->branchContext->getBranchId() !== $branchId) {
            abort(403, 'Unauthorized branch access.');
        }
    }

    public function storeAccount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id'      => 'required|exists:branches,id',
            'group_id'       => 'required|exists:account_groups,id',
            'name'           => 'required|string|max:255',
            'code'           => ['required', 'string', 'max:20', \Illuminate\Validation\Rule::unique('accounts')->where(fn($q) => $q->where('branch_id', $request->branch_id))],
            'type'           => 'required|in:asset,liability,equity,income,expense',
            'normal_balance' => 'required|in:debit,credit',
            'opening_balance'=> 'nullable|numeric',
            'is_bank_account'=> 'boolean',
            'is_cash_account'=> 'boolean',
            'description'    => 'nullable|string',
        ]);

        $this->assertBranchWriteAllowed((int) $data['branch_id']);

        $account = Account::create($data);

        if ($account->name !== self::OPENING_BALANCE_EQUITY_NAME && (float) ($data['opening_balance'] ?? 0) !== 0.0) {
            $this->adjustOpeningBalanceEquity($account, 0.0, (float) $account->opening_balance);
        }

        return response()->json($account->load('group'), 201);
    }

    public function updateAccount(Request $request, Account $account): JsonResponse
    {
        $this->assertBranchWriteAllowed((int) $account->branch_id);

        $data = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'code'           => ['sometimes', 'string', 'max:20', \Illuminate\Validation\Rule::unique('accounts')->where(fn($q) => $q->where('branch_id', $account->branch_id))->ignore($account->id)],
            'is_active'      => 'sometimes|boolean',
            'description'    => 'nullable|string',
            'opening_balance'=> 'nullable|numeric',
        ]);

        $oldOpeningBalance = (float) $account->opening_balance;
        $account->update($data);

        if ($account->name !== self::OPENING_BALANCE_EQUITY_NAME && array_key_exists('opening_balance', $data)) {
            $this->adjustOpeningBalanceEquity($account, $oldOpeningBalance, (float) $account->opening_balance);
        }

        return response()->json($account->fresh('group'));
    }

    public function destroyAccount(Account $account): JsonResponse
    {
        $this->assertBranchWriteAllowed((int) $account->branch_id);

        if ($account->journalLines()->exists()) {
            return response()->json(['message' => 'Cannot delete an account that has journal entries.'], 422);
        }

        if ($account->name !== self::OPENING_BALANCE_EQUITY_NAME && (float) $account->opening_balance !== 0.0) {
            $this->adjustOpeningBalanceEquity($account, (float) $account->opening_balance, 0.0);
        }

        $account->delete();
        return response()->json(['message' => 'Account deleted.']);
    }

    // ── Account Groups ────────────────────────────────────────────────

    public function groups(): JsonResponse
    {
        // Eager-loaded accounts (including nested under child groups) must respect
        // the same branch scope as the main accounts() listing — previously
        // unfiltered here, so any group with sub-groups would carry every
        // branch's accounts in its response even though the frontend only
        // uses the top-level `accounts` key today (rebuilt from the properly
        // scoped accounts() call). Closing this so it can't silently leak the
        // moment a sub-group is ever created.
        $scopeAccounts = function ($q) {
            $this->branchContext->applyScope($q);
        };
        $groups = AccountGroup::with(['children.accounts' => $scopeAccounts, 'accounts' => $scopeAccounts])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();
        return response()->json($groups);
    }

    public function storeGroup(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'code'       => 'required|string|max:10|unique:account_groups,code',
            'type'       => 'required|in:asset,liability,equity,income,expense',
            'parent_id'  => 'nullable|exists:account_groups,id',
            'sort_order' => 'nullable|integer',
        ]);

        $group = AccountGroup::create($data);
        return response()->json($group, 201);
    }

    // ── Financial Years ────────────────────────────────────────────────

    public function financialYears(): JsonResponse
    {
        return response()->json(FinancialYear::orderByDesc('start_date')->get());
    }

    public function storeFinancialYear(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        if (FinancialYear::where('status', 'active')->exists()) {
            FinancialYear::where('status', 'active')->update(['status' => 'closed']);
        }

        $year = FinancialYear::create(array_merge($data, ['status' => 'active']));
        return response()->json($year, 201);
    }

    // ── Journal Entries ────────────────────────────────────────────────

    public function journalEntries(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();
        $q = JournalEntry::with('lines.account', 'createdBy', 'branch');

        if ($branchId)           $q->where('branch_id', $branchId);
        if ($request->date_from) $q->whereDate('entry_date', '>=', $request->date_from);
        if ($request->date_to)   $q->whereDate('entry_date', '<=', $request->date_to);
        if ($request->type)      $q->where('type', $request->type);
        if ($request->search) {
            $q->where(fn($q) => $q
                ->where('entry_number', 'like', "%{$request->search}%")
                ->orWhere('description', 'like', "%{$request->search}%")
            );
        }

        return response()->json($q->latest('entry_date')->paginate($request->input('per_page', 30)));
    }

    public function showJournalEntry(JournalEntry $journalEntry): JsonResponse
    {
        return response()->json($journalEntry->load('lines.account', 'createdBy', 'branch'));
    }

    public function storeJournalEntry(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id'           => 'required|exists:branches,id',
            'entry_date'          => 'required|date',
            'description'         => 'required|string',
            'lines'               => 'required|array|min:2',
            'lines.*.account_id'  => 'required|exists:accounts,id',
            'lines.*.debit'       => 'nullable|numeric|min:0',
            'lines.*.credit'      => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string',
        ]);

        $totalDebit  = collect($data['lines'])->sum('debit');
        $totalCredit = collect($data['lines'])->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return response()->json(['message' => 'Journal entry is not balanced. Debits (' . $totalDebit . ') must equal Credits (' . $totalCredit . ').'], 422);
        }

        $year = FinancialYear::active();
        if (!$year) {
            return response()->json(['message' => 'No active financial year. Please create one first.'], 422);
        }

        if ($data['entry_date'] < $year->start_date->toDateString() || $data['entry_date'] > $year->end_date->toDateString()) {
            return response()->json([
                'message' => "Entry date must fall within the active financial year ({$year->start_date->toDateString()} to {$year->end_date->toDateString()}).",
            ], 422);
        }

        return DB::transaction(function () use ($data, $request, $year, $totalDebit, $totalCredit) {
            $entry = JournalEntry::create([
                'branch_id'         => $data['branch_id'],
                'financial_year_id' => $year->id,
                'created_by'        => $request->user()->id,
                'entry_number'      => $this->numbers->journalNumber($data['branch_id']),
                'type'              => 'manual',
                'entry_date'        => $data['entry_date'],
                'description'       => $data['description'],
                'total_debit'       => $totalDebit,
                'total_credit'      => $totalCredit,
                'status'            => 'posted',
                'is_auto_generated' => false,
            ]);

            foreach ($data['lines'] as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id'       => $line['account_id'],
                    'debit'            => $line['debit'] ?? 0,
                    'credit'           => $line['credit'] ?? 0,
                    'description'      => $line['description'] ?? null,
                ]);
            }

            return response()->json($entry->load('lines.account', 'createdBy'), 201);
        }, 5);
    }

    public function destroyJournalEntry(JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->is_auto_generated || $journalEntry->reference_type) {
            // Expense-sourced entries can be cleanly deleted end-to-end (source
            // record + journal entry + any linked cheque) — delegate to that flow
            // instead of just blocking, so the ledger's delete button does what
            // it looks like it does. Other reference types (invoice, payment,
            // GRN, purchase/sales return, cheque bounce) don't yet have a safe
            // single-call reversal and still require going to their own page.
            if ($journalEntry->reference_type === 'expense') {
                $expense = \App\Models\Expense::find($journalEntry->reference_id);
                if (!$expense) {
                    return response()->json(['message' => 'The source expense no longer exists.'], 404);
                }
                return app(ExpenseController::class)->destroy($expense);
            }

            return response()->json([
                'message' => 'This entry was generated automatically from a '
                    . ($journalEntry->reference_type ?? 'system')
                    . ' transaction and cannot be deleted directly — reverse or cancel the source transaction instead.',
            ], 422);
        }

        DB::transaction(function () use ($journalEntry) {
            $journalEntry->lines()->delete();
            $journalEntry->delete();
        });

        return response()->json(['message' => 'Journal entry deleted.']);
    }

    // ── Reports ────────────────────────────────────────────────────────

    public function trialBalance(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();
        $asOf     = $request->as_of ?? now()->toDateString();

        $accounts = Account::with('group')->where('is_active', true)->orderBy('code')->get()
            ->map(function ($account) use ($branchId, $asOf) {
                [$debits, $credits] = $this->accountMovements($account->id, $branchId, null, $asOf);
                $ob = $this->accountOpeningBalance($account, $branchId);

                $balance = $account->normal_balance === 'debit'
                    ? $ob + $debits - $credits
                    : $ob + $credits - $debits;

                return [
                    'id'             => $account->id,
                    'code'           => $account->code,
                    'name'           => $account->name,
                    'type'           => $account->type,
                    'group'          => $account->group?->name,
                    'normal_balance' => $account->normal_balance,
                    'debit_balance'  => $balance > 0 && $account->normal_balance === 'debit'  ? $balance : ($balance < 0 && $account->normal_balance === 'credit' ? -$balance : 0),
                    'credit_balance' => $balance > 0 && $account->normal_balance === 'credit' ? $balance : ($balance < 0 && $account->normal_balance === 'debit'  ? -$balance : 0),
                ];
            })
            ->filter(fn($a) => $a['debit_balance'] > 0 || $a['credit_balance'] > 0)
            ->values();

        $accounts = $this->mergeAccountRowsForAllBranches($accounts, ['debit_balance', 'credit_balance']);

        $totalDebit  = $accounts->sum('debit_balance');
        $totalCredit = $accounts->sum('credit_balance');

        return response()->json([
            'as_of'         => $asOf,
            'accounts'      => $accounts,
            'total_debit'   => $totalDebit,
            'total_credit'  => $totalCredit,
            'balanced'      => abs($totalDebit - $totalCredit) < 0.01,
        ]);
    }

    public function profitLoss(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();
        $fromDate = $request->from_date ?? now()->startOfYear()->toDateString();
        $toDate   = $request->to_date   ?? now()->toDateString();

        $accounts = Account::with('group')
            ->whereIn('type', ['income', 'expense'])
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($branchId, $fromDate, $toDate) {
                [$debits, $credits] = $this->accountMovements($account->id, $branchId, $fromDate, $toDate);

                $balance = $account->normal_balance === 'credit'
                    ? (float) ($credits - $debits)
                    : (float) ($debits - $credits);

                return [
                    'id'             => $account->id,
                    'code'           => $account->code,
                    'name'           => $account->name,
                    'type'           => $account->type,
                    'group'          => $account->group?->name,
                    'normal_balance' => $account->normal_balance,
                    'balance'        => $balance,
                ];
            })
            ->filter(fn($a) => abs($a['balance']) > 0.001)
            ->values();

        $accounts = $this->mergeAccountRowsForAllBranches($accounts, ['balance']);

        $incomeAccounts  = $accounts->where('type', 'income')->values();
        $expenseAccounts = $accounts->where('type', 'expense')->values();

        // Contra accounts (e.g. Sales Returns: type=income but normal_balance=debit)
        // must SUBTRACT from their category total rather than add.
        $totalIncome   = $incomeAccounts->sum(fn($a) => $a['normal_balance'] === 'credit' ? $a['balance'] : -$a['balance']);
        $totalExpenses = $expenseAccounts->sum(fn($a) => $a['normal_balance'] === 'debit' ? $a['balance'] : -$a['balance']);

        return response()->json([
            'from_date'       => $fromDate,
            'to_date'         => $toDate,
            'income_accounts' => $incomeAccounts,
            'expense_accounts'=> $expenseAccounts,
            'total_income'    => $totalIncome,
            'total_expenses'  => $totalExpenses,
            'net_profit'      => $totalIncome - $totalExpenses,
        ]);
    }

    public function balanceSheet(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();
        $asOf     = $request->as_of ?? now()->toDateString();

        $accounts = Account::with('group')
            ->whereIn('type', ['asset', 'liability', 'equity'])
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($account) use ($branchId, $asOf) {
                [$debits, $credits] = $this->accountMovements($account->id, $branchId, null, $asOf);
                $ob = $this->accountOpeningBalance($account, $branchId);

                $balance = $account->normal_balance === 'debit'
                    ? $ob + $debits - $credits
                    : $ob + $credits - $debits;

                return [
                    'id'      => $account->id,
                    'code'    => $account->code,
                    'name'    => $account->name,
                    'type'    => $account->type,
                    'group'   => $account->group?->name,
                    'balance' => $balance,
                ];
            })
            ->filter(fn($a) => abs($a['balance']) > 0.001)
            ->values();

        $accounts = $this->mergeAccountRowsForAllBranches($accounts, ['balance']);

        // Retained earnings since inception: this system has no year-end closing
        // entries into a dedicated equity account, so the balance sheet must carry
        // cumulative net profit all-time (not just this calendar year) — and, like
        // every asset/liability/equity account above, it must include each income/
        // expense account's own opening_balance (legacy balance from before this
        // system's journal tracking began — see accountOpeningBalance()), or assets
        // will never reconcile to liabilities+equity.
        $retainedEarnings = Account::whereIn('type', ['income', 'expense'])
            ->where('is_active', true)
            ->get()
            ->sum(function ($account) use ($branchId, $asOf) {
                [$debits, $credits] = $this->accountMovements($account->id, $branchId, null, $asOf);
                $ob = $this->accountOpeningBalance($account, $branchId);
                $balance = $account->normal_balance === 'debit'
                    ? $ob + $debits - $credits
                    : $ob + $credits - $debits;

                if ($account->type === 'income') {
                    return $account->normal_balance === 'credit' ? $balance : -$balance;
                }
                return $account->normal_balance === 'debit' ? -$balance : $balance;
            });

        return response()->json([
            'as_of'                   => $asOf,
            'asset_accounts'          => $accounts->where('type', 'asset')->values(),
            'liability_accounts'      => $accounts->where('type', 'liability')->values(),
            'equity_accounts'         => $accounts->where('type', 'equity')->values(),
            'total_assets'            => $accounts->where('type', 'asset')->sum('balance'),
            'total_liabilities'       => $accounts->where('type', 'liability')->sum('balance'),
            'total_equity'            => $accounts->where('type', 'equity')->sum('balance'),
            'retained_earnings'       => $retainedEarnings,
            'total_liabilities_equity'=> $accounts->where('type', 'liability')->sum('balance')
                                       + $accounts->where('type', 'equity')->sum('balance')
                                       + $retainedEarnings,
        ]);
    }

    /** Name of the branch currently in scope, or "All Branches" — same as every other PDF export in the app. */
    private function activeBranchName(): string
    {
        $branchId = $this->branchContext->getBranchId();
        return $branchId ? (\App\Models\Branch::find($branchId)?->name ?? 'All Branches') : 'All Branches';
    }

    // Each PDF export calls straight into its own JSON endpoint above and reuses
    // the exact same computed data — the figures on the PDF can never drift
    // from what's on screen, because they're the same numbers, not a re-query.
    public function trialBalancePdf(Request $request)
    {
        $data = $this->trialBalance($request)->getData(true);
        $pdf = Pdf::loadView('pdf.trial-balance', ['data' => $data, 'branchName' => $this->activeBranchName()]);
        return $pdf->download("trial-balance-{$data['as_of']}.pdf");
    }

    public function profitLossPdf(Request $request)
    {
        $data = $this->profitLoss($request)->getData(true);
        $pdf = Pdf::loadView('pdf.profit-loss', ['data' => $data, 'branchName' => $this->activeBranchName()]);
        return $pdf->download("profit-loss-{$data['from_date']}-to-{$data['to_date']}.pdf");
    }

    public function balanceSheetPdf(Request $request)
    {
        $data = $this->balanceSheet($request)->getData(true);
        $pdf = Pdf::loadView('pdf.balance-sheet', ['data' => $data, 'branchName' => $this->activeBranchName()]);
        return $pdf->download("balance-sheet-{$data['as_of']}.pdf");
    }

    public function generalLedger(Request $request): JsonResponse
    {
        $request->validate(['account_id' => 'required|exists:accounts,id']);

        $account  = Account::findOrFail($request->account_id);
        $branchId = $this->branchContext->getBranchId();
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate   = $request->to_date   ?? now()->toDateString();

        // Opening balance before period
        [$obDr, $obCr] = $this->accountMovements($account->id, $branchId, null, date('Y-m-d', strtotime($fromDate . ' -1 day')));
        $ob = $this->accountOpeningBalance($account, $branchId);
        $openingBalance = $account->normal_balance === 'debit'
            ? $ob + $obDr - $obCr
            : $ob + $obCr - $obDr;

        // Lines in period
        $lines = JournalEntryLine::with('journalEntry')
            ->where('account_id', $account->id)
            ->whereHas('journalEntry', function ($q) use ($branchId, $fromDate, $toDate) {
                $q->where('status', 'posted')
                  ->whereDate('entry_date', '>=', $fromDate)
                  ->whereDate('entry_date', '<=', $toDate);
                if ($branchId) $q->where('branch_id', $branchId);
            })
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.id')
            ->select('journal_entry_lines.*')
            ->get();

        $runningBalance = $openingBalance;
        $entries = $lines->map(function ($line) use (&$runningBalance, $account) {
            if ($account->normal_balance === 'debit') {
                $runningBalance += (float) $line->debit - (float) $line->credit;
            } else {
                $runningBalance += (float) $line->credit - (float) $line->debit;
            }
            return [
                'journal_entry_id' => $line->journalEntry->id,
                'date'             => $line->journalEntry->entry_date,
                'entry_number'     => $line->journalEntry->entry_number,
                'type'             => $line->journalEntry->type,
                'description'      => $line->description ?? $line->journalEntry->description,
                'debit'            => (float) $line->debit,
                'credit'           => (float) $line->credit,
                'balance'          => $runningBalance,
            ];
        });

        return response()->json([
            'account'         => ['id' => $account->id, 'code' => $account->code, 'name' => $account->name, 'type' => $account->type],
            'from_date'       => $fromDate,
            'to_date'         => $toDate,
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
            'entries'         => $entries,
        ]);
    }

    // ── Accounting Settings ────────────────────────────────────────────

    public function getSettings(): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId() ?? 0;
        $keys = [
            'acc_trade_receivables', 'acc_trade_payables', 'acc_sales_revenue',
            'acc_sales_returns', 'acc_sales_cancelled', 'acc_cogs', 'acc_inventory',
            'acc_cash', 'acc_bank', 'acc_tax_payable', 'acc_cheques_in_hand',
            'acc_service_income',
        ];

        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = $this->accounting->getAccountId($branchId, $key) ?: null;
        }

        return response()->json($settings);
    }

    public function saveSettings(Request $request): JsonResponse
    {
        $data = $request->validate([
            'acc_trade_receivables' => 'nullable|exists:accounts,id',
            'acc_trade_payables'    => 'nullable|exists:accounts,id',
            'acc_sales_revenue'     => 'nullable|exists:accounts,id',
            'acc_sales_returns'     => 'nullable|exists:accounts,id',
            'acc_sales_cancelled'   => 'nullable|exists:accounts,id',
            'acc_cogs'              => 'nullable|exists:accounts,id',
            'acc_inventory'         => 'nullable|exists:accounts,id',
            'acc_cash'              => 'nullable|exists:accounts,id',
            'acc_bank'              => 'nullable|exists:accounts,id',
            'acc_tax_payable'       => 'nullable|exists:accounts,id',
            'acc_cheques_in_hand'   => 'nullable|exists:accounts,id',
            'acc_service_income'    => 'nullable|exists:accounts,id',
        ]);

        $branchId = $this->branchContext->getBranchId();
        foreach ($data as $key => $value) {
            if ($value !== null) {
                SystemSetting::updateOrCreate(
                    ['key' => $key, 'branch_id' => $branchId],
                    ['value' => (string) $value, 'group' => 'accounting']
                );
            }
        }

        return response()->json(['message' => 'Accounting settings saved successfully.']);
    }

    /** Live check: do all accounts' opening balances net to zero? Used for a Chart of Accounts banner. */
    public function openingBalanceCheck(): JsonResponse
    {
        $accounts = Account::select('opening_balance', 'normal_balance')->get();
        $net = $accounts->sum(fn($a) => $a->normal_balance === 'debit' ? (float) $a->opening_balance : -(float) $a->opening_balance);

        $equity = Account::where('name', self::OPENING_BALANCE_EQUITY_NAME)->first();

        return response()->json([
            'balanced'                  => abs($net) < 0.01,
            'difference'                => round($net, 2),
            'opening_balance_equity'    => $equity ? round((float) $equity->opening_balance, 2) : 0,
        ]);
    }

    // ── Private helpers ────────────────────────────────────────────────

    /**
     * Keep the chart of accounts balanced by construction: whenever any account's opening_balance
     * is set, changed, or removed, post the exact offsetting amount to the "Opening Balance Equity"
     * account. This exists because accounts are migrated in one at a time from the client's old
     * accounting system (their historical closing balance becomes the new opening balance), with
     * no cross-account balancing step, which otherwise throws Trial Balance / Balance Sheet out of
     * balance every time a new account is added.
     */
    private function adjustOpeningBalanceEquity(Account $account, float $oldValue, float $newValue): void
    {
        $delta = $newValue - $oldValue;
        if ($delta === 0.0) {
            return;
        }

        $signedDelta = $account->normal_balance === 'debit' ? $delta : -$delta;

        $equity = $this->openingBalanceEquityAccount($account->branch_id);
        $equity->opening_balance = (float) $equity->opening_balance + $signedDelta;
        $equity->save();
    }

    /**
     * Finds this branch's own "Opening Balance Equity" plug account, creating
     * it on first use. Must be scoped per-branch — offsetting a MEDFIX
     * account's opening balance against Head Office's equity account would
     * balance the books globally while leaving MEDFIX's own trial balance
     * (and Head Office's) both out of balance by the same amount.
     */
    private function openingBalanceEquityAccount(int $branchId): Account
    {
        $account = Account::where('name', self::OPENING_BALANCE_EQUITY_NAME)->where('branch_id', $branchId)->first();
        if ($account) {
            return $account;
        }

        $groupId = AccountGroup::where('type', 'equity')->orderBy('id')->value('id')
            ?? AccountGroup::orderBy('id')->value('id');

        do {
            $code = 'ACC' . random_int(10000, 99999);
        } while (Account::where('branch_id', $branchId)->where('code', $code)->exists());

        return Account::create([
            'branch_id'       => $branchId,
            'group_id'        => $groupId,
            'name'            => self::OPENING_BALANCE_EQUITY_NAME,
            'code'            => $code,
            'type'            => 'equity',
            'normal_balance'  => 'credit',
            'opening_balance' => 0,
            'is_active'       => true,
            'description'     => 'System account. Automatically absorbs the offsetting amount whenever another account\'s opening balance is added, changed, or removed, so the chart of accounts always nets to zero.',
        ]);
    }

    /**
     * An account's opening_balance only belongs to its own branch. Include it
     * when viewing "All Branches" (branchId null — every account contributes
     * its own opening balance exactly once) or when the selected branch is
     * that specific account's own branch; otherwise it's another branch's
     * figure and must read as 0 here.
     */
    private function accountOpeningBalance(Account $account, ?int $branchId): float
    {
        return ($branchId === null || $branchId === $account->branch_id)
            ? (float) $account->opening_balance
            : 0.0;
    }

    /**
     * In "All Branches" view, each branch's clone of a structural account
     * shares the same name (e.g. three separate "Cash in Hand" rows, one per
     * branch) — merge those into a single consolidated row so reports show
     * one line per logical account instead of duplicates. This only affects
     * the displayed breakdown: summing the merged rows equals summing the
     * original per-branch rows either way, so grand totals are unaffected.
     */
    private function mergeAccountRowsForAllBranches(\Illuminate\Support\Collection $rows, array $sumFields): \Illuminate\Support\Collection
    {
        if (!$this->branchContext->isAllBranches()) {
            return $rows;
        }
        return $rows->groupBy('name')->map(function ($group) use ($sumFields) {
            $merged = $group->first();
            foreach ($sumFields as $field) {
                $merged[$field] = $group->sum($field);
            }
            return $merged;
        })->values();
    }

    /** Returns [totalDebits, totalCredits] for an account in a date range */
    private function accountMovements(int $accountId, ?int $branchId, ?string $fromDate, ?string $toDate): array
    {
        $q = JournalEntryLine::where('account_id', $accountId)
            ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
            ->where('journal_entries.status', 'posted');

        if ($branchId) $q->where('journal_entries.branch_id', $branchId);
        if ($fromDate)  $q->whereDate('journal_entries.entry_date', '>=', $fromDate);
        if ($toDate)    $q->whereDate('journal_entries.entry_date', '<=', $toDate);

        $debits  = (float) (clone $q)->sum('journal_entry_lines.debit');
        $credits = (float) (clone $q)->sum('journal_entry_lines.credit');

        return [$debits, $credits];
    }
}
