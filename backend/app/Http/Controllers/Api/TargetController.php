<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Target;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    public function index(Request $request): JsonResponse
    {
        $q = Target::query();
        $this->branchContext->applyScope($q);
        if ($request->user_id) $q->where('user_id', $request->user_id);
        if ($request->year) $q->where('year', $request->year);
        if ($request->period) $q->where('period', $request->period);
        if ($request->period_number) $q->where('period_number', $request->period_number);
        return response()->json($q->with(['user', 'branch'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'user_id' => 'nullable|exists:users,id',
            'type' => 'required|in:revenue,quantity,new_customers',
            'period' => 'required|in:monthly,quarterly,annual',
            'year' => 'required|integer|min:2020|max:2030',
            'period_number' => 'required|integer|min:1|max:12',
            'target_value' => 'required|numeric|min:0',
            'alert_threshold_percent' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $data['created_by'] = $request->user()->id;
        $target = Target::create($data);

        // Calculate current achievement
        $this->recalculate($target);

        return response()->json($target->fresh(['user', 'branch']), 201);
    }

    public function update(Request $request, Target $target): JsonResponse
    {
        $data = $request->validate([
            'target_value' => 'sometimes|numeric|min:0',
            'alert_threshold_percent' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $target->update($data);
        return response()->json($target->fresh(['user', 'branch']));
    }

    public function destroy(Target $target): JsonResponse
    {
        $target->delete();
        return response()->json(['message' => 'Target deleted.']);
    }

    public function progress(Request $request): JsonResponse
    {
        $branchId = $this->branchContext->getBranchId();
        $year = $request->input('year', now()->year);
        $period = $request->input('period', 'monthly');
        $periodNumber = $request->input('period_number', now()->month);

        $targets = Target::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('year', $year)
            ->where('period', $period)
            ->where('period_number', $periodNumber)
            ->where('is_active', true)
            ->with(['user', 'branch'])
            ->get();

        foreach ($targets as $target) {
            $this->recalculate($target);
        }

        return response()->json($targets->fresh(['user', 'branch']));
    }

    public function branchSummary(Request $request): JsonResponse
    {
        $year  = (int) $request->input('year',  now()->year);
        $month = (int) $request->input('month', now()->month);

        $monthly = Target::with(['branch', 'user'])
            ->where('year', $year)->where('period', 'monthly')
            ->where('period_number', $month)->where('is_active', true);
        $this->branchContext->applyScope($monthly);
        $monthly = $monthly->get();

        $annual = Target::with(['branch', 'user'])
            ->where('year', $year)->where('period', 'annual')
            ->where('is_active', true);
        $this->branchContext->applyScope($annual);
        $annual = $annual->get();

        foreach ($monthly as $t) $this->recalculate($t);
        foreach ($annual  as $t) $this->recalculate($t);

        $fmt = fn($t) => [
            'id'             => $t->id,
            'branch_id'      => (int) $t->branch_id,
            'branch_name'    => $t->branch?->name ?? '—',
            'user_id'        => $t->user_id ? (int) $t->user_id : null,
            'user_name'      => $t->user?->name,
            'type'           => $t->type,
            'target_value'   => (float) $t->target_value,
            'achieved_value' => (float) $t->achieved_value,
            'pct'            => $t->achievement_percent,
            'notes'          => $t->notes,
        ];

        return response()->json([
            'year'    => $year,
            'month'   => $month,
            'monthly' => $monthly->fresh(['branch', 'user'])->map($fmt)->values(),
            'annual'  => $annual->fresh(['branch',  'user'])->map($fmt)->values(),
        ]);
    }

    private function recalculate(Target $target): void
    {
        [$start, $end] = $this->getPeriodDates($target->year, $target->period, $target->period_number);

        $repScope = function ($q) use ($target) {
            if (!$target->user_id) return;
            $uid = $target->user_id;
            // Match invoices assigned to this rep (sales_rep_id takes priority, fallback to created_by)
            $q->where(function ($q) use ($uid) {
                $q->where('sales_rep_id', $uid)
                  ->orWhere(function ($q) use ($uid) {
                      $q->whereNull('sales_rep_id')->where('created_by', $uid);
                  });
            });
        };

        $achieved = match ($target->type) {
            'revenue' => Invoice::where('branch_id', $target->branch_id)
                ->where('type', 'invoice') // exclude credit notes — they share this table but aren't sales
                ->when($target->user_id, $repScope)
                ->whereIn('status', ['confirmed', 'partially_paid', 'paid'])
                ->whereBetween('invoice_date', [$start, $end])
                ->sum('total'),
            'quantity' => Invoice::where('branch_id', $target->branch_id)
                ->where('type', 'invoice') // exclude credit notes — they share this table but aren't sales
                ->when($target->user_id, $repScope)
                ->whereIn('status', ['confirmed', 'partially_paid', 'paid'])
                ->whereBetween('invoice_date', [$start, $end])
                ->join('invoice_items', 'invoices.id', '=', 'invoice_items.invoice_id')
                ->sum('invoice_items.quantity'),
            'new_customers' => \App\Models\Customer::where('branch_id', $target->branch_id)
                ->when($target->user_id, fn($q) => $q->where('assigned_user_id', $target->user_id))
                ->whereBetween('created_at', [$start, $end])
                ->count(),
            default => 0,
        };

        $target->update(['achieved_value' => $achieved]);
    }

    private function getPeriodDates(int $year, string $period, int $periodNumber): array
    {
        return match ($period) {
            'monthly' => [
                "{$year}-" . str_pad($periodNumber, 2, '0', STR_PAD_LEFT) . "-01",
                date('Y-m-t', mktime(0, 0, 0, $periodNumber, 1, $year)),
            ],
            'quarterly' => [
                "{$year}-" . str_pad(($periodNumber - 1) * 3 + 1, 2, '0', STR_PAD_LEFT) . "-01",
                date('Y-m-t', mktime(0, 0, 0, $periodNumber * 3, 1, $year)),
            ],
            'annual' => ["{$year}-01-01", "{$year}-12-31"],
            default => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
        };
    }
}
