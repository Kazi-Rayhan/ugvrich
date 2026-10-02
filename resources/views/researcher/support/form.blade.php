<x-layouts.researcher :title="__('researcher.support.form.title')">

    <x-portal-heading :title="__('researcher.support.form.title')" :lead="__('researcher.support.lead')" />

    <form method="POST" action="{{ route('researcher.support.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        @if ($errors->any())
            <div class="rounded-2xl border border-rose-200 bg-rose-50 p-5" role="alert">
                <ul class="space-y-1 text-[13.5px] leading-relaxed text-rose-800">
                    @foreach ($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- -------------------------------------------------- who is asking --}}
        <fieldset class="field-group">
            <legend class="field-legend">{{ __('researcher.support.form.who') }}</legend>
            <p class="field-help">{{ __('researcher.support.form.prefilled') }}</p>

            <div class="mt-6 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label for="name" class="field-label">{{ __('research_hub.forms.name') }} <span class="text-brand-600">*</span></label>
                    <input id="name" name="name" type="text" required value="{{ old('name', $support->name) }}" class="field-input mt-2">
                </div>

                <div>
                    <label for="email" class="field-label">{{ __('research_hub.forms.email') }} <span class="text-brand-600">*</span></label>
                    <input id="email" name="email" type="email" required value="{{ old('email', $support->email) }}" class="field-input mt-2">
                </div>

                <div>
                    <label for="phone" class="field-label">{{ __('research_hub.forms.phone') }}</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $support->phone) }}" class="field-input mt-2">
                </div>

                <div>
                    <label for="role" class="field-label">{{ __('research_hub.forms.role') }}</label>
                    <input id="role" name="role" type="text" value="{{ old('role', $support->role) }}" class="field-input mt-2">
                </div>

                <div class="sm:col-span-2 xl:col-span-1">
                    <label for="department" class="field-label">{{ __('research_hub.forms.department') }}</label>
                    <select id="department" name="department" class="field-input mt-2">
                        <option value="">{{ __('research_hub.forms.choose') }}</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department }}" @selected(old('department', $support->department) === $department)>{{ $department }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </fieldset>

        {{-- ------------------------------------------------- what is needed --}}
        <fieldset class="field-group">
            <legend class="field-legend">{{ __('researcher.support.form.what') }} <span class="text-brand-600">*</span></legend>
            <p class="field-help">{{ __('researcher.support.form.what_help') }}</p>

            @php $chosen = (array) old('support_types', []); @endphp

            <div class="mt-5 grid gap-2.5 sm:grid-cols-2 xl:grid-cols-3">
                {{-- The same tiles as the public form: the whole tile is the
                     control, and the value stored is the English label whatever
                     language the tile is read in. --}}
                @foreach ($types as $value => $label)
                    <label class="group relative flex cursor-pointer items-center gap-3 rounded-2xl border border-ink-200 bg-white px-4 py-3.5 transition duration-200
                                  hover:border-ink-300 hover:bg-ink-50/60
                                  has-[:checked]:border-brand-500 has-[:checked]:bg-brand-50
                                  has-[:checked]:shadow-[0_8px_22px_-16px_var(--color-brand-600)]
                                  has-[:focus-visible]:ring-4 has-[:focus-visible]:ring-brand-500/15">
                        <input type="checkbox" name="support_types[]" value="{{ $value }}"
                               @checked(in_array($value, $chosen, true))
                               class="peer sr-only">

                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-ink-300 bg-white transition duration-200
                                     peer-checked:border-brand-600 peer-checked:bg-brand-600">
                            <x-ui-icon name="check" class="h-3 w-3 text-white opacity-0 transition-opacity duration-200 peer-checked:opacity-100" stroke="3" />
                        </span>

                        <span class="text-[13.5px] font-medium leading-snug text-ink-700 transition peer-checked:font-semibold peer-checked:text-brand-800">{{ $label }}</span>
                    </label>
                @endforeach
            </div>

            @error('support_types') <p class="field-error">{{ $message }}</p> @enderror
        </fieldset>

        {{-- ------------------------------------------------------- the work --}}
        <fieldset class="field-group">
            <legend class="field-legend">{{ __('researcher.support.form.about') }}</legend>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="title" class="field-label">{{ __('researcher.support.form.work_title') }} <span class="text-brand-600">*</span></label>
                    <input id="title" name="title" type="text" required value="{{ old('title', $support->title) }}" class="field-input mt-2">
                </div>

                <div class="sm:col-span-2">
                    <label for="details" class="field-label">{{ __('researcher.support.form.details') }} <span class="text-brand-600">*</span></label>
                    <textarea id="details" name="details" rows="7" required class="field-input mt-2">{{ old('details', $support->details) }}</textarea>
                    <p class="field-help">{{ __('researcher.support.form.details_help') }}</p>
                </div>

                <div class="contents">
                    <div>
                        <label for="stage" class="field-label">{{ __('researcher.support.form.stage') }}</label>
                        <select id="stage" name="stage" class="field-input mt-2">
                            <option value="">{{ __('research_hub.forms.choose') }}</option>
                            @foreach ($stages as $value => $label)
                                <option value="{{ $value }}" @selected(old('stage', $support->stage) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="needed_by" class="field-label">{{ __('researcher.support.form.needed_by') }}</label>
                        <input id="needed_by" name="needed_by" type="date" min="{{ now()->toDateString() }}" value="{{ old('needed_by') }}" class="field-input mt-2">
                        <p class="field-help">{{ __('researcher.support.form.needed_by_help') }}</p>
                    </div>
                </div>

                <div class="sm:col-span-2">
                    <label for="document" class="field-label">{{ __('researcher.support.form.document') }}</label>
                    <input id="document" name="document" type="file" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip"
                           class="field-input mt-2 file:me-4 file:rounded-lg file:border-0 file:bg-ink-100 file:px-3 file:py-1.5 file:text-[12.5px] file:font-semibold file:text-ink-700">
                    <p class="field-help">{{ __('researcher.support.form.document_help') }}</p>
                </div>
            </div>
        </fieldset>

        <div class="flex flex-wrap items-center gap-4">
            <button type="submit" class="btn-lead group">
                <span class="relative">{{ __('researcher.support.form.submit') }}</span>
                <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
            </button>

            <a href="{{ route('researcher.support.index') }}" class="btn-ghost">
                {{ __('researcher.support.back') }}
            </a>
        </div>
    </form>
</x-layouts.researcher>
