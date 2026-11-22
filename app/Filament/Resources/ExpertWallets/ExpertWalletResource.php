<?php

namespace App\Filament\Resources\ExpertWallets;

use App\Filament\Resources\ExpertWallets\Pages\ListExpertWallets;
use App\Models\ConsultancyApplication as Application;
use App\Models\ExpertWalletEntry as Entry;
use App\Services\ExpertWallet;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class ExpertWalletResource extends Resource
{
    protected static ?string $model = Application::class;

    protected static ?string $slug = 'expert-wallets';

    protected static ?string $modelLabel = 'Expert wallet';

    public static function canViewAny(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
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
            ])
            ->recordActions([
                Action::make('credit')->label(__('expert_wallet.credit_wallet'))->icon('heroicon-o-plus-circle')
                    ->authorize(fn () => self::canViewAny())
                    ->schema([
                        TextInput::make('amount')->label(__('expert_wallet.amount'))
                            ->numeric()->required()->minValue(0.01)->maxValue(9999999999.99)->step(0.01)->rule('decimal:0,2')->prefix('BDT'),
                        TextInput::make('reference')->label(__('expert_wallet.reference'))->required()->maxLength(150),
                    ])
                    ->action(function (Application $record, array $data, ListExpertWallets $livewire): void {
                        try {
                            app(ExpertWallet::class)->credit($record, auth()->user(), $data['amount'], $data['reference']);
                        } catch (ValidationException $exception) {
                            $path = $livewire->getSchema($livewire->getMountedActionSchemaName())->getStatePath();
                            $errors = [];
                            foreach ($exception->errors() as $field => $messages) {
                                $errors[$path.'.'.$field] = $messages;
                            }

                            throw ValidationException::withMessages($errors);
                        }
                    })
                    ->successNotificationTitle(__('expert_wallet.credit_saved')),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListExpertWallets::route('/')];
    }
}
