<x-layouts.app :title="__('site.services.meta_title')"
               :description="__('site.services.meta_description')">

    @php($heroVideo = $site->get('services_video'))

    {{-- Hero, in the same voice as the Research page: navy, a pill eyebrow and
         the highlighted phrase. A video from Site Settings plays behind it. --}}
    <section class="relative isolate overflow-hidden bg-navy-700 text-white">
        @if ($heroVideo)
            {{-- Muted, looped and decorative. With reduced motion it stays on its first frame. --}}
            <video class="pointer-events-none absolute inset-0 -z-20 h-full w-full object-cover"
                   autoplay muted loop playsinline preload="auto"
                   x-data x-init="if (matchMedia('(prefers-reduced-motion: reduce)').matches) { $el.removeAttribute('autoplay'); $el.pause() }"
                   aria-hidden="true" tabindex="-1">
                <source src="{{ Storage::url($heroVideo) }}" type="{{ str_ends_with(strtolower($heroVideo), '.webm') ? 'video/webm' : 'video/mp4' }}">
            </video>
            <div class="pointer-events-none absolute inset-0 -z-10 bg-navy-900/70" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-0 -z-10 bg-gradient-to-r from-navy-900/90 via-navy-800/60 to-transparent" aria-hidden="true"></div>
        @else
            <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.15]" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -right-40 -top-48 -z-10 h-[34rem] w-[34rem] rounded-full bg-brand-600/35 blur-[130px]" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-52 -left-32 -z-10 h-[30rem] w-[30rem] rounded-full border border-white/10" aria-hidden="true"></div>
        @endif

        <div class="container-rich py-16 sm:py-20">
            <p class="reveal eyebrow-invert">{{ __('site.services.hero_eyebrow') }}</p>

            <h1 class="reveal mt-7 max-w-4xl font-display text-[38px] font-bold leading-[1.05] tracking-[-0.03em] !text-white sm:text-[58px]">
                {!! __('site.services.hero_title') !!}
            </h1>

            <p class="reveal mt-7 max-w-2xl text-[17.5px] leading-[1.8] text-white/75">{{ __('site.services.hero_lead') }}</p>

            <div class="reveal mt-10 flex flex-wrap gap-3">
                <a href="{{ route('consultancy.create') }}" class="btn-lead group">
                    <span class="relative">{{ __('site.actions.request_consultancy') }}</span>
                    <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                </a>
                <a href="#services" class="btn-invert px-7 py-4 text-[15px]">
                    {{ __('site.actions.browse_catalogue') }}
                </a>
            </div>
        </div>
    </section>

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

            {{-- Each sector's service, laid out the way the client asked for:
                 the heading and the numbered sub-services flow across a
                 four-column grid while one large image holds the top-right
                 corner, so the last of them wrap underneath it instead of
                 leaving a column of white space beside it.

                 A dense grid rather than a fixed arrangement, because the
                 sectors are not the same size — Smart ICT has nine
                 sub-services, Language Services has two. --}}
            <div class="mt-14 space-y-20 sm:space-y-28">
                @foreach ($categories as $i => $category)
                    <section id="{{ $category->slug }}" class="scroll-mt-28 border-t border-ink-100 pt-12 first:border-0 first:pt-0">
                        <div class="grid gap-x-10 gap-y-10 lg:grid-cols-4 lg:[grid-auto-flow:dense]">

                            {{-- Heading: the left half of the first row --}}
                            <div class="reveal lg:col-span-2">
                                <p class="text-[11.5px] font-bold uppercase tracking-[0.18em] text-brand-600">
                                    <span class="text-brand-300">//</span> {{ __('site.services.included_eyebrow') }}
                                </p>

                                <h2 class="mt-4 font-display text-[28px] font-bold leading-[1.1] tracking-tight text-ink-950 sm:text-[40px]">
                                    <a href="{{ route('services.show', $category) }}" class="transition-colors hover:text-brand-700">{{ $category->name }}</a>
                                </h2>

                                <div class="mt-4 flex flex-wrap items-center gap-2.5">
                                    @if ($category->sector_name)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-ink-200 px-3.5 py-1.5 text-[12.5px] text-ink-600">
                                            <x-ui-icon name="building" class="h-3.5 w-3.5 text-brand-600" />
                                            <span class="font-semibold text-ink-800">{{ __('site.services.sector') }}:</span>
                                            {{ $category->sector_name }}
                                        </span>
                                    @endif

                                    <span class="rounded-full bg-brand-50 px-3 py-1.5 font-numeric text-[12.5px] font-bold tabular-nums text-brand-700">
                                        {{ trans_choice('site.services.service_count', $category->services->count(), ['count' => $category->services->count()]) }}
                                    </span>
                                </div>

                                @if ($category->description)
                                    <p class="mt-5 text-[14.5px] leading-[1.9] text-ink-600">{{ $category->description }}</p>
                                @endif
                            </div>

                            {{-- The image holds the top-right corner across two rows --}}
                            <div class="reveal lg:col-span-2 lg:col-start-3 lg:row-span-2 lg:row-start-1">
                                @if ($category->image)
                                    <x-media-frame :src="$category->image" :alt="$category->name"
                                                   ratio="aspect-[16/11]" class="h-full rounded-[1.5rem] bg-brand-50" />
                                @else
                                    {{-- No photograph yet: the sector's own drawn poster. --}}
                                    <div class="aspect-[16/11] h-full overflow-hidden rounded-[1.5rem]">
                                        <x-service-poster :sector="$category->department" :seed="$category->slug" class="h-full w-full object-cover" />
                                    </div>
                                @endif
                            </div>

                            {{-- The sub-services, numbered, filling every cell the image leaves --}}
                            @foreach ($category->services as $j => $service)
                                <article class="reveal group" style="transition-delay: {{ min($j * 40, 280) }}ms">
                                    <p class="font-numeric text-[15px] font-bold tabular-nums text-brand-600">
                                        {{ str_pad($j + 1, 2, '0', STR_PAD_LEFT) }}.
                                    </p>

                                    <h3 class="mt-3 font-display text-[16.5px] font-bold leading-snug text-ink-950">
                                        <a href="{{ route('services.detail', [$category, $service]) }}" class="transition-colors group-hover:text-brand-700">
                                            {{ $service->name }}
                                        </a>
                                    </h3>

                                    @if ($service->description)
                                        <p class="mt-2.5 text-[13.5px] leading-[1.9] text-ink-600">{{ $service->description }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>

                        <div class="reveal mt-10 flex flex-wrap items-center gap-4">
                            <a href="{{ route('services.show', $category) }}" class="btn-ghost">
                                {{ __('site.services.area_detail') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                            </a>
                            <a href="{{ route('consultancy.create') }}" class="text-[13.5px] font-semibold text-brand-700 transition hover:text-brand-600">
                                {{ __('site.actions.request_consultancy') }}
                            </a>
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </section>

</x-layouts.app>
