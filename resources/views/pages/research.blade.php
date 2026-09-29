<x-layouts.app :title="__('site.nav.research')"
               :description="__('site.research_meta')">

    @php
        $f = \App\Support\ResearchFramework::all();

        // The priority areas, and how many of them each department lists.
        $areas = collect($f['priority_areas']);
        $perDepartment = $areas->groupBy(0)->map->count();

        // Numbers the page counts out are written in the digits of the language being read.
        $num = fn ($value) => app()->getLocale() === 'bn'
            ? strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
            : (string) $value;

        $eyebrow = 'reveal text-[11px] font-semibold uppercase tracking-[0.2em] text-brand-700';
        $h2 = 'reveal mt-3 font-display text-[26px] font-bold leading-tight tracking-tight text-ink-950 sm:text-[34px]';
        $h3 = 'reveal mt-14 font-display text-[18px] font-bold text-ink-950 sm:text-[20px]';
        $para = 'reveal mt-5 max-w-3xl text-[15.5px] leading-[1.85] text-ink-700';
        $label = 'text-[10.5px] font-semibold uppercase tracking-[0.18em] text-ink-400';
        $panel = 'reveal rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-8';
        $delay = fn ($i, $step = 45, $max = 300) => 'transition-delay: '.min($i * $step, $max).'ms';
    @endphp

    {{-- ---------------- Cover ---------------- --}}
    <section class="relative isolate overflow-hidden bg-navy-700">
        <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.18]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-40 -top-40 -z-10 h-[34rem] w-[34rem] rounded-full bg-brand-600/35 blur-[120px]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-40 -left-32 -z-10 h-[26rem] w-[26rem] rounded-full border border-white/10" aria-hidden="true"></div>

        <div class="container-rich py-20 sm:py-24">
            <p class="font-display text-[12px] font-bold uppercase tracking-[0.28em] text-brand-300">RICH-</p>

            <h1 class="mt-5 max-w-4xl font-display text-[32px] font-bold leading-[1.1] tracking-tight !text-white sm:text-[46px]">
                {{ $f['title'] }}
            </h1>

            <p class="mt-6 font-display text-[22px] font-bold uppercase tracking-[0.18em] text-brand-300 sm:text-[26px]">{{ $f['wing'] }}</p>

            <div class="mt-6 h-px w-24 bg-white/25"></div>

            <p class="mt-6 text-[17px] font-semibold text-white/90">{{ $f['subtitle'] }}</p>
            <p class="text-[17px] font-semibold text-white/90">{{ $f['institution'] }}</p>

            <p class="mt-9 inline-flex rounded-full border border-white/20 bg-white/[0.06] px-5 py-2.5 text-[13.5px] text-white/80 backdrop-blur-sm">
                {{ $f['strapline'] }}
            </p>
        </div>
    </section>

    {{-- ---------------- Planning pillars ----------------
         The four pillars read as one run: Research → Innovation →
         Consultancy → Impact, which is the order the cover states. --}}
    <section class="relative isolate overflow-hidden border-b border-ink-100 bg-white py-16 sm:py-20">
        <div class="pointer-events-none absolute inset-x-0 -top-40 -z-10 h-80 bg-gradient-to-b from-brand-50/70 to-transparent" aria-hidden="true"></div>

        <div class="container-rich">
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($f['pillars'] as $i => [$pillar, $direction, $icon])
                    <div class="reveal group relative" style="{{ $delay($i, 70, 280) }}">
                        {{-- The gradient ring shows through on hover --}}
                        <div class="relative h-full overflow-hidden rounded-[1.5rem] bg-gradient-to-br from-ink-200/60 to-ink-100/40 p-px transition duration-500 group-hover:from-brand-500 group-hover:to-navy-700 group-hover:shadow-[0_30px_60px_-38px_rgba(2,34,81,0.65)]">
                            <div class="relative flex h-full flex-col overflow-hidden rounded-[1.45rem] bg-white p-7 transition duration-500 group-hover:-translate-y-1">

                                {{-- Oversized numeral, kept behind the text --}}
                                <span class="pointer-events-none absolute -right-3 -top-6 select-none font-display text-[7rem] font-bold leading-none text-ink-50 transition-colors duration-500 group-hover:text-brand-50"
                                      aria-hidden="true">{{ $num(str_pad($i + 1, 2, '0', STR_PAD_LEFT)) }}</span>

                                <span class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-600 to-navy-700 text-white shadow-[0_14px_28px_-16px_rgba(2,34,81,0.8)] transition duration-500 group-hover:scale-105">
                                    <x-ui-icon :name="$icon" class="h-6 w-6" />
                                </span>

                                <p class="relative mt-7 font-display text-[20px] font-bold tracking-tight text-ink-950">{{ $pillar }}</p>

                                <span class="relative mt-3 block h-0.5 w-10 rounded-full bg-brand-600 transition-all duration-500 group-hover:w-16" aria-hidden="true"></span>

                                <p class="relative mt-4 text-[13.5px] leading-relaxed muted">{{ $direction }}</p>
                            </div>
                        </div>

                        {{-- Connector to the next pillar --}}
                        @unless ($loop->last)
                            <span class="pointer-events-none absolute -right-[13px] top-1/2 z-10 hidden h-6 w-6 -translate-y-1/2 items-center justify-center rounded-full bg-white text-brand-600 ring-1 ring-ink-200 xl:flex" aria-hidden="true">
                                <x-ui-icon name="arrow-right" class="h-3 w-3" stroke="2.6" />
                            </span>
                        @endunless
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- Purpose and Strategic Direction ---------------- --}}
    <section class="bg-white py-16 sm:py-20">
        <div class="container-rich">
            <p class="{{ $eyebrow }}">{{ $f['headings']['purpose_eyebrow'] }}</p>
            <h2 class="{{ $h2 }}">{{ $f['headings']['purpose'] }}</h2>

            @foreach ($f['purpose'] as $paragraph)
                <p class="{{ $para }}">{{ $paragraph }}</p>
            @endforeach

            {{-- A table: the shift, where it is now, and where it is going.
                 On phones each row stacks with its own column labels. --}}
            <div class="reveal mt-12 overflow-hidden rounded-[1.5rem] border border-ink-200 bg-white">
                <div class="hidden grid-cols-[1fr_1.2fr_1.4fr] gap-6 border-b border-ink-200 bg-ink-50 px-6 py-3.5 lg:grid">
                    <span class="{{ $label }}">{{ $f['headings']['shift_column'] }}</span>
                    <span class="{{ $label }}">{{ $f['headings']['from'] }}</span>
                    <span class="{{ $label }}">{{ $f['headings']['to'] }}</span>
                </div>

                @foreach ($f['shifts'] as $i => [$shift, $from, $to])
                    <div class="reveal grid gap-3 border-b border-ink-100 px-6 py-5 last:border-b-0 odd:bg-white even:bg-ink-50/40 lg:grid-cols-[1fr_1.2fr_1.4fr] lg:items-center lg:gap-6"
                         style="{{ $delay($i, 40) }}">

                        <p class="font-display text-[15px] font-bold text-ink-950">{{ $shift }}</p>

                        <div class="flex items-center gap-3">
                            <span class="{{ $label }} lg:hidden">{{ $f['headings']['from'] }}</span>
                            <span class="rounded-lg bg-ink-100 px-3 py-1.5 text-[13.5px] text-ink-600">{{ $from }}</span>
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="{{ $label }} lg:hidden">{{ $f['headings']['to'] }}</span>
                            <x-ui-icon name="arrow-right" class="hidden h-4 w-4 shrink-0 text-brand-600 lg:block" />
                            <span class="rounded-lg bg-brand-50 px-3 py-1.5 text-[13.5px] font-semibold text-brand-800">{{ $to }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- 1. Identity ---------------- --}}
    <section class="border-t border-ink-100 bg-ink-50 py-16 sm:py-20">
        <div class="container-rich">
            <p class="{{ $eyebrow }}">{{ $f['headings']['section_1'] }}</p>
            <h2 class="{{ $h2 }}">{{ $f['headings']['identity'] }}</h2>
            <p class="{{ $para }}">{{ $f['identity'] }}</p>

            {{-- 1.1 Existing Departments --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['departments'] }}</h3>
            <div class="mt-6 grid gap-px overflow-hidden rounded-[1.25rem] border border-ink-100 bg-ink-100 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($f['departments'] as $i => $department)
                    <div class="reveal flex items-center gap-3 bg-white p-5" style="{{ $delay($i, 35) }}">
                        <span class="font-display text-[22px] font-bold tabular-nums text-brand-200">{{ $num(str_pad($i + 1, 2, '0', STR_PAD_LEFT)) }}</span>
                        <span class="text-[13.5px] font-semibold leading-snug text-ink-800">{{ $department }}</span>
                    </div>
                @endforeach
            </div>

            {{-- 1.2 Rationale --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['rationale'] }}</h3>
            <div class="reveal mt-5 max-w-3xl space-y-4 border-l-2 border-brand-600 pl-6">
                @foreach ($f['rationale'] as $paragraph)
                    <p class="text-[15.5px] leading-[1.85] text-ink-700">{{ $paragraph }}</p>
                @endforeach
            </div>

            {{-- 1.3 Vision --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['vision'] }}</h3>
            <figure class="reveal mt-5 rounded-[1.75rem] bg-navy-700 p-8 sm:p-10">
                <blockquote class="max-w-4xl font-display text-[19px] font-semibold leading-[1.6] !text-white sm:text-[23px]">{{ $f['vision'] }}</blockquote>
            </figure>

            {{-- 1.3.1 Five Core Ideas of the Vision --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['core_ideas'] }}</h3>
            <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($f['core_ideas'] as $i => [$idea, $meaning, $icon])
                    <div class="reveal flex h-full flex-col rounded-2xl border border-ink-100 bg-white p-6 transition duration-500 hover:-translate-y-1 hover:border-brand-300 hover:shadow-[0_24px_50px_-32px_rgba(2,34,81,0.45)]"
                         style="{{ $delay($i, 50) }}">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <x-ui-icon :name="$icon" class="h-4.5 w-4.5" />
                        </span>
                        <p class="mt-5 font-display text-[16px] font-bold text-ink-950">{{ $idea }}</p>
                        <p class="mt-2 text-[13px] leading-relaxed muted">{{ $meaning }}</p>
                    </div>
                @endforeach
            </div>

            {{-- 1.4 Mission --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['mission'] }}</h3>
            <div class="mt-6 grid gap-2.5 lg:grid-cols-2">
                @foreach ($f['mission'] as $i => $point)
                    <div class="reveal flex items-start gap-3 rounded-xl border border-ink-100 bg-white px-5 py-4" style="{{ $delay($i, 30) }}">
                        <span class="mt-0.5 font-display text-[11px] font-bold tabular-nums text-brand-500">{{ $num(str_pad($i + 1, 2, '0', STR_PAD_LEFT)) }}</span>
                        <span class="text-[14px] leading-snug text-ink-800">{{ $point }}</span>
                    </div>
                @endforeach
            </div>

            {{-- 1.5 Core Objectives --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['objectives'] }}</h3>

            <h4 class="reveal mt-7 font-display text-[16.5px] font-bold text-brand-700">1.5.1 {{ $f['objective_one']['heading'] }}</h4>
            <p class="{{ $para }}">{{ $f['objective_one']['body'] }}</p>

            @php
                // Decorative icons only; the wording is the plan's.
                $activityIcons = ['beaker', 'chat', 'calendar', 'users', 'star', 'document', 'handshake'];
            @endphp

            <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($f['objective_one']['activities'] as $i => [$activity, $purpose])
                    <div class="reveal group relative flex h-full flex-col overflow-hidden rounded-2xl border border-ink-100 bg-white p-6 transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1 hover:border-brand-300 hover:shadow-[0_26px_54px_-34px_rgba(2,34,81,0.5)]"
                         style="{{ $delay($i, 40) }}">

                        {{-- A green wash grows in from the top on hover --}}
                        <span class="pointer-events-none absolute inset-x-0 top-0 h-0 bg-gradient-to-b from-brand-50 to-transparent transition-all duration-500 group-hover:h-20" aria-hidden="true"></span>

                        <div class="relative flex items-center justify-between">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition duration-500 group-hover:bg-brand-600 group-hover:text-white">
                                <x-ui-icon :name="$activityIcons[$i] ?? 'check'" class="h-5 w-5" />
                            </span>
                            <span class="font-display text-[12px] font-bold tabular-nums text-ink-300 transition-colors duration-500 group-hover:text-brand-500">
                                {{ $num(str_pad($i + 1, 2, '0', STR_PAD_LEFT)) }}
                            </span>
                        </div>

                        <p class="relative mt-5 font-display text-[16px] font-bold leading-snug text-ink-950">{{ $activity }}</p>
                        <p class="relative mt-2 text-[13.5px] leading-relaxed muted">{{ $purpose }}</p>
                    </div>
                @endforeach
            </div>

            <h4 class="reveal mt-12 font-display text-[16.5px] font-bold text-brand-700">1.5.2 {{ $f['objective_two']['heading'] }}</h4>
            <p class="{{ $para }}">{{ $f['objective_two']['body'] }}</p>

            <div class="mt-7 flex flex-wrap gap-2">
                @foreach ($f['objective_two']['areas'] as $i => $area)
                    <span class="reveal rounded-lg border border-ink-200 bg-white px-3.5 py-2 text-[13px] font-medium text-ink-700" style="{{ $delay($i, 20, 260) }}">{{ $area }}</span>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- 2. Department-Wise Research Priority Areas ---------------- --}}
    <section class="border-t border-ink-100 bg-white py-16 sm:py-20" x-data="{ dept: 'all' }">
        <div class="container-rich">
            <p class="{{ $eyebrow }}">{{ $f['headings']['section_2'] }}</p>
            <h2 class="{{ $h2 }}">{{ $f['headings']['priority_areas'] }}</h2>

            {{-- The table's rows, filtered by department --}}
            <div class="reveal mt-10 flex flex-wrap gap-1.5 rounded-[1.25rem] border border-ink-200 bg-ink-50 p-1.5">
                <button type="button" @click="dept = 'all'" class="area-tab !py-2 !text-[13px]" :class="dept === 'all' && 'is-on'">
                    <x-ui-icon name="grid" class="h-4 w-4 shrink-0" />
                    <span class="area-tab-count">{{ $areas->count() }}</span>
                </button>
                @foreach ($perDepartment as $department => $count)
                    <button type="button" @click="dept = @js($department)" class="area-tab !py-2 !text-[13px]" :class="dept === @js($department) && 'is-on'">
                        {{ \App\Support\Vocabulary::label('research_departments', $department, $department) }}
                        <span class="area-tab-count">{{ $count }}</span>
                    </button>
                @endforeach
            </div>

            <div class="mt-6 grid gap-3 lg:grid-cols-2">
                @foreach ($f['priority_areas'] as $i => [$department, $area, $sdgs, $funding])
                    <div class="group reveal flex items-center gap-5 rounded-2xl border border-ink-100 bg-white px-5 py-4 transition duration-300 hover:border-brand-300"
                         style="{{ $delay($i, 10, 240) }}"
                         x-show="dept === 'all' || dept === @js($department)" x-transition.opacity>
                        <span class="min-w-0 flex-1">
                            <span class="block font-display text-[14.5px] font-bold leading-snug text-ink-950">{{ $area }}</span>
                            <span class="mt-1 flex flex-wrap items-center gap-2 text-[12px]">
                                <span class="font-medium uppercase tracking-[0.1em] text-ink-400">{{ \App\Support\Vocabulary::label('research_departments', $department, $department) }}</span>
                                <span class="rounded bg-brand-50 px-2 py-0.5 font-semibold text-brand-700">{{ $sdgs }}</span>
                            </span>
                        </span>

                        <span class="flex shrink-0 items-center gap-3">
                            <span class="h-1.5 w-16 overflow-hidden rounded-full bg-ink-100 sm:w-24">
                                <span class="block h-full rounded-full bg-brand-600" style="width: {{ $funding }}%"></span>
                            </span>
                            <span class="w-10 text-right font-display text-[14px] font-bold tabular-nums text-ink-950">{{ $num($funding) }}%</span>
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- 3. Interdisciplinary Research Clusters ---------------- --}}
    <section class="border-t border-ink-100 bg-ink-50 py-16 sm:py-20">
        <div class="container-rich">
            <p class="{{ $eyebrow }}">{{ $f['headings']['section_3'] }}</p>
            <h2 class="{{ $h2 }}">{{ $f['headings']['clusters'] }}</h2>
            <p class="{{ $para }}">{{ $f['clusters_intro'] }}</p>

            <div class="mt-10 space-y-5">
                @foreach ($f['clusters'] as $i => $cluster)
                    <div class="reveal overflow-hidden rounded-[1.5rem] border border-ink-100 bg-white" style="{{ $delay($i, 55) }}">
                        <div class="flex flex-wrap items-center gap-4 border-b border-ink-100 px-6 py-5">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-600 font-display text-[13px] font-bold text-white">{{ $num($cluster['no']) }}</span>
                            <p class="font-display text-[18px] font-bold leading-snug text-ink-950 sm:text-[20px]">{{ $cluster['theme'] }}</p>
                        </div>

                        <div class="grid gap-6 px-6 py-6 lg:grid-cols-[0.75fr_1.25fr]">
                            <div>
                                <p class="{{ $label }}">{{ $f['headings']['participating_departments'] }}</p>
                                <p class="mt-3 text-[14px] font-semibold leading-relaxed text-navy-700">{{ $cluster['departments'] }}</p>
                            </div>
                            <div>
                                <p class="{{ $label }}">{{ $f['headings']['possible_areas'] }}</p>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach ($cluster['areas'] as $area)
                                        <span class="rounded-lg bg-ink-50 px-3 py-1.5 text-[13px] text-ink-700">{{ $area }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- 3.6 UGV as a Living Laboratory --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['living_lab'] }}</h3>
            <div class="reveal mt-5 rounded-[1.5rem] border border-brand-200 bg-brand-50/60 p-7 sm:p-9">
                <p class="max-w-3xl text-[15.5px] leading-[1.85] text-ink-700">{{ $f['living_lab'] }}</p>
            </div>
        </div>
    </section>

    {{-- ---------------- 4. Research Project Development Process ---------------- --}}
    <section class="border-t border-ink-100 bg-white py-16 sm:py-20">
        <div class="container-rich">
            <p class="{{ $eyebrow }}">{{ $f['headings']['section_4'] }}</p>
            <h2 class="{{ $h2 }}">{{ $f['headings']['process'] }}</h2>

            <ol class="chain mt-12">
                @foreach ($f['process'] as $i => [$stage, $process])
                    <li @class(['chain-link reveal', 'has-line' => ! $loop->last]) style="{{ $delay($i, 45, 360) }}">
                        <div class="chain-node">
                            <span class="chain-dot"><x-ui-icon name="check" class="h-5 w-5" /></span>
                            <span class="chain-number">{{ $num(str_pad($i + 1, 2, '0', STR_PAD_LEFT)) }}</span>
                        </div>
                        <div class="chain-body">
                            <p class="font-display text-[14.5px] font-bold leading-snug text-ink-950">{{ $stage }}</p>
                            <p class="mt-2 text-[13px] leading-relaxed muted">{{ $process }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>

            {{-- 4.1 Interdisciplinary Matching Example --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['matching'] }}</h3>
            <div class="mt-6 grid gap-4 lg:grid-cols-3">
                @foreach ($f['matching'] as $i => [$problem, $contributions])
                    <div class="reveal flex h-full flex-col rounded-2xl border border-ink-100 bg-white p-6" style="{{ $delay($i, 60, 180) }}">
                        <p class="font-display text-[16px] font-bold leading-snug text-ink-950">{{ $problem }}</p>
                        <div class="mt-4 space-y-2">
                            @foreach (explode(';', $contributions) as $contribution)
                                <p class="rounded-lg bg-ink-50 px-3 py-2 text-[13px] leading-snug text-ink-700">{{ trim($contribution) }}</p>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- 4.2 Scope of Displaying Outcomes --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['outcomes'] }}</h3>
            <div class="mt-6 grid gap-px overflow-hidden rounded-[1.25rem] border border-ink-100 bg-ink-100 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($f['outcomes'] as $i => [$channel, $purpose])
                    <div class="reveal bg-white p-5" style="{{ $delay($i) }}">
                        <p class="font-display text-[15px] font-bold text-ink-950">{{ $channel }}</p>
                        <p class="mt-1.5 text-[13px] leading-relaxed muted">{{ $purpose }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- 5. Research Funding System ---------------- --}}
    <section class="border-t border-ink-100 bg-ink-50 py-16 sm:py-20">
        <div class="container-rich">
            <p class="{{ $eyebrow }}">{{ $f['headings']['section_5'] }}</p>
            <h2 class="{{ $h2 }}">{{ $f['headings']['funding'] }}</h2>
            <p class="{{ $para }}">{{ $f['funding_intro'] }}</p>

            {{-- 5.1 Internal Funding --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['internal_funding'] }}</h3>
            <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($f['internal_funding'] as $i => [$category, $purpose])
                    <div class="reveal flex h-full flex-col rounded-2xl border border-ink-100 bg-white p-6 transition duration-500 hover:-translate-y-1 hover:border-brand-300 hover:shadow-[0_24px_50px_-32px_rgba(2,34,81,0.45)]"
                         style="{{ $delay($i, 50) }}">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <x-ui-icon name="star" class="h-4.5 w-4.5" />
                        </span>
                        <p class="mt-5 font-display text-[15.5px] font-bold text-ink-950">{{ $category }}</p>
                        <p class="mt-2 text-[13px] leading-relaxed muted">{{ $purpose }}</p>
                    </div>
                @endforeach
            </div>

            {{-- 5.2 External Grant Development --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['external_funding'] }}</h3>
            <p class="{{ $para }}">{{ $f['external_funding']['intro'] }}</p>
            <div class="mt-6 grid gap-2.5 lg:grid-cols-2">
                @foreach ($f['external_funding']['items'] as $i => $item)
                    <div class="reveal flex items-start gap-3 rounded-xl border border-ink-100 bg-white px-5 py-4" style="{{ $delay($i, 30) }}">
                        <x-ui-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" stroke="2.6" />
                        <span class="text-[14px] leading-snug text-ink-800">{{ $item }}</span>
                    </div>
                @endforeach
            </div>
            <p class="reveal mt-6 rounded-2xl border-l-2 border-brand-600 bg-white px-6 py-5 text-[14.5px] font-medium leading-relaxed text-ink-800">{{ $f['external_funding']['objective'] }}</p>

            {{-- 5.3 Research Support & Help Desk --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['help_desk'] }}</h3>
            <div class="mt-6 grid gap-4 lg:grid-cols-3">
                @foreach ($f['help_desk'] as $i => [$stage, $services])
                    <div class="reveal flex h-full flex-col rounded-2xl border border-ink-100 bg-white p-6" style="{{ $delay($i, 60, 180) }}">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 font-display text-[12px] font-bold text-white">{{ $i + 1 }}</span>
                            <p class="font-display text-[15px] font-bold text-ink-950">{{ $stage }}</p>
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach (explode(';', $services) as $service)
                                <span class="rounded-lg bg-ink-50 px-3 py-1.5 text-[12.5px] text-ink-700">{{ trim($service) }}</span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- 5.4 Student Research Ecosystem --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['student_ecosystem'] }}</h3>
            <p class="{{ $para }}">{{ $f['student_ecosystem']['intro'] }}</p>
            <div class="mt-6 grid gap-px overflow-hidden rounded-[1.25rem] border border-ink-100 bg-ink-100 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($f['student_ecosystem']['programmes'] as $i => [$programme, $purpose])
                    <div class="reveal bg-white p-5" style="{{ $delay($i, 35) }}">
                        <p class="font-display text-[14.5px] font-bold leading-snug text-ink-950">{{ $programme }}</p>
                        <p class="mt-1.5 text-[13px] leading-relaxed muted">{{ $purpose }}</p>
                    </div>
                @endforeach
            </div>
            <p class="{{ $para }}">{{ $f['student_ecosystem']['skills'] }}</p>

            {{-- 5.5 Faculty Research Mentorship --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['mentorship'] }}</h3>
            <div class="reveal mt-6 grid gap-6 rounded-[1.5rem] border border-ink-100 bg-white p-7 lg:grid-cols-2 sm:p-9">
                <div>
                    <p class="{{ $label }}">{{ $f['headings']['mentorship_model'] }}</p>
                    <p class="mt-3 font-display text-[18px] font-bold leading-snug text-ink-950">{{ $f['mentorship']['model'] }}</p>
                </div>
                <div>
                    <p class="{{ $label }}">{{ $f['headings']['team_structure'] }}</p>
                    <p class="mt-3 font-display text-[18px] font-bold leading-snug text-brand-700">{{ $f['mentorship']['team'] }}</p>
                </div>
                <p class="text-[14.5px] leading-relaxed text-ink-700 lg:col-span-2">{{ $f['mentorship']['note'] }}</p>
            </div>
        </div>
    </section>

    {{-- ---------------- 6. Research Activities and Growth Plan ---------------- --}}
    <section class="border-t border-ink-100 bg-white py-16 sm:py-20">
        <div class="container-rich">
            <p class="{{ $eyebrow }}">{{ $f['headings']['section_6'] }}</p>
            <h2 class="{{ $h2 }}">{{ $f['headings']['activities'] }}</h2>

            {{-- 6.1 RICH Monthly Research Seminar Series --}}
            <h3 class="{{ $h3 }} !mt-10">{{ $f['headings']['seminar_series'] }}</h3>
            <p class="{{ $para }}">{{ $f['seminar_series']['intro'] }}</p>
            <div class="mt-6 grid gap-2.5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($f['seminar_series']['sessions'] as $i => $session)
                    <div class="reveal rounded-xl border border-ink-100 bg-white px-4 py-3.5 text-[13.5px] font-medium text-ink-800" style="{{ $delay($i, 30, 240) }}">
                        {{ $session }}
                    </div>
                @endforeach
            </div>

            {{-- 6.2 Annual UGV Research Conference --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['conference'] }}</h3>
            <p class="{{ $para }}">{{ $f['conference']['intro'] }}</p>
            <div class="mt-6 grid gap-px overflow-hidden rounded-[1.25rem] border border-ink-100 bg-ink-100 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($f['conference']['components'] as $i => [$component, $purpose])
                    <div class="reveal bg-white p-5" style="{{ $delay($i, 40) }}">
                        <p class="font-display text-[14.5px] font-bold text-ink-950">{{ $component }}</p>
                        <p class="mt-1.5 text-[13px] leading-relaxed muted">{{ $purpose }}</p>
                    </div>
                @endforeach
            </div>

            {{-- 6.3 Research Publication Strategy, as the funnel it describes --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['publication'] }}</h3>
            <div class="{{ $panel }} mt-6">
                <div class="space-y-2">
                    @foreach ($f['publication_pathway'] as $i => $stage)
                        @php $width = 100 - $i * 9; @endphp
                        <div class="reveal flex items-center gap-4" style="{{ $delay($i, 45) }}">
                            <span class="w-6 shrink-0 font-display text-[13px] font-bold tabular-nums text-ink-400">{{ $i + 1 }}</span>
                            <span class="flex h-12 items-center rounded-xl px-5 text-[13.5px] font-semibold text-white transition-all duration-500"
                                  style="width: {{ $width }}%; background: linear-gradient(90deg, var(--color-navy-700), var(--color-brand-600));">
                                {{ $stage }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            <p class="{{ $para }}">{{ $f['publication_note'] }}</p>

            {{-- 6.4 Research Ethics & Integrity --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['ethics'] }}</h3>
            <div class="mt-6 grid gap-2.5 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($f['ethics'] as $i => $priority)
                    <div class="reveal flex items-center gap-3 rounded-xl border border-ink-100 bg-white px-4 py-3.5" style="{{ $delay($i, 25, 275) }}">
                        <x-ui-icon name="shield" class="h-4 w-4 shrink-0 text-brand-600" />
                        <span class="text-[13.5px] font-medium text-ink-800">{{ $priority }}</span>
                    </div>
                @endforeach
            </div>

            {{-- 6.5 Research Repository --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['repository'] }}</h3>
            <p class="{{ $para }}">{{ $f['repository']['intro'] }}</p>
            <div class="mt-6 grid gap-2.5 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($f['repository']['items'] as $i => $item)
                    <div class="reveal flex items-center gap-2.5 rounded-xl bg-ink-50 px-4 py-3.5" style="{{ $delay($i, 30, 240) }}">
                        <x-ui-icon name="document" class="h-4 w-4 shrink-0 text-brand-600" />
                        <span class="text-[13.5px] text-ink-800">{{ $item }}</span>
                    </div>
                @endforeach
            </div>
            <p class="{{ $para }}">{{ $f['repository']['note'] }}</p>
        </div>
    </section>

    {{-- ---------------- 7. Research Collaboration & Networking ---------------- --}}
    <section class="border-t border-ink-100 bg-ink-50 py-16 sm:py-20">
        <div class="container-rich">
            <p class="{{ $eyebrow }}">{{ $f['headings']['section_7'] }}</p>
            <h2 class="{{ $h2 }}">{{ $f['headings']['collaboration'] }}</h2>
            @foreach ($f['collaboration_intro'] as $paragraph)
                <p class="{{ $para }}">{{ $paragraph }}</p>
            @endforeach

            {{-- The pathway, read left to right --}}
            <div class="mt-10 grid gap-3 sm:grid-cols-2 xl:grid-cols-6">
                @foreach ($f['collaboration_pathways'] as $i => [$pathway, $output])
                    <div class="reveal relative flex h-full flex-col rounded-2xl border border-ink-100 bg-white p-5" style="{{ $delay($i) }}">
                        <span class="font-display text-[12px] font-bold tabular-nums text-brand-500">{{ $num(str_pad($i + 1, 2, '0', STR_PAD_LEFT)) }}</span>
                        <p class="mt-3 font-display text-[14.5px] font-bold leading-snug text-ink-950">{{ $pathway }}</p>
                        <p class="mt-2 text-[12.5px] leading-relaxed muted">{{ $output }}</p>
                        @unless ($loop->last)
                            <span class="pointer-events-none absolute -right-2 top-1/2 hidden h-4 w-4 -translate-y-1/2 items-center justify-center rounded-full bg-brand-600 text-white xl:flex" aria-hidden="true">
                                <x-ui-icon name="arrow-right" class="h-2.5 w-2.5" stroke="3" />
                            </span>
                        @endunless
                    </div>
                @endforeach
            </div>

            <div class="mt-12 grid gap-10 lg:grid-cols-2">
                {{-- 7.1 Possible Forms of Collaboration --}}
                <div>
                    <h3 class="{{ $h3 }} !mt-0">{{ $f['headings']['collaboration_forms'] }}</h3>
                    <div class="mt-5 grid gap-2.5 sm:grid-cols-2">
                        @foreach ($f['collaboration_forms'] as $i => $form)
                            <div class="reveal flex items-center gap-2.5 rounded-xl border border-ink-100 bg-white px-4 py-3" style="{{ $delay($i, 30, 240) }}">
                                <x-ui-icon name="handshake" class="h-4 w-4 shrink-0 text-brand-600" />
                                <span class="text-[13.5px] text-ink-800">{{ $form }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- 7.3 Research Impact and Knowledge Dissemination --}}
                <div>
                    <h3 class="{{ $h3 }} !mt-0">{{ $f['headings']['dissemination'] }}</h3>
                    <div class="mt-5 grid gap-2.5 sm:grid-cols-2">
                        @foreach ($f['dissemination'] as $i => $channel)
                            <div class="reveal flex items-center gap-2.5 rounded-xl border border-ink-100 bg-white px-4 py-3" style="{{ $delay($i, 30, 240) }}">
                                <x-ui-icon name="globe" class="h-4 w-4 shrink-0 text-brand-600" />
                                <span class="text-[13.5px] text-ink-800">{{ $channel }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ---------------- 8. Annual Research Calendar ---------------- --}}
    <section class="border-t border-ink-100 bg-white py-16 sm:py-20">
        <div class="container-rich">
            <p class="{{ $eyebrow }}">{{ $f['headings']['section_8'] }}</p>
            <h2 class="{{ $h2 }}">{{ $f['headings']['calendar'] }}</h2>

            {{-- The year as one track, quarter by quarter --}}
            <div class="relative mt-12">
                <div class="pointer-events-none absolute left-0 right-0 top-6 hidden h-px bg-ink-200 xl:block" aria-hidden="true"></div>

                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($f['calendar'] as $i => [$quarter, $focus, $activities])
                        <div class="reveal relative" style="{{ $delay($i, 60, 240) }}">
                            <span class="relative z-10 mb-5 hidden h-3 w-3 rounded-full bg-brand-600 ring-4 ring-white xl:block" aria-hidden="true"></span>
                            <div class="flex h-full flex-col rounded-2xl border border-ink-100 bg-white p-6">
                                <span class="inline-flex w-fit rounded-full bg-navy-700 px-3 py-1 text-[11.5px] font-bold uppercase tracking-[0.12em] text-white">{{ $quarter }}</span>
                                <p class="mt-4 font-display text-[16px] font-bold leading-snug text-ink-950">{{ $focus }}</p>
                                <ul class="mt-4 space-y-2">
                                    @foreach (explode(';', $activities) as $activity)
                                        <li class="flex items-start gap-2 text-[13px] leading-snug text-ink-700">
                                            <span class="mt-[7px] h-1 w-1 shrink-0 rounded-full bg-brand-500"></span>
                                            {{ trim($activity) }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 8.1 Monitoring, Evaluation & KPIs --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['kpis'] }}</h3>
            <div class="mt-6 grid gap-4 lg:grid-cols-2">
                @foreach ($f['kpis'] as $i => [$domain, $indicators, $icon])
                    <div class="reveal flex h-full gap-4 rounded-2xl border border-ink-100 bg-white p-6" style="{{ $delay($i) }}">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                            <x-ui-icon :name="$icon" class="h-4.5 w-4.5" />
                        </span>
                        <span>
                            <span class="block font-display text-[15.5px] font-bold text-ink-950">{{ $domain }}</span>
                            <span class="mt-3 flex flex-wrap gap-1.5">
                                @foreach (explode(';', $indicators) as $indicator)
                                    <span class="rounded-lg bg-ink-50 px-2.5 py-1 text-[12px] text-ink-600">{{ trim($indicator) }}</span>
                                @endforeach
                            </span>
                        </span>
                    </div>
                @endforeach
            </div>

            {{-- 8.2 Three-Year Strategic Roadmap --}}
            <h3 class="{{ $h3 }}">{{ $f['headings']['roadmap'] }}</h3>
            <div class="relative mt-8">
                <div class="pointer-events-none absolute left-0 right-0 top-[34px] hidden h-0.5 bg-gradient-to-r from-brand-200 via-brand-500 to-navy-700 lg:block" aria-hidden="true"></div>

                <div class="grid gap-5 lg:grid-cols-3">
                    @foreach ($f['roadmap'] as $i => $year)
                        <div class="reveal" style="{{ $delay($i, 70, 210) }}">
                            <div class="relative z-10 mb-6 hidden items-center gap-3 lg:flex">
                                <span class="flex h-[18px] w-[18px] items-center justify-center rounded-full bg-white ring-2 ring-brand-600">
                                    <span class="h-2 w-2 rounded-full bg-brand-600"></span>
                                </span>
                            </div>

                            <div class="flex h-full flex-col overflow-hidden rounded-[1.5rem] border border-ink-100 bg-white">
                                <div class="bg-navy-700 px-6 py-5">
                                    <p class="font-display text-[17px] font-bold !text-white">{{ $year['year'] }}</p>
                                    <p class="mt-1 text-[13px] text-white/70">{{ $year['theme'] }}</p>
                                </div>
                                <div class="flex flex-1 flex-col px-6 py-5">
                                    <ul class="space-y-2">
                                        @foreach (explode(';', rtrim($year['priorities'], '.')) as $priority)
                                            <li class="flex items-start gap-2 text-[13px] leading-snug text-ink-700">
                                                <span class="mt-[7px] h-1 w-1 shrink-0 rounded-full bg-brand-500"></span>
                                                {{ trim($priority) }}
                                            </li>
                                        @endforeach
                                    </ul>
                                    <p class="mt-auto flex items-start gap-2 border-t border-ink-100 pt-5 text-[13.5px] font-semibold text-brand-700">
                                        <x-ui-icon name="target" class="mt-0.5 h-4 w-4 shrink-0" />{{ $year['goal'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ---------------- 9. Expected Achievements ---------------- --}}
    <section class="border-t border-ink-100 bg-ink-50 py-16 sm:py-20">
        <div class="container-rich">
            <p class="{{ $eyebrow }}">{{ $f['headings']['section_9'] }}</p>
            <h2 class="{{ $h2 }}">{{ $f['headings']['achievements'] }}</h2>

            <div class="mt-10 grid gap-px overflow-hidden rounded-[1.5rem] border border-ink-100 bg-ink-100 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($f['achievements'] as $i => [$area, $result])
                    <div class="reveal flex h-full flex-col bg-white p-6" style="{{ $delay($i, 35) }}">
                        <p class="{{ $label }}">{{ $area }}</p>
                        <p class="mt-3 text-[14px] font-medium leading-relaxed text-ink-800">{{ $result }}</p>
                    </div>
                @endforeach
            </div>

            <div class="reveal mt-10 rounded-[1.75rem] bg-navy-700 p-8 sm:p-10">
                <p class="max-w-4xl font-display text-[17px] font-semibold leading-[1.75] !text-white sm:text-[20px]">{{ $f['closing'] }}</p>
            </div>
        </div>
    </section>
</x-layouts.app>
