@php
    use App\Models\ResearchIdea;

    $statuses = ResearchIdea::statuses();

    /* The queue in four numbers, so the page answers "where do things stand"
       before anything has to be read. Counted off the collection that is
       already loaded rather than with four more queries. */
    $counts = [
        'drafts' => $ideas->where('status', ResearchIdea::DRAFT)->count(),
        'reviewing' => $ideas->whereIn('status', [ResearchIdea::SUBMITTED, ResearchIdea::UNDER_REVIEW])->count(),
        'revision' => $ideas->where('status', ResearchIdea::REVISION)->count(),
        'approved' => $ideas->whereIn('status', [ResearchIdea::APPROVED, ResearchIdea::PROPOSAL])->count(),
    ];

    /* A stripe down the side of each card, so status is visible at a glance
       across the whole list rather than one badge at a time. */
    $stripe = fn (string $status) => match ($status) {
        ResearchIdea::DRAFT => 'bg-ink-200',
        ResearchIdea::SUBMITTED, ResearchIdea::UNDER_REVIEW => 'bg-sky-400',
        ResearchIdea::REVISION => 'bg-amber-400',
        ResearchIdea::APPROVED, ResearchIdea::PROPOSAL => 'bg-brand-500',
        ResearchIdea::REJECTED => 'bg-rose-400',
        default => 'bg-ink-200',
    };
@endphp

<x-layouts.researcher :title="__('researcher.ideas.title')">

    <x-portal-heading :title="__('researcher.ideas.title')" :lead="__('researcher.ideas.lead')">
        <x-slot:actions>
            <a href="{{ route('researcher.ideas.create') }}" class="btn-lead group">
                <span class="relative">{{ __('researcher.ideas.new') }}</span>
                <x-ui-icon name="plus" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:rotate-90" />
            </a>
        </x-slot:actions>
    </x-portal-heading>

    @if ($ideas->isEmpty())
        <div class="rounded-[1.5rem] border border-dashed border-ink-200 bg-white px-6 py-20 text-center">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                <x-ui-icon name="lightbulb" class="h-6 w-6" />
            </span>
            <p class="mt-5 font-display text-[18px] font-bold text-ink-950">{{ __('researcher.ideas.none') }}</p>
            <p class="mx-auto mt-2 max-w-sm text-[14px] leading-relaxed text-ink-500">{{ __('researcher.ideas.none_lead') }}</p>
            <a href="{{ route('researcher.ideas.create') }}" class="btn-primary mt-7">
                {{ __('researcher.ideas.new') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
            </a>
        </div>
    @else
        <div class="space-y-6">

            {{-- Where things stand, in four numbers --}}
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ([
                    ['drafts', 'text-ink-500', 'bg-ink-100'],
                    ['reviewing', 'text-sky-700', 'bg-sky-400'],
                    ['revision', 'text-amber-700', 'bg-amber-400'],
                    ['approved', 'text-brand-700', 'bg-brand-500'],
                ] as [$key, $tone, $dot])
                    <div @class([
                        'flex items-center gap-3.5 rounded-2xl border bg-white px-5 py-4 transition duration-300',
                        'border-ink-100' => $counts[$key] === 0,
                        'border-ink-200' => $counts[$key] > 0,
                        'opacity-60' => $counts[$key] === 0,
                    ])>
                        <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $dot }}"></span>
                        <span class="font-numeric text-[22px] font-bold leading-none tabular-nums {{ $counts[$key] ? $tone : 'text-ink-400' }}">{{ $counts[$key] }}</span>
                        <span class="text-[13px] font-medium leading-snug text-ink-600">{{ __('researcher.ideas.counts.'.$key) }}</span>
                    </div>
                @endforeach
            </div>

            {{-- The ideas themselves --}}
            <div class="grid gap-4 xl:grid-cols-2">
                @foreach ($ideas as $idea)
                    <a href="{{ route('researcher.ideas.show', $idea) }}"
                       class="group relative flex flex-col overflow-hidden rounded-[1.25rem] border border-ink-100 bg-white ps-1.5 transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-[0_26px_54px_-42px_rgba(2,34,81,0.55)]">

                        <span class="absolute inset-y-0 start-0 w-1.5 {{ $stripe($idea->status) }}" aria-hidden="true"></span>

                        <div class="flex flex-1 flex-col p-6">
                            <div class="flex items-start justify-between gap-3">
                                <h3 class="font-display text-[16.5px] font-bold leading-snug text-ink-950 transition group-hover:text-brand-700">{{ $idea->title }}</h3>
                                <x-researcher-status :status="$idea->status" :labels="$statuses" />
                            </div>

                            <p class="mt-2 text-[12.5px] text-ink-500">{{ $idea->department }} · {{ $idea->research_field }}</p>

                            @if ($idea->description)
                                <p class="mt-3 line-clamp-2 text-[13.5px] leading-relaxed text-ink-600">{{ $idea->description }}</p>
                            @endif

                            @if ($idea->keywords)
                                <div class="mt-4 flex flex-wrap gap-1.5">
                                    @foreach (array_slice($idea->keywords, 0, 3) as $keyword)
                                        <span class="rounded-lg bg-ink-50 px-2.5 py-1 text-[11.5px] font-medium text-ink-600">{{ $keyword }}</span>
                                    @endforeach

                                    @if (count($idea->keywords) > 3)
                                        <span class="rounded-lg bg-ink-50 px-2.5 py-1 font-numeric text-[11.5px] font-medium text-ink-500">+{{ count($idea->keywords) - 3 }}</span>
                                    @endif
                                </div>
                            @endif

                            <div class="mt-auto flex items-center gap-4 pt-5 text-[12px] text-ink-400">
                                <span>{{ $idea->submitted_at ? __('researcher.ideas.submitted_on').' '.$idea->submitted_at->isoFormat('D MMM YYYY') : $idea->updated_at?->diffForHumans() }}</span>

                                @if ($idea->reviews_count)
                                    <span class="inline-flex items-center gap-1">
                                        <x-ui-icon name="chat" class="h-3.5 w-3.5" />
                                        {{ $idea->reviews_count }}
                                    </span>
                                @endif

                                <span class="ms-auto inline-flex items-center gap-1 font-semibold text-ink-300 transition group-hover:text-brand-600">
                                    <x-ui-icon name="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1" />
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
</x-layouts.researcher>
