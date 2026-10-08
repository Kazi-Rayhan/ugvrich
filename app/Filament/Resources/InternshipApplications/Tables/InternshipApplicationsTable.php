<?php

namespace App\Filament\Resources\InternshipApplications\Tables;

use App\Models\InternshipApplication;
use App\Support\Vocabulary;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InternshipApplicationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->tooltip(fn ($record) => $record->created_at?->format('j F Y, g:i A'))
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Applicant')
                    ->searchable(['name', 'email', 'university', 'department'])
                    ->weight('semibold')
                    ->description(fn ($record) => $record->reference.' · '.$record->university),

                TextColumn::make('phone')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-m-phone'),

                TextColumn::make('email')
                    ->copyable()
                    ->icon('heroicon-m-envelope')
                    ->toggleable(),

                TextColumn::make('track')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state) => Vocabulary::label('internship_tracks', $state) ?? $state)
                    ->wrap(),

                TextColumn::make('duration_months')
                    ->label('Duration')
                    ->formatStateUsing(fn ($state) => Vocabulary::label('internship_durations', (string) $state) ?? $state)
                    ->description(fn ($record) => $record->mode_label),

                TextColumn::make('start_date')
                    ->label('Start')
                    ->date('j M Y')
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => InternshipApplication::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'warning',
                        'shortlisted' => 'info',
                        'interview' => 'primary',
                        'accepted' => 'success',
                        'declined' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->multiple()->options(InternshipApplication::STATUSES),
                SelectFilter::make('track')->options(fn () => Vocabulary::all('internship_tracks')),
                SelectFilter::make('duration_months')->label('Duration')->options(fn () => Vocabulary::all('internship_durations')),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
