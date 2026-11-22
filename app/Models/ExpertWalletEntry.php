<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpertWalletEntry extends Model
{
    public const CREDIT = 'credit';
    public const WITHDRAWAL = 'withdrawal';
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const PAID = 'paid';
    public const REJECTED = 'rejected';

    protected $guarded = [];

    protected $hidden = ['destination', 'request_key'];

    protected $casts = [
        'amount_minor' => 'integer',
        'destination' => 'encrypted:array',
        'history' => 'array',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(ConsultancyApplication::class, 'consultancy_application_id');
    }
}
