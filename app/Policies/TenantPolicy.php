<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

/**
 * TenantPolicy — authorises central-admin actions against Tenant records.
 *
 * All authenticated central-admin users share the same privilege level in the
 * current release.  This policy acts as a single authorisation gate that
 * future roles (read-only auditor, billing-admin, etc.) can extend without
 * touching controller code.
 *
 * Every method receives the authenticated User from the central database.
 * Tenancy is NOT initialised on the central domain, so tenant-DB queries
 * must not be made here.
 */
class TenantPolicy
{
    /** Any authenticated central user may list tenants. */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return true;
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->is_admin;
    }

    /** Controls access to the status transition endpoint. */
    public function transitionStatus(User $user, Tenant $tenant): bool
    {
        return true;
    }

    public function addDomain(User $user, Tenant $tenant): bool
    {
        return true;
    }

    public function removeDomain(User $user, Tenant $tenant): bool
    {
        return true;
    }
}
