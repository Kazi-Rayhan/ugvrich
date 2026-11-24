<?php

namespace App\Filament\Resources\FinanceAccounts;

use App\Filament\Resources\FinanceAccounts\Pages\ListFinanceAccounts;
use App\Filament\Resources\FinanceTransactions\FinanceTransactionResource;
use App\Models\FinanceAccount;
use App\Support\Money;
use App\Support\Numerals;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class FinanceAccountResource extends Resource
{
    protected static ?string $model = FinanceAccount::class;

    protected static ?string $modelLabel = 'account';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(120)->unique(ignoreRecord: true),
            Select::make('type')->options(FinanceAccount::TYPES)->required()->live(),
            Section::make('Bangladesh bank details')
                ->statePath('details.bank')
                ->visible(fn (Get $get) => $get('type') === 'bank')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('bank_name')->maxLength(120),
                    TextInput::make('branch_name')->maxLength(120),
                    TextInput::make('district')->label('Branch district')->maxLength(120),
                    TextInput::make('account_name')->label('Account holder')->maxLength(120),
                    self::accountNumber('account_number', '/^[0-9]{6,30}$/D'),
                    self::accountNumber('routing_number', '/^[0-9]{9}$/D')->helperText('Enter the branch’s 9-digit routing number.'),
                ]),
            Section::make('Mobile banking details')
                ->statePath('details.mobile')
                ->visible(fn (Get $get) => $get('type') === 'mobile')
                ->columns(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('provider')->maxLength(120)->placeholder('e.g. bKash, Nagad, or Rocket'),
                    self::accountNumber('mobile_number', '/^(?:\\+880|0)1[0-9]{9}$/D')
                        ->helperText('Use 01XXXXXXXXX or +8801XXXXXXXXX.'),
                ]),
            Toggle::make('is_active')->label('Active')->default(true)
                ->helperText('Inactive accounts are closed to new entries. Their balances and history stay visible.'),
            Textarea::make('notes')->maxLength(2000)->columnSpanFull(),
        ]);
    }

    private static function accountNumber(string $name, string $pattern): TextInput
    {
        $normalize = fn ($state) => is_string($state)
            ? preg_replace('/[\s-]+/u', '', strtr($state, collect(range(0, 9))
                ->mapWithKeys(fn (int $digit) => [Numerals::localize($digit, 'bn') => (string) $digit])->all()))
            : $state;

        return TextInput::make($name)->inputMode('tel')->regex($pattern)
            ->mutateStateForValidationUsing($normalize)
            ->dehydrateStateUsing($normalize);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('type')->formatStateUsing(fn (string $state) => FinanceAccount::TYPES[$state]),
                TextColumn::make('balance_minor')->label('Balance (BDT)')->sortable()
                    ->formatStateUsing(fn ($state) => Money::format((int) $state, true))
                    ->summarize(Sum::make()->label('Total balance')->formatStateUsing(fn ($state) => Money::format((int) $state, true))),
                TextColumn::make('is_active')->label('Status')->badge()
                    ->formatStateUsing(fn (bool $state) => $state ? 'Active' : 'Inactive')
                    ->color(fn (bool $state) => $state ? 'success' : 'gray'),
            ])
            ->filters([
                SelectFilter::make('type')->options(FinanceAccount::TYPES),
                TernaryFilter::make('is_active')->label('Status')->trueLabel('Active')->falseLabel('Inactive'),
            ])
            ->recordActions([
                Action::make('statement')->label('Statement')
                    ->visible(fn () => FinanceTransactionResource::canViewAny())
                    ->url(fn (FinanceAccount $record) => FinanceTransactionResource::getUrl('index', [
                        'filters' => ['account' => ['id' => $record->id]],
                    ])),
                EditAction::make()
                    ->mutateRecordDataUsing(fn (array $data, FinanceAccount $record) => [
                        ...$data,
                        'details' => $record->details,
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListFinanceAccounts::route('/')];
    }
}
