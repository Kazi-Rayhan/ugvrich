<?php

namespace App\Support\Sales;

use App\Models\Client;
use App\Models\FinanceTransaction;
use App\Models\Order;
use App\Models\OrderExpertAssignment;
use App\Models\Payment;
use App\Models\WorkOrder;

class ClientDeletion
{
    public static function delete(Client $client): bool
    {
        return $client->getConnection()->transaction(function () use ($client): bool {
            $client = Client::whereKey($client->getKey())->lockForUpdate()->firstOrFail();
            $proposalIds = $client->proposals()->lockForUpdate()->pluck('id');
            $workOrderIds = WorkOrder::where('client_id', $client->id)
                ->orWhereIn('proposal_id', $proposalIds)
                ->orWhereHas('order', fn ($query) => $query->where('client_id', $client->id)
                    ->orWhereIn('proposal_id', $proposalIds))
                ->lockForUpdate()->pluck('id');
            $orderIds = Order::where('client_id', $client->id)
                ->orWhereIn('proposal_id', $proposalIds)
                ->orWhereIn('work_order_id', $workOrderIds)
                ->lockForUpdate()->pluck('id');

            $paymentIds = Payment::whereIn('order_id', $orderIds)->lockForUpdate()->pluck('id');
            FinanceTransaction::whereIn('payment_id', $paymentIds)->update(['payment_id' => null]);
            Payment::whereKey($paymentIds)->delete();
            OrderExpertAssignment::whereIn('order_id', $orderIds)->delete();
            Order::whereKey($orderIds)->delete();
            WorkOrder::whereKey($workOrderIds)->delete();
            $client->proposals()->delete();

            return (bool) $client->delete();
        }, attempts: 3);
    }
}
