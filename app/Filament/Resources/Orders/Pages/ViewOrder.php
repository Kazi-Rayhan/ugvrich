<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Support\PaymentActions;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

/** The order, its money, and (below) its payment history. */
class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Reloads the page so the money section and the history both show the new payment.
            PaymentActions::record(fn () => $this->record)
                ->after(fn () => $this->redirect(OrderResource::getUrl('view', ['record' => $this->record]))),
            Action::make('print')
                ->label('Print summary')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn (Order $record) => route('sales.orders.print', $record))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
