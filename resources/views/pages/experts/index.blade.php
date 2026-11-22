<x-layouts.app :title="__($translationPrefix.'meta_title')"
               :description="__($translationPrefix.'meta_description')">

    <x-page-hero
        :eyebrow="__($translationPrefix.'hero_eyebrow')"
        :title="__($translationPrefix.'hero_title')"
        :lead="__($translationPrefix.'hero_lead')"
        :breadcrumbs="[__($translationPrefix.'breadcrumb') => null]" />

    <section class="py-16 bg-white sm:py-20">
        <div class="container-rich">

            <nav class="mb-8 flex flex-wrap gap-3" aria-label="{{ __('site.experts.directory_label') }}">
                <a href="{{ route('experts.index') }}" class="{{ $directoryRoute === 'experts.index' ? 'btn-primary' : 'btn-ghost' }}" @if ($directoryRoute === 'experts.index') aria-current="page" @endif>{{ __('site.experts.breadcrumb') }}</a>
                <a href="{{ route('administrations.index') }}" class="{{ $directoryRoute === 'administrations.index' ? 'btn-primary' : 'btn-ghost' }}" @if ($directoryRoute === 'administrations.index') aria-current="page" @endif>{{ __('site.administrations.breadcrumb') }}</a>
            </nav>

            {{-- Search + filter --}}
            @php

                $activeArea = $categories->firstWhere('slug', $area);
            @endphp

            @include('pages.experts.filters')

            {{-- Results bar --}}
            <div class="mt-8 flex flex-wrap items-center justify-between gap-3">
                <p class="text-[15px] text-ink-700">
                    {!! trans_choice($translationPrefix.'found', $experts->total(), ['count' => '<span class="font-numeric text-xl font-bold tabular-nums text-ink-950">'.$experts->total().'</span>']) !!}
                </p>

                @if ($search || $area)
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($search)
                            <a href="{{ route($directoryRoute, array_filter(['area' => $area])) }}"
                               class="group inline-flex items-center gap-1.5 rounded-full border border-brand-200 bg-brand-50 py-1.5 pl-3 pr-2 text-[13px] font-medium text-brand-700 transition hover:border-brand-400">
                                “{{ $search }}”
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                                    <x-ui-icon name="x" class="h-3 w-3" stroke="2.4" />
                                </span>
                            </a>
                        @endif
                        @if ($activeArea)
                            <a href="{{ route($directoryRoute, array_filter(['q' => $search])) }}"
                               class="group inline-flex items-center gap-1.5 rounded-full border border-brand-200 bg-brand-50 py-1.5 pl-3 pr-2 text-[13px] font-medium text-brand-700 transition hover:border-brand-400">
                                {{ \Illuminate\Support\Str::before($activeArea->name, ' Consultancy') }}
                                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-white text-brand-600 transition group-hover:bg-brand-600 group-hover:text-white">
                                    <x-ui-icon name="x" class="h-3 w-3" stroke="2.4" />
                                </span>
                            </a>
                        @endif
                        <a href="{{ route($directoryRoute) }}" class="px-2 text-[13px] font-semibold text-ink-500 transition hover:text-brand-700">{{ __($translationPrefix.'clear_all') }}</a>
                    </div>
                @endif
            </div>

            @if ($experts->isEmpty())
                <div class="mt-8 rounded-[2rem] border-2 border-dashed border-ink-200 bg-ink-50/50 px-6 py-16 text-center">
                    <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white text-brand-600 shadow-[0_12px_30px_-20px_rgba(11,15,24,0.4)]">
                        <x-ui-icon name="search" class="h-7 w-7" />
                    </span>
                    <p class="mt-6 font-display text-xl font-bold text-ink-950">{{ __($translationPrefix.'empty_title') }}</p>
                    <p class="mx-auto mt-2 max-w-md text-[14.5px] muted">{{ __($translationPrefix.'empty_body') }}</p>
                    <div class="mt-7 flex flex-wrap justify-center gap-3">
                        <a href="{{ route($directoryRoute) }}" class="btn-primary">{{ __($translationPrefix.'show_all') }}</a>
                        <a href="{{ route('contact') }}" class="btn-ghost">{{ __($translationPrefix.'ask_us') }}</a>
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
