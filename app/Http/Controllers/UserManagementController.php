<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->paginate(25);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $roles       = Role::all();
        $permissions = Permission::all();
        $supervisors = User::orderBy('name')->get();
        return view('users.create', compact('roles', 'permissions', 'supervisors'));
    }

    public function store(Request $request)
    {
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

        $user = User::create([
            'name'           => $validated['name'],
            'user_name'      => $validated['user_name'],
            'email'          => $validated['email'],
            'phone'          => $validated['phone'] ?? null,
            'supervisor_id'  => $validated['supervisor_id'] ?? null,
            'password'       => $validated['password'],
            'is_active'      => $request->boolean('is_active'),
            'is_admin'       => $request->boolean('is_admin'),
            'is_locked'      => $request->boolean('is_locked'),
            'is_2fa_enabled' => $request->boolean('is_2fa_enabled'),
        ]);

        $user->syncRoles($validated['roles'] ?? []);
        $user->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} has been created.");
    }

    public function edit(User $user)
    {
        $roles       = Role::all();
        $permissions = Permission::all();
        $supervisors = User::where('id', '!=', $user->id)->orderBy('name')->get();
        return view('users.edit', compact('user', 'roles', 'permissions', 'supervisors'));
    }

    public function update(Request $request, User $user)
    {
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

        $user->fill([
            'name'           => $validated['name'],
            'user_name'      => $validated['user_name'],
            'email'          => $validated['email'],
            'phone'          => $validated['phone'] ?? null,
            'supervisor_id'  => $validated['supervisor_id'] ?? null,
            'is_active'      => $request->boolean('is_active'),
            'is_admin'       => $request->boolean('is_admin'),
            'is_locked'      => $request->boolean('is_locked'),
            'is_2fa_enabled' => $request->boolean('is_2fa_enabled'),
        ]);

        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();
        $user->syncRoles($validated['roles'] ?? []);
        $user->syncPermissions($validated['permissions'] ?? []);

        return redirect()->route('users.index')
            ->with('success', "User {$user->name} has been updated.");
    }

    public function destroy(User $user)
    {
        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "{$name} has been removed.");
    }
}
