<?php

namespace Tests\Feature;

use App\Support\InnovationFramework;
use Database\Seeders\InnovationRecordSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Each proposed innovation has a page of its own.
 *
 * The Innovation page lists the six two to a row with the concept only; the
 * rest of the write-up lives on these pages.
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
}
