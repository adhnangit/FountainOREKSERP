<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cheque;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\AccountingService;
use App\Services\BranchContextService;
use App\Services\NumberGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function __construct(
        private BranchContextService $branchContext,
        private NumberGeneratorService $numbers,
        private AccountingService $accounting
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = Expense::query();
        $this->branchContext->applyScope($q);
        if ($request->status) $q->where('status', $request->status);
        if ($request->category_id) $q->where('category_id', $request->category_id);
        if ($request->from_date) $q->whereDate('expense_date', '>=', $request->from_date);
        if ($request->to_date) $q->whereDate('expense_date', '<=', $request->to_date);
        return response()->json($q->with(['category', 'account', 'paymentAccount', 'createdBy', 'branch'])->latest('expense_date')->paginate($request->input('per_page', 20)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id'            => 'required|exists:branches,id',
            'category_id'          => 'nullable|exists:expense_categories,id',
            'account_id'           => 'nullable|exists:accounts,id',
            'payment_account_id'   => 'nullable|exists:accounts,id',
            'amount'               => 'required|numeric|min:0.01',
            'expense_date'         => 'required|date',
            'description'          => 'required|string|max:500',
            'notes'                => 'nullable|string',
            'payment_method'       => 'required|in:cash,cheque,bank_transfer,credit_card',
            'reference_number'     => 'nullable|string|max:100',
            'cheque_number'        => 'nullable|string|max:100',
            'bank_name'            => 'nullable|string|max:100',
            'cheque_date'          => 'nullable|date',
            'cheque_id'            => 'nullable|exists:cheques,id',
            'is_recurring'         => 'nullable|boolean',
            'recurrence_frequency' => 'nullable|string',
            'recurrence_end_date'  => 'nullable|date',
        ]);

        // Strip time/timezone from date fields — MySQL DATE columns reject ISO-8601 strings
        foreach (['expense_date', 'cheque_date', 'recurrence_end_date'] as $df) {
            if (!empty($data[$df])) {
                $data[$df] = \Carbon\Carbon::parse($data[$df])->format('Y-m-d');
            }
        }

        if ($request->hasFile('receipt')) {
            $data['receipt_attachment'] = $request->file('receipt')->store('expenses/receipts', 'public');
        }

        return DB::transaction(function () use ($data, $request) {
            $data['expense_number'] = $this->numbers->expenseNumber($data['branch_id']);
            $data['created_by'] = $request->user()->id;
            $data['status'] = 'approved';
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();

            $expense = Expense::create($data);

            // Mark the party cheque as used so it no longer appears as available
            if (!empty($data['cheque_id'])) {
                Cheque::where('id', $data['cheque_id'])
                    ->where('direction', 'received')
                    ->where('status', 'in_hand')
                    ->update(['status' => 'transferred', 'notes' => 'Transferred for expense: ' . $expense->expense_number]);
            }

            $this->accounting->postExpenseJournal($expense, $request->user()->id);

            return response()->json($expense->load(['category', 'branch']), 201);
        }, 5);
    }

    public function show(Expense $expense): JsonResponse
    {
        return response()->json($expense->load(['category', 'account', 'paymentAccount', 'createdBy', 'approvedBy', 'branch']));
    }

    public function update(Request $request, Expense $expense): JsonResponse
    {
        if (!in_array($expense->status, ['draft', 'pending'])) {
            return response()->json(['message' => 'Cannot edit an approved or rejected expense.'], 422);
        }

        $data = $request->validate([
            'category_id'        => 'nullable|exists:expense_categories,id',
            'account_id'         => 'nullable|exists:accounts,id',
            'payment_account_id' => 'nullable|exists:accounts,id',
            'amount'             => 'sometimes|numeric|min:0.01',
            'expense_date'       => 'sometimes|date',
            'description'        => 'sometimes|string|max:500',
            'notes'              => 'nullable|string',
            'payment_method'     => 'sometimes|in:cash,cheque,bank_transfer,credit_card',
            'reference_number'   => 'nullable|string|max:100',
            'cheque_number'      => 'nullable|string|max:100',
            'bank_name'          => 'nullable|string|max:100',
            'cheque_date'        => 'nullable|date',
            'cheque_id'          => 'nullable|exists:cheques,id',
            'approved_by_name'   => 'nullable|string|max:100',
        ]);

        // Sanitize date fields
        foreach (['expense_date', 'cheque_date'] as $df) {
            if (!empty($data[$df])) {
                $data[$df] = \Carbon\Carbon::parse($data[$df])->format('Y-m-d');
            }
        }

        // If a new party cheque is being assigned, mark it as transferred
        $oldChequeId = $expense->cheque_id;
        $newChequeId = $data['cheque_id'] ?? null;

        if ($newChequeId && $newChequeId != $oldChequeId) {
            Cheque::where('id', $newChequeId)
                ->where('direction', 'received')
                ->where('status', 'in_hand')
                ->update(['status' => 'transferred', 'notes' => 'Transferred for expense: ' . $expense->expense_number]);
        }

        // If the old cheque is being replaced or cleared, restore it to in_hand
        if ($oldChequeId && $oldChequeId != $newChequeId) {
            Cheque::where('id', $oldChequeId)
                ->where('status', 'transferred')
                ->update(['status' => 'in_hand', 'notes' => null]);
        }

        // Re-approve and re-post journal when a pending expense gets a payment account
        $isRepayment = $expense->status === 'pending' && !empty($data['payment_account_id']);
        if ($isRepayment) {
            $data['status']      = 'approved';
            $data['approved_by'] = $request->user()->id;
            $data['approved_at'] = now();
        }

        $expense->update($data);

        if ($isRepayment) {
            $this->accounting->postExpenseJournal($expense->fresh(), $request->user()->id);
        }

        return response()->json($expense->fresh(['category', 'branch', 'account', 'paymentAccount']));
    }

    public function approve(Request $request, Expense $expense): JsonResponse
    {
        if ($expense->status !== 'pending') {
            return response()->json(['message' => 'Expense is not pending approval.'], 422);
        }

        $expense->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        $this->accounting->postExpenseJournal($expense->fresh(), $request->user()->id);

        return response()->json(['message' => 'Expense approved.', 'expense' => $expense->fresh()]);
    }

    public function reject(Request $request, Expense $expense): JsonResponse
    {
        $request->validate(['reason' => 'required|string']);
        $expense->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id,
            'rejection_reason' => $request->reason,
        ]);
        return response()->json(['message' => 'Expense rejected.']);
    }

    public function destroy(Expense $expense): JsonResponse
    {
        return DB::transaction(function () use ($expense) {
            // Restore any party cheque used to pay this expense back to in_hand
            if ($expense->cheque_id) {
                Cheque::where('id', $expense->cheque_id)
                    ->where('status', 'transferred')
                    ->update(['status' => 'in_hand', 'notes' => null]);
            }

            // Remove the journal entry this expense posted, if any, so deleting
            // the expense also undoes its accounting effect.
            \App\Models\JournalEntry::where('reference_type', 'expense')
                ->where('reference_id', $expense->id)
                ->get()
                ->each(function ($je) {
                    $je->lines()->delete();
                    $je->forceDelete();
                });

            $expense->delete();

            return response()->json(['message' => 'Expense deleted.']);
        });
    }

    public function categories(): JsonResponse
    {
        return response()->json(ExpenseCategory::where('is_active', true)->orderBy('name')->get());
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:expense_categories',
            'description' => 'nullable|string',
        ]);
        return response()->json(ExpenseCategory::create($data), 201);
    }
}
