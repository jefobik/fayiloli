<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the primary workspace administrator account for every provisioned tenant.
 *
 * ─── Contract ──────────────────────────────────────────────────────────────
 * • Runs in ALL environments (local, staging, production) as part of the
 *   TenantDatabaseSeeder pipeline — called AFTER RolesPermissionsSeeder so
 *   that the 'admin' Spatie role already exists when this seeder runs.
 *
 * • Creates (or refreshes) exactly one admin account using the tenant's own
 *   admin_email.  That address is surfaced via TenantConfig::$storageToConfigMap
 *   → config('mail.from.address') when tenancy is initialised (which it always
 *   is inside tenants:seed).  Direct tenant fallback used if config is empty.
 *
 * • Idempotent: updateOrCreate keyed on email — safe to re-run.
 *
 * ─── Permissions granted ───────────────────────────────────────────────────
 * The 'admin' Spatie role (assigned here explicitly) carries ALL workspace
 * permissions seeded by RolesPermissionsSeeder, including:
 *   manage roles    → create / edit / delete workspace roles
 *   invite users    → add new workspace members
 *   edit users      → update user profiles and account flags
 *   delete users    → soft-delete workspace members
 *   view users      → access the User Management module
 *   deactivate / activate users
 *   manage settings / view audit log / view stats / …
 *
 * ─── Login credentials ─────────────────────────────────────────────────────
 *   Email    : <tenant admin_email>          (set during provisioning)
 *   Username : local-part of admin_email     (e.g. "admin" for admin@org.local)
 *   Password : env('TENANT_ADMIN_DEFAULT_PASSWORD')  → fallback "ChangeMe123!"
 *              Change this after first login!
 *
 * ─── Local development ─────────────────────────────────────────────────────
 * In local / testing environments TenantDevDataSeeder also runs and creates
 * additional fixture users (superadmin, admin1, admin2, standard users).
 * Their credentials are managed by UserSeeder.  There is no email overlap
 * as those use the central domain (e.g. superadmin@fcta.gov.local) while
 * TenantAdminSeeder uses the tenant-specific admin_email.
 */
class TenantAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Resolve admin_email: TenantConfig maps tenant.admin_email → config('mail.from.address')
        // when tenancy is initialised (standard during tenants:seed).
        // Fall back to direct tenant access for robustness.
        $tenant     = tenancy()->tenant;
        $adminEmail = config('mail.from.address') ?: ($tenant?->admin_email ?? null);

        if (!$adminEmail) {
            $this->command?->warn(
                '  TenantAdminSeeder: admin_email not set on tenant — workspace admin was NOT created.'
            );
            return;
        }

        $orgName  = config('app.name', $tenant?->organization_name ?? 'Workspace');

        // Derive a clean username from the email local-part.
        $localPart = explode('@', $adminEmail)[0];
        $username  = strtolower(preg_replace('/[^a-z0-9_]/', '_', $localPart));

        $password = env('TENANT_ADMIN_DEFAULT_PASSWORD', 'P@ssword123!');

        $user = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name'              => 'Workspace Administrator',
                'user_name'         => $username,
                'phone'             => null,
                'email_verified_at' => now(),
                'password'          => $password,
                'is_active'         => true,
                'is_admin'          => true,
                'is_locked'         => false,
                'is_2fa_enabled'    => false,
            ]
        );

        // Explicitly assign the 'admin' Spatie role so all workspace permissions
        // are granted immediately — without needing a second RolesPermissionsSeeder pass.
        if (!$user->hasRole('admin')) {
            $user->assignRole('admin');
        }

        $domain = $tenant?->domains()->first()?->domain ?? 'tenant-domain';

        $this->command?->info("  ┌─ Workspace admin created ─────────────────────────────────");
        $this->command?->info("  │  Organisation : {$orgName}");
        $this->command?->info("  │  Login URL    : http://{$domain}/login");
        $this->command?->info("  │  Email        : {$adminEmail}");
        $this->command?->info("  │  Username     : {$username}");
        $this->command?->info("  │  Password     : {$password}");
        $this->command?->info("  │  Role         : admin (all workspace permissions)");
        $this->command?->info("  └───────────────────────────────────────────────────────────");
        $this->command?->warn("  ⚠  Change the default password after first login!");
    }
}
