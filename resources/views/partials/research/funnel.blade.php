{{--
 | A three-stage funding funnel: all proposals → the top few → the funded.
 | Used on the Research page for both the faculty cycle and the external one.
 |
 | $stages  three [value, title, body] rows, widest stage first
 | $perks   optional lines listed inside the middle (top proposals) card
 | $delay   the page's reveal-delay helper
--}}
@php $perks ??= []; @endphp

<ol class="mt-5 grid gap-4 lg:grid-cols-3 lg:gap-6">
    @foreach ($stages as $i => [$value, $title, $body])
        @php
            [$icon, $box, $valueColour, $textColour] = [
                ['document', 'bg-ink-50/70 border border-ink-100', 'text-ink-950', 'text-ink-600'],
                ['chart', 'bg-brand-50 border border-brand-100', 'text-brand-800', 'text-brand-900/70'],
                ['star', 'bg-brand-600 text-white shadow-[0_24px_50px_-28px_var(--color-brand-600)]', '!text-white', 'text-white/85'],
            ][$i];
        @endphp
        <li class="reveal relative flex flex-col rounded-[1.25rem] p-5 {{ $box }}" style="{{ $delay($i, 90) }}">
            <div class="flex items-center justify-between gap-4">
                <span class="font-display text-[34px] font-bold leading-none tabular-nums {{ $valueColour }}">{{ $value }}</span>
                <span @class([
                        'flex h-9 w-9 items-center justify-center rounded-lg',
                        'bg-white text-brand-600 ring-1 ring-ink-100' => ! $loop->last,
                        'bg-white/15 text-white' => $loop->last,
                      ])>
                    <x-ui-icon :name="$icon" class="h-4 w-4" />
                </span>
            </div>
            <p class="mt-3.5 font-display text-[15.5px] font-bold {{ $valueColour }}">{{ $title }}</p>
            <p class="mt-1 text-[13px] leading-relaxed {{ $textColour }}">{{ $body }}</p>

            {{-- What the top proposals get on top of being announced --}}
            @if ($i === 1 && $perks)
                <ul class="mt-3 space-y-1.5 border-t border-brand-100 pt-3">
                    @foreach ($perks as $perk)
                        <li class="flex items-start gap-2 text-[13px] font-semibold text-brand-800">
                            <x-ui-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-brand-600" />
                            {{ $perk }}
                        </li>
                    @endforeach
                </ul>
            @endif

            @unless ($loop->last)
                {{-- Arrow into the next stage --}}
                <span class="absolute -bottom-[1.625rem] left-1/2 z-10 flex h-9 w-9 -translate-x-1/2 items-center justify-center rounded-full bg-white text-brand-600 shadow-md ring-1 ring-ink-100 lg:-right-[1.875rem] lg:bottom-auto lg:left-auto lg:top-1/2 lg:translate-x-0 lg:-translate-y-1/2" aria-hidden="true">
                    <x-ui-icon name="arrow-right" class="h-4 w-4 rotate-90 lg:rotate-0" />
                </span>
            @endunless
        </li>
    @endforeach
</ol>
