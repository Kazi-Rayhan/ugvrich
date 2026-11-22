<?php

namespace App\Filament\Resources\ConsultancyEngagementPolicies\Pages;

use App\Filament\Resources\ConsultancyEngagementPolicies\ConsultancyEngagementPolicyResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListConsultancyEngagementPolicies extends ListRecords
{
    protected static string $resource = ConsultancyEngagementPolicyResource::class;

    public function getTitle(): string
    {
        return __('consultancy_engagement.policies');
    }

    public function getSubheading(): ?string
    {
        return __('consultancy_engagement.edit_hint');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('language')->label(app()->isLocale('bn') ? 'English' : 'বাংলা')->color('gray')
                ->url(ConsultancyEngagementPolicyResource::getUrl().'?language='.(app()->isLocale('bn') ? 'en' : 'bn')),
        ];
    }
}
