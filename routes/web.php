<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TenantController;

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

// ─── Tenant Management (Super-Admin) ──────────────────────────────────────
Route::middleware('auth')->prefix('admin/tenants')->name('tenants.')->group(function () {
    Route::get('/',                         [TenantController::class, 'index'])->name('index');
    Route::get('/create',                   [TenantController::class, 'create'])->name('create');
    Route::post('/',                        [TenantController::class, 'store'])->name('store');
    Route::get('/{tenant}',                 [TenantController::class, 'show'])->name('show');
    Route::get('/{tenant}/edit',            [TenantController::class, 'edit'])->name('edit');
    Route::put('/{tenant}',                 [TenantController::class, 'update'])->name('update');
    Route::delete('/{tenant}',              [TenantController::class, 'destroy'])->name('destroy');
    Route::post('/{tenant}/domains',        [TenantController::class, 'addDomain'])->name('domains.add');
    Route::delete('/{tenant}/domains',      [TenantController::class, 'removeDomain'])->name('domains.remove');
    Route::patch('/{tenant}/toggle-active', [TenantController::class, 'toggleActive'])->name('toggle_active');
});
