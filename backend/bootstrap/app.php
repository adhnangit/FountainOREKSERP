<?php

use App\Http\Middleware\CheckMaintenanceMode;
use App\Http\Middleware\SetBranchContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'branch.context' => SetBranchContext::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // medri_api_token is a plain value set directly by frontend JS (see
        // layouts/app.blade.php's setApiToken()), not one of Laravel's own
        // encrypted response cookies — the default EncryptCookies middleware
        // tries to decrypt every incoming cookie and silently nulls out any
        // it can't, which would otherwise make CheckMaintenanceMode's cookie
        // read always come back empty.
        $middleware->encryptCookies(except: ['medri_api_token']);

        // Global, not route-by-route — every web page and every API call
        // passes through this before anything else runs, so a brand new
        // route never accidentally bypasses maintenance mode by omission.
        $middleware->web(append: [CheckMaintenanceMode::class]);
        $middleware->api(append: [
            \Illuminate\Http\Middleware\HandleCors::class,
            CheckMaintenanceMode::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });
    })->create();
