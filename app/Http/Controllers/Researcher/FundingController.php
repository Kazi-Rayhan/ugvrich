<?php

namespace App\Http\Controllers\Researcher;

use App\Http\Controllers\Controller;
use App\Models\FundingOpportunity;
use App\Models\ResearchFunding;
use Illuminate\Http\Request;

/**
 * What a researcher can see about money: the opportunities the wing has
 * published, and the funding decisions recorded against their own proposals.
 *
 * Read-only on purpose. A funding decision is the Research Wing's to make, so
 * there is no route here that writes one — the researcher sees the outcome,
 * not a form for producing it.
 */
class FundingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('researcher.funding.index', [
            // Only this researcher's own proposals, through the relation.
            'fundings' => ResearchFunding::query()
                ->whereHas('proposal', fn ($q) => $q->where('user_id', $user->id))
                ->with('proposal')
                ->latest()
                ->get(),

            'opportunities' => FundingOpportunity::query()
                ->active()
                ->open()
                ->orderByRaw('deadline is null, deadline asc')
                ->orderBy('sort_order')
                ->get(),

            'closed' => FundingOpportunity::query()
                ->active()
                ->whereDate('deadline', '<', today())
                ->orderByDesc('deadline')
                ->limit(6)
                ->get(),
        ]);
    }

    public function show(Request $request, FundingOpportunity $opportunity)
    {
        abort_unless($opportunity->is_active, 404);

        return view('researcher.funding.show', ['opportunity' => $opportunity]);
    }
}
