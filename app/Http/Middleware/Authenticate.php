<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as BaseAuthenticate;
use Illuminate\Http\Request;

class Authenticate extends BaseAuthenticate
{
    /**
     * Redirect unauthenticated users to /login on the *current* domain.
     *
     * The base-class fallback calls route('login'), which generates an
     * absolute URL from APP_URL (the central domain).  That would send a
     * tenant-domain visitor to the central login page, where tenancy is not
     * initialised and LoginController::redirectTo() would bounce them to
     * /admin/tenants after a successful login.
     *
     * Using a root-relative path keeps the redirect on whichever domain
     * (central or tenant) the original request arrived on.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : '/login';
    }
}
