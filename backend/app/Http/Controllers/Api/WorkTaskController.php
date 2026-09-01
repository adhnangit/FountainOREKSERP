<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkTask;
use App\Models\WorkTaskCategory;
use App\Models\WorkTaskFollowup;
use App\Models\WorkTaskSubtask;
use App\Models\User;
use App\Services\BranchContextService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function dashboard(): JsonResponse
    {
        $today = Carbon::today();

        $q = WorkTask::with('category');
        $this->branchContext->applyScope($q);
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

        $recentFollowups = WorkTaskFollowup::with(['task', 'user'])->latest()->take(8)->get();

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
            ->when($categoryIds, fn ($q) => $q->whereIn('category_id', $categoryIds))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->priority, fn ($q) => $q->where('priority', $request->priority))
            ->when($request->assigned_to, fn ($q) => $q->where('assigned_to', $request->assigned_to))
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
            'category_id' => 'nullable|exists:work_task_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'required|in:Low,Medium,High',
            'status' => 'required|in:Pending,In Progress,Completed,Cancelled',
            'due_date' => 'nullable|date',
        ]);

        $data['branch_id'] = $data['branch_id'] ?? $this->branchContext->getBranchId();
        $data['created_by'] = $request->user()->id;
        $data['completed_at'] = $data['status'] === 'Completed' ? now() : null;

        $task = WorkTask::create($data);

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
            'category_id' => 'nullable|exists:work_task_categories,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'priority' => 'sometimes|in:Low,Medium,High',
            'status' => 'sometimes|in:Pending,In Progress,Completed,Cancelled',
            'due_date' => 'nullable|date',
        ]);

        $previousStatus = $workTask->status;
        $newStatus = $data['status'] ?? $previousStatus;

        $data['completed_at'] = $newStatus === 'Completed'
            ? ($previousStatus === 'Completed' ? $workTask->completed_at : now())
            : null;

        $workTask->update($data);

        if ($previousStatus !== $newStatus) {
            $this->logStatusChange($workTask, $request->user()->id, $previousStatus, $newStatus);
        }

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
        }

        return response()->json($workTask->fresh(['category', 'assignee']));
    }

    public function destroy(WorkTask $workTask): JsonResponse
    {
        $workTask->delete();

        return response()->json(['message' => 'Task deleted.']);
    }

    public function addFollowup(Request $request, WorkTask $workTask): JsonResponse
    {
        $data = $request->validate(['note' => 'required|string|min:1']);

        $followup = WorkTaskFollowup::create([
            'task_id' => $workTask->id,
            'user_id' => $request->user()->id,
            'note' => $data['note'],
        ]);

        return response()->json($followup->load('user'), 201);
    }

    public function storeSubtask(Request $request, WorkTask $workTask): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $data['work_task_id'] = $workTask->id;
        $data['sort_order'] = $workTask->subtasks()->count();

        $subtask = WorkTaskSubtask::create($data);

        return response()->json($subtask->load('assignee'), 201);
    }

    public function updateSubtask(Request $request, WorkTask $workTask, WorkTaskSubtask $subtask): JsonResponse
    {
        abort_if($subtask->work_task_id !== $workTask->id, 404);

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $subtask->update($data);

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

        return response()->json($followup->load('user'), 201);
    }

    public function toggleSubtask(WorkTask $workTask, WorkTaskSubtask $subtask): JsonResponse
    {
        abort_if($subtask->work_task_id !== $workTask->id, 404);

        $subtask->update(['completed' => !$subtask->completed]);

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
}
