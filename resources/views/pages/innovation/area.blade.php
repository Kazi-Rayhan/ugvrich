<x-layouts.app :title="$area->name" :description="$area->description">

    @php
        // Numbers the page counts out are written in the digits of the language being read.
        $num = fn ($value) => \App\Support\Numerals::localize((string) $value);
    @endphp

    {{-- One innovation area on a page of its own, laid out the way a service
         category page is: the banner, what the area covers beside its picture,
         the projects running in it, and the other areas. --}}
    <x-page-hero
        :eyebrow="__('site.innovation.hero_eyebrow')"
        :title="$area->name"
        :lead="$area->summary"
        :image="$area->hero_image"
        :video="$area->hero_video"
        :breadcrumbs="[__('site.nav.innovation') => route('innovation.index'), $area->name => null]">
        <a href="{{ route('ideas.create') }}" class="btn-primary">
            {{ __('site.actions.submit_idea') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
        </a>
        <a href="#focus" class="btn-ghost">
            {{ __('site.innovation.see_focus') }}
        </a>
    </x-page-hero>

    {{-- ---------------- What this area covers ---------------- --}}
    <section id="focus" class="scroll-mt-32 bg-white py-20 sm:py-24">
        <div class="container-rich">
            <div class="grid gap-x-10 gap-y-10 lg:grid-cols-4 lg:[grid-auto-flow:dense]">

                {{-- Heading: the left half of the first row --}}
                <div class="reveal lg:col-span-2">
                    <p class="text-[11.5px] font-bold uppercase tracking-[0.18em] text-brand-600">
                        <span class="text-brand-300">//</span> {{ __('site.innovation.focus_eyebrow') }}
                    </p>

                    <h2 class="mt-4 font-display text-[30px] font-bold leading-[1.1] tracking-tight text-ink-950 sm:text-[42px]">
                        {{ $area->name }}
                    </h2>

                    <div class="mt-4 flex flex-wrap items-center gap-2.5">

                        <span class="rounded-full bg-brand-50 px-3 py-1.5 font-numeric text-[12.5px] font-bold tabular-nums text-brand-700">
                            {{ trans_choice('site.innovation.innovation_count', $area->innovations->count(), ['count' => $num($area->innovations->count())]) }}
                        </span>
                    </div>

                    @if ($area->description)
                        <p class="mt-5 text-[15px] leading-[1.9] text-ink-600">{{ $area->description }}</p>
                    @endif
                </div>

                {{-- The image (or a video in its place) holds the top-right corner across two rows --}}
                <div class="reveal lg:col-span-2 lg:col-start-3 lg:row-span-2 lg:row-start-1">
                    @if ($area->video)
                        {{-- Muted and looped. With reduced motion it stays on its first frame. --}}
                        <div class="relative aspect-[16/11] h-full overflow-hidden rounded-[1.5rem] bg-brand-50">
                            <video class="h-full w-full object-cover"
                                   autoplay muted loop playsinline preload="auto"
                                   @if ($area->image) poster="{{ Storage::url($area->image) }}" @endif
                                   x-data x-init="if (matchMedia('(prefers-reduced-motion: reduce)').matches) { $el.removeAttribute('autoplay'); $el.pause() }"
                                   aria-label="{{ __('site.projects.video_title', ['title' => $area->name]) }}">
                                <source src="{{ Storage::url($area->video) }}"
                                        type="{{ str_ends_with(strtolower($area->video), '.webm') ? 'video/webm' : 'video/mp4' }}">
                            </video>
                        </div>
                    @elseif ($area->image)
                        <x-media-frame :src="$area->image" :alt="$area->name"
                                       ratio="aspect-[16/11]" class="h-full rounded-[1.5rem] bg-brand-50" />
                    @else
                        {{-- No photograph yet: the department's own drawn poster. --}}
                        <div class="aspect-[16/11] h-full overflow-hidden rounded-[1.5rem]">
                            <x-service-poster :sector="$area->department" :seed="$area->slug" class="h-full w-full object-cover" />
                        </div>
                    @endif
                </div>

                {{-- The innovations, as small cards (photo, type, name), filling every
                     cell the image leaves, as a service category lists its services. --}}
                @foreach ($area->innovations as $j => $innovation)
                    <x-cards.innovation-mini :innovation="$innovation" :slug="$innovationSlugs[$innovation->id] ?? null" :index="$j" />
                @endforeach
            </div>

            {{-- What this area can be asked for --}}
            <div class="reveal mt-14 flex flex-wrap items-center gap-4 border-t border-ink-100 pt-8">
                <a href="{{ route('ideas.create') }}" class="btn-primary">
                    {{ __('site.actions.submit_idea') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                </a>
                <a href="{{ route('innovation.index') }}#areas" class="btn-ghost">
                    {{ __('site.innovation.others_link') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                </a>

                <p class="text-[13px] text-ink-500">
                    {{ trans_choice('site.innovation.project_count', $projects->count(), ['count' => $num($projects->count())]) }}
                </p>
            </div>
        </div>
    </section>

    {{-- ---------------- Projects in this area ---------------- --}}
    @if ($projects->isNotEmpty())
        <section class="border-t hairline bg-ink-50 py-20">
            <div class="container-rich">
                <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                    <x-section-heading
                        :eyebrow="__('site.innovation.projects_eyebrow')"
                        :title="__('site.innovation.projects_title')" />
                    <a href="{{ route('projects.index') }}" class="btn-ghost reveal shrink-0">
                        {{ __('site.actions.all_projects') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>

                <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($projects as $i => $project)
                        <x-cards.project-image-card :project="$project" :index="$i" class="min-h-[24rem]" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ---------------- Other areas ---------------- --}}
    @if ($siblings->isNotEmpty())
        <section class="border-t border-ink-100 bg-white py-20 sm:py-24">
            <div class="container-rich">
                <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                    <x-section-heading
                        :eyebrow="__('site.innovation.others_eyebrow')"
                        :title="__('site.innovation.others_title')" />
                    <a href="{{ route('innovation.index') }}#areas" class="btn-ghost reveal shrink-0">
                        {{ __('site.innovation.others_link') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                </div>

                <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($siblings as $i => $sibling)
                        <a href="{{ route('innovation.area', $sibling) }}"
                           class="reveal group flex flex-col rounded-[1.5rem] border border-ink-100 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-brand-200 hover:shadow-[0_22px_44px_-30px_rgba(7,20,38,0.45)] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500"
                           style="transition-delay: {{ min($i * 60, 300) }}ms">
                            <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand-600 ring-1 ring-brand-100 transition group-hover:bg-brand-600 group-hover:text-white">
                                <x-ui-icon :name="$sibling->icon ?? 'lightbulb'" class="h-5 w-5" />
                            </span>
                            <span class="mt-5 font-display text-[16.5px] font-bold leading-snug text-ink-950 transition-colors group-hover:text-brand-700">{{ $sibling->name }}</span>
                            @if ($sibling->summary)
                                <span class="mt-1.5 text-[13px] leading-snug text-ink-500">{{ $sibling->summary }}</span>
                            @endif
                            <span class="mt-auto inline-flex items-center gap-1.5 pt-5 text-[13px] font-semibold text-brand-700">
                                {{ __('site.innovation.area_detail') }}
                                <x-ui-icon name="arrow-right" class="h-3.5 w-3.5 transition-transform duration-300 group-hover:translate-x-1" />
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</x-layouts.app>
