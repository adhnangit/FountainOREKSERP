<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    public function index(Request $request): JsonResponse
    {
        $q = Holiday::query();
        $this->branchContext->applyScope($q);
        if ($request->from_date) $q->whereDate('date', '>=', $request->from_date);
        if ($request->to_date) $q->whereDate('date', '<=', $request->to_date);
        return response()->json($q->orderBy('date')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'date' => 'required|date',
            'name' => 'required|string|max:255',
            'is_recurring_yearly' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        return response()->json(Holiday::create($data), 201);
    }

    public function update(Request $request, Holiday $holiday): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'date' => 'sometimes|date',
            'name' => 'sometimes|string|max:255',
            'is_recurring_yearly' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $holiday->update($data);
        return response()->json($holiday->fresh());
    }

    public function destroy(Holiday $holiday): JsonResponse
    {
        $holiday->delete();
        return response()->json(['message' => 'Holiday deleted.']);
    }
}
