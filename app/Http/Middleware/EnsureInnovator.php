<?php

namespace App\Http\Middleware;

use App\Support\RoleRedirector;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The innovator dashboard is for innovators.
 *
 * A signed-out visitor is sent to sign in; anyone signed in with another kind
 * of account is sent to their own dashboard rather than shown an error.
 */
class EnsureInnovator
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! $user->isInnovator()) {
            return redirect(RoleRedirector::pathFor($user));
        }

        return $next($request);
    }
}
