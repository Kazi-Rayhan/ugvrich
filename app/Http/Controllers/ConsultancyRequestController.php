<?php

namespace App\Http\Controllers;

use App\Models\ConsultancyRequest;
use App\Models\ServiceCategory;
use App\Support\MeetingSlots;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ConsultancyRequestController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'organization' => ['nullable', Rule::in(ConsultancyRequest::ORGANIZATION_TYPES)],
            'email' => ['nullable', 'email:rfc', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'service_category_id' => ['nullable', Rule::exists(ServiceCategory::class, 'id')],
            'area_of_interest' => ['nullable', 'string', 'max:180'],

            // A preferred meeting time is optional, but a date and a slot only
            // make sense together, and the office is shut on Thursday and Friday.
            'preferred_date' => ['nullable', 'date', 'after_or_equal:today', 'required_with:preferred_slot', function ($attribute, $value, $fail) {
                if (filled($value) && ! MeetingSlots::isOpenOn(CarbonImmutable::parse($value))) {
                    $fail(__('site.consultancy.closed_day'));
                }
            }],
            'preferred_slot' => ['nullable', 'required_with:preferred_date', Rule::in(MeetingSlots::values()),
                function ($attribute, $value, $fail) use ($request) {
                    // Two people cannot hold the same half hour.
                    $date = $request->date('preferred_date')?->toDateString();

                    if ($date && filled($value) && ! MeetingSlots::isFree($date, $value)) {
                        $fail(__('site.consultancy.slot_taken'));
                    }
                }],
            'document' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,png,jpg,jpeg'],
            'website' => ['nullable', 'size:0'], // honeypot
        ], [
            'website.size' => 'Submission rejected.',
        ]);

        unset($data['website']);

        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')->store('consultancy-requests', 'public');
        }

        $consultancy = ConsultancyRequest::create($data);

        return redirect()
            ->route('consultancy.thanks')
            ->with('consultancy_request', [
                'reference' => 'RICH-'.str_pad((string) $consultancy->id, 5, '0', STR_PAD_LEFT),
                'name' => $consultancy->name,
                'email' => $consultancy->email,
                'area' => $consultancy->service_category_id
                    ? ServiceCategory::find($consultancy->service_category_id)?->name
                    : null,
                'preferred_date' => $consultancy->preferred_date?->translatedFormat('j F Y'),
                'preferred_slot' => MeetingSlots::label($consultancy->preferred_slot),
            ]);
    }

    public function thanks()
    {
        // Only reachable straight after a submission; otherwise send people to the form.
        if (! session()->has('consultancy_request')) {
            return redirect()->route('contact');
        }

        return view('pages.consultancy-thanks', [
            'submission' => session('consultancy_request'),
        ]);
    }
}
