<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /** The same statuses in the reader's own language, for the portal. */
    public static function statusLabels(): array
    {
        return collect(array_keys(static::statuses()))
            ->mapWithKeys(fn (string $status) => [$status => __('researcher.support.statuses.'.$status)])
            ->all();
    }

    /** Null for a request sent from the public form, which has no account. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function reference(): string
    {
        return 'RSD-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }
}
