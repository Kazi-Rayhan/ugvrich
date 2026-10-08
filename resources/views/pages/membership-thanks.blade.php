<x-layouts.app :title="__('site.membership.thanks_title')"
               :description="__('site.membership.thanks_description')">

    @php
        $firstName = \Illuminate\Support\Str::of($application['name'] ?? '')
            ->replaceMatches('/^(Dr\.?|Prof\.?|Mr\.?|Ms\.?|Mrs\.?|Md\.?|Engr\.?)\s+/i', '')
            ->explode(' ')->first();
        $track = \App\Support\Vocabulary::label('internship_tracks', $application['track'] ?? null);
    @endphp

    <section class="relative isolate overflow-hidden bg-ink-50 py-16 sm:py-24">
        <div class="pointer-events-none absolute inset-0 -z-10 text-brand-700 grid-overlay opacity-40 [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute left-1/2 top-24 -z-10 h-[34rem] w-[34rem] -translate-x-1/2 rounded-full border border-brand-100" aria-hidden="true"></div>
        <div class="pointer-events-none absolute left-1/2 top-44 -z-10 h-[22rem] w-[22rem] -translate-x-1/2 rounded-full border border-brand-100" aria-hidden="true"></div>

        <div class="container-rich">
            <div class="mx-auto max-w-3xl text-center">
                <span class="relative mx-auto flex h-20 w-20 items-center justify-center">
                    <span class="absolute inset-0 animate-ping rounded-full bg-brand-400 opacity-20" aria-hidden="true"></span>
                    <span class="absolute inset-0 rounded-full bg-brand-100" aria-hidden="true"></span>
                    <span class="relative flex h-14 w-14 items-center justify-center rounded-full bg-brand-600 text-white shadow-[0_14px_30px_-12px_var(--color-brand-600)]">
                        <x-ui-icon name="check" class="h-7 w-7" stroke="2.6" />
                    </span>
                </span>

                <span class="eyebrow mt-8">
                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ __('site.membership.thanks_title') }}
                </span>

                <h1 class="mt-5 font-display text-4xl font-bold leading-[1.1] tracking-tight text-ink-950 sm:text-5xl">
                    {{ __('site.thanks.thank_you', ['name' => $firstName ? ', '.$firstName : '']) }}
                </h1>
                <p class="mx-auto mt-5 max-w-xl text-[17px] leading-relaxed muted">
                    {{ __('site.membership.thanks_body') }}
                </p>

                @if ($application['reference'] ?? null)
                    <div class="mx-auto mt-8 max-w-md rounded-[1.5rem] border border-brand-200 bg-white p-5 text-center shadow-[0_24px_50px_-36px_rgba(7,20,38,0.45)] sm:p-6">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-brand-600">{{ __('site.membership.thanks_card') }}</p>
                        <p class="mt-3 inline-flex flex-wrap items-center justify-center gap-2 rounded-2xl bg-brand-50 px-5 py-3 text-[15px] font-semibold text-brand-800">
                            <x-ui-icon name="academic" class="h-4 w-4 text-brand-600" />
                            <span class="tabular-nums">{{ $application['reference'] }}</span>
                            @if ($track)
                                <span class="text-brand-300">·</span>
                                <span>{{ $track }}</span>
                            @endif
                        </p>
                    </div>
                @endif

                <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('home') }}" class="btn-primary group">
                        {{ __('site.actions.back_to_home') }} <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" />
                    </a>
                    <a href="{{ route('internship.create') }}" class="btn-ghost">{{ __('site.nav.internship') }}</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
