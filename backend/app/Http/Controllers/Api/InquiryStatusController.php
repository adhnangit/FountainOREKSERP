<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InquiryStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InquiryStatusController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(InquiryStatus::orderBy('order_by')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'order_by' => 'nullable|integer',
        ]);
        $data['color'] = $data['color'] ?? '#4f46e5';
        $data['order_by'] = $data['order_by'] ?? 0;

        return response()->json(InquiryStatus::create($data), 201);
    }

    public function update(Request $request, InquiryStatus $inquiryStatus): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'order_by' => 'nullable|integer',
        ]);
        $data['color'] = $data['color'] ?? '#4f46e5';
        $data['order_by'] = $data['order_by'] ?? 0;

        $inquiryStatus->update($data);

        return response()->json($inquiryStatus->fresh());
    }

    public function destroy(InquiryStatus $inquiryStatus): JsonResponse
    {
        $inquiryStatus->delete();

        return response()->json(['message' => 'Status deleted.']);
    }
}
