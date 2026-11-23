<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinanceAccount extends Model
{
    public const TYPES = ['cash' => 'Cash', 'bank' => 'Bank', 'mobile' => 'Mobile banking', 'other' => 'Other'];

    protected $fillable = ['name', 'type', 'is_active', 'notes'];

    protected $casts = ['is_active' => 'boolean', 'balance_minor' => 'integer'];
}
