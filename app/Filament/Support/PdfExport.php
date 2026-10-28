<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * "Export PDF" for an admin table: the rows the table shows (or the ones
 * ticked) open as a print-ready report in a new tab, where the browser's
 * "Save as PDF" makes the file. No PDF library is involved.
 *
 * The chosen ids and a line describing the filters are parked in the cache
 * for a few minutes under a random token that only the same user can open.
 */
class PdfExport
{
    /** Everything the table currently shows: its filters, search and sort. */
    public static function headerAction(string $route): Action
    {
        return Action::make('exportPdf')
            ->label('Export PDF')
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray')
            ->action(function ($livewire) use ($route) {
                $query = $livewire->getFilteredSortedTableQuery();
                $ids = $query->pluck($query->getModel()->getQualifiedKeyName())->all();

                self::open($livewire, $route, $ids, self::describeFilters($livewire));
            });
    }

    /** Only the ticked rows. */
    public static function bulkAction(string $route): BulkAction
    {
        return BulkAction::make('exportSelectedPdf')
            ->label('Export selected to PDF')
            ->icon('heroicon-o-document-arrow-down')
            ->deselectRecordsAfterCompletion()
            ->action(fn (Collection $records, $livewire) => self::open(
                $livewire,
                $route,
                $records->modelKeys(),
                $records->count().' selected record'.($records->count() === 1 ? '' : 's'),
            ));
    }

    /** @return array{ids: list<int>, scope: string}|null */
    public static function pull(string $token): ?array
    {
        $payload = Cache::get('pdf-export:'.$token);

        return $payload && ($payload['user_id'] ?? null) === auth()->id() ? $payload : null;
    }

    protected static function open($livewire, string $route, array $ids, string $scope): void
    {
        $token = Str::random(40);

        Cache::put('pdf-export:'.$token, [
            'user_id' => auth()->id(),
            'ids' => array_values(array_map('intval', $ids)),
            'scope' => $scope,
        ], now()->addMinutes(30));

        $livewire->js('window.open('.json_encode(route($route, ['token' => $token])).', "_blank")');
    }

    /** "Status: New · Category: Engineering · Search: “solar”", or "All records". */
    protected static function describeFilters($livewire): string
    {
        $parts = [];
        $table = $livewire->getTable();

        foreach ((array) ($livewire->tableFilters ?? []) as $name => $state) {
            $filter = $table->getFilter($name);
            $values = array_filter((array) ($state['values'] ?? [$state['value'] ?? null]), 'filled');

            if (! $filter || $values === [] || ! method_exists($filter, 'getOptions')) {
                continue;
            }

            $options = $filter->getOptions();
            $parts[] = $filter->getLabel().': '.collect($values)->map(fn ($v) => $options[$v] ?? $v)->implode(', ');
        }

        if (filled($search = $livewire->getTableSearch())) {
            $parts[] = 'Search: “'.$search.'”';
        }

        return $parts === [] ? 'All records' : implode(' · ', $parts);
    }
}
