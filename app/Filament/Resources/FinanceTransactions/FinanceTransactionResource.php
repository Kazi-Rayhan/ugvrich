<?php

namespace App\Filament\Resources\FinanceTransactions;

use App\Filament\Resources\FinanceTransactions\Pages\ListFinanceTransactions;
use App\Filament\Resources\FinanceTransactions\Pages\ListFinanceTransfers;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Services\FinanceLedger;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class FinanceTransactionResource extends Resource
{
    protected static ?string $model = FinanceTransaction::class;

    protected static ?string $modelLabel = 'transaction';

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['fromAccount', 'toAccount'])->withExists('reversal'))
            ->defaultSort('transaction_date', 'desc')
            ->columns([
                TextColumn::make('id')->label('Entry')->sortable(),
                TextColumn::make('transaction_date')->label('Date')->date()->sortable(),
                TextColumn::make('kind')->badge()->formatStateUsing(fn (string $state) => FinanceTransaction::KINDS[$state]),
                TextColumn::make('fromAccount.name')->label('From')->placeholder('—'),
                TextColumn::make('toAccount.name')->label('To')->placeholder('—'),
                TextColumn::make('amount_minor')->label('Amount (BDT)')->sortable()
                    ->formatStateUsing(fn ($state) => Money::format((int) $state, true)),
                TextColumn::make('category')->searchable()->placeholder('—'),
                TextColumn::make('reference')->searchable()->placeholder('—'),
                TextColumn::make('status')->badge()->state(fn (FinanceTransaction $record) => match (true) {
                    $record->reversal_of_id !== null => 'Reversal of #'.$record->reversal_of_id,
                    $record->reversal_exists => 'Reversed',
                    default => 'Posted',
                }),
                TextColumn::make('recorded_by_name')->label('Recorded by')->toggleable(),
                TextColumn::make('created_at')->label('Posted at')->dateTime(timezone: 'Asia/Dhaka')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes')->wrap()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('account')
                    ->schema([Select::make('id')->label('Account')->searchable()
                        ->options(fn () => FinanceAccount::orderBy('name')->pluck('name', 'id'))])
                    ->query(fn (Builder $query, array $data) => $query->when($data['id'] ?? null, fn (Builder $query, $id) => $query
                        ->where(fn (Builder $query) => $query->where('from_account_id', $id)->orWhere('to_account_id', $id)))),
                Filter::make('date')
                    ->schema([DatePicker::make('from')->label('From date'), DatePicker::make('until')->label('Through date')])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $date) => $query->whereDate('transaction_date', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, $date) => $query->whereDate('transaction_date', '<=', $date))),
            ])
            ->recordActions([
                Action::make('reverse')->color('danger')
                    ->authorize('reverse')
                    ->visible(fn (FinanceTransaction $record) => $record->reversal_of_id === null && ! $record->reversal_exists)
                    ->modalDescription('Post an offsetting entry dated today and keep the original in the ledger.')
                    ->schema([Textarea::make('reason')->required()->maxLength(2000)])
                    ->action(function (Action $action, FinanceTransaction $record, array $data): void {
                        try {
                            app(FinanceLedger::class)->reverse(auth()->user(), $record, $data['reason']);
                        } catch (ValidationException $exception) {
                            Notification::make()->title($exception->validator->errors()->first())->danger()->send();
                            $action->halt();
                        }
                    })->successNotificationTitle('Reversal posted'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFinanceTransactions::route('/'),
            'transfers' => ListFinanceTransfers::route('/transfers'),
        ];
    }
}
