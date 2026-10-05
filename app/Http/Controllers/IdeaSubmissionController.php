<?php

namespace App\Http\Controllers;

use App\Models\IdeaSubmission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Ideas submitted through the Startup & Incubation page. They enter the
 * journey at its first stage and are triaged from the admin panel.
 */
class IdeaSubmissionController extends Controller
{
    public function create()
    {
        return view('pages.submit-idea');
    }

    public function store(Request $request)
    {
        // Two steps on the form: register (who, how to reach them, a working
        // title), then the idea itself as one uploaded file.
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],          // a person or a team
            'email' => ['required', 'email:rfc', 'max:180'],
            'phone' => ['required', 'string', 'max:40'],
            'title' => ['required', 'string', 'max:200'],
            'document' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,ppt,pptx,zip,png,jpg,jpeg'],
            'role' => ['nullable', Rule::in(array_keys(config('rich.idea_roles')))],
            'website' => ['nullable', 'size:0'], // honeypot
        ], [
            'document.required' => __('site.ideas.file_required'),
            'website.size' => 'Submission rejected.',
        ]);

        unset($data['website']);

        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('idea-submissions', 'public');
        }

        $idea = IdeaSubmission::create($data);

        return redirect()
            ->route('ideas.thanks')
            ->with('idea_submission', [
                'reference' => 'IDEA-'.str_pad((string) $idea->id, 5, '0', STR_PAD_LEFT),
                'name' => $idea->name,
                'title' => $idea->title,
            ]);
    }

    public function thanks()
    {
        if (! session()->has('idea_submission')) {
            return redirect()->route('startup');
        }

        return view('pages.idea-thanks', [
            'submission' => session('idea_submission'),
        ]);
    }
}
