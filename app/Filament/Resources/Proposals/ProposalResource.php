<?php

namespace App\Filament\Resources\Proposals;

use App\Filament\Resources\Clients\ClientResource;
use App\Filament\Resources\Proposals\Pages\CreateProposal;
use App\Filament\Resources\Proposals\Pages\EditProposal;
use App\Filament\Resources\Proposals\Pages\ListProposals;
use App\Filament\Resources\Proposals\Pages\ViewProposal;
use App\Filament\Support\SalesConditions;
use App\Filament\Support\SalesLineItems;
use App\Models\Client;
use App\Models\ConsultancyRequest;
use App\Models\Proposal;
use App\Support\Money;
use App\Support\Sales\Totals;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Proposals: what RICH offers a client, priced service by service.
 *
 * Written as a draft, sent, then accepted or rejected; an accepted proposal
 * becomes an order through the "Convert to order" action. Once it has been
 * accepted, rejected or converted it is a record and can no longer be edited.
 */
class ProposalResource extends Resource
{
    protected static ?string $model = Proposal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $modelLabel = 'Proposal';

    public static function canEdit(Model $record): bool
    {
        return $record->isEditable() && parent::canEdit($record);
    }

    /** Only a draft that never went anywhere can be removed. */
    public static function canDelete(Model $record): bool
    {
        return $record->status === 'draft' && parent::canDelete($record);
    }

