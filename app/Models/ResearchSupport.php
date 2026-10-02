<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** A request to the research support desk. */
class ResearchSupport extends Model
{
    protected $guarded = [];

    protected $casts = [
        'support_types' => 'array',
        'needed_by' => 'date',
        'reviewed_at' => 'datetime',
    ];

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            'new' => 'New',
            'in_progress' => 'In progress',
            'answered' => 'Answered',
            'closed' => 'Closed',
        ];
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }

    public function reference(): string
    {
        return 'RSD-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }
}
