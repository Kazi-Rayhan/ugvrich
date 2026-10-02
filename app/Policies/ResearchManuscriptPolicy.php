<?php

namespace App\Policies;

use App\Models\ResearchManuscript;
use App\Models\User;

/**
 * Manuscripts, from draft to publication.
 *
 * A manuscript under review is not the researcher's to change and not
 * staff's to rewrite; staff record a decision instead.
 */
class ResearchManuscriptPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /** Staff read everything; a researcher reads their own and nobody else's. */
    public function view(User $user, ResearchManuscript $manuscript): bool
    {
        return $user->isAdmin() || $user->id === $manuscript->user_id;
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
    public function update(User $user, ResearchManuscript $manuscript): bool
    {
        return $user->id === $manuscript->user_id && $manuscript->isEditable();
    }

    /** Nothing here is deleted: the trail is the point. */
    public function delete(User $user, ResearchManuscript $manuscript): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    /** Assigning a reviewer, starting a review, recording a decision. */
    public function review(User $user, ResearchManuscript $manuscript): bool
    {
        return $user->isAdmin();
    }
}
