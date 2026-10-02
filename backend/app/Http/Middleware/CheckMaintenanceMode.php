<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

// Registered globally on both the web and api middleware groups (see
// bootstrap/app.php) so it covers every route in the app without needing to
// be attached route-by-route, and without depending on auth:sanctum having
// already run (route-level middleware runs AFTER the global group, so
// $request->user() isn't populated yet when this fires on API requests —
// this resolves the token itself instead, the same way for both web page
// loads (via the medri_api_token cookie) and API calls (via the real
// Authorization header).
//
// Cookie/localStorage key names are still the literal "medri_*" ones this
// app was forked from — not a typo, matches resources/views/layouts/app.blade.php
// and resources/views/auth/login.blade.php exactly as they exist in this codebase.
class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        // The admin bypass login page/endpoint and the health check must
        // always work, maintenance mode or not.
        if ($request->is('system-access') || $request->is('api/auth/login') || $request->is('up')) {
            return $next($request);
        }

        if (!$this->isEnabled() || $this->currentUserMayBypass($request)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => $this->message()], 503);
        }

        return response()->view('maintenance', ['message' => $this->message()], 503);
    }

    private function isEnabled(): bool
    {
        return SystemSetting::get('maintenance_mode', null, '0') === '1';
    }

    private function message(): string
    {
        return SystemSetting::get('maintenance_message', null, '')
            ?: "OREKS is currently undergoing scheduled maintenance. We'll be back shortly.";
    }

    private function currentUserMayBypass(Request $request): bool
    {
        $token = $this->resolveToken($request);
        if (!$token) {
            return false;
        }

        $accessToken = PersonalAccessToken::findToken($token);
        if (!$accessToken || ($accessToken->expires_at && $accessToken->expires_at->isPast())) {
            return false;
        }

        $user = $accessToken->tokenable;
        if (!$user || !$user->is_active) {
            return false;
        }

        return $user->isSuperAdmin() || $user->can('system.maintenance.manage');
    }

    // API calls carry a real Authorization header; plain page loads don't
    // (the frontend only attaches that header from JS, never on navigation),
    // so those instead rely on the medri_api_token cookie app.blade.php's
    // setApiToken()/the login pages already set alongside localStorage.
    private function resolveToken(Request $request): ?string
    {
        $header = $request->header('Authorization');
        if ($header && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return $request->cookie('medri_api_token');
    }
}
