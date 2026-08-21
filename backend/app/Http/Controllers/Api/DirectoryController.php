<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OfficeDirectory;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DirectoryController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    public function index(Request $request): JsonResponse
    {
        $q = OfficeDirectory::query();
        $this->branchContext->applyScope($q);
        if ($request->contact_type) $q->where('contact_type', $request->contact_type);
        if ($request->category) $q->where('category', $request->category);
        if ($request->department) $q->where('department', 'like', "%{$request->department}%");
        if ($request->search) {
            $q->where(fn($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
                ->orWhere('designation', 'like', "%{$request->search}%")
                ->orWhere('company', 'like', "%{$request->search}%")
            );
        }
        return response()->json($q->with('branch')->orderBy('name')->paginate($request->input('per_page', 20)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'contact_type' => 'required|in:internal,external',
            'name' => 'required|string|max:255',
            'designation' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'company' => 'nullable|string|max:255',
            'category' => 'required|in:employee,vendor,service_provider,hospital,clinic,courier,other',
            'phone' => 'nullable|string|max:20',
            'phone2' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'extension' => 'nullable|string|max:10',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $entry = OfficeDirectory::create($data);
        return response()->json($entry->load('branch'), 201);
    }

    public function show(OfficeDirectory $officeDirectory): JsonResponse
    {
        return response()->json($officeDirectory->load('branch'));
    }

    public function update(Request $request, OfficeDirectory $officeDirectory): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'contact_type' => 'sometimes|in:internal,external',
            'name' => 'sometimes|string|max:255',
            'designation' => 'nullable|string',
            'department' => 'nullable|string',
            'company' => 'nullable|string|max:255',
            'category' => 'sometimes|in:employee,vendor,service_provider,hospital,clinic,courier,other',
            'phone' => 'nullable|string',
            'phone2' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'extension' => 'nullable|string',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $officeDirectory->update($data);
        return response()->json($officeDirectory->fresh('branch'));
    }

    public function destroy(OfficeDirectory $officeDirectory): JsonResponse
    {
        $officeDirectory->delete();
        return response()->json(['message' => 'Contact deleted.']);
    }
}
