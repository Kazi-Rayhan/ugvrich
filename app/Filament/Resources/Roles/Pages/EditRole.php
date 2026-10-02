<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\Pages\Concerns\SyncsPermissionGroups;
use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    use SyncsPermissionGroups;

    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()->hidden(fn () => $this->record->isAdmin())];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->fillPermissionGroups($data, $this->record);
    }

    protected function afterSave(): void
    {
        $this->syncPermissionGroups($this->record);
    }
}
