<?php

namespace App\Console\Commands;

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Console\Command;

class SyncPermissions extends Command
{
    protected $signature = 'permissions:sync';

    protected $description = 'Sync the permissions defined by the application';

    public function handle(RolePermissionSeeder $seeder): int
    {
        $count = $seeder->syncPermissions();

        $this->info("Synced {$count} permissions.");

        return self::SUCCESS;
    }
}
