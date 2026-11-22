<x-consultancy-dashboard :application="$application" page="details">
    <section class="rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-7">
        @php($items = $application->engagementReviewItems())
        @if ($items)
            <dl class="grid items-start gap-4 md:grid-cols-2">
                @foreach ($items as $item)
                    <div class="min-w-0 rounded-xl border border-ink-100 bg-ink-50/50 p-4">
                        <dt class="text-[14px] font-semibold break-words text-ink-950">{{ $item['title'] }}</dt>
                        <dd class="mt-2 whitespace-pre-wrap break-words text-[13.5px] leading-relaxed text-ink-700">{{ $item['description'] }}</dd>
                    </div>
                @endforeach
            </dl>
        @else
            <p class="text-sm text-ink-600">{{ __('consultancy_engagement.contract_empty') }}</p>
        @endif

        <a class="mt-5 inline-flex items-center gap-2 text-[13px] font-semibold text-brand-700 hover:underline" href="{{ route('expert.signature', $application) }}">
            <x-ui-icon name="document" class="size-4" />
            {{ __('consultancy_engagement.download_signature') }}
        </a>
    </section>
</x-consultancy-dashboard>
