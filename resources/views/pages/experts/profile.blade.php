@php
    $biography = $expert->biography();
    $skills = $expert->professional_skills ?? [];
    $linkedIn = preg_match('~^https?://~i', $expert->linkedin ?? '') ? $expert->linkedin : null;
    $affiliation = $expert->affiliation_type ? __('consultancy_engagement.'.$expert->affiliation_type) : null;
    $facts = array_filter([
        'current_job' => $expert->current_job,
        'department' => $expert->department,
        'affiliation' => $affiliation,
    ], 'filled');
@endphp

<x-layouts.app :title="$expert->name" :description="$expert->designation">
    <header class="border-b-4 border-teal-700 bg-navy-800 text-white">
        <div class="container-rich !max-w-6xl py-7 sm:py-12">
            <a href="{{ route('experts.index') }}" class="inline-flex min-h-10 items-center gap-2 rounded-md text-[13px] font-semibold text-teal-100 hover:underline focus-visible:outline-2 focus-visible:outline-teal-100">
                <x-ui-icon name="arrow-left" class="size-4" /> {{ __('site.experts.breadcrumb') }}
            </a>
            <div class="mt-5 grid items-center gap-6 sm:grid-cols-[minmax(0,1fr)_minmax(0,.65fr)] sm:gap-10">
                <div class="grid aspect-[5/4] w-full max-w-64 place-items-center overflow-hidden rounded-xl border border-white/25 bg-navy-700 text-teal-100 sm:order-last sm:ml-auto sm:max-w-80">
                    @if ($expert->photo)
                        <img src="{{ Storage::disk('public')->url($expert->photo) }}" alt="" width="320" height="256" class="size-full object-cover object-top">
                    @else
                        <x-ui-icon name="user" class="size-10 sm:size-14" />
                    @endif
                </div>
                <div class="min-w-0">
                    @if ($expert->category)
                        <a href="{{ route('services.show', $expert->category) }}" class="text-[14px] font-semibold wrap-anywhere text-teal-200 hover:underline">{{ $expert->category->name }}</a>
                    @endif
                    <h1 class="mt-2 font-display text-[clamp(2rem,5vw,4rem)] font-bold leading-tight tracking-tight wrap-anywhere !text-white">{{ $expert->name }}</h1>
                    @if ($expert->designation)
                        <p class="mt-3 text-lg font-medium wrap-anywhere text-white/90">{{ $expert->designation }}</p>
                    @endif
                    @if ($expert->current_job)
                        <p class="mt-1 text-[14px] wrap-anywhere text-white/80">{{ $expert->current_job }}</p>
                    @endif
                    <nav class="mt-5 flex flex-wrap gap-x-5 gap-y-1" aria-label="{{ __('site.experts.profile') }}">
                        @foreach (['expert-biography' => [$biography, 'biography'], 'expert-skills' => [$skills || $expert->expertise, 'skills'], 'public-portfolio' => [$portfolios->isNotEmpty(), 'portfolio']] as $section => [$visible, $label])
                            @if ($visible)
                                <a href="#{{ $section }}" class="inline-flex min-h-11 items-center gap-2 border-b border-white/30 text-sm font-semibold text-teal-100 hover:border-teal-200 hover:text-white">
                                    {{ __('consultancy_engagement.'.$label) }} <x-ui-icon name="arrow-right" class="size-4" />
                                </a>
                            @endif
                        @endforeach
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <div class="container-rich !max-w-6xl py-7 sm:py-10">
        <div @class(['grid items-start gap-8 lg:gap-12', 'lg:grid-cols-[minmax(0,15rem)_minmax(0,1fr)]' => $facts || $expert->email || $expert->phone || $linkedIn])>
            <div class="min-w-0 divide-y divide-ink-200">
                @if ($biography)
                    <section class="scroll-mt-28 pb-6 sm:pb-8" id="expert-biography">
                        <h2 class="font-display text-xl font-bold text-navy-900">{{ __('consultancy_engagement.biography') }}</h2>
                        <div class="expert-biography mt-3 max-w-[68ch] text-[15px] leading-relaxed wrap-anywhere text-ink-800">{!! $biography !!}</div>
                    </section>
                @endif

                @if ($skills || $expert->expertise)
                    <section class="grid scroll-mt-28 gap-5 py-6 first:pt-0 sm:grid-cols-2 sm:py-8" id="expert-skills">
                        @foreach (['expertise' => $expert->expertise ?? [], 'skills' => $skills] as $key => $entries)
                            @if ($entries)
                                <div class="min-w-0">
                                    <h2 class="font-display text-xl font-bold text-navy-900">{{ __('consultancy_engagement.'.$key) }}</h2>
                                    <ul class="mt-3 flex flex-wrap gap-2">
                                        @foreach ($entries as $entry)
                                            <li class="max-w-full rounded-md bg-teal-50 px-2.5 py-1 text-[13px] font-medium wrap-anywhere text-teal-900">{{ $entry }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        @endforeach
                    </section>
                @endif

                @if ($portfolios->isNotEmpty())
                    <section class="scroll-mt-28 py-6 first:pt-0 sm:py-8" id="public-portfolio">
                        <h2 class="font-display text-xl font-bold text-navy-900">{{ __('consultancy_engagement.portfolio') }}</h2>
                        <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                            @foreach ($portfolios as $portfolio)
                                <li>
                                    <a href="{{ route('experts.portfolio', [$expert, $portfolio]) }}" class="group flex h-full flex-col gap-3 rounded-xl border border-ink-200 p-4 hover:border-teal-300 hover:bg-navy-50/60">
                                        <x-ui-icon name="document" class="size-6 text-teal-700" />
                                        <span class="text-[14px] font-semibold wrap-anywhere text-navy-900">{{ $portfolio->title }}</span>
                                        <span class="mt-auto inline-flex items-center gap-1.5 text-[13px] font-semibold text-brand-700 group-hover:underline">{{ __('consultancy_engagement.download') }} <x-ui-icon name="arrow-up-right" class="size-4" /></span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>

            @if ($facts || $expert->email || $expert->phone || $linkedIn)
                <aside class="border-t-2 border-teal-700 pt-4 lg:col-start-1 lg:row-start-1">
                    <h2 class="font-display text-base font-bold text-navy-900">{{ __('site.experts.profile') }}</h2>
                    <dl class="mt-3 space-y-3">
                        @foreach ($facts as $label => $value)
                            <div>
                                <dt class="text-[12.5px] font-medium text-ink-600">{{ __('consultancy_engagement.'.$label) }}</dt>
                                <dd class="mt-0.5 text-sm wrap-anywhere text-ink-900">{{ $value }}</dd>
                            </div>
                        @endforeach
                        @if ($expert->email)
                            <div><dt class="text-[12.5px] font-medium text-ink-600">{{ __('consultancy_engagement.email') }}</dt><dd class="mt-0.5 text-sm wrap-anywhere"><a class="text-brand-700 hover:underline" href="mailto:{{ $expert->email }}">{{ $expert->email }}</a></dd></div>
                        @endif
                        @if ($expert->phone)
                            <div><dt class="text-[12.5px] font-medium text-ink-600">{{ __('consultancy_engagement.public_phone') }}</dt><dd class="mt-0.5 text-sm"><a class="text-brand-700 hover:underline" href="tel:{{ preg_replace('/[^\d+]/', '', $expert->phone) }}">{{ $expert->phone }}</a></dd></div>
                        @endif
                    </dl>
                    @if ($linkedIn)
                        <a class="mt-4 inline-flex min-h-10 items-center gap-2 text-sm font-semibold text-brand-700 hover:underline" href="{{ $linkedIn }}" target="_blank" rel="noopener noreferrer">
                            LinkedIn <x-ui-icon name="arrow-up-right" class="size-4" />
                        </a>
                    @endif
                </aside>
            @endif
        </div>

        @if ($related->isNotEmpty())
            <section class="mt-8 border-t border-ink-200 pt-6">
                <h2 class="font-display text-xl font-bold text-navy-900">{{ __('site.experts.related_title') }}</h2>
                <ul class="mt-5 grid gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3">
                    @foreach ($related as $index => $item)
                        <li class="min-w-0">
                            <x-cards.expert-profile-card :expert="$item" :index="$index" class="h-full" />
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-layouts.app>
