<x-layouts.app :title="__('innovator.portal')">

    @php
        $num = fn ($value) => \App\Support\Numerals::localize((string) $value);
        $stageKeys = array_keys($stages);
        $total = count($stageKeys);
        $stageAt = fn ($idea) => max(0, (int) array_search($idea->stage, $stageKeys, true));

        // The furthest any idea has got: the figure the welcome ring shows.
        $furthest = $ideas->isNotEmpty() ? $ideas->map($stageAt)->max() : null;
        $ring = $furthest === null ? 0 : ($furthest + 1) / $total;

        $initials = \Illuminate\Support\Str::of($user->name)->explode(' ')->filter()->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
        $firstName = \Illuminate\Support\Str::of($user->name)->explode(' ')->first();

        // Status pills: one colour per state the office can set.
        $statusTone = [
            'new' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'in_review' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'accepted' => 'bg-brand-50 text-brand-700 ring-brand-200',
            'on_hold' => 'bg-ink-50 text-ink-600 ring-ink-200',
            'declined' => 'bg-rose-50 text-rose-700 ring-rose-200',
        ];
        $statusDot = [
            'new' => 'bg-sky-500', 'in_review' => 'bg-amber-500', 'accepted' => 'bg-brand-500',
            'on_hold' => 'bg-ink-400', 'declined' => 'bg-rose-500',
        ];

        // Each category keeps its own icon and accent colour.
        $categoryLook = [
            'engineering' => ['cpu', 'bg-navy-600', 'bg-navy-50 text-navy-700'],
            'business' => ['briefcase', 'bg-gold-400', 'bg-gold-100 text-gold-700'],
            'arts' => ['users', 'bg-brand-500', 'bg-brand-50 text-brand-700'],
            'other' => ['grid', 'bg-ink-400', 'bg-ink-100 text-ink-700'],
        ];

        $stageIcons = [
            'idea' => 'lightbulb', 'evaluation' => 'search', 'mentorship' => 'users', 'prototype' => 'cog',
            'business_model' => 'chart', 'funding' => 'briefcase', 'startup' => 'rocket', 'market' => 'globe',
        ];

        // Filter tabs: everything, then each status that actually has ideas.
        $counts = $ideas->countBy('status');
        $tabs = collect(['all' => $ideas->count()])->merge(
            collect(array_keys($statusTone))->filter(fn ($s) => $counts->has($s))->mapWithKeys(fn ($s) => [$s => $counts[$s]])
        );
    @endphp

    <section class="relative isolate bg-ink-50 py-10 sm:py-14">
        <div class="pointer-events-none absolute inset-x-0 top-0 -z-10 h-72 bg-gradient-to-b from-navy-50 to-transparent" aria-hidden="true"></div>

        <div class="container-rich grid gap-8 lg:grid-cols-[16.5rem_minmax(0,1fr)]">

            {{-- ---------------- Sidebar ---------------- --}}
            <aside class="lg:sticky lg:top-28 lg:self-start">
                <div class="overflow-hidden rounded-[1.75rem] border border-ink-100 bg-white shadow-[0_24px_60px_-48px_rgba(7,20,38,0.5)]">
                    <div class="relative isolate overflow-hidden bg-navy-800 px-6 pb-6 pt-7 text-white">
                        <div class="pointer-events-none absolute -right-10 -top-10 -z-10 h-32 w-32 rounded-full bg-brand-500/40 blur-2xl" aria-hidden="true"></div>
                        <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-400 to-gold-400 font-display text-[19px] font-bold text-navy-900 shadow-[0_12px_28px_-12px_rgba(0,0,0,0.6)]">
                            {{ $initials ?: 'I' }}
                        </span>
                        <p class="mt-4 truncate font-display text-[17px] font-bold !text-white">{{ $user->name }}</p>
                        <p class="truncate text-[13px] text-white/60">{{ $user->email }}</p>
                        <span class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-[11.5px] font-semibold text-gold-300 ring-1 ring-white/15">
                            <x-ui-icon name="lightbulb" class="h-3.5 w-3.5" /> {{ __('innovator.role') }}
                        </span>
                    </div>

                    <nav class="space-y-1 p-3" aria-label="{{ __('innovator.portal') }}">
                        @foreach ([
                            ['#overview', 'grid', __('innovator.nav_overview'), true],
                            ['#ideas', 'lightbulb', __('innovator.ideas_title'), false],
                            [route('ideas.create'), 'plus', __('innovator.submit_another'), false],
                            [route('home'), 'globe', __('innovator.back_to_site'), false],
                        ] as [$href, $icon, $label, $current])
                            <a href="{{ $href }}"
                               @class([
                                   'flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-[14px] font-medium transition',
                                   'bg-brand-50 text-brand-700' => $current,
                                   'text-ink-600 hover:bg-ink-50 hover:text-ink-900' => ! $current,
                               ])>
                                <x-ui-icon :name="$icon" class="h-[18px] w-[18px]" /> {{ $label }}
                            </a>
                        @endforeach
                    </nav>

                    <form method="POST" action="{{ route('innovator.logout') }}" class="border-t border-ink-100 p-3">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3.5 py-2.5 text-left text-[14px] font-medium text-ink-500 transition hover:bg-rose-50 hover:text-rose-700">
                            <x-ui-icon name="arrow-left" class="h-[18px] w-[18px]" /> {{ __('innovator.logout') }}
                        </button>
                    </form>
                </div>
            </aside>

            {{-- ---------------- Main ---------------- --}}
            <div class="min-w-0 space-y-8">

                @if (session('saved'))
                    <div class="flex items-center gap-3 rounded-2xl border border-brand-200 bg-white px-5 py-4 text-[14px] font-medium text-brand-800 shadow-[0_14px_30px_-24px_rgba(7,20,38,0.4)]" role="status">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-brand-600">
                            <x-ui-icon name="check" class="h-4 w-4" stroke="2.4" />
                        </span>
                        {{ session('saved') }}
                    </div>
                @endif

                {{-- Welcome, with how far the furthest idea has got --}}
                <div id="overview" class="relative isolate scroll-mt-28 overflow-hidden rounded-[2rem] bg-navy-800 p-7 text-white shadow-[0_40px_80px_-50px_rgba(2,22,52,0.8)] sm:p-9">
                    <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.07]" aria-hidden="true"></div>
                    <div class="pointer-events-none absolute -left-20 -top-28 -z-10 h-72 w-72 rounded-full bg-brand-500/30 blur-[90px]" aria-hidden="true"></div>
                    <div class="pointer-events-none absolute -bottom-24 right-10 -z-10 h-60 w-60 rounded-full bg-gold-400/15 blur-[90px]" aria-hidden="true"></div>

                    <div class="flex flex-col gap-8 md:flex-row md:items-center md:justify-between">
                        <div class="max-w-xl">
                            <p class="eyebrow-invert">{{ __('innovator.eyebrow') }}</p>
                            <h1 class="mt-5 font-display text-[30px] font-bold leading-[1.1] tracking-[-0.02em] !text-white sm:text-[40px]">
                                {{ __('innovator.welcome', ['name' => $firstName]) }}
                            </h1>
                            <p class="mt-3 text-[15.5px] leading-[1.75] text-white/70">{{ __('innovator.lead') }}</p>
                            <a href="{{ route('ideas.create') }}" class="btn-lead group mt-7" data-magnetic>
                                <span class="relative">{{ __('innovator.submit_another') }}</span>
                                <x-ui-icon name="plus" class="relative h-[18px] w-[18px]" />
                            </a>
                        </div>

                        @if ($furthest !== null)
                            {{-- The ring: the furthest stage reached, out of the eight --}}
                            <div class="flex items-center gap-5 rounded-[1.5rem] bg-white/[0.06] p-5 ring-1 ring-white/10 backdrop-blur-sm md:flex-col md:text-center">
                                <div class="relative h-28 w-28 shrink-0">
                                    <svg class="h-full w-full -rotate-90" viewBox="0 0 120 120" aria-hidden="true">
                                        <circle cx="60" cy="60" r="50" fill="none" stroke-width="9" class="stroke-white/10" />
                                        <circle cx="60" cy="60" r="50" fill="none" stroke-width="9" stroke-linecap="round" pathLength="100"
                                                class="stroke-gold-300 transition-[stroke-dashoffset] duration-[1.4s] ease-[cubic-bezier(0.22,1,0.36,1)]"
                                                stroke-dasharray="100" stroke-dashoffset="100"
                                                x-data x-init="requestAnimationFrame(() => $el.style.strokeDashoffset = {{ round(100 - $ring * 100, 2) }})" />
                                    </svg>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                                        <span class="font-display text-[26px] font-bold leading-none !text-white">{{ $num($furthest + 1) }}<span class="text-[15px] text-white/50">/{{ $num($total) }}</span></span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gold-300">{{ __('innovator.furthest') }}</p>
                                    <p class="mt-1 font-display text-[16px] font-bold !text-white">{{ $stages[$stageKeys[$furthest]] }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Figures --}}
                <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    @foreach ([
                        [$ideas->count(), __('innovator.stats.ideas'), 'lightbulb', 'from-navy-600 to-navy-800'],
                        [$counts['in_review'] ?? 0, __('innovator.stats.in_review'), 'clock', 'from-amber-400 to-amber-600'],
                        [$counts['accepted'] ?? 0, __('innovator.stats.accepted'), 'star', 'from-brand-500 to-brand-700'],
                        [$furthest === null ? 0 : $furthest + 1, __('innovator.furthest'), 'rocket', 'from-gold-300 to-gold-500'],
                    ] as $i => [$value, $label, $icon, $gradient])
                        <div class="reveal group relative overflow-hidden rounded-[1.5rem] border border-ink-100 bg-white p-5 transition duration-300 hover:-translate-y-0.5 hover:shadow-[0_22px_44px_-30px_rgba(7,20,38,0.45)]"
                             style="transition-delay: {{ $i * 70 }}ms"
                             x-data="counter({{ (int) $value }})" x-intersect.once="start()">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br {{ $gradient }} text-white shadow-[0_10px_22px_-12px_rgba(7,20,38,0.6)] transition duration-500 group-hover:-rotate-6">
                                <x-ui-icon :name="$icon" class="h-5 w-5" />
                            </span>
                            <p class="mt-4 font-display text-[30px] font-bold leading-none tabular-nums text-ink-950"><span x-text="value">{{ $num($value) }}</span></p>
                            <p class="mt-1.5 text-[13px] text-ink-500">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>

                {{-- ---------------- My ideas ---------------- --}}
                <div id="ideas" class="scroll-mt-28" x-data="{ filter: 'all' }">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <h2 class="font-display text-[24px] font-bold text-ink-950">{{ __('innovator.ideas_title') }}</h2>

                        @if ($ideas->isNotEmpty())
                            <div class="flex flex-wrap gap-1.5 rounded-full bg-white p-1 ring-1 ring-ink-100" role="tablist">
                                @foreach ($tabs as $key => $count)
                                    <button type="button" role="tab" @click="filter = '{{ $key }}'" :aria-selected="filter === '{{ $key }}'"
                                            class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-[13px] font-semibold transition"
                                            :class="filter === '{{ $key }}' ? 'bg-navy-800 text-white shadow' : 'text-ink-600 hover:text-ink-900'">
                                        {{ $key === 'all' ? __('innovator.filter_all') : __('innovator.status.'.$key) }}
                                        <span class="rounded-full px-1.5 text-[11px] tabular-nums"
                                              :class="filter === '{{ $key }}' ? 'bg-white/15' : 'bg-ink-100'">{{ $num($count) }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="mt-5 space-y-5">
                        @forelse ($ideas as $idea)
                            @php
                                $at = $stageAt($idea);
                                [$catIcon, $catBar, $catPill] = $categoryLook[$idea->category] ?? $categoryLook['other'];
                            @endphp
                            <article x-show="filter === 'all' || filter === '{{ $idea->status }}'" x-transition.opacity
                                     class="group relative overflow-hidden rounded-[1.75rem] border border-ink-100 bg-white shadow-[0_20px_50px_-42px_rgba(7,20,38,0.5)] transition duration-300 hover:shadow-[0_28px_60px_-40px_rgba(7,20,38,0.55)]">
                                {{-- The category's colour, down the left edge --}}
                                <span class="absolute inset-y-0 left-0 w-1.5 {{ $catBar }}" aria-hidden="true"></span>

                                <div class="flex flex-col gap-5 p-6 pl-8 sm:flex-row sm:items-start sm:justify-between sm:p-7 sm:pl-9">
                                    <div class="flex min-w-0 gap-4">
                                        <span class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-2xl sm:flex {{ $catPill }}">
                                            <x-ui-icon :name="$catIcon" class="h-5 w-5" />
                                        </span>
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2 text-[12px]">
                                                <span class="font-numeric font-bold tabular-nums text-ink-400">{{ $idea->reference }}</span>
                                                @if ($idea->category_name)
                                                    <span class="rounded-full px-2.5 py-0.5 font-semibold {{ $catPill }}">{{ $idea->category_name }}</span>
                                                @endif
                                            </div>
                                            <h3 class="mt-2 font-display text-[19px] font-bold leading-snug text-ink-950">{{ $idea->title }}</h3>
                                            <p class="mt-1 flex items-center gap-1.5 text-[13px] text-ink-500">
                                                <x-ui-icon name="calendar" class="h-3.5 w-3.5" />
                                                {{ __('innovator.submitted', ['date' => $num($idea->created_at->translatedFormat('j F Y'))]) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[12.5px] font-semibold ring-1 {{ $statusTone[$idea->status] ?? $statusTone['new'] }}">
                                            <span class="h-1.5 w-1.5 rounded-full {{ $statusDot[$idea->status] ?? $statusDot['new'] }}"></span>
                                            {{ __('innovator.status.'.$idea->status) }}
                                        </span>
                                        @if ($idea->document)
                                            <a href="{{ Storage::url($idea->document) }}" target="_blank" rel="noopener"
                                               class="inline-flex items-center gap-1.5 rounded-full border border-ink-200 px-3 py-1.5 text-[12.5px] font-semibold text-ink-700 transition hover:border-brand-300 hover:text-brand-700">
                                                <x-ui-icon name="document" class="h-3.5 w-3.5" /> {{ __('innovator.download') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                {{-- The journey: a stepper, done in green, the current stage in gold --}}
                                <div class="border-t border-ink-100 bg-ink-50/60 px-6 py-5 pl-8 sm:px-7 sm:pl-9">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('innovator.journey') }}</p>
                                        <p class="text-[12.5px] text-ink-600">
                                            <span class="font-semibold text-brand-700">{{ __('innovator.stage_of', ['current' => $num($at + 1), 'total' => $num($total)]) }}</span>
                                            ·
                                            @if ($at + 1 < $total)
                                                {{ __('innovator.next', ['stage' => $stages[$stageKeys[$at + 1]]]) }}
                                            @else
                                                {{ __('innovator.final') }}
                                            @endif
                                        </p>
                                    </div>

                                    <ol class="relative mt-5 grid grid-cols-8" aria-label="{{ __('innovator.journey') }}">
                                        {{-- The line behind the steps, filled up to the current one --}}
                                        <span class="absolute left-[6.25%] right-[6.25%] top-4 h-0.5 -translate-y-1/2 rounded-full bg-ink-200" aria-hidden="true">
                                            <span class="block h-full rounded-full bg-gradient-to-r from-brand-500 to-gold-400" style="width: {{ $total > 1 ? round($at / ($total - 1) * 100, 2) : 0 }}%"></span>
                                        </span>

                                        @foreach ($stages as $key => $label)
                                            <li class="relative flex min-w-0 flex-col items-center text-center" @if ($loop->index === $at) aria-current="step" @endif>
                                                <span @class([
                                                    'relative flex h-8 w-8 items-center justify-center rounded-full ring-4 ring-ink-50 transition',
                                                    'bg-brand-600 text-white' => $loop->index < $at,
                                                    'bg-gold-400 text-navy-900 shadow-[0_0_0_6px_var(--color-gold-100)]' => $loop->index === $at,
                                                    'bg-white text-ink-300 ring-ink-50 border border-ink-200' => $loop->index > $at,
                                                ]) title="{{ $label }}">
                                                    @if ($loop->index < $at)
                                                        <x-ui-icon name="check" class="h-4 w-4" stroke="2.6" />
                                                    @else
                                                        <x-ui-icon :name="$stageIcons[$key] ?? 'check'" class="h-3.5 w-3.5" />
                                                    @endif
                                                </span>
                                                <span @class([
                                                    'mt-2 hidden w-full truncate px-0.5 text-[11px] leading-tight md:block',
                                                    'font-semibold text-ink-900' => $loop->index === $at,
                                                    'text-ink-500' => $loop->index !== $at,
                                                ])>{{ $label }}</span>
                                            </li>
                                        @endforeach
                                    </ol>
                                </div>
                            </article>
                        @empty
                            <div class="flex flex-col items-center rounded-[1.75rem] border-2 border-dashed border-ink-200 bg-white px-6 py-16 text-center">
                                <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 text-white shadow-[0_16px_34px_-16px_var(--color-brand-600)]">
                                    <x-ui-icon name="lightbulb" class="h-7 w-7" />
                                </span>
                                <p class="mt-5 font-display text-[19px] font-bold text-ink-950">{{ __('innovator.empty_title') }}</p>
                                <p class="mt-1.5 max-w-sm text-[14px] text-ink-500">{{ __('innovator.empty_body') }}</p>
                                <a href="{{ route('ideas.create') }}" class="btn-primary mt-6">
                                    <x-ui-icon name="plus" class="h-4 w-4" /> {{ __('innovator.submit_another') }}
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- ---------------- The journey, explained ---------------- --}}
                <div class="rounded-[1.75rem] border border-ink-100 bg-white p-6 sm:p-7">
                    <h2 class="font-display text-[19px] font-bold text-ink-950">{{ __('innovator.journey_title') }}</h2>
                    <p class="mt-1.5 text-[14px] text-ink-500">{{ __('innovator.journey_lead') }}</p>

                    <ol class="mt-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($stages as $key => $label)
                            <li class="flex items-center gap-3 rounded-2xl bg-ink-50 px-4 py-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-brand-600 ring-1 ring-ink-100">
                                    <x-ui-icon :name="$stageIcons[$key] ?? 'check'" class="h-4 w-4" />
                                </span>
                                <span class="min-w-0">
                                    <span class="block font-numeric text-[11px] font-bold tabular-nums text-ink-400">{{ $num(str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)) }}</span>
                                    <span class="block truncate text-[13.5px] font-semibold text-ink-800">{{ $label }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
