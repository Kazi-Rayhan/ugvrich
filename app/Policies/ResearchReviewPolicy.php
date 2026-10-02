<?php

namespace App\Policies;

use App\Models\ResearchReview;
use App\Models\User;

/**
 * The review trail.
 *
 * Append-only: a review is a record of what somebody decided and when, so
 * nothing here may be edited or removed, by anybody.
 */
class ResearchReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->allowedTo('viewAny', ResearchReview::class);
    }

    public function view(User $user, ResearchReview $review): bool
    {
        return $user->allowedTo('view', ResearchReview::class) || $user->id === $review->reviewable?->user_id;
    }

    public function create(User $user): bool
    {
        return $user->allowedTo('create', ResearchReview::class);
    }

    public function update(User $user, ResearchReview $review): bool
    {
        return false;
    }

    public function delete(User $user, ResearchReview $review): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
