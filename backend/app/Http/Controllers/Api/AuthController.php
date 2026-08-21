<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember' => 'sometimes|boolean',
        ]);

        $user = User::with(['branches', 'defaultBranch', 'roles'])
            ->where('email', $request->email)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json(['message' => 'Your account is inactive.'], 403);
        }

        // IP restriction check
        if ($user->allowed_ips) {
            $allowedIps = explode(',', $user->allowed_ips);
            if (!in_array($request->ip(), array_map('trim', $allowedIps))) {
                AuditLog::create([
                    'user_id' => $user->id,
                    'event' => 'login_blocked',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                return response()->json(['message' => 'Access from this IP is not allowed.'], 403);
            }
        }

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'branch_id' => $user->default_branch_id,
            'event' => 'login',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->url(),
        ]);

        // "Remember me" controls how long the session survives: unchecked, it
        // ends after 12 hours (so closing the browser on a shared/office PC
        // doesn't leave it signed in indefinitely); checked, it lasts 30 days.
        $expiresAt = $request->boolean('remember') ? now()->addDays(30) : now()->addHours(12);
        $token = $user->createToken('api-token', ['*'], $expiresAt)->plainTextToken;

        return response()->json([
            'token' => $token,
            'expires_at' => $expiresAt->toIso8601String(),
            'user' => $this->formatUser($user),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'event' => 'logout',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['branches', 'defaultBranch', 'roles', 'roles.permissions']);
        return response()->json($this->formatUser($user));
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'avatar' => 'sometimes|nullable|string',
        ]);

        $user->update($data);
        return response()->json(['message' => 'Profile updated.', 'user' => $this->formatUser($user->fresh(['branches', 'defaultBranch', 'roles']))]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);
        return response()->json(['message' => 'Password changed successfully.']);
    }

    public function switchBranch(Request $request): JsonResponse
    {
        $request->validate(['branch_id' => 'required|exists:branches,id']);
        $user = $request->user();

        if (!$user->isSuperAdmin() && !$user->canAccessBranch($request->branch_id)) {
            return response()->json(['message' => 'Unauthorized branch.'], 403);
        }

        return response()->json(['message' => 'Branch switched.', 'branch_id' => $request->branch_id]);
    }

    private function formatUser(User $user): array
    {
        // The HR module's own Employee record for this login, if any — distinct from the
        // legacy free-text `employee_id` string field below. Drives Self-Service/Manager
        // Portal nav visibility: "has_employee_record" gates Self-Service, "is_manager"
        // gates the Manager Portal (a manager is data-driven — anyone with direct reports
        // via employees.reporting_manager_id — not a separate Spatie role).
        $myEmployee = \App\Models\Employee::where('user_id', $user->id)->first(['id']);

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'designation' => $user->designation,
            'department' => $user->department,
            'employee_id' => $user->employee_id,
            'avatar' => $user->avatar,
            'is_super_admin' => $user->is_super_admin,
            'is_active' => $user->is_active,
            'default_branch_id' => $user->default_branch_id,
            'default_branch' => $user->defaultBranch,
            'branches' => $user->branches,
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'last_login_at' => $user->last_login_at,
            'hr_employee_id' => $myEmployee?->id,
            'has_employee_record' => (bool) $myEmployee,
            'is_manager' => $myEmployee ? \App\Models\Employee::where('reporting_manager_id', $myEmployee->id)->exists() : false,
        ];
    }
}
