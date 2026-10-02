<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A named bag of permissions.
 *
 * A user has one primary role and may be given others besides; what they may
 * do is the union of the lot. Roles are rows rather than constants so that a
 * new one — a reviewer who may decide on papers but not on money, say — can be
 * made in the admin without a deployment.
 */
class Role extends Model
{
    /** The role every other role is measured against: it holds everything. */
    public const ADMIN = 'admin';

    /** Roles whose people work in the admin dashboard. */
    public const DASHBOARD = 'dashboard';

    /** Roles whose people work in the Researcher Portal instead. */
    public const PORTAL = 'portal';

    /** @return array<string, string> */
    public static function groups(): array
    {
        return [
            self::DASHBOARD => 'Admin dashboard',
            self::PORTAL => 'Researcher Portal',
        ];
    }

    protected $fillable = ['name', 'display_name', 'group', 'description'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /** Users who carry this as an additional role. */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /** Users who carry it as their primary one. */
    public function primaryUsers(): HasMany
    {
        return $this->hasMany(User::class, 'role_id');
    }

    /**
     * Give this role exactly these permissions, by name.
     *
     * Names that do not exist are created, because a seeder that lists a
     * permission is the thing declaring it should exist.
     *
     * @param  array<int, string>  $names
     */
    public function syncPermissionNames(array $names, ?string $group = null): void
    {
        $ids = collect($names)
            ->unique()
            ->map(fn (string $name) => Permission::firstOrCreate(['name' => $name], ['group' => $group])->id)
            ->all();

        $this->permissions()->sync($ids);
    }

    public function isAdmin(): bool
    {
        return $this->name === self::ADMIN;
    }

    public function isDashboardRole(): bool
    {
        return $this->group !== self::PORTAL;
    }

    /** Where the people with this role work, in words. */
    public function worksIn(): string
    {
        return static::groups()[$this->group] ?? static::groups()[self::DASHBOARD];
    }

    protected static function booted(): void
    {
        /* A dashboard role has to be able to open the dashboard, and a portal
           role must not. Enforced here rather than left to whoever ticks the
           boxes, because getting it wrong either locks somebody out or lets
           them in. */
        static::saved(function (self $role) {
            $permission = Permission::firstOrCreate(
                ['name' => User::ACCESS_PANEL],
                ['group' => 'People and access', 'description' => 'Sign in to the admin dashboard'],
            );

            $role->isDashboardRole()
                ? $role->permissions()->syncWithoutDetaching([$permission->id])
                : $role->permissions()->detach($permission->id);
        });
    }
}
