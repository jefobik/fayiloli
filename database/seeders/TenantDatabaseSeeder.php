<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Tenant database seeder.
 *
 * Called by the SeedDatabase job (via config/tenancy.php seeder_parameters)
 * whenever a new tenant is provisioned.  Every class listed here runs inside
 * the tenant's own database connection — never against the central database.
 *
 * Central database seeding (super-admin user, etc.) is handled separately
 * by DatabaseSeeder, which is invoked only via `php artisan db:seed`.
 */
class TenantDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RolesPermissionsSeeder::class,
            CategorySeeder::class,
            FolderSeeder::class,
            TagSeeder::class,
            DocumentSeeder::class,
            ShareDocumentSeeder::class,
            FileRequestSeeder::class,
            NotificationSeeder::class,
        ]);
    }
}
