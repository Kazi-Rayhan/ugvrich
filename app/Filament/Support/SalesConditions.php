<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

class SalesConditions
{
    public static function editor(): Group
    {
        return Group::make([
            Hidden::make('conditions')->default([]),

            Group::make(fn (Get $get) => self::fields((array) $get('conditions'))),

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
            ]),
        ]);
    }

    /**
     * One section per heading, one field per condition.
     *
     * @return array<int, \Filament\Schemas\Components\Component>
     */
    protected static function fields(array $rows): array
    {
        $components = [];

        $groups = collect($rows)
            ->filter(self::isManual(...))
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

        return $components;
    }

    /** The conditions on a view page, grouped under their headings. */
    public static function section(): \Filament\Schemas\Components\Section
    {
        return \Filament\Schemas\Components\Section::make('Scope & conditions')
            ->icon('heroicon-o-clipboard-document-list')
            ->collapsible()
            ->visible(fn ($record) => self::grouped($record?->conditions) !== [])
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
            ->filter(fn ($row) => self::isManual($row) && filled($row['label'] ?? null))
            ->groupBy(fn (array $row) => filled($row['heading'] ?? null) ? $row['heading'] : 'General')
            ->map(fn ($group) => $group->map(fn (array $row) => ['label' => $row['label'], 'details' => (string) ($row['details'] ?? '')])->values()->all())
            ->all();
    }

    private static function isManual(mixed $row): bool
    {
        return is_array($row) && ! Str::startsWith((string) ($row['source'] ?? ''), ['category:', 'service:']);
    }
}
