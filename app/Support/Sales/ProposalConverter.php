<?php

namespace App\Support\Sales;

use App\Models\Order;
use App\Models\Proposal;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Turns an accepted proposal into an order.
 *
 * Everything agreed is copied: client, lines with their agreed prices and
 * discounts, the extra discount, tax rate and terms. The proposal stays as
 * it was, marked converted, and the order keeps a permanent link to it.
 *
 * Safe to trigger twice. The proposal row is locked while this runs, and the
 * orders table allows one order per proposal, so a second attempt finds the
 * first order and stops; it never creates another or changes the first.
 */
class ProposalConverter
{
    public static function convert(Proposal $proposal, User $user): Order
    {
        return DB::transaction(function () use ($proposal, $user) {
            /** @var Proposal $locked */
            $locked = Proposal::whereKey($proposal->id)->lockForUpdate()->firstOrFail();

            if ($existing = Order::where('proposal_id', $locked->id)->first()) {
                throw new RuntimeException("This proposal is already order {$existing->number}.");
            }

            if ($locked->status !== 'accepted') {
                throw new RuntimeException('Only an accepted proposal can be converted to an order.');
            }

            $locked->load(['items', 'categories']);

            $order = Order::create([
                'client_id' => $locked->client_id,
                'proposal_id' => $locked->id,
                'status' => 'pending',
                'order_date' => now()->toDateString(),
                'extra_discount' => $locked->extra_discount,
                'tax_rate' => $locked->tax_rate,
                'payment_terms' => $locked->payment_terms,
                'terms' => $locked->terms,
                'conditions' => $locked->conditions,
                'created_by' => $user->id,
            ]);

            foreach ($locked->items as $item) {
                $order->items()->create($item->only([
                    'service_category_id', 'service_id', 'service_name', 'description',
                    'quantity', 'unit_price', 'discount', 'sort',
                ]));
            }

            $order->categories()->sync($locked->categories->pluck('id')->all());
            $order->recalculate();

            $locked->forceFill(['status' => 'converted', 'converted_at' => now()])->save();

            return $order;
        });
    }
}
