@props(['name', 'label', 'photo' => null, 'subtitle' => null, 'primary' => false])

<section class="overflow-hidden rounded-3xl border border-ink-200 bg-white shadow-sm">
    <div class="expert-profile-cover relative h-28 overflow-hidden sm:h-40 lg:h-48" aria-hidden="true">
        <div class="absolute inset-0 grid-overlay text-white/20 opacity-40"></div>
    </div>
    <div class="relative px-5 pb-6 sm:px-8 sm:pb-8">
        <div class="flex flex-wrap items-end justify-between gap-x-5 gap-y-4">
            <div class="min-w-0">
                <div aria-hidden="true" class="-mt-10 grid size-20 place-items-center overflow-hidden rounded-3xl border-4 border-white bg-brand-50 text-brand-700 shadow-sm sm:-mt-14 sm:size-28">
                    @if ($photo)
                        <img src="{{ Storage::disk('public')->url($photo) }}" width="112" height="112" class="size-full object-cover" alt="">
                    @else
                        <x-ui-icon name="user" class="size-9 sm:size-12" />
                    @endif
                </div>
                @if ($label)
                    <p class="mt-3 text-[13px] font-semibold text-teal-800">{{ $label }}</p>
                @endif
                @if ($primary)
                    <h1 class="mt-1 font-display text-[23px] font-bold tracking-tight break-words sm:text-[27px]">{{ $name }}</h1>
                @else
                    <h2 class="mt-1 font-display text-[23px] font-bold tracking-tight break-words sm:text-[27px]">{{ $name }}</h2>
                @endif
                @if ($subtitle)
                    <p class="mt-1 text-[14px] font-medium break-words text-ink-700">{{ $subtitle }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
        {{ $slot }}
    </div>
</section>
