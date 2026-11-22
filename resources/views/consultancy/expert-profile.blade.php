@php
    $expert = $application->expert;
    $name = $expert?->name ?? $application->name;
    $designation = $expert?->designation ?? $application->occupation;
    $skills = $expert?->professional_skills ?? array_column($application->skills ?? [], 'name');
    $expertise = $expert?->expertise ?? [];
    $biography = $expert ? $expert->biography() : ($application->bio ? nl2br(e($application->bio)) : null);
    $resume = [
        'education' => collect($application->education)->map(fn (array $item) => [$item['qualification'], $item['institution'], $item['year']]),
        'occupations' => collect($application->occupations)->map(fn (array $item) => [$item['title'], $item['institution'], $item['start_date'].' / '.($item['end_date'] ?? __('consultancy_engagement.present'))]),
        'experience' => collect($application->experience)->map(fn (array $item) => [$item['position'], $item['organization'], $item['period']]),
    ];
@endphp

<x-consultancy-dashboard :application="$application" page="profile">
    <x-expert-profile-header :name="$name" :subtitle="$designation" :photo="$expert?->photo" :label="$application->category->name">
        <x-slot:actions>
            @if ($expert?->is_active)
                <a class="inline-flex items-center gap-2 rounded-xl border border-ink-200 px-3.5 py-2 text-[13px] font-semibold text-ink-800 hover:border-teal-300" href="{{ route('experts.show', $expert) }}">
                    {{ __('consultancy_engagement.view_profile') }} <x-ui-icon name="arrow-up-right" class="size-4" />
                </a>
            @endif
            <a href="#edit-profile" class="inline-flex items-center gap-2 rounded-xl bg-brand-700 px-3.5 py-2 text-[13px] font-semibold text-white hover:bg-brand-800">
                {{ __('consultancy_engagement.edit_profile') }}
            </a>
        </x-slot:actions>
    </x-expert-profile-header>

    <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,0.75fr)_minmax(0,1.25fr)]">
        <div class="space-y-5">
            <section class="rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="font-display text-[15.5px] font-bold">{{ __('consultancy_engagement.contact') }}</h2>
                <dl class="mt-3 space-y-3">
                    @foreach ([
                        'email' => $application->email,
                        'phone' => $application->phone,
                        'location' => $application->location,
                        'department' => $expert?->department,
                        'current_job' => $expert?->current_job,
                        'designation' => $designation,
                        'affiliation' => $expert?->affiliation_type ? __('consultancy_engagement.'.$expert->affiliation_type) : null,
                    ] as $key => $value)
                        <div>
                            <dt class="text-[12.5px] font-medium text-ink-600">{{ __('consultancy_engagement.'.$key) }}</dt>
                            <dd class="mt-0.5 text-[14px] font-semibold break-words text-ink-950">{{ $value ?: __('consultancy_engagement.not_provided') }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            @foreach (['expertise' => $expertise, 'skills' => $skills] as $label => $entries)
                <section class="rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="font-display text-[15.5px] font-bold">{{ __('consultancy_engagement.'.$label) }}</h2>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        @forelse ($entries as $entry)
                            <li class="max-w-full rounded-lg border border-teal-100 bg-teal-50 px-2.5 py-1 text-[13px] font-medium break-words text-teal-900">{{ $entry }}</li>
                        @empty
                            <li class="text-[13.5px] text-ink-600">{{ __('consultancy_engagement.not_provided') }}</li>
                        @endforelse
                    </ul>
                </section>
            @endforeach
        </div>

        <div class="space-y-5">
            <section class="rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="font-display text-[17px] font-bold">{{ __('consultancy_engagement.biography') }}</h2>
                <div class="expert-biography mt-3 break-words text-[13.5px] leading-relaxed text-ink-700">
                    @if ($biography)
                        {!! $biography !!}
                    @else
                        {{ __('consultancy_engagement.not_provided') }}
                    @endif
                </div>
            </section>

            @foreach ($resume as $label => $entries)
                <section class="min-w-0 rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="font-display text-[15.5px] font-bold">{{ __('consultancy_engagement.'.$label) }}</h2>
                    <ul class="mt-3 divide-y divide-ink-100">
                        @forelse ($entries as [$title, $institution, $period])
                            <li class="flex flex-wrap items-start justify-between gap-2.5 py-3 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    <p class="text-[14px] font-semibold break-words">{{ $title }}</p>
                                    <p class="mt-0.5 text-[13.5px] break-words text-ink-600">{{ $institution }}</p>
                                </div>
                                <span class="rounded-lg bg-ink-50 px-2.5 py-1 text-[12.5px] font-medium text-ink-700">{{ $period }}</span>
                            </li>
                        @empty
                            <li class="text-[13.5px] text-ink-600">{{ __('consultancy_engagement.not_provided') }}</li>
                        @endforelse
                    </ul>
                </section>
            @endforeach
        </div>
    </div>

    <section id="edit-profile" class="scroll-mt-5 rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-7">
        <h2 class="border-b border-ink-100 pb-4 font-display text-[17px] font-bold">{{ __('consultancy_engagement.edit_profile') }}</h2>
        <form method="POST" action="{{ route('expert.profile') }}" class="mt-5 space-y-5">
            @csrf
            @method('PUT')
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ([
                    'name' => ['public_name', $name, 150, 'text', true],
                    'occupation' => ['designation', $designation, 150, 'text', true],
                    'current_job' => ['current_job', $expert?->current_job, 150, 'text', false],
                    'phone' => ['phone', $application->phone, 40, 'tel', true],
                    'location' => ['location', $application->location, 150, 'text', true],
                    'department' => ['department', $expert?->inLocale('department', 'en'), 150, 'text', false],
                    'linkedin' => ['linkedin', $expert?->linkedin, 255, 'url', false],
                ] as $key => [$label, $value, $max, $type, $required])
                    <label class="field-label">
                        {{ __('consultancy_engagement.'.$label) }}
                        <input class="field-input mt-2" name="{{ $key }}" type="{{ $type }}" value="{{ old($key, $value) }}" maxlength="{{ $max }}" @required($required)>
                    </label>
                @endforeach
                <label class="field-label">
                    {{ __('consultancy_engagement.affiliation') }}
                    <select class="field-input mt-2" name="affiliation_type">
                        <option value=""></option>
                        @foreach (\App\Models\ConsultancyApplication::AFFILIATIONS as $type)
                            <option value="{{ $type }}" @selected(old('affiliation_type', $expert?->affiliation_type) === $type)>{{ __('consultancy_engagement.'.$type) }}</option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach (['expertise' => ['expertise', $expert?->inLocale('expertise', 'en') ?? []], 'professional_skills' => ['skills', $skills]] as $field => [$label, $entries])
                    <x-expert-tags :name="$field" :label="__('consultancy_engagement.'.$label)" :tags="$entries" />
                @endforeach
            </div>

            <div class="grid gap-4 xl:grid-cols-2">
                <x-expert-bio-editor name="bio_html" :label="__('consultancy_engagement.biography').' (English)'" :html="$expert?->biography('en', false)" />
                <x-expert-bio-editor name="bio_html_bn" :label="__('consultancy_engagement.biography').' ('.__('consultancy_engagement.language_bn').')'" :html="$expert?->biography('bn', false)" />
            </div>

            <button type="submit" class="btn-lead">{{ __('consultancy_engagement.save') }}</button>
        </form>
    </section>
</x-consultancy-dashboard>
