@props(['expert', 'index' => 0])

@php
    $url = route('experts.show', $expert);
    $tags = array_slice($expert->expertise ?? [], 0, 3);
    $moreTags = max(count($expert->expertise ?? []) - 3, 0);
    $department = \Illuminate\Support\Str::after($expert->department ?? '', 'Department of ');
    $area = $expert->category?->name;
    $iconBtn = 'relative z-20 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-ink-200 bg-white text-navy-700 shadow-sm transition hover:border-brand-600 hover:bg-brand-600 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600';
@endphp

{{-- The scroll-reveal lives on the wrapper so its transform and delay never fight the card's hover. --}}
<div {{ $attributes->merge(['class' => 'reveal']) }} style="transition-delay: {{ min($index * 80, 400) }}ms">
    <article class="group relative flex h-full flex-col overflow-hidden rounded-[1.75rem] border border-ink-200 bg-white shadow-[0_12px_40px_-22px_rgba(2,34,81,0.3)] transition-[transform,box-shadow,border-color] duration-300 motion-safe:hover:-translate-y-1.5 hover:border-brand-300 hover:shadow-[0_24px_60px_-24px_rgba(2,34,81,0.35)]">

        {{-- A soft image backdrop fills the frame; the foreground preserves the full portrait. --}}
        <div class="relative isolate aspect-[4/3] overflow-hidden bg-navy-900">
            @if ($expert->photo)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($expert->photo) }}" alt="" aria-hidden="true" loading="lazy" decoding="async"
                     class="absolute inset-0 h-full w-full scale-110 object-cover opacity-45 blur-xl">
                <div class="absolute inset-0 bg-gradient-to-tr from-navy-900/70 via-transparent to-brand-600/20" aria-hidden="true"></div>
                <img src="{{ \Illuminate\Support\Facades\Storage::url($expert->photo) }}" alt="{{ $expert->name }}" loading="lazy" decoding="async"
                     class="absolute inset-0 h-full w-full object-contain object-center">
            @else
                <div class="absolute inset-0 bg-gradient-to-br from-navy-700 via-navy-900 to-brand-800" aria-hidden="true"></div>
                <span class="absolute left-1/2 top-1/2 flex h-32 w-32 -translate-x-1/2 -translate-y-1/2 items-center justify-center rounded-full border border-white/25 bg-white/10 font-display text-5xl font-semibold text-white shadow-xl backdrop-blur-sm" aria-hidden="true">{{ $expert->initials }}</span>
            @endif
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-navy-900/40 to-transparent" aria-hidden="true"></div>
        </div>

        {{-- Details --}}
        <div class="relative -mt-4 flex flex-1 flex-col rounded-t-[1.5rem] bg-white px-6 pb-6 pt-7">
            @if ($expert->designation)
                <p class="mb-3 flex items-start gap-2 text-[11px] font-bold uppercase leading-relaxed tracking-[0.08em] text-brand-700"><span class="mt-1 h-1.5 w-1.5 shrink-0 rounded-full bg-brand-500"></span>{{ $expert->designation }}</p>
            @endif

            <h3 class="font-display text-[23px] font-bold leading-tight tracking-tight text-navy-800 break-words">
                {{-- Stretched link: the whole card is clickable; contact buttons sit above it --}}
                <a href="{{ $url }}" class="transition-colors group-hover:text-brand-700 after:absolute after:inset-0 after:z-10 after:rounded-[1.75rem] focus-visible:outline-none focus-visible:after:ring-2 focus-visible:after:ring-brand-600">
                    {{ $expert->name }}
                </a>
            </h3>

            @if ($department)
                <p class="mt-1.5 flex items-start gap-2 text-[13px] leading-relaxed text-ink-500">
                    <x-ui-icon name="building" class="mt-0.5 h-4 w-4 shrink-0 text-ink-400" />
                    <span class="break-words">{{ $department }}</span>
                </p>
            @endif

            @if ($area)
                <p class="mt-4 flex items-center gap-3 rounded-xl border border-navy-100/70 bg-navy-50/70 p-3 text-[12px] font-medium leading-relaxed text-navy-700">
                    <x-ui-icon :name="$expert->category->icon ?? 'briefcase'" class="h-5 w-5 shrink-0 text-navy-600" />
                    <span class="break-words">{{ $area }}</span>
                </p>
            @endif

            @if ($tags)
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($tags as $tag)
                        <span class="max-w-full rounded-full border border-brand-100 bg-brand-50/80 px-3 py-1.5 text-[11px] font-medium leading-relaxed text-brand-800 break-words">{{ $tag }}</span>
                    @endforeach
                    @if ($moreTags)
                        <span class="rounded-full border border-ink-200 bg-white px-3 py-1.5 text-[11px] font-semibold leading-relaxed text-ink-500">+{{ $moreTags }}</span>
                    @endif
                </div>
            @endif

            <div class="min-h-6 flex-1"></div>

            {{-- Footer: contact + profile --}}
            <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-4 border-t border-ink-100 pt-5">
                <div class="flex flex-wrap items-center gap-2">
                    @if ($expert->email)
                        <a href="mailto:{{ $expert->email }}" aria-label="Email {{ $expert->name }}" title="{{ $expert->email }}" class="{{ $iconBtn }}">
                            <x-ui-icon name="mail" class="h-4 w-4" />
                        </a>
                    @endif
                    @if ($expert->phone)
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $expert->phone) }}" aria-label="Call {{ $expert->name }}" class="{{ $iconBtn }}">
                            <x-ui-icon name="phone" class="h-4 w-4" />
                        </a>
                    @endif
                    @if ($expert->linkedin)
                        <a href="{{ $expert->linkedin }}" target="_blank" rel="noopener" aria-label="{{ $expert->name }} on LinkedIn" class="{{ $iconBtn }}">
                            <x-ui-icon name="linkedin" class="h-4 w-4" />
                        </a>
                    @endif
                    @if ($expert->scholar)
                        <a href="{{ $expert->scholar }}" target="_blank" rel="noopener" aria-label="{{ $expert->name }} on Google Scholar" class="{{ $iconBtn }}">
                            <x-ui-icon name="academic" class="h-4 w-4" />
                        </a>
                    @endif
                </div>

                <span class="ml-auto inline-flex min-h-10 items-center gap-3 rounded-full bg-navy-800 py-1.5 pl-4 pr-1.5 text-[12px] font-semibold text-white shadow-[0_4px_12px_-4px_rgba(2,34,81,0.4)] transition-colors group-hover:bg-brand-700" aria-hidden="true">
                    {{ __('site.cards.view_profile') }}
                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-white/15 text-white transition-transform motion-safe:group-hover:rotate-45">
                        <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                    </span>
                </span>
            </div>
        </div>
    </article>
</div>
