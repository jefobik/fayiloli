<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

class TenancyServiceProvider extends ServiceProvider
{
    // By default, no namespace is used to support the callable array syntax.
    public static string $controllerNamespace = '';

    public function events()
    {
        return [
            // Tenant events
            Events\CreatingTenant::class => [],
            Events\TenantCreated::class => [
                JobPipeline::make([
                    Jobs\CreateDatabase::class,
                    Jobs\MigrateDatabase::class,
                    Jobs\SeedDatabase::class,

                    // Your own jobs to prepare the tenant.
                    // Provision API keys, create S3 buckets, anything you want!

                ])->send(function (Events\TenantCreated $event) {
                    return $event->tenant;
                })->shouldBeQueued(false), // `false` by default, but you probably want to make this `true` for production.
            ],
            Events\SavingTenant::class => [],
            Events\TenantSaved::class => [],
            Events\UpdatingTenant::class => [],
            Events\TenantUpdated::class => [],
            Events\DeletingTenant::class => [],
            Events\TenantDeleted::class => [
                JobPipeline::make([
                    Jobs\DeleteDatabase::class,
                ])->send(function (Events\TenantDeleted $event) {
                    return $event->tenant;
                })->shouldBeQueued(false), // `false` by default, but you probably want to make this `true` for production.
            ],

            // Domain events
            Events\CreatingDomain::class => [],
            Events\DomainCreated::class => [],
            Events\SavingDomain::class => [],
            Events\DomainSaved::class => [],
            Events\UpdatingDomain::class => [],
            Events\DomainUpdated::class => [],
            Events\DeletingDomain::class => [],
            Events\DomainDeleted::class => [],

            // Database events
            Events\DatabaseCreated::class => [],
            Events\DatabaseMigrated::class => [],
            Events\DatabaseSeeded::class => [],
            Events\DatabaseRolledBack::class => [],
            Events\DatabaseDeleted::class => [],

            // Tenancy events
            Events\InitializingTenancy::class => [],
            Events\TenancyInitialized::class => [
                Listeners\BootstrapTenancy::class,
            ],

            Events\EndingTenancy::class => [],
            Events\TenancyEnded::class => [
                Listeners\RevertToCentralContext::class,
            ],

            Events\BootstrappingTenancy::class => [],
            Events\TenancyBootstrapped::class => [],
            Events\RevertingToCentralContext::class => [],
            Events\RevertedToCentralContext::class => [],

            // Resource syncing
            Events\SyncedResourceSaved::class => [
                Listeners\UpdateSyncedResource::class,
            ],

            // Fired only when a synced resource is changed in a different DB than the origin DB (to avoid infinite loops)
            Events\SyncedResourceChangedInForeignDatabase::class => [],
        ];
    }

    public function register()
    {
        //
    }

    public function boot()
    {
        $this->bootEvents();
        $this->mapRoutes();
        $this->configureTenancyMiddleware();

        $this->makeTenancyMiddlewareHighestPriority();
    }

    /**
     * Configure middleware behaviour for the global-web-group strategy.
     *
     * InitializeTenancyByDomain is prepended to the global 'web' group in
     * bootstrap/app.php so that a single set of Auth routes (in web.php) can
     * serve both the central admin domain AND every tenant domain.
     *
     * The middleware itself has NO built-in logic to skip central domains —
     * it always tries to resolve a tenant from the request host. We must
     * supply an $onFail callback that passes through silently when the host
     * is listed in tenancy.central_domains, and re-throws for all other
     * unknown domains (which would indicate a misconfigured request).
     */
    protected function configureTenancyMiddleware(): void
    {
        // ── InitializeTenancyByDomain: pass-through on central, 404 on unknown ──
        InitializeTenancyByDomain::$onFail = function ($exception, $request, $next) {
            if (in_array($request->getHost(), config('tenancy.central_domains'), true)) {
                // Central domain — tenancy is intentionally not initialized.
                // Let the request continue so that central admin + auth routes work.
                return $next($request);
            }

            // Unknown tenant domain — surface as a proper 404.
            abort(404);
        };

        // ── PreventAccessFromCentralDomains: redirect instead of aborting 404 ──
        //
        // This middleware runs BEFORE StartSession (it sits at the top of the
        // middleware priority list), so auth()->check() is unreliable here —
        // the session has not been started yet and always returns false.
        //
        // To avoid a redirect loop (unauthenticated-appearing /login → guest
        // middleware → route('home') → PreventAccessFromCentralDomains → /login
        // → …), we redirect to /admin/tenants unconditionally.  That route's
        // own auth middleware runs AFTER StartSession, so it correctly sends
        // unauthenticated visitors to /login and lets authenticated admins
        // through to the panel.
        PreventAccessFromCentralDomains::$abortRequest = function ($request, $next) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This endpoint is only accessible from a tenant domain.'], 403);
            }

            return redirect('/admin/tenants');
        };

        // ── RedirectIfAuthenticated: tenant-aware home redirect ────────────────
        //
        // The framework's defaultRedirectUri() scans registered route names for
        // 'dashboard' or 'home'.  It finds the 'home' named route from
        // tenant.php and returns route('home'), which on the central domain is
        // a URL guarded by PreventAccessFromCentralDomains — creating a loop:
        //
        //   /login (guest) → route('home') → PreventAccessFromCentralDomains
        //   → /admin/tenants → (correct) … but /login → /home → /admin/tenants
        //   needs to be direct, not via the tenant route.
        //
        // Override to use tenancy state:
        //   - Tenant domain  → '/'  (EDMS application root; RBAC and module gating
        //                            is enforced by the destination route/middleware,
        //                            not by the redirect target)
        //   - Central domain → '/admin/tenants'  (super-admin / central-admin panel)
        //
        // NOTE: LoginController::redirectTo() (POST-login) correctly sends tenant
        // users to '/home' — this callback only fires for users who are ALREADY
        // authenticated and try to hit GET /login again (e.g. back-button after
        // login).  Routing them to '/' lets the tenant's own document-index or
        // module middleware take over from there, preserving the isolated-DB flow.
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            return tenancy()->initialized ? '/' : '/admin/tenants';
        });
    }

    protected function bootEvents()
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }

                Event::listen($event, $listener);
            }
        }
    }

    protected function mapRoutes()
    {
        $this->app->booted(function () {
            if (file_exists(base_path('routes/tenant.php'))) {
                Route::namespace(static::$controllerNamespace)
                    ->group(base_path('routes/tenant.php'));
            }
        });
    }

    protected function makeTenancyMiddlewareHighestPriority()
    {
        $tenancyMiddleware = [
            // Even higher priority than the initialization middleware
            Middleware\PreventAccessFromCentralDomains::class,

            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }
}
