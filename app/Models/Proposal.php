<?php

namespace App\Models;

use App\Models\Concerns\HasSalesLines;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** What RICH offers a client, line by line, before anything is agreed. */
class Proposal extends Model
{
    use HasSalesLines;

    public const NUMBER_PREFIX = 'PRP';

    public const STATUSES = [
        'draft' => 'Draft',
        'sent' => 'Sent',
        'accepted' => 'Accepted',
        'rejected' => 'Rejected',
        'expired' => 'Expired',
        'converted' => 'Converted to order',
    ];

    public const STATUS_COLORS = [
        'draft' => 'gray',
        'sent' => 'info',
        'accepted' => 'success',
        'rejected' => 'danger',
        'expired' => 'warning',
        'converted' => 'primary',
    ];

    protected $guarded = ['id', 'number', 'subtotal', 'discount_total', 'tax_amount', 'total'];

    protected $casts = [
        'issue_date' => 'date',
        'valid_until' => 'date',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'converted_at' => 'datetime',
        'conditions' => 'array',
    ];

    protected function categoryPivotTable(): string
    {
        return 'proposal_service_category';
    }

    public static function deliveryTimelineForDates(?string $issueDate, ?string $validUntil): ?string
    {
        if (! CarbonImmutable::canBeCreatedFromFormat($issueDate, 'Y-m-d')
            || ! CarbonImmutable::canBeCreatedFromFormat($validUntil, 'Y-m-d')) {
            return null;
        }

        $start = CarbonImmutable::createFromFormat('!Y-m-d', $issueDate);
        $end = CarbonImmutable::createFromFormat('!Y-m-d', $validUntil);
        if ($end->lt($start)) {
            return null;
        }

        $days = (int) $start->diffInDays($end);

        return $days.' '.($days === 1 ? 'day' : 'days');
    }

    /** Still being written or with the client: the only states it can be edited in. */
    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'sent'], true);
    }

    public function isPastValidity(): bool
    {
        return $this->valid_until !== null && $this->valid_until->endOfDay()->isPast();
    }

    public function items(): HasMany
    {
        return $this->hasMany(ProposalItem::class)->orderBy('sort');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function consultancyRequest(): BelongsTo
    {
        return $this->belongsTo(ConsultancyRequest::class);
    }

    public function order(): HasOne
    {
        return $this->hasOne(Order::class);
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accepted_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
