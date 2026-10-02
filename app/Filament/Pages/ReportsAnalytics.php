<?php

namespace App\Filament\Pages;

use App\Models\ConsultancyRequest;
use App\Models\IdeaSubmission;
use App\Models\Project;
use App\Models\Publication;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

/** Read-only breakdowns across the hub: by department, by type, over time. */
class ReportsAnalytics extends Page
{
    public const ACCESS_PERMISSION = 'view_reports_analytics';

    protected string $view = 'filament.pages.reports-analytics';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'Reports & Analytics';

    protected static ?string $title = 'Reports & Analytics';

    protected static ?string $slug = 'reports';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user !== null && ($user->isAdmin() || $user->hasPermission(self::ACCESS_PERMISSION));
    }

    /** @return array<string, mixed> */
    public function getData(): array
    {
        $projects = Project::query()->get(['department', 'type', 'status', 'stage']);
        $ideas = IdeaSubmission::query()->get(['department', 'role', 'status', 'created_at']);
        $requests = ConsultancyRequest::query()->get(['status', 'created_at']);

        // Every department, in config order, with its counts side by side.
        $byDepartment = collect(config('rich.departments'))->map(fn ($name, $code) => [
            'code' => $code,
            'name' => $name,
            'projects' => $projects->where('department', $code)->count(),
            'ideas' => $ideas->where('department', $code)->count(),
        ])->values();

        // Twelve months of incoming ideas and consultancy requests.
        $months = collect(range(11, 0))->map(function ($ago) use ($ideas, $requests) {
            $month = now()->startOfMonth()->subMonths($ago);
            $in = fn ($rows) => $rows->filter(fn ($row) => $row->created_at?->isSameMonth($month))->count();

            return ['label' => $month->format('M'), 'ideas' => $in($ideas), 'requests' => $in($requests)];
        });

        return [
            'totals' => [
                ['Projects', $projects->count()],
                ['Ideas submitted', $ideas->count()],
                ['Consultancy requests', $requests->count()],
                ['Publications', Publication::where('kind', '!=', 'funded-project')->count()],
            ],
            'byDepartment' => $byDepartment,
            'departmentMax' => max(1, $byDepartment->max(fn ($row) => $row['projects'] + $row['ideas'])),
            'byType' => collect(config('rich.project_types'))->map(fn ($label, $key) => [$label, $projects->where('type', $key)->count()])->values(),
            'byStatus' => collect(['ongoing' => 'Ongoing', 'completed' => 'Completed', 'planned' => 'Planned'])
                ->map(fn ($label, $key) => [$label, $projects->where('status', $key)->count()])->values(),
            'ideasByRole' => collect(config('rich.idea_roles'))->map(fn ($label, $key) => [$label, $ideas->where('role', $key)->count()])->values(),
            'requestsByStatus' => $requests->groupBy('status')->map->count()->sortDesc(),
            'months' => $months,
            'monthMax' => max(1, $months->max(fn ($m) => max($m['ideas'], $m['requests']))),
        ];
    }
}
