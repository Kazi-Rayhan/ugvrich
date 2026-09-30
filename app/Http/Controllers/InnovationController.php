<?php

namespace App\Http\Controllers;

use App\Models\InnovationArea;
use App\Models\Project;
use App\Support\InnovationFramework;
use Illuminate\Http\Request;

class InnovationController extends Controller
{
    /** The Innovation Wing proposal, exactly as the document sets it out. */
    public function index()
    {
        return view('pages.innovation.index', [
            'doc' => InnovationFramework::all(),
        ]);
    }

    /** The eight departmental areas, and the projects running in each. */
    public function areas(Request $request)
    {
        $areas = InnovationArea::active()
            ->withCount(['projects' => fn ($q) => $q->where('type', 'innovation')])
            ->orderBy('sort_order')
            ->get();

        $area = $areas->firstWhere('slug', $request->string('area')->toString());

        $projects = Project::with('innovationArea')
            ->where('type', 'innovation')
            ->when($area, fn ($q) => $q->where('innovation_area_id', $area->id))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get();

        return view('pages.innovation.areas', [
            'areas' => $areas,
            'activeArea' => $area,
            'projects' => $projects,
        ]);
    }

    /**
     * One proposed innovation, in full.
     *
     * The Innovation page lists the six side by side with a paragraph each;
     * this is where the rest of the write-up lives, and the address a proposal
     * can be sent to on its own.
     */
    public function show(string $slug)
    {
        $doc = InnovationFramework::all();

        $proposal = InnovationFramework::proposal($slug);

        abort_if($proposal === null, 404);

        $index = array_search($proposal, $doc['proposed'], true);

        return view('pages.innovation.show', [
            'doc' => $doc,
            'item' => $proposal,
            'index' => $index,
            'cover' => $doc['proposed_covers'][$index] ?? null,
            // The two either side, so a reader can walk the set.
            'previous' => $doc['proposed'][$index - 1] ?? null,
            'next' => $doc['proposed'][$index + 1] ?? null,
        ]);
    }

    /** The Innovation Wing proposal, rendered from config/innovation_framework.php. */
    public function plan()
    {
        return view('pages.innovation.plan', [
            'doc' => InnovationFramework::all(),
        ]);
    }
}
