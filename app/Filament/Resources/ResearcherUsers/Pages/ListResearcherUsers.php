<?php

namespace App\Filament\Resources\ResearcherUsers\Pages;

use App\Filament\Resources\ResearcherUsers\ResearcherUserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResearcherUsers extends ListRecords
{
    protected static string $resource = ResearcherUserResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
