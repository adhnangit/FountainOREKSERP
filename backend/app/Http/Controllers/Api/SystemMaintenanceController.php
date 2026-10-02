<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Global (not branch-scoped) on purpose — maintenance mode takes the whole
// system down, not one branch. See App\Http\Middleware\CheckMaintenanceMode
// for the actual enforcement; this just reads/writes the two settings it
// checks (maintenance_mode, maintenance_message).
class SystemMaintenanceController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'enabled' => SystemSetting::get('maintenance_mode') === '1',
            'message' => SystemSetting::get('maintenance_message') ?: '',
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'enabled' => 'required|boolean',
            'message' => 'nullable|string|max:1000',
        ]);

        SystemSetting::set('maintenance_mode', $data['enabled'] ? '1' : '0', null, 'system');
        if (array_key_exists('message', $data)) {
            SystemSetting::set('maintenance_message', $data['message'] ?? '', null, 'system');
        }

        return response()->json([
            'message' => $data['enabled'] ? 'Maintenance mode enabled.' : 'Maintenance mode disabled.',
            'enabled' => $data['enabled'],
        ]);
    }
}
