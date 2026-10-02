<?php

namespace App\Filament\Resources\ResearchFundings\Pages;

use App\Filament\Resources\ResearchFundings\ResearchFundingResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\CreateRecord;

class CreateResearchFunding extends CreateRecord
{
    protected static string $resource = ResearchFundingResource::class;
}
