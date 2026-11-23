<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\Proposals\ProposalResource;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkOrders extends ListRecords
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New work order')
                ->url(fn () => ProposalResource::canCreate()
                    ? ProposalResource::getUrl('create', ['work_order' => 1])
                    : WorkOrderResource::getUrl('create')),
        ];
    }
}
