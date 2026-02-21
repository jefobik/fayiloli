<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureCentralAdmin — Central admin portal access guard.
 *
 * Aborts with 403 unless the authenticated user has at least
 * `is_admin = true` (or `is_super_admin = true`, which implicitly
 * satisfies this check via User::isAdminOrAbove()).
 *
 * Applied to the entire `/admin/tenants` route group in routes/web.php
 * so that an authenticated-but-not-admin tenant user who somehow hits
 * a central URL gets a clean 403 rather than an authorisation exception
 * from TenantPolicy.
 *
 * The 'auth' middleware must run before this one (it is listed first
 * in the route group middleware stack).
 */
class EnsureCentralAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdminOrAbove()) {
            abort(403, 'Central admin portal access is restricted to administrators.');
        }

        return $next($request);
    }
}
