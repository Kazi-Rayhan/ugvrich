<?php

namespace App\Filament\Resources\ResearcherUsers\Pages;

use App\Filament\Resources\ResearcherUsers\ResearcherUserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateResearcherUser extends CreateRecord
{
    protected static string $resource = ResearcherUserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [...$data, 'role' => User::RESEARCHER];
    }

    protected function afterCreate(): void
    {
        $this->record->profile();
    }
}
