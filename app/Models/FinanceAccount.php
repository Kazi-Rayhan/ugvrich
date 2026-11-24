<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceAccount extends Model
{
    public const TYPES = ['cash' => 'Cash', 'bank' => 'Bank', 'mobile' => 'Mobile banking', 'other' => 'Other'];

    protected $fillable = ['name', 'type', 'is_active', 'notes', 'details'];

    protected $hidden = ['details'];

    protected $casts = ['is_active' => 'boolean', 'balance_minor' => 'integer', 'details' => 'encrypted:array'];

    protected static function booted(): void
    {
        static::saving(function (FinanceAccount $account): void {
            if (! $account->isDirty(['type', 'details'])) {
                return;
            }

            $fields = match ($account->type) {
                'bank' => ['bank_name', 'branch_name', 'district', 'account_name', 'account_number', 'routing_number'],
                'mobile' => ['provider', 'mobile_number'],
                default => [],
            };
            $details = collect($account->details[$account->type] ?? [])->only($fields)
                ->filter(fn ($value) => filled($value))->all();

            $account->details = $details === [] ? null : [$account->type => $details];
        });
    }
}
