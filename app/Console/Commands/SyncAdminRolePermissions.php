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
        $defined = $seeder->syncPermissions();
        $count = $seeder->syncAdminRolePermissions();
        $users = $seeder->assignLegacyAdminUsers();

        $this->info("Synced {$defined} application permissions and assigned all {$count} permissions to the admin role.");
        $this->info("Linked {$users} legacy admin accounts to the admin role.");

        return self::SUCCESS;
    }
}
