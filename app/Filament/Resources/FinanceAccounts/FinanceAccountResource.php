<?php

namespace App\Filament\Resources\FinanceAccounts;

use App\Filament\Resources\FinanceAccounts\Pages\ListFinanceAccounts;
use App\Filament\Resources\FinanceTransactions\FinanceTransactionResource;
use App\Models\FinanceAccount;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
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
            Select::make('type')->options(FinanceAccount::TYPES)->required(),
            Toggle::make('is_active')->label('Active')->default(true)
                ->helperText('Inactive accounts are closed to new entries. Their balances and history stay visible.'),
            Textarea::make('notes')->maxLength(2000)->columnSpanFull(),
        ]);
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
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListFinanceAccounts::route('/')];
    }
}
