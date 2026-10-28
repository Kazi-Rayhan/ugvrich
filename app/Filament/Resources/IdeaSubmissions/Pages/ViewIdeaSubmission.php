<?php

namespace App\Filament\Resources\IdeaSubmissions\Pages;

use App\Filament\Resources\IdeaSubmissions\IdeaSubmissionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

/** One idea, laid out to be read; handling it (stage, status, notes) is on Edit. */
class ViewIdeaSubmission extends ViewRecord
{
    protected static string $resource = IdeaSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->label('Handle'),
            DeleteAction::make(),
        ];
    }
}
