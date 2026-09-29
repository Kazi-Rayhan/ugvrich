<?php

namespace App\Http\Controllers;

use App\Models\CoreArea;
use App\Models\Expert;
use App\Models\Facility;
use App\Models\Partner;
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

    public function research()
    {
        // Research areas are read off the faculty's own expertise tags, so the
        // list stays true to who actually works here.
        $researchAreas = Expert::active()
            ->get(['expertise'])
            ->flatMap(fn ($expert) => $expert->expertise ?? [])
            ->map(fn ($tag) => trim($tag))
            ->filter()
            ->countBy()
            ->sortDesc();

        return view('pages.research', [
            'researchAreas' => $researchAreas,
            'researchers' => Expert::active()->with('category')->orderByDesc('is_featured')->orderBy('sort_order')->get(),
            'ongoing' => Project::with(['category', 'innovationArea'])
                ->where('type', 'research')
                ->where('status', '!=', 'completed')
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->get(),
        ]);
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
