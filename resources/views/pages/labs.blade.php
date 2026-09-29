<x-layouts.app :title="__('site.nav.labs')"
               :description="__('site.labs.meta_description')">

    <x-page-hero
        :eyebrow="__('site.labs.hero_eyebrow')"
        :title="__('site.labs.hero_title')"
        :lead="__('site.labs.hero_lead')"
        :breadcrumbs="[__('site.nav.labs') => null]">
        <a href="{{ route('consultancy.create') }}" class="btn-primary">
            {{ __('site.actions.request_consultancy') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
        </a>
    </x-page-hero>

    @php
        $departments = $facilities->pluck('department')->filter()->unique()->values();
        $bookable = $facilities->where('is_bookable', true)->count();
        $equipmentCount = $facilities->sum(fn ($facility) => count($facility->equipment ?? []));
    @endphp

    {{-- ---------------- At a glance ---------------- --}}
    <section class="border-b border-ink-100 bg-white py-10">
        <div class="container-rich grid gap-px overflow-hidden rounded-2xl border border-ink-100 bg-ink-100 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                [$facilities->count(), 'facilities', 'beaker'],
                [$departments->count(), 'departments', 'building'],
                [$equipmentCount, 'instruments', 'cog'],
                [$bookable, 'bookable', 'handshake'],
            ] as [$value, $label, $icon])
                <div class="flex items-center gap-4 bg-white px-6 py-5">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                        <x-ui-icon :name="$icon" class="h-5 w-5" />
                    </span>
                    <div>
                        <p class="font-numeric text-[26px] font-bold leading-none tabular-nums text-ink-950">{{ $value }}</p>
                        <p class="mt-1.5 text-[12.5px] leading-snug muted">{{ __('site.labs.count_'.$label) }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ---------------- The facilities ---------------- --}}
    <section class="bg-white py-16 sm:py-20">
        <div class="container-rich">
            <x-section-heading
                :eyebrow="__('site.labs.list_eyebrow')"
                :title="__('site.labs.list_title')"
                :lead="__('site.labs.list_lead')" />

            <div class="mt-12 grid gap-6 lg:grid-cols-2">
                @foreach ($facilities as $i => $facility)
                    <article class="reveal" style="transition-delay: {{ min($i * 45, 270) }}ms">
                        <div class="group relative flex h-full flex-col overflow-hidden rounded-[1.75rem] bg-white ring-1 ring-ink-200/70
                                    shadow-[0_2px_4px_-2px_rgba(7,20,38,0.08),0_12px_28px_-20px_rgba(7,20,38,0.35)]
                                    transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]
                                    hover:-translate-y-1 hover:ring-brand-200 hover:shadow-[0_8px_16px_-8px_rgba(7,20,38,0.1),0_36px_70px_-40px_rgba(2,34,81,0.45)]">

                            {{-- Cover: the facility photograph, or generated blueprint art --}}
                            <div class="relative">
                                <x-media-frame :src="$facility->image" :alt="$facility->name" :seed="$facility->slug"
                                               ratio="aspect-[16/6]" class="bg-ink-100" />

                                <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-white via-white/20 to-transparent"></div>

                                @if ($facility->department)
                                    <span class="absolute right-5 top-5 rounded-lg bg-navy-700/95 px-2.5 py-1 font-display text-[11px] font-bold tracking-[0.08em] text-white backdrop-blur-sm">
                                        {{ $facility->department }}
                                    </span>
                                @endif

                                <span class="absolute -bottom-7 left-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-brand-600 shadow-[0_10px_24px_-12px_rgba(7,20,38,0.5)] ring-1 ring-ink-100 transition duration-500 group-hover:bg-brand-600 group-hover:text-white group-hover:ring-brand-600">
                                    <x-ui-icon :name="$facility->icon ?? 'beaker'" class="h-6 w-6" />
                                </span>

                                <span class="absolute bottom-4 right-6 font-numeric text-[12px] font-bold tabular-nums text-ink-300">
                                    {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>

                            {{-- Title --}}
                            <div class="px-6 pt-11">
                                <h2 class="font-display text-[19px] font-bold leading-snug tracking-tight text-ink-950">{{ $facility->name }}</h2>
                                @if ($facility->department_name)
                                    <p class="mt-1 text-[12.5px] font-medium uppercase tracking-[0.12em] text-ink-400">{{ $facility->department_name }}</p>
                                @endif

                                @if ($facility->description)
                                    <p class="mt-4 text-[13.5px] leading-relaxed muted">{{ $facility->description }}</p>
                                @endif
                            </div>

                            {{-- Equipment and services, divided --}}
                            <div class="mt-6 grid flex-1 divide-y divide-ink-100 border-t border-ink-100 sm:grid-cols-2 sm:divide-x sm:divide-y-0">
                                @foreach ([['equipment', $facility->equipment, 'cog'], ['available_for', $facility->services, 'check']] as [$label, $items, $icon])
                                    <div class="px-6 py-5">
                                        <p class="flex items-center gap-2 text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">
                                            <x-ui-icon :name="$icon" class="h-3.5 w-3.5 text-brand-600" /> {{ __('site.labs.'.$label) }}
                                        </p>

                                        @if ($items)
                                            <ul class="mt-3 space-y-2">
                                                @foreach ($items as $item)
                                                    <li class="flex items-start gap-2 text-[13px] leading-snug text-ink-700">
                                                        <span class="mt-[7px] h-1 w-1 shrink-0 rounded-full bg-brand-400"></span>
                                                        {{ $item }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="mt-3 text-[13px] muted">--</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            {{-- Footer --}}
                            <div class="flex flex-wrap items-center gap-4 border-t border-ink-100 bg-ink-50/60 px-6 py-4 text-[12.5px]">
                                @if ($facility->is_bookable)
                                    <span class="inline-flex items-center gap-1.5 font-semibold text-brand-700">
                                        <span class="relative flex h-1.5 w-1.5">
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-brand-500 opacity-60"></span>
                                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-brand-600"></span>
                                        </span>
                                        {{ __('site.labs.open_external') }}
                                    </span>
                                @endif
                                @if ($facility->location)
                                    <span class="inline-flex items-center gap-1.5 muted">
                                        <x-ui-icon name="map-pin" class="h-3.5 w-3.5" /> {{ $facility->location }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            @if ($facilities->isEmpty())
                <p class="mt-8 rounded-2xl border-2 border-dashed border-ink-200 p-8 text-center text-[14.5px] muted">
                    {{ __('site.labs.empty') }}
                </p>
            @endif
        </div>
    </section>

    {{-- ---------------- Commissioning work ---------------- --}}
    <section class="border-t border-ink-100 bg-ink-50 py-16 sm:py-20">
        <div class="container-rich grid gap-12 lg:grid-cols-[0.95fr_1.05fr] lg:gap-16">
            <div>
                <x-section-heading
                    :eyebrow="__('site.labs.commission_eyebrow')"
                    :title="__('site.labs.commission_title')"
                    :lead="__('site.labs.commission_lead')" />

                <a href="{{ route('consultancy.create') }}" class="btn-primary reveal mt-8">
                    {{ __('site.actions.request_consultancy') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                </a>
            </div>

            <ol class="grid gap-3 sm:grid-cols-2">
                @foreach ([
                    ['document', 'tell'],
                    ['users', 'scope'],
                    ['target', 'quote'],
                    ['check', 'report'],
                ] as $i => [$icon, $step])
                    <li class="reveal rounded-2xl border border-ink-100 bg-white p-5" style="transition-delay: {{ min($i * 60, 300) }}ms">
                        <div class="flex items-center justify-between">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                <x-ui-icon :name="$icon" class="h-4.5 w-4.5" />
                            </span>
                            <span class="font-numeric text-[12px] font-bold tabular-nums text-ink-300">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <p class="mt-4 font-display text-[15px] font-semibold text-ink-950">{{ __('site.labs.step_'.$step) }}</p>
                        <p class="mt-1.5 text-[13px] leading-relaxed muted">{{ __('site.labs.step_'.$step.'_note') }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
</x-layouts.app>
