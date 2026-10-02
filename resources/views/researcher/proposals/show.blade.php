@php
    use App\Models\ResearchProposal;
    use App\Models\ResearchReview;

    $statuses = ResearchProposal::statuses();
    $flow = ResearchProposal::flow();
    $reached = array_search($proposal->status, $flow, true);

    $researcher = [
        'form.full_name' => $proposal->name,
        'form.email' => $proposal->email,
        'form.phone' => $proposal->phone,
        'form.researcher_type' => $proposal->researcher_type
            ? __('research_hub.forms.proposal.researcher_types.'.$proposal->researcher_type)
            : null,
        'form.researcher_type_other' => $proposal->researcher_type_other,
        'form.researcher_department' => $proposal->researcher_department,
        'form.designation' => $proposal->designation,
        'form.institution' => $proposal->institution,
    ];

    $category = [
        'form.research_type' => $proposal->research_type
            ? __('research_hub.forms.proposal.research_types.'.$proposal->research_type)
            : null,
        'form.department' => $proposal->department,
        'form.research_field' => $proposal->research_field,
        'form.research_area' => $proposal->research_area,
    ];

    $team = [
        'form.pi' => $proposal->principal_investigator,
        'form.co_researchers' => collect($proposal->co_researchers ?? [])
            ->map(fn (array $researcher) => collect([
                $researcher['name'] ?? null,
                $researcher['designation'] ?? null,
                $researcher['department'] ?? null,
            ])->filter()->join(' · '))
            ->filter()
            ->join("\n"),
        'form.external_collaborator' => $proposal->external_collaborator,
        'form.external_department' => $proposal->external_department,
        'form.external_institution' => $proposal->external_institution,
    ];

    $details = [
        'form.background' => $proposal->background ?: $proposal->summary,
        'form.gap' => $proposal->research_gap,
        'form.objectives' => $proposal->objectives,
        'form.questions' => $proposal->research_questions,
        'form.methodology' => $proposal->methodology,
        'form.outcome' => $proposal->expected_outcome,
        'form.impact' => $proposal->expected_impact,
        'form.innovation_novelty' => $proposal->innovation_novelty,
        'form.timeline' => $proposal->timeline,
    ];

    $funding = [
        'form.funding_required' => $proposal->funding_required,
        'form.budget' => $proposal->budget,
        'form.budget_breakdown' => $proposal->budget_breakdown,
        'form.funding_source' => $proposal->funding_source,
        'form.external_funding_applied' => $proposal->external_funding_applied,
    ];

    $ethics = [
        'form.human_participants' => $proposal->human_participants,
        'form.sensitive_data' => $proposal->sensitive_data,
        'form.ethical_approval_required' => $proposal->ethical_approval_required,
        'form.informed_consent_required' => $proposal->informed_consent_required,
        'form.ai_used' => $proposal->ai_used,
    ];
@endphp

