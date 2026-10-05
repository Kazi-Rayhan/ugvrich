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
            $user->isResearcher() => route('researcher.dashboard', absolute: false),
            $user->isInnovator() => route('innovator.dashboard', absolute: false),
            default => '/admin',
        };
    }
}
