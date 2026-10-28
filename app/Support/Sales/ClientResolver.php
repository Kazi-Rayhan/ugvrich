<?php

namespace App\Support\Sales;

use App\Models\Client;

/**
 * Finds the client a set of details belongs to, or makes one.
 *
 * Matching is by email first, then by phone (digits only, +880 folded into
 * the local 0). A match is reused as it is: details typed for a "new" client
 * who turns out to exist fill only the gaps on the existing record and never
 * overwrite what is there. Callers run this inside their own transaction.
 */
class ClientResolver
{
    /** @param  array<string, mixed>  $details  name, email, phone, organization, organization_type, address */
    public static function resolve(array $details, ?int $userId = null): Client
    {
        $email = filled($details['email'] ?? null) ? mb_strtolower(trim($details['email'])) : null;
        $phone = Client::normalizePhone($details['phone'] ?? null);

        $existing = null;

        if ($email) {
            $existing = Client::where('email', $email)->lockForUpdate()->first();
        }

        if (! $existing && $phone) {
            $existing = Client::where('phone_normalized', $phone)->lockForUpdate()->first();
        }

        $fields = collect($details)
            ->only(['name', 'email', 'phone', 'organization', 'organization_type', 'address'])
            ->filter(fn ($value) => filled($value))
            ->all();

        if ($existing) {
            $gaps = collect($fields)->filter(fn ($value, $key) => blank($existing->{$key}))->all();

            // Never take an email or phone that already belongs to someone else.
            if (isset($gaps['email']) && Client::where('email', $email)->whereKeyNot($existing->id)->exists()) {
                unset($gaps['email']);
            }

            if (isset($gaps['phone']) && Client::where('phone_normalized', $phone)->whereKeyNot($existing->id)->exists()) {
                unset($gaps['phone']);
            }

            if ($gaps !== []) {
                $existing->fill($gaps)->save();
            }

            return $existing;
        }

        return Client::create($fields + ['created_by' => $userId]);
    }
}
