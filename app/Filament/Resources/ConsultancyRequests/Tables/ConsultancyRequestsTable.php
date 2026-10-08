<?php

namespace App\Filament\Resources\ConsultancyRequests\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;

class ConsultancyRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->defaultPaginationPageOption(25)
            ->paginationPageOptions([25, 50, 100])

            /*
             | Six columns: received, requester, phone, email, area (the main
             | category only) and status. The rest is a toggle, and the meeting
             | the requester asked for is on the record itself.
             */
            ->columns([
                TextColumn::make('created_at')
                    ->label('Received')
                    ->since()
                    ->description(fn ($record) => $record->created_at?->format('j M Y'))
                    ->tooltip(fn ($record) => $record->created_at?->format('j F Y, g:i A'))
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Requester')
                    ->weight('semibold')
                    // Organisation and designation are still searchable, they
                    // are just not printed under the name any more - a second
                    // line there is what forced every row taller.
                    ->searchable(['name', 'organization', 'designation'])
                    ->tooltip(fn ($record) => collect([$record->designation, $record->organization_label])
                        ->filter()
                        ->implode(' · ') ?: null),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Phone copied')
                    ->placeholder('Not given'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Email copied')
                    ->placeholder('Not given'),

                // The main category only; the specific service is on the record itself.
                TextColumn::make('category.name')
                    ->label('Area')
                    ->badge()
                    ->color('gray')
                    ->placeholder('Not chosen')
                    ->wrap(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => str($state)->replace('_', ' ')->title())
                    ->color(fn (string $state) => match ($state) {
                        'new' => 'warning',
                        'in_review' => 'info',
                        'proposal_sent' => 'primary',
                        'accepted' => 'success',
                        'declined', 'closed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                // Read on demand: useful when scanning, too long to live here.
                TextColumn::make('requirement')
                    ->label('Requirement')
                    ->limit(80)
                    ->wrap()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('handled_at')
                    ->label('Handled')
                    ->dateTime('j M Y, g:i A')
                    ->placeholder('--')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            // Long lists read better in blocks; none is applied until chosen.
            ->groups([
                Group::make('status')
                    ->label('Status')
                    ->getTitleFromRecordUsing(fn ($record) => str($record->status)->replace('_', ' ')->title()),

                Group::make('category.name')
                    ->label('Area of consultancy'),

                Group::make('created_at')
                    ->label('Month received')
                    ->date(),
            ])

            ->filters([
                SelectFilter::make('status')
                    ->multiple()
                    ->options([
                        'new' => 'New',
                        'in_review' => 'In review',
                        'proposal_sent' => 'Proposal sent',
                        'accepted' => 'Accepted',
                        'declined' => 'Declined',
                        'closed' => 'Closed',
                    ]),

                SelectFilter::make('service_category_id')
                    ->label('Area of consultancy')
                    ->relationship('category', 'name')
                    ->preload(),

                Filter::make('has_meeting')
                    ->label('Asked for a meeting')
                    ->query(fn ($query) => $query->whereNotNull('preferred_date')),

                Filter::make('upcoming')
                    ->label('Meeting still ahead')
                    ->query(fn ($query) => $query->whereDate('preferred_date', '>=', today())),

                Filter::make('has_document')
                    ->label('Has attachment')
                    ->query(fn ($query) => $query->whereNotNull('document')),
            ])
            ->filtersFormColumns(2)

            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('No consultancy requests yet')
            ->emptyStateDescription('Requests sent through the public form land here.');
    }
}
