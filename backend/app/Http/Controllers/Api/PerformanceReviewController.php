<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PerformanceReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PerformanceReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = PerformanceReview::with(['employee:id,first_name,last_name,employee_code,department_id', 'employee.department:id,name', 'reviewer:id,name', 'cycle:id,name,status']);

        if ($request->cycle_id) $q->where('cycle_id', $request->cycle_id);
        if ($request->employee_id) $q->where('employee_id', $request->employee_id);
        if ($request->reviewer_id) $q->where('reviewer_id', $request->reviewer_id);
        if ($request->status) $q->where('status', $request->status);

        return response()->json($q->orderByDesc('created_at')->get());
    }

    public function show(PerformanceReview $performanceReview): JsonResponse
    {
        $performanceReview->load(['employee', 'reviewer:id,name', 'cycle', 'goals']);
        return response()->json($performanceReview);
    }

    public function update(Request $request, PerformanceReview $performanceReview): JsonResponse
    {
        $data = $request->validate([
            'reviewer_id' => 'nullable|exists:users,id',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'overall_rating' => 'nullable|integer|min:1|max:5',
            'employee_comments' => 'nullable|string',
            'reviewer_comments' => 'nullable|string',
        ]);

        if (($data['status'] ?? null) === 'completed' && !$performanceReview->completed_at) {
            $data['completed_at'] = now();
        }

        $performanceReview->update($data);
        return response()->json($performanceReview->fresh(['employee', 'reviewer:id,name', 'cycle']));
    }
}
