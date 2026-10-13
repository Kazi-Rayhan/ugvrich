<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\RoleRedirector;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

/**
 * "Forgot password" for every account that signs in at /login: researchers,
 * innovators and staff. The emailed link lands on a page to choose a new
 * password, then signs the account straight in.
 */
class PasswordResetController extends Controller
{
    public function request()
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request)
    {
        $request->validate(['email' => ['required', 'email:rfc']]);

        $status = Password::sendResetLink($request->only('email'));

        // Too many requests is the one failure worth saying out loud. Whether an
        // address has an account is not revealed: every other case reads "sent".
        if ($status === Password::RESET_THROTTLED) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('researcher.auth.forgot_throttled')]);
        }

        return back()->with('status', __('researcher.auth.forgot_sent'));
    }

    public function edit(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email:rfc'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                Auth::login($user);
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('researcher.auth.reset_invalid')]);
        }

        $request->session()->regenerate();

        return redirect(RoleRedirector::pathFor(Auth::user()))
            ->with('status', __('researcher.auth.reset_done'));
    }
}
