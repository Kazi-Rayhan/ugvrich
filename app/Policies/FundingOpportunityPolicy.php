<?php

namespace App\Policies;

use App\Models\FundingOpportunity;
use App\Models\User;

/**
 * Funding calls published to the portal.
 *
 * Staff write them; any signed-in researcher reads the ones that are live.
 */
class FundingOpportunityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->allowedTo('viewAny', FundingOpportunity::class);
    }

    public function view(User $user, FundingOpportunity $opportunity): bool
    {
        return $user->allowedTo('view', FundingOpportunity::class) || (bool) $opportunity->is_active;
    }

    public function create(User $user): bool
    {
        return $user->allowedTo('create', FundingOpportunity::class);
    }

    public function update(User $user, FundingOpportunity $opportunity): bool
    {
        return $user->allowedTo('update', FundingOpportunity::class);
    }

    public function delete(User $user, FundingOpportunity $opportunity): bool
    {
        return $user->allowedTo('delete', FundingOpportunity::class);
    }

    public function deleteAny(User $user): bool
    {
        return $user->allowedTo('delete', FundingOpportunity::class);
    }
}
