<?php

namespace App\Filament\Support;

use App\Models\ConsultancyApplication as Application;
use App\Models\Order;
use App\Models\WorkOrder;
use App\Services\ConsultancyEngagement;
use App\Services\OrderDelivery;
use App\Support\Money;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;

class OrderDeliveryActions
{
    public static function assignExperts(): Action
    {
        return Action::make('assignExperts')
            ->label('Assign experts')
            ->icon('heroicon-o-user-group')
            ->authorize(fn (Order $record) => auth()->user()->can('update', $record) && ConsultancyEngagement::canAssign(auth()->user()))
            ->visible(fn (Order $record) => self::canPlan($record))
            ->fillForm(fn (Order $record) => [
                'experts' => $record->expertAssignments()->whereHas('application', fn ($query) => $query->withoutTrashed())
                    ->pluck('consultancy_application_id')->all(),
                'percentages' => $record->expertAssignments->pluck('engagement_percentage', 'consultancy_application_id')->all(),
            ])
            ->schema([
                Select::make('experts')->multiple()->searchable()->maxItems(30)->live()
                    ->options(fn () => Application::assignableToOrders()
                        ->orderBy('name')->get(['id', 'name', 'contract_type', 'engagement_percentage'])
                        ->mapWithKeys(fn (Application $expert) => [$expert->id => collect([
                            $expert->name,
                            Application::contractLabel($expert->contract_type ?? 'freelancer'),
                            $expert->contract_type && $expert->engagement_percentage !== null ? $expert->engagement_percentage.'%' : null,
                        ])->filter()->implode(' · ')])->all())
                    ->helperText('Each expert’s rate is divided by the number of assigned experts.'),
                Group::make(fn (Get $get): array => Application::assignableToOrders()
                    ->whereKey($get('experts') ?? [])->whereNull('contract_type')->orderBy('name')->get(['id', 'name'])
                    ->map(fn (Application $expert) => TextInput::make("percentages.{$expert->id}")
                        ->label("{$expert->name} — Short-term percentage")
                        ->required()->numeric()->minValue(0)->maxValue(100)->step(0.01)->suffix('%')->rule('decimal:0,2'))
                    ->all()),
            ])
            ->action(function (Order $record, array $data, OrderDelivery $delivery): void {
                $delivery->assign($record, auth()->user(), $data['experts'] ?? [], $data['percentages'] ?? []);
                $record->refresh();
                Notification::make()->title('Expert team saved')->success()->send();
            });
    }

    public static function group(): ActionGroup
    {
        return ActionGroup::make([
            Action::make('planMilestones')
                ->label('Plan milestones and tasks')
                ->icon('heroicon-o-rectangle-stack')
                ->authorize('update')
                ->visible(fn (Order $record) => self::canPlan($record))
                ->fillForm(fn (Order $record) => [
                    'milestones' => $record->delivery_milestones ?: ($record->workOrder?->deliveryMilestones() ?? []),
                ])
                ->schema([
                    Repeater::make('milestones')->defaultItems(0)->maxItems(20)->collapsible()
                        ->itemLabel(fn (array $state) => $state['title'] ?? 'Milestone')
                        ->addActionLabel('Add milestone')
                        ->schema([
                            Hidden::make('id'),
                            TextInput::make('title')->required()->maxLength(120),
                            DatePicker::make('due')->label('Due date')
                                ->default(fn (Order $record) => $record->expected_delivery_date ?? $record->workOrderDeliveryDate()),
                            Repeater::make('tasks')->defaultItems(0)->maxItems(20)->collapsible()
                                ->itemLabel(fn (array $state) => $state['title'] ?? 'Task')
                                ->addActionLabel('Add task')
                                ->schema([
                                    Hidden::make('id'),
                                    TextInput::make('title')->required()->maxLength(140),
                                    Textarea::make('description')->rows(2)->maxLength(600),
                                ])->columnSpanFull(),
                        ])->columns(2),
                ])
                ->action(function (Order $record, array $data, OrderDelivery $delivery): void {
                    $delivery->plan($record, auth()->user(), $data['milestones'] ?? []);
                    $record->refresh();
                    Notification::make()->title('Milestone plan saved')->success()->send();
                }),
            Action::make('completeMilestone')
                ->label('Complete a milestone')
                ->icon('heroicon-o-check-circle')
                ->authorize('update')
                ->visible(fn (Order $record) => $record->status !== 'cancelled' && self::openMilestones($record) !== [])
                ->modalDescription('Choose any milestone to complete. Its tasks will also be marked done.')
                ->schema([Select::make('milestone')->required()->options(fn (Order $record) => self::openMilestones($record))])
                ->action(function (Order $record, array $data, OrderDelivery $delivery): void {
                    $delivery->complete($record, auth()->user(), $data['milestone']);
                    $record->refresh();
                    Notification::make()->title('Milestone completed')->success()->send();
                }),
            Action::make('releaseExpertEarnings')
                ->label('Release wallet earnings')
                ->icon('heroicon-o-wallet')
                ->authorize(fn (Order $record) => auth()->user()->isAdmin() && auth()->user()->can('update', $record))
                ->visible(fn (Order $record) => $record->status !== 'cancelled' && $record->expertAssignments->isNotEmpty())
                ->requiresConfirmation()
                ->modalDescription('Credit any unreleased earnings earned from confirmed client payments and completed milestones. This fixes the team and milestone plan for this order.')
                ->action(function (Order $record, OrderDelivery $delivery): void {
                    $released = $delivery->release($record, auth()->user());
                    $record->refresh();
                    Notification::make()->title($released > 0 ? Money::format($released, true).' credited to expert wallets' : 'No new earnings to release')
                        ->success()->send();
                }),
        ])->label('Delivery')->icon('heroicon-o-rectangle-stack')->button()->color('gray');
    }

    private static function canPlan(Order $order): bool
    {
        return $order->status !== 'cancelled' && ! $order->expertAssignments->contains(fn ($row) => $row->credited_minor > 0);
    }

    private static function openMilestones(Order $order): array
    {
        return collect($order->delivery_milestones)->whereNull('completed_at')->pluck('title', 'id')->all();
    }
}
