<?php

namespace App\Http\Controllers\Researcher;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

/**
 * Registration and sign-in for researchers.
 *
 * Built on the framework's own `web` guard and the existing `users` table —
 * no second authentication system, and no package. Filament keeps its own
 * login at /admin/login for staff; this is the public one.
 *
 * Registration asks for an account and nothing more. The academic record is
 * filled in afterwards, from the dashboard, where there is room for it and
 * where leaving it half-done costs nothing.
 */
class AuthController extends Controller
{
    public function showRegister()
    {
        return view('researcher.auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:180', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'website' => ['nullable', 'size:0'],   // honeypot
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::RESEARCHER,
        ]);

        // The profile row exists from the first minute, so every page can read
        // it without checking whether it is there.
        $user->profile();

        Auth::login($user, remember: true);

        $request->session()->regenerate();

        return redirect()->route('researcher.dashboard')->with('welcome', true);
    }

    public function showLogin()
    {
        return view('researcher.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email:rfc'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('researcher.auth.failed')]);
        }

        $request->session()->regenerate();

        // Staff who sign in here belong in the panel, not the portal.
        if (Auth::user()->isAdmin()) {
            return redirect('/admin');
        }

        return redirect()->intended(route('researcher.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('research');
    }
}
