<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ExpertController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->routeIs('administrations.*', 'en.administrations.*') ? 'administration' : 'expert';

        $search = $request->string('q')->trim()->toString();
        $area = $request->string('area')->toString();

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
            ->when($area, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->paginate(9)
            ->withQueryString();

        return view('pages.experts.index', [
            'experts' => $experts,
            'directoryRoute' => $type === 'administration' ? 'administrations.index' : 'experts.index',
            'translationPrefix' => $type === 'administration' ? 'site.administrations.' : 'site.experts.',
            'categories' => ServiceCategory::active()
                ->withCount(['experts' => fn ($q) => $q->active()->where('type', $type)])
                ->orderBy('sort_order')
                ->get(),
            'totalExperts' => Expert::active()->where('type', $type)->count(),
            'search' => $search,
            'area' => $area,
        ]);
    }

    public function show(Expert $expert)
    {
        abort_unless($expert->is_active, 404);

        $expert->load('category');

        return view('pages.experts.show', [
            'expert' => $expert,
            'related' => Expert::with('category')->active()
                ->where('type', $expert->type)
                ->whereKeyNot($expert->id)
                ->when($expert->service_category_id, fn ($q) => $q->where('service_category_id', $expert->service_category_id))
                ->orderBy('sort_order')
                ->take(3)
                ->get(),
        ]);
    }
}
