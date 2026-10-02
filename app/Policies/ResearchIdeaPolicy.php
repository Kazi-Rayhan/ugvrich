<?php

namespace App\Policies;

use App\Models\ResearchIdea;
use App\Models\User;

/**
 * Research ideas, which arrive from the Researcher Portal.
 *
 * Read-only for staff by design: an idea is the researcher's own words.
 * What staff do to it is review it, and that leaves a record of its own.
 */
class ResearchIdeaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /** Staff read everything; a researcher reads their own and nobody else's. */
    public function view(User $user, ResearchIdea $idea): bool
    {
        return $user->isAdmin() || $user->id === $idea->user_id;
    }

    /** These are written in the portal, never in the admin. */
    public function create(User $user): bool
    {
        return $user->isResearcher();
    }

    /**
     * The researcher's own words, and theirs alone to change — and only while
     * the work is back with them. Staff record decisions instead of editing.
     */
    public function update(User $user, ResearchIdea $idea): bool
    {
        return $user->id === $idea->user_id && $idea->isEditable();
    }

    /** Nothing here is deleted: the trail is the point. */
    public function delete(User $user, ResearchIdea $idea): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    /** Assigning a reviewer, starting a review, recording a decision. */
    public function review(User $user, ResearchIdea $idea): bool
    {
        return $user->isAdmin();
    }
}
