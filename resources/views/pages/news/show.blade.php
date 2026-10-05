<x-layouts.app :title="$post->title" :description="$post->excerpt">

    @php
        // YouTube / Vimeo links become an embeddable URL; anything else is shown as a link.
        $embed = null;
        if ($post->video_url && preg_match('~(?:youtube\.com/(?:watch\?v=|shorts/|embed/)|youtu\.be/)([\w-]{11})~', $post->video_url, $m)) {
            $embed = 'https://www.youtube-nocookie.com/embed/'.$m[1];
        } elseif ($post->video_url && preg_match('~vimeo\.com/(\d+)~', $post->video_url, $m)) {
            $embed = 'https://player.vimeo.com/video/'.$m[1];
        }

        // The source: its name if given, otherwise the site the link points at.
        $sourceName = $post->reference_name
            ?: ($post->external_url ? preg_replace('/^www\./', '', (string) parse_url($post->external_url, PHP_URL_HOST)) : null);
    @endphp

    <x-page-hero
        :eyebrow="$post->category ?: ucfirst($post->type)"
        :title="$post->title"
        :lead="$post->excerpt"
        :image="$post->image"
        :breadcrumbs="[__('site.nav.news') => route('news.index'), Str::limit($post->title, 40) => null]" />

    <section class="py-16 bg-white sm:py-20">
        <div class="container-rich grid gap-12 lg:grid-cols-[1.4fr_0.6fr] lg:gap-16">

            <article>
                <x-media-frame :src="$post->image" :alt="$post->title" :seed="$post->slug"
                               :icon="$post->type === 'event' ? 'calendar' : 'document'"
                               ratio="aspect-[16/8]" class="reveal rounded-3xl" />

                <div class="reveal mt-8 flex flex-wrap items-center gap-x-6 gap-y-2 border-b hairline pb-6 text-[13.5px] muted">
                    @if ($post->author)
                        <span class="flex items-center gap-2"><x-ui-icon name="users" class="h-4 w-4" />{{ $post->author }}</span>
                    @endif
                    @if ($post->published_at)
                        <span class="flex items-center gap-2"><x-ui-icon name="calendar" class="h-4 w-4" />{{ $post->published_at->format('j F Y') }}</span>
                    @endif
                    <span class="flex items-center gap-2"><x-ui-icon name="clock" class="h-4 w-4" />{{ __('site.news.reading_time', ['minutes' => $post->reading_time]) }}</span>
                </div>

                <div class="prose-rich reveal mt-8 text-[16.5px] muted">
                    @foreach (preg_split("/\r\n|\n|\r/", (string) $post->body) as $paragraph)
                        @if (trim($paragraph) !== '')
                            <p>{{ $paragraph }}</p>
                        @endif
                    @endforeach
                </div>

                {{-- Video --}}
                @if ($embed)
                    <div class="reveal mt-10 aspect-video overflow-hidden rounded-3xl bg-ink-950">
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

                <div class="mt-12 flex items-center justify-between gap-4 border-t hairline pt-8">
                    <a href="{{ route('news.index') }}" class="btn-ghost">
                        <x-ui-icon name="arrow-left" class="h-4 w-4" /> {{ __('site.news.back') }}
                    </a>
                </div>
            </article>

            <aside class="space-y-6 lg:sticky lg:top-32 lg:self-start">
                @if ($post->type === 'event')
                    <div class="reveal relative overflow-hidden rounded-3xl bg-brand-700 p-7 text-white">
                        <div class="pointer-events-none absolute inset-0 text-white grid-overlay opacity-[0.12]"></div>
                        <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-white/70">{{ __('site.news.event_details') }}</p>

                        @if ($post->event_at)
                            <p class="mt-4 font-display text-2xl font-bold">{{ $post->event_at->format('j F Y') }}</p>
                            <p class="mt-1 text-[14px] text-white/80">{{ $post->event_at->format('g:i A') }}</p>
                        @endif

                        @if ($post->location)
                            <p class="mt-5 flex items-start gap-2.5 text-[14px] text-white/90">
                                <x-ui-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0" />{{ $post->location }}
                            </p>
                        @endif

                        <a href="{{ route('contact') }}"
                           class="mt-7 inline-flex items-center gap-2 rounded-full bg-white px-5 py-2.5 text-[13.5px] font-semibold text-brand-600
                                  transition hover:bg-brand-50 hover:text-white">
                            {{ __('site.news.register_interest') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    </div>
                @endif

                <div class="card reveal">
                    <h3 class="font-display text-lg font-bold">{{ __('site.news.cta_title') }}</h3>
                    <p class="mt-3 text-[14.5px] leading-relaxed muted">
                        {{ __('site.news.cta_body') }}
                    </p>
                    <a href="{{ route('contact') }}" class="btn-primary mt-6 w-full">
                        {{ __('site.news.cta_button') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>
            </aside>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="border-t hairline py-20 bg-ink-50">
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
