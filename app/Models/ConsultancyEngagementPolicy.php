<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ConsultancyEngagementPolicy extends Model
{
    public const CONTENT_FIELDS = ['privacy_notice', 'privacy_notice_bn', 'participation_policy', 'participation_policy_bn', 'sections'];

    protected $fillable = self::CONTENT_FIELDS;

    protected $casts = ['sections' => 'array'];

    protected static function booted(): void
    {
        static::updating(function (self $policy): void {
            if ($policy->isDirty(self::CONTENT_FIELDS)) {
                $policy->version = (string) Str::ulid();
            }
        });
    }
}
