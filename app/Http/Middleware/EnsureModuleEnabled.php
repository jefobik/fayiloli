<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\TenantModule;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureModuleEnabled — Gate access to tenant route groups by module.
 *
 * Usage in routes/tenant.php:
 *   Route::middleware(['auth', 'tenant.module:documents'])->group(...)
 *
 * The middleware reads the tenant's `settings.modules` array (populated
 * when the tenant is provisioned/edited) and aborts with 403 if the
 * requested module is disabled for this organisation.
 *
 * Falls back to TenantModule::defaults() when the settings key is absent,
 * so existing tenants without an explicit module list are not locked out.
 */
class EnsureModuleEnabled
{
    public function handle(Request $request, Closure $next, string $module): Response
    {
        $tenant = tenancy()->tenant;

        // Outside a tenant context (central domain) — let it through;
        // the PreventAccessFromCentralDomains middleware handles that gate.
        if (! $tenant) {
            return $next($request);
        }

        try {
            $tenantModule = TenantModule::from($module);
        } catch (\ValueError) {
            // Unknown module identifier — fail open to avoid misconfiguration
            // locking out entire sections of the application unexpectedly.
            return $next($request);
        }

        if (! $tenant->hasModule($tenantModule)) {
            abort(403, "The \"{$tenantModule->label()}\" module is not enabled for your organisation.");
        }

        return $next($request);
    }
}
