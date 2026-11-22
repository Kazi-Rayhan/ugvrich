<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\ExpertBiography;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'professional_skills' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $expert): void {
            foreach (['bio' => 'bio_html', 'bio_bn' => 'bio_html_bn'] as $plain => $rich) {
                if ($expert->isDirty($plain) && ! $expert->isDirty($rich)) {
                    $expert->{$rich} = null;
                }
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function consultancyApplication(): HasOne
    {
        return $this->hasOne(ConsultancyApplication::class);
    }

    public function biography(?string $locale = null, bool $fallback = true): ?string
    {
        $bangla = ($locale ?? app()->getLocale()) === 'bn';
        $html = ExpertBiography::sanitize($this->{$bangla ? 'bio_html_bn' : 'bio_html'});
        $plain = $this->getRawOriginal($bangla ? 'bio_bn' : 'bio');

        if ($html || filled($plain)) {
            return $html ?? nl2br(e($plain));
        }

        return $bangla && $fallback ? $this->biography('en', false) : null;
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
