<?php

use App\Http\Controllers\ClientPortalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'auth.session'])->prefix('client/dashboard')->name('client.')->group(function () {
    Route::get('/', [ClientPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [ClientPortalController::class, 'orders'])->name('orders');
    Route::get('/orders/{order}', [ClientPortalController::class, 'showOrder'])->whereNumber('order')->name('orders.show');
    Route::get('/orders/{order}/invoice', [ClientPortalController::class, 'invoice'])->whereNumber('order')->name('orders.invoice');
    Route::get('/profile', [ClientPortalController::class, 'profile'])->name('profile');
    Route::put('/profile', [ClientPortalController::class, 'updateProfile'])->name('profile.update');
    Route::get('/security', [ClientPortalController::class, 'security'])->name('security');
    Route::put('/security', [ClientPortalController::class, 'updatePassword'])->middleware('throttle:8,1')->name('security.update');
    Route::post('/logout', [ClientPortalController::class, 'logout'])->name('logout');
});
