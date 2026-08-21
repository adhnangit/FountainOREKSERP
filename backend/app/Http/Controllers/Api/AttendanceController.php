<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    public function index(Request $request): JsonResponse
    {
        $q = Attendance::with(['employee:id,first_name,last_name,employee_code,department_id,designation_id', 'markedBy:id,name']);
        $this->branchContext->applyScope($q);

        if ($request->employee_id) $q->where('employee_id', $request->employee_id);
        if ($request->date) $q->whereDate('date', $request->date);
        if ($request->from_date) $q->whereDate('date', '>=', $request->from_date);
        if ($request->to_date) $q->whereDate('date', '<=', $request->to_date);
        if ($request->status) $q->where('status', $request->status);

        return response()->json($q->orderByDesc('date')->paginate($request->input('per_page', 100)));
    }

    /**
     * Employees in scope for a given date, each paired with their existing attendance
     * record for that date (or null if not yet marked). Feeds the "Mark Attendance" grid.
     */
    public function forDate(Request $request): JsonResponse
    {
        $request->validate(['date' => 'required|date']);

        $eq = Employee::where('is_active', true)->with(['department', 'designation']);
        $this->branchContext->applyScope($eq);
        if ($request->department_id) $eq->where('department_id', $request->department_id);
        $employees = $eq->orderBy('first_name')->get();

        $existing = Attendance::whereDate('date', $request->date)
            ->whereIn('employee_id', $employees->pluck('id'))
            ->get()
            ->keyBy('employee_id');

        $data = $employees->map(fn($e) => [
            'employee_id' => $e->id,
            'employee_code' => $e->employee_code,
            'name' => $e->full_name,
            'department' => $e->department?->name,
            'designation' => $e->designation?->name,
            'attendance' => $existing->get($e->id),
        ]);

        return response()->json($data->values());
    }

    public function bulkMark(Request $request): JsonResponse
    {
        $data = $request->validate([
            'date' => 'required|date',
            'records' => 'required|array|min:1',
            'records.*.employee_id' => 'required|exists:employees,id',
            'records.*.status' => 'required|in:present,absent,half_day,late,on_leave,holiday,weekend',
            'records.*.time_in' => 'nullable|date_format:H:i,H:i:s',
            'records.*.time_out' => 'nullable|date_format:H:i,H:i:s',
            'records.*.notes' => 'nullable|string',
        ]);

        $marked = DB::transaction(function () use ($data) {
            $rows = [];
            foreach ($data['records'] as $rec) {
                $employee = Employee::find($rec['employee_id']);
                $workHours = $this->computeWorkHours($rec['time_in'] ?? null, $rec['time_out'] ?? null);

                $attendance = Attendance::updateOrCreate(
                    ['employee_id' => $rec['employee_id'], 'date' => $data['date']],
                    [
                        'branch_id' => $employee->branch_id,
                        'status' => $rec['status'],
                        'time_in' => $rec['time_in'] ?? null,
                        'time_out' => $rec['time_out'] ?? null,
                        'work_hours' => $workHours,
                        'notes' => $rec['notes'] ?? null,
                        'marked_by' => auth()->id(),
                        'source' => 'bulk',
                    ]
                );
                $rows[] = $attendance;
            }
            return $rows;
        }, 5);

        return response()->json(['marked' => count($marked)]);
    }

    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        $data = $request->validate([
            'status' => 'sometimes|in:present,absent,half_day,late,on_leave,holiday,weekend',
            'time_in' => 'nullable|date_format:H:i,H:i:s',
            'time_out' => 'nullable|date_format:H:i,H:i:s',
            'late_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        if (array_key_exists('time_in', $data) || array_key_exists('time_out', $data)) {
            $workHours = $this->computeWorkHours(
                $data['time_in'] ?? $attendance->time_in,
                $data['time_out'] ?? $attendance->time_out
            );
            $data['work_hours'] = $workHours;
        }

        $data['marked_by'] = auth()->id();
        $attendance->update($data);
        return response()->json($attendance->fresh());
    }

    public function destroy(Attendance $attendance): JsonResponse
    {
        $attendance->delete();
        return response()->json(['message' => 'Attendance record deleted.']);
    }

    /** Null if either time is missing or time_out isn't after time_in (e.g. an overnight shift — not supported here). */
    private function computeWorkHours(?string $timeIn, ?string $timeOut): ?float
    {
        if (empty($timeIn) || empty($timeOut)) return null;
        $in = \Carbon\Carbon::parse($timeIn);
        $out = \Carbon\Carbon::parse($timeOut);
        if ($out->lessThanOrEqualTo($in)) return null;
        return round($in->diffInMinutes($out) / 60, 2);
    }

    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2000',
        ]);

        $eq = Employee::where('is_active', true)->with(['department', 'designation']);
        $this->branchContext->applyScope($eq);
        if ($request->department_id) $eq->where('department_id', $request->department_id);
        $employees = $eq->orderBy('first_name')->get();

        $records = Attendance::whereYear('date', $request->year)
            ->whereMonth('date', $request->month)
            ->whereIn('employee_id', $employees->pluck('id'))
            ->get()
            ->groupBy('employee_id');

        $statuses = ['present', 'absent', 'half_day', 'late', 'on_leave', 'holiday', 'weekend'];

        $data = $employees->map(function ($e) use ($records, $statuses) {
            $rows = $records->get($e->id, collect());
            $counts = collect($statuses)->mapWithKeys(fn($s) => [$s => $rows->where('status', $s)->count()])->all();
            return [
                'employee_id' => $e->id,
                'employee_code' => $e->employee_code,
                'name' => $e->full_name,
                'department' => $e->department?->name,
                'designation' => $e->designation?->name,
                'counts' => $counts,
                'total_marked' => $rows->count(),
                'total_work_hours' => round((float) $rows->sum('work_hours'), 2),
            ];
        });

        return response()->json($data->values());
    }
}
