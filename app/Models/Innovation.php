<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One innovation on /innovation, in either phase of the Innovation Wing plan.
 *
 * Kept apart from Project on purpose. A project is a piece of work with a code,
 * a budget and a deadline; these are write-ups in a document, and the proposed
 * ones in particular are shaped by the document (concept / how / why, the
 * departments behind it, the SDGs it serves) rather than by a work schedule.
 */
class Innovation extends Model
{
    use HasTranslations;

    /** The two phases the document sets out. */
    public const CURRENT = 'current';

    public const PROPOSED = 'proposed';

    /** Fields with a `_bn` twin; see the HasTranslations trait. */
    protected array $translatable = [
        'name',
        'subtitle',
        'tagline',
        'lead',
        'highlights',
        'concept',
        'how',
        'why',
        'departments',
        'sdg',
    ];

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /** @return array<string, string> */
    public static function phases(): array
    {
        return [
            self::CURRENT => 'Current innovation — prototype to field test',
            self::PROPOSED => 'Proposed innovation — next phase',
        ];
    }

    /** The innovation area it belongs to, as a service belongs to its category. */
    public function area(): BelongsTo
    {
        return $this->belongsTo(InnovationArea::class, 'innovation_area_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePhase(Builder $query, string $phase): Builder
    {
        return $query->where('phase', $phase);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * The name in the other script, shown in brackets beside the heading.
     *
     * The document prints each proposed innovation as "Solar Scooty (সোলার
     * স্কুটি)" in English and the reverse in Bangla, so this is whichever of
     * the two names the reader is not already looking at.
     */
    public function otherName(): ?string
    {
        $other = app()->getLocale() === 'bn'
            ? $this->getRawOriginal('name')
            : $this->getRawOriginal('name_bn');

        return filled($other) && $other !== $this->name ? $other : null;
    }
}
