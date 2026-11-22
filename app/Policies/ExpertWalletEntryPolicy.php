<?php

namespace App\Policies;

use App\Models\ExpertWalletEntry;
use App\Models\User;

class ExpertWalletEntryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->allowedTo('viewAny', ExpertWalletEntry::class);
    }

    public function viewDestination(User $user, ExpertWalletEntry $entry): bool
    {
        return $this->viewAny($user) && $user->allowedTo('viewDestination', ExpertWalletEntry::class);
    }

    public function review(User $user, ExpertWalletEntry $entry): bool
    {
        return $this->viewAny($user) && $user->allowedTo('review', ExpertWalletEntry::class);
    }

    public function markPaid(User $user, ExpertWalletEntry $entry): bool
    {
        return $this->viewDestination($user, $entry) && $user->allowedTo('markPaid', ExpertWalletEntry::class);
    }
}
