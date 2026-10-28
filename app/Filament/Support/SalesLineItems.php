<?php

namespace App\Filament\Support;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Support\Money;
use App\Support\Sales\Totals;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

/**
 * The priced part of a proposal or order form, built once for both.
 *
 * Categories narrow the service picker; picking a service copies its name,
 * description and default price into the line, where they can be adjusted to
 * what was negotiated. The figures shown are a preview only: the saved ones
 * are recomputed on the server (HasSalesLines::recalculate).
 */
class SalesLineItems
{
    public static function categories(): Select
    {
        return Select::make('categories')
            ->label('Consultancy categories')
            ->relationship('categories', 'name')
            ->multiple()
            ->preload()
            ->searchable()
            ->live()
            ->required()
            // Each chosen category brings its template conditions into the proposal.
            ->afterStateUpdated(fn ($state, Get $get, Set $set) => $set('conditions', SalesConditions::sync(
                (array) $get('conditions'),
                (array) $state,
                SalesConditions::serviceIds((array) $get('items')),
            )))
            ->helperText('Pick every area this proposal covers. The service list and the conditions follow from it.')
            ->columnSpanFull();
    }

    public static function items(bool $locked = false): Repeater
    {
        return Repeater::make('items')
            ->relationship()
            ->orderColumn('sort')
            ->label('Services')
            ->addActionLabel('Add a service')
            ->minItems(1)
            ->required()
            ->live()
            ->disabled($locked)
            ->collapsible()
            ->itemLabel(fn (array $state) => $state['service_name'] ?? null)
            ->schema([
                Grid::make(12)->schema([
                    Select::make('service_id')
                        ->label('Service')
                        ->options(fn (Get $get) => self::serviceOptions((array) $get('../../categories')))
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($state, Get $get, Set $set) {
                            $service = $state ? Service::find($state) : null;

                            if (! $service) {
                                return;
                            }

                            // This line isn't in the items state yet with its new service, so add it by hand.
                            $set('../../conditions', SalesConditions::sync(
                                (array) $get('../../conditions'),
                                (array) $get('../../categories'),
                                [...SalesConditions::serviceIds((array) $get('../../items')), (int) $service->id],
                            ));

                            $set('service_name', $service->name);
                            $set('service_category_id', $service->service_category_id);
                            $set('description', $service->description);
                            $set('unit_price', $service->price !== null ? Money::fromMinor(Money::toMinor($service->price)) : null);
                        })
                        ->helperText('Choose categories above first.')
                        ->columnSpan(6),

                    TextInput::make('service_name')
                        ->label('Name on the proposal')
                        ->required()
                        ->maxLength(200)
                        ->columnSpan(6),

                    Hidden::make('service_category_id'),

                    Textarea::make('description')
                        ->rows(2)
                        ->columnSpanFull(),

                    TextInput::make('quantity')
                        ->numeric()
                        ->minValue(0.01)
                        ->step('0.01')
                        ->default(1)
                        ->required()
                        ->live(onBlur: true)
                        ->columnSpan(3),

                    TextInput::make('unit_price')
                        ->label('Unit price')
                        ->numeric()
                        ->minValue(0)
                        ->step('0.01')
                        ->prefix(Money::CURRENCY)
                        ->required()
                        ->live(onBlur: true)
                        ->columnSpan(3),

                    TextInput::make('discount')
                        ->label('Line discount')
                        ->numeric()
                        ->minValue(0)
                        ->step('0.01')
                        ->default(0)
                        ->prefix(Money::CURRENCY)
                        ->live(onBlur: true)
                        ->columnSpan(3),

                    TextEntry::make('line_total_preview')
                        ->label('Line total')
                        ->state(fn (Get $get) => self::safe(fn () => Money::format(
                            Totals::line($get('quantity'), $get('unit_price'), $get('discount'))['total'],
                            true,
                        )))
                        ->weight('bold')
                        ->columnSpan(3),
                ]),
            ]);
    }

    /** Extra discount, tax and the running figures. */
    public static function totals(bool $locked = false): Section
    {
        return Section::make('Totals')
            ->columns(2)
            ->schema([
                TextInput::make('extra_discount')
                    ->label('Extra discount on the whole')
                    ->numeric()
                    ->minValue(0)
                    ->step('0.01')
                    ->default(0)
                    ->prefix(Money::CURRENCY)
                    ->disabled($locked)
                    ->live(onBlur: true),

                TextInput::make('tax_rate')
                    ->label('Tax / VAT')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->step('0.01')
                    ->default(0)
                    ->suffix('%')
                    ->disabled($locked)
                    ->live(onBlur: true),

                TextEntry::make('totals_preview')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->html()
                    ->state(function (Get $get) {
                        $totals = self::safe(fn () => Totals::of((array) $get('items'), $get('extra_discount'), $get('tax_rate')));

                        if (! is_array($totals)) {
                            return 'Enter quantities and prices to see the totals.';
                        }

                        return sprintf(
                            'Subtotal <b>%s</b> &nbsp;·&nbsp; Discounts <b>%s</b> &nbsp;·&nbsp; Tax <b>%s</b> &nbsp;·&nbsp; Grand total <b style="font-size:1.1em">%s</b>',
                            e(Money::format($totals['subtotal'], true)),
                            e(Money::format($totals['discount_total'], true)),
                            e(Money::format($totals['tax_amount'], true)),
                            e(Money::format($totals['total'], true)),
                        );
                    }),
            ]);
    }

    /**
     * Active services in the chosen categories, grouped by category.
     *
     * @param  array<int|string>  $categoryIds
     * @return array<string, array<int, string>>
     */
    public static function serviceOptions(array $categoryIds): array
    {
        $categoryIds = array_filter(array_map('intval', $categoryIds));

        if ($categoryIds === []) {
            return [];
        }

        $names = ServiceCategory::whereIn('id', $categoryIds)->pluck('name', 'id');

        return Service::query()
            ->whereIn('service_category_id', $categoryIds)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'price', 'service_category_id'])
            ->groupBy(fn (Service $service) => $names[$service->service_category_id] ?? 'Other')
            ->map(fn ($services) => $services->mapWithKeys(fn (Service $service) => [
                $service->id => $service->name.($service->price !== null ? ' — '.Money::format($service->price) : ''),
            ])->all())
            ->all();
    }

    /** Half-typed numbers ("12.", "-") should blank the preview, not break the form. */
    protected static function safe(callable $callback): mixed
    {
        try {
            return $callback();
        } catch (\InvalidArgumentException) {
            return null;
        }
    }
}
