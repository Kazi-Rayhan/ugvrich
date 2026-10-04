@props([
    'eyebrow' => null,
    'title' => '',
    'lead' => null,
    'breadcrumbs' => [],   // [label => url|null]
    'image' => null,       // storage path, absolute URL or /public path; falls back to a per-section default
    'video' => null,       // optional storage path; plays behind the text, with the photo as its poster
])

@php
    $route = (string) request()->route()?->getName();

    // Default photography per section of the site.
    $defaults = [
        'about' => asset('images/heroes/about.jpg'),
        'research' => asset('images/heroes/research.jpg'),
        'contact' => asset('images/heroes/contact.jpg'),
        'experts' => asset('images/heroes/experts.jpg'),
        'services' => asset('images/heroes/services.jpg'),
        'projects' => \Illuminate\Support\Facades\Storage::url('projects/renewable-energy.jpg'),
        'news' => \Illuminate\Support\Facades\Storage::url('posts/digital-government-conference.jpg'),
    ];

    $src = match (true) {
        blank($image) => $defaults[\Illuminate\Support\Str::before($route, '.')] ?? asset('images/heroes/about.jpg'),
        \Illuminate\Support\Str::startsWith($image, ['http://', 'https://', '/']) => $image,
        default => \Illuminate\Support\Facades\Storage::url($image),
    };
@endphp

<section class="relative isolate overflow-hidden bg-ink-950 pb-12 pt-8 sm:pb-14 sm:pt-10 lg:pb-16">
    {{-- Background: a muted looped video when one is set (the photo is its poster), otherwise the photo.
         With reduced motion the video stays on its first frame. --}}
    @if ($video)
        <video class="pointer-events-none absolute inset-0 -z-20 h-full w-full object-cover"
               autoplay muted loop playsinline preload="auto" poster="{{ $src }}"
               x-data x-init="if (matchMedia('(prefers-reduced-motion: reduce)').matches) { $el.removeAttribute('autoplay'); $el.pause() }"
               aria-hidden="true" tabindex="-1">
            <source src="{{ \Illuminate\Support\Facades\Storage::url($video) }}"
                    type="{{ str_ends_with(strtolower($video), '.webm') ? 'video/webm' : 'video/mp4' }}">
        </video>
    @else
        <img src="{{ $src }}" alt="" aria-hidden="true" fetchpriority="high"
             class="absolute inset-0 -z-20 h-full w-full scale-105 object-cover">
    @endif

    {{-- Legibility layers --}}
    <div class="absolute inset-0 -z-10 bg-ink-950/60" aria-hidden="true"></div>
    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-ink-950/90 via-ink-950/55 to-transparent" aria-hidden="true"></div>
    <div class="absolute inset-0 -z-10 text-white grid-overlay opacity-25 [mask-image:linear-gradient(to_right,black,transparent_70%)]" aria-hidden="true"></div>
    <div class="absolute inset-x-0 bottom-0 -z-10 h-1 bg-brand-600" aria-hidden="true"></div>

    {{-- Decorative rings --}}
    <div class="pointer-events-none absolute -right-24 top-1/2 -z-10 hidden h-[34rem] w-[34rem] -translate-y-1/2 rounded-full border border-white/10 lg:block" aria-hidden="true"></div>
    <div class="pointer-events-none absolute right-16 top-1/2 -z-10 hidden h-[20rem] w-[20rem] -translate-y-1/2 rounded-full border border-white/10 lg:block" aria-hidden="true"></div>

    <div class="container-rich">
        @if (! empty($breadcrumbs))
            <nav aria-label="Breadcrumb" class="mb-10">
                <ol class="flex max-w-full flex-wrap items-center gap-x-3 gap-y-1.5 text-[13.5px] font-medium">
                    <li>
                        <a href="{{ route('home') }}" class="group flex items-center gap-2 text-white/65 transition hover:text-white">
                            <svg class="h-4 w-4 text-brand-300 transition group-hover:text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5"/>
                            </svg>
                            Home
                        </a>
                    </li>
                    @foreach ($breadcrumbs as $label => $url)
                        <li aria-hidden="true" class="h-px w-5 bg-white/30"></li>
                        <li class="min-w-0">
                            @if ($url)
                                <a href="{{ $url }}" class="text-white/65 transition hover:text-white">{{ $label }}</a>
                            @else
                                <span class="relative block truncate pb-1 text-white after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-6 after:rounded-full after:bg-brand-400" aria-current="page">{{ $label }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        <div class="max-w-3xl">
            @if ($eyebrow)
                <span class="inline-flex items-center gap-3 text-[12px] font-semibold uppercase tracking-[0.2em] text-brand-300">
                    <span class="h-0.5 w-8 rounded-full bg-brand-400"></span>{{ $eyebrow }}
                </span>
            @endif

            <h1 class="mt-4 font-display text-[28px] font-bold leading-[1.12] tracking-tight !text-white sm:text-[34px] lg:text-[40px] [&_.text-accent]:!text-brand-300">
                {!! $title !!}
            </h1>

            @if ($lead)
                <p class="mt-4 max-w-2xl text-[15.5px] leading-[1.7] text-white/75">{{ $lead }}</p>
            @endif

            @if (trim($slot) !== '')
                <div class="mt-7 flex flex-wrap items-center gap-3 [&_.btn-ghost]:border-white/30 [&_.btn-ghost]:bg-white/10 [&_.btn-ghost]:text-white [&_.btn-ghost]:backdrop-blur-md [&_.btn-ghost:hover]:border-white [&_.btn-ghost:hover]:bg-white [&_.btn-ghost:hover]:text-ink-950">{{ $slot }}</div>
            @endif
        </div>
    </div>
</section>
