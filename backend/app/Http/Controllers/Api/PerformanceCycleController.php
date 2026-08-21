<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PerformanceCycle;
use App\Models\PerformanceReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerformanceCycleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(PerformanceCycle::withCount('reviews')->orderByDesc('start_date')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);
        $data['created_by'] = auth()->id();

        return response()->json(PerformanceCycle::create($data), 201);
    }

    public function update(Request $request, PerformanceCycle $performanceCycle): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'status' => 'sometimes|in:draft,active,closed',
        ]);

        $performanceCycle->update($data);
        return response()->json($performanceCycle->fresh());
    }

    public function destroy(PerformanceCycle $performanceCycle): JsonResponse
    {
        if ($performanceCycle->reviews()->exists()) {
            return response()->json(['message' => 'Cannot delete a cycle that already has reviews. Close it instead.'], 422);
        }

        $performanceCycle->delete();
        return response()->json(['message' => 'Performance cycle deleted.']);
    }

    /**
     * Creates a pending review for every active employee who doesn't already have one in
     * this cycle. The reviewer defaults to the employee's reporting manager's linked user
     * account — "manager" here is data-driven (employees.reporting_manager_id), matching
     * the rest of this HR module; an employee with no manager or an unlinked manager is
     * still given a review with reviewer_id left null so HR can assign one manually.
     */
    public function generateReviews(PerformanceCycle $performanceCycle): JsonResponse
    {
        $employees = Employee::where('is_active', true)->with('reportingManager:id,user_id')->get(['id', 'reporting_manager_id']);
        $existing = PerformanceReview::where('cycle_id', $performanceCycle->id)->pluck('employee_id')->all();

        $created = DB::transaction(function () use ($employees, $existing, $performanceCycle) {
            $n = 0;
            foreach ($employees as $employee) {
                if (in_array($employee->id, $existing, true)) continue;
                PerformanceReview::create([
                    'cycle_id' => $performanceCycle->id,
                    'employee_id' => $employee->id,
                    'reviewer_id' => $employee->reportingManager?->user_id,
                    'status' => 'pending',
                ]);
                $n++;
            }
            return $n;
        }, 5);

        return response()->json(['created' => $created]);
    }
}
