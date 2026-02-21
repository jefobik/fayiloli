<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            // Named alias used explicitly in routes/tenant.php.
            'tenant'              => \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,

            // Reject requests originating from a central domain on tenant-only routes.
            'tenant.central_deny' => \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,

            // Guard: abort 403 if tenancy has not been initialised yet.
            'tenant.initialized'  => \App\Http\Middleware\EnsureTenantInitialized::class,

            // Module access gate — usage: 'tenant.module:documents'
            'tenant.module'       => \App\Http\Middleware\EnsureModuleEnabled::class,

            // Central-domain: restrict route to platform super-admin only.
            'super-admin'         => \App\Http\Middleware\EnsureSuperAdmin::class,

            // Central-domain: require is_admin or is_super_admin for portal access.
            'central-admin'       => \App\Http\Middleware\EnsureCentralAdmin::class,
        ]);

        // Prepend InitializeTenancyByDomain to the global web middleware group.
        //
        // This lets a SINGLE set of auth routes (Auth::routes in web.php) serve
        // BOTH the central admin domain AND every tenant domain — the middleware
        // self-skips for central domains (127.0.0.1, localhost) via the
        // tenancy.central_domains config, so there is zero overhead on the
        // super-admin side.  On tenant domains it switches the DB connection
        // before any controller or auth guard runs.
        $middleware->prependToGroup(
            'web',
            \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
