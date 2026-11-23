<?php

namespace App\Services;

use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\User;
use App\Support\Money;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FinanceLedger
{
    public const AMOUNT_PATTERN = '/^\d{1,10}(?:\.\d{1,2})?$/D';

    public function post(User $user, array $input): FinanceTransaction
    {
        Gate::forUser($user)->authorize('create', FinanceTransaction::class);

        $data = Validator::make($input, [
            'kind' => ['required', Rule::in(array_keys(FinanceTransaction::KINDS))],
            'from_account_id' => ['nullable', 'integer', 'min:1', 'required_if:kind,expense,transfer', 'prohibited_if:kind,income,opening'],
            'to_account_id' => ['nullable', 'integer', 'min:1', 'required_if:kind,income,transfer,opening', 'prohibited_if:kind,expense'],
            'amount' => ['required', 'regex:'.self::AMOUNT_PATTERN, 'numeric', 'gt:0'],
            'transaction_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.today('Asia/Dhaka')->toDateString()],
            'category' => ['nullable', 'string', 'max:100'],
            'reference' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'request_key' => ['required', 'uuid'],
        ])->validate();

        $entry = [
            'kind' => $data['kind'],
            'from_account_id' => isset($data['from_account_id']) ? (int) $data['from_account_id'] : null,
            'to_account_id' => isset($data['to_account_id']) ? (int) $data['to_account_id'] : null,
            'amount_minor' => Money::toMinor($data['amount']),
            'transaction_date' => $data['transaction_date'],
            'category' => $data['category'] ?? null,
            'reference' => $data['reference'] ?? null,
            'notes' => $data['notes'] ?? null,
            'request_key' => $data['request_key'],
        ];

        if ($entry['from_account_id'] !== null && $entry['from_account_id'] === $entry['to_account_id']) {
            throw ValidationException::withMessages(['to_account_id' => 'Select a different destination account.']);
        }

        try {
            return DB::transaction(fn () => $this->record($user, $entry), attempts: 5);
        } catch (UniqueConstraintViolationException $exception) {
            $existing = FinanceTransaction::where('request_key', $entry['request_key'])->first();

            return $existing ? $this->matchRequest($existing, $user, $entry) : throw $exception;
        }
    }

    public function reverse(User $user, FinanceTransaction $transaction, string $reason): FinanceTransaction
    {
        Gate::forUser($user)->authorize('reverse', $transaction);

        $reason = Validator::make(['reason' => trim($reason)], [
            'reason' => ['required', 'string', 'max:2000'],
        ])->validate()['reason'];

        return DB::transaction(function () use ($user, $transaction, $reason) {
            $original = FinanceTransaction::lockForUpdate()->findOrFail($transaction->id);

            if ($original->reversal_of_id !== null) {
                throw ValidationException::withMessages(['reason' => 'A reversal cannot be reversed.']);
            }

            if ($existing = $original->reversal()->first()) {
                return $existing;
            }

            return $this->record($user, [
                'kind' => $original->kind,
                'from_account_id' => $original->to_account_id,
                'to_account_id' => $original->from_account_id,
                'amount_minor' => $original->amount_minor,
                'transaction_date' => today('Asia/Dhaka')->toDateString(),
                'category' => $original->category,
                'reference' => $original->reference,
                'notes' => $reason,
                'request_key' => (string) Str::uuid(),
                'reversal_of_id' => $original->id,
            ]);
        }, attempts: 5);
    }

    private function record(User $user, array $entry): FinanceTransaction
    {
        $fromId = $entry['from_account_id'];
        $toId = $entry['to_account_id'];
        $accounts = FinanceAccount::query()
            ->whereKey(array_values(array_filter([$fromId, $toId])))
            ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
        $reversing = isset($entry['reversal_of_id']);

        if (! $reversing && ($existing = FinanceTransaction::where('request_key', $entry['request_key'])->first())) {
            return $this->matchRequest($existing, $user, $entry);
        }

        foreach (['from_account_id' => $fromId, 'to_account_id' => $toId] as $field => $id) {
            if ($id !== null && (! $accounts->has($id) || (! $reversing && ! $accounts[$id]->is_active))) {
                throw ValidationException::withMessages([$field => 'Select an active account.']);
            }
        }

        if (! $reversing && $entry['kind'] === FinanceTransaction::OPENING
            && FinanceTransaction::where('from_account_id', $toId)->orWhere('to_account_id', $toId)->exists()) {
            throw ValidationException::withMessages(['to_account_id' => 'An opening balance must be the first entry for the account.']);
        }

        $amount = $entry['amount_minor'];
        if ($fromId !== null && $accounts[$fromId]->balance_minor < $amount) {
            throw ValidationException::withMessages([
                $reversing ? 'reason' : 'amount' => 'The source account has insufficient funds.',
            ]);
        }

        if ($fromId !== null) {
            $accounts[$fromId]->decrement('balance_minor', $amount);
        }
        if ($toId !== null) {
            $accounts[$toId]->increment('balance_minor', $amount);
        }

        return FinanceTransaction::create($entry + [
            'recorded_by' => $user->getKey(),
            'recorded_by_name' => $user->name,
        ]);
    }

    private function matchRequest(FinanceTransaction $existing, User $user, array $entry): FinanceTransaction
    {
        $saved = $existing->only(array_keys($entry));
        $saved['transaction_date'] = $existing->transaction_date->toDateString();

        if ((int) $existing->recorded_by !== (int) $user->getKey() || $saved !== $entry) {
            throw ValidationException::withMessages(['amount' => 'This form has already been submitted. Open a new form to post another entry.']);
        }

        return $existing;
    }
}
