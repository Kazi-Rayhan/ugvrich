<?php

namespace App\Support;

use App\Models\User;

/**
 * Where an account belongs once it is signed in.
 *
 * Researcher accounts use the portal, innovator accounts their dashboard;
 * every other account uses the admin dashboard. One place decides it, so signing in and signing in as somebody
 * else agree with each other.
 */
class RoleRedirector
{
    public static function pathFor(User $user): string
    {
        return match (true) {
            $user->role === User::EXPERT => route('expert.dashboard', absolute: false),
            $user->role !== User::ADMIN && $user->consultancyApplication()
                ->where('status', \App\Models\ConsultancyApplication::APPROVED)->exists() => route('expert.dashboard', absolute: false),
            $user->isResearcher() => route('researcher.dashboard', absolute: false),
            $user->isInnovator() => route('innovator.dashboard', absolute: false),
            default => '/admin',
        };
    }
}
