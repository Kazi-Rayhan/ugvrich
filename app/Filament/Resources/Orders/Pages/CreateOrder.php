<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use Filament\Resources\Pages\CreateRecord;

/** An order entered directly, without a proposal (most come from "Convert to order"). */
class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data + ['created_by' => auth()->id()];
    }

    protected function afterCreate(): void
    {
        $this->record->recalculate();
    }

    protected function getRedirectUrl(): string
    {
        return OrderResource::getUrl('view', ['record' => $this->record]);
    }
}
