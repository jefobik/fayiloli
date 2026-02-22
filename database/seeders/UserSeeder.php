<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds 13 deterministic dev users into the current tenant database.
 *
 * Runs inside the tenant database context — never against the central DB.
 * All users are fully deterministic (no faker/random data) to guarantee
 * stable test fixtures and reproducible debugging sessions across re-runs.
 *
 * User roster:
 *   superadmin   / passw0rd!    is_admin=true  — mirrors the central super-admin
 *   admin1–2     / Password12!  is_admin=true  — workspace admin accounts
 *   user1–10     / Password123! is_admin=false — standard workspace users
 *
 * NOTE: is_super_admin is a central-DB-only flag and does NOT exist in the
 * tenant users table.  Workspace authority is expressed via Spatie roles
 * (admin, manager, user, viewer) assigned by RolesPermissionsSeeder.
 */
class UserSeeder extends Seeder
{
    /**
     * Deterministic user definitions.
     * Key: user_name (unique, stable identity for updateOrCreate).
     *
     * @var array<int, array{user_name: string, name: string, phone: string, is_admin: bool, password: string}>
     */
    private const USERS = [
        // ── Workspace admins ──────────────────────────────────────────────────
        ['user_name' => 'superadmin', 'name' => 'Super Administrator', 'phone' => '08000000001', 'is_admin' => true,  'password' => 'passw0rd!'],
        ['user_name' => 'admin1',     'name' => 'System Admin One',    'phone' => '08100000001', 'is_admin' => true,  'password' => 'Password12!'],
        ['user_name' => 'admin2',     'name' => 'System Admin Two',    'phone' => '08100000002', 'is_admin' => true,  'password' => 'Password12!'],

        // ── Standard users (user1–10) ─────────────────────────────────────────
        ['user_name' => 'user1',  'name' => 'Aisha Bello',         'phone' => '08000000011', 'is_admin' => false, 'password' => 'Password123!'],
        ['user_name' => 'user2',  'name' => 'Emeka Okafor',        'phone' => '08100000012', 'is_admin' => false, 'password' => 'Password123!'],
        ['user_name' => 'user3',  'name' => 'Fatima Aliyu',        'phone' => '07000000013', 'is_admin' => false, 'password' => 'Password123!'],
        ['user_name' => 'user4',  'name' => 'Chidi Nwosu',         'phone' => '09000000014', 'is_admin' => false, 'password' => 'Password123!'],
        ['user_name' => 'user5',  'name' => 'Ngozi Eze',           'phone' => '09100000015', 'is_admin' => false, 'password' => 'Password123!'],
        ['user_name' => 'user6',  'name' => 'Ibrahim Musa',        'phone' => '08000000016', 'is_admin' => false, 'password' => 'Password123!'],
        ['user_name' => 'user7',  'name' => 'Chinwe Obi',          'phone' => '08100000017', 'is_admin' => false, 'password' => 'Password123!'],
        ['user_name' => 'user8',  'name' => 'Yusuf Hassan',        'phone' => '07000000018', 'is_admin' => false, 'password' => 'Password123!'],
        ['user_name' => 'user9',  'name' => 'Adaeze Nwankwo',      'phone' => '09000000019', 'is_admin' => false, 'password' => 'Password123!'],
        ['user_name' => 'user10', 'name' => 'Oluwaseun Adeyemi',   'phone' => '09100000020', 'is_admin' => false, 'password' => 'Password123!'],
    ];

    public function run(): void
    {
        foreach (self::USERS as $data) {
            User::updateOrCreate(
                ['user_name' => $data['user_name']],
                [
                    'name'              => $data['name'],
                    'user_name'         => $data['user_name'],
                    'email'             => "{$data['user_name']}@nectarmetrics.com.ng",
                    'phone'             => $data['phone'],
                    'email_verified_at' => now(),
                    'phone_verified_at' => now(),
                    'password'          => bcrypt($data['password']),
                    'is_active'         => true,
                    'is_admin'          => $data['is_admin'],
                ]
            );
        }

        $this->command?->line('  ' . count(self::USERS) . ' tenant users seeded.');
    }
}
