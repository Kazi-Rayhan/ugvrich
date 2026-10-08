<?php

namespace App\Http\Controllers;

use App\Models\InternshipApplication;
use App\Support\Vocabulary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Internship applications: a three-step public form (about you, academic,
 * the internship), stored for the RICH office to handle from the admin
 * panel. One computer can send one application, then waits ten minutes.
 */
class InternshipController extends Controller
{
    public function create()
    {
        return view('pages.internship', [
            'departments' => collect(Vocabulary::all('departments'))->except('RICH'),
            'tracks' => Vocabulary::all('internship_tracks'),
            'years' => Vocabulary::all('internship_years'),
            'durations' => Vocabulary::all('internship_durations'),
            'modes' => Vocabulary::all('internship_modes'),
        ]);
    }

    public function store(Request $request)
    {
        $this->guardComputer($request);

        if ($phone = $this->normalizePhone($request->input('phone'))) {
            $request->merge(['phone' => $phone]);
        }

        if ($request->filled('email')) {
            $request->merge(['email' => Str::lower(Str::squish($request->input('email')))]);
        }

        $data = $request->validate([
            // About you
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:180'],
            'phone' => ['required', 'regex:/^01[3-9]\d{8}$/'],

            // Academic
            'university' => ['required', 'string', 'max:180'],
            'department' => ['required', 'string', 'max:150'],
            'programme' => ['required', 'string', 'max:150'],
            'year_level' => ['required', Rule::in(array_keys(config('rich.internship_years')))],
            'student_id' => ['nullable', 'string', 'max:40'],
            'cgpa' => ['nullable', 'numeric', 'between:0,4'],

            // The internship
            'track' => ['required', Rule::in(array_keys(config('rich.internship_tracks')))],
            'duration_months' => ['required', Rule::in(array_keys(config('rich.internship_durations')))],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'mode' => ['required', Rule::in(array_keys(config('rich.internship_modes')))],
            'skills' => ['nullable', 'string', 'max:2000'],
            'motivation' => ['required', 'string', 'min:30', 'max:3000'],
            'cv' => ['required', 'file', 'max:5120', 'mimes:pdf,doc,docx'],
            'portfolio_url' => ['nullable', 'url', 'max:300'],

            'website' => ['nullable', 'size:0'], // honeypot
        ], [
            'phone.regex' => __('site.internship.phone_invalid'),
            'motivation.min' => __('site.internship.motivation_short'),
            'cv.required' => __('site.internship.cv_required'),
            'website.size' => 'Submission rejected.',
        ]);

        unset($data['website']);

        $data['name'] = Str::squish($data['name']);
        $data['cv'] = $request->file('cv')->store('internship-applications', 'public');

        $application = InternshipApplication::create($data);

        RateLimiter::hit($this->computerKey($request), 10 * 60);

        return redirect()
            ->route('internship.thanks')
            ->with('internship_application', [
                'reference' => $application->reference,
                'name' => $application->name,
                'track' => $application->track_label,
                'email' => $application->email,
            ]);
    }

    public function thanks()
    {
        if (! session()->has('internship_application')) {
            return redirect()->route('internship.create');
        }

        return view('pages.internship-thanks', [
            'application' => session('internship_application'),
        ]);
    }

    /** A second application from the same computer has to wait out the window. */
    protected function guardComputer(Request $request): void
    {
        $key = $this->computerKey($request);

        if (! RateLimiter::tooManyAttempts($key, 1)) {
            return;
        }

        $minutes = (int) max(1, ceil(RateLimiter::availableIn($key) / 60));

        throw ValidationException::withMessages([
            'form' => __('site.internship.rate_limited', ['minutes' => $minutes]),
        ]);
    }

    protected function computerKey(Request $request): string
    {
        return 'internship-submit:'.$request->ip();
    }

    /** 01XXXXXXXXX, however it was typed (+880, spaces, dashes). */
    protected function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone) ?? '';

        if (str_starts_with($digits, '880')) {
            $digits = substr($digits, 3);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '1')) {
            $digits = '0'.$digits;
        }

        return preg_match('/^01[3-9]\d{8}$/', $digits) ? $digits : null;
    }
}
