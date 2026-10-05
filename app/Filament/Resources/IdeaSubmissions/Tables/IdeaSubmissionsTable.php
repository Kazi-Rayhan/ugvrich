<?php

namespace App\Filament\Resources\IdeaSubmissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IdeaSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at?->format('j F Y, g:i A'))
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable()
                    ->weight('semibold')
                    ->limit(45)
                    ->description(fn ($record) => $record->name),

                TextColumn::make('category')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn (?string $state) => config('rich.idea_categories.'.$state, $state))
                    ->placeholder('—'),

                TextColumn::make('role')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state) => config('rich.idea_roles.'.$state, $state)),

                TextColumn::make('department')
                    ->badge()
                    ->color('gray')
                    ->placeholder('--')
                    ->tooltip(fn ($record) => $record->department_name),

                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('stage')
                    ->label('Journey stage')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (?string $state) => config('rich.startup_stages.'.$state, $state)),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title())
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'warning',
                        'in_review' => 'info',
                        'accepted' => 'success',
                        'declined' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'new' => 'New',
                    'in_review' => 'In review',
                    'accepted' => 'Accepted',
                    'on_hold' => 'On hold',
                    'declined' => 'Declined',
                ]),

                SelectFilter::make('stage')->label('Journey stage')->options(config('rich.startup_stages')),

                SelectFilter::make('category')->options(config('rich.idea_categories')),
                SelectFilter::make('role')->options(config('rich.idea_roles')),

                SelectFilter::make('department')->options(config('rich.departments')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
