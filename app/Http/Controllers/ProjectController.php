<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $categories = ServiceCategory::active()->orderBy('sort_order')->get();
        $type = array_key_exists($request->string('type')->toString(), config('rich.project_types'))
            ? $request->string('type')->toString()
            : null;

        $projects = Project::public()->with(['category', 'innovationArea'])
            ->ofType($type)
            ->when($request->string('area')->toString(), function ($q, $slug) {
                $q->whereHas('category', fn ($c) => $c->where('slug', $slug));
            })
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->paginate(9)
            ->withQueryString();

        $typeCounts = Project::public()->selectRaw('type, count(*) as total')->groupBy('type')->pluck('total', 'type');

        return view('pages.projects.index', compact('projects', 'categories', 'type', 'typeCounts'));
    }

    public function show(Project $project)
    {
        $project->load(['category', 'innovationArea']);

        return view('pages.projects.show', [
            'project' => $project,
            'related' => Project::public()->with(['category', 'innovationArea'])
                ->whereKeyNot($project->id)
                ->where('type', $project->type)
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->take(3)
                ->get(),
        ]);
    }
}
