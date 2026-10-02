<?php

namespace App\Policies;

use App\Models\Permission;
use App\Models\User;

/**
 * Permissions.
 *
 * Read-only for everybody: a permission exists because a policy asks about it,
 * so the list comes from the seeder rather than from this screen. Writing one
 * here would give a name that nothing ever checks.
 */
class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->allowedTo('viewAny', Permission::class);
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->allowedTo('view', Permission::class);
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Permission $permission): bool
    {
        return false;
    }

    public function delete(User $user, Permission $permission): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
