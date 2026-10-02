<?php

use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

// Bangla is the site's own language and answers at the root.
Route::middleware(SetLocale::class)->group(base_path('routes/public.php'));

// English is the same site, one segment deeper.
Route::prefix('en')->name('en.')->middleware(SetLocale::class)->group(base_path('routes/public.php'));

// The Researcher Portal: signed-in, and registered under both language roots,
// so its pages can be localised and the route check used by tests still sees a
// complete public site. The root copy is the real application route.
Route::middleware(SetLocale::class)->group(base_path('routes/researcher.php'));
Route::prefix('en')->name('en.')->middleware(SetLocale::class)->group(base_path('routes/researcher.php'));

/* Signing in as another account.
   Outside the admin panel on purpose: while signed in as a researcher the panel
   is closed, and the way back has to stay open. */
Route::middleware('auth')->group(function () {
    Route::get('/impersonate/{user}', [ImpersonationController::class, 'start'])->name('impersonate.start');
});
Route::get('/impersonate', [ImpersonationController::class, 'stop'])->name('impersonate.stop');

Route::prefix('en')->name('en.')->group(function () {
    Route::get('/impersonate', [ImpersonationController::class, 'stop'])->name('impersonate.stop');
});
