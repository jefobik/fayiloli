<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Tenant database seeder.
 *
 * Invoked by the SeedDatabase job (via config/tenancy.php seeder_parameters)
 * on every TenantCreated event — once per tenant, always executed inside the
 * tenant's own isolated PostgreSQL database connection.
 *
 * Seeding contract:
 *
 *   ALWAYS (all environments):
 *     RolesPermissionsSeeder  — RBAC bootstrap.  Every tenant requires roles
 *                               and permissions before any user can log in.
 *
 *   LOCAL / TESTING only:
 *     TenantDevDataSeeder     — Development fixtures: predictable dev users,
 *                               categories, folders, tags, and sample documents.
 *                               TenantDevDataSeeder guards itself against
 *                               production execution.
 *
 * Production workspaces start structurally complete but content-empty.
 * The tenant admin invites real users and uploads real documents through the
 * EDMS UI — fake fixture data must never appear in a live tenant workspace.
 *
 * DO NOT add stub/empty seeders to this pipeline.  Seeders with no
 * implementation belong to their feature branch, not the provisioning chain.
 */
class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Structural bootstrap (all environments) ───────────────────────────
        // RBAC must be in place before any authenticated action can succeed.
        $this->call(RolesPermissionsSeeder::class);

        // ── Development fixtures (local / testing only) ───────────────────────
        // Provides a ready-to-use workspace for local development and CI.
        // Never executes in production (TenantDevDataSeeder enforces this).
        if (app()->environment(['local', 'testing'])) {
            $this->call(TenantDevDataSeeder::class);
        }
    }
}
