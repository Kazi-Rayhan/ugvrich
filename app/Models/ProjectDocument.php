<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A file belonging to a research project.
 *
 * Private by default. Ethics approvals, data files and draft manuscripts are
 * not public documents, and the default should never be the one that leaks.
 */
class ProjectDocument extends Model
{
    protected $guarded = [];

    protected $casts = ['is_public' => 'boolean'];

    /** @return array<string, string> */
    public static function kinds(): array
    {
        return [
            'approval' => 'Approval letter',
            'ethics' => 'Ethics approval',
            'proposal' => 'Research proposal',
            'data' => 'Data collection',
            'analysis' => 'Analysis',
            'manuscript' => 'Manuscript',
            'report' => 'Final report',
            'other' => 'Other supporting document',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }
}
