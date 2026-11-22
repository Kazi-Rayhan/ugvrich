<?php

namespace App\Filament\Resources\ConsultancyApplications\Pages;

use App\Filament\Resources\ConsultancyApplications\ConsultancyApplicationResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewConsultancyApplication extends ViewRecord
{
    protected static string $resource = ConsultancyApplicationResource::class;

    protected string $view = 'consultancy.review';

    public function getTitle(): string
    {
        return 'Application #'.$this->getRecord()->getKey();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('chain')->label('Chain of command')->color('gray')->icon('heroicon-o-queue-list')
                ->url(ConsultancyApplicationResource::getUrl('chain')),
            ConsultancyApplicationResource::decisionAction(true)->record($this->getRecord()),
            ConsultancyApplicationResource::decisionAction(false)->record($this->getRecord()),
        ];
    }
}
