<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Designation;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DesignationController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    public function index(Request $request): JsonResponse
    {
        $q = Designation::with('department')->withCount('employees');
        if ($request->department_id) $q->where('department_id', $request->department_id);

        // Designations don't carry their own branch_id — they inherit it from their
        // department. A designation with no department isn't tied to any branch, so
        // it stays visible everywhere rather than disappearing under branch scoping.
        if (!$this->branchContext->isAllBranches()) {
            $branchId = $this->branchContext->getBranchId() ?? -1;
            $q->where(function ($qq) use ($branchId) {
                $qq->whereNull('department_id')
                   ->orWhereHas('department', fn($dq) => $dq->where('branch_id', $branchId));
            });
        }

        return response()->json($q->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:designations',
            'description' => 'nullable|string',
        ]);

        return response()->json(Designation::create($data), 201);
    }

    public function update(Request $request, Designation $designation): JsonResponse
    {
        $data = $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:20|unique:designations,code,' . $designation->id,
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $designation->update($data);
        return response()->json($designation->fresh());
    }

    public function destroy(Designation $designation): JsonResponse
    {
        if ($designation->employees()->exists()) {
            return response()->json(['message' => 'Cannot delete a designation that still has employees assigned to it.'], 422);
        }

        $designation->delete();
        return response()->json(['message' => 'Designation deleted.']);
    }
}
