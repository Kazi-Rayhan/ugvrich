<?php

namespace App\Policies;

use App\Models\FinanceTransaction;
use App\Models\User;

class FinanceTransactionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->allowedTo('viewAny', FinanceTransaction::class);
    }

    public function create(User $user): bool
    {
        return $this->viewAny($user) && $user->allowedTo('create', FinanceTransaction::class);
    }

    public function reverse(User $user, FinanceTransaction $transaction): bool
    {
        return $this->viewAny($user) && $user->allowedTo('reverse', $transaction);
    }
}
