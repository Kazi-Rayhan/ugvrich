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

class OrderDeliveryActions
{
    public static function group(): ActionGroup
    {
        return ActionGroup::make([
            Action::make('assignExperts')
                ->label('Assign experts')
                ->icon('heroicon-o-user-group')
                ->authorize(fn (Order $record) => auth()->user()->can('update', $record) && ConsultancyEngagement::canAssign(auth()->user()))
                ->visible(fn (Order $record) => self::canPlan($record))
                ->fillForm(fn (Order $record) => ['experts' => $record->expertAssignments->pluck('consultancy_application_id')->all()])
                ->schema([
                    Select::make('experts')->multiple()->searchable()->maxItems(30)
                        ->options(fn () => Application::where('status', Application::APPROVED)->whereNotNull('signed_at')
                            ->orderBy('name')->pluck('name', 'id'))
                        ->helperText('Only experts who have signed their engagement can be assigned.'),
                    TextInput::make('team_rate')->label('Team engagement rate')->numeric()->minValue(0)->maxValue(100)->step(0.01)->suffix('%')
                        ->helperText('Optional. A 50% team rate with two experts gives each 25%. Leave blank to use the saved or signed rates.'),
                ])
                ->action(function (Order $record, array $data, OrderDelivery $delivery): void {
                    $delivery->assign($record, auth()->user(), $data['experts'] ?? [], isset($data['team_rate']) ? (string) $data['team_rate'] : null);
                    $record->refresh();
                    Notification::make()->title('Expert team saved')->success()->send();
                }),
            Action::make('planMilestones')
                ->label('Plan milestones and tasks')
                ->icon('heroicon-o-rectangle-stack')
                ->authorize('update')
                ->visible(fn (Order $record) => self::canPlan($record))
                ->fillForm(fn (Order $record) => ['milestones' => $record->delivery_milestones ?? self::suggestedMilestones($record)])
                ->schema([
                    Repeater::make('milestones')->defaultItems(0)->maxItems(20)->collapsible()
                        ->itemLabel(fn (array $state) => $state['title'] ?? 'Milestone')
                        ->addActionLabel('Add milestone')
                        ->schema([
                            Hidden::make('id'),
                            TextInput::make('title')->required()->maxLength(120),
                            DatePicker::make('due')->label('Due date'),
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

    private static function suggestedMilestones(Order $order): array
    {
        return collect(config('work_order_admin.sectors.'.$order->workOrder?->sector.'.stages', []))
            ->map(fn (array $stage, int $index) => [
                'title' => $stage[0],
                'due' => $order->workOrder?->detail("stage_dates.{$index}"),
                'tasks' => [['title' => $stage[1]]],
            ])->all();
    }
}
