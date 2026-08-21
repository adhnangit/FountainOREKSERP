<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\CandidateInterview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CandidateInterviewController extends Controller
{
    public function store(Request $request, Candidate $candidate): JsonResponse
    {
        $data = $request->validate([
            'scheduled_at' => 'required|date',
            'mode' => 'nullable|in:in_person,phone,video',
            'interviewer_id' => 'nullable|exists:users,id',
            'location_or_link' => 'nullable|string|max:255',
        ]);
        $data['candidate_id'] = $candidate->id;
        $data['created_by'] = auth()->id();

        $interview = CandidateInterview::create($data);

        if ($candidate->status === 'applied' || $candidate->status === 'screening') {
            $candidate->update(['status' => 'interview']);
        }

        return response()->json($interview->load('interviewer:id,name'), 201);
    }

    public function update(Request $request, CandidateInterview $candidateInterview): JsonResponse
    {
        $data = $request->validate([
            'scheduled_at' => 'sometimes|date',
            'mode' => 'nullable|in:in_person,phone,video',
            'interviewer_id' => 'nullable|exists:users,id',
            'location_or_link' => 'nullable|string|max:255',
            'status' => 'sometimes|in:scheduled,completed,cancelled,no_show',
            'feedback' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5',
        ]);

        $candidateInterview->update($data);
        return response()->json($candidateInterview->fresh(['interviewer:id,name']));
    }

    public function destroy(CandidateInterview $candidateInterview): JsonResponse
    {
        $candidateInterview->delete();
        return response()->json(['message' => 'Interview removed.']);
    }
}
