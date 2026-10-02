<?php

namespace App\Http\Controllers\Researcher;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * A researcher's own research projects.
 *
 * Every route here checks ownership on the record itself rather than trusting
 * the link that led to it. A project belonging to someone else answers 404,
 * not 403: the portal should not confirm that a project exists.
 *
 * Documents are held on the private disk and streamed through
 * {@see self::download()}, so a file is reachable only by somebody the check
 * lets through. There is no public URL to guess.
 */
class ProjectController extends Controller
{
    public function index(Request $request)
    {
        return view('researcher.projects.index', [
            'projects' => Project::query()
                ->where('user_id', $request->user()->id)
                ->with('researchProposal')
                ->withCount(['updates', 'documents'])
                ->latest()
                ->get(),
        ]);
    }

    public function show(Request $request, Project $project)
    {
        $this->authoriseOwner($request, $project);

        return view('researcher.projects.show', [
            'project' => $project->load([
                'researchProposal.fundings',
                'updates.author',
                'documents.uploader',
            ]),
        ]);
    }

    /**
     * Add an entry to the progress log.
     *
     * Append-only: this writes a new row and never touches an earlier one, so
     * the project's history stays the sequence it actually was. The researcher
     * may report progress; the project's status is the wing's to set.
     */
    public function storeUpdate(Request $request, Project $project)
    {
        $this->authoriseOwner($request, $project);

        $data = $request->validate([
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'comment' => ['required', 'string', 'min:10', 'max:4000'],
            'document' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,zip,png,jpg,jpeg'],
        ]);

        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store(
                'research-projects/'.$project->id,
                'local',
            );
        }

        $project->updates()->create([
            ...$data,
            'user_id' => $request->user()->id,
        ]);

        // The headline figure follows the latest report, but the status does not.
        if (isset($data['progress'])) {
            $project->forceFill(['progress' => $data['progress']])->save();
        }

        return redirect()
            ->route('researcher.projects.show', $project)
            ->with('saved', __('researcher.projects.update_saved'));
    }

    /**
     * Stream a project document.
     *
     * Two checks, not one: the document has to belong to this project, and the
     * project has to belong to this researcher. Without the first, a valid id
     * from another project would be served to anyone who owned any project.
     */
    public function download(Request $request, Project $project, ProjectDocument $document)
    {
        $this->authoriseOwner($request, $project);

        abort_unless($document->project_id === $project->id, 404);

        abort_unless(Storage::disk('local')->exists($document->file), 404);

        return Storage::disk('local')->download($document->file, basename($document->file));
    }

    /** The same for a file attached to a progress entry. */
    public function downloadUpdate(Request $request, Project $project, \App\Models\ProjectUpdate $update)
    {
        $this->authoriseOwner($request, $project);

        abort_unless($update->project_id === $project->id, 404);
        abort_unless($update->document && Storage::disk('local')->exists($update->document), 404);

        return Storage::disk('local')->download($update->document, basename($update->document));
    }

    /**
     * A project is the researcher's own, or it does not exist as far as the
     * portal is concerned.
     */
    protected function authoriseOwner(Request $request, Project $project): void
    {
        abort_unless($project->user_id && $project->user_id === $request->user()->id, 404);
    }
}
