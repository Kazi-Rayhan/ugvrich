<?php

namespace App\Support\Sales;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Records, confirms, fails and reverses payments against an order.
 *
 * Every step locks the order row first, so two people recording at the same
 * moment are handled one after the other and the outstanding balance each of
 * them checks is the real one. A confirmed payment can never take the paid
 * total past the order total (overpayment is not accepted).
 */
class PaymentRecorder
{
    /** @param  array<string, mixed>  $data  amount, paid_on, method, transaction_id, status, notes, receipt */
    public static function record(Order $order, array $data, User $user): Payment
    {
        return DB::transaction(function () use ($order, $data, $user) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === 'cancelled') {
                self::fail('amount', 'Payments cannot be recorded against a cancelled order.');
            }

            $amount = Money::toMinor($data['amount'] ?? null);
            $status = ($data['status'] ?? Payment::PENDING) === Payment::CONFIRMED ? Payment::CONFIRMED : Payment::PENDING;
            $transactionId = filled($data['transaction_id'] ?? null) ? trim($data['transaction_id']) : null;

            if ($amount <= 0) {
                self::fail('amount', 'The amount must be more than zero.');
            }

            // Pending payments are counted here too, so a pending and a confirmed
            // payment together can't add up to more than is owed.
            $waiting = (int) $locked->payments()->where('status', Payment::PENDING)->get(['amount'])
                ->sum(fn (Payment $p) => Money::toMinor($p->amount));

            if ($amount > $locked->outstandingMinor() - $waiting) {
                self::fail('amount', 'This is more than the outstanding balance of '
                    .Money::format(max($locked->outstandingMinor() - $waiting, 0), true)
                    .($waiting > 0 ? ' (after pending payments).' : '.'));
            }

            if ($transactionId && $locked->payments()->where('transaction_id', $transactionId)->exists()) {
                self::fail('transaction_id', 'This transaction ID is already recorded on this order.');
            }

            // A double click or a resubmitted form: same amount, date and method
            // from the same person a moment ago.
            $duplicate = $locked->payments()
                ->where('recorded_by', $user->id)
                ->where('amount', Money::fromMinor($amount))
                ->whereDate('paid_on', $data['paid_on'])
                ->where('method', $data['method'])
                ->where('created_at', '>=', now()->subMinutes(2))
                ->exists();

            if ($duplicate) {
                self::fail('amount', 'An identical payment was recorded a moment ago. Check the history before recording it again.');
            }

            return $locked->payments()->create([
                'amount' => Money::fromMinor($amount),
                'paid_on' => $data['paid_on'],
                'method' => $data['method'],
                'transaction_id' => $transactionId,
                'status' => $status,
                'notes' => $data['notes'] ?? null,
                'receipt' => $data['receipt'] ?? null,
                'recorded_by' => $user->id,
                'confirmed_at' => $status === Payment::CONFIRMED ? now() : null,
                'confirmed_by' => $status === Payment::CONFIRMED ? $user->id : null,
            ]);
        });
    }

    public static function confirm(Payment $payment, User $user): void
    {
        DB::transaction(function () use ($payment, $user) {
            $order = Order::whereKey($payment->order_id)->lockForUpdate()->firstOrFail();
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== Payment::PENDING) {
                self::fail('payment', 'Only a pending payment can be confirmed.');
            }

            if (Money::toMinor($locked->amount) > $order->outstandingMinor()) {
                self::fail('payment', 'Confirming this would take the order past its total. Outstanding: '
                    .Money::format($order->outstandingMinor(), true).'.');
            }

            $locked->update(['status' => Payment::CONFIRMED, 'confirmed_at' => now(), 'confirmed_by' => $user->id]);
        });
    }

    public static function markFailed(Payment $payment, ?string $note = null): void
    {
        DB::transaction(function () use ($payment, $note) {
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== Payment::PENDING) {
                self::fail('payment', 'Only a pending payment can be marked failed.');
            }

            $locked->update([
                'status' => Payment::FAILED,
                'notes' => trim(($locked->notes ? $locked->notes."\n" : '').($note ? 'Failed: '.$note : '')) ?: $locked->notes,
            ]);
        });
    }

    /** The correction for a confirmed payment: kept on record, no longer counted as received. */
    public static function reverse(Payment $payment, string $reason, User $user): void
    {
        DB::transaction(function () use ($payment, $reason, $user) {
            Order::whereKey($payment->order_id)->lockForUpdate()->firstOrFail();
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== Payment::CONFIRMED) {
                self::fail('payment', 'Only a confirmed payment can be reversed.');
            }

            $locked->update([
                'status' => Payment::REVERSED,
                'reversed_at' => now(),
                'reversed_by' => $user->id,
                'reversal_reason' => $reason,
            ]);
        });
    }

    protected static function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