<x-layouts.researcher :title="$proposal->title">
    <x-portal-heading :title="$proposal->title"
                      :lead="collect([$proposal->department, $proposal->research_field, $proposal->research_area])->filter()->join(' · ')">
        <x-slot:actions>
            <a href="{{ route('researcher.proposals.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-ink-200 px-4 py-2.5 text-[13.5px] font-semibold text-ink-700 transition hover:border-brand-300 hover:text-brand-700">
                <x-ui-icon name="arrow-left" class="h-4 w-4" />
                {{ __('researcher.proposals.title') }}
            </a>

            @if ($proposal->isEditable())
                <a href="{{ route('researcher.proposals.edit', $proposal) }}" class="btn-lead group">
                    <span class="relative">{{ __('researcher.proposals.edit') }}</span>
                    <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
                </a>
            @endif
        </x-slot:actions>
    </x-portal-heading>

    <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_19rem]">
        <div class="min-w-0 space-y-6">
            <section class="rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-numeric text-[12px] font-bold tabular-nums tracking-[0.12em] text-ink-400">{{ $proposal->reference() }}</p>
                        <p class="mt-2 text-[13px] text-ink-500">
                            {{ __('researcher.proposals.submitted_on') }} · {{ $proposal->submitted_at?->isoFormat('D MMM YYYY') ?? $proposal->created_at?->isoFormat('D MMM YYYY') }}
                        </p>
                    </div>
                    <x-researcher-status :status="$proposal->status" :labels="$statuses" class="!px-3 !py-1.5 !text-[12px]" />
                </div>

                <div class="mt-7 border-t border-ink-100 pt-6">
                    <h2 class="font-display text-[17px] font-bold text-ink-950">{{ __('researcher.proposals.detail_sections.status') }}</h2>
                    <ol class="mt-5 grid gap-4 sm:grid-cols-5">
                        @foreach ($flow as $i => $step)
                            @php $done = $reached !== false && $i <= $reached; @endphp
                            <li class="flex items-center gap-3 sm:flex-col sm:items-start sm:gap-2">
                                <span @class([
                                    'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[11px] font-bold',
                                    'bg-brand-600 text-white' => $done,
                                    'bg-ink-100 text-ink-400' => ! $done,
                                ])>
                                    @if ($done)
                                        <x-ui-icon name="check" class="h-3.5 w-3.5" stroke="3" />
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </span>
                                <span class="text-[12px] font-medium {{ $done ? 'text-ink-800' : 'text-ink-400' }}">{{ $statuses[$step] }}</span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>

            @if ($proposal->isEditable())
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-brand-100 bg-brand-50/70 px-5 py-4">
                    <p class="text-[13.5px] leading-relaxed text-ink-700">{{ __('researcher.proposals.editable_note') }}</p>
                    <a href="{{ route('researcher.proposals.edit', $proposal) }}" class="btn-primary shrink-0">{{ __('researcher.proposals.edit') }}</a>
                </div>
            @endif

            @foreach ([
                'researcher' => $researcher,
                'category' => $category,
                'team' => $team,
                'proposal' => $details,
            ] as $group => $fields)
                @if (collect($fields)->filter(fn ($value) => filled($value))->isNotEmpty() || ($group === 'category' && $proposal->sdgs))
                    <section class="rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-8">
                        <div class="flex items-center gap-3 border-b border-ink-100 pb-4">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-brand-700">
                                <x-ui-icon :name="match ($group) {
                                    'researcher' => 'academic',
                                    'category' => 'sparkles',
                                    'team' => 'users',
                                    default => 'document',
                                }" class="h-[18px] w-[18px]" />
                            </span>
                            <h2 class="font-display text-[16px] font-bold text-ink-950">{{ __('researcher.proposals.detail_sections.'.$group) }}</h2>
                        </div>

                        <dl class="mt-5 grid gap-x-7 gap-y-6 sm:grid-cols-2">
                            @foreach ($fields as $key => $value)
                                @continue(blank($value))
                                <div @class([
                                    'sm:col-span-2' => in_array($key, ['form.background', 'form.gap', 'form.objectives', 'form.questions', 'form.methodology', 'form.outcome', 'form.impact', 'form.innovation_novelty', 'form.timeline', 'form.co_researchers']),
                                    'rounded-xl bg-ink-50/70 p-4' => in_array($group, ['proposal', 'team']),
                                ])>
                                    <dt class="text-[10.5px] font-semibold uppercase tracking-[0.13em] text-ink-400">{{ __('researcher.proposals.'.$key) }}</dt>
                                    <dd class="mt-2 whitespace-pre-line break-words text-[14px] leading-[1.8] text-ink-700">{{ $value }}</dd>
                                </div>
                            @endforeach

                            @if ($group === 'category' && $proposal->sdgs)
                                <div class="sm:col-span-2">
                                    <dt class="text-[10.5px] font-semibold uppercase tracking-[0.13em] text-ink-400">{{ __('research_hub.forms.proposal.sdgs') }}</dt>
                                    <dd class="mt-2.5 flex flex-wrap gap-2">
                                        @foreach ($proposal->sdgs as $sdg)
                                            <span class="rounded-lg border border-navy-100 bg-ink-50 px-3 py-1.5 text-[12px] font-semibold text-navy-700">{{ $sdg }}</span>
                                        @endforeach
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </section>
                @endif
            @endforeach

            @foreach (['funding' => $funding, 'ethics' => $ethics] as $group => $fields)
                @if (collect($fields)->contains(fn ($value) => is_bool($value) || filled($value)))
                    <section class="rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-8">
                        <div class="flex items-center gap-3 border-b border-ink-100 pb-4">
                            <span class="flex h-9 w-9 items-center justify-center rounded-xl {{ $group === 'funding' ? 'bg-gold-100 text-gold-700' : 'bg-sky-50 text-sky-700' }}">
                                <x-ui-icon :name="$group === 'funding' ? 'briefcase' : 'shield'" class="h-[18px] w-[18px]" />
                            </span>
                            <h2 class="font-display text-[16px] font-bold text-ink-950">{{ __('researcher.proposals.detail_sections.'.$group) }}</h2>
                        </div>

                        <dl class="mt-5 grid gap-3 sm:grid-cols-2">
                            @foreach ($fields as $key => $value)
                                @continue($value === null || ($group === 'funding' && $value === false && $key !== 'form.funding_required'))
                                @continue($group === 'funding' && ! $proposal->funding_required && in_array($key, ['form.budget', 'form.budget_breakdown']))
                                <div class="flex items-start justify-between gap-4 rounded-xl bg-ink-50/70 px-4 py-3.5">
                                    <dt class="text-[12px] leading-relaxed text-ink-500">{{ __('researcher.proposals.'.$key) }}</dt>
                                    <dd class="shrink-0 text-right text-[13px] font-semibold text-ink-800">
                                        @if (is_bool($value))
                                            <span @class([
                                                'inline-flex rounded-full px-2.5 py-1 text-[11.5px] font-semibold',
                                                'bg-brand-50 text-brand-700' => $value,
                                                'bg-ink-100 text-ink-500' => ! $value,
                                            ])>{{ __('research_hub.forms.proposal.'.($value ? 'yes' : 'no')) }}</span>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </section>
                @endif
            @endforeach

            <section class="rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-8">
                <div class="flex items-center justify-between gap-4 border-b border-ink-100 pb-4">
                    <div>
                        <h2 class="font-display text-[16px] font-bold text-ink-950">{{ __('researcher.proposals.reviews') }}</h2>
                        <p class="mt-1 text-[12.5px] text-ink-500">{{ __('researcher.proposals.review_history_help') }}</p>
                    </div>
                    @if ($proposal->reviews->isNotEmpty())
                        <span class="rounded-lg bg-ink-50 px-2.5 py-1 font-numeric text-[12px] font-semibold tabular-nums text-ink-500">{{ $proposal->reviews->count() }}</span>
                    @endif
                </div>

                @if ($proposal->reviews->isEmpty())
                    <p class="pt-5 text-[13.5px] leading-relaxed text-ink-500">{{ __('researcher.proposals.no_reviews') }}</p>
                @else
                    <ol class="mt-6 space-y-6">
                        @foreach ($proposal->reviews as $review)
                            <li class="relative flex gap-4 ps-1">
                                @unless ($loop->last)
                                    <span class="absolute start-[13px] top-7 bottom-[-1.5rem] w-px bg-ink-200" aria-hidden="true"></span>
                                @endunless
                                <span class="relative z-10 mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand-50 ring-4 ring-white">
                                    <span class="h-2 w-2 rounded-full bg-brand-600"></span>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-baseline justify-between gap-3">
                                        <p class="text-[14px] font-bold text-ink-950">{{ ResearchReview::decisions()[$review->decision] ?? $review->decision }}</p>
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
            </section>
        </div>

        <aside class="space-y-5 xl:sticky xl:top-[84px]">
            <section class="rounded-[1.5rem] border border-ink-100 bg-white p-5">
                <h2 class="font-display text-[15px] font-bold text-ink-950">{{ __('researcher.proposals.detail_sections.status') }}</h2>
                <div class="mt-4 flex items-center justify-between gap-3">
                    <x-researcher-status :status="$proposal->status" :labels="$statuses" class="!px-3 !py-1.5 !text-[12px]" />
                    <span class="text-[11px] text-ink-400">{{ __('researcher.proposals.reference') }} {{ $proposal->reference() }}</span>
                </div>
                <dl class="mt-5 space-y-4 border-t border-ink-100 pt-4">
                    <div>
                        <dt class="text-[10px] font-semibold uppercase tracking-[0.13em] text-ink-400">{{ __('researcher.proposals.submitted_on') }}</dt>
                        <dd class="mt-1 text-[13px] font-medium text-ink-700">{{ $proposal->submitted_at?->isoFormat('D MMMM YYYY') ?? __('researcher.proposals.not_submitted') }}</dd>
                    </div>
                    @if ($proposal->reviewed_at)
                        <div>
                            <dt class="text-[10px] font-semibold uppercase tracking-[0.13em] text-ink-400">{{ __('researcher.proposals.last_reviewed') }}</dt>
                            <dd class="mt-1 text-[13px] font-medium text-ink-700">{{ $proposal->reviewed_at->isoFormat('D MMMM YYYY') }}</dd>
                        </div>
                    @endif
                </dl>
                @if ($proposal->isEditable())
                    <a href="{{ route('researcher.proposals.edit', $proposal) }}" class="btn-primary mt-5 w-full justify-center">
                        {{ __('researcher.proposals.edit') }}
                    </a>
                @else
                    <p class="mt-5 border-t border-ink-100 pt-4 text-[12.5px] leading-relaxed text-ink-500">{{ __('researcher.proposals.locked') }}</p>
                @endif
            </section>
        </aside>
    </div>
</x-layouts.researcher>
