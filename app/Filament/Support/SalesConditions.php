<?php

namespace App\Filament\Support;

use App\Models\Service;
use App\Models\ServiceCategory;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

/**
 * The "scope and conditions" of a proposal or order: what exactly is being
 * delivered, in rows like "Number of pages — Up to 10 pages".
 *
 * Each category and service can hold a template of such rows. Choosing it on
 * a proposal copies its rows in (once, marked with where they came from), so
 * they can be adjusted for this client. Unchoosing it takes its rows back out,
 * unless they have been edited. The proposal keeps its own copy, as does the
 * order made from it.
 *
 * A row: ['source' => 'category:6' | 'service:39' | null, 'heading' => …, 'label' => …, 'details' => …]
 */
class SalesConditions
{
    /** The template editor on a category or service. */
    public static function templateEditor(): Repeater
    {
        return Repeater::make('proposal_conditions')
            ->label('Proposal conditions')
            ->helperText('Loaded into a proposal when this is chosen there, and adjusted for each client. E.g. "Number of pages" — "Up to 10 pages".')
            ->addActionLabel('Add a condition')
            ->reorderable()
            ->collapsible()
            ->itemLabel(fn (array $state) => $state['label'] ?? null)
            ->default([])
            ->defaultItems(0)
            ->schema([
                Grid::make(3)->schema([
                    TextInput::make('label')->required()->maxLength(120)->placeholder('Number of pages'),
                    Textarea::make('details')->rows(2)->required()->maxLength(1000)->placeholder('Up to 10 pages')->columnSpan(2),
                ]),
            ]);
    }

    /**
     * The conditions on a proposal or order, as a form: one section per area
     * or service, and in it one labelled field per condition ("Number of
     * pages", "Payment gateways", …), pre-filled from the template.
     *
     * The rows themselves live in `conditions` (kept whole by the hidden
     * field); the fields edit their `details` in place.
     */
    public static function editor(): Group
    {
        return Group::make([
            Hidden::make('conditions')->default([]),

            Group::make(fn (Get $get) => self::fields((array) $get('conditions'), (array) $get('categories'))),

            Actions::make([
                Action::make('addCondition')
                    ->label('Add a condition')
                    ->icon('heroicon-o-plus')
                    ->color('gray')
                    ->modalHeading('Add a condition')
                    ->modalWidth('lg')
                    ->schema([
                        TextInput::make('heading')
                            ->label('Section')
                            ->datalist(fn (Get $get) => collect((array) $get('conditions'))->pluck('heading')->filter()->unique()->values()->all())
                            ->default('Other conditions')
                            ->required()
                            ->maxLength(150)
                            ->helperText('An existing section, or a new one.'),
                        TextInput::make('label')->label('Condition')->required()->maxLength(120)->placeholder('e.g. Number of revisions'),
                        Textarea::make('details')->rows(3)->required()->maxLength(2000)->placeholder('e.g. Up to 3 rounds'),
                    ])
                    ->action(function (array $data, Get $get, Set $set) {
                        $rows = (array) $get('conditions');
                        $rows[(string) Str::uuid()] = [
                            'source' => null,
                            'heading' => $data['heading'],
                            'label' => $data['label'],
                            'details' => $data['details'],
                        ];
                        $set('conditions', $rows);
                    }),

                Action::make('reloadTemplates')
                    ->label('Reload templates')
                    ->icon('heroicon-o-arrow-path')
                    ->color('gray')
                    ->tooltip('Add back any template conditions of the chosen areas and services that were removed.')
                    ->action(fn (Get $get, Set $set) => $set('conditions', self::sync(
                        (array) $get('conditions'),
                        (array) $get('categories'),
                        self::serviceIds((array) $get('items')),
                    ))),
            ]),
        ]);
    }

