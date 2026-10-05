<?php

use App\Http\Controllers\Innovator\InnovatorController;
use App\Http\Middleware\EnsureInnovator;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Innovator dashboard
|--------------------------------------------------------------------------
|
| Loaded the same way as the Researcher Portal (both language roots), so its
| pages read in the visitor's language. There is no sign-up page: an account
| is made when an idea is submitted, and the email it sends links to the
| set-password page below. Signing in uses the shared /login.
*/

Route::prefix('innovator')->name('innovator.')->group(function () {
    Route::get('/set-password/{token}', [InnovatorController::class, 'showSetPassword'])->name('password.set');
    Route::post('/set-password', [InnovatorController::class, 'setPassword'])->name('password.update');

    Route::post('/logout', [InnovatorController::class, 'logout'])->middleware('auth')->name('logout');

    Route::middleware(EnsureInnovator::class)->group(function () {
        Route::get('/dashboard', [InnovatorController::class, 'dashboard'])->name('dashboard');
    });
});
