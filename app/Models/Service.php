<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\Vocabulary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    use HasTranslations;

    /** Fields with a `_bn` twin; see the HasTranslations trait. */
    protected array $translatable = [
        'name',
        'description',
        'body',
        'highlights',
    ];

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'highlights' => 'array',
        'highlights_bn' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function getDepartmentNameAttribute(): ?string
    {
        return Vocabulary::label('departments', $this->department);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /** A service is addressed under the main service it belongs to. */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** True once there is more to read than the card already shows. */
    public function hasDetail(): bool
    {
        return filled($this->body) || filled($this->highlights);
    }
}
