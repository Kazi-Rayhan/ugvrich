<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\Vocabulary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InnovationArea extends Model
{
    use HasTranslations;

    /** Fields with a `_bn` twin; see the HasTranslations trait. */
    protected array $translatable = [
        'name',
        'tagline',
        'description',
        'focus',
    ];

    protected $guarded = [];

    protected $casts = [
        'focus' => 'array',
        'focus_bn' => 'array',
        'is_active' => 'boolean',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /** The innovations in this area, as a service category has its services. */
    public function innovations(): HasMany
    {
        return $this->hasMany(Innovation::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function getDepartmentNameAttribute(): ?string
    {
        return Vocabulary::label('departments', $this->department);
    }

    /**
     * The short description, or, until one is written, the opening of the
     * description, so the menu and the banner always have a line to show.
     */
    public function getSummaryAttribute(): ?string
    {
        if (filled($this->tagline)) {
            return $this->tagline;
        }

        return filled($this->description)
            ? \Illuminate\Support\Str::limit(trim((string) $this->description), 80)
            : null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
