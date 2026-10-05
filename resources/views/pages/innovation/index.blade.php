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

        $sections = ['focus', 'process', 'areas', 'funding', 'conclusion'];
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
                @foreach ($sections as $anchor)
                    <a href="#{{ $anchor }}"
                       class="shrink-0 whitespace-nowrap rounded-full px-3.5 py-1.5 text-[12.5px] font-medium text-white/55 transition hover:bg-white/10 hover:text-white">
                        {{ $anchor === 'areas' ? __('site.innovation.catalogue_eyebrow') : $title($anchor) }}
                    </a>
                @endforeach
            </div>
        </nav>
    </section>

    {{-- ---------------- Vision, Mission & Focus ----------------
         Straight after the hero: the vision and the mission. (The document's
         culture, development path and impact lines are not shown here.) --}}
    @php
        $focusItems = collect($doc['focus'])->values();
        $isArrowPath = function (string $text) {
            $steps = collect(explode('→', rtrim($text, '।. ')))->map(fn ($step) => trim($step))->filter();

            return $steps->count() >= 3 && $steps->max(fn ($step) => mb_strlen($step)) <= 30 ? $steps->values() : null;
        };
        // Bengali digits back to ASCII, so "SDG ৩,৪" reads the same as "SDG 3,4".
        $ascii = fn (string $text) => strtr($text, ['০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9']);

        [$vision, $mission] = [$focusItems->get(0), $focusItems->get(1)];
        $rest = $focusItems->slice(2)->values();
        $path = $rest->first(fn ($item) => $isArrowPath($item[1]));
        $shift = $rest->first(fn ($item) => $item !== $path && str_contains($item[1], '→'));
        $impact = $rest->first(fn ($item) => preg_match('/SDG\s*[\d০-৯]/u', $item[1]));
        $others = $rest->reject(fn ($item) => in_array($item, [$path, $shift, $impact], true));
    @endphp

    <section id="focus" class="relative isolate scroll-mt-28 overflow-hidden bg-ink-50 py-20 sm:py-24">
        <div class="pointer-events-none absolute inset-0 -z-10 text-brand-700 grid-overlay opacity-25 [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]" aria-hidden="true"></div>

        <div class="container-rich">
            <p class="reveal eyebrow"><span class="h-1.5 w-1.5 rounded-full bg-current"></span>{{ $title('focus') }}</p>

            <div class="mt-10 grid gap-5 lg:grid-cols-12">
                {{-- Vision and mission, drawn the same way: the statement over a target
                     of slow-turning rings, with the word itself as an outlined watermark. --}}
                @foreach ([[$vision, 'target', 'lg:col-span-7', 'text-[22px] sm:text-[28px]'], [$mission, 'compass', 'lg:col-span-5', 'text-[19px] sm:text-[22px]']] as $k => [$statement, $icon, $span, $size])
                    @continue (! $statement)
                    <article data-tilt class="wing-tile reveal spotlight spotlight-invert group relative isolate flex min-h-[22rem] flex-col justify-between overflow-hidden rounded-[2rem] bg-navy-900 p-8 text-white shadow-[0_40px_80px_-50px_rgba(2,22,52,0.85)] sm:p-10 {{ $span }}"
                             style="{{ $delay($k, 90) }}">
                        {{-- Light and texture --}}
                        <div class="pointer-events-none absolute -left-24 -top-24 -z-10 h-80 w-80 rounded-full bg-brand-500/35 blur-[90px]" aria-hidden="true"></div>
                        <div class="pointer-events-none absolute -bottom-28 right-0 -z-10 h-72 w-72 rounded-full bg-gold-400/20 blur-[90px]" aria-hidden="true"></div>
                        <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.06] [mask-image:linear-gradient(to_bottom,black,transparent_85%)]" aria-hidden="true"></div>

                        {{-- The target: three rings, the dashed one turning --}}
                        <div class="pointer-events-none absolute -right-16 -top-16 -z-10 h-64 w-64" aria-hidden="true">
                            <span class="absolute inset-0 rounded-full border border-white/10"></span>
                            <span class="absolute inset-8 animate-[spin_40s_linear_infinite] rounded-full border-2 border-dashed border-gold-300/30"></span>
                            <span class="absolute inset-16 rounded-full border border-white/15"></span>
                            <span class="absolute inset-[6.5rem] rounded-full bg-gold-300/80 shadow-[0_0_30px_8px_rgba(242,205,107,0.35)]"></span>
                        </div>

                        {{-- The word, outlined, as a watermark --}}
                        <span class="pointer-events-none absolute -bottom-6 right-4 -z-10 select-none font-display text-[100px] font-extrabold uppercase leading-none tracking-tight text-transparent sm:text-[140px]"
                              style="-webkit-text-stroke: 1.5px rgb(255 255 255 / 0.07)" aria-hidden="true">{{ $statement[0] }}</span>

                        <div class="relative z-10">
                            <span class="inline-flex items-center gap-2 rounded-full border border-gold-300/30 bg-gold-300/10 py-1.5 pl-1.5 pr-4 text-[12px] font-bold uppercase tracking-[0.18em] text-gold-300 backdrop-blur-md">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-gold-300 text-navy-900">
                                    <x-ui-icon :name="$icon" class="h-4 w-4" />
                                </span>
                                {{ $statement[0] }}
                            </span>
                        </div>

                        <div class="relative z-10 mt-10 flex gap-5">
                            <span class="w-1 shrink-0 rounded-full bg-gradient-to-b from-gold-300 via-gold-400 to-transparent" aria-hidden="true"></span>

                            @if ($statement === $mission)
                                {{-- The mission, item by item --}}
                                <ol class="space-y-3.5">
                                    @foreach (collect(preg_split('/;\s*/u', rtrim($statement[1], '।. ')))->map(fn ($p) => trim($p))->filter() as $point)
                                        <li class="flex items-start gap-3.5">
                                            <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gold-300 font-numeric text-[12px] font-bold text-navy-900">{{ $num($loop->iteration) }}</span>
                                            <span class="font-display text-[16px] font-semibold leading-[1.5] !text-white sm:text-[17px]">{{ \Illuminate\Support\Str::ucfirst($point) }}</span>
                                        </li>
                                    @endforeach
                                </ol>
                            @else
                                <p class="font-display font-semibold leading-[1.45] tracking-[-0.01em] !text-white {{ $size }}">{{ $statement[1] }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach

                {{-- Anything else the document adds later, as a plain card --}}
                @foreach ($others as $item)
                    <article class="reveal rounded-[2rem] border border-ink-100 bg-white p-8 lg:col-span-6">
                        <h3 class="font-display text-[22px] font-bold text-ink-950">{{ $item[0] }}</h3>
                        <p class="mt-4 text-[15px] leading-[1.8] text-ink-700">{{ $item[1] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- Innovation Process Framework ----------------
         Right after vision and mission: how an idea is taken from ideation to
         market, stage by stage with its TRL, in interlocking chevrons. --}}
    <section id="process" class="scroll-mt-28 bg-white py-20 sm:py-24">
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

    {{-- ---------------- Innovation areas ----------------

         The departmental areas, laid out the way the Services page lays out its
         categories: the heading and the numbered focus topics flow across a
         four-column grid while the image (or a video) holds the top-right
         corner. --}}
    <section id="areas" class="scroll-mt-28 bg-white py-20 sm:py-28">
        <div class="container-rich">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <x-section-heading :eyebrow="__('site.innovation.catalogue_eyebrow')" :title="__('site.innovation.catalogue_heading')" size="lg" />

                <a href="{{ route('ideas.create') }}" class="btn-primary reveal shrink-0">
                    {{ __('site.actions.submit_idea') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                </a>
            </div>

            <div class="mt-14 space-y-20 sm:space-y-28">
                @foreach ($areas as $area)
                    <section id="area-{{ $area->slug }}" class="scroll-mt-28 border-t border-ink-100 pt-12 first:border-0 first:pt-0">
                        {{-- Text on the left, the picture on the right at one fixed shape, so
                             every area's picture is the same size whatever sits beside it. --}}
                        <div class="grid items-center gap-x-12 gap-y-8 lg:grid-cols-2">
                            <div class="reveal">
                                <p class="text-[11.5px] font-bold uppercase tracking-[0.18em] text-brand-600">
                                    <span class="text-brand-300">//</span> {{ __('site.innovation.focus_eyebrow') }}
                                </p>

                                <h2 class="mt-4 font-display text-[28px] font-bold leading-[1.1] tracking-tight text-ink-950 sm:text-[38px]">
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

                                <div class="mt-7 flex flex-wrap items-center gap-4">
                                    <a href="{{ route('innovation.area', $area) }}" class="btn-ghost">
                                        {{ __('site.innovation.area_detail') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                                    </a>
                                    <a href="{{ route('ideas.create') }}" class="text-[13.5px] font-semibold text-brand-700 transition hover:text-brand-600">
                                        {{ __('site.actions.submit_idea') }}
                                    </a>
                                </div>
                            </div>

                            {{-- The picture (or a video in its place), always 16:10 --}}
                            <div class="reveal">
                                <div class="relative aspect-[16/10] overflow-hidden rounded-[1.5rem] bg-brand-50">
                                    @if ($area->video)
                                        {{-- Muted and looped. With reduced motion it stays on its first frame. --}}
                                        <video class="absolute inset-0 h-full w-full object-cover"
                                               autoplay muted loop playsinline preload="metadata"
                                               @if ($area->image) poster="{{ Storage::url($area->image) }}" @endif
                                               x-data x-init="if (matchMedia('(prefers-reduced-motion: reduce)').matches) { $el.removeAttribute('autoplay'); $el.pause() }"
                                               aria-label="{{ __('site.projects.video_title', ['title' => $area->name]) }}">
                                            <source src="{{ Storage::url($area->video) }}"
                                                    type="{{ str_ends_with(strtolower($area->video), '.webm') ? 'video/webm' : 'video/mp4' }}">
                                        </video>
                                    @elseif ($area->image)
                                        <img src="{{ Storage::url($area->image) }}" alt="{{ $area->name }}" loading="lazy"
                                             class="absolute inset-0 h-full w-full object-cover">
                                    @else
                                        {{-- No photograph yet: the department's own drawn poster. --}}
                                        <x-service-poster :sector="$area->department" :seed="$area->slug" class="absolute inset-0 h-full w-full object-cover" />
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- The innovations, as compact cards in a row under the area --}}
                        @if ($area->innovations->isNotEmpty())
                            <div class="mt-8 grid gap-5 md:grid-cols-2">
                                @foreach ($area->innovations as $j => $innovation)
                                    <x-cards.innovation-mini :innovation="$innovation" :slug="$innovationSlugs[$innovation->id] ?? null" :index="$j" />
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ---------------- Funding & Sustainability ----------------
         Straight after the areas: where the money for an innovation comes
         from, each source as its own card, and how revenue is shared. --}}
    @php
        $fundingParts = collect($doc['funding'])->map(function ($paragraph) {
            $split = explode(':', $paragraph, 2);

            return count($split) === 2
                ? ['heading' => trim($split[0]), 'body' => trim($split[1])]
                : ['heading' => null, 'body' => trim($paragraph)];
        });
        $sources = $fundingParts->first();
        $sourceItems = $sources ? collect(explode(',', rtrim($sources['body'], '।. ')))->map(fn ($x) => trim($x))->filter()->values() : collect();
        $fundingNotes = $fundingParts->slice(1)->values();
        $sourceIcons = ['building', 'academic', 'cpu', 'rocket', 'briefcase', 'handshake', 'globe'];
    @endphp

    <section id="funding" class="relative isolate scroll-mt-28 overflow-hidden bg-ink-50 py-20 sm:py-24">
        <div class="pointer-events-none absolute inset-0 -z-10 text-brand-700 grid-overlay opacity-20 [mask-image:radial-gradient(ellipse_at_top,black,transparent_70%)]" aria-hidden="true"></div>

        <div class="container-rich">
            <x-section-heading :eyebrow="__('site.innovation.funding_sources')" :title="$title('funding')" />

            <div class="mt-12 grid gap-5 lg:grid-cols-[1.6fr_1fr]">
                {{-- The sources --}}
                <ul class="grid gap-3 sm:grid-cols-2">
                    @foreach ($sourceItems as $i => $source)
                        <li class="reveal group flex items-center gap-4 rounded-2xl border border-ink-100 bg-white p-4 transition duration-300 hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-[0_18px_36px_-26px_rgba(7,20,38,0.45)]"
                            style="{{ $delay($i, 50) }}">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-brand-100 transition duration-300 group-hover:bg-brand-600 group-hover:text-white">
                                <x-ui-icon :name="$sourceIcons[$i] ?? 'briefcase'" class="h-5 w-5" />
                            </span>
                            <span class="font-display text-[15.5px] font-bold leading-snug text-ink-900">{{ $source }}</span>
                        </li>
                    @endforeach
                </ul>

                {{-- How revenue is shared, and any other note the document adds --}}
                <div class="space-y-5">
                    @foreach ($fundingNotes as $note)
                        <div class="reveal relative isolate overflow-hidden rounded-[1.75rem] bg-navy-800 p-7 text-white sm:p-8">
                            <div class="pointer-events-none absolute -right-12 -top-12 -z-10 h-40 w-40 rounded-full bg-gold-400/20 blur-3xl" aria-hidden="true"></div>
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-gold-300 text-navy-900">
                                <x-ui-icon name="chart" class="h-5 w-5" />
                            </span>
                            @if ($note['heading'])
                                <p class="mt-5 font-display text-[19px] font-bold !text-white">{{ $note['heading'] }}</p>
                            @endif
                            @php $shares = collect(explode('+', $note['body']))->map(fn ($x) => trim($x))->filter(); @endphp
                            @if ($shares->count() > 1)
                                <ul class="mt-4 space-y-2">
                                    @foreach ($shares as $share)
                                        <li class="flex items-start gap-2.5 text-[14.5px] leading-relaxed text-white/85">
                                            <x-ui-icon name="check" class="mt-1 h-4 w-4 shrink-0 text-gold-300" stroke="2.4" />
                                            {{ rtrim($share, '।. ') }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="mt-3 text-[14.5px] leading-relaxed text-white/80">{{ $note['body'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
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
