<?php

namespace App\Filament\Resources\Roles\Pages\Concerns;

use App\Filament\Resources\Roles\RoleResource;
use App\Models\Role;

/**
 * The permission checklist is shown one group at a time, so it has to be taken
 * apart on the way in and put back together on the way out.
 */
trait SyncsPermissionGroups
{
    /** Fill each group's field from the permissions the role already holds. */
    protected function fillPermissionGroups(array $data, Role $role): array
    {
        $held = $role->permissions()->pluck('permissions.id')->all();

        foreach (RoleResource::permissionGroups() as $group => $permissions) {
            $data[RoleResource::fieldFor($group)] = array_values(
                array_intersect($permissions->pluck('id')->all(), $held),
            );
        }

        return $data;
    }

    /** Collect every group's ticks back into one list and save it. */
    protected function syncPermissionGroups(Role $role): void
    {
        // The Administrator role is the seeder's to decide, not this screen's.
        if ($role->isAdmin()) {
            return;
        }

        $chosen = [];

        foreach (RoleResource::permissionGroups() as $group => $permissions) {
            $chosen = array_merge($chosen, (array) ($this->data[RoleResource::fieldFor($group)] ?? []));
        }

        $role->permissions()->sync(array_map('intval', $chosen));

        /* Saving again puts back the permission that opens the dashboard for a
           role that works there, and takes it away from one that does not.
           See Role::booted(). */
        $role->save();
    }
}
