@props([])

@php
    $categories = $site->navCategories();

    // Grouped navigation. `match` lists the route patterns that light a group up;
    // groups with a `panel` open the full-width panel under the bar.
    // The main bar. Anything that does not fit lives in $utility below, in the
    // strip above the bar, so every section is reachable without a dropdown.
    $links = [
        ['key' => 'home', 'label' => __('site.nav.home'), 'route' => 'home', 'match' => ['home']],
        ['key' => 'research', 'label' => __('site.nav.research'), 'route' => 'research', 'match' => ['research']],
        ['key' => 'innovation', 'label' => __('site.nav.innovation'), 'route' => 'innovation.index', 'match' => ['innovation.*'], 'panel' => [
            'title' => __('site.nav.innovation'),
            'text' => __('site.nav.innovation_panel_text'),
            'children' => $site->innovationAreas()->map(fn ($area) => [
                $area->name, route('innovation.area', $area), $area->icon ?? 'lightbulb', $area->summary,
            ])->all(),
        ]],
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
        ['key' => 'internship', 'label' => __('site.nav.internship'), 'route' => 'internship.create', 'match' => ['internship.*']],
        ['key' => 'news', 'label' => __('site.nav.news'), 'route' => 'news.index', 'match' => ['news.*', 'events']],
    ];

    // Either side of the logo.
    $leftLinks = array_slice($links, 0, (int) ceil(count($links) / 2));
    $rightLinks = array_slice($links, (int) ceil(count($links) / 2));

    // The rest of the sections, in the strip above the bar.
    $utility = [
        [__('site.nav.projects'), route('projects.index'), 'document', ['projects.*']],
        // [__('site.nav.publications'), route('publications'), 'document', ['publications']],   // hidden from the strip on request
        [__('site.nav.patents'), route('patents'), 'key', ['patents']],
        [__('site.nav.industry'), route('industry'), 'handshake', ['industry']],
        // [__('site.nav.membership'), route('membership.create'), 'academic', ['membership.*']],   // hidden: the Internship page in the main menu covers it
        // [__('site.nav.labs'), route('labs'), 'cpu', ['labs']],   // hidden from the strip on request
    ];

    $email = $site->get('contact_email');
    $phone = $site->get('contact_phone');
@endphp

