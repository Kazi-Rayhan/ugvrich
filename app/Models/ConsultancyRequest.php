<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultancyRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'handled_at' => 'datetime',
        'preferred_date' => 'date',
    ];

    /** The requested half-hour slot, read in the language of the page. */
    public function getPreferredSlotLabelAttribute(): ?string
    {
        return \App\Support\MeetingSlots::label($this->preferred_slot);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
}
