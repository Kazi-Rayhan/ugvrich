<?php

namespace App\Filament\Resources\StudentMemberships\Tables;

use App\Support\Vocabulary;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StudentMembershipsTable
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

                TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn ($record) => $record->reference),

                TextColumn::make('student_id')
                    ->label('ID')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('department')
                    ->formatStateUsing(fn (?string $state) => Vocabulary::label('departments', $state) ?? $state)
                    ->toggleable(),

                TextColumn::make('semester')
                    ->label('Sem.')
                    ->toggleable(),

                TextColumn::make('track')
                    ->label('Track')
                    ->formatStateUsing(fn (?string $state) => Vocabulary::label('internship_tracks', $state) ?? $state)
                    ->wrap(),

                TextColumn::make('phone')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-phone'),

                TextColumn::make('email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-envelope'),

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
                    'declined' => 'Declined',
                ]),
                SelectFilter::make('track')
                    ->label('Track')
                    ->options(fn () => Vocabulary::all('internship_tracks')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ]);
    }
}
