<?php

namespace App\Filament\Resources\ConsultancyEngagements\Pages;

use App\Filament\Resources\ConsultancyEngagementPolicies\ConsultancyEngagementPolicyResource;
use App\Filament\Resources\ConsultancyEngagements\ConsultancyEngagementResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListConsultancyEngagements extends ListRecords
{
    protected static string $resource = ConsultancyEngagementResource::class;

    public function getTitle(): string
    {
        return __('consultancy_engagement.engagements');
    }

    public function getSubheading(): ?string
    {
        return __('consultancy_engagement.list_hint');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('language')->label(app()->isLocale('bn') ? 'English' : 'বাংলা')->color('gray')
                ->url(ConsultancyEngagementResource::getUrl().'?language='.(app()->isLocale('bn') ? 'en' : 'bn')),
            Action::make('policies')->label(__('consultancy_engagement.policies'))->color('gray')->icon('heroicon-o-document-text')
                ->visible(fn () => ConsultancyEngagementPolicyResource::canViewAny())
                ->url(ConsultancyEngagementPolicyResource::getUrl()),
        ];
    }
}
