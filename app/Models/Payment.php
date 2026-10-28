<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

/**
 * One instalment against an order.
 *
 * A payment is never edited or deleted once it is in. It moves forward only:
 * pending → confirmed or failed, and confirmed → reversed (with a reason and
 * who did it). The amount, date, method and order it was taken against are
 * fixed from the moment it is recorded.
 */
class Payment extends Model
{
    public const PENDING = 'pending';

    public const CONFIRMED = 'confirmed';

    public const FAILED = 'failed';

    public const REVERSED = 'reversed';

    public const STATUSES = [
        self::PENDING => 'Pending',
        self::CONFIRMED => 'Confirmed',
        self::FAILED => 'Failed',
        self::REVERSED => 'Reversed',
    ];

    public const STATUS_COLORS = [
        self::PENDING => 'warning',
        self::CONFIRMED => 'success',
        self::FAILED => 'gray',
        self::REVERSED => 'danger',
    ];

    public const METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank transfer',
        'cheque' => 'Cheque',
        'bkash' => 'bKash',
        'nagad' => 'Nagad',
        'rocket' => 'Rocket',
        'card' => 'Card',
        'other' => 'Other',
    ];

    /** The fields that make a payment what it is; none of them may change after it is recorded. */
    protected const FIXED = ['order_id', 'amount', 'paid_on', 'method', 'transaction_id', 'reference', 'recorded_by'];

    /** status => the statuses it may move on to. */
    protected const MOVES = [
        self::PENDING => [self::CONFIRMED, self::FAILED],
        self::CONFIRMED => [self::REVERSED],
        self::FAILED => [],
        self::REVERSED => [],
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'paid_on' => 'date',
        'confirmed_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Payment $payment) {
            $payment->reference ??= 'TMP-'.\Illuminate\Support\Str::random(20);
        });

        static::created(function (Payment $payment) {
            if (str_starts_with($payment->reference, 'TMP-')) {
                // Set once, straight after the insert; FIXED guards it from then on.
                static::whereKey($payment->id)->update(['reference' => sprintf('PAY-%s-%06d', now()->format('Y'), $payment->id)]);
                $payment->reference = sprintf('PAY-%s-%06d', now()->format('Y'), $payment->id);
                $payment->syncOriginalAttribute('reference');
            }
        });

        static::updating(function (Payment $payment) {
            foreach (self::FIXED as $field) {
                if ($payment->isDirty($field)) {
                    throw new LogicException("A recorded payment's {$field} cannot be changed. Reverse it and record a new one.");
                }
            }

            if ($payment->isDirty('status')) {
                $from = $payment->getOriginal('status');

                if (! in_array($payment->status, self::MOVES[$from] ?? [], true)) {
                    throw new LogicException("A {$from} payment cannot become {$payment->status}.");
                }
            }
        });

        static::deleting(function () {
            throw new LogicException('Payments are kept for the record and cannot be deleted. Mark it failed or reverse it instead.');
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function confirmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function reverser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }
}
