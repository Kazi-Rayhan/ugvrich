@php
    $signedDate = $application->signed_at?->copy()->timezone('Asia/Dhaka')->locale(app()->getLocale())->isoFormat('LL');
    $assignedDate = $application->engagement_assigned_at?->copy()->timezone('Asia/Dhaka')->locale(app()->getLocale())->isoFormat('LL');
    $designation = $application->designation ?: $application->occupation;
    $organization = $application->department_organization ?: $application->current_job;
    $contact = implode(' · ', array_filter([$application->location, $application->email, $application->phone]));
    $qualifications = collect($application->education)->pluck('qualification')->filter()->implode(', ');
    $services = $application->expertiseServices->pluck('name')->implode(', ');
    $items = $application->engagementReviewItems();
    $percentage = $items['percentage']['description'] ?? null;
    unset($items['percentage']);
@endphp

<article class="mou-document" aria-labelledby="mou-title">
    <header class="mou-letterhead">
        <img src="{{ $logo }}" width="88" height="88" alt="UGV RICH">
        <p class="mou-institution">{{ __('expert_mou.institution') }}</p>
        <p class="mou-wing">{{ __('expert_mou.wing') }}</p>
        <h1 id="mou-title">{{ __('expert_mou.title') }}</h1>
        <p class="mou-subtitle">{{ $application->contract_type === 'long_term' ? __('expert_mou.long_term') : __('expert_mou.collaboration') }}</p>
    </header>

    <table class="mou-parties">
        <tbody>
            <tr><th scope="row">{{ __('expert_mou.reference') }}</th><td>UGV-RICH/CONS/MOU/{{ $application->id }}</td></tr>
            <tr><th scope="row">{{ __('expert_mou.execution_date') }}</th><td>{{ $signedDate }}</td></tr>
            <tr><th scope="row">{{ __('expert_mou.first_party') }}</th><td>{{ __('expert_mou.institution') }}</td></tr>
            <tr><th scope="row">{{ __('expert_mou.representative') }}</th><td>{{ $representativeName }} · {{ $representativeDesignation }}</td></tr>
            <tr><th scope="row">{{ __('expert_mou.second_party') }}</th><td>{{ $application->name }}{{ $organization ? ' · '.$organization : '' }}</td></tr>
            <tr><th scope="row">{{ __('expert_mou.contact') }}</th><td>{{ $contact }}</td></tr>
            <tr><th scope="row">{{ __('expert_mou.identification') }}</th><td><span class="mou-line"></span></td></tr>
            @if ($application->engagement_term_years)
                <tr><th scope="row">{{ __('consultancy_engagement.term_years') }}</th><td>{{ $application->engagement_term_years }}</td></tr>
            @endif
            @if ($percentage !== null)
                <tr><th scope="row">{{ __('consultancy_engagement.percentage') }}</th><td>{{ $percentage }}</td></tr>
            @endif
        </tbody>
    </table>

    @if ($items)
        <p class="mou-intro">{{ __('expert_mou.intro') }}</p>
        @foreach ($items as $item)
            @continue(blank($item['title']) && blank($item['description']))
            <section class="mou-clause{{ preg_match('/^\s*(?:8|৮)[.\s]/u', $item['title'] ?? '') ? ' mou-clause-highlight' : '' }}">
                <h2 class="mou-section-title">{{ $item['title'] }}</h2>
                <p class="mou-clause-copy">{{ $item['description'] }}</p>
            </section>
        @endforeach
    @else
        <p class="mou-intro">{{ __('consultancy_engagement.contract_empty') }}</p>
    @endif

    <section class="mou-schedule">
        <h2 class="mou-section-title">{{ __('expert_mou.schedule') }}</h2>
        <dl class="mou-panel">
            <div><dt>{{ __('expert_mou.service_area') }}</dt><dd>{{ $application->category?->name }}</dd></div>
            <div><dt>{{ __('expert_mou.specific_services') }}</dt><dd>{{ $services }}</dd></div>
            <div><dt>{{ __('expert_mou.qualified_expert') }}</dt><dd>{{ implode(' · ', array_filter([$application->name, $qualifications])) }}</dd></div>
            <div><dt>{{ __('expert_mou.hub_contact') }}</dt><dd></dd></div>
            <div><dt>{{ __('expert_mou.special_terms') }}</dt><dd></dd></div>
        </dl>
    </section>

    <section class="mou-signatures">
        <h2 class="mou-section-title">{{ __('expert_mou.signatures') }}</h2>
        <section class="mou-panel">
            <h3>{{ __('expert_mou.first_party_signature') }}</h3>
            <dl>
                <div>
                    <dt>{{ __('expert_mou.signature') }}</dt>
                    <dd>@if ($representativeSignature)<img class="mou-signature-image" src="{{ $representativeSignature }}" alt="{{ __('expert_mou.signature') }}">@endif</dd>
                </div>
                <div><dt>{{ __('expert_mou.name') }}</dt><dd>{{ $representativeName }}</dd></div>
                <div><dt>{{ __('expert_mou.designation') }}</dt><dd>{{ $representativeDesignation }}</dd></div>
                <div><dt>{{ __('expert_mou.date') }}</dt><dd>{{ $assignedDate }}</dd></div>
            </dl>
        </section>
        <section class="mou-panel mou-panel-expert">
            <h3>{{ __('expert_mou.second_party_signature') }}</h3>
            <dl>
                <div>
                    <dt>{{ __('expert_mou.signature') }}</dt>
                    <dd>@if ($signature)<img class="mou-signature-image" src="{{ $signature }}" alt="{{ __('expert_mou.signature') }}">@endif</dd>
                </div>
                <div><dt>{{ __('expert_mou.name') }}</dt><dd>{{ $application->name }}</dd></div>
                <div><dt>{{ __('expert_mou.designation') }}</dt><dd>{{ $designation }}</dd></div>
                <div><dt>{{ __('expert_mou.organization') }}</dt><dd>{{ $organization }}</dd></div>
                <div><dt>{{ __('expert_mou.date') }}</dt><dd>{{ $signedDate }}</dd></div>
                <div><dt>{{ __('expert_mou.seal') }}</dt><dd></dd></div>
            </dl>
        </section>
    </section>
</article>
