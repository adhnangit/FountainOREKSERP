<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkTaskCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkTaskCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $all = WorkTaskCategory::withCount('tasks')->orderBy('name')->get();

        return response()->json(WorkTaskCategory::buildTree($all)->values());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'status' => 'required|in:Active,Inactive',
            'parent_id' => 'nullable|exists:work_task_categories,id',
        ]);

        $category = WorkTaskCategory::create($data);

        return response()->json($category, 201);
    }

    public function update(Request $request, WorkTaskCategory $workTaskCategory): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'required|string|max:7',
            'status' => 'required|in:Active,Inactive',
            'parent_id' => 'nullable|exists:work_task_categories,id',
        ]);

        if (!empty($data['parent_id'])) {
            $excluded = array_merge([$workTaskCategory->id], $workTaskCategory->allDescendantIds());
            if (in_array((int) $data['parent_id'], $excluded, true)) {
                return response()->json(['message' => 'A category cannot be its own parent.', 'errors' => ['parent_id' => ['A category cannot be its own parent.']]], 422);
            }
        }

        $workTaskCategory->update($data);

        return response()->json($workTaskCategory->fresh());
    }

    public function destroy(WorkTaskCategory $workTaskCategory): JsonResponse
    {
        // Promote any children to the deleted category's own parent rather than
        // leaving them pointing at a now-missing row; tasks filed under it just
        // fall back to uncategorized via the FK's nullOnDelete.
        WorkTaskCategory::where('parent_id', $workTaskCategory->id)->update(['parent_id' => $workTaskCategory->parent_id]);
        $workTaskCategory->delete();

        return response()->json(['message' => 'Category deleted.']);
    }
}
