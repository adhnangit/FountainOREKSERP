<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveRequestController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    public function index(Request $request): JsonResponse
    {
        $q = LeaveRequest::with(['employee:id,first_name,last_name,employee_code,branch_id,department_id', 'leaveType:id,name,code', 'approvedBy:id,name'])
            ->whereHas('employee', function ($eq) {
                $this->branchContext->applyScope($eq);
            });

        if ($request->employee_id) $q->where('employee_id', $request->employee_id);
        if ($request->status) $q->where('status', $request->status);
        if ($request->from_date) $q->whereDate('end_date', '>=', $request->from_date);
        if ($request->to_date) $q->whereDate('start_date', '<=', $request->to_date);

        return response()->json($q->orderByDesc('start_date')->paginate($request->input('per_page', 100)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_half_day' => 'nullable|boolean',
            'half_day_period' => 'nullable|in:first_half,second_half',
            'reason' => 'nullable|string',
        ]);

        $isHalfDay = $data['is_half_day'] ?? false;
        if ($isHalfDay && $data['start_date'] !== $data['end_date']) {
            throw ValidationException::withMessages(['is_half_day' => 'A half-day request must have the same start and end date.']);
        }

        $totalDays = $isHalfDay
            ? 0.5
            : \Carbon\Carbon::parse($data['start_date'])->diffInDays(\Carbon\Carbon::parse($data['end_date'])) + 1;

        $overlap = LeaveRequest::where('employee_id', $data['employee_id'])
            ->whereIn('status', ['pending', 'approved'])
            ->where('start_date', '<=', $data['end_date'])
            ->where('end_date', '>=', $data['start_date'])
            ->exists();
        if ($overlap) {
            throw ValidationException::withMessages(['start_date' => 'This employee already has a pending or approved leave request that overlaps these dates.']);
        }

        $leaveType = LeaveType::findOrFail($data['leave_type_id']);

        return DB::transaction(function () use ($data, $isHalfDay, $totalDays, $leaveType) {
            $leaveRequest = LeaveRequest::create([
                'employee_id' => $data['employee_id'],
                'leave_type_id' => $data['leave_type_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'is_half_day' => $isHalfDay,
                'half_day_period' => $data['half_day_period'] ?? null,
                'total_days' => $totalDays,
                'reason' => $data['reason'] ?? null,
                'status' => 'pending',
                'applied_by' => auth()->id(),
            ]);

            if (!$leaveType->requires_approval) {
                $this->applyApproval($leaveRequest, auth()->id(), 'Auto-approved — this leave type does not require approval.');
            }

            return response()->json($leaveRequest->fresh(['employee', 'leaveType']), 201);
        }, 5);
    }

    public function approve(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        if ($leaveRequest->status !== 'pending') {
            return response()->json(['message' => 'Only a pending leave request can be approved.'], 422);
        }

        $data = $request->validate(['decision_notes' => 'nullable|string']);

        // Any insufficient-balance ValidationException thrown inside applyApproval() is left
        // to propagate — Laravel's default handler turns it into the same 422 + errors{} shape
        // as a plain $request->validate() failure, which apiFetch() on the frontend already
        // knows how to surface.
        DB::transaction(fn() => $this->applyApproval($leaveRequest, auth()->id(), $data['decision_notes'] ?? null));

        return response()->json($leaveRequest->fresh(['employee', 'leaveType']));
    }

    public function reject(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        if ($leaveRequest->status !== 'pending') {
            return response()->json(['message' => 'Only a pending leave request can be rejected.'], 422);
        }

        $data = $request->validate(['decision_notes' => 'nullable|string']);

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'decision_notes' => $data['decision_notes'] ?? null,
        ]);

        return response()->json($leaveRequest->fresh(['employee', 'leaveType']));
    }

    public function cancel(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        if (!in_array($leaveRequest->status, ['pending', 'approved'])) {
            return response()->json(['message' => 'Only a pending or approved leave request can be cancelled.'], 422);
        }

        $data = $request->validate(['decision_notes' => 'nullable|string']);

        DB::transaction(function () use ($leaveRequest, $data) {
            if ($leaveRequest->status === 'approved') {
                $this->reverseApproval($leaveRequest);
            }
            $leaveRequest->update([
                'status' => 'cancelled',
                'decision_notes' => $data['decision_notes'] ?? $leaveRequest->decision_notes,
            ]);
        }, 5);

        return response()->json($leaveRequest->fresh(['employee', 'leaveType']));
    }

    /**
     * Deducts the leave balance (if this type is balance-tracked) and marks each day of the
     * request as an attendance record (so it shows up automatically wherever attendance does),
     * linked back via attendances.leave_request_id so cancellation can cleanly reverse both.
     */
    private function applyApproval(LeaveRequest $leaveRequest, ?int $decidedBy, ?string $notes): void
    {
        $leaveType = $leaveRequest->leaveType ?? LeaveType::find($leaveRequest->leave_type_id);
        $employee = $leaveRequest->employee ?? Employee::find($leaveRequest->employee_id);

        if ($leaveType->max_days_per_year !== null) {
            $year = (int) $leaveRequest->start_date->format('Y');
            $balance = LeaveBalance::firstOrCreate(
                ['employee_id' => $leaveRequest->employee_id, 'leave_type_id' => $leaveType->id, 'year' => $year],
                ['allocated_days' => $leaveType->max_days_per_year]
            );
            $remaining = (float) $balance->allocated_days + (float) $balance->carried_forward - (float) $balance->used_days;
            if ((float) $leaveRequest->total_days > $remaining) {
                throw ValidationException::withMessages([
                    'total_days' => "Insufficient {$leaveType->name} balance — only {$remaining} day(s) remaining for {$year}.",
                ]);
            }
            $balance->increment('used_days', $leaveRequest->total_days);
        }

        $status = $leaveRequest->is_half_day ? 'half_day' : 'on_leave';
        $date = $leaveRequest->start_date->copy();
        while ($date->lessThanOrEqualTo($leaveRequest->end_date)) {
            Attendance::updateOrCreate(
                ['employee_id' => $leaveRequest->employee_id, 'date' => $date->toDateString()],
                [
                    'leave_request_id' => $leaveRequest->id,
                    'branch_id' => $employee->branch_id,
                    'status' => $status,
                    'time_in' => null,
                    'time_out' => null,
                    'work_hours' => null,
                    'notes' => "Leave: {$leaveType->name}",
                    'marked_by' => $decidedBy,
                    'source' => 'bulk',
                ]
            );
            $date->addDay();
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $decidedBy,
            'approved_at' => now(),
            'decision_notes' => $notes,
        ]);
    }

    private function reverseApproval(LeaveRequest $leaveRequest): void
    {
        $leaveType = $leaveRequest->leaveType ?? LeaveType::find($leaveRequest->leave_type_id);

        if ($leaveType->max_days_per_year !== null) {
            $year = (int) $leaveRequest->start_date->format('Y');
            $balance = LeaveBalance::where('employee_id', $leaveRequest->employee_id)
                ->where('leave_type_id', $leaveType->id)
                ->where('year', $year)
                ->first();
            if ($balance) {
                $balance->update(['used_days' => max(0, (float) $balance->used_days - (float) $leaveRequest->total_days)]);
            }
        }

        // Deletes only the attendance rows this leave request created/overwrote (tagged via
        // leave_request_id). Note: if a day already had an attendance record before approval,
        // applyApproval() overwrote it rather than preserving it — that prior value isn't
        // restored here, only the row itself is removed.
        Attendance::where('leave_request_id', $leaveRequest->id)->delete();
    }
}
