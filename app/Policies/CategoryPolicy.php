<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view folders');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->can('view folders');
    }

    public function create(User $user): bool
    {
        return $user->can('create folders');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->can('edit folders');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->can('edit folders');
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->can('edit folders');
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $user->isAdminOrAbove();
    }
}
