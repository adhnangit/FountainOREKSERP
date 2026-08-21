<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DistrictController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = District::with(['cities' => function ($q) use ($request) {
            if ($request->boolean('active_only', true)) $q->where('is_active', true);
            $q->orderBy('name');
        }]);
        if ($request->boolean('active_only', true)) {
            $q->where('is_active', true);
        }
        return response()->json($q->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:districts,name',
            'is_active' => 'sometimes|boolean',
        ]);

        return response()->json(District::create($data)->load('cities'), 201);
    }

    public function update(Request $request, District $district): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255|unique:districts,name,' . $district->id,
            'is_active' => 'sometimes|boolean',
        ]);

        $district->update($data);
        return response()->json($district->fresh('cities'));
    }

    public function destroy(District $district): JsonResponse
    {
        if ($district->cities()->exists()) {
            return response()->json(['message' => 'Cannot delete a district that still has cities. Delete its cities first.'], 422);
        }

        $district->delete();
        return response()->json(['message' => 'District deleted.']);
    }
}
