<?php

namespace App\Models;

use App\Support\Money;
use App\Support\Sales\Totals;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One priced line. The service name, description and price are copies taken
 * when the line was written, so a later change to the catalogue leaves it as
 * it was agreed.
 */
class ProposalItem extends Model
{
    protected $guarded = ['id', 'line_total'];

    protected static function booted(): void
    {
        static::saving(fn (self $item) => $item->fillSnapshotAndTotal());
    }

    /** Shared with OrderItem: copy the service name if none was typed, and price the line. */
    public function fillSnapshotAndTotal(): void
    {
        if (blank($this->service_name) && $this->service_id) {
            $this->service_name = Service::find($this->service_id)?->name ?? 'Service';
        }

        if ($this->service_id && blank($this->service_category_id)) {
            $this->service_category_id = Service::whereKey($this->service_id)->value('service_category_id');
        }

        $this->line_total = Money::fromMinor(Totals::line($this->quantity, $this->unit_price, $this->discount)['total']);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
}
