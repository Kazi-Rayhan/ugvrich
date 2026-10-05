<x-layouts.app :title="__('site.nav.innovation')" :description="$doc['summary']">

    @php
        /*
         | The Innovation Wing proposal, rendered from config/innovation_framework.php.
         | Every word is the document's own — the layout only decides how each part
         | is shown. The document's section numbering stays in the data; the page
         | reads better without it, so it is stripped on the way out.
         */
        $unnumbered = fn (string $text) => trim(preg_replace('/^[\d০-৯]+(?:\.[\d০-৯]+)*\.?\s*/u', '', $text));
        $title = fn (string $key) => $unnumbered($doc['headings'][$key] ?? '');

        $h2 = 'reveal font-display text-[28px] font-bold leading-[1.15] tracking-tight text-ink-950 sm:text-[38px]';
        $para = 'reveal mt-6 max-w-3xl text-[15.5px] leading-[1.85] text-ink-700';
        $label = 'text-[10.5px] font-semibold uppercase tracking-[0.18em] text-ink-400';
        $rule = 'reveal mb-5 block h-1 w-12 rounded-full bg-brand-600';
        $delay = fn ($i, $step = 45, $max = 300) => 'transition-delay: '.min($i * $step, $max).'ms';

        // Numbers the page counts out are written in the digits of the language being read.
        $num = fn ($value) => app()->getLocale() === 'bn'
            ? strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
            : (string) $value;

        // The strapline lists what the wing does; show each as its own chip.
        [$straplineLead, $straplineItems] = str_contains($doc['strapline'], ':')
            ? explode(':', $doc['strapline'], 2)
            : [null, $doc['strapline']];
        $straplineItems = collect(explode('•', $straplineItems))->map(fn ($item) => trim($item))->filter();

        // The summary names the nine departments between em dashes.
        $summaryParts = explode('—', $doc['summary']);
        $departments = count($summaryParts) >= 3
            ? collect(preg_split('/,\s*|\s+and\s+|\s+এবং\s+/u', $summaryParts[1]))->map(fn ($d) => trim($d))->filter()
            : collect();

        // Each proposal's SDG line, as chips.
        $chips = fn (string $line) => collect(explode(',', $line))->map(fn ($chip) => trim($chip))->filter();

        // Every SDG the six proposals name, once each, in the order they appear.
        $allSdgs = collect($doc['proposed'])
            ->flatMap(fn ($item) => $chips($item['sdg'])->all())
            ->map(fn ($chip) => trim(preg_replace('/\s*\(.*$/u', '', $chip)))
            ->unique()
            ->values();

        $fieldIcons = ['concept' => 'lightbulb', 'how' => 'cog', 'why' => 'chart'];
        $focusIcons = ['target', 'compass', 'users', 'chart', 'globe'];

        $sections = ['summary', 'purpose', 'focus', 'current', 'proposed', 'process', 'organization', 'kpis', 'abbreviations', 'funding', 'conclusion'];
    @endphp

    @php
        $heroVideo = $site->get('innovation_video');
    @endphp

    {{-- Hero, in the same voice as the Services and Research pages: navy, a
         pill eyebrow and the highlighted phrase. A video from Site Settings
         plays behind it. The strip at the foot jumps to each part below. --}}
    <section class="relative isolate overflow-hidden bg-navy-700 text-white">
        @if ($heroVideo)
            {{-- Muted, looped and decorative. With reduced motion it stays on its first frame. --}}
            <video class="pointer-events-none absolute inset-0 -z-20 h-full w-full object-cover"
                   autoplay muted loop playsinline preload="auto"
                   x-data x-init="if (matchMedia('(prefers-reduced-motion: reduce)').matches) { $el.removeAttribute('autoplay'); $el.pause() }"
                   aria-hidden="true" tabindex="-1">
                <source src="{{ Storage::url($heroVideo) }}" type="{{ str_ends_with(strtolower($heroVideo), '.webm') ? 'video/webm' : 'video/mp4' }}">
            </video>
            <div class="pointer-events-none absolute inset-0 -z-10 bg-navy-900/70" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-r from-navy-900/90 via-navy-800/60 to-transparent" aria-hidden="true"></div>
        @else
            <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.15]" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -right-40 -top-48 -z-10 h-[34rem] w-[34rem] rounded-full bg-brand-600/35 blur-[130px]" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-52 -left-32 -z-10 h-[30rem] w-[30rem] rounded-full border border-white/10" aria-hidden="true"></div>
        @endif

        <div class="container-rich py-16 sm:py-20">
            <p class="reveal eyebrow-invert">{{ __('site.innovation.hero_eyebrow') }}</p>

            <h1 class="reveal mt-7 max-w-4xl font-display text-[38px] font-bold leading-[1.05] tracking-[-0.03em] !text-white sm:text-[58px]">
                {!! __('site.innovation.hero_title') !!}
            </h1>

            <p class="reveal mt-7 max-w-2xl text-[17.5px] leading-[1.8] text-white/75">{{ __('site.innovation.hero_lead') }}</p>

            <div class="reveal mt-10 flex flex-wrap gap-3">
                <a href="#areas" class="btn-invert px-7 py-4 text-[15px]">
                    {{ __('site.innovation.explore_areas') }}
                </a>
                <a href="{{ route('innovation.screening') }}"
                   class="group inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-full border-2 border-brand-400 bg-transparent px-7 py-[14px] text-[15px] font-semibold text-white transition duration-300 hover:-translate-y-0.5 hover:border-brand-500 hover:bg-brand-600">
                    {{ __('site.screening.nav') }}
                    <x-ui-icon name="arrow-up-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5" />
                </a>
                <a href="{{ route('ideas.create') }}" class="btn-lead group">
                    <span class="relative">{{ __('site.actions.submit_idea') }}</span>
                    <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                </a>
            </div>
        </div>

        {{-- The sections, by name --}}
        <nav class="border-t border-white/10" aria-label="{{ $doc['title'] }}">
            <div class="container-rich flex gap-1 overflow-x-auto py-2.5 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                <a href="#areas"
                   class="shrink-0 whitespace-nowrap rounded-full px-3.5 py-1.5 text-[12.5px] font-medium text-white/55 transition hover:bg-white/10 hover:text-white">
                    {{ __('site.innovation.catalogue_eyebrow') }}
                </a>
                @foreach ($sections as $anchor)
                    <a href="#{{ $anchor }}"
                       class="shrink-0 whitespace-nowrap rounded-full px-3.5 py-1.5 text-[12.5px] font-medium text-white/55 transition hover:bg-white/10 hover:text-white">
                        {{ $title($anchor) }}
                    </a>
                @endforeach
            </div>
        </nav>
    </section>

    {{-- ---------------- Innovation areas ----------------

         The departmental areas, laid out the way the Services page lays out its
         categories: the heading and the numbered focus topics flow across a
         four-column grid while the image (or a video) holds the top-right
         corner. --}}
    <section id="areas" class="scroll-mt-28 bg-white py-20 sm:py-28">
        <div class="container-rich">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <x-section-heading :eyebrow="__('site.innovation.catalogue_eyebrow')" />

                <a href="{{ route('ideas.create') }}" class="btn-primary reveal shrink-0">
                    {{ __('site.actions.submit_idea') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                </a>
            </div>

            <div class="mt-14 space-y-20 sm:space-y-28">
                @foreach ($areas as $area)
                    <section id="area-{{ $area->slug }}" class="scroll-mt-28 border-t border-ink-100 pt-12 first:border-0 first:pt-0">
                        <div class="grid gap-x-10 gap-y-10 lg:grid-cols-4 lg:[grid-auto-flow:dense]">

                            {{-- Heading: the left half of the first row --}}
                            <div class="reveal lg:col-span-2">
                                <p class="text-[11.5px] font-bold uppercase tracking-[0.18em] text-brand-600">
                                    <span class="text-brand-300">//</span> {{ __('site.innovation.focus_eyebrow') }}
                                </p>

                                <h2 class="mt-4 font-display text-[28px] font-bold leading-[1.1] tracking-tight text-ink-950 sm:text-[40px]">
                                    <a href="{{ route('innovation.area', $area) }}" class="transition-colors hover:text-brand-700">{{ $area->name }}</a>
                                </h2>

                                <div class="mt-4 flex flex-wrap items-center gap-2.5">

                                    <span class="rounded-full bg-brand-50 px-3 py-1.5 font-numeric text-[12.5px] font-bold tabular-nums text-brand-700">
                                        {{ trans_choice('site.innovation.innovation_count', $area->innovations->count(), ['count' => $num($area->innovations->count())]) }}
                                    </span>

                                    @if ($area->projects_count)
                                        <span class="rounded-full bg-ink-50 px-3 py-1.5 font-numeric text-[12.5px] font-semibold tabular-nums text-ink-600">
                                            {{ trans_choice('site.innovation.project_count', $area->projects_count, ['count' => $num($area->projects_count)]) }}
                                        </span>
                                    @endif
                                </div>

                                @if ($area->description)
                                    <p class="mt-5 text-[14.5px] leading-[1.9] text-ink-600">{{ $area->description }}</p>
                                @endif
                            </div>

                            {{-- The image (or a video in its place) holds the top-right corner across two rows --}}
                            <div class="reveal lg:col-span-2 lg:col-start-3 lg:row-span-2 lg:row-start-1">
                                @if ($area->video)
                                    {{-- Muted and looped. With reduced motion it stays on its first frame. --}}
                                    <div class="relative aspect-[16/11] h-full overflow-hidden rounded-[1.5rem] bg-brand-50">
                                        <video class="h-full w-full object-cover"
                                               autoplay muted loop playsinline preload="metadata"
                                               @if ($area->image) poster="{{ Storage::url($area->image) }}" @endif
                                               x-data x-init="if (matchMedia('(prefers-reduced-motion: reduce)').matches) { $el.removeAttribute('autoplay'); $el.pause() }"
                                               aria-label="{{ __('site.projects.video_title', ['title' => $area->name]) }}">
                                            <source src="{{ Storage::url($area->video) }}"
                                                    type="{{ str_ends_with(strtolower($area->video), '.webm') ? 'video/webm' : 'video/mp4' }}">
                                        </video>
                                    </div>
                                @elseif ($area->image)
                                    <x-media-frame :src="$area->image" :alt="$area->name"
                                                   ratio="aspect-[16/11]" class="h-full rounded-[1.5rem] bg-brand-50" />
                                @else
                                    {{-- No photograph yet: the department's own drawn poster. --}}
                                    <div class="aspect-[16/11] h-full overflow-hidden rounded-[1.5rem]">
                                        <x-service-poster :sector="$area->department" :seed="$area->slug" class="h-full w-full object-cover" />
                                    </div>
                                @endif
                            </div>

                            {{-- The innovations, as small cards (photo, type, name), filling every
                                 cell the image leaves, as a service category lists its services. --}}
                            @foreach ($area->innovations as $j => $innovation)
                                <x-cards.innovation-mini :innovation="$innovation" :slug="$innovationSlugs[$innovation->id] ?? null" :index="$j" />
                            @endforeach
                        </div>

                        <div class="reveal mt-10 flex flex-wrap items-center gap-4">
                            <a href="{{ route('innovation.area', $area) }}" class="btn-ghost">
                                {{ __('site.innovation.area_detail') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                            </a>
                            <a href="{{ route('ideas.create') }}" class="text-[13.5px] font-semibold text-brand-700 transition hover:text-brand-600">
                                {{ __('site.actions.submit_idea') }}
                            </a>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- Executive Summary ---------------- --}}
    <section id="summary" class="scroll-mt-28 bg-white py-20 sm:py-24">
        <div class="container-rich grid gap-12 lg:grid-cols-[1.15fr_0.85fr] lg:gap-16">
            <div>
                <span class="{{ $rule }}" aria-hidden="true"></span>
                <h2 class="{{ $h2 }}">{{ $title('summary') }}</h2>
                <p class="reveal mt-7 max-w-3xl text-[17px] leading-[1.85] text-ink-700 sm:text-[18px]">{{ $doc['summary'] }}</p>
            </div>

            @if ($departments->isNotEmpty())
                <div class="reveal self-start rounded-[2rem] border border-ink-100 bg-ink-50/70 p-7 sm:p-8">
                    <p class="font-display text-[40px] font-bold leading-none text-brand-700">{{ $num($departments->count()) }}</p>
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach ($departments as $department)
                            <span class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-white px-3.5 py-1.5 text-[13px] font-medium text-ink-700 transition duration-300 hover:border-brand-300 hover:text-brand-700">
                                <span class="h-1.5 w-1.5 rounded-full bg-brand-600"></span>{{ $department }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- ---------------- Purpose & Strategic Direction ---------------- --}}
    <section id="purpose" class="scroll-mt-28 relative isolate overflow-hidden bg-ink-950 py-20 sm:py-24">
        <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.12]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -left-32 top-1/2 -z-10 h-[26rem] w-[26rem] -translate-y-1/2 rounded-full bg-brand-600/25 blur-[120px]" aria-hidden="true"></div>

        <div class="container-rich">
            <span class="reveal mb-5 block h-1 w-12 rounded-full bg-brand-500" aria-hidden="true"></span>
            <h2 class="reveal font-display text-[28px] font-bold leading-[1.15] tracking-tight !text-white sm:text-[38px]">{{ $title('purpose') }}</h2>

            <p class="reveal mt-8 max-w-5xl font-display text-[21px] font-semibold leading-[1.6] !text-white sm:text-[28px]">
                {{ $doc['purpose'] }}
            </p>
        </div>
    </section>

    {{-- ---------------- Vision, Mission & Focus ---------------- --}}
    <section id="focus" class="scroll-mt-28 bg-white py-20 sm:py-24">
        <div class="container-rich">
            <span class="{{ $rule }}" aria-hidden="true"></span>
            <h2 class="{{ $h2 }}">{{ $title('focus') }}</h2>

            <div class="mt-12 grid gap-5 lg:grid-cols-6">
                @foreach ($doc['focus'] as $i => [$pillar, $direction])
                    @php
                        /* Only the development path is a chain: several short steps
                           joined by arrows. A sentence that happens to use an arrow
                           stays a sentence. */
                        $steps = collect(explode('→', rtrim($direction, '।. ')))->map(fn ($step) => trim($step))->filter();
                        $isPath = $steps->count() >= 3 && $steps->max(fn ($step) => mb_strlen($step)) <= 30;
                    @endphp

                    <article @class([
                        'reveal group relative overflow-hidden rounded-[1.75rem] p-8 transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1.5',
                        'lg:col-span-6 bg-gradient-to-br from-brand-700 to-brand-600 shadow-[0_30px_70px_-45px_var(--color-brand-600)]' => $i === 0,
                        'lg:col-span-6 border border-ink-100 bg-ink-50/70' => $isPath,
                        'lg:col-span-3 border border-ink-100 bg-white hover:border-brand-200 hover:shadow-[0_30px_60px_-42px_rgba(2,34,81,0.45)]' => $i !== 0 && ! $isPath,
                    ]) style="{{ $delay($i, 55) }}">

                        <span @class([
                            'pointer-events-none absolute -right-12 -top-12 h-36 w-36 rounded-full transition duration-500',
                            'border border-white/15 group-hover:border-white/30' => $i === 0,
                            'border border-ink-100 group-hover:border-brand-200' => $i !== 0,
                        ]) aria-hidden="true"></span>

                        <span @class([
                            'flex h-12 w-12 items-center justify-center rounded-2xl transition duration-500 group-hover:rotate-[-6deg]',
                            'bg-white text-brand-700' => $i === 0,
                            'bg-brand-50 text-brand-600' => $i !== 0,
                        ])>
                            <x-ui-icon :name="$focusIcons[$i] ?? 'check'" class="h-5 w-5" />
                        </span>

                        <h3 @class([
                            'relative mt-6 font-display text-[22px] font-bold sm:text-[24px]',
                            '!text-white' => $i === 0,
                            'text-ink-950' => $i !== 0,
                        ])>{{ $pillar }}</h3>

                        @if ($isPath)
                            <ol class="relative mt-6 flex flex-wrap items-center gap-2">
                                @foreach ($steps as $step)
                                    <li class="flex items-center gap-2">
                                        <span class="flex items-center gap-2.5 rounded-2xl border border-brand-100 bg-white px-4 py-2.5 shadow-[0_10px_24px_-20px_rgba(2,34,81,0.6)]">
                                            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-brand-600 font-display text-[11px] font-bold text-white">{{ $num($loop->iteration) }}</span>
                                            <span class="font-display text-[14.5px] font-semibold text-brand-800">{{ $step }}</span>
                                        </span>
                                        @unless ($loop->last)
                                            <x-ui-icon name="arrow-right" class="h-4 w-4 shrink-0 text-ink-300" />
                                        @endunless
                                    </li>
                                @endforeach
                            </ol>
                        @else
                            <p @class([
                                'relative mt-4 text-[15.5px] leading-[1.8]',
                                'text-white/85' => $i === 0,
                                'text-ink-700' => $i !== 0,
                            ])>{{ $direction }}</p>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- Current Innovations ---------------- --}}
    <section id="current" class="scroll-mt-28 relative isolate overflow-hidden bg-ink-50 py-20 sm:py-24">
        <div class="pointer-events-none absolute -right-32 top-16 -z-10 h-80 w-80 rounded-full bg-brand-100/60 blur-3xl" aria-hidden="true"></div>

        <div class="container-rich">
            <span class="{{ $rule }}" aria-hidden="true"></span>
            <h2 class="{{ $h2 }}">{{ $title('current') }}</h2>

            <div class="mt-12 grid gap-6 lg:grid-cols-3">
                @foreach ($doc['current'] as $i => [$innovation, $lead, $highlights])
                    @php
                        // The cell carries the highlights and then what comes next.
                        $split = preg_split('/\s*'.preg_quote($doc['headings']['next'], '/').'\s*:\s*/u', $highlights, 2);
                        [$now, $next] = [$split[0], $split[1] ?? null];
                    @endphp

                    <article id="{{ $doc['current_slugs'][$i] }}"
                             class="reveal group relative flex h-full scroll-mt-28 flex-col overflow-hidden rounded-[2rem] bg-white ring-1 ring-ink-200/70
                                    transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]
                                    hover:-translate-y-1.5 hover:ring-brand-300 hover:shadow-[0_40px_80px_-50px_rgba(2,34,81,0.5)]"
                             style="{{ $delay($i, 70) }}">

                        {{-- Cover: the photograph where there is one, generated art otherwise --}}
                        <div class="relative">
                            <x-media-frame :src="$doc['current_covers'][$i] ?? null" :alt="$innovation" :seed="$innovation"
                                           ratio="aspect-[16/10]" class="bg-ink-100" />

                            <span class="absolute left-6 top-5 flex h-10 w-10 items-center justify-center rounded-xl bg-white/90 font-numeric text-[13px] font-bold tabular-nums text-ink-950 shadow-sm backdrop-blur-sm">
                                {{ $num(str_pad($i + 1, 2, '0', STR_PAD_LEFT)) }}
                            </span>
                        </div>

                        {{-- The name sits below the photograph, not over it: the
                             image is left as it is, with nothing darkening it. --}}
                        <div class="px-7 pb-6 pt-6">
                            <h3 class="font-display text-[19px] font-bold leading-snug text-ink-950">{{ $innovation }}</h3>

                            <p class="mt-4 inline-flex items-start gap-2 rounded-full bg-ink-50 px-3.5 py-1.5 text-[12.5px] font-medium text-ink-600">
                                <x-ui-icon name="users" class="mt-0.5 h-3.5 w-3.5 shrink-0 text-brand-600" />
                                {{ $lead }}
                            </p>

                            <p class="mt-5 text-[14.5px] leading-[1.8] text-ink-700">{{ $now }}</p>

                            <a href="{{ route('innovation.show', $doc['current_slugs'][$i]) }}"
                               class="mt-5 inline-flex items-center gap-2 text-[13.5px] font-semibold text-brand-700 transition group-hover:gap-3">
                                <span class="absolute inset-0" aria-hidden="true"></span>
                                {{ __('site.cards.view_innovation') }}
                                <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" />
                            </a>
                        </div>

                        @if ($next)
                            <div class="mt-auto border-t border-ink-100 bg-gradient-to-br from-brand-50 to-white px-7 py-5">
                                <p class="flex items-center gap-2 {{ $label }}">
                                    <x-ui-icon name="arrow-right" class="h-3.5 w-3.5 text-brand-600" />
                                    {{ $doc['headings']['next'] }}
                                </p>
                                <p class="mt-2 text-[13.5px] leading-relaxed text-brand-900">{{ $next }}</p>
                            </div>
                        @endif
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- Proposed Innovations ---------------- --}}
    <section id="proposed" class="scroll-mt-28 bg-white py-20 sm:py-24">
        <div class="container-rich">
            <span class="{{ $rule }}" aria-hidden="true"></span>
            <h2 class="{{ $h2 }}">{{ $title('proposed') }}</h2>
            <p class="{{ $para }}">{{ $doc['proposed_intro'] }}</p>

            {{-- Jump to any of the six, and every SDG they name --}}
            <div class="reveal mt-8 flex flex-wrap items-center gap-2">
                @foreach ($doc['proposed'] as $item)
                    <a href="#{{ $item['slug'] }}"
                       class="rounded-full border border-ink-200 bg-white px-4 py-1.5 text-[13px] font-medium text-ink-700 transition duration-300 hover:-translate-y-0.5 hover:border-brand-400 hover:text-brand-700">
                        {{ $item['name'] }}
                    </a>
                @endforeach
            </div>

            @if ($allSdgs->isNotEmpty())
                <div class="reveal mt-4 flex flex-wrap items-center gap-1.5">
                    <span class="{{ $label }} me-1">{{ $doc['headings']['sdg'] }}</span>
                    @foreach ($allSdgs as $sdg)
                        <span class="rounded-lg bg-navy-700 px-2.5 py-1 font-display text-[12px] font-bold text-white">{{ $sdg }}</span>
                    @endforeach
                </div>
            @endif

            {{-- Two to a row: the six read as a set to choose from, and the
                 full write-up of any one of them is a click away. --}}
            <div class="mt-12 grid gap-6 lg:grid-cols-2">
                @foreach ($doc['proposed'] as $i => $item)
                    <article id="{{ $item['slug'] }}"
                             class="reveal group relative flex h-full scroll-mt-28 flex-col overflow-hidden rounded-[2rem] border border-ink-100 bg-white transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1 hover:border-brand-200 hover:shadow-[0_44px_90px_-60px_rgba(2,34,81,0.55)]"
                             style="{{ $delay($i, 45) }}">

                        {{-- Cover band, as a case study opens --}}
                        <header class="relative isolate overflow-hidden">
                            <x-media-frame :src="$doc['proposed_covers'][$i] ?? null" :alt="$item['name']" :seed="$item['name'].' '.$item['tagline']"
                                           ratio="aspect-[16/9]" class="bg-ink-100" />

                            <span class="absolute left-5 top-4 flex h-9 w-9 items-center justify-center rounded-xl bg-white/90 font-numeric text-[12.5px] font-bold tabular-nums text-ink-950 shadow-sm backdrop-blur-sm">
                                {{ $num(str_pad($i + 1, 2, '0', STR_PAD_LEFT)) }}
                            </span>
                        </header>

                        {{-- Name, tagline and the rest sit below the photograph
                             rather than over it, so nothing darkens the image. --}}
                        <div class="flex flex-1 flex-col px-6 pb-6 pt-5">
                            <h3 class="font-display text-[21px] font-bold leading-[1.2] text-ink-950 sm:text-[23px]">
                                {{ $item['name'] }}
                                @isset($item['native'])
                                    <span class="font-sans text-[14px] font-medium text-ink-400">({{ $item['native'] }})</span>
                                @endisset
                            </h3>

                            @isset($item['subtitle'])
                                <p class="mt-1.5 text-[13px] font-semibold text-brand-700">{{ $item['subtitle'] }}</p>
                            @endisset

                            <p class="mt-4 inline-flex w-fit items-center gap-2 rounded-full bg-brand-50 px-3.5 py-1.5 text-[12.5px] font-semibold text-brand-700">
                                <x-ui-icon name="sparkles" class="h-3.5 w-3.5" />
                                {{ $item['tagline'] }}
                            </p>

                            {{-- The concept only. How it works and why it matters
                                 are on the innovation's own page. --}}
                            <p class="mt-4 text-[14.5px] leading-[1.8] text-ink-700">{{ \Illuminate\Support\Str::limit($item['concept'], 190) }}</p>

                            <ul class="mt-5 flex flex-wrap gap-1.5">
                                @foreach ($chips($item['sdg'])->take(3) as $chip)
                                    <li class="rounded-lg border border-navy-100 bg-ink-50 px-2.5 py-1 text-[12px] font-semibold text-navy-700">{{ $chip }}</li>
                                @endforeach
                            </ul>

                            <a href="{{ route('innovation.show', $item['slug']) }}"
                               class="mt-auto inline-flex items-center gap-2 pt-6 text-[13.5px] font-semibold text-brand-700 transition group-hover:gap-3">
                                <span class="absolute inset-0" aria-hidden="true"></span>
                                {{ __('site.innovation.read_full') }}
                                <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5" />
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- Innovation Process Framework ----------------
         The same journey the home page draws: interlocking chevrons, one soft
         colour per stage, following an idea from ideation to market. --}}
    <section id="process" class="scroll-mt-28 border-y border-ink-100 bg-ink-50 py-20 sm:py-24">
        <div class="container-rich">
            <span class="{{ $rule }}" aria-hidden="true"></span>
            <h2 class="{{ $h2 }}">{{ $title('process') }}</h2>

            @php
                // One icon per stage, in the document's order.
                $stageIcons = ['search', 'document', 'cog', 'target', 'key', 'rocket', 'briefcase'];

                // One soft, friendly colour per stage: pastel block, bright icon tile.
                // Listed in full so Tailwind keeps every class.
                $tones = [
                    'bg-sky-50 text-ink-950',
                    'bg-indigo-50 text-ink-950',
                    'bg-violet-50 text-ink-950',
                    'bg-pink-50 text-ink-950',
                    'bg-amber-50 text-ink-950',
                    'bg-emerald-50 text-ink-950',
                    'bg-blue-50 text-ink-950',
                ];
                $iconTones = [
                    'bg-sky-500 text-white', 'bg-indigo-500 text-white', 'bg-violet-500 text-white',
                    'bg-pink-500 text-white', 'bg-amber-500 text-white', 'bg-emerald-500 text-white',
                    'bg-blue-600 text-white',
                ];
            @endphp

            <div class="reveal relative isolate mt-10 overflow-hidden rounded-[2rem] border border-ink-100 bg-white p-6 shadow-[0_24px_60px_-44px_rgba(11,15,24,0.4)] sm:p-10">
                <div class="pointer-events-none absolute inset-0 -z-10 text-brand-700 grid-overlay opacity-25 [mask-image:linear-gradient(to_bottom,black,transparent_70%)]" aria-hidden="true"></div>

                {{-- From the first stage to the last, as the document names them --}}
                <div class="flex flex-wrap items-center justify-end gap-2 text-[13px] font-medium text-ink-500">
                    <span class="h-2 w-2 rounded-full bg-sky-500"></span> {{ $unnumbered($doc['process'][0][0]) }}
                    <x-ui-icon name="arrow-right" class="h-3.5 w-3.5" />
                    <span class="h-2 w-2 rounded-full bg-blue-600"></span> {{ $unnumbered($doc['process'][count($doc['process']) - 1][0]) }}
                </div>

                <ol class="mt-6 grid gap-2 sm:grid-cols-2 lg:grid-cols-4 lg:gap-y-3">
                    @foreach ($doc['process'] as $i => [$stage, $what, $output])
                        <li class="group relative">
                            <div @class([
                                'relative flex h-full flex-col justify-between gap-5 rounded-2xl p-5 transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:-translate-y-1 group-hover:shadow-[0_22px_40px_-24px_rgba(11,15,24,0.35)] lg:rounded-none lg:py-6 lg:pl-10 lg:pr-9',
                                $tones[$i] ?? 'bg-sky-50 text-ink-950',
                                // Chevron shape on desktop: notch on the left (except the first of a row), point on the right.
                                'lg:[clip-path:polygon(0_0,calc(100%-22px)_0,100%_50%,calc(100%-22px)_100%,0_100%)] lg:pl-6' => $i % 4 === 0,
                                'lg:[clip-path:polygon(0_0,calc(100%-22px)_0,100%_50%,calc(100%-22px)_100%,0_100%,22px_50%)]' => $i % 4 !== 0,
                            ])>
                                <span class="flex h-12 w-12 items-center justify-center rounded-xl transition duration-500 group-hover:scale-110 group-hover:rotate-[-6deg] {{ $iconTones[$i] ?? 'bg-sky-500 text-white' }}">
                                    <x-ui-icon :name="$stageIcons[$i] ?? 'check'" class="h-6 w-6" stroke="1.7" />
                                </span>
                                <div>
                                    <p class="font-display text-[17px] font-bold leading-tight">{{ $unnumbered($stage) }}</p>
                                    <p class="mt-1.5 text-[13px] leading-snug text-ink-600">{{ $what }}</p>
                                    <p class="mt-3 inline-flex rounded-lg bg-white/70 px-2.5 py-1 text-[12px] font-semibold text-ink-700">{{ $output }}</p>
                                </div>
                            </div>

                            {{-- Mobile connector --}}
                            @unless ($loop->last)
                                <span class="flex justify-center py-1 text-ink-300 sm:hidden" aria-hidden="true">
                                    <x-ui-icon name="chevron-down" class="h-4 w-4" />
                                </span>
                            @endunless
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    {{-- ---------------- Organization & Mentorship ---------------- --}}
    <section id="organization" class="scroll-mt-28 bg-white py-20 sm:py-24">
        <div class="container-rich">
            <span class="{{ $rule }}" aria-hidden="true"></span>
            <h2 class="{{ $h2 }}">{{ $title('organization') }}</h2>

            <div class="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($doc['organization'] as $i => [$role, $responsibility])
                    <div @class([
                        'reveal group relative overflow-hidden rounded-[1.75rem] p-7 transition duration-500 hover:-translate-y-1.5',
                        'md:col-span-2 lg:col-span-3 bg-gradient-to-br from-navy-700 to-navy-800 shadow-[0_34px_70px_-48px_rgba(2,34,81,0.9)]' => $i === 0,
                        'border border-ink-100 bg-white hover:border-brand-200 hover:shadow-[0_28px_56px_-42px_rgba(2,34,81,0.45)]' => $i !== 0,
                    ]) style="{{ $delay($i, 55) }}">

                        <span @class([
                            'pointer-events-none absolute -right-10 -top-10 h-28 w-28 rounded-full border transition duration-500 group-hover:scale-110',
                            'border-white/15' => $i === 0,
                            'border-ink-100' => $i !== 0,
                        ]) aria-hidden="true"></span>

                        <span @class([
                            'flex h-11 w-11 items-center justify-center rounded-xl font-numeric text-[13px] font-bold tabular-nums',
                            'bg-white text-navy-800' => $i === 0,
                            'bg-brand-50 text-brand-700' => $i !== 0,
                        ])>{{ $num(str_pad($i + 1, 2, '0', STR_PAD_LEFT)) }}</span>

                        <p @class([
                            'relative mt-5 font-display text-[17px] font-bold leading-snug',
                            '!text-white' => $i === 0,
                            'text-ink-950' => $i !== 0,
                        ])>{{ $role }}</p>

                        <p @class([
                            'relative mt-2 text-[14px] leading-relaxed',
                            'text-white/75' => $i === 0,
                            'text-ink-600' => $i !== 0,
                        ])>{{ $responsibility }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- KPIs ---------------- --}}
    <section id="kpis" class="scroll-mt-28 relative isolate overflow-hidden border-y border-ink-100 bg-ink-50 py-20 sm:py-24">
        <div class="pointer-events-none absolute -left-24 bottom-0 -z-10 h-72 w-72 rounded-full bg-brand-100/60 blur-3xl" aria-hidden="true"></div>

        <div class="container-rich">
            <span class="{{ $rule }}" aria-hidden="true"></span>
            <h2 class="{{ $h2 }}">{{ $title('kpis') }}</h2>

            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($doc['kpis'] as $i => [$kpi, $target])
                    @php
                        // The target opens with its number; set that big and keep the rest beside it.
                        preg_match('/^(\S+)\s*(.*)$/u', $target, $m);
                        [$figure, $rest] = [$m[1] ?? $target, trim($m[2] ?? '')];
                    @endphp

                    <div class="reveal group relative flex h-full flex-col overflow-hidden rounded-[1.75rem] border border-ink-100 bg-white p-7 transition duration-500 hover:-translate-y-1.5 hover:border-brand-300 hover:shadow-[0_32px_64px_-44px_rgba(2,34,81,0.5)]"
                         style="{{ $delay($i, 55) }}">
                        <span class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-brand-50 to-transparent opacity-0 transition duration-500 group-hover:opacity-100" aria-hidden="true"></span>

                        <p class="relative {{ $label }}">{{ $kpi }}</p>

                        <p class="relative mt-4 font-numeric text-[46px] font-bold leading-none tabular-nums text-brand-700">{{ $figure }}</p>

                        @if ($rest !== '')
                            <p class="relative mt-3 text-[13.5px] leading-relaxed text-ink-600">{{ $rest }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- Abbreviations & Short Forms ---------------- --}}
    <section id="abbreviations" class="scroll-mt-28 bg-white py-20 sm:py-24">
        <div class="container-rich">
            <span class="{{ $rule }}" aria-hidden="true"></span>
            <h2 class="{{ $h2 }}">{{ $title('abbreviations') }}</h2>

            <dl class="reveal mt-12 grid gap-px overflow-hidden rounded-[1.75rem] border border-ink-100 bg-ink-100 lg:grid-cols-2">
                @foreach ($doc['abbreviations'] as [$short, $full])
                    <div class="group flex items-baseline gap-4 bg-white px-6 py-4 transition duration-300 hover:bg-brand-50/60">
                        <dt class="w-16 shrink-0 font-display text-[13.5px] font-bold text-brand-700">{{ $short }}</dt>
                        <dd class="text-[13.5px] leading-relaxed text-ink-700">{{ $full }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- ---------------- Funding & Sustainability ---------------- --}}
    <section id="funding" class="scroll-mt-28 border-y border-ink-100 bg-ink-50 py-20 sm:py-24">
        <div class="container-rich">
            <span class="{{ $rule }}" aria-hidden="true"></span>
            <h2 class="{{ $h2 }}">{{ $title('funding') }}</h2>

            <div class="mt-12 grid gap-5 lg:grid-cols-2">
                @foreach ($doc['funding'] as $i => $paragraph)
                    @php
                        // Each line opens with its own label, then a list.
                        $split = explode(':', $paragraph, 2);
                        [$heading, $body] = count($split) === 2 ? [trim($split[0]), trim($split[1])] : [null, $paragraph];
                        $items = $body ? collect(explode(',', rtrim($body, '।. ')))->map(fn ($x) => trim($x))->filter() : collect();
                    @endphp

                    <div class="reveal rounded-[1.75rem] border border-ink-100 bg-white p-8" style="{{ $delay($i, 60) }}">
                        @if ($heading)
                            <p class="{{ $label }}">{{ $heading }}</p>
                        @endif

                        @if ($items->count() > 1)
                            <ul class="mt-5 flex flex-wrap gap-2">
                                @foreach ($items as $item)
                                    <li class="inline-flex items-center gap-2 rounded-full border border-ink-200 bg-ink-50 px-4 py-2 text-[13.5px] font-medium text-ink-700 transition duration-300 hover:border-brand-300 hover:bg-white hover:text-brand-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-brand-600"></span>{{ $item }}
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="mt-4 text-[15px] leading-[1.8] text-ink-700">{{ $body }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- Conclusion ---------------- --}}
    <section id="conclusion" class="scroll-mt-28 bg-white py-20 sm:py-24">
        <div class="container-rich">
            <div class="reveal relative isolate overflow-hidden rounded-[2.5rem] bg-navy-700 px-8 py-16 sm:px-14 sm:py-20">
                <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-20" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -right-28 -top-28 -z-10 h-80 w-80 rounded-full bg-brand-600/50" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-32 -left-24 -z-10 h-72 w-72 rounded-full border border-white/10" aria-hidden="true"></div>

                <span class="mb-6 block h-1 w-12 rounded-full bg-brand-400" aria-hidden="true"></span>
                <h2 class="font-display text-[28px] font-bold leading-tight !text-white sm:text-[38px]">{{ $title('conclusion') }}</h2>
                <p class="mt-8 max-w-4xl text-[17px] leading-[1.85] text-white/85">{{ $doc['conclusion'] }}</p>
            </div>
        </div>
    </section>
</x-layouts.app>
