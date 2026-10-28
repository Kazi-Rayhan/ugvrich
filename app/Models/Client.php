<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Someone RICH sends proposals to and takes orders from. */
class Client extends Model
{
    protected $guarded = ['id', 'phone_normalized'];

    protected static function booted(): void
    {
        static::saving(function (Client $client) {
            $client->email = filled($client->email) ? mb_strtolower(trim($client->email)) : null;
            $client->phone_normalized = static::normalizePhone($client->phone);
        });
    }

    /**
     * Digits only, with Bangladesh's +880 / 880 folded into the local leading 0,
     * so the same number typed two ways matches one client.
     */
    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '880') && strlen($digits) === 13) {
            $digits = '0'.substr($digits, 3);
        }

        return $digits;
    }

    /** The same kinds of requester the public consultancy form offers. */
    public static function organizationTypeOptions(): array
    {
        return ConsultancyRequest::organizationOptions();
    }

    /** "Name (Organization)" — what the client pickers show. */
    public function getDisplayNameAttribute(): string
    {
        return $this->organization ? "{$this->name} ({$this->organization})" : $this->name;
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
