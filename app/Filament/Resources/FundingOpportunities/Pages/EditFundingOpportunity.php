<?php

namespace App\Filament\Resources\FundingOpportunities\Pages;

use App\Filament\Resources\FundingOpportunities\FundingOpportunityResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFundingOpportunity extends EditRecord
{
    protected static string $resource = FundingOpportunityResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
