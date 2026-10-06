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
        $outcomes = collect(__('site.screening.outcomes'))->keyBy('key');
        $tiers = __('site.screening.tiers');
        $totalDays = collect($tiers)->max('to');

        // One look per outcome: the accent, the dot and the pill.
        $outcomeTone = [
            'approved' => ['bar' => 'bg-brand-600', 'dot' => 'bg-emerald-500', 'pill' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'line' => '#316d31', 'icon' => 'arrow-right'],
            'revision' => ['bar' => 'bg-amber-500', 'dot' => 'bg-amber-500', 'pill' => 'bg-amber-50 text-amber-700 ring-amber-200', 'line' => '#d48a06', 'icon' => 'clock'],
            'rejected' => ['bar' => 'bg-red-500', 'dot' => 'bg-red-500', 'pill' => 'bg-red-50 text-red-700 ring-red-200', 'line' => '#c2413b', 'icon' => 'document'],
        ];

        // One colour per tier: the first is the gate, the last the board.
        $tierTone = [
            ['head' => 'bg-gradient-to-b from-brand-500 to-brand-700', 'solid' => '#316d31', 'text' => 'text-brand-700', 'soft' => 'bg-brand-50 ring-brand-100', 'check' => 'text-brand-600 bg-brand-50 ring-brand-100'],
            ['head' => 'bg-gradient-to-b from-navy-500 to-navy-600', 'solid' => '#0f4280', 'text' => 'text-navy-600', 'soft' => 'bg-navy-50 ring-navy-100', 'check' => 'text-navy-600 bg-navy-50 ring-navy-100'],
            ['head' => 'bg-gradient-to-b from-navy-700 to-navy-800', 'solid' => '#021c42', 'text' => 'text-navy-800', 'soft' => 'bg-ink-50 ring-ink-200', 'check' => 'text-navy-800 bg-ink-50 ring-ink-200'],
            ['head' => 'bg-gradient-to-b from-amber-400 to-amber-500', 'solid' => '#e48a06', 'text' => 'text-amber-700', 'soft' => 'bg-amber-50 ring-amber-200', 'check' => 'text-amber-600 bg-amber-50 ring-amber-200'],
        ];

        $num = fn ($value) => app()->getLocale() === 'bn'
            ? strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
            : (string) $value;

        $stepLabel = 'font-display text-[11.5px] font-bold uppercase tracking-[0.2em] text-ink-500';
    @endphp

    {{-- ---------------- Who can apply → submission → screening → outcomes ----------------

         On wide screens a single left-to-right diagram: the three kinds of
         applicant converge on submission, screening branches into the three
         outcomes, and two dashed loops show the ways back — a revision returns
         to screening, an idea not selected can come round again next cycle.
         Narrower screens get the same story stacked, top to bottom. --}}
    <section class="bg-ink-50/60 py-16 sm:py-20">
        <div class="container-rich">

            {{-- Step labels --}}
            <div class="reveal hidden grid-cols-[minmax(0,1fr)_56px_190px_56px_210px_72px_minmax(0,1fr)] border-b border-ink-200 pb-4 xl:grid">
                <p class="{{ $stepLabel }}"><span class="text-brand-600">01</span> · {{ __('site.screening.who_title') }}</p>
                <p class="col-start-3 col-span-3 {{ $stepLabel }}"><span class="text-brand-600">02</span> · {{ __('site.screening.flow_step') }}</p>
                <p class="col-start-7 {{ $stepLabel }}"><span class="text-brand-600">03</span> · {{ __('site.screening.outcomes_title') }}</p>
            </div>

            <div class="mt-0 grid gap-y-6 xl:mt-8 xl:grid-cols-[minmax(0,1fr)_56px_190px_56px_210px_72px_minmax(0,1fr)] xl:gap-y-0">

                {{-- 01 Applicants --}}
                <div class="xl:col-start-1 xl:row-start-1">
                    <div class="mb-4 flex items-center justify-between gap-3 xl:hidden">
                        <p class="{{ $stepLabel }}"><span class="text-brand-600">01</span> · {{ __('site.screening.who_title') }}</p>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-[12px] font-semibold text-brand-700 ring-1 ring-brand-100">
                            <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>{{ __('site.screening.who_note') }}
                        </span>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3 xl:h-full xl:grid-cols-1 xl:grid-rows-3">
                        @foreach ($applicants as $a)
                            <div class="reveal flex items-center gap-4 rounded-2xl border border-ink-100 bg-white p-4 shadow-[0_18px_40px_-34px_rgba(7,20,38,0.5)] sm:flex-col sm:text-center xl:flex-row xl:text-left"
                                 style="transition-delay: {{ $loop->index * 70 }}ms">
                                <span class="grid h-14 w-14 shrink-0 place-items-center rounded-2xl bg-gradient-to-br from-navy-600 to-navy-800 text-white shadow-lg shadow-navy-700/25">
                                    <x-ui-icon :name="$a['icon']" class="h-6 w-6" />
                                </span>
                                <div class="min-w-0">
                                    <h3 class="font-display text-[16.5px] font-bold leading-snug text-ink-950">{{ $a['title'] }}</h3>
                                    <p class="mt-0.5 text-[13.5px] text-ink-500">{{ $a['text'] }}</p>
                                    <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-brand-50 px-2.5 py-0.5 text-[10.5px] font-bold uppercase tracking-[0.12em] text-brand-700 ring-1 ring-brand-200">
                                        <x-ui-icon name="check" class="h-3 w-3" /> {{ __('site.screening.eligible') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Converging connector (wide screens) --}}
                <div class="relative hidden xl:col-start-2 xl:row-start-1 xl:block" aria-hidden="true">
                    <svg class="absolute inset-0 h-full w-full" viewBox="0 0 56 300" preserveAspectRatio="none" fill="none">
                        <g stroke="#97a1b4" stroke-width="2.5" vector-effect="non-scaling-stroke">
                            <path d="M0 47 C 30 47, 26 150, 50 150" vector-effect="non-scaling-stroke"/>
                            <path d="M0 150 L 50 150" vector-effect="non-scaling-stroke"/>
                            <path d="M0 253 C 30 253, 26 150, 50 150" vector-effect="non-scaling-stroke"/>
                        </g>
                    </svg>
                    <span class="absolute right-0 top-1/2 -mt-[7px] h-0 w-0 border-y-[7px] border-l-[11px] border-y-transparent border-l-ink-400"></span>
                </div>
                <div class="flex justify-center xl:hidden" aria-hidden="true"><x-ui-icon name="chevron-down" class="h-6 w-6 text-ink-400" /></div>

                {{-- 02 Submission — centred in its row; below it, the dashed way back
                     for ideas not selected rises to meet it --}}
                <div class="xl:col-start-3 xl:row-start-1 xl:flex xl:flex-col">
                    <div class="hidden flex-1 xl:block" aria-hidden="true"></div>
                    <p class="mb-4 xl:hidden {{ $stepLabel }}"><span class="text-brand-600">02</span> · {{ __('site.screening.flow_step') }}</p>
                    <div class="reveal relative z-10 flex items-center gap-4 rounded-3xl border border-ink-200 bg-white p-5 shadow-[0_18px_40px_-30px_rgba(7,20,38,0.45)] xl:flex-col xl:gap-3 xl:px-4 xl:py-6 xl:text-center">
                        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-navy-50 text-navy-700">
                            <x-ui-icon name="document" class="h-6 w-6" />
                        </span>
                        <div>
                            <p class="font-display text-[18px] font-bold leading-tight text-ink-950">{{ __('site.screening.flow_submit') }}</p>
                            <p class="mt-1 text-[13px] text-ink-500">{{ __('site.screening.flow_submit_note') }}</p>
                        </div>
                    </div>
                    <div class="relative hidden flex-1 xl:block" aria-hidden="true">
                        <span class="absolute bottom-0 left-1/2 top-2 border-l-2 border-dashed border-[#c2413b]"></span>
                        <span class="absolute left-1/2 top-0 -ml-[6px] h-0 w-0 border-x-[7px] border-b-[11px] border-x-transparent border-b-[#c2413b]"></span>
                    </div>
                </div>

                <div class="relative hidden xl:col-start-4 xl:row-start-1 xl:block" aria-hidden="true">
                    <span class="absolute left-0 right-[10px] top-1/2 h-[3px] -translate-y-1/2 rounded-full bg-navy-700"></span>
                    <span class="absolute right-0 top-1/2 -mt-2 h-0 w-0 border-y-8 border-l-[12px] border-y-transparent border-l-navy-700"></span>
                </div>
                <div class="flex justify-center xl:hidden" aria-hidden="true"><x-ui-icon name="chevron-down" class="h-6 w-6 text-navy-700" /></div>

                {{-- Screening --}}
                <div class="xl:col-start-5 xl:row-start-1 xl:self-center">
                    <div class="reveal relative overflow-hidden rounded-3xl bg-gradient-to-br from-navy-600 to-navy-800 p-5 text-white shadow-[0_28px_50px_-24px_rgba(2,22,52,0.7)] xl:px-5 xl:py-8 xl:text-center">
                        <div class="pointer-events-none absolute -right-12 -top-12 h-36 w-36 rounded-full bg-brand-500/25 blur-2xl" aria-hidden="true"></div>
                        <div class="relative flex items-center gap-4 xl:flex-col xl:gap-3">
                            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-white/10 ring-1 ring-white/20">
                                <x-ui-icon name="search" class="h-6 w-6" />
                            </span>
                            <div>
                                <p class="font-display text-[21px] font-extrabold leading-tight">{{ __('site.screening.flow_screen') }}</p>
                                <p class="mt-1 text-[13px] text-navy-200">{{ __('site.screening.flow_screen_note') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Branching connector, with the revision loop running back (wide screens) --}}
                <div class="relative hidden xl:col-start-6 xl:row-start-1 xl:block" aria-hidden="true">
                    <svg class="absolute inset-0 h-full w-full" viewBox="0 0 72 300" preserveAspectRatio="none" fill="none">
                        <path d="M0 132 C 36 132, 30 47, 64 47" stroke="#316d31" stroke-width="2.5" vector-effect="non-scaling-stroke"/>
                        <path d="M0 150 L 64 150" stroke="#d48a06" stroke-width="2.5" vector-effect="non-scaling-stroke"/>
                        <path d="M0 168 C 36 168, 30 253, 64 253" stroke="#c2413b" stroke-width="2.5" vector-effect="non-scaling-stroke"/>
                        <path d="M72 178 L 10 178" stroke="#d48a06" stroke-width="2" stroke-dasharray="6 6" vector-effect="non-scaling-stroke"/>
                    </svg>
                    <span class="absolute right-0 top-[15.67%] -mt-[7px] h-0 w-0 border-y-[7px] border-l-[11px] border-y-transparent border-l-[#316d31]"></span>
                    <span class="absolute right-0 top-1/2 -mt-[7px] h-0 w-0 border-y-[7px] border-l-[11px] border-y-transparent border-l-[#d48a06]"></span>
                    <span class="absolute right-0 top-[84.33%] -mt-[7px] h-0 w-0 border-y-[7px] border-l-[11px] border-y-transparent border-l-[#c2413b]"></span>
                    <span class="absolute left-0 top-[59.33%] -mt-[6px] h-0 w-0 border-y-[6px] border-r-[10px] border-y-transparent border-r-[#d48a06]"></span>
                </div>
                <div class="flex justify-center xl:hidden" aria-hidden="true"><x-ui-icon name="chevron-down" class="h-6 w-6 text-ink-400" /></div>

                {{-- 03 Outcomes --}}
                <div class="xl:col-start-7 xl:row-start-1">
                    <p class="mb-4 xl:hidden {{ $stepLabel }}"><span class="text-brand-600">03</span> · {{ __('site.screening.outcomes_title') }}</p>
                    <div class="grid gap-4 sm:grid-cols-3 xl:h-full xl:grid-cols-1 xl:grid-rows-3">
                        @foreach ($outcomes as $key => $o)
                            @php $tone = $outcomeTone[$key] ?? $outcomeTone['approved']; @endphp
                            <div class="reveal relative flex flex-col justify-center overflow-hidden rounded-2xl border border-ink-100 bg-white py-4 pl-7 pr-4 shadow-[0_18px_40px_-34px_rgba(7,20,38,0.5)]"
                                 style="transition-delay: {{ $loop->index * 70 }}ms">
                                <span class="absolute inset-y-0 left-0 w-2 {{ $tone['bar'] }}" aria-hidden="true"></span>
                                <p class="inline-flex items-center gap-2.5 font-display text-[17.5px] font-extrabold text-ink-950">
                                    <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $tone['dot'] }}"></span>{{ $o['label'] }}
                                </p>
                                <span class="mt-2.5 inline-flex w-fit items-center gap-1.5 rounded-full px-3 py-1 text-[12.5px] font-bold ring-1 {{ $tone['pill'] }}">
                                    {{ $o['note'] }} <x-ui-icon :name="$tone['icon']" class="h-3.5 w-3.5" />
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- The way back for ideas not selected: from Rejected round to Submission (wide screens) --}}
                <div class="relative hidden h-16 xl:col-start-3 xl:row-start-2 xl:block" aria-hidden="true">
                    <span class="absolute bottom-6 left-1/2 right-0 top-0 rounded-bl-2xl border-b-2 border-l-2 border-dashed border-[#c2413b]"></span>
                </div>
                <div class="relative hidden h-16 xl:col-start-4 xl:col-end-7 xl:row-start-2 xl:block">
                    <span class="absolute inset-x-0 top-[38px] border-b-2 border-dashed border-[#c2413b]" aria-hidden="true"></span>
                    <span class="absolute left-1/2 top-[38px] -translate-x-1/2 -translate-y-1/2 whitespace-nowrap rounded-full border border-red-200 bg-white px-4 py-1.5 text-[13px] font-semibold text-red-700 shadow-sm">
                        {{ __('site.screening.reapply') }}
                    </span>
                </div>
                <div class="relative hidden h-16 xl:col-start-7 xl:row-start-2 xl:block" aria-hidden="true">
                    <span class="absolute bottom-6 left-0 top-0 w-24 rounded-br-2xl border-b-2 border-r-2 border-dashed border-[#c2413b]"></span>
                </div>
            </div>

            {{-- Key, and the note the loops carry on narrow screens --}}
            <div class="mt-8 flex flex-wrap items-center justify-between gap-x-6 gap-y-3 border-t border-ink-200 pt-5 text-[13px] text-ink-500">
                <p class="flex items-start gap-2 xl:hidden">
                    <x-ui-icon name="bell" class="mt-0.5 h-4 w-4 shrink-0 text-amber-500" /> {{ __('site.screening.outcomes_footnote') }}
                </p>
                <p class="hidden items-center gap-2.5 xl:inline-flex">
                    <span class="w-8 border-t-2 border-dashed border-ink-400" aria-hidden="true"></span>{{ __('site.screening.loop_note') }}
                </p>
                <span class="hidden items-center gap-1.5 rounded-full bg-brand-50 px-3 py-1 text-[12px] font-semibold text-brand-700 ring-1 ring-brand-100 xl:inline-flex">
                    <span class="h-1.5 w-1.5 rounded-full bg-brand-500"></span>{{ __('site.screening.who_note') }}
                </span>
            </div>
        </div>
    </section>

    {{-- ---------------- Screening & approval pipeline ----------------

         The 21 working days drawn to scale — each tier as wide as the days it
         takes — over a day ruler, with dotted leads down to a card per tier. --}}
    <section id="pipeline" class="scroll-mt-28 bg-white py-16 sm:py-20">
        <div class="container-rich">
            <div class="reveal flex flex-wrap items-end justify-between gap-5">
                <div>
                    <h2 class="font-display text-[26px] font-extrabold tracking-tight text-ink-950 sm:text-[34px]">{{ __('site.screening.pipeline_title') }}</h2>
                    <p class="mt-2 max-w-2xl text-[15px] leading-relaxed text-ink-600">{{ __('site.screening.sequential') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full bg-gradient-to-b from-amber-400 to-amber-500 px-4 py-2 font-display text-[14px] font-extrabold text-white shadow-[0_10px_22px_-10px_rgba(228,138,6,0.8)]">
                        <x-ui-icon name="clock" class="h-4 w-4" /> {{ __('site.screening.pipeline_badge') }}
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-navy-50 px-4 py-2 text-[13px] font-medium text-navy-700 ring-1 ring-navy-100">
                        <x-ui-icon name="calendar" class="h-4 w-4" /> {{ __('site.screening.pipeline_note') }}
                    </span>
                </div>
            </div>

            {{-- Timeline, to scale --}}
            <div class="reveal mt-10 hidden lg:block">
                <p class="{{ $stepLabel }}">{{ __('site.screening.timeline') }}</p>
                <div class="mt-4 flex h-16 overflow-hidden rounded-2xl shadow-[0_16px_36px_-24px_rgba(2,22,52,0.6)]">
                    @foreach ($tiers as $i => $t)
                        @php $span = $t['to'] - $t['from'] + 1; @endphp
                        <div class="flex flex-col items-center justify-center text-white {{ ($tierTone[$i] ?? $tierTone[0])['head'] }} @unless ($loop->last) border-r-[3px] border-white @endunless"
                             style="flex: {{ $span }} 1 0%">
                            <span class="font-display text-[13px] font-extrabold uppercase tracking-[0.12em]">{{ __('site.screening.tier', ['n' => $num($i)]) }}</span>
                            <span class="text-[12.5px] font-semibold text-white/85">{{ __('site.screening.days_count', ['n' => $num($span)]) }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- One mark per working day; each tier's first and last day picked out in its colour --}}
                @php
                    $edges = [];
                    foreach ($tiers as $i => $t) { $edges[$t['from']] = $edges[$t['to']] = ($tierTone[$i] ?? $tierTone[0])['solid']; }
                @endphp
                <div class="relative mt-2">
                    <span class="absolute -left-1 top-[13px] -translate-x-full text-[11.5px] font-semibold text-ink-400">{{ __('site.screening.day') }}</span>
                    <div class="grid" style="grid-template-columns: repeat({{ $totalDays }}, minmax(0, 1fr))">
                        @for ($d = 1; $d <= $totalDays; $d++)
                            <div class="flex flex-col items-center">
                                <span class="w-px {{ isset($edges[$d]) ? 'h-2.5 w-[2px]' : 'h-1.5 bg-ink-300' }}" @isset($edges[$d]) style="background: {{ $edges[$d] }}" @endisset></span>
                                <span class="mt-1 text-[11.5px] tabular-nums {{ isset($edges[$d]) ? 'font-bold' : 'text-ink-400' }}" @isset($edges[$d]) style="color: {{ $edges[$d] }}" @endisset>{{ $num($d) }}</span>
                            </div>
                        @endfor
                    </div>
                </div>

                {{-- Dotted leads from each tier's span down to its card --}}
                @php
                    $gutter = 1.6; // % of the width taken by each gap between cards (gap-5 at this size, roughly)
                    $cardW = (100 - 3 * $gutter) / 4;
                @endphp
                <svg class="mt-1 block h-10 w-full" viewBox="0 0 1000 40" preserveAspectRatio="none" fill="none" aria-hidden="true">
                    @foreach ($tiers as $i => $t)
                        @php
                            $from = (($t['from'] - 1 + $t['to']) / 2) / $totalDays * 1000;
                            $to = ($i * ($cardW + $gutter) + $cardW / 2) * 10;
                        @endphp
                        <path d="M{{ $from }} 0 C {{ $from }} 22, {{ $to }} 18, {{ $to }} 40" stroke="{{ ($tierTone[$i] ?? $tierTone[0])['solid'] }}" stroke-width="2" stroke-dasharray="2 6" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
                    @endforeach
                </svg>
            </div>

            {{-- A card per tier --}}
            <ol class="mt-8 grid gap-5 sm:grid-cols-2 lg:mt-0 lg:grid-cols-4">
                @foreach ($tiers as $i => $t)
                    @php $tone = $tierTone[$i] ?? $tierTone[0]; $who = array_map('trim', explode('+', $t['by'])); @endphp
                    <li class="reveal relative flex flex-col overflow-visible" style="transition-delay: {{ $i * 80 }}ms">
                        <div class="flex flex-1 flex-col overflow-hidden rounded-3xl border border-ink-100 bg-white shadow-[0_22px_44px_-34px_rgba(7,20,38,0.55)] transition hover:-translate-y-1 hover:shadow-[0_28px_50px_-30px_rgba(7,20,38,0.55)]">
                            <div class="flex items-center justify-between px-6 py-4 text-white {{ $tone['head'] }}">
                                <span class="font-display text-[13px] font-extrabold uppercase tracking-[0.14em]">{{ __('site.screening.tier', ['n' => $num($i)]) }}</span>
                                <span class="font-display text-[15px] font-bold">{{ $t['days'] }}</span>
                            </div>
                            <div class="flex flex-1 flex-col p-6">
                                <h3 class="font-display text-[20px] font-extrabold leading-snug text-ink-950">{{ $t['title'] }}</h3>

                                <p class="mt-5 text-[11px] font-bold uppercase tracking-[0.18em] text-ink-400">{{ __('site.screening.reviewed_by') }}</p>
                                <div class="mt-2 flex gap-3 rounded-2xl px-4 py-3 ring-1 lg:min-h-[104px] {{ $tone['soft'] }}">
                                    <x-ui-icon name="users" class="mt-0.5 h-5 w-5 shrink-0 {{ $tone['text'] }}" />
                                    <div class="space-y-1">
                                        @foreach ($who as $person)
                                            <p class="font-display text-[15px] font-bold leading-snug {{ $tone['text'] }}">{{ $loop->first ? '' : '+ ' }}{{ $person }}</p>
                                        @endforeach
                                    </div>
                                </div>

                                <p class="mt-6 text-[11px] font-bold uppercase tracking-[0.18em] text-ink-400">{{ $loop->last ? __('site.screening.confirmed') : __('site.screening.checked_for') }}</p>
                                <ul class="mt-3 space-y-2.5">
                                    @foreach ($t['checks'] ?? [] as $check)
                                        <li class="flex items-center gap-3 text-[15px] text-ink-800">
                                            <span class="grid h-6 w-6 shrink-0 place-items-center rounded-full ring-1 {{ $tone['check'] }}">
                                                <x-ui-icon name="check" class="h-3.5 w-3.5" />
                                            </span>
                                            {{ $check }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        @unless ($loop->last)
                            <span class="absolute -right-[22px] top-1/2 z-10 hidden h-9 w-9 -translate-y-1/2 place-items-center rounded-full bg-white text-navy-700 shadow-md ring-1 ring-ink-100 lg:grid" aria-hidden="true">
                                <x-ui-icon name="chevron-right" class="h-4 w-4" />
                            </span>
                        @endunless
                    </li>
                @endforeach
            </ol>

            <p class="mt-6 text-[13px] text-ink-500">
                <span class="font-bold text-brand-700">TRL</span>{{ \Illuminate\Support\Str::after(__('site.screening.trl_note'), 'TRL') }}
            </p>

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
