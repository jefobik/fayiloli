<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Permissions ────────────────────────────────────────────────────
        $permissions = [
            // Documents
            'view documents',
            'create documents',
            'edit documents',
            'delete documents',
            'download documents',
            'share documents',
            'print documents info',
            'view document versions',
            'create document versions',
            'edit document versions',
            'delete document versions',

            // Folders
            'view folders',
            'create folders',
            'edit folders',
            'delete folders',


            // Tags
            'view tags',
            'create tags',
            'edit tags',
            'delete tags',
            'print tags info',

            // Users
            'view users',
            'invite users',
            'edit users',
            'delete users',
            'deactivate users',
            'activate users',
            'view users profile',
            'print users profile',

            // Notifications
            'view notifications',
            'dismiss notifications',

            // HRM
            'view employees',
            'edit employees',
            'delete employees',
            'print employees',

            // Stats
            'view stats',

            // Tenant admin (workspace scope only — tenant provisioning is a
            // central-domain concern handled by is_super_admin, not Spatie)
            'manage roles',
            'view audit log',
            'manage settings',
        ];

        $permissions = array_values(array_unique($permissions));

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── Roles ──────────────────────────────────────────────────────────
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions($permissions); // all permissions (deduplicated)

        // Manager role
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'view documents',
            'create documents',
            'edit documents',
            'delete documents',
            'download documents',
            'share documents',
            'view folders',
            'create folders',
            'edit folders',
            'delete folders',
            'view tags',
            'create tags',
            'edit tags',
            'view users',
            'invite users',
            'view employees',
            'print employees',
            'view stats',
            'view notifications',
            'dismiss notifications',
            'view audit log',
        ]);

        // User role
        $user = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $user->syncPermissions([
            'view documents',
            'create documents',
            'edit documents',
            'download documents',
            'share documents',
            'view folders',
            'create folders',
            'edit folders',
            'view tags',
            'view notifications',
            'dismiss notifications',
            'view employees',
            'edit employees',
            'delete employees',
            'print employees',
            'view stats',
        ]);

        // Viewer role
        $viewer = Role::firstOrCreate(['name' => 'viewer', 'guard_name' => 'web']);
        $viewer->syncPermissions([
            'view documents',
            'download documents',
            'view folders',
            'view tags',
            'view notifications',
        ]);

        // ── Assign Spatie roles to all seeded tenant users ─────────────────
        // is_super_admin / is_admin are central-DB flags; inside the tenant DB
        // we use Spatie roles to express workspace-level capabilities.
        // Mapping: central is_admin=true  → always 'admin' Spatie role (force-sync)
        //          all other users        → 'user' role if they have no role yet
        // The superadmin central user is mirrored in tenant DB as is_admin=true,
        // so they also receive the tenant 'admin' Spatie role for workspace ops.
        User::where('is_admin', true)->each(function (User $u) use ($admin): void {
            // Force-sync so that is_admin=true always reflects the admin Spatie role.
            // Existing non-admin roles (manager, viewer) are preserved alongside admin.
            if (!$u->hasRole('admin')) {
                $u->assignRole($admin);
            }
        });

        User::where('is_admin', false)->each(function (User $u) use ($user): void {
            // Only assign 'user' role if the user has no Spatie role at all.
            // Preserves manager/viewer roles assigned by tenant admins.
            if ($u->roles->isEmpty()) {
                $u->assignRole($user);
            }
        });

        $this->command?->info('Roles and permissions seeded successfully.');
        $this->command?->table(
            ['Role', 'Permission Count'],
            [
                ['admin', Permission::count()],
                ['manager', $manager->permissions()->count()],
                ['user', $user->permissions()->count()],
                ['viewer', $viewer->permissions()->count()],
            ]
        );
    }
}
