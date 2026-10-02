<?php

namespace App\Http\Controllers\Researcher;

use App\Http\Controllers\Controller;
use App\Models\ResearchIdea;
use App\Models\ResearchProposal;
use App\Support\ResearchTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Research proposals, written in the portal.
 *
 * A proposal normally begins life as an approved idea: starting one from an
 * idea carries the department, field, area, title and SDGs across, so the
 * researcher is not asked again for what they have already told us.
 *
 * The same rule as ideas: a draft or a returned revision is the researcher's
 * to change, anything under review is not.
 */
class ProposalController extends Controller
{
    public function index(Request $request)
    {
        return view('researcher.proposals.index', [
            'proposals' => ResearchProposal::ownedBy($request->user())
                ->withCount('reviews')
                ->latest()
                ->get(),

            // Approved ideas with no proposal yet: the obvious next step.
            'readyIdeas' => ResearchIdea::ownedBy($request->user())
                ->whereIn('status', [ResearchIdea::APPROVED, ResearchIdea::PROPOSAL])
                ->whereDoesntHave('proposal')
                ->get(),
        ]);
    }

    public function create(Request $request)
    {
        $profile = $request->user()->profile();
        $idea = null;

        // Carried forward from an approved idea, if one was named.
        if ($request->filled('idea')) {
            $idea = ResearchIdea::ownedBy($request->user())
                ->whereKey($request->integer('idea'))
                ->firstOrFail();

            abort_unless($idea->isApproved(), 403, __('researcher.proposals.idea_not_approved'));
        }

        $proposal = new ResearchProposal([
            'status' => ResearchProposal::DRAFT,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'phone' => $profile->phone,
            'designation' => $profile->designation,
            'institution' => $profile->organization,
            'researcher_department' => $profile->department,
            'researcher_type' => null,
            'research_idea_id' => $idea?->id,
            'department' => $idea?->department
                ?? (in_array($profile->department, ResearchTaxonomy::departments(), true) ? $profile->department : null),
            'research_field' => $idea?->research_field,
            'research_area' => $idea?->research_area,
            'title' => $idea?->title,
            'background' => $idea?->description,
            'summary' => $idea?->description ?? '',
            'sdgs' => $idea?->sdgs,
        ]);

        return view('researcher.proposals.form', [
            'proposal' => $proposal,
            'idea' => $idea,
            'tree' => ResearchTaxonomy::tree(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $proposal = ResearchProposal::create([
            ...$data,
            'user_id' => $request->user()->id,
            'name' => $request->user()->name,
            'email' => $request->user()->email,
            'status' => ResearchProposal::DRAFT,
        ]);

        return $this->finish($request, $proposal);
    }

    public function edit(Request $request, ResearchProposal $proposal)
    {
        $this->authoriseOwner($request, $proposal);

        abort_unless($proposal->isEditable(), 403);

        return view('researcher.proposals.form', [
            'proposal' => $proposal,
            'idea' => $proposal->idea,
            'tree' => ResearchTaxonomy::tree(),
        ]);
    }

    public function update(Request $request, ResearchProposal $proposal)
    {
        $this->authoriseOwner($request, $proposal);

        abort_unless($proposal->isEditable(), 403);

        $proposal->update($this->validated($request));

        return $this->finish($request, $proposal);
    }

    public function show(Request $request, ResearchProposal $proposal)
    {
        $this->authoriseOwner($request, $proposal);

        return view('researcher.proposals.show', [
            'proposal' => $proposal->load('reviews.reviewer'),
        ]);
    }

    /* ------------------------------------------------------------ helpers */

    /** @return array<string, mixed> */
    protected function validated(Request $request): array
    {
        $tree = collect(ResearchTaxonomy::tree());
        $submitting = $request->input('action') === 'submit';

        $data = $request->validate([
            'research_idea_id' => [
                'nullable',
                // Only this researcher's own approved ideas may be linked.
                Rule::exists('research_ideas', 'id')
                    ->where('user_id', $request->user()->id)
                    ->whereIn('status', [ResearchIdea::APPROVED, ResearchIdea::PROPOSAL]),
            ],

            'title' => [$submitting ? 'required' : 'nullable', 'string', 'max:220'],
            'phone' => ['nullable', 'string', 'max:40'],
            'institution' => ['nullable', 'string', 'max:200'],
            'researcher_type' => [$submitting ? 'required' : 'nullable', Rule::in(array_keys(ResearchProposal::researcherTypes()))],
            'researcher_type_other' => [
                Rule::requiredIf($submitting && $request->input('researcher_type') === 'other'),
                'nullable',
                'string',
                'max:160',
            ],
            'researcher_department' => [
                Rule::requiredIf($submitting && $request->input('researcher_type') === 'student'),
                'nullable',
                'string',
                'max:160',
            ],
            'designation' => [
                Rule::requiredIf($submitting && in_array($request->input('researcher_type'), ['professor', 'other'], true)),
                'nullable',
                'string',
                'max:160',
            ],
            'department' => [$submitting ? 'required' : 'nullable', Rule::in($tree->pluck('department')->all())],
            'research_field' => [$submitting ? 'required' : 'nullable', 'string', 'max:200'],
            'research_area' => ['nullable', 'string', 'max:200'],
            'research_type' => [$submitting ? 'required' : 'nullable', Rule::in(array_keys(ResearchProposal::researchTypes()))],

            'background' => ['nullable', 'string', 'max:6000'],
            'research_gap' => ['nullable', 'string', 'max:4000'],
            'objectives' => ['nullable', 'string', 'max:4000'],
            'research_questions' => ['nullable', 'string', 'max:4000'],
            'methodology' => ['nullable', 'string', 'max:6000'],
            'expected_outcome' => ['nullable', 'string', 'max:4000'],
            'expected_impact' => ['nullable', 'string', 'max:4000'],
            'innovation_novelty' => ['nullable', 'string', 'max:4000'],
            'timeline' => ['nullable', 'string', 'max:4000'],
            'sdgs' => ['nullable', 'array'],
            'sdgs.*' => ['string', Rule::in(array_map(fn ($number) => 'SDG '.$number, array_keys(__('research_hub.sdg.goals'))))],

            'budget' => ['nullable', 'string', 'max:120'],
            'funding_required' => ['nullable', 'boolean'],
            'budget_breakdown' => ['nullable', 'string', 'max:4000'],
            'funding_source' => ['nullable', 'string', 'max:500'],
            'external_funding_applied' => ['nullable', 'boolean'],
            'principal_investigator' => ['nullable', 'string', 'max:160'],
            'co_researchers' => ['nullable', 'array', 'max:30'],
            'co_researchers.*.name' => ['nullable', 'string', 'max:160'],
            'co_researchers.*.designation' => ['nullable', 'string', 'max:160'],
            'co_researchers.*.department' => ['nullable', 'string', 'max:160'],
            'external_collaborator' => ['nullable', 'string', 'max:200'],
            'external_department' => ['nullable', 'string', 'max:160'],
            'external_institution' => ['nullable', 'string', 'max:200'],
            'human_participants' => ['nullable', 'boolean'],
            'sensitive_data' => ['nullable', 'boolean'],
            'ethical_approval_required' => ['nullable', 'boolean'],
            'informed_consent_required' => ['nullable', 'boolean'],
            'ai_used' => ['nullable', 'boolean'],

        ]);

        $data['title'] = $data['title'] ?? '';
        $data['department'] = $data['department'] ?? '';
        $data['research_field'] = $data['research_field'] ?? '';
        $data['summary'] = $data['background'] ?? '';
        if (($data['researcher_type'] ?? null) === 'student') {
            $data['designation'] = null;
        } else {
            $data['researcher_department'] = null;
        }
        if (($data['researcher_type'] ?? null) !== 'other') {
            $data['researcher_type_other'] = null;
        }
        $data['sdgs'] = array_values(array_unique($data['sdgs'] ?? []));
        $data['co_researchers'] = collect($data['co_researchers'] ?? [])
            ->map(fn (array $researcher) => array_filter($researcher, fn ($value) => filled($value)))
            ->filter()
            ->values()
            ->all();
        if (($data['research_type'] ?? null) !== 'collaborative') {
            $data['principal_investigator'] = null;
            $data['co_researchers'] = [];
        }
        if (! in_array($data['research_type'] ?? null, ['collaborative', 'interdisciplinary'], true)) {
            $data['external_collaborator'] = null;
            $data['external_department'] = null;
            $data['external_institution'] = null;
        }

        // The field has to belong to the department. Both are selects, but the
        // second is filled in the browser, so the pairing is checked again.
        $department = $tree->firstWhere('department', $data['department'] ?? null);

        if (filled($data['research_field'] ?? null)
            && ! collect($department['fields'] ?? [])->pluck('name')->contains($data['research_field'])) {
            abort(422, __('research_hub.forms.errors.field_mismatch'));
        }

        return $data;
    }

    /** Keep it as a draft, or send it for review. */
    protected function finish(Request $request, ResearchProposal $proposal)
    {
        if ($request->input('action') === 'submit') {
            $proposal->update([
                'status' => ResearchProposal::SUBMITTED,
                'submitted_at' => now(),
            ]);

            return redirect()
                ->route('researcher.proposals.show', $proposal)
                ->with('saved', __('researcher.proposals.submitted'));
        }

        return redirect()
            ->route('researcher.proposals.show', $proposal)
            ->with('saved', __('researcher.proposals.draft_saved'));
    }

    protected function authoriseOwner(Request $request, ResearchProposal $proposal): void
    {
        // A proposal from the public form has no owner and belongs to nobody
        // here; a 404 rather than a 403, so the portal gives nothing away.
        abort_unless($proposal->user_id === $request->user()->id, 404);
    }
}
