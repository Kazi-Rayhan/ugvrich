@php
    // Two rows drifting in opposite directions read as a wall of partners
    // rather than a single ticker. With few partners, one row is enough.
    $rows = $partners->count() >= 6
        ? $partners->split(2)
        : collect([$partners]);

    $partnerStat = $stats->first(fn ($s) => str_contains(strtolower($s->label), 'partner'));

    // The home page shows the marks in greyscale until hovered; About keeps colour.
    $subdued ??= false;
@endphp

@if ($partners->isNotEmpty())
    <section @class(['relative overflow-hidden border-b hairline bg-ink-50', 'partners-subdued py-20 sm:py-24' => $subdued, 'py-16 sm:py-20' => ! $subdued])>
        <div class="container-rich">
            <div class="grid items-center gap-10 lg:grid-cols-[0.38fr_0.62fr] lg:gap-14">

                {{-- Framing --}}
                <div class="reveal">
                    <span class="eyebrow">
                        <span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ __('site.home.partners_eyebrow') }}
                    </span>

                    <p class="mt-6 font-display text-[44px] font-bold leading-none text-ink-950"
                       x-data="counter({{ (int) preg_replace('/\D/', '', $partnerStat->value ?? '45') }})"
                       x-intersect.once="start()">
                        <span x-text="value">{{ $partnerStat->value ?? '45' }}</span><span class="text-brand-600">{{ $partnerStat->suffix ?? '+' }}</span>
                    </p>

                    <h2 class="mt-3 font-display text-[19px] font-bold leading-snug">
                        {{ __('site.home.partners_title') }}
                    </h2>

                    <p class="mt-3 max-w-sm text-[14.5px] leading-relaxed muted">
                        {{ __('site.home.partners_lead') }}
                    </p>

                    @unless (request()->routeIs('about'))
                        <a href="{{ route('about') }}"
                           class="group mt-6 inline-flex items-center gap-2 text-[13.5px] font-semibold text-brand-700">
                            {{ __('site.home.partners_link') }}
                            <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
                        </a>
                    @endunless
                </div>

                {{-- The wall --}}
                <div class="mask-fade-x -mx-5 overflow-hidden sm:-mx-8 lg:mx-0">
                    <div class="space-y-4">
                        @foreach ($rows as $rowIndex => $row)
                            <div class="flex w-max items-center gap-4 {{ $rowIndex % 2 === 0 ? 'animate-marquee' : 'animate-marquee-reverse' }}
                                        hover:[animation-play-state:paused]">
                                {{-- Rendered twice so the loop is seamless. --}}
                                @foreach (range(1, 2) as $pass)
                                    @foreach ($row as $partner)
                                        <div class="partner-chip" @if ($pass === 2) aria-hidden="true" @endif>
                                            <x-partner-mark :partner="$partner" size="lg" />

                                            <span class="min-w-0">
                                                <span class="block whitespace-nowrap text-[15px] font-semibold leading-tight text-ink-900">
                                                    {{ $partner->name }}
                                                </span>
                                                @if ($partner->type)
                                                    <span class="partner-chip-type">{{ $partner->type }}</span>
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
