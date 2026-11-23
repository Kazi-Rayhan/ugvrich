<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use App\Models\ExpertPortfolio;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExpertController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->routeIs('administrations.*', 'en.administrations.*') ? 'administration' : 'expert';

        $search = $request->string('q')->trim()->toString();
        $area = $request->string('area')->toString();
        $serviceArea = $type === 'expert' && $area
            ? ServiceCategory::active()->where('slug', $area)->first()
            : null;

        $experts = Expert::active()->where('type', $type)
            ->with('category')
            ->when($search, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('designation', 'like', "%{$search}%")
                        ->orWhere('department', 'like', "%{$search}%")
                        ->orWhere('research_interests', 'like', "%{$search}%")
                        ->orWhere('expertise', 'like', "%{$search}%");
                });
            })
            ->when($area, fn ($q, $slug) => $serviceArea
                ? $q->forServiceArea($serviceArea)
                : $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($type === 'administration', fn ($q) => $q->orderByDesc('is_featured'))
            ->orderBy('sort_order', 'asc')
            ->orderBy('id')
            ->paginate(9)
            ->withQueryString();

        $categories = ServiceCategory::query()
            ->withCount(['experts' => fn ($q) => $q->active()->where('type', $type)])
            ->orderBy('sort_order')
            ->get();

        if ($type === 'expert') {
            foreach ($categories->filter(fn (ServiceCategory $category) => filled($category->department))->groupBy('department') as $departmentCategories) {
                $count = $departmentCategories->sum('experts_count');

                foreach ($departmentCategories as $category) {
                    $category->experts_count = $count;
                }
            }
        }

        return view($type === 'expert' ? 'pages.experts.directory' : 'pages.experts.index', [
            'experts' => $experts,
            'directoryRoute' => $type === 'administration' ? 'administrations.index' : 'experts.index',
            'translationPrefix' => $type === 'administration' ? 'site.administrations.' : 'site.experts.',
            'categories' => $categories->where('is_active', true),
            'totalExperts' => Expert::active()->where('type', $type)->count(),
            'search' => $search,
            'area' => $area,
        ]);
    }

    public function show(Expert $expert)
    {
        abort_unless($expert->is_active, 404);

        $expert->load('category');
        $application = $expert->type === 'expert' ? $expert->consultancyApplication()->whereNotNull('signed_at')->first() : null;

        return view($expert->type === 'administration' ? 'pages.experts.show' : 'pages.experts.profile', [
            'expert' => $expert,
            'portfolios' => $application?->portfolios()->where('is_public', true)->latest()->get() ?? collect(),
            'related' => Expert::with('category')->active()
                ->where('type', $expert->type)
                ->whereKeyNot($expert->id)
                ->when($expert->service_category_id, fn ($q) => $q->where('service_category_id', $expert->service_category_id))
                ->orderBy('sort_order')
                ->take(3)
                ->get(),
        ]);
    }

    public function portfolio(Expert $expert, ExpertPortfolio $portfolio)
    {
        abort_unless($expert->is_active && $expert->type === 'expert' && $portfolio->is_public
            && $expert->consultancyApplication()->whereKey($portfolio->consultancy_application_id)->whereNotNull('signed_at')->exists(), 404);

        return Storage::disk('local')->download($portfolio->path, headers: ['Cache-Control' => 'private, no-store']);
    }
}
