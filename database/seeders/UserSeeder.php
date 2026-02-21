<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds tenant users.
 *
 * Runs inside the tenant database context — never against the central DB.
 * Creates 13 predictable dev users:
 *   superadmin  / passw0rd!     (is_admin = true — workspace admin)
 *   admin1–2    / Password12!   (is_admin = true)
 *   user1–10    / Password123!
 *
 * NOTE: is_super_admin is intentionally absent — it is a central-DB-only
 * flag and does NOT exist in the tenant users table.  The platform
 * super-admin privilege is enforced at the central database level only.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->createSuperAdmin();
        $this->createAdmins();
        $this->createBulkUsers();
    }

    private function createSuperAdmin(): void
    {
        User::updateOrCreate(
            ['user_name' => 'superadmin'],
            [
                'name'              => 'Super Administrator',
                'user_name'         => 'superadmin',
                'email'             => 'superadmin@nectarmetrics.com.ng',
                'phone'             => '08000000001',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                'password'          => bcrypt('passw0rd!'),
                'is_active'         => true,
                'is_admin'          => true,
            ]
        );
    }

    private function createAdmins(): void
    {
        for ($i = 1; $i <= 2; $i++) {
            User::updateOrCreate(
                ['user_name' => "admin{$i}"],
                [
                    'name'              => "System Admin {$i}",
                    'user_name'         => "admin{$i}",
                    'email'             => "admin{$i}@nectarmetrics.com.ng",
                    'phone'             => $this->generatePhone($i),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'password'          => bcrypt('Password12!'),
                    'is_active'         => true,
                    'is_admin'          => true,
                ]
            );
        }
    }

    private function createBulkUsers(): void
    {
        $faker = fake();

        for ($i = 1; $i <= 10; $i++) {
            User::updateOrCreate(
                ['user_name' => "user{$i}"],
                [
                    'name'              => $faker->name(),
                    'user_name'         => "user{$i}",
                    'email'             => "user{$i}@nectarmetrics.com.ng",
                    'phone'             => $this->generatePhone($i + 10),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'password'          => bcrypt('Password123!'),
                    'is_active'         => true,
                ]
            );
        }
    }

    private function generatePhone(int $seed): string
    {
        $prefixes = ['080', '081', '070', '090', '091'];
        $prefix   = $prefixes[$seed % count($prefixes)];

        return $prefix . str_pad((string) $seed, 8, '0', STR_PAD_LEFT);
    }
}
