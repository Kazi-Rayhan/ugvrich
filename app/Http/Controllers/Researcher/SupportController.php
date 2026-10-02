<?php

namespace App\Http\Controllers\Researcher;

use App\Http\Controllers\Controller;
use App\Models\ResearchSupport;
use App\Support\ResearchTaxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * The support desk, seen from inside the portal.
 *
 * The same `research_supports` table the public form writes to — a researcher
 * should not have two separate histories depending on which door they came
 * through. What the portal adds is the account: the request is stamped with
 * `user_id`, so it can be listed back, followed, and answered in one place.
 *
 * There is no workflow here. A person at the Research Wing reads the request
 * and writes a reply into it; that reply is what the researcher sees. Requests
 * sent from the public form have no `user_id` and so are not visible here.
 */
class SupportController extends Controller
{
    public function index(Request $request)
    {
        return view('researcher.support.index', [
            'requests' => ResearchSupport::ownedBy($request->user())->latest()->get(),
            'types' => $this->types(),
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $profile = $user->researcherProfile;

        /* The account already knows most of the "who are you" part, so it is
           filled in rather than asked for again. */
        return view('researcher.support.form', [
            'support' => new ResearchSupport([
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $profile?->phone,
                'role' => $profile?->designation ?: $profile?->profession,
                'department' => $profile?->department,
            ]),
            'types' => $this->types(),
            'stages' => $this->stages(),
            'departments' => ResearchTaxonomy::departments(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'role' => ['nullable', 'string', 'max:80'],
            'department' => ['nullable', Rule::in(ResearchTaxonomy::departments())],

            'support_types' => ['required', 'array', 'min:1'],
            'support_types.*' => [Rule::in(array_keys($this->types()))],

            'title' => ['required', 'string', 'max:200'],
            'details' => ['required', 'string', 'min:20', 'max:5000'],
            'stage' => ['nullable', Rule::in(array_keys($this->stages()))],
            'needed_by' => ['nullable', 'date', 'after_or_equal:today'],
            'document' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip'],
        ], [
            'details.min' => __('research_hub.forms.errors.details'),
        ]);

        /* On the private disk, like every other file in the portal: a draft
           chapter sent to the support desk is not a public document. It is
           read back through download() below, after the ownership check. */
        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('research-support/portal', 'local');
        }

        $support = ResearchSupport::create([
            ...$data,
            'user_id' => $request->user()->id,
            'status' => 'new',
        ]);

        return redirect()
            ->route('researcher.support.show', $support)
            ->with('saved', __('researcher.support.sent', ['reference' => $support->reference()]));
    }

    public function show(Request $request, ResearchSupport $support)
    {
        $this->authoriseOwner($request, $support);

        return view('researcher.support.show', [
            'support' => $support,
            'types' => $this->types(),
            'stages' => $this->stages(),
        ]);
    }

    public function download(Request $request, ResearchSupport $support)
    {
        $this->authoriseOwner($request, $support);

        abort_unless(
            $support->document && Storage::disk('local')->exists($support->document),
            404
        );

        return Storage::disk('local')->download($support->document, basename($support->document));
    }

    /* ------------------------------------------------------------ helpers */

    /**
     * A request belonging to somebody else does not exist, and nor does one
     * sent from the public form: that one has no account behind it.
     */
    protected function authoriseOwner(Request $request, ResearchSupport $support): void
    {
        abort_unless($support->user_id === $request->user()->id, 404);
    }

    /**
     * The kinds of help, stored in English and shown in the reader's language.
     *
     * The public form stores whichever language the visitor was reading, which
     * leaves the admin with two vocabularies for one list. The portal stores one
     * and translates on the way out instead.
     *
     * @return array<string, string>  stored value => label
     */
    protected function types(): array
    {
        return $this->pairs('research_hub.support.items');
    }

    /** @return array<string, string> */
    protected function stages(): array
    {
        return $this->pairs('research_hub.forms.support.stages');
    }

    /** @return array<string, string> */
    protected function pairs(string $key): array
    {
        $values = (array) trans($key, [], 'en');
        $labels = (array) __($key);

        return collect($values)
            ->mapWithKeys(fn (string $value, int $i) => [$value => $labels[$i] ?? $value])
            ->all();
    }
}
