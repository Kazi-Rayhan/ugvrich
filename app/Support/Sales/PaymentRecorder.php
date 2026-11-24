<?php

namespace App\Support\Sales;

use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\FinanceLedger;
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
    /** @param  array<string, mixed>  $data  amount, paid_on, finance_account_id (or legacy method), transaction_id, status, notes, receipt */
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
            $account = null;
            $method = $data['method'] ?? null;
            if (array_key_exists('finance_account_id', $data)) {
                $account = FinanceAccount::where('is_active', true)->lockForUpdate()->find($data['finance_account_id']);
                if (! $account instanceof FinanceAccount) {
                    self::fail('finance_account_id', 'Select an active receiving account.');
                }
                $method = 'account';
            } elseif (! is_string($method) || ! isset(Payment::METHODS[$method])) {
                self::fail('finance_account_id', 'Select a receiving account.');
            }

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
                ->where('method', $method)
                ->where('finance_account_id', $account?->id)
                ->where('created_at', '>=', now()->subMinutes(2))
                ->exists();

            if ($duplicate) {
                self::fail('amount', 'An identical payment was recorded a moment ago. Check the history before recording it again.');
            }

            $payment = $locked->payments()->create([
                'amount' => Money::fromMinor($amount),
                'paid_on' => $data['paid_on'],
                'method' => $method,
                'finance_account_id' => $account?->id,
                'transaction_id' => $transactionId,
                'status' => $status,
                'notes' => $data['notes'] ?? null,
                'receipt' => $data['receipt'] ?? null,
                'recorded_by' => $user->id,
                'confirmed_at' => $status === Payment::CONFIRMED ? now() : null,
                'confirmed_by' => $status === Payment::CONFIRMED ? $user->id : null,
            ]);

            app(FinanceLedger::class)->postOrderPayment($payment, $user);

            return $payment;
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
            app(FinanceLedger::class)->postOrderPayment($locked, $user);
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

            app(FinanceLedger::class)->reverseOrderPayment($locked, $user, $reason);
            $locked->update([
                'status' => Payment::REVERSED,
                'reversed_at' => now(),
                'reversed_by' => $user->id,
                'reversal_reason' => $reason,
            ]);
        });
    }

    public static function postExisting(Payment $payment, User $user): void
    {
        abort_unless($user->can('create', FinanceTransaction::class) && $user->can('confirm', $payment), 403);

        DB::transaction(function () use ($payment, $user): void {
            Order::whereKey($payment->order_id)->lockForUpdate()->firstOrFail();
            $locked = Payment::whereKey($payment->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== Payment::CONFIRMED || $locked->finance_account_id === null) {
                self::fail('payment', 'Only a confirmed payment with a receiving account can be posted.');
            }

            app(FinanceLedger::class)->postOrderPayment($locked, $user);
        });
    }

    protected static function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
