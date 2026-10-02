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
        return $user->allowedTo('viewAny', Project::class);
    }

    public function view(?User $user, Project $project): bool
    {
        if ($project->visibility === 'public') {
            return true;
        }

        return $user !== null && ($user->allowedTo('view', Project::class) || $user->id === $project->user_id);
    }

    public function create(User $user): bool
    {
        return $user->allowedTo('create', Project::class);
    }

    public function update(User $user, Project $project): bool
    {
        return $user->allowedTo('update', Project::class);
    }

    /** Progress reported by the researcher running it. */
    public function report(User $user, Project $project): bool
    {
        return $user->id === $project->user_id;
    }

    /** The official status, which is RICH's to set. */
    public function setStatus(User $user, Project $project): bool
    {
        return $user->allowedTo('setStatus', Project::class);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->allowedTo('delete', Project::class);
    }

    public function deleteAny(User $user): bool
    {
        return $user->allowedTo('delete', Project::class);
    }
}
