<x-layouts.app :title="__($translationPrefix.'meta_title')" :description="__($translationPrefix.'meta_description')">
    <header class="border-b-4 border-teal-600 bg-navy-800">
        <div class="container-rich !max-w-6xl py-9 sm:py-14">
            <div class="grid items-end gap-4 md:grid-cols-[minmax(0,1.1fr)_minmax(0,0.9fr)] md:gap-12">
                <h1 class="max-w-[20ch] font-display text-4xl font-bold leading-tight tracking-tight !text-white sm:text-5xl">{{ strip_tags(__($translationPrefix.'hero_title')) }}</h1>
                <p class="max-w-[45ch] border-l border-white/30 pl-4 text-[15px] leading-relaxed text-white/85 md:pl-6">{{ __($translationPrefix.'hero_lead') }}</p>
            </div>
        </div>
    </header>

    <div class="container-rich !max-w-6xl py-6 sm:py-9">
        <nav class="mb-6 flex flex-wrap gap-x-6 gap-y-1 border-b border-ink-200" aria-label="{{ __('site.experts.directory_label') }}">
            <a class="border-b-2 border-teal-700 py-2.5 text-sm font-semibold text-navy-900" aria-current="page" href="{{ route('experts.index') }}">{{ __('site.experts.breadcrumb') }}</a>
            <a class="border-b-2 border-transparent py-2.5 text-sm font-medium text-ink-600 hover:text-brand-700" href="{{ route('administrations.index') }}">{{ __('site.administrations.breadcrumb') }}</a>
        </nav>

        <div class="space-y-7">
            <form method="GET" action="{{ route($directoryRoute) }}" class="grid items-end gap-4 border-y border-ink-200 bg-ink-50/70 px-4 py-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,1fr)_auto]">
                <div class="min-w-0">
                    <input id="expert-search" name="q" type="search" value="{{ $search }}" class="field-input" aria-label="{{ __($translationPrefix.'search_label') }}" placeholder="{{ __($translationPrefix.'search_placeholder') }}">
                </div>
                <div class="min-w-0">
                    <label for="expert-area" class="field-label">{{ __('consultancy_engagement.category') }}</label>
                    <select id="expert-area" name="area" class="field-input mt-2">
                        <option value="">{{ __($translationPrefix.'all_areas') }} ({{ $totalExperts }})</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->slug }}" @selected($area === $category->slug)>{{ $category->name }} ({{ $category->experts_count }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-wrap items-center gap-3 sm:col-span-2 lg:col-span-1 lg:min-h-11">
                    <button type="submit" class="btn-primary">{{ __('site.actions.search') }}</button>
                    @if ($search !== '' || $area !== '')
                        <a class="py-2 text-sm font-semibold text-brand-700 hover:underline" href="{{ route($directoryRoute) }}">{{ __($translationPrefix.'clear_all') }}</a>
                    @endif
                </div>
            </form>

            <section class="min-w-0" aria-label="{{ __('site.experts.directory_label') }}">
                @if ($experts->isEmpty())
                    <div class="border-b border-ink-100 py-8">
                        <h3 class="font-display text-lg font-bold">{{ __($translationPrefix.'empty_title') }}</h3>
                        <p class="mt-2 text-sm text-ink-600">{{ __($translationPrefix.'empty_body') }}</p>
                        <a class="mt-4 inline-flex py-2 text-sm font-semibold text-brand-700 hover:underline" href="{{ route($directoryRoute) }}">{{ __($translationPrefix.'show_all') }}</a>
                    </div>
                @else
                    <ul class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 sm:gap-6">
                        @foreach ($experts as $index => $expert)
                            <li class="min-w-0">
                                <x-cards.expert-profile-card :expert="$expert" :index="$index" class="h-full" />
                            </li>
                        @endforeach
                    </ul>
                    @if ($experts->hasPages())
                        <div class="mt-5 border-t border-ink-200 pt-5">{{ $experts->links('vendor.pagination.rich') }}</div>
                    @endif
                @endif
            </section>
        </div>
    </div>
</x-layouts.app>
