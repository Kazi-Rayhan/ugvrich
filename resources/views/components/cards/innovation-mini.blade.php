@props(['innovation', 'slug' => null, 'index' => 0])

{{-- A small card for one innovation under its area: the photograph (or the
     drawn cover when there is none), its type — a working prototype or a
     proposal — and its name and line, leading to its page. --}}
@php
    $isPrototype = $innovation->phase === \App\Models\Innovation::CURRENT;
    $tag = $slug ? 'a' : 'div';
@endphp

<{{ $tag }} @if ($slug) href="{{ route('innovation.show', $slug) }}" @endif
    {{ $attributes->class([
        'reveal group flex flex-col overflow-hidden rounded-[1.25rem] border border-ink-100 bg-white transition duration-300',
        'hover:-translate-y-1 hover:border-brand-200 hover:shadow-[0_22px_44px_-30px_rgba(7,20,38,0.5)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500' => $slug,
    ]) }}
    style="transition-delay: {{ min($index * 50, 300) }}ms">

    <div class="relative">
        <x-media-frame :src="$innovation->image" :alt="$innovation->name" :seed="$innovation->name"
                       icon="lightbulb" ratio="aspect-[16/10]" />

        <span @class([
            'absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-[0.08em] shadow-[0_8px_18px_-10px_rgba(7,20,38,0.6)]',
            'bg-brand-600 text-white' => $isPrototype,
            'bg-gold-300 text-navy-900' => ! $isPrototype,
        ])>
            <x-ui-icon :name="$isPrototype ? 'cog' : 'lightbulb'" class="h-3 w-3" />
            {{ __('site.innovation.type.'.($isPrototype ? 'current' : 'proposed')) }}
        </span>
    </div>

    <div class="flex flex-1 flex-col p-4">
        <h3 class="font-display text-[15.5px] font-bold leading-snug text-ink-950 transition-colors group-hover:text-brand-700">{{ $innovation->name }}</h3>
        @if ($innovation->tagline)
            <p class="mt-1.5 line-clamp-2 text-[13px] leading-relaxed text-ink-600">{{ $innovation->tagline }}</p>
        @endif
        @if ($slug)
            <span class="mt-auto inline-flex items-center gap-1.5 pt-3 text-[12.5px] font-semibold text-brand-700">
                {{ __('site.innovation.view_innovation') }}
                <x-ui-icon name="arrow-right" class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-1" />
            </span>
        @endif
    </div>
</{{ $tag }}>
