<x-layouts.app :title="__('site.thanks.consultancy_title')"
               :description="__('site.thanks.consultancy_description')">

    @php
        $firstName = \Illuminate\Support\Str::of($submission['name'] ?? '')
            ->replaceMatches('/^(Dr\.?|Prof\.?|Mr\.?|Ms\.?|Mrs\.?|Md\.?|Engr\.?)\s+/i', '')
            ->explode(' ')->first();
        $email = $site->get('contact_email');
    @endphp

    <section class="relative isolate overflow-hidden bg-ink-50 py-16 sm:py-24">
        <div class="pointer-events-none absolute inset-0 -z-10 text-brand-700 grid-overlay opacity-40 [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute left-1/2 top-24 -z-10 h-[34rem] w-[34rem] -translate-x-1/2 rounded-full border border-brand-100" aria-hidden="true"></div>
        <div class="pointer-events-none absolute left-1/2 top-44 -z-10 h-[22rem] w-[22rem] -translate-x-1/2 rounded-full border border-brand-100" aria-hidden="true"></div>

        <div class="container-rich">
            <div class="mx-auto max-w-3xl">
                {{-- Confirmation --}}
                <div class="text-center">
                    <span class="relative mx-auto flex h-20 w-20 items-center justify-center">
                        <span class="absolute inset-0 animate-ping rounded-full bg-brand-400 opacity-20" aria-hidden="true"></span>
                        <span class="absolute inset-0 rounded-full bg-brand-100" aria-hidden="true"></span>
                        <span class="relative flex h-14 w-14 items-center justify-center rounded-full bg-brand-600 text-white shadow-[0_14px_30px_-12px_var(--color-brand-600)]">
                            <x-ui-icon name="check" class="h-7 w-7" stroke="2.6" />
                        </span>
                    </span>

                    <span class="eyebrow mt-8">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ __('site.thanks.consultancy_title') }}
                    </span>

                    <h1 class="mt-5 font-display text-4xl font-bold leading-[1.1] tracking-tight text-ink-950 sm:text-5xl">
                        {{ __('site.thanks.thank_you', ['name' => $firstName ? ', '.$firstName : '']) }}
                    </h1>
                    <p class="mx-auto mt-5 max-w-xl text-[17px] leading-relaxed muted">
                        {{ __('site.thanks.consultancy_body') }}
                    </p>

                    {{-- The meeting they asked for, if they named one --}}
                    @if (($submission['preferred_date'] ?? null) && ($submission['preferred_slot'] ?? null))
                        <p class="mx-auto mt-6 inline-flex flex-wrap items-center justify-center gap-2 rounded-2xl border border-brand-200 bg-brand-50 px-5 py-3 text-[14.5px] font-semibold text-brand-800">
                            <x-ui-icon name="calendar" class="h-4 w-4 text-brand-600" />
                            {{ $submission['preferred_date'] }}
                            <span class="text-brand-300">·</span>
                            <span class="tabular-nums">{{ $submission['preferred_slot'] }}</span>
                        </p>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('home') }}" class="btn-primary group">
                        {{ __('site.actions.back_to_home') }} <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" />
                    </a>
                    <a href="{{ route('experts.index') }}" class="btn-ghost">{{ __('site.thanks.meet_experts') }}</a>
                    <a href="{{ route('projects.index') }}" class="btn-ghost">{{ __('site.thanks.see_projects') }}</a>
                </div>

                @if ($email)
                    <p class="mt-8 text-center text-[14px] muted">
                        {{ __('site.thanks.add_something') }}
                        <a href="mailto:{{ $email }}?subject={{ rawurlencode(__('site.thanks.email_subject')) }}" class="font-semibold text-brand-700 hover:underline">{{ $email }}</a>.
                    </p>
                @endif
            </div>
        </div>
    </section>
</x-layouts.app>
