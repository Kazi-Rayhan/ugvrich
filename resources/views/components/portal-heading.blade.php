@props(['title', 'lead' => null])

{{-- One heading for every page in the portal, so a list and the form behind it
     open the same way: title, a line of plain explanation, and whatever action
     belongs at the top right. --}}
<div {{ $attributes->merge(['class' => 'mb-6 flex flex-wrap items-start justify-between gap-5']) }}>
    <div class="max-w-2xl">
        <h2 class="font-display text-[22px] font-bold tracking-tight text-ink-950 sm:text-[26px]">{{ $title }}</h2>

        @if ($lead)
            <p class="mt-2 text-[14.5px] leading-[1.8] text-ink-600">{{ $lead }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-3">{{ $actions }}</div>
    @endisset
</div>
