<x-layouts.app :title="__('site.nav.news')"
               :description="__('site.news.meta_description')">

    <x-page-hero
        :eyebrow="__('site.news.hero_eyebrow')"
        :title="__('site.news.hero_title')"
        :lead="__('site.news.hero_lead')"
        :breadcrumbs="[__('site.nav.news') => null]" />

    {{-- ---------------- Upcoming events ----------------
         Dark and featured: the next event, large, with its photograph and a
         live countdown. One event only — the one coming up first. --}}
    @if ($upcoming->isNotEmpty())
        @php
            $next = $upcoming->first();
            $num = fn ($value) => \App\Support\Numerals::localize((string) $value);
        @endphp

        <section class="relative isolate overflow-hidden bg-navy-800 py-16 text-white sm:py-20">
            <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.07]" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -left-32 -top-32 -z-10 h-96 w-96 rounded-full bg-brand-500/25 blur-[120px]" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-32 right-0 -z-10 h-80 w-80 rounded-full bg-gold-400/15 blur-[110px]" aria-hidden="true"></div>

            <div class="container-rich">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="inline-flex items-center gap-2.5 rounded-full border border-white/20 bg-white/10 px-3.5 py-1.5 text-[12px] font-semibold text-white">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-gold-300 opacity-70"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-gold-300"></span>
                            </span>
                            {{ __('site.news.upcoming') }}
                        </p>
                        <h2 class="mt-4 font-display text-[28px] font-bold leading-tight tracking-[-0.02em] !text-white sm:text-[36px]">{{ __('site.news.upcoming_title') }}</h2>
                    </div>
                    <a href="{{ route('events') }}" class="group inline-flex items-center gap-2 text-[14px] font-semibold text-gold-300 hover:text-gold-200">
                        {{ __('site.news.all_events') }}
                        <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
                    </a>
                </div>

                <div class="mt-10">

                    {{-- The next event --}}
                    <a href="{{ route('news.show', $next) }}"
                       class="reveal group relative isolate flex min-h-[26rem] flex-col justify-end overflow-hidden rounded-[2rem] ring-1 ring-white/10 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-gold-300">
                        @if ($next->image)
                            <img src="{{ Storage::url($next->image) }}" alt="" aria-hidden="true" loading="lazy"
                                 class="absolute inset-0 -z-20 h-full w-full object-cover transition duration-700 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-105">
                        @else
                            <div class="absolute inset-0 -z-20 bg-gradient-to-br from-navy-600 to-navy-900"></div>
                        @endif
                        <div class="absolute inset-0 -z-10 bg-gradient-to-t from-navy-900 via-navy-900/70 to-navy-900/10" aria-hidden="true"></div>

                        {{-- Date tile, top left; rulebook badge, top right --}}
                        <div class="absolute left-6 top-6 flex h-20 w-20 flex-col items-center justify-center rounded-2xl bg-white text-navy-900 shadow-[0_16px_34px_-16px_rgba(0,0,0,0.6)]">
                            <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-brand-600">{{ $next->event_at->translatedFormat('M') }}</span>
                            <span class="font-display text-[32px] font-bold leading-none">{{ $num($next->event_at->format('j')) }}</span>
                        </div>
                        @if ($next->rulebook)
                            <span class="absolute right-6 top-6 inline-flex items-center gap-1.5 rounded-full bg-gold-300 px-3 py-1.5 text-[12px] font-bold text-navy-900">
                                <x-ui-icon name="document" class="h-3.5 w-3.5" /> {{ __('site.news.rulebook') }}
                            </span>
                        @endif

                        <div class="p-6 sm:p-8">
                            @if ($next->category)
                                <span class="rounded-full bg-white/15 px-3 py-1 text-[12px] font-semibold text-white backdrop-blur-md">{{ $next->category }}</span>
                            @endif
                            <h3 class="mt-4 font-display text-[24px] font-bold leading-snug !text-white transition-colors group-hover:text-gold-200 sm:text-[30px]">{{ $next->title }}</h3>

                            <div class="mt-4 flex flex-wrap gap-x-5 gap-y-2 text-[13.5px] text-white/75">
                                <span class="inline-flex items-center gap-1.5"><x-ui-icon name="clock" class="h-4 w-4 text-gold-300" />{{ $next->event_at->translatedFormat('l') }}, {{ $num($next->event_at->format('g:i A')) }}</span>
                                @if ($next->location)
                                    <span class="inline-flex items-center gap-1.5"><x-ui-icon name="map-pin" class="h-4 w-4 text-gold-300" />{{ $next->location }}</span>
                                @endif
                            </div>

                            {{-- Countdown to the start --}}
                            <div class="mt-6 flex flex-wrap items-end gap-2.5"
                                 x-data="eventCountdown({{ $next->event_at->getTimestamp() * 1000 }}, {{ app()->getLocale() === 'bn' ? 'true' : 'false' }})">
                                @foreach (['d' => 'days', 'h' => 'hours', 'm' => 'minutes', 's' => 'seconds'] as $unit => $label)
                                    <div class="min-w-[4.25rem] rounded-2xl bg-white/10 px-3 py-2.5 text-center ring-1 ring-white/15 backdrop-blur-md">
                                        <span class="block font-display text-[24px] font-bold leading-none tabular-nums !text-white" x-text="digits(left.{{ $unit }})">--</span>
                                        <span class="mt-1 block text-[10.5px] font-semibold uppercase tracking-[0.12em] text-white/55">{{ __('site.news.countdown.'.$label) }}</span>
                                    </div>
                                @endforeach
                                <span class="ml-auto hidden items-center gap-2 rounded-full bg-gold-300 px-5 py-3 text-[14px] font-bold text-navy-900 transition group-hover:bg-gold-200 sm:inline-flex">
                                    {{ __('site.news.event_open') }} <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </section>
    @endif

    <section class="py-16 bg-white sm:py-20">
        <div class="container-rich">
            {{-- Type filter --}}
            <div class="reveal flex flex-wrap items-center gap-2.5">
                @foreach ([['', __('site.news.filter_all')], ['news', __('site.news.filter_news')], ['event', __('site.news.filter_events')]] as [$value, $label])
                    <a href="{{ $value ? route('news.index', ['type' => $value]) : route('news.index') }}"
                       @class([
                           'rounded-full border px-5 py-2 text-[13.5px] font-medium transition',
                           'border-brand-600 bg-brand-600 text-white' => $type === $value,
                           'hairline muted hover:border-brand-300 hover:text-brand-700' => $type !== $value,
                       ])>
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            @if ($posts->isEmpty())
                <div class="mt-10 rounded-3xl border border-dashed hairline p-14 text-center">
                    <x-ui-icon name="document" class="mx-auto h-9 w-9 muted" stroke="1.3" />
                    <p class="mt-4 font-display text-lg font-semibold">{{ __('site.news.empty_title') }}</p>
                    <p class="mt-2 text-[14.5px] muted">{{ __('site.news.empty_body') }}</p>
                </div>
            @else
                <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $i => $post)
                        <x-cards.post-card :post="$post" :index="$i" />
                    @endforeach
                </div>

                <div class="mt-14 border-t border-ink-100 pt-8">{{ $posts->links('vendor.pagination.rich') }}</div>
            @endif
        </div>
    </section>

</x-layouts.app>
