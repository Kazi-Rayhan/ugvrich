<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Researcher Portal is for researchers.
 *
 * A signed-out visitor is sent to sign in; a signed-in member of staff is sent
 * to the panel, which is their equivalent, rather than being shown an error.
 */
class EnsureResearcher
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('researcher.login'));
        }

        if (! $user->isResearcher()) {
            return redirect('/admin');
        }

        return $next($request);
    }
}
