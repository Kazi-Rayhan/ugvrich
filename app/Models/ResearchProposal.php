<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** A research proposal sent in through the Research hub. */
class ResearchProposal extends Model
{
    protected $guarded = [];

    protected $casts = [
        'sdgs' => 'array',
        'reviewed_at' => 'datetime',
    ];

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'new' => 'New',
            'under_review' => 'Under review',
            'shortlisted' => 'Shortlisted',
            'accepted' => 'Accepted',
            'declined' => 'Declined',
        ];
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }

    public function reference(): string
    {
        return 'RP-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }
}
