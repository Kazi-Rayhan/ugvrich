<?php

namespace App\Filament\Resources\Proposals\Pages;

use App\Filament\Pages\ListSalesDocuments;
use App\Filament\Resources\Proposals\ProposalResource;
use Filament\Actions\CreateAction;

class ListProposals extends ListSalesDocuments
{
    protected static string $resource = ProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('New proposal')];
    }
}
