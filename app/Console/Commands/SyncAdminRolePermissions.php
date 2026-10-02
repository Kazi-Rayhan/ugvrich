<?php

namespace App\Console\Commands;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Console\Command;

class SyncAdminRolePermissions extends Command
{
    protected $signature = 'roles:sync-admin-permissions';

    protected $description = 'Give the admin role every permission in the system';

    public function handle(RolePermissionSeeder $seeder): int
    {
        $count = $seeder->syncAdminRolePermissions();

        $this->info("Synced all {$count} permissions to the admin role.");

        return self::SUCCESS;
    }
}
