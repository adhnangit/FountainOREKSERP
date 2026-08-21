<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = User::query();
        if ($request->branch_id) {
            $q->whereHas('branches', fn($q) => $q->where('branch_id', $request->branch_id));
        }
        if ($request->role) {
            $q->whereHas('roles', fn($q) => $q->where('name', $request->role));
        }
        if ($request->search) {
            $q->where(fn($q) => $q->where('name', 'like', "%{$request->search}%")->orWhere('email', 'like', "%{$request->search}%"));
        }
        if ($request->is_active !== null) $q->where('is_active', $request->boolean('is_active'));

        return response()->json($q->with(['branches', 'roles', 'defaultBranch'])->orderBy('name')->paginate($request->input('per_page', 20)));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'designation' => 'nullable|string',
            'department' => 'nullable|string',
            'employee_id' => 'nullable|string|unique:users',
            'default_branch_id' => 'nullable|exists:branches,id',
            'branch_ids' => 'nullable|array',
            'branch_ids.*' => 'exists:branches,id',
            'role' => 'required|string|exists:roles,name',
            'is_active' => 'nullable|boolean',
            'is_super_admin' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'designation' => $data['designation'] ?? null,
            'department' => $data['department'] ?? null,
            'employee_id' => $data['employee_id'] ?? null,
            'default_branch_id' => $data['default_branch_id'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'is_super_admin' => $data['is_super_admin'] ?? false,
        ]);

        $user->assignRole($data['role']);
        if (!empty($data['branch_ids'])) {
            $user->branches()->sync($data['branch_ids']);
        }

        return response()->json($user->load(['branches', 'roles', 'defaultBranch']), 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($user->load(['branches', 'roles', 'defaultBranch']));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'designation' => 'nullable|string',
            'department' => 'nullable|string',
            'default_branch_id' => 'nullable|exists:branches,id',
            'branch_ids' => 'nullable|array',
            'branch_ids.*' => 'exists:branches,id',
            'role' => 'sometimes|string|exists:roles,name',
            'is_active' => 'sometimes|boolean',
            'allowed_ips' => 'nullable|string',
        ]);

        if (isset($data['role'])) {
            $user->syncRoles([$data['role']]);
            unset($data['role']);
        }

        if (isset($data['branch_ids'])) {
            $user->branches()->sync($data['branch_ids']);
            unset($data['branch_ids']);
        }

        $user->update($data);
        return response()->json($user->fresh(['branches', 'roles', 'defaultBranch']));
    }

    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $data = $request->validate(['password' => 'required|string|min:8']);
        $user->update(['password' => Hash::make($data['password'])]);
        return response()->json(['message' => 'Password reset successfully.']);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Cannot delete yourself.'], 422);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted.']);
    }

    // Roles & Permissions
    public function roles(): JsonResponse
    {
        $userModel = addslashes(\App\Models\User::class);
        return response()->json(
            Role::with('permissions')
                ->select('roles.*', \Illuminate\Support\Facades\DB::raw(
                    "(SELECT COUNT(*) FROM model_has_roles WHERE model_has_roles.role_id = roles.id AND model_has_roles.model_type = '{$userModel}') as users_count"
                ))
                ->where('guard_name', 'web')
                ->get()
        );
    }

    public function permissions(): JsonResponse
    {
        return response()->json(
            Permission::where('guard_name', 'web')->get()
        );
    }

    public function storeRole(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|unique:roles',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        if (!empty($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }
        return response()->json($role->load('permissions'), 201);
    }

    public function updateRole(Request $request, Role $role): JsonResponse
    {
        $data = $request->validate(['permissions' => 'required|array']);
        $role->syncPermissions($data['permissions']);
        return response()->json($role->fresh('permissions'));
    }

    public function activityLog(Request $request): JsonResponse
    {
        $q = \App\Models\AuditLog::query();
        if ($request->user_id) $q->where('user_id', $request->user_id);
        if ($request->event) $q->where('event', $request->event);
        if ($request->from_date) $q->whereDate('created_at', '>=', $request->from_date);
        if ($request->to_date) $q->whereDate('created_at', '<=', $request->to_date);
        return response()->json($q->with('user')->latest()->paginate(50));
    }
}
