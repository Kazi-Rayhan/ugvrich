<?php

namespace App\Http\Controllers\Researcher;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * The account itself, as opposed to the academic record.
 *
 * My Profile holds the research life — degrees, interests, publications. This
 * holds the three things that are about signing in: the name and address the
 * account answers to, the password, and how much email it wants. Nothing here
 * touches the researcher profile, and there is no new authentication: the same
 * `users` table and the same web guard as everywhere else.
 */
class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        return view('researcher.settings', [
            'user' => $request->user(),
        ]);
    }

    /** Name, address, and whether events are worth an email. */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => [
                'required', 'email:rfc', 'max:180',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'email_notifications' => ['nullable', 'boolean'],
            'locale' => ['nullable', Rule::in(array_keys(SetLocale::LOCALES))],
        ]);

        /* Changing the address means the verified mark no longer applies to it.
           Nothing in the portal gates on verification today, but leaving a mark
           behind that refers to a different address would be a small lie. */
        $emailChanged = $data['email'] !== $user->email;

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'email_notifications' => (bool) ($data['email_notifications'] ?? false),
        ]);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        // The portal carries its language in the session; see SetLocale.
        if (! empty($data['locale'])) {
            $request->session()->put(SetLocale::SESSION_KEY, $data['locale']);
        }

        return redirect()
            ->route('researcher.settings')
            ->with('saved', __('researcher.settings.account_saved'));
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $request->user()->update(['password' => $data['password']]);

        /* A new password should not leave the old session id in circulation.
           Other devices are not signed out — that needs the AuthenticateSession
           middleware, which this application does not use — so the page says so
           rather than implying otherwise. */
        $request->session()->regenerate();

        return redirect()
            ->route('researcher.settings')
            ->with('saved', __('researcher.settings.password_saved'));
    }
}
