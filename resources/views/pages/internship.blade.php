<x-layouts.app :title="__('site.internship.meta_title')"
               :description="__('site.internship.meta_description')">

    <x-page-hero
        :eyebrow="__('site.internship.hero_eyebrow')"
        :title="__('site.internship.hero_title')"
        :lead="__('site.internship.hero_lead')"
        :breadcrumbs="[__('site.internship.meta_title') => null]" />

    @php
        // One select's look, shared by the dropdowns on the form.
        $select = fn (string $name) => collect([
            'w-full appearance-none rounded-2xl border bg-ink-50/60 py-3.5 pl-11 pr-10 text-[15px] text-ink-900 transition focus:bg-white focus:outline-none focus:ring-4',
            $errors->has($name)
                ? 'border-red-300 focus:border-red-400 focus:ring-red-100'
                : 'border-ink-200 hover:border-ink-300 focus:border-brand-400 focus:ring-brand-100',
        ])->implode(' ');

        // Which step to open on after a failed submit: the one holding the first error.
        $steps = [
            1 => ['name', 'email', 'phone'],
            2 => ['university', 'department', 'programme', 'year_level', 'student_id', 'cgpa'],
            3 => ['track', 'duration_months', 'start_date', 'mode', 'skills', 'motivation', 'cv', 'portfolio_url'],
        ];
        $openStep = collect($steps)->search(fn ($fields) => $errors->hasAny($fields)) ?: 1;
    @endphp

    <section class="bg-white py-14 sm:py-16">
        <div class="container-rich grid gap-10 lg:grid-cols-[minmax(0,1.4fr)_minmax(0,0.6fr)] lg:gap-12">

            <div>
                @if ($errors->any())
                    <div class="reveal mb-6 flex items-start gap-4 rounded-[1.5rem] border border-red-200 bg-red-50 p-6" role="alert">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                            <x-ui-icon name="x" class="h-5 w-5" stroke="2.5" />
                        </span>
                        <div>
                            <p class="font-display text-[16px] font-bold text-red-700">{{ __('site.forms.errors_title') }}</p>
                            <ul class="mt-2 space-y-1 text-[14px] text-red-600">
                                @foreach ($errors->all() as $error)
                                    <li>· {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                {{-- Three steps, all in the DOM and only hidden, so one submit sends
                     everything and the browser's own validation still applies. --}}
                <form action="{{ route('internship.store') }}" method="POST" enctype="multipart/form-data"
                      class="reveal overflow-hidden rounded-[2rem] border border-ink-100 bg-white shadow-[0_30px_70px_-50px_rgba(7,20,38,0.45)]"
                      x-data="{
                          step: {{ $openStep }},
                          last: 3,
                          file: null,
                          dragging: false,
                          fields: { name: @js(old('name', '')), track: @js(old('track', '')) },
                          take(files) {
                              if (! files || ! files.length) return;
                              this.$refs.cv.files = files;
                              this.pick();
                          },
                          pick() {
                              const f = this.$refs.cv.files[0];
                              this.file = f ? { name: f.name, size: f.size < 1048576 ? Math.max(1, Math.round(f.size / 1024)) + ' KB' : (f.size / 1048576).toFixed(1) + ' MB' } : null;
                          },
                          clear() { this.$refs.cv.value = ''; this.file = null },
                          advance() {
                              for (const field of this.$refs['step' + this.step].querySelectorAll('input, textarea, select')) {
                                  if (! field.checkValidity()) { field.reportValidity(); return }
                              }
                              this.step = Math.min(this.step + 1, this.last);
                              this.$refs.top.scrollIntoView({ behavior: 'smooth', block: 'start' });
                          },
                          back() {
                              this.step = Math.max(this.step - 1, 1);
                              this.$refs.top.scrollIntoView({ behavior: 'smooth', block: 'start' });
                          },
                      }">
                    @csrf

                    {{-- Honeypot --}}
                    <div class="hidden" aria-hidden="true">
                        <label for="website">{{ __('site.forms.website') }}</label>
                        <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    {{-- Progress: three steps on a dotted track that fills as you go --}}
                    <div x-ref="top" class="relative isolate overflow-hidden border-b border-ink-100 bg-ink-50/70 px-6 py-6 sm:px-10 sm:py-7">
                        <div class="pointer-events-none absolute inset-0 -z-10 text-brand-700 grid-overlay opacity-40 [mask-image:linear-gradient(to_left,black,transparent_70%)]" aria-hidden="true"></div>

                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <h2 class="font-display text-2xl font-bold text-ink-950 sm:text-[28px]">{{ __('site.internship.form_title') }}</h2>
                            <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white px-3.5 py-1.5 text-[12.5px] font-medium text-ink-600">
                                <x-ui-icon name="shield" class="h-4 w-4 text-brand-600" /> {{ __('site.internship.confidential') }}
                            </span>
                        </div>

                        <div class="relative mt-8">
                            <div class="absolute left-[16.667%] right-[16.667%] top-6 -translate-y-1/2 border-t-[3px] border-dotted border-ink-300" aria-hidden="true">
                                <div class="absolute left-0 -top-[3px] border-t-[3px] border-dotted border-brand-600 transition-[width] duration-700 ease-[cubic-bezier(0.22,1,0.36,1)]"
                                     :style="`width: ${(step - 1) / (last - 1) * 100}%`"></div>
                            </div>

                            <ol class="relative grid grid-cols-3">
                                @foreach ([[1, __('site.internship.step_you'), 'user'], [2, __('site.internship.step_academic'), 'academic'], [3, __('site.internship.step_internship'), 'briefcase']] as [$n, $label, $icon])
                                    <li class="flex flex-col items-center text-center">
                                        <button type="button" @click="if (step > {{ $n }}) step = {{ $n }}"
                                                :disabled="step <= {{ $n }}"
                                                :aria-current="step === {{ $n }} ? 'step' : null"
                                                class="relative flex h-12 w-12 items-center justify-center rounded-full border-2 transition-all duration-500 disabled:cursor-default"
                                                :class="step > {{ $n }}
                                                    ? 'border-brand-600 bg-brand-600 text-white shadow-[0_10px_24px_-10px_var(--color-brand-600)] hover:scale-105'
                                                    : (step === {{ $n }}
                                                        ? 'scale-110 border-brand-600 bg-white text-brand-600 shadow-[0_0_0_6px_var(--color-brand-100),0_14px_30px_-12px_var(--color-brand-600)]'
                                                        : 'border-ink-200 bg-white text-ink-400')">
                                            <span x-show="step === {{ $n }}" class="absolute inset-0 animate-ping rounded-full bg-brand-400/25" aria-hidden="true"></span>
                                            <x-ui-icon name="check" class="h-5 w-5" stroke="2.6" x-show="step > {{ $n }}" x-cloak />
                                            <x-ui-icon :name="$icon" class="h-5 w-5" x-show="step <= {{ $n }}" />
                                        </button>
                                        <span class="mt-3 text-[10.5px] font-semibold uppercase tracking-[0.16em] transition-colors"
                                              :class="step >= {{ $n }} ? 'text-brand-600' : 'text-ink-400'">{{ __('site.consultancy.step', ['number' => $n]) }}</span>
                                        <span class="mt-0.5 block font-display text-[12.5px] font-semibold leading-snug transition-colors sm:text-[14px]"
                                              :class="step >= {{ $n }} ? 'text-ink-950' : 'text-ink-400'">{{ $label }}</span>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>

                    <div class="px-6 py-8 sm:px-10 sm:py-10">

                        {{-- Step 1 — About you --}}
                        <fieldset x-ref="step1" x-show="step === 1" x-transition.opacity>
                            <legend class="sr-only">{{ __('site.internship.step_you') }}</legend>
                            <p class="font-display text-[19px] font-bold text-ink-950">{{ __('site.internship.step_you') }}</p>
                            <p class="mt-1.5 text-[14px] muted">{{ __('site.internship.you_note') }}</p>

                            <div class="mt-7 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                                <x-form.field name="name" :label="__('site.forms.full_name')" icon="user" required autocomplete="name" x-model="fields.name" />
                                <x-form.field name="email" :label="__('site.forms.email')" type="email" icon="mail" required autocomplete="email" />
                                <x-form.field name="phone" :label="__('site.internship.phone')" type="tel" icon="phone" required
                                              placeholder="01XXXXXXXXX" autocomplete="tel" />
                            </div>
                        </fieldset>

                        {{-- Step 2 — Academic --}}
                        <fieldset x-ref="step2" x-show="step === 2" x-cloak x-transition.opacity>
                            <legend class="sr-only">{{ __('site.internship.step_academic') }}</legend>
                            <p class="font-display text-[19px] font-bold text-ink-950">{{ __('site.internship.step_academic') }}</p>
                            <p class="mt-1.5 text-[14px] muted">{{ __('site.internship.academic_note') }}</p>

                            <div class="mt-7 grid gap-5 sm:grid-cols-2">
                                {{-- UGV filled in; a student from elsewhere types over it --}}
                                <div class="sm:col-span-2">
                                    <label for="university" class="mb-2 block text-[13px] font-semibold text-ink-700">
                                        {{ __('site.internship.university') }} <span class="text-brand-600">*</span>
                                    </label>
                                    <div class="group relative">
                                        <x-ui-icon name="building" class="pointer-events-none absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-ink-400 transition-colors group-focus-within:text-brand-600" />
                                        <input id="university" type="text" name="university" required maxlength="180" autocomplete="organization"
                                               value="{{ old('university', __('site.internship.university_default')) }}"
                                               @class([
                                                   'w-full rounded-2xl border bg-ink-50/60 py-3.5 pl-11 pr-4 text-[15px] text-ink-900 transition focus:bg-white focus:outline-none focus:ring-4',
                                                   'border-red-300 focus:border-red-400 focus:ring-red-100' => $errors->has('university'),
                                                   'border-ink-200 hover:border-ink-300 focus:border-brand-400 focus:ring-brand-100' => ! $errors->has('university'),
                                               ])>
                                    </div>
                                    @error('university')
                                        <p class="mt-1.5 text-[13px] text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Free text, with the UGV departments offered as suggestions --}}
                                <div>
                                    <x-form.field name="department" :label="__('site.internship.department')" icon="grid" required
                                                  :placeholder="__('site.internship.department_placeholder')" list="internship-departments" />
                                    <datalist id="internship-departments">
                                        @foreach ($departments as $label)
                                            <option value="{{ $label }}"></option>
                                        @endforeach
                                    </datalist>
                                </div>

                                <x-form.field name="programme" :label="__('site.internship.programme')" icon="academic" required
                                              :placeholder="__('site.internship.programme_placeholder')" />

                                <div>
                                    <label for="year_level" class="mb-2 block text-[13px] font-semibold text-ink-700">
                                        {{ __('site.internship.year') }} <span class="text-brand-600">*</span>
                                    </label>
                                    <div class="group relative">
                                        <x-ui-icon name="calendar" class="pointer-events-none absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-ink-400 transition-colors group-focus-within:text-brand-600" />
                                        <select id="year_level" name="year_level" required class="{{ $select('year_level') }}">
                                            <option value="">{{ __('site.internship.select') }}</option>
                                            @foreach ($years as $value => $label)
                                                <option value="{{ $value }}" @selected((string) old('year_level') === (string) $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <x-ui-icon name="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                                    </div>
                                    @error('year_level')
                                        <p class="mt-1.5 text-[13px] text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <x-form.field name="student_id" :label="__('site.internship.student_id').' '.__('site.internship.optional')" icon="key" autocomplete="off" />
                                    <x-form.field name="cgpa" :label="__('site.internship.cgpa').' '.__('site.internship.optional')" icon="star"
                                                  type="number" step="0.01" min="0" max="4" :placeholder="__('site.internship.cgpa_placeholder')" />
                                </div>
                            </div>
                        </fieldset>

                        {{-- Step 3 — The internship --}}
                        <fieldset x-ref="step3" x-show="step === 3" x-cloak x-transition.opacity>
                            <legend class="sr-only">{{ __('site.internship.step_internship') }}</legend>
                            <p class="font-display text-[19px] font-bold text-ink-950">{{ __('site.internship.step_internship') }}</p>
                            <p class="mt-1.5 text-[14px] muted">{{ __('site.internship.internship_note') }}</p>

                            {{-- Track --}}
                            <div class="mt-7 max-w-xl">
                                <label for="track" class="mb-2 block text-[13px] font-semibold text-ink-700">
                                    {{ __('site.internship.track') }} <span class="text-brand-600">*</span>
                                </label>
                                <div class="group relative">
                                    <x-ui-icon name="briefcase" class="pointer-events-none absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-ink-400 transition-colors group-focus-within:text-brand-600" />
                                    <select id="track" name="track" required x-model="fields.track" class="{{ $select('track') }}">
                                        <option value="">{{ __('site.internship.select') }}</option>
                                        @foreach ($tracks as $key => $label)
                                            <option value="{{ $key }}" @selected(old('track') === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <x-ui-icon name="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                                </div>
                                @error('track')
                                    <p class="mt-1.5 text-[13px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Duration, start and mode --}}
                            <div class="mt-7 grid gap-5 sm:grid-cols-3">
                                @foreach ([['duration_months', __('site.internship.duration'), 'clock', $durations], ['mode', __('site.internship.mode'), 'globe', $modes]] as [$name, $label, $icon, $options])
                                    <div>
                                        <label for="{{ $name }}" class="mb-2 block text-[13px] font-semibold text-ink-700">
                                            {{ $label }} <span class="text-brand-600">*</span>
                                        </label>
                                        <div class="group relative">
                                            <x-ui-icon :name="$icon" class="pointer-events-none absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-ink-400 transition-colors group-focus-within:text-brand-600" />
                                            <select id="{{ $name }}" name="{{ $name }}" required class="{{ $select($name) }}">
                                                <option value="">{{ __('site.internship.select') }}</option>
                                                @foreach ($options as $value => $optionLabel)
                                                    <option value="{{ $value }}" @selected((string) old($name) === (string) $value)>{{ $optionLabel }}</option>
                                                @endforeach
                                            </select>
                                            <x-ui-icon name="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                                        </div>
                                        @error($name)
                                            <p class="mt-1.5 text-[13px] text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                @endforeach

                                <x-form.field name="start_date" :label="__('site.internship.start_date')" type="date" icon="calendar" required
                                              :min="now()->toDateString()" />
                            </div>

                            {{-- Skills and motivation --}}
                            <div class="mt-7 grid gap-5">
                                <div>
                                    <label for="skills" class="mb-2 block text-[13px] font-semibold text-ink-700">
                                        {{ __('site.internship.skills') }} <span class="font-normal muted">{{ __('site.internship.optional') }}</span>
                                    </label>
                                    <textarea id="skills" name="skills" rows="2" maxlength="2000"
                                              placeholder="{{ __('site.internship.skills_placeholder') }}"
                                              class="w-full rounded-2xl border border-ink-200 bg-ink-50/60 px-4 py-3.5 text-[15px] leading-relaxed text-ink-900 placeholder:text-ink-400 transition hover:border-ink-300 focus:border-brand-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-brand-100">{{ old('skills') }}</textarea>
                                </div>

                                <div>
                                    <label for="motivation" class="mb-2 block text-[13px] font-semibold text-ink-700">
                                        {{ __('site.internship.motivation') }} <span class="text-brand-600">*</span>
                                    </label>
                                    <textarea id="motivation" name="motivation" rows="5" required minlength="30" maxlength="3000"
                                              placeholder="{{ __('site.internship.motivation_placeholder') }}"
                                              @class([
                                                  'w-full rounded-2xl border bg-ink-50/60 px-4 py-3.5 text-[15px] leading-relaxed text-ink-900 placeholder:text-ink-400 transition focus:bg-white focus:outline-none focus:ring-4',
                                                  'border-red-300 focus:border-red-400 focus:ring-red-100' => $errors->has('motivation'),
                                                  'border-ink-200 hover:border-ink-300 focus:border-brand-400 focus:ring-brand-100' => ! $errors->has('motivation'),
                                              ])>{{ old('motivation') }}</textarea>
                                    @error('motivation')
                                        <p class="mt-1.5 text-[13px] text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- CV (drag & drop) and a link --}}
                            <div class="mt-7 grid gap-5 lg:grid-cols-[1.3fr_1fr]">
                                <div>
                                    <p class="mb-2 text-[13px] font-semibold text-ink-700">{{ __('site.internship.cv') }} <span class="text-brand-600">*</span></p>
                                    <label for="cv"
                                           @dragover.prevent="dragging = true" @dragenter.prevent="dragging = true"
                                           @dragleave.prevent="dragging = false"
                                           @drop.prevent="dragging = false; take($event.dataTransfer.files)"
                                           class="group flex min-h-[11rem] cursor-pointer flex-col items-center justify-center gap-3 rounded-[1.5rem] border-2 border-dashed px-6 py-7 text-center transition duration-300"
                                           :class="dragging
                                               ? 'scale-[1.01] border-brand-500 bg-brand-50 ring-8 ring-brand-100'
                                               : (file ? 'border-brand-300 bg-brand-50/60' : 'border-ink-200 bg-ink-50/40 hover:border-brand-300 hover:bg-brand-50/40')">
                                        <div x-show="! file" class="flex flex-col items-center">
                                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white text-brand-600 ring-1 ring-ink-100 transition duration-500 group-hover:-translate-y-1"
                                                  :class="dragging && '-translate-y-1 bg-brand-600 !text-white'">
                                                <x-ui-icon name="upload" class="h-6 w-6" />
                                            </span>
                                            <span class="mt-3 font-display text-[15.5px] font-bold text-ink-950">{{ __('site.internship.cv_drop') }}</span>
                                            <span class="mt-1 text-[13px] text-ink-500">
                                                {{ __('site.internship.cv_or') }}
                                                <span class="font-semibold text-brand-700 underline-offset-4 group-hover:underline">{{ __('site.internship.cv_browse') }}</span>
                                            </span>
                                            <span class="mt-3 rounded-full bg-white px-3 py-1 text-[11.5px] font-medium text-ink-500 ring-1 ring-ink-100">{{ __('site.internship.cv_types') }}</span>
                                        </div>

                                        <div x-show="file" x-cloak class="flex w-full max-w-xs flex-col items-center">
                                            <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-600 text-white">
                                                <x-ui-icon name="document" class="h-6 w-6" />
                                            </span>
                                            <span class="mt-3 w-full truncate font-display text-[15px] font-bold text-ink-950" x-text="file?.name"></span>
                                            <span class="mt-0.5 text-[12.5px] text-ink-500" x-text="file?.size"></span>
                                            <span class="mt-3 flex items-center gap-2">
                                                <span class="rounded-full border border-ink-200 bg-white px-3 py-1 text-[12px] font-semibold text-ink-700">{{ __('site.internship.cv_change') }}</span>
                                                <button type="button" @click.prevent="clear()"
                                                        class="rounded-full px-3 py-1 text-[12px] font-semibold text-red-600 transition hover:bg-red-50">{{ __('site.internship.cv_remove') }}</button>
                                            </span>
                                        </div>
                                    </label>
                                    <input id="cv" x-ref="cv" type="file" name="cv" required class="sr-only" @change="pick()" accept=".pdf,.doc,.docx">
                                    @error('cv')
                                        <p class="mt-1.5 text-[13px] text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <x-form.field name="portfolio_url" :label="__('site.internship.portfolio').' '.__('site.internship.optional')" type="url" icon="link"
                                                  :placeholder="__('site.internship.portfolio_placeholder')" />
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    {{-- Navigation --}}
                    <div class="flex flex-col gap-4 border-t border-ink-100 bg-ink-50/70 px-6 py-6 sm:flex-row sm:items-center sm:justify-between sm:px-10">
                        <p class="text-[13px] muted">
                            <span class="font-semibold text-brand-600">*</span> {{ __('site.consultancy.required_note') }} ·
                            {!! __('site.consultancy.step_of', ['current' => '<span x-text="step">1</span>', 'total' => 3]) !!}
                        </p>

                        <div class="flex flex-wrap gap-3">
                            <button type="button" x-show="step > 1" x-cloak @click="back()" class="btn-ghost">
                                <x-ui-icon name="arrow-left" class="h-4 w-4" /> {{ __('site.consultancy.back') }}
                            </button>
                            <button type="button" x-show="step < last" @click="advance()" class="btn-primary group justify-center">
                                {{ __('site.consultancy.continue') }}
                                <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" />
                            </button>
                            <button type="submit" x-show="step === last" x-cloak class="btn-primary group justify-center">
                                {{ __('site.internship.submit') }}
                                <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" />
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- How it works, and the tracks --}}
            <aside class="space-y-5 lg:sticky lg:top-28 lg:self-start">
                <div class="reveal relative isolate overflow-hidden rounded-[2rem] bg-navy-800 p-6 text-white sm:p-7">
                    <div class="pointer-events-none absolute -right-12 -top-12 -z-10 h-40 w-40 rounded-full bg-brand-500/30 blur-3xl" aria-hidden="true"></div>
                    <h2 class="font-display text-[18px] font-bold !text-white">{{ __('site.internship.aside_title') }}</h2>
                    <ol class="mt-5 space-y-4">
                        @foreach (__('site.internship.aside_steps') as [$title, $body])
                            <li class="flex items-start gap-3">
                                <span @class([
                                    'flex h-8 w-8 shrink-0 items-center justify-center rounded-full font-numeric text-[12px] font-bold',
                                    'bg-gold-300 text-navy-900' => $loop->last,
                                    'bg-white/10 text-white ring-1 ring-white/20' => ! $loop->last,
                                ])>{{ \App\Support\Numerals::localize((string) $loop->iteration) }}</span>
                                <span class="pt-0.5">
                                    <span class="block font-display text-[14.5px] font-bold !text-white">{{ $title }}</span>
                                    <span class="mt-0.5 block text-[13px] leading-relaxed text-white/70">{{ $body }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </div>

                <div class="reveal rounded-[2rem] border border-ink-100 bg-white p-6 sm:p-7">
                    <h2 class="font-display text-[16px] font-bold text-ink-950">{{ __('site.internship.aside_tracks') }}</h2>
                    <ul class="mt-4 space-y-2.5">
                        @foreach ($tracks as $key => $label)
                            <li class="flex items-center gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                    <x-ui-icon :name="\App\Models\StudentMembership::TRACK_ICONS[$key] ?? 'sparkles'" class="h-4 w-4" />
                                </span>
                                <span class="text-[13.5px] leading-snug text-ink-800">{{ $label }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </aside>
        </div>
    </section>
</x-layouts.app>
