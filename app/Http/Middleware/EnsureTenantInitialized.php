<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureTenantInitialized
 *
 * Guards tenant-scoped routes on the central domain by aborting with 403
 * when tenancy has not been bootstrapped.  This acts as a second line of
 * defence after InitializeTenancyByDomain has run — useful on routes that
 * may be reachable from both central and tenant contexts.
 *
 * Typical usage in web.php (central) for routes that must only ever be
 * accessed within a tenant context:
 *
 *   Route::middleware(['auth', 'tenant.initialized'])->group(...)
 */
class EnsureTenantInitialized
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! tenancy()->initialized) {
            // Redirect rather than abort — central-domain users who somehow
            // reach a tenant-only route should land on the appropriate page.
            return redirect(auth()->check() ? '/admin/tenants' : '/login');
        }

        return $next($request);
    }
}
