@props([])

@php
    $categories = $site->navCategories();

    // Grouped navigation. `match` lists the route patterns that light a group up;
    // groups with a `panel` open the full-width panel under the bar.
    // The main bar. Anything that does not fit lives in $utility below, in the
    // strip above the bar, so every section is reachable without a dropdown.
    $links = [
        ['key' => 'home', 'label' => __('site.nav.home'), 'route' => 'home', 'match' => ['home']],
        ['key' => 'projects', 'label' => __('site.nav.projects'), 'route' => 'projects.index', 'match' => ['projects.*']],
        ['key' => 'research', 'label' => __('site.nav.research'), 'route' => 'research', 'match' => ['research']],
        ['key' => 'innovation', 'label' => __('site.nav.innovation'), 'route' => 'innovation.index', 'match' => ['innovation.*']],
        ['key' => 'consultancy', 'label' => __('site.nav.consultancy'), 'route' => 'services.index', 'match' => ['services.*', 'consultancy.*'], 'panel' => [
            'title' => __('site.nav.consultancy'),
            'text' => __('site.nav.consultancy_panel_text'),
            'children' => array_merge(
                $categories->map(fn ($category) => [
                    $category->name, route('services.show', $category), $category->icon ?? 'grid', $category->tagline,
                ])->all(),
                [[__('site.nav.industry'), route('industry'), 'handshake', __('site.nav.industry_note')]],
            ),
        ]],
        ['key' => 'team', 'label' => __('site.nav.team'), 'route' => 'experts.index', 'match' => ['experts.*']],
        ['key' => 'about', 'label' => __('site.nav.about'), 'route' => 'about', 'match' => ['about']],
        ['key' => 'startup', 'label' => __('site.nav.startup'), 'route' => 'startup', 'match' => ['startup', 'ideas.*']],
        ['key' => 'news', 'label' => __('site.nav.news'), 'route' => 'news.index', 'match' => ['news.*', 'events']],
    ];

    // Either side of the logo.
    $leftLinks = array_slice($links, 0, (int) ceil(count($links) / 2));
    $rightLinks = array_slice($links, (int) ceil(count($links) / 2));

    // The rest of the sections, in the strip above the bar.
    $utility = [
        // [__('site.nav.publications'), route('publications'), 'document', ['publications']],   // hidden from the strip on request
        [__('site.nav.patents'), route('patents'), 'key', ['patents']],
        [__('site.nav.industry'), route('industry'), 'handshake', ['industry']],
        // [__('site.nav.labs'), route('labs'), 'cpu', ['labs']],   // hidden from the strip on request
    ];

    $email = $site->get('contact_email');
    $phone = $site->get('contact_phone');
@endphp

