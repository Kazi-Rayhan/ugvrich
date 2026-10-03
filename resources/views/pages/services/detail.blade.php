<x-layouts.app :title="$service->name" :description="$service->description">

    @php
        /*
         | One service, on a page of its own.
         |
         | Three tiers meet here: the sector that runs it, the main service it
         | belongs to, and the service itself. The page says all three in the
         | first screen, because a visitor arriving from a search needs to know
         | which desk this is before they read what it does.
         |
         | Everything below the overview is drawn from what the record holds —
         | a service with nothing written yet still renders as a clean page
         | rather than a run of empty headings.
         */
        $label = 'text-[10.5px] font-semibold uppercase tracking-[0.18em] text-ink-400';
        $highlights = collect($service->highlights ?: []);

        // An uploaded clip fills the hero behind the text, like a cover image.
        $video = $service->video ? Storage::url($service->video) : null;
        $videoType = str_ends_with(strtolower((string) $service->video), '.webm') ? 'video/webm' : 'video/mp4';
        $poster = $service->image ? Storage::url($service->image) : null;
    @endphp

    {{-- ---------------- Hero ---------------- --}}
    <section @class([
        'relative isolate overflow-hidden bg-navy-700 text-white',
        'py-14 sm:py-20' => ! $video,
        'flex min-h-[340px] items-end py-12 sm:min-h-[400px] sm:py-16' => $video,
    ])>
        @if ($video)
            {{-- Muted, looped and decorative. With reduced motion it stays on its first frame. --}}
            <video class="pointer-events-none absolute inset-0 -z-20 h-full w-full object-cover"
                   autoplay muted loop playsinline preload="auto"
                   @if ($poster) poster="{{ $poster }}" @endif
                   x-data x-init="if (matchMedia('(prefers-reduced-motion: reduce)').matches) { $el.removeAttribute('autoplay'); $el.pause() }"
                   aria-hidden="true" tabindex="-1">
                <source src="{{ $video }}" type="{{ $videoType }}">
            </video>
            {{-- Darkened from the bottom-left, where the text sits, so it stays readable on any footage. --}}
            <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-t from-navy-900/95 via-navy-800/70 to-navy-700/40" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-r from-navy-900/70 to-transparent" aria-hidden="true"></div>
        @else
            <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.12]" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -right-32 -top-24 -z-10 h-80 w-80 rounded-full bg-brand-500/25 blur-[100px]" aria-hidden="true"></div>
        @endif

        <div class="container-rich w-full">
            {{-- Where this sits: main service, then this one --}}
            <nav class="flex flex-wrap items-center gap-x-2 gap-y-1 text-[13px] text-white/60" aria-label="Breadcrumb">
                <a href="{{ route('services.index') }}" class="transition hover:text-white">{{ __('site.nav.services') }}</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('services.show', $category) }}" class="transition hover:text-white">{{ $category->name }}</a>
            </nav>

            <div class="mt-6 grid gap-10 lg:grid-cols-[1.4fr_0.6fr] lg:items-end">
                <div>
                    @if ($category->sector_name)
                        <span class="eyebrow-invert">
                            <x-ui-icon name="building" class="h-3.5 w-3.5" />
                            {{ $category->sector_name }}
                        </span>
                    @endif

                    <h1 class="mt-5 font-display text-[30px] font-bold leading-[1.1] !text-white sm:text-[46px]">{{ $service->name }}</h1>

                    @if ($service->description)
                        <p @class(['mt-5 max-w-2xl text-[15.5px] leading-[1.85]', 'text-white/75' => ! $video, 'text-white/85' => $video])>{{ $service->description }}</p>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3 lg:justify-end">
                    <a href="{{ route('consultancy.create', ['area' => $category->slug, 'service' => $service->slug]) }}" class="btn-primary">
                        {{ __('site.actions.request_consultancy') }}
                        <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ---------------- Overview and what is included ---------------- --}}
    @if ($service->body || $highlights->isNotEmpty())
        <section class="bg-white py-16 sm:py-20">
            <div class="container-rich grid gap-x-12 gap-y-10 lg:grid-cols-[1.3fr_0.7fr]">
                <div class="reveal">
                    @if ($service->body)
                        <span class="mb-5 block h-1 w-12 rounded-full bg-brand-600" aria-hidden="true"></span>
                        <h2 class="font-display text-[24px] font-bold leading-tight text-ink-950 sm:text-[30px]">{{ __('site.services.overview') }}</h2>

                        <div class="mt-5 space-y-5 text-[15.5px] leading-[1.9] text-ink-700">
                            @foreach (preg_split('/\R{2,}/u', trim($service->body)) as $paragraph)
                                <p>{{ $paragraph }}</p>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($highlights->isNotEmpty())
                    <aside class="reveal self-start rounded-[1.75rem] bg-ink-50/80 p-7 sm:p-8">
                        <p class="{{ $label }}">{{ __('site.services.included') }}</p>

                        <ul class="mt-4 space-y-3">
                            @foreach ($highlights as $highlight)
                                <li class="flex items-start gap-3 text-[14.5px] leading-[1.7] text-ink-700">
                                    <x-ui-icon name="check" class="mt-1 h-4 w-4 shrink-0 text-brand-600" />
                                    {{ $highlight }}
                                </li>
                            @endforeach
                        </ul>
                    </aside>
                @endif
            </div>
        </section>
    @endif

    {{-- ---------------- The rest of this main service ---------------- --}}
    @if ($siblings->isNotEmpty())
        <section class="border-t border-ink-100 bg-ink-50 py-16 sm:py-20">
            <div class="container-rich">
                <span class="reveal mb-5 block h-1 w-12 rounded-full bg-brand-600" aria-hidden="true"></span>
                <div class="reveal flex flex-wrap items-end justify-between gap-4">
                    <h2 class="font-display text-[24px] font-bold leading-tight text-ink-950 sm:text-[30px]">
                        {{ __('site.services.more_in', ['category' => $category->name]) }}
                    </h2>
                    <a href="{{ route('services.show', $category) }}" class="group inline-flex items-center gap-1.5 text-[13.5px] font-semibold text-brand-700">
                        {{ __('site.actions.view_all') }}
                        <x-ui-icon name="arrow-right" class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-0.5" />
                    </a>
                </div>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($siblings as $sibling)
                        <a href="{{ route('services.detail', [$category, $sibling]) }}"
                           class="reveal group flex flex-col rounded-2xl border border-ink-100 bg-white p-6 transition duration-400 hover:-translate-y-1 hover:border-brand-300 hover:shadow-[0_30px_60px_-45px_rgba(2,34,81,0.5)]">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                                <x-ui-icon :name="$sibling->icon ?? 'briefcase'" class="h-5 w-5" />
                            </span>

                            <h3 class="mt-4 font-display text-[16.5px] font-bold leading-snug text-ink-950">{{ $sibling->name }}</h3>

                            @if ($sibling->description)
                                <p class="mt-2 text-[13.5px] leading-[1.75] text-ink-600">{{ \Illuminate\Support\Str::limit($sibling->description, 96) }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ---------------- Who you would be working with ---------------- --}}
    @if ($experts->isNotEmpty())
        <section class="bg-white py-16 sm:py-20">
            <div class="container-rich">
                <span class="reveal mb-5 block h-1 w-12 rounded-full bg-brand-600" aria-hidden="true"></span>
                <h2 class="reveal font-display text-[24px] font-bold leading-tight text-ink-950 sm:text-[30px]">{{ __('site.services.who_leads') }}</h2>

                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($experts as $expert)
                        <div class="reveal flex items-center gap-4 rounded-2xl border border-ink-100 bg-white p-5">
                            <x-media-frame :src="$expert->photo" :alt="$expert->name" :seed="$expert->name"
                                           ratio="aspect-square" class="h-14 w-14 shrink-0 rounded-full" />
                            <div class="min-w-0">
                                <p class="font-display text-[15px] font-bold text-ink-950">{{ $expert->name }}</p>
                                <p class="mt-0.5 truncate text-[13px] text-ink-600">{{ $expert->designation }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ---------------- Ask for this service ---------------- --}}
    <section class="border-t border-ink-100 bg-ink-50 py-14 sm:py-16">
        <div class="container-rich">
            <div class="reveal grid gap-6 rounded-[2rem] bg-navy-700 px-7 py-10 text-white sm:px-12 lg:grid-cols-[1.4fr_0.6fr] lg:items-center">
                <div>
                    <h2 class="font-display text-[22px] font-bold leading-tight sm:text-[28px]">
                        {{ __('site.services.cta_title', ['service' => $service->name]) }}
                    </h2>
                    <p class="mt-3 max-w-xl text-[14.5px] leading-[1.8] text-white/70">{{ __('site.services.cta_body') }}</p>
                </div>

                <div class="flex flex-wrap gap-3 lg:justify-end">
                    <a href="{{ route('consultancy.create', ['area' => $category->slug, 'service' => $service->slug]) }}" class="btn-primary">
                        {{ __('site.actions.request_consultancy') }}
                        <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                    </a>
                    <a href="{{ route('contact') }}" class="btn-invert">{{ __('site.nav.contact') }}</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.app>
