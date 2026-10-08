<?php

namespace App\Filament\Resources\InternshipApplications\Pages;

use App\Filament\Resources\InternshipApplications\InternshipApplicationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInternshipApplication extends EditRecord
{
    protected static string $resource = InternshipApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
