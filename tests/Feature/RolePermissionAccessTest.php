<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageSiteSettings;
use App\Filament\Pages\ReportsAnalytics;
use App\Filament\Resources\Publications\PublicationResource;
use App\Models\Project;
use App\Models\Publication;
use App\Models\Role;
use App\Models\User;
use App\Filament\Resources\Projects\ProjectResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_role_permissions_are_not_bypassed_by_the_legacy_admin_field(): void
    {
        $role = Role::create([
            'name' => 'project_reader',
            'display_name' => 'Project Reader',
            'group' => Role::DASHBOARD,
        ]);
        $role->syncPermissionNames(['view_any_project']);
        Publication::create(['title' => 'Restricted publication record']);

        $user = User::factory()->create([
            'role' => User::ADMIN,
            'role_id' => $role->id,
        ]);

        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->canAccessPanel(app(\Filament\Panel::class)));
        $this->assertTrue($user->can('viewAny', Project::class));
        $this->assertFalse($user->can('create', Project::class));

        $this->actingAs($user)
            ->get(ProjectResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(ProjectResource::getUrl('create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(PublicationResource::getUrl('index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->get(ReportsAnalytics::getUrl())
            ->assertForbidden();

        $this->actingAs($user)
            ->get(ManageSiteSettings::getUrl())
            ->assertForbidden();

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Research Projects')
            ->assertDontSee('Restricted publication record')
            ->assertDontSee('Recent publications')
            ->assertDontSee('Reports &amp; Analytics', false);
    }

    public function test_explicit_administrator_role_still_has_full_access(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->can('create', Project::class));
    }
}
