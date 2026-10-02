<?php

namespace App\Http\Controllers;

use App\Models\ResearchProposal;
use App\Models\ResearchSupport;
use App\Support\ResearchTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The two things a researcher can send the Research Wing: a request to the
 * support desk, and a research proposal.
 *
 * Both land in the admin for triage. Neither pretends to be more than that —
 * there is no review workflow, no routing, no automatic assignment. A person
 * reads them.
 */
class ResearchRequestController extends Controller
{
    /* ------------------------------------------------------ support desk */

    public function supportCreate()
    {
        return view('pages.research.support', [
            'departments' => ResearchTaxonomy::departments(),
        ]);
    }

    public function supportStore(Request $request)
    {
        $types = __('research_hub.support.items');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'role' => ['nullable', 'string', 'max:80'],
            'department' => ['nullable', Rule::in(ResearchTaxonomy::departments())],

            'support_types' => ['required', 'array', 'min:1'],
            'support_types.*' => [Rule::in($types)],

            'title' => ['required', 'string', 'max:200'],
            'details' => ['required', 'string', 'min:20', 'max:5000'],
            'stage' => ['nullable', 'string', 'max:80'],
            'needed_by' => ['nullable', 'date', 'after_or_equal:today'],
            'document' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip'],
            'website' => ['nullable', 'size:0'],   // honeypot
        ], [
            'details.min' => __('research_hub.forms.errors.details'),
            'website.size' => __('research_hub.forms.errors.rejected'),
        ]);

        unset($data['website']);

        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('research-support', 'public');
        }

        $support = ResearchSupport::create($data);

        return redirect()->route('research.thanks')->with('research_request', [
            'kind' => 'support',
            'reference' => $support->reference(),
            'name' => $support->name,
            'title' => $support->title,
        ]);
    }

    /* --------------------------------------------------------- proposals */

    public function proposalCreate()
    {
        return view('pages.research.proposal', [
            'tree' => ResearchTaxonomy::tree(),
        ]);
    }

    public function proposalStore(Request $request)
    {
        $tree = collect(ResearchTaxonomy::tree());

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'institution' => ['nullable', 'string', 'max:200'],

            'department' => ['required', Rule::in($tree->pluck('department')->all())],
            'research_field' => ['required', 'string', 'max:200'],
            'research_area' => ['nullable', 'string', 'max:200'],
            'research_type' => ['required', Rule::in(array_keys(ResearchProposal::researchTypes()))],
            'researcher_type' => ['required', Rule::in(array_keys(ResearchProposal::researcherTypes()))],
            'researcher_type_other' => ['required_if:researcher_type,other', 'nullable', 'string', 'max:160'],
            'researcher_department' => ['required_if:researcher_type,student', 'nullable', 'string', 'max:160'],
            'designation' => ['required_if:researcher_type,professor,other', 'nullable', 'string', 'max:160'],

            'title' => ['required', 'string', 'max:220'],
            'background' => ['nullable', 'string', 'max:6000'],
            'objectives' => ['nullable', 'string', 'max:4000'],
            'methodology' => ['nullable', 'string', 'max:6000'],
            'research_gap' => ['nullable', 'string', 'max:4000'],
            'research_questions' => ['nullable', 'string', 'max:4000'],
            'timeline' => ['nullable', 'string', 'max:4000'],
            'expected_outcome' => ['nullable', 'string', 'max:4000'],
            'expected_impact' => ['nullable', 'string', 'max:4000'],
            'innovation_novelty' => ['nullable', 'string', 'max:4000'],
            'sdgs' => ['nullable', 'array'],
            'sdgs.*' => ['string', Rule::in(array_map(fn ($number) => 'SDG '.$number, array_keys(__('research_hub.sdg.goals'))))],
            'principal_investigator' => ['nullable', 'string', 'max:160'],
            'co_researchers' => ['nullable', 'array', 'max:30'],
            'co_researchers.*.name' => ['nullable', 'string', 'max:160'],
            'co_researchers.*.designation' => ['nullable', 'string', 'max:160'],
            'co_researchers.*.department' => ['nullable', 'string', 'max:160'],
            'external_collaborator' => ['nullable', 'string', 'max:200'],
            'external_department' => ['nullable', 'string', 'max:160'],
            'external_institution' => ['nullable', 'string', 'max:200'],
            'funding_required' => ['nullable', 'boolean'],
            'budget' => ['nullable', 'string', 'max:120'],
            'budget_breakdown' => ['nullable', 'string', 'max:4000'],
            'funding_source' => ['nullable', 'string', 'max:500'],
            'external_funding_applied' => ['nullable', 'boolean'],
            'human_participants' => ['nullable', 'boolean'],
            'sensitive_data' => ['nullable', 'boolean'],
            'ethical_approval_required' => ['nullable', 'boolean'],
            'informed_consent_required' => ['nullable', 'boolean'],
            'ai_used' => ['nullable', 'boolean'],
            'website' => ['nullable', 'size:0'],   // honeypot
        ], [
            'website.size' => __('research_hub.forms.errors.rejected'),
        ]);

        unset($data['website']);
        $data['summary'] = $data['background'] ?? '';
        if ($data['researcher_type'] === 'student') {
            $data['designation'] = null;
        } else {
            $data['researcher_department'] = null;
        }
        if ($data['researcher_type'] !== 'other') {
            $data['researcher_type_other'] = null;
        }
        $data['sdgs'] = array_values(array_unique($data['sdgs'] ?? []));
        $data['co_researchers'] = collect($data['co_researchers'] ?? [])
            ->map(fn (array $researcher) => array_filter($researcher, fn ($value) => filled($value)))
            ->filter()
            ->values()
            ->all();
        if ($data['research_type'] !== 'collaborative') {
            $data['principal_investigator'] = null;
            $data['co_researchers'] = [];
        }
        if (! in_array($data['research_type'], ['collaborative', 'interdisciplinary'], true)) {
            $data['external_collaborator'] = null;
            $data['external_department'] = null;
            $data['external_institution'] = null;
        }

        /* The field has to belong to the department that was chosen. Both are
           select boxes, but the second is filled in the browser, so the pairing
           is checked again here rather than trusted. */
        $department = $tree->firstWhere('department', $data['department']);
        $fields = collect($department['fields'] ?? [])->pluck('name');

        if (! $fields->contains($data['research_field'])) {
            return back()
                ->withInput()
                ->withErrors(['research_field' => __('research_hub.forms.errors.field_mismatch')]);
        }

        // Public proposals are intake items first, so they use the same
        // "new" status the support requests do until a person reads them.
        $proposal = ResearchProposal::create([...$data, 'status' => ResearchProposal::NEW, 'submitted_at' => now()]);

        return redirect()->route('research.thanks')->with('research_request', [
            'kind' => 'proposal',
            'reference' => $proposal->reference(),
            'name' => $proposal->name,
            'title' => $proposal->title,
        ]);
    }

    /* ------------------------------------------------------------ thanks */

    public function thanks(Request $request)
    {
        $submission = $request->session()->get('research_request');

        // Nothing to thank anybody for; send them back to the hub.
        if (! $submission) {
            return redirect()->route('research');
        }

        return view('pages.research.thanks', ['submission' => $submission]);
    }
}
