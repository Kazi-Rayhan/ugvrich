<?php

namespace Tests\Feature;

use App\Filament\Resources\InternshipApplications\InternshipApplicationResource;
use App\Filament\Resources\StudentMemberships\StudentMembershipResource;
use App\Models\InternshipApplication;
use App\Models\Role;
use App\Models\StudentMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The two application inboxes follow the role system like every other part of the dashboard. */
class ApplicationInboxPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('permissions:sync')->assertSuccessful();
    }

    protected function staff(array $permissions): User
    {
        $role = Role::create(['name' => 'inbox_test', 'display_name' => 'Inbox test', 'group' => Role::DASHBOARD]);
        $role->syncPermissionNames(array_merge([User::ACCESS_PANEL], $permissions));

        return User::factory()->create(['role' => User::ADMIN, 'role_id' => $role->id]);
    }

    public function test_the_permissions_exist(): void
    {
        foreach (['internship_application', 'student_membership'] as $model) {
            foreach (['view_any', 'view', 'update', 'delete'] as $action) {
                $this->assertDatabaseHas('permissions', ['name' => "{$action}_{$model}"]);
            }
        }
    }

    public function test_staff_without_the_permission_are_refused(): void
    {
        $user = $this->staff([]);

        $this->actingAs($user)->get(InternshipApplicationResource::getUrl('index'))->assertForbidden();
        $this->actingAs($user)->get(StudentMembershipResource::getUrl('index'))->assertForbidden();
    }

    public function test_staff_whose_role_has_it_can_read_the_inbox(): void
    {
        $user = $this->staff(['view_any_internship_application', 'view_any_student_membership']);

        $this->assertTrue($user->can('viewAny', InternshipApplication::class));
        $this->assertFalse($user->can('delete', new InternshipApplication));
        $this->assertTrue($user->can('viewAny', StudentMembership::class));

        $this->actingAs($user)->get(InternshipApplicationResource::getUrl('index'))->assertOk();
        $this->actingAs($user)->get(StudentMembershipResource::getUrl('index'))->assertOk();
    }

    public function test_an_administrator_sees_both(): void
    {
        $this->artisan('roles:sync-admin-permissions')->assertSuccessful();
        $admin = User::factory()->create(['role' => User::ADMIN, 'role_id' => Role::where('name', Role::ADMIN)->value('id')]);

        $this->actingAs($admin)->get(InternshipApplicationResource::getUrl('index'))->assertOk();
        $this->actingAs($admin)->get(StudentMembershipResource::getUrl('index'))->assertOk();
    }
}
