<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;

/**
 * Roles.
 *
 * Editing a role changes what everybody carrying it may do, so this sits with
 * account management rather than with the research work.
 */
class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->allowedTo('viewAny', Role::class);
    }

    public function view(User $user, Role $role): bool
    {
        return $user->allowedTo('view', Role::class);
    }

    public function create(User $user): bool
    {
        return $user->allowedTo('create', Role::class);
    }

    /**
     * The Administrator role opens like any other, so it can be read, but its
     * permissions are the seeder's: the screen shows them disabled and the page
     * skips them on save.
     */
    public function update(User $user, Role $role): bool
    {
        return $user->allowedTo('update', Role::class);
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->allowedTo('delete', Role::class) && ! $role->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->allowedTo('delete', Role::class);
    }
}
