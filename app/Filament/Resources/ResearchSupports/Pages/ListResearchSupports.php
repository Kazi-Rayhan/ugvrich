<?php

namespace App\Filament\Resources\ResearchSupports\Pages;

use App\Filament\Resources\ResearchSupports\ResearchSupportResource;
use App\Models\ResearchSupport;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListResearchSupports extends ListRecords
{
    protected static string $resource = ResearchSupportResource::class;

    /**
     * Support requests grouped by whether anybody has answered them.
     *
     * The first tab is what is waiting on staff, because that is the question
     * this screen is usually opened with. Each tab carries its own count, so
     * the shape of the queue is visible without clicking through it.
     */
    public function getTabs(): array
    {
        return [
            'new' => Tab::make('Unanswered')
                ->badge($this->countOf(['new']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['new'])),
            'working' => Tab::make('Being looked at')
                ->badge($this->countOf(['in_progress']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['in_progress'])),
            'answered' => Tab::make('Answered')
                ->badge($this->countOf(['answered']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['answered'])),
            'closed' => Tab::make('Closed')
                ->badge($this->countOf(['closed']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['closed'])),
            'all' => Tab::make('All'),
        ];
    }

    /** @param  array<int, string>  $statuses */
    protected function countOf(array $statuses): ?string
    {
        $count = ResearchSupport::query()->whereIn('status', $statuses)->count();

        return $count > 0 ? (string) $count : null;
    }
}
