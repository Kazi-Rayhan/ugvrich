<?php

namespace App\Filament\Resources\Projects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('image')->label('')->height(40)->width(64),

                TextColumn::make('code')
                    ->label('Project ID')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->size('sm'),

                TextColumn::make('title')
                    ->searchable()
                    ->weight('semibold')
                    ->wrap()
                    ->description(fn ($record) => $record->lead_name ?: $record->client),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => config('rich.project_types.'.$state, $state))
                    ->color(fn (string $state) => match ($state) {
                        'innovation' => 'primary',
                        'research' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('department')->badge()->color('gray')->placeholder('--'),

                TextColumn::make('stage')
                    ->label('Stage')
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => config('rich.pipeline_stages.'.$state, $state))
                    ->placeholder('--'),

                TextColumn::make('progress')
                    ->suffix('%')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('patent_status')
                    ->label('Patent / IP')
                    ->formatStateUsing(fn ($state) => config('rich.patent_statuses.'.$state, $state))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('commercialization_status')
                    ->label('Commercialization')
                    ->formatStateUsing(fn ($state) => config('rich.commercialization_statuses.'.$state, $state))
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('budget')->money('BDT')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deadline')->date()->sortable()->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'completed' => 'success',
                        'ongoing' => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('is_featured')->label('Featured')->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')->options(config('rich.project_types')),
                SelectFilter::make('department')->options(config('rich.departments')),
                SelectFilter::make('stage')->label('Pipeline stage')->options(config('rich.pipeline_stages')),

                SelectFilter::make('innovation_area_id')
                    ->label('Innovation area')
                    ->relationship('innovationArea', 'name')
                    ->preload(),

                SelectFilter::make('status')->options([
                    'ongoing' => 'Ongoing',
                    'completed' => 'Completed',
                    'planned' => 'Planned',
                ]),

                // Used by the dashboard menu to open the list pre-filtered.
                SelectFilter::make('ip')
                    ->label('Patent / IP')
                    ->options(['protected' => 'Has patent or IP status'])
                    ->query(fn ($query, array $data) => ($data['value'] ?? null) === 'protected'
                        ? $query->where('patent_status', '!=', 'none')
                        : $query),

                SelectFilter::make('commercialization_status')
                    ->label('Commercialization')
                    ->options(config('rich.commercialization_statuses')),

                TernaryFilter::make('is_featured')->label('Featured on homepage'),
            ])
            ->recordActions([
                \App\Filament\Support\ResearchReviewActions::setProjectStatus(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
