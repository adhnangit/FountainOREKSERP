<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InquirySubject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InquirySubjectController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(InquirySubject::orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|min:2|max:255']);

        return response()->json(InquirySubject::create($data), 201);
    }

    public function update(Request $request, InquirySubject $inquirySubject): JsonResponse
    {
        $data = $request->validate(['name' => 'required|string|min:2|max:255']);
        $inquirySubject->update($data);

        return response()->json($inquirySubject->fresh());
    }

    public function destroy(InquirySubject $inquirySubject): JsonResponse
    {
        $inquirySubject->delete();

        return response()->json(['message' => 'Category deleted.']);
    }
}
