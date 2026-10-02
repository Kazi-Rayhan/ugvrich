<?php

namespace Tests\Feature;

use App\Models\ResearchProposal;
use App\Models\User;
use App\Support\ResearchTaxonomy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResearcherProposalIntakeTest extends TestCase
{
    use RefreshDatabase;

    public function test_researcher_can_save_the_expanded_proposal_as_a_draft(): void
    {
        $user = User::factory()->create([
            'name' => 'Ayesha Rahman',
            'email' => 'ayesha@example.edu',
            'role' => User::RESEARCHER,
        ]);
        $user->profile()->update([
            'phone' => '+880 1700 000000',
            'designation' => 'Lecturer',
            'organization' => 'Example University',
            'department' => 'Computer Science',
        ]);

        [$department, $field] = $this->somewhereInTheFramework();

        $this->actingAs($user)->post(route('researcher.proposals.store'), [
            'department' => $department,
            'research_field' => $field,
            'research_type' => 'collaborative',
            'researcher_type' => 'professor',
            'phone' => '+880 1700 000000',
            'designation' => 'Lecturer',
            'institution' => 'Example University',
            'title' => 'Responsible use of learning analytics',
            'background' => 'A mixed-methods study of responsible learning analytics in higher education.',
            'principal_investigator' => 'Ayesha Rahman',
            'co_researchers' => [
                ['name' => 'Nabil Hasan', 'designation' => 'Assistant Professor', 'department' => 'Education'],
            ],
            'sdgs' => ['SDG 4', 'SDG 9'],
            'funding_required' => '1',
            'budget' => '200000',
            'budget_breakdown' => 'Data collection: 120000; analysis: 80000',
            'external_funding_applied' => '0',
            'human_participants' => '1',
            'sensitive_data' => '0',
            'ethical_approval_required' => '1',
            'informed_consent_required' => '1',
            'ai_used' => '0',
            'action' => 'draft',
        ])->assertRedirect();

        $proposal = ResearchProposal::firstOrFail();

        $this->assertSame($user->id, $proposal->user_id);
        $this->assertSame($user->name, $proposal->name);
        $this->assertSame($user->email, $proposal->email);
        $this->assertSame('collaborative', $proposal->research_type);
        $this->assertSame($proposal->background, $proposal->summary);
        $this->assertSame($department, $proposal->department);
        $this->assertSame('professor', $proposal->researcher_type);
        $this->assertNull($proposal->researcher_type_other);
        $this->assertNull($proposal->researcher_department);
        $this->assertSame('Lecturer', $proposal->designation);
        $this->assertSame('Example University', $proposal->institution);
        $this->assertSame('+880 1700 000000', $proposal->phone);
        $this->assertSame(['SDG 4', 'SDG 9'], $proposal->sdgs);
        $this->assertSame('Nabil Hasan', $proposal->co_researchers[0]['name']);
        $this->assertTrue($proposal->funding_required);
        $this->assertFalse($proposal->external_funding_applied);
        $this->assertTrue($proposal->ethical_approval_required);
        $this->assertFalse($proposal->ai_used);
        $this->assertSame(ResearchProposal::DRAFT, $proposal->status);
    }

    public function test_researcher_proposal_form_prefills_identity_from_profile(): void
    {
        $user = User::factory()->create([
            'name' => 'Ayesha Rahman',
            'email' => 'ayesha@example.edu',
            'role' => User::RESEARCHER,
        ]);
        $department = ResearchTaxonomy::departments()[0];
        $user->profile()->update([
            'phone' => '+880 1700 000000',
            'designation' => 'Lecturer',
            'organization' => 'Example University',
            'department' => $department,
        ]);

        $this->actingAs($user)
            ->get(route('researcher.proposals.create'))
            ->assertOk()
            ->assertSeeInOrder([
                __('research_hub.forms.proposal.researcher_information'),
                __('research_hub.forms.proposal.category'),
                __('research_hub.forms.proposal.research_team'),
                __('researcher.proposals.form.proposal'),
            ])
            ->assertSee('data-step="0" x-show="step === 0" x-cloak', escape: false)
            ->assertSee('data-step="2" x-show="step === 2"', escape: false)
            ->assertSee('x-show="step === 1 && teamVisible"', escape: false)
            ->assertSee('updateResearchType($event.detail)', escape: false)
            ->assertSee('proposal-research-type-change', escape: false)
            ->assertSee('value="+880 1700 000000"', escape: false)
            ->assertSee('value="Lecturer"', escape: false)
            ->assertSee('value="Example University"', escape: false)
            ->assertSee($department, escape: false)
            ->assertSee('name="researcher_type_other"', escape: false)
            ->assertSee('researcherType === \'other\'', escape: false)
            ->assertSee('x-show="fundingRequired === \'1\'"', escape: false)
            ->assertDontSee('name="summary"', escape: false)
            ->assertDontSee('name="proposal_document"', escape: false)
            ->assertDontSee('name="research_team"', escape: false);
    }

    public function test_researcher_can_save_an_incomplete_draft(): void
    {
        $user = User::factory()->create(['role' => User::RESEARCHER]);

        $this->actingAs($user)
            ->post(route('researcher.proposals.store'), ['action' => 'draft'])
            ->assertRedirect();

        $proposal = ResearchProposal::firstOrFail();

        $this->assertSame('', $proposal->title);
        $this->assertSame('', $proposal->summary);
        $this->assertSame('', $proposal->department);
        $this->assertSame('', $proposal->research_field);
        $this->assertSame(ResearchProposal::DRAFT, $proposal->status);
    }

    public function test_student_proposal_saves_department_and_clears_designation(): void
    {
        $user = User::factory()->create(['role' => User::RESEARCHER]);
        [$department, $field] = $this->somewhereInTheFramework();

        $this->actingAs($user)->post(route('researcher.proposals.store'), [
            'department' => $department,
            'research_field' => $field,
            'research_type' => 'individual',
            'researcher_type' => 'student',
            'researcher_department' => 'Economics',
            'designation' => 'Should be omitted',
            'title' => 'Student research proposal',
            'action' => 'submit',
        ])->assertRedirect();

        $proposal = ResearchProposal::firstOrFail();

        $this->assertSame('student', $proposal->researcher_type);
        $this->assertSame('Economics', $proposal->researcher_department);
        $this->assertNull($proposal->designation);
        $this->assertSame(ResearchProposal::SUBMITTED, $proposal->status);
    }

    public function test_other_researcher_type_is_saved_and_displayed(): void
    {
        $user = User::factory()->create(['role' => User::RESEARCHER]);
        [$department, $field] = $this->somewhereInTheFramework();

        $this->actingAs($user)->post(route('researcher.proposals.store'), [
            'department' => $department,
            'research_field' => $field,
            'research_type' => 'individual',
            'researcher_type' => 'other',
            'researcher_type_other' => 'Independent consultant',
            'designation' => 'Consultant',
            'title' => 'Consultant research proposal',
            'action' => 'submit',
        ])->assertRedirect();

        $proposal = ResearchProposal::firstOrFail();
        $this->assertSame('Independent consultant', $proposal->researcher_type_other);
        $this->assertSame('Consultant', $proposal->designation);

        $this->actingAs($user)
            ->get(route('researcher.proposals.show', $proposal))
            ->assertOk()
            ->assertSee('Independent consultant');
    }

    /** @return array{string, string} */
    private function somewhereInTheFramework(): array
    {
        $department = ResearchTaxonomy::tree()[0];

        return [$department['department'], $department['fields'][0]['name']];
    }
}
