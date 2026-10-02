<x-layouts.app :title="__('research_hub.forms.support.title')" :description="__('research_hub.forms.support.lead')">

    @php
        // Defined once in app.css, so every field on both forms matches.
        $field = 'field-input';
        $labelCls = 'field-label';
        $help = 'field-help';
        $legend = 'field-legend';
        $error = 'field-error';
    @endphp

    <section class="relative isolate overflow-hidden bg-navy-700 py-14 text-white sm:py-20">
        <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.12]" aria-hidden="true"></div>

        <div class="container-rich">
            <nav class="flex flex-wrap items-center gap-2 text-[13px] text-white/60" aria-label="Breadcrumb">
                <a href="{{ route('research') }}" class="transition hover:text-white">{{ __('site.nav.research') }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-white/85">{{ __('research_hub.forms.support.eyebrow') }}</span>
            </nav>

            <h1 class="mt-6 max-w-3xl font-display text-[32px] font-bold leading-[1.05] tracking-[-0.02em] !text-white sm:text-[46px]">
                {{ __('research_hub.forms.support.title') }}
            </h1>
            <p class="mt-5 max-w-2xl text-[16.5px] leading-[1.8] text-white/75">{{ __('research_hub.forms.support.lead') }}</p>
        </div>
    </section>

    <section class="bg-white py-14 sm:py-20">
        <div class="container-rich max-w-3xl">
            @if ($errors->any())
                <div class="mb-8 flex gap-4 rounded-2xl border border-rose-200 bg-rose-50 p-5" role="alert">
                    <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-rose-600 text-white">
                        <x-ui-icon name="x" class="h-4 w-4" stroke="2.4" />
                    </span>
                    <ul class="space-y-1.5 text-[14px] leading-relaxed text-rose-800">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('research.support.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                {{-- Bait for robots; a person never sees it, so a filled one is not a person. --}}
                <div class="hidden" aria-hidden="true">
                    <label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
                </div>

                {{-- ------------------------------------------------ you --}}
                <fieldset class="field-group">
                    <legend class="{{ $legend }}">{{ __('research_hub.forms.you') }}</legend>

                    <div class="mt-6 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="{{ $labelCls }}">{{ __('research_hub.forms.name') }} <span class="text-brand-600">*</span></label>
                            <input id="name" name="name" type="text" required value="{{ old('name') }}" class="mt-2 {{ $field }}">
                            @error('name') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="{{ $labelCls }}">{{ __('research_hub.forms.email') }} <span class="text-brand-600">*</span></label>
                            <input id="email" name="email" type="email" required value="{{ old('email') }}" class="mt-2 {{ $field }}">
                            @error('email') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="phone" class="{{ $labelCls }}">{{ __('research_hub.forms.phone') }}</label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" class="mt-2 {{ $field }}">
                        </div>

                        <div>
                            <label for="role" class="{{ $labelCls }}">{{ __('research_hub.forms.role') }}</label>
                            <select id="role" name="role" class="mt-2 {{ $field }}">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                @foreach (__('research_hub.forms.roles') as $role)
                                    <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="sm:col-span-2">
                            <label for="department" class="{{ $labelCls }}">{{ __('research_hub.forms.department') }}</label>
                            <select id="department" name="department" class="mt-2 {{ $field }}">
                                <option value="">{{ __('research_hub.forms.choose') }}</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department }}" @selected(old('department') === $department)>{{ $department }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>

                {{-- ------------------------------------- what is needed --}}
                <fieldset class="field-group">
                    <legend class="{{ $legend }}">{{ __('research_hub.forms.support.what') }}</legend>
                    <p class="{{ $help }}">{{ __('research_hub.forms.support.what_help') }}</p>

                    <div class="mt-5 grid gap-2.5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach (__('research_hub.support.items') as $i => $item)
                            {{-- The whole tile is the control, and it says so by
                                 changing state rather than by a tick alone. --}}
                            <label class="group relative flex cursor-pointer items-center gap-3 rounded-2xl border border-ink-200 bg-white px-4 py-3.5 transition duration-200
                                          hover:border-ink-300 hover:bg-ink-50/60
                                          has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50
                                          has-[:checked]:shadow-[0_8px_22px_-16px_var(--color-brand-600)]
                                          has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-brand-500/15">
                                <input type="checkbox" name="support_types[]" value="{{ $item }}"
                                       @checked(in_array($item, (array) old('support_types', []), true))
                                       class="peer sr-only">

                                <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-ink-300 bg-white transition duration-200
                                             peer-checked:border-brand-600 peer-checked:bg-brand-600">
                                    <x-ui-icon name="check" class="h-3 w-3 text-white opacity-0 transition-opacity duration-200 peer-checked:opacity-100" stroke="3" />
                                </span>

                                <span class="text-[13.5px] font-medium leading-snug text-ink-700 transition peer-checked:font-semibold peer-checked:text-brand-800">{{ $item }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('support_types') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </fieldset>

                {{-- ------------------------------------------ the work --}}
                <fieldset class="field-group">
                    <legend class="{{ $legend }}">{{ __('research_hub.forms.support.about') }}</legend>

                    <div class="mt-6 space-y-5">
                        <div>
                            <label for="title" class="{{ $labelCls }}">{{ __('research_hub.forms.support.work_title') }} <span class="text-brand-600">*</span></label>
                            <input id="title" name="title" type="text" required value="{{ old('title') }}" class="mt-2 {{ $field }}">
                            @error('title') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="details" class="{{ $labelCls }}">{{ __('research_hub.forms.support.details') }} <span class="text-brand-600">*</span></label>
                            <textarea id="details" name="details" rows="6" required class="mt-2 {{ $field }}">{{ old('details') }}</textarea>
                            <p class="{{ $help }}">{{ __('research_hub.forms.support.details_help') }}</p>
                            @error('details') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>

                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="stage" class="{{ $labelCls }}">{{ __('research_hub.forms.support.stage') }}</label>
                                <select id="stage" name="stage" class="mt-2 {{ $field }}">
                                    <option value="">{{ __('research_hub.forms.choose') }}</option>
                                    @foreach (__('research_hub.forms.support.stages') as $stage)
                                        <option value="{{ $stage }}" @selected(old('stage') === $stage)>{{ $stage }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="needed_by" class="{{ $labelCls }}">{{ __('research_hub.forms.support.needed_by') }}</label>
                                <input id="needed_by" name="needed_by" type="date" min="{{ now()->toDateString() }}" value="{{ old('needed_by') }}" class="mt-2 {{ $field }}">
                                @error('needed_by') <p class="{{ $error }}">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="document" class="{{ $labelCls }}">{{ __('research_hub.forms.document') }}</label>
                            <input id="document" name="document" type="file" class="mt-2 {{ $field }} file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-[13px] file:font-semibold file:text-brand-700">
                            <p class="{{ $help }}">{{ __('research_hub.forms.document_help') }}</p>
                            @error('document') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </fieldset>

                <div class="flex flex-wrap items-center gap-5 pt-2">
                    <button type="submit" class="btn-lead group">
                        {{ __('research_hub.forms.support.submit') }} <x-ui-icon name="arrow-right" class="h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                    </button>
                    <a href="{{ route('research.proposal') }}" class="text-[13.5px] font-semibold text-brand-700 transition hover:text-brand-600">
                        {{ __('research_hub.forms.thanks.other_proposal') }}
                    </a>
                </div>
            </form>
        </div>
    </section>
</x-layouts.app>
