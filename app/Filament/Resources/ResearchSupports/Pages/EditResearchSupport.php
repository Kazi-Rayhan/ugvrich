<?php

namespace App\Filament\Resources\ResearchSupports\Pages;

use App\Filament\Resources\ResearchSupports\ResearchSupportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditResearchSupport extends EditRecord
{
    protected static string $resource = ResearchSupportResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
