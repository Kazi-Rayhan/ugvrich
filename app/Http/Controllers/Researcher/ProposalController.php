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
            'research_idea_id' => $idea?->id,
            'department' => $idea?->department,
            'research_field' => $idea?->research_field,
            'research_area' => $idea?->research_area,
            'title' => $idea?->title,
            'summary' => $idea?->description,
            'sdgs' => $idea?->sdgs,
        ]);

        return view('researcher.proposals.form', [
            'proposal' => $proposal,
            'idea' => $idea,
            'tree' => ResearchTaxonomy::tree(),
            'ideas' => $this->linkableIdeas($request),
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
            'ideas' => $this->linkableIdeas($request),
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
            'proposal' => $proposal->load('reviews.reviewer', 'idea'),
        ]);
    }

    /* ------------------------------------------------------------ helpers */

    /** The researcher's approved ideas, for the "from an idea" select. */
    protected function linkableIdeas(Request $request)
    {
        return ResearchIdea::ownedBy($request->user())
            ->whereIn('status', [ResearchIdea::APPROVED, ResearchIdea::PROPOSAL])
            ->get();
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request): array
    {
        $tree = collect(ResearchTaxonomy::tree());

        $data = $request->validate([
            'research_idea_id' => [
                'nullable',
                // Only this researcher's own approved ideas may be linked.
                Rule::exists('research_ideas', 'id')
                    ->where('user_id', $request->user()->id)
                    ->whereIn('status', [ResearchIdea::APPROVED, ResearchIdea::PROPOSAL]),
            ],

            'title' => ['required', 'string', 'max:220'],
            'department' => ['required', Rule::in($tree->pluck('department')->all())],
            'research_field' => ['required', 'string', 'max:200'],
            'research_area' => ['nullable', 'string', 'max:200'],

            'summary' => ['required', 'string', 'min:40', 'max:6000'],
            'background' => ['nullable', 'string', 'max:6000'],
            'research_gap' => ['nullable', 'string', 'max:4000'],
            'objectives' => ['nullable', 'string', 'max:4000'],
            'research_questions' => ['nullable', 'string', 'max:4000'],
            'hypothesis' => ['nullable', 'string', 'max:3000'],

            'methodology' => ['nullable', 'string', 'max:6000'],
            'study_design' => ['nullable', 'string', 'max:200'],
            'population' => ['nullable', 'string', 'max:3000'],
            'data_collection' => ['nullable', 'string', 'max:3000'],
            'data_analysis' => ['nullable', 'string', 'max:3000'],

            'expected_outcome' => ['nullable', 'string', 'max:4000'],
            'expected_impact' => ['nullable', 'string', 'max:4000'],
            'sdgs' => ['nullable', 'array'],
            'sdgs.*' => ['string', 'max:60'],

            'duration' => ['nullable', 'string', 'max:120'],
            'timeline' => ['nullable', 'string', 'max:4000'],
            'budget' => ['nullable', 'string', 'max:120'],
            'funding_needed' => ['nullable', 'string', 'max:120'],
            'funding_type' => ['nullable', Rule::in(array_keys(ResearchProposal::fundingTypes()))],
            'funding_organization' => ['nullable', 'string', 'max:180'],
            'research_team' => ['nullable', 'string', 'max:3000'],
            'principal_investigator' => ['nullable', 'string', 'max:160'],
            'collaborators_needed' => ['nullable', 'string', 'max:160'],

            'proposal_document' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,zip'],
            'document' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,zip'],
        ]);

        // The field has to belong to the department. Both are selects, but the
        // second is filled in the browser, so the pairing is checked again.
        $department = $tree->firstWhere('department', $data['department']);

        if (! collect($department['fields'] ?? [])->pluck('name')->contains($data['research_field'])) {
            abort(422, __('research_hub.forms.errors.field_mismatch'));
        }

        foreach (['proposal_document', 'document'] as $file) {
            if ($request->hasFile($file)) {
                $data[$file] = $request->file($file)->store('research-proposals', 'public');
            } else {
                unset($data[$file]);
            }
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
