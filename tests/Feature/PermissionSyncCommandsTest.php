<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionSyncCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_permissions_sync_command_syncs_the_application_permissions_only(): void
    {
        $this->artisan('permissions:sync')->assertSuccessful();

        $this->assertDatabaseHas('permissions', ['name' => User::ACCESS_PANEL]);
        $this->assertDatabaseHas('permissions', ['name' => 'view_any_research_proposal']);
        $this->assertDatabaseHas('permissions', ['name' => 'view_any_publication']);
        $this->assertDatabaseHas('permissions', ['name' => 'view_reports_analytics']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage_site_settings']);
        $this->assertDatabaseCount('roles', 0);
    }

    public function test_admin_permissions_command_syncs_every_permission_to_the_admin_role(): void
    {
        Permission::create(['name' => 'custom_test_permission', 'group' => 'Tests']);

        $this->artisan('roles:sync-admin-permissions')->assertSuccessful();

        $admin = Role::query()->where('name', Role::ADMIN)->firstOrFail();

        $this->assertSame(
            Permission::query()->orderBy('id')->pluck('id')->all(),
            $admin->permissions()->orderBy('permissions.id')->pluck('permissions.id')->all(),
        );
        $this->assertSame('Administrator', $admin->display_name);
    }

    public function test_admin_permissions_sync_links_legacy_admin_accounts_missing_a_primary_role(): void
    {
        $legacyAdmin = User::factory()->create(['role' => User::ADMIN]);
        $legacyAdmin->forceFill(['role_id' => null])->save();

        $assignedAdmin = Role::create([
            'name' => 'assigned_admin_staff',
            'display_name' => 'Assigned Admin Staff',
            'group' => Role::DASHBOARD,
        ]);
        $adminWithExplicitRole = User::factory()->create([
            'role' => User::ADMIN,
            'role_id' => $assignedAdmin->id,
        ]);

        $this->artisan('roles:sync-admin-permissions')->assertSuccessful();

        $adminRole = Role::query()->where('name', Role::ADMIN)->firstOrFail();
        $this->assertSame($adminRole->id, $legacyAdmin->fresh()->role_id);
        $this->assertContains('view_any_project', $legacyAdmin->fresh()->permissionNames()->all());
        $this->assertSame($assignedAdmin->id, $adminWithExplicitRole->fresh()->role_id);
    }
}
