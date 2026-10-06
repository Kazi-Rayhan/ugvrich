@php
    // An uploaded video wins; otherwise the bundled RICH clip.
    $uploaded = $site->get('hero_video');
    $heroVideo = $uploaded ? Storage::url($uploaded) : asset('media/solar-car-new.mp4');
    $videoType = str_ends_with(strtolower((string) ($uploaded ?: 'solar-car-new.mp4')), '.webm') ? 'video/webm' : 'video/mp4';

    $heroPoster = $site->get('hero_poster') ? Storage::url($site->get('hero_poster')) : null;

    // Wrap the configured highlight word so the accent survives edits to the heading from the admin panel.
    $heading = e((string) $site->get('hero_heading'));
    $highlight = trim((string) $site->get('hero_highlight', 'innovation'));

    $headingHtml = $highlight === ''
        ? $heading
        : preg_replace('/'.preg_quote(e($highlight), '/').'/i', '<span class="text-brand-300">$0</span>', $heading, 1);

    // The rotating kicker above the headline.
    $rotatingWords = __('site.home.rotate_words');

@endphp

{{-- The clip is square, so rather than cropping it across the full width it sits whole in a frame beside the copy. --}}
<section class="hero-scene relative isolate overflow-hidden bg-ink-950" x-data="heroScene">

    {{-- Backdrop: brand glow, a faint grid, the drifting research network and
         a soft light that follows the pointer. All decorative. --}}
    <div class="absolute inset-0 -z-10" aria-hidden="true">
        <div class="absolute -right-40 top-10 h-[640px] w-[640px] rounded-full bg-brand-500/30 blur-[140px]"></div>
        <div class="absolute -left-40 bottom-0 h-[420px] w-[420px] rounded-full bg-navy-500/35 blur-[120px]"></div>
        <div class="grid-overlay absolute inset-0 text-white [mask-image:radial-gradient(ellipse_at_center,black,transparent_75%)]"></div>
        <canvas x-ref="network" class="absolute inset-0 h-full w-full opacity-80 [mask-image:radial-gradient(ellipse_at_45%_50%,black_30%,transparent_80%)]"></canvas>
        <div class="hero-cursor-glow"></div>
    </div>

    <div class="container-rich grid items-center gap-12 pb-20 pt-16 lg:grid-cols-[1.05fr_1fr] lg:gap-16 lg:pb-24 lg:pt-24">
        <div class="max-w-xl">
            {{-- Kicker: "From idea to …" cycling through what RICH does. Screen
                 readers get the whole list once instead of the motion. --}}
            <p class="reveal mb-5 font-display text-[18px] font-semibold tracking-tight text-white/75 sm:text-[21px]"
               x-data="rotatingWord({{ count($rotatingWords) }})" x-intersect:enter="play()" x-intersect:leave="pause()">
                <span aria-hidden="true">
                    {{ __('site.home.rotate_lead') }}
                    <span class="rotator">
                        @foreach ($rotatingWords as $i => $word)
                            <span class="rotator-word text-highlight" :class="{ 'is-active': current === {{ $i }}, 'is-leaving': previous === {{ $i }} }"
                                  @if ($i === 0) data-first @endif>{{ $word }}</span>
                        @endforeach
                    </span>
                </span>
                <span class="sr-only">{{ __('site.home.rotate_lead') }} {{ implode(', ', $rotatingWords) }}</span>
            </p>

            <h1 data-split
                class="reveal font-display text-[28px] font-bold leading-[1.15] tracking-tight text-white sm:text-[36px] lg:text-[44px]">
                {!! $headingHtml !!}
            </h1>

            <p class="reveal mt-6 text-[16px] leading-[1.75] text-white/70 sm:text-[17px]" style="transition-delay: 120ms">
                {{ $site->get('hero_subheading') }}
            </p>

            <div class="reveal mt-9 flex flex-wrap items-center gap-3" style="transition-delay: 220ms">
                <a href="{{ route('innovation.index') }}" class="btn-primary group !px-6" data-magnetic>
                    {{ __('site.home.explore_innovation') }}
                    <x-ui-icon name="arrow-up-right" class="h-4 w-4 transition-transform duration-300 group-hover:rotate-45" />
                </a>
                <a href="{{ route('ideas.create') }}"
                   class="btn !px-6 bg-white text-ink-950 hover:-translate-y-0.5 hover:bg-brand-50">
                    {{ __('site.actions.submit_idea') }}
                </a>
                <a href="{{ route('consultancy.create') }}"
                   class="btn !px-6 text-white underline-offset-4 hover:underline">
                    {{ __('site.actions.collaborate_with_us') }}
                </a>
            </div>
        </div>

        {{-- Footage, shown whole. With reduced motion it stays on its first frame. --}}
        <div class="reveal relative mx-auto w-full max-w-[560px] lg:mx-0 lg:justify-self-end" style="transition-delay: 160ms">
            <div class="absolute -inset-3 rounded-[2.25rem] border border-white/10" aria-hidden="true"></div>
            <div class="relative aspect-square overflow-hidden rounded-[1.75rem] bg-ink-900 ring-1 ring-white/15
                        shadow-[0_50px_100px_-40px_rgba(49,109,49,0.5)]">
                <video class="h-full w-full object-cover"
                       autoplay muted loop playsinline preload="auto"
                       @if ($heroPoster) poster="{{ $heroPoster }}" @endif
                       x-data x-init="if (matchMedia('(prefers-reduced-motion: reduce)').matches) { $el.removeAttribute('autoplay'); $el.pause() }"
                       aria-hidden="true" tabindex="-1">
                    <source src="{{ $heroVideo }}" type="{{ $videoType }}">
                </video>
                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-ink-950/60 to-transparent"></div>
            </div>

        </div>
    </div>
</section>
