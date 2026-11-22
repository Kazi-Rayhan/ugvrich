<?php

namespace App\Filament\Resources\ExpertWallets;

use App\Filament\Resources\ExpertWallets\Pages\ListExpertWallets;
use App\Models\ConsultancyApplication as Application;
use App\Models\ExpertWalletEntry as Entry;
use App\Support\Money;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpertWalletResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $slug = 'expert-wallets';

    protected static ?string $modelLabel = 'Expert wallet';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('viewWallet', Application::class) ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', Application::APPROVED)->whereNotNull('signed_at')
            ->withSum(['walletEntries as credited_minor' => fn ($query) => $query->where('kind', Entry::CREDIT)->where('status', Entry::PAID)], 'amount_minor')
            ->withSum(['walletEntries as committed_minor' => fn ($query) => $query->where('kind', Entry::WITHDRAWAL)
                ->whereIn('status', [Entry::PENDING, Entry::APPROVED, Entry::PAID])], 'amount_minor');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('name')->label(__('expert_wallet.expert'))->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('available')->label(__('expert_wallet.available'))
                    ->state(fn (Application $record) => Money::format((int) $record->credited_minor - (int) $record->committed_minor, true)),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListExpertWallets::route('/')];
    }
}
