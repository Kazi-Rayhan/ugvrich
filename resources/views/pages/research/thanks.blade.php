@php
    $isProposal = ($submission['kind'] ?? null) === 'proposal';
    $title = $isProposal
        ? __('research_hub.forms.thanks.proposal_title')
        : __('research_hub.forms.thanks.support_title');
@endphp

<x-layouts.app :title="$title">

    <section class="relative isolate overflow-hidden bg-navy-700 py-20 text-white sm:py-28">
        <div class="pointer-events-none absolute inset-0 -z-10 text-white grid-overlay opacity-[0.12]" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -right-32 -top-32 -z-10 h-96 w-96 rounded-full bg-brand-600/30 blur-[110px]" aria-hidden="true"></div>

        <div class="container-rich max-w-2xl text-center">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-brand-600">
                <x-ui-icon name="check" class="h-8 w-8 text-white" stroke="2.2" />
            </span>

            <h1 class="mt-8 font-display text-[28px] font-bold leading-tight !text-white sm:text-[38px]">{{ $title }}</h1>

            <p class="mt-5 text-[15.5px] leading-[1.85] text-white/75">
                {{ __('research_hub.forms.thanks.lead', ['name' => $submission['name'] ?? '']) }}
            </p>

            <div class="mx-auto mt-9 max-w-sm rounded-2xl border border-white/15 bg-white/[0.07] p-6 text-start backdrop-blur-sm">
                <p class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-brand-300">{{ __('research_hub.forms.thanks.reference') }}</p>
                <p class="mt-2 font-numeric text-[22px] font-bold tabular-nums !text-white">{{ $submission['reference'] ?? '' }}</p>

                @if (! empty($submission['title']))
                    <p class="mt-3 border-t border-white/10 pt-3 text-[14px] leading-relaxed text-white/70">{{ $submission['title'] }}</p>
                @endif

                <p class="mt-3 text-[12.5px] text-white/50">{{ __('research_hub.forms.thanks.reference_note') }}</p>
            </div>

            <div class="mt-10 flex flex-wrap justify-center gap-3">
                <a href="{{ route('research') }}" class="btn-primary">
                    {{ __('research_hub.forms.thanks.back') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                </a>

                <a href="{{ $isProposal ? route('research.support') : route('research.proposal') }}" class="btn-invert">
                    {{ $isProposal ? __('research_hub.forms.thanks.other_support') : __('research_hub.forms.thanks.other_proposal') }}
                </a>
            </div>
        </div>
    </section>
</x-layouts.app>
