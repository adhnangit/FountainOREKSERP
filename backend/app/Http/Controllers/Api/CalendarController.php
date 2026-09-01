<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use App\Models\CalendarEventCompletion;
use App\Models\User;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    /**
     * Minimal active-user list for the attendee/assignee picker.
     */
    public function assignableUsers(): JsonResponse
    {
        return response()->json(
            User::where('is_active', true)->orderBy('name')->get(['id', 'name'])
        );
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $q = CalendarEvent::query();

        $branchId = $this->branchContext->getBranchId();
        $q->where(fn($q) => $q
            ->where('is_company_wide', true)
            ->when($branchId, fn($q) => $q->orWhere('branch_id', $branchId))
            ->orWhere('created_by', $user->id)
            ->orWhereHas('attendees', fn($q) => $q->where('user_id', $user->id))
        );

        if ($request->start) $q->where('start_at', '>=', $request->start);
        if ($request->end)   $q->where('start_at', '<=', $request->end);
        if ($request->type)  $q->where('type', $request->type);

        return response()->json($q->with(['createdBy', 'attendees', 'completions'])->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id'            => 'nullable|exists:branches,id',
            'title'                => 'required|string|max:255',
            'description'          => 'nullable|string',
            'type'                 => 'required|in:meeting,customer_visit,follow_up,payment_reminder,delivery,other',
            'start_at'             => 'required|date',
            'end_at'               => 'nullable|date',
            'all_day'              => 'nullable|boolean',
            'color'                => 'nullable|string|max:20',
            'location'             => 'nullable|string',
            'is_company_wide'      => 'nullable|boolean',
            'linked_module'        => 'nullable|string',
            'linked_id'            => 'nullable|integer',
            'reminder_minutes'     => 'nullable|integer',
            'attendee_ids'         => 'nullable|array',
            'attendee_ids.*'       => 'exists:users,id',
            'recurrence'           => 'nullable|in:none,weekly,monthly,yearly',
            'recurrence_days'      => 'nullable|array',
            'recurrence_days.*'    => 'integer|between:0,6',
            'recurrence_end_date'  => 'nullable|date',
        ]);

        $data['created_by'] = $request->user()->id;
        if (empty($data['recurrence'])) $data['recurrence'] = 'none';

        $attendeeIds = $data['attendee_ids'] ?? [];
        unset($data['attendee_ids']);

        $event = CalendarEvent::create($data);

        if ($attendeeIds) {
            $event->attendees()->sync($attendeeIds);
        }

        return response()->json($event->load(['createdBy', 'attendees', 'completions']), 201);
    }

    public function show(CalendarEvent $calendarEvent): JsonResponse
    {
        return response()->json($calendarEvent->load(['createdBy', 'attendees', 'completions']));
    }

    public function update(Request $request, CalendarEvent $calendarEvent): JsonResponse
    {
        $data = $request->validate([
            'branch_id'            => 'nullable|exists:branches,id',
            'title'                => 'sometimes|string|max:255',
            'description'          => 'nullable|string',
            'type'                 => 'sometimes|in:meeting,customer_visit,follow_up,payment_reminder,delivery,other',
            'start_at'             => 'sometimes|date',
            'end_at'               => 'nullable|date',
            'all_day'              => 'nullable|boolean',
            'color'                => 'nullable|string',
            'location'             => 'nullable|string',
            'is_company_wide'      => 'nullable|boolean',
            'linked_module'        => 'nullable|string',
            'linked_id'            => 'nullable|integer',
            'reminder_minutes'     => 'nullable|integer',
            'attendee_ids'         => 'nullable|array',
            'recurrence'           => 'nullable|in:none,weekly,monthly,yearly',
            'recurrence_days'      => 'nullable|array',
            'recurrence_days.*'    => 'integer|between:0,6',
            'recurrence_end_date'  => 'nullable|date',
        ]);

        if (isset($data['attendee_ids'])) {
            $calendarEvent->attendees()->sync($data['attendee_ids']);
            unset($data['attendee_ids']);
        }

        $calendarEvent->update($data);
        return response()->json($calendarEvent->fresh(['attendees', 'completions']));
    }

    public function destroy(CalendarEvent $calendarEvent): JsonResponse
    {
        $calendarEvent->delete();
        return response()->json(['message' => 'Event deleted.']);
    }

    public function complete(Request $request, CalendarEvent $calendarEvent): JsonResponse
    {
        $data = $request->validate(['occurrence_date' => 'required|date_format:Y-m-d']);

        $existing = CalendarEventCompletion::where('event_id', $calendarEvent->id)
            ->where('occurrence_date', $data['occurrence_date'])
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            CalendarEventCompletion::create([
                'event_id'        => $calendarEvent->id,
                'occurrence_date' => $data['occurrence_date'],
                'completed_by'    => $request->user()->id,
                'completed_at'    => now(),
            ]);
        }

        return response()->json([
            'completions' => $calendarEvent->fresh('completions')->completions,
        ]);
    }
}
