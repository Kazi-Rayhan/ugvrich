<x-layouts.app :title="__('site.nav.research')" :description="__('research_hub.hero.lead')">

    @php
        /*
         | The Research hub — what RICH is, and what it is being built to become.
         |
         | A vision page. Everything factual comes from one of two places: the
         | Research Wing's own framework document, or a count from the database.
         | Capabilities that do not exist yet are named as intentions and carry a
         | "planned" badge, so nobody reads them as something they can use today.
         |
         | The structure is kept flat and section-by-section on purpose: when a
         | module is actually built, its section can be replaced on its own
         | without the rest of the page being redesigned around it.
         */
        $eyebrow = 'reveal inline-flex items-center gap-2 rounded-full bg-brand-50 px-3.5 py-1.5 text-[11px] font-bold uppercase tracking-[0.18em] text-brand-700 ring-1 ring-brand-100';
        $h2 = 'reveal mt-4 max-w-3xl font-display text-[30px] font-bold leading-[1.08] tracking-[-0.02em] text-ink-950 sm:text-[42px]';
        $lead = 'reveal mt-6 max-w-2xl text-[16.5px] leading-[1.8] text-ink-600';
        $label = 'text-[10.5px] font-semibold uppercase tracking-[0.18em] text-ink-400';
        $delay = fn ($i, $step = 45, $max = 320) => 'transition-delay: '.min($i * $step, $max).'ms';

        $num = fn ($value) => app()->getLocale() === 'bn'
            ? strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
            : (string) $value;

        /* Priority areas, from the framework: [department, field, SDGs, funding
           alignment]. Grouped by department, which is the Department → Research
           field path the hub is meant to offer. */
        $areas = collect($framework['priority_areas'] ?? []);
        $byDepartment = $areas->groupBy(0);

        /* Every SDG the framework's areas name, and how many fields serve each.
           Real counts off real data — no goal is listed that nothing works on.

           The Bangla framework writes its goal numbers in Bengali digits
           ("এসডিজি ৪, ১০"), which \d does not match, so the digits are brought
           back to ASCII before counting. Without this the whole section
           disappeared in Bangla while looking perfectly fine in English. */
        $ascii = fn (string $text) => strtr($text, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4',
                                                    '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);

        $sdgCounts = $areas
            ->flatMap(fn ($area) => preg_match_all('/(\d+)/', $ascii((string) ($area[2] ?? '')), $m) ? $m[1] : [])
            ->countBy()
            ->sortDesc();

        $goals = __('research_hub.sdg.goals');

        /* The annual faculty research funding cycle, from config/research_calls.php. */
        $calls = config('research_calls');
        $faculties = collect($calls['faculties']);
        $min = $calls['shortlist_min'];
        $max = $calls['shortlist_max'];
        $funded = $calls['funded_per_faculty'];
        $callVars = ['min' => $num($min), 'max' => $num($max), 'funded' => $num($funded), 'total' => $num($funded * $faculties->count())];

        /* The external cycle, for researchers and partners outside UGV. */
        $external = $calls['external'];
        $externalVars = ['funded' => $num($external['funded_per_year'])];

        $range = fn ($from, $to) => $from === $to ? $num($from) : $num($from).'–'.$num($to);

        /* Fills :placeholders in a [title, body] pair from the lang file. */
        $fill = fn (array $pair, array $vars) => array_map(
            fn ($text) => strtr($text, collect($vars)->mapWithKeys(fn ($v, $k) => [':'.$k => $v])->all()),
            $pair,
        );

    @endphp

    {{-- ---------------- 1. Hero ---------------- --}}
    <section class="relative isolate overflow-hidden bg-navy-700 text-white">
        <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.15]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-40 -top-48 -z-10 h-[34rem] w-[34rem] rounded-full bg-brand-600/35 blur-[130px]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-52 -left-32 -z-10 h-[30rem] w-[30rem] rounded-full border border-white/10" aria-hidden="true"></div>

        <div class="container-rich py-20 sm:py-28">
            <p class="reveal eyebrow-invert">{{ __('research_hub.hero.eyebrow') }}</p>

            <h1 class="reveal mt-7 max-w-4xl font-display text-[38px] font-bold leading-[1.03] tracking-[-0.03em] !text-white sm:text-[62px]">
                {!! __('research_hub.hero.title') !!}
            </h1>

            <p class="reveal mt-7 max-w-2xl text-[17.5px] leading-[1.8] text-white/75">{{ __('research_hub.hero.lead') }}</p>

            {{-- The first action on the page is the one a researcher came to
                 take: send in a proposal. The other two lead into the page. --}}
            <div class="reveal mt-10 flex flex-wrap gap-3">
                {{-- Two doors into the portal: one for people who have not
                     been here before, one for people who have. The sign-in
                     link was missing entirely before. --}}
                @auth
                    <a href="{{ route('researcher.dashboard') }}" class="btn-lead group">
                        <span class="relative">{{ __('research_hub.portal.dashboard') }}</span>
                        <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                    </a>
                @else
                    <a href="{{ route('researcher.register') }}" class="btn-lead group">
                        <span class="relative">{{ __('research_hub.portal.join') }}</span>
                        <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                    </a>
                    <a href="{{ route('login') }}" class="btn-invert px-7 py-4 text-[15px]">
                        {{ __('research_hub.portal.sign_in') }}
                    </a>
                @endauth

                <a href="#areas" class="inline-flex items-center gap-2 px-2 py-3 text-[14px] font-semibold text-white/70 transition hover:text-white">
                    {{ __('research_hub.hero.secondary') }}
                    <x-ui-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            <p class="reveal mt-8 inline-flex max-w-2xl items-center gap-2 rounded-full border border-white/15 bg-white/[0.06] px-4 py-2 text-[13px] text-white/70 backdrop-blur-sm">
                <x-ui-icon name="compass" class="h-4 w-4 shrink-0 text-brand-300" />
                {{ __('research_hub.hero.note') }}
            </p>
        </div>
    </section>

    {{-- ---------------- Annual faculty research funding ----------------

         The yearly cycle as a funnel: unlimited proposals, the top few in each
         faculty announced, one funded per faculty. Every figure comes from
         config/research_calls.php. --}}
    <section id="funding-call" class="scroll-mt-24 border-b border-ink-100 bg-white py-20 sm:py-24">
        <div class="container-rich">
            <div class="flex flex-wrap items-end justify-between gap-6">
                <div>
                    <p class="{{ $eyebrow }}"><span class="text-brand-300">//</span> {{ __('research_hub.calls.eyebrow') }}</p>
                    <h2 class="{{ $h2 }}">{{ __('research_hub.calls.title') }}</h2>
                    <p class="{{ $lead }}">{{ __('research_hub.calls.lead', $callVars) }}</p>
                </div>

                <a href="{{ route('research.proposal') }}" class="reveal btn-primary group shrink-0">
                    {{ __('research_hub.calls.cta') }}
                    <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" />
                </a>
            </div>

            {{-- The faculty funnel: each stage narrower than the last --}}
            <p class="reveal mt-12 {{ $label }}">{{ __('research_hub.calls.faculty_label') }}</p>
            @include('partials.research.funnel', [
                'stages' => collect(['proposals' => '∞', 'shortlist' => $range($min, $max), 'funded' => $num($funded)])
                    ->map(fn ($value, $stage) => [$value, ...$fill(__('research_hub.calls.funnel.'.$stage), $callVars)])
                    ->values()->all(),
                'perks' => __('research_hub.calls.perks'),
            ])


            {{-- The external funnel: the same three stages, for work from outside UGV --}}
            <p class="reveal mt-14 {{ $label }}">{{ __('research_hub.calls.external.label') }}</p>
            @include('partials.research.funnel', [
                'stages' => collect([
                    'proposals' => '∞',
                    'shortlist' => $range($external['shortlist_min'], $external['shortlist_max']),
                    'funded' => $num($external['funded_per_year']),
                ])
                    ->map(fn ($value, $stage) => [$value, ...$fill(__('research_hub.calls.external.funnel.'.$stage), $externalVars)])
                    ->values()->all(),
                'perks' => __('research_hub.calls.perks'),
            ])

            {{-- One card per faculty: its top proposals, and what it funds --}}
            <ul class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($faculties as $key => $icon)
                    <li class="reveal flex flex-col rounded-[1.5rem] border border-ink-100 bg-white p-6 shadow-[0_20px_50px_-40px_rgba(7,20,38,0.45)]" style="{{ $delay($loop->index) }}">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <x-ui-icon :name="$icon" class="h-5 w-5" />
                        </span>
                        <h3 class="mt-4 font-display text-[16.5px] font-bold leading-snug text-ink-950">{{ __('research_hub.calls.faculties.'.$key) }}</h3>

                        <div class="mt-auto pt-5">
                            <p class="{{ $label }}">{{ __('research_hub.calls.shortlist_label') }}</p>
                            {{-- Solid: always announced. Dotted: announced when the year's proposals earn it. --}}
                            <div class="mt-2.5 flex gap-1.5" aria-hidden="true">
                                @for ($c = 1; $c <= $max; $c++)
                                    <span @class([
                                        'h-2.5 flex-1 rounded-full',
                                        'bg-brand-600' => $c <= $min,
                                        'border-2 border-dotted border-brand-300' => $c > $min,
                                    ])></span>
                                @endfor
                            </div>
                            <p class="mt-2 text-[12.5px] text-ink-500">{{ __('research_hub.calls.shortlist_range', $callVars) }}</p>

                            <div class="mt-4 flex items-center justify-between gap-3 rounded-xl bg-brand-50 px-3.5 py-2.5">
                                <span class="text-[12.5px] font-medium text-brand-800">{{ __('research_hub.calls.funded_label') }}</span>
                                <span class="inline-flex items-center gap-1.5 text-[13px] font-bold text-brand-700">
                                    <x-ui-icon name="star" class="h-4 w-4" />
                                    {{ trans_choice('research_hub.calls.funded_value', $funded, ['count' => $num($funded)]) }}
                                </span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            {{-- How the year runs --}}
            <div class="reveal mt-14">
                <p class="{{ $label }}">{{ __('research_hub.calls.steps_title') }}</p>

                <div class="relative mt-6">
                    <div class="absolute left-[12.5%] right-[12.5%] top-5 hidden border-t-[3px] border-dotted border-brand-300 lg:block" aria-hidden="true"></div>
                    <ol class="relative grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach (__('research_hub.calls.steps') as $i => [$title, $body])
                            <li class="relative flex flex-col items-start lg:items-center lg:text-center">
                                <span @class([
                                    'relative flex h-10 w-10 items-center justify-center rounded-full font-display text-[14px] font-bold ring-4 ring-white',
                                    'bg-brand-600 text-white' => $loop->last,
                                    'border-2 border-brand-600 bg-white text-brand-700' => ! $loop->last,
                                ])>
                                    @if ($loop->last)
                                        <x-ui-icon name="star" class="h-4 w-4" />
                                    @else
                                        {{ $num($i + 1) }}
                                    @endif
                                </span>
                                <p class="mt-4 font-display text-[15.5px] font-bold text-ink-950">{{ $title }}</p>
                                <p class="mt-1.5 max-w-[16rem] text-[13.5px] leading-relaxed text-ink-600">{{ strtr($body, [':min' => $callVars['min'], ':max' => $callVars['max'], ':funded' => $callVars['funded']]) }}</p>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </section>

    {{-- ---------------- 2. What is RICH ---------------- --}}
    <section class="bg-white py-20 sm:py-24">
        <div class="container-rich grid gap-x-14 gap-y-10 lg:grid-cols-[1.1fr_0.9fr]">
            <div>
                <p class="{{ $eyebrow }}"><span class="text-brand-300">//</span> {{ __('research_hub.about.eyebrow') }}</p>
                <h2 class="{{ $h2 }}">{{ __('research_hub.about.title') }}</h2>
                <p class="{{ $lead }}">{{ __('research_hub.about.lead') }}</p>

                <a href="{{ route('research.framework') }}"
                   class="reveal group mt-7 inline-flex items-center gap-2 text-[13.5px] font-semibold text-brand-700 transition-all hover:gap-3">
                    <x-ui-icon name="document" class="h-4 w-4" />
                    {{ __('research_hub.about.framework_link') }}
                    <x-ui-icon name="arrow-right" class="h-3.5 w-3.5" />
                </a>
            </div>

            <div class="reveal self-start rounded-[1.75rem] border border-ink-100 bg-ink-50/70 p-7 sm:p-8">
                <p class="{{ $label }}">{{ __('research_hub.about.brings_together') }}</p>

                <ul class="mt-5 grid grid-cols-2 gap-2.5">
                    @foreach (__('research_hub.about.items') as $i => $item)
                        <li class="flex items-center gap-2 rounded-xl bg-white px-3 py-2.5 text-[13.5px] font-medium text-ink-700 ring-1 ring-ink-100">
                            <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- ---------------- 3. Research lifecycle ---------------- --}}
    <section id="journey" class="scroll-mt-24 border-y border-ink-100 bg-ink-50 py-20 sm:py-24">
        <div class="container-rich">
            <p class="{{ $eyebrow }}"><span class="text-brand-300">//</span> {{ __('research_hub.lifecycle.eyebrow') }}</p>
            <h2 class="{{ $h2 }}">{{ __('research_hub.lifecycle.title') }}</h2>
            <p class="{{ $lead }}">{{ __('research_hub.lifecycle.lead') }}</p>

            {{-- The journey, grouped.

                 Eleven equal steps is a list; three phases with the steps
                 inside them is a shape somebody can hold in their head. The
                 grouping is editorial, not a second workflow — the eleven
                 stages are unchanged underneath. --}}
            @php
                /* 1–5 settle the question, 6–8 do the work, 9–11 publish it.
                   Split here rather than in the language file so a translator
                   never has to keep two lists in step. */
                $phases = [
                    ['from' => 0, 'to' => 4],
                    ['from' => 5, 'to' => 7],
                    ['from' => 8, 'to' => 10],
                ];
                $stages = __('research_hub.lifecycle.stages');
                $phaseNames = __('research_hub.lifecycle.phases');
            @endphp

            <div class="mt-14 space-y-12">
                @foreach ($phases as $p => $phase)
                    <div class="reveal" style="{{ $delay($p, 90) }}">
                        {{-- The phase heading, set quietly against a rule --}}
                        <div class="flex items-center gap-4">
                            <span class="font-numeric text-[12px] font-bold tabular-nums text-gold-500">
                                {{ $num(str_pad($p + 1, 2, '0', STR_PAD_LEFT)) }}
                            </span>
                            <h3 class="font-display text-[15px] font-bold tracking-tight text-ink-950">{{ $phaseNames[$p] ?? '' }}</h3>
                            <span class="h-px flex-1 bg-ink-200" aria-hidden="true"></span>
                        </div>

                        <ol class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            @for ($i = $phase['from']; $i <= $phase['to']; $i++)
                                @continue(! isset($stages[$i]))
                                @php [$stage, $what] = $stages[$i]; @endphp

                                <li class="group relative rounded-2xl border border-ink-100 bg-white p-5 transition duration-400 hover:-translate-y-1 hover:border-brand-300 hover:shadow-[0_22px_46px_-38px_rgba(2,34,81,0.5)]">
                                    <div class="flex items-baseline gap-2.5">
                                        <span @class([
                                            'font-numeric text-[12.5px] font-bold tabular-nums',
                                            'text-gold-600' => $i === count($stages) - 1,
                                            'text-brand-500' => $i !== count($stages) - 1,
                                        ])>{{ $num(str_pad($i + 1, 2, '0', STR_PAD_LEFT)) }}</span>

                                        <h4 class="font-display text-[15.5px] font-bold leading-snug text-ink-950">{{ $stage }}</h4>
                                    </div>

                                    <p class="mt-2 text-[13.5px] leading-[1.8] text-ink-600">{{ $what }}</p>
                                </li>
                            @endfor
                        </ol>
                    </div>
                @endforeach
            </div>

            <a href="{{ route('research.framework') }}#process"
               class="reveal mt-8 inline-flex items-center gap-2 text-[13.5px] font-semibold text-brand-700 transition-all hover:gap-3">
                {{ __('research_hub.lifecycle.document_link') }}
                <x-ui-icon name="arrow-right" class="h-3.5 w-3.5" />
            </a>
        </div>
    </section>

    {{-- ---------------- 4. Research areas ----------------
         Real data: the priority fields the framework names for each department.
         The department switch is presentational — nothing is queried. --}}
    @if ($byDepartment->isNotEmpty())
        <section id="areas" class="scroll-mt-24 border-y border-ink-100 bg-ink-50 py-20 sm:py-24"
                 x-data="{ dept: @js($byDepartment->keys()->first()) }">
            <div class="container-rich">
                <p class="{{ $eyebrow }}"><span class="text-brand-300">//</span> {{ __('research_hub.areas.eyebrow') }}</p>
                <h2 class="{{ $h2 }}">{{ __('research_hub.areas.title') }}</h2>
                <p class="{{ $lead }}">{{ __('research_hub.areas.lead') }}</p>

                {{-- Departments --}}
                <div class="reveal mt-10 flex flex-wrap gap-2" role="tablist">
                    @foreach ($byDepartment as $department => $fields)
                        <button type="button" role="tab"
                                @click="dept = @js($department)"
                                :aria-selected="dept === @js($department) ? 'true' : 'false'"
                                class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-[13.5px] font-semibold transition duration-300"
                                :class="dept === @js($department)
                                    ? 'border-brand-600 bg-brand-600 text-white'
                                    : 'border-ink-200 bg-white text-ink-700 hover:border-brand-300 hover:text-brand-700'">
                            {{ $department }}
                            <span class="rounded-full px-1.5 font-numeric text-[11.5px] tabular-nums"
                                  :class="dept === @js($department) ? 'bg-white/20' : 'bg-ink-100'">{{ $num($fields->count()) }}</span>
                        </button>
                    @endforeach
                </div>

                {{-- Fields of the chosen department --}}
                @foreach ($byDepartment as $department => $fields)
                    <div x-show="dept === @js($department)" x-cloak class="mt-8">
                        <p class="{{ $label }}">
                            {{ $department }} · {{ trans_choice('research_hub.areas.count', $fields->count(), ['count' => $num($fields->count())]) }}
                        </p>

                        <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($fields as $i => $area)
                                <div class="group rounded-2xl border border-ink-100 bg-white p-5 transition duration-300 hover:border-brand-300">
                                    <h3 class="font-display text-[15px] font-bold leading-snug text-ink-950">{{ $area[1] ?? '' }}</h3>

                                    <div class="mt-3 flex flex-wrap items-center gap-1.5">
                                        @foreach (preg_split('/,\s*/u', (string) ($area[2] ?? '')) as $sdg)
                                            @if (trim($sdg) !== '')
                                                <span class="rounded-lg border border-navy-100 bg-navy-50 px-2 py-0.5 text-[11.5px] font-semibold text-navy-700">{{ trim($sdg) }}</span>
                                            @endif
                                        @endforeach
                                    </div>

                                    @isset($area[3])
                                        <div class="mt-4">
                                            <div class="flex items-center justify-between text-[11px] font-semibold text-ink-400">
                                                <span>{{ __('research_hub.areas.funding_label') }}</span>
                                                <span class="font-numeric tabular-nums text-ink-700">{{ $num($area[3]) }}%</span>
                                            </div>
                                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-ink-100">
                                                <div class="h-full rounded-full bg-brand-500" style="width: {{ (int) $area[3] }}%"></div>
                                            </div>
                                        </div>
                                    @endisset
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ---------------- 5. Research support ---------------- --}}
    <section class="border-y border-ink-100 bg-ink-50 py-20 sm:py-24">
        <div class="container-rich">
            <p class="{{ $eyebrow }}"><span class="text-brand-300">//</span> {{ __('research_hub.support.eyebrow') }}</p>
            <h2 class="{{ $h2 }}">{{ __('research_hub.support.title') }}</h2>
            <p class="{{ $lead }}">{{ __('research_hub.support.lead') }}</p>

            <div class="mt-10 flex flex-wrap gap-2.5">
                @foreach (__('research_hub.support.items') as $item)
                    <span class="rounded-full border border-ink-200 bg-white px-3 py-2 text-[12.5px] font-semibold text-ink-700">{{ $item }}</span>
                @endforeach
            </div>

            <a href="{{ route('research.support') }}" class="reveal mt-8 inline-flex items-center gap-2 text-[13.5px] font-semibold text-brand-700">
                {{ __('research_hub.support.cta') }}
                <x-ui-icon name="arrow-right" class="h-3.5 w-3.5" />
            </a>
        </div>
    </section>

    {{-- ---------------- 6. SDG research vision ---------------- --}}
    @if ($sdgCounts->isNotEmpty())
        <section class="border-y border-ink-100 bg-ink-50 py-20 sm:py-24">
            <div class="container-rich">
                <p class="{{ $eyebrow }}"><span class="text-brand-300">//</span> {{ __('research_hub.sdg.eyebrow') }}</p>
                <h2 class="{{ $h2 }}">{{ __('research_hub.sdg.title') }}</h2>
                <p class="{{ $lead }}">{{ __('research_hub.sdg.lead') }}</p>

                <div class="mt-11 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($sdgCounts as $goal => $count)
                        @continue(! isset($goals[(int) $goal]))

                        <div class="reveal flex items-start gap-4 rounded-2xl border border-ink-100 bg-white p-5" style="{{ $delay($loop->index, 35) }}">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-navy-700 font-numeric text-[17px] font-bold tabular-nums text-white">
                                {{ $num($goal) }}
                            </span>
                            <div class="min-w-0">
                                <p class="font-display text-[14.5px] font-bold leading-snug text-ink-950">{{ $goals[(int) $goal] }}</p>
                                <p class="mt-1 text-[12.5px] text-ink-500">
                                    {{ trans_choice('research_hub.sdg.areas_label', $count, ['count' => $num($count)]) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ---------------- 7. The ecosystem ---------------- --}}
    <section class="relative isolate overflow-hidden bg-navy-700 py-20 text-white sm:py-24">
        <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.12]" aria-hidden="true"></div>

        <div class="container-rich">
            <p class="reveal inline-flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.2em] text-brand-300">
                <span class="text-white/30">//</span> {{ __('research_hub.ecosystem.eyebrow') }}
            </p>
            <h2 class="reveal mt-4 font-display text-[27px] font-bold leading-[1.15] tracking-tight !text-white sm:text-[36px]">
                {{ __('research_hub.ecosystem.title') }}
            </h2>
            <p class="reveal mt-5 max-w-3xl text-[15.5px] leading-[1.85] text-white/70">{{ __('research_hub.ecosystem.lead') }}</p>

            {{-- The hub in the middle, everything else around it. A ring on wide
                 screens; a plain list on a phone, where a ring is unreadable. --}}
            <div class="reveal mt-14">
                <div class="relative mx-auto hidden aspect-square w-full max-w-[620px] lg:block">
                    {{-- The ring is drawn at exactly the radius the nodes sit
                         on, so the mark reads as the centre they share. --}}
                    <span class="absolute inset-[12%] rounded-full border border-dashed border-white/20" aria-hidden="true"></span>
                    <span class="absolute inset-[12%] rounded-full bg-brand-500/[0.04]" aria-hidden="true"></span>

                    {{-- The mark itself at the centre, not its initials. The
                         emblem is dark navy and green on transparency, so it
                         sits on a white disc to be legible against the navy. --}}
                    <div class="absolute left-1/2 top-1/2 flex h-36 w-36 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full bg-white p-5 shadow-[0_0_70px_-6px_rgba(65,132,63,0.55)] ring-1 ring-white/40">
                        <img src="{{ asset('media/logo-mark.png') }}" alt="{{ $site->name() }}"
                             width="256" height="249" class="h-full w-full object-contain">
                    </div>

                    @foreach (__('research_hub.ecosystem.nodes') as $i => $node)
                        @php
                            $count = count(__('research_hub.ecosystem.nodes'));
                            $angle = ($i / $count) * 2 * M_PI - M_PI / 2;
                            // Same 38% as the ring above (50% - 12% inset).
                            $x = 50 + 38 * cos($angle);
                            $y = 50 + 38 * sin($angle);
                        @endphp
                        <span class="absolute flex -translate-x-1/2 -translate-y-1/2 items-center rounded-full border border-white/20 bg-white/[0.08] px-4 py-2 text-[12.5px] font-semibold text-white/90 backdrop-blur-sm"
                              style="left: {{ round($x, 2) }}%; top: {{ round($y, 2) }}%">
                            {{ $node }}
                        </span>
                    @endforeach
                </div>

                <div class="grid gap-2.5 sm:grid-cols-2 lg:hidden">
                    <div class="flex items-center justify-center gap-3.5 rounded-2xl bg-white/[0.08] px-5 py-5 ring-1 ring-white/15 sm:col-span-2">
                        <span class="flex h-14 w-14 items-center justify-center rounded-full bg-white p-2">
                            <img src="{{ asset('media/logo-mark.png') }}" alt="{{ $site->name() }}"
                                 width="256" height="249" class="h-full w-full object-contain">
                        </span>
                        <span class="font-display text-[17px] font-bold !text-white">{{ __('research_hub.ecosystem.centre') }}</span>
                    </div>
                    @foreach (__('research_hub.ecosystem.nodes') as $node)
                        <div class="flex items-center gap-2.5 rounded-2xl border border-white/15 bg-white/[0.06] px-5 py-3.5 text-[14px] font-semibold text-white/85">
                            <span class="h-1.5 w-1.5 rounded-full bg-brand-300"></span>
                            {{ $node }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ---------------- 8. Final call ---------------- --}}
    <section class="border-t border-ink-100 bg-ink-50 py-16 sm:py-20">
        <div class="container-rich">
            <div class="reveal grid gap-8 rounded-[2rem] bg-navy-700 px-7 py-11 text-white sm:px-12 lg:grid-cols-[1.3fr_0.7fr] lg:items-center">
                <div>
                    <h2 class="font-display text-[24px] font-bold leading-tight !text-white sm:text-[32px]">{{ __('research_hub.cta.title') }}</h2>
                    <p class="mt-4 max-w-2xl text-[14.5px] leading-[1.85] text-white/70">{{ __('research_hub.cta.lead') }}</p>
                </div>

                <div class="flex flex-wrap gap-3 lg:justify-end">
                    @auth
                        <a href="{{ route('researcher.dashboard') }}" class="btn-lead group">
                            <span class="relative">{{ __('research_hub.portal.dashboard') }}</span>
                            <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                        </a>
                    @else
                        <a href="{{ route('researcher.register') }}" class="btn-lead group">
                            <span class="relative">{{ __('research_hub.portal.join') }}</span>
                            <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                        </a>
                        <a href="{{ route('login') }}" class="btn-invert">{{ __('research_hub.portal.sign_in') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
