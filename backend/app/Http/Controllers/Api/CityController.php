<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = City::with('district');
        if ($request->district_id) $q->where('district_id', $request->district_id);
        if ($request->boolean('active_only', true)) $q->where('is_active', true);
        return response()->json($q->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'district_id' => 'required|exists:districts,id',
            'name' => 'required|string|max:255|unique:cities,name,NULL,id,district_id,' . $request->district_id,
            'is_active' => 'sometimes|boolean',
        ]);

        return response()->json(City::create($data)->load('district'), 201);
    }

    public function update(Request $request, City $city): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255|unique:cities,name,' . $city->id . ',id,district_id,' . $city->district_id,
            'is_active' => 'sometimes|boolean',
        ]);

        $city->update($data);
        return response()->json($city->fresh('district'));
    }

    public function destroy(City $city): JsonResponse
    {
        $city->delete();
        return response()->json(['message' => 'City deleted.']);
    }
}
