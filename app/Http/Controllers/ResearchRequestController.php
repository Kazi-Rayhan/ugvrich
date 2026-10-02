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
            'role' => ['nullable', 'string', 'max:80'],

            'department' => ['required', Rule::in($tree->pluck('department')->all())],
            'research_field' => ['required', 'string', 'max:200'],
            'research_area' => ['nullable', 'string', 'max:200'],

            'title' => ['required', 'string', 'max:220'],
            'summary' => ['required', 'string', 'min:40', 'max:5000'],
            'objectives' => ['nullable', 'string', 'max:3000'],
            'methodology' => ['nullable', 'string', 'max:3000'],
            'duration' => ['nullable', 'string', 'max:80'],
            'collaborators_needed' => ['nullable', 'string', 'max:120'],
            'funding_needed' => ['nullable', 'string', 'max:120'],
            'sdgs' => ['nullable', 'array'],
            'sdgs.*' => ['string', 'max:60'],
            'document' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,zip'],
            'website' => ['nullable', 'size:0'],   // honeypot
        ], [
            'summary.min' => __('research_hub.forms.errors.summary'),
            'website.size' => __('research_hub.forms.errors.rejected'),
        ]);

        unset($data['website']);

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

        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('research-proposals', 'public');
        }

        // Explicit rather than relying on the column default, so a proposal
        // from the public form carries the same status vocabulary as one
        // written in the portal.
        $proposal = ResearchProposal::create([...$data, 'status' => ResearchProposal::SUBMITTED, 'submitted_at' => now()]);

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
