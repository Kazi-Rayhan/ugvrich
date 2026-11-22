<x-consultancy-dashboard :application="$application" page="portfolio">
    <div class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)]">
        <section class="min-w-0 rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-7" aria-label="{{ __('consultancy_engagement.portfolio') }}">
            @if ($application->portfolios->isEmpty())
                <div class="rounded-xl border border-dashed border-ink-200 bg-ink-50/50 p-6 text-center">
                    <x-ui-icon name="document" class="mx-auto mb-3 size-6 text-teal-700" />
                    <p class="text-[13px] font-medium text-ink-700">{{ __('consultancy_engagement.portfolio_empty') }}</p>
                </div>
            @else
                <ul class="space-y-2">
                    @foreach ($application->portfolios as $portfolio)
                        <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-ink-100 p-3.5">
                            <a class="flex min-w-0 items-center gap-3 text-[14px] font-semibold text-ink-900 hover:text-teal-800 hover:underline" href="{{ route('expert.portfolio.download', $portfolio) }}">
                                <x-ui-icon name="document" class="size-5 shrink-0 text-teal-700" />
                                <span class="min-w-0 flex-1 break-words">{{ $portfolio->title }}</span>
                                <x-ui-icon name="arrow-up-right" class="size-4 shrink-0 text-ink-500" />
                            </a>
                            <form method="POST" action="{{ route('expert.portfolio.visibility', $portfolio) }}" class="flex flex-wrap items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="is_public" value="{{ $portfolio->is_public ? '0' : '1' }}">
                                <span @class(['rounded-full px-2 py-0.5 text-[12px] font-semibold', 'bg-teal-50 text-teal-900' => $portfolio->is_public, 'bg-ink-50 text-ink-700' => ! $portfolio->is_public])>{{ __('consultancy_engagement.'.($portfolio->is_public ? 'public' : 'private')) }}</span>
                                <button type="submit" class="rounded-lg border border-ink-200 px-2.5 py-1 text-[12.5px] font-semibold text-brand-700 hover:bg-brand-50">{{ __('consultancy_engagement.'.($portfolio->is_public ? 'make_private' : 'publish')) }}</button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="min-w-0 rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="font-display text-[15.5px] font-bold">{{ __('consultancy_engagement.upload') }}</h2>
            <p class="mt-2 text-[13.5px] leading-relaxed text-ink-600">{{ __('consultancy_engagement.portfolio_help_visibility') }}</p>
            <form method="POST" action="{{ route('expert.portfolio.upload') }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                <label class="field-label block">{{ __('consultancy_engagement.title') }} <input class="field-input mt-2" name="title" value="{{ old('title') }}" maxlength="150" required></label>
                <label class="field-label block">
                    {{ __('consultancy_engagement.file') }}
                    <input class="mt-2 w-full rounded-xl border border-ink-200 p-2 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-teal-50 file:px-3 file:py-2 file:font-semibold file:text-teal-800" name="portfolio" type="file" accept="application/pdf,image/png,image/jpeg" required>
                </label>
                <label class="flex items-start gap-2 text-[13px] font-medium text-ink-800">
                    <input type="hidden" name="is_public" value="0">
                    <input type="checkbox" name="is_public" value="1" @checked(old('is_public', '1') == '1') class="mt-0.5 size-4 accent-brand-700">
                    {{ __('consultancy_engagement.publish_portfolio') }}
                </label>
                <button type="submit" class="btn-lead">{{ __('consultancy_engagement.upload') }}</button>
            </form>
        </section>
    </div>
</x-consultancy-dashboard>
