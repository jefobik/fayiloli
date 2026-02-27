<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Folder;
use App\Models\User;

class FolderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view folders');
    }

    public function view(User $user, Folder $folder): bool
    {
        return $user->can('view folders');
    }

    public function create(User $user): bool
    {
        return $user->can('create folders');
    }

    public function update(User $user, Folder $folder): bool
    {
        return $user->can('edit folders');
    }

    public function delete(User $user, Folder $folder): bool
    {
        return $user->can('delete folders');
    }

    public function restore(User $user, Folder $folder): bool
    {
        return $user->can('edit folders');
    }

    public function forceDelete(User $user, Folder $folder): bool
    {
        return $user->isAdminOrAbove();
    }

    public function download(User $user, Folder $folder): bool
    {
        return $user->can('view folders');
    }
}
