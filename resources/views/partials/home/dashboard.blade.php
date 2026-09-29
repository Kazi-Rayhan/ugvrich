{{-- Live KPI dashboard: the numbers are maintained in the admin (Site → KPIs, Site Settings → Pipeline). --}}
@if ($stats->isNotEmpty())
    <section id="dashboard" class="relative z-10 bg-ink-50 pb-20 pt-16 sm:pb-24 sm:pt-20">
        <div class="container-rich">
            {{-- KPI cards --}}
            <div class="reveal">
                {{-- Header --}}
                <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand-500 opacity-60"></span>
                            <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-brand-600"></span>
                        </span>
                        <p class="font-display text-[15px] font-bold text-ink-950">{{ __('site.home.glance') }}</p>
                        <span class="hidden text-[13px] muted sm:inline">{{ __('site.home.glance_note') }}</span>
                    </div>
                    <a href="{{ route('projects.index') }}" class="group inline-flex items-center gap-1.5 text-[13px] font-semibold text-brand-700">
                        {{ __('site.home.view_all_projects') }}
                        <x-ui-icon name="arrow-right" class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-1" />
                    </a>
                </div>

                {{-- Tiles --}}
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 lg:gap-5">
                    @foreach ($stats as $i => $stat)
                        <div class="group relative isolate flex flex-col items-center overflow-hidden rounded-[1.75rem] border border-ink-100 bg-white p-5 text-center shadow-[0_18px_40px_-30px_rgba(11,15,24,0.45)] transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1 hover:border-brand-600 hover:bg-brand-600 hover:shadow-[0_24px_44px_-22px_var(--color-brand-600)] sm:p-6"
                             x-data="counter({{ (int) preg_replace('/\D/', '', $stat->value) }})" x-intersect.once="start()">
                            <span class="flex h-16 w-16 items-center justify-center rounded-[1.25rem] bg-brand-50 text-brand-600 ring-1 ring-brand-100 transition duration-500 group-hover:rotate-[-6deg] group-hover:scale-105 group-hover:bg-white group-hover:text-brand-600 group-hover:ring-white sm:h-[4.5rem] sm:w-[4.5rem]">
                                <x-ui-icon :name="$stat->icon ?? 'chart'" class="h-8 w-8 sm:h-9 sm:w-9" stroke="1.5" />
                            </span>

                            <p class="mt-6 font-numeric text-[44px] font-bold leading-none tracking-tight tabular-nums text-ink-950 transition duration-500 group-hover:text-white sm:text-[50px]">
                                <span x-text="value">{{ $stat->value }}</span><span class="text-brand-600 transition group-hover:text-brand-200">{{ $stat->suffix }}</span>
                            </p>
                            <p class="mt-2 text-[13.5px] font-semibold text-ink-700 transition duration-500 group-hover:text-white">{{ $stat->label }}</p>

                        </div>
                    @endforeach
                </div>
            </div>

            @include('partials.innovation-pipeline', ['class' => 'mt-6'])
        </div>
    </section>
@endif
