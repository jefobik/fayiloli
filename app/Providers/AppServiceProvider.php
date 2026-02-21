<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerSuperAdminGate();
    }

    /**
     * Grant the platform super-admin unconditional access to every Gate check.
     *
     * Gate::before() fires before any policy method or ability check.
     * Returning `true` short-circuits evaluation and grants the ability.
     * Returning `null` continues normal evaluation — non-super-admins fall
     * through to TenantPolicy or Spatie permission checks unchanged.
     *
     * This covers:
     *  - All TenantPolicy methods (viewAny, view, create, update, delete, …)
     *  - All Spatie-based $user->can('permission-name') checks in tenant context
     *  - Any future policies added to the application
     *
     * IMPORTANT: This runs for BOTH central and tenant DB contexts.  In the
     * tenant context the auth guard resolves the user from the tenant DB, so
     * the super-admin (who only exists in the central DB) is never authenticated
     * on a tenant domain — meaning this bypass will never fire there unless a
     * tenant user is deliberately granted is_super_admin = true, which the
     * application intentionally prevents.
     */
    private function registerSuperAdminGate(): void
    {
        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->isSuperAdmin()) {
                return true;    // Grant unconditionally — skip all policy evaluation.
            }

            return null;        // Continue to policy / permission check.
        });
    }
}
