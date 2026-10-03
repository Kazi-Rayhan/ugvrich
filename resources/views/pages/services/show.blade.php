<x-layouts.app :title="$category->name" :description="$category->description">

    <x-page-hero
        :eyebrow="$category->tagline"
        :title="$category->name"
        :lead="$category->description"
        :breadcrumbs="[__('site.services.breadcrumb') => route('services.index'), $category->name => null]">
        <a href="{{ route('consultancy.create', ['area' => $category->slug]) }}" class="btn-primary">
            {{ __('site.services.request_service') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
        </a>
        <a href="#services" class="btn-ghost">
            {{ __('site.services.see_included') }}
        </a>
    </x-page-hero>

    {{-- ---------------- About this service ----------------

         The layout the client asked for: the heading and the numbered services
         flow across a four-column grid while one large image is anchored to the
         top right, so the last of the services wrap underneath it rather than
         leaving a column of white space beside it.

         It is a dense grid rather than a fixed arrangement, so it holds whether
         a service has two sub-services or nine — the image keeps its corner and
         the items fill in around it. --}}
    <section id="services" class="scroll-mt-32 bg-white py-20 sm:py-24">
        <div class="container-rich">
            <div class="grid gap-x-10 gap-y-10 lg:grid-cols-4 lg:[grid-auto-flow:dense]">

                {{-- Heading: the left half of the first row --}}
                <div class="reveal lg:col-span-2">
                    <p class="text-[11.5px] font-bold uppercase tracking-[0.18em] text-brand-600">
                        <span class="text-brand-300">//</span> {{ __('site.services.included_eyebrow') }}
                    </p>

                    <h2 class="mt-4 font-display text-[30px] font-bold leading-[1.1] tracking-tight text-ink-950 sm:text-[42px]">
                        {{ $category->name }}
                    </h2>

                    @if ($category->sector_name)
                        <p class="mt-4 inline-flex items-center gap-2 rounded-full border border-ink-200 px-3.5 py-1.5 text-[12.5px] text-ink-600">
                            <x-ui-icon name="building" class="h-3.5 w-3.5 text-brand-600" />
                            <span class="font-semibold text-ink-800">{{ __('site.services.sector') }}:</span>
                            {{ $category->sector_name }}
                        </p>
                    @endif
                </div>

                {{-- The image (or a video in its place) holds the top-right corner across two rows --}}
                <div class="reveal lg:col-span-2 lg:col-start-3 lg:row-span-2 lg:row-start-1">
                    @if ($category->video)
                        {{-- Muted and looped. With reduced motion it stays on its first frame. --}}
                        <div class="relative aspect-[16/11] h-full overflow-hidden rounded-[1.5rem] bg-brand-50">
                            <video class="h-full w-full object-cover"
                                   autoplay muted loop playsinline preload="auto"
                                   @if ($category->image) poster="{{ Storage::url($category->image) }}" @endif
                                   x-data x-init="if (matchMedia('(prefers-reduced-motion: reduce)').matches) { $el.removeAttribute('autoplay'); $el.pause() }"
                                   aria-label="{{ __('site.projects.video_title', ['title' => $category->name]) }}">
                                <source src="{{ Storage::url($category->video) }}"
                                        type="{{ str_ends_with(strtolower($category->video), '.webm') ? 'video/webm' : 'video/mp4' }}">
                            </video>
                        </div>
                    @elseif ($category->image)
                        <x-media-frame :src="$category->image" :alt="$category->name"
                                       ratio="aspect-[16/11]" class="h-full rounded-[1.5rem] bg-brand-50" />
                    @else
                        {{-- No photograph yet: the sector's own drawn poster. --}}
                        <div class="aspect-[16/11] h-full overflow-hidden rounded-[1.5rem]">
                            <x-service-poster :sector="$category->department" :seed="$category->slug" class="h-full w-full object-cover" />
                        </div>
                    @endif
                </div>

                {{-- The services, numbered, filling every cell the image leaves --}}
                @foreach ($category->services as $i => $service)
                    <article class="reveal group" style="transition-delay: {{ min($i * 45, 300) }}ms">
                        <p class="font-numeric text-[15px] font-bold tabular-nums text-brand-600">
                            {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}.
                        </p>

                        <h3 class="mt-3 font-display text-[17px] font-bold leading-snug text-ink-950">
                            <a href="{{ route('services.detail', [$category, $service]) }}" class="transition-colors group-hover:text-brand-700">
                                {{ $service->name }}
                            </a>
                        </h3>

                        @if ($service->description)
                            <p class="mt-2.5 text-[14px] leading-[1.9] text-ink-600">{{ $service->description }}</p>
                        @endif

                        <a href="{{ route('services.detail', [$category, $service]) }}"
                           class="mt-3 inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-brand-700 transition-all group-hover:gap-2.5">
                            {{ __('site.services.explore_service') }}
                            <x-ui-icon name="arrow-right" class="h-3.5 w-3.5" />
                        </a>
                    </article>
                @endforeach
            </div>

            {{-- What this area can be asked for --}}
            <div class="reveal mt-14 flex flex-wrap items-center gap-4 border-t border-ink-100 pt-8">
                <a href="{{ route('consultancy.create', ['area' => $category->slug]) }}" class="btn-primary">
                    {{ __('site.services.request_service') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                </a>
                <a href="{{ route('services.index') }}" class="btn-ghost">
                    {{ __('site.services.others_link') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                </a>

                @if ($expertCount || $projectCount)
                    <p class="text-[13px] text-ink-500">
                        @if ($expertCount)
                            <span class="font-semibold text-ink-800">{{ $expertCount }}</span> {{ trans_choice('site.services.stat_experts', $expertCount) }}
                        @endif
                        @if ($expertCount && $projectCount) · @endif
                        @if ($projectCount)
                            <span class="font-semibold text-ink-800">{{ $projectCount }}</span> {{ trans_choice('site.services.stat_projects', $projectCount) }}
                        @endif
                    </p>
                @endif
            </div>
        </div>
    </section>

    {{-- Experts in this area --}}
    @if ($experts->isNotEmpty())
        <section class="border-t hairline py-20 bg-ink-50">
            <div class="container-rich">
                <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                    <x-section-heading
                        :eyebrow="__('site.services.experts_eyebrow')"
                        :title="__('site.services.experts_title')"
                        :lead="__('site.services.experts_lead')" />
                    <a href="{{ route('experts.index') }}?area={{ $category->slug }}" class="btn-ghost reveal shrink-0">
                        {{ __('site.services.experts_link') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>

                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($experts as $i => $expert)
                        <x-cards.expert-profile-card :expert="$expert" :index="$i" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Projects in this area --}}
    @if ($projects->isNotEmpty())
        <section class="py-20 bg-white">
            <div class="container-rich">
                <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                    <x-section-heading
                        :eyebrow="__('site.services.work_eyebrow')"
                        :title="__('site.services.work_title')" />
                    <a href="{{ route('projects.index') }}?area={{ $category->slug }}" class="btn-ghost reveal shrink-0">
                        {{ __('site.actions.all_projects') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>

                <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $i => $project)
                        <x-cards.project-card :project="$project" :index="$i" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Other areas --}}
    @if ($siblings->isNotEmpty())
        <section class="border-t border-ink-100 bg-ink-50 py-20 sm:py-24">
            <div class="container-rich">
                <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                    <x-section-heading
                        :eyebrow="__('site.services.others_eyebrow')"
                        :title="__('site.services.others_title')" />
                    <a href="{{ route('services.index') }}" class="btn-ghost reveal shrink-0">
                        {{ __('site.services.others_link') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>

                <div class="mt-12 grid gap-6 sm:grid-cols-2">
                    @foreach ($siblings as $i => $sibling)
                        <x-cards.service-card :category="$sibling" :index="$i" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</x-layouts.app>
