<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderExpertAssignment extends Model
{
    protected $guarded = [];

    protected $casts = [
        'engagement_percentage' => 'decimal:2',
        'credited_minor' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(ConsultancyApplication::class, 'consultancy_application_id');
    }
}
