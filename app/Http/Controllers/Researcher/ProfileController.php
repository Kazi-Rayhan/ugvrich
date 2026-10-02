<?php

namespace App\Http\Controllers\Researcher;

use App\Http\Controllers\Controller;
use App\Support\ResearchTaxonomy;
use Illuminate\Http\Request;

/** The researcher's own academic record. */
class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $profile = $request->user()->profile();

        return view('researcher.profile', [
            'profile' => $profile,
            'completion' => $profile->completion(),
            'checklist' => $profile->checklist(),
            'departments' => ResearchTaxonomy::departments(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],

            'photo' => ['nullable', 'image', 'max:4096'],
            'gender' => ['nullable', 'string', 'max:40'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'nationality' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', 'string', 'max:80'],
            'researcher_scope' => ['nullable', 'string', 'max:40'],
            'profession' => ['nullable', 'string', 'max:120'],
            'designation' => ['nullable', 'string', 'max:120'],
            'organization' => ['nullable', 'string', 'max:180'],

            'department' => ['nullable', 'string', 'max:120'],
            'faculty' => ['nullable', 'string', 'max:120'],
            'highest_degree' => ['nullable', 'string', 'max:120'],
            'qualifications' => ['nullable', 'string', 'max:3000'],
            'research_experience' => ['nullable', 'string', 'max:80'],
            'research_interests' => ['nullable', 'array'],
            'research_interests.*' => ['string', 'max:120'],
            'research_fields' => ['nullable', 'array'],
            'research_fields.*' => ['string', 'max:120'],
            'expertise' => ['nullable', 'array'],
            'expertise.*' => ['string', 'max:120'],

            'phone' => ['nullable', 'string', 'max:40'],
            'alternative_phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],

            'orcid' => ['nullable', 'string', 'max:80'],
            'google_scholar' => ['nullable', 'url', 'max:255'],
            'scopus' => ['nullable', 'string', 'max:255'],
            'researchgate' => ['nullable', 'url', 'max:255'],
            'linkedin' => ['nullable', 'url', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],

            'biography' => ['nullable', 'string', 'max:5000'],
            'research_profile' => ['nullable', 'string', 'max:3000'],
            'languages' => ['nullable', 'array'],
            'languages.*' => ['string', 'max:60'],
            'awards' => ['nullable', 'string', 'max:3000'],
            'publications' => ['nullable', 'string', 'max:5000'],
        ]);

        // The display name lives on the account, not on the profile.
        $user->update(['name' => $data['name']]);
        unset($data['name']);

        /* An empty tag box posts nothing at all, and a key that is not in the
           request never reaches the update — so without this, removing every
           research interest left the old ones in place. */
        foreach (['research_interests', 'research_fields', 'expertise', 'languages'] as $list) {
            $data[$list] = array_values(array_filter(
                $data[$list] ?? [],
                fn ($value) => filled($value),
            ));
        }

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('researchers', 'public');
        } else {
            unset($data['photo']);
        }

        $user->profile()->update($data);

        return redirect()
            ->route('researcher.profile')
            ->with('saved', __('researcher.profile.saved'));
    }
}
