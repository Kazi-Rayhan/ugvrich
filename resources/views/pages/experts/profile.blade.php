@php
    $biography = $expert->biography();
    $skills = $expert->professional_skills ?? [];
    $linkedIn = preg_match('~^https?://~i', $expert->linkedin ?? '') ? $expert->linkedin : null;
    $affiliation = $expert->affiliation_type ? __('consultancy_engagement.'.$expert->affiliation_type) : null;
@endphp

<x-layouts.app :title="$expert->name" :description="$expert->designation">
    <header class="expert-portfolio-hero relative isolate overflow-hidden text-white">
        <div class="pointer-events-none absolute inset-0 -z-10 grid-overlay text-white opacity-10" aria-hidden="true"></div>
        <div class="container-rich !max-w-5xl pt-6 pb-12 sm:pt-8 sm:pb-16 lg:pb-20">
            <a href="{{ route('experts.index') }}" class="inline-flex items-center gap-2 rounded-lg text-[13px] font-semibold text-white/85 hover:text-white hover:underline focus-visible:outline-2 focus-visible:outline-cyan-200">
                <x-ui-icon name="arrow-left" class="size-4" />
                {{ __('site.experts.breadcrumb') }}
            </a>

            <div class="mt-8 grid items-center gap-9 lg:mt-10 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,0.85fr)] lg:gap-14">
                <div class="min-w-0">
                    @if ($expert->category)
                        <p class="mb-3 text-[14px] font-semibold text-teal-200">{{ $expert->category->name }}</p>
                    @endif
                    <h1 class="font-display text-[clamp(2.35rem,6vw,5rem)] font-bold leading-[1.08] tracking-tight break-words !text-white">{{ $expert->name }}</h1>
                    @if ($expert->designation)
                        <p class="mt-4 text-lg font-semibold break-words text-white/90 sm:text-xl">{{ $expert->designation }}</p>
                    @endif
                    @if ($expert->current_job || $affiliation)
                        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-[14px]">
                            @if ($expert->current_job)
                                <p class="inline-flex items-center gap-2 text-white/85"><x-ui-icon name="briefcase" class="size-4 shrink-0 text-teal-200" /> {{ $expert->current_job }}</p>
                            @endif
                            @if ($affiliation)
                                <span class="rounded-full border border-white/25 bg-white/10 px-3 py-1 text-[12.5px] font-semibold text-teal-100">{{ $affiliation }}</span>
                            @endif
                        </div>
                    @endif
                    @if ($portfolios->isNotEmpty() || $biography)
                        <a href="{{ $portfolios->isNotEmpty() ? '#public-portfolio' : '#expert-biography' }}" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-teal-100 px-4 py-2.5 text-[14px] font-bold text-navy-700 transition-colors hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-teal-200">
                            {{ __('consultancy_engagement.'.($portfolios->isNotEmpty() ? 'portfolio' : 'biography')) }}
                            <x-ui-icon name="arrow-right" class="size-4" />
                        </a>
                    @endif
                </div>

                <div class="relative mx-auto aspect-square w-full max-w-56 sm:max-w-72 lg:max-w-88">
                    <div class="absolute inset-0 rounded-full border border-teal-200/35" aria-hidden="true"></div>
                    <div class="absolute inset-[7%] grid place-items-center overflow-hidden rounded-full border-4 border-white/20 bg-linear-to-br from-brand-500 to-teal-700 shadow-xl shadow-navy-950/30">
                        @if ($expert->photo)
                            <img src="{{ Storage::disk('public')->url($expert->photo) }}" alt="" width="350" height="350" class="size-full object-cover">
                        @else
                            <x-ui-icon name="user" class="size-20 text-white/90 sm:size-28" />
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="expert-portfolio-body text-white">
        <div class="container-rich !max-w-5xl space-y-18 py-10 sm:space-y-24 sm:py-16">
            @php
                $highlights = array_filter([
                    ['icon' => 'briefcase', 'title' => __('consultancy_engagement.expertise'), 'items' => $expert->expertise ?? [], 'target' => 'expert-skills'],
                    ['icon' => 'check', 'title' => __('consultancy_engagement.skills'), 'items' => $skills, 'target' => 'expert-skills'],
                    ['icon' => 'document', 'title' => __('consultancy_engagement.portfolio'), 'items' => $portfolios->pluck('title')->all(), 'target' => 'public-portfolio'],
                ], fn ($group) => $group['items']);
            @endphp
            @if ($highlights)
                <div class="grid gap-4 sm:grid-cols-[repeat(auto-fit,minmax(min(100%,12rem),1fr))]">
                    @foreach ($highlights as $highlight)
                        <a href="#{{ $highlight['target'] }}" class="group flex flex-col items-start rounded-2xl border border-white/15 bg-white/[.07] p-5 text-white transition-colors hover:border-teal-200/50 hover:bg-white/10 focus-visible:outline-2 focus-visible:outline-teal-200 sm:p-6">
                            <x-ui-icon :name="$highlight['icon']" class="mb-4 size-7 text-teal-200" />
                            <h2 class="font-display text-lg font-bold !text-white">{{ $highlight['title'] }}</h2>
                            <p class="mt-2 text-[13.5px] leading-relaxed break-words text-white/75">{{ implode(' / ', array_slice($highlight['items'], 0, 3)) }}</p>
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($biography || $expert->designation || $expert->current_job || $expert->department || $affiliation || $expert->category || $expert->email || $expert->phone || $linkedIn)
            <section id="expert-biography" class="scroll-mt-28" aria-labelledby="expert-about">
                <h2 id="expert-about" class="mb-8 text-center font-display text-3xl font-bold tracking-tight !text-white sm:mb-10 sm:text-4xl">{{ __('consultancy_engagement.biography') }}</h2>
                <div class="grid items-center gap-7 md:grid-cols-[minmax(0,0.85fr)_minmax(0,1.15fr)] md:gap-12">
                    <div class="relative mx-auto w-full max-w-80">
                        <div class="absolute -inset-2 translate-x-2 translate-y-2 rounded-2xl border border-teal-200/30" aria-hidden="true"></div>
                        <div class="relative grid aspect-[5/4] place-items-center overflow-hidden rounded-2xl bg-linear-to-br from-navy-600 to-navy-900">
                            @if ($expert->photo)
                                <img src="{{ Storage::disk('public')->url($expert->photo) }}" alt="" loading="lazy" class="size-full object-cover object-center">
                            @else
                                <x-ui-icon name="user" class="size-18 text-teal-100" />
                            @endif
                        </div>
                    </div>
                    <div class="min-w-0">
                        @if ($biography)
                            <div class="expert-biography mb-5 break-words text-[14.5px] leading-relaxed text-white/85">{!! $biography !!}</div>
                        @endif
                        <dl class="grid gap-4 rounded-2xl border border-white/15 bg-white/[.06] p-4 sm:grid-cols-2 sm:p-5">
                            @foreach ([
                                __('consultancy_engagement.designation') => $expert->designation,
                                __('consultancy_engagement.current_job') => $expert->current_job,
                                __('consultancy_engagement.department') => $expert->department,
                                __('consultancy_engagement.affiliation') => $affiliation,
                            ] as $label => $value)
                                @if ($value)
                                    <div>
                                        <dt class="text-[12.5px] font-semibold text-teal-200">{{ $label }}</dt>
                                        <dd class="mt-1 text-[14px] font-medium break-words text-white">{{ $value }}</dd>
                                    </div>
                                @endif
                            @endforeach
                            @if ($expert->category)
                                <div>
                                    <dt class="text-[12.5px] font-semibold text-teal-200">{{ __('consultancy_engagement.category') }}</dt>
                                    <dd class="mt-1 text-[14px]"><a class="hover:underline" href="{{ route('services.show', $expert->category) }}">{{ $expert->category->name }}</a></dd>
                                </div>
                            @endif
                            @if ($expert->email)
                                <div><dt class="text-[12.5px] font-semibold text-teal-200">{{ __('consultancy_engagement.email') }}</dt><dd class="mt-1 text-[14px] break-words"><a class="hover:underline" href="mailto:{{ $expert->email }}">{{ $expert->email }}</a></dd></div>
                            @endif
                            @if ($expert->phone)
                                <div><dt class="text-[12.5px] font-semibold text-teal-200">{{ __('consultancy_engagement.public_phone') }}</dt><dd class="mt-1 text-[14px]"><a class="hover:underline" href="tel:{{ preg_replace('/[^\d+]/', '', $expert->phone) }}">{{ $expert->phone }}</a></dd></div>
                            @endif
                            @if ($linkedIn)
                                <div><dt class="text-[12.5px] font-semibold text-teal-200">LinkedIn</dt><dd class="mt-1 text-[14px]"><a class="hover:underline" href="{{ $linkedIn }}" target="_blank" rel="noopener noreferrer">{{ $expert->name }}</a></dd></div>
                            @endif
                        </dl>
                    </div>
                </div>
            </section>
            @endif

            @if ($skills || $expert->expertise)
                <section id="expert-skills" class="scroll-mt-28" aria-labelledby="expert-skill-heading">
                    <h2 id="expert-skill-heading" class="mb-7 text-center font-display text-3xl font-bold tracking-tight !text-white sm:text-4xl">{{ __('consultancy_engagement.skills') }}</h2>
                    <div class="grid gap-4 md:grid-cols-[repeat(auto-fit,minmax(min(100%,20rem),1fr))]">
                        @foreach (['expertise' => $expert->expertise ?? [], 'skills' => $skills] as $label => $entries)
                            @if ($entries)
                                <section class="rounded-2xl border border-white/15 bg-white/[.06] p-5">
                                    <h3 class="font-display text-base font-bold !text-white">{{ __('consultancy_engagement.'.$label) }}</h3>
                                    <ul class="mt-4 grid gap-2 sm:grid-cols-2">
                                        @foreach ($entries as $entry)
                                            <li class="flex min-w-0 items-start gap-2 rounded-lg bg-white/[.07] px-2.5 py-2 text-[13.5px] font-medium break-words text-white/90"><x-ui-icon name="check" class="mt-0.5 size-4 shrink-0 text-teal-200" /> <span class="min-w-0">{{ $entry }}</span></li>
                                        @endforeach
                                    </ul>
                                </section>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($portfolios->isNotEmpty())
                <section id="public-portfolio" class="scroll-mt-28" aria-labelledby="published-portfolio-heading">
                    <h2 id="published-portfolio-heading" class="mb-7 text-center font-display text-3xl font-bold tracking-tight !text-white sm:text-4xl">{{ __('consultancy_engagement.portfolio') }}</h2>
                    <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($portfolios as $portfolio)
                            <li class="min-w-0">
                                <a href="{{ route('experts.portfolio', [$expert, $portfolio]) }}" class="group flex h-full flex-col overflow-hidden rounded-2xl border border-white/15 bg-white/[.06] transition-colors hover:border-teal-200/50 focus-visible:outline-2 focus-visible:outline-teal-200">
                                    <div class="grid h-28 place-items-center bg-linear-to-br from-navy-600 to-navy-800 text-teal-100"><x-ui-icon name="document" class="size-9" /></div>
                                    <div class="flex flex-1 flex-col gap-3 p-4">
                                        <span class="text-[14px] font-semibold break-words text-white">{{ $portfolio->title }}</span>
                                        <span class="mt-auto inline-flex items-center gap-1.5 text-[13px] font-semibold text-teal-200 group-hover:underline">{{ __('consultancy_engagement.download') }} <x-ui-icon name="arrow-up-right" class="size-4" /></span>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section class="border-t hairline bg-white py-14 sm:py-18">
            <div class="container-rich">
                <x-section-heading :eyebrow="__('site.experts.related_eyebrow')" :title="__('site.experts.related_title')" />
                <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($related as $i => $item)
                        <x-cards.expert-profile-card :expert="$item" :index="$i" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layouts.app>
