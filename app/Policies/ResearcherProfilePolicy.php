<?php

namespace App\Policies;

use App\Models\ResearcherProfile;
use App\Models\User;

/**
 * The researcher's own academic record.
 *
 * Theirs to write. Staff may read one — a reviewer should be able to see
 * who wrote what — but never edit it.
 */
class ResearcherProfilePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, ResearcherProfile $profile): bool
    {
        return $user->isAdmin() || $user->id === $profile->user_id;
    }

    public function create(User $user): bool
    {
        return $user->isResearcher();
    }

    public function update(User $user, ResearcherProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }

    public function delete(User $user, ResearcherProfile $profile): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
