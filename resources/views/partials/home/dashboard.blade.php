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

                {{-- The headline figures (Stats marked "Show on the home page"), in
                     one panel divided by hairlines. Each cell rises in on a stagger,
                     counts up, and draws a brand-to-gold line under itself. --}}
                @php($homeStats = $stats->where('show_on_home', true)->values())
                <div class="overflow-hidden rounded-[1.75rem] bg-white shadow-[0_24px_60px_-40px_rgba(7,20,38,0.35)] ring-1 ring-ink-100">
                <div class="-mb-px -mr-px grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6">
                    @foreach ($homeStats as $i => $stat)
                        <div class="kpi-cell reveal spotlight group relative isolate border-b border-r border-ink-100 px-5 pb-8 pt-6 sm:px-6"
                             style="transition-delay: {{ $i * 80 }}ms; --d: {{ $i * 80 }}ms"
                             x-data="counter({{ (int) preg_replace('/\D/', '', $stat->value) }})" x-intersect.once="start()">
                            <div class="relative z-10 flex items-center justify-between">
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-brand-100 transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:-translate-y-0.5 group-hover:-rotate-6 group-hover:bg-brand-600 group-hover:text-white group-hover:ring-brand-600">
                                    <x-ui-icon :name="$stat->icon ?? 'chart'" class="h-5 w-5" />
                                </span>
                                <span class="font-numeric text-[11px] font-semibold tabular-nums text-ink-300" aria-hidden="true">{{ \App\Support\Numerals::localize(str_pad($i + 1, 2, '0', STR_PAD_LEFT)) }}</span>
                            </div>

                            <p class="relative z-10 mt-6 font-display text-[36px] font-bold leading-none tracking-tight tabular-nums text-ink-950 sm:text-[40px]">
                                <span x-text="value">{{ $stat->value }}</span><span class="text-gold-500">{{ $stat->suffix }}</span>
                            </p>
                            <p class="relative z-10 mt-2.5 text-[13.5px] font-medium leading-snug text-ink-600">{{ $stat->label }}</p>

                            <span class="kpi-bar" aria-hidden="true"></span>
                        </div>
                    @endforeach
                </div>
                </div>
            </div>

            @include('partials.innovation-pipeline', ['class' => 'mt-6'])
        </div>
    </section>
@endif
