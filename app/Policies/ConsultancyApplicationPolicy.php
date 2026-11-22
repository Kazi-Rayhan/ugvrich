<?php

namespace App\Policies;

use App\Models\ConsultancyApplication as Application;
use App\Models\User;

class ConsultancyApplicationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === User::ADMIN || $user->allowedTo('viewAny', Application::class)
            || $user->consultancyReviewStages()->exists();
    }

    public function view(User $user, Application $application): bool
    {
        return $this->viewAny($user);
    }

    public function review(User $user, Application $application): bool
    {
        return $application->canReview($user);
    }

    public function manageReviewers(User $user): bool
    {
        return $user->allowedTo('manageReviewers', Application::class);
    }

    public function viewEngagement(User $user): bool
    {
        return $this->assignEngagement($user) || $user->allowedTo('viewEngagement', Application::class);
    }

    public function assignEngagement(User $user): bool
    {
        return $user->allowedTo('assignEngagement', Application::class)
            || $user->consultancyReviewStages()->where('key', Application::DIRECTOR)->exists();
    }

    public function viewWallet(User $user): bool
    {
        return $user->allowedTo('viewWallet', Application::class);
    }
}
