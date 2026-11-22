@php
    $servicesVideo = $site->get('services_video');

    $wings = [
        [
            'key' => 'consultancy', 'url' => route('services.index'), 'icon' => 'briefcase',
            'image' => asset('media/pillars/consultancy.jpg'),
            'video' => $servicesVideo ? Storage::url($servicesVideo) : null,
            'tint' => 'from-navy-900 via-navy-900/75 to-gold-700/30',
        ],
        [
            'key' => 'research', 'url' => route('research'), 'icon' => 'beaker',
            'image' => asset('media/pillars/research.jpg'), 'video' => null,
            'tint' => 'from-navy-900 via-navy-900/75 to-navy-900/15',
        ],
        [
            'key' => 'innovation', 'url' => route('innovation.index'), 'icon' => 'lightbulb',
            'image' => asset('media/pillars/innovation.jpg'), 'video' => null,
            'tint' => 'from-brand-900 via-brand-900/75 to-brand-700/25',
        ],
    ];
@endphp

<section id="wings" class="relative isolate overflow-hidden bg-navy-800 py-24 text-white sm:py-28">
    <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.07]" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -left-40 top-1/3 -z-10 h-[30rem] w-[30rem] rounded-full bg-brand-600/20 blur-[130px]" aria-hidden="true"></div>

    <div class="container-rich">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="reveal eyebrow-invert">{{ __('site.home.wings_eyebrow') }}</p>
                <h2 data-split class="reveal mt-6 font-display text-[32px] font-bold leading-[1.08] tracking-[-0.02em] !text-white sm:text-[46px]">
                    {!! __('site.home.wings_title') !!}
                </h2>
            </div>
            <p class="reveal max-w-md text-[15.5px] leading-[1.8] text-white/65">{{ __('site.home.wings_lead') }}</p>
        </div>

        <div class="mt-14 grid auto-rows-fr gap-4 md:grid-cols-3 lg:gap-5">
            @foreach ($wings as $i => $wing)
                <a href="{{ $wing['url'] }}" data-tilt
                   class="wing-tile group spotlight spotlight-invert reveal relative isolate flex min-h-[18rem] min-w-0 flex-col overflow-hidden rounded-3xl bg-navy-900 p-6 ring-1 ring-white/15 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold-300 lg:min-h-[21rem] lg:p-8"
                   style="transition-delay: {{ min($i * 70, 280) }}ms">

                    {{-- Background: the video when there is one (loaded only once the
                         tile is on screen, and left on its poster under reduced
                         motion), otherwise the photograph. --}}
                    @if ($wing['video'])
                        <video class="pointer-events-none absolute inset-0 -z-20 h-full w-full object-cover opacity-60 transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105 group-hover:opacity-75"
                               muted loop playsinline preload="none" poster="{{ $wing['image'] }}"
                               x-data
                               x-intersect:enter="if (! matchMedia('(prefers-reduced-motion: reduce)').matches) $el.play().catch(() => {})"
                               x-intersect:leave="$el.pause()"
                               aria-hidden="true" tabindex="-1">
                            <source src="{{ $wing['video'] }}" type="{{ str_ends_with(strtolower($wing['video']), '.webm') ? 'video/webm' : 'video/mp4' }}">
                        </video>
                    @else
                        <img src="{{ $wing['image'] }}" alt="" aria-hidden="true" loading="lazy" decoding="async"
                             class="pointer-events-none absolute inset-0 -z-20 h-full w-full object-cover opacity-55 transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105 group-hover:opacity-70">
                    @endif
                    <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t {{ $wing['tint'] }}" aria-hidden="true"></div>

                    <div class="relative z-10">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-gold-300 ring-1 ring-white/15 backdrop-blur-md">
                            <x-ui-icon :name="$wing['icon']" class="h-6 w-6" />
                        </span>
                    </div>

                    <div class="relative z-10 mt-8 flex flex-1 flex-col">
                        <h3 class="font-display text-2xl font-bold leading-tight tracking-tight !text-white lg:text-[28px]">
                            {{ __('site.home.wings.'.$wing['key'].'.title') }}
                        </h3>
                        <p class="mt-3 max-w-md text-sm leading-relaxed text-white/85 lg:text-[15px]">
                            {{ __('site.home.wings.'.$wing['key'].'.body') }}
                        </p>
                        <span class="mt-auto flex items-center justify-between gap-3 pt-6 text-sm font-semibold text-gold-300">
                            {{ __('site.home.wings_explore') }}
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-white/10 text-white ring-1 ring-white/15 transition-colors group-hover:bg-gold-300 group-hover:text-navy-900 group-focus-visible:bg-gold-300 group-focus-visible:text-navy-900" aria-hidden="true">
                                <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                            </span>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</section>