{{-- A white sticky bar on every page; it only tightens up once you scroll. --}}
<header
    x-data="siteHeader()"
    x-init="init()"
    @scroll.window="onScroll()"
    @keydown.escape.window="panel = null"
    class="sticky top-0 z-50 animate-header-in">

    <div class="relative" @mouseleave="leave()">

        <div class="header-accent h-[3px]" aria-hidden="true"></div>

        {{-- Info strip: folds away once the page scrolls --}}
        <div class="header-strip header-ease hidden overflow-hidden lg:block"
             :class="solid ? 'max-h-0 opacity-0' : 'max-h-10 opacity-100'">
            {{-- Same row width as the bar below, so the strip's first and last
                 items sit above the first and last items of the menu. --}}
            <div class="header-row flex h-9 items-center justify-between gap-6 text-[12.5px]">
                {{-- How far the first menu label sits from the edge depends on how
                     wide the menu is, which changes with the language and the
                     viewport — so the inset is measured, not guessed. --}}
                <nav x-ref="strip" class="flex items-center gap-5" aria-label="Secondary"
                     :style="`padding-inline-start: ${stripInset}px`">
                    @foreach ($utility as [$label, $url, $icon, $patterns])
                        <a href="{{ $url }}"
                           @class(['flex items-center gap-1.5 transition hover:opacity-100', 'font-semibold' => \App\Support\Navigation::isCurrent(...$patterns)])>
                            <x-ui-icon :name="$icon" class="h-3.5 w-3.5" />
                            <span data-strip-label>{{ $label }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-5">
                    <a href="{{ route('ideas.create') }}" class="group hidden items-center gap-1.5 font-medium transition hover:opacity-100 lg:flex">
                        {{ __('site.actions.have_an_idea') }}
                        <x-ui-icon name="arrow-right" class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-0.5" />
                    </a>

                    <x-language-switch class="!py-1" />

                    <a href="{{ route('consultancy.create') }}"
                       class="group flex items-center gap-1.5 rounded-full bg-brand-600 px-3.5 py-1 font-semibold text-white transition hover:bg-brand-500">
                        {{ __('site.actions.collaborate') }}
                        <x-ui-icon name="arrow-up-right" class="h-3.5 w-3.5 transition-transform duration-300 group-hover:rotate-45" />
                    </a>
                </div>
            </div>
        </div>

        {{-- Main bar --}}
        <div class="header-bar header-ease border-b border-ink-100 bg-white"
             :class="solid ? 'shadow-[0_10px_30px_-26px_rgba(7,20,38,0.6)]' : 'shadow-none'">

            <div class="header-row header-ease flex items-center justify-between gap-5"
                 :class="solid ? 'h-[72px]' : 'h-[108px]'">

                {{-- Split navigation: half the menu, the logo, then the rest.
                     Each side carries its own sliding highlight, so `side` says
                     which one is allowed to show it. --}}
                @foreach ([['left', $leftLinks, 'justify-end'], ['right', $rightLinks, 'justify-start']] as [$sideKey, $sideLinks, $justify])
                    @if ($sideKey === 'right')
                        {{-- The logo spans both rows: it rises into the strip above the
                             bar, which is light enough to read it against. --}}
                        <x-brand-mark class="header-ease z-10 h-[118px] shrink-0"
                                      ::style="`height: ${solid ? 60 : 118}px; margin-top: ${solid ? 0 : -37}px`" />
                    @endif

                    <nav x-ref="nav-{{ $sideKey }}"
                         class="relative hidden h-full flex-1 items-stretch {{ $justify }} xl:flex"
                         aria-label="{{ $sideKey === 'left' ? 'Primary' : 'Primary, continued' }}"
                         @mouseleave="settle()">
                        @foreach ($sideLinks as $i => $link)
                            @php $active = \App\Support\Navigation::isCurrent(...$link['match']); @endphp

                            <a href="{{ route($link['route']) }}"
                               class="nav-item animate-nav-in"
                               style="animation-delay: {{ 120 + $i * 40 }}ms"
                               data-active="{{ $active ? 'true' : 'false' }}"
                               data-side="{{ $sideKey }}"
                               @if ($active) x-ref="current" @endif
                               @mouseenter="hover($el, {{ isset($link['panel']) ? "'{$link['key']}'" : 'null' }}, '{{ $sideKey }}')"
                               @focus="hover($el, {{ isset($link['panel']) ? "'{$link['key']}'" : 'null' }}, '{{ $sideKey }}')"
                               @if (isset($link['panel'])) :aria-expanded="panel === '{{ $link['key'] }}'" @endif>
                                {{ $link['label'] }}
                                @isset($link['panel'])
                                    <x-ui-icon name="chevron-down" class="h-3.5 w-3.5 opacity-60 transition-transform duration-300"
                                               ::class="panel === '{{ $link['key'] }}' && 'rotate-180'" />
                                @endisset
                            </a>
                        @endforeach

                        <span class="nav-indicator" aria-hidden="true"
                              :style="`transform: translateX(${ind.left}px); width: ${ind.width}px; opacity: ${ind.width && side === '{{ $sideKey }}' ? 1 : 0}`"></span>
                    </nav>
                @endforeach

                {{-- On narrow screens the navs are hidden, so this sits opposite the logo. --}}
                <div class="flex shrink-0 items-center gap-2.5 xl:hidden">
                    <button type="button" @click="open = true"
                            class="menu-button flex h-10 items-center gap-2 rounded-full border border-ink-200 bg-white pl-4 pr-3
                                   text-[13.5px] font-semibold text-ink-800 transition duration-300 hover:border-brand-300 hover:text-brand-700"
                            aria-label="{{ __('site.nav.open_menu') }}">
                        {{ __('site.nav.menu') }}
                        <x-ui-icon name="menu" class="h-4.5 w-4.5" />
                    </button>
                </div>
            </div>

            {{-- Scroll progress --}}
            <div class="relative h-0.5 w-full" aria-hidden="true">
                <div class="h-0.5 origin-left bg-brand-600 transition-transform duration-150 ease-out"
                     :style="`transform: scaleX(${progress})`"></div>
            </div>
        </div>

        {{-- One full-width panel; its content swaps with the hovered section --}}
        <div x-show="panel" x-cloak
             x-transition:enter="transition duration-300 ease-out"
             x-transition:enter-start="opacity-0 -translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition duration-150 ease-in"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             @mouseenter="keep()"
             class="absolute inset-x-0 top-full hidden border-b border-ink-100 bg-white shadow-[0_40px_80px_-50px_rgba(11,15,24,0.45)] xl:block">
            @foreach ($links as $link)
                @isset($link['panel'])
                    @php $many = count($link['panel']['children']) > 3; @endphp
                    <div x-show="panel === '{{ $link['key'] }}'"
                         x-transition:enter="transition duration-300 ease-out"
                         x-transition:enter-start="opacity-0 translate-x-3"
                         x-transition:enter-end="opacity-100 translate-x-0"
                         class="container-rich grid grid-cols-[280px_1fr] gap-12 py-9">

                        <div class="border-r border-ink-100 pr-10">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-brand-600">{{ $link['label'] }}</p>
                            <h3 class="mt-3 font-display text-[24px] font-bold leading-tight tracking-tight text-ink-950">{{ $link['panel']['title'] }}</h3>
                            <p class="mt-3 text-[14px] leading-relaxed muted">{{ $link['panel']['text'] }}</p>
                            <a href="{{ route($link['route']) }}"
                               class="group mt-6 inline-flex items-center gap-2 text-[14px] font-semibold text-brand-700">
                                View all
                                <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
                            </a>
                        </div>

                        <div @class(['grid content-start gap-2', 'grid-cols-3' => $many, 'grid-cols-2 max-w-3xl' => ! $many])>
                            @foreach ($link['panel']['children'] as $j => [$childLabel, $childUrl, $childIcon, $childText])
                                <a href="{{ $childUrl }}"
                                   class="group flex items-start gap-3.5 rounded-2xl border border-transparent p-4 transition duration-300 hover:border-ink-100 hover:bg-ink-50">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600 transition duration-300 group-hover:bg-brand-600 group-hover:text-white">
                                        <x-ui-icon :name="$childIcon" class="h-4.5 w-4.5" />
                                    </span>
                                    <span class="min-w-0">
                                        <span class="flex items-center gap-1 text-[14.5px] font-semibold leading-snug text-ink-950">
                                            {{ $childLabel }}
                                            <x-ui-icon name="arrow-up-right" class="h-3.5 w-3.5 -translate-x-1 opacity-0 transition duration-300 group-hover:translate-x-0 group-hover:opacity-100" />
                                        </span>
                                        @if ($childText)
                                            <span class="mt-1 block text-[13px] leading-snug muted">{{ $childText }}</span>
                                        @endif
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endisset
            @endforeach
        </div>
    </div>

    {{-- Mobile / tablet: full-screen menu --}}
    <div x-show="open" x-cloak
         x-transition:enter="transition duration-400 ease-out"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-200 ease-in"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[60] flex flex-col overflow-y-auto bg-ink-950 text-white xl:hidden"
         role="dialog" aria-modal="true" aria-label="Site menu">

        <div class="container-rich flex h-[100px] shrink-0 items-center justify-between">
            <x-brand-mark invert class="h-[72px]" />
            <button type="button" @click="open = false"
                    class="flex h-10 items-center gap-2 rounded-full border border-white/20 pl-4 pr-3 text-[13.5px] font-semibold transition hover:bg-white/10"
                    aria-label="{{ __('site.nav.close_menu') }}">
                {{ __('site.nav.close') }}
                <x-ui-icon name="x" class="h-4.5 w-4.5" />
            </button>
        </div>

        <nav class="container-rich mt-4 flex flex-col" aria-label="Mobile">
            @foreach ($links as $i => $link)
                <div x-show="open"
                     x-transition:enter="transition duration-500 ease-out"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     style="transition-delay: {{ 80 + $i * 50 }}ms"
                     class="border-b border-white/10"
                     @isset($link['panel']) x-data="{ sub: false }" @endisset>
                    @isset($link['panel'])
                        <button type="button" @click="sub = ! sub" :aria-expanded="sub"
                                class="flex w-full items-center gap-4 py-4 text-left">
                            <span class="w-6 font-mono text-[12px] text-white/40">{{ sprintf('%02d', $i + 1) }}</span>
                            <span class="flex-1 font-display text-[26px] font-semibold tracking-tight">{{ $link['label'] }}</span>
                            <span class="flex h-8 w-8 items-center justify-center rounded-full border border-white/20">
                                <x-ui-icon name="plus" class="h-4 w-4 transition-transform duration-300" ::class="sub && 'rotate-45'" />
                            </span>
                        </button>
                        <div x-show="sub" x-collapse>
                            <div class="grid gap-1 pb-5 pl-10 sm:grid-cols-2">
                                @foreach ($link['panel']['children'] as [$childLabel, $childUrl, $childIcon])
                                    <a href="{{ $childUrl }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-[15px] text-white/75 transition hover:bg-white/5 hover:text-white">
                                        <x-ui-icon :name="$childIcon" class="h-4 w-4 shrink-0 text-brand-300" /> {{ $childLabel }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <a href="{{ route($link['route']) }}" class="flex items-center gap-4 py-4">
                            <span class="w-6 font-mono text-[12px] text-white/40">{{ sprintf('%02d', $i + 1) }}</span>
                            <span class="flex-1 font-display text-[26px] font-semibold tracking-tight">{{ $link['label'] }}</span>
                            <x-ui-icon name="arrow-up-right" class="h-5 w-5 text-white/40" />
                        </a>
                    @endisset
                </div>
            @endforeach
        </nav>

        <div class="container-rich mt-8">
            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-white/40">{{ __('site.nav.more') }}</p>
            <div class="mt-3 grid gap-1 sm:grid-cols-2">
                @foreach ($utility as [$label, $url, $icon, $patterns])
                    <a href="{{ $url }}" class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-[15px] text-white/75 transition hover:bg-white/5 hover:text-white">
                        <x-ui-icon :name="$icon" class="h-4 w-4 shrink-0 text-brand-300" /> {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="container-rich mt-8">
            <x-language-switch invert />
        </div>

        <div class="container-rich mt-auto grid gap-6 pb-10 pt-12 sm:grid-cols-2 sm:items-end">
            <div class="text-sm text-white/60">
                @if ($email)
                    <a href="mailto:{{ $email }}" class="block transition hover:text-white">{{ $email }}</a>
                @endif
                @if ($phone)
                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="mt-1 block transition hover:text-white">{{ $phone }}</a>
                @endif
            </div>
            <a href="{{ route('consultancy.create') }}" class="btn-primary w-full sm:w-auto sm:justify-self-end">
                {{ __('site.actions.collaborate_with_us') }}
                <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
            </a>
        </div>
    </div>
</header>
