<?php

namespace App\Filament\Resources\ResearchFundings\Pages;

use App\Filament\Resources\ResearchFundings\ResearchFundingResource;
use App\Models\ResearchFunding;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListResearchFundings extends ListRecords
{
    protected static string $resource = ResearchFundingResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }

    /**
     * Funding decisions grouped by their outcome.
     *
     * The first tab is what is waiting on staff, because that is the question
     * this screen is usually opened with. Each tab carries its own count, so
     * the shape of the queue is visible without clicking through it.
     */
    public function getTabs(): array
    {
        return [
            'pending' => Tab::make('Awaiting a decision')
                ->badge($this->countOf(['pending']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['pending'])),
            'approved' => Tab::make('Funded')
                ->badge($this->countOf(['approved', 'partial']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['approved', 'partial'])),
            'unfunded' => Tab::make('Unfunded')
                ->badge($this->countOf(['unfunded']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['unfunded'])),
            'all' => Tab::make('All'),
        ];
    }

    /** @param  array<int, string>  $statuses */
    protected function countOf(array $statuses): ?string
    {
        $count = ResearchFunding::query()->whereIn('status', $statuses)->count();

        return $count > 0 ? (string) $count : null;
    }
}
