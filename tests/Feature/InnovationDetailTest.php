<?php

namespace Tests\Feature;

use App\Support\InnovationFramework;
use Database\Seeders\InnovationRecordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every innovation has a page of its own, in either phase.
 *
 * The Innovation page gives each a card with one paragraph on it; the rest of
 * the write-up lives on these pages.
 */
class InnovationDetailTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_proposal_has_a_page_before_anything_is_seeded(): void
    {
        // Straight from the document, which is what a fresh install serves.
        foreach (InnovationFramework::all()['proposed'] as $item) {
            $this->get(route('innovation.show', $item['slug']))
                ->assertOk()
                ->assertSee($item['name'])
                ->assertSee($item['how'])
                ->assertSee($item['why']);
        }
    }

    public function test_every_proposal_has_a_page_once_the_records_exist(): void
    {
        $this->seed(InnovationRecordSeeder::class);

        foreach (InnovationFramework::all()['proposed'] as $item) {
            $this->get(route('innovation.show', $item['slug']))->assertOk()->assertSee($item['name']);
        }
    }

    public function test_the_innovation_page_links_to_each_of_them(): void
    {
        $this->seed(InnovationRecordSeeder::class);
        $this->listUnderAnArea();

        $page = $this->get(route('innovation.index'))->assertOk();

        foreach (InnovationFramework::all()['proposed'] as $item) {
            $page->assertSee(route('innovation.show', $item['slug']), escape: false);
        }
    }

    public function test_an_unknown_innovation_is_a_404(): void
    {
        $this->get('/innovation/there-is-no-such-thing')->assertNotFound();
    }

    /**
     * The catch-all sits after these two in the route file. Were it registered
     * first it would swallow both, so this is worth holding down.
     */
    public function test_the_areas_and_plan_addresses_are_not_swallowed(): void
    {
        $this->get('/innovation/areas')->assertOk();
        $this->get('/innovation/plan')->assertRedirect('/innovation');
    }

    public function test_both_languages_use_the_same_address(): void
    {
        $this->seed(InnovationRecordSeeder::class);

        // The slug comes from the English name in either language, so a link
        // shared from the Bangla site opens the same page on the English one.
        $this->get('/innovation/solar-scooty')->assertOk()->assertSee('সোলার স্কুটি');
        $this->get('/en/innovation/solar-scooty')->assertOk()->assertSee('Solar Scooty');
    }

    public function test_every_current_innovation_has_a_page(): void
    {
        $doc = InnovationFramework::all();

        foreach ($doc['current_slugs'] as $i => $slug) {
            [$name, $lead, $highlights] = $doc['current'][$i];

            $this->get(route('innovation.show', $slug))
                ->assertOk()
                ->assertSee($name)
                ->assertSee($lead);
        }
    }

    public function test_the_innovation_page_links_to_each_current_innovation(): void
    {
        $this->seed(InnovationRecordSeeder::class);
        $this->listUnderAnArea();

        $page = $this->get(route('innovation.index'))->assertOk();

        foreach (InnovationFramework::all()['current_slugs'] as $slug) {
            $page->assertSee(route('innovation.show', $slug), escape: false);
        }
    }

    /**
     * A current innovation has no concept / how / why and no SDG line, so the
     * page must not reach for them.
     */
    public function test_a_current_innovation_page_shows_what_it_has(): void
    {
        $this->seed(InnovationRecordSeeder::class);

        $doc = InnovationFramework::all();

        $this->get(route('innovation.show', $doc['current_slugs'][0]))
            ->assertOk()
            ->assertSee($doc['headings']['lead_support'])
            ->assertDontSee($doc['headings']['sdg']);
    }

    /**
     * The Innovation page lists innovations under their areas, as cards, so a
     * test that looks for them there first gives them an area.
     */
    protected function listUnderAnArea(): void
    {
        $area = \App\Models\InnovationArea::create(['name' => 'Mechanical & Mobility Innovation', 'slug' => 'mechanical-mobility', 'is_active' => true]);
        \App\Models\Innovation::query()->update(['innovation_area_id' => $area->id]);
    }
}
