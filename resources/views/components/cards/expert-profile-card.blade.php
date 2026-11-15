@props(['expert', 'index' => 0])

@php
    $url = route('experts.show', $expert);
    $tags = array_slice($expert->expertise ?? [], 0, 2);
    $moreTags = max(count($expert->expertise ?? []) - 2, 0);
    $department = \Illuminate\Support\Str::after($expert->department ?? '', 'Department of ');
    $area = $expert->category?->name;
    $iconBtn = 'relative z-20 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-ink-200 bg-white text-ink-500 transition hover:border-brand-600 hover:bg-brand-50 hover:text-brand-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600';
@endphp

{{-- The scroll-reveal lives on the wrapper so its transform and delay never fight the card's hover. --}}
<div {{ $attributes->merge(['class' => 'reveal']) }} style="transition-delay: {{ min($index * 80, 400) }}ms">
    <article class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-[0_4px_20px_-12px_rgba(7,20,38,0.16)] transition-[transform,box-shadow,border-color] duration-300 motion-safe:hover:-translate-y-1 hover:border-brand-200 hover:shadow-[0_16px_40px_-20px_rgba(7,20,38,0.24)]">

        {{-- Full portrait stays visible regardless of the uploaded image proportions. --}}
        <div class="relative flex aspect-[3/2] items-center justify-center overflow-hidden border-b border-ink-100 bg-gradient-to-br from-ink-50 to-ink-100">
            @if ($expert->photo)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($expert->photo) }}" alt="{{ $expert->name }}" loading="lazy" decoding="async"
                     class="h-full w-full object-contain object-center">
            @else
                <span class="flex h-28 w-28 items-center justify-center rounded-full border border-brand-200 bg-brand-50 font-display text-4xl font-semibold text-brand-700" aria-hidden="true">{{ $expert->initials }}</span>
            @endif
        </div>

        {{-- Details --}}
        <div class="flex flex-1 flex-col p-6">
            @if ($expert->designation)
                <p class="mb-2 text-xs font-semibold leading-relaxed tracking-wide text-brand-700">{{ $expert->designation }}</p>
            @endif

            <h3 class="font-display text-xl font-bold leading-snug text-ink-950 break-words">
                {{-- Stretched link: the whole card is clickable; contact buttons sit above it --}}
                <a href="{{ $url }}" class="transition-colors group-hover:text-brand-700 after:absolute after:inset-0 after:z-10 after:rounded-2xl focus-visible:outline-none focus-visible:after:ring-2 focus-visible:after:ring-brand-600">
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
                <p class="mt-3 flex items-start gap-2 text-[13px] leading-relaxed text-ink-600">
                    <x-ui-icon :name="$expert->category->icon ?? 'briefcase'" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                    <span class="break-words">{{ $area }}</span>
                </p>
            @endif

            @if ($tags)
                <div class="mt-5 flex flex-wrap gap-2">
                    @foreach ($tags as $tag)
                        <span class="max-w-full rounded-md bg-ink-50 px-2.5 py-1.5 text-xs leading-relaxed text-ink-600 break-words">{{ $tag }}</span>
                    @endforeach
                    @if ($moreTags)
                        <span class="rounded-md border border-ink-200 px-2.5 py-1.5 text-xs leading-relaxed text-ink-500">+{{ $moreTags }}</span>
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

                <span class="ml-auto inline-flex items-center gap-2 text-[13px] font-semibold text-brand-700" aria-hidden="true">
                    {{ __('site.cards.view_profile') }}
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-50 text-brand-700 transition-colors group-hover:bg-brand-600 group-hover:text-white">
                        <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                    </span>
                </span>
            </div>
        </div>
    </article>
</div>
