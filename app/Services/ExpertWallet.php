<?php

namespace App\Services;

use App\Models\ConsultancyApplication as Application;
use App\Models\ExpertWalletEntry as Entry;
use App\Models\User;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ExpertWallet
{
    public const METHODS = ['bank', 'bkash'];

    public const AMOUNT_RULES = ['required', 'numeric', 'between:0.01,9999999999.99', 'decimal:0,2'];

    public function balances(Application $application): array
    {
        $totals = $application->walletEntries()->selectRaw('kind, status, SUM(amount_minor) AS total')
            ->groupBy('kind', 'status')->get()->keyBy(fn (Entry $entry) => $entry->kind.'.'.$entry->status);
        $credited = (int) ($totals->get('credit.paid')?->total ?? 0);
        $held = (int) ($totals->get('withdrawal.pending')?->total ?? 0) + (int) ($totals->get('withdrawal.approved')?->total ?? 0);
        $paid = (int) ($totals->get('withdrawal.paid')?->total ?? 0);

        return ['available' => $credited - $held - $paid, 'held' => $held, 'paid' => $paid];
    }

    public function destinations(Application $application): array
    {
        $destinations = [];
        foreach ($application->wallet_destinations ?? [] as $id => $saved) {
            $destinations[$id] = isset($saved['method'], $saved['details']) ? $saved : ['method' => $id, 'details' => $saved];
        }

        return $destinations;
    }

    public function saveDestination(Application $record, string $method, array $destination, ?string $id = null): void
    {
        DB::transaction(function () use ($record, $method, $destination, $id): void {
            $application = $this->lockActiveApplication($record->id);
            $destinations = $this->destinations($application);
            abort_if($id !== null && ($destinations[$id]['method'] ?? null) !== $method, 404);
            $destinations[$id ?? (string) Str::uuid()] = ['method' => $method, 'details' => $destination];
            $application->update(['wallet_destinations' => $destinations]);
        });
    }

    public function withdraw(Application $record, string $destinationId, string $amount, string $key): void
    {
        DB::transaction(function () use ($record, $destinationId, $amount, $key): void {
            $application = $this->lockActiveApplication($record->id);
            $requestKey = hash('sha256', 'withdrawal:'.$key);
            if ($application->walletEntries()->where('request_key', $requestKey)->exists()) {
                return;
            }

            $destination = $this->destinations($application)[$destinationId] ?? null;
            if (! $destination) {
                throw ValidationException::withMessages(['method' => __('expert_wallet.method_missing')]);
            }

            $amountMinor = Money::toMinor($amount);
            if ($amountMinor <= 0 || $amountMinor > $this->balances($application)['available']) {
                throw ValidationException::withMessages(['amount' => __('expert_wallet.insufficient')]);
            }

            $application->walletEntries()->create([
                'kind' => Entry::WITHDRAWAL,
                'status' => Entry::PENDING,
                'amount_minor' => $amountMinor,
                'method' => $destination['method'],
                'destination' => $destination['details'],
                'request_key' => $requestKey,
            ]);
        });
    }

    public function credit(Application $record, User $staff, string $amount, string $reference): void
    {
        abort_unless($staff->isAdmin(), 403);
        validator(['amount' => $amount], ['amount' => self::AMOUNT_RULES])->validate();
        DB::transaction(function () use ($record, $staff, $amount, $reference): void {
            $application = $this->lockActiveApplication($record->id);
            $requestKey = hash('sha256', 'credit:'.mb_strtolower(trim($reference)));
            if ($application->walletEntries()->where('request_key', $requestKey)->exists()) {
                throw ValidationException::withMessages(['reference' => __('expert_wallet.duplicate_reference')]);
            }

            $application->walletEntries()->create([
                'kind' => Entry::CREDIT, 'status' => Entry::PAID, 'amount_minor' => Money::toMinor($amount),
                'reference' => $reference, 'request_key' => $requestKey, 'history' => [$this->audit($staff, Entry::PAID)],
            ]);
        });
    }

    public function decide(Entry $record, User $staff, string $status, ?string $reference = null, ?string $note = null): void
    {
        abort_unless($staff->isAdmin(), 403);
        DB::transaction(function () use ($record, $staff, $status, $reference, $note): void {
            Application::query()->lockForUpdate()->findOrFail($record->consultancy_application_id);
            $entry = Entry::query()->lockForUpdate()->findOrFail($record->id);
            $allowed = match ($entry->status) {
                Entry::PENDING => [Entry::APPROVED, Entry::REJECTED],
                Entry::APPROVED => [Entry::PAID, Entry::REJECTED],
                default => [],
            };
            if ($entry->kind !== Entry::WITHDRAWAL || ! in_array($status, $allowed, true) || ($status === Entry::PAID && blank($reference))) {
                throw ValidationException::withMessages(['reference' => __('expert_wallet.invalid_decision')]);
            }
            $entry->update([
                'status' => $status,
                'reference' => $reference,
                'note' => $note,
                'history' => [...($entry->history ?? []), $this->audit($staff, $status)],
            ]);
        });
    }

    private function audit(User $staff, string $status): array
    {
        return ['user_id' => $staff->id, 'status' => $status, 'at' => now()->toIso8601String()];
    }

    private function lockActiveApplication(int $id): Application
    {
        return Application::query()->where('status', Application::APPROVED)->whereNotNull('signed_at')->lockForUpdate()->findOrFail($id);
    }
}
