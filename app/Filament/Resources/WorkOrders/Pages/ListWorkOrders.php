<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Pages\ListSalesDocuments;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use Filament\Actions\CreateAction;

class ListWorkOrders extends ListSalesDocuments
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('New work order')];
    }
}
