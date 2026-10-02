<?php

namespace App\Providers;

use App\Helpers\PolicyHelper;
use App\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Turns every permission row into a gate.
 *
 * `Gate::before` is asked about every ability. If the ability happens to be the
 * name of a permission, the answer is whether the user's roles include it;
 * anything else is left alone and falls through to the policies, which is what
 * keeps `view`, `update` and the rest of {@see \App\Policies} in charge of who
 * owns what.
 *
 * The lookup is cached, so this costs one query per cache period rather than
 * one per check.
 */
class AuthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::before(function ($user, $ability, $arguments) {
            if (! is_string($ability) || $ability === '' || ! $user) {
                return null;
            }

            if (! method_exists($user, 'hasPermission')) {
                return null;
            }

            // Resolved once per request rather than once per check.
            static $permissions = null;
            $permissions ??= $this->permissionLookup();

            if (isset($permissions[$ability])) {
                return $user->isAdmin() || $user->hasPermission($ability);
            }

            $model = $arguments[0] ?? null;

            if ((! is_string($model) && ! $model instanceof Model) || Gate::getPolicyFor($model)) {
                return null;
            }

            $permissionAction = match ($ability) {
                'deleteAny', 'forceDelete', 'forceDeleteAny' => 'delete',
                'replicate' => 'create',
                'reorder', 'restore', 'restoreAny' => 'update',
                default => $ability,
            };
            $permission = PolicyHelper::name($permissionAction, $model);

            if ($user->isAdmin()) {
                return true;
            }

            if (! isset($permissions[$permission])) {
                return false;
            }

            return $user->hasPermission($permission);
        });
    }

    /**
     * The permission names, or nothing at all if the table is not there yet —
     * migrations and `php artisan` must keep working on a fresh database.
     *
     * @return array<string, true>
     */
    protected function permissionLookup(): array
    {
        try {
            return Permission::cachedNameLookup();
        } catch (\Throwable) {
            return [];
        }
    }
}
