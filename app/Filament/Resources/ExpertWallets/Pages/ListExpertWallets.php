<?php

namespace App\Filament\Resources\ExpertWallets\Pages;

use App\Filament\Resources\ExpertWallets\ExpertWalletResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListExpertWallets extends ListRecords
{
    protected static string $resource = ExpertWalletResource::class;

    public function getTitle(): string
    {
        return __('expert_wallet.wallet_admin');
    }

    public function getSubheading(): string
    {
        return __('expert_wallet.available_to_experts');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('language')->label(app()->isLocale('bn') ? 'English' : __('consultancy_engagement.language_bn'))->color('gray')
                ->url(ExpertWalletResource::getUrl().'?language='.(app()->isLocale('bn') ? 'en' : 'bn')),
        ];
    }
}
