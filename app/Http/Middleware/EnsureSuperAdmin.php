<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureSuperAdmin — Route guard for super-admin-only endpoints.
 *
 * Aborts with 403 unless the authenticated user has `is_super_admin = true`.
 * This middleware complements the Gate::before() bypass: while the Gate
 * bypass grants abilities globally, this middleware explicitly protects
 * routes (e.g. admin user management) that only the platform owner should
 * ever reach, making the restriction visible at the routing layer.
 *
 * Usage in routes/web.php:
 *   Route::middleware(['auth', 'super-admin'])->group(...)
 */
class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isSuperAdmin()) {
            abort(403, 'This area is restricted to the platform super-administrator.');
        }

        return $next($request);
    }
}
