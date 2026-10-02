<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A manuscript moving from draft to publication.
 *
 * It points at its project rather than copying it: department, field and team
 * are read through the relation, so a correction in one place is a correction
 * everywhere.
 *
 * Reviews live in the polymorphic `research_reviews` with ideas and proposals,
 * so one review history serves the whole workflow.
 */
class ResearchManuscript extends Model
{
    protected $guarded = [];

    protected $casts = [
        'keywords' => 'array',
        'published_on' => 'date',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
        'is_public' => 'boolean',
    ];

    public const DRAFT = 'draft';

    public const SUBMITTED = 'submitted';

    public const INTERNAL_REVIEW = 'internal_review';

    public const REVISION = 'revision_required';

    public const APPROVED = 'approved';

    public const AT_JOURNAL = 'submitted_to_journal';

    public const JOURNAL_REVIEW = 'journal_review';

    public const ACCEPTED = 'accepted';

    public const PUBLISHED = 'published';

    public const REJECTED = 'rejected';

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Submitted',
            self::INTERNAL_REVIEW => 'Internal review',
            self::REVISION => 'Revision required',
            self::APPROVED => 'Approved',
            self::AT_JOURNAL => 'Submitted to journal',
            self::JOURNAL_REVIEW => 'Under journal review',
            self::ACCEPTED => 'Accepted',
            self::PUBLISHED => 'Published',
            self::REJECTED => 'Rejected',
        ];
    }

    /** The rail the researcher's page counts out. */
    public static function flow(): array
    {
        return [self::DRAFT, self::SUBMITTED, self::INTERNAL_REVIEW, self::APPROVED, self::AT_JOURNAL, self::PUBLISHED];
    }

    /** @return array<string, string> */
    public static function types(): array
    {
        return [
            'journal' => 'Journal article',
            'conference' => 'Conference paper',
            'review' => 'Review article',
            'chapter' => 'Book chapter',
            'report' => 'Research report',
            'preprint' => 'Preprint',
        ];
    }

    /** @return array<string, string> */
    public static function citationStyles(): array
    {
        return [
            'apa' => 'APA',
            'mla' => 'MLA',
            'chicago' => 'Chicago',
            'ieee' => 'IEEE',
            'harvard' => 'Harvard',
            'vancouver' => 'Vancouver',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_reviewer_id');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(ResearchReview::class, 'reviewable')->latest();
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * What the repository may show.
     *
     * Two conditions, not one: published, and explicitly marked public. An
     * approved or even published manuscript stays out of the repository until
     * somebody decides it belongs there.
     */
    public function scopePubliclyListed(Builder $query): Builder
    {
        return $query->where('is_public', true)->where('status', self::PUBLISHED);
    }

    /** Drafts and returned revisions are the researcher's; nothing else is. */
    public function isEditable(): bool
    {
        return in_array($this->status, [self::DRAFT, self::REVISION], true);
    }

    public function reference(): string
    {
        return 'MS-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }
}
