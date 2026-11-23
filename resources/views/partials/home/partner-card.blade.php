<div class="grid min-h-24 w-66 shrink-0 grid-cols-[auto_minmax(0,1fr)] items-center gap-3.5 rounded-2xl bg-white p-4 sm:w-72" @if ($duplicate) aria-hidden="true" @endif>
    <div class="flex size-14 items-center justify-center rounded-xl {{ $partner->logo ? 'bg-white' : 'bg-[#edf2e8]' }}" aria-hidden="true">
        @if ($partner->logo)
            <img src="{{ Storage::url($partner->logo) }}" alt="" class="size-full object-contain" width="56" height="56" loading="lazy" decoding="async">
        @else
            <x-partner-mark :partner="$partner" size="lg" />
        @endif
    </div>
    <div class="min-w-0">
        <p class="font-display text-[15px] leading-snug font-bold text-ink-950 [overflow-wrap:anywhere]">{{ $partner->name }}</p>
        @if ($partner->type)
            <p class="mt-1 text-xs leading-relaxed text-ink-600 [overflow-wrap:anywhere]">{{ $partner->type }}</p>
        @endif
    </div>
</div>
