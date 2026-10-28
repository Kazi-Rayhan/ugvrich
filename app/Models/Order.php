<?php

namespace App\Models;

use App\Models\Concerns\HasSalesLines;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Work a client has agreed to, usually from an accepted proposal.
 *
 * Its status is the work's progress. What has been paid is a separate thing,
 * read from the payments themselves: only confirmed payments count, and the
 * paid, outstanding and payment-status figures are never stored or typed.
 */
class Order extends Model
{
    use HasSalesLines;

    public const NUMBER_PREFIX = 'ORD';

    public const STATUSES = [
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'in_progress' => 'In progress',
        'on_hold' => 'On hold',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public const STATUS_COLORS = [
        'pending' => 'gray',
        'confirmed' => 'info',
        'in_progress' => 'primary',
        'on_hold' => 'warning',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];

    public const PAYMENT_STATUSES = [
        'unpaid' => 'Unpaid',
        'partially_paid' => 'Partially paid',
        'paid' => 'Paid',
    ];

    public const PAYMENT_STATUS_COLORS = [
        'unpaid' => 'danger',
        'partially_paid' => 'warning',
        'paid' => 'success',
    ];

    protected $guarded = ['id', 'number', 'subtotal', 'discount_total', 'tax_amount', 'total'];

    protected $casts = [
        'order_date' => 'date',
        'expected_delivery_date' => 'date',
        'conditions' => 'array',
    ];

    protected function categoryPivotTable(): string
    {
        return 'order_service_category';
    }

    /** Confirmed payments, in paisa. Pending, failed and reversed ones are not money received. */
    public function paidMinor(): int
    {
        $sum = $this->relationLoaded('payments')
            ? $this->payments->where('status', Payment::CONFIRMED)->sum(fn (Payment $p) => Money::toMinor($p->amount))
            : $this->payments()->where('status', Payment::CONFIRMED)->get(['amount'])->sum(fn (Payment $p) => Money::toMinor($p->amount));

        return (int) $sum;
    }

    public function outstandingMinor(): int
    {
        return max(Money::toMinor($this->total) - $this->paidMinor(), 0);
    }

    public function getAmountPaidAttribute(): string
    {
        return Money::fromMinor($this->paidMinor());
    }

    public function getAmountOutstandingAttribute(): string
    {
        return Money::fromMinor($this->outstandingMinor());
    }

    public function getPaymentStatusAttribute(): string
    {
        $paid = $this->paidMinor();

        return match (true) {
            $paid <= 0 => 'unpaid',
            $paid < Money::toMinor($this->total) => 'partially_paid',
            default => 'paid',
        };
    }

    /** Lines and figures can change only while no money has been taken or is waiting to be confirmed. */
    public function linesAreLocked(): bool
    {
        return $this->payments()->whereIn('status', [Payment::PENDING, Payment::CONFIRMED])->exists();
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->orderBy('sort');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('paid_on')->orderBy('id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function proposal(): BelongsTo
    {
        return $this->belongsTo(Proposal::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
