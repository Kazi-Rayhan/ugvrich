<x-layouts.researcher :title="__('researcher.manuscripts.title')">

    @php $statuses = \App\Models\ResearchManuscript::statuses(); @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-5">
            <div class="max-w-2xl">
                <h2 class="font-display text-[22px] font-bold tracking-tight text-ink-950 sm:text-[26px]">{{ __('researcher.manuscripts.title') }}</h2>
                <p class="mt-2 text-[14.5px] leading-[1.8] text-ink-600">{{ __('researcher.manuscripts.lead') }}</p>
            </div>

            <a href="{{ route('researcher.manuscripts.create') }}" class="btn-lead group shrink-0">
                <span class="relative">{{ __('researcher.manuscripts.new') }}</span>
                <x-ui-icon name="arrow-right" class="relative h-[18px] w-[18px] transition-transform duration-300 group-hover:translate-x-1" />
            </a>
        </div>

        @if ($manuscripts->isEmpty())
            <div class="rounded-[1.5rem] border border-dashed border-ink-200 bg-white px-6 py-16 text-center">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                    <x-ui-icon name="academic" class="h-6 w-6" />
                </span>
                <p class="mt-5 font-display text-[17px] font-bold text-ink-950">{{ __('researcher.manuscripts.none') }}</p>
                <p class="mx-auto mt-2 max-w-md text-[14px] leading-relaxed text-ink-500">{{ __('researcher.manuscripts.none_lead') }}</p>
            </div>
        @else
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($manuscripts as $manuscript)
                    <a href="{{ route('researcher.manuscripts.show', $manuscript) }}"
                       class="group flex flex-col rounded-2xl border border-ink-100 bg-white p-6 transition duration-300 hover:-translate-y-1 hover:border-brand-300 hover:shadow-[0_24px_50px_-40px_rgba(2,34,81,0.5)]">

                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-display text-[16px] font-bold leading-snug text-ink-950 transition group-hover:text-brand-700">{{ $manuscript->title }}</h3>
                            <x-researcher-status :status="$manuscript->status" :labels="$statuses" />
                        </div>

                        <p class="mt-2 text-[12.5px] text-ink-500">
                            {{ $manuscript->reference() }}
                            @if ($manuscript->project)
                                · {{ $manuscript->project->title }}
                            @endif
                        </p>

                        <p class="mt-3 line-clamp-2 text-[13.5px] leading-relaxed text-ink-600">{{ $manuscript->abstract }}</p>

                        <div class="mt-auto flex flex-wrap items-center gap-4 pt-5 text-[12px] text-ink-400">
                            @if ($manuscript->target_journal)
                                <span class="truncate">{{ $manuscript->target_journal }}</span>
                            @endif
                            @if ($manuscript->reviews_count)
                                <span class="inline-flex items-center gap-1">
                                    <x-ui-icon name="chat" class="h-3.5 w-3.5" />
                                    {{ $manuscript->reviews_count }}
                                </span>
                            @endif
                            @if ($manuscript->is_public)
                                <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[11px] font-semibold text-brand-700">{{ __('researcher.manuscripts.public_note') }}</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.researcher>
