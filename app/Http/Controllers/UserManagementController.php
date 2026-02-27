<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * UserManagementController — Tenant-scoped user CRUD.
 *
 * All operations run inside the tenant DB context (enforced by
 * InitializeTenancyByDomain + PreventAccessFromCentralDomains middleware on
 * routes/tenant.php).
 *
 * Uses App\Models\Role and App\Models\Permission — our UUID-aware extensions
 * of Spatie's models (HasUuids + $keyType='string').  Using Spatie's default
 * models (int PK, no HasUuids) would produce
 * "operator does not exist: uuid = integer" on PostgreSQL pivot joins.
 */
class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with('roles')->orderBy('name');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('user_name', 'ilike', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->whereHas('roles', fn ($q) => $q->where('name', $role));
        }

        if ($status = $request->input('status')) {
            match ($status) {
                'active'   => $query->where('is_active', true)->where('is_locked', false),
                'inactive' => $query->where('is_active', false),
                'locked'   => $query->where('is_locked', true),
                default    => null,
            };
        }

        $users = $query->paginate(25)->withQueryString();
        $roles = Role::orderBy('name')->get();

        // Stats for the summary bar — run against the full (unfiltered) table.
        $stats = [
            'total'    => User::count(),
            'active'   => User::where('is_active', true)->where('is_locked', false)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'locked'   => User::where('is_locked', true)->count(),
            'admin'    => User::where('is_admin', true)->count(),
        ];

        return view('users.index', compact('users', 'roles', 'stats'));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::all();
        $permissions = Permission::all();
        $supervisors = User::orderBy('name')->get();

        return view('users.create', compact('roles', 'permissions', 'supervisors'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'user_name'      => 'required|string|max:100|unique:users,user_name|alpha_dash',
            'email'          => 'required|email|max:255|unique:users,email',
            'phone'          => 'nullable|string|max:30',
            'supervisor_id'  => 'nullable|uuid|exists:users,id',
            'password'       => 'required|string|min:8|confirmed',
            'roles'          => 'array',
            'roles.*'        => 'string|exists:roles,name',
            'permissions'    => 'array',
            'permissions.*'  => 'string|exists:permissions,name',
            'is_active'      => 'boolean',
            'is_admin'       => 'boolean',
            'is_locked'      => 'boolean',
            'is_2fa_enabled' => 'boolean',
        ]);

        // Only users with 'manage roles' may set the is_admin flag.
        $canManageRoles = $request->user()->can('manage roles');
        $isAdmin = $canManageRoles && $request->boolean('is_admin');

        $user = User::create([
            'name'           => $validated['name'],
            'user_name'      => $validated['user_name'],
            'email'          => $validated['email'],
            'phone'          => $validated['phone'] ?? null,
            'supervisor_id'  => $validated['supervisor_id'] ?? null,
            'password'       => $validated['password'],
            'is_active'      => $request->boolean('is_active'),
            'is_admin'       => $isAdmin,
            'is_locked'      => $request->boolean('is_locked'),
            'is_2fa_enabled' => $request->boolean('is_2fa_enabled'),
        ]);

        if ($canManageRoles) {
            $roles = $this->resolveRoles($isAdmin, $validated['roles'] ?? []);
            $user->syncRoles($roles);
            $user->syncPermissions($validated['permissions'] ?? []);
        } else {
            // Non-role-managers always create users with the baseline 'user' role.
            $user->syncRoles(['user']);
        }

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} has been created.");
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load('roles', 'permissions', 'supervisor');

        return view('users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::all();
        $permissions = Permission::all();
        $supervisors = User::where('id', '!=', $user->id)->orderBy('name')->get();

        return view('users.edit', compact('user', 'roles', 'permissions', 'supervisors'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'user_name'      => 'required|string|max:100|alpha_dash|unique:users,user_name,' . $user->id,
            'email'          => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone'          => 'nullable|string|max:30',
            'supervisor_id'  => 'nullable|uuid|exists:users,id',
            'password'       => 'nullable|string|min:8|confirmed',
            'roles'          => 'array',
            'roles.*'        => 'string|exists:roles,name',
            'permissions'    => 'array',
            'permissions.*'  => 'string|exists:permissions,name',
            'is_active'      => 'boolean',
            'is_admin'       => 'boolean',
            'is_locked'      => 'boolean',
            'is_2fa_enabled' => 'boolean',
        ]);

        $canManageRoles = $request->user()->can('manage roles');

        // Non-role-managers cannot escalate is_admin — preserve the existing value.
        $isAdmin = $canManageRoles
            ? $request->boolean('is_admin')
            : $user->is_admin;

        $user->fill([
            'name'           => $validated['name'],
            'user_name'      => $validated['user_name'],
            'email'          => $validated['email'],
            'phone'          => $validated['phone'] ?? null,
            'supervisor_id'  => $validated['supervisor_id'] ?? null,
            'is_active'      => $request->boolean('is_active'),
            'is_admin'       => $isAdmin,
            'is_locked'      => $request->boolean('is_locked'),
            'is_2fa_enabled' => $request->boolean('is_2fa_enabled'),
        ]);

        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

        if ($canManageRoles) {
            // Full role + permission control — enforce is_admin ↔ admin role sync.
            $roles = $this->resolveRoles($isAdmin, $validated['roles'] ?? []);
            $user->syncRoles($roles);
            $user->syncPermissions($validated['permissions'] ?? []);
        } else {
            // Self-edit or limited-permission editor: preserve existing roles.
            // Only mirror the is_admin flag onto the Spatie 'admin' role.
            $this->enforceAdminRole($user, $isAdmin);
        }

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} has been updated.");
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $name = $user->name;
        $user->delete(); // Soft-deletes via SoftDeletes trait — sets deleted_at, preserves audit trail.

        return redirect()->route('users.index')
            ->with('success', "{$name} has been removed.");
    }

    /**
     * Resolve the final set of Spatie role names given the is_admin flag.
     *
     * - is_admin=true  → always includes 'admin' Spatie role; merges requested roles
     * - is_admin=false → 'admin' stripped from requested; falls back to 'user' if empty
     */
    private function resolveRoles(bool $isAdmin, array $requestedRoles): array
    {
        if ($isAdmin) {
            return array_values(array_unique(array_merge(['admin'], $requestedRoles)));
        }

        $roles = array_values(array_filter($requestedRoles, fn($r) => $r !== 'admin'));

        return empty($roles) ? ['user'] : $roles;
    }

    /**
     * Mirror the is_admin boolean onto the Spatie 'admin' role without touching
     * other roles (used when the acting user lacks 'manage roles' permission).
     */
    private function enforceAdminRole(User $user, bool $isAdmin): void
    {
        if ($isAdmin && !$user->hasRole('admin')) {
            $user->assignRole('admin');
        } elseif (!$isAdmin && $user->hasRole('admin')) {
            $user->removeRole('admin');
            // Ensure the user still has at least a baseline role after admin removal.
            if ($user->fresh()->roles->isEmpty()) {
                $user->assignRole('user');
            }
        }
    }
}
