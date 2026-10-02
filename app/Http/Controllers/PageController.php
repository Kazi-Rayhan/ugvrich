<?php

namespace App\Http\Controllers;

use App\Models\CoreArea;
use App\Models\Expert;
use App\Models\Facility;
use App\Models\Partner;
use App\Models\StudentTeam;
use App\Support\ResearchFramework;
use App\Models\Project;
use App\Models\Publication;
use App\Models\ServiceCategory;
use App\Models\Stat;
use App\Support\MeetingSlots;

class PageController extends Controller
{
    public function home()
    {
        return view('pages.home', [
            'stats' => Stat::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function about()
    {
        return view('pages.about', [
            'coreAreas' => CoreArea::active()->orderBy('sort_order')->get(),
            'stats' => Stat::active()->orderBy('sort_order')->get(),
            'partners' => Partner::active()->orderBy('sort_order')->get(),
            'experts' => Expert::with('category')->active()->where('is_featured', true)->orderBy('sort_order')->take(6)->get(),
        ]);
    }

    /**
     * The Research hub: what RICH is, and what it is being built to become.
     *
     * A vision page, not a research management system. Everything factual on it
     * comes from one of two places — the Research Wing's own framework document,
     * or a count taken from this database. Nothing is invented: where there is
     * nothing to count, the page says "coming soon" instead of showing a number.
     */
    public function research()
    {
        return view('pages.research', [
            'framework' => ResearchFramework::all(),
            'impact' => $this->researchImpact(),
        ]);
    }

    /** The full framework document, which the hub page links out to. */
    public function researchFramework()
    {
        return view('pages.research-framework', [
            'f' => ResearchFramework::all(),
        ]);
    }

    /**
     * What the site can honestly count today.
     *
     * A null means there is no source for that figure yet, and the page shows
     * it as "coming soon". A zero is a real answer and is shown as a zero.
     *
     * @return array<string, int|null>
     */
    protected function researchImpact(): array
    {
        $publications = Publication::query();

        return [
            'research_projects' => Project::where('type', 'research')->count(),
            'publications' => Publication::count(),
            'conference_papers' => (clone $publications)->where('kind', 'conference')->count(),
            'external_grants' => (clone $publications)->where('kind', 'funded-project')->count(),
            'patents' => Project::whereNotNull('patent_status')->where('patent_status', '!=', 'none')->count(),
            'industry' => Partner::count(),

            // No source for these yet; the page says so rather than guessing.
            'quartile' => null,
            'funding' => null,
            'international' => null,
            'student_researchers' => StudentTeam::count() ?: null,
        ];
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function labs()
    {
        return view('pages.labs', [
            'facilities' => Facility::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function publications()
    {
        $publications = Publication::active()->orderByDesc('year')->orderBy('sort_order')->get()->groupBy('kind');

        return view('pages.publications', [
            'journals' => $publications['journal'] ?? collect(),
            'conferences' => $publications['conference'] ?? collect(),
            'other' => $publications['publication'] ?? collect(),
            'funded' => $publications['funded-project'] ?? collect(),
        ]);
    }

    public function patents()
    {
        $projects = Project::with('innovationArea')
            ->where(fn ($q) => $q->where('patent_status', '!=', 'none')
                ->orWhere('commercialization_status', '!=', 'none'))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->get();

        // Two shelves: what is being protected, and what is going to market.
        $groups = [
            [
                'key' => 'applications',
                'label' => __('site.patents.group_applications'),
                'icon' => 'document',
                'text' => __('site.patents.group_applications_text'),
                'items' => $projects->whereIn('patent_status', ['planned', 'filed', 'published', 'granted', 'copyright', 'design'])->values(),
            ],
            [
                'key' => 'commercialization',
                'label' => __('site.patents.group_commercialization'),
                'icon' => 'rocket',
                'text' => __('site.patents.group_commercialization_text'),
                'items' => $projects->whereIn('commercialization_status', ['exploring', 'licensing', 'incubating', 'startup', 'market'])->values(),
            ],
        ];

        return view('pages.patents', [
            'projects' => $projects,
            'groups' => collect($groups),
        ]);
    }

    public function industry()
    {
        // Partners only: the page is the record of who the hub works with.
        return view('pages.industry', [
            'partners' => Partner::active()->orderBy('sort_order')->get(),
        ]);
    }

    public function startup()
    {
        return view('pages.startup');
    }

    /** The consultancy request form: its own page, its own submissions. */
    public function requestConsultancy()
    {
        return view('pages.consultancy-request', [
            'categories' => ServiceCategory::active()->with('services')->orderBy('sort_order')->get(),
            'slots' => MeetingSlots::all(),
            'takenSlots' => MeetingSlots::taken(),
        ]);
    }
}
