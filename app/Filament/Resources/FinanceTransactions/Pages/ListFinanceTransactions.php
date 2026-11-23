<?php

namespace App\Filament\Resources\FinanceTransactions\Pages;

use App\Filament\Resources\FinanceTransactions\FinanceTransactionResource;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Services\FinanceLedger;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListFinanceTransactions extends ListRecords
{
    protected static string $resource = FinanceTransactionResource::class;

    public function table(Table $table): Table
    {
        return $table->pushFilters([SelectFilter::make('kind')->options(FinanceTransaction::KINDS)]);
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->recordAction(FinanceTransaction::INCOME, 'Record income'),
            $this->recordAction(FinanceTransaction::EXPENSE, 'Record expense'),
            $this->recordAction(FinanceTransaction::OPENING, 'Opening balance'),
            $this->exportAction(),
        ];
    }

    protected function exportAction(): Action
    {
        return Action::make('exportCsv')->label('Export CSV')->color('gray')
            ->authorize('viewAny', FinanceTransaction::class)
            ->action(function (): StreamedResponse {
                $query = $this->getFilteredSortedTableQuery();
                $filename = $this instanceof ListFinanceTransfers ? 'finance-transfers.csv' : 'finance-transactions.csv';

                return response()->streamDownload(function () use ($query): void {
                    $output = fopen('php://output', 'wb');
                    fputcsv($output, ['Entry', 'Date', 'Type', 'From', 'To', 'Amount (BDT)', 'Category', 'Reference', 'Reversal of', 'Recorded by', 'Notes'], escape: '');

                    foreach ($query->lazy() as $entry) {
                        $row = [
                            $entry->id,
                            $entry->transaction_date->toDateString(),
                            FinanceTransaction::KINDS[$entry->kind],
                            $entry->fromAccount?->name,
                            $entry->toAccount?->name,
                            Money::fromMinor($entry->amount_minor),
                            $entry->category,
                            $entry->reference,
                            $entry->reversal_of_id,
                            $entry->recorded_by_name,
                            $entry->notes,
                        ];
                        // Keep user-entered text from becoming a spreadsheet formula.
                        $row = array_map(fn ($value) => is_string($value) && preg_match('/^[\s\x00-\x1F]*[=+@-]/', $value)
                            ? "'".$value : $value, $row);
                        fputcsv($output, $row, escape: '');
                    }

                    fclose($output);
                }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
            });
    }

    protected function recordAction(string $kind, string $label): Action
    {
        return Action::make($kind)->label($label)
            ->authorize('create', FinanceTransaction::class)
            ->modalDescription($kind === FinanceTransaction::TRANSFER
                ? 'Record a transfer already made between your accounts.'
                : null)
            ->schema([
                Hidden::make('request_key')->default(fn () => (string) Str::uuid()),
                $this->accountSelect('from_account_id', 'Source account')
                    ->visible(in_array($kind, [FinanceTransaction::EXPENSE, FinanceTransaction::TRANSFER], true)),
                $this->accountSelect('to_account_id', 'Destination account')->visible($kind !== FinanceTransaction::EXPENSE),
                TextInput::make('amount')->label('Amount (BDT)')->numeric()->step('0.01')
                    ->required()->minValue(0.01)->regex(FinanceLedger::AMOUNT_PATTERN),
                DatePicker::make('transaction_date')->label('Transaction date')->required()
                    ->default(fn () => today('Asia/Dhaka')->toDateString())
                    ->maxDate(fn () => today('Asia/Dhaka')->toDateString()),
                TextInput::make('category')->maxLength(100)->visible(in_array($kind, [FinanceTransaction::INCOME, FinanceTransaction::EXPENSE], true)),
                TextInput::make('reference')->maxLength(150),
                Textarea::make('notes')->maxLength(2000),
            ])
            ->action(function (Action $action, array $data) use ($kind): void {
                try {
                    app(FinanceLedger::class)->post(auth()->user(), ['kind' => $kind] + $data);
                } catch (ValidationException $exception) {
                    Notification::make()->title($exception->validator->errors()->first())->danger()->send();
                    $action->halt();
                }
            })
            ->successNotificationTitle($kind === FinanceTransaction::TRANSFER ? 'Transfer posted' : 'Transaction posted');
    }

    private function accountSelect(string $field, string $label): Select
    {
        return Select::make($field)->label($label)->searchable()->required()
            ->options(fn () => FinanceAccount::where('is_active', true)->orderBy('name')->pluck('name', 'id'));
    }
}
