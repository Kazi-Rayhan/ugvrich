<?php

namespace Tests\Feature;

use App\Models\User;
use App\Filament\Resources\ResearchIdeas\ResearchIdeaResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserImpersonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_a_user_dashboard_from_the_users_list(): void
    {
        $admin = User::factory()->create();
        $target = User::factory()->create([
            'name' => 'Researcher Account',
            'role' => User::RESEARCHER,
        ]);

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('Login as')
            ->assertSee(route('impersonate.start', $target), escape: false);

        $this->actingAs($admin)
            ->get(route('impersonate.start', $target))
            ->assertRedirect(route('researcher.dashboard'));

        $this->assertAuthenticatedAs($target);

        $this->get(route('researcher.dashboard'))->assertOk();

        $this->assertAuthenticatedAs($target);
        $this->get(route('researcher.dashboard'))->assertSee('Back to my account');
    }

    public function test_admin_can_return_to_their_own_account_after_impersonating(): void
    {
        $admin = User::factory()->create();
        $target = User::factory()->create(['role' => User::RESEARCHER]);

        $this->actingAs($admin)->get(route('impersonate.start', $target));

        $this->get(route('impersonate.stop'))
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($admin);
    }

    public function test_staff_dashboard_login_as_refreshes_filaments_authenticated_session_hash(): void
    {
        $admin = User::factory()->create();
        $target = User::factory()->create(['password' => Hash::make('different-password')]);

        $this->actingAs($admin)->get('/admin')->assertOk();

        $this->get(route('impersonate.start', $target))
            ->assertRedirect('/admin');

        $this->get('/admin')->assertOk();

        $this->assertAuthenticatedAs($target);

        $this->get(ResearchIdeaResource::getUrl('index').'?tab=approved')
            ->assertOk()
            ->assertSee('Back to my account')
            ->assertSee('position: fixed')
            ->assertSee('impersonation-return__icon')
            ->assertSee(route('impersonate.stop'), escape: false);

        $this->get(route('impersonate.stop'))
            ->assertRedirect('/admin');

        $this->get('/admin')->assertOk();

        $this->assertAuthenticatedAs($admin);
    }

    public function test_an_account_cannot_use_login_as_on_itself(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('impersonate.start', $user))
            ->assertForbidden();

        $this->get('/admin')
            ->assertOk()
            ->assertDontSee('Back to my account');
    }
}
