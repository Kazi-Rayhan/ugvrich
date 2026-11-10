<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\WorkOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkOrder extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    public function getTitle(): string
    {
        return $this->record->number;
    }

    protected function getHeaderActions(): array
    {
        return [
            WorkOrderResource::printAction()->label('View / Download PDF')->color('primary'),
            DeleteAction::make(),
        ];
    }
}
