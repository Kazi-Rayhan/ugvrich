<x-layouts.researcher :title="$support->reference()">

    @php $statuses = \App\Models\ResearchSupport::statusLabels(); @endphp

    <div class="max-w-3xl space-y-6">

        {{-- What was asked --}}
        <div class="rounded-[1.5rem] border border-ink-100 bg-white p-6 sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="font-numeric text-[11.5px] font-semibold tracking-wider text-ink-400">{{ $support->reference() }}</p>
                    <h2 class="mt-1.5 font-display text-[20px] font-bold leading-snug tracking-tight text-ink-950 sm:text-[23px]">{{ $support->title }}</h2>
                </div>

                <x-researcher-status :status="$support->status" :labels="$statuses" class="mt-1" />
            </div>

            @if ($support->support_types)
                <div class="mt-5">
                    <p class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('researcher.support.types') }}</p>
                    <div class="mt-2.5 flex flex-wrap gap-1.5">
                        @foreach ($support->support_types as $type)
                            <span class="rounded-lg border border-brand-100 bg-brand-50/70 px-2.5 py-1 text-[12.5px] font-semibold text-brand-800">{{ $types[$type] ?? $type }}</span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-6">
                <p class="text-[10.5px] font-semibold uppercase tracking-[0.16em] text-ink-400">{{ __('researcher.support.details') }}</p>
                <p class="mt-2.5 whitespace-pre-line text-[14.5px] leading-[1.85] text-ink-700">{{ $support->details }}</p>
            </div>

            {{-- The small facts, left out when there is nothing to say --}}
            @php
                $facts = array_filter([
                    __('researcher.support.sent_on') => $support->created_at?->isoFormat('D MMMM YYYY'),
                    __('researcher.support.stage') => $support->stage ? ($stages[$support->stage] ?? $support->stage) : null,
                    __('researcher.support.needed_by') => $support->needed_by?->isoFormat('D MMMM YYYY'),
                    __('research_hub.forms.department') => $support->department,
                ]);
            @endphp

            <dl class="mt-7 grid gap-4 border-t border-ink-100 pt-6 sm:grid-cols-2">
                @foreach ($facts as $label => $value)
                    <div>
                        <dt class="text-[11.5px] font-semibold uppercase tracking-wider text-ink-400">{{ $label }}</dt>
                        <dd class="mt-1 text-[14px] font-medium text-ink-800">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            @if ($support->document)
                <a href="{{ route('researcher.support.download', $support) }}"
                   class="mt-6 inline-flex items-center gap-2.5 rounded-xl border border-ink-200 px-4 py-2.5 text-[13.5px] font-semibold text-ink-700 transition hover:border-brand-300 hover:text-brand-700">
                    <x-ui-icon name="document" class="h-[18px] w-[18px]" />
                    {{ __('researcher.support.document') }}
                </a>
            @endif
        </div>

        {{-- What came back. The Research Wing writes its reply into the request
             itself, so there is one thread rather than two records. --}}
        <div class="rounded-[1.5rem] border border-ink-100 bg-white">
            <p class="border-b border-ink-100 px-6 py-4 font-display text-[15.5px] font-bold text-ink-950">{{ __('researcher.support.reply') }}</p>

            @if ($support->admin_notes)
                <div class="px-6 py-6">
                    <p class="whitespace-pre-line text-[14.5px] leading-[1.85] text-ink-700">{{ $support->admin_notes }}</p>

                    @if ($support->reviewed_at)
                        <p class="mt-4 text-[12px] text-ink-400">{{ __('researcher.support.answered_on') }} {{ $support->reviewed_at->isoFormat('D MMMM YYYY') }}</p>
                    @endif
                </div>
            @else
                <p class="px-6 py-8 text-[13.5px] leading-relaxed text-ink-500">{{ __('researcher.support.no_reply') }}</p>
            @endif
        </div>

        <a href="{{ route('researcher.support.index') }}" class="inline-flex items-center gap-2 text-[13.5px] font-semibold text-ink-500 transition hover:text-ink-800">
            <x-ui-icon name="arrow-left" class="h-4 w-4" />
            {{ __('researcher.support.back') }}
        </a>
    </div>
</x-layouts.researcher>
