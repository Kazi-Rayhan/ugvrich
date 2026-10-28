<?php

namespace App\Filament\Resources\Proposals\Pages;

use App\Filament\Resources\Proposals\ProposalResource;
use App\Filament\Support\ProposalActions;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewProposal extends ViewRecord
{
    protected static string $resource = ProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [...ProposalActions::all(), EditAction::make()];
    }
}
