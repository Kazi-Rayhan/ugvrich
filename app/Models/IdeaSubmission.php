<?php

namespace App\Models;

use App\Support\Vocabulary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IdeaSubmission extends Model
{
    protected $guarded = [];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    /** The innovator account it was submitted from. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCategoryNameAttribute(): ?string
    {
        return Vocabulary::label('idea_categories', $this->category);
    }

    /** IDEA-00012: the reference the submitter was given. */
    public function getReferenceAttribute(): string
    {
        return 'IDEA-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function getDepartmentNameAttribute(): ?string
    {
        return Vocabulary::label('departments', $this->department);
    }

    public function getStageLabelAttribute(): ?string
    {
        return Vocabulary::label('startup_stages', $this->stage);
    }
}