    /** Editing: the same parts as the wizard, as tabs. */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('proposal')
                ->persistTabInQueryString()
                ->columnSpanFull()
                ->tabs(collect(self::parts())->map(fn (array $part) => Tab::make($part['title'])
                    ->icon($part['icon'])
                    ->schema($part['schema']))->all()),
        ]);
    }

    /**
     * Creating: a guided wizard, one decision per step.
     *
     * @return array<int, Step>
     */
    public static function steps(): array
    {
        return collect(self::parts())
            ->map(fn (array $part) => Step::make($part['title'])
                ->description($part['description'])
                ->icon($part['icon'])
                ->schema($part['schema']))
            ->all();
    }

    /**
     * The proposal form in five parts, shared by the wizard and the edit tabs.
     *
     * @return list<array{title: string, description: string, icon: string, schema: array}>
     */
    protected static function parts(): array
    {
        return [
            [
                'title' => 'Client',
                'description' => 'Who it is for',
                'icon' => 'heroicon-o-user',
                'schema' => [
                    Callout::make('Choose the client this proposal is for')
                        ->description('Search by name, organization, phone or email. For someone new, choose "New client". If their email or phone is already on file, that client is used rather than a duplicate being made.')
                        ->info()
                        ->visibleOn('create'),

                    ToggleButtons::make('client_mode')
                        ->hiddenLabel()
                        ->options(['existing' => 'Existing client', 'new' => 'New client'])
                        ->icons(['existing' => 'heroicon-o-magnifying-glass', 'new' => 'heroicon-o-user-plus'])
                        ->default('existing')
                        ->inline()
                        ->live()
                        ->hiddenOn('edit'),

                    Select::make('client_id')
                        ->label('Client')
                        ->relationship('client', 'name')
                        ->getOptionLabelFromRecordUsing(fn (Client $record) => $record->display_name
                            .($record->phone ? ' · '.$record->phone : ''))
                        ->searchable(['name', 'organization', 'email', 'phone'])
                        ->preload()
                        ->placeholder('Start typing a name, organization or phone…')
                        ->required(fn (Get $get) => $get('client_mode') !== 'new')
                        ->visible(fn (Get $get) => $get('client_mode') !== 'new'),

                    Section::make('New client')
                        ->icon('heroicon-o-user-plus')
                        ->compact()
                        ->schema([
                            Group::make(ClientResource::detailFields(checkUnique: false))
                                ->statePath('new_client')
                                ->columns(2),
                        ])
                        ->visible(fn (Get $get) => $get('client_mode') === 'new'),
                ],
            ],
            [
                'title' => 'Proposal',
                'description' => 'Title, dates, timeline',
                'icon' => 'heroicon-o-document-text',
                'schema' => [
                    Section::make()
                        ->columns(3)
                        ->schema([
                            TextInput::make('title')
                                ->label('Proposal title')
                                ->placeholder('e.g. E-commerce website for ABC Traders')
                                ->helperText('Printed at the top of the proposal.')
                                ->required()
                                ->maxLength(200)
                                ->columnSpanFull(),

                            DatePicker::make('issue_date')
                                ->label('Proposal date')
                                ->default(now())
                                ->required()
                                ->native(false),

                            DatePicker::make('valid_until')
                                ->label('Valid until')
                                ->default(now()->addDays(30))
                                ->afterOrEqual('issue_date')
                                ->native(false)
                                ->helperText('The offer stands until this date.'),

                            TextInput::make('delivery_timeline')
                                ->label('Estimated delivery')
                                ->placeholder('e.g. 6 weeks from start')
                                ->maxLength(150),

                            Select::make('consultancy_request_id')
                                ->label('Linked consultancy request')
                                ->relationship('consultancyRequest', 'name')
                                ->getOptionLabelFromRecordUsing(fn (ConsultancyRequest $record) => "#{$record->id} · {$record->name} · ".$record->created_at?->format('j M Y'))
                                ->searchable(['name', 'email', 'phone'])
                                ->placeholder('None')
                                ->helperText('Optional: the website request this proposal answers.')
                                ->columnSpanFull(),
                        ]),
                ],
            ],
            [
                'title' => 'Services & pricing',
                'description' => 'What is offered, at what price',
                'icon' => 'heroicon-o-squares-plus',
                'schema' => [
                    Callout::make('Pick the areas first, then add a line for each service')
                        ->description('Choosing a service fills in its name, description and default price; change any of them to what was agreed with the client. Totals update as you type.')
                        ->info()
                        ->visibleOn('create'),
                    Section::make('Areas')->compact()->schema([SalesLineItems::categories()]),
                    Section::make('Service lines')->compact()->schema([SalesLineItems::items()]),
                    SalesLineItems::totals(),
                ],
            ],
            [
                'title' => 'Scope & conditions',
                'description' => 'Exactly what is delivered',
                'icon' => 'heroicon-o-clipboard-document-list',
                'schema' => [
                    Callout::make('The specifics the client is agreeing to')
                        ->description('Each area and service you chose has its own section below, with a field for every condition in its template, pre-filled with the usual wording. Change each field to what was agreed with this client, remove what does not apply (×), or add your own.')
                        ->info(),
                    SalesConditions::editor(),
                ],
            ],
            [
                'title' => 'Terms & review',
                'description' => 'Payment terms, then check',
                'icon' => 'heroicon-o-check-badge',
                'schema' => [
                    Section::make('Terms')
                        ->columns(2)
                        ->schema([
                            Textarea::make('payment_terms')
                                ->rows(4)
                                ->placeholder("e.g. 50% advance on signing\n50% on delivery"),
                            Textarea::make('terms')
                                ->label('Terms and conditions')
                                ->rows(4)
                                ->placeholder('e.g. Prices are in BDT and exclude third-party fees.'),
                            Textarea::make('notes')
                                ->label('Internal notes')
                                ->helperText('For RICH staff only. Not printed.')
                                ->rows(2)
                                ->columnSpanFull(),
                        ]),

                    Section::make('Summary')
                        ->icon('heroicon-o-eye')
                        ->visibleOn('create')
                        ->schema([
                            TextEntry::make('review')
                                ->hiddenLabel()
                                ->html()
                                ->state(fn (Get $get) => self::reviewSummary($get)),
                        ]),
                ],
            ],
        ];
    }

    /** The last step's check before saving: who, what, how much. */
    protected static function reviewSummary(Get $get): string
    {
        $client = $get('client_mode') === 'new'
            ? trim(($get('new_client.name') ?? '').($get('new_client.organization') ? ' ('.$get('new_client.organization').')' : '')).' (new client)'
            : (Client::find($get('client_id'))?->display_name ?? 'Not chosen');

        $items = collect((array) $get('items'))->filter(fn ($item) => filled($item['service_name'] ?? null));

        try {
            $total = Money::format(Totals::of($items->all(), $get('extra_discount'), $get('tax_rate'))['total'], true);
        } catch (\InvalidArgumentException) {
            $total = '—';
        }

        $rows = [
            'Client' => e($client),
            'Title' => e($get('title') ?: '—'),
            'Valid until' => e($get('valid_until') ? Carbon::parse($get('valid_until'))->format('j M Y') : 'Open'),
            'Services' => $items->count().' line'.($items->count() === 1 ? '' : 's').($items->isNotEmpty() ? ': '.e($items->pluck('service_name')->implode(', ')) : ''),
            'Conditions' => count((array) $get('conditions')).' listed',
            'Grand total' => '<b style="font-size:1.15em">'.e($total).'</b>',
        ];

        return '<dl style="display:grid;grid-template-columns:140px 1fr;gap:8px 16px;margin:0">'
            .collect($rows)->map(fn ($value, $label) => '<dt style="opacity:.65">'.$label.'</dt><dd style="margin:0">'.$value.'</dd>')->implode('')
            .'</dl><p style="margin-top:14px;opacity:.65">It is saved as a <b>draft</b>. Check it, print it, then mark it as sent.</p>';
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(4)
                ->schema([
                    TextEntry::make('number')->weight('bold')->copyable(),
                    TextEntry::make('status')
                        ->badge()
                        ->formatStateUsing(fn (string $state) => Proposal::STATUSES[$state] ?? $state)
                        ->color(fn (string $state) => Proposal::STATUS_COLORS[$state] ?? 'gray'),
                    TextEntry::make('issue_date')->label('Date')->date('j M Y'),
                    TextEntry::make('valid_until')->label('Valid until')->date('j M Y')->placeholder('Open')
                        ->color(fn (Proposal $record) => $record->isPastValidity() && $record->isEditable() ? 'danger' : null),
                    TextEntry::make('title')->columnSpanFull()->weight('semibold'),
                    TextEntry::make('order.number')
                        ->label('Order')
                        ->url(fn (Proposal $record) => $record->order ? \App\Filament\Resources\Orders\OrderResource::getUrl('view', ['record' => $record->order]) : null)
                        ->visible(fn (Proposal $record) => $record->order !== null),
                ]),

            Section::make('Client')
                ->columns(4)
                ->schema([
                    TextEntry::make('client.name')->label('Name'),
                    TextEntry::make('client.organization')->label('Organization')->placeholder('—'),
                    TextEntry::make('client.phone')->label('Phone')->placeholder('—')->copyable(),
                    TextEntry::make('client.email')->label('Email')->placeholder('—')->copyable(),
                ]),

            Section::make('Services')
                ->schema([
                    TextEntry::make('categories.name')->label('Categories')->badge(),
                    RepeatableEntry::make('items')
                        ->hiddenLabel()
                        ->columns(6)
                        ->schema([
                            TextEntry::make('service_name')->label('Service')->columnSpan(2)
                                ->helperText(fn ($record) => $record->description),
                            TextEntry::make('quantity')->label('Qty'),
                            TextEntry::make('unit_price')->label('Unit price')->formatStateUsing(fn ($state) => Money::format($state)),
                            TextEntry::make('discount')->label('Discount')->formatStateUsing(fn ($state) => Money::format($state)),
                            TextEntry::make('line_total')->label('Line total')->weight('bold')->formatStateUsing(fn ($state) => Money::format($state)),
                        ]),
                ]),

            SalesConditions::section(),

            self::figures(),

            Section::make('Terms')
                ->columns(3)
                ->collapsible()
                ->schema([
                    TextEntry::make('delivery_timeline')->label('Estimated delivery')->placeholder('—'),
                    TextEntry::make('payment_terms')->placeholder('—'),
                    TextEntry::make('terms')->label('Terms and conditions')->placeholder('—'),
                    TextEntry::make('notes')->label('Internal notes')->placeholder('—')->columnSpanFull(),
                ]),
        ]);
    }

    /** The saved figures, shared with the order page. */
    public static function figures(): Section
    {
        $money = fn (string $field, string $label) => TextEntry::make($field)->label($label)->formatStateUsing(fn ($state) => Money::format($state));

        return Section::make('Figures')
            ->columns(4)
            ->schema([
                $money('subtotal', 'Subtotal'),
                $money('discount_total', 'Discounts'),
                TextEntry::make('tax_amount')->label('Tax')
                    ->formatStateUsing(fn ($state, $record) => Money::format($state).' ('.rtrim(rtrim((string) $record->tax_rate, '0'), '.').'%)'),
                $money('total', 'Grand total')->weight('bold')->size('lg'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('number')->searchable()->weight('semibold')->sortable(),

                TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable(['name', 'organization'])
                    ->description(fn (Proposal $record) => $record->client?->organization),

                TextColumn::make('title')->limit(40)->searchable()->toggleable(),

                TextColumn::make('total')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Proposal::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => Proposal::STATUS_COLORS[$state] ?? 'gray')
                    ->sortable(),

                TextColumn::make('valid_until')
                    ->label('Valid until')
                    ->date('j M Y')
                    ->color(fn (Proposal $record) => $record->isPastValidity() && $record->isEditable() ? 'danger' : null)
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('issue_date')->label('Date')->date('j M Y')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->multiple()->options(Proposal::STATUSES),
                SelectFilter::make('categories')->relationship('categories', 'name')->multiple()->preload(),
                SelectFilter::make('client_id')->label('Client')->relationship('client', 'name')->searchable(),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->emptyStateHeading('No proposals yet')
            ->emptyStateDescription('Write one with "New proposal", or start from a consultancy request.');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'title', 'client.name'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProposals::route('/'),
            'create' => CreateProposal::route('/create'),
            'view' => ViewProposal::route('/{record}'),
            'edit' => EditProposal::route('/{record}/edit'),
        ];
    }
}
