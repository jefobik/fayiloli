<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CentralUserController extends Controller
{
    public function __construct()
    {
        // Central users use the standard 'web' guard, but we explicitly authorize actions based on Central policies
        $this->authorizeResource(User::class, 'user');
    }

    public function index(Request $request)
    {
        $query = User::orderBy('name');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%")
                    ->orWhere('user_name', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            match ($status) {
                'active' => $query->where('is_active', true)->where('is_locked', false),
                'inactive' => $query->where('is_active', false),
                'locked' => $query->where('is_locked', true),
                default => null,
            };
        }

        $users = $query->paginate(25)->withQueryString();

        $stats = [
            'total' => User::count(),
            'active' => User::where('is_active', true)->where('is_locked', false)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'locked' => User::where('is_locked', true)->count(),
            'admin' => User::where('is_admin', true)->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_name' => 'required|string|max:100|unique:users,user_name|alpha_dash',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:8|confirmed',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
            'is_super_admin' => 'boolean',
            'is_locked' => 'boolean',
            'is_2fa_enabled' => 'boolean',
        ]);

        // Only super_admins can create other super_admins
        $isSuperAdmin = $request->user()->isSuperAdmin() ? $request->boolean('is_super_admin') : false;

        $user = User::create([
            'name' => $validated['name'],
            'user_name' => $validated['user_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active'),
            'is_admin' => $request->boolean('is_admin'),
            'is_super_admin' => $isSuperAdmin,
            'is_locked' => $request->boolean('is_locked'),
            'is_2fa_enabled' => $request->boolean('is_2fa_enabled'),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Central User {$user->name} has been created.");
    }

    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'user_name' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:30',
            'password' => 'nullable|string|min:8|confirmed',
            'is_active' => 'boolean',
            'is_admin' => 'boolean',
            'is_super_admin' => 'boolean',
            'is_locked' => 'boolean',
            'is_2fa_enabled' => 'boolean',
        ]);

        $isSuperAdmin = $request->user()->isSuperAdmin() ? $request->boolean('is_super_admin') : $user->is_super_admin;

        // Prevent a user from demoting themselves from super_admin accidentally
        if ($user->id === $request->user()->id && $request->user()->isSuperAdmin() && !$request->boolean('is_super_admin')) {
            $isSuperAdmin = true;
            session()->flash('warning', 'You cannot demote yourself from Super Admin.');
        }

        $user->fill([
            'name' => $validated['name'],
            'user_name' => $validated['user_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'is_admin' => $request->boolean('is_admin'),
            'is_super_admin' => $isSuperAdmin,
            'is_locked' => $request->boolean('is_locked'),
            'is_2fa_enabled' => $request->boolean('is_2fa_enabled'),
        ]);

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', "Central User {$user->name} has been updated.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "{$name} has been removed.");
    }
}
