<x-filament-panels::page>
    @include('consultancy.admin-styles')

    @php
        $application = $this->getRecord();
        $statusColor = match ($application->status) {
            $application::APPROVED => 'success',
            $application::DECLINED => 'danger',
            default => 'warning',
        };
        $decisions = collect($application->decisions)->keyBy('stage');
        $skills = array_column($application->skills ?? [], 'name');
    @endphp

    <div class="consultancy-admin">
        <header class="consultancy-hero">
            <div class="consultancy-avatar" aria-hidden="true">
                @if ($application->profile_photo_path)
                    <img src="{{ route('expert.application-photo', $application) }}" alt="" width="56" height="56">
                @else
                    <x-filament::icon icon="heroicon-o-user" class="h-6 w-6" />
                @endif
            </div>
            <div>
                <h2>{{ $application->name }}</h2>
                <p>{{ $application->occupation }} &middot; {{ $application->category->name }}</p>
            </div>
            <div class="consultancy-status">
                <x-filament::badge :color="$statusColor">{{ $application::STATUSES[$application->status] }}</x-filament::badge>
            </div>
        </header>

        <div class="consultancy-grid">
            <div class="consultancy-admin">
                <x-filament::section heading="Applicant information" icon="heroicon-o-identification">
                    <dl class="consultancy-fields">
                        @foreach ([
                            'Email address' => $application->email,
                            'Phone' => $application->phone,
                            'Location' => $application->location,
                            __('consultancy_engagement.affiliation') => $application->affiliation_type ? __('consultancy_engagement.'.$application->affiliation_type) : null,
                            __('consultancy_engagement.current_job') => $application->current_job,
                            __('consultancy_engagement.designation') => $application->current_designation,
                            'Submitted' => $application->created_at?->toDayDateTimeString(),
                        ] as $label => $value)
                            <div><dt>{{ $label }}</dt><dd>{{ $value ?: 'Not provided' }}</dd></div>
                        @endforeach
                    </dl>
                </x-filament::section>

                <x-filament::section heading="Education" icon="heroicon-o-academic-cap">
                    <ul class="consultancy-entries">
                        @forelse ($application->education as $entry)
                            <li>
                                <p class="consultancy-entry-title">{{ $entry['qualification'] }}</p>
                                <p class="consultancy-muted">{{ $entry['institution'] }} &middot; {{ $entry['year'] }}</p>
                            </li>
                        @empty
                            <li class="consultancy-muted">No education entries submitted.</li>
                        @endforelse
                    </ul>
                </x-filament::section>

                @if ($application->occupations)
                    <x-filament::section heading="Occupation history" icon="heroicon-o-building-office">
                        <ul class="consultancy-entries">
                            @foreach ($application->occupations as $entry)
                                <li>
                                    <p class="consultancy-entry-title">{{ $entry['title'] }}</p>
                                    <p class="consultancy-muted">{{ $entry['institution'] }} &middot; {{ $entry['start_date'] }} to {{ $entry['end_date'] ?? 'Present' }}</p>
                                </li>
                            @endforeach
                        </ul>
                    </x-filament::section>
                @endif

                <x-filament::section heading="Work experience" icon="heroicon-o-briefcase">
                    <ul class="consultancy-entries">
                        @forelse ($application->experience ?? [] as $entry)
                            <li>
                                <p class="consultancy-entry-title">{{ $entry['position'] }}</p>
                                <p class="consultancy-muted">{{ $entry['organization'] }} &middot; {{ $entry['period'] }}</p>
                            </li>
                        @empty
                            <li class="consultancy-muted">No work experience submitted.</li>
                        @endforelse
                    </ul>
                </x-filament::section>

                <x-filament::section heading="Skills and summary" icon="heroicon-o-sparkles">
                    <p class="consultancy-entry-title">{{ $skills ? implode(', ', $skills) : 'No skills submitted' }}</p>
                    @if ($application->bio)
                        <p class="consultancy-note">{{ $application->bio }}</p>
                    @endif
                </x-filament::section>
            </div>

            <div class="consultancy-admin">
                <x-filament::section heading="Decision history" icon="heroicon-o-queue-list">
                    <ol>
                        @foreach ($application::REVIEW_STAGES as $status => $label)
                            @php($decision = $decisions->get($status))
                            <li class="consultancy-stage">
                                <span class="consultancy-stage-number" aria-hidden="true">{{ $loop->iteration }}</span>
                                <div>
                                    <div class="consultancy-stage-meta">
                                        <span class="consultancy-entry-title">{{ $label }}</span>
                                        @if ($decision)
                                            <x-filament::badge :color="$decision['decision'] === 'Approved' ? 'success' : 'danger'">{{ $decision['decision'] }}</x-filament::badge>
                                        @elseif ($application->status === $status)
                                            <x-filament::badge color="warning">Current stage</x-filament::badge>
                                        @else
                                            <x-filament::badge color="gray">{{ $application->status === $application::DECLINED ? 'Closed' : 'Waiting' }}</x-filament::badge>
                                        @endif
                                    </div>
                                    @if ($decision)
                                        <p class="consultancy-muted">{{ $decision['reviewer'] }} &middot; {{ $decision['at'] }}</p>
                                        @if ($decision['note'])
                                            <p class="consultancy-note">{{ $decision['note'] }}</p>
                                        @endif
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </x-filament::section>

                @if ($application->status === $application::APPROVED)
                    <x-filament::section heading="Engagement and onboarding" icon="heroicon-o-document-check">
                        <dl class="consultancy-fields">
                            <div><dt>Engagement</dt><dd>{{ $application::CONTRACTS[$application->contract_type] ?? 'Awaiting assignment' }}</dd></div>
                            @if ($application->engagement_percentage !== null)
                                <div><dt>Percentage</dt><dd>{{ $application->engagement_percentage }}%</dd></div>
                            @endif
                            <div><dt>Onboarding</dt><dd>{{ $application->signed_at ? 'Signed '.$application->signed_at->toDayDateTimeString() : ($application->contract_type ? 'Awaiting applicant signature' : 'Pending engagement') }}</dd></div>
                        </dl>
                        @if ($application->signed_at)
                            <p class="consultancy-note">Policy version {{ $application->policy_version }} &middot; <a class="consultancy-link" href="{{ route('expert.signature', $application) }}">Download signed PNG</a></p>
                        @endif
                    </x-filament::section>
                @endif
            </div>
        </div>
    </div>
</x-filament-panels::page>
