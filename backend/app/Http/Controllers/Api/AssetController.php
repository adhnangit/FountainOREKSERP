<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Services\BranchContextService;
use App\Services\NumberGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function __construct(
        private BranchContextService $branchContext,
        private NumberGeneratorService $numbers
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = Asset::with(['branch', 'currentAssignment.employee:id,first_name,last_name,employee_code']);
        $this->branchContext->applyScope($q);
        if ($request->status) $q->where('status', $request->status);
        if ($request->search) {
            $q->where(fn($q) => $q->where('name', 'like', "%{$request->search}%")->orWhere('asset_code', 'like', "%{$request->search}%"));
        }

        return response()->json($q->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'branch_id' => 'nullable|exists:branches,id',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'serial_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);
        $data['asset_code'] = $this->numbers->assetCode();

        return response()->json(Asset::create($data)->load('branch'), 201);
    }

    public function show(Asset $asset): JsonResponse
    {
        $asset->load(['branch', 'assignments' => fn($q) => $q->with('employee:id,first_name,last_name,employee_code')->orderByDesc('assigned_date')]);
        return response()->json($asset);
    }

    public function update(Request $request, Asset $asset): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'nullable|string|max:100',
            'branch_id' => 'nullable|exists:branches,id',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'serial_number' => 'nullable|string|max:100',
            'status' => 'sometimes|in:available,assigned,under_repair,retired',
            'notes' => 'nullable|string',
        ]);

        $asset->update($data);
        return response()->json($asset->fresh('branch'));
    }

    public function destroy(Asset $asset): JsonResponse
    {
        if ($asset->assignments()->whereNull('returned_date')->exists()) {
            return response()->json(['message' => 'Cannot delete an asset that is currently assigned. Return it first.'], 422);
        }

        $asset->delete();
        return response()->json(['message' => 'Asset deleted.']);
    }

    public function assign(Request $request, Asset $asset): JsonResponse
    {
        if ($asset->assignments()->whereNull('returned_date')->exists()) {
            return response()->json(['message' => 'This asset is already assigned. Return it before reassigning.'], 422);
        }

        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'assigned_date' => 'required|date',
            'condition_on_assign' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);
        $data['asset_id'] = $asset->id;
        $data['assigned_by'] = auth()->id();

        DB::transaction(function () use ($data, $asset) {
            AssetAssignment::create($data);
            $asset->update(['status' => 'assigned']);
        }, 5);

        return response()->json($asset->fresh(['branch', 'currentAssignment.employee:id,first_name,last_name,employee_code']));
    }

    public function returnAsset(Request $request, AssetAssignment $assetAssignment): JsonResponse
    {
        if ($assetAssignment->returned_date) {
            return response()->json(['message' => 'This assignment has already been returned.'], 422);
        }

        $data = $request->validate([
            'returned_date' => 'required|date',
            'condition_on_return' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($data, $assetAssignment) {
            $assetAssignment->update($data);
            $assetAssignment->asset->update(['status' => 'available']);
        }, 5);

        return response()->json($assetAssignment->fresh('asset'));
    }
}
