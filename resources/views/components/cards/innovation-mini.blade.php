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
        'reveal group flex items-center gap-3 rounded-2xl border border-ink-100 bg-white p-2.5 pr-3.5 transition duration-300',
        'hover:-translate-y-0.5 hover:border-brand-200 hover:shadow-[0_18px_36px_-26px_rgba(7,20,38,0.5)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500' => $slug,
    ]) }}
    style="transition-delay: {{ min($index * 50, 300) }}ms">

    <x-media-frame :src="$innovation->image" :alt="$innovation->name" :seed="$innovation->name"
                   icon="lightbulb" ratio="aspect-square" class="w-[4.5rem] shrink-0 rounded-xl" />

    <div class="min-w-0 flex-1">
        <span @class([
            'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-[0.08em]',
            'bg-brand-50 text-brand-700' => $isPrototype,
            'bg-gold-100 text-gold-700' => ! $isPrototype,
        ])>
            <x-ui-icon :name="$isPrototype ? 'cog' : 'lightbulb'" class="h-2.5 w-2.5" />
            {{ __('site.innovation.type.'.($isPrototype ? 'current' : 'proposed')) }}
        </span>
        <h3 class="mt-1 line-clamp-2 font-display text-[14px] font-bold leading-snug text-ink-950 transition-colors group-hover:text-brand-700">{{ $innovation->name }}</h3>
    </div>

    @if ($slug)
        <x-ui-icon name="arrow-right" class="h-4 w-4 shrink-0 text-ink-300 transition duration-300 group-hover:translate-x-0.5 group-hover:text-brand-600" />
    @endif
</{{ $tag }}>
