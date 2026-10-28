<?php

namespace App\Filament\Resources\Clients;

use App\Filament\Resources\Clients\Pages\CreateClient;
use App\Filament\Resources\Clients\Pages\EditClient;
use App\Filament\Resources\Clients\Pages\ListClients;
use App\Models\Client;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

/**
 * Clients of the consultancy. Usually made from the proposal form; kept here
 * so their details can be corrected and their proposals and orders found.
 * Email and phone are each unique, which is what keeps a client from being
 * entered twice.
 */
class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $modelLabel = 'Client';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Client')
                ->columns(2)
                ->schema(self::detailFields()),

            Section::make('Notes')
                ->schema([
                    Textarea::make('notes')->hiddenLabel()->rows(3),
                ]),
        ]);
    }

    /**
     * The client's details, shared with the "new client" part of the proposal form.
     *
     * @return array<int, \Filament\Forms\Components\Field>
     */
    public static function detailFields(bool $checkUnique = true): array
    {
        return [
            TextInput::make('name')
                ->label('Contact name')
                ->required()
                ->maxLength(150),

            TextInput::make('organization')
                ->maxLength(200),

            Select::make('organization_type')
                ->label('Organization type')
                ->options(Client::organizationTypeOptions())
                ->native(false),

            TextInput::make('email')
                ->email()
                ->maxLength(180)
                ->when($checkUnique, fn (TextInput $field) => $field->unique(Client::class, 'email', ignoreRecord: true)),

            TextInput::make('phone')
                ->tel()
                ->maxLength(30)
                ->regex('/^[+\d][\d\s\-()]{6,28}$/')
                ->validationMessages(['regex' => 'Enter a valid phone number.'])
                // $record is a Client here, but a Proposal when these fields sit in the proposal form.
                ->rule(fn ($record) => function (string $attribute, $value, \Closure $fail) use ($record, $checkUnique) {
                    if (! $checkUnique || blank($value)) {
                        return;
                    }

                    $taken = Client::where('phone_normalized', Client::normalizePhone($value))
                        ->when($record instanceof Client, fn ($q) => $q->whereKeyNot($record->id))
                        ->exists();

                    if ($taken) {
                        $fail('Another client already has this phone number.');
                    }
                }),

            Textarea::make('address')
                ->rows(2)
                ->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->label('Client')
                    ->weight('semibold')
                    ->searchable(['name', 'organization', 'email', 'phone'])
                    ->description(fn (Client $record) => $record->organization),

                TextColumn::make('phone')->copyable()->placeholder('Not given'),
                TextColumn::make('email')->copyable()->placeholder('Not given'),

                TextColumn::make('organization_type')
                    ->label('Type')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state) => Client::organizationTypeOptions()[$state] ?? $state)
                    ->toggleable(),

                TextColumn::make('proposals_count')->counts('proposals')->label('Proposals')->sortable(),
                TextColumn::make('orders_count')->counts('orders')->label('Orders')->sortable(),

                TextColumn::make('created_at')->label('Added')->date('j M Y')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('organization_type')->label('Type')->options(Client::organizationTypeOptions()),
            ])
            ->recordActions([EditAction::make()])
            ->emptyStateHeading('No clients yet')
            ->emptyStateDescription('Clients are added from the proposal form, or here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListClients::route('/'),
            'create' => CreateClient::route('/create'),
            'edit' => EditClient::route('/{record}/edit'),
        ];
    }
}
