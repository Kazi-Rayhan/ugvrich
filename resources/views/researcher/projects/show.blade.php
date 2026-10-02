@php
    use App\Models\Project;
    use App\Models\ProjectDocument;
    use App\Models\ResearchFunding;

    $statuses = Project::researchStatuses();
    $kinds = ProjectDocument::kinds();
    $fundings = $project->researchProposal?->fundings ?? collect();
@endphp

<x-layouts.researcher :title="$project->title">

    <div class="max-w-4xl space-y-6">

        {{-- The project at a glance --}}
        <div class="rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-7">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    @if ($project->code)
                        <p class="font-numeric text-[12px] font-bold tabular-nums text-ink-400">{{ $project->code }}</p>
                    @endif
                    <h2 class="mt-1 font-display text-[20px] font-bold leading-snug text-ink-950 sm:text-[24px]">{{ $project->title }}</h2>
                    <p class="mt-2 text-[13px] text-ink-500">{{ $project->department_name ?? $project->department }}</p>
                </div>

                <div class="flex shrink-0 flex-col items-end gap-2">
                    <x-researcher-status :status="$project->status" :labels="$statuses" class="!text-[12.5px]" />
                    @if ($project->visibility === 'internal')
                        <span class="rounded-full bg-ink-100 px-2.5 py-1 text-[11px] font-semibold text-ink-500">{{ __('researcher.projects.internal') }}</span>
                    @endif
                </div>
            </div>

            <div class="mt-6">
                <div class="flex items-center justify-between text-[12.5px] font-semibold">
                    <span class="text-ink-600">{{ __('researcher.projects.progress') }}</span>
                    <span class="font-numeric tabular-nums text-brand-700">{{ (int) $project->progress }}%</span>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-ink-100">
                    <div class="h-full rounded-full bg-brand-600 transition-all duration-700" style="width: {{ (int) $project->progress }}%"></div>
                </div>
            </div>

            <dl class="mt-7 grid gap-5 border-t border-ink-100 pt-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    'pi' => $project->lead_name,
                    'dates' => trim(($project->start_date?->isoFormat('D MMM YYYY') ?? '').' – '.($project->deadline?->isoFormat('D MMM YYYY') ?? ''), ' –'),
                    'budget' => $project->budget ? number_format((float) $project->budget) : null,
                ] as $key => $value)
                    <div>
                        <dt class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('researcher.projects.'.$key) }}</dt>
                        <dd class="mt-1.5 text-[14px] text-ink-800">{{ filled($value) ? $value : __('researcher.projects.not_set') }}</dd>
                    </div>
                @endforeach
            </dl>

            @if ($project->researchProposal)
                <p class="mt-6 inline-flex items-center gap-2 rounded-full bg-ink-50 px-3.5 py-1.5 text-[12.5px] text-ink-600">
                    <x-ui-icon name="document" class="h-3.5 w-3.5 text-brand-600" />
                    <span class="font-semibold text-ink-800">{{ __('researcher.projects.from_proposal') }}:</span>
                    <a href="{{ route('researcher.proposals.show', $project->researchProposal) }}" class="text-brand-700 hover:text-brand-600">{{ $project->researchProposal->reference() }}</a>
                </p>
            @endif
        </div>

        {{-- Lifecycle --}}
        <div class="rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-7">
            <p class="font-display text-[15.5px] font-bold text-ink-950">{{ __('researcher.projects.lifecycle') }}</p>

            <dl class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (['ethics' => 'ethics_status', 'data_collection' => 'data_collection_status', 'analysis' => 'analysis_status', 'manuscript' => 'manuscript_status'] as $key => $column)
                    <div class="rounded-2xl bg-ink-50/70 p-4">
                        <dt class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('researcher.projects.'.$key) }}</dt>
                        <dd class="mt-1.5 text-[13.5px] font-semibold {{ $project->{$column} ? 'text-ink-800' : 'text-ink-400' }}">
                            {{ $project->{$column} ?: __('researcher.projects.not_set') }}
                        </dd>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- Funding, read-only: the decision is the wing's --}}
        @if ($fundings->isNotEmpty())
            <div class="rounded-[1.5rem] border border-ink-100 bg-white">
                <p class="border-b border-ink-100 px-6 py-4 font-display text-[15.5px] font-bold text-ink-950">{{ __('researcher.funding.title') }}</p>

                <ul class="divide-y divide-ink-100">
                    @foreach ($fundings as $funding)
                        <li class="px-6 py-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <p class="text-[14px] font-bold text-ink-950">{{ $funding->source ?: __('researcher.funding.title') }}</p>
                                <x-researcher-status :status="$funding->status" :labels="\App\Models\ResearchFunding::statuses()" />
                            </div>

                            <p class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-[13px] text-ink-600">
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
                                <p class="mt-2 whitespace-pre-line text-[13px] leading-relaxed text-ink-600">{{ $funding->decision }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Documents: private, streamed through an authorised route --}}
        <div class="rounded-[1.5rem] border border-ink-100 bg-white">
            <div class="border-b border-ink-100 px-6 py-4">
                <p class="font-display text-[15.5px] font-bold text-ink-950">{{ __('researcher.projects.documents') }}</p>
                <p class="mt-1 text-[12.5px] text-ink-500">{{ __('researcher.projects.private_note') }}</p>
            </div>

            @if ($project->documents->isEmpty())
                <p class="px-6 py-8 text-[13.5px] leading-relaxed text-ink-500">{{ __('researcher.projects.no_documents') }}</p>
            @else
                <ul class="divide-y divide-ink-100">
                    @foreach ($project->documents as $document)
                        <li class="flex flex-wrap items-center justify-between gap-3 px-6 py-4">
                            <span class="min-w-0">
                                <span class="block text-[14px] font-semibold text-ink-950">{{ $document->title }}</span>
                                <span class="text-[12.5px] text-ink-500">{{ $kinds[$document->kind] ?? $document->kind }} · {{ $document->created_at?->isoFormat('D MMM YYYY') }}</span>
                            </span>

                            <a href="{{ route('researcher.projects.documents.download', [$project, $document]) }}"
                               class="inline-flex shrink-0 items-center gap-2 text-[13px] font-semibold text-brand-700 hover:text-brand-600">
                                <x-ui-icon name="upload" class="h-4 w-4 rotate-180" />
                                {{ __('site.actions.view_all') }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Progress history, append-only --}}
        <div class="rounded-[1.5rem] border border-ink-100 bg-white">
            <p class="border-b border-ink-100 px-6 py-4 font-display text-[15.5px] font-bold text-ink-950">{{ __('researcher.projects.updates') }}</p>

            <div class="border-b border-ink-100 px-6 py-5">
                <form method="POST" action="{{ route('researcher.projects.updates.store', $project) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <label for="comment" class="field-label">{{ __('researcher.projects.update_comment') }} <span class="text-brand-600">*</span></label>
                        <textarea id="comment" name="comment" rows="3" required class="field-input mt-2">{{ old('comment') }}</textarea>
                        @error('comment') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="progress" class="field-label">{{ __('researcher.projects.update_progress') }}</label>
                            <input id="progress" name="progress" type="number" min="0" max="100" value="{{ old('progress', (int) $project->progress) }}" class="field-input mt-2">
                        </div>
                        <div>
                            <label for="document" class="field-label">{{ __('researcher.projects.update_file') }}</label>
                            <input id="document" name="document" type="file"
                                   class="field-input mt-2 file:mr-3 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-[13px] file:font-semibold file:text-brand-700">
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <button type="submit" class="btn-primary">
                            {{ __('researcher.projects.add_update') }} <x-ui-icon name="plus" class="h-4 w-4" />
                        </button>
                        <p class="text-[12.5px] text-ink-500">{{ __('researcher.projects.update_note') }}</p>
                    </div>
                </form>
            </div>

            @if ($project->updates->isEmpty())
                <p class="px-6 py-8 text-[13.5px] text-ink-500">{{ __('researcher.projects.no_updates') }}</p>
            @else
                <ul class="divide-y divide-ink-100">
                    @foreach ($project->updates as $update)
                        <li class="px-6 py-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <p class="text-[13.5px] font-semibold text-ink-900">
                                    {{ $update->author?->name ?? '—' }}
                                    @if ($update->progress !== null)
                                        <span class="ms-2 font-numeric text-[12.5px] tabular-nums text-brand-700">{{ $update->progress }}%</span>
                                    @endif
                                </p>
                                <p class="text-[12px] text-ink-400">{{ $update->created_at?->isoFormat('D MMM YYYY, h:mm a') }}</p>
                            </div>

                            <p class="mt-2 whitespace-pre-line text-[13.5px] leading-[1.8] text-ink-600">{{ $update->comment }}</p>

                            @if ($update->document)
                                <a href="{{ route('researcher.projects.updates.download', [$project, $update]) }}"
                                   class="mt-2 inline-flex items-center gap-2 text-[12.5px] font-semibold text-brand-700 hover:text-brand-600">
                                    <x-ui-icon name="document" class="h-3.5 w-3.5" />
                                    {{ basename($update->document) }}
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-layouts.researcher>
