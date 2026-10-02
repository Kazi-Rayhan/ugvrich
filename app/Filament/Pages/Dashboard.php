<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ConsultancyRequests\ConsultancyRequestResource;
use App\Filament\Resources\IdeaSubmissions\IdeaSubmissionResource;
use App\Filament\Resources\Posts\PostResource;
use App\Filament\Resources\Projects\ProjectResource;
use App\Filament\Resources\Publications\PublicationResource;
use App\Models\ConsultancyRequest;
use App\Models\IdeaSubmission;
use App\Models\Post;
use App\Models\Project;
use App\Models\Publication;
use Filament\Pages\Dashboard as BaseDashboard;

/**
 * The management dashboard home: headline counts, the innovation pipeline,
 * where each flagship project stands, and what needs attention next.
 */
class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.pages.dashboard';

    protected static ?string $title = 'Dashboard';

    public function getHeading(): string
    {
        return 'UGV RICH';
    }

    public function getSubheading(): ?string
    {
        return 'Research & Innovation Management System';
    }

    /** @return array<string, mixed> */
    public function getData(): array
    {
        $user = auth()->user();
        $canView = fn (string $model): bool => $user !== null && $user->can('viewAny', $model);
        $canViewProjects = $canView(Project::class);
        $canViewIdeas = $canView(IdeaSubmission::class);
        $canViewPublications = $canView(Publication::class);
        $canViewEvents = $canView(Post::class);
        $canViewRequests = $canView(ConsultancyRequest::class);

        $projects = $canViewProjects
            ? Project::query()->get(['id', 'title', 'slug', 'type', 'status', 'stage', 'patent_status', 'commercialization_status', 'progress', 'department'])
            : collect();
        $ideas = $canViewIdeas ? IdeaSubmission::query()->get(['id', 'stage', 'status']) : collect();

        $url = fn (string $resource, array $filters = []) => $resource::getUrl('index', $filters ? ['filters' => $filters] : []);

        $kpis = [];

        if ($canViewProjects) {
            $kpis[] = ['Active Projects', $projects->where('status', 'ongoing')->count(), 'briefcase', 'green',
                $url(ProjectResource::class, ['status' => ['value' => 'ongoing']])];
            $kpis[] = ['Prototypes', $projects->where('stage', 'prototype')->count(), 'cog', 'blue',
                $url(ProjectResource::class, ['stage' => ['value' => 'prototype']])];
            $kpis[] = ['Patent / IP', $projects->where('patent_status', '!=', 'none')->count(), 'key', 'violet',
                $url(ProjectResource::class, ['ip' => ['value' => 'protected']])];
        }

        if ($canViewIdeas) {
            $kpis[] = ['New Ideas', $ideas->where('status', 'new')->count(), 'light-bulb', 'amber',
                $url(IdeaSubmissionResource::class, ['status' => ['value' => 'new']])];
        }

        if ($canViewProjects || $canViewIdeas) {
            $startupCount = $canViewProjects
                ? $projects->whereIn('commercialization_status', ['incubating', 'startup', 'market'])->count()
                : 0;
            $startupCount += $canViewIdeas ? $ideas->whereIn('stage', ['startup', 'market'])->count() : 0;
            $startupResource = $canViewProjects ? ProjectResource::class : IdeaSubmissionResource::class;
            $kpis[] = ['Startups', $startupCount, 'rocket', 'navy', $url($startupResource)];
        }

        if ($canViewPublications) {
            $kpis[] = ['Publications', Publication::where('is_active', true)->where('kind', '!=', 'funded-project')->count(), 'document-text', 'slate',
                $url(PublicationResource::class)];
        }

        // The pipeline as the management team talks about it. Submitted ideas
        // feed the first two stages; projects carry the rest.
        $at = fn (string ...$stages) => $projects->whereIn('stage', $stages)->count();
        $pipeline = ($canViewProjects || $canViewIdeas) ? [
            ['Ideas', ($canViewIdeas ? $ideas->where('stage', 'idea')->count() : 0) + $at('idea'), 'Submitted and waiting for review'],
            ['Evaluation', ($canViewIdeas ? $ideas->where('stage', 'evaluation')->count() : 0) + $at('selected'), 'Reviewed for originality and feasibility'],
            ['Research', $at('research'), 'Studying the problem and the approach'],
            ['Prototype', $at('prototype') + ($canViewIdeas ? $ideas->where('stage', 'prototype')->count() : 0), 'A working model being built'],
            ['Testing', $at('testing'), 'Proving it in real conditions'],
            ['Patent / IP', $at('patent'), 'Protection being filed'],
            ['Commercialization', $at('incubation', 'commercialization'), 'Preparing for market'],
            ['Startup', ($canViewProjects ? $projects->where('commercialization_status', 'startup')->count() : 0)
                + ($canViewIdeas ? $ideas->where('stage', 'startup')->count() : 0), 'A venture has been formed'],
        ] : [];

        return [
            'kpis' => $kpis,
            'pipeline' => $pipeline,
            'pipelineMax' => $pipeline ? max(1, max(array_column($pipeline, 1))) : 1,
            'projects' => $projects->where('type', 'innovation')->sortBy(fn ($p) => $p->title)->values(),
            'events' => $canViewEvents ? Post::published()->events()->where('event_at', '>=', now()->startOfDay())->orderBy('event_at')->take(4)->get() : collect(),
            'publications' => $canViewPublications ? Publication::where('is_active', true)->where('kind', '!=', 'funded-project')->orderByDesc('year')->take(4)->get() : collect(),
            'funding' => $canViewPublications ? Publication::where('is_active', true)->where('kind', 'funded-project')->orderByDesc('year')->take(4)->get() : collect(),
            'requests' => $canViewRequests ? ConsultancyRequest::with('category')->latest()->take(5)->get() : collect(),
            'access' => [
                'projects' => $canViewProjects,
                'pipeline' => $canViewProjects || $canViewIdeas,
                'events' => $canViewEvents,
                'publications' => $canViewPublications,
                'requests' => $canViewRequests,
            ],
            'links' => [
                'projects' => $url(ProjectResource::class, ['type' => ['value' => 'innovation']]),
                'events' => $url(PostResource::class, ['type' => ['value' => 'event']]),
                'publications' => $url(PublicationResource::class),
                'funding' => $url(PublicationResource::class, ['kind' => ['value' => 'funded-project']]),
                'requests' => $url(ConsultancyRequestResource::class),
            ],
        ];
    }
}