    /**
     * One section per heading, one field per condition.
     *
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    protected static function fields(array $rows, array $categoryIds): array
    {
        $components = [];

        $groups = collect($rows)
            ->filter(fn ($row) => is_array($row))
            ->groupBy(fn (array $row) => filled($row['heading'] ?? null) ? $row['heading'] : 'Other conditions', preserveKeys: true);

        foreach ($groups as $heading => $groupRows) {
            $components[] = Section::make($heading)
                ->icon('heroicon-o-rectangle-stack')
                ->compact()
                ->collapsible()
                ->columns(2)
                ->schema($groupRows->map(fn (array $row, int|string $key) => Textarea::make("conditions.{$key}.details")
                    ->label($row['label'] ?? 'Condition')
                    ->rows(1)
                    ->autosize()
                    ->maxLength(2000)
                    ->hintAction(
                        Action::make('remove')
                            ->icon('heroicon-m-x-mark')
                            ->color('danger')
                            ->tooltip('Remove this condition')
                            ->action(function (Get $get, Set $set) use ($key) {
                                $rows = (array) $get('conditions');
                                unset($rows[$key]);
                                $set('conditions', $rows);
                            }),
                    ))->values()->all());
        }

        // Areas chosen that have no template yet: say so, rather than show nothing.
        $withRows = collect($rows)->pluck('source')->filter()->all();
        $missing = ServiceCategory::whereIn('id', array_filter(array_map('intval', $categoryIds)))
            ->get(['id', 'name'])
            ->reject(fn (ServiceCategory $category) => in_array('category:'.$category->id, $withRows, true))
            ->pluck('name');

        if ($components === []) {
            $components[] = Callout::make('No conditions yet')
                ->description($categoryIds === []
                    ? 'Choose the areas in "Services & pricing" first. Areas with a proposal template show their fields here.'
                    : 'The chosen areas have no proposal template yet. Add conditions below, or set up a template under Service Categories → Proposal template.')
                ->warning();
        } elseif ($missing->isNotEmpty()) {
            $components[] = Callout::make('No template for: '.$missing->implode(', '))
                ->description('Add their conditions with "Add a condition", or give them a template under Service Categories → Proposal template.')
                ->color('gray');
        }

        return $components;
    }

    /**
     * Bring the rows in line with what is chosen: add each chosen category's
     * and service's template rows not yet present, and drop untouched template
     * rows of anything no longer chosen. Hand-written and edited rows stay.
     *
     * @param  array<string, array<string, mixed>>  $rows
     * @param  array<int|string>  $categoryIds
     * @param  array<int|string>  $serviceIds
     * @return array<string, array<string, mixed>>
     */
    public static function sync(array $rows, array $categoryIds, array $serviceIds): array
    {
        $wanted = self::templates($categoryIds, $serviceIds);

        // Drop rows from sources no longer chosen, when they still match their template exactly.
        $all = self::templates(
            self::sourceIds($rows, 'category'),
            self::sourceIds($rows, 'service'),
        );

        foreach ($rows as $key => $row) {
            $source = $row['source'] ?? null;

            if (! $source || isset($wanted[$source])) {
                continue;
            }

            $untouched = collect($all[$source]['rows'] ?? [])->contains(
                fn (array $t) => trim((string) ($row['label'] ?? '')) === trim($t['label'])
                    && trim((string) ($row['details'] ?? '')) === trim($t['details']),
            );

            if ($untouched) {
                unset($rows[$key]);
            }
        }

        // Add template rows that aren't there yet (matched on source and label).
        foreach ($wanted as $source => $template) {
            foreach ($template['rows'] as $t) {
                $present = collect($rows)->contains(fn (array $row) => ($row['source'] ?? null) === $source
                    && mb_strtolower(trim((string) ($row['label'] ?? ''))) === mb_strtolower(trim($t['label'])));

                if (! $present) {
                    $rows[(string) Str::uuid()] = [
                        'source' => $source,
                        'heading' => $template['heading'],
                        'label' => $t['label'],
                        'details' => $t['details'],
                    ];
                }
            }
        }

        return $rows;
    }

    /** @return list<int> */
    public static function serviceIds(array $items): array
    {
        return collect($items)->pluck('service_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all();
    }

    /** @return array<string, array{heading: string, rows: list<array{label: string, details: string}>}> */
    protected static function templates(array $categoryIds, array $serviceIds): array
    {
        $templates = [];

        $categoryIds = array_filter(array_map('intval', $categoryIds));
        $serviceIds = array_filter(array_map('intval', $serviceIds));

        if ($categoryIds !== []) {
            foreach (ServiceCategory::whereIn('id', $categoryIds)->orderBy('sort_order')->get(['id', 'name', 'proposal_conditions']) as $category) {
                if ($rows = self::cleanRows($category->proposal_conditions)) {
                    $templates['category:'.$category->id] = ['heading' => (string) $category->name, 'rows' => $rows];
                }
            }
        }

        if ($serviceIds !== []) {
            foreach (Service::whereIn('id', $serviceIds)->get(['id', 'name', 'proposal_conditions']) as $service) {
                if ($rows = self::cleanRows($service->proposal_conditions)) {
                    $templates['service:'.$service->id] = ['heading' => (string) $service->name, 'rows' => $rows];
                }
            }
        }

        return $templates;
    }

    /** @return list<int> */
    protected static function sourceIds(array $rows, string $type): array
    {
        return collect($rows)
            ->pluck('source')
            ->filter(fn ($source) => is_string($source) && str_starts_with($source, $type.':'))
            ->map(fn (string $source) => (int) Str::after($source, ':'))
            ->unique()
            ->values()
            ->all();
    }

    /** @return list<array{label: string, details: string}> */
    protected static function cleanRows(mixed $rows): array
    {
        return collect(is_array($rows) ? $rows : [])
            ->filter(fn ($row) => is_array($row) && filled($row['label'] ?? null))
            ->map(fn (array $row) => ['label' => (string) $row['label'], 'details' => (string) ($row['details'] ?? '')])
            ->values()
            ->all();
    }

    /** The conditions on a view page, grouped under their headings. */
    public static function section(): \Filament\Schemas\Components\Section
    {
        return \Filament\Schemas\Components\Section::make('Scope & conditions')
            ->icon('heroicon-o-clipboard-document-list')
            ->collapsible()
            ->visible(fn ($record) => filled($record?->conditions))
            ->schema([
                \Filament\Infolists\Components\TextEntry::make('conditions_list')
                    ->hiddenLabel()
                    ->html()
                    ->state(function ($record) {
                        $html = '';

                        foreach (self::grouped($record->conditions) as $heading => $rows) {
                            $html .= '<p style="font-weight:700;margin:14px 0 6px">'.e($heading).'</p><dl style="display:grid;grid-template-columns:minmax(140px,220px) 1fr;gap:6px 18px;margin:0">';

                            foreach ($rows as $row) {
                                $html .= '<dt style="font-weight:600">'.e($row['label']).'</dt><dd style="margin:0;white-space:pre-line">'.e($row['details']).'</dd>';
                            }

                            $html .= '</dl>';
                        }

                        return $html;
                    }),
            ]);
    }

    /**
     * Rows grouped under their headings, for the view page and the print.
     *
     * @return array<string, list<array{label: string, details: string}>>
     */
    public static function grouped(?array $rows): array
    {
        return collect($rows ?? [])
            ->filter(fn ($row) => is_array($row) && filled($row['label'] ?? null))
            ->groupBy(fn (array $row) => filled($row['heading'] ?? null) ? $row['heading'] : 'General')
            ->map(fn ($group) => $group->map(fn (array $row) => ['label' => $row['label'], 'details' => (string) ($row['details'] ?? '')])->values()->all())
            ->all();
    }
}
