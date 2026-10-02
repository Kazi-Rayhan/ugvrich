<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

/**
 * Research projects.
 *
 * The official status of a project is RICH's to set, not the researcher's:
 * they report progress, staff decide what the project is. Reading is wider,
 * because a public project is public.
 */
class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(?User $user, Project $project): bool
    {
        if ($project->visibility === 'public') {
            return true;
        }

        return $user !== null && ($user->isAdmin() || $user->id === $project->user_id);
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    /** Progress reported by the researcher running it. */
    public function report(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /** The official status, which is RICH's to set. */
    public function setStatus(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isAdmin();
    }
}
