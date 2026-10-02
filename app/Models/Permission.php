<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * One thing somebody may do, named rather than coded.
 *
 * Names follow {@see \App\Helpers\PolicyHelper}: the policy action and the
 * model it is about, both in snake case — `view_any_research_idea`,
 * `review_research_proposal`. Keeping to that means a policy, a route and a
 * permission row can never drift apart.
 */
class Permission extends Model
{
    /** Every permission name, for registering gates. */
    public const CACHE_KEY_NAMES = 'permissions.all_names';

    /** The same names keyed for an O(1) lookup while a request is served. */
    public const CACHE_KEY_NAME_LOOKUP = 'permissions.all_names_lookup';

    public const CACHE_TTL = 3600;

    protected $fillable = ['name', 'group', 'status', 'description'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /** @return Collection<int, string> */
    public static function cachedNames(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY_NAMES,
            self::CACHE_TTL,
            fn () => static::query()->orderBy('group')->orderBy('name')->pluck('name'),
        );
    }

    /**
     * The names keyed by themselves, so the gate can answer "is this ability a
     * permission at all?" without a query or a scan.
     *
     * @return array<string, true>
     */
    public static function cachedNameLookup(): array
    {
        return Cache::remember(
            self::CACHE_KEY_NAME_LOOKUP,
            self::CACHE_TTL,
            fn () => array_fill_keys(static::query()->pluck('name')->all(), true),
        );
    }

    public static function forgetCachedNames(): void
    {
        Cache::forget(self::CACHE_KEY_NAMES);
        Cache::forget(self::CACHE_KEY_NAME_LOOKUP);
    }

    /** A permission read as a sentence, for the admin. */
    public function label(): string
    {
        return $this->description ?: str($this->name)->replace('_', ' ')->ucfirst()->toString();
    }

    protected static function booted(): void
    {
        // The cache is a copy of this table, so it goes when the table moves.
        static::saved(fn () => static::forgetCachedNames());
        static::deleted(fn () => static::forgetCachedNames());
    }
}
