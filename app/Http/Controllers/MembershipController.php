<?php

namespace App\Http\Controllers;

use App\Models\StudentMembership;
use App\Support\Vocabulary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Student membership applications. One computer can send one application,
 * then has to wait ten minutes before sending another.
 */
class MembershipController extends Controller
{
    public function create()
    {
        return view('pages.membership', [
            'departments' => collect(Vocabulary::all('departments'))->except('RICH'),
            'tracks' => Vocabulary::all('internship_tracks'),
        ]);
    }

    public function store(Request $request)
    {
        $this->guardComputer($request);

        $normalized = $this->normalizePhone($request->input('phone'));

        if ($normalized) {
            $request->merge(['phone' => $normalized]);
        }

        if ($request->filled('email')) {
            $request->merge(['email' => Str::lower(Str::squish($request->input('email')))]);
        }

        $departments = array_keys(array_diff_key(config('rich.departments'), ['RICH' => true]));

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'student_id' => ['required', 'string', 'max:40', 'regex:/^[A-Za-z0-9][A-Za-z0-9\-\/]*$/', Rule::unique(StudentMembership::class, 'student_id')],
            'semester' => ['required', 'integer', 'between:1,8'],
            'department' => ['required', Rule::in($departments)],
            'phone' => ['required', 'regex:/^01[3-9]\d{8}$/', Rule::unique(StudentMembership::class, 'phone')],
            'email' => ['required', 'string', 'email', 'max:150', Rule::unique(StudentMembership::class, 'email')],
            'track' => ['required', Rule::in(array_keys(config('rich.internship_tracks')))],
            'website' => ['nullable', 'size:0'],
        ], [
            'student_id.unique' => __('site.membership.id_taken'),
            'student_id.regex' => __('site.membership.id_invalid'),
            'phone.regex' => __('site.membership.phone_invalid'),
            'phone.unique' => __('site.membership.phone_taken'),
            'email.email' => __('site.membership.email_invalid'),
            'email.unique' => __('site.membership.email_taken'),
            'track.required' => __('site.membership.track_required'),
            'website.size' => 'Submission rejected.',
        ]);

        unset($data['website']);

        $data['name'] = Str::squish($data['name']);
        $data['student_id'] = Str::squish($data['student_id']);

        $application = StudentMembership::create($data);

        RateLimiter::hit($this->computerKey($request), 10 * 60);

        return redirect()
            ->route('membership.thanks')
            ->with('membership_application', [
                'reference' => $application->reference,
                'name' => $application->name,
                'track' => $application->track,
            ]);
    }

    public function thanks()
    {
        if (! session()->has('membership_application')) {
            return redirect()->route('membership.create');
        }

        return view('pages.membership-thanks', [
            'application' => session('membership_application'),
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
            'form' => __('site.membership.rate_limited', ['minutes' => $minutes]),
        ]);
    }

    protected function computerKey(Request $request): string
    {
        return 'membership-submit:'.$request->ip();
    }

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
