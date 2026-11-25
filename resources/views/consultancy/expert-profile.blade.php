@php
    $expert = $application->expert;
    $name = $expert?->name ?? $application->name;
    $designation = $expert?->designation ?? $application->occupation;
    $skills = $expert?->professional_skills ?? array_column($application->skills ?? [], 'name');
    $expertise = $expert?->expertise ?? [];
    $biography = $expert ? $expert->biography() : ($application->bio ? nl2br(e($application->bio)) : null);
    $photoUrl = $expert?->photo ? Storage::disk('public')->url($expert->photo) : null;
    $resume = [
        'education' => collect($application->education)->map(fn (array $item) => [$item['qualification'], $item['institution'], $item['year']]),
        'occupations' => collect($application->occupations)->map(fn (array $item) => [$item['title'], $item['institution'], $item['start_date'].' / '.($item['end_date'] ?? __('consultancy_engagement.present'))]),
        'experience' => collect($application->experience)->map(fn (array $item) => [$item['position'], $item['organization'], $item['period']]),
    ];
@endphp

<x-consultancy-dashboard :application="$application" page="profile">
    <style>
        .expert-profile-space { --profile-line:#dce6ec; --profile-accent:#10685e; display:grid; gap:18px; }
        .expert-profile-space :focus-visible { outline:2px solid #168476; outline-offset:3px; }
        .profile-identity { position:relative; isolation:isolate; overflow:hidden; border-radius:18px; background:#12283d; color:white; padding:clamp(17px,3.6vw,38px); }
        .profile-identity-content { position:relative; z-index:1; max-width:65%; }
        .profile-avatar { inline-size:clamp(66px,10vw,98px); aspect-ratio:1; display:grid; place-items:center; overflow:hidden; border:2px solid #91d9c7; border-radius:16px; background:#25445a; color:#a8e7d6; }
        .profile-avatar img, .profile-upload-preview img { width:100%; height:100%; object-fit:cover; }
        .profile-avatar svg, .profile-upload-preview svg { width:32px; height:32px; }
        .profile-identity h2 { color:white; font-family:var(--font-display); font-size:clamp(1.45rem,3.5vw,2.8rem); line-height:1.12; font-weight:800; letter-spacing:-.025em; margin-top:10px; overflow-wrap:anywhere; }
        .profile-identity-category { color:#a8e7d6; font-size:.82rem; font-weight:750; margin-top:8px; overflow-wrap:anywhere; }
        .profile-identity-subtitle { color:#d0dee8; font-size:.85rem; margin-top:3px; overflow-wrap:anywhere; }
        .profile-identity-actions { display:flex; flex-wrap:wrap; gap:8px; margin-top:15px; }
        .profile-identity-actions a { display:inline-flex; align-items:center; gap:5px; min-height:42px; padding:7px 10px; border:1px solid #6d879a; border-radius:8px; color:#fff; font-size:.79rem; font-weight:750; }
        .profile-identity-actions .profile-edit-link { background:#a8e7d6; color:#153544; border-color:#a8e7d6; }
        .profile-identity-actions a:hover { border-color:#d3f4eb; }
        .profile-art { position:absolute; z-index:-1; right:0; bottom:0; width:min(43%,420px); height:auto; }
        .profile-art-path { stroke-dasharray:1; animation:profile-draw 1.15s ease-out both; }
        .profile-sheet { background:#fff; border:1px solid var(--profile-line); border-radius:15px; }
        .profile-editor { scroll-margin-top:12px; padding:clamp(14px,3vw,29px); }
        .profile-editor-heading { display:flex; align-items:center; gap:9px; border-bottom:1px solid var(--profile-line); padding-bottom:12px; }
        .profile-editor-heading svg { color:var(--profile-accent); width:20px; height:20px; }
        .profile-save-status { display:none; }
        .profile-editor:target .profile-save-status { display:block; margin-top:12px; padding:8px 10px; background:#f0f7f6; border-radius:8px; color:#10685e; font-size:.84rem; }
        .profile-photo-section { display:grid; grid-template-columns:auto minmax(0,1fr); align-items:center; gap:12px; padding:13px; border:1px dashed #a9c7c9; border-radius:12px; background:#f0f7f6; }
        .profile-upload-preview { display:grid; place-items:center; width:66px; aspect-ratio:1; overflow:hidden; border-radius:11px; background:#dcefeb; color:#13786b; }
        .profile-photo-section label { color:#163448; font-size:.88rem; font-weight:800; display:block; }
        .profile-photo-section p { color:#486275; font-size:.75rem; margin-top:3px; }
        .profile-photo-section input { display:block; width:100%; min-width:0; margin-top:8px; font-size:.75rem; color:#3c5569; }
        .profile-photo-section input::file-selector-button { cursor:pointer; margin-right:7px; min-height:36px; padding:5px 8px; border:1px solid #b7d1d0; border-radius:7px; color:#10685e; background:#fff; font-weight:750; }
        .profile-photo-section [role="alert"] { color:#912f36; }
        .profile-editor .field-input { border-color:#d2dfe6; border-radius:8px; background:#f8fbfc; }
        .profile-editor .field-input:focus { border-color:#168476; background:#fff; }
        .profile-editor-footer { display:flex; justify-content:flex-end; border-top:1px solid var(--profile-line); padding-top:12px; }
        .profile-save { display:inline-flex; align-items:center; justify-content:center; gap:7px; min-height:44px; padding:8px 14px; border-radius:8px; background:#10685e; color:#fff; cursor:pointer; font-size:.85rem; font-weight:750; }
        .profile-save:hover { background:#0d544d; }
        .profile-save svg { width:17px; height:17px; }
        @keyframes profile-draw { from { stroke-dashoffset:1; } to { stroke-dashoffset:0; } }
        @media (max-width:36rem) {
            .profile-identity-content { max-width:100%; }
            .profile-art { width:48%; top:0; bottom:auto; opacity:.65; }
            .profile-upload-preview { width:48px; }
            .profile-photo-section { gap:9px; }
            .profile-save { width:100%; }
        }
        @media (prefers-reduced-motion:reduce) { .profile-art-path { animation:none; } }
    </style>

    <div class="expert-profile-space">
    <section class="profile-identity" aria-labelledby="expert-profile-name">
        <svg class="profile-art" viewBox="0 0 380 244" fill="none" aria-hidden="true">
            <defs><pattern id="profile-dots" width="14" height="14" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1" fill="#3e5e74"/></pattern></defs>
            <path d="M0 244v-72L148 24h232v220Z" fill="url(#profile-dots)"/>
            <g stroke="#54758b"><circle cx="242" cy="135" r="88" stroke-dasharray="4 6"/><circle cx="242" cy="135" r="70"/><path d="M0 214h91l42-42h42m205-95h-61l-30 29m91 95h-43l-28-28M242 29v28m0 155v32"/></g>
            <rect x="193" y="81" width="98" height="107" rx="12" fill="#20445b" stroke="#8bd4c5" stroke-width="1.5"/>
            <circle cx="241" cy="117" r="15" stroke="#a8e7d6" stroke-width="1.5"/><path d="M214 159c7-24 47-24 54 0m-44 12h34" stroke="#a8e7d6" stroke-width="1.5" stroke-linecap="round"/>
            <path class="profile-art-path" d="M48 202h54l32-33h39l18-19m101-24h27l20-20h41" pathLength="1" stroke="#8bd4c5" stroke-width="2"/>
            <g fill="#a8e7d6"><circle cx="48" cy="202" r="4"/><circle cx="134" cy="169" r="4"/><circle cx="339" cy="106" r="4"/></g>
        </svg>
        <div class="profile-identity-content">
            <div class="profile-avatar" aria-hidden="true">
                @if ($photoUrl)<img src="{{ $photoUrl }}" width="98" height="98" alt="">@else<x-ui-icon name="user" />@endif
            </div>
            <p class="profile-identity-category">{{ $application->category->name }}</p>
            <h2 id="expert-profile-name">{{ $name }}</h2>
            @if ($designation)<p class="profile-identity-subtitle">{{ $designation }}</p>@endif
            <div class="profile-identity-actions">
                <a href="#edit-profile" class="profile-edit-link">{{ __('consultancy_engagement.edit_profile') }}</a>
                @if ($expert?->is_active)
                    <a href="{{ route('experts.show', $expert) }}">
                        {{ __('consultancy_engagement.view_profile') }} <x-ui-icon name="arrow-up-right" class="size-4" />
                    </a>
                @endif
            </div>
        </div>
    </section>

    <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,0.75fr)_minmax(0,1.25fr)]">
        <div class="space-y-5">
            <section class="profile-sheet p-5 sm:p-6">
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
                <section class="profile-sheet p-5 sm:p-6">
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
            <section class="profile-sheet p-5 sm:p-6">
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
                <section class="profile-sheet min-w-0 p-5 sm:p-6">
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

    <section id="edit-profile" class="profile-sheet profile-editor" aria-labelledby="profile-editor-heading">
        <div class="profile-editor-heading"><x-ui-icon name="user" /><h2 id="profile-editor-heading" class="font-display text-[17px] font-bold">{{ __('consultancy_engagement.edit_profile') }}</h2></div>
        @if (session('status'))<p class="profile-save-status">{{ session('status') }}</p>@endif
        <form method="POST" action="{{ route('expert.profile') }}" enctype="multipart/form-data" class="mt-5 space-y-5">
            @csrf
            @method('PUT')
            <div class="profile-photo-section">
                <span class="profile-upload-preview" aria-hidden="true">
                    <img data-profile-preview @if ($photoUrl) src="{{ $photoUrl }}" @else hidden @endif width="66" height="66" alt="">
                    <span data-profile-placeholder @if ($photoUrl) hidden @endif><x-ui-icon name="user" /></span>
                </span>
                <div>
                    <label for="expert-profile-photo">{{ __('consultancy_engagement.profile_design.photo') }}</label>
                    <p id="expert-profile-photo-help">{{ __('consultancy_engagement.profile_design.photo_help') }}</p>
                    <input type="file" id="expert-profile-photo" name="profile_photo" accept="image/jpeg,image/png,image/webp" aria-describedby="expert-profile-photo-help">
                    @error('profile_photo')<p role="alert">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ([
                    'name' => ['public_name', $expert?->inLocale('name', 'en') ?? $application->name, 150, 'text', true],
                    'occupation' => ['designation', $expert?->inLocale('designation', 'en') ?? $application->occupation, 150, 'text', true],
                    'current_job' => ['current_job', $expert?->inLocale('current_job', 'en'), 150, 'text', false],
                    'phone' => ['phone', $application->phone, 40, 'tel', true],
                    'location' => ['location', $application->location, 150, 'text', true],
                    'department' => ['department', $expert?->inLocale('department', 'en'), 150, 'text', false],
                    'linkedin' => ['linkedin', $expert?->linkedin, 255, 'url', false],
                ] as $key => [$label, $value, $max, $type, $required])
                    <label class="field-label">
                        {{ __('consultancy_engagement.'.$label) }}
                        @if (in_array($key, ['name', 'occupation', 'current_job', 'department'], true)) (English) @endif
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

            <x-expert-tags name="professional_skills" :label="__('consultancy_engagement.skills').' (English)'"
                :tags="$expert?->inLocale('professional_skills', 'en') ?? array_column($application->skills ?? [], 'name')" />

            <x-expert-bio-editor name="bio_html" :label="__('consultancy_engagement.biography').' (English)'" :html="$expert?->biography('en', false)" />

            <fieldset class="grid min-w-0 gap-4 rounded-lg border border-ink-200 p-4 sm:grid-cols-2" lang="bn">
                <legend class="px-2 font-semibold text-ink-900">বাংলা প্রোফাইল</legend>
                @foreach (['name_bn' => 'public_name', 'designation_bn' => 'designation', 'department_bn' => 'department', 'current_job_bn' => 'current_job'] as $field => $label)
                    <label class="field-label">
                        {{ __('consultancy_engagement.'.$label, locale: 'bn') }}
                        <input class="field-input mt-2" name="{{ $field }}" type="text" value="{{ old($field, $expert?->{$field}) }}" maxlength="150">
                    </label>
                @endforeach
                <x-expert-tags name="expertise_bn" :label="__('consultancy_engagement.expertise', locale: 'bn')" :tags="$expert?->expertise_bn ?? []" />
                <x-expert-tags name="professional_skills_bn" :label="__('consultancy_engagement.skills', locale: 'bn')" :tags="$expert?->professional_skills_bn ?? []" />
                <div class="sm:col-span-2">
                    <x-expert-bio-editor name="bio_html_bn" :label="__('consultancy_engagement.biography', locale: 'bn')" :html="$expert?->biography('bn', false)" />
                </div>
            </fieldset>

            <div class="profile-editor-footer">
                <button type="submit" class="profile-save"><x-ui-icon name="check" /> {{ __('consultancy_engagement.save') }}</button>
            </div>
        </form>
    </section>
    </div>
    <script>
        (() => {
            const input = document.querySelector('#expert-profile-photo');
            const preview = document.querySelector('[data-profile-preview]');
            const placeholder = document.querySelector('[data-profile-placeholder]');
            const original = preview.getAttribute('src');
            let objectUrl;

            input.addEventListener('change', () => {
                if (objectUrl) URL.revokeObjectURL(objectUrl);
                const file = input.files[0];
                objectUrl = file ? URL.createObjectURL(file) : null;
                const source = objectUrl || original;
                if (source) preview.src = source;
                else preview.removeAttribute('src');
                preview.hidden = !source;
                placeholder.hidden = Boolean(source);
            });
        })();
    </script>
</x-consultancy-dashboard>
