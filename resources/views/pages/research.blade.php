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

        /* The future modules, with an honest status against each. A module is
           only "available" where the site genuinely has that page today. */
        $modules = __('research_hub.future.modules');
        $moduleStatus = [
            0 => ['planned', null],
            1 => ['planned', null],
            2 => ['planned', null],
            3 => ['planned', null],
            4 => ['planned', null],
            5 => ['planned', null],
            6 => ['planned', null],
            7 => ['soon', null],
            8 => ['soon', null],
            9 => ['planned', null],
            10 => ['planned', null],
            11 => ['planned', null],
            12 => ['planned', null],
        ];
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
                <a href="{{ route('research.proposal') }}" class="btn-lead group">
                    <span class="relative">{{ __('research_hub.forms.proposal.title') }}</span>
                    <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                </a>
                <a href="{{ route('research.support') }}" class="btn-invert px-7 py-4 text-[15px]">
                    {{ __('research_hub.support.cta') }}
                </a>
                <a href="#areas" class="inline-flex items-center gap-2 px-2 py-3 text-[14px] font-semibold text-white/70 transition hover:text-white">
                    {{ __('research_hub.hero.secondary') }}
                    <x-ui-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            <p class="reveal mt-9 inline-flex max-w-2xl items-start gap-2.5 rounded-2xl border border-white/15 bg-white/[0.06] px-5 py-3.5 text-[13px] leading-relaxed text-white/70 backdrop-blur-sm">
                <x-ui-icon name="compass" class="mt-0.5 h-4 w-4 shrink-0 text-brand-300" />
                {{ __('research_hub.hero.note') }}
            </p>
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

            {{-- The journey as a path, not a grid of cards.

                 Eleven identical tiles read as a list of features; a single
                 column with a line running through it reads as a sequence, which
                 is what this is. The last stage is the one the whole thing is
                 for, so it is the one picked out in gold. --}}
            <ol class="relative mt-14 max-w-4xl">
                <span class="pointer-events-none absolute bottom-10 left-[27px] top-6 w-px bg-gradient-to-b from-brand-200 via-ink-200 to-gold-300 sm:left-[35px]" aria-hidden="true"></span>

                @foreach (__('research_hub.lifecycle.stages') as $i => [$stage, $what])
                    @php
                        $last = $loop->last;
                        $first = $loop->first;
                    @endphp

                    <li class="reveal group relative flex gap-5 pb-9 last:pb-0 sm:gap-7" style="{{ $delay($i, 40) }}">
                        {{-- The marker on the line --}}
                        <span @class([
                            'relative z-10 flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border transition duration-400 sm:h-[70px] sm:w-[70px]',
                            'border-gold-300 bg-gold-100' => $last,
                            'border-brand-200 bg-brand-50' => $first,
                            'border-ink-200 bg-white group-hover:border-brand-300' => ! $first && ! $last,
                        ])>
                            <span @class([
                                'font-display text-[22px] font-bold tabular-nums sm:text-[28px]',
                                'numeral-outline-gold' => $last,
                                'text-brand-700' => $first,
                                'numeral-outline' => ! $first && ! $last,
                            ])>{{ $num(str_pad($i + 1, 2, '0', STR_PAD_LEFT)) }}</span>
                        </span>

                        <div class="min-w-0 pt-2.5 sm:pt-4">
                            <h3 @class([
                                'font-display text-[19px] font-bold leading-tight tracking-tight sm:text-[23px]',
                                'text-gold-700' => $last,
                                'text-ink-950' => ! $last,
                            ])>{{ $stage }}</h3>

                            <p class="mt-2 max-w-xl text-[14.5px] leading-[1.85] text-ink-600">{{ $what }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>

            <a href="{{ route('research.framework') }}#process"
               class="reveal mt-8 inline-flex items-center gap-2 text-[13.5px] font-semibold text-brand-700 transition-all hover:gap-3">
                {{ __('research_hub.lifecycle.document_link') }}
                <x-ui-icon name="arrow-right" class="h-3.5 w-3.5" />
            </a>
        </div>
    </section>

    {{-- ---------------- 4. What RICH could offer ---------------- --}}
    <section class="bg-white py-20 sm:py-24">
        <div class="container-rich">
            <p class="{{ $eyebrow }}"><span class="text-brand-300">//</span> {{ __('research_hub.offer.eyebrow') }}</p>
            <h2 class="{{ $h2 }}">{{ __('research_hub.offer.title') }}</h2>
            <p class="{{ $lead }}">{{ __('research_hub.offer.lead') }}</p>

            {{-- Eight equal tiles is a spreadsheet. The first two carry the
                 ones a researcher reaches for first and are given the room to
                 say so; the rest sit beneath them at an ordinary size. --}}
            <div class="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (__('research_hub.offer.cards') as $i => [$name, $what, $icon])
                    @php $feature = $i < 2; @endphp

                    <article @class([
                                 'reveal group relative flex h-full flex-col overflow-hidden rounded-[1.5rem] p-6 transition duration-400 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1.5',
                                 'sm:col-span-1 lg:col-span-2 lg:p-8' => $feature,
                                 'border border-ink-100 bg-white hover:border-brand-300 hover:shadow-[0_30px_64px_-46px_rgba(2,34,81,0.55)]' => ! $feature,
                                 'bg-ink-950 text-white shadow-[0_28px_64px_-44px_rgba(7,20,38,0.9)]' => $feature,
                             ])
                             style="{{ $delay($i, 45) }}">

                        @if ($feature)
                            <span class="pointer-events-none absolute -right-16 -top-16 h-52 w-52 rounded-full bg-brand-600/25 blur-3xl transition duration-700 group-hover:bg-gold-400/25" aria-hidden="true"></span>
                            <span class="pointer-events-none absolute inset-0 text-white grid-overlay opacity-[0.07]" aria-hidden="true"></span>
                        @endif

                        <span @class([
                            'relative flex items-center justify-center rounded-2xl transition duration-300',
                            'h-14 w-14 bg-white/10 text-gold-300 ring-1 ring-white/15' => $feature,
                            'h-12 w-12 bg-brand-50 text-brand-600 group-hover:bg-brand-600 group-hover:text-white' => ! $feature,
                        ])>
                            <x-ui-icon :name="$icon" class="{{ $feature ? 'h-6 w-6' : 'h-5 w-5' }}" />
                        </span>

                        <h3 @class([
                            'relative font-display font-bold leading-snug',
                            'mt-6 text-[21px] !text-white sm:text-[24px]' => $feature,
                            'mt-5 text-[16px] text-ink-950' => ! $feature,
                        ])>{{ $name }}</h3>

                        <p @class([
                            'relative leading-[1.8]',
                            'mt-3 max-w-md text-[14.5px] text-white/65' => $feature,
                            'mt-2.5 text-[13.5px] text-ink-600' => ! $feature,
                        ])>{{ $what }}</p>

                        <span @class([
                            'relative mt-auto inline-flex w-fit items-center gap-1.5 pt-5 text-[11px] font-semibold uppercase tracking-[0.14em]',
                            'text-white/45' => $feature,
                            'text-ink-400' => ! $feature,
                        ])>
                            <span class="h-1.5 w-1.5 rounded-full bg-gold-400"></span>
                            {{ __('research_hub.future.status.planned') }}
                        </span>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- 5. Research areas ----------------
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

    {{-- ---------------- 6. Collaboration vision ---------------- --}}
    <section class="bg-white py-20 sm:py-24">
        <div class="container-rich">
            <p class="{{ $eyebrow }}"><span class="text-brand-300">//</span> {{ __('research_hub.collaboration.eyebrow') }}</p>
            <h2 class="{{ $h2 }}">{{ __('research_hub.collaboration.title') }}</h2>
            <p class="{{ $lead }}">{{ __('research_hub.collaboration.lead') }}</p>

            <div class="mt-12 grid gap-x-12 gap-y-10 lg:grid-cols-[0.95fr_1.05fr]">
                {{-- What a request would carry --}}
                <div>
                    <p class="{{ $label }}">{{ __('research_hub.collaboration.criteria_title') }}</p>

                    <ul class="mt-5 space-y-2.5">
                        @foreach (__('research_hub.collaboration.criteria') as $i => [$name, $what])
                            <li class="reveal flex items-start gap-3.5 rounded-2xl border border-ink-100 bg-ink-50/60 p-4" style="{{ $delay($i, 40) }}">
                                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white text-brand-600 ring-1 ring-ink-100">
                                    <x-ui-icon name="check" class="h-3.5 w-3.5" stroke="2.4" />
                                </span>
                                <span>
                                    <span class="block text-[14px] font-bold text-ink-950">{{ $name }}</span>
                                    <span class="mt-0.5 block text-[13px] leading-relaxed text-ink-600">{{ $what }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="space-y-6">
                    {{-- Real examples, from the framework --}}
                    @if (! empty($framework['matching']))
                        <div class="reveal rounded-[1.75rem] border border-ink-100 bg-white p-7">
                            <p class="{{ $label }}">{{ __('research_hub.collaboration.examples_title') }}</p>
                            <p class="mt-2 text-[13px] leading-relaxed text-ink-500">{{ __('research_hub.collaboration.examples_lead') }}</p>

                            <div class="mt-6 space-y-5">
                                @foreach ($framework['matching'] as [$problem, $who])
                                    <div class="border-s-2 border-brand-200 ps-4">
                                        <p class="font-display text-[15px] font-bold text-ink-950">{{ $problem }}</p>
                                        <p class="mt-1.5 text-[13.5px] leading-[1.8] text-ink-600">{{ $who }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Post an idea --}}
                    <div class="reveal relative overflow-hidden rounded-[1.75rem] bg-navy-700 p-7 text-white">
                        <div class="pointer-events-none absolute -right-16 -top-16 h-44 w-44 rounded-full bg-brand-600/30 blur-2xl" aria-hidden="true"></div>

                        <div class="flex items-center justify-between gap-4">
                            <p class="font-display text-[17px] font-bold !text-white">{{ __('research_hub.collaboration.post_title') }}</p>
                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-white/15 px-2.5 py-1 text-[10.5px] font-semibold uppercase tracking-[0.14em] text-white/80">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-300"></span>
                                {{ __('research_hub.future.status.planned') }}
                            </span>
                        </div>

                        <p class="mt-3 text-[14px] leading-[1.8] text-white/70">{{ __('research_hub.collaboration.post_lead') }}</p>

                        <div class="mt-6 rounded-2xl border border-white/15 bg-white/[0.07] p-5">
                            <p class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-brand-300">{{ __('research_hub.collaboration.post_example_label') }}</p>
                            <p class="mt-2 font-display text-[16px] font-bold leading-snug !text-white">“{{ __('research_hub.collaboration.post_example') }}”</p>
                            <p class="mt-3 inline-flex items-center gap-2 text-[12.5px] text-white/60">
                                <x-ui-icon name="users" class="h-3.5 w-3.5" />
                                {{ __('research_hub.collaboration.post_interest') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ---------------- 7. Research support ---------------- --}}
    <section class="border-y border-ink-100 bg-ink-50 py-20 sm:py-24">
        <div class="container-rich">
            <p class="{{ $eyebrow }}"><span class="text-brand-300">//</span> {{ __('research_hub.support.eyebrow') }}</p>
            <h2 class="{{ $h2 }}">{{ __('research_hub.support.title') }}</h2>
            <p class="{{ $lead }}">{{ __('research_hub.support.lead') }}</p>

            <div class="mt-11 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach (__('research_hub.support.items') as $i => $item)
                    <div class="reveal group flex items-center gap-3 rounded-2xl border border-ink-100 bg-white px-5 py-4 transition duration-300 hover:border-brand-300"
                         style="{{ $delay($i, 25) }}">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                            <x-ui-icon name="check" class="h-4 w-4" stroke="2.4" />
                        </span>
                        <span class="text-[14px] font-semibold text-ink-800">{{ $item }}</span>
                    </div>
                @endforeach
            </div>

            {{-- The framework's own before / during / after help --}}
            @if (! empty($framework['help_desk']))
                <div class="mt-10 grid gap-4 lg:grid-cols-3">
                    @foreach ($framework['help_desk'] as $i => [$when, $what])
                        <div class="reveal rounded-[1.5rem] border border-ink-100 bg-white p-6" style="{{ $delay($i, 60) }}">
                            <p class="font-display text-[15px] font-bold text-brand-700">{{ $when }}</p>
                            <p class="mt-2.5 text-[13.5px] leading-[1.8] text-ink-600">{{ $what }}</p>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="reveal mt-10">
                <a href="{{ route('research.support') }}" class="btn-primary">
                    {{ __('research_hub.support.cta') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>
        </div>
    </section>

    {{-- ---------------- 8. Research impact ----------------
         Real counts only. A null is shown as "coming soon" rather than a zero
         dressed up as a figure. --}}
    <section class="bg-white py-20 sm:py-24">
        <div class="container-rich">
            <p class="{{ $eyebrow }}"><span class="text-brand-300">//</span> {{ __('research_hub.impact.eyebrow') }}</p>
            <h2 class="{{ $h2 }}">{{ __('research_hub.impact.title') }}</h2>
            <p class="{{ $lead }}">{{ __('research_hub.impact.lead') }}</p>

            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                @foreach (__('research_hub.impact.metrics') as $key => $name)
                    @php $value = $impact[$key] ?? null; @endphp

                    <div class="reveal flex flex-col rounded-2xl border border-ink-100 bg-white p-5 text-center transition duration-400 hover:-translate-y-1 hover:border-brand-300"
                         style="{{ $delay($loop->index, 30) }}">
                        @if ($value === null)
                            <span class="font-display text-[15px] font-bold leading-none text-ink-300">{{ __('research_hub.impact.soon') }}</span>
                        @else
                            <span class="font-numeric text-[34px] font-bold leading-none tabular-nums text-ink-950">{{ $num($value) }}</span>
                        @endif

                        <span class="mt-3 text-[12.5px] font-semibold leading-snug text-ink-600">{{ $name }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- 9. SDG research vision ---------------- --}}
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

    {{-- ---------------- 10. The ecosystem ---------------- --}}
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

    {{-- ---------------- 11. The future of RICH ---------------- --}}
    <section class="bg-white py-20 sm:py-24">
        <div class="container-rich">
            <p class="{{ $eyebrow }}"><span class="text-brand-300">//</span> {{ __('research_hub.future.eyebrow') }}</p>
            <h2 class="{{ $h2 }}">{{ __('research_hub.future.title') }}</h2>
            <p class="{{ $lead }}">{{ __('research_hub.future.lead') }}</p>

            @php
                /* Grouped by state rather than listed flat: thirteen identical
                   rows say nothing about order, and the order is the point of a
                   roadmap. */
                $grouped = collect($modules)
                    ->map(fn ($module, $i) => ['name' => $module, 'state' => ($moduleStatus[$i][0] ?? 'planned')])
                    ->groupBy('state');

                $columns = [
                    'soon' => ['bg-sky-400', 'border-sky-200', 'bg-sky-50/60'],
                    'planned' => ['bg-gold-400', 'border-gold-200', 'bg-gold-100/50'],
                    'live' => ['bg-brand-500', 'border-brand-200', 'bg-brand-50'],
                ];
            @endphp

            <div class="mt-12 grid gap-5 lg:grid-cols-3">
                @foreach ($columns as $state => [$dot, $border, $tint])
                    @continue(! isset($grouped[$state]))

                    <div class="reveal rounded-[1.75rem] border {{ $border }} {{ $tint }} p-6 sm:p-7" style="{{ $delay($loop->index, 80) }}">
                        <div class="flex items-center justify-between gap-3">
                            <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.16em] text-ink-600">
                                <span class="h-2 w-2 rounded-full {{ $dot }}"></span>
                                {{ __('research_hub.future.status.'.$state) }}
                            </p>
                            <span class="font-numeric text-[13px] font-bold tabular-nums text-ink-400">{{ $num($grouped[$state]->count()) }}</span>
                        </div>

                        <ul class="mt-5 space-y-2.5">
                            @foreach ($grouped[$state] as $module)
                                <li class="flex items-start gap-2.5 text-[14px] font-medium leading-snug text-ink-800">
                                    <span class="mt-[7px] h-1 w-1 shrink-0 rounded-full {{ $dot }}"></span>
                                    {{ $module['name'] }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- Send something in ----------------
         The two things on this page that are not a concept: a researcher can
         actually submit these today, and a person reads them. Kept apart from
         the planned modules above so the difference is obvious. --}}
    <section id="send" class="scroll-mt-24 border-t border-ink-100 bg-white py-20 sm:py-24">
        <div class="container-rich">
            <p class="{{ $eyebrow }}"><span class="text-brand-300">//</span> {{ __('research_hub.send.eyebrow') }}</p>
            <h2 class="{{ $h2 }}">{{ __('research_hub.send.title') }}</h2>
            <p class="{{ $lead }}">{{ __('research_hub.send.lead') }}</p>

            <div class="mt-12 grid gap-5 lg:grid-cols-2">
                @foreach ([
                    ['research.support', 'heart', 'research_hub.forms.support.title', 'research_hub.forms.support.lead', 'research_hub.forms.support.submit'],
                    ['research.proposal', 'document', 'research_hub.forms.proposal.title', 'research_hub.forms.proposal.lead', 'research_hub.forms.proposal.submit'],
                ] as $i => [$route, $icon, $cardTitle, $cardLead, $action])
                    <a href="{{ route($route) }}"
                       @class([
                           'reveal group relative flex flex-col overflow-hidden rounded-[1.75rem] p-7 transition duration-400 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1.5 sm:p-9',
                           // The proposal is the page's main invitation, so its
                           // card is the dark one and the support card sits beside it.
                           'bg-navy-700 text-white shadow-[0_30px_70px_-46px_rgba(2,34,81,0.9)] hover:shadow-[0_40px_84px_-44px_rgba(2,34,81,0.95)]' => $route === 'research.proposal',
                           'border border-ink-100 bg-white hover:border-brand-300 hover:shadow-[0_34px_70px_-50px_rgba(2,34,81,0.55)]' => $route !== 'research.proposal',
                       ])
                       style="{{ $delay($i, 70) }}">
                        <span @class([
                            'pointer-events-none absolute -right-10 -top-10 h-36 w-36 rounded-full transition duration-500 group-hover:scale-125',
                            'bg-brand-600/25 blur-xl' => $route === 'research.proposal',
                            'bg-brand-50' => $route !== 'research.proposal',
                        ]) aria-hidden="true"></span>

                        <span @class([
                            'relative flex h-13 w-13 items-center justify-center rounded-2xl p-3.5',
                            'bg-brand-500 text-white' => $route === 'research.proposal',
                            'bg-brand-600 text-white' => $route !== 'research.proposal',
                        ])>
                            <x-ui-icon :name="$icon" class="h-6 w-6" />
                        </span>

                        <h3 @class([
                            'relative mt-6 font-display text-[21px] font-bold leading-snug sm:text-[25px]',
                            '!text-white' => $route === 'research.proposal',
                            'text-ink-950' => $route !== 'research.proposal',
                        ])>{{ __($cardTitle) }}</h3>

                        <p @class([
                            'relative mt-3.5 text-[14.5px] leading-[1.85]',
                            'text-white/70' => $route === 'research.proposal',
                            'text-ink-600' => $route !== 'research.proposal',
                        ])>{{ __($cardLead) }}</p>

                        <span @class([
                            'relative mt-auto inline-flex items-center gap-2 pt-7 text-[14px] font-bold transition-all group-hover:gap-3',
                            'text-brand-300' => $route === 'research.proposal',
                            'text-brand-700' => $route !== 'research.proposal',
                        ])>
                            {{ __($action) }}
                            <x-ui-icon name="arrow-right" class="h-4 w-4" />
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- 12. Final call ---------------- --}}
    <section class="border-t border-ink-100 bg-ink-50 py-16 sm:py-20">
        <div class="container-rich">
            <div class="reveal grid gap-8 rounded-[2rem] bg-navy-700 px-7 py-11 text-white sm:px-12 lg:grid-cols-[1.3fr_0.7fr] lg:items-center">
                <div>
                    <h2 class="font-display text-[24px] font-bold leading-tight !text-white sm:text-[32px]">{{ __('research_hub.cta.title') }}</h2>
                    <p class="mt-4 max-w-2xl text-[14.5px] leading-[1.85] text-white/70">{{ __('research_hub.cta.lead') }}</p>
                </div>

                <div class="flex flex-wrap gap-3 lg:justify-end">
                    <a href="{{ route('research.proposal') }}" class="btn-lead group">
                        <span class="relative">{{ __('research_hub.forms.proposal.title') }}</span>
                        <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                    </a>
                    <a href="{{ route('research.support') }}" class="btn-invert">{{ __('research_hub.support.cta') }}</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
