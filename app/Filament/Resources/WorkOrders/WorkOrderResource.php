<?php

namespace App\Filament\Resources\WorkOrders;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Proposals\ProposalResource;
use App\Filament\Resources\WorkOrders\Pages\CreateWorkOrder;
use App\Filament\Resources\WorkOrders\Pages\EditWorkOrder;
use App\Filament\Resources\WorkOrders\Pages\ListWorkOrders;
use App\Models\WorkOrder;
use App\Support\Money;
use App\Support\Sales\WorkOrderConfirmation;
use BackedEnum;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

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
            ->visible(fn (WorkOrder $record) => WorkOrder::sector($record->sector) !== [])
            ->url(fn (WorkOrder $record) => route('sales.work-orders.print', $record))
            ->openUrlInNewTab();
    }

    public static function canEdit(Model $record): bool
    {
        return $record->status !== 'confirmed' && parent::canEdit($record);
    }

    public static function canDelete(Model $record): bool
    {
        return $record->status !== 'confirmed' && ! $record->order()->exists() && parent::canDelete($record);
    }

    public static function acceptAction(): Action
    {
        return Action::make('acceptWorkOrder')
            ->label('Accept')
            ->icon('heroicon-o-check-badge')
            ->authorize('update')
            ->visible(fn (WorkOrder $record) => in_array($record->status ?? 'draft', ['draft', 'issued', 'signed'], true))
            ->requiresConfirmation()
            ->action(function (WorkOrder $record): void {
                WorkOrder::whereKey($record->id)
                    ->where(fn ($query) => $query->whereNull('status')->orWhereIn('status', ['draft', 'issued', 'signed']))
                    ->update(['status' => 'accepted']);
                $record->refresh();
            });
    }

    public static function confirmAction(): Action
    {
        return Action::make('confirmWorkOrder')
            ->label('Confirm & create order')
            ->icon('heroicon-o-shopping-bag')
            ->color('success')
            ->authorize(fn (WorkOrder $record) => static::canEdit($record) && OrderResource::canCreate())
            ->visible(fn (WorkOrder $record) => in_array($record->status ?? 'draft', WorkOrder::CONFIRMABLE, true))
            ->requiresConfirmation()
            ->modalDescription('Confirm the saved work order and open the resulting order to record payments and print the invoice.')
            ->action(function (WorkOrder $record, Action $action): void {
                try {
                    $order = WorkOrderConfirmation::confirm($record, auth()->user());
                } catch (DomainException $exception) {
                    Notification::make()->title($exception->getMessage())->danger()->send();
                    $action->cancel();

                    return;
                }

                Notification::make()->title("Work order confirmed as {$order->number}")->success()->send();
                if (OrderResource::canView($order)) {
                    $action->redirect(OrderResource::getUrl('view', ['record' => $order]));
                } else {
                    $action->redirect(static::getUrl('index'));
                }
            });
    }

    public static function orderAction(): Action
    {
        return Action::make('openOrder')
            ->label('Open order')
            ->icon('heroicon-o-arrow-top-right-on-square')
            ->visible(fn (WorkOrder $record) => $record->order && Gate::allows('view', $record->order))
            ->url(fn (WorkOrder $record) => $record->order ? OrderResource::getUrl('view', ['record' => $record->order]) : null);
    }

    public static function editUrl(WorkOrder $workOrder): string
    {
        if ($workOrder->proposal && static::canEdit($workOrder) && ProposalResource::canEdit($workOrder->proposal)) {
            return ProposalResource::getUrl('edit', [
                'record' => $workOrder->proposal,
                'work_order' => $workOrder->getKey(),
            ]);
        }

        return static::getUrl('edit', ['record' => $workOrder]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('order'))
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('number')->label('Work order number')->searchable()->weight('semibold')->copyable(),
                TextColumn::make('client_name')
                    ->label('Client')
                    ->searchable()
                    ->description(fn (WorkOrder $record) => $record->detail('client.contact')),
                TextColumn::make('sector')
                    ->label('Sector')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state) => WorkOrder::sector($state)['code'] ?? $state)
                    ->tooltip(fn (?string $state) => config("work_order_admin.sectors.{$state}.department")),
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
                TextColumn::make('proposal.number')->label('Proposal')->placeholder('—')->toggleable()
                    ->url(fn (WorkOrder $record) => $record->proposal && ProposalResource::canView($record->proposal)
                        ? ProposalResource::getUrl('view', ['record' => $record->proposal])
                        : null),
            ])
            ->filters([
                SelectFilter::make('sector')->options(collect(config('work_orders.sectors'))->map(fn ($s) => $s['code'].' — '.$s['name'])->all()),
                SelectFilter::make('status')->options(WorkOrder::STATUSES),
            ])
            ->recordUrl(fn (WorkOrder $record) => static::canEdit($record) ? static::editUrl($record) : null)
            ->recordActions([
                self::printAction(),
                self::acceptAction(),
                self::confirmAction(),
                self::orderAction(),
                EditAction::make()->url(fn (WorkOrder $record) => static::editUrl($record)),
                DeleteAction::make(),
            ])
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
