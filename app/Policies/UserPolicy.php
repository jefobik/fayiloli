<?php
namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view users');
    }

    public function view(User $user, User $model)
    {
        return $user->hasPermissionTo('view users') || $user->id === $model->id;
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('create users');
    }

    public function update(User $user, User $model)
    {
        return $user->hasPermissionTo('edit users') || $user->id === $model->id;
    }

    public function delete(User $user, User $model)
    {
        return $user->hasPermissionTo('delete users') && $user->id !== $model->id;
    }
}
