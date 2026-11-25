<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\RelationManagers\PaymentsRelationManager;
use App\Filament\Support\OrderDeliveryActions;
use App\Filament\Support\PaymentActions;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\View;

/** The order, its money, and (below) its payment history. */
class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    public function getTitle(): string
    {
        return 'Order '.$this->record->number;
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return $this->getCachedRelationManagers() !== [];
    }

    public function getContentTabLabel(): string
    {
        return 'Overview';
    }

    public function content(Schema $schema): Schema
    {
        return $schema->record($this->record)->components([
            View::make('filament.orders.overview'),
            OrderResource::paymentSummary(),
            $this->hasCombinedRelationManagerTabsWithContent()
                ? $this->getRelationManagersContentComponent()
                : $this->getInfolistContentComponent(),
        ]);
    }

    public function deliveryDetailsAction(): Action
    {
        return Action::make('deliveryDetails')
            ->label('Edit delivery details')
            ->icon('heroicon-o-pencil-square')
            ->color('gray')
            ->button()
            ->record($this->record)
            ->authorize('update')
            ->fillForm(fn (Order $record): array => [
                'assigned_to' => $record->assigned_to,
                'expected_delivery_date' => $record->expected_delivery_date ?? $record->workOrderDeliveryDate(),
            ])
            ->schema([
                OrderResource::assigneeField(),
                DatePicker::make('expected_delivery_date')
                    ->label('Expected delivery')
                    ->minDate(fn (Order $record) => $record->order_date),
            ])
            ->action(function (Action $action, Order $record, array $data): void {
                $record->update($data);
                $record->refresh();
                $action->success();
            })
            ->successNotificationTitle('Delivery details updated');
    }

    protected function getHeaderActions(): array
    {
        return [
            // Reloads the page so the money section and the history both show the new payment.
            PaymentActions::record(fn () => $this->record)
                ->after(fn () => $this->redirect(OrderResource::getUrl('view', [
                    'record' => $this->record,
                    ...(PaymentsRelationManager::canViewForRecord($this->record, static::class) ? ['relation' => 'payments'] : []),
                ]))),
            Action::make('invoice')
                ->label('Print invoice')
                ->icon('heroicon-o-document-text')
                ->url(fn (Order $record) => route('sales.orders.invoice', $record))
                ->openUrlInNewTab(),
            OrderDeliveryActions::assignExperts(),
            OrderDeliveryActions::group(),
            ActionGroup::make([
                EditAction::make(),
                Action::make('print')
                    ->label('Print order summary')
                    ->icon('heroicon-o-printer')
                    ->url(fn (Order $record) => route('sales.orders.print', $record))
                    ->openUrlInNewTab(),
            ])->label('More')->button()->color('gray'),
        ];
    }
}
