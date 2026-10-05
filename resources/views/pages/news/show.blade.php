<x-layouts.app :title="$post->title" :description="$post->excerpt">

    @php
        $isEvent = $post->type === 'event';

        // YouTube / Vimeo links become an embeddable URL; anything else is shown as a link.
        $embed = null;
        if ($post->video_url && preg_match('~(?:youtube\.com/(?:watch\?v=|shorts/|embed/)|youtu\.be/)([\w-]{11})~', $post->video_url, $m)) {
            $embed = 'https://www.youtube-nocookie.com/embed/'.$m[1];
        } elseif ($post->video_url && preg_match('~vimeo\.com/(\d+)~', $post->video_url, $m)) {
            $embed = 'https://player.vimeo.com/video/'.$m[1];
        }

        // The same video as a silent background for the hero: muted, looped,
        // no controls. The inline player below keeps the sound.
        $backgroundEmbed = null;
        if ($embed && str_contains($embed, 'youtube')) {
            $id = basename(parse_url($embed, PHP_URL_PATH));
            $backgroundEmbed = $embed.'?'.http_build_query(['autoplay' => 1, 'mute' => 1, 'loop' => 1, 'playlist' => $id, 'controls' => 0, 'modestbranding' => 1, 'playsinline' => 1, 'rel' => 0]);
        } elseif ($embed) {
            $backgroundEmbed = $embed.'?'.http_build_query(['background' => 1, 'autoplay' => 1, 'muted' => 1, 'loop' => 1]);
        }

        // The source: its name if given, otherwise the site the link points at.
        $sourceName = $post->reference_name
            ?: ($post->external_url ? preg_replace('/^www\./', '', (string) parse_url($post->external_url, PHP_URL_HOST)) : null);

        $cover = $post->image ? Storage::url($post->image) : null;

        // The rulebook, and how big a download it is.
        $rulebookUrl = $post->rulebook ? Storage::url($post->rulebook) : null;
        $rulebookSize = null;
        if ($post->rulebook && Storage::disk('public')->exists($post->rulebook)) {
            $bytes = Storage::disk('public')->size($post->rulebook);
            $rulebookSize = $bytes < 1048576 ? max(1, round($bytes / 1024)).' KB' : number_format($bytes / 1048576, 1).' MB';
        }
        $num = fn ($value) => \App\Support\Numerals::localize((string) $value);

        // An event can go straight into the reader's calendar (two hours, by default).
        $calendarUrl = $isEvent && $post->event_at
            ? 'https://calendar.google.com/calendar/render?'.http_build_query([
                'action' => 'TEMPLATE',
                'text' => $post->title,
                'dates' => $post->event_at->copy()->utc()->format('Ymd\THis\Z').'/'.$post->event_at->copy()->utc()->addHours(2)->format('Ymd\THis\Z'),
                'location' => (string) $post->location,
                'details' => route('news.show', $post),
            ])
            : null;

        // Sharing: the page's own address, nothing tracked.
        $pageUrl = route('news.show', $post);
        $share = [
            ['facebook', 'Facebook', 'https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($pageUrl)],
            ['linkedin', 'LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url='.rawurlencode($pageUrl)],
            ['x-social', 'X', 'https://twitter.com/intent/tweet?url='.rawurlencode($pageUrl).'&text='.rawurlencode($post->title)],
            ['whatsapp', 'WhatsApp', 'https://wa.me/?text='.rawurlencode($post->title.' '.$pageUrl)],
        ];

        $paragraphs = collect(preg_split("/\r\n|\n|\r/", (string) $post->body))->map(fn ($p) => trim($p))->filter()->values();
    @endphp

    {{-- Reading progress: a thin bar along the very top that fills as the article is read. --}}
    <div class="pointer-events-none fixed inset-x-0 top-0 z-[60] h-[3px]" aria-hidden="true"
         x-data="{ p: 0 }"
         @scroll.window.passive="const a = document.getElementById('article'); if (a) { const r = a.getBoundingClientRect(); p = Math.min(Math.max(-r.top / Math.max(r.height - innerHeight, 1), 0), 1) }">
        <div class="h-full origin-left bg-gradient-to-r from-brand-500 to-gold-400" :style="`transform: scaleX(${p})`" style="transform: scaleX(0)"></div>
    </div>

    {{-- ---------------- Hero ----------------
         Full-bleed: the hero video behind the title when there is one, the
         cover photograph otherwise, under a navy wash. The facts a reader
         needs first (when, where, how long) sit in glass pills. --}}
    <section class="relative isolate overflow-hidden bg-navy-800 text-white">
        @if ($post->hero_video)
            <video class="pointer-events-none absolute inset-0 -z-20 h-full w-full object-cover"
                   autoplay muted loop playsinline preload="auto" @if ($cover) poster="{{ $cover }}" @endif
                   x-data x-init="if (matchMedia('(prefers-reduced-motion: reduce)').matches) { $el.removeAttribute('autoplay'); $el.pause() }"
                   aria-hidden="true" tabindex="-1">
                <source src="{{ Storage::url($post->hero_video) }}" type="{{ str_ends_with(strtolower($post->hero_video), '.webm') ? 'video/webm' : 'video/mp4' }}">
            </video>
        @elseif ($backgroundEmbed)
            {{-- The post's YouTube/Vimeo video, silent behind the title. The cover
                 shows underneath until it starts, and stays for reduced motion. --}}
            @if ($cover)
                <img src="{{ $cover }}" alt="" aria-hidden="true" fetchpriority="high"
                     class="absolute inset-0 -z-30 h-full w-full object-cover">
            @endif
            <div class="pointer-events-none absolute inset-0 -z-20 overflow-hidden [container-type:size] motion-reduce:hidden" aria-hidden="true">
                <iframe src="{{ $backgroundEmbed }}" title="" tabindex="-1" loading="eager"
                        allow="autoplay; encrypted-media; picture-in-picture"
                        class="absolute left-1/2 top-1/2 h-[max(100cqh,56.25cqw)] w-[max(100cqw,177.78cqh)] -translate-x-1/2 -translate-y-1/2 border-0"></iframe>
            </div>
        @elseif ($cover)
            <img src="{{ $cover }}" alt="" aria-hidden="true" fetchpriority="high"
                 class="absolute inset-0 -z-20 h-full w-full scale-105 object-cover">
        @endif
        <div class="pointer-events-none absolute inset-0 -z-10 bg-navy-900/70" aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t from-navy-900 via-navy-900/50 to-navy-900/20" aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-1 bg-gradient-to-r from-brand-500 to-gold-400" aria-hidden="true"></div>

        <div class="container-rich flex min-h-[26rem] flex-col justify-end pb-14 pt-10 sm:min-h-[32rem] sm:pb-16">
            <nav class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[13px] text-white/60" aria-label="Breadcrumb">
                <a href="{{ route('home') }}" class="transition hover:text-white">{{ __('site.nav.home') }}</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('news.index') }}" class="transition hover:text-white">{{ __('site.nav.news') }}</a>
            </nav>

            <div class="mt-auto pt-16">
                <div class="reveal flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-gold-300 px-3 py-1 text-[11.5px] font-bold uppercase tracking-[0.12em] text-navy-900">
                        <x-ui-icon :name="$isEvent ? 'calendar' : 'document'" class="h-3.5 w-3.5" />
                        {{ $isEvent ? __('site.news.filter_events') : __('site.news.filter_news') }}
                    </span>
                    @if ($post->category)
                        <span class="eyebrow-invert">{{ $post->category }}</span>
                    @endif
                </div>

                <h1 class="reveal mt-6 max-w-4xl font-display text-[32px] font-bold leading-[1.08] tracking-[-0.02em] !text-white sm:text-[50px]">
                    {{ $post->title }}
                </h1>

                @if ($post->excerpt)
                    <p class="reveal mt-5 max-w-2xl text-[16.5px] leading-[1.75] text-white/75">{{ $post->excerpt }}</p>
                @endif

                <div class="reveal mt-8 flex flex-wrap gap-2.5 text-[13.5px]">
                    @if ($isEvent && $post->event_at)
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 backdrop-blur-md">
                            <x-ui-icon name="calendar" class="h-4 w-4 text-gold-300" />
                            {{ $num($post->event_at->translatedFormat('j F Y')) }} · {{ $num($post->event_at->format('g:i A')) }}
                        </span>
                    @elseif ($post->published_at)
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 backdrop-blur-md">
                            <x-ui-icon name="calendar" class="h-4 w-4 text-gold-300" />
                            {{ $num($post->published_at->translatedFormat('j F Y')) }}
                        </span>
                    @endif
                    @if ($isEvent && $post->location)
                        <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 backdrop-blur-md">
                            <x-ui-icon name="map-pin" class="h-4 w-4 text-gold-300" />
                            {{ $post->location }}
                        </span>
                    @endif
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-4 py-2 backdrop-blur-md">
                        <x-ui-icon name="clock" class="h-4 w-4 text-gold-300" />
                        {{ __('site.news.reading_time', ['minutes' => $num($post->reading_time)]) }}
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- ---------------- Article and the event card ---------------- --}}
    <section class="bg-white py-16 sm:py-20">
        <div class="container-rich grid gap-12 lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-16">

            <article id="article" class="min-w-0">
                {{-- The thumbnail, at the head of the article --}}
                @if ($post->image)
                    <x-media-frame :src="$post->image" :alt="$post->title" :seed="$post->slug"
                                   :icon="$isEvent ? 'calendar' : 'document'"
                                   ratio="aspect-[16/9]" class="reveal mb-8 rounded-[1.75rem] shadow-[0_30px_60px_-40px_rgba(7,20,38,0.5)]" />
                @endif

                @if ($post->author)
                    <p class="reveal flex items-center gap-3 text-[13.5px] text-ink-500">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-brand-50 text-brand-600 ring-1 ring-brand-100">
                            <x-ui-icon name="users" class="h-4 w-4" />
                        </span>
                        <span><span class="font-semibold text-ink-900">{{ $post->author }}</span>
                            @if ($post->published_at) · {{ $num($post->published_at->translatedFormat('j F Y')) }} @endif</span>
                    </p>
                @endif

                {{-- The opening paragraph reads as a lead; the rest as body text --}}
                <div class="reveal mt-8 space-y-6">
                    @foreach ($paragraphs as $i => $paragraph)
                        <p @class([
                            'font-display text-[20px] font-medium leading-[1.65] text-ink-900 sm:text-[22px]' => $i === 0,
                            'text-[16.5px] leading-[1.9] text-ink-700' => $i > 0,
                        ])>{{ $paragraph }}</p>
                    @endforeach
                </div>

                {{-- Video --}}
                @if ($embed)
                    <div class="reveal mt-10 aspect-video overflow-hidden rounded-3xl bg-ink-950 shadow-[0_30px_60px_-40px_rgba(7,20,38,0.6)]">
                        <iframe src="{{ $embed }}" title="{{ __('site.projects.video_title', ['title' => $post->title]) }}" class="h-full w-full" loading="lazy"
                                allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                @elseif ($post->video_url)
                    <a href="{{ $post->video_url }}" target="_blank" rel="noopener noreferrer" class="btn-ghost reveal mt-10">
                        <x-ui-icon name="play" class="h-4 w-4" /> {{ __('site.projects.watch_video') }}
                    </a>
                @endif

                {{-- Where the item was first published --}}
                @if ($sourceName)
                    <div class="reveal mt-10 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-ink-100 bg-ink-50 px-5 py-4">
                        <p class="flex items-center gap-3 text-[14px] text-ink-600">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-white text-brand-600 ring-1 ring-ink-100">
                                <x-ui-icon name="link" class="h-4 w-4" />
                            </span>
                            <span>
                                <span class="block text-[11px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('site.news.source') }}</span>
                                <span class="font-semibold text-ink-900">{{ $sourceName }}</span>
                            </span>
                        </p>
                        @if ($post->external_url)
                            <a href="{{ $post->external_url }}" target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center gap-1.5 text-[13.5px] font-semibold text-brand-700 hover:underline">
                                {{ __('site.news.read_original') }} <x-ui-icon name="external" class="h-3.5 w-3.5" />
                            </a>
                        @endif
                    </div>
                @endif

                <div class="mt-12 border-t hairline pt-8">
                    <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 text-[14px] font-semibold text-ink-700 transition hover:text-brand-700">
                        <x-ui-icon name="arrow-left" class="h-4 w-4" /> {{ __('site.news.back') }}
                    </a>
                </div>
            </article>

            {{-- The side card: the date as a tile, the place, the facts, sharing,
                 and one highlighted action: Register & Submit Idea. Sticky while
                 the article scrolls. --}}
            <aside class="space-y-5 lg:sticky lg:top-32 lg:self-start">
                @if ($rulebookUrl)
                    {{-- The rulebook, highlighted: read the rules before registering --}}
                    <div class="reveal relative isolate overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-gold-200 via-gold-300 to-gold-400 p-6 text-navy-900 shadow-[0_30px_60px_-36px_rgba(195,136,26,0.7)]">
                        <div class="pointer-events-none absolute -right-8 -top-8 -z-10 h-32 w-32 rounded-full bg-white/40 blur-2xl" aria-hidden="true"></div>
                        <div class="flex items-center gap-4">
                            <span class="flex h-14 w-12 shrink-0 flex-col items-center justify-center rounded-xl bg-white text-[10px] font-bold uppercase tracking-wider text-rose-600 shadow-[0_10px_24px_-14px_rgba(7,20,38,0.6)]">
                                <x-ui-icon name="document" class="h-5 w-5 text-navy-800" />
                                PDF
                            </span>
                            <div class="min-w-0">
                                <p class="font-display text-[18px] font-bold leading-tight">{{ __('site.news.rulebook') }}</p>
                                <p class="mt-0.5 text-[13px] text-navy-900/70">{{ __('site.news.rulebook_note') }}</p>
                            </div>
                        </div>
                        <a href="{{ $rulebookUrl }}" download target="_blank" rel="noopener"
                           class="group mt-5 flex w-full items-center justify-center gap-2 rounded-full bg-navy-900 px-5 py-3.5 text-[14.5px] font-bold text-white transition hover:bg-navy-800">
                            <x-ui-icon name="arrow-right" class="h-4 w-4 rotate-90 transition-transform duration-300 group-hover:translate-y-0.5" />
                            {{ __('site.news.rulebook_download') }}
                            @if ($rulebookSize)
                                <span class="text-[12px] font-semibold text-white/60">· {{ $rulebookSize }}</span>
                            @endif
                        </a>
                    </div>
                @endif

                <div class="reveal overflow-hidden rounded-[1.75rem] border border-ink-100 bg-white shadow-[0_30px_70px_-50px_rgba(7,20,38,0.45)]">

                    @if ($isEvent && $post->event_at)
                        {{-- Date tile --}}
                        <div class="relative isolate flex items-center gap-5 overflow-hidden bg-navy-800 p-6 text-white">
                            <div class="pointer-events-none absolute -right-10 -top-10 -z-10 h-32 w-32 rounded-full bg-brand-500/30 blur-2xl" aria-hidden="true"></div>
                            <div class="flex h-20 w-20 shrink-0 flex-col items-center justify-center rounded-2xl bg-white text-navy-900">
                                <span class="text-[11px] font-bold uppercase tracking-[0.14em] text-brand-600">{{ $post->event_at->translatedFormat('M') }}</span>
                                <span class="font-display text-[32px] font-bold leading-none">{{ $num($post->event_at->format('j')) }}</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-gold-300">{{ __('site.news.event_details') }}</p>
                                <p class="mt-1.5 font-display text-[17px] font-bold leading-snug !text-white">{{ $post->event_at->translatedFormat('l') }}</p>
                                <p class="text-[14px] text-white/70">{{ $num($post->event_at->format('g:i A')) }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="p-6">
                        <dl class="space-y-4 text-[14px]">
                            @if ($isEvent && $post->location)
                                <div class="flex items-start gap-3">
                                    <x-ui-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                                    <div>
                                        <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">{{ __('site.news.venue') }}</dt>
                                        <dd class="mt-0.5 font-medium text-ink-900">{{ $post->location }}</dd>
                                    </div>
                                </div>
                            @endif
                            @if ($post->category)
                                <div class="flex items-start gap-3">
                                    <x-ui-icon name="grid" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                                    <div>
                                        <dt class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">{{ __('site.news.category') }}</dt>
                                        <dd class="mt-0.5 font-medium text-ink-900">{{ $post->category }}</dd>
                                    </div>
                                </div>
                            @endif
                        </dl>

                        @if ($calendarUrl)
                            <a href="{{ $calendarUrl }}" target="_blank" rel="noopener noreferrer"
                               class="mt-5 inline-flex items-center gap-2 text-[13.5px] font-semibold text-brand-700 hover:underline">
                                <x-ui-icon name="plus" class="h-4 w-4" /> {{ __('site.news.add_to_calendar') }}
                            </a>
                        @endif

                        {{-- The one highlighted action --}}
                        <div class="mt-6 border-t hairline pt-6">
                            <a href="{{ route('ideas.create') }}" class="btn-lead group w-full" data-magnetic>
                                <span class="relative">{{ __('site.news.register_and_submit') }}</span>
                                <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                            </a>
                        </div>

                        {{-- Share --}}
                        <div class="mt-6 flex items-center justify-between gap-3 border-t hairline pt-5"
                             x-data="{ copied: false }">
                            <span class="text-[11px] font-semibold uppercase tracking-[0.14em] text-ink-400">{{ __('site.news.share') }}</span>
                            <div class="flex items-center gap-1.5">
                                @foreach ($share as [$icon, $label, $url])
                                    <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $label }}"
                                       class="flex h-9 w-9 items-center justify-center rounded-full bg-ink-50 text-ink-600 ring-1 ring-ink-100 transition hover:bg-brand-600 hover:text-white hover:ring-brand-600">
                                        <x-ui-icon :name="$icon" class="h-4 w-4" />
                                    </a>
                                @endforeach
                                <button type="button" aria-label="{{ __('site.news.copy_link') }}"
                                        @click="navigator.clipboard?.writeText(@js($pageUrl)).then(() => { copied = true; setTimeout(() => copied = false, 1800) })"
                                        class="flex h-9 w-9 items-center justify-center rounded-full bg-ink-50 text-ink-600 ring-1 ring-ink-100 transition hover:bg-brand-600 hover:text-white hover:ring-brand-600"
                                        :class="copied && '!bg-brand-600 !text-white'">
                                    <x-ui-icon name="link" class="h-4 w-4" x-show="! copied" />
                                    <x-ui-icon name="check" class="h-4 w-4" x-show="copied" x-cloak />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="border-t hairline bg-ink-50 py-20">
            <div class="container-rich">
                <x-section-heading :eyebrow="__('site.news.related_eyebrow')" :title="__('site.news.related_title')" />
                <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $i => $item)
                        <x-cards.post-card :post="$item" :index="$i" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
