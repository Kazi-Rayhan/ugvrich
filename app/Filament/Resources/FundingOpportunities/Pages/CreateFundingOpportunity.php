<?php

namespace App\Filament\Resources\FundingOpportunities\Pages;

use App\Filament\Resources\FundingOpportunities\FundingOpportunityResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\CreateRecord;

class CreateFundingOpportunity extends CreateRecord
{
    protected static string $resource = FundingOpportunityResource::class;
}
