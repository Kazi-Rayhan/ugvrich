<x-layouts.app :title="__('site.membership.meta_title')"
               :description="__('site.membership.meta_description')">

    <x-page-hero
        :eyebrow="__('site.membership.hero_eyebrow')"
        :title="__('site.membership.hero_title')"
        :lead="__('site.membership.hero_lead')"
        :breadcrumbs="[__('site.membership.meta_title') => null]" />

    <section class="bg-white py-16 sm:py-20">
        <div class="container-rich mx-auto max-w-3xl">

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

                <form action="{{ route('membership.store') }}" method="POST"
                      class="reveal overflow-hidden rounded-[2rem] border border-ink-100 bg-white shadow-[0_30px_70px_-50px_rgba(7,20,38,0.45)]">
                    @csrf

                    <div class="hidden" aria-hidden="true">
                        <label for="website">{{ __('site.forms.website') }}</label>
                        <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="border-b border-ink-100 bg-ink-50/60 px-6 py-5 sm:px-8">
                        <h2 class="font-display text-[19px] font-bold text-ink-950">{{ __('site.membership.form_title') }}</h2>
                        <p class="mt-1 text-[13.5px] muted">{{ __('site.membership.form_note') }}</p>
                    </div>

                    <div class="space-y-6 px-6 py-7 sm:px-8">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <x-form.field name="name" :label="__('site.forms.full_name')" icon="user" required autocomplete="name" />
                            <x-form.field name="student_id" :label="__('site.membership.student_id')" icon="key" required
                                          :placeholder="__('site.membership.student_id_placeholder')" autocomplete="off" />

                            <div>
                                <label for="semester" class="mb-2 block text-[13px] font-semibold text-ink-700">
                                    {{ __('site.membership.semester') }} <span class="text-brand-600">*</span>
                                </label>
                                <div class="group relative">
                                    <x-ui-icon name="academic" class="pointer-events-none absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-ink-400 transition-colors group-focus-within:text-brand-600" />
                                    <select id="semester" name="semester" required
                                            @class([
                                                'w-full appearance-none rounded-2xl border bg-ink-50/60 py-3.5 pl-11 pr-10 text-[15px] text-ink-900 transition focus:bg-white focus:outline-none focus:ring-4',
                                                'border-red-300 focus:border-red-400 focus:ring-red-100' => $errors->has('semester'),
                                                'border-ink-200 hover:border-ink-300 focus:border-brand-400 focus:ring-brand-100' => ! $errors->has('semester'),
                                            ])>
                                        <option value="">{{ __('site.membership.semester_placeholder') }}</option>
                                        @for ($semester = 1; $semester <= 8; $semester++)
                                            <option value="{{ $semester }}" @selected((string) old('semester') === (string) $semester)>
                                                {{ __('site.membership.semester_option', ['n' => $semester]) }}
                                            </option>
                                        @endfor
                                    </select>
                                    <x-ui-icon name="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                                </div>
                                @error('semester')
                                    <p class="mt-1.5 text-[13px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="department" class="mb-2 block text-[13px] font-semibold text-ink-700">
                                    {{ __('site.membership.department') }} <span class="text-brand-600">*</span>
                                </label>
                                <div class="group relative">
                                    <x-ui-icon name="building" class="pointer-events-none absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-ink-400 transition-colors group-focus-within:text-brand-600" />
                                    <select id="department" name="department" required
                                            @class([
                                                'w-full appearance-none rounded-2xl border bg-ink-50/60 py-3.5 pl-11 pr-10 text-[15px] text-ink-900 transition focus:bg-white focus:outline-none focus:ring-4',
                                                'border-red-300 focus:border-red-400 focus:ring-red-100' => $errors->has('department'),
                                                'border-ink-200 hover:border-ink-300 focus:border-brand-400 focus:ring-brand-100' => ! $errors->has('department'),
                                            ])>
                                        <option value="">{{ __('site.membership.department_placeholder') }}</option>
                                        @foreach ($departments as $code => $label)
                                            <option value="{{ $code }}" @selected(old('department') === $code)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <x-ui-icon name="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                                </div>
                                @error('department')
                                    <p class="mt-1.5 text-[13px] text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <x-form.field name="phone" :label="__('site.membership.phone')" type="tel" icon="phone" required
                                          placeholder="01XXXXXXXXX" autocomplete="tel" />
                            <x-form.field name="email" :label="__('site.forms.email')" type="email" icon="mail" required
                                          :placeholder="__('site.membership.email_placeholder')" autocomplete="email" />
                        </div>

                        {{-- The track, as a dropdown like the semester and department --}}
                        <div>
                            <label for="track" class="mb-2 block text-[13px] font-semibold text-ink-700">
                                {{ __('site.membership.track_label') }} <span class="text-brand-600">*</span>
                            </label>
                            <div class="group relative">
                                <x-ui-icon name="briefcase" class="pointer-events-none absolute left-4 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-ink-400 transition-colors group-focus-within:text-brand-600" />
                                <select id="track" name="track" required
                                        @class([
                                            'w-full appearance-none rounded-2xl border bg-ink-50/60 py-3.5 pl-11 pr-10 text-[15px] text-ink-900 transition focus:bg-white focus:outline-none focus:ring-4',
                                            'border-red-300 focus:border-red-400 focus:ring-red-100' => $errors->has('track'),
                                            'border-ink-200 hover:border-ink-300 focus:border-brand-400 focus:ring-brand-100' => ! $errors->has('track'),
                                        ])>
                                    <option value="">{{ __('site.membership.track_placeholder') }}</option>
                                    @foreach ($tracks as $key => $label)
                                        <option value="{{ $key }}" @selected(old('track') === $key)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <x-ui-icon name="chevron-down" class="pointer-events-none absolute right-4 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400" />
                            </div>
                            <p class="mt-1.5 text-[13px] muted">{{ __('site.membership.track_note') }}</p>
                            @error('track')
                                <p class="mt-1.5 text-[13px] text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <button type="submit" class="btn-primary group w-full sm:w-auto">
                                {{ __('site.membership.submit') }}
                                <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" />
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</x-layouts.app>
