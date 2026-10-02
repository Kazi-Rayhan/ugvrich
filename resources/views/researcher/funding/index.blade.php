@php
    use App\Models\FundingOpportunity;
    use App\Models\ResearchFunding;

    $categories = FundingOpportunity::categories();
@endphp

<x-layouts.researcher :title="__('researcher.funding.title')">

    <div class="space-y-7">
        <div class="max-w-2xl">
            <h2 class="font-display text-[22px] font-bold tracking-tight text-ink-950 sm:text-[26px]">{{ __('researcher.funding.title') }}</h2>
            <p class="mt-2 text-[14.5px] leading-[1.8] text-ink-600">{{ __('researcher.funding.lead') }}</p>
        </div>

        {{-- The researcher's own funding records --}}
        <div class="rounded-[1.5rem] border border-ink-100 bg-white">
            <p class="border-b border-ink-100 px-6 py-4 font-display text-[15.5px] font-bold text-ink-950">{{ __('researcher.funding.mine') }}</p>

            @if ($fundings->isEmpty())
                <div class="px-6 py-10">
                    <p class="text-[14.5px] font-semibold text-ink-700">{{ __('researcher.funding.none_mine') }}</p>
                    <p class="mt-2 max-w-xl text-[13.5px] leading-relaxed text-ink-500">{{ __('researcher.funding.none_mine_lead') }}</p>
                </div>
            @else
                <ul class="divide-y divide-ink-100">
                    @foreach ($fundings as $funding)
                        <li class="px-6 py-5">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-[14px] font-bold text-ink-950">{{ $funding->funder() }}</p>

                                    <p class="mt-1 flex flex-wrap items-center gap-1.5 text-[12px]">
                                        <span @class([
                                            'rounded-lg px-2 py-0.5 font-semibold',
                                            'bg-gold-100 text-gold-700' => $funding->isExternal(),
                                            'bg-ink-100 text-ink-600' => ! $funding->isExternal(),
                                        ])>{{ ResearchFunding::sourceTypes()[$funding->source_type] ?? $funding->source_type }}</span>

                                        @if ($funding->type)
                                            <span class="rounded-lg bg-ink-100 px-2 py-0.5 font-medium text-ink-600">{{ ResearchFunding::types()[$funding->type] ?? $funding->type }}</span>
                                        @endif

                                        @if ($funding->source)
                                            <span class="text-ink-500">{{ $funding->source }}</span>
                                        @endif
                                    </p>

                                    @if ($funding->proposal)
                                        <p class="mt-1 text-[12.5px] text-ink-500">
                                            {{ __('researcher.funding.for_proposal') }}:
                                            <a href="{{ route('researcher.proposals.show', $funding->proposal) }}" class="text-brand-700 hover:text-brand-600">{{ $funding->proposal->title }}</a>
                                        </p>
                                    @endif
                                </div>

                                <x-researcher-status :status="$funding->status" :labels="ResearchFunding::statuses()" />
                            </div>

                            <p class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-[13px] text-ink-600">
                                @if ($funding->requested_amount)
                                    <span>{{ __('researcher.funding.requested') }}: <span class="font-numeric tabular-nums">{{ $funding->currency }} {{ number_format((float) $funding->requested_amount) }}</span></span>
                                @endif
                                @if ($funding->approved_amount)
                                    <span>{{ __('researcher.funding.approved') }}: <span class="font-numeric font-semibold tabular-nums text-ink-900">{{ $funding->currency }} {{ number_format((float) $funding->approved_amount) }}</span></span>
                                @endif
                                @if ($funding->starts_on || $funding->ends_on)
                                    <span>{{ __('researcher.funding.period') }}: {{ $funding->starts_on?->isoFormat('MMM YYYY') }} – {{ $funding->ends_on?->isoFormat('MMM YYYY') }}</span>
                                @endif
                            </p>

                            @if ($funding->decision)
                                <p class="mt-2 rounded-xl bg-ink-50 px-4 py-3 text-[13px] leading-relaxed text-ink-700">{{ $funding->decision }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Opportunities. Nothing is seeded here: an invented grant would be
             an invented fact, so an empty list says so plainly. --}}
        <div>
            <p class="font-display text-[15.5px] font-bold text-ink-950">{{ __('researcher.funding.opportunities') }}</p>

            @if ($opportunities->isEmpty())
                <div class="mt-4 rounded-[1.5rem] border border-dashed border-ink-200 bg-white px-6 py-14 text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-ink-50 text-ink-400">
                        <x-ui-icon name="compass" class="h-6 w-6" />
                    </span>
                    <p class="mt-5 font-display text-[16px] font-bold text-ink-950">{{ __('researcher.funding.none') }}</p>
                    <p class="mx-auto mt-2 max-w-sm text-[13.5px] leading-relaxed text-ink-500">{{ __('researcher.funding.none_lead') }}</p>
                </div>
            @else
                <div class="mt-4 grid gap-4 lg:grid-cols-2">
                    @foreach ($opportunities as $opportunity)
                        <a href="{{ route('researcher.funding.show', $opportunity) }}"
                           class="group flex flex-col rounded-2xl border border-ink-100 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-brand-300">

                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-display text-[16px] font-bold leading-snug text-ink-950 transition group-hover:text-brand-700">{{ $opportunity->title }}</h3>
                                <span class="shrink-0 rounded-full bg-brand-50 px-2.5 py-1 text-[11.5px] font-semibold text-brand-700">
                                    {{ $categories[$opportunity->category] ?? $opportunity->category }}
                                </span>
                            </div>

                            @if ($opportunity->organization)
                                <p class="mt-1.5 text-[12.5px] text-ink-500">{{ $opportunity->organization }}</p>
                            @endif

                            @if ($opportunity->description)
                                <p class="mt-3 line-clamp-2 text-[13.5px] leading-relaxed text-ink-600">{{ $opportunity->description }}</p>
                            @endif

                            <div class="mt-auto flex flex-wrap items-center gap-x-5 gap-y-1 pt-5 text-[12.5px] text-ink-500">
                                <span class="inline-flex items-center gap-1.5">
                                    <x-ui-icon name="calendar" class="h-3.5 w-3.5" />
                                    {{ $opportunity->deadline?->isoFormat('D MMM YYYY') ?? __('researcher.funding.no_deadline') }}
                                </span>
                                @if ($opportunity->amount)
                                    <span class="font-semibold text-ink-700">{{ $opportunity->amount }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif

            @if ($closed->isNotEmpty())
                <p class="mt-8 text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('researcher.funding.closed') }}</p>
                <ul class="mt-3 space-y-2">
                    @foreach ($closed as $opportunity)
                        <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-ink-100 bg-ink-50/60 px-4 py-3">
                            <span class="text-[13.5px] text-ink-600">{{ $opportunity->title }}</span>
                            <span class="text-[12px] text-ink-400">{{ $opportunity->deadline?->isoFormat('D MMM YYYY') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-layouts.researcher>
