@php
    $user = auth()->user();

    /* The profile asks for a great deal, so it is asked for in five sittings
       rather than one long scroll. Presentation only: every field stays in the
       page and the form posts as a whole.

       Each step lists its own fields, so a rejected save opens on the step the
       complaint belongs to. */
    $steps = [
        'personal' => ['photo', 'name', 'gender', 'nationality', 'country', 'profession', 'designation', 'organization', 'date_of_birth', 'researcher_scope'],
        'academic' => ['department', 'faculty', 'highest_degree', 'research_experience', 'qualifications', 'research_interests', 'research_fields', 'expertise'],
        'contact' => ['phone', 'alternative_phone', 'city', 'address'],
        'links' => ['orcid', 'google_scholar', 'scopus', 'researchgate', 'linkedin', 'website'],
        'additional' => ['biography', 'research_profile', 'languages', 'awards', 'publications'],
    ];

    /* The completion checklist groups the profile its own way, by what counts
       as done rather than by where it is filled in. This says where each of its
       lines lives, so clicking one goes there. */
    $sectionStep = [
        'basic' => 0,
        'academic' => 1,
        'interests' => 1,
        'expertise' => 1,
        'contact' => 2,
        'links' => 3,
    ];

    $keys = array_keys($steps);
    $stepNames = array_map(fn (string $key) => __('researcher.profile.steps.'.$key), $keys);

    $openStep = 0;

    foreach (array_values($steps) as $i => $names) {
        if ($errors->hasAny($names)) {
            $openStep = $i;
            break;
        }
    }
@endphp

