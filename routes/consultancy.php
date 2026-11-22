<?php

use App\Http\Controllers\ConsultancyExpertController;
use Illuminate\Support\Facades\Route;

Route::prefix('consultancy/expert-registration')->name('consultancy.registration.')->group(function () {
    Route::get('/', [ConsultancyExpertController::class, 'create'])->name('register');
    Route::post('/', [ConsultancyExpertController::class, 'store'])->middleware('throttle:8,1')->name('store');
    Route::get('/thank-you', function () {
        return session()->has('application_reference') ? view('consultancy.thanks') : redirect()->route('consultancy.registration.register');
    })->name('thanks');
});

Route::middleware('auth')->prefix('expert/dashboard')->name('expert.')->group(function () {
    Route::get('/', [ConsultancyExpertController::class, 'dashboard'])->name('dashboard');
    Route::get('/onboarding', [ConsultancyExpertController::class, 'onboarding'])->name('onboarding');
    Route::get('/profile', [ConsultancyExpertController::class, 'showProfile'])->name('profile.show');
    Route::get('/portfolio', [ConsultancyExpertController::class, 'showPortfolio'])->name('portfolio.index');
    Route::get('/contract', [ConsultancyExpertController::class, 'showContract'])->name('contract.show');
    Route::get('/wallet', [ConsultancyExpertController::class, 'showWallet'])->name('wallet.show');
    Route::put('/wallet/payout-method', [ConsultancyExpertController::class, 'savePayoutMethod'])->name('wallet.payout');
    Route::post('/wallet/withdrawals', [ConsultancyExpertController::class, 'requestWithdrawal'])->middleware('throttle:12,1')->name('wallet.withdraw');
    Route::post('/signature', [ConsultancyExpertController::class, 'sign'])->name('sign');
    Route::put('/profile', [ConsultancyExpertController::class, 'update'])->name('profile');
    Route::post('/portfolio', [ConsultancyExpertController::class, 'upload'])->name('portfolio.upload');
    Route::get('/portfolio/{portfolio}', [ConsultancyExpertController::class, 'download'])->name('portfolio.download');
    Route::patch('/portfolio/{portfolio}/visibility', [ConsultancyExpertController::class, 'updatePortfolioVisibility'])->name('portfolio.visibility');
    Route::get('/signature/{application}', [ConsultancyExpertController::class, 'signature'])->name('signature');
    Route::get('/application-photo/{application}', [ConsultancyExpertController::class, 'reviewPhoto'])->name('application-photo');
});
