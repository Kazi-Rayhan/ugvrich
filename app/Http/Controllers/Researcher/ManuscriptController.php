<?php

namespace App\Http\Controllers\Researcher;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ResearchManuscript;
use App\Notifications\ResearchEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Manuscripts, from draft to submission.
 *
 * The same rules as ideas and proposals: a draft or a returned revision is the
 * researcher's to change, anything with the reviewers is not, and a manuscript
 * belonging to somebody else does not exist as far as this controller is
 * concerned.
 *
 * Files go to the private disk and are streamed through an authorised route.
 * A manuscript under review is not a public document.
 */
class ManuscriptController extends Controller
{
    public function index(Request $request)
    {
        return view('researcher.manuscripts.index', [
            'manuscripts' => ResearchManuscript::ownedBy($request->user())
                ->withCount('reviews')
                ->with('project')
                ->latest()
                ->get(),
        ]);
    }

    public function create(Request $request)
    {
        return view('researcher.manuscripts.form', [
            'manuscript' => new ResearchManuscript(['status' => ResearchManuscript::DRAFT]),
            'projects' => $this->ownProjects($request),
        ]);
    }

    public function store(Request $request)
    {
        $manuscript = ResearchManuscript::create([
            ...$this->validated($request),
            'user_id' => $request->user()->id,
            'status' => ResearchManuscript::DRAFT,
        ]);

        return $this->finish($request, $manuscript);
    }

    public function edit(Request $request, ResearchManuscript $manuscript)
    {
        $this->authoriseOwner($request, $manuscript);

        abort_unless($manuscript->isEditable(), 403);

        return view('researcher.manuscripts.form', [
            'manuscript' => $manuscript,
            'projects' => $this->ownProjects($request),
        ]);
    }

    public function update(Request $request, ResearchManuscript $manuscript)
    {
        $this->authoriseOwner($request, $manuscript);

        abort_unless($manuscript->isEditable(), 403);

        $manuscript->update($this->validated($request));

        return $this->finish($request, $manuscript);
    }

    public function show(Request $request, ResearchManuscript $manuscript)
    {
        $this->authoriseOwner($request, $manuscript);

        return view('researcher.manuscripts.show', [
            'manuscript' => $manuscript->load('reviews.reviewer', 'project'),
        ]);
    }

    /** Stream a manuscript file after checking who is asking. */
    public function download(Request $request, ResearchManuscript $manuscript, string $file)
    {
        $this->authoriseOwner($request, $manuscript);

        abort_unless(in_array($file, ['manuscript_file', 'supplementary_file', 'similarity_report'], true), 404);

        $path = $manuscript->{$file};

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, basename($path));
    }

    /* ------------------------------------------------------------ helpers */

    /** Only the researcher's own projects may be attached to a manuscript. */
    protected function ownProjects(Request $request)
    {
        return Project::where('user_id', $request->user()->id)->orderBy('title')->get();
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'project_id' => [
                'nullable',
                // A manuscript may only point at a project this researcher owns.
                Rule::exists('projects', 'id')->where('user_id', $request->user()->id),
            ],

            'title' => ['required', 'string', 'max:260'],
            'abstract' => ['required', 'string', 'min:60', 'max:6000'],
            'keywords' => ['nullable', 'array', 'max:12'],
            'keywords.*' => ['string', 'max:60'],
            'manuscript_type' => ['nullable', Rule::in(array_keys(ResearchManuscript::types()))],
            'department' => ['nullable', 'string', 'max:120'],
            'research_field' => ['nullable', 'string', 'max:200'],

            'primary_author' => ['nullable', 'string', 'max:180'],
            'co_authors' => ['nullable', 'string', 'max:2000'],
            'corresponding_author' => ['nullable', 'string', 'max:180'],
            'affiliation' => ['nullable', 'string', 'max:200'],
            'orcid' => ['nullable', 'string', 'max:80'],

            'target_journal' => ['nullable', 'string', 'max:200'],
            'publisher' => ['nullable', 'string', 'max:200'],
            'issn' => ['nullable', 'string', 'max:40'],
            'journal_url' => ['nullable', 'url', 'max:255'],
            'quartile' => ['nullable', 'string', 'max:20'],
            'impact_factor' => ['nullable', 'string', 'max:20'],
            'journal_scope' => ['nullable', 'string', 'max:2000'],

            'funding_source' => ['nullable', 'string', 'max:200'],
            'ethics_approval' => ['nullable', 'string', 'max:200'],
            'conflict_of_interest' => ['nullable', 'string', 'max:2000'],
            'ai_use_declaration' => ['nullable', 'string', 'max:2000'],

            'references_list' => ['nullable', 'string', 'max:20000'],
            'citation_style' => ['nullable', Rule::in(array_keys(ResearchManuscript::citationStyles()))],

            'doi' => ['nullable', 'string', 'max:120'],
            'published_on' => ['nullable', 'date'],
            'volume' => ['nullable', 'string', 'max:40'],
            'issue' => ['nullable', 'string', 'max:40'],
            'pages' => ['nullable', 'string', 'max:40'],
            'publication_url' => ['nullable', 'url', 'max:255'],

            'notes' => ['nullable', 'string', 'max:3000'],

            'manuscript_file' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx'],
            'supplementary_file' => ['nullable', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,zip'],
            'similarity_report' => ['nullable', 'file', 'max:20480', 'mimes:pdf'],
        ]);

        // Private disk: a manuscript under review has no business being on a
        // publicly reachable path.
        foreach (['manuscript_file', 'supplementary_file', 'similarity_report'] as $file) {
            if ($request->hasFile($file)) {
                $data[$file] = $request->file($file)->store('research-manuscripts/'.$request->user()->id, 'local');
            } else {
                unset($data[$file]);
            }
        }

        return $data;
    }

    /** Keep it as a draft, or send it for internal review. */
    protected function finish(Request $request, ResearchManuscript $manuscript)
    {
        if ($request->input('action') === 'submit') {
            $manuscript->update([
                'status' => ResearchManuscript::SUBMITTED,
                'submitted_at' => now(),
            ]);

            // Submission is the researcher's own doing, so it is recorded on
            // the dashboard but never emailed back to them.
            $request->user()->notify(new ResearchEvent(
                'manuscript.submitted',
                $manuscript->title,
                route('researcher.manuscripts.show', $manuscript),
            ));

            return redirect()
                ->route('researcher.manuscripts.show', $manuscript)
                ->with('saved', __('researcher.manuscripts.submitted'));
        }

        return redirect()
            ->route('researcher.manuscripts.show', $manuscript)
            ->with('saved', __('researcher.manuscripts.draft_saved'));
    }

    protected function authoriseOwner(Request $request, ResearchManuscript $manuscript): void
    {
        abort_unless($manuscript->user_id === $request->user()->id, 404);
    }
}
