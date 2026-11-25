<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\Proposals\ProposalResource;
use App\Filament\Resources\WorkOrders\WorkOrderResource;
use App\Filament\Support\SalesConditions;
use App\Filament\Support\SalesLineItems;
use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\WorkOrder;
use App\Models\User;
use App\Support\Money;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
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

    public static function getDeleteAuthorizationResponse(Model $record): Response
    {
        if ($record->proposal_id !== null || $record->work_order_id !== null
            || $record->payments()->exists() || $record->expertAssignments()->exists()) {
            return Response::deny();
        }

        return parent::getDeleteAuthorizationResponse($record);
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

                    self::assigneeField(),
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
                    Textarea::make('notes')->label('Internal notes')->rows(2)->columnSpanFull(),
                ]),
        ]);
    }

    public static function assigneeField(): Select
    {
        return Select::make('assigned_to')
            ->label('Responsible staff')
            ->options(fn () => User::query()->where('role', User::ADMIN)->orderBy('name')->pluck('name', 'id'))
            ->searchable()
            ->native(false);
    }

    public static function paymentSummary(): Section
    {
        $money = fn (string $field, string $label) => TextEntry::make($field)
            ->label($label)
            ->formatStateUsing(fn ($state) => Money::format($state))
            ->size('lg')
            ->weight('bold');

        return Section::make('Payment summary')
            ->icon('heroicon-o-banknotes')
            ->columns(['sm' => 2, 'lg' => 4])
            ->schema([
                $money('amount_outstanding', 'Balance due')
                    ->color(fn (Order $record) => $record->outstandingMinor() > 0 ? 'warning' : 'success'),
                $money('total', 'Order total'),
                $money('amount_paid', 'Payments received'),
                TextEntry::make('payment_status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Order::PAYMENT_STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => Order::PAYMENT_STATUS_COLORS[$state] ?? 'gray')
                    ->helperText(function (Order $record): ?string {
                        $pending = $record->payments->where('status', Payment::PENDING)
                            ->sum(fn (Payment $payment) => Money::toMinor($payment->amount));

                        return $pending > 0 ? Money::format($pending, true).' awaiting confirmation' : null;
                    }),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['lg' => 2])->columnSpanFull()->schema([
                Section::make('Order details')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns(['sm' => 2])
                    ->schema([
                        TextEntry::make('status')
                            ->label('Work status')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => Order::STATUSES[$state] ?? $state)
                            ->color(fn (string $state) => Order::STATUS_COLORS[$state] ?? 'gray'),
                        TextEntry::make('order_date')->label('Order date')->date('j M Y'),
                        TextEntry::make('workOrder.number')
                            ->label('Work order')
                            ->visible(fn (Order $record) => $record->work_order_id !== null)
                            ->url(fn (Order $record) => $record->workOrder && WorkOrder::sector($record->workOrder->sector) !== []
                                && WorkOrderResource::canView($record->workOrder)
                                ? route('sales.work-orders.print', $record->workOrder)
                                : null),
                        TextEntry::make('proposal.number')
                            ->label('Proposal')
                            ->visible(fn (Order $record) => $record->proposal_id !== null)
                            ->helperText(fn (Order $record) => $record->proposal?->title)
                            ->url(fn (Order $record) => $record->proposal && ProposalResource::canView($record->proposal)
                                ? ProposalResource::getUrl('view', ['record' => $record->proposal])
                                : null),
                    ]),
                Section::make('Client')
                    ->icon('heroicon-o-user')
                    ->columns(['sm' => 2])
                    ->schema([
                        TextEntry::make('client.name')->label('Name'),
                        TextEntry::make('client.organization')->label('Organization')
                            ->visible(fn (Order $record) => filled($record->client?->organization)),
                        TextEntry::make('client.phone')->label('Phone')->copyable()
                            ->visible(fn (Order $record) => filled($record->client?->phone)),
                        TextEntry::make('client.email')->label('Email')->copyable()
                            ->visible(fn (Order $record) => filled($record->client?->email)),
                        TextEntry::make('client.address')->label('Billing address')->columnSpanFull()
                            ->visible(fn (Order $record) => filled($record->client?->address)),
                    ]),
            ]),

            Section::make('Delivery and expert team')
                ->icon('heroicon-o-rectangle-stack')
                ->columnSpanFull()
                ->schema([View::make('filament.orders.delivery')]),

            Section::make('Itemized charges')
                ->icon('heroicon-o-list-bullet')
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('categories.name')->label('Categories')->badge()
                        ->visible(fn (Order $record) => $record->categories->isNotEmpty()),
                    RepeatableEntry::make('items')
                        ->hiddenLabel()
                        ->placeholder('No charges recorded')
                        ->table([
                            TableColumn::make('Service'),
                            TableColumn::make('Qty')->alignEnd(),
                            TableColumn::make('Unit price')->alignEnd(),
                            TableColumn::make('Discount')->alignEnd(),
                            TableColumn::make('Line total')->alignEnd(),
                        ])
                        ->schema([
                            TextEntry::make('service_name')->weight('medium')
                                ->helperText(fn ($record) => $record->description),
                            TextEntry::make('quantity')->alignEnd(),
                            TextEntry::make('unit_price')->alignEnd()->formatStateUsing(fn ($state) => Money::format($state)),
                            TextEntry::make('discount')->alignEnd()->formatStateUsing(fn ($state) => Money::format($state)),
                            TextEntry::make('line_total')->alignEnd()->weight('bold')->formatStateUsing(fn ($state) => Money::format($state)),
                        ]),
                    Grid::make(['sm' => 3])->schema([
                        TextEntry::make('subtotal')->formatStateUsing(fn ($state) => Money::format($state)),
                        TextEntry::make('discount_total')->label('Discounts')->formatStateUsing(fn ($state) => Money::format($state)),
                        TextEntry::make('tax_amount')->label('Tax / VAT')->formatStateUsing(fn ($state) => Money::format($state)),
                    ]),
                ]),

            SalesConditions::section()->collapsible()->collapsed(),

            Section::make('Terms and notes')
                ->icon('heroicon-o-document-text')
                ->collapsible()
                ->collapsed()
                ->visible(fn (Order $record) => filled($record->payment_terms) || filled($record->notes))
                ->schema([
                    TextEntry::make('payment_terms')->visible(fn (Order $record) => filled($record->payment_terms)),
                    TextEntry::make('notes')->label('Internal notes')->visible(fn (Order $record) => filled($record->notes)),
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
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->button()
                    ->authorizationTooltip()
                    ->authorizationMessage('Deleting this order requires delete permission and no linked proposal, work order, payments, or expert assignments.'),
            ])
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
        return ['payments' => PaymentsRelationManager::class];
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
