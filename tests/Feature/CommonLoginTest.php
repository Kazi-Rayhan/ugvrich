<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommonLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_common_login_page_is_available_in_both_site_locales(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('action="'.route('login.store').'"', escape: false);

        $this->get('/en/login')
            ->assertOk()
            ->assertSee('action="'.route('en.login.store').'"', escape: false);
    }

    public function test_legacy_researcher_login_url_redirects_to_common_login_page(): void
    {
        $this->get('/researcher/login')->assertRedirect('/login');
        $this->get('/en/researcher/login')->assertRedirect('/en/login');
    }

    public function test_successful_researcher_login_opens_researcher_dashboard(): void
    {
        $researcher = User::factory()->create([
            'email' => 'researcher@example.org',
            'role' => User::RESEARCHER,
        ]);

        $this->post('/login', [
            'email' => $researcher->email,
            'password' => 'password',
        ])->assertRedirect(route('researcher.dashboard', absolute: false));

        $this->assertAuthenticatedAs($researcher);
    }

    public function test_english_researcher_login_keeps_the_english_portal_url(): void
    {
        $researcher = User::factory()->create([
            'email' => 'english-researcher@example.org',
            'role' => User::RESEARCHER,
        ]);

        $this->post('/en/login', [
            'email' => $researcher->email,
            'password' => 'password',
        ])->assertRedirect('/en/researcher/dashboard');
    }

    public function test_successful_staff_login_opens_admin_dashboard(): void
    {
        $staff = User::factory()->create([
            'email' => 'staff@example.org',
            'role' => 'reviewer',
        ]);

        $this->post('/login', [
            'email' => $staff->email,
            'password' => 'password',
        ])->assertRedirect('/admin');

        $this->assertAuthenticatedAs($staff);
    }

    public function test_homepage_main_navigation_includes_projects_and_common_login(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('href="'.route('projects.index').'"', escape: false)
            ->assertSee('href="'.route('login').'"', escape: false);
    }

    public function test_authenticated_header_user_menu_links_to_dashboard_and_logs_out(): void
    {
        $researcher = User::factory()->create([
            'email' => 'menu-researcher@example.org',
            'role' => User::RESEARCHER,
        ]);

        $this->actingAs($researcher)
            ->get('/')
            ->assertOk()
            ->assertSee('aria-label="'.__('site.nav.dashboard').'"', escape: false)
            ->assertSee('href="'.route('researcher.dashboard', absolute: false).'"', escape: false)
            ->assertSee('action="'.route('researcher.logout').'"', escape: false)
            ->assertSee(__('researcher.nav.logout'));
    }

    public function test_desktop_account_dropdown_is_not_clipped_by_the_utility_strip(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('header-strip relative z-20 hidden overflow-visible lg:block', escape: false)
            ->assertSee('@click="accountOpen = ! accountOpen"', escape: false)
            ->assertSee('x-show="accountOpen"', escape: false);
    }
}
