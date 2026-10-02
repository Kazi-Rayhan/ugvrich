<?php

namespace App\Filament\Resources\ResearchIdeas\Pages;

use App\Filament\Resources\ResearchIdeas\ResearchIdeaResource;
use App\Filament\Support\ResearchReviewActions;
use Filament\Resources\Pages\ViewRecord;

/**
 * One idea, with its review history underneath.
 *
 * The same three actions as the list, so a reviewer can read the whole thing
 * and decide in one place rather than going back to the table.
 */
class ViewResearchIdea extends ViewRecord
{
    protected static string $resource = ResearchIdeaResource::class;

    protected function getHeaderActions(): array
    {
        return ResearchReviewActions::all();
    }
}
