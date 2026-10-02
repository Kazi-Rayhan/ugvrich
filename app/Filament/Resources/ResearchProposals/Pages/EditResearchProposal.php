<?php

namespace App\Filament\Resources\ResearchProposals\Pages;

use App\Filament\Resources\ResearchProposals\ResearchProposalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResearchProposal extends EditRecord
{
    protected static string $resource = ResearchProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
