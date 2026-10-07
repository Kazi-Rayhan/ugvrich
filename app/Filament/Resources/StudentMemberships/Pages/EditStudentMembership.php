<?php

namespace App\Filament\Resources\StudentMemberships\Pages;

use App\Filament\Resources\StudentMemberships\StudentMembershipResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStudentMembership extends EditRecord
{
    protected static string $resource = StudentMembershipResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
