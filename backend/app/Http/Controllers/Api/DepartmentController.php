<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    public function index(Request $request): JsonResponse
    {
        $q = Department::with('children')->whereNull('parent_id')
            ->withCount('employees');
        $this->branchContext->applyScope($q);
        return response()->json($q->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:departments',
            'parent_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string',
        ]);

        return response()->json(Department::create($data), 201);
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:20|unique:departments,code,' . $department->id,
            'parent_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $department->update($data);
        return response()->json($department->fresh());
    }

    public function destroy(Department $department): JsonResponse
    {
        if ($department->employees()->exists()) {
            return response()->json(['message' => 'Cannot delete a department that still has employees assigned to it.'], 422);
        }
        if ($department->children()->exists()) {
            return response()->json(['message' => 'Cannot delete a department that has sub-departments.'], 422);
        }
        if ($department->designations()->exists()) {
            return response()->json(['message' => 'Cannot delete a department that has designations under it.'], 422);
        }

        $department->delete();
        return response()->json(['message' => 'Department deleted.']);
    }
}
