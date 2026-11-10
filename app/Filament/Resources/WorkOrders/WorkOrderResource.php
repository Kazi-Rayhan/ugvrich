<?php

namespace App\Filament\Resources\WorkOrders;

use App\Filament\Resources\WorkOrders\Pages\CreateWorkOrder;
use App\Filament\Resources\WorkOrders\Pages\EditWorkOrder;
use App\Filament\Resources\WorkOrders\Pages\ListWorkOrders;
use App\Models\WorkOrder;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Service work orders (সেবা কার্যাদেশ): the controlled forms WO-ICT-01 …
 * WO-LAN-01, filled in here and printed exactly as the approved layout.
 */
class WorkOrderResource extends Resource
{
    protected static ?string $model = WorkOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $modelLabel = 'Work order';

    public static function form(Schema $schema): Schema
    {
        return WorkOrderForm::configure($schema);
    }

    public static function printAction(): Action
    {
        return Action::make('print')
            ->label('View / PDF')
            ->icon('heroicon-o-document-arrow-down')
            ->color('gray')
            ->url(fn (WorkOrder $record) => route('sales.work-orders.print', $record))
            ->openUrlInNewTab();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('number')->label('কার্যাদেশ নম্বর')->searchable()->weight('semibold')->copyable(),
                TextColumn::make('client_name')
                    ->label('Client')
                    ->searchable()
                    ->description(fn (WorkOrder $record) => $record->detail('client.contact')),
                TextColumn::make('sector')
                    ->label('Sector')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state) => WorkOrder::sector($state)['code'] ?? $state)
                    ->tooltip(fn (?string $state) => WorkOrder::sector($state)['department'] ?? null),
                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => WorkOrder::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => WorkOrder::STATUS_COLORS[$state] ?? 'gray')
                    ->sortable(),
                TextColumn::make('issue_date')->label('Issued')->date('j M Y')->sortable(),
                TextColumn::make('proposal.number')->label('Proposal')->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('sector')->options(collect(config('work_orders.sectors'))->map(fn ($s) => $s['code'].' — '.$s['name'])->all()),
                SelectFilter::make('status')->options(WorkOrder::STATUSES),
            ])
            ->recordUrl(fn (WorkOrder $record) => static::getUrl('edit', ['record' => $record]))
            ->recordActions([self::printAction(), EditAction::make(), DeleteAction::make()])
            ->emptyStateHeading('No work orders yet')
            ->emptyStateDescription('Create one here, or from a consultancy request or a proposal.');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'client_name'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkOrders::route('/'),
            'create' => CreateWorkOrder::route('/create'),
            'edit' => EditWorkOrder::route('/{record}/edit'),
        ];
    }
}
