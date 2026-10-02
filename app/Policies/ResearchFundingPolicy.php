<?php

namespace App\Policies;

use App\Models\ResearchFunding;
use App\Models\User;

/**
 * Funding decisions.
 *
 * Written by staff, read by the researcher whose proposal they are about.
 * A decision is amendable — amounts and dates change — but only by staff.
 */
class ResearchFundingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->allowedTo('viewAny', ResearchFunding::class);
    }

    public function view(User $user, ResearchFunding $funding): bool
    {
        return $user->allowedTo('view', ResearchFunding::class) || $user->id === $funding->proposal?->user_id;
    }

    public function create(User $user): bool
    {
        return $user->allowedTo('create', ResearchFunding::class);
    }

    public function update(User $user, ResearchFunding $funding): bool
    {
        return $user->allowedTo('update', ResearchFunding::class);
    }

    /* A decision recorded in error is worth removing; one already acted on is
       not, but that is a judgement for staff rather than a rule here. */
    public function delete(User $user, ResearchFunding $funding): bool
    {
        return $user->allowedTo('delete', ResearchFunding::class);
    }

    public function deleteAny(User $user): bool
    {
        return $user->allowedTo('delete', ResearchFunding::class);
    }
}
