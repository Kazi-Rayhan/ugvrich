<?php

namespace App\Filament\Resources\WalletWithdrawals\Pages;

use App\Filament\Resources\WalletWithdrawals\WalletWithdrawalResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListWalletWithdrawals extends ListRecords
{
    protected static string $resource = WalletWithdrawalResource::class;

    public function getTitle(): string
    {
        return __('expert_wallet.withdrawal_admin');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('language')->label(app()->isLocale('bn') ? 'English' : __('consultancy_engagement.language_bn'))->color('gray')
                ->url(WalletWithdrawalResource::getUrl().'?language='.(app()->isLocale('bn') ? 'en' : 'bn')),
        ];
    }
}
