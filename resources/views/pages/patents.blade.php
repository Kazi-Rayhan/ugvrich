<x-layouts.app :title="__('site.patents.meta_title')"
               :description="__('site.patents.meta_description')">

    <x-page-hero
        :eyebrow="__('site.nav.patents')"
        :title="__('site.patents.hero_title')"
        :lead="__('site.patents.hero_lead')"
        :breadcrumbs="[__('site.nav.patents') => null]">
        <a href="{{ route('consultancy.create') }}" class="btn-primary">
            {{ __('site.patents.licensing_enquiry') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
        </a>
    </x-page-hero>

    {{-- ---------------- The register ---------------- --}}
    @foreach ($groups as $index => $group)
        <section id="{{ $group['key'] }}"
                 @class(['scroll-mt-28 py-14 sm:py-16', 'bg-white' => $index % 2 === 0, 'border-y border-ink-100 bg-ink-50' => $index % 2 === 1])>
            <div class="container-rich">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-4">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-brand-600 text-white">
                            <x-ui-icon :name="$group['icon']" class="h-5 w-5" />
                        </span>
                        <div>
                            <h2 class="font-display text-[22px] font-bold leading-tight text-ink-950 sm:text-[26px]">{{ $group['label'] }}</h2>
                            <p class="mt-1.5 text-[14px] muted">{{ $group['text'] }}</p>
                        </div>
                    </div>

                    <span class="shrink-0 self-start rounded-full border border-ink-200 bg-white px-3.5 py-1.5 text-[13px] font-semibold text-ink-600 sm:self-auto">
                        {{ trans_choice('site.patents.project_count', $group['items']->count(), ['count' => $group['items']->count()]) }}
                    </span>
                </div>

                @if ($group['items']->isEmpty())
                    <p class="mt-7 rounded-2xl border-2 border-dashed border-ink-200 px-6 py-8 text-center text-[14px] muted">
                        {{ __('site.patents.group_empty') }}
                    </p>
                @else
                    <div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($group['items'] as $i => $project)
                            <a href="{{ route('projects.show', $project) }}"
                               class="group reveal flex h-full flex-col overflow-hidden rounded-[1.5rem] bg-white ring-1 ring-ink-200/70
                                      shadow-[0_2px_4px_-2px_rgba(7,20,38,0.08),0_12px_28px_-20px_rgba(7,20,38,0.35)]
                                      transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)]
                                      hover:-translate-y-1 hover:ring-brand-200 hover:shadow-[0_8px_16px_-8px_rgba(7,20,38,0.1),0_36px_70px_-40px_rgba(2,34,81,0.45)]"
                               style="transition-delay: {{ min($i * 60, 300) }}ms">

                                {{-- Photograph --}}
                                <div class="relative">
                                    <x-media-frame :src="$project->image" :alt="$project->title" :seed="$project->slug"
                                                   ratio="aspect-[4/3]" class="bg-ink-100" />

                                    <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-ink-950/70 via-ink-950/10 to-transparent"></div>

                                    <span class="absolute left-5 top-5 flex flex-wrap items-center gap-2">
                                        @if ($project->department)
                                            <span class="rounded-md bg-navy-700/95 px-2 py-0.5 font-display text-[10.5px] font-bold tracking-[0.08em] text-white backdrop-blur-sm">{{ $project->department }}</span>
                                        @endif
                                        @if ($project->code)
                                            <span class="rounded-md bg-white/90 px-2 py-0.5 text-[11px] font-semibold tabular-nums text-ink-700 backdrop-blur-sm">{{ $project->code }}</span>
                                        @endif
                                    </span>

                                    <h3 class="absolute bottom-4 left-5 right-5 font-display text-[19px] font-bold leading-snug !text-white">
                                        {{ $project->title }}
                                    </h3>
                                </div>

                                {{-- Body --}}
                                <div class="flex flex-1 flex-col px-6 py-5">
                                    @if ($project->summary)
                                        <p class="text-[13.5px] leading-relaxed muted">{{ \Illuminate\Support\Str::limit($project->summary, 130) }}</p>
                                    @endif

                                    <div class="mt-auto flex flex-wrap items-center gap-2 pt-5">
                                        @if ($project->patent_status !== 'none')
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-50 px-2.5 py-1 text-[12px] font-semibold text-brand-700">
                                                <x-ui-icon name="shield" class="h-3.5 w-3.5" /> {{ $project->patent_status_label }}
                                            </span>
                                        @endif
                                        @if ($project->commercialization_status !== 'none')
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-ink-200 px-2.5 py-1 text-[12px] font-medium text-ink-600">
                                                <x-ui-icon name="rocket" class="h-3.5 w-3.5" /> {{ $project->commercialization_status_label }}
                                            </span>
                                        @endif
                                        @if ($project->stage)
                                            <span class="text-[12px] muted">{{ __('site.patents.stage', ['index' => $project->stage_index]) }}</span>
                                        @endif

                                        <x-ui-icon name="arrow-right" class="ml-auto h-4 w-4 text-ink-400 transition-transform duration-300 group-hover:translate-x-1 group-hover:text-brand-600" />
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endforeach

    {{-- ---------------- How IP support works ---------------- --}}
    <section class="border-t border-ink-100 bg-white py-16 sm:py-20">
        <div class="container-rich grid gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:gap-16">
            <div>
                <x-section-heading
                    :eyebrow="__('site.patents.support_eyebrow')"
                    :title="__('site.patents.support_title')"
                    :lead="__('site.patents.support_lead')" />

                <div class="reveal mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('ideas.create') }}" class="btn-primary">
                        {{ __('site.actions.submit_idea') }} <x-ui-icon name="arrow-up-right" class="h-4 w-4" />
                    </a>
                    <a href="{{ route('consultancy.create') }}" class="btn-ghost">{{ __('site.patents.licensing_enquiry') }}</a>
                </div>
            </div>

            <ol class="grid gap-3 sm:grid-cols-2">
                @foreach ([
                    ['search', 'novelty'],
                    ['shield', 'disclosure'],
                    ['document', 'drafting'],
                    ['handshake', 'licensing'],
                ] as $i => [$icon, $step])
                    <li class="reveal rounded-2xl border border-ink-100 bg-white p-5" style="transition-delay: {{ min($i * 60, 300) }}ms">
                        <div class="flex items-center justify-between">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand-600">
                                <x-ui-icon :name="$icon" class="h-4.5 w-4.5" />
                            </span>
                            <span class="font-numeric text-[12px] font-bold tabular-nums text-ink-300">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <p class="mt-4 font-display text-[15px] font-semibold text-ink-950">{{ __('site.patents.step_'.$step) }}</p>
                        <p class="mt-1.5 text-[13px] leading-relaxed muted">{{ __('site.patents.step_'.$step.'_note') }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>
</x-layouts.app>
