<?php

namespace App\Http\Controllers;

use App\Models\IdeaSubmission;
use App\Models\User;
use App\Notifications\SetInnovatorPassword;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * Ideas submitted through the Startup & Incubation page. They enter the
 * journey at its first stage and are triaged from the admin panel.
 */
class IdeaSubmissionController extends Controller
{
    public function create(Request $request)
    {
        // A signed-in innovator has registered already: their details are filled in.
        $innovator = $request->user()?->isInnovator() ? $request->user() : null;

        return view('pages.submit-idea', [
            'innovator' => $innovator,
            'innovatorPhone' => $innovator?->ideaSubmissions()->value('phone'),
        ]);
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
            'category' => ['required', Rule::in(array_keys(config('rich.idea_categories')))],
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

        [$user, $isNew] = $this->innovatorFor($request, $data);

        $idea = IdeaSubmission::create($data + ['user_id' => $user?->id]);

        // A new account gets the link to choose its password. Mail trouble is
        // reported, never allowed to lose the idea that was just submitted.
        $linkSent = false;
        if ($isNew) {
            try {
                $user->notify(new SetInnovatorPassword(Password::broker('innovators')->createToken($user), $idea->title));
                $linkSent = true;
            } catch (Throwable $e) {
                report($e);
            }

            // The account was made by this very submission, so its owner is the
            // person here: sign them in, and the thank-you page can open their
            // dashboard straight away. (An existing account is never signed in
            // this way — knowing an email must not open somebody's dashboard.)
            Auth::login($user);
            $request->session()->regenerate();
        }

        return redirect()
            ->route('ideas.thanks')
            ->with('idea_submission', [
                'reference' => $idea->reference,
                'name' => $idea->name,
                'title' => $idea->title,
                'email' => $idea->email,
                'account' => match (true) {
                    $linkSent => 'new',
                    $user !== null => 'existing',
                    default => null,
                },
            ]);
    }

    /**
     * The innovator account an idea belongs to, and whether it was just made.
     *
     * A signed-in innovator submits as themselves. Otherwise the email decides:
     * an innovator already registered under it gets the idea; a new address
     * becomes a new innovator account with a random password, replaced from
     * the emailed link. An address that belongs to staff or a researcher is
     * left alone, and the idea is kept without an account.
     *
     * @return array{0: ?User, 1: bool}
     */
    protected function innovatorFor(Request $request, array $data): array
    {
        if ($request->user()?->isInnovator()) {
            return [$request->user(), false];
        }

        // One account per address, whatever the capitals: compared and stored lowercase.
        $email = Str::lower($data['email']);

        $existing = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($existing) {
            return [$existing->isInnovator() ? $existing : null, false];
        }

        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $email,
                'password' => Str::random(40),
                'role' => User::INNOVATOR,
            ]);
        } catch (UniqueConstraintViolationException) {
            // A second submit raced this one (a double click) and made the
            // account first: use that account, never a second one.
            $existing = User::whereRaw('LOWER(email) = ?', [$email])->first();

            return [$existing?->isInnovator() ? $existing : null, false];
        }

        return [$user, true];
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
