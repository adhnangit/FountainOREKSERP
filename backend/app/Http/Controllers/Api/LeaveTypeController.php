<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeaveType;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveTypeController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    public function index(Request $request): JsonResponse
    {
        $q = LeaveType::query();
        $this->branchContext->applyScope($q);
        if ($request->active_only) $q->where('is_active', true);
        return response()->json($q->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:leave_types',
            'max_days_per_year' => 'nullable|numeric|min:0',
            'is_paid' => 'nullable|boolean',
            'requires_approval' => 'nullable|boolean',
        ]);

        return response()->json(LeaveType::create($data), 201);
    }

    public function update(Request $request, LeaveType $leaveType): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:20|unique:leave_types,code,' . $leaveType->id,
            'max_days_per_year' => 'nullable|numeric|min:0',
            'is_paid' => 'nullable|boolean',
            'requires_approval' => 'nullable|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $leaveType->update($data);
        return response()->json($leaveType->fresh());
    }

    public function destroy(LeaveType $leaveType): JsonResponse
    {
        if ($leaveType->requests()->exists()) {
            return response()->json(['message' => 'Cannot delete a leave type that already has leave requests recorded against it. Deactivate it instead.'], 422);
        }

        $leaveType->delete();
        return response()->json(['message' => 'Leave type deleted.']);
    }
}
