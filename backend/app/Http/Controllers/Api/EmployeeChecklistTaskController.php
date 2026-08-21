<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChecklistTemplate;
use App\Models\Employee;
use App\Models\EmployeeChecklistTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeChecklistTaskController extends Controller
{
    public function index(Employee $employee, Request $request): JsonResponse
    {
        $q = $employee->checklistTasks()->with('completedBy:id,name')->orderBy('sort_order');
        if ($request->type) $q->where('type', $request->type);
        return response()->json($q->get());
    }

    public function store(Request $request, Employee $employee): JsonResponse
    {
        $data = $request->validate([
            'type' => 'required|in:onboarding,offboarding',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);
        $data['employee_id'] = $employee->id;
        $data['created_by'] = auth()->id();
        $data['sort_order'] = $employee->checklistTasks()->where('type', $data['type'])->count();

        return response()->json(EmployeeChecklistTask::create($data), 201);
    }

    /**
     * Instantiates every item on a template as a task for this employee. Due dates are
     * computed from due_days_offset against join_date (onboarding) or exit_date, falling
     * back to today (offboarding — exit_date is often not set yet when offboarding starts).
     */
    public function applyTemplate(Request $request, Employee $employee): JsonResponse
    {
        $data = $request->validate(['template_id' => 'required|exists:checklist_templates,id']);
        $template = ChecklistTemplate::with('items')->findOrFail($data['template_id']);

        $anchor = $template->type === 'onboarding'
            ? ($employee->join_date ?? now())
            : ($employee->exit_date ?? now());

        $existingCount = $employee->checklistTasks()->where('type', $template->type)->count();

        $created = DB::transaction(function () use ($template, $employee, $anchor, $existingCount) {
            $n = 0;
            foreach ($template->items as $item) {
                EmployeeChecklistTask::create([
                    'employee_id' => $employee->id,
                    'type' => $template->type,
                    'title' => $item->title,
                    'description' => $item->description,
                    'due_date' => (clone $anchor)->addDays($item->due_days_offset),
                    'sort_order' => $existingCount + $n,
                    'created_by' => auth()->id(),
                ]);
                $n++;
            }
            return $n;
        }, 5);

        return response()->json(['created' => $created]);
    }

    public function update(Request $request, EmployeeChecklistTask $employeeChecklistTask): JsonResponse
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'status' => 'sometimes|in:pending,completed',
        ]);

        if (($data['status'] ?? null) === 'completed' && $employeeChecklistTask->status !== 'completed') {
            $data['completed_by'] = auth()->id();
            $data['completed_at'] = now();
        } elseif (($data['status'] ?? null) === 'pending') {
            $data['completed_by'] = null;
            $data['completed_at'] = null;
        }

        $employeeChecklistTask->update($data);
        return response()->json($employeeChecklistTask->fresh('completedBy:id,name'));
    }

    public function destroy(EmployeeChecklistTask $employeeChecklistTask): JsonResponse
    {
        $employeeChecklistTask->delete();
        return response()->json(['message' => 'Task removed.']);
    }
}
