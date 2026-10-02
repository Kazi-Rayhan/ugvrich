<?php

namespace Tests\Feature;

use App\Models\ResearchProposal;
use App\Models\ResearchSupport;
use App\Support\ResearchTaxonomy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The two things a researcher can actually send the Research Wing.
 *
 * Everything else on the Research page is a concept; these two write a row and
 * are read by a person, so they are worth holding down properly.
 */
class ResearchRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @return array{0: string, 1: string, 2: string} department, field, area */
    private function somewhereInTheFramework(): array
    {
        $tree = ResearchTaxonomy::tree();

        $this->assertNotEmpty($tree, 'the framework should name departments');

        return [$tree[0]['department'], $tree[0]['fields'][0]['name'], $tree[0]['areas'][0] ?? ''];
    }

    /* ------------------------------------------------------ support desk */

    public function test_the_support_form_renders_in_both_languages(): void
    {
        $this->get(route('research.support'))->assertOk();
        $this->get(route('en.research.support'))->assertOk()->assertSee(__('research_hub.forms.support.title', [], 'en'));
    }

    public function test_a_support_request_is_recorded(): void
    {
        $response = $this->post(route('research.support.store'), [
            'name' => 'Nusrat Jahan',
            'email' => 'nusrat@example.edu',
            'support_types' => [__('research_hub.support.items')[0], __('research_hub.support.items')[3]],
            'title' => 'Flood resilience of rural embankments',
            'details' => 'The sampling frame is settled but the analysis plan is not, and I would like a second reading of it.',
        ]);

        $support = ResearchSupport::firstOrFail();

        $this->assertSame('Nusrat Jahan', $support->name);
        $this->assertCount(2, $support->support_types);
        $this->assertSame('new', $support->status);

        $response->assertRedirect(route('research.thanks'));

        $this->followingRedirects()
            ->post(route('research.support.store'), [
                'name' => 'Nusrat Jahan',
                'email' => 'nusrat@example.edu',
                'support_types' => [__('research_hub.support.items')[0]],
                'title' => 'Second request',
                'details' => 'Another request long enough to pass the minimum length check on this field.',
            ])
            ->assertOk()
            ->assertSee(ResearchSupport::latest('id')->first()->reference());
    }

    public function test_a_support_request_must_name_what_is_needed(): void
    {
        $this->post(route('research.support.store'), [
            'name' => 'A Researcher',
            'email' => 'a@example.edu',
            'title' => 'Something',
            'details' => 'A description that is comfortably longer than the minimum length.',
        ])->assertSessionHasErrors('support_types');

        $this->assertSame(0, ResearchSupport::count());
    }

    public function test_a_support_request_keeps_its_attachment(): void
    {
        Storage::fake('public');

        $this->post(route('research.support.store'), [
            'name' => 'A Researcher',
            'email' => 'a@example.edu',
            'support_types' => [__('research_hub.support.items')[0]],
            'title' => 'With a document',
            'details' => 'A description that is comfortably longer than the minimum length.',
            'document' => UploadedFile::fake()->create('plan.pdf', 64, 'application/pdf'),
        ])->assertRedirect(route('research.thanks'));

        $stored = ResearchSupport::firstOrFail()->document;

        $this->assertNotNull($stored);
        Storage::disk('public')->assertExists($stored);
    }

    /* --------------------------------------------------------- proposals */

    public function test_the_proposal_form_offers_the_frameworks_own_departments(): void
    {
        $page = $this->get(route('en.research.proposal'))->assertOk();

        foreach (ResearchTaxonomy::departments() as $department) {
            $page->assertSee($department, escape: false);
        }
    }

    public function test_a_proposal_is_recorded(): void
    {
        [$department, $field, $area] = $this->somewhereInTheFramework();

        $this->post(route('research.proposal.store'), [
            'name' => 'Rafiul Karim',
            'email' => 'rafiul@example.edu',
            'department' => $department,
            'research_field' => $field,
            'research_area' => $area,
            'title' => 'Teacher feedback practices in first-year writing',
            'summary' => 'A mixed-methods study of how written feedback is given and received across two semesters of first-year writing.',
        ])->assertRedirect(route('research.thanks'));

        $proposal = ResearchProposal::firstOrFail();

        $this->assertSame($department, $proposal->department);
        $this->assertSame($field, $proposal->research_field);
        $this->assertSame('new', $proposal->status);
    }

    /**
     * The field select is filled in the browser from the chosen department, so
     * a mismatched pair can only arrive from a tampered-with form. It is
     * checked again on the server rather than trusted.
     */
    public function test_a_field_from_another_department_is_refused(): void
    {
        $tree = ResearchTaxonomy::tree();

        $this->assertGreaterThan(1, count($tree), 'need two departments for this test');

        $this->post(route('research.proposal.store'), [
            'name' => 'Rafiul Karim',
            'email' => 'rafiul@example.edu',
            'department' => $tree[0]['department'],
            'research_field' => $tree[1]['fields'][0]['name'],   // belongs to another department
            'title' => 'A proposal',
            'summary' => 'A summary that is comfortably longer than the minimum length required by the form.',
        ])->assertSessionHasErrors('research_field');

        $this->assertSame(0, ResearchProposal::count());
    }

    public function test_a_proposal_must_say_where_it_sits(): void
    {
        $this->post(route('research.proposal.store'), [
            'name' => 'Rafiul Karim',
            'email' => 'rafiul@example.edu',
            'title' => 'A proposal',
            'summary' => 'A summary that is comfortably longer than the minimum length required by the form.',
        ])->assertSessionHasErrors(['department', 'research_field']);
    }

    /* ------------------------------------------------------------ thanks */

    public function test_the_thanks_page_is_not_reachable_on_its_own(): void
    {
        $this->get(route('research.thanks'))->assertRedirect(route('research'));
    }

    public function test_the_research_page_links_to_both_forms(): void
    {
        $page = $this->get(route('en.research'))->assertOk();

        $page->assertSee(route('en.research.support'), escape: false);
        $page->assertSee(route('en.research.proposal'), escape: false);
    }
}
