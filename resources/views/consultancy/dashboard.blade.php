@php
    use App\Models\ConsultancyApplication;

    $contractLabel = $application->contract_type ? ConsultancyApplication::contractLabel($application->contract_type) : __('consultancy_engagement.pending');
    $status = $application->signed_at ? 'profile_active' : ($application->engagement_policy ? 'signature_required' : 'pending');
@endphp

<x-consultancy-dashboard :application="$application">
    @if ($application->signed_at && $application->order_assignments_count)
        <a href="{{ route('expert.orders.index') }}" class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-teal-200 bg-teal-50 px-5 py-4 text-teal-900">
            <span class="font-semibold">{{ __('consultancy_engagement.assignments') }} <span class="ml-2 rounded-full border border-teal-200 bg-white px-2 py-0.5 text-xs">{{ $application->order_assignments_count }}</span></span>
            <span class="inline-flex items-center gap-2 text-sm font-semibold">{{ __('Open task boards') }} <x-ui-icon name="arrow-right" class="size-4" /></span>
        </a>
    @endif
    <section aria-labelledby="engagement-summary" class="overflow-hidden rounded-2xl border border-ink-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-ink-100 bg-linear-to-r from-teal-50/80 to-white px-5 py-6 sm:px-8 sm:py-8">
            <div class="flex min-w-0 items-center gap-4">
                <span class="grid size-12 shrink-0 place-items-center rounded-2xl border border-teal-100 bg-white text-teal-800" aria-hidden="true">
                    <x-ui-icon name="briefcase" class="size-6" />
                </span>
                <div class="min-w-0">
                    <h2 id="engagement-summary" class="font-display text-[23px] font-bold tracking-tight sm:text-[27px] break-words">{{ $application->expert?->name ?? $application->name }}</h2>
                    <p class="mt-1 text-[13px] font-medium text-ink-600">{{ __('consultancy_engagement.engagement_summary') }}</p>
                </div>
            </div>
            <span @class(['rounded-full px-3 py-1 text-[13px] font-medium', 'bg-teal-100 text-teal-900' => $application->signed_at, 'bg-amber-100 text-amber-900' => ! $application->signed_at])>
                {{ __('consultancy_engagement.'.$status) }}
            </span>
        </div>

        <dl class="grid gap-5 px-5 py-6 sm:grid-cols-[minmax(0,1.35fr)_minmax(0,1fr)] sm:px-8">
            <div>
                <dt class="text-[13px] font-medium text-ink-600">{{ __('consultancy_engagement.category') }}</dt>
                <dd class="mt-1 text-[14px] font-semibold break-words text-ink-950">{{ $application->category->name }}</dd>
            </div>
            <div>
                <dt class="text-[13px] font-medium text-ink-600">{{ __('consultancy_engagement.engagement_type') }}</dt>
                <dd class="mt-1 text-[14px] font-semibold text-ink-950">{{ $contractLabel }}</dd>
            </div>
        </dl>
    </section>

    @if (! $application->signed_at)
        <section class="rounded-2xl border border-ink-200 bg-white p-5 shadow-sm sm:p-7">
            @if ($application->engagement_policy)
                <h2 class="font-display text-[15.5px] font-bold">{{ __('consultancy_engagement.onboarding') }}</h2>
                <p class="mt-2 text-sm text-ink-600">{{ __('consultancy_engagement.review_one_at_a_time') }}</p>
                <a class="btn-lead mt-4" href="{{ route('expert.onboarding') }}">{{ __('consultancy_engagement.continue_onboarding') }} <x-ui-icon name="arrow-right" class="size-4" /></a>
            @else
                <h2 class="font-display text-[15.5px] font-bold">{{ __('consultancy_engagement.waiting') }}</h2>
                <p class="mt-2 text-sm text-ink-600">{{ __('consultancy_engagement.waiting_hint') }}</p>
            @endif
        </section>
    @endif
</x-consultancy-dashboard>
