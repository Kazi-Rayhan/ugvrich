<?php

namespace App\Policies;

use App\Models\ResearchSupport;
use App\Models\User;

/**
 * Requests to the research support desk.
 *
 * A request sent from the public form belongs to nobody: it has no account
 * behind it, so only staff can see it. One sent from the portal is also the
 * researcher's own.
 */
class ResearchSupportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, ResearchSupport $support): bool
    {
        return $user->isAdmin() || ($support->user_id !== null && $user->id === $support->user_id);
    }

    public function create(User $user): bool
    {
        return true;
    }

    /** Staff answer them; the researcher does not edit a sent request. */
    public function update(User $user, ResearchSupport $support): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, ResearchSupport $support): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
