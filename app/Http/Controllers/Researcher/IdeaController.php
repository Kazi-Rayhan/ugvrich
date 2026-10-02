<?php

namespace App\Http\Controllers\Researcher;

use App\Http\Controllers\Controller;
use App\Models\ResearchIdea;
use App\Support\ResearchTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Research ideas: the first thing a researcher submits.
 *
 * An idea can be kept as a draft and worked on, submitted for review, and —
 * if a reviewer asks for changes — edited and submitted again. Once it is with
 * the reviewers it is read-only, so what they are reading cannot move under
 * them.
 */
class IdeaController extends Controller
{
    public function index(Request $request)
    {
        return view('researcher.ideas.index', [
            'ideas' => ResearchIdea::ownedBy($request->user())->withCount('reviews')->latest()->get(),
        ]);
    }

    public function create(Request $request)
    {
        return view('researcher.ideas.form', [
            'idea' => new ResearchIdea(['status' => ResearchIdea::DRAFT]),
            'tree' => ResearchTaxonomy::tree(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $idea = ResearchIdea::create([
            ...$data,
            'user_id' => $request->user()->id,
            'status' => ResearchIdea::DRAFT,
        ]);

        return $this->finish($request, $idea);
    }

    public function edit(Request $request, ResearchIdea $idea)
    {
        $this->authoriseOwner($request, $idea);

        abort_unless($idea->isEditable(), 403);

        return view('researcher.ideas.form', [
            'idea' => $idea,
            'tree' => ResearchTaxonomy::tree(),
        ]);
    }

    public function update(Request $request, ResearchIdea $idea)
    {
        $this->authoriseOwner($request, $idea);

        abort_unless($idea->isEditable(), 403);

        $idea->update($this->validated($request));

        return $this->finish($request, $idea);
    }

    public function show(Request $request, ResearchIdea $idea)
    {
        $this->authoriseOwner($request, $idea);

        return view('researcher.ideas.show', [
            'idea' => $idea->load('reviews.reviewer'),
        ]);
    }

    /* ------------------------------------------------------------ helpers */

    /** @return array<string, mixed> */
    protected function validated(Request $request): array
    {
        $tree = collect(ResearchTaxonomy::tree());

        $data = $request->validate([
            'title' => ['required', 'string', 'max:220'],
            'department' => ['required', Rule::in($tree->pluck('department')->all())],
            'research_field' => ['required', 'string', 'max:200'],
            'research_area' => ['nullable', 'string', 'max:200'],

            'problem' => ['required', 'string', 'min:30', 'max:4000'],
            'description' => ['required', 'string', 'min:30', 'max:6000'],
            'motivation' => ['nullable', 'string', 'max:4000'],
            'expected_contribution' => ['nullable', 'string', 'max:4000'],

            'sdgs' => ['nullable', 'array'],
            'sdgs.*' => ['string', 'max:60'],
            'keywords' => ['nullable', 'array', 'max:12'],
            'keywords.*' => ['string', 'max:60'],

            'proposed_team' => ['nullable', 'string', 'max:2000'],
            'collaboration_requirement' => ['nullable', 'string', 'max:160'],
            'document' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,zip'],
        ]);

        // The field has to belong to the department; the select is filled in
        // the browser, so the pairing is checked again rather than trusted.
        $department = $tree->firstWhere('department', $data['department']);

        if (! collect($department['fields'] ?? [])->pluck('name')->contains($data['research_field'])) {
            abort(422, __('researcher.ideas.field_mismatch'));
        }

        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('research-ideas', 'public');
        } else {
            unset($data['document']);
        }

        return $data;
    }

    /** Save as a draft, or send it for review, depending on the button used. */
    protected function finish(Request $request, ResearchIdea $idea)
    {
        if ($request->input('action') === 'submit') {
            $idea->update([
                'status' => ResearchIdea::SUBMITTED,
                'submitted_at' => now(),
            ]);

            return redirect()
                ->route('researcher.ideas.show', $idea)
                ->with('saved', __('researcher.ideas.submitted'));
        }

        return redirect()
            ->route('researcher.ideas.show', $idea)
            ->with('saved', __('researcher.ideas.draft_saved'));
    }

    protected function authoriseOwner(Request $request, ResearchIdea $idea): void
    {
        abort_unless($idea->user_id === $request->user()->id, 404);
    }
}
