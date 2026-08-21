<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PerformanceGoal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceGoalController extends Controller
{
    public function index(Employee $employee): JsonResponse
    {
        return response()->json($employee->performanceGoals()->orderByDesc('created_at')->get());
    }

    public function store(Request $request, Employee $employee): JsonResponse
    {
        $data = $request->validate([
            'review_id' => 'nullable|exists:performance_reviews,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date',
        ]);
        $data['employee_id'] = $employee->id;
        $data['created_by'] = auth()->id();

        return response()->json(PerformanceGoal::create($data), 201);
    }

    public function update(Request $request, PerformanceGoal $performanceGoal): JsonResponse
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'target_date' => 'nullable|date',
            'status' => 'sometimes|in:not_started,in_progress,completed,cancelled',
            'progress_percent' => 'nullable|integer|min:0|max:100',
        ]);

        $performanceGoal->update($data);
        return response()->json($performanceGoal->fresh());
    }

    public function destroy(PerformanceGoal $performanceGoal): JsonResponse
    {
        $performanceGoal->delete();
        return response()->json(['message' => 'Goal deleted.']);
    }
}
