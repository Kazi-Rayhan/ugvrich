<?php

namespace App\Http\Controllers\Innovator;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * The innovator side of the site: choosing a password from the emailed link,
 * the dashboard of one's own ideas, and signing out.
 *
 * Accounts are not registered here. One is made when somebody submits an
 * idea (IdeaSubmissionController), and the email it sends leads to setPassword.
 */
class InnovatorController extends Controller
{
    /** The innovator's ideas, each with where it has got to on the journey. */
    public function dashboard(Request $request)
    {
        $user = $request->user();

        return view('innovator.dashboard', [
            'user' => $user,
            'ideas' => $user->ideaSubmissions()->get(),
            'stages' => \App\Support\Vocabulary::all('startup_stages'),
        ]);
    }

    /** The page the emailed link opens. */
    public function showSetPassword(Request $request, string $token)
    {
        return view('innovator.set-password', [
            'token' => $token,
            'email' => (string) $request->query('email'),
        ]);
    }

    public function setPassword(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::broker('innovators')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
                Auth::login($user, remember: true);
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('innovator.password.invalid')]);
        }

        $request->session()->regenerate();

        return redirect()->route('innovator.dashboard')->with('saved', __('innovator.password.done'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
