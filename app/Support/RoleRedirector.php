<?php

namespace App\Support;

use App\Models\User;

/**
 * Where an account belongs once it is signed in.
 *
 * Researcher accounts use the portal; every other account uses the admin
 * dashboard. One place decides it, so signing in and signing in as somebody
 * else agree with each other.
 */
class RoleRedirector
{
    public static function pathFor(User $user): string
    {
        return $user->isResearcher()
            ? route('researcher.dashboard', absolute: false)
            : '/admin';
    }
}
