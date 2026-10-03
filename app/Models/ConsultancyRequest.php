<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsultancyRequest extends Model
{
    protected $guarded = [];

    /** The kinds of requester the public form offers, stored in `organization`. */
    public const ORGANIZATION_TYPES = ['individual', 'government', 'private', 'ngo', 'other'];

    /** @return array<string, string> */
    public static function organizationOptions(): array
    {
        return collect(self::ORGANIZATION_TYPES)
            ->mapWithKeys(fn ($type) => [$type => __('site.consultancy.organization_types.'.$type)])
            ->all();
    }

    /** The organization type, read in the language of the page. Older free-text values pass through. */
    public function getOrganizationLabelAttribute(): ?string
    {
        return self::organizationOptions()[$this->organization] ?? $this->organization;
    }

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