{{-- A white sticky bar on every page; it only tightens up once you scroll. --}}
<header
    x-data="siteHeader()"
    x-init="init()"
    @scroll.window.passive="onScroll()"
    @keydown.escape.window="panel = null; open = false"
    class="sticky top-0 z-50">

    <div class="relative" @mouseleave="leave()">

        <div class="header-accent h-[3px]" aria-hidden="true"></div>

        {{-- Info strip: folds away once the page scrolls --}}
        <div class="header-strip relative z-20 hidden overflow-visible lg:block">
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
                    <x-account-menu />
                </div>
            </div>
        </div>

        {{-- Main bar --}}
        <div class="header-bar border-b border-ink-100 bg-white shadow-[0_10px_30px_-26px_rgba(7,20,38,0.35)]">

            {{-- One height, always. The bar used to shrink as the page
                 scrolled, which meant animating the height of the bar and of
                 the logo inside it on every frame — layout work the browser
                 cannot put on the compositor, and the cause of the stutter. --}}
            <div class="header-row flex items-center justify-between gap-3 sm:gap-5 h-[72px] lg:h-[88px]">

                {{-- Split navigation: half the menu, the logo, then the rest.
                     Each side carries its own sliding highlight, so `side` says
                     which one is allowed to show it. --}}
                @foreach ([['left', $leftLinks, 'justify-end'], ['right', $rightLinks, 'justify-start']] as [$sideKey, $sideLinks, $justify])
                    @if ($sideKey === 'right')
                        {{-- The logo spans both rows: it rises into the strip above the
                             bar, which is light enough to read it against. --}}
                        <x-brand-mark class="z-10 h-12 shrink-0 sm:h-14 lg:h-[76px]" />
                    @endif

                    <nav x-ref="nav-{{ $sideKey }}"
                         class="relative hidden h-full flex-1 items-stretch {{ $justify }} xl:flex"
                         aria-label="{{ $sideKey === 'left' ? 'Primary' : 'Primary, continued' }}"
                         @mouseleave="settle()">
                        @foreach ($sideLinks as $i => $link)
                            @php $active = \App\Support\Navigation::isCurrent(...$link['match']); @endphp

                            <a href="{{ $link['url'] ?? route($link['route']) }}"
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
                {{-- On narrow screens the two navs are hidden, so this sits
                     opposite the logo. A plain round icon button on a phone,
                     where the word costs more room than it earns; the label
                     comes back as soon as there is space for it. --}}
                <div class="flex shrink-0 items-center gap-2.5 xl:hidden">
                    <x-account-menu />

                    <a href="{{ route('consultancy.create') }}"
                       class="hidden h-11 items-center gap-1.5 rounded-full bg-brand-600 px-4 text-[13.5px] font-semibold text-white transition hover:bg-brand-500 sm:flex">
                        {{ __('site.actions.collaborate') }}
                    </a>

                    <button type="button" @click="open = true"
                            class="menu-button flex h-11 w-11 items-center justify-center rounded-full border border-ink-200 bg-white
                                   text-ink-800 transition duration-300 hover:border-brand-300 hover:text-brand-700
                                   sm:w-auto sm:gap-2 sm:pl-4 sm:pr-3 sm:text-[13.5px] sm:font-semibold"
                            aria-label="{{ __('site.nav.open_menu') }}">
                        <span class="hidden sm:inline">{{ __('site.nav.menu') }}</span>
                        <x-ui-icon name="menu" class="h-5 w-5 sm:h-4.5 sm:w-4.5" />
                    </button>
                </div>
            </div>

            {{-- Scroll progress --}}
            <div class="relative h-0.5 w-full" aria-hidden="true">
                <div class="header-progress h-0.5 origin-left bg-brand-600 transition-transform duration-150 ease-out"
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
                            <a href="{{ $link['url'] ?? route($link['route']) }}"
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

    {{-- The panel is teleported to the end of <body>.

         It has to leave the header: the header runs an entrance animation whose
         keyframes carry a transform, and while that animation is filling the
         header becomes the containing block for any `fixed` descendant. The
         panel was therefore being sized and clipped to the header's own box —
         which is why only its top strip showed. Teleporting keeps this Alpine
         scope (`open` still drives it) while the element itself sits directly
         under <body>, where `fixed` means the viewport again. --}}
    <template x-teleport="body">
        <div>
        {{-- Mobile / tablet: an off-canvas panel.

             It slides in from the edge the menu button sits on and leaves the page
             showing behind it, so you can see where you are while you choose where
             to go. A full-screen takeover loses that, and on a phone it reads like
             a new page rather than a menu.

             Logical properties throughout (`end`, `ps`), so the side follows the
             writing direction rather than being pinned to the right. --}}

        {{-- Backdrop: dims the page and closes on a tap --}}
        <div x-show="open" x-cloak
             @click="open = false"
             x-transition:enter="transition-opacity duration-300 ease-out"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-200 ease-in"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-[55] bg-ink-950/60 backdrop-blur-[2px] xl:hidden"
             aria-hidden="true"></div>

        <div x-show="open" x-cloak
             x-transition:enter="transform transition duration-400 ease-[cubic-bezier(0.22,1,0.36,1)]"
             x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transform transition duration-200 ease-in"
             x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
             class="fixed inset-y-0 end-0 z-[60] flex w-[88%] max-w-[380px] flex-col overflow-y-auto overscroll-contain
                    bg-ink-950 text-white shadow-[0_0_60px_-12px_rgba(0,0,0,0.8)] xl:hidden"
             role="dialog" aria-modal="true" aria-label="{{ __('site.nav.menu') }}">

            {{-- Head: the mark, and the way out --}}
            <div class="flex h-[72px] shrink-0 items-center justify-between border-b border-white/10 px-5">
                <x-brand-mark invert class="h-11" />
                <button type="button" @click="open = false"
                        class="flex h-10 w-10 items-center justify-center rounded-full border border-white/20 transition hover:bg-white/10"
                        aria-label="{{ __('site.nav.close_menu') }}">
                    <x-ui-icon name="x" class="h-5 w-5" />
                </button>
            </div>

            <nav class="px-5 pt-2" aria-label="Mobile">
                @foreach ($links as $i => $link)
                    <div class="border-b border-white/10" @isset($link['panel']) x-data="{ sub: false }" @endisset>
                        @isset($link['panel'])
                            <button type="button" @click="sub = ! sub" :aria-expanded="sub"
                                    class="flex w-full items-center gap-3 py-3.5 text-left">
                                <span class="flex-1 font-display text-[17px] font-semibold tracking-tight">{{ $link['label'] }}</span>
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-white/20">
                                    <x-ui-icon name="plus" class="h-3.5 w-3.5 transition-transform duration-300" ::class="sub && 'rotate-45'" />
                                </span>
                            </button>

                            <div x-show="sub" x-collapse>
                                <div class="grid gap-0.5 pb-4 ps-1">
                                    @foreach ($link['panel']['children'] as [$childLabel, $childUrl, $childIcon, $childText])
                                        <a href="{{ $childUrl }}" class="flex items-start gap-2.5 rounded-xl px-3 py-2.5 text-[14.5px] text-white/70 transition hover:bg-white/5 hover:text-white">
                                            <x-ui-icon :name="$childIcon" class="mt-0.5 h-4 w-4 shrink-0 text-brand-300" />
                                            <span class="min-w-0">
                                                <span class="block">{{ $childLabel }}</span>
                                                @if ($childText)
                                                    <span class="mt-0.5 block text-[12.5px] leading-snug text-white/45">{{ $childText }}</span>
                                                @endif
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ route($link['route']) }}"
                               @class(['flex items-center gap-3 py-3.5', 'text-brand-300' => \App\Support\Navigation::isCurrent(...$link['match'])])>
                                <span class="flex-1 font-display text-[17px] font-semibold tracking-tight">{{ $link['label'] }}</span>
                                <x-ui-icon name="arrow-up-right" class="h-4 w-4 shrink-0 text-white/30" />
                            </a>
                        @endisset
                    </div>
                @endforeach
            </nav>

            <div class="px-5 pt-6">
                <p class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-white/40">{{ __('site.nav.more') }}</p>
                <div class="mt-2 grid gap-0.5">
                    @foreach ($utility as [$label, $url, $icon, $patterns])
                        <a href="{{ $url }}" class="flex items-center gap-2.5 rounded-xl py-2.5 text-[14.5px] text-white/70 transition hover:text-white">
                            <x-ui-icon :name="$icon" class="h-4 w-4 shrink-0 text-brand-300" /> {{ $label }}
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="px-5 pt-6">
                <x-language-switch invert />
            </div>

            {{-- Foot: pinned below, however short the menu is --}}
            <div class="mt-auto border-t border-white/10 px-5 pb-8 pt-6">
                <a href="{{ route('consultancy.create') }}" class="btn-primary w-full">
                    {{ __('site.actions.collaborate_with_us') }}
                    <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                </a>

                <div class="mt-4 text-[13px] text-white/55">
                    @if ($email)
                        <a href="mailto:{{ $email }}" class="block transition hover:text-white">{{ $email }}</a>
                    @endif
                    @if ($phone)
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}" class="mt-1 block transition hover:text-white">{{ $phone }}</a>
                    @endif
                </div>
            </div>
        </div>
        </div>
    </template>
</header>
