<x-layouts.app :title="__('site.innovation.areas_title')"
               :description="__('site.innovation.areas_lead')">

    <x-page-hero
        :eyebrow="__('site.innovation.hero_eyebrow')"
        :title="__('site.innovation.areas_title')"
        :lead="__('site.innovation.areas_lead')"
        image="projects/sun-car.jpg"
        :breadcrumbs="[__('site.nav.innovation') => route('innovation.index'), __('site.innovation.areas_title') => null]">
        <a href="{{ route('ideas.create') }}" class="btn-primary group">
            <x-ui-icon name="lightbulb" class="h-4 w-4" /> {{ __('site.actions.submit_idea') }}
        </a>
        <a href="#areas" class="btn-ghost">{{ __('site.innovation.explore_areas') }}</a>
    </x-page-hero>

    {{-- Areas as tabs, with the selected area's projects below --}}
    <section id="areas" class="scroll-mt-28 bg-white py-16 sm:py-20">
        <div class="container-rich">
            {{-- Tab bar. Every tab is a link, and they wrap into rows rather
                 than scrolling sideways, so the whole set is always in view. --}}
            <div class="reveal flex flex-wrap gap-1.5 rounded-[1.25rem] border border-ink-200 bg-ink-50 p-1.5"
                 role="tablist" aria-label="{{ __('site.innovation.tablist') }}">

                <a href="{{ route('innovation.areas') }}#areas" role="tab"
                   aria-selected="{{ $activeArea ? 'false' : 'true' }}"
                   @class(['area-tab', 'is-on' => ! $activeArea])>
                    <x-ui-icon name="grid" class="h-4 w-4 shrink-0" />
                    {{ __('site.innovation.all_areas') }}
                    <span class="area-tab-count">{{ $areas->sum('projects_count') }}</span>
                </a>

                @foreach ($areas as $area)
                    @php $on = $activeArea?->is($area); @endphp
                    <a href="{{ route('innovation.areas', ['area' => $area->slug]) }}#areas" role="tab"
                       aria-selected="{{ $on ? 'true' : 'false' }}"
                       title="{{ $area->name }}"
                       @class(['area-tab', 'is-on' => $on])>
                        @if ($area->department)
                            <span class="area-tab-dept">{{ $area->department }}</span>
                        @endif
                        {{ \Illuminate\Support\Str::of($area->name)->replaceEnd(' Innovation', '') }}
                        <span class="area-tab-count">{{ $area->projects_count }}</span>
                    </a>
                @endforeach
            </div>

            {{-- What the selected area covers --}}
            @if ($activeArea)
                <div class="reveal mt-8 rounded-[1.75rem] border border-ink-100 bg-ink-50 p-6 sm:p-8">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-600 text-white">
                            <x-ui-icon :name="$activeArea->icon ?? 'lightbulb'" class="h-5 w-5" />
                        </span>
                        <div>
                            <h3 class="mt-2 font-display text-[20px] font-bold leading-tight text-ink-950">{{ $activeArea->name }}</h3>
                        </div>
                    </div>

                    @if ($activeArea->description)
                        <p class="mt-5 max-w-3xl text-[15px] leading-relaxed muted">{{ $activeArea->description }}</p>
                    @endif

                </div>
            @endif

            {{-- Projects in the selected area --}}
            @if ($projects->isEmpty())
                <div class="mt-10 rounded-[2rem] border-2 border-dashed border-ink-200 bg-white px-6 py-14 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                        <x-ui-icon name="lightbulb" class="h-6 w-6" />
                    </span>
                    <p class="mt-5 font-display text-lg font-bold text-ink-950">{{ __('site.innovation.empty_title') }}</p>
                    <p class="mx-auto mt-2 max-w-md text-[14.5px] muted">{{ __('site.innovation.empty_body') }}</p>
                    <a href="{{ route('ideas.create') }}" class="btn-primary mt-6">{{ __('site.actions.submit_idea') }}</a>
                </div>
            @else
                <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $i => $project)
                        <x-cards.project-image-card :project="$project" :index="$i" class="min-h-[24rem]" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Idea CTA --}}
    {{-- <section class="bg-white py-20 sm:py-24">
        <div class="container-rich">
            <div class="reveal relative isolate overflow-hidden rounded-[2.5rem] bg-ink-950 px-7 py-14 text-center sm:px-14 sm:py-16">
                <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-20" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -right-24 -top-24 -z-10 h-72 w-72 rounded-full bg-brand-600/60" aria-hidden="true"></div>

                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-600 text-white">
                    <x-ui-icon name="rocket" class="h-6 w-6" />
                </span>
                <h2 class="mx-auto mt-6 max-w-2xl font-display text-3xl font-bold leading-tight !text-white sm:text-[40px]">From Idea to Enterprise</h2>
                <p class="mx-auto mt-4 max-w-xl text-[16px] leading-relaxed text-white/70">
                    Students and faculty can submit an innovation idea. The Innovation Wing evaluates it, connects mentors and supports it from prototype to market.
                </p>
                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('ideas.create') }}" class="btn-invert">Submit Your Idea <x-ui-icon name="arrow-up-right" class="h-4 w-4" /></a>
                    <a href="{{ route('projects.index') }}" class="btn border border-white/30 text-white hover:bg-white/10">All projects</a>
                </div>
            </div>
        </div>
    </section> --}}
</x-layouts.app>
