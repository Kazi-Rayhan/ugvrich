<?php

namespace App\Filament\Resources\Innovations\Pages;

use App\Filament\Resources\Innovations\InnovationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInnovation extends EditRecord
{
    protected static string $resource = InnovationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
