<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\InquiryFollowup;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InquiryController extends Controller
{
    /**
     * Minimal active-user list for the "Assign To" picker.
     */
    public function assignableUsers(): JsonResponse
    {
        return response()->json(
            User::where('is_active', true)->orderBy('name')->get(['id', 'name'])
        );
    }

    public function index(Request $request): JsonResponse
    {
        $filtered = Inquiry::query()
            ->when($request->search, fn ($q) => $q->where(function ($w) use ($request) {
                $w->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('subject', 'like', '%'.$request->search.'%')
                    ->orWhere('phone', 'like', '%'.$request->search.'%');
            }))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->subject, fn ($q) => $q->where('subject', $request->subject));

        // Stats reflect the full filtered set, not just the current page —
        // summing/counting a paginated slice would silently understate these
        // the moment there's more than one page of results.
        $stats = [
            'total' => (clone $filtered)->count(),
            'won' => (clone $filtered)->where('status', 'Won')->count(),
            'pipeline_value' => (clone $filtered)->sum('potential_value'),
        ];

        $inquiries = $filtered->with('assignee')
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 10));

        return response()->json([
            'inquiries' => $inquiries,
            'stats' => $stats,
        ]);
    }

    public function show(Inquiry $inquiry): JsonResponse
    {
        return response()->json($inquiry->load(['assignee', 'followups.user']));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'required|string|max:255',
            'source' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'status' => 'required|string|max:100',
            'potential_value' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $data['potential_value'] = $data['potential_value'] ?? 0;

        $inquiry = Inquiry::create($data);

        return response()->json($inquiry->load('assignee'), 201);
    }

    public function update(Request $request, Inquiry $inquiry): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'subject' => 'sometimes|string|max:255',
            'source' => 'nullable|string|max:255',
            'message' => 'nullable|string',
            'internal_notes' => 'nullable|string',
            'status' => 'sometimes|string|max:100',
            'potential_value' => 'nullable|numeric|min:0',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $inquiry->update($data);

        return response()->json($inquiry->fresh('assignee'));
    }

    public function destroy(Inquiry $inquiry): JsonResponse
    {
        $inquiry->delete();

        return response()->json(['message' => 'Inquiry deleted.']);
    }

    public function addFollowup(Request $request, Inquiry $inquiry): JsonResponse
    {
        $data = $request->validate([
            'followup_date' => 'required|date',
            'notes' => 'required|string|min:5',
            'outcome' => 'nullable|string|max:255',
        ]);

        $followup = InquiryFollowup::create([
            'inquiry_id' => $inquiry->id,
            'followup_date' => $data['followup_date'],
            'notes' => $data['notes'],
            'outcome' => $data['outcome'] ?? null,
            'user_id' => $request->user()->id,
        ]);

        return response()->json($followup->load('user'), 201);
    }

    public function deleteFollowup(Inquiry $inquiry, InquiryFollowup $followup): JsonResponse
    {
        abort_if($followup->inquiry_id !== $inquiry->id, 404);

        $followup->delete();

        return response()->json(['message' => 'Follow-up removed.']);
    }
}
