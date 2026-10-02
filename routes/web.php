<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Support\Facades\Route;

// Bangla is the site's own language and answers at the root.
Route::middleware(SetLocale::class)->group(base_path('routes/public.php'));

// English is the same site, one segment deeper.
Route::prefix('en')->name('en.')->middleware(SetLocale::class)->group(base_path('routes/public.php'));

// The Researcher Portal: signed-in, and registered once rather than per
// language, since it is an application rather than a page of the brochure.
Route::middleware(SetLocale::class)->group(base_path('routes/researcher.php'));
