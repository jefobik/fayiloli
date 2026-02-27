<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

class RolePolicy
{
    /** System roles that cannot be deleted (but can have their permissions edited). */
    private const SYSTEM_ROLES = ['admin', 'manager', 'user', 'viewer'];

    public function viewAny(User $user): bool
    {
        return $user->can('manage roles');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('manage roles');
    }

    public function create(User $user): bool
    {
        return $user->can('manage roles');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('manage roles');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('manage roles')
            && ! in_array($role->name, self::SYSTEM_ROLES, true);
    }
}