<x-layouts.researcher :title="__('researcher.profile.title')">

    <x-portal-heading :title="__('researcher.profile.title')" :lead="__('researcher.profile.lead')">
        <x-slot:actions>
            <a href="{{ route('researcher.settings') }}" class="inline-flex items-center gap-2 rounded-xl border border-ink-200 px-4 py-2.5 text-[13.5px] font-semibold text-ink-700 transition hover:border-brand-300 hover:text-brand-700">
                <x-ui-icon name="cog" class="h-4 w-4" />
                {{ __('researcher.settings.title') }}
            </a>
        </x-slot:actions>
    </x-portal-heading>

    <div x-data="stepForm({{ count($steps) }}, {{ $openStep }}, @js($stepNames))"
         class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_17rem]">

        <div class="min-w-0">
            <x-portal-steps :names="$stepNames" />

            <form method="POST" action="{{ route('researcher.profile.update') }}" enctype="multipart/form-data" class="space-y-6"
                  @submit="guard($event)"
                  @keydown.enter="if ($event.target.tagName !== 'TEXTAREA' && ! last) { $event.preventDefault(); next() }">
                @csrf
                @method('PUT')

                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5" role="alert">
                        <ul class="space-y-1 text-[13.5px] leading-relaxed text-rose-800">
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- ------------------------------------------ step 1, personal --}}
                <fieldset class="field-group" data-step="0" x-show="step === 0">
                    <legend class="field-legend">{{ __('researcher.profile.personal') }}</legend>

                    <div class="mt-6 flex items-center gap-5">
                        @if ($profile->photo)
                            <img src="{{ Storage::url($profile->photo) }}" alt="" class="h-20 w-20 rounded-2xl object-cover ring-1 ring-ink-200">
                        @else
                            <span class="flex h-20 w-20 items-center justify-center rounded-2xl bg-brand-600 font-display text-[26px] font-bold text-white">
                                {{ \Illuminate\Support\Str::of($user->name)->trim()->substr(0, 1)->upper() }}
                            </span>
                        @endif

                        <div class="min-w-0 flex-1">
                            <label for="photo" class="field-label">{{ __('researcher.profile.photo') }}</label>
                            <input id="photo" name="photo" type="file" accept="image/*"
                                   class="field-input mt-2 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-[13px] file:font-semibold file:text-brand-700">
                        </div>
                    </div>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        <div class="sm:col-span-2">
                            <label for="name" class="field-label">{{ __('researcher.auth.name') }} <span class="text-brand-600">*</span></label>
                            <input id="name" name="name" type="text" required value="{{ old('name', $user->name) }}" class="field-input mt-2">
                        </div>

                        @foreach ([
                            ['gender', 'Gender'], ['nationality', 'Nationality'], ['country', 'Country'],
                            ['profession', 'Profession'], ['designation', 'Designation'], ['organization', 'Organization / Institution'],
                        ] as [$name, $label])
                            <div>
                                <label for="{{ $name }}" class="field-label">{{ $label }}</label>
                                <input id="{{ $name }}" name="{{ $name }}" type="text" value="{{ old($name, $profile->{$name}) }}" class="field-input mt-2">
                            </div>
                        @endforeach

                        <div>
                            <label for="date_of_birth" class="field-label">Date of birth</label>
                            <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', $profile->date_of_birth?->toDateString()) }}" class="field-input mt-2">
                        </div>

                        <div>
                            <label for="researcher_scope" class="field-label">National / international</label>
                            <select id="researcher_scope" name="researcher_scope" class="field-input mt-2">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                @foreach (['national' => 'National', 'international' => 'International'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('researcher_scope', $profile->researcher_scope) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>

                {{-- ------------------------------------------ step 2, academic --}}
                <fieldset class="field-group" data-step="1" x-show="step === 1" x-cloak>
                    <legend class="field-legend">{{ __('researcher.profile.academic') }}</legend>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <label for="department" class="field-label">{{ __('research_hub.forms.department') }}</label>
                            <select id="department" name="department" class="field-input mt-2">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department }}" @selected(old('department', $profile->department) === $department)>{{ $department }}</option>
                                @endforeach
                            </select>
                        </div>

                        @foreach ([['faculty', 'Faculty'], ['highest_degree', 'Highest degree'], ['research_experience', 'Research experience']] as [$name, $label])
                            <div>
                                <label for="{{ $name }}" class="field-label">{{ $label }}</label>
                                <input id="{{ $name }}" name="{{ $name }}" type="text" value="{{ old($name, $profile->{$name}) }}" class="field-input mt-2">
                            </div>
                        @endforeach

                        <div class="sm:col-span-2 xl:col-span-3">
                            <label for="qualifications" class="field-label">Academic qualifications</label>
                            <textarea id="qualifications" name="qualifications" rows="3" class="field-input mt-2">{{ old('qualifications', $profile->qualifications) }}</textarea>
                        </div>
                    </div>

                    {{-- The three tag boxes. Each word becomes a tag, and a tag is
                         what posts — so the box says as much, and commits whatever
                         has been typed when you leave it. --}}
                    <div class="mt-5 grid gap-5 lg:grid-cols-3">
                        @foreach ([
                            ['research_interests', 'Research interests'],
                            ['research_fields', 'Research fields'],
                            ['expertise', 'Areas of expertise'],
                        ] as [$name, $label])
                            <x-portal-tag-box :name="$name" :label="$label" :value="old($name, $profile->{$name} ?? [])" />
                        @endforeach
                    </div>
                </fieldset>

                {{-- ------------------------------------------- step 3, contact --}}
                <fieldset class="field-group" data-step="2" x-show="step === 2" x-cloak>
                    <legend class="field-legend">{{ __('researcher.profile.contact') }}</legend>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <label class="field-label">{{ __('researcher.auth.email') }}</label>
                            <input type="email" value="{{ $user->email }}" disabled class="field-input mt-2">
                            <p class="field-help">
                                <a href="{{ route('researcher.settings') }}" class="font-semibold text-brand-700 underline-offset-2 hover:underline">{{ __('researcher.settings.title') }}</a>
                            </p>
                        </div>

                        @foreach ([['phone', 'Phone'], ['alternative_phone', 'Alternative phone'], ['city', 'City']] as [$name, $label])
                            <div>
                                <label for="{{ $name }}" class="field-label">{{ $label }}</label>
                                <input id="{{ $name }}" name="{{ $name }}" type="text" value="{{ old($name, $profile->{$name}) }}" class="field-input mt-2">
                            </div>
                        @endforeach

                        <div class="sm:col-span-2 xl:col-span-3">
                            <label for="address" class="field-label">Address</label>
                            <input id="address" name="address" type="text" value="{{ old('address', $profile->address) }}" class="field-input mt-2">
                        </div>
                    </div>
                </fieldset>

                {{-- --------------------------------------------- step 4, links --}}
                <fieldset class="field-group" data-step="3" x-show="step === 3" x-cloak>
                    <legend class="field-legend">{{ __('researcher.profile.links') }}</legend>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ([
                            ['orcid', 'ORCID'], ['google_scholar', 'Google Scholar'], ['scopus', 'Scopus'],
                            ['researchgate', 'ResearchGate'], ['linkedin', 'LinkedIn'], ['website', 'Personal website'],
                        ] as [$name, $label])
                            <div>
                                <label for="{{ $name }}" class="field-label">{{ $label }}</label>
                                <input id="{{ $name }}" name="{{ $name }}" type="text" value="{{ old($name, $profile->{$name}) }}" class="field-input mt-2">
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                {{-- ---------------------------------------- step 5, additional --}}
                <fieldset class="field-group" data-step="4" x-show="step === 4" x-cloak>
                    <legend class="field-legend">{{ __('researcher.profile.additional') }}</legend>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="biography" class="field-label">Biography</label>
                            <textarea id="biography" name="biography" rows="5" class="field-input mt-2">{{ old('biography', $profile->biography) }}</textarea>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="research_profile" class="field-label">Short research profile</label>
                            <textarea id="research_profile" name="research_profile" rows="3" class="field-input mt-2">{{ old('research_profile', $profile->research_profile) }}</textarea>
                        </div>

                        <div class="sm:col-span-2">
                            <x-portal-tag-box name="languages" label="Languages" :value="old('languages', $profile->languages ?? [])" />
                        </div>

                        <div>
                            <label for="awards" class="field-label">Awards / achievements</label>
                            <textarea id="awards" name="awards" rows="4" class="field-input mt-2">{{ old('awards', $profile->awards) }}</textarea>
                        </div>

                        <div>
                            <label for="publications" class="field-label">Publications</label>
                            <textarea id="publications" name="publications" rows="4" class="field-input mt-2">{{ old('publications', $profile->publications) }}</textarea>
                        </div>
                    </div>
                </fieldset>

                {{-- ----------------------------------------------- the actions --}}
                <div class="sticky bottom-0 z-10 flex flex-wrap items-center gap-3 rounded-[1.25rem] border border-ink-100 bg-white/95 p-4 shadow-[0_-18px_44px_-44px_rgba(2,34,81,0.6)] backdrop-blur-sm">
                    <button type="button" x-show="! first" x-cloak @click="back()" class="btn-ghost">
                        <x-ui-icon name="arrow-left" class="h-4 w-4" />
                        {{ __('researcher.step.back') }}
                    </button>

                    <span class="flex-1"></span>

                    {{-- Saving is allowed from any step: the profile is filled in
                         over time, and only the name is ever insisted on. On the
                         last step the lead button says it instead. --}}
                    <button type="submit" x-show="! last" class="btn-ghost">
                        <x-ui-icon name="check" class="h-4 w-4" stroke="2.4" />
                        {{ __('researcher.profile.save') }}
                    </button>

                    <button type="button" x-show="! last" @click="next()" class="btn-lead group">
                        <span class="relative">{{ __('researcher.step.next') }}</span>
                        <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                    </button>

                    <button type="submit" x-show="last" x-cloak class="btn-lead group">
                        <span class="relative">{{ __('researcher.profile.save') }}</span>
                        <x-ui-icon name="check" class="relative h-[18px] w-[18px]" stroke="2.4" />
                    </button>
                </div>
            </form>
        </div>

        {{-- How far through, and what is left.

             Beside the form rather than above it: it is about the profile as a
             whole, not the step in hand, and in the way of the fields. Each line
             is a way into the step that would tick it off. --}}
        <aside class="rounded-[1.5rem] border border-ink-100 bg-white p-6 xl:sticky xl:top-[84px]">
            <div class="flex items-center justify-between text-[13px] font-semibold">
                <span class="text-ink-700">{{ __('researcher.dashboard.completion') }}</span>
                <span class="font-numeric tabular-nums text-brand-700">{{ $completion }}%</span>
            </div>
            <ul class="mt-4 flex flex-wrap gap-x-5 gap-y-2">
                @foreach ($checklist as $section => $done)
                    <li>
                        <button type="button" @click="go({{ $sectionStep[$section] ?? 0 }})"
                                class="flex items-center gap-2 text-[13px] underline-offset-4 transition hover:underline {{ $done ? 'text-ink-700 hover:text-ink-900' : 'text-ink-400 hover:text-brand-700' }}">
                            @if ($done)
                                <x-ui-icon name="check" class="h-4 w-4 text-brand-600" stroke="2.6" />
                            @else
                                <span class="flex h-4 w-4 items-center justify-center"><span class="h-2 w-2 rounded-full ring-1 ring-ink-300"></span></span>
                            @endif
                            {{ __('researcher.profile.sections.'.$section) }}
                        </button>
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>
</x-layouts.researcher>
