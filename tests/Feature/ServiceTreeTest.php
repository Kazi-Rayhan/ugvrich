<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\ServiceCategory;
use Database\Seeders\RichContentSeeder;
use Database\Seeders\RichServiceDepartmentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The services tree has three levels: a sector runs a main service, and a main
 * service holds the services people actually ask for.
 */
class ServiceTreeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RichContentSeeder::class, RichServiceDepartmentSeeder::class]);
    }

    private function firstService(): Service
    {
        return Service::active()->with('category')->firstOrFail();
    }

    public function test_every_service_has_a_page_under_its_main_service(): void
    {
        foreach (Service::active()->with('category')->get() as $service) {
            $this->get(route('services.detail', [$service->category, $service]))
                ->assertOk()
                ->assertSee($service->name);
        }
    }

    public function test_the_main_service_page_links_to_each_service_beneath_it(): void
    {
        $category = ServiceCategory::active()->with('services')->firstOrFail();

        $page = $this->get(route('services.show', $category))->assertOk();

        foreach ($category->services as $service) {
            $page->assertSee(route('services.detail', [$category, $service]), escape: false);
        }
    }

    /**
     * The binding is scoped, so a service cannot be reached under a main service
     * it does not belong to — which would otherwise be a second address for the
     * same page, and a wrong one.
     */
    public function test_a_service_cannot_be_reached_under_the_wrong_parent(): void
    {
        $service = $this->firstService();

        $other = ServiceCategory::active()->whereKeyNot($service->service_category_id)->firstOrFail();

        $this->get("/services/{$other->slug}/{$service->slug}")->assertNotFound();
    }

    public function test_an_unknown_service_is_a_404(): void
    {
        $category = ServiceCategory::active()->firstOrFail();

        $this->get("/services/{$category->slug}/there-is-no-such-service")->assertNotFound();
    }

    public function test_a_hidden_service_is_not_reachable(): void
    {
        $service = $this->firstService();

        $service->update(['is_active' => false]);

        $this->get(route('services.detail', [$service->category, $service]))->assertNotFound();
    }

    public function test_each_main_service_names_the_sector_that_runs_it(): void
    {
        // Set from the departments of the services beneath it, by migration.
        foreach (ServiceCategory::active()->get() as $category) {
            $this->assertNotNull($category->department, "{$category->name} has no sector");

            $this->get(route('services.show', $category))
                ->assertOk()
                ->assertSee($category->sector_name);
        }
    }

    public function test_a_service_page_shows_the_detail_when_it_has_been_written(): void
    {
        $service = $this->firstService();

        $service->update([
            'body' => "First paragraph of the overview.\n\nSecond paragraph of the overview.",
            'highlights' => ['A written report', 'Two revision rounds'],
        ]);

        $this->get(route('services.detail', [$service->category, $service]))
            ->assertOk()
            ->assertSee('First paragraph of the overview.')
            ->assertSee('Second paragraph of the overview.')
            ->assertSee('A written report')
            ->assertSee('Two revision rounds');
    }

    public function test_a_service_page_stands_up_before_any_detail_is_written(): void
    {
        $service = $this->firstService();

        $service->update(['body' => null, 'highlights' => null]);

        $this->get(route('services.detail', [$service->category, $service]))
            ->assertOk()
            ->assertSee($service->name)
            ->assertDontSee(__('site.services.overview'));
    }

    public function test_both_languages_serve_the_service_page(): void
    {
        $service = $this->firstService();

        $path = "/services/{$service->category->slug}/{$service->slug}";

        $this->get($path)->assertOk();
        $this->get("/en{$path}")->assertOk();
    }
}
