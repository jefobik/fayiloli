<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * RoleController — Tenant-scoped role & permission management.
 *
 * Gated by RolePolicy (requires 'manage roles' Spatie permission).
 * System roles (admin, manager, user, viewer) can have their permissions
 * edited but cannot be deleted. Custom roles support full CRUD.
 */
class RoleController extends Controller
{
    private const SYSTEM_ROLES = ['admin', 'manager', 'user', 'viewer'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $this->authorize('viewAny', Role::class);

        $roles = Role::withCount('permissions')
            ->with('users')
            ->orderByRaw("CASE WHEN name = 'admin' THEN 0 WHEN name = 'manager' THEN 1 WHEN name = 'user' THEN 2 WHEN name = 'viewer' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->get()
            ->map(function (Role $role) {
                $role->is_system = in_array($role->name, self::SYSTEM_ROLES, true);
                $role->users_count = $role->users->count();
                return $role;
            });

        $stats = [
            'total'       => $roles->count(),
            'system'      => $roles->where('is_system', true)->count(),
            'custom'      => $roles->where('is_system', false)->count(),
            'total_users' => $roles->sum('users_count'),
        ];

        return view('roles.index', compact('roles', 'stats'));
    }

    public function create(): View
    {
        $this->authorize('create', Role::class);

        $permissions = $this->groupedPermissions();

        return view('roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => [
                'required', 'string', 'max:100',
                'unique:roles,name',
                'regex:/^[a-z][a-z0-9_-]*$/',
                function ($attribute, $value, $fail) {
                    if (in_array($value, self::SYSTEM_ROLES, true)) {
                        $fail('That role name is reserved.');
                    }
                },
            ],
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ], [
            'name.regex' => 'Role name must start with a lowercase letter and contain only lowercase letters, digits, hyphens, or underscores.',
        ]);

        $role = Role::create(['name' => $validated['name'], 'guard_name' => 'web']);
        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.show', $role)
            ->with('success', "Role \"{$role->name}\" created successfully.");
    }

    public function show(Role $role): View
    {
        $this->authorize('view', $role);

        $role->load('permissions');
        $users = User::role($role->name)->orderBy('name')->get();
        $permissions = $this->groupedPermissions();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $isSystem = in_array($role->name, self::SYSTEM_ROLES, true);

        return view('roles.show', compact('role', 'users', 'permissions', 'rolePermissions', 'isSystem'));
    }

    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        $permissions = $this->groupedPermissions();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $isSystem = in_array($role->name, self::SYSTEM_ROLES, true);

        return view('roles.edit', compact('role', 'permissions', 'rolePermissions', 'isSystem'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $rules = ['permissions' => 'nullable|array', 'permissions.*' => 'string|exists:permissions,name'];

        // Allow renaming only for custom roles
        if (! in_array($role->name, self::SYSTEM_ROLES, true)) {
            $rules['name'] = ['required', 'string', 'max:100', 'unique:roles,name,' . $role->id, 'regex:/^[a-z][a-z0-9_-]*$/'];
        }

        $validated = $request->validate($rules);

        if (isset($validated['name'])) {
            $role->update(['name' => $validated['name']]);
        }

        $role->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('roles.show', $role)
            ->with('success', "Role \"{$role->name}\" permissions updated.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        $name = $role->name;

        // Re-assign affected users to 'user' role before deleting
        $defaultRole = Role::where('name', 'user')->first();
        if ($defaultRole) {
            foreach ($role->users as $user) {
                $user->removeRole($role);
                if ($user->roles->isEmpty()) {
                    $user->assignRole($defaultRole);
                }
            }
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', "Role \"{$name}\" deleted. Affected users were moved to the default role.");
    }

    /**
     * Return all permissions grouped by resource prefix for the UI.
     */
    private function groupedPermissions(): array
    {
        $groups = [
            'documents'     => [],
            'folders'       => [],
            'tags'          => [],
            'users'         => [],
            'notifications' => [],
            'hrm'           => [],
            'stats'         => [],
            'system'        => [],
        ];

        $resourceMap = [
            'documents'     => 'documents',
            'folders'       => 'folders',
            'tags'          => 'tags',
            'users'         => 'users',
            'notifications' => 'notifications',
            'employees'     => 'hrm',
            'stats'         => 'stats',
            'roles'         => 'system',
            'audit'         => 'system',
            'settings'      => 'system',
        ];

        Permission::orderBy('name')->each(function (Permission $permission) use (&$groups, $resourceMap) {
            $parts = explode(' ', $permission->name);
            $last  = end($parts);

            $group = $resourceMap[$last] ?? 'system';
            $groups[$group][] = $permission;
        });

        return array_filter($groups, fn ($g) => ! empty($g));
    }
}
