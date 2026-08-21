<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobOpening;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobOpeningController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    public function index(Request $request): JsonResponse
    {
        $q = JobOpening::with(['branch', 'department', 'designation'])->withCount('candidates');
        $this->branchContext->applyScope($q);
        if ($request->status) $q->where('status', $request->status);

        return response()->json($q->orderByDesc('created_at')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'employment_type' => 'nullable|in:full_time,part_time,contract,intern',
            'openings_count' => 'nullable|integer|min:1',
            'posted_date' => 'nullable|date',
            'closing_date' => 'nullable|date|after_or_equal:posted_date',
        ]);
        $data['created_by'] = auth()->id();

        return response()->json(JobOpening::create($data)->load(['branch', 'department', 'designation']), 201);
    }

    public function show(JobOpening $jobOpening): JsonResponse
    {
        $jobOpening->load(['branch', 'department', 'designation', 'candidates' => fn($q) => $q->orderByDesc('created_at')]);
        return response()->json($jobOpening);
    }

    public function update(Request $request, JobOpening $jobOpening): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'requirements' => 'nullable|string',
            'employment_type' => 'nullable|in:full_time,part_time,contract,intern',
            'openings_count' => 'nullable|integer|min:1',
            'status' => 'sometimes|in:open,on_hold,closed,filled',
            'posted_date' => 'nullable|date',
            'closing_date' => 'nullable|date|after_or_equal:posted_date',
        ]);

        $jobOpening->update($data);
        return response()->json($jobOpening->fresh(['branch', 'department', 'designation']));
    }

    public function destroy(JobOpening $jobOpening): JsonResponse
    {
        if ($jobOpening->candidates()->exists()) {
            return response()->json(['message' => 'Cannot delete a job opening that already has candidates. Close it instead.'], 422);
        }

        $jobOpening->delete();
        return response()->json(['message' => 'Job opening deleted.']);
    }
}
