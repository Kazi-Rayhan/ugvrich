<?php

namespace App\Filament\Resources\ConsultancyApplications\Pages;

use App\Filament\Resources\ConsultancyApplications\ConsultancyApplicationResource;
use App\Filament\Resources\ConsultancyEngagements\ConsultancyEngagementResource;
use App\Models\ConsultancyApplication;
use App\Services\ConsultancyReview;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListConsultancyApplications extends ListRecords
{
    protected static string $resource = ConsultancyApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('chain')->label('Chain of command')->icon('heroicon-o-queue-list')
                ->url(ConsultancyApplicationResource::getUrl('chain')),
            Action::make('engagements')->label('Engagements')->icon('heroicon-o-briefcase')->color('gray')
                ->visible(fn () => ConsultancyEngagementResource::canViewAny())
                ->url(ConsultancyEngagementResource::getUrl()),
        ];
    }

    public function getSubheading(): ?string
    {
        $unassigned = [];
        foreach (ConsultancyApplication::REVIEW_STAGES as $stage => $label) {
            if (ConsultancyReview::reviewers($stage)->isEmpty()) {
                $unassigned[] = $label;
            }
        }

        return $unassigned
            ? 'Assign reviewers in Chain of command to enable reviews and email notifications. Stages without reviewers: '.implode(', ', $unassigned).'.'
            : 'The Consultancy Wing reviews first. The Director records the final decision. Engagement setup follows approval.';
    }
}
