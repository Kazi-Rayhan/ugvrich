<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * A funding opportunity a researcher can apply for.
 *
 * Entered by the Research Wing in the admin. Nothing is seeded: inventing
 * grants, deadlines and amounts would be inventing facts, so the list is empty
 * until somebody who knows adds a real one.
 */
class FundingOpportunity extends Model
{
    protected $guarded = [];

    protected $casts = [
        'deadline' => 'date',
        'is_active' => 'boolean',
    ];

    /** @return array<string, string> */
    public static function categories(): array
    {
        return [
            'internal' => 'Internal funding',
            'seed' => 'Seed grant',
            'faculty' => 'Faculty research grant',
            'student' => 'Student research grant',
            'external' => 'External funding',
            'national' => 'National grant',
            'international' => 'International grant',
            'industry' => 'Industry funding',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Open ones first, by how soon they close; undated ones last. */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q->whereNull('deadline')->orWhereDate('deadline', '>=', today()));
    }

    public function hasClosed(): bool
    {
        return $this->deadline !== null && $this->deadline->isPast();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
