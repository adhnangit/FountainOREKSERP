<?php

namespace App\Providers;

use App\Services\AccountingService;
use App\Services\BranchContextService;
use App\Services\NumberGeneratorService;
use App\Services\StockService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(BranchContextService::class);
        $this->app->singleton(NumberGeneratorService::class);
        $this->app->singleton(StockService::class);
        $this->app->singleton(AccountingService::class, fn($app) => new AccountingService(
            $app->make(NumberGeneratorService::class)
        ));
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);

        // On some cPanel/Apache hosts the Authorization header is stripped.
        // Fall back to reading the token from a cookie set by the frontend.
        Sanctum::getAccessTokenFromRequestUsing(function ($request) {
            return $request->bearerToken()
                ?? $request->cookie('medri_api_token');
        });

        // Super admins always pass every permission/role check, mirroring the
        // frontend's hasPerm() bypass — even if a permission is added later
        // without being explicitly granted to the role.
        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });
    }
}
