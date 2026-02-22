<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Central database seeder.
 *
 * STRICT CONTRACT — this seeder is scoped to the central database only:
 *   • Seeds the platform super-admin account (always, all environments).
 *   • Provisions sample development tenants (local/testing environments only).
 *   • MUST NOT call EDMS or tenant-scoped seeders directly.
 *
 * Tenant data is seeded automatically and in isolation during tenant
 * provisioning via the TenantCreated event pipeline:
 *   TenantCreated → CreateDatabase → MigrateDatabase → SeedDatabase
 *       → TenantDatabaseSeeder (runs inside the tenant's own database)
 *
 * In production, tenant provisioning is performed through the admin portal
 * (TenantController::store()) — never through this seeder.
 *
 * Usage:
 *   php artisan db:seed                   — additive, safe to re-run
 *   php artisan migrate:fresh --seed      — full central rebuild + seed
 *   php artisan db:seed --class=SampleTenantSeeder  — re-provision dev tenants only
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSuperAdmin();

        // Local and test environments: provision representative sample tenants
        // so developers have a working EDMS workspace immediately after setup.
        // SampleTenantSeeder guards itself against production execution.
        if (app()->environment(['local', 'testing'])) {
            $this->call(SampleTenantSeeder::class);
        }
    }

    // ── Central super-admin ───────────────────────────────────────────────────

    /**
     * Create or refresh the platform super-admin account.
     *
     * is_super_admin grants an unconditional Gate::before() bypass defined in
     * AppServiceProvider.  This account exists ONLY in the central database —
     * the tenant users table has no is_super_admin column.
     */
    private function seedSuperAdmin(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@nectarmetrics.com.ng'],
            [
                'name'              => 'Super Administrator',
                'user_name'         => 'superadmin',
                'phone'             => '08000000001',
                'email_verified_at' => now(),
                'password'          => bcrypt('passw0rd!'),
                'is_super_admin'    => true,
                'is_admin'          => true,
                'is_active'         => true,
            ]
        );
    }
}
