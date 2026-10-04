<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse as Responsable;
use Illuminate\Http\RedirectResponse;

/** Signing out of the admin panel lands on the public home page, not the panel's login screen. */
class LogoutResponse implements Responsable
{
    public function toResponse($request): RedirectResponse
    {
        return redirect()->route('home');
    }
}
