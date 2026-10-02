<?php

use App\Http\Controllers\Researcher\AuthController;
use App\Http\Controllers\Researcher\DashboardController;
use App\Http\Controllers\Researcher\IdeaController;
use App\Http\Controllers\Researcher\FundingController;
use App\Http\Controllers\Researcher\ManuscriptController;
use App\Http\Controllers\Researcher\NotificationController;
use App\Http\Controllers\Researcher\ProfileController;
use App\Http\Controllers\Researcher\ProjectController;
use App\Http\Controllers\Researcher\ProposalController;
use App\Http\Controllers\Researcher\SettingsController;
use App\Http\Controllers\Researcher\SupportController;
use App\Http\Middleware\EnsureResearcher;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Researcher Portal
|--------------------------------------------------------------------------
|
| Registered once, not twice: unlike the public site this is a signed-in
| application rather than a brochure, so it is not mirrored under /en. The
| locale middleware still runs, so it reads in whichever language the visitor
| has been using.
|
| Staff keep Filament at /admin; this is the public-facing side.
*/

Route::prefix('researcher')->name('researcher.')->group(function () {

    // ----------------------------------------------------------- guest
    Route::middleware('guest')->group(function () {
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.store');
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

    // ------------------------------------------------------- the portal
    Route::middleware(['auth', EnsureResearcher::class])->group(function () {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('/ideas', [IdeaController::class, 'index'])->name('ideas.index');
        Route::get('/ideas/new', [IdeaController::class, 'create'])->name('ideas.create');
        Route::post('/ideas', [IdeaController::class, 'store'])->name('ideas.store');
        Route::get('/ideas/{idea}', [IdeaController::class, 'show'])->name('ideas.show');
        Route::get('/ideas/{idea}/edit', [IdeaController::class, 'edit'])->name('ideas.edit');
        Route::put('/ideas/{idea}', [IdeaController::class, 'update'])->name('ideas.update');

        Route::get('/proposals', [ProposalController::class, 'index'])->name('proposals.index');
        Route::get('/proposals/new', [ProposalController::class, 'create'])->name('proposals.create');
        Route::post('/proposals', [ProposalController::class, 'store'])->name('proposals.store');
        Route::get('/proposals/{proposal}', [ProposalController::class, 'show'])->name('proposals.show');
        Route::get('/proposals/{proposal}/edit', [ProposalController::class, 'edit'])->name('proposals.edit');
        Route::put('/proposals/{proposal}', [ProposalController::class, 'update'])->name('proposals.update');

        Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
        Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
        Route::post('/projects/{project}/updates', [ProjectController::class, 'storeUpdate'])->name('projects.updates.store');

        // Private files: streamed after the ownership check, never linked directly.
        Route::get('/projects/{project}/documents/{document}', [ProjectController::class, 'download'])->name('projects.documents.download');
        Route::get('/projects/{project}/updates/{update}/file', [ProjectController::class, 'downloadUpdate'])->name('projects.updates.download');

        Route::get('/funding', [FundingController::class, 'index'])->name('funding.index');
        Route::get('/funding/opportunities/{opportunity}', [FundingController::class, 'show'])->name('funding.show');

        Route::get('/manuscripts', [ManuscriptController::class, 'index'])->name('manuscripts.index');
        Route::get('/manuscripts/new', [ManuscriptController::class, 'create'])->name('manuscripts.create');
        Route::post('/manuscripts', [ManuscriptController::class, 'store'])->name('manuscripts.store');
        Route::get('/manuscripts/{manuscript}', [ManuscriptController::class, 'show'])->name('manuscripts.show');
        Route::get('/manuscripts/{manuscript}/edit', [ManuscriptController::class, 'edit'])->name('manuscripts.edit');
        Route::put('/manuscripts/{manuscript}', [ManuscriptController::class, 'update'])->name('manuscripts.update');
        Route::get('/manuscripts/{manuscript}/file/{file}', [ManuscriptController::class, 'download'])->name('manuscripts.download');

        Route::get('/support', [SupportController::class, 'index'])->name('support.index');
        Route::get('/support/new', [SupportController::class, 'create'])->name('support.create');
        Route::post('/support', [SupportController::class, 'store'])->name('support.store');
        Route::get('/support/{support}', [SupportController::class, 'show'])->name('support.show');
        Route::get('/support/{support}/file', [SupportController::class, 'download'])->name('support.download');

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/{notification}', [NotificationController::class, 'read'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

        // The portal keeps its own language choice; see SetLocale.
        Route::post('/locale', [NotificationController::class, 'locale'])->name('locale');
    });
});
