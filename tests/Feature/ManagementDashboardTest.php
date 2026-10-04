<?php

namespace Tests\Feature;

use App\Models\Facility;
use App\Models\StudentTeam;
use App\Models\User;
use Database\Seeders\RichContentSeeder;
use Database\Seeders\RichFacilitySeeder;
use Database\Seeders\RichInnovationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The login-based management dashboard: its home, its menu and its new sections. */
class ManagementDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RichContentSeeder::class, RichInnovationSeeder::class, RichFacilitySeeder::class]);
    }

    public function test_dashboard_requires_a_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_signing_out_of_the_dashboard_lands_on_the_home_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('filament.admin.auth.logout'))
            ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_dashboard_home_shows_the_figures_pipeline_and_projects(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/admin')->assertOk();

        $response->assertSee('Research & Innovation Management System');

        foreach (['Active Projects', 'New Ideas', 'Prototypes', 'Patent / IP', 'Startups', 'Publications'] as $figure) {
            $response->assertSee($figure, false);
        }

        foreach (['Ideas', 'Evaluation', 'Research', 'Prototype', 'Testing', 'Commercialization', 'Startup'] as $stage) {
            $response->assertSee($stage, false);
        }

        $response->assertSee('SUN CAR')
            ->assertSee('Upcoming events')
            ->assertSee('Recent publications')
            ->assertSee('Funding & grants', false)
            ->assertSee('Industry requests');
    }

    public function test_left_menu_lists_every_section_in_order(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/admin')->assertOk();

        $response->assertSeeInOrder([
            'Dashboard', 'Research Projects', 'Innovation Projects', 'Idea Submission', 'Researchers',
            'Students / Teams', 'Publications', 'Patent &amp; IP', 'Prototype Management',
            'Startup &amp; Incubation', 'Consultancy Projects', 'Industry Partners', 'Labs &amp; Equipment',
            'Funding &amp; Grants', 'Events &amp; Training', 'Reports &amp; Analytics', 'Website Content',
            'Users &amp; Permissions', 'Settings',
        ], false);
    }

    public function test_reports_page_renders(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/reports')
            ->assertOk()
            ->assertSee('Activity by department');
    }

    public function test_new_sections_list_their_records(): void
    {
        $user = User::factory()->create();

        StudentTeam::create(['name' => 'Team Solaris', 'department' => 'EEE', 'members' => ['A', 'B']]);

        $this->actingAs($user)->get('/admin/student-teams')->assertOk()->assertSee('Team Solaris');
        $this->actingAs($user)->get('/admin/facilities')->assertOk()->assertSee(Facility::first()->name);
        $this->actingAs($user)->get('/admin/users')->assertOk()->assertSee($user->email);
    }
}
