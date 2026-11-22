<x-consultancy-dashboard :application="$application" page="onboarding_nav">
    @if ($application->engagement_policy)
        @include('consultancy.onboarding', ['reviewItems' => $application->engagementReviewItems()])
    @else
        <section class="rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-7">
            <h2 class="font-display text-[17px] font-bold">{{ __('consultancy_engagement.waiting') }}</h2>
            <p class="mt-2 text-sm text-ink-600">{{ __('consultancy_engagement.waiting_hint') }}</p>
        </section>
    @endif
</x-consultancy-dashboard>
