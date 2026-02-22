<?php

use App\Http\Controllers\PortalController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ═══════════════════════════════════════════════════════════════════════════
//  CENTRAL-DOMAIN ROUTES  (localhost / 127.0.0.1)
//
//  This file contains ONLY routes that belong to the central (super-admin)
//  domain.  No EDMS application routes live here — those are served
//  exclusively through routes/tenant.php under a tenant domain.
//
//  Route name collision rule: NEVER register the same ->name() here as in
//  routes/tenant.php.  Duplicate names cause the last-registered route to
//  silently overwrite the first in route:cache, breaking one of the two
//  contexts every time the cache is rebuilt.
// ═══════════════════════════════════════════════════════════════════════════

// ─── Central root redirect ────────────────────────────────────────────────
//  Authenticated admins/super-admins   → tenant management portal
//  Everyone else (guests + non-admins) → organisation discovery portal
Route::get('/', function () {
    if (auth()->check() && auth()->user()->isAdminOrAbove()) {
        return redirect('/admin/tenants');
    }
    return redirect('/portal');
});

// ─── Organisation Discovery Portal (public) ──────────────────────────────
//  Renders the "Find Your Organisation" page.  No auth required.
//  Only ACTIVE tenants are shown; signing in at an org link initialises
//  tenancy on that subdomain and authenticates against the tenant DB.
Route::get('/portal', PortalController::class)->name('portal.discover');

// ─── Authentication (shared — central admin + tenant users) ──────────────
//  A SINGLE set of auth routes serves both the super-admin portal and every
//  tenant domain.  InitializeTenancyByDomain (prepended to the web group in
//  bootstrap/app.php) self-skips on central domains and switches the DB
//  connection on tenant domains before the auth guard runs, so credentials
//  are always validated against the correct database.
//
//  Registration is disabled here; tenant users are provisioned via the
//  admin portal or by a tenant-side admin account.
Auth::routes(['register' => false]);

// ─── Central Admin Portal ─────────────────────────────────────────────────
//  Stack: auth (must be logged in) → central-admin (must be is_admin or
//  is_super_admin) → authorizeResource (TenantPolicy, bypassed for
//  super-admins via Gate::before() in AppServiceProvider).
Route::middleware(['auth', 'central-admin'])
    ->prefix('admin/tenants')
    ->name('tenants.')
    ->group(function () {

        // ── Provisioning (super-admin only) ───────────────────────────────
        //  Double-gated: 'super-admin' middleware blocks at the routing layer;
        //  TenantPolicy::create() returns false for non-super-admins as a
        //  defence-in-depth backstop via authorizeResource().
        //  MUST be defined before /{tenant} to prevent "create" being resolved as a UUID.
        Route::get('/create',  [TenantController::class, 'create'])->name('create')->middleware('super-admin');
        Route::post('/',       [TenantController::class, 'store'])->name('store')->middleware('super-admin');

        // ── Read + update (any central admin) ─────────────────────────────
        Route::get('/',              [TenantController::class, 'index'])->name('index');
        Route::get('/{tenant}',      [TenantController::class, 'show'])->name('show');
        Route::get('/{tenant}/edit', [TenantController::class, 'edit'])->name('edit');
        Route::put('/{tenant}',      [TenantController::class, 'update'])->name('update');

        // ── Deletion (super-admin only — triple-gated) ────────────────────
        //  Gate 1: 'super-admin' middleware (EnsureSuperAdmin) — routing layer.
        //  Gate 2: authorizeResource() → TenantPolicy::delete() — policy layer.
        //  Gate 3: abort_unless($user->isSuperAdmin()) inside destroy() — method layer.
        Route::delete('/{tenant}', [TenantController::class, 'destroy'])->name('destroy')->middleware('super-admin');

        // ── Domain management (any central admin) ─────────────────────────
        Route::post('/{tenant}/domains',   [TenantController::class, 'addDomain'])->name('domains.add');
        Route::delete('/{tenant}/domains', [TenantController::class, 'removeDomain'])->name('domains.remove');

        // ── Status lifecycle (any central admin) ──────────────────────────
        //  Target status is validated against the state machine in
        //  TransitionTenantStatusRequest before the controller executes.
        Route::patch('/{tenant}/status', [TenantController::class, 'transitionStatus'])->name('transition_status');
    });
