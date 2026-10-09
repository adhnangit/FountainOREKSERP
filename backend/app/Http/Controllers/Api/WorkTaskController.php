<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\WorkTask;
use App\Models\WorkTaskCategory;
use App\Models\WorkTaskFollowup;
use App\Models\WorkTaskSubtask;
use App\Models\User;
use App\Services\BranchContextService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WorkTaskController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    /**
     * Minimal active-user list for the "Assign To" picker.
     */
    public function assignableUsers(): JsonResponse
    {
        return response()->json(
            User::where('is_active', true)->orderBy('name')->get(['id', 'name'])
        );
    }

    public function dashboard(Request $request): JsonResponse
    {
        $today = Carbon::today();

        $q = WorkTask::with('category')->whereNull('archived_at');
        $this->branchContext->applyScope($q);

        if ($request->boolean('my_tasks')) {
            $userId = $request->user()->id;
            $q->where(function ($sub) use ($userId) {
                $sub->where('assigned_to', $userId)
                    ->orWhereHas('subtasks', fn ($s) => $s->where('assigned_to', $userId));
            });
        }

        $allTasks = $q->get();
        $activeTasks = $allTasks->whereNotIn('status', ['Cancelled']);

        $stats = [
            'total' => $allTasks->count(),
            'pending' => $allTasks->where('status', 'Pending')->count(),
            'in_progress' => $allTasks->where('status', 'In Progress')->count(),
            'completed' => $allTasks->where('status', 'Completed')->count(),
            'cancelled' => $allTasks->where('status', 'Cancelled')->count(),
            'overdue' => $allTasks->filter(fn ($t) => $t->isOverdue())->count(),
            'due_soon' => $allTasks->filter(fn ($t) => $t->due_date && !in_array($t->status, ['Completed', 'Cancelled'])
                && $t->due_date->greaterThanOrEqualTo($today) && $t->due_date->lessThanOrEqualTo($today->copy()->addDays(7))
            )->count(),
        ];

        $completionBase = $activeTasks->count();
        $stats['completion_rate'] = $completionBase > 0 ? round(($stats['completed'] / $completionBase) * 100, 1) : 0;

        $completedTasks = $allTasks->where('status', 'Completed')->filter(fn ($t) => $t->completed_at);
        $onTimeCount = $completedTasks->filter(fn ($t) => !$t->due_date || $t->completed_at->lte($t->due_date->copy()->endOfDay()))->count();
        $stats['on_time_rate'] = $completedTasks->count() > 0 ? round(($onTimeCount / $completedTasks->count()) * 100, 1) : null;

        // Sub-tasks aren't their own WorkTask rows, so they're invisible to every
        // count above — surface them separately rather than conflating "tasks"
        // (which drives status/priority/category semantics) with checklist items.
        $allSubtasks = WorkTaskSubtask::whereIn('work_task_id', $allTasks->pluck('id'))->get();
        $stats['subtasks_total'] = $allSubtasks->count();
        $stats['subtasks_completed'] = $allSubtasks->where('completed', true)->count();
        $stats['subtasks_overdue'] = $allSubtasks->filter(fn ($s) => $s->due_date && !$s->completed && $s->due_date->isPast())->count();
        $stats['subtasks_completion_rate'] = $stats['subtasks_total'] > 0
            ? round(($stats['subtasks_completed'] / $stats['subtasks_total']) * 100, 1)
            : null;

        $categories = WorkTaskCategory::orderBy('name')->get()->map(function ($cat) use ($allTasks) {
            $catTasks = $allTasks->where('category_id', $cat->id);
            $total = $catTasks->count();
            $completed = $catTasks->where('status', 'Completed')->count();
            return [
                'name' => $cat->name,
                'color' => $cat->color,
                'total' => $total,
                'completed' => $completed,
                'overdue' => $catTasks->filter(fn ($t) => $t->isOverdue())->count(),
                'percentage' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            ];
        })->filter(fn ($c) => $c['total'] > 0)->values();

        $uncategorizedCount = $allTasks->whereNull('category_id')->count();

        $overdueTasks = $allTasks->filter(fn ($t) => $t->isOverdue())->sortBy('due_date')->take(6)->values();
        $dueSoonTasks = $allTasks->filter(fn ($t) => $t->due_date && !in_array($t->status, ['Completed', 'Cancelled'])
            && $t->due_date->greaterThanOrEqualTo($today) && $t->due_date->lessThanOrEqualTo($today->copy()->addDays(7))
        )->sortBy('due_date')->take(6)->values();

        $overdueTasks->load('assignee');
        $dueSoonTasks->load('assignee');

        $recentFollowups = WorkTaskFollowup::with(['task', 'user'])
            ->when($request->boolean('my_tasks'), fn ($q) => $q->whereIn('task_id', $allTasks->pluck('id')))
            ->latest()->take(8)->get();

        return response()->json([
            'stats' => $stats,
            'category_breakdown' => $categories,
            'uncategorized_count' => $uncategorizedCount,
            'overdue_tasks' => $overdueTasks,
            'due_soon_tasks' => $dueSoonTasks,
            'recent_followups' => $recentFollowups,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $categoryIds = null;
        if ($request->category_id) {
            $selectedCategory = WorkTaskCategory::find($request->category_id);
            $categoryIds = $selectedCategory
                ? array_merge([$selectedCategory->id], $selectedCategory->allDescendantIds())
                : [$request->category_id];
        }

        $q = WorkTask::with(['category', 'assignee'])
            ->withCount(['followups', 'subtasks', 'subtasks as subtasks_completed_count' => fn ($q) => $q->where('completed', true)])
            ->when($request->boolean('archived'), fn ($q) => $q->whereNotNull('archived_at'), fn ($q) => $q->whereNull('archived_at'))
            ->when($categoryIds, fn ($q) => $q->whereIn('category_id', $categoryIds))
            ->when($request->status, fn ($q) => $q->whereIn('status', explode(',', $request->status)))
            ->when($request->priority, fn ($q) => $q->where('priority', $request->priority))
            ->when($request->assigned_to ?: ($request->boolean('my_tasks') ? $request->user()->id : null), function ($q, $userId) use ($request) {
                $q->where(function ($sub) use ($userId) {
                    $sub->where('assigned_to', $userId)
                        ->orWhereHas('subtasks', fn ($s) => $s->where('assigned_to', $userId));
                })
                // So the UI can show *why* a task matched when it's only via a sub-task,
                // not the task's own assignee — only the sub-tasks assigned to this
                // specific filtered user are loaded here (not the full sub-task list).
                // Also honor the active status filter here, else a "Pending" filter
                // would still list that person's already-Completed sub-tasks in the
                // match note, contradicting the filter the user just picked.
                ->with(['subtasks' => function ($s) use ($userId, $request) {
                    $s->where('assigned_to', $userId)->with('assignee');
                    if ($request->status) {
                        $s->whereIn('status', explode(',', $request->status));
                    }
                }]);
            })
            ->when($request->boolean('overdue'), fn ($q) => $q->whereNotNull('due_date')
                ->whereDate('due_date', '<', Carbon::today())
                ->whereNotIn('status', ['Completed', 'Cancelled']))
            ->when($request->search, fn ($q) => $q->where('title', 'like', '%'.$request->search.'%'))
            ->orderByRaw("CASE WHEN status IN ('Completed', 'Cancelled') THEN 1 ELSE 0 END")
            ->orderBy('due_date')
            ->orderByDesc('id');

        $this->branchContext->applyScope($q);

        return response()->json($q->paginate($request->input('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'category_id' => ['nullable', Rule::exists('work_task_categories', 'id')->whereNull('deleted_at')],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:Low,Medium,High',
            'status' => 'required|in:Pending,In Progress,Completed,Cancelled',
            'due_date' => 'nullable|date',
            'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,csv,txt',
        ]);

        $data['branch_id'] = $data['branch_id'] ?? $this->branchContext->getBranchId();
        $data['created_by'] = $request->user()->id;
        $data['completed_at'] = $data['status'] === 'Completed' ? now() : null;

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('task-attachments', 'public');
            $data['attachment_name'] = $request->file('attachment')->getClientOriginalName();
        }
        unset($data['attachment']);

        $task = WorkTask::create($data);

        $this->notifyAssignee($task, $request->user()->id, $task->title);
        $mentioned = $this->notifyMentions($task, $task->description, $request->user()->id);
        $this->notifyAllUsers($task, $request->user()->id, 'New task created', $request->user()->name . ' created "' . $task->title . '"', '📋', $mentioned);

        return response()->json($task->load(['category', 'assignee']), 201);
    }

    public function show(WorkTask $workTask): JsonResponse
    {
        return response()->json($workTask->load(['category', 'assignee', 'creator', 'followups.user', 'subtasks.assignee', 'subtasks.followups.user']));
    }

    public function update(Request $request, WorkTask $workTask): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'category_id' => ['nullable', Rule::exists('work_task_categories', 'id')->whereNull('deleted_at')],
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'sometimes|in:Low,Medium,High',
            'status' => 'sometimes|in:Pending,In Progress,Completed,Cancelled',
            'due_date' => 'nullable|date',
        ]);

        $previousStatus = $workTask->status;
        $newStatus = $data['status'] ?? $previousStatus;
        $previousAssignee = $workTask->assigned_to;
        $previousDescription = $workTask->description;

        $data['completed_at'] = $newStatus === 'Completed'
            ? ($previousStatus === 'Completed' ? $workTask->completed_at : now())
            : null;

        $workTask->update($data);

        if ($previousStatus !== $newStatus) {
            $this->logStatusChange($workTask, $request->user()->id, $previousStatus, $newStatus);
        }

        if ($workTask->assigned_to && (int) $workTask->assigned_to !== (int) $previousAssignee) {
            $this->notifyAssignee($workTask, $request->user()->id, $workTask->title);
        }
        // Only re-scan for @mentions when the description text actually changed —
        // otherwise an unrelated edit (e.g. just priority) would re-notify
        // whoever was mentioned in a description that was never touched.
        $mentioned = $workTask->description !== $previousDescription
            ? $this->notifyMentions($workTask, $workTask->description, $request->user()->id)
            : [];
        $this->notifyAllUsers($workTask, $request->user()->id, 'Task updated', $request->user()->name . ' updated "' . $workTask->title . '"', '📋', $mentioned);

        return response()->json($workTask->fresh(['category', 'assignee']));
    }

    public function quickStatus(Request $request, WorkTask $workTask): JsonResponse
    {
        $data = $request->validate(['status' => 'required|in:Pending,In Progress,Completed,Cancelled']);

        $previousStatus = $workTask->status;
        $workTask->update([
            'status' => $data['status'],
            'completed_at' => $data['status'] === 'Completed' ? now() : null,
        ]);

        if ($previousStatus !== $data['status']) {
            $this->logStatusChange($workTask, $request->user()->id, $previousStatus, $data['status']);
            $icon = $data['status'] === 'Completed' ? '✅' : '🔄';
            $this->notifyAllUsers($workTask, $request->user()->id, 'Task status changed',
                $request->user()->name . ' marked "' . $workTask->title . '" as ' . $data['status'], $icon);
        }

        return response()->json($workTask->fresh(['category', 'assignee']));
    }

    public function archive(WorkTask $workTask): JsonResponse
    {
        $workTask->update(['archived_at' => now()]);

        return response()->json($workTask->fresh(['category', 'assignee']));
    }

    public function restore(WorkTask $workTask): JsonResponse
    {
        $workTask->update(['archived_at' => null]);

        return response()->json($workTask->fresh(['category', 'assignee']));
    }

    public function destroy(WorkTask $workTask): JsonResponse
    {
        $workTask->delete();

        return response()->json(['message' => 'Task deleted.']);
    }

    public function addFollowup(Request $request, WorkTask $workTask): JsonResponse
    {
        $data = $request->validate([
            'note' => 'required|string|min:1',
            'attachment' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,csv,txt',
        ]);

        $followupData = [
            'task_id' => $workTask->id,
            'user_id' => $request->user()->id,
            'note' => $data['note'],
        ];

        if ($request->hasFile('attachment')) {
            $followupData['attachment_path'] = $request->file('attachment')->store('task-attachments', 'public');
            $followupData['attachment_name'] = $request->file('attachment')->getClientOriginalName();
        }

        $followup = WorkTaskFollowup::create($followupData);

        $mentioned = $this->notifyMentions($workTask, $data['note'], $request->user()->id);
        $this->notifyAllUsers($workTask, $request->user()->id, 'New comment on task',
            $request->user()->name . ' commented on "' . $workTask->title . '"', '💬', $mentioned);

        return response()->json($followup->load('user'), 201);
    }

    public function storeSubtask(Request $request, WorkTask $workTask): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'priority' => 'nullable|in:Low,Medium,High',
            'status' => 'nullable|in:Pending,In Progress,Completed,Cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $data['work_task_id'] = $workTask->id;
        $data['sort_order'] = $workTask->subtasks()->count();
        $data['priority'] = $data['priority'] ?? 'Medium';
        $data['status'] = $data['status'] ?? 'Pending';

        $subtask = WorkTaskSubtask::create($data);

        $this->notifyAllUsers($workTask, $request->user()->id, 'Sub-task added',
            $request->user()->name . ' added a sub-task to "' . $workTask->title . '"');

        return response()->json($subtask->load('assignee'), 201);
    }

    public function updateSubtask(Request $request, WorkTask $workTask, WorkTaskSubtask $subtask): JsonResponse
    {
        abort_if($subtask->work_task_id !== $workTask->id, 404);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'priority' => 'sometimes|in:Low,Medium,High',
            'status' => 'sometimes|in:Pending,In Progress,Completed,Cancelled',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $subtask->update($data);

        $this->notifyAllUsers($workTask, $request->user()->id, 'Sub-task updated',
            $request->user()->name . ' updated a sub-task on "' . $workTask->title . '"');

        return response()->json($subtask->fresh('assignee'));
    }

    public function storeSubtaskFollowup(Request $request, WorkTask $workTask, WorkTaskSubtask $subtask): JsonResponse
    {
        abort_if($subtask->work_task_id !== $workTask->id, 404);

        $data = $request->validate(['note' => 'required|string|min:1']);

        $followup = WorkTaskFollowup::create([
            'task_id' => $workTask->id,
            'subtask_id' => $subtask->id,
            'user_id' => $request->user()->id,
            'note' => $data['note'],
        ]);

        $mentioned = $this->notifyMentions($workTask, $data['note'], $request->user()->id);
        $this->notifyAllUsers($workTask, $request->user()->id, 'New note on sub-task',
            $request->user()->name . ' added a note to a sub-task on "' . $workTask->title . '"', '💬', $mentioned);

        return response()->json($followup->load('user'), 201);
    }

    public function toggleSubtask(Request $request, WorkTask $workTask, WorkTaskSubtask $subtask): JsonResponse
    {
        abort_if($subtask->work_task_id !== $workTask->id, 404);

        $nowCompleted = !$subtask->completed;
        $subtask->update(['status' => $nowCompleted ? 'Completed' : 'Pending']);

        $this->notifyAllUsers($workTask, $request->user()->id, $nowCompleted ? 'Sub-task completed' : 'Sub-task reopened',
            $request->user()->name . ' ' . ($nowCompleted ? 'completed' : 'reopened') . ' a sub-task on "' . $workTask->title . '"',
            $nowCompleted ? '✅' : '🔄');

        return response()->json($subtask->fresh('assignee'));
    }

    public function destroySubtask(WorkTask $workTask, WorkTaskSubtask $subtask): JsonResponse
    {
        abort_if($subtask->work_task_id !== $workTask->id, 404);

        $subtask->delete();

        return response()->json(['message' => 'Sub-task removed.']);
    }

    private function logStatusChange(WorkTask $task, int $userId, string $from, string $to): void
    {
        WorkTaskFollowup::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'note' => "Status changed from \"{$from}\" to \"{$to}\".",
            'status_snapshot' => $to,
        ]);
    }

    /**
     * Pings the task's current assignee specifically — skipped when there's no
     * assignee, or the assignee is the one who just did the thing.
     */
    private function notifyAssignee(WorkTask $task, int $actorId, string $message): void
    {
        if (!$task->assigned_to || (int) $task->assigned_to === $actorId) {
            return;
        }

        AppNotification::create([
            'user_id' => $task->assigned_to,
            'title' => 'Task assigned to you',
            'message' => $message,
            'type' => 'task_assigned',
            'icon' => '📋',
            'link' => '/task-manager/board?open=' . $task->id,
        ]);
    }

    /**
     * Broadcasts a task event to every other active user — this module has no
     * per-task membership/visibility concept, so "all user need to get the
     * notification" (as requested) means every active account, not a subset.
     * $exclude lets a caller skip users who already got a more specific
     * notification for this same action (e.g. an @-mention), so they aren't
     * double-pinged for one event.
     */
    private function notifyAllUsers(WorkTask $task, int $actorId, string $title, string $message, string $icon = '📋', array $exclude = []): void
    {
        User::where('is_active', true)
            ->where('id', '!=', $actorId)
            ->whereNotIn('id', $exclude)
            ->pluck('id')
            ->each(fn ($userId) => AppNotification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => 'task_activity',
                'icon' => $icon,
                'link' => '/task-manager/board?open=' . $task->id,
            ]));
    }

    /**
     * Parses @[Name](userId) tokens — inserted by the frontend's @mention
     * picker — out of free text (task description, a follow-up/comment note),
     * and notifies each mentioned user specifically. Returns the mentioned
     * user ids so the caller can exclude them from a broader "notify everyone"
     * broadcast for the same action.
     */
    private function notifyMentions(WorkTask $task, ?string $text, int $actorId): array
    {
        if (!$text) {
            return [];
        }

        preg_match_all('/@\[[^\]]+\]\((\d+)\)/', $text, $matches);
        $mentionedIds = array_unique(array_map('intval', $matches[1] ?? []));

        foreach ($mentionedIds as $userId) {
            if ($userId === $actorId) {
                continue;
            }
            AppNotification::create([
                'user_id' => $userId,
                'title' => 'You were mentioned',
                'message' => $task->title,
                'type' => 'task_mention',
                'icon' => '💬',
                'link' => '/task-manager/board?open=' . $task->id,
            ]);
        }

        return $mentionedIds;
    }
}
