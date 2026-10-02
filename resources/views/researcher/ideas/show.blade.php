@php
    use App\Models\ResearchIdea;
    use App\Models\ResearchReview;

    $statuses = ResearchIdea::statuses();
    $flow = ResearchIdea::flow();
    $reached = array_search($idea->status, $flow, true);

    /* What was written, in two weights: the two that carry the idea take the
       full measure, the rest sit side by side. */
    $long = array_filter([
        'form.problem' => $idea->problem,
        'form.description' => $idea->description,
    ]);

    $short = array_filter([
        'form.motivation' => $idea->motivation,
        'form.contribution' => $idea->expected_contribution,
        'form.team' => $idea->proposed_team,
        'form.collaboration' => $idea->collaboration_requirement,
    ]);
@endphp

<x-layouts.researcher :title="$idea->title">

    <x-portal-heading :title="$idea->title"
                      :lead="collect([$idea->department, $idea->research_field, $idea->research_area])->filter()->join(' · ')">
        <x-slot:actions>
            <a href="{{ route('researcher.ideas.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-ink-200 px-4 py-2.5 text-[13.5px] font-semibold text-ink-700 transition hover:border-brand-300 hover:text-brand-700">
                <x-ui-icon name="arrow-left" class="h-4 w-4" />
                {{ __('researcher.ideas.title') }}
            </a>

            @if ($idea->isEditable())
                <a href="{{ route('researcher.ideas.edit', $idea) }}" class="btn-lead group">
                    <span class="relative">{{ __('researcher.ideas.edit') }}</span>
                    <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                </a>
            @endif
        </x-slot:actions>
    </x-portal-heading>

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">

        {{-- ------------------------------------------------- what was written --}}
        <div class="min-w-0 space-y-6">

            {{-- Approved ideas say so loudly, because the next move is the
                 researcher's and it is easy to miss in a page of prose. --}}
            @if ($idea->isApproved())
                <div class="flex flex-wrap items-center justify-between gap-4 rounded-[1.5rem] border border-gold-200 bg-gold-100/60 p-6">
                    <div class="flex items-start gap-3.5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white text-gold-700">
                            <x-ui-icon name="check" class="h-5 w-5" stroke="2.6" />
                        </span>
                        <p class="max-w-md text-[13.5px] leading-relaxed text-ink-700">{{ __('researcher.ideas.approved_note') }}</p>
                    </div>

                    @if ($idea->proposal)
                        <a href="{{ route('researcher.proposals.show', $idea->proposal) }}" class="btn-ghost shrink-0">
                            {{ __('researcher.proposals.title') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    @else
                        <a href="{{ route('researcher.proposals.create', ['idea' => $idea->id]) }}" class="btn-lead group shrink-0">
                            <span class="relative">{{ __('researcher.ideas.develop_proposal') }}</span>
                            <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                        </a>
                    @endif
                </div>
            @endif

            <div class="rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-8">
                <dl class="space-y-7">
                    @foreach ($long as $key => $value)
                        <div>
                            <dt class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('researcher.ideas.'.$key) }}</dt>
                            <dd class="mt-2.5 whitespace-pre-line text-[15px] leading-[1.85] text-ink-700">{{ $value }}</dd>
                        </div>
                    @endforeach

                    @if ($short)
                        <div class="grid gap-7 border-t border-ink-100 pt-7 sm:grid-cols-2">
                            @foreach ($short as $key => $value)
                                <div>
                                    <dt class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('researcher.ideas.'.$key) }}</dt>
                                    <dd class="mt-2 whitespace-pre-line text-[14px] leading-[1.8] text-ink-700">{{ $value }}</dd>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </dl>
            </div>

            {{-- ------------------------------------------- what the reviewers said --}}
            <div class="rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-8">
                <div class="flex items-center justify-between gap-4">
                    <p class="font-display text-[15.5px] font-bold text-ink-950">{{ __('researcher.ideas.reviews') }}</p>

                    @if ($idea->reviews->isNotEmpty())
                        <span class="rounded-lg bg-ink-50 px-2.5 py-1 font-numeric text-[12px] font-semibold tabular-nums text-ink-500">{{ $idea->reviews->count() }}</span>
                    @endif
                </div>

                @if ($idea->reviews->isEmpty())
                    <p class="mt-5 text-[13.5px] leading-relaxed text-ink-500">{{ __('researcher.ideas.no_reviews') }}</p>
                @else
                    {{-- A thread rather than a table: each decision is an event,
                         and the line makes the order of them obvious. --}}
                    <ol class="mt-6 space-y-6">
                        @foreach ($idea->reviews as $review)
                            <li class="relative flex gap-4 ps-1">
                                @unless ($loop->last)
                                    <span class="absolute start-[13px] top-7 bottom-[-1.5rem] w-px bg-ink-200" aria-hidden="true"></span>
                                @endunless

                                <span class="relative z-10 mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-50 ring-4 ring-white">
                                    <span class="h-2 w-2 rounded-full bg-brand-600"></span>
                                </span>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-baseline justify-between gap-3">
                                        <p class="text-[14px] font-bold text-ink-950">
                                            {{ ResearchReview::decisions()[$review->decision] ?? $review->decision }}
                                        </p>
                                        <p class="text-[12px] text-ink-400">{{ $review->created_at?->isoFormat('D MMM YYYY') }}</p>
                                    </div>

                                    @if ($review->comment)
                                        <p class="mt-2 whitespace-pre-line text-[13.5px] leading-[1.8] text-ink-600">{{ $review->comment }}</p>
                                    @endif

                                    @if ($review->recommendation)
                                        <p class="mt-3 rounded-xl border border-ink-100 bg-ink-50/70 px-4 py-3 text-[13px] leading-relaxed text-ink-700">{{ $review->recommendation }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>
        </div>

        {{-- ------------------------------------------------------- where it stands --}}
        <aside class="space-y-6 xl:sticky xl:top-[84px]">

            <div class="rounded-[1.5rem] border border-ink-100 bg-white p-6">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('researcher.dashboard.activity') }}</p>
                    <x-researcher-status :status="$idea->status" :labels="$statuses" />
                </div>

                {{-- The journey downwards: a status is a position on it rather
                     than a word on its own. --}}
                <ol class="mt-6">
                    @foreach ($flow as $i => $step)
                        @php
                            $done = $reached !== false && $i < $reached;
                            $here = $idea->status === $step;
                        @endphp

                        <li class="relative flex gap-3 pb-5 last:pb-0">
                            @unless ($loop->last)
                                <span @class([
                                    'absolute start-[11px] top-6 bottom-0 w-px',
                                    'bg-brand-300' => $done,
                                    'bg-ink-200' => ! $done,
                                ]) aria-hidden="true"></span>
                            @endunless

                            <span @class([
                                'relative z-10 flex h-[23px] w-[23px] shrink-0 items-center justify-center rounded-full border-2 bg-white font-numeric text-[10.5px] font-bold transition',
                                'border-brand-600 bg-brand-600 text-white ring-4 ring-brand-500/15' => $here,
                                'border-brand-500 bg-brand-50 text-brand-700' => $done && ! $here,
                                'border-ink-200 text-ink-400' => ! $done && ! $here,
                            ])>
                                @if ($done && ! $here)
                                    <x-ui-icon name="check" class="h-3 w-3" stroke="3" />
                                @else
                                    {{ $i + 1 }}
                                @endif
                            </span>

                            <span @class([
                                'pt-0.5 text-[13px] leading-snug',
                                'font-bold text-ink-950' => $here,
                                'font-medium text-ink-600' => $done && ! $here,
                                'text-ink-400' => ! $done && ! $here,
                            ])>{{ $statuses[$step] }}</span>
                        </li>
                    @endforeach
                </ol>

                @unless ($idea->isEditable())
                    <p class="mt-5 border-t border-ink-100 pt-5 text-[12.5px] leading-relaxed text-ink-500">{{ __('researcher.ideas.locked') }}</p>
                @endunless
            </div>

            {{-- The small facts, left out when there is nothing to say --}}
            @php
                $facts = array_filter([
                    __('researcher.ideas.submitted_on') => $idea->submitted_at?->isoFormat('D MMMM YYYY'),
                    __('research_hub.forms.department') => $idea->department,
                    __('research_hub.forms.proposal.field') => $idea->research_field,
                    __('research_hub.forms.proposal.area') => $idea->research_area,
                ]);
            @endphp

            @if ($facts || $idea->keywords || $idea->document || $idea->sdgs)
                <div class="rounded-[1.5rem] border border-ink-100 bg-white p-6">
                    <dl class="space-y-4">
                        @foreach ($facts as $label => $value)
                            <div class="flex items-baseline justify-between gap-4">
                                <dt class="shrink-0 text-[12px] text-ink-400">{{ $label }}</dt>
                                <dd class="text-end text-[13px] font-medium text-ink-800">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </dl>

                    @if ($idea->keywords)
                        <div class="mt-5 border-t border-ink-100 pt-5">
                            <p class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('researcher.ideas.form.keywords') }}</p>
                            <div class="mt-2.5 flex flex-wrap gap-1.5">
                                @foreach ($idea->keywords as $keyword)
                                    <span class="rounded-lg bg-ink-50 px-2.5 py-1 text-[12.5px] font-medium text-ink-700">{{ $keyword }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($idea->sdgs)
                        <div class="mt-5 border-t border-ink-100 pt-5">
                            <p class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('research_hub.forms.proposal.sdgs') }}</p>
                            <div class="mt-2.5 flex flex-wrap gap-1.5">
                                @foreach ($idea->sdgs as $sdg)
                                    <span class="rounded-lg border border-navy-100 bg-white px-2.5 py-1 text-[12.5px] font-semibold text-navy-700">{{ $sdg }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if ($idea->document)
                        <a href="{{ Storage::url($idea->document) }}" target="_blank" rel="noopener"
                           class="mt-5 flex items-center gap-2.5 rounded-xl border border-ink-200 px-4 py-2.5 text-[13px] font-semibold text-ink-700 transition hover:border-brand-300 hover:text-brand-700">
                            <x-ui-icon name="document" class="h-4 w-4 shrink-0" />
                            <span class="truncate">{{ basename($idea->document) }}</span>
                        </a>
                    @endif
                </div>
            @endif
        </aside>
    </div>
</x-layouts.researcher>
