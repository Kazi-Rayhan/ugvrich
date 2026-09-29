<x-layouts.app :title="__('site.about.meta_title')" :description="$site->get('about_intro')">

    <x-page-hero
        :eyebrow="__('site.home.about_eyebrow')"
        :title="__('site.about.hero_title')"
        :lead="$site->get('about_intro')"
        :breadcrumbs="[__('site.about.meta_title') => null]">
        <a href="{{ route('contact') }}" class="btn-primary">
            {{ __('site.actions.request_consultancy') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
        </a>
        <a href="{{ route('services.index') }}" class="btn-ghost">
            {{ __('site.about.view_services') }}
        </a>
    </x-page-hero>

    {{-- Who we are --}}
    @php
        $missionIcons = ['beaker', 'lightbulb', 'briefcase', 'handshake', 'academic', 'globe', 'chart', 'target'];
        $leadStat = $stats->first();
    @endphp

    <section class="bg-white py-20 sm:py-28">
        <div class="container-rich grid items-center gap-14 lg:grid-cols-2 lg:gap-20">
            {{-- Copy --}}
            <div class="reveal">
                <span class="eyebrow"><span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ __('site.about.who_eyebrow') }}</span>

                <h2 class="mt-6 font-display text-3xl font-bold leading-[1.12] text-ink-950 sm:text-[42px]">
                    {!! __('site.about.who_title') !!}
                </h2>

                <p class="mt-6 text-[17px] leading-[1.75] text-ink-700">{{ $site->get('about_body') }}</p>

                <p class="mt-4 text-[15.5px] leading-[1.75] muted">
                    {{ __('site.about.who_body') }}
                </p>

                <ul class="mt-8 grid gap-3 sm:grid-cols-3">
                    @foreach ([
                        ['users', 'staffed'],
                        ['calendar', 'milestone'],
                        ['target', 'actionable'],
                    ] as [$icon, $tile])
                        <li class="group rounded-2xl border border-ink-100 bg-ink-50/60 p-4 transition hover:border-brand-200 hover:bg-white">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-brand-600 ring-1 ring-ink-100 transition group-hover:bg-brand-600 group-hover:text-white group-hover:ring-brand-600">
                                <x-ui-icon :name="$icon" class="h-[18px] w-[18px]" />
                            </span>
                            <p class="mt-3 font-display text-[15px] font-semibold text-ink-950">{{ __('site.about.tile_'.$tile) }}</p>
                            <p class="mt-0.5 text-[12.5px] leading-snug muted">{{ __('site.about.tile_'.$tile.'_note') }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Image collage --}}
            <div class="reveal relative mx-auto w-full max-w-xl lg:max-w-none" style="transition-delay: 120ms">
                <div class="grid grid-cols-5 gap-4">
                    <div class="col-span-3 row-span-2 overflow-hidden rounded-[2rem]">
                        <img src="{{ asset('images/heroes/experts.jpg') }}" alt="{{ __('site.about.alt_meeting') }}" loading="lazy"
                             class="h-full min-h-[22rem] w-full object-cover transition duration-700 hover:scale-105 sm:min-h-[28rem]">
                    </div>
                    <div class="col-span-2 overflow-hidden rounded-[2rem]">
                        <img src="{{ asset('images/heroes/research.jpg') }}" alt="{{ __('site.about.alt_lab') }}" loading="lazy"
                             class="aspect-[4/5] h-full w-full object-cover transition duration-700 hover:scale-105">
                    </div>
                    <div class="col-span-2 flex flex-col justify-between rounded-[2rem] bg-brand-600 p-5 text-white sm:p-6">
                        <x-ui-icon name="sparkles" class="h-6 w-6 text-brand-200" />
                        <div>
                            <p class="font-display text-[15px] font-semibold leading-snug !text-white">{{ __('site.about.collage_caption') }}</p>
                        </div>
                    </div>
                </div>

                @if ($leadStat)
                    <div class="absolute -bottom-6 left-6 flex items-center gap-4 rounded-2xl border border-ink-100 bg-white p-4 pr-6 shadow-[0_24px_50px_-24px_rgba(11,15,24,0.45)] sm:left-10">
                        <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <x-ui-icon name="briefcase" class="h-5 w-5" />
                        </span>
                        <div>
                            <p class="font-display text-2xl font-bold leading-none text-ink-950">{{ $leadStat->value }}<span class="text-brand-600">{{ $leadStat->suffix }}</span></p>
                            <p class="mt-1 text-[12.5px] muted">{{ $leadStat->label }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Vision & mission --}}
    <section class="relative isolate overflow-hidden bg-ink-50 py-20 sm:py-28">
        <div class="pointer-events-none absolute inset-0 -z-10 text-brand-700 grid-overlay opacity-30 [mask-image:linear-gradient(to_bottom,black,transparent)]" aria-hidden="true"></div>

        <div class="container-rich">
            <x-section-heading
                align="center"
                :eyebrow="__('site.about.vm_eyebrow')"
                :title="__('site.about.vm_title')" />

            <div class="mt-14 grid gap-6 lg:grid-cols-[0.85fr_1.15fr]">
                {{-- Vision --}}
                <div class="reveal relative isolate flex flex-col overflow-hidden rounded-[2rem] bg-brand-700 p-8 text-white sm:p-10">
                    <div class="pointer-events-none absolute inset-0 -z-10 text-ink-950 grid-overlay opacity-60" aria-hidden="true"></div>
                    <div class="pointer-events-none absolute -right-20 -top-20 -z-10 h-64 w-64 rounded-full bg-brand-600" aria-hidden="true"></div>
                    <div class="pointer-events-none absolute -bottom-24 -left-24 -z-10 h-72 w-72 rounded-full border border-white/10" aria-hidden="true"></div>

                    <div class="flex items-center justify-between">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-brand-700 shadow-[0_12px_28px_-12px_rgba(0,0,0,0.5)]">
                            <x-ui-icon name="target" class="h-6 w-6" />
                        </span>
                        <span class="text-[12px] font-semibold uppercase tracking-[0.2em] text-brand-200">{{ __('site.home.our_vision') }}</span>
                    </div>

                    <x-ui-icon name="quote" class="mt-10 h-10 w-10 text-brand-300/60" />
                    <p class="mt-4 font-display text-[24px] font-semibold leading-[1.4] !text-white sm:text-[28px]">
                        {{ $site->get('vision') }}
                    </p>

                    <div class="mt-auto flex items-center gap-3 border-t border-white/15 pt-6 text-[13px] text-white/70">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 font-display text-[13px] font-bold text-white">R</span>
                        {{ __('site.about.vision_signature') }}
                    </div>
                </div>

                {{-- Mission --}}
                <div class="reveal rounded-[2rem] border border-ink-100 bg-white p-8 sm:p-10" style="transition-delay: 100ms">
                    <div class="flex items-center justify-between">
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                            <x-ui-icon name="compass" class="h-6 w-6" />
                        </span>
                        <span class="text-[12px] font-semibold uppercase tracking-[0.2em] text-brand-700">{{ __('site.home.our_mission') }}</span>
                    </div>

                    <p class="mt-8 font-display text-[22px] font-bold text-ink-950">{{ $site->get('mission_intro') }}</p>

                    <ol class="mt-6 grid gap-3 sm:grid-cols-2">
                        @foreach ($site->list('mission_points') as $i => $point)
                            <li class="group flex items-start gap-3.5 rounded-2xl border border-ink-100 bg-ink-50/60 p-4 transition duration-300 hover:-translate-y-0.5 hover:border-brand-200 hover:bg-white hover:shadow-[0_16px_36px_-26px_rgba(2,34,81,0.5)]">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-brand-600 ring-1 ring-ink-100 transition group-hover:bg-brand-600 group-hover:text-white group-hover:ring-brand-600">
                                    <x-ui-icon :name="$missionIcons[$i] ?? 'check'" class="h-[18px] w-[18px]" />
                                </span>
                                <span class="min-w-0">
                                    <span class="block font-numeric text-[11px] font-bold tabular-nums text-ink-300">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="mt-0.5 block text-[14px] leading-snug text-ink-800">{{ rtrim($point, '.') }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </section>

    {{-- Stats band --}}
    <section class="border-y hairline py-14 bg-ink-50">
        <div class="container-rich grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($stats as $stat)
                <div class="reveal text-center" x-data="counter({{ (int) preg_replace('/\D/', '', $stat->value) }})" x-intersect.once="start()">
                    <p class="text-[40px] font-display font-bold leading-none text-ink-950">
                        <span x-text="value">{{ $stat->value }}</span><span class="text-brand-600">{{ $stat->suffix }}</span>
                    </p>
                    <p class="mt-2 text-[13.5px] muted">{{ $stat->label }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Core areas --}}
    <section class="py-24 bg-white">
        <div class="container-rich">
            <x-section-heading
                align="center"
                :eyebrow="__('site.about.core_eyebrow')"
                :title="__('site.about.core_title')"
                :lead="__('site.about.core_lead')" />

            <div class="mt-14 grid gap-7 md:grid-cols-2">
                @foreach ($coreAreas as $i => $area)
                    <x-cards.core-area-card :area="$area" :index="$i" />
                @endforeach
            </div>
        </div>
    </section>

    {{-- Who we serve --}}
    <section class="relative overflow-hidden bg-white py-24">
        <div class="pointer-events-none absolute inset-0 text-brand-600 grid-overlay opacity-[0.25]"></div>
        <div class="pointer-events-none absolute -right-40 top-1/4 h-[460px] w-[460px] rounded-full bg-brand-200/50 blur-[140px]"></div>

        <div class="container-rich relative grid gap-14 lg:grid-cols-2 lg:gap-20">
            <div>
                <x-section-heading
                    :eyebrow="__('site.about.serve_eyebrow')"
                    :title="__('site.about.serve_title')"
                    :lead="__('site.about.serve_lead')" />

                <div class="mt-9 flex flex-wrap gap-2.5">
                    @foreach ($site->list('who_we_serve') as $i => $audience)
                        <span class="reveal rounded-full border border-ink-200 bg-ink-50 px-4 py-2 text-[13.5px] text-ink-600
                                     transition hover:border-brand-300 hover:text-brand-700"
                              style="transition-delay: {{ min($i * 40, 320) }}ms">
                            {{ $audience }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div>
                <x-section-heading
                    :eyebrow="__('site.about.partners_eyebrow')"
                    :title="__('site.about.partners_title')" />

                <div class="mt-9 grid gap-3 sm:grid-cols-2">
                    @foreach ($site->list('partnership_types') as $i => $type)
                        <div class="reveal flex items-center gap-3 rounded-2xl border border-ink-200 bg-white px-4 py-3.5"
                             style="transition-delay: {{ min($i * 50, 300) }}ms">
                            <x-ui-icon name="handshake" class="h-4.5 w-4.5 shrink-0 text-brand-600" />
                            <span class="text-[14px] text-ink-600">{{ $type }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- Why choose (shared with the home page) --}}
    @include('partials.home.why-choose')

    {{-- Partners (shared with the home page) --}}
    @include('partials.home.marquee')

    {{-- Experts (shared with the home page) --}}
    @include('partials.home.experts')

</x-layouts.app>
