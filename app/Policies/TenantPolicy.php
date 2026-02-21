<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

/**
 * TenantPolicy — Authorises central-admin actions against Tenant records.
 *
 * Role hierarchy (evaluated AFTER Gate::before() in AppServiceProvider):
 *
 *   Super Admin (is_super_admin = true)
 *     → Gate::before() returns true and short-circuits ALL policy methods.
 *       None of the methods below are evaluated for super-admins at runtime;
 *       they document intent and are reached only by non-super-admins.
 *
 *   Admin (is_admin = true, is_super_admin = false)
 *     → May: list, view, update tenants; transition status;
 *            add and remove domains.
 *     → May NOT: create or delete tenants (super-admin only for both).
 *
 *   Unauthenticated / no flags
 *     → All methods return false.  The 'auth' middleware on the route
 *       group catches unauthenticated users before the policy runs.
 *
 * Every method receives the authenticated User from the CENTRAL database.
 * Tenancy is NOT initialised on the central domain, so tenant-DB queries
 * must never be made inside this policy.
 */
class TenantPolicy
{
    /** List all tenants — any admin-level user. */
    public function viewAny(User $user): bool
    {
        return $user->isAdminOrAbove();
    }

    /** View a single tenant — any admin-level user. */
    public function view(User $user, Tenant $tenant): bool
    {
        return $user->isAdminOrAbove();
    }

    /**
     * Provision a new tenant — super-admin only.
     *
     * Tenant provisioning creates a new PostgreSQL database, runs all
     * tenant migrations, and seeds default data.  This is a high-impact,
     * irreversible infrastructure operation restricted to the platform
     * super-administrator.
     *
     * Gate::before() grants super-admins unconditionally — this method
     * is only evaluated for non-super-admins, where it returns false.
     * The create/store routes also carry the 'super-admin' middleware as
     * a belt-and-suspenders routing guard (see routes/web.php).
     */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    /** Update tenant metadata — any admin-level user. */
    public function update(User $user, Tenant $tenant): bool
    {
        return $user->isAdminOrAbove();
    }

    /**
     * Permanently delete a tenant and drop its database — super-admin only.
     *
     * This is the highest-blast-radius operation in the platform: it drops
     * the tenant's PostgreSQL database, destroys all EDMS data, and removes
     * all domain registrations.  It is irrecoverable.
     *
     * Three independent guards must all pass before the database is touched:
     *   1. Route middleware 'super-admin' (EnsureSuperAdmin) — HTTP layer.
     *   2. Gate::before() → grants true for super-admin; non-super-admins
     *      fall through to this method which returns false → 403.
     *   3. TenantController::destroy() explicit abort_unless() — final
     *      belt-and-suspenders check before $tenant->delete() is called.
     *
     * Gate::before() short-circuits for super-admins, so this method is
     * only ever reached by non-super-admins at runtime, returning false.
     */
    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->isSuperAdmin();
    }

    /** Transition the tenant lifecycle status — any admin-level user. */
    public function transitionStatus(User $user, Tenant $tenant): bool
    {
        return $user->isAdminOrAbove();
    }

    /** Attach a domain to a tenant — any admin-level user. */
    public function addDomain(User $user, Tenant $tenant): bool
    {
        return $user->isAdminOrAbove();
    }

    /** Remove a domain from a tenant — any admin-level user. */
    public function removeDomain(User $user, Tenant $tenant): bool
    {
        return $user->isAdminOrAbove();
    }
}
