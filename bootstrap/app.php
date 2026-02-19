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
            // Initialise tenancy from the request hostname → domains table lookup.
            // Applied on every route inside routes/tenant.php.
            'tenant'              => \Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class,

            // Reject requests originating from a central domain on tenant routes.
            'tenant.central_deny' => \Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains::class,

            // Guard: abort 403 if tenancy has not been initialised yet.
            'tenant.initialized'  => \App\Http\Middleware\EnsureTenantInitialized::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
