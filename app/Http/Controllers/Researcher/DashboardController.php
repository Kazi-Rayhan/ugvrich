<?php

namespace App\Http\Controllers\Researcher;

use App\Http\Controllers\Controller;
use App\Models\ResearchIdea;
use Illuminate\Http\Request;

/** The portal's front page: where everything stands, and what to do next. */
class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $profile = $user->profile();

        $ideas = ResearchIdea::ownedBy($user)->latest()->get();

        return view('researcher.dashboard', [
            'profile' => $profile,
            'completion' => $profile->completion(),
            'checklist' => $profile->checklist(),
            'ideas' => $ideas,
            'counts' => [
                'ideas' => $ideas->count(),
                'ideas_approved' => $ideas->where('status', ResearchIdea::APPROVED)->count(),
                'proposals' => 0,
                'projects' => 0,
                'papers' => 0,
            ],
            // Every review on this researcher's ideas, newest first.
            'activity' => $ideas->flatMap->reviews->sortByDesc('created_at')->take(6),
            'notifications' => $user->unreadNotifications()->take(5)->get(),
        ]);
    }
}
