<x-layouts.app :title="__('site.services.meta_title')"
               :description="__('site.services.meta_description')">

    <x-page-hero
        :eyebrow="__('site.services.hero_eyebrow')"
        :title="__('site.services.hero_title')"
        :breadcrumbs="[__('site.nav.consultancy') => null]">
        <a href="{{ route('consultancy.create') }}" class="btn-primary">
            {{ __('site.actions.request_consultancy') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
        </a>
        <a href="#services" class="btn-ghost">{{ __('site.actions.browse_catalogue') }}</a>
    </x-page-hero>

    {{-- The positioning statement. This is the page's revenue pitch, so it is
         set large, with the services marked and the clients called out. --}}
    <section class="relative isolate overflow-hidden border-b border-ink-100 bg-white py-16 sm:py-20">
        <div class="pointer-events-none absolute -left-24 top-0 -z-10 h-72 w-72 rounded-full bg-brand-100/50 blur-3xl" aria-hidden="true"></div>

        <div class="container-rich">
            <div class="reveal relative max-w-4xl border-l-4 border-brand-600 py-2 pl-7 sm:pl-10">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-brand-600">{{ __('site.services.offer_eyebrow') }}</p>

                <p class="mt-4 font-display text-[21px] font-semibold leading-[1.5] text-ink-950 sm:text-[26px] sm:leading-[1.5]">
                    {!! __('site.services.offer_statement') !!}
                </p>

                <div class="mt-9 flex flex-wrap items-center gap-x-8 gap-y-3">
                    @foreach ([['building', 'industries'], ['shield', 'government'], ['heart', 'ngos'], ['briefcase', 'businesses']] as [$icon, $label])
                        <span class="inline-flex items-center gap-2 text-[13.5px] font-medium text-ink-600">
                            <x-ui-icon :name="$icon" class="h-4 w-4 text-brand-600" /> {{ __('site.services.client_'.$label) }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ---------------- Services by consultancy area ---------------- --}}
    <section id="services" class="scroll-mt-28 bg-white py-20 sm:py-28">
        <div class="container-rich">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <x-section-heading
                    :eyebrow="__('site.services.catalogue_eyebrow')"
                    :title="__('site.services.catalogue_title', ['services' => $serviceCount, 'areas' => $categories->count()])"
                    :lead="__('site.services.catalogue_lead')" />

                <a href="{{ route('consultancy.create') }}" class="btn-primary reveal shrink-0">
                    {{ __('site.actions.request_consultancy') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                </a>
            </div>

            <div class="mt-14 space-y-16 sm:space-y-20">
                @foreach ($categories as $i => $category)
                    <section id="{{ $category->slug }}" class="reveal scroll-mt-28 overflow-hidden rounded-[2.25rem] border border-ink-100 bg-white shadow-[0_30px_70px_-60px_rgba(2,34,81,0.6)]">

                        {{-- Cover band: the photograph where the wing has one,
                             generated blueprint art otherwise, so every section
                             is distinct before a single image is uploaded. --}}
                        <header class="relative isolate overflow-hidden">
                            <x-media-frame :src="$category->image" :alt="$category->name" :seed="$category->name.' '.$category->slug"
                                           ratio="aspect-[21/9] sm:aspect-[32/9]" class="bg-navy-700" />

                            <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-navy-700 via-navy-700/80 to-navy-700/35"></div>

                            <div class="absolute inset-0 flex items-end">
                                <div class="w-full px-6 pb-6 sm:px-10 sm:pb-8">
                                    <div class="flex flex-wrap items-center gap-2.5">
                                        {{-- The wing that runs it, as the consultancy plan names it --}}
                                        @if ($category->wing_name)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-[12px] font-semibold text-white backdrop-blur-sm">
                                                <x-ui-icon name="building" class="h-3.5 w-3.5" />
                                                {{ $category->wing_name }}
                                            </span>
                                        @endif

                                        <span class="rounded-full bg-brand-600 px-3 py-1 font-numeric text-[12px] font-bold tabular-nums text-white">
                                            {{ trans_choice('site.services.service_count', $category->services->count(), ['count' => $category->services->count()]) }}
                                        </span>
                                    </div>

                                    <h2 class="mt-3 font-display text-[24px] font-bold leading-[1.15] !text-white sm:text-[34px]">{{ $category->name }}</h2>
                                </div>
                            </div>
                        </header>

                        <div class="px-6 py-8 sm:px-10 sm:py-10">
                            @if ($category->description)
                                <p class="max-w-3xl text-[15px] leading-[1.85] text-ink-700">{{ $category->description }}</p>
                            @endif

                            {{-- The services under it. Each is a page of its own. --}}
                            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($category->services as $j => $service)
                                    <a href="{{ route('services.detail', [$category, $service]) }}"
                                       class="group relative flex flex-col rounded-2xl border border-ink-100 bg-ink-50/50 p-5 transition duration-400 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1 hover:border-brand-300 hover:bg-white hover:shadow-[0_26px_54px_-40px_rgba(2,34,81,0.55)]"
                                       style="transition-delay: {{ min($j * 35, 260) }}ms">

                                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-brand-600 ring-1 ring-ink-100 transition group-hover:bg-brand-600 group-hover:text-white group-hover:ring-brand-600">
                                            <x-ui-icon :name="$service->icon ?: ($category->icon ?? 'check')" class="h-4.5 w-4.5" />
                                        </span>

                                        <h3 class="mt-4 font-display text-[15.5px] font-bold leading-snug text-ink-950">{{ $service->name }}</h3>

                                        @if ($service->description)
                                            <p class="mt-2 text-[13.5px] leading-[1.7] text-ink-600">{{ \Illuminate\Support\Str::limit($service->description, 92) }}</p>
                                        @endif

                                        <span class="mt-auto inline-flex items-center gap-1.5 pt-4 text-[12.5px] font-semibold text-brand-700 transition group-hover:gap-2.5">
                                            {{ __('site.services.explore_service') }}
                                            <x-ui-icon name="arrow-right" class="h-3.5 w-3.5" />
                                        </span>
                                    </a>
                                @endforeach
                            </div>

                            <div class="mt-8 flex flex-wrap items-center gap-3 border-t border-ink-100 pt-6">
                                <a href="{{ route('services.show', $category) }}" class="btn-ghost">
                                    {{ __('site.services.area_detail') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                                </a>
                                <a href="{{ route('consultancy.create') }}" class="text-[13.5px] font-semibold text-brand-700 transition hover:text-brand-600">
                                    {{ __('site.actions.request_consultancy') }}
                                </a>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </section>

</x-layouts.app>
