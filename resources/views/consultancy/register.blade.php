<x-researcher-auth :consultancy="true" :wide="true" title="Consultancy expert registration" lead="Apply to join the UGV RICH expert directory.">
    <x-slot:sidebar>
        <aside class="relative isolate hidden min-h-full overflow-hidden rounded-l-[calc(var(--radius-3xl)-1px)] bg-[#183b2b] px-9 py-10 text-white lg:flex lg:flex-col xl:px-11 xl:py-12">
            <h2 class="max-w-[12ch] font-display text-4xl font-bold leading-[1.08] tracking-tight !text-[#f6f8ed] xl:text-[2.75rem]">Join the expert network.</h2>
            <p class="mt-4 max-w-60 text-sm leading-relaxed text-[#d1decf]">Bring your professional experience to UGV RICH consultancy projects.</p>
            <img src="{{ asset('media/consultancy/expert-network.svg') }}" alt="" width="364" height="320" class="mx-auto my-9 h-auto w-full max-w-90 xl:my-12">
            <div class="mt-auto border-t border-white/20 pt-5 text-sm leading-relaxed text-[#d1decf]">
                <p class="font-semibold text-white">Have these ready</p>
                <p class="mt-1">Your qualifications, work history, and a profile photo.</p>
            </div>
        </aside>
    </x-slot:sidebar>

    <x-slot:header>
        <header class="px-5 pt-7 sm:px-9 sm:pt-10 xl:px-12">
            <h1 class="font-display text-3xl font-bold leading-tight tracking-tight text-ink-950 sm:text-[2.25rem]">Consultancy expert registration</h1>
            <p class="mt-2 text-sm leading-relaxed text-ink-600 sm:text-base">Apply to join the UGV RICH expert directory.</p>
        </header>
    </x-slot:header>

    <form method="POST" action="{{ route('consultancy.registration.store') }}" enctype="multipart/form-data" class="space-y-6 px-5 pt-6 pb-7 sm:px-9 sm:pb-9 xl:px-12" id="expert-registration">
        @csrf

        <div hidden data-progress class="border-b border-ink-100 pb-5">
            <p class="mb-2 text-xs font-semibold text-brand-800 sm:sr-only" aria-live="polite" data-progress-label></p>
            <ol class="grid grid-cols-5 gap-2" aria-label="Application steps">
                @foreach (['Personal details', 'Education', 'Occupations', 'Work experience', 'Skills'] as $label)
                    <li data-step-marker class="group min-w-0 text-ink-600 aria-[current=step]:text-brand-800 data-[complete]:text-brand-800">
                        <span aria-hidden="true" class="block h-1 rounded-full bg-ink-100 group-data-[complete]:bg-brand-300 group-aria-[current=step]:bg-brand-600"></span>
                        <span class="sr-only text-xs font-semibold leading-tight sm:not-sr-only sm:mt-2 sm:block">{{ $label }}</span>
                        <span class="sr-only hidden group-data-[complete]:inline"> (completed)</span>
                    </li>
                @endforeach
            </ol>
        </div>

        <section data-step="Personal details" data-invalid="{{ $errors->hasAny(['name', 'email', 'phone', 'location', 'affiliation_type', 'service_category_id', 'expertise_service_ids', 'expertise_service_ids.*']) ? 'yes' : 'no' }}">
            <h2 class="mb-5 font-display text-xl font-bold tracking-tight sm:text-2xl" tabindex="-1">Personal details</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach (['name' => ['Full name', 'text', 'name'], 'email' => ['Email address', 'email', 'email'], 'phone' => ['Phone number', 'tel', 'tel'], 'location' => ['City and country', 'text', 'address-level2']] as $key => [$label, $type, $autocomplete])
                    <label class="field-label">
                        {{ $label }}
                        <input class="field-input mt-2" type="{{ $type }}" name="{{ $key }}" value="{{ old($key) }}" autocomplete="{{ $autocomplete }}" maxlength="{{ $key === 'phone' ? 40 : ($key === 'email' ? 180 : 150) }}" required>
                    </label>
                @endforeach
                <label class="field-label">
                    {{ __('consultancy_engagement.affiliation') }}
                    <select name="affiliation_type" class="field-input mt-2" required>
                        <option value="">Choose affiliation</option>
                        @foreach (\App\Models\ConsultancyApplication::AFFILIATIONS as $type)
                            <option value="{{ $type }}" @selected(old('affiliation_type') === $type)>{{ __('consultancy_engagement.'.$type) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field-label">
                    Consultancy category
                    <select name="service_category_id" class="field-input mt-2" required>
                        <option value="">Choose one category</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('service_category_id') == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </label>
                <div id="expertise-field" class="field-label min-w-0 sm:col-span-2">
                    <label for="expertise-services">Areas of expertise</label>
                    <select id="expertise-services" name="expertise_service_ids[]" class="field-input mt-2" multiple size="4" aria-describedby="expertise-help">
                        @foreach ($categories as $category)
                            @if ($category->services->isNotEmpty())
                                <optgroup label="{{ $category->name }}" data-category="{{ $category->id }}">
                                    @foreach ($category->services as $service)
                                        <option value="{{ $service->id }}" @selected(in_array($service->id, (array) old('expertise_service_ids', [])))>{{ $service->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                    <span id="expertise-help" class="field-help block font-normal">Select one or more areas.</span>
                    <span id="expertise-required" class="mt-1 block text-sm text-red-700" role="status" hidden>Select at least one area of expertise.</span>
                    @if ($message = $errors->first('expertise_service_ids') ?: $errors->first('expertise_service_ids.*'))
                        <span class="mt-1 block text-sm text-red-700">{{ $message }}</span>
                    @endif
                </div>
            </div>
        </section>

        @foreach ($groups as $group => $fields)
            @php
                $heading = ['education' => 'Education qualifications', 'occupations' => 'Occupation history', 'experience' => 'Work experience', 'skills' => 'Skills and summary'][$group];
                $rows = old($group, $group === 'experience' ? [] : [[]]);
            @endphp
            <section data-step="{{ $heading }}" data-invalid="{{ $errors->hasAny([$group, $group.'.*', ...($group === 'skills' ? ['bio', 'profile_photo'] : [])]) ? 'yes' : 'no' }}">
                <h2 class="mb-5 font-display text-xl font-bold tracking-tight sm:text-2xl" tabindex="-1">{{ $heading }}</h2>
                @if ($group === 'occupations') <p class="mb-4 text-sm text-ink-600">List your primary occupation first. Add each additional role separately.</p> @endif
                @if ($group === 'experience') <p class="mb-4 text-sm text-ink-600">Optional. Add each relevant position separately.</p> @endif
                <div data-repeater data-group="{{ $group }}" data-min="{{ $group === 'experience' ? 0 : 1 }}">
                    <div class="space-y-3" data-rows>
                        @foreach (is_array($rows) ? array_values($rows) : [] as $row)
                            @include('consultancy.fields', ['index' => $loop->index, 'row' => $row])
                        @endforeach
                    </div>
                    <template>@include('consultancy.fields', ['index' => '__index__', 'row' => []])</template>
                    <button type="button" hidden data-add class="mt-3 rounded-xl border border-brand-200 px-4 py-2.5 text-sm font-semibold text-brand-700 hover:bg-brand-50 disabled:opacity-50">+ Add {{ ['education' => 'qualification', 'occupations' => 'occupation', 'experience' => 'experience', 'skills' => 'skill'][$group] }}</button>
                </div>
                @if ($group === 'skills')
                    <label class="field-label mt-5 block">
                        Professional summary (optional)
                        <textarea name="bio" class="field-input mt-2" rows="4" maxlength="3000">{{ old('bio') }}</textarea>
                    </label>
                    <div class="flex flex-col items-start gap-4 rounded-xl border border-ink-100 mt-5 bg-ink-50/60 p-4 sm:flex-row sm:items-center">
                        <div class="relative grid size-16 shrink-0 place-items-center overflow-hidden rounded-full bg-white text-brand-700 ring-1 ring-ink-200">
                            <x-ui-icon name="user" class="size-7" />
                            <img hidden data-photo-preview alt="" class="absolute inset-0 size-full object-cover">
                        </div>
                        <div class="min-w-0 flex-1">
                            <label for="profile-photo" class="field-label">Profile photo <span class="font-normal text-ink-500">(required)</span></label>
                            <p id="profile-photo-help" class="mt-1 text-sm text-ink-600">JPG, PNG or WebP up to 3 MB. Shown on your expert profile after activation.</p>
                            <input id="profile-photo" name="profile_photo" type="file" required accept="image/jpeg,image/png,image/webp" aria-describedby="profile-photo-help" class="mt-3 w-full text-sm text-ink-600 file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:font-semibold file:text-brand-700 file:ring-1 file:ring-inset file:ring-ink-200">
                        </div>
                    </div>
                @endif
            </section>
        @endforeach

        <div hidden><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>
        <div class="flex flex-wrap items-center gap-3 border-t border-ink-100 pt-5">
            <button type="button" hidden data-back class="rounded-xl border border-ink-200 px-4 py-2 text-sm font-semibold">Back</button>
            <a class="rounded-lg px-2 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-50" href="{{ route('login') }}">Back to sign in</a>
            <button type="button" hidden data-next class="btn-lead ml-auto">Continue</button>
            <button type="submit" class="btn-lead ml-auto">Submit application</button>
        </div>
    </form>

    @vite('resources/js/consultancy-registration.js')
</x-researcher-auth>
