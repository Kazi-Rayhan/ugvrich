<x-layouts.app :title="__('site.experts.meta_title')"
               :description="__('site.experts.meta_description')">

    <x-page-hero
        :eyebrow="__('site.experts.hero_eyebrow')"
        :title="__('site.experts.hero_title')"
        :lead="__('site.experts.hero_lead')"
        :breadcrumbs="[__('site.experts.breadcrumb') => null]" />

    <section class="py-16 bg-white sm:py-20">
        <div class="container-rich">

            {{-- Search + filter --}}
            @php
                $areaUrl = fn ($slug) => route('experts.index', array_filter(['q' => $search, 'area' => $slug]));
                $activeArea = $categories->firstWhere('slug', $area);
            @endphp

            <div class="reveal relative isolate overflow-hidden rounded-[2rem] border border-ink-100 bg-ink-50/70 p-3 sm:p-4">
                <div class="pointer-events-none absolute inset-0 -z-10 text-brand-700 grid-overlay opacity-30 [mask-image:linear-gradient(to_left,black,transparent_60%)]" aria-hidden="true"></div>

                <form method="GET" action="{{ route('experts.index') }}" x-data="{ q: @js($search) }">
                    @if ($area)
                        <input type="hidden" name="area" value="{{ $area }}">
                    @endif

                    <div class="group flex items-center gap-2 rounded-[1.4rem] border border-ink-200 bg-white p-2 shadow-[0_12px_30px_-24px_rgba(11,15,24,0.4)] transition focus-within:border-brand-400 focus-within:ring-4 focus-within:ring-brand-100 sm:rounded-full">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-brand-50 text-brand-600 transition group-focus-within:bg-brand-600 group-focus-within:text-white">
                            <x-ui-icon name="search" class="h-[18px] w-[18px]" />
                        </span>
                        <label for="expert-search" class="sr-only">{{ __('site.experts.search_label') }}</label>
                        <input id="expert-search" type="search" name="q" x-model="q" value="{{ $search }}" autocomplete="off"
                               placeholder="{{ __('site.experts.search_placeholder') }}"
                               class="min-w-0 flex-1 bg-transparent px-1 py-3 text-[15.5px] text-ink-900 placeholder:text-ink-400 focus:outline-none [&::-webkit-search-cancel-button]:hidden">
                        <button type="button" x-show="q.length" x-cloak @click="q = ''; $nextTick(() => $el.closest('form').querySelector('input[name=q]').focus())"
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-ink-400 transition hover:bg-ink-100 hover:text-ink-700" aria-label="{{ __('site.experts.clear_search') }}">
                            <x-ui-icon name="x" class="h-4 w-4" />
                        </button>
                        <button type="submit" class="btn-primary shrink-0 !px-5 sm:!px-6">
                            <span class="hidden sm:inline">{{ __('site.actions.search') }}</span>
                            <x-ui-icon name="arrow-right" class="h-4 w-4" />
                        </button>
                    </div>
                </form>

                {{-- Area chips --}}
                <div class="mt-3 flex gap-2 overflow-x-auto pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    <a href="{{ $areaUrl(null) }}"
                       @class([
                           'inline-flex shrink-0 items-center gap-2 rounded-full border px-4 py-2 text-[13.5px] font-medium transition',
                           'border-ink-950 bg-ink-950 text-white' => ! $area,
                           'border-ink-200 bg-white text-ink-700 hover:border-ink-300 hover:text-ink-950' => $area,
                       ])>
                        <x-ui-icon name="grid" class="h-4 w-4" />
                        {{ __('site.experts.all_areas') }}
                        <span @class(['rounded-full px-1.5 text-[11.5px] font-semibold tabular-nums', 'bg-white/15' => ! $area, 'bg-ink-100 text-ink-500' => $area])>{{ $totalExperts }}</span>
                    </a>
                    @foreach ($categories as $category)
                        @php $on = $area === $category->slug; @endphp
                        <a href="{{ $areaUrl($category->slug) }}" @if ($on) aria-current="true" @endif
                           @class([
                               'inline-flex shrink-0 items-center gap-2 rounded-full border px-4 py-2 text-[13.5px] font-medium transition',
                               'border-brand-600 bg-brand-600 text-white shadow-[0_10px_22px_-12px_var(--color-brand-600)]' => $on,
                               'border-ink-200 bg-white text-ink-700 hover:border-brand-300 hover:text-brand-700' => ! $on,
                           ])>
                            <x-ui-icon :name="$category->icon ?? 'grid'" @class(['h-4 w-4', 'text-brand-600' => ! $on]) />
                            {{ \Illuminate\Support\Str::before($category->name, ' Consultancy') }}
                            <span @class(['rounded-full px-1.5 text-[11.5px] font-semibold tabular-nums', 'bg-white/20' => $on, 'bg-ink-100 text-ink-500' => ! $on])>{{ $category->experts_count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Results bar --}}
            <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
                <p class="text-[15px] text-ink-700">
                    {!! trans_choice('site.experts.found', $experts->total(), ['count' => '<span class="font-numeric text-xl font-bold tabular-nums text-ink-950">'.$experts->total().'</span>']) !!}
                </p>

                @if ($search || $area)
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($search)
                            <a href="{{ route('experts.index', array_filter(['area' => $area])) }}"
                               class="group inline-flex items-center gap-1.5 rounded-full border border-brand-200 bg-brand-50 py-1.5 pl-3 pr-2 text-[13px] font-medium text-brand-700 transition hover:border-brand-400">
                                “{{ $search }}”
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                                    <x-ui-icon name="x" class="h-3 w-3" stroke="2.4" />
                                </span>
                            </a>
                        @endif
                        @if ($activeArea)
                            <a href="{{ route('experts.index', array_filter(['q' => $search])) }}"
                               class="group inline-flex items-center gap-1.5 rounded-full border border-brand-200 bg-brand-50 py-1.5 pl-3 pr-2 text-[13px] font-medium text-brand-700 transition hover:border-brand-400">
                                {{ \Illuminate\Support\Str::before($activeArea->name, ' Consultancy') }}
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                                    <x-ui-icon name="x" class="h-3 w-3" stroke="2.4" />
                                </span>
                            </a>
                        @endif
                        <a href="{{ route('experts.index') }}" class="px-2 text-[13px] font-semibold text-ink-500 transition hover:text-brand-700">{{ __('site.experts.clear_all') }}</a>
                    </div>
                @endif
            </div>

            @if ($experts->isEmpty())
                <div class="mt-8 rounded-[2rem] border-2 border-dashed border-ink-200 bg-ink-50/50 px-6 py-16 text-center">
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-brand-600 shadow-[0_12px_30px_-20px_rgba(11,15,24,0.4)]">
                        <x-ui-icon name="search" class="h-7 w-7" />
                    </span>
                    <p class="mt-6 font-display text-xl font-bold text-ink-950">{{ __('site.experts.empty_title') }}</p>
                    <p class="mx-auto mt-2 max-w-md text-[14.5px] muted">{{ __('site.experts.empty_body') }}</p>
                    <div class="mt-7 flex flex-wrap justify-center gap-3">
                        <a href="{{ route('experts.index') }}" class="btn-primary">{{ __('site.experts.show_all') }}</a>
                        <a href="{{ route('contact') }}" class="btn-ghost">{{ __('site.experts.ask_us') }}</a>
                    </div>
                </div>
            @else
                <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($experts as $i => $expert)
                        <x-cards.expert-profile-card :expert="$expert" :index="$i" />
                    @endforeach
                </div>

                <div class="mt-14 border-t border-ink-100 pt-8">{{ $experts->links('vendor.pagination.rich') }}</div>
            @endif
        </div>
    </section>

</x-layouts.app>
