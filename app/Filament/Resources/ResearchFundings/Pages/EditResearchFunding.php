<?php

namespace App\Filament\Resources\ResearchFundings\Pages;

use App\Filament\Resources\ResearchFundings\ResearchFundingResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResearchFunding extends EditRecord
{
    protected static string $resource = ResearchFundingResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
