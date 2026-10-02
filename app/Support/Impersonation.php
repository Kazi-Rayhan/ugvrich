<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

/**
 * Signing in as somebody else, to see what they see.
 *
 * Who started it is kept in the session, encrypted, so the page a support
 * person is looking at cannot be used to work out whose session it really is —
 * and so the id cannot be edited to come back as anybody else.
 *
 * Deliberately shallow: one level only. Somebody already signed in as another
 * person cannot sign in as a third, which would make the trail impossible to
 * read.
 */
class Impersonation
{
    public const SESSION_KEY = 'impersonation_payload';

    public static function active(): bool
    {
        return session()->has(self::SESSION_KEY);
    }

    /** The account that started this, if any. */
    public static function original(): ?User
    {
        $payload = static::payload();

        return $payload === null ? null : User::find($payload['from']);
    }

    /** Begin. The caller is responsible for having checked the policy first. */
    public static function begin(User $impersonator, User $target): void
    {
        Auth::login($target, false);
        static::forgetAuthenticatedPasswordHash();

        session()->put(self::SESSION_KEY, Crypt::encryptString(json_encode([
            'from' => $impersonator->id,
            'to' => $target->id,
        ], JSON_THROW_ON_ERROR)));

        // A new session id, so the two sessions are not the same one.
        session()->regenerate();
    }

    /**
     * End it, and sign back in as whoever started it.
     *
     * Returns the account signed back in as, or null where that account can no
     * longer be found — in which case the caller should sign out entirely
     * rather than leave somebody signed in as a person they are not.
     */
    public static function finish(): ?User
    {
        $payload = static::payload();

        session()->forget(self::SESSION_KEY);

        if ($payload === null || Auth::id() !== $payload['to']) {
            return null;
        }

        $original = User::find($payload['from']);

        if (! $original) {
            return null;
        }

        Auth::login($original, false);
        static::forgetAuthenticatedPasswordHash();
        session()->regenerate();

        return $original;
    }

    protected static function forgetAuthenticatedPasswordHash(): void
    {
        session()->forget('password_hash_'.Auth::getDefaultDriver());
    }

    /**
     * @return array{from: int, to: int}|null
     */
    protected static function payload(): ?array
    {
        $encrypted = session(self::SESSION_KEY);

        if (! is_string($encrypted) || $encrypted === '') {
            return null;
        }

        try {
            $data = json_decode(Crypt::decryptString($encrypted), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            // Tampered with, or from an older key. Treat it as not set.
            return null;
        }

        $from = (int) ($data['from'] ?? 0);
        $to = (int) ($data['to'] ?? 0);

        return ($from > 0 && $to > 0) ? ['from' => $from, 'to' => $to] : null;
    }
}
