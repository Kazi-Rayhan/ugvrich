@php
    use App\Models\ResearchProposal;
    use App\Models\ResearchReview;

    $statuses = ResearchProposal::statuses();
    $flow = ResearchProposal::flow();
    $reached = array_search($proposal->status, $flow, true);

    /* Everything written, in the order it was asked for. Blank sections are
       simply not shown — a proposal is allowed to be partly answered. */
    $sections = [
        'form.summary' => $proposal->summary,
        'form.background' => $proposal->background,
        'form.gap' => $proposal->research_gap,
        'form.objectives' => $proposal->objectives,
        'form.questions' => $proposal->research_questions,
        'form.hypothesis' => $proposal->hypothesis,
        'form.methodology' => $proposal->methodology,
        'form.study_design' => $proposal->study_design,
        'form.population' => $proposal->population,
        'form.collection' => $proposal->data_collection,
        'form.analysis' => $proposal->data_analysis,
        'form.outcome' => $proposal->expected_outcome,
        'form.impact' => $proposal->expected_impact,
        'form.duration' => $proposal->duration,
        'form.timeline' => $proposal->timeline,
        'form.budget' => $proposal->budget,
        'form.funding' => $proposal->funding_needed,
        'form.pi' => $proposal->principal_investigator,
        'form.team' => $proposal->research_team,
        'form.collaborators' => $proposal->collaborators_needed,
    ];
@endphp

<x-layouts.researcher :title="$proposal->title">

    <div class="max-w-4xl space-y-6">

        {{-- Where it has got to --}}
        <div class="rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-7">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="font-numeric text-[12px] font-bold tabular-nums text-ink-400">{{ $proposal->reference() }}</p>
                    <h2 class="mt-1 font-display text-[20px] font-bold leading-snug text-ink-950 sm:text-[24px]">{{ $proposal->title }}</h2>
                    <p class="mt-2 text-[13px] text-ink-500">{{ $proposal->department }} · {{ $proposal->research_field }}@if ($proposal->research_area) · {{ $proposal->research_area }}@endif</p>
                </div>

                <x-researcher-status :status="$proposal->status" :labels="$statuses" class="!text-[12.5px]" />
            </div>

            @if ($proposal->idea)
                <p class="mt-4 inline-flex items-center gap-2 rounded-full bg-ink-50 px-3.5 py-1.5 text-[12.5px] text-ink-600">
                    <x-ui-icon name="lightbulb" class="h-3.5 w-3.5 text-gold-500" />
                    <span class="font-semibold text-ink-800">{{ __('researcher.proposals.linked_idea') }}:</span>
                    <a href="{{ route('researcher.ideas.show', $proposal->idea) }}" class="text-brand-700 hover:text-brand-600">{{ $proposal->idea->title }}</a>
                </p>
            @endif

            {{-- The rail, so the status is a position rather than a word --}}
            <ol class="mt-7 flex flex-wrap items-center gap-x-2 gap-y-3">
                @foreach ($flow as $i => $step)
                    @php $done = $reached !== false && $i <= $reached; @endphp

                    <li class="flex items-center gap-2">
                        <span @class([
                            'flex h-6 w-6 items-center justify-center rounded-full text-[11px] font-bold',
                            'bg-brand-600 text-white' => $done,
                            'bg-ink-100 text-ink-400' => ! $done,
                        ])>{{ $i + 1 }}</span>
                        <span class="text-[12.5px] font-medium {{ $done ? 'text-ink-800' : 'text-ink-400' }}">{{ $statuses[$step] }}</span>
                        @unless ($loop->last)
                            <span class="mx-1 h-px w-5 bg-ink-200" aria-hidden="true"></span>
                        @endunless
                    </li>
                @endforeach
            </ol>

            <div class="mt-7 flex flex-wrap items-center gap-3 border-t border-ink-100 pt-6">
                @if ($proposal->isEditable())
                    <a href="{{ route('researcher.proposals.edit', $proposal) }}" class="btn-primary">
                        {{ __('researcher.proposals.edit') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                @else
                    <p class="text-[13px] text-ink-500">{{ __('researcher.proposals.locked') }}</p>
                @endif

                @foreach (['proposal_document' => 'form.proposal_document', 'document' => 'form.supporting'] as $file => $label)
                    @if ($proposal->{$file})
                        <a href="{{ Storage::url($proposal->{$file}) }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 text-[13px] font-semibold text-brand-700 hover:text-brand-600">
                            <x-ui-icon name="document" class="h-4 w-4" />
                            {{ __('researcher.proposals.'.$label) }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- What was written --}}
        <div class="rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-8">
            <dl class="space-y-6">
                @foreach ($sections as $key => $value)
                    @continue(blank($value))

                    <div>
                        <dt class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('researcher.proposals.'.$key) }}</dt>
                        <dd class="mt-2 whitespace-pre-line text-[14.5px] leading-[1.85] text-ink-700">{{ $value }}</dd>
                    </div>
                @endforeach

                @if ($proposal->sdgs)
                    <div>
                        <dt class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('research_hub.forms.proposal.sdgs') }}</dt>
                        <dd class="mt-2.5 flex flex-wrap gap-1.5">
                            @foreach ($proposal->sdgs as $sdg)
                                <span class="rounded-lg border border-navy-100 bg-ink-50 px-2.5 py-1 text-[12.5px] font-semibold text-navy-700">{{ $sdg }}</span>
                            @endforeach
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        {{-- What the reviewers said --}}
        <div class="rounded-[1.5rem] border border-ink-100 bg-white">
            <p class="border-b border-ink-100 px-6 py-4 font-display text-[15.5px] font-bold text-ink-950">{{ __('researcher.proposals.reviews') }}</p>

            @if ($proposal->reviews->isEmpty())
                <p class="px-6 py-8 text-[13.5px] text-ink-500">{{ __('researcher.proposals.no_reviews') }}</p>
            @else
                <ul class="divide-y divide-ink-100">
                    @foreach ($proposal->reviews as $review)
                        <li class="px-6 py-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <p class="text-[14px] font-bold text-ink-950">
                                    {{ ResearchReview::decisions()[$review->decision] ?? $review->decision }}
                                </p>
                                <p class="text-[12px] text-ink-400">{{ $review->created_at?->isoFormat('D MMM YYYY') }}</p>
                            </div>

                            @if ($review->comment)
                                <p class="mt-2 whitespace-pre-line text-[13.5px] leading-[1.8] text-ink-600">{{ $review->comment }}</p>
                            @endif

                            @if ($review->recommendation)
                                <p class="mt-2 rounded-xl bg-ink-50 px-4 py-3 text-[13px] leading-relaxed text-ink-700">{{ $review->recommendation }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-layouts.researcher>
