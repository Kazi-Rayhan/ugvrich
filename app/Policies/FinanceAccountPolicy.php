<?php

namespace App\Policies;

use App\Models\FinanceAccount;
use App\Models\User;

class FinanceAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->allowedTo('viewAny', FinanceAccount::class);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user) && $user->allowedTo('create', FinanceAccount::class);
    }

    public function update(User $user, FinanceAccount $account): bool
    {
        return $this->viewAny($user) && $user->allowedTo('update', $account);
    }

    public function delete(User $user, FinanceAccount $account): bool
    {
        return false;
    }
}
