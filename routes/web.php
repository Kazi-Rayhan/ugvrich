<?php

use App\Http\Controllers\Admin\IdeaSubmissionExportController;
use App\Http\Controllers\Admin\ImpersonationController;
use App\Http\Controllers\Admin\SalesPrintController;
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

// The innovator dashboard, registered the same way.
Route::middleware(SetLocale::class)->group(base_path('routes/innovator.php'));
Route::prefix('en')->name('en.')->middleware(SetLocale::class)->group(base_path('routes/innovator.php'));

/* Signing in as another account.
   Outside the admin panel on purpose: while signed in as a researcher the panel
   is closed, and the way back has to stay open. */
Route::middleware('auth')->group(function () {
    Route::get('/impersonate/{user}', [ImpersonationController::class, 'start'])->name('impersonate.start');
});
Route::get('/impersonate', [ImpersonationController::class, 'stop'])->name('impersonate.stop');

// Printable proposals and orders, opened from the admin panel.
Route::middleware('auth')->prefix('admin/print')->name('sales.')->group(function () {
    Route::get('/proposals/{proposal}', [SalesPrintController::class, 'proposal'])->name('proposals.print');
    Route::get('/orders/{order}', [SalesPrintController::class, 'order'])->name('orders.print');
});

// "Export PDF" on the idea submissions list.
Route::middleware('auth')->get('/admin/export/idea-submissions', IdeaSubmissionExportController::class)
    ->name('exports.idea-submissions');

Route::prefix('en')->name('en.')->group(function () {
    Route::get('/impersonate', [ImpersonationController::class, 'stop'])->name('impersonate.stop');
});
