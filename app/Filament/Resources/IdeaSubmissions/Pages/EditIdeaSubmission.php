<?php

namespace App\Filament\Resources\IdeaSubmissions\Pages;

use App\Filament\Resources\IdeaSubmissions\IdeaSubmissionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditIdeaSubmission extends EditRecord
{
    protected static string $resource = IdeaSubmissionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (($data['status'] ?? null) === 'final_selected'
            && ! in_array($this->record->fresh()->status, ['primary_selected', 'final_selected'])) {
            throw ValidationException::withMessages([
                'data.status' => 'Mark this idea as primary selected before final selection.',
            ]);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
