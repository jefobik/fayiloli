<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class UserManagementController extends Controller
{
    // List all users for current tenant
    public function index()
    {
        $users = User::all(); // Stancl tenancy will scope this automatically
        return view('users.index', compact('users'));
    }

    // Show user creation form
    public function create()
    {
        $roles = Role::all();
        $permissions = Permission::all();
        return view('users.create', compact('roles', 'permissions'));
    }

    // Store new user
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'roles' => 'array',
            'permissions' => 'array',
        ]);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);
        $user->syncRoles($validated['roles'] ?? []);
        $user->syncPermissions($validated['permissions'] ?? []);
        return redirect()->route('users.index')->with('success', 'User created!');
    }

    // Show user edit form
    public function edit(User $user)
    {
        $roles = Role::all();
        $permissions = Permission::all();
        return view('users.edit', compact('user', 'roles', 'permissions'));
    }

    // Update user
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'roles' => 'array',
            'permissions' => 'array',
        ]);
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);
        if ($validated['password']) {
            $user->password = $validated['password'];
            $user->save();
        }
        $user->syncRoles($validated['roles'] ?? []);
        $user->syncPermissions($validated['permissions'] ?? []);
        return redirect()->route('users.index')->with('success', 'User updated!');
    }

    // Delete user
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted!');
    }
}
