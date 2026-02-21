<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Central database seeder.
 *
 * Seeds only the central (super-admin) database.  Run via:
 *   php artisan db:seed
 *
 * This seeder MUST NOT call any EDMS seeders (CategorySeeder, FolderSeeder,
 * TagSeeder, DocumentSeeder, etc.) — those tables do not exist in the central
 * database.  EDMS data is seeded per-tenant through TenantDatabaseSeeder,
 * which is invoked automatically by the SeedDatabase job when a tenant is
 * created (see config/tenancy.php → seeder_parameters).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Platform Super-Admin ───────────────────────────────────────────
        // This user exists ONLY in the central database.
        // is_super_admin = true grants unconditional Gate bypass via
        // AppServiceProvider::registerSuperAdminGate().
        User::updateOrCreate(
            ['email' => 'superadmin@nectarmetrics.com.ng'],
            [
                'name'              => 'Super Administrator',
                'user_name'         => 'superadmin',
                'phone'             => '08000000001',
                'email_verified_at' => now(),
                'password'          => bcrypt('passw0rd!'),
                'is_super_admin'    => true,
                'is_admin'          => true,    // redundant but explicit for clarity
                'is_active'         => true,
            ]
        );
    }
}
