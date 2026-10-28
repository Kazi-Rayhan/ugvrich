<?php

namespace App\Filament\Support;

use App\Models\Order;
use App\Models\Payment;
use App\Support\Money;
use App\Support\Sales\PaymentRecorder;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Recording a payment and moving it on. All of the checks (outstanding balance,
 * duplicates, allowed status changes) live in PaymentRecorder, inside a locked
 * transaction; these actions only collect input and report the outcome.
 *
 *   record            create_payment
 *   confirm / failed  confirm_payment
 *   reverse           reverse_payment
 */
class PaymentActions
{
    /** @param  Closure(): Order  $order */
    public static function record(Closure $order): Action
    {
        return Action::make('recordPayment')
            ->authorize('create', Payment::class)
            ->label('Record payment')
            ->icon('heroicon-o-banknotes')
            ->color('success')
            ->visible(fn () => $order()->status !== 'cancelled' && $order()->outstandingMinor() > 0)
            ->modalHeading('Record a payment')
            ->modalDescription(fn () => 'Outstanding: '.Money::format($order()->amount_outstanding).'. Only confirmed payments reduce it.')
            ->modalSubmitActionLabel('Record payment')
            ->schema([
                TextInput::make('amount')
                    ->numeric()
                    ->required()
                    ->minValue(0.01)
                    ->step('0.01')
                    ->prefix(Money::CURRENCY)
                    ->maxValue(fn () => (float) $order()->amount_outstanding)
                    ->helperText('Cannot be more than the outstanding balance.'),
                DatePicker::make('paid_on')->label('Payment date')->default(now())->maxDate(now())->required(),
                Select::make('method')->options(Payment::METHODS)->required()->native(false),
                TextInput::make('transaction_id')->label('Transaction / reference no.')->maxLength(100),
                Select::make('status')
                    ->options([Payment::CONFIRMED => 'Confirmed (money received)', Payment::PENDING => 'Pending (to be verified)'])
                    ->default(Payment::CONFIRMED)
                    ->required()
                    ->native(false),
                FileUpload::make('receipt')
                    ->disk('local')
                    ->directory('payment-receipts')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(5120)
                    ->helperText('Optional. PDF or image, up to 5 MB. Stored privately.'),
                Textarea::make('notes')->rows(2)->columnSpanFull(),
            ])
            ->action(function (array $data, Action $action) use ($order) {
                try {
                    $payment = PaymentRecorder::record($order(), $data, auth()->user());
                } catch (ValidationException $e) {
                    Notification::make()->title(collect($e->errors())->flatten()->first())->danger()->send();
                    $action->halt();
                }

                Notification::make()
                    ->title("Payment {$payment->reference} recorded")
                    ->body(Money::format($payment->amount).' · '.Payment::STATUSES[$payment->status])
                    ->success()
                    ->send();
            });
    }

    public static function confirm(): Action
    {
        return Action::make('confirm')
            ->authorize('confirm')
            ->label('Confirm')
            ->icon('heroicon-o-check')
            ->color('success')
            ->requiresConfirmation()
            ->modalDescription('The money has been received. It will count towards the order from now on.')
            ->visible(fn (Payment $record) => $record->status === Payment::PENDING)
            ->action(fn (Payment $record) => self::attempt(fn () => PaymentRecorder::confirm($record, auth()->user()), 'Payment confirmed'));
    }

    public static function markFailed(): Action
    {
        return Action::make('fail')
            ->authorize('confirm')
            ->label('Failed')
            ->icon('heroicon-o-x-mark')
            ->color('gray')
            ->visible(fn (Payment $record) => $record->status === Payment::PENDING)
            ->schema([Textarea::make('note')->label('What happened?')->rows(2)])
            ->action(fn (Payment $record, array $data) => self::attempt(fn () => PaymentRecorder::markFailed($record, $data['note'] ?? null), 'Marked as failed'));
    }

    public static function reverse(): Action
    {
        return Action::make('reverse')
            ->authorize('reverse')
            ->label('Reverse')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('danger')
            ->visible(fn (Payment $record) => $record->status === Payment::CONFIRMED)
            ->modalHeading('Reverse this payment')
            ->modalDescription('For a refund or a payment entered by mistake. The payment stays in the history marked as reversed and no longer counts as received.')
            ->schema([Textarea::make('reason')->required()->rows(3)->maxLength(1000)])
            ->action(fn (Payment $record, array $data) => self::attempt(fn () => PaymentRecorder::reverse($record, $data['reason'], auth()->user()), 'Payment reversed'));
    }

    public static function receipt(): Action
    {
        return Action::make('receipt')
            ->authorize('view')
            ->label('Receipt')
            ->icon('heroicon-o-paper-clip')
            ->color('gray')
            ->visible(fn (Payment $record) => filled($record->receipt))
            ->action(fn (Payment $record) => Storage::disk('local')->download($record->receipt, $record->reference.'.'.pathinfo($record->receipt, PATHINFO_EXTENSION)));
    }

    protected static function attempt(Closure $step, string $success): void
    {
        try {
            $step();
            Notification::make()->title($success)->success()->send();
        } catch (ValidationException $e) {
            Notification::make()->title(collect($e->errors())->flatten()->first())->danger()->send();
        }
    }
}
