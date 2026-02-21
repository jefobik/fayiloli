<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    // public function run(): void
    // {
    //     User::factory()->count(10)->create();
    // }
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
                'name' => 'Super Administrator',
                'user_name' => 'superadmin',
                'email' => 'superadmin@nectarmetrics.com.ng',
                'phone' => '08000000001',
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
                // 'password' => Hash::make('Passw0rd!'),
                'password'=> bcrypt('passw0rd!'),
                'is_active' => true,
                'is_admin' => true,
            ]
        );
    }

    private function createAdmins(): void
    {
        for ($i = 1; $i <= 2; $i++) {
            User::updateOrCreate(
                ['user_name' => "admin$i"],
                [
                    'name' => "System Admin $i",
                    'user_name' => "admin$i",
                    'email' => "admin$i@nectarmetrics.com.ng",
                    'phone' => $this->generatePhone($i),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    // 'password' => Hash::make('Password12!'),
                    'password' => bcrypt('Password12!'),
                    'is_active' => true,
                    'is_admin' => true,
                ]
            );
        }
    }

    private function createBulkUsers(): void
    {
        $faker = fake();

        for ($i = 1; $i <= 10; $i++) {
            User::updateOrCreate(
                ['user_name' => "user$i"],
                [
                    'name' => $faker->name(),
                    'email' => "user$i@nectarmetrics.com.ng",
                    'phone' => $this->generatePhone($i + 10),
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'password' => bcrypt('Password123!'),
                    'is_active' => true,
                ]
            );
        }
    }

    private function generatePhone(int $seed): string
    {
        // Nigerian phone format: 080, 081, 070, 090, 091
        $prefixes = ['080', '081', '070', '090', '091'];
        $prefix = $prefixes[array_rand($prefixes)];

        return $prefix . str_pad($seed, 8, '0', STR_PAD_LEFT);
    }
}
