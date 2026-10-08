<?php

use App\Http\Controllers\ConsultancyRequestController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\ExpertController;
use App\Http\Controllers\IdeaSubmissionController;
use App\Http\Controllers\InnovationController;
use App\Http\Controllers\InternshipController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\Researcher\AuthController;
use App\Http\Controllers\ResearchRequestController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SubscriberController;
use Illuminate\Support\Facades\Route;

/*
| The public site, registered twice by routes/web.php: once at the root
| for Bangla and once under /en for English. Route names in the English
| copy carry an `en.` prefix, which LocalizedUrlGenerator applies for us.
*/

Route::get('/', [PageController::class, 'home'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/research', [PageController::class, 'research'])->name('research');
// The planning document the hub page is drawn from.
Route::get('/research/framework', [PageController::class, 'researchFramework'])->name('research.framework');

// What a researcher can send the wing: a support request, or a proposal.
Route::get('/research/support', [ResearchRequestController::class, 'supportCreate'])->name('research.support');
Route::post('/research/support', [ResearchRequestController::class, 'supportStore'])->name('research.support.store');
Route::get('/research/proposal', [ResearchRequestController::class, 'proposalCreate'])->name('research.proposal');
Route::post('/research/proposal', [ResearchRequestController::class, 'proposalStore'])->name('research.proposal.store');
Route::get('/research/thank-you', [ResearchRequestController::class, 'thanks'])->name('research.thanks');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/request-consultancy', [PageController::class, 'requestConsultancy'])->name('consultancy.create');

Route::get('/labs', [PageController::class, 'labs'])->name('labs');
Route::get('/publications', [PageController::class, 'publications'])->name('publications');
Route::get('/patents', [PageController::class, 'patents'])->name('patents');
Route::get('/industry-collaboration', [PageController::class, 'industry'])->name('industry');

Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/services/{serviceCategory}', [ServiceController::class, 'show'])->name('services.show');
// A single service, under the main service it belongs to.
Route::get('/services/{serviceCategory}/{service}', [ServiceController::class, 'service'])
    ->scopeBindings()
    ->name('services.detail');

// The RICH inauguration: curtains, a ribbon and one button.
// Route::view('/inaugurate', 'pages.udbodhon')->name('inaugurate');
// Its first address, kept so links already shared still arrive.
Route::permanentRedirect('/udbodhon', '/inaugurate');

Route::get('/innovation', [InnovationController::class, 'index'])->name('innovation.index');
Route::get('/innovation/areas', [InnovationController::class, 'areas'])->name('innovation.areas');
// One innovation area on a page of its own, the way a service category has one.
Route::get('/innovation/areas/{innovationArea}', [InnovationController::class, 'area'])->name('innovation.area');
Route::redirect('/innovation/plan', '/innovation')->name('innovation.plan');
// Who can apply, and how a submitted idea is screened.
Route::view('/innovation/application-screening', 'pages.innovation.screening')->name('innovation.screening');
// Last of the three: a bare segment would otherwise swallow `areas` and `plan`.
Route::get('/innovation/{slug}', [InnovationController::class, 'show'])->name('innovation.show');

Route::get('/startup', [PageController::class, 'startup'])->name('startup');
Route::get('/submit-idea', [IdeaSubmissionController::class, 'create'])->name('ideas.create');
Route::get('/submit-idea/thank-you', [IdeaSubmissionController::class, 'thanks'])->name('ideas.thanks');
Route::post('/submit-idea', [IdeaSubmissionController::class, 'store'])
    ->middleware('throttle:8,1')
    ->name('ideas.store');

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');

Route::get('/experts', [ExpertController::class, 'index'])->name('experts.index');
Route::get('/experts/{expert}', [ExpertController::class, 'show'])->name('experts.show');

Route::get('/events', [PostController::class, 'events'])->name('events');
Route::get('/news', [PostController::class, 'index'])->name('news.index');
Route::get('/news/{post}', [PostController::class, 'show'])->name('news.show');

Route::get('/contact/thank-you', [ConsultancyRequestController::class, 'thanks'])->name('consultancy.thanks');

Route::post('/consultancy-request', ConsultancyRequestController::class)
    ->middleware('throttle:8,1')
    ->name('consultancy.store');

Route::post('/contact-message', ContactMessageController::class)
    ->middleware('throttle:8,1')
    ->name('contact.store');

Route::post('/subscribe', SubscriberController::class)
    ->middleware('throttle:8,1')
    ->name('subscribe');

Route::get('/membership', [MembershipController::class, 'create'])->name('membership.create');
Route::get('/membership/thank-you', [MembershipController::class, 'thanks'])->name('membership.thanks');
Route::post('/membership', [MembershipController::class, 'store'])->name('membership.store');

// Internship applications: the form, its thank-you page, and the submit.
Route::get('/internship', [InternshipController::class, 'create'])->name('internship.create');
Route::get('/internship/thank-you', [InternshipController::class, 'thanks'])->name('internship.thanks');
Route::post('/internship', [InternshipController::class, 'store'])->name('internship.store');
