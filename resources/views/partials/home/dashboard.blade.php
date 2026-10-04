{{-- Live KPI dashboard: the numbers are maintained in the admin (Site → KPIs, Site Settings → Pipeline). --}}
@if ($stats->isNotEmpty())
    <section id="dashboard" class="relative z-10 bg-ink-50 pb-20 pt-16 sm:pb-24 sm:pt-20">
        <div class="container-rich">
            {{-- KPI cards --}}
            <div class="reveal">
                {{-- Header: a live badge, the heading with its highlight drawn in,
                     and the way on to every project. --}}
                <div class="mb-10 flex flex-wrap items-end justify-between gap-6">
                    <div>
                        <p class="inline-flex items-center gap-2.5 rounded-full border border-brand-100 bg-white px-3.5 py-1.5 text-[12px] font-semibold text-brand-700 shadow-[0_8px_20px_-14px_rgba(7,20,38,0.35)]">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand-500 opacity-70"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-brand-600"></span>
                            </span>
                            {{ __('site.home.glance_live') }}
                        </p>
                        <h2 data-split class="mt-5 font-display text-[30px] font-bold leading-[1.1] tracking-[-0.02em] text-ink-950 sm:text-[40px]">
                            {!! __('site.home.glance_title') !!}
                        </h2>
                    </div>

                    <a href="{{ route('projects.index') }}" data-magnetic
                       class="group inline-flex items-center gap-3 rounded-full border border-ink-200 bg-white py-2 pl-5 pr-2 text-[13.5px] font-semibold text-ink-900 transition duration-300 hover:border-brand-300 hover:text-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500">
                        {{ __('site.home.view_all_projects') }}
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-600 text-white transition duration-300 group-hover:rotate-45">
                            <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                        </span>
                    </a>
                </div>

                {{-- The headline figures (Stats marked "Show on the home page"), each
                     inside a ring that draws itself while the number counts up.
                     The ring always closes: it is decoration, not a gauge. --}}
                @php
                    // A stat without the flag (a database the show_on_home migration
                    // has not reached yet) counts as shown, and if nothing is marked
                    // for the home page the first six stand in, so the panel is
                    // never left empty.
                    $homeStats = $stats->filter(fn ($stat) => $stat->show_on_home ?? true)->values();
                    $homeStats = $homeStats->isNotEmpty() ? $homeStats : $stats->take(6)->values();
                @endphp
                {{-- One gradient, shared by every ring --}}
                <svg class="absolute h-0 w-0" aria-hidden="true" focusable="false">
                    <defs>
                        <linearGradient id="kpi-ring-gradient" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" style="stop-color: var(--color-brand-500)" />
                            <stop offset="100%" style="stop-color: var(--color-gold-400)" />
                        </linearGradient>
                    </defs>
                </svg>

                <ul class="grid grid-cols-2 gap-x-4 gap-y-10 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach ($homeStats as $i => $stat)
                        <li class="kpi-ring-item reveal group flex flex-col items-center text-center"
                            style="transition-delay: {{ $i * 90 }}ms; --d: {{ $i * 90 }}ms"
                            x-data="counter({{ (int) preg_replace('/\D/', '', $stat->value) }})" x-intersect.once="start()">
                            <div class="relative h-36 w-36 sm:h-40 sm:w-40">
                                <svg class="kpi-ring h-full w-full -rotate-90" viewBox="0 0 120 120" aria-hidden="true">
                                    <circle cx="60" cy="60" r="52" fill="none" stroke-width="6" class="stroke-ink-100" />
                                    <circle cx="60" cy="60" r="52" fill="none" stroke-width="6" stroke-linecap="round"
                                            pathLength="100" stroke="url(#kpi-ring-gradient)" class="kpi-ring-progress" />
                                </svg>

                                {{-- Soft disc behind the figure, lit on hover --}}
                                <div class="absolute inset-[18%] rounded-full bg-white shadow-[0_14px_34px_-18px_rgba(7,20,38,0.35)] transition duration-500 group-hover:shadow-[0_18px_40px_-14px_var(--color-brand-500)]" aria-hidden="true"></div>

                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <x-ui-icon :name="$stat->icon ?? 'chart'" class="h-4 w-4 text-brand-600 transition-transform duration-500 group-hover:-translate-y-0.5 group-hover:scale-110" />
                                    <p class="mt-1 font-display text-[30px] font-bold leading-none tracking-tight tabular-nums text-ink-950 sm:text-[34px]">
                                        <span x-text="value">{{ $stat->value }}</span><span class="text-gold-500">{{ $stat->suffix }}</span>
                                    </p>
                                </div>
                            </div>

                            <p class="mt-4 max-w-[10rem] text-[14px] font-semibold leading-snug text-ink-800">{{ $stat->label }}</p>
                        </li>
                    @endforeach
                </ul>
            </div>

            @include('partials.innovation-pipeline', ['class' => 'mt-6'])
        </div>
    </section>
@endif
