<?php

namespace Tests\Feature;

use App\Filament\Resources\StudentMemberships\StudentMembershipResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\StudentMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The internship inbox follows the role system like every other part of the dashboard. */
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

    public function test_the_permissions_exist_under_the_inbox_as_internship_applications(): void
    {
        foreach (['view_any', 'view', 'update', 'delete'] as $action) {
            $this->assertDatabaseHas('permissions', ['name' => "{$action}_student_membership", 'group' => 'Inbox']);
        }

        $this->assertSame('List internship application records', Permission::where('name', 'view_any_student_membership')->value('description'));
    }

    public function test_the_retired_internship_form_permissions_are_removed(): void
    {
        Permission::create(['name' => 'view_any_internship_application', 'group' => 'Inbox']);

        $this->artisan('permissions:sync')->assertSuccessful();

        $this->assertDatabaseMissing('permissions', ['name' => 'view_any_internship_application']);
    }

    public function test_staff_without_the_permission_are_refused(): void
    {
        $this->actingAs($this->staff([]))->get(StudentMembershipResource::getUrl('index'))->assertForbidden();
    }

    public function test_staff_whose_role_has_it_can_read_the_inbox(): void
    {
        $user = $this->staff(['view_any_student_membership']);

        $this->assertTrue($user->can('viewAny', StudentMembership::class));
        $this->assertFalse($user->can('delete', new StudentMembership));

        $this->actingAs($user)->get(StudentMembershipResource::getUrl('index'))->assertOk();
    }

    public function test_an_administrator_sees_it(): void
    {
        $this->artisan('roles:sync-admin-permissions')->assertSuccessful();
        $admin = User::factory()->create(['role' => User::ADMIN, 'role_id' => Role::where('name', Role::ADMIN)->value('id')]);

        $this->actingAs($admin)->get(StudentMembershipResource::getUrl('index'))->assertOk();
    }
}
