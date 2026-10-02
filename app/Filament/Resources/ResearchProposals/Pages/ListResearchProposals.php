<?php

namespace App\Filament\Resources\ResearchProposals\Pages;

use App\Filament\Resources\ResearchProposals\ResearchProposalResource;
use App\Models\ResearchProposal;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListResearchProposals extends ListRecords
{
    protected static string $resource = ResearchProposalResource::class;

    /**
     * Proposals grouped by where they stand.
     *
     * The first tab is what is waiting on staff, because that is the question
     * this screen is usually opened with. Each tab carries its own count, so
     * the shape of the queue is visible without clicking through it.
     */
    public function getTabs(): array
    {
        return [
            'waiting' => Tab::make('Needs a decision')
                ->badge($this->countOf(['submitted']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['submitted'])),
            'reviewing' => Tab::make('Being reviewed')
                ->badge($this->countOf(['under_review']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['under_review'])),
            'revision' => Tab::make('Back with the researcher')
                ->badge($this->countOf(['revision_required']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['revision_required'])),
            'approved' => Tab::make('Approved')
                ->badge($this->countOf(['approved']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['approved'])),
            'closed' => Tab::make('Not taken forward')
                ->badge($this->countOf(['rejected']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['rejected'])),
            'all' => Tab::make('All'),
        ];
    }

    /** @param  array<int, string>  $statuses */
    protected function countOf(array $statuses): ?string
    {
        $count = ResearchProposal::query()->whereIn('status', $statuses)->count();

        return $count > 0 ? (string) $count : null;
    }
}
