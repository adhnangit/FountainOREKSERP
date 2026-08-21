<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\CandidateStatusHistory;
use App\Models\Employee;
use App\Models\JobOpening;
use App\Services\NumberGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CandidateController extends Controller
{
    public function __construct(private NumberGeneratorService $numbers) {}

    public function index(Request $request): JsonResponse
    {
        $q = Candidate::with(['jobOpening:id,title']);

        if ($request->job_opening_id) $q->where('job_opening_id', $request->job_opening_id);
        if ($request->status) $q->where('status', $request->status);
        if ($request->search) {
            $q->where(fn($q) => $q
                ->where('first_name', 'like', "%{$request->search}%")
                ->orWhere('last_name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%")
            );
        }

        return response()->json($q->orderByDesc('created_at')->paginate($request->input('per_page', 100)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'job_opening_id' => 'nullable|exists:job_openings,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'cover_letter' => 'nullable|string',
            'source' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'resume' => 'nullable|file|max:5120|extensions:pdf,doc,docx',
        ]);

        if ($request->hasFile('resume')) {
            $file = $request->file('resume');
            $ext = $file->getClientOriginalExtension();
            $data['resume_path'] = $file->storeAs('candidates/resumes', Str::random(40) . ($ext ? '.' . $ext : ''), 'public');
        }
        unset($data['resume']);
        $data['created_by'] = auth()->id();

        return DB::transaction(function () use ($data) {
            $candidate = Candidate::create($data);
            CandidateStatusHistory::create([
                'candidate_id' => $candidate->id,
                'old_status' => null,
                'new_status' => 'applied',
                'changed_by' => auth()->id(),
                'notes' => 'Candidate added to pipeline.',
            ]);
            return response()->json($candidate->load('jobOpening:id,title'), 201);
        }, 5);
    }

    public function show(Candidate $candidate): JsonResponse
    {
        $candidate->load([
            'jobOpening', 'employee:id,employee_code,first_name,last_name',
            'interviews' => fn($q) => $q->with('interviewer:id,name')->orderByDesc('scheduled_at'),
            'statusHistory' => fn($q) => $q->with('changedBy:id,name')->orderByDesc('created_at'),
        ]);
        return response()->json($candidate);
    }

    public function update(Request $request, Candidate $candidate): JsonResponse
    {
        $data = $request->validate([
            'job_opening_id' => 'nullable|exists:job_openings,id',
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'cover_letter' => 'nullable|string',
            'source' => 'nullable|string|max:100',
            'status' => 'sometimes|in:applied,screening,interview,offer,hired,rejected,withdrawn',
            'rating' => 'nullable|integer|min:1|max:5',
            'notes' => 'nullable|string',
            'offered_salary' => 'nullable|numeric|min:0',
            'offer_date' => 'nullable|date',
        ]);

        if (isset($data['status']) && $data['status'] === 'hired') {
            return response()->json(['message' => 'Use the Hire action to move a candidate to hired — it creates their employee record.'], 422);
        }

        DB::transaction(function () use ($data, $candidate) {
            $oldStatus = $candidate->status;
            $candidate->update($data);

            if (isset($data['status']) && $data['status'] !== $oldStatus) {
                CandidateStatusHistory::create([
                    'candidate_id' => $candidate->id,
                    'old_status' => $oldStatus,
                    'new_status' => $data['status'],
                    'changed_by' => auth()->id(),
                ]);
            }
        }, 5);

        return response()->json($candidate->fresh(['jobOpening']));
    }

    public function destroy(Candidate $candidate): JsonResponse
    {
        if ($candidate->employee_id) {
            return response()->json(['message' => 'Cannot delete a candidate who has already been hired.'], 422);
        }

        $candidate->delete();
        return response()->json(['message' => 'Candidate deleted.']);
    }

    /**
     * Converts a candidate into a real employee record — the bridge into Module 1.
     * The candidate's own record is kept (status -> hired, employee_id set) rather than
     * removed, so recruitment history stays traceable back from the employee.
     */
    public function hire(Request $request, Candidate $candidate): JsonResponse
    {
        if ($candidate->status === 'hired' && $candidate->employee_id) {
            return response()->json(['message' => 'This candidate has already been hired.'], 422);
        }

        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',
            'join_date' => 'required|date',
            'basic_salary' => 'nullable|numeric|min:0',
            'employment_type' => 'nullable|in:full_time,part_time,contract,intern',
        ]);

        $jobOpening = $candidate->jobOpening;

        return DB::transaction(function () use ($data, $candidate, $jobOpening) {
            $employee = Employee::create([
                'employee_code' => $this->numbers->employeeCode(),
                'branch_id' => $data['branch_id'] ?? $jobOpening?->branch_id,
                'department_id' => $data['department_id'] ?? $jobOpening?->department_id,
                'designation_id' => $data['designation_id'] ?? $jobOpening?->designation_id,
                'first_name' => $candidate->first_name,
                'last_name' => $candidate->last_name,
                'personal_email' => $candidate->email,
                'phone' => $candidate->phone,
                'employment_type' => $data['employment_type'] ?? $jobOpening?->employment_type ?? 'full_time',
                'join_date' => $data['join_date'],
                'basic_salary' => $data['basic_salary'] ?? $candidate->offered_salary,
                'employment_status' => 'active',
                'created_by' => auth()->id(),
                'notes' => 'Hired via recruitment pipeline' . ($jobOpening ? " — {$jobOpening->title}" : '') . '.',
            ]);

            $oldStatus = $candidate->status;
            $candidate->update(['status' => 'hired', 'employee_id' => $employee->id]);

            CandidateStatusHistory::create([
                'candidate_id' => $candidate->id,
                'old_status' => $oldStatus,
                'new_status' => 'hired',
                'changed_by' => auth()->id(),
                'notes' => "Hired as employee {$employee->employee_code}.",
            ]);

            // Auto-close the opening once every seat is filled.
            if ($jobOpening) {
                $hiredCount = Candidate::where('job_opening_id', $jobOpening->id)->where('status', 'hired')->count();
                if ($hiredCount >= $jobOpening->openings_count && $jobOpening->status !== 'filled') {
                    $jobOpening->update(['status' => 'filled']);
                }
            }

            return response()->json(['candidate' => $candidate->fresh(), 'employee' => $employee], 201);
        }, 5);
    }

    public function resume(Candidate $candidate)
    {
        $path = $candidate->resume_path;
        abort_unless($path && Storage::disk('public')->exists($path), 404);
        $mimes = ['pdf' => 'application/pdf', 'doc' => 'application/msword', 'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return response(Storage::disk('public')->get($path), 200, [
            'Content-Type' => $mimes[$ext] ?? 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($candidate->full_name) . ' - Resume.' . $ext . '"',
            'Cache-Control' => 'private, max-age=86400',
        ]);
    }
}
