<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\Vocabulary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ServiceCategory extends Model
{
    use HasTranslations;

    /** Fields with a `_bn` twin; see the HasTranslations trait. */
    protected array $translatable = [
        'name',
        'tagline',
        'description',
    ];

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** The sector that runs this service, spelled out. */
    public function getDepartmentNameAttribute(): ?string
    {
        return Vocabulary::label('departments', $this->department);
    }

    /** The sector, as the Board minutes name it: "CSE", "Civil Engineering". */
    public function getSectorNameAttribute(): ?string
    {
        return Vocabulary::label('sectors', $this->department);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class)->orderBy('sort_order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function experts(): HasMany
    {
        return $this->hasMany(Expert::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
