<?php

namespace App\Filament\Resources\ResearchManuscripts\Pages;

use App\Filament\Resources\ResearchManuscripts\ResearchManuscriptResource;
use App\Filament\Support\ResearchReviewActions;
use Filament\Resources\Pages\ViewRecord;

class ViewResearchManuscript extends ViewRecord
{
    protected static string $resource = ResearchManuscriptResource::class;

    protected function getHeaderActions(): array
    {
        return ResearchReviewActions::forManuscripts();
    }
}
