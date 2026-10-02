<?php

namespace Tests\Feature;

use App\Support\ResearchFramework;
use Database\Seeders\RichContentSeeder;
use Database\Seeders\RichResearchSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Research hub explains the research framework and available pathways in
 * both site languages.
 */
class ResearchHubTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RichContentSeeder::class, RichResearchSeeder::class]);
    }

    public function test_the_hub_renders_in_both_languages(): void
    {
        $this->get(route('research'))->assertOk()->assertSee(__('research_hub.cta.title'));

        $this->get(route('en.research'))->assertOk();
    }

    public function test_every_section_is_present(): void
    {
        $page = $this->get(route('en.research'))->assertOk();

        foreach ([
            'research_hub.about.title',
            'research_hub.lifecycle.title',
            'research_hub.areas.title',
            'research_hub.support.title',
            'research_hub.sdg.title',
            'research_hub.ecosystem.title',
            'research_hub.cta.title',
        ] as $key) {
            $page->assertSee(__($key, [], 'en'), escape: false);
        }
    }

    public function test_unwanted_planned_and_counting_sections_are_not_rendered(): void
    {
        $page = $this->get(route('en.research'))->assertOk();

        foreach ([
            'research_hub.send.title',
            'research_hub.impact.title',
            'research_hub.future.title',
            'research_hub.offer.title',
            'research_hub.collaboration.title',
        ] as $key) {
            $page->assertDontSee(__($key, [], 'en'), escape: false);
            $page->assertDontSee(__($key, [], 'bn'), escape: false);
        }
    }

    /**
     * The SDG tally is read off the framework's own "SDG 4, 10" strings. The
     * Bangla document writes those in Bengali digits, which a \d pattern does
     * not match — so the section quietly vanished in Bangla while looking fine
     * in English. Both languages are checked.
     */
    public function test_the_sdg_section_appears_in_both_languages(): void
    {
        $this->get(route('research'))->assertOk()->assertSee(__('research_hub.sdg.title'));
        $this->get(route('en.research'))->assertOk()->assertSee(__('research_hub.sdg.title', [], 'en'));
    }

    public function test_the_research_areas_come_from_the_framework(): void
    {
        $areas = collect(ResearchFramework::all()['priority_areas'] ?? []);

        $this->assertNotEmpty($areas, 'the framework should name priority areas');

        $page = $this->get(route('en.research'))->assertOk();

        // Every department the framework names is offered as a tab.
        foreach ($areas->groupBy(0)->keys() as $department) {
            $page->assertSee($department, escape: false);
        }
    }

    /** The planning document keeps a page of its own, linked from the hub. */
    public function test_the_framework_document_is_still_reachable(): void
    {
        $this->get(route('en.research'))
            ->assertOk()
            ->assertSee(route('en.research.framework'), escape: false);

        $this->get(route('research.framework'))->assertOk();
        $this->get(route('en.research.framework'))->assertOk();
    }

}
