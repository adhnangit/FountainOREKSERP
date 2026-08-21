<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoleWidgetSetting;
use Database\Seeders\RoleWidgetSettingsSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardWidgetSettingsController extends Controller
{
    // Return widget config for the current user's role (used by dashboard)
    public function forCurrentUser(Request $request): JsonResponse
    {
        $user = $request->user();
        $role = $user->isSuperAdmin() ? 'super_admin' : ($user->getRoleNames()->first() ?? 'viewer');

        $allWidgets = collect(RoleWidgetSettingsSeeder::WIDGETS);
        $saved = RoleWidgetSetting::where('role_name', $role)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('widget_key');

        $config = $allWidgets->map(function ($w, $idx) use ($saved) {
            $row = $saved->get($w['key']);
            return [
                'key'        => $w['key'],
                'label'      => $w['label'],
                'is_visible' => $row ? $row->is_visible : true,
                'sort_order' => $row ? $row->sort_order : $idx,
            ];
        })->sortBy('sort_order')->values();

        return response()->json([
            'role'    => $role,
            'widgets' => $config,
        ]);
    }

    // Return all roles' settings for the admin page
    public function adminIndex(): JsonResponse
    {
        $roles = [
            'super_admin', 'branch_manager', 'sales_person', 'accountant',
            'inventory_manager', 'purchase_officer', 'hr_admin', 'viewer',
        ];
        $allWidgets = RoleWidgetSettingsSeeder::WIDGETS;

        $result = [];
        foreach ($roles as $role) {
            $saved = RoleWidgetSetting::where('role_name', $role)
                ->get()->keyBy('widget_key');

            $widgets = [];
            foreach ($allWidgets as $idx => $w) {
                $row = $saved->get($w['key']);
                $widgets[] = [
                    'key'        => $w['key'],
                    'label'      => $w['label'],
                    'is_visible' => $row ? (bool) $row->is_visible : true,
                    'sort_order' => $row ? (int) $row->sort_order : $idx,
                ];
            }
            usort($widgets, fn($a, $b) => $a['sort_order'] - $b['sort_order']);
            $result[$role] = $widgets;
        }

        return response()->json($result);
    }

    // Save widget settings for one or more roles
    public function save(Request $request): JsonResponse
    {
        $data = $request->validate([
            'role'            => 'required|string',
            'widgets'         => 'required|array',
            'widgets.*.key'   => 'required|string',
            'widgets.*.is_visible'  => 'required|boolean',
            'widgets.*.sort_order'  => 'required|integer',
        ]);

        foreach ($data['widgets'] as $w) {
            RoleWidgetSetting::updateOrInsert(
                ['role_name' => $data['role'], 'widget_key' => $w['key']],
                ['is_visible' => $w['is_visible'], 'sort_order' => $w['sort_order'], 'updated_at' => now()]
            );
        }

        return response()->json(['message' => 'Widget settings saved.']);
    }
}
