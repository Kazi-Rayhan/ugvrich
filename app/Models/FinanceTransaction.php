<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FinanceTransaction extends Model
{
    public const INCOME = 'income';

    public const EXPENSE = 'expense';

    public const TRANSFER = 'transfer';

    public const OPENING = 'opening';

    public const KINDS = [
        self::OPENING => 'Opening balance',
        self::INCOME => 'Income',
        self::EXPENSE => 'Expense',
        self::TRANSFER => 'Transfer',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'from_account_id' => 'integer',
        'to_account_id' => 'integer',
        'amount_minor' => 'integer',
        'transaction_date' => 'date',
    ];

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(FinanceAccount::class, 'to_account_id');
    }

    public function reversal(): HasOne
    {
        return $this->hasOne(self::class, 'reversal_of_id');
    }
}
