<x-researcher-auth :consultancy="true" :wide="true" title="Consultancy expert registration" lead="Apply to join the UGV RICH expert directory. Select one consultancy category that matches your expertise.">
    <form method="POST" action="{{ route('consultancy.registration.store') }}" enctype="multipart/form-data" class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:space-y-8 sm:px-9 sm:py-8" id="expert-registration">
        @csrf

        <div hidden data-progress class="mx-auto max-w-2xl px-1 text-center sm:px-3">
            <p class="mb-3 text-sm font-semibold text-ink-950 sm:sr-only" aria-live="polite" data-progress-label></p>
            <ol class="grid grid-cols-5" aria-label="Application steps">
                @foreach (['Personal details', 'Education', 'Occupations', 'Work experience', 'Skills'] as $label)
                    <li data-step-marker class="group relative min-w-0 text-ink-600 aria-[current=step]:text-brand-800 data-[complete]:text-ink-900">
                        <span aria-hidden="true" class="flex h-10 items-center justify-center">
                            @unless ($loop->last)
                                <span class="absolute top-5 left-1/2 h-0.5 w-full -translate-y-1/2 bg-ink-200 transition-colors motion-reduce:transition-none group-data-[complete]:bg-brand-400"></span>
                            @endunless
                            <span class="relative z-10 inline-flex size-8 shrink-0 items-center justify-center rounded-full border border-ink-200 bg-white text-sm font-semibold tabular-nums shadow-sm group-aria-[current=step]:border-brand-600 group-aria-[current=step]:bg-brand-600 group-aria-[current=step]:text-white group-aria-[current=step]:ring-4 group-aria-[current=step]:ring-brand-100 group-data-[complete]:border-brand-300 group-data-[complete]:bg-brand-50 group-data-[complete]:text-brand-700">
                                <span class="group-data-[complete]:hidden">{{ $loop->iteration }}</span>
                                <span class="hidden group-data-[complete]:block"><x-ui-icon name="check" class="size-3.5" /></span>
                            </span>
                        </span>
                        <span class="sr-only px-1 text-xs font-semibold leading-tight sm:not-sr-only sm:mt-2 sm:block md:text-sm">{{ $label }}</span>
                        <span class="sr-only hidden group-data-[complete]:inline"> (completed)</span>
                    </li>
                @endforeach
            </ol>
        </div>

        <section class="rounded-2xl border border-ink-200 bg-white p-4 shadow-sm sm:p-7" data-step="Personal information" data-invalid="{{ $errors->hasAny(['name', 'email', 'phone', 'location', 'affiliation_type', 'current_designation', 'service_category_id']) ? 'yes' : 'no' }}">
            <h2 class="mb-5 border-b border-ink-100 pb-4 font-display text-xl font-bold tracking-tight sm:text-2xl" tabindex="-1">Personal information</h2>
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
                        <option value=""></option>
                        @foreach (\App\Models\ConsultancyApplication::AFFILIATIONS as $type)
                            <option value="{{ $type }}" @selected(old('affiliation_type') === $type)>{{ __('consultancy_engagement.'.$type) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="field-label">
                    {{ __('consultancy_engagement.designation') }}
                    <input class="field-input mt-2" name="current_designation" value="{{ old('current_designation') }}" maxlength="150">
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
            </div>
        </section>

        @foreach ($groups as $group => $fields)
            @php
                $heading = ['education' => 'Education qualifications', 'occupations' => 'Occupation history', 'experience' => 'Work experience', 'skills' => 'Skills and summary'][$group];
                $rows = old($group, $group === 'experience' ? [] : [[]]);
            @endphp
            <section class="rounded-2xl border border-ink-200 bg-white p-4 shadow-sm sm:p-7" data-step="{{ $heading }}" data-invalid="{{ $errors->hasAny([$group, $group.'.*', ...($group === 'skills' ? ['bio', 'profile_photo'] : [])]) ? 'yes' : 'no' }}">
                <h2 class="mb-5 border-b border-ink-100 pb-4 font-display text-xl font-bold tracking-tight sm:text-2xl" tabindex="-1">{{ $heading }}</h2>
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
                            <label for="profile-photo" class="field-label">Profile photo <span class="font-normal text-ink-500">(optional)</span></label>
                            <p id="profile-photo-help" class="mt-1 text-sm text-ink-600">JPG, PNG or WebP up to 3 MB. Shown on your expert profile after activation.</p>
                            <input id="profile-photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="profile-photo-help" class="mt-3 w-full text-sm text-ink-600 file:mr-3 file:rounded-lg file:border-0 file:bg-white file:px-3 file:py-2 file:font-semibold file:text-brand-700 file:ring-1 file:ring-inset file:ring-ink-200">
                        </div>
                    </div>
                @endif
            </section>
        @endforeach

        <div hidden><label>Website <input name="website" tabindex="-1" autocomplete="off"></label></div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" hidden data-back class="rounded-xl border border-ink-200 px-4 py-2 text-sm font-semibold">Back</button>
            <a class="rounded-lg px-2 py-2 text-sm font-semibold text-brand-700 hover:bg-brand-50" href="{{ route('login') }}">Back to sign in</a>
            <button type="button" hidden data-next class="btn-lead ml-auto">Continue</button>
            <button type="submit" class="btn-lead ml-auto">Submit application</button>
        </div>
    </form>

    <script>
        (() => {
            const form = document.querySelector('#expert-registration');
            const steps = [...form.querySelectorAll('[data-step]')];
            const progress = form.querySelector('[data-progress]');
            const markers = [...progress.querySelectorAll('[data-step-marker]')];
            const back = form.querySelector('[data-back]');
            const next = form.querySelector('[data-next]');
            const submit = form.querySelector('[type=submit]');
            let current = 0;
            const photo = form.querySelector('#profile-photo');
            const preview = form.querySelector('[data-photo-preview]');
            let previewUrl;

            photo.addEventListener('change', () => {
                if (previewUrl) URL.revokeObjectURL(previewUrl);
                const file = photo.files[0];
                previewUrl = file ? URL.createObjectURL(file) : undefined;
                preview.hidden = !previewUrl;
                if (previewUrl) preview.src = previewUrl;
                else preview.removeAttribute('src');
            });

            function showStep(index, focus = true) {
                current = index;
                steps.forEach((step, position) => { step.hidden = position !== index; });
                markers.forEach((marker, position) => {
                    marker.setAttribute('aria-current', position === index ? 'step' : 'false');
                    marker.toggleAttribute('data-complete', position < index);
                });
                progress.querySelector('[data-progress-label]').textContent =
                    `Step ${index + 1} of ${steps.length} · ${steps[index].dataset.step}`;
                back.hidden = index === 0;
                next.hidden = index === steps.length - 1;
                submit.hidden = !next.hidden;
                if (focus) steps[index].querySelector('h2').focus();
            }

            function validStep(step) {
                return [...step.querySelectorAll('input, select, textarea')].every(field => field.reportValidity());
            }

            function advance() {
                if (validStep(steps[current])) showStep(current + 1);
            }

            progress.hidden = false;
            showStep(Math.max(0, steps.findIndex(step => step.dataset.invalid === 'yes')), false);
            form.noValidate = true;
            next.addEventListener('click', advance);
            back.addEventListener('click', () => showStep(current - 1));
            form.addEventListener('submit', event => {
                if (current < steps.length - 1) {
                    event.preventDefault();
                    advance();
                    return;
                }

                const invalidStep = steps.findIndex(step => step.querySelector(':invalid'));
                if (invalidStep !== -1) {
                    event.preventDefault();
                    showStep(invalidStep, false);
                    validStep(steps[invalidStep]);
                }
            });

            form.querySelectorAll('[data-repeater]').forEach(repeater => {
                const rows = repeater.querySelector('[data-rows]');
                const add = repeater.querySelector('[data-add]');
                const minimum = Number(repeater.dataset.min);
                let index = rows.children.length;

                function refresh() {
                    rows.querySelectorAll('[data-remove]').forEach(button => {
                        button.hidden = rows.children.length <= minimum;
                    });
                    add.disabled = rows.children.length >= 15;
                }

                add.hidden = false;
                refresh();
                add.addEventListener('click', () => {
                    rows.insertAdjacentHTML('beforeend', repeater.querySelector('template').innerHTML.replaceAll('__index__', index++));
                    refresh();
                    rows.lastElementChild.querySelector('input').focus();
                });
                rows.addEventListener('click', event => {
                    const button = event.target.closest('[data-remove]');
                    if (!button || rows.children.length <= minimum) return;
                    button.closest('fieldset').remove();
                    refresh();
                    add.focus();
                });
            });
        })();
    </script>
</x-researcher-auth>
