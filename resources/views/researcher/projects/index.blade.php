<x-layouts.researcher :title="__('researcher.projects.title')">

    @php $statuses = \App\Models\Project::researchStatuses(); @endphp

    <div class="space-y-6">
        <div class="max-w-2xl">
            <h2 class="font-display text-[22px] font-bold tracking-tight text-ink-950 sm:text-[26px]">{{ __('researcher.projects.title') }}</h2>
            <p class="mt-2 text-[14.5px] leading-[1.8] text-ink-600">{{ __('researcher.projects.lead') }}</p>
        </div>

        @if ($projects->isEmpty())
            <div class="rounded-[1.5rem] border border-dashed border-ink-200 bg-white px-6 py-16 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                    <x-ui-icon name="briefcase" class="h-6 w-6" />
                </span>
                <p class="mt-5 font-display text-[17px] font-bold text-ink-950">{{ __('researcher.projects.none') }}</p>
                <p class="mx-auto mt-2 max-w-md text-[14px] leading-relaxed text-ink-500">{{ __('researcher.projects.none_lead') }}</p>
            </div>
        @else
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($projects as $project)
                    <a href="{{ route('researcher.projects.show', $project) }}"
                       class="group flex flex-col rounded-2xl border border-ink-100 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-[0_24px_50px_-40px_rgba(2,34,81,0.5)]">

                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-display text-[16px] font-bold leading-snug text-ink-950 transition group-hover:text-brand-700">{{ $project->title }}</h3>
                            <x-researcher-status :status="$project->status" :labels="$statuses" />
                        </div>

                        <p class="mt-2 flex flex-wrap items-center gap-2 text-[12.5px] text-ink-500">
                            @if ($project->code)
                                <span class="font-numeric tabular-nums">{{ $project->code }}</span>
                                <span aria-hidden="true">·</span>
                            @endif
                            <span>{{ $project->department_name ?? $project->department }}</span>

                            @if ($project->visibility === 'internal')
                                <span class="rounded-full bg-ink-100 px-2 py-0.5 text-[11px] font-semibold text-ink-500">{{ __('researcher.projects.internal') }}</span>
                            @endif
                        </p>

                        {{-- Progress, the one number worth seeing from a list --}}
                        <div class="mt-5">
                            <div class="flex items-center justify-between text-[12px] font-semibold">
                                <span class="text-ink-500">{{ __('researcher.projects.progress') }}</span>
                                <span class="font-numeric tabular-nums text-ink-800">{{ (int) $project->progress }}%</span>
                            </div>
                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-ink-100">
                                <div class="h-full rounded-full bg-brand-500" style="width: {{ (int) $project->progress }}%"></div>
                            </div>
                        </div>

                        <div class="mt-auto flex items-center gap-4 pt-5 text-[12px] text-ink-400">
                            <span class="inline-flex items-center gap-1">
                                <x-ui-icon name="chat" class="h-3.5 w-3.5" />
                                {{ $project->updates_count }}
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <x-ui-icon name="document" class="h-3.5 w-3.5" />
                                {{ $project->documents_count }}
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.researcher>
