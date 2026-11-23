<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RichContentSeeder;
use Database\Seeders\RichInnovationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RichContentSeeder::class, RichInnovationSeeder::class]);
    }

    public function test_admin_panel_requires_authentication(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    /**
     * Every parameterless Filament GET route should render for a signed-in admin.
     * This catches broken resource forms, tables and custom pages in one pass.
     */
    public function test_all_filament_pages_render_for_an_admin(): void
    {
        $user = User::factory()->create();

        $urls = collect(Route::getRoutes())
            ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'filament.admin.')
                && in_array('GET', $route->methods(), true)
                && ! str_contains($route->uri(), '{')
                && ! str_contains($route->uri(), 'login')
                && ! str_contains($route->uri(), 'logout'))
            ->map(fn ($route) => '/'.$route->uri())
            ->unique()
            ->values();

        $this->assertGreaterThan(10, $urls->count(), 'Expected the admin panel to expose many pages.');

        foreach ($urls as $url) {
            $response = $this->actingAs($user)->get($url);

            if ($url === '/admin/work-orders/create') {
                $response->assertRedirect('/admin/proposals/create?work_order=1');

                continue;
            }

            $response->assertOk("Admin page {$url} did not render.");
        }
    }

    public function test_resource_edit_pages_render(): void
    {
        $user = User::factory()->create();

        $records = [
            \App\Filament\Resources\ServiceCategories\ServiceCategoryResource::class => \App\Models\ServiceCategory::first(),
            \App\Filament\Resources\Services\ServiceResource::class => \App\Models\Service::first(),
            \App\Filament\Resources\CoreAreas\CoreAreaResource::class => \App\Models\CoreArea::first(),
            \App\Filament\Resources\Experts\ExpertResource::class => \App\Models\Expert::first(),
            \App\Filament\Resources\Projects\ProjectResource::class => \App\Models\Project::first(),
            \App\Filament\Resources\Posts\PostResource::class => \App\Models\Post::first(),
            \App\Filament\Resources\Publications\PublicationResource::class => \App\Models\Publication::first(),
            \App\Filament\Resources\Partners\PartnerResource::class => \App\Models\Partner::first(),
            \App\Filament\Resources\Testimonials\TestimonialResource::class => \App\Models\Testimonial::first(),
            \App\Filament\Resources\Faqs\FaqResource::class => \App\Models\Faq::first(),
            \App\Filament\Resources\Stats\StatResource::class => \App\Models\Stat::first(),
            \App\Filament\Resources\Settings\SettingResource::class => \App\Models\Setting::first(),
        ];

        foreach ($records as $resource => $record) {
            $this->actingAs($user)
                ->get($resource::getUrl('edit', ['record' => $record]))
                ->assertOk("Edit page for {$resource} did not render.");
        }
    }
}
