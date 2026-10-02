<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Impersonation;
use App\Support\RoleRedirector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Signing in as another account, to see the site as they see it.
 *
 * A support tool: a researcher writes in to say a page is wrong, and the fastest
 * answer is to look at it from their side rather than to guess from the data.
 *
 * Two rules keep it honest. It is written to the log both ways, so there is a
 * record of who looked at whose account; and it can only be started by somebody
 * holding the permission for it, which is the Administrator role alone.
 */
class ImpersonationController extends Controller
{
    public function start(Request $request, User $user)
    {
        $impersonator = $request->user();

        abort_unless($impersonator?->can('impersonate', $user), 403);

        Log::info('Signed in as another account', [
            'by' => $impersonator->id,
            'as' => $user->id,
            'ip' => $request->ip(),
        ]);

        Impersonation::begin($impersonator, $user);

        return redirect(RoleRedirector::pathFor($user))
            ->with('saved', __('Signed in as :name. Use “Back to my account” when you are done.', ['name' => $user->name]));
    }

    public function stop(Request $request)
    {
        if (! $request->user()) {
            return response()->view('researcher.auth.login');
        }

        if (! Impersonation::active()) {
            return redirect(RoleRedirector::pathFor($request->user()));
        }

        $wasActingAs = Auth::id();
        $original = Impersonation::finish();

        /* The account that started it has gone — deleted while this was open,
           most likely. Nobody should be left signed in as somebody they are
           not, so the session ends. */
        if (! $original) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('researcher.login')
                ->with('saved', __('Your own session could not be restored. Please sign in again.'));
        }

        Log::info('Stopped signing in as another account', [
            'by' => $original->id,
            'as' => $wasActingAs,
            'ip' => $request->ip(),
        ]);

        return redirect(RoleRedirector::pathFor($original))
            ->with('saved', __('You are back in your own account, :name.', ['name' => $original->name]));
    }
}
