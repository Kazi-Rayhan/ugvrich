<?php

namespace App\Policies;

use App\Models\ResearchProposal;
use App\Models\User;

/**
 * Research proposals.
 *
 * The same rules as ideas: the researcher owns the text, staff own the
 * decisions. A proposal under review is nobody's to edit.
 */
class ResearchProposalPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->allowedTo('viewAny', ResearchProposal::class);
    }

    /** Staff read everything; a researcher reads their own and nobody else's. */
    public function view(User $user, ResearchProposal $proposal): bool
    {
        return $user->allowedTo('view', ResearchProposal::class) || $user->id === $proposal->user_id;
    }

    /** These are written in the portal, never in the admin. */
    public function create(User $user): bool
    {
        return $user->isResearcher();
    }

    /**
     * The researcher may change their own while it is back with them.
     *
     * Staff may also edit a proposal, which ideas and manuscripts do not allow:
     * the admin has had an edit screen for it since before the portal existed,
     * and proposals arrive from the public form too, where there is no account
     * to send them back to.
     */
    public function update(User $user, ResearchProposal $proposal): bool
    {
        if ($user->allowedTo('update', ResearchProposal::class)) {
            return true;
        }

        return $user->id === $proposal->user_id && $proposal->isEditable();
    }

    public function delete(User $user, ResearchProposal $proposal): bool
    {
        return $user->allowedTo('delete', ResearchProposal::class);
    }

    public function deleteAny(User $user): bool
    {
        return $user->allowedTo('delete', ResearchProposal::class);
    }

    /** Assigning a reviewer, starting a review, recording a decision. */
    public function review(User $user, ResearchProposal $proposal): bool
    {
        return $user->allowedTo('review', ResearchProposal::class);
    }
}
