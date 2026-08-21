<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\BranchContextService;
use App\Services\NumberGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct(
        private NumberGeneratorService $numbers,
        private BranchContextService $branchContext
    ) {}

    public function index(Request $request): JsonResponse
    {
        $q = Service::query();
        $this->branchContext->applyScope($q);
        if ($request->search) {
            $q->where(fn($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%")
            );
        }
        if ($request->category_id) $q->where('category_id', $request->category_id);
        if ($request->is_active !== null) $q->where('is_active', $request->boolean('is_active'));

        $services = $q->with('category')
            ->orderBy('name')
            ->paginate($request->input('per_page', 200));

        return response()->json($services);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:service_categories,id',
            'unit' => 'nullable|string|max:50',
            'rate' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data['code'] = $this->numbers->serviceCode();
        $service = Service::create($data);

        return response()->json($service->load('category'), 201);
    }

    public function show(Service $service): JsonResponse
    {
        return response()->json($service->load('category'));
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category_id' => 'nullable|exists:service_categories,id',
            'unit' => 'nullable|string|max:50',
            'rate' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $service->update($data);
        return response()->json($service->fresh('category'));
    }

    public function destroy(Service $service): JsonResponse
    {
        if ($service->invoiceItems()->exists()) {
            return response()->json(['message' => 'Cannot delete a service that has already been billed on an invoice. Mark it inactive instead.'], 422);
        }
        $service->delete();
        return response()->json(['message' => 'Service deleted.']);
    }

    // Service Categories
    public function categories(Request $request): JsonResponse
    {
        $q = ServiceCategory::with('children')->whereNull('parent_id');
        $this->branchContext->applyScope($q);
        return response()->json($q->get());
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:service_categories',
            'parent_id' => 'nullable|exists:service_categories,id',
            'description' => 'nullable|string',
        ]);

        return response()->json(ServiceCategory::create($data), 201);
    }

    public function updateCategory(Request $request, ServiceCategory $category): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:20|unique:service_categories,code,' . $category->id,
            'parent_id' => 'nullable|exists:service_categories,id',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $category->update($data);
        return response()->json($category->fresh());
    }

    public function destroyCategory(ServiceCategory $category): JsonResponse
    {
        if ($category->services()->exists()) {
            return response()->json(['message' => 'Cannot delete a category that still has services assigned to it.'], 422);
        }
        if ($category->children()->exists()) {
            return response()->json(['message' => 'Cannot delete a category that has sub-categories.'], 422);
        }

        $category->delete();
        return response()->json(['message' => 'Category deleted.']);
    }
}
