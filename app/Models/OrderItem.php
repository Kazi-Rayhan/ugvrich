<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** An order's line: the same shape as a proposal's, copied from it on conversion. */
class OrderItem extends ProposalItem
{
    protected $table = 'order_items';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
