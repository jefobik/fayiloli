<?php

declare(strict_types=1);
namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        if ($user->isAdminOrAbove())
            return true;
        return $user->hasPermissionTo('view users');
    }

    public function view(User $user, User $model)
    {
        if ($user->isAdminOrAbove())
            return true;
        return $user->hasPermissionTo('view users') || $user->id === $model->id;
    }

    public function create(User $user)
    {
        if ($user->isAdminOrAbove())
            return true;
        return $user->hasPermissionTo('invite users');
    }

    public function restore(User $user, User $model)
    {
        if ($user->isAdminOrAbove())
            return true;
        return $user->hasPermissionTo('edit users');
    }

    public function update(User $user, User $model)
    {
        if ($user->isAdminOrAbove())
            return true;
        return $user->hasPermissionTo('edit users') || $user->id === $model->id;
    }

    public function delete(User $user, User $model)
    {
        if ($user->isAdminOrAbove() && $user->id !== $model->id)
            return true;
        return $user->hasPermissionTo('delete users') && $user->id !== $model->id;
    }
}
