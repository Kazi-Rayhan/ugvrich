<x-layouts.app :title="$category->name" :description="$category->description">

    <x-page-hero
        :eyebrow="$category->tagline"
        :title="$category->name"
        :lead="$category->description"
        :breadcrumbs="[__('site.services.breadcrumb') => route('services.index'), $category->name => null]">
        <a href="{{ route('contact') }}?area={{ $category->slug }}" class="btn-primary">
            {{ __('site.services.request_service') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
        </a>
        <a href="#services" class="btn-ghost">
            {{ __('site.services.see_included') }}
        </a>
    </x-page-hero>

    {{-- What is included --}}
    <section id="services" class="scroll-mt-32 bg-white py-20 sm:py-24">
        <div class="container-rich grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-14">

            {{-- Summary --}}
            <div class="lg:sticky lg:top-32 lg:self-start">
                <x-section-heading
                    :eyebrow="__('site.services.included_eyebrow')"
                    :title="__('site.services.included_title', ['count' => $category->services->count()])"
                    :lead="__('site.services.included_lead')" />

                <div class="reveal relative isolate mt-8 overflow-hidden rounded-[2rem] bg-brand-700 p-7 text-white">
                    <div class="pointer-events-none absolute inset-0 -z-10 text-ink-950 grid-overlay opacity-60" aria-hidden="true"></div>
                    <div class="pointer-events-none absolute -right-16 -top-16 -z-10 h-44 w-44 rounded-full bg-brand-600" aria-hidden="true"></div>

                    <div class="flex items-center gap-3.5">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white text-brand-700">
                            <x-ui-icon :name="$category->icon ?? 'grid'" class="h-5 w-5" />
                        </span>
                        <p class="font-display text-[17px] font-bold leading-snug !text-white">{{ $category->name }}</p>
                    </div>

                    <ul class="mt-6 grid grid-cols-3 gap-2 border-t border-white/15 pt-6 text-center">
                        @foreach ([
                            [$category->services->count(), __('site.services.stat_services')],
                            [$expertCount, trans_choice('site.services.stat_experts', $expertCount)],
                            [$projectCount, trans_choice('site.services.stat_projects', $projectCount)],
                        ] as [$num, $label])
                            <li class="rounded-xl bg-white/[0.07] px-2 py-3">
                                <span class="block font-numeric text-2xl font-bold tabular-nums !text-white">{{ $num }}</span>
                                <span class="mt-0.5 block text-[11.5px] text-white/70">{{ $label }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <a href="{{ route('contact') }}?area={{ $category->slug }}"
                       class="group mt-6 flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3.5 text-[14px] font-semibold text-brand-700 transition hover:bg-brand-50">
                        {{ __('site.services.request_service') }}
                        <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
                    </a>
                </div>
            </div>

            {{-- Services --}}
            <ol class="grid content-start gap-4 sm:grid-cols-2">
                @foreach ($category->services as $i => $service)
                    <li class="reveal" style="transition-delay: {{ min($i * 60, 350) }}ms">
                        <div class="group relative flex h-full flex-col overflow-hidden rounded-[1.5rem] border border-ink-100 bg-white p-6 transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] hover:-translate-y-1 hover:border-brand-200 hover:shadow-[0_24px_50px_-30px_rgba(2,34,81,0.45)]">
                            <span class="absolute inset-x-0 top-0 h-1 origin-left scale-x-0 bg-brand-600 transition-transform duration-500 group-hover:scale-x-100" aria-hidden="true"></span>
                            <span class="pointer-events-none absolute -right-2 -top-4 select-none font-display text-[5.5rem] font-bold leading-none text-ink-50 transition-colors duration-500 group-hover:text-brand-50" aria-hidden="true">
                                {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
                            </span>

                            <span class="relative flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-50 text-brand-600 transition-colors duration-300 group-hover:bg-brand-600 group-hover:text-white">
                                <x-ui-icon :name="$service->icon ?: ($category->icon ?? 'check')" class="h-5 w-5" />
                            </span>

                            <h3 class="relative mt-5 font-display text-[17px] font-bold leading-snug text-ink-950">{{ $service->name }}</h3>
                            @if ($service->description)
                                <p class="relative mt-2 text-[14px] leading-relaxed muted">{{ $service->description }}</p>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ol>
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
