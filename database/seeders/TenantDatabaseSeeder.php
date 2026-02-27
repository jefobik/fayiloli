<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Tenant database seeder — entry point for every tenant's isolated database.
 *
 * Invoked by the SeedDatabase job (config/tenancy.php seeder_parameters) on
 * every TenantCreated event.  Always executes inside the tenant's own isolated
 * PostgreSQL database; tenancy is initialised before this seeder runs so that
 * TenantConfig maps tenant JSONB fields → Laravel config values.
 *
 * ─── Pipeline (all environments) ───────────────────────────────────────────
 *
 *   1. RolesPermissionsSeeder
 *        Creates the four system roles (admin / manager / user / viewer) and
 *        all workspace permissions.  Runs first so roles exist before any
 *        subsequent seeder attempts to assign them.
 *
 *   2. TenantAdminSeeder
 *        Creates the primary workspace admin using the tenant's admin_email
 *        (available via TenantConfig → config('mail.from.address')).
 *        Assigns the 'admin' Spatie role explicitly → grants all permissions:
 *          manage roles, invite / edit / delete / view users, manage settings…
 *        Outputs login credentials and URL to the console.
 *        Idempotent (updateOrCreate) — safe to re-run on existing tenants.
 *
 * ─── Pipeline (local / testing only) ───────────────────────────────────────
 *
 *   3. TenantDevDataSeeder
 *        Fixture users (superadmin, admin1-2, standard users) + sample
 *        categories, folders, tags, and documents for UI development.
 *        UserSeeder (inside TenantDevDataSeeder) assigns Spatie roles inline.
 *        TenantDevDataSeeder guards itself against production execution.
 *
 * ─── Re-running on existing tenants ────────────────────────────────────────
 *   php artisan tenants:seed [--tenants=<uuid>]
 *   All operations are idempotent — re-runs refresh permissions and recreate
 *   the admin account if deleted, without duplicating or wiping real data.
 */
class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. RBAC bootstrap — must run first ────────────────────────────────
        $this->call(RolesPermissionsSeeder::class);

        // ── 2. Primary workspace admin (all environments) ─────────────────────
        // Every provisioned tenant immediately has a working admin account with
        // full user-management + role-assignment capabilities.
        $this->call(TenantAdminSeeder::class);

        // ── 3. Development fixtures (local / testing only) ────────────────────
        if (app()->environment(['local', 'testing'])) {
            $this->call(TenantDevDataSeeder::class);
        }
    }
}
