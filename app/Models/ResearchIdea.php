<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A research idea, which is where the whole workflow starts.
 *
 * An idea does not become a project by being approved. It becomes eligible to
 * be written up as a proposal, which is a separate record with its own review.
 */
class ResearchIdea extends Model
{
    protected $guarded = [];

    protected $casts = [
        'sdgs' => 'array',
        'keywords' => 'array',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public const DRAFT = 'draft';

    public const SUBMITTED = 'submitted';

    public const UNDER_REVIEW = 'under_review';

    public const REVISION = 'revision_required';

    public const APPROVED = 'approved';

    public const PROPOSAL = 'proposal_development';

    public const REJECTED = 'rejected';

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::UNDER_REVIEW => 'Under review',
            self::REVISION => 'Revision required',
            self::APPROVED => 'Approved',
            self::PROPOSAL => 'Proposal development',
            self::REJECTED => 'Not taken forward',
        ];
    }

    /** The order the workflow runs in, for the progress rail on the page. */
    public static function flow(): array
    {
        return [self::DRAFT, self::SUBMITTED, self::UNDER_REVIEW, self::APPROVED, self::PROPOSAL];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(ResearchReview::class, 'reviewable')->latest();
    }

    public function assignedReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_reviewer_id');
    }

    /** The proposal this idea was carried forward into, once there is one. */
    public function proposal(): HasOne
    {
        return $this->hasOne(ResearchProposal::class);
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /** Still the researcher's to change — once submitted it is not. */
    public function isEditable(): bool
    {
        return in_array($this->status, [self::DRAFT, self::REVISION], true);
    }

    public function isApproved(): bool
    {
        return in_array($this->status, [self::APPROVED, self::PROPOSAL], true);
    }
}
