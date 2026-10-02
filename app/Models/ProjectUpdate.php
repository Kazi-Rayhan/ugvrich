<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One entry in a project's progress log.
 *
 * Append-only, like the review history: a project's story is the sequence of
 * these, so an entry is never edited away.
 */
class ProjectUpdate extends Model
{
    protected $guarded = [];

    protected $casts = ['progress' => 'integer'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
