<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Services\BranchContextService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private BranchContextService $branchContext) {}

    /**
     * Minimal active-user list for the "Assign To" picker. Deliberately not the
     * full /users endpoint (gated behind users.view, an admin-only permission) —
     * assigning a task to a colleague is a basic action anyone with tasks.create
     * should be able to do, so this only exposes id+name.
     */
    public function assignableUsers(): JsonResponse
    {
        return response()->json(
            User::where('is_active', true)->orderBy('name')->get(['id', 'name'])
        );
    }

    public function index(Request $request): JsonResponse
    {
        $q = Task::query();
        $this->branchContext->applyScope($q);
        if ($request->status) $q->where('status', $request->status);
        if ($request->priority) $q->where('priority', $request->priority);
        if ($request->assigned_to) $q->where('assigned_to', $request->assigned_to);
        if ($request->my_tasks) $q->where('assigned_to', $request->user()->id);
        if ($request->overdue) $q->whereNotIn('status', ['done', 'cancelled'])->where('due_date', '<', today());
        return response()->json($q->with(['assignedTo', 'createdBy', 'branch'])->orderBy('due_date')->paginate($request->input('per_page', 20)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'assigned_to' => 'nullable|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'linked_module' => 'nullable|string',
            'linked_id' => 'nullable|integer',
            'is_recurring' => 'nullable|boolean',
            'recurrence_frequency' => 'nullable|string',
        ]);

        $data['created_by'] = $request->user()->id;
        $data['status'] = 'todo';
        $task = Task::create($data);
        return response()->json($task->load(['assignedTo', 'createdBy', 'branch']), 201);
    }

    public function show(Task $task): JsonResponse
    {
        return response()->json($task->load(['assignedTo', 'createdBy', 'branch', 'comments.user']));
    }

    public function update(Request $request, Task $task): JsonResponse
    {
        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:todo,in_progress,on_hold,done,cancelled',
            'due_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'linked_module' => 'nullable|string',
            'linked_id' => 'nullable|integer',
            'is_recurring' => 'nullable|boolean',
            'recurrence_frequency' => 'nullable|string',
        ]);

        if (isset($data['status']) && $data['status'] === 'done' && !$task->completed_at) {
            $data['completed_at'] = today();
        }

        $task->update($data);
        return response()->json($task->fresh(['assignedTo', 'createdBy']));
    }

    public function destroy(Task $task): JsonResponse
    {
        $task->delete();
        return response()->json(['message' => 'Task deleted.']);
    }

    public function addComment(Request $request, Task $task): JsonResponse
    {
        $data = $request->validate(['comment' => 'required|string']);
        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
            'comment' => $data['comment'],
        ]);
        return response()->json($comment->load('user'), 201);
    }
}
