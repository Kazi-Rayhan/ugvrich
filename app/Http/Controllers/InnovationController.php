<?php

namespace App\Http\Controllers;

use App\Models\Innovation;
use App\Models\InnovationArea;
use App\Models\Project;
use App\Support\InnovationFramework;
use Illuminate\Http\Request;

class InnovationController extends Controller
{
    /**
     * The Innovation Wing: a hero and the departmental areas, laid out the way
     * the Services page lays out its categories, then the wing's proposal as
     * the document sets it out.
     */
    public function index()
    {
        $doc = InnovationFramework::all();

        return view('pages.innovation.index', [
            'doc' => $doc,
            'areas' => InnovationArea::active()
                ->with(['innovations' => fn ($q) => $q->active()])
                ->withCount(['projects' => fn ($q) => $q->public()->where('type', 'innovation')])
                ->orderBy('sort_order')
                ->get(),
            // Where each innovation's own page is, for the links under each area.
            'innovationSlugs' => InnovationFramework::recordSlugs($doc),
        ]);
    }

    /** One area on a page of its own: what it covers, and the projects in it. */
    public function area(InnovationArea $innovationArea)
    {
        abort_unless($innovationArea->is_active, 404);

        $innovationArea->load(['innovations' => fn ($q) => $q->active()]);

        return view('pages.innovation.area', [
            'area' => $innovationArea,
            'innovationSlugs' => InnovationFramework::recordSlugs(),
            'projects' => Project::public()->with('innovationArea')
                ->where('type', 'innovation')
                ->where('innovation_area_id', $innovationArea->id)
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->get(),
            'siblings' => InnovationArea::active()->whereKeyNot($innovationArea->id)->orderBy('sort_order')->get(),
        ]);
    }

    /** The eight departmental areas, and the projects running in each. */
    public function areas(Request $request)
    {
        // A link to one area (the old ?area= tabs) now goes to that area's own page.
        if ($request->filled('area') && ($one = InnovationArea::active()->where('slug', $request->string('area'))->first())) {
            return redirect()->route('innovation.area', $one);
        }

        $areas = InnovationArea::active()
            ->withCount(['projects' => fn ($q) => $q->where('type', 'innovation')])
            ->orderBy('sort_order')
            ->get();

        $area = $areas->firstWhere('slug', $request->string('area')->toString());

        $projects = Project::public()->with('innovationArea')
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
     * One innovation, in full.
     *
     * The Innovation page gives each one a card with a paragraph on it; this is
     * where the rest of the write-up lives, and the address a single innovation
     * can be sent to on its own. Current and proposed innovations are written
     * up differently, and the view follows whichever this is.
     */
    public function show(string $slug)
    {
        $doc = InnovationFramework::all();

        $found = InnovationFramework::find($slug, $doc);

        abort_if($found === null, 404);

        ['phase' => $phase, 'item' => $item, 'index' => $index] = $found;

        $siblings = $phase === Innovation::CURRENT
            ? $this->currentNeighbours($doc, $index)
            : [$doc['proposed'][$index - 1] ?? null, $doc['proposed'][$index + 1] ?? null];

        return view('pages.innovation.show', [
            'doc' => $doc,
            'phase' => $phase,
            'item' => $item,
            'index' => $index,
            'cover' => $doc[$phase.'_covers'][$index] ?? null,
            // The two either side, so a reader can walk the set.
            'previous' => $siblings[0],
            'next' => $siblings[1],
        ]);
    }

    /**
     * The current innovations are positional arrays, so a neighbour is built
     * from the name and the address held beside it.
     *
     * @return array{0: ?array, 1: ?array}
     */
    protected function currentNeighbours(array $doc, int $index): array
    {
        $at = function (int $i) use ($doc): ?array {
            if (! isset($doc['current'][$i])) {
                return null;
            }

            return ['name' => $doc['current'][$i][0], 'slug' => $doc['current_slugs'][$i]];
        };

        return [$at($index - 1), $at($index + 1)];
    }

    /** The Innovation Wing proposal, rendered from config/innovation_framework.php. */
    public function plan()
    {
        return view('pages.innovation.plan', [
            'doc' => InnovationFramework::all(),
        ]);
    }
}
