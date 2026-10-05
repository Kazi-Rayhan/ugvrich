@props(['innovation', 'slug' => null, 'index' => 0])

{{-- A compact card for one innovation under its area: a small square
     thumbnail (or the drawn cover when there is no photograph), its type — a
     working prototype or a proposal — and its name, leading to its page. --}}
@php
    $isPrototype = $innovation->phase === \App\Models\Innovation::CURRENT;
    $tag = $slug ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($slug) href="{{ route('innovation.show', $slug) }}" @endif
    {{ $attributes->class([
        'reveal group flex items-center gap-5 rounded-[1.5rem] border border-ink-100 bg-white p-3.5 pr-5 transition duration-300',
        'hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-[0_18px_36px_-26px_rgba(7,20,38,0.5)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500' => $slug,
    ]) }}
    style="transition-delay: {{ min($index * 50, 300) }}ms">

    <x-media-frame :src="$innovation->image" :alt="$innovation->name" :seed="$innovation->name"
                   icon="lightbulb" ratio="aspect-square" class="w-[9rem] shrink-0 rounded-2xl" />

    <div class="min-w-0 flex-1">
        <span @class([
            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.08em]',
            'bg-brand-50 text-brand-700' => $isPrototype,
            'bg-gold-100 text-gold-700' => ! $isPrototype,
        ])>
            <x-ui-icon :name="$isPrototype ? 'cog' : 'lightbulb'" class="h-3 w-3" />
            {{ __('site.innovation.type.'.($isPrototype ? 'current' : 'proposed')) }}
        </span>
        <h3 class="mt-2.5 line-clamp-2 font-display text-[18px] font-bold leading-snug text-ink-950 transition-colors group-hover:text-brand-700">{{ $innovation->name }}</h3>
        @if ($innovation->tagline)
            <p class="mt-1.5 line-clamp-2 text-[14px] leading-relaxed text-ink-500">{{ $innovation->tagline }}</p>
        @endif
    </div>

    @if ($slug)
        <x-ui-icon name="arrow-right" class="h-[18px] w-[18px] shrink-0 text-ink-300 transition duration-300 group-hover:translate-x-0.5 group-hover:text-brand-600" />
    @endif
</{{ $tag }}>
