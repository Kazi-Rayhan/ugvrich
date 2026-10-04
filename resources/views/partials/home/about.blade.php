@php
    $visionMedia = $site->get('vision_media')
        ? Storage::url($site->get('vision_media'))
        : asset('media/about/vision.jpg');

    $mission = $site->get('mission_statement')
        ?: collect($site->list('mission_points'))->map(fn ($p) => rtrim($p, '.'))->implode('; ');

    // The four pillars, each a way into the site rather than a label.
    $pillars = [
        ['beaker', 'research', route('research')],
        ['lightbulb', 'innovation', route('innovation.index')],
        ['briefcase', 'industry', route('services.index')],
        ['rocket', 'entrepreneurship', route('startup')],
    ];
@endphp

{{-- About, in the editorial style of the rest of the home page: a large
     heading beside the profile, then vision, mission and the four pillars
     as a bento. Light ground, between the dark wings and the dark close. --}}
<section class="relative isolate overflow-hidden bg-white py-24 sm:py-28">
    <div class="pointer-events-none absolute -right-32 top-0 -z-10 h-[28rem] w-[28rem] rounded-full bg-brand-100/50 blur-[120px]" aria-hidden="true"></div>

    <div class="container-rich">
        {{-- Heading and profile --}}
        <div class="grid gap-8 lg:grid-cols-[1fr_1fr] lg:items-end lg:gap-16">
            <div>
                <p class="reveal eyebrow">
                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ __('site.home.about_eyebrow') }}
                </p>
                <h2 data-split class="reveal mt-6 font-display text-[32px] font-bold leading-[1.08] tracking-[-0.02em] text-ink-950 sm:text-[46px]">
                    {!! __('site.home.about_title') !!}
                </h2>
            </div>

            <div class="reveal" style="transition-delay: 100ms">
                <p class="text-[18px] leading-[1.75] text-ink-800">{{ $site->get('about_intro') }}</p>
                <p class="mt-4 text-[15.5px] leading-[1.8] muted">{{ $site->get('about_body') }}</p>
            </div>
        </div>

        {{-- Vision and mission --}}
        <div class="mt-14 grid gap-5 lg:grid-cols-12">
            <div class="reveal spotlight spotlight-invert group relative isolate flex min-h-[20rem] flex-col overflow-hidden rounded-[1.75rem] p-8 sm:p-10 lg:col-span-7">
                <img src="{{ $visionMedia }}" alt="" aria-hidden="true" loading="lazy" decoding="async"
                     class="absolute inset-0 -z-20 h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105">
                <div class="absolute inset-0 -z-10 bg-gradient-to-tr from-navy-900/95 via-navy-900/80 to-brand-900/50" aria-hidden="true"></div>

                <div class="relative z-10 flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/10 text-gold-300 ring-1 ring-white/15 backdrop-blur-md">
                        <x-ui-icon name="target" class="h-5 w-5" />
                    </span>
                    <p class="text-[12px] font-semibold uppercase tracking-[0.2em] text-gold-300">{{ __('site.home.our_vision') }}</p>
                </div>
                <p class="relative z-10 mt-auto pt-10 font-display text-[22px] font-semibold leading-[1.45] !text-white sm:text-[27px]">
                    {{ $site->get('vision') }}
                </p>
            </div>

            <div class="reveal relative flex flex-col overflow-hidden rounded-[1.75rem] bg-brand-50 p-8 ring-1 ring-brand-100 sm:p-10 lg:col-span-5" style="transition-delay: 100ms">
                <x-ui-icon name="quote" class="pointer-events-none absolute -right-4 -top-4 h-32 w-32 text-brand-100" />

                <div class="relative flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-brand-600 text-white">
                        <x-ui-icon name="compass" class="h-5 w-5" />
                    </span>
                    <p class="text-[12px] font-semibold uppercase tracking-[0.2em] text-brand-700">{{ __('site.home.our_mission') }}</p>
                </div>
                <p class="relative mt-auto pt-10 text-[17px] leading-[1.75] text-ink-800">{{ $mission }}</p>
            </div>
        </div>

        {{-- The four pillars, then the full profile --}}
        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-5">
            @foreach ($pillars as $i => [$icon, $key, $url])
                <a href="{{ $url }}"
                   class="reveal group flex items-center gap-3 rounded-2xl border border-ink-100 bg-white p-4 transition duration-300 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-[0_18px_40px_-28px_rgba(7,20,38,0.45)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
                   style="transition-delay: {{ $i * 60 }}ms">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                        <x-ui-icon :name="$icon" class="h-5 w-5" />
                    </span>
                    <span class="min-w-0 flex-1 text-[14.5px] font-semibold text-ink-900">{{ __('site.home.chip_'.$key) }}</span>
                    <x-ui-icon name="arrow-right" class="h-4 w-4 shrink-0 text-ink-300 transition duration-300 group-hover:translate-x-0.5 group-hover:text-brand-600" />
                </a>
            @endforeach

            <a href="{{ route('about') }}" data-magnetic
               class="reveal group flex items-center justify-between gap-3 rounded-2xl bg-navy-800 p-4 pl-5 text-white transition duration-300 hover:bg-navy-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gold-300 sm:col-span-2 lg:col-span-1"
               style="transition-delay: 240ms">
                <span class="text-[14.5px] font-semibold">{{ __('site.home.read_profile') }}</span>
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gold-300 text-navy-900 transition duration-300 group-hover:rotate-45">
                    <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                </span>
            </a>
        </div>
    </div>
</section>
