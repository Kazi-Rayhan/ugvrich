<?php

namespace App\Filament\Resources\Experts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ExpertsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                ImageColumn::make('photo')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?background=1f42f5&color=fff&name='.urlencode($record->name)),

                TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn ($record) => $record->designation),

                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                TextColumn::make('department')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('category.name')
                    ->label('Area')
                    ->badge()
                    ->color('primary')
                    ->placeholder('--'),

                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Visible')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(['expert' => 'Expert', 'administration' => 'Administration']),

                SelectFilter::make('service_category_id')
                    ->label('Area of consultancy')
                    ->relationship('category', 'name')
                    ->preload(),

                TernaryFilter::make('is_featured')->label('Featured on homepage'),
                TernaryFilter::make('is_active')->label('Visible on site'),
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
