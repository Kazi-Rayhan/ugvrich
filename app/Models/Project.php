<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\Vocabulary;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasTranslations;

    /** Fields with a `_bn` twin; see the HasTranslations trait. */
    protected array $translatable = [
        'title',
        'summary',
        'description',
        'outcome',
        'client',
        'duration',
        'lead_name',
        'problem',
        'solution',
        'research_summary',
        'patent_details',
        'commercial_potential',
        'technologies',
        'team_members',
    ];

    protected $guarded = [];

    protected $casts = [
        'gallery' => 'array',
        'team_members' => 'array',
        'team_members_bn' => 'array',
        'technologies' => 'array',
        'technologies_bn' => 'array',
        'is_featured' => 'boolean',
        'budget' => 'decimal:2',
        'start_date' => 'date',
        'deadline' => 'date',
        'progress' => 'integer',
    ];

    protected static function booted(): void
    {
        // Every project gets a permanent ID such as RICH-CSE-2026-001.
        static::creating(function (Project $project) {
            if (blank($project->code)) {
                $project->code = static::nextCode($project->department, $project->start_date?->year ?? (int) ($project->year ?: date('Y')));
            }
        });
    }

    public static function nextCode(?string $department, int $year): string
    {
        $prefix = sprintf('RICH-%s-%d-', strtoupper($department ?: 'RICH'), $year);

        $last = static::where('code', 'like', $prefix.'%')->orderByDesc('code')->value('code');
        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix.str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function innovationArea(): BelongsTo
    {
        return $this->belongsTo(InnovationArea::class);
    }

    /* ---------------------------------------- research management ---- */

    /** The approved proposal this project was created from, if any. */
    public function researchProposal(): BelongsTo
    {
        return $this->belongsTo(ResearchProposal::class, 'research_proposal_id');
    }

    /** The researcher whose portal this project appears in. */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function updates(): HasMany
    {
        return $this->hasMany(ProjectUpdate::class)->latest();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class)->latest();
    }

    /** Projects the public site may show. */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', 'public');
    }

    /** The lifecycle a research project moves through. */
    public static function researchStatuses(): array
    {
        return [
            'approved' => 'Approved',
            'planning' => 'Planning',
            'ongoing' => 'Ongoing',
            'on_hold' => 'On hold',
            'completed' => 'Completed',
            'closed' => 'Closed',
        ];
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('is_featured', true);
    }

    public function scopeOfType(Builder $q, ?string $type): Builder
    {
        return $type ? $q->where('type', $type) : $q;
    }

    public function getDepartmentNameAttribute(): ?string
    {
        return Vocabulary::label('departments', $this->department);
    }

    public function getTypeLabelAttribute(): string
    {
        return Vocabulary::label('project_types', $this->type, ucfirst((string) $this->type));
    }

    public function getStageLabelAttribute(): ?string
    {
        return Vocabulary::label('pipeline_stages', $this->stage);
    }

    /** 1-based position of the current stage in the pipeline, or 0. */
    public function getStageIndexAttribute(): int
    {
        $position = array_search($this->stage, array_keys(config('rich.pipeline_stages')), true);

        return $position === false ? 0 : $position + 1;
    }

    public function getPatentStatusLabelAttribute(): string
    {
        return Vocabulary::label('patent_statuses', $this->patent_status, 'Not applicable');
    }

    public function getCommercializationStatusLabelAttribute(): string
    {
        return Vocabulary::label('commercialization_statuses', $this->commercialization_status, 'Not started');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
