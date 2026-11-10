<?php

namespace App\Filament\Support;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Proposal;
use App\Support\Sales\ProposalConverter;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use RuntimeException;

/**
 * The steps a proposal moves through. Each checks its permission on the
 * server (->authorize), not only by hiding the button:
 *
 *   send / reject / expire   update_proposal
 *   accept                   accept_proposal
 *   convert to order         convert_proposal
 */
class ProposalActions
{
    /** @return array<int, Action> */
    public static function all(): array
    {
        return [self::print(), self::workOrder(), self::send(), self::accept(), self::reject(), self::expire(), self::convert()];
    }

    public static function print(): Action
    {
        return Action::make('print')
            ->label('Print / PDF')
            ->icon('heroicon-o-printer')
            ->color('gray')
            ->url(fn (Proposal $record) => route('sales.proposals.print', $record))
            ->openUrlInNewTab();
    }

    /** The controlled work order (সেবা কার্যাদেশ) for this proposal, pre-filled from it. */
    public static function workOrder(): Action
    {
        return Action::make('workOrder')
            ->authorize('create', \App\Models\WorkOrder::class)
            ->label('Work order')
            ->icon('heroicon-o-clipboard-document-check')
            ->color('gray')
            ->url(fn (Proposal $record) => \App\Filament\Resources\WorkOrders\WorkOrderResource::getUrl('create', ['proposal' => $record->id]));
    }

    public static function send(): Action
    {
        return Action::make('send')
            ->authorize('update')
            ->label('Mark as sent')
            ->icon('heroicon-o-paper-airplane')
            ->color('info')
            ->requiresConfirmation()
            ->visible(fn (Proposal $record) => $record->status === 'draft')
            ->action(fn (Proposal $record) => self::move($record, ['draft'], ['status' => 'sent', 'sent_at' => now()], 'Marked as sent'));
    }

    public static function accept(): Action
    {
        return Action::make('accept')
            ->authorize('accept')
            ->label('Accept')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->modalDescription('The client has agreed to this proposal. It can no longer be edited once accepted.')
            ->visible(fn (Proposal $record) => in_array($record->status, ['draft', 'sent'], true))
            ->action(fn (Proposal $record) => self::move($record, ['draft', 'sent'], [
                'status' => 'accepted',
                'accepted_at' => now(),
                'accepted_by' => auth()->id(),
            ], 'Proposal accepted'));
    }

    public static function reject(): Action
    {
        return Action::make('reject')
            ->authorize('update')
            ->label('Rejected')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->requiresConfirmation()
            ->visible(fn (Proposal $record) => in_array($record->status, ['draft', 'sent'], true))
            ->action(fn (Proposal $record) => self::move($record, ['draft', 'sent'], ['status' => 'rejected'], 'Marked as rejected'));
    }

    public static function expire(): Action
    {
        return Action::make('expire')
            ->authorize('update')
            ->label('Mark expired')
            ->icon('heroicon-o-clock')
            ->color('warning')
            ->requiresConfirmation()
            ->visible(fn (Proposal $record) => $record->status === 'sent' && $record->isPastValidity())
            ->action(fn (Proposal $record) => self::move($record, ['sent'], ['status' => 'expired'], 'Marked as expired'));
    }

    public static function convert(): Action
    {
        return Action::make('convert')
            ->authorize('convert')
            ->label('Convert to order')
            ->icon('heroicon-o-shopping-bag')
            ->color('primary')
            ->requiresConfirmation()
            ->modalDescription('An order is created with this proposal\'s client, services, agreed prices and terms. The proposal is kept as it is.')
            ->visible(fn (Proposal $record) => $record->status === 'accepted')
            ->action(function (Proposal $record, Action $action) {
                try {
                    $order = ProposalConverter::convert($record, auth()->user());
                } catch (RuntimeException $e) {
                    Notification::make()->title($e->getMessage())->danger()->send();
                    $action->cancel();

                    return;
                }

                Notification::make()->title("Order {$order->number} created")->success()->send();
                $action->redirect(OrderResource::getUrl('view', ['record' => $order]));
            });
    }

    /** Change status only if it is still where the button expected, so two clicks can't skip a step. */
    protected static function move(Proposal $record, array $from, array $changes, string $message): void
    {
        $moved = Proposal::whereKey($record->id)->whereIn('status', $from)->update($changes + ['updated_at' => now()]);

        $moved
            ? Notification::make()->title($message)->success()->send()
            : Notification::make()->title('This proposal has already moved on. Reload to see its current status.')->warning()->send();

        $record->refresh();
    }
}
