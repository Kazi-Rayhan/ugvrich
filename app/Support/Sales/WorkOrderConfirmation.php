<?php

namespace App\Support\Sales;

use App\Models\Client;
use App\Models\Order;
use App\Models\Proposal;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\OrderDelivery;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use DomainException;

class WorkOrderConfirmation
{
    public static function confirm(WorkOrder $workOrder, User $user): Order
    {
        return DB::transaction(function () use ($workOrder, $user): Order {
            $locked = WorkOrder::whereKey($workOrder->id)->lockForUpdate()->firstOrFail();

            if ($order = $locked->order()->first()) {
                return $order;
            }

            if (! in_array($locked->status ?? 'draft', WorkOrder::CONFIRMABLE, true)) {
                throw new DomainException('This work order can no longer be confirmed.');
            }

            $order = $locked->proposal_id
                ? self::fromProposal($locked, $user)
                : self::fromWorkOrder($locked, $user);

            $order->workOrder()->associate($locked);
            $order->expected_delivery_date ??= $order->workOrderDeliveryDate();
            $order->delivery_milestones ??= OrderDelivery::initialWorkOrderPlan($locked);
            if ($order->status === 'pending') {
                $order->status = 'confirmed';
            }
            $order->save();
            $locked->update(['status' => 'confirmed']);

            return $order;
        });
    }

    private static function fromProposal(WorkOrder $workOrder, User $user): Order
    {
        $proposal = Proposal::whereKey($workOrder->proposal_id)->lockForUpdate()->firstOrFail();
        $existing = Order::where('proposal_id', $proposal->id)->lockForUpdate()->first();

        if ($existing) {
            if ($existing->work_order_id !== null || $existing->status === 'cancelled') {
                throw new DomainException('The proposal already has an order that cannot be linked to this work order.');
            }

            return $existing;
        }

        if (! in_array($proposal->status, ['draft', 'sent', 'accepted'], true)) {
            throw new DomainException('The linked proposal can no longer be converted.');
        }

        if ($user->cannot('convert', $proposal) || ($proposal->isEditable() && $user->cannot('accept', $proposal))) {
            throw new DomainException('You need permission to accept and convert the linked proposal.');
        }

        if ($proposal->isEditable()) {
            $proposal->update(['status' => 'accepted', 'accepted_at' => now(), 'accepted_by' => $user->id]);
        }

        return ProposalConverter::convert($proposal, $user);
    }

    private static function fromWorkOrder(WorkOrder $workOrder, User $user): Order
    {
        $clientId = $workOrder->client_id;
        if ($clientId === null) {
            if (blank($workOrder->client_name)) {
                throw new DomainException('Add a client before confirming this work order.');
            }
            if ($user->cannot('create', Client::class)) {
                throw new DomainException('Select an existing client before confirming this work order.');
            }

            $clientId = ClientResolver::resolve([
                'name' => $workOrder->client_name,
                'address' => $workOrder->detail('client.address'),
            ], $user->id)->id;
            $workOrder->client_id = $clientId;
        }

        $tax = max(Money::toMinor($workOrder->detail('tax_amount')), 0);
        $deductTax = $workOrder->detail('tax_mode') === 'deduct';
        $order = Order::create([
            'client_id' => $clientId,
            'status' => 'confirmed',
            'order_date' => now()->toDateString(),
            'extra_discount' => $deductTax ? Money::fromMinor($tax) : 0,
            'payment_terms' => $workOrder->detail('payment_due'),
            'created_by' => $user->id,
        ]);

        foreach (config('work_order_admin.fee_lines') as $index => $label) {
            $amount = $workOrder->detail("fees.{$index}.total");
            if (blank($amount)) {
                continue;
            }

            $order->items()->create([
                'service_name' => preg_replace('/^\d+\. /', '', $label),
                'description' => $index === 0 ? $workOrder->detail('included') : null,
                'quantity' => 1,
                'unit_price' => $amount,
                'sort' => $index,
            ]);
        }

        if (! $deductTax && $tax > 0) {
            $order->items()->create([
                'service_name' => 'Fixed tax / VAT',
                'quantity' => 1,
                'unit_price' => Money::fromMinor($tax),
                'sort' => count(config('work_order_admin.fee_lines')),
            ]);
        }

        $order->recalculate();

        return $order;
    }
}
