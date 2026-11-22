<?php

namespace App\Filament\Resources\WalletWithdrawals;

use App\Filament\Resources\WalletWithdrawals\Pages\ListWalletWithdrawals;
use App\Models\ExpertWalletEntry as Entry;
use App\Services\ExpertWallet;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WalletWithdrawalResource extends Resource
{
    protected static ?string $model = Entry::class;

    protected static ?string $slug = 'wallet-withdrawals';

    protected static ?string $modelLabel = 'Wallet withdrawal';

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('kind', Entry::WITHDRAWAL);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('application.name')->label(__('expert_wallet.expert'))->searchable(),
                TextColumn::make('amount_minor')->label(__('expert_wallet.amount'))->formatStateUsing(fn ($state) => Money::format((int) $state, true)),
                TextColumn::make('method')->label(__('expert_wallet.method'))->formatStateUsing(fn (string $state) => __('expert_wallet.'.$state)),
                TextColumn::make('status')->label(__('expert_wallet.status'))->badge()
                    ->color(fn (string $state) => match ($state) {
                        Entry::PAID => 'success',
                        Entry::REJECTED => 'danger',
                        Entry::APPROVED => 'info',
                        default => 'warning',
                    })->formatStateUsing(fn (string $state) => self::statuses()[$state]),
                TextColumn::make('reference')->label(__('expert_wallet.reference')),
                TextColumn::make('created_at')->label(__('expert_wallet.date'))->dateTime(),
            ])
            ->filters([SelectFilter::make('status')->label(__('expert_wallet.status'))->options(self::statuses())])
            ->recordActions([
                Action::make('payoutDetails')->label(__('expert_wallet.payout_details'))->icon('heroicon-o-credit-card')
                    ->authorize(fn () => self::canViewAny())
                    ->modalContent(fn (Entry $record) => view('consultancy.wallet-payout-details', ['entry' => $record]))
                    ->modalSubmitAction(false),
                self::decision('approve', Entry::APPROVED, [Entry::PENDING], 'info'),
                self::decision('reject', Entry::REJECTED, [Entry::PENDING, Entry::APPROVED], 'danger'),
                self::decision('mark_paid', Entry::PAID, [Entry::APPROVED], 'success'),
            ]);
    }

    private static function decision(string $action, string $status, array $from, string $color): Action
    {
        return Action::make($action)->label(__('expert_wallet.'.$action))->color($color)
            ->authorize(fn () => self::canViewAny())
            ->visible(fn (Entry $record) => in_array($record->status, $from, true))
            ->requiresConfirmation()
            ->modalDescription($status === Entry::PAID ? __('expert_wallet.manual_payout') : null)
            ->schema(match ($status) {
                Entry::PAID => [TextInput::make('reference')->label(__('expert_wallet.payment_reference'))->required()->maxLength(150)],
                Entry::REJECTED => [Textarea::make('note')->label(__('expert_wallet.decline_note'))->required()->maxLength(500)],
                default => [],
            })
            ->action(function (Entry $record, array $data) use ($status): void {
                app(ExpertWallet::class)->decide($record, auth()->user(), $status, $data['reference'] ?? null, $data['note'] ?? null);
                $record->refresh();
            })
            ->successNotificationTitle(__('expert_wallet.decision_saved'));
    }

    private static function statuses(): array
    {
        return [
            Entry::PENDING => __('expert_wallet.pending'),
            Entry::APPROVED => __('expert_wallet.approved'),
            Entry::PAID => __('expert_wallet.paid_status'),
            Entry::REJECTED => __('expert_wallet.rejected'),
        ];
    }

    public static function getPages(): array
    {
        return ['index' => ListWalletWithdrawals::route('/')];
    }
}
