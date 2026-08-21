<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Bank::query();
        if ($request->boolean('active_only', true)) {
            $q->where('is_active', true);
        }
        return response()->json($q->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255|unique:banks,name',
            'short_name' => 'nullable|string|max:20',
            'swift_code' => 'nullable|string|max:20',
            'is_active'  => 'sometimes|boolean',
        ]);

        return response()->json(Bank::create($data), 201);
    }

    public function update(Request $request, Bank $bank): JsonResponse
    {
        $data = $request->validate([
            'name'       => 'sometimes|string|max:255|unique:banks,name,' . $bank->id,
            'short_name' => 'nullable|string|max:20',
            'swift_code' => 'nullable|string|max:20',
            'is_active'  => 'sometimes|boolean',
        ]);

        $bank->update($data);
        return response()->json($bank->fresh());
    }

    public function destroy(Bank $bank): JsonResponse
    {
        $bank->delete();
        return response()->json(['message' => 'Bank deleted.']);
    }
}
