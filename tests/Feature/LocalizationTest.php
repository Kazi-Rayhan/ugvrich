<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Support\Site;
use Database\Seeders\BanglaContentSeeder;
use Database\Seeders\RichContentSeeder;
use Database\Seeders\RichInnovationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Bangla is the site's own language and answers at the root; English is the
 * same site one segment deeper, under /en.
 */
class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RichContentSeeder::class, RichInnovationSeeder::class, BanglaContentSeeder::class]);
    }

    public function test_the_root_is_bangla_and_the_en_prefix_is_english(): void
    {
        $this->get('/')->assertOk()->assertSee('প্রভাব ও উদ্যোগে রূপান্তর', false);
        $this->get('/en')->assertOk()->assertSee('Transforming Knowledge', false);
    }

    public function test_seeded_records_read_in_the_language_of_the_page(): void
    {
        $this->get('/projects')->assertOk()->assertSee('সান কার', false);
        $this->get('/en/projects')->assertOk()->assertSee('SUN CAR', false);
    }

    public function test_a_field_without_a_bangla_value_falls_back_to_english(): void
    {
        Setting::query()->where('key', 'site_tagline')->first()->update(['value_bn' => null]);
        Site::flush();

        $this->get('/')->assertOk()->assertSee('Research, Innovation', false);
    }

    public function test_the_language_switch_points_at_the_same_page(): void
    {
        $this->get('/about')->assertOk()->assertSee('/en/about', false);
        $this->get('/en/about')->assertOk()->assertSee('hreflang="bn"', false);
    }

    public function test_every_public_page_renders_in_both_languages(): void
    {
        // Every page the Bangla site answers with, taken from the routes themselves.
        $paths = collect(Route::getRoutes()->getRoutesByMethod()['GET'] ?? [])
            ->filter(fn (RoutingRoute $route) => filled($route->getName()))
            ->filter(fn (RoutingRoute $route) => ! str_starts_with((string) $route->getName(), 'en.'))
            ->filter(fn (RoutingRoute $route) => ! str_starts_with((string) $route->getName(), 'filament.'))
            ->filter(fn (RoutingRoute $route) => ! str_contains($route->uri(), '{'))
            // Routes that only forward somewhere else answer with a redirect.
            ->filter(fn (RoutingRoute $route) => ! str_contains($route->getActionName(), 'RedirectController'))
            // The thank-you pages send a visitor without a submission back to the form.
            ->filter(fn (RoutingRoute $route) => ! str_contains((string) $route->getName(), 'thanks'))
            ->map(fn (RoutingRoute $route) => '/'.ltrim($route->uri(), '/'))
            ->unique();

        $this->assertGreaterThan(10, $paths->count());

        foreach ($paths as $path) {
            $this->get($path)->assertOk();
            $this->get(rtrim('/en'.$path, '/'))->assertOk();
        }
    }
}
