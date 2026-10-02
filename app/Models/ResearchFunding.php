<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A funding decision on a proposal.
 *
 * Its own record, not a few columns on the proposal, for three reasons: a
 * proposal may be funded from more than one source, it may be funded in part,
 * and an unfavourable decision is still a decision worth keeping. Approving a
 * proposal does not create one of these — somebody has to decide.
 */
class ResearchFunding extends Model
{
    protected $guarded = [];

    protected $casts = [
        'requested_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'decided_at' => 'datetime',
    ];

    public const PENDING = 'pending';

    public const APPROVED = 'approved';

    public const PARTIAL = 'partial';

    public const UNFUNDED = 'unfunded';

    /** @return array<string, string> */
    public static function statuses(): array
    {
        return [
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::PARTIAL => 'Partially funded',
            self::UNFUNDED => 'Unfunded',
        ];
    }

    public const INTERNAL = 'internal';

    public const EXTERNAL = 'external';

    /** The university's own money, or somebody else's. */
    public static function sourceTypes(): array
    {
        return [
            self::INTERNAL => 'Internal (UGV / RICH)',
            self::EXTERNAL => 'External funder',
        ];
    }

    /**
     * The kind of grant, which depends on where the money comes from.
     *
     * Called without an argument it returns both sets, so an existing row can
     * always be read back whatever it was saved as.
     *
     * @return array<string, string>
     */
    public static function types(?string $sourceType = null): array
    {
        $internal = [
            'internal' => 'University fund',
            'seed' => 'Seed grant',
            'faculty' => 'Faculty research grant',
            'student' => 'Student research grant',
        ];

        $external = [
            'national' => 'National grant',
            'international' => 'International grant',
            'donor' => 'Donor / development partner',
            'industry' => 'Industry funding',
        ];

        return match ($sourceType) {
            self::INTERNAL => $internal,
            self::EXTERNAL => $external,
            default => [...$internal, ...$external],
        };
    }

    /** What sort of body an external funder is. */
    public static function organizationTypes(): array
    {
        return [
            'government' => 'Government / ministry',
            'un_sdg' => 'UN agency / SDG programme',
            'development' => 'Development partner / donor agency',
            'bank' => 'Development bank',
            'foundation' => 'Foundation / trust',
            'ngo' => 'NGO / civil society',
            'industry' => 'Industry / private company',
            'university' => 'University / research institute',
            'other' => 'Other',
        ];
    }

    /**
     * Funders seen often enough to be worth offering, so the same body is not
     * typed five different ways. The field stays free text: this is a list of
     * suggestions, not a closed set.
     *
     * @return array<int, string>
     */
    public static function organizations(): array
    {
        return [
            'University Grants Commission (UGC)',
            'Ministry of Science and Technology',
            'Ministry of Education',
            'Bangladesh Agricultural Research Council (BARC)',
            'Bangladesh Academy of Sciences',
            'UNDP',
            'UNESCO',
            'UNICEF',
            'FAO',
            'WHO',
            'UN Women',
            'World Bank',
            'Asian Development Bank (ADB)',
            'Islamic Development Bank (IsDB)',
            'JICA',
            'KOICA',
            'USAID',
            'FCDO (UK)',
            'European Union / Horizon Europe',
            'Bill & Melinda Gates Foundation',
            'BRAC',
            'Industry partner',
        ];
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(ResearchProposal::class, 'research_proposal_id');
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /** Settled either way; a pending record is not a decision. */
    public function isDecided(): bool
    {
        return $this->status !== self::PENDING;
    }

    public function isExternal(): bool
    {
        return $this->source_type === self::EXTERNAL;
    }

    /** Who is paying, in one line: the funder, or the university. */
    public function funder(): string
    {
        if (! $this->isExternal()) {
            return self::sourceTypes()[self::INTERNAL];
        }

        return $this->organization ?: self::sourceTypes()[self::EXTERNAL];
    }

    public function amount(): ?string
    {
        $value = $this->approved_amount ?? $this->requested_amount;

        return $value === null ? null : $this->currency.' '.number_format((float) $value);
    }
}
