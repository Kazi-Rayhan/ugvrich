<?php

namespace Tests\Feature;

use App\Support\ResearchFramework;
use Database\Seeders\RichContentSeeder;
use Database\Seeders\RichResearchSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The Research hub: a vision page, not a research management system.
 *
 * What is worth holding down here is the honesty of it — that the figures come
 * from the database rather than from the page, that a figure with no source
 * says so instead of showing a number, and that the sections built from the
 * framework document survive in both languages.
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
            'research_hub.offer.title',
            'research_hub.areas.title',
            'research_hub.collaboration.title',
            'research_hub.support.title',
            'research_hub.impact.title',
            'research_hub.sdg.title',
            'research_hub.ecosystem.title',
            'research_hub.future.title',
            'research_hub.cta.title',
        ] as $key) {
            $page->assertSee(__($key, [], 'en'), escape: false);
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

    /** Counts are taken from the database, not written into the page. */
    public function test_the_impact_figures_are_the_real_counts(): void
    {
        $projects = \App\Models\Project::where('type', 'research')->count();

        $this->assertGreaterThan(0, $projects, 'the seeder should create research projects');

        $this->get(route('en.research'))
            ->assertOk()
            ->assertSeeInOrder([(string) $projects, __('research_hub.impact.metrics.research_projects', [], 'en')], escape: false);
    }

    /** A figure with no source says so, rather than showing a nought. */
    public function test_a_figure_with_no_source_says_coming_soon(): void
    {
        $this->get(route('en.research'))
            ->assertOk()
            ->assertSee(__('research_hub.impact.soon', [], 'en'));
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

    /** Nothing on the page claims a capability that is not built. */
    public function test_future_capabilities_are_labelled_as_such(): void
    {
        $this->get(route('en.research'))
            ->assertOk()
            ->assertSee(__('research_hub.future.status.planned', [], 'en'));
    }
}
