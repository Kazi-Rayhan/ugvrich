<x-layouts.app :title="__('site.screening.nav')"
               :description="__('site.screening.meta_description')">

    <x-page-hero
        :eyebrow="__('site.screening.hero_eyebrow')"
        :title="__('site.screening.hero_title')"
        :lead="__('site.screening.hero_lead')"
        :breadcrumbs="[__('site.nav.innovation') => route('innovation.index'), __('site.screening.nav') => null]">
        <a href="{{ route('ideas.create') }}" class="btn-primary">
            {{ __('site.actions.submit_idea') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
        </a>
        <a href="#pipeline" class="btn-ghost">{{ __('site.screening.see_pipeline') }}</a>
    </x-page-hero>

    @php
        $applicants = __('site.screening.applicants');
        $outcomes = __('site.screening.outcomes');
        $tiers = __('site.screening.tiers');

        // Outcome dots, and the pill beside each.
        $outcomeDot = ['approved' => 'bg-emerald-500', 'revision' => 'bg-amber-400', 'rejected' => 'bg-red-500'];
        $outcomePill = [
            'approved' => 'bg-emerald-500/15 text-emerald-300 ring-emerald-400/30',
            'revision' => 'bg-amber-400/15 text-amber-300 ring-amber-400/30',
            'rejected' => 'bg-red-500/15 text-red-300 ring-red-400/30',
        ];

        // One colour per tier: the first is the gate, the last the board.
        $tierTone = [
            ['dot' => 'bg-brand-600', 'head' => 'bg-brand-600', 'text' => 'text-brand-700', 'soft' => 'bg-brand-50'],
            ['dot' => 'bg-navy-600', 'head' => 'bg-navy-600', 'text' => 'text-navy-600', 'soft' => 'bg-navy-50'],
            ['dot' => 'bg-navy-700', 'head' => 'bg-navy-700', 'text' => 'text-navy-700', 'soft' => 'bg-navy-50'],
            ['dot' => 'bg-amber-500', 'head' => 'bg-amber-500', 'text' => 'text-amber-600', 'soft' => 'bg-amber-50'],
        ];

        $num = fn ($value) => app()->getLocale() === 'bn'
            ? strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
            : (string) $value;
    @endphp

    {{-- ---------------- Who can apply / Screening outcomes ---------------- --}}
    <section class="bg-ink-50/60 py-16 sm:py-20">
        <div class="container-rich grid gap-6 lg:grid-cols-3">

            {{-- Who can apply --}}
            <div class="reveal rounded-3xl border border-ink-100 bg-white p-6 shadow-sm sm:p-8 lg:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-display text-[20px] font-bold text-ink-950 sm:text-[24px]">{{ __('site.screening.who_title') }}</h2>
                    <span class="text-[12.5px] font-medium text-ink-400">{{ __('site.screening.who_note') }}</span>
                </div>

                <div class="mt-7 grid gap-4 sm:grid-cols-3">
                    @foreach ($applicants as $a)
                        <div class="reveal flex flex-col items-center rounded-2xl border border-ink-100 bg-ink-50/50 px-5 py-7 text-center transition hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-md"
                             style="transition-delay: {{ $loop->index * 60 }}ms">
                            <span class="grid h-14 w-14 place-items-center rounded-2xl bg-navy-700 text-white shadow-lg shadow-navy-700/20">
                                <x-ui-icon :name="$a['icon']" class="h-6 w-6" />
                            </span>
                            <h3 class="mt-5 font-display text-[16px] font-bold leading-snug text-ink-950">{{ $a['title'] }}</h3>
                            <p class="mt-1 text-[13px] text-ink-500">{{ $a['text'] }}</p>
                            <span class="mt-5 inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.12em] text-brand-700 ring-1 ring-brand-100">
                                <x-ui-icon name="check" class="h-3.5 w-3.5" /> {{ __('site.screening.eligible') }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Screening outcomes --}}
            <div class="reveal relative overflow-hidden rounded-3xl bg-navy-700 p-6 text-white shadow-xl shadow-navy-900/20 sm:p-8">
                <div class="pointer-events-none absolute -right-20 -top-20 h-56 w-56 rounded-full bg-brand-600/30 blur-3xl" aria-hidden="true"></div>

                <h2 class="relative text-[12px] font-bold uppercase tracking-[0.18em] text-white/70">{{ __('site.screening.outcomes_title') }}</h2>

                <ul class="relative mt-6 divide-y divide-white/10">
                    @foreach ($outcomes as $o)
                        <li class="flex items-center justify-between gap-3 py-4">
                            <span class="inline-flex items-center gap-3 text-[15px] font-semibold">
                                <span class="h-2.5 w-2.5 rounded-full {{ $outcomeDot[$o['key']] }}"></span>
                                {{ $o['label'] }}
                            </span>
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 {{ $outcomePill[$o['key']] }}">{{ $o['note'] }}</span>
                        </li>
                    @endforeach
                </ul>

                <p class="relative mt-4 flex gap-2.5 rounded-2xl bg-white/5 p-4 text-[12.5px] leading-relaxed text-white/70 ring-1 ring-white/10">
                    <x-ui-icon name="bell" class="mt-0.5 h-4 w-4 shrink-0 text-amber-300" />
                    {{ __('site.screening.outcomes_footnote') }}
                </p>
            </div>
        </div>
    </section>

    {{-- ---------------- Screening & approval pipeline ---------------- --}}
    <section id="pipeline" class="scroll-mt-28 bg-white py-16 sm:py-20">
        <div class="container-rich">
            <div class="reveal rounded-3xl border border-ink-100 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="font-display text-[20px] font-bold text-ink-950 sm:text-[24px]">{{ __('site.screening.pipeline_title') }}</h2>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500 px-3 py-1 text-[11.5px] font-bold text-white shadow-sm">
                            <x-ui-icon name="clock" class="h-3.5 w-3.5" /> {{ __('site.screening.pipeline_badge') }}
                        </span>
                        <span class="text-[13px] text-ink-400">{{ __('site.screening.pipeline_lead') }}</span>
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-navy-50 px-3 py-1.5 text-[12px] font-medium text-navy-700">
                        <x-ui-icon name="calendar" class="h-3.5 w-3.5" /> {{ __('site.screening.pipeline_note') }}
                    </span>
                </div>

                {{-- The tiers on one line: dots joined by a rule on wide screens, stacked on narrow ones. --}}
                <ol class="relative mt-10 grid gap-5 md:grid-cols-4 md:gap-4">
                    <span class="pointer-events-none absolute left-[12.5%] right-[12.5%] top-6 hidden h-0.5 bg-gradient-to-r from-brand-600 via-navy-600 to-amber-500 md:block" aria-hidden="true"></span>

                    @foreach ($tiers as $i => $t)
                        @php $tone = $tierTone[$i] ?? $tierTone[0]; @endphp
                        <li class="reveal relative flex flex-col" style="transition-delay: {{ $i * 80 }}ms">
                            <div class="flex items-center gap-3 md:flex-col md:items-center">
                                <span class="relative z-10 grid h-12 w-12 shrink-0 place-items-center rounded-full font-display text-[17px] font-bold text-white shadow-lg ring-4 ring-white {{ $tone['dot'] }}">
                                    {{ $num($i) }}
                                </span>
                                <div class="md:hidden">
                                    <p class="text-[11px] font-bold uppercase tracking-[0.14em] {{ $tone['text'] }}">{{ __('site.screening.tier', ['n' => $num($i)]) }}</p>
                                    <p class="text-[12px] text-ink-500">{{ $t['days'] }}</p>
                                </div>
                            </div>

                            <div class="mt-4 flex-1 overflow-hidden rounded-2xl border border-ink-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                                <div class="hidden items-center justify-between px-4 py-2 text-white md:flex {{ $tone['head'] }}">
                                    <span class="text-[11px] font-bold uppercase tracking-[0.14em]">{{ __('site.screening.tier', ['n' => $num($i)]) }}</span>
                                    <span class="text-[11.5px] font-semibold text-white/90">{{ $t['days'] }}</span>
                                </div>
                                <div class="p-4">
                                    <h3 class="font-display text-[15.5px] font-bold leading-snug text-ink-950">{{ $t['title'] }}</h3>
                                    <p class="mt-2 inline-flex items-start gap-1.5 rounded-lg px-2 py-1 text-[11.5px] font-semibold {{ $tone['soft'] }} {{ $tone['text'] }}">
                                        <x-ui-icon name="users" class="mt-px h-3.5 w-3.5 shrink-0" /> {{ $t['by'] }}
                                    </p>
                                    <p class="mt-3 text-[13px] leading-relaxed text-ink-500">{{ $t['text'] }}</p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ol>

                {{-- The same four, as a legend --}}
                <div class="mt-8 flex flex-wrap items-center justify-center gap-x-2 gap-y-2 border-t border-ink-100 pt-6 text-[12px] text-ink-500">
                    @foreach ($tiers as $i => $t)
                        <span class="inline-flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full {{ ($tierTone[$i] ?? $tierTone[0])['dot'] }}"></span>{{ $t['title'] }}
                        </span>
                        @unless ($loop->last)
                            <x-ui-icon name="chevron-right" class="h-3.5 w-3.5 text-ink-300" />
                        @endunless
                    @endforeach
                </div>
            </div>

            {{-- On to the form --}}
            <div class="reveal mt-10 flex flex-col items-center justify-between gap-5 rounded-3xl bg-brand-50 p-6 text-center ring-1 ring-brand-100 sm:flex-row sm:p-8 sm:text-left">
                <div>
                    <h3 class="font-display text-[19px] font-bold text-ink-950">{{ __('site.screening.cta_title') }}</h3>
                    <p class="mt-1 text-[14px] text-ink-600">{{ __('site.screening.cta_text') }}</p>
                </div>
                <a href="{{ route('ideas.create') }}" class="btn-primary shrink-0">
                    {{ __('site.actions.submit_idea') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                </a>
            </div>
        </div>
    </section>
</x-layouts.app>
