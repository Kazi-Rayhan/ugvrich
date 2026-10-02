<?php

namespace App\Filament\Resources\ResearchManuscripts\Pages;

use App\Filament\Resources\ResearchManuscripts\ResearchManuscriptResource;
use App\Models\ResearchManuscript;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListResearchManuscripts extends ListRecords
{
    protected static string $resource = ResearchManuscriptResource::class;

    /**
     * Papers grouped by where they are in their life.
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
            'reviewing' => Tab::make('In internal review')
                ->badge($this->countOf(['internal_review']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['internal_review'])),
            'revision' => Tab::make('Back with the author')
                ->badge($this->countOf(['revision_required']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['revision_required'])),
            'journal' => Tab::make('With the journal')
                ->badge($this->countOf(['approved', 'submitted_to_journal', 'journal_review']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['approved', 'submitted_to_journal', 'journal_review'])),
            'published' => Tab::make('Accepted or published')
                ->badge($this->countOf(['accepted', 'published']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['accepted', 'published'])),
            'closed' => Tab::make('Rejected')
                ->badge($this->countOf(['rejected']))
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['rejected'])),
            'all' => Tab::make('All'),
        ];
    }

    /** @param  array<int, string>  $statuses */
    protected function countOf(array $statuses): ?string
    {
        $count = ResearchManuscript::query()->whereIn('status', $statuses)->count();

        return $count > 0 ? (string) $count : null;
    }
}
