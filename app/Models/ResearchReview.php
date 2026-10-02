<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One reviewer's decision on one record, and the status change it caused.
 *
 * Polymorphic on purpose: an idea, a proposal and a paper are all read the
 * same way, by possibly several people in turn. Rows are never edited — a
 * second look is a second row, so the sequence stays a readable history.
 */
class ResearchReview extends Model
{
    protected $guarded = [];

    public const APPROVE = 'approve';

    public const REVISION = 'revision';

    public const REJECT = 'reject';

    public const RECOMMEND = 'recommend';

    /** @return array<string, string> */
    public static function decisions(): array
    {
        return [
            self::APPROVE => 'Approve',
            self::REVISION => 'Request revision',
            self::REJECT => 'Reject',
            self::RECOMMEND => 'Recommend for proposal',
        ];
    }

    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
