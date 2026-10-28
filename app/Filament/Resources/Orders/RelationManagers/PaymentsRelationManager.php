<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Support\PaymentActions;
use App\Models\Payment;
use App\Support\Money;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * The order's payment history, oldest first. Read-only: payments are added
 * with "Record payment" and only ever move forward (confirm, fail, reverse),
 * never edited or deleted.
 */
class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Payment history';

    public function isReadOnly(): bool
    {
        return false;
    }

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->can('viewAny', Payment::class) ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reference')
            ->defaultSort('paid_on')
            ->modifyQueryUsing(fn ($query) => $query->with(['recorder', 'confirmer', 'reverser']))
            ->columns([
                TextColumn::make('reference')->weight('semibold')->copyable()->searchable(),
                TextColumn::make('paid_on')->label('Date')->date('j M Y')->sortable(),
                TextColumn::make('amount')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd()
                    ->weight('semibold')
                    ->color(fn (Payment $record) => $record->status === Payment::CONFIRMED ? null : 'gray'),
                TextColumn::make('method')->formatStateUsing(fn (string $state) => Payment::METHODS[$state] ?? $state),
                TextColumn::make('transaction_id')->label('Transaction ID')->placeholder('—')->copyable()->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Payment::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => Payment::STATUS_COLORS[$state] ?? 'gray')
                    ->tooltip(fn (Payment $record) => match ($record->status) {
                        Payment::CONFIRMED => 'Confirmed by '.($record->confirmer?->name ?? '—').' on '.$record->confirmed_at?->format('j M Y, g:i A'),
                        Payment::REVERSED => 'Reversed by '.($record->reverser?->name ?? '—').' on '.$record->reversed_at?->format('j M Y').': '.$record->reversal_reason,
                        default => null,
                    }),
                TextColumn::make('notes')->limit(40)->tooltip(fn (Payment $record) => $record->notes)->placeholder('—')->toggleable(),
                TextColumn::make('recorder.name')->label('Recorded by')->description(fn (Payment $record) => $record->created_at?->format('j M Y, g:i A')),
            ])
            // "Record payment" sits in the order page's header. Each step here
            // reloads that page, so its money section matches the history.
            ->recordActions([
                PaymentActions::confirm()->after($this->reloadOrder(...)),
                PaymentActions::markFailed()->after($this->reloadOrder(...)),
                PaymentActions::reverse()->after($this->reloadOrder(...)),
                PaymentActions::receipt(),
            ])
            ->emptyStateHeading('No payments yet')
            ->emptyStateDescription('Use "Record payment" at the top of the page when money comes in. Each instalment is kept here.');
    }

    public function reloadOrder(): void
    {
        $this->redirect(OrderResource::getUrl('view', ['record' => $this->getOwnerRecord()]));
    }
}
