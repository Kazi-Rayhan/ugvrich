<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\Proposals\ProposalResource;
use App\Filament\Support\SalesConditions;
use App\Filament\Support\SalesLineItems;
use App\Models\Client;
use App\Models\Order;
use App\Models\User;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Orders: agreed work, with its progress and the money paid against it.
 *
 * The order's status is the work. Its payment status (unpaid, partially paid,
 * paid) is a separate thing, read from confirmed payments every time and never
 * stored, so the two can't disagree with the payment history.
 */
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?string $recordTitleAttribute = 'number';

    protected static ?string $modelLabel = 'Order';

    /** Only an order with no payments, and not made from a proposal, can be removed; others are cancelled. */
    public static function canDelete(Model $record): bool
    {
        return $record->proposal_id === null && ! $record->payments()->exists() && parent::canDelete($record);
    }

    public static function form(Schema $schema): Schema
    {
        $locked = fn (?Order $record) => $record?->exists && $record->linesAreLocked();

        return $schema->components([
            Section::make('Order')
                ->columns(3)
                ->schema([
                    Select::make('client_id')
                        ->label('Client')
                        ->relationship('client', 'name')
                        ->getOptionLabelFromRecordUsing(fn (Client $record) => $record->display_name)
                        ->searchable(['name', 'organization', 'email', 'phone'])
                        ->preload()
                        ->required()
                        // The client of an order made from a proposal is the proposal's.
                        ->disabled(fn (?Order $record) => $record?->proposal_id !== null)
                        ->columnSpan(2),

                    Select::make('status')
                        ->options(Order::STATUSES)
                        ->default('pending')
                        ->required()
                        ->native(false),

                    DatePicker::make('order_date')->label('Order date')->default(now())->required(),
                    DatePicker::make('expected_delivery_date')->label('Expected delivery')->afterOrEqual('order_date'),

                    Select::make('assigned_to')
                        ->label('Responsible staff')
                        ->options(fn () => User::query()->where('role', User::ADMIN)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()
                        ->native(false),
                ]),

            Section::make('Services')
                ->description(fn (?Order $record) => $record?->exists && $record->linesAreLocked()
                    ? 'Locked: payments have been recorded against these figures.'
                    : null)
                ->schema([
                    SalesLineItems::categories(),
                    SalesLineItems::items()->disabled($locked),
                ]),

            SalesLineItems::totals()->disabled($locked),

            Section::make('Scope & conditions')
                ->description('What is delivered: copied from the proposal, and kept up to date here if the agreement changes.')
                ->icon('heroicon-o-clipboard-document-list')
                ->collapsible()
                ->schema([SalesConditions::editor()]),

            Section::make('Terms and notes')
                ->columns(2)
                ->collapsible()
                ->schema([
                    Textarea::make('payment_terms')->rows(3),
                    Textarea::make('terms')->label('Terms and conditions')->rows(3),
                    Textarea::make('notes')->label('Internal notes')->rows(2)->columnSpanFull(),
                ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        $money = fn (string $label, callable $state) => TextEntry::make(str($label)->snake()->toString())
            ->label($label)
            ->state(fn (Order $record) => Money::format($state($record)));

        return $schema->components([
            Section::make()
                ->columns(4)
                ->schema([
                    TextEntry::make('number')->weight('bold')->copyable(),
                    TextEntry::make('status')
                        ->label('Work status')
                        ->badge()
                        ->formatStateUsing(fn (string $state) => Order::STATUSES[$state] ?? $state)
                        ->color(fn (string $state) => Order::STATUS_COLORS[$state] ?? 'gray'),
                    TextEntry::make('payment_status')
                        ->label('Payment status')
                        ->badge()
                        ->formatStateUsing(fn (string $state) => Order::PAYMENT_STATUSES[$state] ?? $state)
                        ->color(fn (string $state) => Order::PAYMENT_STATUS_COLORS[$state] ?? 'gray'),
                    TextEntry::make('assignee.name')->label('Responsible staff')->placeholder('Not assigned'),
                    TextEntry::make('order_date')->label('Order date')->date('j M Y'),
                    TextEntry::make('expected_delivery_date')->label('Expected delivery')->date('j M Y')->placeholder('—'),
                    TextEntry::make('proposal.number')
                        ->label('From proposal')
                        ->placeholder('Entered directly')
                        ->url(fn (Order $record) => $record->proposal ? ProposalResource::getUrl('view', ['record' => $record->proposal]) : null),
                    TextEntry::make('proposal.title')->label('Proposal title')->placeholder('—'),
                ]),

            Section::make('Money')
                ->columns(4)
                ->schema([
                    $money('Order total', fn (Order $o) => $o->total)->weight('bold'),
                    $money('Paid (confirmed)', fn (Order $o) => $o->amount_paid)->color('success'),
                    $money('Outstanding', fn (Order $o) => $o->amount_outstanding)
                        ->color(fn (Order $record) => $record->outstandingMinor() > 0 ? 'danger' : 'success')
                        ->weight('bold'),
                    TextEntry::make('pending_note')
                        ->label('Awaiting confirmation')
                        ->state(fn (Order $record) => Money::format(
                            $record->payments->where('status', 'pending')->sum(fn ($p) => Money::toMinor($p->amount)),
                            true,
                        )),
                ]),

            Section::make('Client')
                ->columns(4)
                ->schema([
                    TextEntry::make('client.name')->label('Name'),
                    TextEntry::make('client.organization')->label('Organization')->placeholder('—'),
                    TextEntry::make('client.phone')->label('Phone')->placeholder('—')->copyable(),
                    TextEntry::make('client.email')->label('Email')->placeholder('—')->copyable(),
                ]),

            Section::make('Services and agreed prices')
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

            ProposalResource::figures(),

            Section::make('Terms and notes')
                ->columns(3)
                ->collapsible()
                ->collapsed()
                ->schema([
                    TextEntry::make('payment_terms')->placeholder('—'),
                    TextEntry::make('terms')->label('Terms and conditions')->placeholder('—'),
                    TextEntry::make('notes')->label('Internal notes')->placeholder('—'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['client', 'payments']))
            ->columns([
                TextColumn::make('number')->searchable()->weight('semibold')->sortable(),

                TextColumn::make('client.name')
                    ->label('Client')
                    ->searchable(['name', 'organization'])
                    ->description(fn (Order $record) => $record->client?->organization),

                TextColumn::make('status')
                    ->label('Work')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Order::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => Order::STATUS_COLORS[$state] ?? 'gray')
                    ->sortable(),

                TextColumn::make('total')
                    ->formatStateUsing(fn ($state) => Money::format($state))
                    ->alignEnd()
                    ->sortable(),

                TextColumn::make('amount_paid')
                    ->label('Paid')
                    ->state(fn (Order $record) => Money::format($record->amount_paid))
                    ->alignEnd(),

                TextColumn::make('amount_outstanding')
                    ->label('Outstanding')
                    ->state(fn (Order $record) => Money::format($record->amount_outstanding))
                    ->color(fn (Order $record) => $record->outstandingMinor() > 0 ? 'danger' : 'success')
                    ->alignEnd(),

                TextColumn::make('payment_status')
                    ->label('Payment')
                    ->badge()
                    ->state(fn (Order $record) => $record->payment_status)
                    ->formatStateUsing(fn (string $state) => Order::PAYMENT_STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => Order::PAYMENT_STATUS_COLORS[$state] ?? 'gray'),

                TextColumn::make('expected_delivery_date')->label('Delivery')->date('j M Y')->sortable()->toggleable(),
                TextColumn::make('order_date')->label('Ordered')->date('j M Y')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('Work status')->multiple()->options(Order::STATUSES),
                SelectFilter::make('payment_status')
                    ->label('Payment status')
                    ->options(Order::PAYMENT_STATUSES)
                    ->query(fn (Builder $query, array $data) => self::wherePaymentStatus($query, $data['value'] ?? null)),
                SelectFilter::make('categories')->relationship('categories', 'name')->multiple()->preload(),
                SelectFilter::make('assigned_to')->label('Responsible staff')->relationship('assignee', 'name'),
            ])
            ->recordActions([ViewAction::make(), EditAction::make()])
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('Orders are made by converting an accepted proposal.');
    }

    /** Filter on the payment status that is otherwise worked out row by row. */
    public static function wherePaymentStatus(Builder $query, ?string $status): Builder
    {
        $paid = '(select coalesce(sum(p.amount), 0) from payments p where p.order_id = orders.id and p.status = \'confirmed\')';

        return match ($status) {
            'unpaid' => $query->whereRaw("{$paid} <= 0"),
            'partially_paid' => $query->whereRaw("{$paid} > 0")->whereRaw("{$paid} < orders.total"),
            'paid' => $query->whereRaw("{$paid} > 0")->whereRaw("{$paid} >= orders.total"),
            default => $query,
        };
    }

    public static function getRelations(): array
    {
        return [PaymentsRelationManager::class];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['number', 'client.name'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
