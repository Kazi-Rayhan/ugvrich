<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Models\WorkOrder;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkOrder extends EditRecord
{
    protected static string $resource = WorkOrderResource::class;

    protected ?bool $hasDatabaseTransactions = true;

    protected function beforeSave(): void
    {
        $workOrder = WorkOrder::whereKey($this->record->id)->lockForUpdate()->firstOrFail();

        abort_unless(WorkOrderResource::canEdit($workOrder), 403);
    }

    public function getTitle(): string
    {
        return $this->record->number;
    }

    protected function getHeaderActions(): array
    {
        return [
            WorkOrderResource::printAction()->label('View / Download PDF')->color('primary'),
            WorkOrderResource::acceptAction(),
            WorkOrderResource::confirmAction(),
            DeleteAction::make(),
        ];
    }
}
