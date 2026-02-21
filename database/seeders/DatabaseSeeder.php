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
        // Create the platform super-admin account.
        // This user exists only in the central database.
        User::updateOrCreate(
            ['email' => 'superadmin@nectarmetrics.com.ng'],
            [
                'name'               => 'Super Administrator',
                'user_name'          => 'superadmin',
                'email'              => 'superadmin@nectarmetrics.com.ng',
                'phone'              => '08000000001',
                'email_verified_at'  => now(),
                'phone_verified_at'  => now(),
                'password'           => bcrypt('passw0rd!'),
                'is_active'          => true,
                'is_admin'           => true,
            ]
        );
    }
}
