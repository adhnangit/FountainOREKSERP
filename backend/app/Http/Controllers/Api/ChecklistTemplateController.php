<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChecklistTemplate;
use App\Models\ChecklistTemplateItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChecklistTemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = ChecklistTemplate::with('items');
        if ($request->type) $q->where('type', $request->type);
        return response()->json($q->orderBy('name')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:onboarding,offboarding',
            'employment_type' => 'nullable|in:full_time,part_time,contract,intern',
            'items' => 'nullable|array',
            'items.*.title' => 'required_with:items|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.due_days_offset' => 'nullable|integer',
        ]);
        $items = $data['items'] ?? [];
        unset($data['items']);
        $data['created_by'] = auth()->id();

        $template = DB::transaction(function () use ($data, $items) {
            $template = ChecklistTemplate::create($data);
            foreach ($items as $i => $item) {
                ChecklistTemplateItem::create([
                    'template_id' => $template->id,
                    'title' => $item['title'],
                    'description' => $item['description'] ?? null,
                    'due_days_offset' => $item['due_days_offset'] ?? 0,
                    'sort_order' => $i,
                ]);
            }
            return $template;
        }, 5);

        return response()->json($template->load('items'), 201);
    }

    public function update(Request $request, ChecklistTemplate $checklistTemplate): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'employment_type' => 'nullable|in:full_time,part_time,contract,intern',
            'is_active' => 'sometimes|boolean',
        ]);

        $checklistTemplate->update($data);
        return response()->json($checklistTemplate->fresh('items'));
    }

    public function destroy(ChecklistTemplate $checklistTemplate): JsonResponse
    {
        $checklistTemplate->delete();
        return response()->json(['message' => 'Template deleted.']);
    }

    public function storeItem(Request $request, ChecklistTemplate $checklistTemplate): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_days_offset' => 'nullable|integer',
        ]);
        $data['template_id'] = $checklistTemplate->id;
        $data['sort_order'] = $checklistTemplate->items()->count();

        return response()->json(ChecklistTemplateItem::create($data), 201);
    }

    public function destroyItem(ChecklistTemplateItem $checklistTemplateItem): JsonResponse
    {
        $checklistTemplateItem->delete();
        return response()->json(['message' => 'Item removed.']);
    }
}
