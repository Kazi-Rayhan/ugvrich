@php
    use App\Models\ResearchManuscript;
    use App\Models\ResearchReview;

    $statuses = ResearchManuscript::statuses();
    $flow = ResearchManuscript::flow();
    $reached = array_search($manuscript->status, $flow, true);
    $f = fn (string $key) => __('researcher.manuscripts.form.'.$key);

    $details = [
        'abstract' => $manuscript->abstract,
        'primary_author' => $manuscript->primary_author,
        'co_authors' => $manuscript->co_authors,
        'corresponding' => $manuscript->corresponding_author,
        'affiliation' => $manuscript->affiliation,
        'target_journal' => $manuscript->target_journal,
        'publisher' => $manuscript->publisher,
        'quartile' => $manuscript->quartile,
        'funding_source' => $manuscript->funding_source,
        'ethics' => $manuscript->ethics_approval,
        'conflict' => $manuscript->conflict_of_interest,
        'ai_use' => $manuscript->ai_use_declaration,
        'citation_style' => $manuscript->citation_style ? (ResearchManuscript::citationStyles()[$manuscript->citation_style] ?? $manuscript->citation_style) : null,
        'references' => $manuscript->references_list,
        'doi' => $manuscript->doi,
    ];
@endphp

<x-layouts.researcher :title="$manuscript->title">

    <div class="max-w-4xl space-y-6">

        <div class="rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-7">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="font-numeric text-[12px] font-bold tabular-nums text-ink-400">{{ $manuscript->reference() }}</p>
                    <h2 class="mt-1 font-display text-[20px] font-bold leading-snug text-ink-950 sm:text-[24px]">{{ $manuscript->title }}</h2>

                    @if ($manuscript->project)
                        <p class="mt-2 text-[13px] text-ink-500">
                            {{ __('researcher.manuscripts.project') }}:
                            <a href="{{ route('researcher.projects.show', $manuscript->project) }}" class="text-brand-700 hover:text-brand-600">{{ $manuscript->project->title }}</a>
                        </p>
                    @endif
                </div>

                <x-researcher-status :status="$manuscript->status" :labels="$statuses" class="!text-[12.5px]" />
            </div>

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
                @if ($manuscript->isEditable())
                    <a href="{{ route('researcher.manuscripts.edit', $manuscript) }}" class="btn-primary">
                        {{ __('researcher.manuscripts.edit') }} <x-ui-icon name="arrow-right" class="h-4 w-4" />
                    </a>
                @else
                    <p class="text-[13px] text-ink-500">{{ __('researcher.manuscripts.locked') }}</p>
                @endif

                {{-- Files are streamed through an authorised route, never linked directly. --}}
                @foreach (['manuscript_file' => 'manuscript_file', 'supplementary_file' => 'supplementary', 'similarity_report' => 'similarity'] as $field => $label)
                    @if ($manuscript->{$field})
                        <a href="{{ route('researcher.manuscripts.download', [$manuscript, $field]) }}"
                           class="inline-flex items-center gap-2 text-[13px] font-semibold text-brand-700 hover:text-brand-600">
                            <x-ui-icon name="document" class="h-4 w-4" />
                            {{ $f($label) }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-8">
            @if ($manuscript->keywords)
                <div class="mb-6">
                    <p class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ $f('keywords') }}</p>
                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                        @foreach ($manuscript->keywords as $keyword)
                            <span class="rounded-lg bg-ink-50 px-2.5 py-1 text-[12.5px] font-medium text-ink-700">{{ $keyword }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            <dl class="space-y-6">
                @foreach ($details as $key => $value)
                    @continue(blank($value))

                    <div>
                        <dt class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ $f($key) }}</dt>
                        <dd class="mt-2 whitespace-pre-line text-[14.5px] leading-[1.85] text-ink-700">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- Review history, never overwritten --}}
        <div class="rounded-[1.5rem] border border-ink-100 bg-white">
            <p class="border-b border-ink-100 px-6 py-4 font-display text-[15.5px] font-bold text-ink-950">{{ __('researcher.manuscripts.reviews') }}</p>

            @if ($manuscript->reviews->isEmpty())
                <p class="px-6 py-8 text-[13.5px] text-ink-500">{{ __('researcher.manuscripts.no_reviews') }}</p>
            @else
                <ul class="divide-y divide-ink-100">
                    @foreach ($manuscript->reviews as $review)
                        <li class="px-6 py-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <p class="text-[14px] font-bold text-ink-950">{{ ResearchReview::decisions()[$review->decision] ?? $review->decision }}</p>
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
