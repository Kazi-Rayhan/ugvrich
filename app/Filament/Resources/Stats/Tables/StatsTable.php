<?php

namespace App\Filament\Resources\Stats\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('value')
                    ->formatStateUsing(fn ($state, $record) => $state.$record->suffix)
                    ->weight('bold')
                    ->size('lg')
                    ->color('primary'),

                TextColumn::make('label')->searchable()->weight('semibold'),

                TextColumn::make('icon')->badge()->color('gray')->placeholder('--'),

                IconColumn::make('is_active')->label('Visible')->boolean(),
                IconColumn::make('show_on_home')->label('Home page')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
