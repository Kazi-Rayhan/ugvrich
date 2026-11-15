<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expert extends Model
{
    use HasTranslations;

    /** Fields with a `_bn` twin; see the HasTranslations trait. */
    protected array $translatable = [
        'name',
        'designation',
        'department',
        'expertise',
        'research_interests',
        'bio',
    ];

    protected $guarded = [];

    protected $attributes = ['type' => 'expert'];

    protected $casts = [
        'expertise' => 'array',
        'expertise_bn' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getInitialsAttribute(): string
    {
        $clean = trim(preg_replace('/^(Dr\.?|Prof\.?|Mr\.?|Ms\.?|Mrs\.?|Engr\.?)\s+/i', '', $this->name));
        $parts = preg_split('/\s+/', $clean);

        return strtoupper(substr($parts[0] ?? '', 0, 1).substr(end($parts) ?: '', 0, 1));
    }
}
