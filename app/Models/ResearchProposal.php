<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * A research proposal, from the public form or from the Researcher Portal.
 *
 * One table, because they are the same thing: the public form is a short way in
 * for someone without an account, and the portal is the full one for a
 * registered researcher carrying an approved idea forward. A portal proposal
 * has a `user_id`; a public one does not, and that is the only structural
 * difference between them.
 */
class ResearchProposal extends Model
{
    protected $guarded = [];

    protected $casts = [
        'sdgs' => 'array',
        'co_researchers' => 'array',
        'funding_required' => 'boolean',
        'external_funding_applied' => 'boolean',
        'human_participants' => 'boolean',
        'sensitive_data' => 'boolean',
        'ethical_approval_required' => 'boolean',
        'informed_consent_required' => 'boolean',
        'ai_used' => 'boolean',
        'reviewed_at' => 'datetime',
        'submitted_at' => 'datetime',
        'decided_at' => 'datetime',
    ];

    public const DRAFT = 'draft';

    /** Public-form submissions are still recorded as "new" in the intake list. */
    public const NEW = 'new';

    public const SUBMITTED = 'submitted';

    public const UNDER_REVIEW = 'under_review';

    public const REVISION = 'revision_required';

    public const APPROVED = 'approved';

    public const REJECTED = 'rejected';

    /**
     * Statuses this phase uses.
     *
     * The wider lifecycle — funding review, funded, research ongoing, completed
     * — is deliberately not here yet: a status nobody can reach is a promise,
     * and those stages arrive with the funding and project work.
     *
     * @return array<string, string>
     */
    public static function statuses(): array
    {
        return [
            self::DRAFT => 'Draft',
            self::NEW => 'New',
            self::SUBMITTED => 'Submitted',
            self::UNDER_REVIEW => 'Under review',
            self::REVISION => 'Revision required',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Not taken forward',
        ];
    }

    /** The order the rail on the researcher's page counts out. */
    public static function flow(): array
    {
        return [self::DRAFT, self::NEW, self::SUBMITTED, self::UNDER_REVIEW, self::APPROVED];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function idea(): BelongsTo
    {
        return $this->belongsTo(ResearchIdea::class, 'research_idea_id');
    }

    public function assignedReviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_reviewer_id');
    }

    /** Funding decisions on this proposal. Approval does not create one. */
    public function fundings(): HasMany
    {
        return $this->hasMany(ResearchFunding::class)->latest();
    }

    /** The project this proposal became, once somebody created it. */
    public function project(): HasOne
    {
        return $this->hasOne(Project::class, 'research_proposal_id');
    }

    public function reviews(): MorphMany
    {
        return $this->morphMany(ResearchReview::class, 'reviewable')->latest();
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /** Proposals written in the portal, as against those from the public form. */
    public function scopeFromPortal(Builder $query): Builder
    {
        return $query->whereNotNull('user_id');
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->whereIn('status', [self::NEW, self::SUBMITTED]);
    }

    /**
     * Still the researcher's to change.
     *
     * Once it is submitted it is not: what a reviewer is reading must not move
     * under them. A revision request hands it back.
     */
    public function isEditable(): bool
    {
        return in_array($this->status, [self::DRAFT, self::REVISION], true);
    }

    public function isApproved(): bool
    {
        return $this->status === self::APPROVED;
    }

    /**
     * What the researcher is asking for, before anybody has decided anything.
     *
     * The decision itself lives on {@see ResearchFunding}; this is the request.
     */
    public static function fundingTypes(): array
    {
        return [
            'none' => 'No funding needed',
            'internal' => 'Internal (UGV / RICH) funding',
            'external' => 'External funder',
            'both' => 'Both internal and external',
        ];
    }

    /** @return array<string, string> */
    public static function researchTypes(): array
    {
        return [
            'individual' => 'Individual Research',
            'collaborative' => 'Collaborative Research',
            'professional_student' => 'Professional or Student Research',
            'interdisciplinary' => 'Interdisciplinary Research',
        ];
    }

    /** @return array<string, string> */
    public static function researcherTypes(): array
    {
        return [
            'student' => 'Student',
            'professor' => 'Professor',
            'other' => 'Other',
        ];
    }

    /** Whether an outside funder is part of what is being asked for. */
    public function wantsExternalFunding(): bool
    {
        return in_array($this->funding_type, ['external', 'both'], true);
    }

    public function reference(): string
    {
        return 'RP-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }
}
