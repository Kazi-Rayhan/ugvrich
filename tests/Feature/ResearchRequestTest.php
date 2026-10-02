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

        $page->assertSee(__('research_hub.forms.proposal.researcher_information'))
            ->assertSee(__('research_hub.forms.proposal.research_team'))
            ->assertSee(__('research_hub.forms.proposal.category'))
            ->assertSee(__('research_hub.forms.proposal.background'))
            ->assertSee(__('research_hub.forms.proposal.funding_required'))
            ->assertSee('x-show="fundingRequired === \'1\'"', escape: false)
            ->assertSee(__('research_hub.forms.proposal.estimated_budget'))
            ->assertSee(__('research_hub.forms.proposal.ethical_information'))
            ->assertDontSee('name="summary"', escape: false)
            ->assertDontSee('name="document"', escape: false)
            ->assertDontSee('name="role"', escape: false);
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
            'research_type' => 'collaborative',
            'researcher_type' => 'professor',
            'designation' => 'Lecturer',
            'institution' => 'University of Example',
            'title' => 'Teacher feedback practices in first-year writing',
            'background' => 'Written feedback is central to first-year writing courses, but practices vary widely.',
            'research_gap' => 'Few local studies compare student and faculty feedback priorities.',
            'expected_outcome' => 'A tested model for improving feedback practices.',
            'innovation_novelty' => 'The study combines classroom data with student interviews.',
            'sdgs' => ['SDG 4'],
            'co_researchers' => [
                ['name' => 'Ayesha Rahman', 'designation' => 'Research Assistant', 'department' => 'CSE'],
                ['name' => '', 'designation' => '', 'department' => ''],
            ],
            'external_collaborator' => 'Example Institute',
            'external_department' => 'Education',
            'external_institution' => 'Example Institute',
            'funding_required' => '1',
            'budget' => '125000',
            'budget_breakdown' => 'Travel: 50,000; materials: 75,000',
            'funding_source' => 'University seed grant',
            'external_funding_applied' => '0',
            'human_participants' => '1',
            'sensitive_data' => '0',
            'ethical_approval_required' => '1',
            'informed_consent_required' => '1',
            'ai_used' => '0',
        ])->assertRedirect(route('research.thanks'));

        $proposal = ResearchProposal::firstOrFail();

        $this->assertSame($department, $proposal->department);
        $this->assertSame($field, $proposal->research_field);
        $this->assertSame('new', $proposal->status);
        $this->assertSame('collaborative', $proposal->research_type);
        $this->assertSame('professor', $proposal->researcher_type);
        $this->assertSame('Lecturer', $proposal->designation);
        $this->assertNull($proposal->researcher_department);
        $this->assertSame('Written feedback is central to first-year writing courses, but practices vary widely.', $proposal->background);
        $this->assertSame($proposal->background, $proposal->summary);
        $this->assertSame(['SDG 4'], $proposal->sdgs);
        $this->assertSame([[
            'name' => 'Ayesha Rahman',
            'designation' => 'Research Assistant',
            'department' => 'CSE',
        ]], $proposal->co_researchers);
        $this->assertTrue($proposal->funding_required);
        $this->assertTrue($proposal->ethical_approval_required);
        $this->assertFalse($proposal->ai_used);
    }

    public function test_student_public_proposal_saves_department_and_omits_designation(): void
    {
        [$department, $field] = $this->somewhereInTheFramework();

        $this->post(route('research.proposal.store'), [
            'name' => 'Nusrat Jahan',
            'email' => 'nusrat@example.edu',
            'department' => $department,
            'research_field' => $field,
            'research_type' => 'individual',
            'researcher_type' => 'student',
            'researcher_department' => 'Economics',
            'designation' => 'Should be omitted',
            'title' => 'Student research proposal',
        ])->assertRedirect(route('research.thanks'));

        $proposal = ResearchProposal::firstOrFail();

        $this->assertSame('student', $proposal->researcher_type);
        $this->assertSame('Economics', $proposal->researcher_department);
        $this->assertNull($proposal->designation);
    }

    public function test_non_collaborative_public_proposals_do_not_save_team_details(): void
    {
        [$department, $field] = $this->somewhereInTheFramework();

        $this->post(route('research.proposal.store'), [
            'name' => 'Rafiul Karim',
            'email' => 'rafiul@example.edu',
            'department' => $department,
            'research_field' => $field,
            'research_type' => 'individual',
            'researcher_type' => 'other',
            'researcher_type_other' => 'Independent consultant',
            'designation' => 'Researcher',
            'title' => 'An individual research proposal',
            'principal_investigator' => 'Should be omitted',
            'co_researchers' => [
                ['name' => 'Should be omitted', 'designation' => 'Assistant Professor', 'department' => 'Education'],
            ],
            'external_collaborator' => 'Should be omitted',
            'external_department' => 'Should be omitted',
            'external_institution' => 'Should be omitted',
        ])->assertRedirect(route('research.thanks'));

        $proposal = ResearchProposal::firstOrFail();

        $this->assertNull($proposal->principal_investigator);
        $this->assertSame('Independent consultant', $proposal->researcher_type_other);
        $this->assertSame([], $proposal->co_researchers);
        $this->assertNull($proposal->external_collaborator);
        $this->assertNull($proposal->external_department);
        $this->assertNull($proposal->external_institution);
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
            'research_type' => 'individual',
            'researcher_type' => 'student',
            'researcher_department' => 'Economics',
            'title' => 'A proposal',
        ])->assertSessionHasErrors('research_field');

        $this->assertSame(0, ResearchProposal::count());
    }

    public function test_a_proposal_must_say_where_it_sits(): void
    {
        $this->post(route('research.proposal.store'), [
            'name' => 'Rafiul Karim',
            'email' => 'rafiul@example.edu',
            'research_type' => 'individual',
            'title' => 'A proposal',
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
