<?php

namespace App\Policies;

use App\Models\User;

/**
 * Dashboard accounts.
 *
 * Managing people is the sharpest permission there is — whoever holds it can
 * grant themselves anything else — so it is deliberately not part of the
 * Research Officer or Reviewer roles.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->allowedTo('viewAny', User::class);
    }

    public function view(User $user, User $subject): bool
    {
        return $user->allowedTo('view', User::class) || $user->is($subject);
    }

    public function create(User $user): bool
    {
        return $user->allowedTo('create', User::class);
    }

    public function update(User $user, User $subject): bool
    {
        return $user->allowedTo('update', User::class);
    }

    /**
     * Sign in as this person, to see what they see.
     *
     * Never yourself, never while already signed in as somebody else, and only
     * with the permission for it — which the Administrator role alone holds.
     */
    public function impersonate(User $user, User $subject): bool
    {
        if ($user->is($subject) || \App\Support\Impersonation::active()) {
            return false;
        }

        return $user->allowedTo('impersonate', User::class);
    }

    /** Nobody deletes the account they are signed in with. */
    public function delete(User $user, User $subject): bool
    {
        return $user->allowedTo('delete', User::class) && ! $user->is($subject);
    }

    public function deleteAny(User $user): bool
    {
        return $user->allowedTo('delete', User::class);
    }
}
