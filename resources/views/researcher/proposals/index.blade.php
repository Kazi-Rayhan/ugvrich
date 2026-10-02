<x-layouts.researcher :title="__('researcher.proposals.title')">

    @php $statuses = \App\Models\ResearchProposal::statuses(); @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-5">
            <div class="max-w-2xl">
                <h2 class="font-display text-[22px] font-bold tracking-tight text-ink-950 sm:text-[26px]">{{ __('researcher.proposals.title') }}</h2>
                <p class="mt-2 text-[14.5px] leading-[1.8] text-ink-600">{{ __('researcher.proposals.lead') }}</p>
            </div>

            <a href="{{ route('researcher.proposals.create') }}" class="btn-lead group shrink-0">
                <span class="relative">{{ __('researcher.proposals.new') }}</span>
                <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
            </a>
        </div>

        {{-- Approved ideas with nothing written yet: the obvious next move --}}
        @if ($readyIdeas->isNotEmpty())
            <div class="rounded-[1.5rem] border border-gold-200 bg-gold-100/50 p-6">
                <p class="font-display text-[15px] font-bold text-ink-950">{{ __('researcher.proposals.ready') }}</p>

                <ul class="mt-4 space-y-2.5">
                    @foreach ($readyIdeas as $idea)
                        <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-white px-4 py-3">
                            <span class="min-w-0">
                                <span class="block truncate text-[14px] font-semibold text-ink-950">{{ $idea->title }}</span>
                                <span class="text-[12.5px] text-ink-500">{{ $idea->department }} · {{ $idea->research_field }}</span>
                            </span>

                            <a href="{{ route('researcher.proposals.create', ['idea' => $idea->id]) }}"
                               class="inline-flex shrink-0 items-center gap-1.5 text-[13px] font-semibold text-brand-700 hover:text-brand-600">
                                {{ __('researcher.proposals.from_idea') }}
                                <x-ui-icon name="arrow-right" class="h-3.5 w-3.5" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($proposals->isEmpty())
            <div class="rounded-[1.5rem] border border-dashed border-ink-200 bg-white px-6 py-16 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                    <x-ui-icon name="document" class="h-6 w-6" />
                </span>
                <p class="mt-5 font-display text-[17px] font-bold text-ink-950">{{ __('researcher.proposals.none') }}</p>
                <p class="mx-auto mt-2 max-w-md text-[14px] leading-relaxed text-ink-500">{{ __('researcher.proposals.none_lead') }}</p>
            </div>
        @else
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($proposals as $proposal)
                    <a href="{{ route('researcher.proposals.show', $proposal) }}"
                       class="group flex flex-col rounded-2xl border border-ink-100 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-[0_24px_50px_-40px_rgba(2,34,81,0.5)]">

                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-display text-[16px] font-bold leading-snug text-ink-950 transition group-hover:text-brand-700">{{ $proposal->title }}</h3>
                            <x-researcher-status :status="$proposal->status" :labels="$statuses" />
                        </div>

                        <p class="mt-2 text-[12.5px] text-ink-500">{{ $proposal->department }} · {{ $proposal->research_field }}</p>
                        <p class="mt-3 line-clamp-2 text-[13.5px] leading-relaxed text-ink-600">{{ $proposal->summary }}</p>

                        <div class="mt-auto flex items-center gap-4 pt-5 text-[12px] text-ink-400">
                            <span>{{ $proposal->reference() }}</span>
                            @if ($proposal->reviews_count)
                                <span class="inline-flex items-center gap-1">
                                    <x-ui-icon name="chat" class="h-3.5 w-3.5" />
                                    {{ $proposal->reviews_count }}
                                </span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.researcher>
